<?php
/**
 * Router REST de la API móvil AllnovuWorker.
 *
 * URL: /api/mobile/index.php?action=<accion>
 * o vía .htaccess: /api/mobile/<accion>
 *
 * Acciones públicas:
 *   POST login                {email, password}
 *   POST refresh              {refresh_token}
 *
 * Acciones autenticadas (Authorization: Bearer <jwt>):
 *   GET  me
 *   GET  ficha
 *   GET  asistencias           [?desde=YYYY-MM-DD&hasta=YYYY-MM-DD]
 *   POST asistencias/marcar    {lat, lng, tipo:"entrada"|"salida", obs?}
 *   GET  vacaciones
 *   POST vacaciones/solicitar  {fecha_inicio, fecha_fin, motivo}
 *   GET  documentos
 *   POST documentos/firmar     {documento_id, firma_base64}
 *   GET  empresa-logo
 */

require_once __DIR__ . '/_bootstrap.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
if (!$action) {
    // permite /api/mobile/<accion> si hay rewrite
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $path);
    $i = array_search('mobile', $segments);
    if ($i !== false && isset($segments[$i + 1])) {
        $action = implode('/', array_slice($segments, $i + 1));
    }
}

switch ($action) {
    case 'login':            require __DIR__ . '/auth.php'; auth_login($app); break;
    case 'refresh':          require __DIR__ . '/auth.php'; auth_refresh($app); break;
    case 'cambiar-password': require __DIR__ . '/auth.php'; auth_cambiar_password($app); break;

    case 'me':               require __DIR__ . '/me.php'; me_handler($app); break;
    case 'ficha':            require __DIR__ . '/ficha.php'; ficha_handler($app); break;

    case 'asistencias':      require __DIR__ . '/asistencias.php'; asistencias_list($app); break;
    case 'asistencias/marcar': require __DIR__ . '/asistencias.php'; asistencias_marcar($app); break;

    case 'vacaciones':       require __DIR__ . '/vacaciones.php'; vacaciones_list($app); break;
    case 'vacaciones/solicitar': require __DIR__ . '/vacaciones.php'; vacaciones_solicitar($app); break;

    case 'documentos':       require __DIR__ . '/documentos.php'; documentos_list($app); break;
    case 'documentos/firmar': require __DIR__ . '/documentos.php'; documentos_firmar($app); break;

    case 'empresa-logo':     require __DIR__ . '/extras.php'; empresa_logo_handler($app); break;

    case 'avisos':                  require __DIR__ . '/avisos.php'; avisos_list($app); break;
    case 'avisos/leer':             require __DIR__ . '/avisos.php'; avisos_marcar_leido($app); break;
    case 'avisos/crear':            require __DIR__ . '/avisos.php'; avisos_crear($app); break;

    case 'admin/resumen':           require __DIR__ . '/admin.php'; admin_resumen($app); break;
    case 'admin/vacaciones':        require __DIR__ . '/admin.php'; admin_vacaciones_list($app); break;
    case 'admin/vacaciones/resolver': require __DIR__ . '/admin.php'; admin_vacaciones_resolver($app); break;

    case 'push/register':           require __DIR__ . '/push.php'; push_register($app); break;

    case null:
    case '':
        api_ok(['name' => 'AllnovuWorker Mobile API', 'version' => '1.0.0']);

    default:
        api_fail("Acción no encontrada: $action", 404);
}
