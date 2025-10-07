<?php
// Debug para verificar las tablas con nombres correctos
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔍 Debug de Tablas Provincia y Municipio (nombres correctos)</h2>";

try {
    // 1. Verificar tabla provincia (sin s)
    echo "<h3>1. Verificando tabla 'provincia':</h3>";
    $sql = "SHOW TABLES LIKE 'provincia'";
    $result = $app->db->fetchAll($sql);
    
    if (!empty($result)) {
        echo "<p style='color: green;'>✅ Tabla 'provincia' existe</p>";
        
        // Contar registros
        $count = $app->db->fetchRow("SELECT COUNT(*) as total FROM provincia");
        echo "<p>📊 Total de provincias: {$count['total']}</p>";
        
        if ($count['total'] > 0) {
            // Mostrar algunas provincias
            $provincias = $app->db->fetchAll("SELECT id, nombre FROM provincia ORDER BY nombre ASC LIMIT 5");
            echo "<h4>Primeras 5 provincias:</h4>";
            echo "<ul>";
            foreach ($provincias as $prov) {
                echo "<li>ID: {$prov['id']} - {$prov['nombre']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ La tabla provincia está vacía</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ La tabla 'provincia' no existe</p>";
    }
    
    // 2. Verificar tabla municipio (sin s)
    echo "<h3>2. Verificando tabla 'municipio':</h3>";
    $sql = "SHOW TABLES LIKE 'municipio'";
    $result = $app->db->fetchAll($sql);
    
    if (!empty($result)) {
        echo "<p style='color: green;'>✅ Tabla 'municipio' existe</p>";
        
        // Contar registros
        $count = $app->db->fetchRow("SELECT COUNT(*) as total FROM municipio");
        echo "<p>📊 Total de municipios: {$count['total']}</p>";
        
        if ($count['total'] > 0) {
            // Mostrar algunos municipios
            $municipios = $app->db->fetchAll("
                SELECT m.id, m.nombre, m.provincia_id, p.nombre as provincia_nombre 
                FROM municipio m 
                LEFT JOIN provincia p ON m.provincia_id = p.id 
                ORDER BY m.nombre ASC 
                LIMIT 5
            ");
            echo "<h4>Primeros 5 municipios:</h4>";
            echo "<ul>";
            foreach ($municipios as $mun) {
                echo "<li>ID: {$mun['id']} - {$mun['nombre']} (Provincia: {$mun['provincia_nombre']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ La tabla municipio está vacía</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ La tabla 'municipio' no existe</p>";
    }
    
    // 3. Verificar tabla trabajadores y sus campos
    echo "<h3>3. Verificando campos en tabla 'trabajadores':</h3>";
    
    // Verificar id_provincia
    $sql = "SHOW COLUMNS FROM trabajadores LIKE 'id_provincia'";
    $result = $app->db->fetchAll($sql);
    
    if (!empty($result)) {
        echo "<p style='color: green;'>✅ Campo 'id_provincia' existe en trabajadores</p>";
    } else {
        echo "<p style='color: red;'>❌ Campo 'id_provincia' no existe en trabajadores</p>";
    }
    
    // Verificar id_municipio
    $sql = "SHOW COLUMNS FROM trabajadores LIKE 'id_municipio'";
    $result = $app->db->fetchAll($sql);
    
    if (!empty($result)) {
        echo "<p style='color: green;'>✅ Campo 'id_municipio' existe en trabajadores</p>";
    } else {
        echo "<p style='color: red;'>❌ Campo 'id_municipio' no existe en trabajadores</p>";
    }
    
    // 4. Probar consultas corregidas
    echo "<h3>4. Probando consultas corregidas:</h3>";
    
    // Probar consulta de provincias
    try {
        $sql = "SELECT id, nombre FROM provincia ORDER BY nombre ASC";
        $provincias = $app->db->fetchAll($sql);
        echo "<p style='color: green;'>✅ Consulta de provincias funciona: " . count($provincias) . " registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta de provincias: " . $e->getMessage() . "</p>";
    }
    
    // Probar consulta de municipios
    try {
        $sql = "SELECT id, nombre, provincia_id FROM municipio ORDER BY nombre ASC";
        $municipios = $app->db->fetchAll($sql);
        echo "<p style='color: green;'>✅ Consulta de municipios funciona: " . count($municipios) . " registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta de municipios: " . $e->getMessage() . "</p>";
    }
    
    // Probar consulta con JOIN
    try {
        $sql = "SELECT 
                t.id, t.nombre, t.apellidos, t.id_provincia, t.id_municipio,
                p.nombre as provincia_nombre,
                m.nombre as municipio_nombre
                FROM trabajadores t 
                LEFT JOIN provincia p ON t.id_provincia = p.id
                LEFT JOIN municipio m ON t.id_municipio = m.id
                WHERE t.trabajador_eliminado = '0'
                LIMIT 3";
        $trabajadores = $app->db->fetchAll($sql);
        echo "<p style='color: green;'>✅ Consulta con JOIN funciona: " . count($trabajadores) . " registros</p>";
        
        if (!empty($trabajadores)) {
            echo "<h4>Ejemplo de trabajadores con provincia/municipio:</h4>";
            echo "<ul>";
            foreach ($trabajadores as $trab) {
                echo "<li>{$trab['nombre']} {$trab['apellidos']} - {$trab['provincia_nombre']} / {$trab['municipio_nombre']}</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta con JOIN: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>📋 Resumen</h3>";
echo "<p>Si ves errores arriba, significa que las tablas o campos no existen con los nombres correctos.</p>";
echo "<p>Si todo está en verde, el problema puede estar en la carga de datos en el formulario.</p>";
?>
