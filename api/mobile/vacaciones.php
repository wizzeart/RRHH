<?php
/**
 * Vacaciones del trabajador autenticado.
 *   GET  /api/mobile/vacaciones                  -> resumen + solicitudes
 *   POST /api/mobile/vacaciones/solicitar        {fecha_inicio, fecha_fin, motivo}
 *
 * Tablas reales (prod):
 *   submayor_vacaciones (id_trabajador, vacaciones, pago_vacaciones)  -> acumuladas
 *   plan_vacaciones     (id, trabajador_id, fecha_inicio, fecha_fin, dias,
 *                        estado, observaciones, fecha_creacion, fecha_aprobacion)
 */

function vacaciones_list(App $app)
{
    $sess = require_worker_session($app);
    $tid  = $sess['trabajador_id'];

    // Acumuladas
    $acumuladas = 0;
    try {
        $row = $app->db->fetchRow(
            "SELECT vacaciones FROM submayor_vacaciones WHERE id_trabajador = :id",
            ['id' => $tid]
        );
        if ($row && isset($row['vacaciones'])) $acumuladas = floatval($row['vacaciones']);
    } catch (Exception $e) {
        try {
            $row = $app->db->fetchRow("SELECT vacaciones_acc FROM trabajadores WHERE id = :id", ['id' => $tid]);
            if ($row) $acumuladas = floatval($row['vacaciones_acc']);
        } catch (Exception $e2) { /* ignore */ }
    }

    // Solicitudes / plan de vacaciones
    $solicitudes = [];
    try {
        $solicitudes = $app->db->fetchAll(
            "SELECT id, fecha_inicio, fecha_fin, dias, estado, observaciones,
                    fecha_creacion, fecha_aprobacion
             FROM plan_vacaciones
             WHERE trabajador_id = :tid
             ORDER BY COALESCE(fecha_inicio, fecha_creacion) DESC, id DESC",
            ['tid' => $tid]
        );
    } catch (Exception $e) { /* tabla ausente -> [] */ }
    $solicitudes = $solicitudes ?: [];

    // Días usados: contamos los días de las solicitudes ya aprobadas.
    $usadas = 0;
    foreach ($solicitudes as &$s) {
        $s['dias_num'] = _vac_dias_count($s);
        if (stripos((string)($s['estado'] ?? ''), 'aprobad') !== false
            && stripos((string)($s['estado'] ?? ''), 'rechaz') === false) {
            $usadas += $s['dias_num'];
        }
    }
    unset($s);

    $disponibles = max(0, $acumuladas - $usadas);

    api_ok([
        'resumen' => [
            'acumuladas'  => round($acumuladas, 2),
            'usadas'      => $usadas,
            'disponibles' => round($disponibles, 2),
        ],
        'solicitudes' => $solicitudes,
    ]);
}

/** Calcula nº de días de una solicitud (campo `dias` o rango de fechas). */
function _vac_dias_count($s)
{
    $dias = isset($s['dias']) ? trim((string)$s['dias']) : '';
    if ($dias !== '') {
        if (is_numeric($dias)) return (int)$dias;
        // Lista separada por comas de fechas/días -> contamos elementos.
        $parts = array_filter(array_map('trim', explode(',', $dias)), 'strlen');
        if (!empty($parts)) return count($parts);
    }
    if (!empty($s['fecha_inicio']) && !empty($s['fecha_fin'])) {
        $a = strtotime($s['fecha_inicio']);
        $b = strtotime($s['fecha_fin']);
        if ($a !== false && $b !== false && $b >= $a) {
            return (int)floor(($b - $a) / 86400) + 1;
        }
    }
    return 0;
}

function vacaciones_solicitar(App $app)
{
    require_writable();
    $sess = require_worker_session($app);
    $tid  = $sess['trabajador_id'];
    $in   = api_input();

    $ini    = isset($in['fecha_inicio']) ? trim($in['fecha_inicio']) : '';
    $fin    = isset($in['fecha_fin']) ? trim($in['fecha_fin']) : '';
    $motivo = isset($in['motivo']) ? substr(trim($in['motivo']), 0, 255) : '';

    if ($ini === '' || $fin === '') {
        api_fail('Fecha de inicio y fin son obligatorias.', 422);
    }
    $a = strtotime($ini); $b = strtotime($fin);
    if ($a === false || $b === false || $b < $a) {
        api_fail('Rango de fechas inválido.', 422);
    }

    // `dias` debe guardarse como LISTA de fechas "YYYY-MM-DD,YYYY-MM-DD,...", igual que hace la
    // web en mdl.Vacaciones::_save. Antes aquí se guardaba un simple contador ("7"), lo que dejaba
    // la columna con dos formatos incompatibles: los consumidores que hacen explode(',') o cuentan
    // comas (planificación anual en inicio, prenómina, tope anual de 24 días) interpretaban esos
    // planes como 1 solo día o directamente los descartaban.
    // Se excluyen sábados y domingos, mismo criterio que la web al derivar desde un rango.
    $dias_laborables = [];
    $current = new DateTime(date('Y-m-d', $a));
    $end     = new DateTime(date('Y-m-d', $b));
    while ($current <= $end) {
        $dia_semana = (int) $current->format('w'); // 0 = domingo, 6 = sábado
        if ($dia_semana != 0 && $dia_semana != 6) {
            $dias_laborables[] = $current->format('Y-m-d');
        }
        $current->modify('+1 day');
    }

    if (empty($dias_laborables)) {
        api_fail('El rango seleccionado no contiene días laborables.', 422);
    }

    $dias_string = implode(',', $dias_laborables);
    $dias = count($dias_laborables);

    try {
        $app->db->insert('plan_vacaciones', [
            'trabajador_id' => $tid,
            'fecha_inicio'  => date('Y-m-d', $a),
            'fecha_fin'     => date('Y-m-d', $b),
            'dias'          => $dias_string,
            'estado'        => 'Pendiente',
            'observaciones' => $motivo,
            'fecha_creacion'=> date('Y-m-d'),
        ]);
        $id = method_exists($app->db, 'last_id') ? $app->db->last_id() : null;
        api_ok(['msg' => 'Solicitud enviada', 'id' => $id, 'dias' => $dias]);
    } catch (Exception $e) {
        api_fail('No se pudo registrar la solicitud: ' . $e->getMessage(), 500);
    }
}
