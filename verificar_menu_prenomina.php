<?php
/**
 * Script para verificar que el menú de prenómina está configurado correctamente
 */

require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔍 Verificación del menú de prenómina</h2>";

// Verificar empresa actual
echo "<h3>1. Empresa activa:</h3>";
echo "<p><strong>ID:</strong> " . ($app->empresa_id ?? 'NULL') . "</p>";
echo "<p><strong>Nombre:</strong> " . ($app->empresa_nombre ?? 'NULL') . "</p>";

// Verificar condición
$esCustodios = (isset($app->empresa_id) && $app->empresa_id == 3);
echo "<p><strong>¿Es Custodios?:</strong> " . ($esCustodios ? 'SÍ' : 'NO') . "</p>";

// Estado del menú
echo "<h3>2. Estado del menú de prenómina:</h3>";
if ($esCustodios) {
    echo "<p style='color: red; font-weight: bold;'>❌ OCULTO - El menú de prenómina NO debe aparecer</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ VISIBLE - El menú de prenómina SÍ debe aparecer</p>";
}

// Verificar archivo de menú
echo "<h3>3. Verificación del archivo de menú:</h3>";
$archivoMenu = 'includes/perfiles/administrador.php';
$contenido = file_get_contents($archivoMenu);

if ($contenido === false) {
    echo "<p style='color: red;'>❌ No se pudo leer el archivo de menú</p>";
} else {
    // Buscar la condición de empresa
    if (strpos($contenido, 'empresa_id == 3') !== false) {
        echo "<p style='color: green;'>✅ Condición de empresa encontrada en el archivo</p>";
    } else {
        echo "<p style='color: red;'>❌ Condición de empresa NO encontrada en el archivo</p>";
    }
    
    // Buscar si el menú está descomentado
    if (strpos($contenido, '<strong>Contabilidad</strong>') !== false && 
        strpos($contenido, '<!-- <li class="list-divider"></li>') === false) {
        echo "<p style='color: green;'>✅ Menú de Contabilidad está descomentado</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Menú de Contabilidad parece estar comentado</p>";
    }
}

// Mostrar todas las empresas disponibles
echo "<h3>4. Empresas disponibles:</h3>";
try {
    $empresas = $app->db->fetchAll("SELECT id, nombre FROM empresas ORDER BY id");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 5px;'>ID</th><th style='padding: 5px;'>Nombre</th><th style='padding: 5px;'>Menú Prenómina</th></tr>";
    foreach ($empresas as $empresa) {
        $highlight = ($empresa['id'] == $app->empresa_id) ? " style='background-color: yellow;'" : "";
        $menuStatus = ($empresa['id'] == 3) ? 
            "<span style='color: red;'>❌ OCULTO</span>" : 
            "<span style='color: green;'>✅ VISIBLE</span>";
        echo "<tr{$highlight}>";
        echo "<td style='padding: 5px;'>{$empresa['id']}</td>";
        echo "<td style='padding: 5px;'>{$empresa['nombre']}</td>";
        echo "<td style='padding: 5px;'>{$menuStatus}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al obtener empresas: " . $e->getMessage() . "</p>";
}

echo "<h3>5. Instrucciones de prueba:</h3>";
echo "<ol>";
echo "<li>Cambia a la empresa <strong>Custodios</strong> → El menú de prenómina NO debe aparecer</li>";
echo "<li>Cambia a cualquier otra empresa → El menú de prenómina SÍ debe aparecer</li>";
echo "<li>Verifica que el menú 'Contabilidad' con submenu 'Prenómina' funciona correctamente</li>";
echo "</ol>";

echo "<h3>6. Archivos creados:</h3>";
echo "<ul>";
echo "<li>✅ includes/menu_config.php - Configuración de menús</li>";
echo "<li>✅ check_menu_prenomina.php - Script de verificación</li>";
echo "<li>✅ modificar_menu_directo.php - Script de modificación</li>";
echo "<li>✅ verificar_menu_prenomina.php - Este script</li>";
echo "</ul>";
?>
