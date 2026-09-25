<?php
/**
 * Test Final - Submayor de Vacaciones
 */

include(__DIR__ . '/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

echo "<h2>🔥 Test Final - Submayor de Vacaciones</h2>";

echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; margin: 20px 0;'>";
echo "<h3 style='color: #0c5460;'>✅ JavaScript Simplificado Implementado</h3>";
echo "<p>Se ha reemplazado el archivo JavaScript con una versión simplificada que:</p>";
echo "<ul>";
echo "<li>✅ <strong>No depende de waitForJQ</strong> - Usa $(document).ready() estándar</li>";
echo "<li>✅ <strong>Incluye logs de debug</strong> - Console.log para troubleshooting</li>";
echo "<li>✅ <strong>Usa alerts simples</strong> - En lugar de niftyNoty que puede fallar</li>";
echo "<li>✅ <strong>Event handlers directos</strong> - Sin dependencias del sistema</li>";
echo "<li>✅ <strong>Formatters externos</strong> - Funciones globales para Bootstrap Table</li>";
echo "</ul>";
echo "</div>";

// Test del endpoint list-trabajadores
echo "<h3>🧪 Test del Endpoint</h3>";
try {
    include_once(BASE_CLASS . '/mdl.SubmayorVacaciones.php');
    $controller = new SubmayorVacaciones($app);
    
    $param = array('method' => 'list-trabajadores');
    
    ob_start();
    $controller->api($param);
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    
    if ($data && is_array($data) && count($data) > 0) {
        echo "<p style='color: green;'>✅ Endpoint list-trabajadores funciona - " . count($data) . " trabajadores</p>";
        echo "<p><strong>Primer trabajador:</strong> " . $data[0]['nombre'] . " " . $data[0]['apellidos'] . " - " . $data[0]['cargo_nombre'] . " ($" . $data[0]['cargo_salario'] . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Error en endpoint: " . htmlspecialchars($output) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 20px 0;'>";
echo "<h3 style='color: #856404;'>🚀 Instrucciones de Prueba</h3>";
echo "<ol>";
echo "<li><strong>Abrir el módulo:</strong> <a href='?module=list-submayor-vacaciones' target='_blank'>?module=list-submayor-vacaciones</a></li>";
echo "<li><strong>Abrir DevTools (F12)</strong> y ir a la pestaña Console</li>";
echo "<li><strong>Verificar logs:</strong> Debe aparecer '🚀 JavaScript cargado - Submayor Vacaciones'</li>";
echo "<li><strong>Probar botón:</strong> Clic en 'Agregar Vacaciones' debe mostrar logs y abrir modal</li>";
echo "<li><strong>Probar edición:</strong> Clic en botones de la tabla debe mostrar logs y abrir modal</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; margin: 20px 0;'>";
echo "<h3 style='color: #721c24;'>🔧 Debug Manual</h3>";
echo "<p>Si aún no funciona, ejecutar en la consola del navegador:</p>";
echo "<pre style='background: #000; color: #0f0; padding: 10px;'>";
echo "// Verificar jQuery\n";
echo "console.log('jQuery:', typeof \$);\n\n";
echo "// Verificar elementos\n";
echo "console.log('Botón:', \$('#btn-agregar-vacaciones').length);\n";
echo "console.log('Tabla:', \$('#table-submayor-vacaciones').length);\n";
echo "console.log('Modal:', \$('#addVacacionesModal').length);\n\n";
echo "// Probar click manual\n";
echo "\$('#btn-agregar-vacaciones').click();\n\n";
echo "// Probar modal manual\n";
echo "\$('#addVacacionesModal').modal('show');";
echo "</pre>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
echo "<h3 style='color: #155724;'>📋 Checklist de Funcionalidades</h3>";
echo "<ul>";
echo "<li>☐ <strong>Tabla carga datos</strong> - Debe mostrar trabajadores</li>";
echo "<li>☐ <strong>Botón 'Agregar Vacaciones'</strong> - Debe abrir modal</li>";
echo "<li>☐ <strong>Modal carga trabajadores</strong> - Select debe llenarse</li>";
echo "<li>☐ <strong>Selección de trabajador</strong> - Debe mostrar cargo</li>";
echo "<li>☐ <strong>Cálculo automático</strong> - Al ingresar días</li>";
echo "<li>☐ <strong>Guardado funciona</strong> - Debe insertar en DB</li>";
echo "<li>☐ <strong>Edición en línea</strong> - Botones de tabla funcionan</li>";
echo "<li>☐ <strong>Modal de edición</strong> - Debe abrir y guardar</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>🎯 Si todas las funcionalidades funcionan, el problema está resuelto.</strong></p>";
?>
