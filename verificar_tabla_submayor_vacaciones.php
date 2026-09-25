<?php
/**
 * Script para verificar y crear la tabla submayor_vacaciones si no existe
 */

include(__DIR__ . '/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

echo "<h2>🔍 Verificación de tabla submayor_vacaciones</h2>";

try {
    // Verificar si la tabla existe
    $sql_check = "SHOW TABLES LIKE 'submayor_vacaciones'";
    $result = $app->db->fetchAll($sql_check);
    
    if (empty($result)) {
        echo "<p style='color: orange;'>⚠️ La tabla 'submayor_vacaciones' no existe. Creándola...</p>";
        
        // Crear la tabla según la estructura proporcionada
        $sql_create = "CREATE TABLE `submayor_vacaciones` (
            `id_trabajador` int(11) NOT NULL,
            `vacaciones` int(11) NULL DEFAULT NULL,
            `pago_vacaciones` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            PRIMARY KEY (`id_trabajador`),
            CONSTRAINT `fk_submayor_trabajador` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $result = $app->db->fetchAll($sql_create);
        echo "<p style='color: green;'>✅ Tabla 'submayor_vacaciones' creada exitosamente</p>";
        
    } else {
        echo "<p style='color: green;'>✅ La tabla 'submayor_vacaciones' ya existe</p>";
    }
    
    // Verificar la estructura de la tabla
    echo "<h3>📋 Estructura de la tabla:</h3>";
    $sql_describe = "DESCRIBE submayor_vacaciones";
    $columns = $app->db->fetchAll($sql_describe);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 5px;'>Campo</th><th style='padding: 5px;'>Tipo</th><th style='padding: 5px;'>Nulo</th><th style='padding: 5px;'>Clave</th><th style='padding: 5px;'>Por defecto</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>{$column['Field']}</td>";
        echo "<td style='padding: 5px;'>{$column['Type']}</td>";
        echo "<td style='padding: 5px;'>{$column['Null']}</td>";
        echo "<td style='padding: 5px;'>{$column['Key']}</td>";
        echo "<td style='padding: 5px;'>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar algunos trabajadores de ejemplo
    echo "<h3>👥 Trabajadores disponibles (primeros 10):</h3>";
    $sql_trabajadores = "SELECT id, nombre, apellidos, apellidos_segundos, vacaciones_acc FROM trabajadores WHERE trabajador_eliminado = '0' LIMIT 10";
    $trabajadores = $app->db->fetchAll($sql_trabajadores);
    
    if (!empty($trabajadores)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th style='padding: 5px;'>ID</th><th style='padding: 5px;'>Nombre Completo</th><th style='padding: 5px;'>Vacaciones Disponibles</th></tr>";
        
        foreach ($trabajadores as $trabajador) {
            $nombreCompleto = trim($trabajador['nombre'] . ' ' . $trabajador['apellidos'] . ' ' . ($trabajador['apellidos_segundos'] ?? ''));
            echo "<tr>";
            echo "<td style='padding: 5px;'>{$trabajador['id']}</td>";
            echo "<td style='padding: 5px;'>{$nombreCompleto}</td>";
            echo "<td style='padding: 5px;'>" . ($trabajador['vacaciones_acc'] ?? '0') . " días</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron trabajadores activos</p>";
    }
    
    // Verificar registros existentes en submayor_vacaciones
    echo "<h3>📊 Registros existentes en submayor_vacaciones:</h3>";
    $sql_submayor = "SELECT COUNT(*) as total FROM submayor_vacaciones";
    $count = $app->db->fetchRow($sql_submayor);
    
    echo "<p><strong>Total de registros:</strong> {$count['total']}</p>";
    
    if ($count['total'] > 0) {
        $sql_sample = "SELECT sv.*, CONCAT(t.nombre, ' ', t.apellidos, ' ', COALESCE(t.apellidos_segundos, '')) as nombre_completo 
                       FROM submayor_vacaciones sv 
                       LEFT JOIN trabajadores t ON sv.id_trabajador = t.id 
                       LIMIT 5";
        $sample = $app->db->fetchAll($sql_sample);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th style='padding: 5px;'>ID Trabajador</th><th style='padding: 5px;'>Nombre</th><th style='padding: 5px;'>Días Vacaciones</th><th style='padding: 5px;'>Pago Vacaciones</th></tr>";
        
        foreach ($sample as $registro) {
            echo "<tr>";
            echo "<td style='padding: 5px;'>{$registro['id_trabajador']}</td>";
            echo "<td style='padding: 5px;'>{$registro['nombre_completo']}</td>";
            echo "<td style='padding: 5px;'>{$registro['vacaciones']}</td>";
            echo "<td style='padding: 5px;'>{$registro['pago_vacaciones']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Verificación completada</h3>";
    echo "<p><strong>Estado:</strong> El módulo Submayor de Vacaciones está listo para usar</p>";
    echo "<p><strong>Acceso:</strong> Menú Contabilidad → Submayor de Vacaciones</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Detalles del error:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

$app->close();
?>
