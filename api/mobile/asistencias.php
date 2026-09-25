<?php
/**
 * Asistencias del trabajador autenticado.
 *   GET  /api/mobile/asistencias?desde=YYYY-MM-DD&hasta=YYYY-MM-DD
 *   POST /api/mobile/asistencias/marcar  {tipo, obs?}   (bloqueado en solo-lectura)
 *
 * Tabla real (prod): `registro_asistencia`
 *   id, trabajador_id, fecha, hora_entrada, hora_salida,
 *   ausencia (0/1), tipo_ausencia, tardanza (0/1), justificacion
 */

function asistencias_list(App $app)
{
    $sess = require_worker_session($app);
    $tid  = $sess['trabajador_id'];

    $desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
    $hasta = isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-t');

    $sql = "SELECT id, trabajador_id, fecha, hora_entrada, hora_salida,
                   ausencia, tipo_ausencia, tardanza, justificacion
            FROM registro_asistencia
            WHERE trabajador_id = :tid AND fecha BETWEEN :d1 AND :d2
            ORDER BY fecha DESC, id DESC";
    try {
        $rows = $app->db->fetchAll($sql, ['tid' => $tid, 'd1' => $desde, 'd2' => $hasta]);
    } catch (Exception $e) {
        $rows = [];
    }
    $rows = $rows ?: [];

    // Normalizamos tipos y construimos resumen.
    $presentes = 0; $ausencias = 0; $tardanzas = 0;
    foreach ($rows as &$r) {
        $r['ausencia']  = (int)($r['ausencia'] ?? 0);
        $r['tardanza']  = (int)($r['tardanza'] ?? 0);
        if ($r['ausencia'] === 1) { $ausencias++; } else { $presentes++; }
        if ($r['tardanza'] === 1) { $tardanzas++; }
    }
    unset($r);

    $resumen = [
        'dias_trabajados' => $presentes,
        'ausencias'       => $ausencias,
        'tardanzas'       => $tardanzas,
        'total'           => count($rows),
        'desde'           => $desde,
        'hasta'           => $hasta,
    ];

    api_ok(['asistencias' => $rows, 'resumen' => $resumen]);
}

function asistencias_marcar(App $app)
{
    require_writable();
    $sess = require_worker_session($app);
    $tid  = $sess['trabajador_id'];
    $in   = api_input();

    $tipo = isset($in['tipo']) && in_array($in['tipo'], ['entrada','salida']) ? $in['tipo'] : 'entrada';

    $hoy = date('Y-m-d');
    $now = date('H:i:s');

    try {
        if ($tipo === 'salida') {
            // Buscamos el registro de hoy para cerrar la salida.
            $last = $app->db->fetchRow(
                "SELECT id FROM registro_asistencia WHERE trabajador_id = :tid AND fecha = :hoy ORDER BY id DESC LIMIT 1",
                ['tid' => $tid, 'hoy' => $hoy]
            );
            if ($last) {
                $app->db->update('registro_asistencia', ['hora_salida' => $now], ['id' => $last['id']]);
                api_ok(['msg' => 'Salida registrada', 'id' => (int)$last['id'], 'tipo' => 'salida', 'hora' => $now, 'fecha' => $hoy]);
            }
        }
        // entrada (o salida sin registro previo): insertamos.
        $app->db->insert('registro_asistencia', [
            'trabajador_id' => $tid,
            'fecha'         => $hoy,
            'hora_entrada'  => $now,
            'ausencia'      => 0,
            'tardanza'      => 0,
        ]);
        $id = method_exists($app->db, 'last_id') ? $app->db->last_id() : null;
        api_ok(['msg' => 'Asistencia registrada', 'id' => $id, 'tipo' => $tipo, 'hora' => $now, 'fecha' => $hoy]);
    } catch (Exception $e) {
        api_fail('No se pudo registrar la asistencia: ' . $e->getMessage(), 500);
    }
}
