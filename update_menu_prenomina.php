<?php
/**
 * Script para actualizar el menú de prenómina con condición de empresa
 */

$archivoMenu = 'c:\xampp\htdocs\includes\perfiles\administrador.php';

// Leer el archivo actual
$contenido = file_get_contents($archivoMenu);

if ($contenido === false) {
    die("Error: No se pudo leer el archivo $archivoMenu");
}

echo "<h2>🔧 Actualizando menú de prenómina</h2>";

// Buscar la sección comentada de prenómina y descomentarla con condición
$buscar = '<!-- <li class="list-divider"></li>
<li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'active-link\') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-money"></i>
        <span class="menu-title">
            <strong>Contabilidad</strong>
        </span>
        <i class="arrow"></i>
    </a> -->';

$reemplazar = '<?php
// Incluir configuración de menú
require_once(INCLUDES . \'/menu_config.php\');
if ($MOSTRAR_PRENOMINA) { ?>
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'active-link\') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-money"></i>
        <span class="menu-title">
            <strong>Contabilidad</strong>
        </span>
        <i class="arrow"></i>
    </a>';

// Buscar la sección de submenu comentada
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
$contenidoNuevo = str_replace($buscar, $reemplazar, $contenido);
$contenidoNuevo = str_replace($buscarSubmenu, $reemplazarSubmenu, $contenidoNuevo);

// Verificar si se hicieron cambios
if ($contenidoNuevo !== $contenido) {
    // Hacer backup del archivo original
    $backup = $archivoMenu . '.backup.' . date('Y-m-d_H-i-s');
    if (copy($archivoMenu, $backup)) {
        echo "<p>✅ Backup creado: $backup</p>";
    }
    
    // Escribir el nuevo contenido
    if (file_put_contents($archivoMenu, $contenidoNuevo)) {
        echo "<p>✅ Archivo actualizado exitosamente</p>";
        echo "<p>📋 Cambios realizados:</p>";
        echo "<ul>";
        echo "<li>Descomentado el menú de Contabilidad</li>";
        echo "<li>Agregada condición para ocultar cuando empresa_id = 3 (Custodios)</li>";
        echo "<li>Incluida configuración de menu_config.php</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Error al escribir el archivo</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No se encontraron las secciones a modificar o ya están actualizadas</p>";
}

echo "<h3>Instrucciones:</h3>";
echo "<p>1. El menú de prenómina ahora se ocultará automáticamente cuando la empresa activa sea Custodios (ID=3)</p>";
echo "<p>2. En todas las demás empresas, el menú aparecerá normalmente</p>";
echo "<p>3. Si necesitas revertir los cambios, usa el archivo de backup creado</p>";
?>
