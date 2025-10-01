<?php

class ImagenesTrabajadores {
    // Configuración CORS
    private $allowedOrigins = [
        'http://tudominio.com',
        'https://tudominio.com',
        'http://www.tudominio.com',
        'http://192.168.8.7/'
    ];
    
    private function setCorsHeaders() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        // Normalize origin (strip trailing slash) to match entries like 'http://192.168.8.7' or with slash
        $originNorm = rtrim($origin, '/');
        $allowed = false;
        foreach ($this->allowedOrigins as $ao) {
            if (rtrim($ao, '/') === $originNorm && $originNorm !== '') { $allowed = true; break; }
        }
        // Verificar si el origen está permitido
        if ($allowed) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // Si prefieres bloquear orígenes no autorizados, reemplaza '*' por nada y responde 403.
            header('Access-Control-Allow-Origin: *'); // mantener compatibilidad por ahora
        }
        
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        header('Access-Control-Max-Age: 86400'); // 24 horas de caché para preflight
        
        // Manejar solicitud OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    // Usar la misma clave que para el login
    private function validarToken($token) {
        // Comparación segura de cadenas para evitar ataques de timing
        return hash_equals(KEYWEB, $token);
    }
    
    private function enviarErrorAutenticacion() {
        header('HTTP/1.0 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Token de autenticación inválido o no proporcionado'
        ]);
        exit;
    }
    private $app;
    private $db;
    private $basePath;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        // Ruta base donde se almacenan las fotos de los trabajadores
        $this->basePath = __DIR__ . '/../uploads/trabajadores/';
        
        // Crear el directorio si no existe
        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    public function api($param) {
        // Configurar CORS
        $this->setCorsHeaders();
        
        // Verificar token de autenticación
        $token = '';
        
        // Buscar el token en los headers o en los parámetros
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                $token = substr($authHeader, 7);
            }
        } elseif (isset($_GET['token'])) {
            $token = $_GET['token'];
        }
        
        // Validar el token
        if (!$this->validarToken($token)) {
            $this->enviarErrorAutenticacion();
            return;
        }
        if (!isset($param['method'])) {
            $this->sendError('Método no especificado');
            return;
        }

        switch ($param['method']) {
            case 'get-image':
                $this->getImage($param);
                break;
            default:
                $this->sendError('Método no válido');
        }
    }

  private function getImage($param) {
    // Validar ID del trabajador
    if (!isset($param['id']) || !is_numeric($param['id'])) {
        $this->sendError('ID de trabajador no válido o no especificado', 400);
        return;
    }

    $id = intval($param['id']);
    // Obtener la ruta de la imagen desde la base de datos
    $sql = "SELECT id, foto, CONCAT(nombre, ' ', apellidos) as nombre_completo FROM trabajadores WHERE id = :id";
    $result = $this->db->fetchRow($sql, ['id' => $id]);

    if (!$result) {
        $this->sendError('Trabajador no encontrado', 404);
        return;
    }

    // Si no hay foto, devolver la imagen por defecto
    if (empty($result['foto'])) {
        $this->sendDefaultImage();
        return;
    }

    $fotoPath = trim($result['foto']);

    // Si la ruta es una URL completa, redirigir a ella
    if (filter_var($fotoPath, FILTER_VALIDATE_URL)) {
        header('Location: ' . $fotoPath);
        exit;
    }

    // Normalizar y construir ruta segura en servidor
    // Eliminar barras iniciales para unir rutas de manera consistente
    $fotoPathNoLead = ltrim($fotoPath, '/\\');

    // Si la ruta contiene uploads/trabajadores/, usarla relativa al proyecto
    if (strpos($fotoPathNoLead, 'uploads/trabajadores/') === 0) {
        $candidate = __DIR__ . '/../' . $fotoPathNoLead;
    } else {
        // Si parece solo un nombre de archivo, usar la carpeta base
        $candidate = $this->basePath . basename($fotoPathNoLead);
    }

    // Resolver realpath para evitar path traversal
    $realCandidate = realpath($candidate);
    $realBase = realpath($this->basePath);

    if ($realCandidate === false || $realBase === false) {
        error_log("Imagen no encontrada (realpath fallo): $candidate");
        $this->sendDefaultImage();
        return;
    }

    // Asegurarnos que el archivo esté dentro del directorio permitido
    if (strpos($realCandidate, $realBase) !== 0) {
        error_log("Intento de acceso fuera del directorio permitido: $realCandidate");
        $this->sendDefaultImage();
        return;
    }

    if (!is_file($realCandidate) || !file_exists($realCandidate)) {
        error_log("Archivo no encontrado: $realCandidate");
        $this->sendDefaultImage();
        return;
    }

    // Obtener tipo MIME de forma segura
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $realCandidate);
    finfo_close($finfo);

    // Enviar la imagen con cabeceras apropiadas
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($realCandidate));
    header('Content-Disposition: inline; filename="' . basename($realCandidate) . '"');
    header('Cache-Control: max-age=604800, public'); // Cache por 1 semana
    readfile($realCandidate);
    exit;
}

    private function sendDefaultImage() {
        $defaultImage = __DIR__ . '/../img/default-user.png';
        
        if (file_exists($defaultImage)) {
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($defaultImage));
            readfile($defaultImage);
        } else {
            // Si no hay imagen por defecto, enviar un error 404
            echo 'Imagen no encontrada';
        }
        exit;
    }

    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
    
    private function sendSuccess($data = null, $message = 'Success') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
}
