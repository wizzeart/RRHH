<?php

class DatabaseImportExport {
    private $app;
    private $db;
    
    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }
    
    public function controlador($request) {
        $action = isset($request['action']) ? $request['action'] : 'list';
        
        switch ($action) {
            case 'export':
                $this->exportDatabase();
                break;
            case 'import':
                $this->importDatabase();
                break;
            case 'list':
            default:
                $this->showImportExportPage();
                break;
        }
    }
    
    private function showImportExportPage() {
        // Establecer variables en el scope global para que la vista pueda acceder
        $GLOBALS['page']['title'] = 'Importar/Exportar Base de Datos';
        $GLOBALS['page']['subtitle'] = 'Herramientas para importar y exportar la base de datos';
        
        // Obtener información de la base de datos y hacerla disponible globalmente
        $GLOBALS['db_info'] = $this->getDatabaseInfo();
        
        // El sistema incluirá automáticamente la vista desde index.php
        // No necesitamos hacer include manualmente
    }
    
    private function exportDatabase() {
        $this->handleDatabaseOperation('export');
    }
    
    private function importDatabase() {
        $this->handleDatabaseOperation('import');
    }
    
    private function handleDatabaseOperation($operation) {
        try {
            // Validación específica para importación
            if ($operation === 'import') {
                $this->validateImportFile();
            }
            
            // Obtener configuración común
            $db_config = $this->getDbConfig();
            
            // Preparar operación específica
            $operation_data = $this->prepareOperation($operation, $db_config);
            
            // Ejecutar comando MySQL
            $this->executeMysqlCommand($operation_data['command_args'], $operation);
            
            // Manejar post-procesamiento específico
            $this->handlePostOperation($operation, $operation_data);
            
            // Mensaje de éxito para importación
            if ($operation === 'import') {
                $_SESSION['success'] = 'Base de datos importada correctamente';
            }
            
        } catch (Exception $e) {
            $this->handleOperationError($operation, $e);
        }
        
        // Redirección para importación
        if ($operation === 'import') {
            header('Location: index.php?module=database-import-export');
            exit;
        }
    }
    
    private function validateImportFile() {
        if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] != UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo SQL');
        }
        
        $file_name = $_FILES['sql_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext != 'sql') {
            throw new Exception('El archivo debe ser un archivo .sql');
        }
    }
    
    private function prepareOperation($operation, $db_config) {
        $data = array();
        
        // Preparar argumentos comunes del comando
        $common_args = array(
            'host' => $db_config['host'],
            'username' => $db_config['username'],
            'password' => $db_config['password'],
            'database' => $db_config['database']
        );
        
        if ($operation === 'export') {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = BASE . '/exports/' . $filename;
            
            // Crear directorio si no existe
            if (!is_dir(BASE . '/exports')) {
                mkdir(BASE . '/exports', 0755, true);
            }
            
            $data['command_args'] = array_merge($common_args, array(
                'tool' => 'mysqldump',
                'output_file' => $filepath,
                'additional_options' => '--single-transaction --routines --triggers'
            ));
            
            $data['filepath'] = $filepath;
            $data['filename'] = $filename;
            
        } elseif ($operation === 'import') {
            $file_tmp = $_FILES['sql_file']['tmp_name'];
            
            $data['command_args'] = array_merge($common_args, array(
                'tool' => 'mysql',
                'input_file' => $file_tmp,
                'additional_options' => ''
            ));
        }
        
        return $data;
    }
    
    private function handlePostOperation($operation, $operation_data) {
        if ($operation === 'export') {
            // Verificar archivo
            if (!file_exists($operation_data['filepath']) || filesize($operation_data['filepath']) === 0) {
                throw new Exception('No se pudo generar el archivo de backup');
            }
            
            // Descargar archivo
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $operation_data['filename'] . '"');
            header('Content-Length: ' . filesize($operation_data['filepath']));
            readfile($operation_data['filepath']);
            
            // Eliminar archivo temporal
            unlink($operation_data['filepath']);
            exit;
        }
    }
    
    private function handleOperationError($operation, $exception) {
        $operation_name = ($operation === 'export') ? 'exportar' : 'importar';
        $_SESSION['error'] = "Error al $operation_name la base de datos: " . $exception->getMessage();
        
        if ($operation === 'export') {
            header('Location: index.php?module=database-import-export');
            exit;
        }
    }
    
    private function executeMysqlCommand($args, $operation) {
        // Obtener la ruta correcta para la herramienta MySQL
        $tool_path = $this->getMysqlToolPath($args['tool']);
        
        // Construir el comando base
        $command_parts = array(
            sprintf('"%s"', $tool_path),
            sprintf('-h%s', escapeshellarg($args['host'])),
            sprintf('-u%s', escapeshellarg($args['username'])),
            sprintf('-p%s', escapeshellarg($args['password'])),
            escapeshellarg($args['database'])
        );
        
        // Agregar opciones adicionales si existen
        if (!empty($args['additional_options'])) {
            $command_parts[] = $args['additional_options'];
        }
        
        // Agregar redirección de entrada/salida según la operación
        if ($operation === 'export' && isset($args['output_file'])) {
            $command_parts[] = sprintf('> "%s" 2>&1', $args['output_file']);
        } elseif ($operation === 'import' && isset($args['input_file'])) {
            $command_parts[] = sprintf('< "%s" 2>&1', $args['input_file']);
        }
        
        // Unir todas las partes del comando
        $command = implode(' ', $command_parts);
        
        // Ejecutar el comando
        exec($command, $output, $return_code);
        
        // Manejar errores
        if ($return_code !== 0) {
            $operation_name = ($operation === 'export') ? 'exportar' : 'importar';
            throw new Exception("Error al $operation_name la base de datos: " . implode("\n", $output));
        }
        
        return $output;
    }
    
    private function getDbConfig() {
        // Usar las constantes del sistema definidas en config.php
        return array(
            'host' => _DB_SERVER_,
            'username' => _DB_USER_,
            'password' => _DB_PASSWD_,
            'database' => _DB_NAME_
        );
    }
    
    private function getMysqlDumpPath() {
        return $this->getMysqlToolPath('mysqldump');
    }
    
    private function getMysqlPath() {
        return $this->getMysqlToolPath('mysql');
    }
    
    private function getMysqlToolPath($tool) {
        // Detectar el sistema operativo y devolver la ruta correcta
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows - buscar en rutas comunes
            $paths = array(
                'C:\\xampp\\mysql\\bin\\' . $tool . '.exe',
                'C:\\wamp64\\bin\\mysql\\mysql*.*\\bin\\' . $tool . '.exe',
                'C:\\Program Files\\MySQL\\MySQL Server*.*\\bin\\' . $tool . '.exe',
                'C:\\mysql\\bin\\' . $tool . '.exe'
            );
            
            foreach ($paths as $path) {
                $expanded_path = glob($path);
                if (!empty($expanded_path) && file_exists($expanded_path[0])) {
                    return $expanded_path[0];
                }
            }
            
            // Si no encuentra en rutas específicas, intentar con PATH del sistema
            return $tool . '.exe';
        } else {
            // Linux/Mac - buscar en rutas comunes
            $paths = array(
                '/usr/bin/' . $tool,
                '/usr/local/bin/' . $tool,
                '/opt/lampp/bin/' . $tool,
                '/opt/lampp/bin/' . $tool . '.bin'
            );
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // Si no encuentra, intentar con PATH del sistema
            return $tool;
        }
    }
    
    public function getDatabaseInfo() {
        $info = array();
        
        // Obtener información de las tablas
        $result = $this->db->fetchAll("SHOW TABLE STATUS");
        
        $total_tables = 0;
        $total_size = 0;
        
        foreach ($result as $row) {
            $total_tables++;
            $total_size += $row['Data_length'] + $row['Index_length'];
        }
        
        $info['total_tables'] = $total_tables;
        $info['total_size'] = $this->formatBytes($total_size);
        
        // Obtener nombre de la base de datos
        $db_result = $this->db->fetchRow("SELECT DATABASE() as db_name");
        $info['database_name'] = $db_result['db_name'];
        
        return $info;
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
?>
