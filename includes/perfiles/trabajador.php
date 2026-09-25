<!--Menu list item-->
<!-- 

<li class="<?php if ($_GET['module'] == 'home')
    print ('active-link') ?>">
    <a href="index.php">
        <i class="fa fa-dashboard"></i>
        <span class="menu-title">
            <strong>Inicio</strong>
        </span>
    </a>
</li>

<li>
    <a href="javascript:void(0);">
        <i class="fa fa-th"></i>
        <span class="menu-title">
            <strong>Partes de Trabajo</strong>
        </span>
        <i class="arrow"></i>
    </a>

    
    
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-pedidos', 'list-pedidos-ptes', 'pedidos', 'list-clientes', 'list-clientes-mr', 'clientes', 'list-cestas', 'cestas', 'list-recargas', 'recargas')))
    print ('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-partes-trabajo', 'partes-trabajo')))
    print ('active-link') ?>">
            <a href="?module=list-partes-trabajo">Partes de Trabajo</a>
        </li>
    </ul>
</li>

<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-th"></i>
        <span class="menu-title">
            <strong>General</strong>
        </span>
        <i class="arrow"></i>
    </a>

   
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios', 'config')))
    print ('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios')))
    print ('active-link') ?>">
            <a href="?module=list-usuarios">Usuarios</a>
        </li>
        <li e>
        <li class="<?php if (in_array($_GET['module'], array('config')))
    print ('active-link') ?>">
            <a href="?module=config">Configuración</a>
        </li>
    </ul>
</li>

-->
    <!--NEW MENU GENERAL-->

    <!--NEW MENU TRABAJADORES-->
    <li class="list-divider"></li>
    <li class="<?php if (in_array($_GET['module'], array('ficha-trabajador')))
    print ('active-link') ?>">
        <?php
// Obtener el ID del trabajador para el usuario actual
$sql_trab = "SELECT id FROM trabajadores WHERE usuario_id = " . $app->user_id;
$row_trab = $app->db->fetchRow($sql_trab);
$trabajador_id = $row_trab ? $row_trab['id'] : 0;
?>
    <a href="?module=ficha-trabajador&id=<?php print ($trabajador_id); ?>">
        <i class="fa fa-user"></i>
        <span class="menu-title">
            <strong>Ficha de Trabajador</strong>
        </span>

    </a>


</li>

<!--NEW MENU BOLSAS EMPLEO-->
<?php if ($app->rol != 2): ?>
    <li class="list-divider"></li>
    <li class="<?php if (in_array($_GET['module'], array('vacaciones', 'planificacion-vacaciones')))
        print ('active-link') ?>">
            <a href="?module=vacaciones">
                <i class="fa fa-umbrella"></i>
                <span class="menu-title">
                    <strong>Mis Vacaciones</strong>
                </span>
                <!--<i class="arrow"></i>-->
            </a>


        </li>
<?php endif; ?>

<!--MENU CAMBIAR CONTRASEÑA-->
<li class="list-divider"></li>
<li class="<?php if ($_REQUEST['module'] == 'cambiar-contrasena')
    print ('active-link') ?>">
        <a href="?module=cambiar-contrasena">
            <i class="fa fa-lock"></i>
            <span class="menu-title">
                <strong>Cambiar Contraseña</strong>
            </span>
        </a>
    </li>