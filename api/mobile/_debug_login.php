<?php
/**
 * ⚠️ TEMPORAL — borrar tras depurar.
 * Diagnóstico del login para un email concreto.
 */
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once INCLUDES . '/functions.php';
init_app();
$app = new App();

$email = isset($_GET['email']) ? strtolower(trim($_GET['email'])) : '';
$pwd   = isset($_GET['pwd']) ? $_GET['pwd'] : '';

echo "EMAIL: $email\nPWD len: ".strlen($pwd)."\n\n";

if (isset($_GET['summary'])) {
    echo "=== Resumen BD ===\n";
    $u = $app->db->fetchRow("SELECT COUNT(*) c FROM usuarios WHERE xeliminado=0");
    echo "Usuarios activos (xeliminado=0): {$u['c']}\n";
    $t = $app->db->fetchRow("SELECT COUNT(*) c FROM trabajadores WHERE trabajador_eliminado=0");
    echo "Trabajadores activos: {$t['c']}\n\n";

    echo "=== Top 10 trabajadores activos ===\n";
    $rows = $app->db->fetchAll(
        "SELECT t.id, t.usuario_id, t.nombre, t.apellidos, u.xemail
         FROM trabajadores t LEFT JOIN usuarios u ON u.xusuario_id = t.usuario_id
         WHERE t.trabajador_eliminado = 0 ORDER BY t.id DESC LIMIT 10"
    );
    foreach ($rows as $r) {
        echo "  tid={$r['id']}  uid={$r['usuario_id']}  {$r['nombre']} {$r['apellidos']}   ({$r['xemail']})\n";
    }
    exit;
}
if (isset($_GET['trabajadores'])) {
    echo "=== Buscando trabajadores 'pedro' o 'manduley' ===\n";
    $rows = $app->db->fetchAll(
        "SELECT t.id, t.usuario_id, t.nombre, t.apellidos, t.apellidos_segundos,
                t.trabajador_eliminado, u.xemail AS email_user, u.xusuario, u.xactivo, u.xeliminado
         FROM trabajadores t
         LEFT JOIN usuarios u ON u.xusuario_id = t.usuario_id
         WHERE LOWER(t.nombre) LIKE '%pedro%' OR LOWER(t.apellidos) LIKE '%manduley%'
         ORDER BY t.id DESC"
    );
    print_r($rows);
    exit;
}
if (isset($_GET['list'])) {
    echo "=== Listado de usuarios (primeros 30) ===\n";
    $rows = $app->db->fetchAll("SELECT xusuario_id, xusuario, xemail, xactivo, xeliminado FROM usuarios ORDER BY xusuario_id DESC LIMIT 30");
    foreach ($rows as $r) {
        echo str_pad($r['xusuario_id'], 5)." | act={$r['xactivo']} elim={$r['xeliminado']} | {$r['xemail']}  ({$r['xusuario']})\n";
    }
    echo "\n=== Búsqueda parcial ===\n";
    $like = $app->db->fetchAll("SELECT xusuario_id, xusuario, xemail FROM usuarios WHERE xemail LIKE :e OR xusuario LIKE :e2", ['e' => '%manduley%', 'e2' => '%pedro%']);
    print_r($like);
    exit;
}

$sql = "SELECT xusuario_id, xusuario, xemail, xpwd, xrol_id, xactivo, xeliminado
        FROM usuarios WHERE LOWER(xemail) = :email";
$row = $app->db->fetchRow($sql, ['email' => $email]);

if (!$row) {
    echo "❌ NO encontrado con WHERE LOWER(xemail)=:email\n";
    // Buscar por LIKE
    $like = $app->db->fetchAll("SELECT xusuario_id, xemail, xactivo, xeliminado FROM usuarios WHERE xemail LIKE :e", ['e' => "%$email%"]);
    print_r($like);
    exit;
}

echo "✅ Encontrado:\n";
print_r([
    'xusuario_id' => $row['xusuario_id'],
    'xusuario'    => $row['xusuario'],
    'xemail'      => $row['xemail'],
    'xrol_id'     => $row['xrol_id'],
    'xactivo'     => $row['xactivo'],   // se espera 'S'
    'xeliminado'  => $row['xeliminado'],// se espera 0
    'xpwd (algo)' => password_get_info($row['xpwd']),
    'xpwd (prefix)' => substr($row['xpwd'], 0, 6),
]);

echo "\nKEYWEB = ".KEYWEB."\n";
echo "Probando password_verify(KEYWEB . pwd, xpwd)...\n";
$ok = password_verify(KEYWEB . $pwd, $row['xpwd']);
echo "Resultado: ".($ok ? "✅ OK" : "❌ NO COINCIDE")."\n";

// Probar también sin KEYWEB por si esta cuenta se guardó distinto
$ok2 = password_verify($pwd, $row['xpwd']);
echo "Sin KEYWEB: ".($ok2 ? "✅ OK" : "❌ NO COINCIDE")."\n";

// ¿Trabajador asociado?
$tr = $app->db->fetchRow(
    "SELECT id, nombre, apellidos, trabajador_eliminado, empresa_id FROM trabajadores WHERE usuario_id = :u",
    ['u' => $row['xusuario_id']]
);
echo "\nTrabajador asociado:\n";
print_r($tr ?: 'NO ENCONTRADO');
