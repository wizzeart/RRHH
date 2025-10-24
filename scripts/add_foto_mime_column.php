<?php
// Adds foto_mime column to trabajadores if missing
require_once __DIR__ . '/../classes/App.class.php';
$app = new App();
$db = $app->db;

try {
    $row = $db->fetchRow("SHOW COLUMNS FROM trabajadores LIKE 'foto_mime'");
    if ($row && count($row) > 0) {
        echo "Column foto_mime already exists.\n";
        exit(0);
    }
} catch (Exception $e) {
    // If fetchRow failed, continue to attempt add
}

try {
    // Use a safe ALTER statement
    $db->directExec("ALTER TABLE trabajadores ADD COLUMN foto_mime VARCHAR(255) NULL DEFAULT NULL");
    echo "Added foto_mime column.\n";
} catch (Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
    exit(1);
}

?>