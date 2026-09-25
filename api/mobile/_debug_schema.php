<?php
require_once __DIR__ . '/_bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
$tables = ['empresa','trabajadores','usuarios','departamentos','cargos','provincia','municipio','bancos','submayor_vacaciones','asistencias','vacaciones','documentos','list_recursos'];
foreach ($tables as $t) {
    echo "\n=== $t ===\n";
    try {
        $rows = $app->db->fetchAll("SHOW COLUMNS FROM $t");
        foreach ($rows as $r) echo "  {$r['Field']}\n";
    } catch (Exception $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
