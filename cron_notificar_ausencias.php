<?php
/**
 * Cron Job: Notificar trabajadores ausentes a las 9 AM
 *
 * Verifica si notificar_ausencia está activado en configuracion_sms,
 * obtiene los trabajadores sin entrada y les envía un SMS
 * indicando que son considerados ausentes, deben registrar su huella
 * y contactar con Recursos Humanos.
 *
 * No se notifica a trabajadores en baja sin liquidar (es_liquidacion = 1):
 * ya no están activos en la práctica, solo pendientes del trámite final.
 */

date_default_timezone_set('America/Havana');

if (!defined('BASE')) {
    define('BASE', __DIR__);
}

require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/classes/MSSql.class.php');
require_once(__DIR__ . '/classes/mdl.NotificacionesSMS.php');

$db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_);

// App mínima para instanciar NotificacionesSMS
$app = new stdClass();
$app->db = $db;

$sms = new NotificacionesSMS($app);
$fecha_actual = date('Y-m-d');

echo "[" . date('Y-m-d H:i:s') . "] Iniciando notificación de ausencias...\n";

try {
    // 1. Verificar si las notificaciones de ausencia están activadas
    $sql_config = "SELECT notificar_ausencia FROM configuracion_sms WHERE id = 1 LIMIT 1";
    $config = $db->fetchRow($sql_config);

    if (!$config || !$config['notificar_ausencia']) {
        echo "[" . date('Y-m-d H:i:s') . "] Notificaciones de ausencia desactivadas. Saliendo.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Notificaciones de ausencia ACTIVADAS.\n";

    // 2. Obtener trabajadores ausentes (sin entrada, no vacaciones, activos)
    $sql_ausentes = "SELECT DISTINCT
                t.id,
                t.nombre,
                t.apellidos,
                t.telefono
            FROM trabajadores t
            LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id
                AND DATE(ra.fecha) = :fecha_ra
            LEFT JOIN plan_vacaciones v ON v.trabajador_id = t.id
                AND v.estado IN ('Aprobado', 'Procesada')
                AND v.fecha_inicio <= :fecha_vac
                AND v.fecha_fin >= :fecha_vac
            WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
            AND t.estatus = 'activo'
            AND (t.es_liquidacion = 0 OR t.es_liquidacion IS NULL)
            AND (t.tipo_horario IS NULL OR t.tipo_horario = 1)
            AND v.id IS NULL
            AND t.telefono IS NOT NULL
            AND t.telefono != ''
            AND NOT EXISTS (
                SELECT 1 FROM registro_asistencia ra2
                WHERE ra2.trabajador_id = t.id
                AND DATE(ra2.fecha) = :fecha
                AND ra2.hora_entrada IS NOT NULL
            )
            AND NOT EXISTS (
                SELECT 1 FROM registro_asistencia ra3
                WHERE ra3.trabajador_id = t.id
                AND DATE(ra3.fecha) = :fecha_just
                AND ra3.tipo_ausencia = 'Justificada'
            )
            ORDER BY t.nombre, t.apellidos";

    $ausentes = $db->fetchAll($sql_ausentes, [
        ':fecha_ra' => $fecha_actual,
        ':fecha_vac' => $fecha_actual,
        ':fecha' => $fecha_actual,
        ':fecha_just' => $fecha_actual
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Trabajadores ausentes encontrados: " . count($ausentes) . "\n";

    if (empty($ausentes)) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay trabajadores ausentes para notificar. Saliendo.\n";
        exit(0);
    }

    // 3. Enviar SMS individual a cada ausente
    $enviados = 0;
    $fallidos = 0;

    foreach ($ausentes as $trabajador) {
        $nombre_completo = trim($trabajador['nombre'] . ' ' . $trabajador['apellidos']);
        $telefono = preg_replace('/\s+/', '', $trabajador['telefono']);

        if (empty($telefono)) {
            $fallidos++;
            continue;
        }

        $mensaje = "Estimado/a trabajador/a, a las 9:00 AM usted es considerado/a ausente. "
            . "Por favor contacte con Recursos Humanos.";

        // Registrar en la base de datos
        try {
            $db->insert('sms_notificaciones', [
                'usuario_id' => null,
                'trabajador_id' => $trabajador['id'],
                'mensaje' => $mensaje,
                'fecha' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error registrando SMS trabajador {$trabajador['id']}: " . $e->getMessage());
        }

        // Enviar usando el método reutilizable del modelo
        try {
            $sms->enviarSMS([$telefono], $mensaje);
            $enviados++;
            echo "[" . date('Y-m-d H:i:s') . "] SMS enviado a: {$nombre_completo} ({$telefono})\n";
        } catch (Exception $e) {
            $fallidos++;
            error_log("Error enviando SMS trabajador {$trabajador['id']}: " . $e->getMessage());
            echo "[" . date('Y-m-d H:i:s') . "] ERROR: {$nombre_completo} - " . $e->getMessage() . "\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado. Enviados: {$enviados}, Fallidos: {$fallidos}, Total: " . count($ausentes) . "\n";

} catch (Exception $e) {
    error_log("Error en cron_notificar_ausencias: " . $e->getMessage());
    echo "[" . date('Y-m-d H:i:s') . "] ERROR GENERAL: " . $e->getMessage() . "\n";
    exit(1);
}

$db->close();
exit(0);
