<?php
require_once 'config.php';
require_once 'classes/class.App.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Verificación de Base de Datos y Tabla 'ayudas' ===\n\n";

try {
    $app = new App();
    $db = $app->db;
    
    // Verificar conexión
    echo "[1/3] Probando conexión a la base de datos...\n";
    $db->query("SELECT 1");
    echo "✓ Conexión exitosa\n\n";
    
    // Verificar si existe la tabla ayudas
    echo "[2/3] Verificando tabla 'ayudas'...\n";
    $tables = $db->fetchAll("SHOW TABLES LIKE 'ayudas'");
    
    if (count($tables) > 0) {
        echo "✓ La tabla 'ayudas' existe\n\n";
        
        // Mostrar estructura de la tabla
        echo "[3/3] Estructura de la tabla 'ayudas':\n";
        $columns = $db->fetchAll("DESCRIBE ayudas");
        echo "+------------------+--------------------------------+------+-----+---------------------+-----------------------------+\n";
        echo "| Field            | Type                           | Null | Key | Default             | Extra                       |\n";
        echo "+------------------+--------------------------------+------+-----+---------------------+-----------------------------+\n";
        
        foreach ($columns as $col) {
            printf("| %-16s | %-30s | %-4s | %-3s | %-19s | %-27s |\n", 
                   $col['Field'], 
                   $col['Type'], 
                   $col['Null'],
                   $col['Key'],
                   $col['Default'] === null ? 'NULL' : $col['Default'],
                   $col['Extra']);
        }
        echo "+------------------+--------------------------------+------+-----+---------------------+-----------------------------+\n\n";
        
        // Mostrar conteo de registros
        $count = $db->fetchOne("SELECT COUNT(*) as c FROM ayudas");
        echo "Total de registros: " . $count['c'] . "\n\n";
        
    } else {
        echo "✗ La tabla 'ayudas' NO existe\n\n";
        
        // Mostrar tablas existentes que podrían ser similares
        echo "Tablas existentes en la base de datos:\n";
        $all_tables = $db->fetchAll("SHOW TABLES");
        foreach ($all_tables as $table) {
            echo "- " . reset($table) . "\n";
        }
        
        // Mostrar script para crear la tabla
        echo "\nPuedes crear la tabla con el siguiente SQL:\n";
        echo "\nCREATE TABLE `ayudas` (";
        echo "\n  `id` int(11) NOT NULL AUTO_INCREMENT,";
        echo "\n  `trabajador_id` int(11) NOT NULL,";
        echo "\n  `tipo_ayuda` varchar(50) NOT NULL,";
        echo "\n  `valor` decimal(10,2) NOT NULL,";
        echo "\n  `moneda` char(3) NOT NULL,";
        echo "\n  `fecha_entrega` date NOT NULL,";
        echo "\n  `descripcion` text DEFAULT NULL,";
        echo "\n  PRIMARY KEY (`id`),"
        echo "\n  KEY `trabajador_id` (`trabajador_id`),"
        echo "\n  CONSTRAINT `ayudas_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE";
        echo "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
    }
    
} catch (Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nVerifica las credenciales en config.php\n";
    }
    
    // Mostrar información de depuración
    echo "\n--- Información de depuración ---\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "DB Host: " . DB_HOST . "\n";
    echo "DB Name: " . DB_NAME . "\n";
    echo "DB User: " . DB_USER . "\n";
    echo "DB Port: " . (defined('DB_PORT') ? DB_PORT : '3306') . "\n";
    
    // Verificar extensión PDO
    echo "\nExtensiones cargadas:\n";
    $extensions = get_loaded_extensions();
    sort($extensions);
    echo implode(", ", $extensions) . "\n";
    
    echo "\n¿PDO está instalado? " . (extension_loaded('pdo') ? 'Sí' : 'No') . "\n";
    if (extension_loaded('pdo')) {
        echo "Controladores PDO disponibles: " . implode(", ", PDO::getAvailableDrivers()) . "\n";
    }
}

// Mostrar información del servidor
echo "\n\n--- Información del servidor ---\n";
echo "Sistema: " . php_uname() . "\n";
echo "Servidor web: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";

// Mostrar últimos errores de PHP
$errors = error_get_last();
if ($errors !== null) {
    echo "\n--- Últimos errores de PHP ---\n";
    print_r($errors);
}
