<?php
// Script para verificar datos en BD y crear datos de prueba si es necesario
session_start();

include(__DIR__ . '/includes/config.php');
include(INCLUDES . DS . 'functions.php');
init_app();

$app = new App();
$db = $app->db;

echo "=== DIAGNÓSTICO CALENDARIO ===\n\n";

// 1. Verificar trabajadores con cumpleaños
echo "1. Trabajadores con fecha_nacimiento:\n";
$sql = "SELECT COUNT(*) as total FROM trabajadores WHERE fecha_nacimiento IS NOT NULL AND (trabajador_eliminado = 0 OR trabajador_eliminado IS NULL)";
$result = $db->fetchAll($sql);
$result = isset($result[0]) ? $result[0] : ['total' => 0];
echo "   Total: " . $result['total'] . "\n\n";

// 2. Verificar vacaciones registradas
echo "2. Registros de vacaciones en el año " . date('Y') . ":\n";
$sql = "SELECT COUNT(*) as total FROM registro_vacaciones WHERE YEAR(fecha_inicio) = " . date('Y');
$result = $db->fetchAll($sql);
$result = isset($result[0]) ? $result[0] : ['total' => 0];
echo "   Total: " . $result['total'] . "\n\n";

// 3. Ver algunos registros de ejemplo
echo "3. Ejemplos de trabajadores:\n";
$sql = "SELECT id, nombre, apellidos, fecha_nacimiento FROM trabajadores WHERE (trabajador_eliminado = 0 OR trabajador_eliminado IS NULL) LIMIT 5";
$rows = $db->fetchAll($sql);
foreach ($rows as $row) {
    echo "   - {$row['nombre']} {$row['apellidos']}: " . ($row['fecha_nacimiento'] ? $row['fecha_nacimiento'] : 'SIN FECHA') . "\n";
}

echo "\n4. Ejemplos de vacaciones:\n";
$sql = "SELECT sv.id, t.nombre, t.apellidos, sv.fecha_inicio, sv.fecha_fin 
         FROM registro_vacaciones sv
         LEFT JOIN trabajadores t ON sv.trabajador_id = t.id
         WHERE YEAR(sv.fecha_inicio) = " . date('Y') . "
         LIMIT 5";
$rows = $db->fetchAll($sql);
if (count($rows) > 0) {
    foreach ($rows as $row) {
        echo "   - {$row['nombre']} {$row['apellidos']}: {$row['fecha_inicio']} a {$row['fecha_fin']}\n";
    }
} else {
    echo "   (Sin registros de vacaciones en el año actual)\n";
}

echo "\n";
?>
