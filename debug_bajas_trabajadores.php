<?php
// Debug para verificar trabajadores dados de baja
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔍 Debug de Trabajadores Dados de Baja</h2>";

try {
    // 1. Verificar estructura de la tabla trabajadores
    echo "<h3>1. Estructura del campo fecha_baja:</h3>";
    $columns = $app->db->fetchAll("SHOW COLUMNS FROM trabajadores WHERE Field = 'fecha_baja'");
    
    if (!empty($columns)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ El campo 'fecha_baja' no existe en la tabla trabajadores</p>";
    }
    
    // 2. Contar todos los trabajadores
    echo "<h3>2. Estadísticas de trabajadores:</h3>";
    $total = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores");
    echo "<p><strong>Total de trabajadores:</strong> " . $total['total'] . "</p>";
    
    $activos = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores WHERE trabajador_eliminado = '0'");
    echo "<p><strong>Trabajadores activos (no eliminados):</strong> " . $activos['total'] . "</p>";
    
    $eliminados = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores WHERE trabajador_eliminado = '1'");
    echo "<p><strong>Trabajadores eliminados:</strong> " . $eliminados['total'] . "</p>";
    
    // 3. Verificar trabajadores con fecha_baja
    echo "<h3>3. Trabajadores con fecha_baja:</h3>";
    
    // Diferentes condiciones para fecha_baja
    $con_fecha_baja_not_null = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores WHERE fecha_baja IS NOT NULL");
    echo "<p><strong>Con fecha_baja IS NOT NULL:</strong> " . $con_fecha_baja_not_null['total'] . "</p>";
    
    $con_fecha_baja_no_vacia = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores WHERE fecha_baja != ''");
    echo "<p><strong>Con fecha_baja != '':</strong> " . $con_fecha_baja_no_vacia['total'] . "</p>";
    
    $con_fecha_baja_ambas = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores WHERE fecha_baja IS NOT NULL AND fecha_baja != ''");
    echo "<p><strong>Con fecha_baja IS NOT NULL AND fecha_baja != '':</strong> " . $con_fecha_baja_ambas['total'] . "</p>";
    
    // 4. Mostrar algunos ejemplos de trabajadores con fecha_baja
    echo "<h3>4. Ejemplos de trabajadores con fecha_baja:</h3>";
    $ejemplos = $app->db->fetchAll("SELECT id, nombre, apellidos, fecha_baja, estatus, trabajador_eliminado FROM trabajadores WHERE fecha_baja IS NOT NULL AND fecha_baja != '' LIMIT 10");
    
    if (!empty($ejemplos)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Nombre</th><th>Apellidos</th><th>Fecha Baja</th><th>Estatus</th><th>Eliminado</th>";
        echo "</tr>";
        
        foreach ($ejemplos as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['apellidos']}</td>";
            echo "<td>{$row['fecha_baja']}</td>";
            echo "<td>{$row['estatus']}</td>";
            echo "<td>{$row['trabajador_eliminado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron trabajadores con fecha_baja válida</p>";
    }
    
    // 5. Probar la consulta exacta del método _list_bajas
    echo "<h3>5. Probando consulta del método _list_bajas:</h3>";
    $sql_list_bajas = "SELECT 
            t.id,
            t.cargos_id,
            t.nombre,
            t.apellidos,
            t.carnet_identidad,
            t.sexo,
            t.edad,
            t.direccion,
            t.telefono,
            t.email,
            t.nivel_educacional,
            t.fecha_contratacion,
            t.fecha_baja,
            t.estatus,
            t.bolsa_empleo_id,
            t.foto,
            c.nombre as cargo_nombre
            FROM trabajadores t 
            LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
            WHERE t.fecha_baja IS NOT NULL 
            AND t.fecha_baja != ''
            ORDER BY t.fecha_baja DESC
            LIMIT 5";
    
    echo "<p><strong>Consulta SQL:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px;'>" . htmlspecialchars($sql_list_bajas) . "</pre>";
    
    $resultado_bajas = $app->db->fetchAll($sql_list_bajas);
    echo "<p><strong>Resultados encontrados:</strong> " . count($resultado_bajas) . "</p>";
    
    if (!empty($resultado_bajas)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Nombre</th><th>Apellidos</th><th>CI</th><th>Cargo</th><th>Fecha Baja</th>";
        echo "</tr>";
        
        foreach ($resultado_bajas as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['apellidos']}</td>";
            echo "<td>{$row['carnet_identidad']}</td>";
            echo "<td>{$row['cargo_nombre']}</td>";
            echo "<td>{$row['fecha_baja']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 6. Verificar si hay trabajadores con estatus 'inactivo'
    echo "<h3>6. Trabajadores con estatus 'inactivo':</h3>";
    $inactivos = $app->db->fetchAll("SELECT id, nombre, apellidos, estatus, fecha_baja FROM trabajadores WHERE estatus = 'inactivo' LIMIT 5");
    
    echo "<p><strong>Trabajadores con estatus 'inactivo':</strong> " . count($inactivos) . "</p>";
    
    if (!empty($inactivos)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Nombre</th><th>Apellidos</th><th>Estatus</th><th>Fecha Baja</th>";
        echo "</tr>";
        
        foreach ($inactivos as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['apellidos']}</td>";
            echo "<td>{$row['estatus']}</td>";
            echo "<td>" . ($row['fecha_baja'] ?: '<em>NULL/Vacío</em>') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>📋 Diagnóstico</h3>";
echo "<p>Este script te ayudará a identificar por qué no aparecen trabajadores dados de baja.</p>";
echo "<p><strong>Posibles causas:</strong></p>";
echo "<ul>";
echo "<li>Campo fecha_baja está NULL o vacío</li>";
echo "<li>Trabajadores marcados como 'inactivo' pero sin fecha_baja</li>";
echo "<li>Problema en la consulta SQL</li>";
echo "<li>Problema en el JOIN con la tabla cargos</li>";
echo "</ul>";
?>
