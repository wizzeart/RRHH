<?php
require_once __DIR__ . '/_bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
$tables = [
    'registro_asistencia',
    'registro_asistencia_horas',
    'plan_vacaciones',
    'submayor_vacaciones',
    'documentos_trabajador',
    'recursos',
    'recursos_trabajadores',
    'categorias_recurso',
    'contratos',
    'tarjetas_snc225',
];
foreach ($tables as $t) {
    echo "\n=== $t ===\n";
    try {
        $rows = $app->db->fetchAll("SHOW COLUMNS FROM `$t`");
        foreach ($rows as $r) echo "  {$r['Field']}  ({$r['Type']})\n";
    } catch (Exception $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
    }
}
