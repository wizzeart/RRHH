<?php
/** ⚠️ TEMPORAL — borrar tras usar */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(30);
ini_set('display_errors', 1);

require_once __DIR__ . '/_db_prod.php';

echo "Host: " . MOBILE_DB_HOST . "\nPort: " . MOBILE_DB_PORT . "\nDB:   " . MOBILE_DB_NAME . "\nUser: " . MOBILE_DB_USER . "\n\n";

$t0 = microtime(true);
$dsn = 'mysql:host=' . MOBILE_DB_HOST . ';port=' . MOBILE_DB_PORT . ';dbname=' . MOBILE_DB_NAME . ';charset=utf8';
echo "DSN: $dsn\n\n";

try {
    $pdo = new PDO($dsn, MOBILE_DB_USER, MOBILE_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    echo "✅ Conectado en " . round((microtime(true)-$t0)*1000) . " ms\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    echo "usuarios.count = " . $stmt->fetchColumn() . "\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM trabajadores WHERE trabajador_eliminado = 0");
    echo "trabajadores activos = " . $stmt->fetchColumn() . "\n";

    $stmt = $pdo->prepare("SELECT xusuario_id, xemail, xactivo, xeliminado FROM usuarios WHERE LOWER(xemail) = :e");
    $stmt->execute(['e' => 'pedroantonio.manduley@allnovu.net']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Usuario test: ";
    print_r($row ?: 'NO encontrado');
} catch (Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
