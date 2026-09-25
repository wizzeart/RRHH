<?php
// Test file para debugging del API calendario
session_start();

// Ver si hay sesión
echo "=== TEST CALENDARIO API ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Session vars: " . json_encode($_SESSION) . "\n";
echo "User ID (from session): " . ($_SESSION['guser_id'] ?? 'NO SET') . "\n\n";

// Ahora llamar al API interno
include(__DIR__ . '/includes/config.php');
include(INCLUDES . DS . 'functions.php');
init_app();

$app = new App();
echo "App user_id: " . ($app->user_id ?: 'EMPTY') . "\n";
echo "App empresa_id: " . ($app->empresa_id ?: 'EMPTY') . "\n\n";

// Si hay user_id, llamar directamente el método
if ($app->user_id) {
    include_once(BASE_CLASS . '/mdl.SubmayorVacaciones.php');
    $mdl = new SubmayorVacaciones($app);
    
    // Simular la llamada del API
    $_GET['year'] = date('Y');
    $result = $mdl->api($_GET);
    
} else {
    echo "ERROR: No user_id available! Session is not valid.\n";
    echo "This is why the API is failing.\n";
}
?>
