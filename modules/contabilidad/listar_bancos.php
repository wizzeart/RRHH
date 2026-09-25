<?php

require_once '../../includes/breadcrumbs.php';
require_once '../../classes/Sql.class.php';

// Crear instancia de la base de datos
$host = 'localhost';
$dbName = 'mb_recursos_humanos';
$user = 'root';
$password = '';
$port = 3306;
$db = new Sql($host, $dbName, $user, $password, $port);

// Consultar datos de las tablas bancos, trabajadores y prenomina
$query = "
    SELECT 
        b.numero_cuenta_estandar, 
        t.carnet_identidad, 
        p.salario_pagar
    FROM bancos b
    LEFT JOIN trabajadores t ON b.trabajador_id = t.id
    LEFT JOIN prenomina p ON b.trabajador_id = p.trabajador_id
";

$data = $db->fetchAll($query);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Bancos</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Listado de Bancos</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Número de Cuenta</th>
                    <th>Carnet de Identidad</th>
                    <th>Salario a Pagar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['numero_cuenta_estandar']) ?></td>
                        <td><?= htmlspecialchars($row['carnet_identidad']) ?></td>
                        <td><?= htmlspecialchars($row['salario_pagar']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>