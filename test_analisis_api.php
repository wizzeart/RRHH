<?php
// Prueba simple de la API
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    include(__DIR__ . '/includes/config.php');
    include(INCLUDES . DS . 'functions.php');
    init_app();
    $app = new App();

    // Simular llamada a la API
    $_REQUEST['module'] = 'analisis-asistencia';
    $_REQUEST['method'] = 'estadisticas';
    $_REQUEST['mes'] = '2026-01';

    echo json_encode(['debug' => 'Antes de include']);

    include_once(BASE_CLASS . '/mdl.AnalisisAsistencia.php');

    echo json_encode(['debug' => 'Después de include, antes de new']);

    $mdl = new AnalisisAsistencia($app);

    echo json_encode(['debug' => 'Después de new, antes de api']);

    $mdl->api($_REQUEST);

    echo json_encode(['debug' => 'Después de api']);

    $app->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
