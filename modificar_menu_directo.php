<?php
/**
 * Script para modificar directamente el menú de administrador
 */

$archivo = 'includes/perfiles/administrador.php';

echo "<h2>🔧 Modificando menú de prenómina</h2>";

// Leer el archivo
$contenido = file_get_contents($archivo);

if ($contenido === false) {
    die("❌ Error: No se pudo leer el archivo $archivo");
}

echo "<p>✅ Archivo leído correctamente</p>";

// Buscar la sección comentada específica de prenómina
$buscarInicio = '<!--NEW MENU SUBCONTRATOS-->
<!-- <li class="list-divider"></li>
<li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'active-link\') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-money"></i>
        <span class="menu-title">
            <strong>Contabilidad</strong>
        </span>
        <i class="arrow"></i>
    </a> -->';

$reemplazarInicio = '<!--NEW MENU SUBCONTRATOS-->
<?php
// Ocultar prenómina si la empresa es Custodios (ID=3)
if (!(isset($app->empresa_id) && $app->empresa_id == 3)) { ?>
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'active-link\') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-money"></i>
        <span class="menu-title">
            <strong>Contabilidad</strong>
        </span>
        <i class="arrow"></i>
    </a>';

// Buscar el submenu comentado
$buscarSubmenu = '    <!--Submenu-->
    <!-- <ul class="collapse <?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'in\') ?>">
        <li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'saldos\'))) print(\'active-link\') ?>">
            <a href="?module=list-saldos">Tarifas por Hora</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'prenomina\', \'prenomina\'))) print(\'active-link\') ?>">
            <a href="?module=prenomina">Prenómina</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'ayudas-trabajadores\', \'list-ayudas-trabajadores\'))) print(\'active-link\') ?>">
            <a href="?module=list-ayudas-trabajadores">Ayudas a Trabajadores</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'list-cuentas\', \'cuentas\'))) print(\'active-link\') ?>">
            <a href="?module=list-cuentas">Cuentas Bancarias</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'list-tarjetas-snc\'))) print(\'active-link\') ?>">
            <a href="?module=list-tarjetas-snc">Tarjeta SNC</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'deudas\', \'list-deudas\'))) print(\'active-link\') ?>">
            <a href="?module=list-deudas">Deudas</a>
        </li>
    </ul>
</li> -->';

$reemplazarSubmenu = '    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'in\') ?>">
        <li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'saldos\'))) print(\'active-link\') ?>">
            <a href="?module=list-saldos">Tarifas por Hora</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'prenomina\', \'prenomina\'))) print(\'active-link\') ?>">
            <a href="?module=prenomina">Prenómina</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'ayudas-trabajadores\', \'list-ayudas-trabajadores\'))) print(\'active-link\') ?>">
            <a href="?module=list-ayudas-trabajadores">Ayudas a Trabajadores</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'list-cuentas\', \'cuentas\'))) print(\'active-link\') ?>">
            <a href="?module=list-cuentas">Cuentas Bancarias</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'list-tarjetas-snc\'))) print(\'active-link\') ?>">
            <a href="?module=list-tarjetas-snc">Tarjeta SNC</a>
        </li>
        <li class="<?php if (in_array($_GET[\'module\'], array(\'deudas\', \'list-deudas\'))) print(\'active-link\') ?>">
            <a href="?module=list-deudas">Deudas</a>
        </li>
    </ul>
</li>
<?php } ?>';

// Realizar los reemplazos
$contenidoNuevo = $contenido;

// Reemplazar el inicio del menú
if (strpos($contenido, $buscarInicio) !== false) {
    $contenidoNuevo = str_replace($buscarInicio, $reemplazarInicio, $contenidoNuevo);
    echo "<p>✅ Sección de inicio del menú encontrada y modificada</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se encontró la sección de inicio del menú</p>";
}

// Reemplazar el submenu
if (strpos($contenidoNuevo, $buscarSubmenu) !== false) {
    $contenidoNuevo = str_replace($buscarSubmenu, $reemplazarSubmenu, $contenidoNuevo);
    echo "<p>✅ Sección de submenu encontrada y modificada</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se encontró la sección de submenu</p>";
}

// Verificar si se hicieron cambios
if ($contenidoNuevo !== $contenido) {
    // Hacer backup
    $backup = $archivo . '.backup.' . date('Y-m-d_H-i-s');
    if (copy($archivo, $backup)) {
        echo "<p>✅ Backup creado: $backup</p>";
    }
    
    // Guardar cambios
    if (file_put_contents($archivo, $contenidoNuevo)) {
        echo "<p style='color: green;'>✅ <strong>Archivo modificado exitosamente</strong></p>";
        echo "<h3>📋 Cambios aplicados:</h3>";
        echo "<ul>";
        echo "<li>✅ Menú de Contabilidad descomentado</li>";
        echo "<li>✅ Condición agregada: se oculta cuando empresa_id = 3 (Custodios)</li>";
        echo "<li>✅ En todas las demás empresas aparece normalmente</li>";
        echo "</ul>";
        
        echo "<h3>🎯 Resultado:</h3>";
        echo "<p><strong>Empresa Custodios (ID=3):</strong> NO aparece el menú de prenómina</p>";
        echo "<p><strong>Otras empresas:</strong> SÍ aparece el menú de prenómina</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Error al guardar el archivo</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No se realizaron cambios (posiblemente ya estaba actualizado)</p>";
}

echo "<h3>🔄 Próximos pasos:</h3>";
echo "<p>1. Recarga la página del sistema para ver los cambios</p>";
echo "<p>2. Cambia entre empresas para verificar que funciona correctamente</p>";
echo "<p>3. Si necesitas revertir, usa el archivo de backup creado</p>";
?>
