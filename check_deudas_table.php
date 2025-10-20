<?php
// Comprueba si la tabla deudas_trabajador existe y muestra su estructura.
require_once __DIR__ . '/classes/Sql.class.php';

$sql = new Sql();

try {
    $res = $sql->select("SHOW TABLES LIKE 'deudas_trabajador'");
    if (empty($res)) {
        echo "Tabla deudas_trabajador no encontrada. Puedes crearla con el archivo sql/deudas_trabajador.sql\n";
    } else {
        $cols = $sql->select("DESCRIBE deudas_trabajador");
        echo "Tabla deudas_trabajador encontrada. Estructura:\n";
        foreach ($cols as $c) {
            echo $c['Field'] . "\t" . $c['Type'] . "\t" . $c['Null'] . "\t" . $c['Key'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error comprobando la tabla: " . $e->getMessage();
}
