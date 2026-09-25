<?php
// Normaliza el formato de la columna `plan_vacaciones.dias`.
//
// CONTEXTO
// La columna llegó a tener DOS formatos incompatibles según el origen del plan:
//   - Web   (classes/mdl.Vacaciones.php::_save)     -> lista "YYYY-MM-DD,YYYY-MM-DD,..."
//   - Móvil (api/mobile/vacaciones.php)             -> un contador numérico ("7")
// Los consumidores que hacen explode(',') o cuentan comas interpretaban los planes móviles
// como 1 solo día (prenómina, tope anual de 24 días) o los descartaban por completo
// (Planificación Anual de Vacaciones en inicio). El origen ya fue corregido; este script
// arregla las filas históricas.
//
// QUÉ TOCA
// SOLO las filas cuyo `dias` es NUMÉRICO (el bug confirmado). Regenera la lista de días
// laborables (sin sábados ni domingos) a partir de fecha_inicio..fecha_fin, mismo criterio
// que la web. Las filas con `dias` vacío o con formato irreconocible NO se tocan: se listan
// aparte para que decidas, porque rellenarlas cambiaría el comportamiento de la prenómina
// (hoy quedan excluidas del descuento por el guard "dias <> ''").
//
// SEGURIDAD
// Antes de aplicar guarda los valores originales en `plan_vacaciones_dias_backup`.
// Todo el UPDATE va en una transacción.
//
// USO:
//   php fix_plan_vacaciones_dias_formato.php                     -> DRY-RUN (no cambia nada)
//   php fix_plan_vacaciones_dias_formato.php --apply             -> aplica los cambios
//   php fix_plan_vacaciones_dias_formato.php --apply --skip-procesadas
//                                                                -> no toca planes ya Procesada

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
}
require_once(__DIR__ . '/includes/config.php');
require_once(BASE_CLASS . DS . 'MSSql.class.php');

$argvSafe        = isset($argv) ? $argv : [];
$APPLY           = in_array('--apply', $argvSafe, true) || (isset($_GET['apply']) && $_GET['apply'] == '1');
$SKIP_PROCESADAS = in_array('--skip-procesadas', $argvSafe, true) || isset($_GET['skip_procesadas']);

/** ¿La cadena es una lista válida de fechas YYYY-MM-DD separadas por coma? */
function es_lista_de_fechas($raw) {
    $partes = array_filter(array_map('trim', explode(',', $raw)), 'strlen');
    if (empty($partes)) return false;
    foreach ($partes as $p) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $p)) return false;
    }
    return true;
}

/** Días laborables (lun-vie) entre dos fechas, inclusive. Devuelve [] si las fechas no sirven. */
function dias_laborables($fecha_inicio, $fecha_fin) {
    if (empty($fecha_inicio) || empty($fecha_fin)) return [];
    if ($fecha_inicio === '0000-00-00' || $fecha_fin === '0000-00-00') return [];
    try {
        $current = new DateTime(substr($fecha_inicio, 0, 10));
        $end     = new DateTime(substr($fecha_fin, 0, 10));
    } catch (Exception $e) {
        return [];
    }
    if ($end < $current) return [];
    $out = [];
    while ($current <= $end) {
        $dow = (int) $current->format('w'); // 0 = domingo, 6 = sábado
        if ($dow != 0 && $dow != 6) {
            $out[] = $current->format('Y-m-d');
        }
        $current->modify('+1 day');
    }
    return $out;
}

