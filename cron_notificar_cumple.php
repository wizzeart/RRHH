<?php
/**
 * Cron Job: Felicitar trabajadores por su cumpleaños
 *
 * Verifica si notificar_cumpleanos está activado en configuracion_sms,
 * obtiene los trabajadores que cumplen años hoy y les envía un SMS
 * de felicitación de parte de Recursos Humanos.
 */

date_default_timezone_set('America/Havana');

if (!defined('BASE')) {
    define('BASE', __DIR__);
}

require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/classes/MSSql.class.php');
require_once(__DIR__ . '/classes/mdl.NotificacionesSMS.php');

$db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_);

$app = new stdClass();
$app->db = $db;

$sms = new NotificacionesSMS($app);
$fecha_actual = date('Y-m-d');
$mes_actual = intval(date('m'));
$dia_actual = intval(date('d'));

echo "[" . date('Y-m-d H:i:s') . "] Iniciando felicitaciones de cumpleaños...\n";

try {
    // 1. Verificar si las notificaciones de cumpleaños están activadas
    $sql_config = "SELECT notificar_cumpleanos FROM configuracion_sms WHERE id = 1 LIMIT 1";
    $config = $db->fetchRow($sql_config);

    if (!$config || empty($config['notificar_cumpleanos'])) {
        echo "[" . date('Y-m-d H:i:s') . "] Notificaciones de cumpleaños desactivadas. Saliendo.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Notificaciones de cumpleaños ACTIVADAS.\n";

    // 2. Obtener trabajadores que cumplen años hoy
    $sql_cumple = "SELECT
                t.id,
                t.nombre,
                t.apellidos,
                t.telefono,
                t.fecha_nacimiento,
                t.carnet_identidad
            FROM trabajadores t
            WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
            AND t.estatus = 'activo'
            AND t.telefono IS NOT NULL
            AND t.telefono != ''
            AND (
                MONTH(t.fecha_nacimiento) = :mes
                AND DAY(t.fecha_nacimiento) = :dia
            )
            ORDER BY t.nombre, t.apellidos";

    $cumples = $db->fetchAll($sql_cumple, [
        ':mes' => $mes_actual,
        ':dia' => $dia_actual
    ]);

    // Si no hay resultados con fecha_nacimiento, intentar con carnet_identidad
    if (empty($cumples)) {
        $sql_cumple_ci = "SELECT
                    t.id,
                    t.nombre,
                    t.apellidos,
                    t.telefono,
                    t.fecha_nacimiento,
                    t.carnet_identidad
                FROM trabajadores t
                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                AND t.estatus = 'activo'
                AND t.telefono IS NOT NULL
                AND t.telefono != ''
                AND t.fecha_nacimiento IS NULL
                AND LENGTH(t.carnet_identidad) >= 6
                AND CAST(SUBSTRING(t.carnet_identidad, 3, 2) AS UNSIGNED) = :mes
                AND CAST(SUBSTRING(t.carnet_identidad, 5, 2) AS UNSIGNED) = :dia
                ORDER BY t.nombre, t.apellidos";

        $cumples = $db->fetchAll($sql_cumple_ci, [
            ':mes' => $mes_actual,
            ':dia' => $dia_actual
        ]);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Cumpleañeros encontrados: " . count($cumples) . "\n";

    if (empty($cumples)) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay cumpleañeros hoy. Saliendo.\n";
        exit(0);
    }

    // 3. Enviar SMS de felicitación
    $enviados = 0;
    $fallidos = 0;
    $anio_actual = date('Y');

    foreach ($cumples as $trabajador) {
        $nombre_completo = trim($trabajador['nombre']);
        $telefono = preg_replace('/\s+/', '', $trabajador['telefono']);

        if (empty($telefono)) {
            $fallidos++;
            continue;
        }

        // Calcular edad
        $edad = '';
        if (!empty($trabajador['fecha_nacimiento'])) {
            $nacimiento = new DateTime($trabajador['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad = $hoy->diff($nacimiento)->y;
        } elseif (!empty($trabajador['carnet_identidad']) && strlen($trabajador['carnet_identidad']) >= 6) {
            $yearCode = intval(substr($trabajador['carnet_identidad'], 0, 2));
            $birthYear = ($yearCode <= 23) ? (2000 + $yearCode) : (1900 + $yearCode);
            $edad = $anio_actual - $birthYear;
        }

        // Construir mensaje
        if (!empty($edad)) {
            $mensaje = "Estimado/a {$nombre_completo}, le deseamos un feliz cumple. "
                . "Que tenga un bonito dia lleno de alegria. Felices {$edad}!";
        } else {
            $mensaje = "Estimado/a {$nombre_completo}, le deseamos un feliz cumple. "
                . "Que tenga un bonito dia lleno de alegria!";
        }

        // Registrar en la base de datos
        try {
            $db->insert('sms_notificaciones', [
                'usuario_id' => null,
                'trabajador_id' => $trabajador['id'],
                'mensaje' => $mensaje,
                'fecha' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error registrando SMS cumpleanyos trabajador {$trabajador['id']}: " . $e->getMessage());
        }

        // Enviar usando el método reutilizable del modelo
        try {
            $sms->enviarSMS([$telefono], $mensaje);
            $enviados++;
            echo "[" . date('Y-m-d H:i:s') . "] SMS enviado a: {$nombre_completo} ({$telefono})" . (!empty($edad) ? " - {$edad} anyos" : "") . "\n";
        } catch (Exception $e) {
            $fallidos++;
            error_log("Error enviando SMS cumpleanyos trabajador {$trabajador['id']}: " . $e->getMessage());
            echo "[" . date('Y-m-d H:i:s') . "] ERROR: {$nombre_completo} - " . $e->getMessage() . "\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado. Enviados: {$enviados}, Fallidos: {$fallidos}, Total: " . count($cumples) . "\n";

} catch (Exception $e) {
    error_log("Error en cron_notificar_cumple: " . $e->getMessage());
    echo "[" . date('Y-m-d H:i:s') . "] ERROR GENERAL: " . $e->getMessage() . "\n";
    exit(1);
}

$db->close();
exit(0);
