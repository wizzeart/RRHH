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

        case 'list-asistencias':
            include_once(BASE_CLASS . '/mdl.Asistencias.php');
            $mdl = new Asistencia($app);
            $mdl->controlador($_REQUEST);
            break;
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
        case 'planificacion-vacaciones':
            break;
        case 'list-trabajadores':
        case 'trabajadores':
        case 'ficha-trabajador':
        case 'delete-trabajadores':
        case 'bajas-trabajadores':
            include_once(BASE_CLASS . '/mdl.Trabajadores.php');
            $mdl = new Trabajador($app);
            $mdl->controlador($_REQUEST);
            break;
            case 'list-recursos':
                case 'gestion-recursos':
            include_once(BASE_CLASS . '/mdl.List_recursos.php');
            $mdl = new List_recursos($app);
            $mdl->controlador($_REQUEST);
            break;
            case 'list-bolsas_empleos':
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
        
        case 'historial':
            include_once(BASE_CLASS . '/mdl.Historial.php');
            $mdl = new Historial($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-subcontratos':
        case 'subcontratos':
        case 'delete-subcontratos':
        case 'bajas-subcontratos':
            include_once(BASE_CLASS . '/mdl.Subcontratos.php');
            $mdl = new Subcontrato($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-departamentos':
        case 'departamentos':
            include_once(BASE_CLASS . '/mdl.Departamentos.php');
            $mdl = new Departamentos($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-cargos':
        case 'cargos':
            include_once(BASE_CLASS . '/mdl.Cargos.php');
            $mdl = new Cargos($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-saldos':
        case 'saldos':
            include_once(BASE_CLASS . '/mdl.Saldos.php');
            $mdl = new Saldos($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-ayudas-trabajadores':
            include_once(BASE_CLASS . '/mdl.Ayudas.php');
            $mdl = new Ayudas($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'cuentas':
        case 'list-cuentas':
            include_once(BASE_CLASS . '/mdl.Cuentas.php');
            $mdl = new Cuentas($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'prenomina':
            include_once(BASE_CLASS . '/mdl.Prenomina.php');
            $mdl = new Prenomina($app);
            $mdl->controlador($_REQUEST);
            break;
        case 'list-programas-capacitacion':
        case 'programas-capacitacion':
        case 'delete-programas-capacitacion':
        case 'finalizados-programas-capacitacion':
            include_once(BASE_CLASS . '/mdl.ProgramasCapacitacion.php');
            $mdl = new ProgramaCapacitacion($app);
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
