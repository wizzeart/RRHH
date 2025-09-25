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

    <!--Submenus-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios', 'historial', 'config'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios'))) print('active-link') ?>">
            <a href="?module=list-usuarios">Usuarios</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('historial'))) print('active-link') ?>">
            <a href="?module=historial">Historial</a>
        </li>
    </ul>
</li>
<!--NEW MENU TRABAJADORES-->
<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-users"></i>
        <span class="menu-title">
            <strong>Trabajadores</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores', 'delete-trabajadores', 'bajas-trabajadores', 'config'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores'))) print('active-link') ?>">
            <a href="?module=list-trabajadores">Lista de Trabajadores</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores'))) print('active-link') ?>">
            <a href="?module=trabajadores">Registrar Trabajador</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('delete-trabajadores'))) print('active-link') ?>">
            <a href="?module=delete-trabajadores">Dar de Baja Trabajadores</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('bajas-trabajadores'))) print('active-link') ?>">
            <a href="?module=bajas-trabajadores">Listado de Bajas</a>
        </li>
    </ul>
</li>


<!--NEW MENU SUBCONTRATOS-->
<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
    <i class="fa fa-street-view" aria-hidden="true"></i>
        <span class="menu-title">
            <strong>Subcontratos</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-subcontratos', 'subcontratos', 'delete-subcontratos', 'bajas-subcontratos'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-subcontratos', 'subcontratos'))) print('active-link') ?>">
            <a href="?module=list-subcontratos">Lista de Subcontratos</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('subcontratos'))) print('active-link') ?>">
            <a href="?module=subcontratos">Registrar Subcontrato</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('delete-subcontratos'))) print('active-link') ?>">
            <a href="?module=delete-subcontratos">Finalizar Subcontratos</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('bajas-subcontratos'))) print('active-link') ?>">
            <a href="?module=bajas-subcontratos">Subcontratos Finalizados</a>
        </li>
    </ul>
</li>
<!--NEW MENU CONTRATOS-->
<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-file-text-o"></i>
        <span class="menu-title">
            <strong>Contratos</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <ul class="collapse <?php if (in_array($_GET['module'], array('list-contratos', 'contratos'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-contratos', 'contratos'))) print('active-link') ?>">
            <a href="?module=list-contratos">Lista de Contratos</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('contratos'))) print('active-link') ?>">
            <a href="?module=contratos">Registrar Contrato</a>
        </li>
    </ul>
</li>

<!--NEW MENU PROGRAMAS CAPACITACION-->
<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-graduation-cap"></i>
        <span class="menu-title">
            <strong>Capacitación</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-programas-capacitacion', 'programas-capacitacion', 'delete-programas-capacitacion', 'finalizados-programas-capacitacion'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-programas-capacitacion', 'programas-capacitacion'))) print('active-link') ?>">
            <a href="?module=list-programas-capacitacion">Lista de Programas</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('programas-capacitacion'))) print('active-link') ?>">
            <a href="?module=programas-capacitacion">Registrar Programa</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('delete-programas-capacitacion'))) print('active-link') ?>">
            <a href="?module=delete-programas-capacitacion">Finalizar Programas</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('finalizados-programas-capacitacion'))) print('active-link') ?>">
            <a href="?module=finalizados-programas-capacitacion">Programas Finalizados</a>
        </li>
    </ul>
</li>

<!--NEW MENU BOLSAS EMPLEO-->
<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-database"></i>
        <span class="menu-title">
            <strong>Reclutamiento</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-bolsas_empleos', 'bolsas_empleos', 'config'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-bolsas_empleos', 'bolsas_empleos'))) print('active-link') ?>">
            <a href="?module=list-bolsas_empleos">Bolsa de Empleos</a>    
        </li>
        <li e>
        <li class="<?php if (in_array($_GET['module'], array('list-bolsas_empleos', 'bolsas_empleos'))) print('active-link') ?>">
            <a href="?module=bolsas_empleos">Postulación a Empleo</a>
        </li>
    </ul>
</li>

<!--NEW MENU ENTREGA DE RECURSOS-->
<li class="list-divider"></li>
<li>
    <a href="javascript:void(0);">
        <i class="fa fa-cube"></i>
        <span class="menu-title">
            <strong>Entrega de Recursos</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-recursos'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-recursos'))) print('active-link') ?>">
            <a href="?module=list-recursos">Lista de Recursos</a>
        </li>
    </ul>
</li>
