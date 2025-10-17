<?php
// Script para verificar y (si es necesario) agregar la columna tiempo_trabajado a tarjetas_snc225
require_once(__DIR__ . '/includes/config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();
$db = $app->db;

header('Content-Type: text/plain; charset=utf-8');

echo "== Verificación de tabla tarjetas_snc225 ==\n";

try {
    // 1) Verificar existencia de la tabla
    $tbl = $db->fetchAll("SHOW TABLES LIKE 'tarjetas_snc225'");
    if (empty($tbl)) {
        echo "❌ La tabla 'tarjetas_snc225' no existe.\n";
        exit(1);
    }
    echo "✅ La tabla 'tarjetas_snc225' existe.\n";

    // 2) Verificar columnas de tiempo de trabajo
    $colCorrect = $db->fetchAll("SHOW COLUMNS FROM tarjetas_snc225 LIKE 'tiempo_trabajo'");
    $colLegacy  = $db->fetchAll("SHOW COLUMNS FROM tarjetas_snc225 LIKE 'tiempo_trabajado'");
    if (!empty($colCorrect)) {
        echo "✅ La columna 'tiempo_trabajo' existe.\n";
    } elseif (!empty($colLegacy)) {
        echo "⚠️  Detectada columna legacy 'tiempo_trabajado'. No se creará 'tiempo_trabajo' para evitar duplicidad.\n";
    } else {
        echo "ℹ️  No existe columna de tiempo. Creando 'tiempo_trabajo'...\n";
        $sql = "ALTER TABLE tarjetas_snc225 ADD COLUMN tiempo_trabajo INT NULL AFTER salarios_devengados";
        $app->db->query($sql);
        echo "✅ Columna 'tiempo_trabajo' creada exitosamente.\n";
    }

    // 3) Mostrar estructura actual de la tabla
    echo "\n📋 Estructura actual de tarjetas_snc225:\n";
    $columns = $db->fetchAll("SHOW COLUMNS FROM tarjetas_snc225");
    foreach ($columns as $c) {
        echo "- {$c['Field']} ({$c['Type']}) " . ($c['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }

    echo "\n✅ Verificación/actualización completada.\n";
} catch (Exception $e) {
    echo "❌ Error: ".$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
    exit(1);
}
