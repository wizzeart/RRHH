<?php
/**
 * Bootstrap para la API móvil AllnovuWorker.
 * - Carga config + clases del proyecto
 * - CORS abierto para la app (Expo dev / build)
 * - Helpers JSON, JWT y respuesta
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M');
set_time_limit(60);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once INCLUDES . '/functions.php';

// Si existe _db_prod.php, la API móvil usa esa BD (solo lectura).
// La web del HRMS sigue intacta usando la BD declarada en includes/config.php.
if (file_exists(__DIR__ . '/_db_prod.php')) {
    require_once __DIR__ . '/_db_prod.php';
}
if (!defined('MOBILE_READONLY')) define('MOBILE_READONLY', false);

if (!defined('MOBILE_JWT_SECRET')) {
    // Cambia este valor en producción (poner en un .env fuera del repo)
    define('MOBILE_JWT_SECRET', 'allnovu-worker-mobile-' . KEYWEB);
}
if (!defined('MOBILE_JWT_TTL'))   define('MOBILE_JWT_TTL',   60 * 60 * 12);       // 12h access token
if (!defined('MOBILE_JWT_RTTL'))  define('MOBILE_JWT_RTTL',  60 * 60 * 24 * 30);  // 30d refresh

init_app();
$app = new App();

// Reemplazamos la conexión del App por la BD móvil (con puerto custom).
// La clase MsSql original NO acepta puerto, así que la extendemos in-line.
if (defined('MOBILE_DB_NAME')) {
    require_once BASE_CLASS . DS . 'MSSql.class.php';

    if (!class_exists('MsSqlPort')) {
        eval('class MsSqlPort extends MsSql {
            public function __construct($host, $port, $db, $user, $pwd) {
                $this->SQL_HOST = $host;
                $this->SQL_USER = $user;
                $this->SQL_PWD = $pwd;
                $this->SQL_DB = $db;
                $this->debug = false;
                $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
                $this->conn = new PDO($dsn, $user, $pwd, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 10,
                ]);
            }
        }');
    }

    try {
        $app->db = new MsSqlPort(MOBILE_DB_HOST, MOBILE_DB_PORT, MOBILE_DB_NAME, MOBILE_DB_USER, MOBILE_DB_PASS);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 0, 'msg' => 'No se pudo conectar a la BD móvil: ' . $e->getMessage()]);
        exit;
    }
}

/* ---------- helpers ---------- */

/**
 * json_encode robusto: si los datos traen bytes no UTF-8 (típico de columnas
 * binarias o BD en latin1), json_encode() devuelve false y `echo false` no
 * imprime nada -> respuesta vacía con HTTP 200. Aquí lo evitamos saneando.
 */
