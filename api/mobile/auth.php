<?php
/**
 * Auth: login (JWT access + refresh) y refresh.
 * Reusa la misma comprobación de password que api-app.php (KEYWEB + password_verify).
 */

function auth_login(App $app)
{
    $in = api_input();
    $email = isset($in['email']) ? trim(strtolower($in['email'])) : '';
    $pwd   = isset($in['password']) ? $in['password'] : '';

    if ($email === '' || $pwd === '') {
        api_fail('Email y contraseña son obligatorios.', 422);
    }

    $sql = "SELECT u.xusuario_id, u.xusuario, u.xemail, u.xpwd, u.xrol_id, u.xactivo, u.xeliminado
            FROM usuarios u
            WHERE LOWER(u.xemail) = :email AND u.xeliminado = 0";
    $row = $app->db->fetchRow($sql, ['email' => $email]);

    if (!$row || $row['xactivo'] !== 'S') {
        api_fail('Credenciales inválidas.', 401);
    }

    if (!password_verify(KEYWEB . $pwd, $row['xpwd'])) {
        api_fail('Credenciales inválidas.', 401);
    }

    // Trabajador asociado (los administradores -rol 1- no tienen ficha y
    // entran igualmente; el resto de roles SÍ requieren ficha de trabajador).
    $tr = $app->db->fetchRow(
        "SELECT t.id, t.nombre, t.apellidos, t.apellidos_segundos, t.empresa_id
         FROM trabajadores t
         WHERE t.usuario_id = :uid AND t.trabajador_eliminado = 0",
        ['uid' => $row['xusuario_id']]
    );
    if (!$tr && (int)$row['xrol_id'] !== MOBILE_ADMIN_ROL) {
        api_fail('Este usuario no tiene una ficha de trabajador asociada.', 403);
    }

    $access  = jwt_sign(['uid' => (int)$row['xusuario_id'], 'rol' => (int)$row['xrol_id'], 'typ' => 'access'], MOBILE_JWT_TTL);
    $refresh = jwt_sign(['uid' => (int)$row['xusuario_id'], 'typ' => 'refresh'], MOBILE_JWT_RTTL);

    // Actualizar fecha último acceso (omitido en modo solo-lectura)
    if (!MOBILE_READONLY) {
        try {
            $app->db->update('usuarios', ['xult_acceso' => date(dateSQL)], ['xusuario_id' => $row['xusuario_id']]);
        } catch (Exception $e) { /* ignore */ }
    }

    api_ok([
        'access_token'  => $access,
        'refresh_token' => $refresh,
        'expires_in'    => MOBILE_JWT_TTL,
        'token_type'    => 'Bearer',
        'user' => [
            'id'       => (int)$row['xusuario_id'],
            'email'    => $row['xemail'],
            'username' => $row['xusuario'],
            'rol_id'   => (int)$row['xrol_id'],
        ],
        'trabajador' => $tr ? [
            'id'                 => (int)$tr['id'],
            'nombre'             => $tr['nombre'],
            'apellidos'          => $tr['apellidos'],
            'apellidos_segundos' => $tr['apellidos_segundos'],
            'empresa_id'         => isset($tr['empresa_id']) ? (int)$tr['empresa_id'] : null,
        ] : null,
    ]);
}

function auth_refresh(App $app)
{
    $in = api_input();
    $token = isset($in['refresh_token']) ? $in['refresh_token'] : '';
    $claims = jwt_verify($token);
    if (!$claims || empty($claims['uid']) || ($claims['typ'] ?? '') !== 'refresh') {
        api_fail('Refresh token inválido.', 401);
    }
    $row = $app->db->fetchRow(
        "SELECT xusuario_id, xrol_id FROM usuarios WHERE xusuario_id = :uid AND xeliminado = 0 AND xactivo = 'S'",
        ['uid' => $claims['uid']]
    );
    if (!$row) api_fail('Usuario no encontrado.', 401);

    $access = jwt_sign(['uid' => (int)$row['xusuario_id'], 'rol' => (int)$row['xrol_id'], 'typ' => 'access'], MOBILE_JWT_TTL);
    api_ok(['access_token' => $access, 'expires_in' => MOBILE_JWT_TTL, 'token_type' => 'Bearer']);
}

/**
 * POST /api/mobile/cambiar-password  {password_actual, password_nueva}
 * Cambia la contraseña del usuario autenticado (mismo esquema KEYWEB + password_hash).
 */
function auth_cambiar_password(App $app)
{
    require_writable();
    $sess = require_user_session($app);
    $in   = api_input();

    $actual = isset($in['password_actual']) ? (string)$in['password_actual'] : '';
    $nueva  = isset($in['password_nueva'])  ? (string)$in['password_nueva']  : '';

    if ($actual === '' || $nueva === '') {
        api_fail('Debes indicar la contraseña actual y la nueva.', 422);
    }
    if (strlen($nueva) < 6) {
        api_fail('La nueva contraseña debe tener al menos 6 caracteres.', 422);
    }
    if ($nueva === $actual) {
        api_fail('La nueva contraseña no puede ser igual a la actual.', 422);
    }

    $u = $app->db->fetchRow(
        "SELECT xusuario_id, xpwd FROM usuarios WHERE xusuario_id = :uid AND xeliminado = 0",
        ['uid' => $sess['user_id']]
    );
    if (!$u) api_fail('Usuario no encontrado.', 404);

    if (!password_verify(KEYWEB . $actual, $u['xpwd'])) {
        api_fail('La contraseña actual no es correcta.', 401);
    }

    try {
        $app->db->update(
            'usuarios',
            ['xpwd' => password_hash(KEYWEB . $nueva, PASSWORD_DEFAULT)],
            ['xusuario_id' => $u['xusuario_id']]
        );
    } catch (Exception $e) {
        api_fail('No se pudo actualizar la contraseña: ' . $e->getMessage(), 500);
    }

    api_ok(['msg' => 'Contraseña actualizada correctamente.']);
}
