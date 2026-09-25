<?php
/**
 * Panel de administración (solo rol 1). Lecturas + aprobación de vacaciones.
 *   GET  /api/mobile/admin/resumen                 -> estadísticas globales
 *   GET  /api/mobile/admin/vacaciones              -> solicitudes por resolver
 *   POST /api/mobile/admin/vacaciones/resolver     {id, accion: aprobar|rechazar}
 *
 * Todo se acota a la empresa del admin (empresa_id) cuando está definida.
 * La aprobación SOLO actualiza filas de la tabla existente `plan_vacaciones`
 * (no crea tablas ni cambia el esquema).
 */

require_once __DIR__ . '/_store.php';
require_once __DIR__ . '/avisos.php';

function admin_resumen(App $app)
{
    $sess = require_admin($app);
    $emp  = (int)$sess['empresa_id'];
    $year = date('Y');

    $scope = $emp > 0 ? ' AND t.empresa_id = :emp ' : '';
    $bind  = $emp > 0 ? ['emp' => $emp] : [];

    $totalTrab = 0; $vacPend = 0; $vacAprob = 0;

    try {
        $r = $app->db->fetchRow(
            "SELECT COUNT(*) AS n FROM trabajadores t WHERE t.trabajador_eliminado = 0 $scope",
            $bind
        );
        $totalTrab = $r ? (int)$r['n'] : 0;
    } catch (Exception $e) { /* ignore */ }

    try {
        $r = $app->db->fetchRow(
            "SELECT COUNT(*) AS n
             FROM plan_vacaciones pv
             INNER JOIN trabajadores t ON t.id = pv.trabajador_id
             WHERE (pv.estado IS NULL OR pv.estado NOT IN ('Aprobado','Rechazado')) $scope",
            $bind
        );
        $vacPend = $r ? (int)$r['n'] : 0;
    } catch (Exception $e) { /* ignore */ }

    try {
        $r = $app->db->fetchRow(
            "SELECT COUNT(*) AS n
             FROM plan_vacaciones pv
             INNER JOIN trabajadores t ON t.id = pv.trabajador_id
             WHERE pv.fecha_aprobacion IS NOT NULL AND pv.fecha_aprobacion <> '0000-00-00'
             AND YEAR(pv.fecha_inicio) = :yr $scope",
            array_merge($bind, ['yr' => $year])
        );
        $vacAprob = $r ? (int)$r['n'] : 0;
    } catch (Exception $e) { /* ignore */ }

    // Avisos activos visibles para la empresa del admin (desde el almacén JSON).
    $avisosActivos = count(_avisos_visibles($emp));

    api_ok(['resumen' => [
        'total_trabajadores'      => $totalTrab,
        'vacaciones_pendientes'   => $vacPend,
        'vacaciones_aprobadas_anio' => $vacAprob,
        'avisos_activos'          => $avisosActivos,
        'empresa_id'              => $emp,
    ]]);
}

function admin_vacaciones_list(App $app)
{
    $sess = require_admin($app);
    $emp  = (int)$sess['empresa_id'];
    $scope = $emp > 0 ? ' AND t.empresa_id = :emp ' : '';
    $bind  = $emp > 0 ? ['emp' => $emp] : [];

    $rows = [];
    try {
        $rows = $app->db->fetchAll(
            "SELECT pv.id, pv.trabajador_id, pv.fecha_inicio, pv.fecha_fin, pv.dias,
                    pv.estado, pv.observaciones, pv.fecha_creacion,
                    t.nombre, t.apellidos, t.apellidos_segundos
             FROM plan_vacaciones pv
             INNER JOIN trabajadores t ON t.id = pv.trabajador_id
             WHERE (pv.estado IS NULL OR pv.estado NOT IN ('Aprobado','Rechazado')) $scope
             ORDER BY COALESCE(pv.fecha_creacion, pv.fecha_inicio) DESC, pv.id DESC",
            $bind
        );
    } catch (Exception $e) { $rows = []; }
    $rows = $rows ?: [];

    foreach ($rows as &$r) {
        $r['id'] = (int)$r['id'];
        $r['trabajador'] = trim(($r['nombre'] ?? '') . ' ' . ($r['apellidos'] ?? '') . ' ' . ($r['apellidos_segundos'] ?? ''));
    }
    unset($r);

    api_ok(['vacaciones' => $rows]);
}

function admin_vacaciones_resolver(App $app)
{
    require_writable();
    $sess = require_admin($app);
    $emp  = (int)$sess['empresa_id'];
    $in   = api_input();

    $id     = isset($in['id']) ? (int)$in['id'] : 0;
    $accion = isset($in['accion']) ? trim((string)$in['accion']) : '';
    if ($id <= 0 || !in_array($accion, ['aprobar', 'rechazar'], true)) {
        api_fail('Parámetros inválidos.', 422);
    }

    // Verificar que la solicitud existe y pertenece a la empresa del admin.
    $row = $app->db->fetchRow(
        "SELECT pv.id, pv.estado, t.empresa_id
         FROM plan_vacaciones pv
         INNER JOIN trabajadores t ON t.id = pv.trabajador_id
         WHERE pv.id = :id",
        ['id' => $id]
    );
    if (!$row) api_fail('Solicitud no encontrada.', 404);
    if ($emp > 0 && (int)$row['empresa_id'] !== $emp) {
        api_fail('No puedes resolver solicitudes de otra empresa.', 403);
    }
    if (in_array((string)$row['estado'], ['Aprobado', 'Rechazado'], true)) {
        api_fail('Esta solicitud ya fue procesada.', 409);
    }

    try {
        if ($accion === 'aprobar') {
            $app->db->update('plan_vacaciones',
                ['estado' => 'Aprobado', 'fecha_aprobacion' => date('Y-m-d')],
                ['id' => $id]
            );
            $msg = 'Vacación aprobada.';
        } else {
            $app->db->update('plan_vacaciones',
                ['estado' => 'Rechazado'],
                ['id' => $id]
            );
            $msg = 'Vacación rechazada.';
        }
    } catch (Exception $e) {
        api_fail('No se pudo actualizar la solicitud: ' . $e->getMessage(), 500);
    }

    api_ok(['msg' => $msg, 'id' => $id, 'accion' => $accion]);
}
