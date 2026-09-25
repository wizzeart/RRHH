<?php
/**
 * Cron Job: Devengo mensual de vacaciones
 *
 * Suma una cantidad fija de días de vacaciones a `trabajadores.vacaciones_acc` para TODOS los
 * trabajadores activos (todas las empresas), una vez al mes.
 *
 * Las vacaciones se atribuyen por MES VENCIDO: el script se ejecuta el día 1 de cada mes y
 * acredita los 2.18 días correspondientes al MES ANTERIOR (el que acaba de terminar).
 * Ejemplo: al correr el 1 de julio se acredita el período de junio.
 *
 * Es la ÚNICA fuente del devengo mensual: la lógica que antes lo hacía dentro de la prenómina
 * (mdl.Prenomina.php) fue retirada para evitar el doble devengo.
 *
 * Idempotente: registra cada período (año-mes) en la tabla `devengo_vacaciones_procesado`,
 * de modo que reejecutarlo para el mismo período no vuelve a sumar.
 *
 * Crontab (día 1 de cada mes, 06:00, zona del servidor):
 *   0 6 1 * * /usr/bin/php /ruta/al/proyecto/cron_devengo_vacaciones.php >> /ruta/al/proyecto/logs/cron_devengo_vacaciones.log 2>&1
 *
 * Uso manual / backfill (opcional):
 *   php cron_devengo_vacaciones.php            -> acredita el mes anterior al actual (mes vencido)
 *   php cron_devengo_vacaciones.php 2026 6     -> acredita el período indicado (año mes)
 */

date_default_timezone_set('America/Havana');

if (!defined('BASE')) {
    define('BASE', __DIR__);
}

require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/classes/MSSql.class.php');

// Cantidad fija de días a devengar cada mes
$DIAS_POR_MES = 2.18;

// Período a acreditar. Por MES VENCIDO: por defecto el mes ANTERIOR al de ejecución
// (al correr el día 1, se acredita el mes que acaba de terminar). Admite override por CLI:
// "php cron_devengo_vacaciones.php <year> <month>" para acreditar un período concreto.
$mesVencido = strtotime('first day of last month');
$year  = isset($argv[1]) ? intval($argv[1]) : intval(date('Y', $mesVencido));
$month = isset($argv[2]) ? intval($argv[2]) : intval(date('n', $mesVencido));
$periodo_key = sprintf('%04d-%02d', $year, $month);

$db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_);

echo "[" . date('Y-m-d H:i:s') . "] Iniciando devengo de vacaciones por mes vencido. Período acreditado: {$periodo_key} (+{$DIAS_POR_MES} días)...\n";

try {
    // 1. Asegurar la tabla de control de idempotencia
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'devengo_vacaciones_procesado'");
    if (empty($tableExists)) {
        $db->directExec("CREATE TABLE IF NOT EXISTS `devengo_vacaciones_procesado` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `year` INT(4) NOT NULL,
            `month` INT(2) NOT NULL,
            `dias` DECIMAL(8,4) NOT NULL,
            `trabajadores_afectados` INT(11) NOT NULL DEFAULT 0,
            `fecha_proceso` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_periodo` (`year`, `month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        echo "[" . date('Y-m-d H:i:s') . "] Tabla devengo_vacaciones_procesado creada.\n";
    }

    // 2. Verificar idempotencia: ¿ya se procesó este período?
    $yaProces = $db->fetchRow(
        "SELECT id FROM devengo_vacaciones_procesado WHERE `year` = :y AND `month` = :m",
        ['y' => $year, 'm' => $month]
    );
    if ($yaProces) {
        echo "[" . date('Y-m-d H:i:s') . "] El período {$periodo_key} ya fue procesado. Saliendo sin cambios.\n";
        $db->close();
        exit(0);
    }

    // 3. Contar trabajadores activos (para log). Sin filtro de empresa = todas las empresas.
    $whereActivos = "(trabajador_eliminado = 0 OR trabajador_eliminado IS NULL) AND estatus = 'activo'";
    $rowCount = $db->fetchRow("SELECT COUNT(*) AS total FROM trabajadores WHERE {$whereActivos}");
    $totalActivos = $rowCount ? intval($rowCount['total']) : 0;
    echo "[" . date('Y-m-d H:i:s') . "] Trabajadores activos a procesar: {$totalActivos}\n";

    if ($totalActivos === 0) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay trabajadores activos. Registrando período y saliendo.\n";
    } else {
        // 4. Update masivo y atómico: sumar la cantidad fija a vacaciones_acc de todos los activos
        $db->directExec(
            "UPDATE trabajadores
                SET vacaciones_acc = COALESCE(vacaciones_acc, 0) + :dias
              WHERE {$whereActivos}",
            ['dias' => $DIAS_POR_MES]
        );
        echo "[" . date('Y-m-d H:i:s') . "] Devengo aplicado: +{$DIAS_POR_MES} días a {$totalActivos} trabajadores.\n";
    }

    // 5. Registrar el período como procesado (idempotencia)
    $db->insert('devengo_vacaciones_procesado', [
        'year' => $year,
        'month' => $month,
        'dias' => $DIAS_POR_MES,
        'trabajadores_afectados' => $totalActivos,
        'fecha_proceso' => date('Y-m-d H:i:s')
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado para {$periodo_key}.\n";

} catch (Exception $e) {
    error_log("Error en cron_devengo_vacaciones ({$periodo_key}): " . $e->getMessage());
    echo "[" . date('Y-m-d H:i:s') . "] ERROR GENERAL: " . $e->getMessage() . "\n";
    $db->close();
    exit(1);
}

$db->close();
exit(0);
