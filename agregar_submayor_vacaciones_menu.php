<?php
/**
 * Script para agregar el módulo Submayor de Vacaciones al menú de Contabilidad
 */

echo "<h2>🔧 Agregando Submayor de Vacaciones al menú</h2>";

// Buscar archivos de menú
$posiblesArchivos = [
    'includes/perfiles/administrador.php',
    'includes/perfiles/administrador.php.backup'
];

$archivoMenu = null;
foreach ($posiblesArchivos as $archivo) {
    if (file_exists($archivo)) {
        $archivoMenu = $archivo;
        break;
    }
}

if (!$archivoMenu) {
    die("❌ Error: No se encontró el archivo de menú de administrador");
}

echo "<p>✅ Archivo de menú encontrado: $archivoMenu</p>";

// Leer el archivo actual
$contenido = file_get_contents($archivoMenu);

if ($contenido === false) {
    die("❌ Error: No se pudo leer el archivo $archivoMenu");
}

// Buscar la sección del menú de contabilidad para agregar el nuevo submódulo
$buscarSubmenu = 'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'';

$reemplazarSubmenu = 'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\', \'submayor-vacaciones\', \'list-submayor-vacaciones\'';

// Buscar donde agregar el nuevo elemento del menú
$buscarUltimoLi = '<li class="<?php if (in_array($_GET[\'module\'], array(\'deudas\', \'list-deudas\'))) print(\'active-link\') ?>">
            <a href="?module=list-deudas">Deudas</a>
        </li>';

$reemplazarUltimoLi = '<li class="<?php if (in_array($_GET[\'module\'], array(\'deudas\', \'list-deudas\'))) print(\'active-link\') ?>">
            <a href="?module=list-deudas">Deudas</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'submayor-vacaciones\', \'list-submayor-vacaciones\'))) print(\'active-link\') ?>">
            <a href="?module=list-submayor-vacaciones">Submayor de Vacaciones</a>
        </li>';

// Realizar los reemplazos
$contenidoNuevo = $contenido;

// Actualizar la lista de módulos activos
if (strpos($contenido, $buscarSubmenu) !== false) {
    $contenidoNuevo = str_replace($buscarSubmenu, $reemplazarSubmenu, $contenidoNuevo);
    echo "<p>✅ Lista de módulos activos actualizada</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se encontró la lista de módulos activos para actualizar</p>";
}

// Agregar el nuevo elemento del menú
if (strpos($contenidoNuevo, $buscarUltimoLi) !== false) {
    $contenidoNuevo = str_replace($buscarUltimoLi, $reemplazarUltimoLi, $contenidoNuevo);
    echo "<p>✅ Elemento de menú 'Submayor de Vacaciones' agregado</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se encontró la sección de menú para agregar el nuevo elemento</p>";
}

// Verificar si se hicieron cambios
if ($contenidoNuevo !== $contenido) {
    // Hacer backup del archivo original
    $backup = $archivoMenu . '.backup.' . date('Y-m-d_H-i-s');
    if (copy($archivoMenu, $backup)) {
        echo "<p>✅ Backup creado: $backup</p>";
    }
    
    // Escribir el nuevo contenido
    if (file_put_contents($archivoMenu, $contenidoNuevo)) {
        echo "<p style='color: green;'>✅ <strong>Menú actualizado exitosamente</strong></p>";
        echo "<h3>📋 Cambios aplicados:</h3>";
        echo "<ul>";
        echo "<li>✅ Agregado 'Submayor de Vacaciones' al menú de Contabilidad</li>";
        echo "<li>✅ Actualizada lista de módulos activos</li>";
        echo "<li>✅ Configurado enlace: ?module=list-submayor-vacaciones</li>";
        echo "</ul>";
        
        echo "<h3>🎯 Resultado:</h3>";
        echo "<p><strong>Nuevo submódulo disponible en:</strong> Contabilidad → Submayor de Vacaciones</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Error al guardar el archivo</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No se realizaron cambios (posiblemente ya estaba actualizado)</p>";
}

echo "<hr>";
echo "<h3>🔍 Verificación:</h3>";
echo "<p>Para verificar que el módulo funciona correctamente:</p>";
echo "<ol>";
echo "<li>Acceda al sistema como administrador</li>";
echo "<li>Vaya al menú 'Contabilidad'</li>";
echo "<li>Busque la opción 'Submayor de Vacaciones'</li>";
echo "<li>Haga clic para acceder al listado</li>";
echo "</ol>";
?>
