<?php
/**
 * Script de prueba rápida para el botón Agregar Vacaciones
 */

include(__DIR__ . '/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

echo "<h2>🧪 Prueba del Botón Agregar Vacaciones</h2>";

echo "<h3>1. Verificar endpoint list-trabajadores</h3>";
try {
    include_once(BASE_CLASS . '/mdl.SubmayorVacaciones.php');
    $controller = new SubmayorVacaciones($app);
    
    // Simular llamada al endpoint
    $param = array('method' => 'list-trabajadores');
    
    ob_start();
    $controller->api($param);
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    
    if ($data && is_array($data) && count($data) > 0) {
        echo "<p style='color: green;'>✅ Endpoint funciona - " . count($data) . " trabajadores encontrados</p>";
        
        // Mostrar primer trabajador como ejemplo
        $primer = $data[0];
        echo "<p><strong>Ejemplo:</strong> " . $primer['nombre'] . " " . $primer['apellidos'] . " - " . $primer['cargo_nombre'] . " ($" . $primer['cargo_salario'] . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Error en endpoint: " . htmlspecialchars($output) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Verificar estructura del modal</h3>";
$modal_file = __DIR__ . '/modules/list-submayor-vacaciones/list-submayor-vacaciones.php';
if (file_exists($modal_file)) {
    $content = file_get_contents($modal_file);
    
    $checks = [
        'btn-agregar-vacaciones' => 'Botón Agregar Vacaciones',
        'addVacacionesModal' => 'Modal de agregar',
        'add-trabajador' => 'Select de trabajador',
        'add-vacaciones' => 'Campo días',
        'add-pago-vacaciones' => 'Campo pago opcional',
        'btn-save-add' => 'Botón guardar'
    ];
    
    foreach ($checks as $id => $desc) {
        if (strpos($content, $id) !== false) {
            echo "<p style='color: green;'>✅ $desc encontrado</p>";
        } else {
            echo "<p style='color: red;'>❌ $desc NO encontrado</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Archivo del módulo no encontrado</p>";
}

echo "<h3>3. Verificar JavaScript</h3>";
$js_file = __DIR__ . '/modules/list-submayor-vacaciones/list-submayor-vacaciones.js';
if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);
    
    $js_checks = [
        "btn-agregar-vacaciones" => 'Event handler del botón',
        "cargarTrabajadores" => 'Función cargar trabajadores',
        "btn-save-add" => 'Event handler guardar',
        "waitForJQ" => 'Wrapper jQuery',
        "addVacacionesModal" => 'Referencias al modal'
    ];
    
    foreach ($js_checks as $pattern => $desc) {
        if (strpos($js_content, $pattern) !== false) {
            echo "<p style='color: green;'>✅ $desc encontrado</p>";
        } else {
            echo "<p style='color: red;'>❌ $desc NO encontrado</p>";
        }
    }
    
    // Verificar que no hay errores de sintaxis obvios
    $syntax_errors = [];
    if (substr_count($js_content, '{') !== substr_count($js_content, '}')) {
        $syntax_errors[] = 'Llaves desbalanceadas';
    }
    if (substr_count($js_content, '(') !== substr_count($js_content, ')')) {
        $syntax_errors[] = 'Paréntesis desbalanceados';
    }
    
    if (empty($syntax_errors)) {
        echo "<p style='color: green;'>✅ Sintaxis básica correcta</p>";
    } else {
        echo "<p style='color: red;'>❌ Errores de sintaxis: " . implode(', ', $syntax_errors) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Archivo JavaScript no encontrado</p>";
}

echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 20px 0;'>";
echo "<h3 style='color: #856404;'>🔧 Pasos para probar manualmente</h3>";
echo "<ol>";
echo "<li>Abrir el módulo: <a href='?module=list-submayor-vacaciones' target='_blank'>?module=list-submayor-vacaciones</a></li>";
echo "<li>Verificar que aparece el botón verde 'Agregar Vacaciones' arriba de la tabla</li>";
echo "<li>Hacer clic en el botón - debe abrir un modal</li>";
echo "<li>El modal debe cargar la lista de trabajadores automáticamente</li>";
echo "<li>Seleccionar un trabajador debe mostrar su cargo y salario</li>";
echo "<li>Ingresar días debe calcular el total automáticamente</li>";
echo "<li>Hacer clic en 'Guardar en Base de Datos' debe insertar el registro</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; margin: 20px 0;'>";
echo "<h3 style='color: #0c5460;'>🐛 Si el botón no funciona</h3>";
echo "<ul>";
echo "<li><strong>Abrir DevTools (F12)</strong> y revisar la consola por errores JavaScript</li>";
echo "<li><strong>Verificar que jQuery está cargado</strong> - escribir <code>\$</code> en la consola</li>";
echo "<li><strong>Verificar que el event handler está registrado</strong> - escribir <code>\$('#btn-agregar-vacaciones').length</code></li>";
echo "<li><strong>Probar manualmente</strong> - escribir <code>\$('#addVacacionesModal').modal('show')</code></li>";
echo "</ul>";
echo "</div>";
?>
