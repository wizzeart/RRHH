<!--Menu list item-->
<!-- 

<li class="<?php if ($_GET['module'] == 'home') print('active-link') ?>">
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

    
    
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-pedidos', 'list-pedidos-ptes', 'pedidos', 'list-clientes', 'list-clientes-mr', 'clientes', 'list-cestas', 'cestas', 'list-recargas', 'recargas'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-partes-trabajo', 'partes-trabajo'))) print('active-link') ?>">
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

   
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios', 'config'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios'))) print('active-link') ?>">
            <a href="?module=list-usuarios">Usuarios</a>
        </li>
        <li e>
        <li class="<?php if (in_array($_GET['module'], array('config'))) print('active-link') ?>">
            <a href="?module=config">Configuración</a>
        </li>
    </ul>
</li>

-->
<!--NEW MENU GENERAL-->

<!--NEW MENU TRABAJADORES-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('ficha-trabajador'))) print('active-link') ?>">
    <a href="?module=ficha-trabajador&usuario_id=<?php print($app->user_id); ?>">
        <i class="fa fa-user"></i>
        <span class="menu-title">
            <strong>Ficha de Trabajador</strong>
        </span>
        <!--<i class="arrow"></i>-->
    </a>

    
</li>

<!--NEW MENU BOLSAS EMPLEO-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('planificacion-vacaciones'))) print('active-link') ?>">
    <a href="?module=planificacion-vacaciones&usuario_id=<?php print($app->user_id); ?>">
        <i class="fa fa-umbrella"></i>
        <span class="menu-title">
            <strong>Planificar Vacaciones</strong>
        </span>
        <!--<i class="arrow"></i>-->
    </a>

    
</li>