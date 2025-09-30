<?php
require_once 'config.php';
require_once 'classes/class.App.php';

try {
    $app = new App();
    $db = $app->db;
    
    // Verificar conexión
    echo "Probando conexión a la base de datos...\n";
    $db->query("SELECT 1");
    echo "✓ Conexión exitosa\n\n";
    
    // Verificar si existe la tabla ayudas
    echo "Verificando tabla 'ayudas'...\n";
    $tables = $db->fetchAll("SHOW TABLES LIKE 'ayudas'");
    
    if (count($tables) > 0) {
        echo "✓ La tabla 'ayudas' existe\n\n";
        
        // Mostrar estructura de la tabla
        echo "Estructura de la tabla 'ayudas':\n";
        $columns = $db->fetchAll("DESCRIBE ayudas");
        foreach ($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})\n";
        }
        
        // Mostrar conteo de registros
        $count = $db->fetchOne("SELECT COUNT(*) as c FROM ayudas");
        echo "\nTotal de registros: " . $count['c'] . "\n";
        
        // Mostrar últimos 5 registros
        $recent = $db->fetchAll("SELECT * FROM ayudas ORDER BY id DESC LIMIT 5");
        echo "\nÚltimos registros:\n";
        print_r($recent);
        
    } else {
        echo "✗ La tabla 'ayudas' NO existe\n\n";
        
        // Mostrar tablas existentes que podrían ser similares
        echo "Tablas existentes en la base de datos:\n";
        $all_tables = $db->fetchAll("SHOW TABLES");
        foreach ($all_tables as $table) {
            echo "- " . reset($table) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nVerifica las credenciales en config.php\n";
    }
}
