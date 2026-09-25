<?php
/**
 * ⚠️ TEMPORAL — borrar tras usar.
 * Crea un usuario + trabajador de prueba para validar la app móvil.
 *
 *   /api/mobile/_seed_test_worker.php?confirm=1
 */

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once INCLUDES . '/functions.php';
init_app();
$app = new App();

if (!isset($_GET['confirm'])) {
    echo "Llama con ?confirm=1 para ejecutar.\n";
    exit;
}

$email = 'pedroantonio.manduley@allnovu.net';
$pwd   = '01022268102';
$nombre = 'Pedro Antonio';
$apellidos = 'Manduley';
$apellidos_segundos = 'Test';

// 1) ¿Ya existe?
$exists = $app->db->fetchRow("SELECT xusuario_id FROM usuarios WHERE LOWER(xemail) = LOWER(:e)", ['e' => $email]);
if ($exists) {
    echo "ℹ️  Usuario ya existe (id={$exists['xusuario_id']}). Actualizando contraseña y asegurando trabajador.\n";
    $usuario_id = (int)$exists['xusuario_id'];
    $app->db->update('usuarios', [
        'xpwd'        => password_hash(KEYWEB . $pwd, PASSWORD_DEFAULT),
        'xactivo'     => 'S',
        'xeliminado'  => 0,
    ], ['xusuario_id' => $usuario_id]);
} else {
    // Buscar siguiente xusuario_id libre
    $maxId = $app->db->fetchRow("SELECT COALESCE(MAX(xusuario_id),0) AS m FROM usuarios");
    $usuario_id = (int)$maxId['m'] + 1;

    $insertU = [
        'xusuario_id' => $usuario_id,
        'xusuario'    => 'pedro.manduley',
        'xemail'      => $email,
        'xpwd'        => password_hash(KEYWEB . $pwd, PASSWORD_DEFAULT),
        'xrol_id'     => 5,        // rol trabajador (5 si existe; ajustar si hace falta)
        'xactivo'     => 'S',
        'xeliminado'  => 0,
    ];
    try {
        $app->db->insert('usuarios', $insertU);
        echo "✅ Usuario insertado id=$usuario_id\n";
    } catch (Exception $e) {
        echo "❌ Error insert usuarios: " . $e->getMessage() . "\n";
        exit;
    }
}

// 2) ¿Tiene trabajador?
$tr = $app->db->fetchRow("SELECT id FROM trabajadores WHERE usuario_id = :u", ['u' => $usuario_id]);
if ($tr) {
    echo "ℹ️  Trabajador ya existe (id={$tr['id']}). Reactivando.\n";
    $app->db->update('trabajadores', ['trabajador_eliminado' => 0, 'estatus' => 'Activo'], ['id' => $tr['id']]);
    $trabajador_id = (int)$tr['id'];
} else {
    // Coger primer empresa / departamento / cargo / provincia / municipio disponibles
    $emp = $app->db->fetchRow("SELECT id FROM empresa ORDER BY id ASC LIMIT 1");
    $dep = $app->db->fetchRow("SELECT id FROM departamentos ORDER BY id ASC LIMIT 1");
    $car = $app->db->fetchRow("SELECT id FROM cargos ORDER BY id ASC LIMIT 1");
    $prov = $app->db->fetchRow("SELECT id FROM provincia ORDER BY id ASC LIMIT 1");
    $mun  = $prov ? $app->db->fetchRow("SELECT id FROM municipio WHERE provincia_id = :p ORDER BY id ASC LIMIT 1", ['p' => $prov['id']]) : null;

    $insertT = [
        'usuario_id'         => $usuario_id,
        'nombre'             => $nombre,
        'apellidos'          => $apellidos,
        'apellidos_segundos' => $apellidos_segundos,
        'sexo'               => 'M',
        'carnet_identidad'   => '01022268102',
        'edad'               => 33,
        'direccion'          => 'Demo 123',
        'telefono'           => '+34 600 000 000',
        'licencia_conduccion'=> '',
        'nivel_educacional'  => 'Universitario',
        'fecha_contratacion' => date('Y-m-d'),
        'estatus'            => 'Activo',
        'ubicacion'          => 0,
        'trabajador_eliminado' => 0,
    ];
    if ($emp)  $insertT['empresa_id']     = $emp['id'];
    if ($dep)  $insertT['departamento_id']= $dep['id'];
    if ($car)  $insertT['cargos_id']      = $car['id'];
    if ($prov) $insertT['provincia_id']   = $prov['id'];
    if ($mun)  $insertT['municipio_id']   = $mun['id'];

    try {
        $app->db->insert('trabajadores', $insertT);
        $trabajador_id = $app->db->last_id();
        echo "✅ Trabajador insertado id=$trabajador_id\n";
        echo "   empresa_id=" . ($emp['id'] ?? 'NULL') . "\n";
        echo "   departamento_id=" . ($dep['id'] ?? 'NULL') . "\n";
        echo "   cargos_id=" . ($car['id'] ?? 'NULL') . "\n";
    } catch (Exception $e) {
        echo "❌ Error insert trabajadores: " . $e->getMessage() . "\n";
        exit;
    }
}

echo "\n=========================================\n";
echo "✅ LISTO. Ya puedes entrar a la app móvil con:\n";
echo "   email   : $email\n";
echo "   password: $pwd\n";
echo "=========================================\n";
echo "\n🧹 Cuando termines de probar, BORRA este archivo y _debug_login.php.\n";
