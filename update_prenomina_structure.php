<?php
/**
 * Script para actualizar la estructura de la tabla prenomina
 * Agregar columnas ing_pers_3 e ing_pers_5
 */

require_once('includes/config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔧 Actualización de estructura de prenómina</h2>";

try {
    // Verificar estructura actual
    echo "<h3>1. Verificando estructura actual:</h3>";
    $columns = $app->db->fetchAll("DESCRIBE prenomina");
    
    $hasIngPers3 = false;
    $hasIngPers5 = false;
    $hasOldIngPers = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] == 'ing_pers_3') $hasIngPers3 = true;
        if ($col['Field'] == 'ing_pers_5') $hasIngPers5 = true;
        if ($col['Field'] == 'ing_pers') $hasOldIngPers = true;
    }
    
    echo "<p>✅ Columna 'ing_pers' existe: " . ($hasOldIngPers ? 'SÍ' : 'NO') . "</p>";
    echo "<p>✅ Columna 'ing_pers_3' existe: " . ($hasIngPers3 ? 'SÍ' : 'NO') . "</p>";
    echo "<p>✅ Columna 'ing_pers_5' existe: " . ($hasIngPers5 ? 'SÍ' : 'NO') . "</p>";
    
    // Agregar nuevas columnas si no existen
    echo "<h3>2. Agregando nuevas columnas:</h3>";
    
    if (!$hasIngPers3) {
        $sql = "ALTER TABLE prenomina ADD COLUMN ing_pers_3 DECIMAL(10,2) DEFAULT 0 AFTER seg_social";
        $app->db->fetchAll($sql);
        echo "<p>✅ Columna 'ing_pers_3' agregada</p>";
    } else {
        echo "<p>⚠️ Columna 'ing_pers_3' ya existe</p>";
    }
    
    if (!$hasIngPers5) {
        $sql = "ALTER TABLE prenomina ADD COLUMN ing_pers_5 DECIMAL(10,2) DEFAULT 0 AFTER ing_pers_3";
        $app->db->fetchAll($sql);
        echo "<p>✅ Columna 'ing_pers_5' agregada</p>";
    } else {
        echo "<p>⚠️ Columna 'ing_pers_5' ya existe</p>";
    }
    
    // Migrar datos existentes si es necesario
    echo "<h3>3. Migrando datos existentes:</h3>";
    
    if ($hasOldIngPers) {
        // Obtener registros con datos en ing_pers
        $records = $app->db->fetchAll("SELECT id, salario_neto, ing_pers FROM prenomina WHERE ing_pers > 0");
        
        if (count($records) > 0) {
            echo "<p>📊 Encontrados " . count($records) . " registros con datos en 'ing_pers'</p>";
            
            foreach ($records as $record) {
                $salarioNeto = floatval($record['salario_neto']);
                $ingPers3 = 0;
                $ingPers5 = 0;
                
                // Aplicar nueva lógica de cálculo
                if ($salarioNeto >= 3260 && $salarioNeto <= 9510) {
                    // Rango 3260-9510: 3% sobre el rango
                    $ingPers3 = intval(($salarioNeto - 3260) * 0.03);
                } elseif ($salarioNeto > 9510) {
                    // Sobre 9510: 3% del rango base + 5% del exceso
                    $ingPers3 = intval((9510 - 3260) * 0.03);
                    $ingPers5 = intval(($salarioNeto - 9510) * 0.05);
                }
                
                // Actualizar registro
                $app->db->update('prenomina', 
                    ['ing_pers_3' => $ingPers3, 'ing_pers_5' => $ingPers5],
                    ['id' => $record['id']]
                );
            }
            
            echo "<p>✅ Datos migrados correctamente</p>";
        } else {
            echo "<p>ℹ️ No hay datos para migrar</p>";
        }
    }
    
    // Mostrar estructura final
    echo "<h3>4. Estructura final:</h3>";
    $finalColumns = $app->db->fetchAll("DESCRIBE prenomina");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 5px;'>Campo</th><th style='padding: 5px;'>Tipo</th><th style='padding: 5px;'>Nulo</th><th style='padding: 5px;'>Default</th></tr>";
    foreach ($finalColumns as $col) {
        $highlight = (in_array($col['Field'], ['ing_pers_3', 'ing_pers_5'])) ? " style='background-color: yellow;'" : "";
        echo "<tr{$highlight}>";
        echo "<td style='padding: 5px;'>{$col['Field']}</td>";
        echo "<td style='padding: 5px;'>{$col['Type']}</td>";
        echo "<td style='padding: 5px;'>{$col['Null']}</td>";
        echo "<td style='padding: 5px;'>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>5. Lógica de cálculo implementada:</h3>";
    echo "<ul>";
    echo "<li><strong>Salario Neto < 3260:</strong> ing_pers_3 = 0, ing_pers_5 = 0</li>";
    echo "<li><strong>Salario Neto 3260-9510:</strong> ing_pers_3 = (Salario Neto - 3260) * 0.03, ing_pers_5 = 0</li>";
    echo "<li><strong>Salario Neto > 9510:</strong> ing_pers_3 = (9510 - 3260) * 0.03, ing_pers_5 = (Salario Neto - 9510) * 0.05</li>";
    echo "</ul>";
    
    echo "<p style='background-color: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 20px;'>";
    echo "<strong>✅ ESTRUCTURA ACTUALIZADA CORRECTAMENTE</strong><br>";
    echo "La tabla prenomina ahora tiene las columnas ing_pers_3 e ing_pers_5 para el nuevo cálculo.";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
