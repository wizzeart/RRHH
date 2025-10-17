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
<li class="<?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios', 'historial', 'config', 'list-departamentos', 'departamentos', 'list-cargos', 'cargos'))) print('active-link') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-th"></i>
        <span class="menu-title">
            <strong>General</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenus-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios', 'historial', 'config', 'list-departamentos', 'departamentos', 'list-cargos', 'cargos'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-usuarios', 'usuarios'))) print('active-link') ?>">
            <a href="?module=list-usuarios">Usuarios</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('list-departamentos', 'departamentos'))) print('active-link') ?>">
            <a href="?module=list-departamentos">Departamentos</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('list-cargos', 'cargos'))) print('active-link') ?>">
            <a href="?module=list-cargos">Cargos</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('historial'))) print('active-link') ?>">
            <a href="?module=historial">Historial</a>
        </li>
        
    </ul>
</li>
<!--NEW MENU TRABAJADORES-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores', 'delete-trabajadores', 'bajas-trabajadores'))) print('active-link') ?>">
    <a href="?module=list-trabajadores">
        <i class="fa fa-users"></i>
        <span class="menu-title">
            <strong>Trabajadores</strong>
        </span>
    </a>
</li>

<!--NEW MENU CUENTAS BANCARIAS-->


<!--NEW MENU TARJETA SNC -->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-tarjetas-snc'))) print('active-link') ?>">
    <a href="?module=list-tarjetas-snc">
        <i class="fa fa-id-card"></i>
        <span class="menu-title">
            <strong>Tarjeta SNC</strong>
        </span>
    </a>
</li>

<!--NEW MENU SUBCONTRATOS-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-saldos', 'list-cuentas', 'saldos', 'prenomina', 'list-prenomina', 'ayudas-trabajadores', 'list-ayudas-trabajadores', 'deudas', 'list-deudas'))) print('active-link') ?>">
    <a href="javascript:void(0);">
        <i class="fa fa-money"></i>
        <span class="menu-title">
            <strong>Contabilidad</strong>
        </span>
        <i class="arrow"></i>
    </a>

    <!--Submenu-->
    <ul class="collapse <?php if (in_array($_GET['module'], array('list-saldos', 'list-cuentas', 'saldos', 'prenomina', 'list-prenomina', 'ayudas-trabajadores', 'list-ayudas-trabajadores', 'deudas', 'list-deudas'))) print('in') ?>">
        <li class="<?php if (in_array($_GET['module'], array('list-saldos', 'saldos'))) print('active-link') ?>">
            <a href="?module=list-saldos">Tarifas por Hora</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('prenomina', 'prenomina'))) print('active-link') ?>">
            <a href="?module=prenomina">Prenómina</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('ayudas-trabajadores', 'list-ayudas-trabajadores'))) print('active-link') ?>">
            <a href="?module=list-ayudas-trabajadores">Ayudas a Trabajadores</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('deudas', 'list-deudas'))) print('active-link') ?>">
            <a href="?module=list-deudas">Deudas</a>
        </li>
        <li class="<?php if (in_array($_GET['module'], array('list-cuentas', 'cuentas'))) print('active-link') ?>">
            <a href="?module=list-cuentas">Cuentas Bancarias</a>
        </li>
    </ul>
</li>
<!--NEW MENU CUENTAS BANCARIAS-->


<!--NEW MENU CONTRATOS-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-contratos', 'contratos'))) print('active-link') ?>">
    <a href="?module=list-contratos">
        <i class="fa fa-file-text-o"></i>
        <span class="menu-title">
            <strong>Contratos</strong>
        </span>
    </a>
</li>

<!--NEW MENU SUBCONTRATOS-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-subcontratos', 'subcontratos', 'delete-subcontratos', 'bajas-subcontratos'))) print('active-link') ?>">
    <a href="?module=list-subcontratos">
        <i class="fa fa-street-view" aria-hidden="true"></i>
        <span class="menu-title">
            <strong>Subcontratación</strong>
        </span>
    </a>
</li>

<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-asistencias', 'asistencias'))) print('active-link') ?>">
    <a href="?module=list-asistencias">
        <i class="fa fa-calendar"></i>
        <span class="menu-title">
            <strong>Registro de Asistencias</strong>
        </span>
    </a>
</li>

<!--NEW MENU PROGRAMAS CAPACITACION-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-programas-capacitacion', 'programas-capacitacion', 'finalizados-programas-capacitacion'))) print('active-link') ?>">
    <a href="?module=list-programas-capacitacion">
        <i class="fa fa-graduation-cap"></i>
        <span class="menu-title">
            <strong>Capacitaciones</strong>
        </span>
    </a>
</li>




<!--NEW MENU BOLSAS EMPLEO-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-bolsas_empleos', 'bolsas_empleos'))) print('active-link') ?>">
    <a href="?module=list-bolsas_empleos">
        <i class="fa fa-database"></i>
        <span class="menu-title">
            <strong>Bolsa de Empleo</strong>
        </span>
    </a>
</li>

<!--NEW MENU ENTREGA DE RECURSOS-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-recursos', 'gestion-recursos'))) print('active-link') ?>">
    <a href="?module=list-recursos">
        <i class="fa fa-cube"></i>
        <span class="menu-title">
            <strong>Asignación de Recursos</strong>
        </span>
    </a>
</li>
