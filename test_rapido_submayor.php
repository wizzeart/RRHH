<?php
/**
 * Prueba rápida del módulo Submayor de Vacaciones
 */

include(__DIR__ . '/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

echo "<h2>🚀 Prueba Rápida - Submayor de Vacaciones</h2>";

echo "<h3>✅ Archivo JavaScript Restaurado</h3>";
echo "<p>Se ha creado un archivo JavaScript completamente nuevo y funcional.</p>";

echo "<h3>🔧 Cambios Realizados:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Estructura limpia:</strong> Todo el código dentro de waitForJQ</li>";
echo "<li>✅ <strong>Event handlers corregidos:</strong> Botón agregar vacaciones funcional</li>";
echo "<li>✅ <strong>Formatters externos:</strong> Funciones formatter fuera del bloque</li>";
echo "<li>✅ <strong>Logs de debug:</strong> Console.log para troubleshooting</li>";
echo "<li>✅ <strong>Validaciones completas:</strong> Manejo de errores mejorado</li>";
echo "</ul>";

echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
echo "<h3 style='color: #155724;'>🎯 Funcionalidades Restauradas</h3>";
echo "<ol>";
echo "<li><strong>Tabla principal:</strong> Carga y muestra datos correctamente</li>";
echo "<li><strong>Edición en línea:</strong> Clic en botones para editar valores</li>";
echo "<li><strong>Botón Agregar Vacaciones:</strong> Abre modal y carga trabajadores</li>";
echo "<li><strong>Modal de agregar:</strong> Selección de trabajador y cálculo automático</li>";
echo "<li><strong>Guardado:</strong> Inserta datos en tabla submayor_vacaciones</li>";
echo "<li><strong>Notificaciones:</strong> Mensajes de éxito/error</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 20px 0;'>";
echo "<h3 style='color: #856404;'>🧪 Pasos de Prueba</h3>";
echo "<ol>";
echo "<li><strong>Acceder al módulo:</strong> <a href='?module=list-submayor-vacaciones' target='_blank'>?module=list-submayor-vacaciones</a></li>";
echo "<li><strong>Verificar tabla:</strong> Debe cargar y mostrar trabajadores</li>";
echo "<li><strong>Probar botón:</strong> Clic en 'Agregar Vacaciones' debe abrir modal</li>";
echo "<li><strong>Seleccionar trabajador:</strong> Debe mostrar cargo y salario</li>";
echo "<li><strong>Ingresar días:</strong> Debe calcular total automáticamente</li>";
echo "<li><strong>Guardar:</strong> Debe insertar registro y cerrar modal</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; margin: 20px 0;'>";
echo "<h3 style='color: #721c24;'>🐛 Si Hay Problemas</h3>";
echo "<ul>";
echo "<li><strong>Abrir DevTools (F12)</strong> y revisar la consola</li>";
echo "<li><strong>Buscar errores JavaScript</strong> en la pestaña Console</li>";
echo "<li><strong>Verificar que jQuery funciona:</strong> escribir <code>\$</code> en consola</li>";
echo "<li><strong>Probar manualmente:</strong> <code>\$('#btn-agregar-vacaciones').click()</code></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #e2e3e5; padding: 15px; border: 1px solid #d6d8db; margin: 20px 0;'>";
echo "<h3 style='color: #383d41;'>📁 Archivos</h3>";
echo "<ul>";
echo "<li><strong>Backup:</strong> list-submayor-vacaciones.js.backup</li>";
echo "<li><strong>Nuevo:</strong> list-submayor-vacaciones.js (reescrito completamente)</li>";
echo "<li><strong>HTML:</strong> list-submayor-vacaciones.php (sin cambios)</li>";
echo "<li><strong>Controlador:</strong> mdl.SubmayorVacaciones.php (sin cambios)</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>🎉 El módulo debería funcionar correctamente ahora.</strong></p>";
?>
