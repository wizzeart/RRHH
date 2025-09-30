<?php

class ImagenesTrabajadores {
    // Configuración CORS
    private $allowedOrigins = [
        'http://tudominio.com',
        'https://tudominio.com',
        'http://www.tudominio.com',
        'https://www.tudominio.com'
    ];
    
    private function setCorsHeaders() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // Verificar si el origen está permitido
        if (in_array($origin, $this->allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header('Access-Control-Allow-Origin: *'); // O puedes bloquear peticiones no autorizadas
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
        
        try {
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

            // Obtener la ruta de la imagen
            $fotoPath = $result['foto'];
            
            // Si la ruta es una URL completa, redirigir a ella
            if (filter_var($fotoPath, FILTER_VALIDATE_URL)) {
                header('Location: ' . $fotoPath);
                exit;
            }
            
            // Si la ruta es relativa, construir la ruta completa
            // Si la ruta ya incluye 'uploads/trabajadores/', usarla directamente
            if (strpos($fotoPath, 'uploads/trabajadores/') === 0) {
                $imagePath = __DIR__ . '/../' . $fotoPath;
                error_log("Ruta 1 (con 'uploads/trabajadores/'): $imagePath");
            } else {
                // Si solo es el nombre del archivo, usar la ruta base
                $imagePath = $this->basePath . basename($fotoPath);
                error_log("Ruta 2 (solo nombre de archivo): $imagePath");
            }

            // Verificar si el archivo existe
            if (!file_exists($imagePath)) {
                error_log("Archivo no encontrado: $imagePath");
                $this->sendDefaultImage();
                return;
            }

            // Obtener información del archivo
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $imagePath);
            finfo_close($fileInfo);

            // Enviar la imagen con las cabeceras adecuadas
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($imagePath));
            header('Content-Disposition: inline; filename="' . basename($imagePath) . '"');
            header('Cache-Control: max-age=86400, public'); // 24 horas de caché
            readfile($imagePath);
            exit;
            $imagePath = $fotoPath;
            error_log("Ruta 3 (ruta absoluta o URL): $imagePath");
        }

        // Verificar si el archivo existe
        if (!file_exists($imagePath)) {
            error_log("Imagen no encontrada en: " . $imagePath);
            $this->sendDefaultImage();
            return;
        }

        // Obtener la extensión del archivo
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        // Determinar el tipo MIME
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp'
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Enviar la imagen
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($imagePath));
        header('Cache-Control: max-age=604800, public'); // Cache por 1 semana
        readfile($imagePath);
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
