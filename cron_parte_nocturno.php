<?php
/**
 * Cron Job: Parte Nocturno Custodios (7:00 AM)
 *
 * Verifica si notificar_parte_nocturno está activado en configuracion_sms,
 * obtiene los custodios con tipo_horario = 2 (Nocturno) y sus registros
 * de huella entre las 6:00 PM del día anterior y las 6:00 AM de hoy
 * desde la tabla registro_asistencia_horas.
 * Envía un SMS consolidado a los números de la empresa con el
 * reporte de todos los custodios nocturnos y sus horarios de entrada/salida.
 *
 * Ejecutar con cron a las 7:00 AM diariamente:
 * 0 7 * * * php /path/to/cron_parte_nocturno.php
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

echo "[" . date('Y-m-d H:i:s') . "] Iniciando Parte Nocturno Custodios...\n";

try {
    // 1. Verificar si la notificación de parte nocturno está activada
    $sql_config = "SELECT notificar_parte_nocturno FROM configuracion_sms WHERE id = 1 LIMIT 1";
    $config = $db->fetchRow($sql_config);

    if (!$config || !$config['notificar_parte_nocturno']) {
        echo "[" . date('Y-m-d H:i:s') . "] Parte Nocturno Custodios desactivado. Saliendo.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Parte Nocturno Custodios ACTIVADO.\n";

    // 2. Definir el rango de tiempo:
    //    Entrada: 6:00 PM del día anterior
    //    Salida:  6:00 AM de hoy
    $fecha_hoy = date('Y-m-d');
    $fecha_ayer = date('Y-m-d', strtotime('-1 day'));

    // Fecha para el encabezado del reporte (el turno nocturno del día anterior)
    $fecha_reporte = date('d/m/Y', strtotime($fecha_ayer));

    // 3. Obtener custodios nocturnos (tipo_horario = 2) con sus registros de huella
    //    en el rango 6PM ayer - 6AM hoy desde registro_asistencia_horas
    $sql_custodios = "SELECT 
                t.id AS trabajador_id,
                CONCAT(t.nombre, ' ', t.apellidos) AS nombre_completo,
                rah.hora,
                rah.fecha
            FROM trabajadores t
            INNER JOIN registro_asistencia_horas rah ON rah.trabajador_id = t.id
            WHERE t.tipo_horario = 2
            AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
            AND t.estatus = 'activo'
            AND (
                (rah.fecha = :fecha_ayer AND rah.hora >= '18:00:00')
                OR
                (rah.fecha = :fecha_hoy AND rah.hora <= '06:00:00')
            )
            ORDER BY t.nombre, t.apellidos, rah.fecha, rah.hora";

    $registros = $db->fetchAll($sql_custodios, [
        ':fecha_ayer' => $fecha_ayer,
        ':fecha_hoy' => $fecha_hoy
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Registros de huellas encontrados: " . count($registros) . "\n";

    // 4. Agrupar registros por custodio
    $custodios = [];
    foreach ($registros as $reg) {
        $tid = $reg['trabajador_id'];
        if (!isset($custodios[$tid])) {
            $custodios[$tid] = [
                'nombre' => $reg['nombre_completo'],
                'horas' => []
            ];
        }
        // Formatear hora a 12h
        $hora_obj = DateTime::createFromFormat('H:i:s', $reg['hora']);
        if (!$hora_obj) {
            $hora_obj = DateTime::createFromFormat('H:i', $reg['hora']);
        }
        $hora_formateada = $hora_obj ? $hora_obj->format('g:i A') : $reg['hora'];
        $custodios[$tid]['horas'][] = $hora_formateada;
    }

    echo "[" . date('Y-m-d H:i:s') . "] Custodios nocturnos con registros: " . count($custodios) . "\n";

    // 5. También obtener custodios nocturnos SIN registros para reportarlos
    $sql_sin_registro = "SELECT 
                t.id AS trabajador_id,
                CONCAT(t.nombre, ' ', t.apellidos) AS nombre_completo
            FROM trabajadores t
            WHERE t.tipo_horario = 2
            AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
            AND t.estatus = 'activo'
            AND t.id NOT IN (
                SELECT DISTINCT rah.trabajador_id 
                FROM registro_asistencia_horas rah
                WHERE (
                    (rah.fecha = :fecha_ayer2 AND rah.hora >= '18:00:00')
                    OR
                    (rah.fecha = :fecha_hoy2 AND rah.hora <= '06:00:00')
                )
            )
            ORDER BY t.nombre, t.apellidos";

    $sin_registro = $db->fetchAll($sql_sin_registro, [
        ':fecha_ayer2' => $fecha_ayer,
        ':fecha_hoy2' => $fecha_hoy
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Custodios nocturnos SIN registros: " . count($sin_registro) . "\n";

    // 6. Construir el mensaje SMS
    $mensaje = "Control de Custodios Horario Nocturno:\n";
    $mensaje .= "Fecha: {$fecha_reporte}\n";

    if (!empty($custodios)) {
        foreach ($custodios as $tid => $data) {
            $horas_str = implode(', ', $data['horas']);
            $mensaje .= "{$data['nombre']}: {$horas_str}\n";
        }
    }

    if (!empty($sin_registro)) {
        $mensaje .= "Sin registro:\n";
        foreach ($sin_registro as $sr) {
            $mensaje .= "- {$sr['nombre_completo']}\n";
        }
    }

    if (empty($custodios) && empty($sin_registro)) {
        $mensaje .= "No hay custodios nocturnos registrados.";
    }

    // Recortar el mensaje final
    $mensaje = trim($mensaje);

    echo "[" . date('Y-m-d H:i:s') . "] Mensaje construido (" . strlen($mensaje) . " caracteres):\n";
    echo $mensaje . "\n";

    // 7. Números destino fijos
    $telefonos_destino = ['53462188', '58415881'];

    // 8. Enviar SMS a cada número destino
    $enviados = 0;
    $fallidos = 0;

    foreach ($telefonos_destino as $telefono) {
        try {
            $sms->enviarSMS([$telefono], $mensaje);
            $enviados++;
            echo "[" . date('Y-m-d H:i:s') . "] SMS enviado a: {$telefono}\n";
        } catch (Exception $e) {
            $fallidos++;
            error_log("Error enviando Parte Nocturno a {$telefono}: " . $e->getMessage());
            echo "[" . date('Y-m-d H:i:s') . "] ERROR: {$telefono} - " . $e->getMessage() . "\n";
        }
    }

    // 9. Registrar en sms_notificaciones (sin trabajador_id ya que es un reporte general)
    try {
        $db->insert('sms_notificaciones', [
            'usuario_id' => null,
            'trabajador_id' => null,
            'mensaje' => $mensaje,
            'fecha' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        error_log("Error registrando SMS Parte Nocturno: " . $e->getMessage());
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado. Enviados: {$enviados}, Fallidos: {$fallidos}\n";

} catch (Exception $e) {
    error_log("Error en cron_parte_nocturno: " . $e->getMessage());
    echo "[" . date('Y-m-d H:i:s') . "] ERROR GENERAL: " . $e->getMessage() . "\n";
    exit(1);
}

$db->close();
exit(0);
