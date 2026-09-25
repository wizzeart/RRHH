<?php
/**
 * Script para aplicar cambios al menú de prenómina
 */

$archivo = 'c:\xampp\htdocs\includes\perfiles\administrador.php';

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Buscar la línea específica del menú de prenómina comentado
$lineaBuscar = '        <li class="<?php if (in_array($_GET[\'module\'], array(\'prenomina\', \'prenomina\'))) print(\'active-link\') ?>">';

// Verificar si la línea existe
if (strpos($contenido, $lineaBuscar) !== false) {
    echo "✅ Línea de prenómina encontrada\n";
    
    // Buscar el bloque completo comentado que contiene prenómina
    $bloqueComentado = '<!-- <li class="list-divider"></li>
<li class="<?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'active-link\') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-money"></i>
        <span class="menu-title">
            <strong>Contabilidad</strong>
        </span>
        <i class="arrow"></i>
    </a> -->';
    
    // Nuevo bloque con condición
    $bloqueNuevo = '<?php
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
    
    // Reemplazar
    $contenidoNuevo = str_replace($bloqueComentado, $bloqueNuevo, $contenido);
    
    // Buscar y reemplazar el submenu también
    $submenuComentado = '    <!-- <ul class="collapse <?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'in\') ?>">
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
    
    $submenuNuevo = '    <ul class="collapse <?php if (in_array($_GET[\'module\'], array(\'list-saldos\', \'list-cuentas\', \'saldos\', \'prenomina\', \'list-prenomina\', \'ayudas-trabajadores\', \'list-ayudas-trabajadores\', \'deudas\', \'list-deudas\', \'list-tarjetas-snc\'))) print(\'in\') ?>">
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
    
    $contenidoNuevo = str_replace($submenuComentado, $submenuNuevo, $contenidoNuevo);
    
    // Guardar el archivo
    if (file_put_contents($archivo, $contenidoNuevo)) {
        echo "✅ Archivo actualizado exitosamente\n";
        echo "📋 El menú de prenómina ahora se ocultará cuando empresa_id = 3 (Custodios)\n";
    } else {
        echo "❌ Error al guardar el archivo\n";
    }
} else {
    echo "❌ No se encontró la línea de prenómina en el archivo\n";
}
?>
