<?php
/**
 * Avisos / notificaciones internas (almacén JSON, sin BD).
 *   GET  /api/mobile/avisos              -> avisos visibles + nº no leídos
 *   POST /api/mobile/avisos/leer         {aviso_id?}  (sin id = marcar todos)
 *   POST /api/mobile/avisos/crear        {titulo, cuerpo, tipo?, alcance}  (ADMIN)
 *
 * Visibilidad: un aviso con empresa_id = null es GLOBAL (todos); con empresa_id
 * fijado solo lo ven los trabajadores de esa empresa.
 */

require_once __DIR__ . '/_store.php';

/** Avisos visibles para una empresa dada (null/0 = solo globales). */
function _avisos_visibles($empresaId)
{
    $all = store_read('avisos');
    $out = [];
    foreach ($all as $a) {
        if (empty($a['activo'])) continue;
        $emp = isset($a['empresa_id']) ? $a['empresa_id'] : null;
        if ($emp === null || (int)$emp === (int)$empresaId) {
            $out[] = $a;
        }
    }
    // Más recientes primero.
    usort($out, function ($x, $y) {
        return strcmp((string)($y['created_at'] ?? ''), (string)($x['created_at'] ?? ''));
    });
    return $out;
}

/** IDs de avisos ya leídos por un usuario. */
function _avisos_leidos_ids($usuarioId)
{
    $rows = store_read('avisos_leidos');
    $ids = [];
    foreach ($rows as $r) {
        if ((int)($r['usuario_id'] ?? 0) === (int)$usuarioId) {
            $ids[(int)$r['aviso_id']] = true;
        }
    }
    return $ids;
}

function avisos_list(App $app)
{
    $sess = require_user_session($app);
    $avisos = _avisos_visibles($sess['empresa_id']);
    $leidos = _avisos_leidos_ids($sess['user_id']);

    $noLeidos = 0;
    foreach ($avisos as &$a) {
        $a['leido'] = isset($leidos[(int)$a['id']]);
        if (!$a['leido']) $noLeidos++;
        // No exponemos campos internos sensibles más allá de lo necesario.
        $a['id'] = (int)$a['id'];
    }
    unset($a);

    api_ok(['avisos' => array_values($avisos), 'no_leidos' => $noLeidos]);
}

function avisos_marcar_leido(App $app)
{
    require_writable();
    $sess = require_user_session($app);
    $in   = api_input();

    $uid = $sess['user_id'];
    $rows = store_read('avisos_leidos');
    $yaLeidos = _avisos_leidos_ids($uid);
    $ahora = date('Y-m-d H:i:s');

    if (isset($in['aviso_id']) && (int)$in['aviso_id'] > 0) {
        $aid = (int)$in['aviso_id'];
        if (!isset($yaLeidos[$aid])) {
            $rows[] = ['aviso_id' => $aid, 'usuario_id' => (int)$uid, 'leido_at' => $ahora];
        }
    } else {
        // Marcar todos los visibles como leídos.
        foreach (_avisos_visibles($sess['empresa_id']) as $a) {
            $aid = (int)$a['id'];
            if (!isset($yaLeidos[$aid])) {
                $rows[] = ['aviso_id' => $aid, 'usuario_id' => (int)$uid, 'leido_at' => $ahora];
                $yaLeidos[$aid] = true;
            }
        }
    }

    store_write('avisos_leidos', $rows);
    api_ok(['msg' => 'Marcado como leído']);
}

function avisos_crear(App $app)
{
    require_writable();
    $sess = require_admin($app);
    $in   = api_input();

    $titulo = isset($in['titulo']) ? trim((string)$in['titulo']) : '';
    $cuerpo = isset($in['cuerpo']) ? trim((string)$in['cuerpo']) : '';
    $tipo   = isset($in['tipo']) ? trim((string)$in['tipo']) : 'info';
    $alcance = isset($in['alcance']) ? trim((string)$in['alcance']) : 'empresa';

    if ($titulo === '' || $cuerpo === '') {
        api_fail('El título y el contenido son obligatorios.', 422);
    }
    if (!in_array($tipo, ['info', 'aviso', 'urgente'], true)) $tipo = 'info';

    // 'global' = visible para todas las empresas; 'empresa' = solo la del admin.
    $empresaId = ($alcance === 'global') ? null : (int)$sess['empresa_id'];

    $nombre = $sess['worker']
        ? trim(($sess['worker']['nombre'] ?? '') . ' ' . ($sess['worker']['apellidos'] ?? ''))
        : '';

    $rows = store_read('avisos');
    $aviso = [
        'id'            => store_next_id($rows),
        'titulo'        => mb_substr($titulo, 0, 150),
        'cuerpo'        => mb_substr($cuerpo, 0, 2000),
        'tipo'          => $tipo,
        'empresa_id'    => $empresaId,
        'creado_por'    => (int)$sess['user_id'],
        'creado_nombre' => $nombre !== '' ? $nombre : ($sess['username'] ?? 'Admin'),
        'activo'        => 1,
        'created_at'    => date('Y-m-d H:i:s'),
    ];
    $rows[] = $aviso;
    store_write('avisos', $rows);

    // Push (fase 2): best-effort, no bloquea si falla / no hay tokens.
    try {
        require_once __DIR__ . '/push.php';
        if (function_exists('push_broadcast')) {
            push_broadcast($empresaId, $titulo, $cuerpo, ['aviso_id' => $aviso['id'], 'tipo' => $tipo]);
        }
    } catch (Exception $e) { /* ignore */ }

    api_ok(['msg' => 'Aviso publicado', 'aviso' => $aviso]);
}
