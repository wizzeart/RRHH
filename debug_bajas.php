<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/config.php');
include(INCLUDES . DS . 'functions.php');
init_app();
$app = new App();

// Verificar si hay trabajadores con trabajador_eliminado = 1
$sql = "SELECT COUNT(*) as total FROM trabajadores WHERE trabajador_eliminado = 1";
$result = $app->db->fetchOne($sql);
echo "<h2>Depuración de Bajas de Trabajadores</h2>";
echo "<p>Trabajadores con trabajador_eliminado = 1: <strong>" . $result['total'] . "</strong></p>";

// Mostrar los registros
$sql = "SELECT id, nombre, apellidos, carnet_identidad, fecha_baja, trabajador_eliminado 
        FROM trabajadores 
        WHERE trabajador_eliminado = 1";
$trabajadores = $app->db->fetchAll($sql);

echo "<h3>Registros encontrados:</h3>";
echo "<pre>";
print_r($trabajadores);
echo "</pre>";

// Verificar si el método _list_bajas está devolviendo datos
if (method_exists('Trabajador', '_list_bajas')) {
    include_once(BASE_CLASS . '/mdl.Trabajadores.php');
    $trabajador = new Trabajador($app);
    $data = $trabajador->_list_bajas(array());
    
    echo "<h3>Resultado de _list_bajas():</h3>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} else {
    echo "<p>El método _list_bajas no existe en la clase Trabajador</p>";
}
?>
