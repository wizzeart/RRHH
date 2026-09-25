<?php
// Script idempotente para preparar el esquema de "vacaciones congeladas" y el
// candado anti-doble-descuento por plan.
//   - trabajadores.vacaciones_congeladas  (saldo reservado al aprobar un plan)
//   - plan_vacaciones.periodo_descuento    (periodo 'YYYY-MM' en que se descontó)
// Ejecutar desde el navegador o por CLI una sola vez. Puede reejecutarse sin riesgo.

// Resolver DOCUMENT_ROOT cuando se ejecuta por CLI (donde viene vacío).
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
}

require_once(__DIR__ . '/includes/config.php');
require_once(BASE_CLASS . DS . 'MSSql.class.php');

try {
    $db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, '3306');

    // 1) trabajadores.vacaciones_congeladas
    $col = $db->fetchAll("SHOW COLUMNS FROM trabajadores LIKE 'vacaciones_congeladas'");
    if (empty($col)) {
        echo "Falta 'trabajadores.vacaciones_congeladas'. Agregando...\n";
        $db->directExec("ALTER TABLE trabajadores ADD COLUMN vacaciones_congeladas DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER vacaciones_acc");
        echo "OK: columna 'vacaciones_congeladas' agregada.\n";
    } else {
        echo "OK: 'trabajadores.vacaciones_congeladas' ya existe.\n";
    }

    // 2) plan_vacaciones.periodo_descuento
    $col = $db->fetchAll("SHOW COLUMNS FROM plan_vacaciones LIKE 'periodo_descuento'");
    if (empty($col)) {
        echo "Falta 'plan_vacaciones.periodo_descuento'. Agregando...\n";
        $db->directExec("ALTER TABLE plan_vacaciones ADD COLUMN periodo_descuento VARCHAR(7) NULL DEFAULT NULL");
        echo "OK: columna 'periodo_descuento' agregada.\n";
    } else {
        echo "OK: 'plan_vacaciones.periodo_descuento' ya existe.\n";
    }

    // 3) Ampliar el ENUM de estado para incluir 'Procesada' (plan ya descontado en nómina).
    $col = $db->fetchRow("SHOW COLUMNS FROM plan_vacaciones LIKE 'estado'");
    $tipo = isset($col['Type']) ? $col['Type'] : '';
    if ($tipo !== '' && stripos($tipo, "'Procesada'") === false) {
        echo "El ENUM 'estado' no incluye 'Procesada'. Ampliando...\n";
        $db->directExec("ALTER TABLE plan_vacaciones MODIFY COLUMN estado ENUM('Pendiente','Aprobado Area','Rechazado Area','Aprobado','Rechazado','Procesada') NULL DEFAULT NULL");
        echo "OK: ENUM 'estado' ampliado con 'Procesada'.\n";
    } else {
        echo "OK: ENUM 'estado' ya incluye 'Procesada'.\n";
    }

    echo "\nVerificacion completada.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
