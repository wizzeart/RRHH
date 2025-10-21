<?php

class ImagenesTrabajadores {
    private $app;
    private $db;
    private $basePath;
    private $allowedOrigins = [
        'http://tudominio.com',
        'https://tudominio.com',
        'http://www.tudominio.com',
        'http://192.168.8.7/',
        'http://127.0.0.1',
        'http://localhost'
    ];

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $this->basePath = __DIR__ . '/../uploads/trabajadores/';
        
        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
        
        error_log("ImagenesTrabajadores::construct - DB connection: " . ($this->db ? 'OK' : 'FAILED'));
    }

    public function api($param) {
        try {
            error_log("ImagenesTrabajadores::api - Request params: " . json_encode($param));
            
            // Configurar CORS primero
            $this->setCorsHeaders();

            if (!isset($param['method'])) {
                throw new Exception('Método no especificado');
            }

            switch ($param['method']) {
                case 'get-image':
                    $this->getImage($param);
                    break;
                default:
                    throw new Exception('Método no válido');
            }
        } catch (Exception $e) {
            error_log("Error en ImagenesTrabajadores::api - " . $e->getMessage());
            $this->sendError($e->getMessage());
        }
    }

private function getImage($param) {
        try {
            if (!isset($param['id']) || !is_numeric($param['id'])) {
                throw new Exception('ID de trabajador no válido o no especificado');
            }

            $id = intval($param['id']);
            $sql = "SELECT id, foto, LENGTH(foto) as foto_length FROM trabajadores WHERE id = :id";
            
            error_log("ImagenesTrabajadores::getImage - Ejecutando query para ID: " . $id);
            $result = $this->db->fetchRow($sql, ['id' => $id]);
            
            if (!$result) {
                error_log("ImagenesTrabajadores::getImage - No se encontró el trabajador ID: " . $id);
                throw new Exception('Trabajador no encontrado');
            }

            error_log("ImagenesTrabajadores::getImage - Datos encontrados: " . json_encode([
                'id' => $result['id'],
                'foto_length' => $result['foto_length'],
                'tiene_foto' => !empty($result['foto'])
            ]));

            if (empty($result['foto'])) {
                error_log("ImagenesTrabajadores::getImage - Sin foto, usando default");
                $this->sendDefaultImage();
                return;
            }

            $foto = $result['foto'];
            
            // Convertir recurso o objeto a string si es necesario
            if (is_resource($foto)) {
                $foto = stream_get_contents($foto);
            } elseif (is_object($foto) && method_exists($foto, 'getContents')) {
                $foto = $foto->getContents();
            }

            // Si parece ser una ruta, tratar como archivo
            if (preg_match('/^[\/\\\\a-zA-Z0-9._-]+$/', trim($foto))) {
                $this->sendImageFile($foto);
                return;
            }

            // Detectar MIME type del contenido binario
            $mime = 'image/jpeg'; // Default MIME type
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected = finfo_buffer($finfo, $foto);
                if ($detected) {
                    $mime = $detected;
                }
                finfo_close($finfo);
            } else {
                $info = @getimagesizefromstring($foto);
                if ($info && isset($info['mime'])) {
                    $mime = $info['mime'];
                }
            }

            // Limpiar cualquier output anterior
            while (ob_get_level()) ob_end_clean();

            error_log("ImagenesTrabajadores::getImage - Enviando imagen: mime=" . $mime . ", length=" . strlen($foto));
            
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . strlen($foto));
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            header('Cache-Control: max-age=604800, public');
            
            // Enviar contenido binario
            echo $foto;
            exit;

        } catch (Exception $e) {
            error_log("Error en ImagenesTrabajadores::getImage - " . $e->getMessage());
            $this->sendError($e->getMessage());
        }
    }

    private function sendImageFile($path) {
        $basePath = __DIR__ . '/../uploads/trabajadores/';
        $pathNoLead = ltrim($path, '/\\');
        
        if (strpos($pathNoLead, 'uploads/trabajadores/') === 0) {
            $filePath = __DIR__ . '/../' . $pathNoLead;
        } else {
            $filePath = $basePath . basename($pathNoLead);
        }

        if (!file_exists($filePath) || !is_file($filePath)) {
            error_log("ImagenesTrabajadores::sendImageFile - Archivo no encontrado: " . $filePath);
            $this->sendDefaultImage();
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=604800, public');
        readfile($filePath);
        exit;
    }

    private function sendDefaultImage() {
        $defaultImage = __DIR__ . '/../images/default-user.png';
        if (!file_exists($defaultImage)) {
            $defaultImage = __DIR__ . '/../img/default-user.png';
        }
        
        if (file_exists($defaultImage)) {
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($defaultImage));
            readfile($defaultImage);
        } else {
            error_log("ImagenesTrabajadores::sendDefaultImage - Imagen por defecto no encontrada");
            $this->sendError('Imagen no encontrada', 404);
        }
        exit;
    }

    private function setCorsHeaders() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $originNorm = rtrim($origin, '/');
        
        $allowed = false;
        foreach ($this->allowedOrigins as $ao) {
            if (rtrim($ao, '/') === $originNorm && $originNorm !== '') {
                $allowed = true;
                break;
            }
        }

        if ($allowed) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header('Access-Control-Allow-Origin: *');
        }
        
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    private function sendError($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message
        ]);
        exit;
    }
}
