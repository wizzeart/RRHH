<?php
require_once '../classes/Sql.class.php';
require_once '../includes/config.php';

// Initialize database connection
$db = new Sql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, '3306');

// Fetch data from bancos, trabajadores, and prenomina tables
// Filter by empresa_id if provided
$empresaId = isset($_GET['empresa_id']) ? $_GET['empresa_id'] : null;

$query = "
    SELECT 
        t.carnet_identidad,
        b.numero_cuenta_estandar,
        p.salario_pagar
    FROM trabajadores t
    LEFT JOIN bancos b ON t.id = b.trabajador_id
    LEFT JOIN prenomina p ON t.id = p.trabajador_id
    LEFT JOIN departamentos d ON t.departamento_id = d.id
";

// Add WHERE clause if empresa_id is provided
if ($empresaId) {
    $query .= " WHERE t.empresa_id = '" . addslashes($empresaId) . "'";
}

$data = $db->fetchAll($query);

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);