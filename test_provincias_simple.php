<?php
// Test simple y directo de provincias y municipios
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🧪 Test Simple de Provincias y Municipios</h2>";

// Test 1: Consulta directa de provincias
echo "<h3>Test 1: Consulta directa de provincias</h3>";
try {
    $sql = "SELECT id, nombre FROM provincias ORDER BY nombre ASC";
    $provincias = $app->db->fetchAll($sql);
    
    echo "<p>Provincias encontradas: " . count($provincias) . "</p>";
    
    if (count($provincias) > 0) {
        echo "<h4>Lista de provincias:</h4>";
        echo "<ul>";
        foreach ($provincias as $prov) {
            echo "<li>ID: {$prov['id']} - {$prov['nombre']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No hay provincias en la base de datos</p>";
        
        // Insertar provincias de prueba
        echo "<p>Insertando provincias de prueba...</p>";
        $provincias_test = ['La Habana', 'Santiago de Cuba', 'Matanzas', 'Villa Clara', 'Holguín'];
        
        foreach ($provincias_test as $nombre) {
            $app->db->fetchAll("INSERT INTO provincias (nombre) VALUES (?)", [$nombre]);
        }
        
        echo "<p style='color: green;'>✅ Provincias de prueba insertadas</p>";
        
        // Volver a consultar
        $provincias = $app->db->fetchAll($sql);
        echo "<p>Provincias después de insertar: " . count($provincias) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 2: Consulta directa de municipios
echo "<h3>Test 2: Consulta directa de municipios</h3>";
try {
    $sql = "SELECT id, nombre, provincia_id FROM municipios ORDER BY nombre ASC";
    $municipios = $app->db->fetchAll($sql);
    
    echo "<p>Municipios encontrados: " . count($municipios) . "</p>";
    
    if (count($municipios) > 0) {
        echo "<h4>Lista de municipios (primeros 10):</h4>";
        echo "<ul>";
        foreach (array_slice($municipios, 0, 10) as $mun) {
            echo "<li>ID: {$mun['id']} - {$mun['nombre']} (Provincia ID: {$mun['provincia_id']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No hay municipios en la base de datos</p>";
        
        // Insertar municipios de prueba si hay provincias
        if (count($provincias) > 0) {
            echo "<p>Insertando municipios de prueba...</p>";
            $municipios_test = [
                [1, 'Playa'], [1, 'Centro Habana'], [1, 'Plaza de la Revolución'],
                [2, 'Santiago de Cuba'], [2, 'Palma Soriano'],
                [3, 'Matanzas'], [3, 'Cárdenas'], [3, 'Varadero']
            ];
            
            foreach ($municipios_test as $mun) {
                $app->db->fetchAll("INSERT INTO municipios (provincia_id, nombre) VALUES (?, ?)", $mun);
            }
            
            echo "<p style='color: green;'>✅ Municipios de prueba insertados</p>";
            
            // Volver a consultar
            $municipios = $app->db->fetchAll($sql);
            echo "<p>Municipios después de insertar: " . count($municipios) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Generar HTML de prueba para los selects
echo "<h3>Test 3: HTML de selects generado</h3>";

if (!empty($provincias)) {
    echo "<h4>Select de Provincias:</h4>";
    echo '<select name="provincia_id" id="test-provincia">';
    echo '<option value="">Seleccionar Provincia</option>';
    foreach ($provincias as $prov) {
        echo '<option value="' . $prov['id'] . '">' . htmlspecialchars($prov['nombre']) . '</option>';
    }
    echo '</select>';
}

if (!empty($municipios)) {
    echo "<h4>Select de Municipios:</h4>";
    echo '<select name="municipio_id" id="test-municipio">';
    echo '<option value="">Seleccionar Municipio</option>';
    foreach ($municipios as $mun) {
        echo '<option value="' . $mun['id'] . '" data-provincia="' . $mun['provincia_id'] . '">' . htmlspecialchars($mun['nombre']) . '</option>';
    }
    echo '</select>';
}

echo "<hr>";
echo "<h3>✅ Test completado</h3>";
echo "<p>Si ves las provincias y municipios arriba, el problema está en el controlador de trabajadores.</p>";
echo "<p>Si no ves datos, el problema está en la base de datos.</p>";
?>
