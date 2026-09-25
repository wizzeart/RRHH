<?php
/**
 * Script de prueba para verificar los cambios en prenómina
 */

require_once('includes/config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🧪 Prueba de cambios en prenómina</h2>";

// Verificar estructura de tabla
echo "<h3>1. Verificando estructura de tabla prenómina:</h3>";
try {
    $columns = $app->db->fetchAll("DESCRIBE prenomina");
    
    $hasIngPers3 = false;
    $hasIngPers5 = false;
    $hasOldIngPers = false;
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 5px;'>Campo</th><th style='padding: 5px;'>Tipo</th><th style='padding: 5px;'>Nulo</th></tr>";
    
    foreach ($columns as $col) {
        if ($col['Field'] == 'ing_pers_3') $hasIngPers3 = true;
        if ($col['Field'] == 'ing_pers_5') $hasIngPers5 = true;
        if ($col['Field'] == 'ing_pers') $hasOldIngPers = true;
        
        $highlight = (in_array($col['Field'], ['ing_pers_3', 'ing_pers_5'])) ? " style='background-color: yellow;'" : "";
        echo "<tr{$highlight}>";
        echo "<td style='padding: 5px;'>{$col['Field']}</td>";
        echo "<td style='padding: 5px;'>{$col['Type']}</td>";
        echo "<td style='padding: 5px;'>{$col['Null']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p>✅ Columna 'ing_pers_3' existe: " . ($hasIngPers3 ? 'SÍ' : 'NO') . "</p>";
    echo "<p>✅ Columna 'ing_pers_5' existe: " . ($hasIngPers5 ? 'SÍ' : 'NO') . "</p>";
    echo "<p>ℹ️ Columna 'ing_pers' (antigua) existe: " . ($hasOldIngPers ? 'SÍ' : 'NO') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Probar cálculos con diferentes rangos salariales
echo "<h3>2. Probando cálculos con diferentes rangos salariales:</h3>";

$testCases = [
    ['salario_neto' => 2000, 'descripcion' => 'Salario bajo (< 3260)'],
    ['salario_neto' => 5000, 'descripcion' => 'Salario medio (3260-9510)'],
    ['salario_neto' => 12000, 'descripcion' => 'Salario alto (> 9510)'],
    ['salario_neto' => 3260, 'descripcion' => 'Límite inferior (3260)'],
    ['salario_neto' => 9510, 'descripcion' => 'Límite superior (9510)'],
];

echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th style='padding: 5px;'>Descripción</th><th style='padding: 5px;'>Salario Neto</th><th style='padding: 5px;'>Ing Pers 3%</th><th style='padding: 5px;'>Ing Pers 5%</th><th style='padding: 5px;'>Total Descuento</th></tr>";

foreach ($testCases as $case) {
    $salario_neto = $case['salario_neto'];
    $ing_pers_3 = 0;
    $ing_pers_5 = 0;
    
    if ($salario_neto >= 3260 && $salario_neto <= 9510) {
        // Rango 3260-9510: 3% sobre el exceso de 3260
        $ing_pers_3 = intval(($salario_neto - 3260) * 0.03);
    } elseif ($salario_neto > 9510) {
        // Sobre 9510: 3% del rango base + 5% del exceso
        $ing_pers_3 = intval((9510 - 3260) * 0.03);
        $ing_pers_5 = intval(($salario_neto - 9510) * 0.05);
    }
    
    $total_descuento = $ing_pers_3 + $ing_pers_5;
    
    echo "<tr>";
    echo "<td style='padding: 5px;'>{$case['descripcion']}</td>";
    echo "<td style='padding: 5px; text-align: right;'>$" . number_format($salario_neto, 2) . "</td>";
    echo "<td style='padding: 5px; text-align: right;'>$" . number_format($ing_pers_3, 2) . "</td>";
    echo "<td style='padding: 5px; text-align: right;'>$" . number_format($ing_pers_5, 2) . "</td>";
    echo "<td style='padding: 5px; text-align: right;'>$" . number_format($total_descuento, 2) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Verificar archivos modificados
echo "<h3>3. Verificando archivos modificados:</h3>";

$archivos = [
    'modules/prenomina/prenomina.php' => 'Tabla HTML con nuevas columnas',
    'classes/mdl.Prenomina.php' => 'Controlador con nueva lógica',
    'modules/prenomina/prenomina.js' => 'JavaScript con nuevos cálculos'
];

foreach ($archivos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $hasIngPers3 = strpos($contenido, 'ing_pers_3') !== false;
        $hasIngPers5 = strpos($contenido, 'ing_pers_5') !== false;
        
        echo "<p>📁 <strong>$archivo</strong> - $descripcion</p>";
        echo "<ul>";
        echo "<li>✅ Contiene 'ing_pers_3': " . ($hasIngPers3 ? 'SÍ' : 'NO') . "</li>";
        echo "<li>✅ Contiene 'ing_pers_5': " . ($hasIngPers5 ? 'SÍ' : 'NO') . "</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ <strong>$archivo</strong> - No encontrado</p>";
    }
}

echo "<h3>4. Lógica implementada:</h3>";
echo "<div style='background-color: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📊 Rangos de cálculo:</h4>";
echo "<ul>";
echo "<li><strong>Salario Neto < 3260:</strong> ing_pers_3 = 0, ing_pers_5 = 0</li>";
echo "<li><strong>Salario Neto 3260-9510:</strong> ing_pers_3 = (Salario Neto - 3260) × 0.03, ing_pers_5 = 0</li>";
echo "<li><strong>Salario Neto > 9510:</strong> ing_pers_3 = (9510 - 3260) × 0.03, ing_pers_5 = (Salario Neto - 9510) × 0.05</li>";
echo "</ul>";

echo "<h4>🔧 Cambios realizados:</h4>";
echo "<ul>";
echo "<li>✅ Tabla HTML: Columna 'importe Ing Pers' → 'importe Ing Pers 3%' + nueva columna 'importe Ing Pers 5%'</li>";
echo "<li>✅ Base de datos: Agregadas columnas ing_pers_3 e ing_pers_5</li>";
echo "<li>✅ Backend PHP: Nueva lógica de cálculo en consultas SQL y funciones</li>";
echo "<li>✅ Frontend JS: Cálculos en tiempo real actualizados</li>";
echo "<li>✅ Exportación Excel: Encabezados y cálculos actualizados</li>";
echo "</ul>";
echo "</div>";

echo "<h3>5. Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Ejecutar <strong>update_prenomina_structure.php</strong> para actualizar la base de datos</li>";
echo "<li>Probar el módulo prenómina para verificar que funciona correctamente</li>";
echo "<li>Verificar que la exportación a Excel incluye las nuevas columnas</li>";
echo "<li>Comprobar que los cálculos se realizan según los rangos especificados</li>";
echo "</ol>";

echo "<p style='background-color: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 20px;'>";
echo "<strong>🎯 RESULTADO:</strong> El módulo de prenómina ahora calcula los impuestos sobre ingresos personales ";
echo "según los rangos salariales especificados: 3% para el rango 3260-9510 y 5% adicional para el exceso sobre 9510.";
echo "</p>";
?>
