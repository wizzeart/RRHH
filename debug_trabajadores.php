<?php
// Debug para verificar carga de provincias y municipios
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');
require_once(BASE_CLASS . '/mdl.Trabajadores.php');

$app = new App();
$trabajador = new Trabajador($app);

echo "<h2>🔍 Debug de Provincias y Municipios en Trabajadores</h2>";

try {
    // 1. Verificar conexión a base de datos
    echo "<h3>1. Verificando conexión a base de datos:</h3>";
    $test = $app->db->fetchRow("SELECT 1 as test");
    if ($test) {
        echo "<p style='color: green;'>✅ Conexión a base de datos OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Error de conexión a base de datos</p>";
    }
    
    // 2. Verificar tablas
    echo "<h3>2. Verificando existencia de tablas:</h3>";
    
    $tables = ['provincias', 'municipios', 'trabajadores'];
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $app->db->fetchAll($sql);
        if (!empty($result)) {
            echo "<p style='color: green;'>✅ Tabla '$table' existe</p>";
            
            // Contar registros
            $count = $app->db->fetchRow("SELECT COUNT(*) as total FROM $table");
            echo "<p>&nbsp;&nbsp;&nbsp;📊 Total de registros: {$count['total']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla '$table' no existe</p>";
        }
    }
    
    // 3. Probar consulta directa de provincias
    echo "<h3>3. Probando consulta directa de provincias:</h3>";
    try {
        $sql = "SELECT id, nombre FROM provincias ORDER BY nombre ASC";
        $provincias = $app->db->fetchAll($sql);
        echo "<p style='color: green;'>✅ Consulta de provincias exitosa</p>";
        echo "<p>📊 Provincias encontradas: " . count($provincias) . "</p>";
        
        if (!empty($provincias)) {
            echo "<h4>Primeras 5 provincias:</h4>";
            echo "<ul>";
            foreach (array_slice($provincias, 0, 5) as $prov) {
                echo "<li>ID: {$prov['id']} - Nombre: {$prov['nombre']}</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta de provincias: " . $e->getMessage() . "</p>";
    }
    
    // 4. Probar consulta directa de municipios
    echo "<h3>4. Probando consulta directa de municipios:</h3>";
    try {
        $sql = "SELECT id, nombre, provincia_id FROM municipios ORDER BY nombre ASC";
        $municipios = $app->db->fetchAll($sql);
        echo "<p style='color: green;'>✅ Consulta de municipios exitosa</p>";
        echo "<p>📊 Municipios encontrados: " . count($municipios) . "</p>";
        
        if (!empty($municipios)) {
            echo "<h4>Primeros 5 municipios:</h4>";
            echo "<ul>";
            foreach (array_slice($municipios, 0, 5) as $mun) {
                echo "<li>ID: {$mun['id']} - Nombre: {$mun['nombre']} - Provincia ID: {$mun['provincia_id']}</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta de municipios: " . $e->getMessage() . "</p>";
    }
    
    // 5. Probar métodos de la clase Trabajador
    echo "<h3>5. Probando métodos de la clase Trabajador:</h3>";
    
    // Usar reflexión para acceder a métodos privados
    $reflection = new ReflectionClass($trabajador);
    
    // Probar _get_list_provincias
    try {
        $method = $reflection->getMethod('_get_list_provincias');
        $method->setAccessible(true);
        $provincias = $method->invoke($trabajador);
        
        echo "<p style='color: green;'>✅ Método _get_list_provincias() funciona</p>";
        echo "<p>📊 Provincias retornadas: " . count($provincias) . "</p>";
        
        if (!empty($provincias)) {
            echo "<h4>Datos retornados por el método:</h4>";
            echo "<pre>" . print_r(array_slice($provincias, 0, 3), true) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en método _get_list_provincias(): " . $e->getMessage() . "</p>";
    }
    
    // Probar _get_list_municipios
    try {
        $method = $reflection->getMethod('_get_list_municipios');
        $method->setAccessible(true);
        $municipios = $method->invoke($trabajador);
        
        echo "<p style='color: green;'>✅ Método _get_list_municipios() funciona</p>";
        echo "<p>📊 Municipios retornados: " . count($municipios) . "</p>";
        
        if (!empty($municipios)) {
            echo "<h4>Datos retornados por el método:</h4>";
            echo "<pre>" . print_r(array_slice($municipios, 0, 3), true) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en método _get_list_municipios(): " . $e->getMessage() . "</p>";
    }
    
    // 6. Simular carga del controlador
    echo "<h3>6. Simulando carga del controlador:</h3>";
    try {
        global $data_form;
        $data_form = array();
        
        // Simular el controlador
        $param = array('module' => 'trabajadores');
        $trabajador->controlador($param);
        
        echo "<p style='color: green;'>✅ Controlador ejecutado sin errores</p>";
        
        if (isset($data_form['provincias'])) {
            echo "<p>📊 Provincias en data_form: " . count($data_form['provincias']) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ data_form['provincias'] no está definido</p>";
        }
        
        if (isset($data_form['municipios'])) {
            echo "<p>📊 Municipios en data_form: " . count($data_form['municipios']) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ data_form['municipios'] no está definido</p>";
        }
        
        echo "<h4>Contenido de data_form:</h4>";
        echo "<pre>" . print_r(array_keys($data_form), true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en controlador: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>📋 Resumen de Diagnóstico</h3>";
echo "<p>Ejecuta este script para identificar exactamente dónde está el problema.</p>";
echo "<p>Una vez identificado, podremos aplicar la solución específica.</p>";
?>
