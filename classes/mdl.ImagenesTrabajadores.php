<?php

class ImagenesTrabajadores {
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
        // Habilitar visualización de errores para depuración
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        if (!isset($param['id'])) {
            $this->sendError('ID de trabajador no especificado');
            return;
        }

        $id = intval($param['id']);
        
        // Obtener la ruta de la imagen desde la base de datos
        $sql = "SELECT foto FROM trabajadores WHERE id = :id";
        $result = $this->db->fetchRow($sql, ['id' => $id]);
        
        // Debug: Mostrar información de la consulta
        error_log("Consulta a la base de datos para ID $id. Resultado: " . print_r($result, true));

        if (!$result || empty($result['foto'])) {
            error_log("No se encontró foto para el trabajador ID: $id");
            $this->sendDefaultImage();
            return;
        }

        // Obtener la ruta de la imagen
        $fotoPath = $result['foto'];
        error_log("Ruta de la imagen desde BD: $fotoPath");
        
        // Si la ruta es relativa, construir la ruta completa
        if (strpos($fotoPath, '/') !== 0 && strpos($fotoPath, 'http') !== 0) {
            // Si la ruta ya incluye 'uploads/trabajadores/', usarla directamente
            if (strpos($fotoPath, 'uploads/trabajadores/') === 0) {
                $imagePath = __DIR__ . '/../' . $fotoPath;
                error_log("Ruta 1 (con 'uploads/trabajadores/'): $imagePath");
            } else {
                // Si solo es el nombre del archivo, usar la ruta base
                $imagePath = $this->basePath . basename($fotoPath);
                error_log("Ruta 2 (solo nombre de archivo): $imagePath");
            }
        } else {
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
            header('HTTP/1.0 404 Not Found');
            echo 'Imagen no encontrada';
        }
        exit;
    }

    private function sendError($message) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit;
    }
}