function api_json_encode($data)
{
    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $json = json_encode($data, $flags);
    if ($json === false) {
        // Sustituye secuencias UTF-8 inválidas por U+FFFD en lugar de fallar.
        $json = json_encode($data, $flags | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
    if ($json === false) {
        $json = json_encode(['status' => 0, 'msg' => 'Error serializando la respuesta (' . json_last_error_msg() . ').']);
    }
    return $json;
}

function api_ok($payload = [])
{
    echo api_json_encode(array_merge(['status' => 1], $payload));
    exit;
}

function api_fail($msg, $code = 400, $extra = [])
{
    http_response_code($code);
    echo api_json_encode(array_merge(['status' => 0, 'msg' => $msg], $extra));
    exit;
}

function require_writable()
{
    if (MOBILE_READONLY) {
        api_fail('La API móvil está en modo SOLO LECTURA. Esta acción está deshabilitada.', 403);
    }
}

function api_input()
{
    $raw = file_get_contents('php://input');
    $body = [];
    if ($raw) {
        $j = json_decode($raw, true);
        if (is_array($j)) $body = $j;
    }
    return array_merge($_GET, $_POST, $body);
}

/* ---------- JWT HS256 (sin dependencias) ---------- */

function jwt_b64url_encode($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
function jwt_b64url_decode($data) { return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4)); }

function jwt_sign(array $claims, $ttl = null)
{
    $ttl = $ttl ?: MOBILE_JWT_TTL;
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $now = time();
    $claims = array_merge(['iat' => $now, 'exp' => $now + $ttl], $claims);
    $h = jwt_b64url_encode(json_encode($header));
    $p = jwt_b64url_encode(json_encode($claims));
    $sig = hash_hmac('sha256', "$h.$p", MOBILE_JWT_SECRET, true);
    return "$h.$p." . jwt_b64url_encode($sig);
}

function jwt_verify($token)
{
    if (!$token) return null;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    list($h, $p, $s) = $parts;
    $expected = jwt_b64url_encode(hash_hmac('sha256', "$h.$p", MOBILE_JWT_SECRET, true));
    if (!hash_equals($expected, $s)) return null;
    $claims = json_decode(jwt_b64url_decode($p), true);
    if (!is_array($claims)) return null;
    if (isset($claims['exp']) && $claims['exp'] < time()) return null;
    return $claims;
}

function bearer_token()
{
    $h = '';
    if (function_exists('getallheaders')) {
        $all = getallheaders();
        foreach ($all as $k => $v) {
            if (strtolower($k) === 'authorization') { $h = $v; break; }
        }
    }
    if (!$h && isset($_SERVER['HTTP_AUTHORIZATION'])) $h = $_SERVER['HTTP_AUTHORIZATION'];
    if (!$h && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) $h = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    if (stripos($h, 'Bearer ') === 0) return trim(substr($h, 7));
    return null;
}

/** Rol de administrador en el HRMS. */
if (!defined('MOBILE_ADMIN_ROL')) define('MOBILE_ADMIN_ROL', 1);

/**
 * Requiere usuario autenticado por JWT. NO exige ficha de trabajador:
 * los administradores (rol 1) no tienen trabajador asociado. En ese caso
 * devuelve trabajador_id = 0, empresa_id = 0 y worker = null.
 */
function require_user_session(App $app)
{
    $claims = jwt_verify(bearer_token());
    if (!$claims || empty($claims['uid'])) {
        api_fail('Token inválido o expirado. Vuelve a iniciar sesión.', 401);
    }

    // Trabajador vinculado (si existe). Misma consulta de siempre.
    $row = $app->db->fetchRow(
        "SELECT t.*, u.xemail AS email, u.xusuario AS username, u.xrol_id AS rol_id
         FROM trabajadores t
         INNER JOIN usuarios u ON u.xusuario_id = t.usuario_id
         WHERE u.xusuario_id = :uid AND u.xeliminado = 0",
        ['uid' => $claims['uid']]
    );

    if ($row) {
        $rolId    = (int)$row['rol_id'];
        $email    = $row['email'];
        $username = $row['username'];
        $trabId   = (int)$row['id'];
        $empresa  = isset($row['empresa_id']) ? (int)$row['empresa_id'] : 0;
        $worker   = $row;
    } else {
        // Sin ficha: cargamos el usuario directamente (caso administrador).
        $u = $app->db->fetchRow(
            "SELECT xusuario_id, xusuario AS username, xemail AS email, xrol_id AS rol_id
             FROM usuarios
             WHERE xusuario_id = :uid AND xeliminado = 0 AND xactivo = 'S'",
            ['uid' => $claims['uid']]
        );
        if (!$u) {
            api_fail('Usuario no encontrado o inactivo. Vuelve a iniciar sesión.', 401);
        }
        $rolId    = (int)$u['rol_id'];
        $email    = $u['email'];
        $username = $u['username'];
        $trabId   = 0;
        $empresa  = 0;
        $worker   = null;
    }

    $app->user_id    = $claims['uid'];
    $app->rol        = isset($claims['rol']) ? (int)$claims['rol'] : $rolId;
    $app->empresa_id = $empresa;
    if ($app->empresa_id > 0) $_SESSION['empresa_id'] = $app->empresa_id;

    return [
        'user_id'       => (int)$claims['uid'],
        'trabajador_id' => $trabId,
        'empresa_id'    => $empresa,
        'rol_id'        => $rolId,
        'email'         => $email,
        'username'      => $username,
        'worker'        => $worker,
    ];
}

/**
 * Requiere usuario autenticado CON ficha de trabajador. Mantiene el contrato
 * anterior (rechaza 403 si no hay trabajador vinculado).
 */
function require_worker_session(App $app)
{
    $sess = require_user_session($app);
    if ($sess['trabajador_id'] <= 0) {
        api_fail('No se ha encontrado el trabajador vinculado a tu cuenta.', 403);
    }
    return $sess;
}

/**
 * Requiere que el usuario autenticado sea ADMINISTRADOR (rol 1).
 * El admin del móvil opera a nivel GLOBAL (todas las empresas): empresa_id = 0.
 */
function require_admin(App $app)
{
    $sess = require_user_session($app);
    if ((int)$sess['rol_id'] !== MOBILE_ADMIN_ROL) {
        api_fail('Solo los administradores pueden realizar esta acción.', 403);
    }
    $sess['empresa_id'] = 0;
    $app->empresa_id    = 0;
    return $sess;
}
