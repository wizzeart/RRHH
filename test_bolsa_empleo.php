<?php
// Archivo de prueba para verificar la estructura de la tabla bolsa_empleo
require_once 'includes/config.php';
require_once 'includes/app.php';

$app = new App();

echo "<h2>Prueba de estructura de tabla bolsa_empleo</h2>";

try {
    // Verificar si la tabla existe
    $sql = "SHOW TABLES LIKE 'bolsa_empleo'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "<p style='color: red;'>❌ La tabla 'bolsa_empleo' NO existe</p>";
        
        // Crear la tabla si no existe
        $createTableSQL = "
        CREATE TABLE `bolsa_empleo` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `apellidos` varchar(100) NOT NULL,
            `curriculum` varchar(255) DEFAULT NULL,
            `cargo_postulado_id` int(11) NOT NULL,
            `telefono` varchar(20) NOT NULL,
            `email` varchar(100) NOT NULL,
            `fecha_registro` date NOT NULL,
            `estatus` varchar(20) NOT NULL DEFAULT 'pendiente',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $app->db->query($createTableSQL);
        echo "<p style='color: green;'>✅ Tabla 'bolsa_empleo' creada exitosamente</p>";
    } else {
        echo "<p style='color: green;'>✅ La tabla 'bolsa_empleo' existe</p>";
    }
    
    // Mostrar estructura de la tabla
    $sql = "DESCRIBE bolsa_empleo";
    $columns = $app->db->fetchAll($sql);
    
    echo "<h3>Estructura de la tabla:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Probar inserción de datos de prueba
    echo "<h3>Prueba de inserción:</h3>";
    
    $testData = array(
        'nombre' => 'Juan',
        'apellidos' => 'Pérez',
        'curriculum' => '',
        'cargo_postulado_id' => 1,
        'telefono' => '12345678',
        'email' => 'juan.perez@test.com',
        'fecha_registro' => date('Y-m-d'),
        'estatus' => 'pendiente'
    );
    
    $result = $app->db->insert('bolsa_empleo', $testData);
    
    if ($result) {
        $lastId = $app->db->last_id();
        echo "<p style='color: green;'>✅ Inserción exitosa. ID: " . $lastId . "</p>";
        
        // Eliminar el registro de prueba
        $app->db->delete('bolsa_empleo', array('id' => $lastId));
        echo "<p style='color: blue;'>🗑️ Registro de prueba eliminado</p>";
    } else {
        echo "<p style='color: red;'>❌ Error en la inserción</p>";
    }
    
    // Mostrar registros existentes
    $sql = "SELECT * FROM bolsa_empleo ORDER BY id DESC LIMIT 5";
    $registros = $app->db->fetchAll($sql);
    
    echo "<h3>Últimos 5 registros:</h3>";
    if (empty($registros)) {
        echo "<p>No hay registros en la tabla</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Apellidos</th><th>Email</th><th>Teléfono</th><th>Cargo ID</th><th>Fecha</th><th>Estatus</th></tr>";
        
        foreach ($registros as $reg) {
            echo "<tr>";
            echo "<td>" . $reg['id'] . "</td>";
            echo "<td>" . $reg['nombre'] . "</td>";
            echo "<td>" . $reg['apellidos'] . "</td>";
            echo "<td>" . $reg['email'] . "</td>";
            echo "<td>" . $reg['telefono'] . "</td>";
            echo "<td>" . $reg['cargo_postulado_id'] . "</td>";
            echo "<td>" . $reg['fecha_registro'] . "</td>";
            echo "<td>" . $reg['estatus'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<br><a href='index.php?module=bolsas_empleos'>← Volver al formulario</a>";
?>
