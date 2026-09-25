<?php
// Corrige prenóminas cuyo campo `vacaciones` quedó inflado por el bug del subquery
// sin filtro de mes (sumaba TODOS los planes del trabajador, no solo los del mes).
//
// SOLO corrige registros donde:
//   - existe al menos un plan que INICIA en ese mes (estado Aprobado/Procesada), y
//   - el valor guardado difiere de los días reales de esos planes.
// NO toca prenóminas cuyo valor vino de carga manual/importación (sin plan del mes)
// ni de planes de otro mes. Recalcula además pago_vac y los montos derivados con las
// mismas fórmulas de _save_horas para dejar la nómina consistente.
//
// USO:
//   php check_fix_vacaciones_prenomina.php          -> DRY-RUN (solo lista, no cambia nada)
//   php check_fix_vacaciones_prenomina.php --apply   -> aplica los cambios (transaccional)

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
}
require_once(__DIR__ . '/includes/config.php');
require_once(BASE_CLASS . DS . 'MSSql.class.php');

$APPLY = in_array('--apply', $argv ?? [], true) || (isset($_GET['apply']) && $_GET['apply'] == '1');

// Recalcula los montos de una fila de prenómina con las fórmulas de _save_horas().
function recalcular($tarifa, $horas, $vac_dias, $ausenciasCosto) {
    $a_cobrar   = $horas * $tarifa;
    $pago_vac   = ($tarifa * 8) * $vac_dias;
    $salario_neto = round($a_cobrar + $pago_vac, 2);
    $seg_social = round(($a_cobrar + $pago_vac) * 0.05, 2);
    $ing_pers_3 = 0; $ing_pers_5 = 0;
    if ($salario_neto >= 3260 && $salario_neto <= 9510) {
        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
    } elseif ($salario_neto > 9510) {
        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
        $ing_pers_5 = round(($salario_neto - 9510) * 0.05, 2);
    }
    $salario_pagar = round($salario_neto - ($seg_social + $ing_pers_3 + $ing_pers_5 + $ausenciasCosto), 2);
    return [
        'a_cobrar' => round($a_cobrar, 2),
        'sal_dev' => round($a_cobrar, 2),
        'pago_vac' => round($pago_vac, 2),
        'salario_neto' => $salario_neto,
        'seg_social' => $seg_social,
        'ing_pers_3' => $ing_pers_3,
        'ing_pers_5' => $ing_pers_5,
        'salario_pagar' => $salario_pagar,
    ];
}

try {
    $db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, '3306');
    $pdo = $db->conn;

    // Candidatos: prenóminas con vacaciones>0 cuyo valor != días reales de planes que
    // inician ese mes, y donde SÍ existe al menos un plan de ese mes (real_dias > 0).
    $cands = $db->fetchAll("
        SELECT p.id, p.trabajador_id, p.year, p.month, p.horas, p.tarifa,
               p.vacaciones AS guardado, p.ausencias_costo,
               COALESCE((
                   SELECT SUM(LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                   FROM plan_vacaciones pv
                   WHERE pv.trabajador_id = p.trabajador_id
                     AND pv.dias IS NOT NULL AND pv.dias <> ''
                     AND pv.estado IN ('Aprobado','Procesada')
                     AND YEAR(pv.fecha_inicio) = p.year
                     AND MONTH(pv.fecha_inicio) = p.month
               ), 0) AS real_dias
        FROM prenomina p
        WHERE p.vacaciones > 0
        HAVING real_dias > 0 AND ABS(p.vacaciones - real_dias) > 0.001
        ORDER BY p.year, p.month, p.trabajador_id
    ");

    echo $APPLY ? "=== MODO APLICAR ===\n" : "=== DRY-RUN (sin cambios) ===\n";
    echo "Prenóminas a corregir: " . count($cands) . "\n\n";

    if (empty($cands)) { echo "Nada que corregir.\n"; return; }

    if ($APPLY) { $pdo->beginTransaction(); }

    foreach ($cands as $c) {
        $tarifa = floatval($c['tarifa']);
        // Si la tarifa guardada es 0, derivarla del cargo (igual que _save_horas)
        if ($tarifa <= 0) {
            $tr = $db->fetchRow("SELECT COALESCE((CASE WHEN COALESCE(c.salario,0) > 1000 THEN c.salario/192 ELSE c.salario END),0) AS tarifa
                                 FROM trabajadores t LEFT JOIN cargos c ON t.cargos_id=c.id WHERE t.id=:tid",
                                 ['tid' => $c['trabajador_id']]);
            $tarifa = $tr ? floatval($tr['tarifa']) : 0.0;
        }
        $horas = floatval($c['horas'] ?: 192);
        $vac_real = floatval($c['real_dias']);
        $ausenciasCosto = floatval($c['ausencias_costo'] ?? 0);

        $m = recalcular($tarifa, $horas, $vac_real, $ausenciasCosto);

        printf("Trabajador %s | %s-%s | vacaciones %s -> %s | pago_vac -> %s | salario_pagar -> %s\n",
            $c['trabajador_id'], $c['year'], $c['month'],
            $c['guardado'], number_format($vac_real, 2),
            number_format($m['pago_vac'], 2), number_format($m['salario_pagar'], 2));

        if ($APPLY) {
            $db->update('prenomina', array_merge(['vacaciones' => round($vac_real, 2)], $m), ['id' => intval($c['id'])]);
        }
    }

    if ($APPLY) {
        $pdo->commit();
        echo "\nCambios aplicados.\n";
    } else {
        echo "\n(DRY-RUN) Ejecute con --apply para aplicar.\n";
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    echo "Error: " . $e->getMessage() . "\n";
}
?>
