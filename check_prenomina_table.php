<?php
// Script para verificar y actualizar la tabla prenomina
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

try {
    // Verificar si existe la tabla prenomina
    $sql = "SHOW TABLES LIKE 'prenomina'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "❌ La tabla 'prenomina' no existe.\n";
        echo "Creando tabla prenomina...\n";
        
        $createTable = "
        CREATE TABLE `prenomina` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trabajador_id` int(11) NOT NULL,
            `year` int(4) NOT NULL,
            `month` int(2) NOT NULL,
            `horas` decimal(10,2) DEFAULT 192.00,
            `tarifa` decimal(10,2) DEFAULT 0.00,
            `a_cobrar` decimal(10,2) DEFAULT 0.00,
            `bonif` decimal(10,2) DEFAULT NULL,
            `sal_dev` decimal(10,2) DEFAULT 0.00,
            `ausencias` int(11) DEFAULT 0,
            `ausencias_costo` decimal(10,2) DEFAULT 0.00,
            `vacaciones` int(11) DEFAULT 0,
            `pago_vac` decimal(10,2) DEFAULT 0.00,
            `salario_neto` decimal(10,2) DEFAULT 0.00,
            `seg_social` decimal(10,2) DEFAULT 0.00,
            `ing_pers` decimal(10,2) DEFAULT 0.00,
            `salario_pagar` decimal(10,2) DEFAULT 0.00,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_worker_period` (`trabajador_id`, `year`, `month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $app->db->query($createTable);
        echo "✅ Tabla 'prenomina' creada exitosamente.\n";
    } else {
        echo "✅ La tabla 'prenomina' existe.\n";
        
        // Verificar si existe el campo ausencias_costo
        $sql = "SHOW COLUMNS FROM prenomina LIKE 'ausencias_costo'";
        $result = $app->db->fetchAll($sql);
        
        if (empty($result)) {
            echo "❌ El campo 'ausencias_costo' no existe.\n";
            echo "Agregando campo 'ausencias_costo'...\n";
            
            $addColumn = "ALTER TABLE prenomina ADD COLUMN ausencias_costo DECIMAL(10,2) DEFAULT 0.00 AFTER ausencias";
            $app->db->query($addColumn);
            echo "✅ Campo 'ausencias_costo' agregado exitosamente.\n";
        } else {
            echo "✅ El campo 'ausencias_costo' existe.\n";
        }
        
        // Mostrar estructura actual de la tabla
        echo "\n📋 Estructura actual de la tabla prenomina:\n";
        $columns = $app->db->fetchAll("SHOW COLUMNS FROM prenomina");
        foreach ($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
        }
    }
    
    // Verificar si existen las tablas relacionadas
    echo "\n🔍 Verificando tablas relacionadas:\n";
    
    $tables = ['trabajadores', 'cargos', 'registro_asistencia', 'registro_vacaciones'];
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $app->db->fetchAll($sql);
        if (empty($result)) {
            echo "❌ La tabla '$table' no existe.\n";
        } else {
            echo "✅ La tabla '$table' existe.\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Verificación completada.\n";
?>
