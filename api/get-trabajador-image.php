<?php
// Configuración
include(__DIR__ . DIRECTORY_SEPARATOR . '../includes/config.php');

// Obtener el cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);

// Validar API Key
$apiKey = '';
if (isset($input['api_key'])) {
  $apiKey = $input['api_key'];
} elseif (isset($_SERVER['HTTP_X_API_KEY'])) {
  $apiKey = $_SERVER['HTTP_X_API_KEY'];
}

if ($apiKey !== API_KEY) {
  http_response_code(401);
  header('Content-Type: application/json');
  die(json_encode([
    'status' => 'error',
    'code' => 401,
    'message' => 'API Key inválida o no proporcionada'
  ]));
}

// Obtener el UUID del trabajador desde la URL
$uuid = $_GET['uuid'] ?? '';

// Validar que se proporcionó un UUID
if (empty($uuid)) {
  http_response_code(400);
  header('Content-Type: application/json');
  die(json_encode([
    'status' => 'error',
    'code' => 400,
    'message' => 'UUID de trabajador requerido',
  ]));
}

// Buscar la foto con diferentes extensiones
$extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$fotoEncontrada = false;
$rutaFoto = '';

foreach ($extensiones as $ext) {
  $rutaActual = FOTOS_TRAB_DIR . $uuid . '.' . $ext;
  if (file_exists($rutaActual)) {
    $fotoEncontrada = true;
    $rutaFoto = $rutaActual;

    // Determinar el tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $rutaFoto);
    finfo_close($finfo);

    // Enviar la imagen
    header('Content-Type: ' . $mimeType);
    readfile($rutaFoto);
    exit;
  }
}

// Si no se encontró la foto
http_response_code(404);
header('Content-Type: application/json');
die(json_encode([
  'status' => 'error',
  'code' => 404,
  'message' => 'No se encontró la imagen del trabajador',
  'uuid_solicitado' => $uuid
]));
