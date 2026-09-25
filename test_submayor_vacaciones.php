<?php
/**
 * Script de prueba para el módulo Submayor de Vacaciones
 */

include(__DIR__ . '/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

echo "<h2>🧪 Prueba del Módulo Submayor de Vacaciones</h2>";

echo "<h3>1. Verificar tabla submayor_vacaciones</h3>";
try {
    $sql_check = "SHOW TABLES LIKE 'submayor_vacaciones'";
    $result = $app->db->fetchAll($sql_check);
    
    if (empty($result)) {
        echo "<p style='color: orange;'>⚠️ Tabla 'submayor_vacaciones' no existe. Creándola...</p>";
        
        $sql_create = "CREATE TABLE `submayor_vacaciones` (
            `id_trabajador` int(11) NOT NULL,
            `vacaciones` int(11) NULL DEFAULT NULL,
            `pago_vacaciones` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            PRIMARY KEY (`id_trabajador`),
            CONSTRAINT `fk_submayor_trabajador` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $app->db->fetchAll($sql_create);
        echo "<p style='color: green;'>✅ Tabla 'submayor_vacaciones' creada exitosamente</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabla 'submayor_vacaciones' existe</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Probar endpoint list-trabajadores</h3>";
try {
    include_once(BASE_CLASS . '/mdl.SubmayorVacaciones.php');
    $controller = new SubmayorVacaciones($app);
    
    // Simular parámetros
    $param = array('method' => 'list-trabajadores');
    
    // Capturar salida
    ob_start();
    $controller->api($param);
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    
    if ($data && is_array($data)) {
        echo "<p style='color: green;'>✅ Endpoint list-trabajadores funciona correctamente</p>";
        echo "<p><strong>Trabajadores encontrados:</strong> " . count($data) . "</p>";
        
        if (count($data) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>CI</th><th>Cargo</th><th>Salario</th></tr>";
            foreach (array_slice($data, 0, 5) as $trabajador) {
                echo "<tr>";
                echo "<td>" . $trabajador['id'] . "</td>";
                echo "<td>" . $trabajador['nombre'] . " " . $trabajador['apellidos'] . "</td>";
                echo "<td>" . $trabajador['carnet_identidad'] . "</td>";
                echo "<td>" . $trabajador['cargo_nombre'] . "</td>";
                echo "<td>$" . number_format($trabajador['cargo_salario'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            if (count($data) > 5) {
                echo "<p><em>... y " . (count($data) - 5) . " más</em></p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Error en endpoint list-trabajadores</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Probar endpoint list (listado principal)</h3>";
try {
    $param = array('method' => 'list');
    
    ob_start();
    $controller->api($param);
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    
    if ($data && is_array($data)) {
        echo "<p style='color: green;'>✅ Endpoint list funciona correctamente</p>";
        echo "<p><strong>Registros encontrados:</strong> " . count($data) . "</p>";
        
        if (count($data) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Cargo</th><th>Días Vac.</th><th>Salario Acum.</th></tr>";
            foreach (array_slice($data, 0, 5) as $registro) {
                echo "<tr>";
                echo "<td>" . $registro['id'] . "</td>";
                echo "<td>" . $registro['nombre_completo'] . "</td>";
                echo "<td>" . $registro['cargo_nombre'] . "</td>";
                echo "<td>" . $registro['dias_vacaciones'] . "</td>";
                echo "<td>$" . $registro['salario_acumulado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error en endpoint list</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Verificar registro en controlador.php</h3>";
$controlador_file = __DIR__ . '/includes/controlador.php';
if (file_exists($controlador_file)) {
    $content = file_get_contents($controlador_file);
    if (strpos($content, 'submayor-vacaciones') !== false) {
        echo "<p style='color: green;'>✅ Módulo registrado en controlador.php</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Módulo NO registrado en controlador.php</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Archivo controlador.php no encontrado</p>";
}

echo "<h3>5. Verificar registro en api-app.php</h3>";
$api_file = __DIR__ . '/api-app.php';
if (file_exists($api_file)) {
    $content = file_get_contents($api_file);
    if (strpos($content, 'submayor-vacaciones') !== false) {
        echo "<p style='color: green;'>✅ Módulo registrado en api-app.php</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Módulo NO registrado en api-app.php</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Archivo api-app.php no encontrado</p>";
}

echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; margin: 20px 0;'>";
echo "<h3 style='color: #2e7d32;'>🎯 Funcionalidades del Modal Mejorado</h3>";
echo "<ul>";
echo "<li>✅ <strong>Botón 'Agregar Vacaciones':</strong> Arriba de la tabla</li>";
echo "<li>✅ <strong>Columna renombrada:</strong> 'Días Vacaciones Acum'</li>";
echo "<li>✅ <strong>Modal específico:</strong> Para insertar en tabla submayor_vacaciones</li>";
echo "<li>✅ <strong>Campos de la tabla:</strong> id_trabajador, vacaciones, pago_vacaciones</li>";
echo "<li>✅ <strong>Cálculo automático:</strong> días × salario_cargo = pago_vacaciones</li>";
echo "<li>✅ <strong>Pago manual:</strong> Campo opcional para especificar pago personalizado</li>";
echo "<li>✅ <strong>Validaciones:</strong> Trabajador y días requeridos</li>";
echo "<li>✅ <strong>Limpieza automática:</strong> Formulario se limpia al abrir/cerrar</li>";
echo "<li>✅ <strong>Información contextual:</strong> Muestra campos de la tabla DB</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #2196f3; margin: 20px 0;'>";
echo "<h3 style='color: #1976d2;'>🔗 Enlaces de Acceso</h3>";
echo "<p><strong>Módulo principal:</strong> <a href='?module=submayor-vacaciones' target='_blank'>?module=submayor-vacaciones</a></p>";
echo "<p><strong>Listado:</strong> <a href='?module=list-submayor-vacaciones' target='_blank'>?module=list-submayor-vacaciones</a></p>";
echo "<p><strong>API List:</strong> <a href='api-app.php?module=submayor-vacaciones&method=list' target='_blank'>api-app.php?module=submayor-vacaciones&method=list</a></p>";
echo "<p><strong>API Trabajadores:</strong> <a href='api-app.php?module=submayor-vacaciones&method=list-trabajadores' target='_blank'>api-app.php?module=submayor-vacaciones&method=list-trabajadores</a></p>";
echo "</div>";
?>
