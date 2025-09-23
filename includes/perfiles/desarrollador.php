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
<!-- 
<li class="<?php if ($_GET['module'] == 'home') print('active-link') ?>">
    <a href="index.php">

        <i class="fa fa-dashboard"></i>
        <span class="menu-title">
            <strong>Inicio</strong>
        </span>
    </a>
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
       
    </ul>
</li> -->

<!--NEW MENU TRABAJADORES-->
<!-- <li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-users"></i>
        <span class="menu-title">
            <strong>Trabajadores</strong>
        </span>
        <i class="arrow"></i>
    </a>

    
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores', 'config'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores'))) print('active-link') ?>">
            <a href="?module=list-trabajadores">Lista de Trabajadores</a>
        </li>
        <li e>
        <li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores'))) print('active-link') ?>">
            <a href="?module=trabajadores">Registrar Trabajador</a>
        </li>
    </ul>
</li> -->