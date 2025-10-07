<?php
// Debug de la tabla prenomina - ejecutar desde navegador
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔍 Debug de la tabla prenomina</h2>";

try {
    // Verificar si existe la tabla prenomina
    echo "<h3>1. Verificando existencia de tabla prenomina:</h3>";
    $sql = "SHOW TABLES LIKE 'prenomina'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "<p style='color: red;'>❌ La tabla 'prenomina' no existe.</p>";
        echo "<p>Necesitas crear la tabla prenomina primero.</p>";
    } else {
        echo "<p style='color: green;'>✅ La tabla 'prenomina' existe.</p>";
        
        // Mostrar estructura de la tabla
        echo "<h3>2. Estructura de la tabla prenomina:</h3>";
        $columns = $app->db->fetchAll("SHOW COLUMNS FROM prenomina");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar campo específico ausencias_costo
        echo "<h3>3. Verificando campo 'ausencias_costo':</h3>";
        $sql = "SHOW COLUMNS FROM prenomina LIKE 'ausencias_costo'";
        $result = $app->db->fetchAll($sql);
        
        if (empty($result)) {
            echo "<p style='color: red;'>❌ El campo 'ausencias_costo' no existe.</p>";
            echo "<p>Ejecuta este SQL para agregarlo:</p>";
            echo "<code>ALTER TABLE prenomina ADD COLUMN ausencias_costo DECIMAL(10,2) DEFAULT 0.00 AFTER ausencias;</code>";
        } else {
            echo "<p style='color: green;'>✅ El campo 'ausencias_costo' existe.</p>";
        }
        
        // Contar registros
        echo "<h3>4. Conteo de registros:</h3>";
        $count = $app->db->fetchRow("SELECT COUNT(*) as total FROM prenomina");
        echo "<p>Total de registros en prenomina: {$count['total']}</p>";
    }
    
    // Verificar tablas relacionadas
    echo "<h3>5. Verificando tablas relacionadas:</h3>";
    $tables = ['trabajadores', 'cargos', 'registro_asistencia', 'registro_vacaciones'];
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $app->db->fetchAll($sql);
        if (empty($result)) {
            echo "<p style='color: red;'>❌ La tabla '$table' no existe.</p>";
        } else {
            echo "<p style='color: green;'>✅ La tabla '$table' existe.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<p>1. Si la tabla no existe, créala usando el SQL apropiado.</p>";
echo "<p>2. Si falta el campo 'ausencias_costo', agrégalo con el ALTER TABLE mostrado arriba.</p>";
echo "<p>3. Una vez corregido, prueba el módulo de prenómina nuevamente.</p>";
?>
