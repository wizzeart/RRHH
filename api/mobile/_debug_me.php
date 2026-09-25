<?php
require_once __DIR__ . '/_bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');

// Forzar usuario_id 277 (Pedro Antonio Manduley)
$uid = 277;

echo "Buscando trabajador para uid=$uid\n";
$row = $app->db->fetchRow(
    "SELECT t.*, u.xemail AS email FROM trabajadores t INNER JOIN usuarios u ON u.xusuario_id = t.usuario_id WHERE u.xusuario_id = :uid AND u.xeliminado = 0",
    ['uid' => $uid]
);
if (!$row) { echo "NO HAY ROW\n"; exit; }

echo "tid={$row['id']}\n";
foreach ($row as $k => $v) {
    if ($k === 'foto' || $k === 'huella_dactilar' || $k === 'huella_dactilar2') {
        echo "  $k: [BLOB len=" . strlen((string)$v) . "]\n";
    } else {
        echo "  $k: " . substr((string)$v, 0, 80) . "\n";
    }
}
