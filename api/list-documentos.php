<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$mysqli = new mysqli(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
if ($mysqli->connect_errno) {
    echo json_encode(['status' => 0, 'error' => $mysqli->connect_error]);
    exit;
}

$sql = "SELECT id, nombre, path, fecha_modificado, firmado FROM documentos ORDER BY fecha_modificado DESC";
$res = $mysqli->query($sql);
$data = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
