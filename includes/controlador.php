<?php

$css = array();
$js = array();
$page = array(
    'title' => '',
    'subtitle' => '',
    'contactos' => 0
);
if (in_array($app->rol, array('1', '3', '9', '12', '16'))) {
    //$page['contactos'] = $app->get_count_contactos();
    //$page['envios-pendientes-7'] = $app->get_count_envios_pendientes(7);
    //$page['envios-pendientes-7-pedidos'] = $app->get_count_envios_pendientes_pedidos(7);
}


//die('kdkdk');

if (isset($_REQUEST['module'])) {
    switch ($_REQUEST['module']) {
        case 'list-partes-trabajo':
     
        case 'config':
            include_once(BASE_CLASS . '/mdl.Config.php');
            $mdl = new Config($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-usuarios':
        case 'usuarios':
            include_once(BASE_CLASS . '/mdl.Usuarios.php');
            $mdl = new Usuario($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-trabajadores':
        case 'trabajadores':
            include_once(BASE_CLASS . '/mdl.Trabajadores.php');
            $mdl = new Trabajador($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-bolsas_empleos':
        case 'list-recursos':
            include_once(BASE_CLASS . '/mdl.List_recursos.php');
            $mdl = new List_recursos($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'gestion-recursos':
            //include_once(BASE_CLASS . '/mdl.Gestion_recursos.php');
            //$mdl = new Gestion_recursos($app);
            //$mdl->controlador($_REQUEST);
            break;
        case 'bolsas_empleos':
            include_once(BASE_CLASS . '/mdl.Bolsas_empleos.php');
            $mdl = new Bolsas_empleos($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'home':
            include_once(BASE_CLASS . '/mdl.Home.php');
            $mdl = new Home($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'logout':
            $app->user_id = '';
            $_SESSION['user_id'] = '';
            unset($_SESSION['user_id']);
            session_destroy();
            header("Location:index.php");
            break;
    }
} else {
    header("Location:index.php?module=home");
}
