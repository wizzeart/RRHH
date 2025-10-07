<?php
// Debug para verificar el problema del listado de trabajadores
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔍 Debug del Listado de Trabajadores</h2>";

try {
    // 1. Verificar estructura de la tabla trabajadores
    echo "<h3>1. Estructura de la tabla 'trabajadores':</h3>";
    $columns = $app->db->fetchAll("SHOW COLUMNS FROM trabajadores");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $has_id_provincia = false;
    $has_id_municipio = false;
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
        
        if ($col['Field'] == 'id_provincia') $has_id_provincia = true;
        if ($col['Field'] == 'id_municipio') $has_id_municipio = true;
    }
    echo "</table>";
    
    echo "<p><strong>¿Existe id_provincia?</strong> " . ($has_id_provincia ? "✅ SÍ" : "❌ NO") . "</p>";
    echo "<p><strong>¿Existe id_municipio?</strong> " . ($has_id_municipio ? "✅ SÍ" : "❌ NO") . "</p>";
    
    // 2. Probar consulta básica sin campos problemáticos
    echo "<h3>2. Probando consulta básica:</h3>";
    try {
        $sql_basica = "SELECT 
                t.id,
                t.nombre,
                t.apellidos,
                t.carnet_identidad,
                t.sexo,
                t.edad,
                t.estatus,
                t.cargos_id,
                CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo
                FROM trabajadores t 
                WHERE t.trabajador_eliminado = '0'
                ORDER BY t.apellidos, t.nombre
                LIMIT 5";
        
        $result = $app->db->fetchAll($sql_basica);
        echo "<p style='color: green;'>✅ Consulta básica funciona: " . count($result) . " registros</p>";
        
        if (!empty($result)) {
            echo "<h4>Primeros registros:</h4>";
            echo "<ul>";
            foreach ($result as $row) {
                echo "<li>ID: {$row['id']} - {$row['nombre']} {$row['apellidos']}</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta básica: " . $e->getMessage() . "</p>";
    }
    
    // 3. Probar consulta con campos de provincia/municipio si existen
    if ($has_id_provincia && $has_id_municipio) {
        echo "<h3>3. Probando consulta con id_provincia e id_municipio:</h3>";
        try {
            $sql_completa = "SELECT 
                    t.id,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.sexo,
                    t.edad,
                    t.estatus,
                    t.cargos_id,
                    t.id_provincia,
                    t.id_municipio,
                    CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo
                    FROM trabajadores t 
                    WHERE t.trabajador_eliminado = '0'
                    ORDER BY t.apellidos, t.nombre
                    LIMIT 5";
            
            $result = $app->db->fetchAll($sql_completa);
            echo "<p style='color: green;'>✅ Consulta completa funciona: " . count($result) . " registros</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error en consulta completa: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<h3>3. Los campos id_provincia/id_municipio no existen</h3>";
        echo "<p style='color: orange;'>⚠️ Necesitas agregar estos campos a la tabla trabajadores:</p>";
        echo "<pre>";
        echo "ALTER TABLE trabajadores ADD COLUMN id_provincia INT(11) DEFAULT NULL;\n";
        echo "ALTER TABLE trabajadores ADD COLUMN id_municipio INT(11) DEFAULT NULL;";
        echo "</pre>";
    }
    
    // 4. Probar consulta con JOIN si las tablas existen
    echo "<h3>4. Probando consulta con JOIN:</h3>";
    try {
        $sql_join = "SELECT 
                t.id,
                t.nombre,
                t.apellidos,
                t.carnet_identidad,
                t.sexo,
                t.edad,
                t.estatus,
                t.cargos_id,
                COALESCE(c.nombre, 'Sin cargo') as cargo_nombre,
                CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo
                FROM trabajadores t 
                LEFT JOIN cargos c ON t.cargos_id = c.id
                WHERE t.trabajador_eliminado = '0'
                ORDER BY t.apellidos, t.nombre
                LIMIT 5";
        
        $result = $app->db->fetchAll($sql_join);
        echo "<p style='color: green;'>✅ Consulta con JOIN básico funciona: " . count($result) . " registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta con JOIN: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>📋 Diagnóstico</h3>";
echo "<p>Este script te ayudará a identificar exactamente qué está causando el problema en el listado.</p>";
?>
