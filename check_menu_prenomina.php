<?php
// Script temporal para verificar la configuración del menú de prenómina
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔍 Verificación de configuración de menú prenómina</h2>";

// Verificar empresa activa
echo "<h3>1. Empresa activa:</h3>";
echo "<p>ID: " . ($app->empresa_id ?? 'NULL') . "</p>";
echo "<p>Nombre: " . ($app->empresa_nombre ?? 'NULL') . "</p>";

// Verificar si es Custodios
$esCustodios = ($app->empresa_id == 3 || (isset($app->empresa_nombre) && strtolower($app->empresa_nombre) == 'custodios'));
echo "<p>¿Es Custodios?: " . ($esCustodios ? 'SÍ' : 'NO') . "</p>";

// Verificar rol del usuario
echo "<h3>2. Usuario actual:</h3>";
echo "<p>Rol: " . ($app->rol ?? 'NULL') . "</p>";
echo "<p>Usuario ID: " . ($app->usuario_id ?? 'NULL') . "</p>";

// Mostrar información de empresas disponibles
echo "<h3>3. Empresas en la base de datos:</h3>";
try {
    $empresas = $app->db->fetchAll("SELECT id, nombre FROM empresas ORDER BY id");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th></tr>";
    foreach ($empresas as $empresa) {
        $highlight = ($empresa['id'] == $app->empresa_id) ? " style='background-color: yellow;'" : "";
        echo "<tr{$highlight}><td>{$empresa['id']}</td><td>{$empresa['nombre']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al obtener empresas: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Condición para ocultar prenómina:</h3>";
echo "<p>Prenómina debe ocultarse cuando empresa_id = 3 (Custodios)</p>";
echo "<p>Estado actual: " . ($esCustodios ? "OCULTAR prenómina" : "MOSTRAR prenómina") . "</p>";
?>