try {
    $db  = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_);
    $pdo = $db->conn;

    $rows = $db->fetchAll("
        SELECT pv.id, pv.trabajador_id, pv.fecha_inicio, pv.fecha_fin, pv.dias, pv.estado,
               CONCAT(t.nombre, ' ', t.apellidos) AS trabajador
        FROM plan_vacaciones pv
        LEFT JOIN trabajadores t ON t.id = pv.trabajador_id
        ORDER BY pv.id
    ");

    $migrar = [];   // dias numérico -> se puede regenerar
    $sinFechas = []; // dias numérico pero fechas inválidas -> no se puede regenerar
    $vacios = [];    // dias vacío -> solo informar
    $raros = [];     // dias con formato irreconocible -> solo informar
    $ok = 0;

    foreach ($rows as $r) {
        $raw = isset($r['dias']) ? trim((string) $r['dias']) : '';

        if ($raw !== '' && es_lista_de_fechas($raw)) { $ok++; continue; }

        if ($raw === '') { $vacios[] = $r; continue; }

        if (!is_numeric($raw)) { $raros[] = $r; continue; }

        // dias numérico: el bug confirmado
        $laborables = dias_laborables($r['fecha_inicio'], $r['fecha_fin']);
        if (empty($laborables)) { $sinFechas[] = $r; continue; }

        $r['_nuevo'] = implode(',', $laborables);
        $r['_nuevo_count'] = count($laborables);
        $r['_viejo_count'] = (int) $raw;
        $migrar[] = $r;
    }

    echo $APPLY ? "=== MODO APLICAR ===\n" : "=== DRY-RUN (sin cambios) ===\n";
    echo "Planes totales revisados : " . count($rows) . "\n";
    echo "Ya en formato correcto   : {$ok}\n";
    echo "A migrar (dias numérico) : " . count($migrar) . "\n";
    echo "dias vacío (NO se tocan) : " . count($vacios) . "\n";
    echo "formato raro (NO se toca): " . count($raros) . "\n";
    echo "sin fechas usables       : " . count($sinFechas) . "\n\n";

    if (!empty($migrar)) {
        echo "--- Planes a migrar ---\n";
        $difCount = 0;
        $procesadasTocadas = 0;

        foreach ($migrar as $m) {
            $esProcesada = in_array($m['estado'], ['Procesada', 'Aprobado'], true);
            $marca = '';
            if ($m['_viejo_count'] !== $m['_nuevo_count']) {
                $difCount++;
                $marca .= '  [!] contaba ' . $m['_viejo_count'] . ' días naturales -> ' . $m['_nuevo_count'] . ' laborables';
            }
            if ($esProcesada) {
                $procesadasTocadas++;
                $marca .= '  [estado=' . $m['estado'] . ']';
            }

            printf("id=%-6s trab=%-5s %-28s %s..%s  dias '%s' -> %d fechas%s\n",
                $m['id'], $m['trabajador_id'], mb_substr((string) $m['trabajador'], 0, 28),
                substr((string) $m['fecha_inicio'], 0, 10), substr((string) $m['fecha_fin'], 0, 10),
                trim((string) $m['dias']), $m['_nuevo_count'], $marca);
        }

        echo "\n";
        if ($difCount > 0) {
            echo "AVISO: en {$difCount} plan(es) el nº de días cambia porque el móvil contaba días\n";
            echo "       naturales (incluía fines de semana) y la web cuenta solo laborables.\n";
        }
        if ($procesadasTocadas > 0) {
            echo "AVISO: {$procesadasTocadas} plan(es) están Aprobado/Procesada. La prenómina los descontó\n";
            echo "       como 1 solo día por este bug; tras migrar los informes mostrarán el nº real.\n";
            echo "       El candado periodo_descuento evita que se vuelvan a descontar solos.\n";
            echo "       Use --skip-procesadas si prefiere no tocarlos.\n";
        }
        echo "\n";
    }

    foreach ([['dias vacío', $vacios], ['formato irreconocible', $raros], ['sin fechas usables', $sinFechas]] as $grupo) {
        list($titulo, $lista) = $grupo;
        if (empty($lista)) continue;
        echo "--- NO tocados ({$titulo}) ---\n";
        foreach ($lista as $r) {
            printf("id=%-6s trab=%-5s %s..%s  dias='%s' estado=%s\n",
                $r['id'], $r['trabajador_id'],
                substr((string) $r['fecha_inicio'], 0, 10), substr((string) $r['fecha_fin'], 0, 10),
                trim((string) $r['dias']), $r['estado']);
        }
        echo "\n";
    }

    if (empty($migrar)) {
        echo "Nada que migrar.\n";
        return;
    }

    if (!$APPLY) {
        echo "(DRY-RUN) Revise la lista y ejecute con --apply para aplicar.\n";
        return;
    }

    // Tabla de respaldo de los valores originales
    $db->directExec("CREATE TABLE IF NOT EXISTS `plan_vacaciones_dias_backup` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `plan_id` INT(11) NOT NULL,
        `dias_old` TEXT NULL,
        `dias_new` TEXT NULL,
        `fecha_migracion` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_plan` (`plan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $pdo->beginTransaction();
    $aplicados = 0;
    $omitidos = 0;
    $ahora = date('Y-m-d H:i:s');

    foreach ($migrar as $m) {
        if ($SKIP_PROCESADAS && in_array($m['estado'], ['Procesada', 'Aprobado'], true)) {
            $omitidos++;
            continue;
        }

        $db->insert('plan_vacaciones_dias_backup', [
            'plan_id'         => intval($m['id']),
            'dias_old'        => (string) $m['dias'],
            'dias_new'        => $m['_nuevo'],
            'fecha_migracion' => $ahora,
        ]);

        $db->update('plan_vacaciones', ['dias' => $m['_nuevo']], ['id' => intval($m['id'])]);
        $aplicados++;
    }

    $pdo->commit();

    echo "Cambios aplicados: {$aplicados} plan(es).\n";
    if ($omitidos > 0) {
        echo "Omitidos por --skip-procesadas: {$omitidos}.\n";
    }
    echo "Respaldo de los valores originales en la tabla `plan_vacaciones_dias_backup`.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    echo "Error: " . $e->getMessage() . "\n";
}
