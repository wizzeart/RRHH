<?php
// Archivo de prueba para verificar endpoints de estadísticas
session_start();

if (!isset($_SESSION['user_id'])) {
    // Simulación de sesión para pruebas
    $_SESSION['user_id'] = 1;
    $_SESSION['empresa_id'] = 1;
}

// Requerir la clase App
require_once(__DIR__ . '/classes/App.class.php');
$app = new App();

// Cargar módulo de asistencias
require_once(__DIR__ . '/classes/mdl.Asistencias.php');
$asistencias = new Asistencia($app);

// Verificar endpoint
echo "<h2>Prueba de Endpoint de Estadísticas de Asistencias</h2>";
echo "<pre>";

$_GET['method'] = 'estadisticas';
ob_start();
$asistencias->api($_GET);
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</pre>";

echo "<h2>Prueba de Endpoint de Estadísticas de Vacaciones</h2>";
echo "<pre>";

require_once(__DIR__ . '/classes/mdl.SubmayorVacaciones.php');
$vacaciones = new SubmayorVacaciones($app);

$_GET['method'] = 'estadisticas';
ob_start();
$vacaciones->api($_GET);
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</pre>";
?>
