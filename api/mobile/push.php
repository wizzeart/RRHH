<?php
/**
 * Push notifications (almacén JSON de tokens, sin BD).
 *   POST /api/mobile/push/register  {token, plataforma}
 *
 * El envío real (push_broadcast) usa la API de Expo Push. En FASE 1 queda
 * preparado pero no surtirá efecto hasta que la app corra en un development
 * build (Expo Go en Android no entrega push remoto) y registre tokens.
 */

require_once __DIR__ . '/_store.php';

function push_register(App $app)
{
    require_writable();
    $sess = require_user_session($app);
    $in   = api_input();

    $token = isset($in['token']) ? trim((string)$in['token']) : '';
    $plat  = isset($in['plataforma']) ? trim((string)$in['plataforma']) : 'unknown';
    if ($token === '') api_fail('Falta el token de push.', 422);

    $rows = store_read('push_tokens');
    $now = date('Y-m-d H:i:s');
    $found = false;
    foreach ($rows as &$r) {
        if (($r['token'] ?? '') === $token) {
            $r['usuario_id'] = (int)$sess['user_id'];
            $r['empresa_id'] = (int)$sess['empresa_id'];
            $r['plataforma'] = $plat;
            $r['updated_at'] = $now;
            $found = true;
            break;
        }
    }
    unset($r);
    if (!$found) {
        $rows[] = [
            'token'      => $token,
            'usuario_id' => (int)$sess['user_id'],
            'empresa_id' => (int)$sess['empresa_id'],
            'plataforma' => $plat,
            'updated_at' => $now,
        ];
    }
    store_write('push_tokens', $rows);
    api_ok(['msg' => 'Token registrado']);
}

/**
 * Carga las credenciales del service account FCM (archivo PHP protegido,
 * solo en el servidor). Devuelve null si no está configurado -> push no-op.
 */
function _fcm_credentials()
{
    static $cred = false;
    if ($cred !== false) return $cred;
    $path = __DIR__ . '/_fcm_credentials.php';
    $cred = is_file($path) ? require $path : null;
    if (!is_array($cred) || empty($cred['private_key']) || empty($cred['client_email']) || empty($cred['project_id'])) {
        $cred = null;
    }
    return $cred;
}

/**
 * Access token OAuth2 para FCM v1, firmando un JWT (RS256) con la clave del
 * service account. Se cachea en disco hasta ~1 min antes de expirar.
 */
function _fcm_access_token(array $cred)
{
    $cacheFile = sys_get_temp_dir() . '/fcm_token_' . md5($cred['client_email']) . '.json';
    if (is_file($cacheFile)) {
        $c = json_decode((string)@file_get_contents($cacheFile), true);
        if (is_array($c) && isset($c['access_token'], $c['exp']) && $c['exp'] > time() + 60) {
            return $c['access_token'];
        }
    }

    $now = time();
    $aud = isset($cred['token_uri']) ? $cred['token_uri'] : 'https://oauth2.googleapis.com/token';
    $b64 = function ($d) { return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); };
    $segments = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' . $b64(json_encode([
        'iss'   => $cred['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => $aud,
        'iat'   => $now,
        'exp'   => $now + 3600,
    ]));
    $sig = '';
    if (!openssl_sign($segments, $sig, $cred['private_key'], OPENSSL_ALGO_SHA256)) {
        return null;
    }
    $assertion = $segments . '.' . $b64($sig);

    $ch = curl_init($aud);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $assertion,
        ]),
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    $tok = json_decode((string)$resp, true);
    if (!is_array($tok) || empty($tok['access_token'])) {
        return null;
    }
    @file_put_contents($cacheFile, json_encode([
        'access_token' => $tok['access_token'],
        'exp'          => $now + (int)($tok['expires_in'] ?? 3600),
    ]));
    return $tok['access_token'];
}

/**
 * Envía un push a los tokens de una empresa (o a todos si $empresaId es null)
 * vía FCM HTTP v1. Best-effort: cualquier fallo se ignora.
 */
function push_broadcast($empresaId, $title, $body, array $data = [])
{
    $cred = _fcm_credentials();
    if (!$cred) return; // FCM no configurado -> no-op
    $rows = store_read('push_tokens');
    if (empty($rows)) return;

    $tokens = [];
    foreach ($rows as $r) {
        $tok = trim((string)($r['token'] ?? ''));
        if ($tok === '') continue;
        if ($empresaId !== null && isset($r['empresa_id']) && (int)$r['empresa_id'] !== (int)$empresaId) {
            continue; // aviso de empresa -> solo esa empresa
        }
        $tokens[$tok] = true; // dedup
    }
    if (empty($tokens)) return;

    $accessToken = _fcm_access_token($cred);
    if (!$accessToken) return;

    // FCM v1 exige que los valores de 'data' sean strings.
    $dataStr = [];
    foreach ($data as $k => $v) {
        $dataStr[(string)$k] = is_scalar($v) ? (string)$v : json_encode($v);
    }

    $url = 'https://fcm.googleapis.com/v1/projects/' . rawurlencode($cred['project_id']) . '/messages:send';
    $headers = ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'];

    foreach (array_keys($tokens) as $tok) {
        $message = ['message' => [
            'token'        => $tok,
            'notification' => ['title' => $title, 'body' => mb_substr($body, 0, 1000)],
            'data'         => $dataStr,
            'android'      => [
                'priority'     => 'HIGH',
                'notification' => [
                    'channel_id'              => 'default',
                    'sound'                   => 'default',
                    'default_vibrate_timings' => true,
                ],
            ],
        ]];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($message),
        ]);
        @curl_exec($ch);
        curl_close($ch);
    }
}
