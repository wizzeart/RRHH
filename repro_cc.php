<?php
// Script de reproducción para probar el backend de cambiar-contrasena
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Simular entorno
include(__DIR__ . '/includes/config.php');
include(INCLUDES . DS . 'functions.php');
init_app();
$app = new App();

// 2. Obtener un usuario Real para probar
$sql = "SELECT xusuario_id, xusuario FROM usuarios WHERE xeliminado=0 LIMIT 1";
$user = $app->db->fetchRow($sql);

if (!$user) {
    die("No hay usuarios para probar.");
}

$uid = $user['xusuario_id'];
echo "Probando con usuario ID: $uid (" . $user['xusuario'] . ")<br>";

// 3. Simular REQUEST
$_REQUEST['module'] = 'cambiar-contrasena';
$_REQUEST['method'] = 'cambiar';
$_REQUEST['user_id'] = $uid;
$_REQUEST['passwordActual'] = 'CONTRASEÑA_INCORRECTA_' . rand(1000, 9999);
$_REQUEST['passwordNueva'] = 'Nueva1234!';

// 4. Mockear Session para la validación de seguridad
$app->user_id = $uid; // Hacemos que la app crea que somos este usuario

// 5. Incluir el módulo manualmente (como hace api-app.php)
include(BASE_CLASS . '/mdl.CambiarContrasena.php');
$mdl = new CambiarContrasena($app);

// 6. Ejecutar API (capturando salida)
ob_start();
$mdl->api($_REQUEST);
$output = ob_get_clean();

echo "<h3>Resultado API (JSON):</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$json = json_decode($output, true);
if ($json) {
    echo "Status: " . $json['status'] . "<br>";
    echo "Msg: " . $json['msg'] . "<br>";

    if ($json['status'] == 0 && strpos($json['msg'], 'incorrecta') !== false) {
        echo "<h2 style='color:green'>PRUEBA EXITOSA: El sistema RECHAZÓ la contraseña incorrecta.</h2>";
    } else {
        echo "<h2 style='color:red'>FALLO: El sistema NO se comportó como se esperaba.</h2>";
    }
} else {
    echo "<h2 style='color:red'>FALLO: Respuesta inválida (No JSON).</h2>";
}
