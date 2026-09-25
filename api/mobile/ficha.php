<?php
/**
 * GET /api/mobile/ficha           -> JSON con TODA la ficha del trabajador autenticado.
 * GET /api/mobile/ficha?part=foto -> devuelve el binario PNG del avatar.
 *
 * Tolerante al esquema: no falla si faltan tablas opcionales.
 */

function ficha_handler(App $app)
{
    $sess = require_worker_session($app);
    $tid = $sess['trabajador_id'];

    // Imagen
    if (isset($_GET['part']) && $_GET['part'] === 'foto') {
        $row = $app->db->fetchRow("SELECT foto FROM trabajadores WHERE id = :id", ['id' => $tid]);
        if ($row && !empty($row['foto'])) {
            header_remove('Content-Type');
            header('Content-Type: image/png');
            header('Cache-Control: private, max-age=300');
            echo $row['foto'];
            exit;
        }
        http_response_code(404);
        exit;
    }

    $sql = "SELECT t.id, HEX(t.uuid) AS uuid, t.nombre, t.apellidos, t.apellidos_segundos,
                   t.sexo, t.carnet_identidad, t.edad, t.direccion, t.telefono,
                   t.licencia_conduccion, t.nivel_educacional,
                   t.fecha_contratacion, t.fecha_baja, t.fecha_nacimiento, t.estatus,
                   t.ubicacion, t.empresa_id, t.departamento_id, t.cargos_id, t.nfc,
                   t.vacaciones_acc, t.salario_acc,
                   p.nombre AS provincia, m.nombre AS municipio,
                   u.xusuario AS username, u.xemail AS email,
                   c.nombre AS cargo, c.salario AS salario,
                   d.nombre AS departamento,
                   e.nombre AS empresa
            FROM trabajadores t
            LEFT JOIN municipio m      ON m.id = t.municipio_id
            LEFT JOIN provincia p      ON p.id = t.provincia_id
            LEFT JOIN usuarios  u      ON u.xusuario_id = t.usuario_id
            LEFT JOIN cargos    c      ON c.id = t.cargos_id
            LEFT JOIN departamentos d  ON d.id = t.departamento_id
            LEFT JOIN empresa e        ON e.id = t.empresa_id
            WHERE t.id = :id";
    $row = $app->db->fetchRow($sql, ['id' => $tid]);
    if (!$row) api_fail('Ficha no encontrada.', 404);

    // Edad: si no viene guardada (0/NULL), la calculamos desde fecha_nacimiento.
    $edad = isset($row['edad']) ? (int)$row['edad'] : 0;
    if ($edad <= 0 && !empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] !== '0000-00-00') {
        $ts = strtotime($row['fecha_nacimiento']);
        if ($ts !== false && $ts < time()) {
            try {
                $edad = (new DateTime())->diff(new DateTime('@' . $ts))->y;
            } catch (Exception $e) { /* deja edad como está */ }
        }
    }
    $row['edad'] = $edad > 0 ? $edad : null;

    // Bancos
    try {
        $banco = $app->db->fetchRow(
            "SELECT numero_tarjeta_salario, numero_cuenta_estandar FROM bancos WHERE trabajador_id = :id",
            ['id' => $tid]
        );
        if ($banco) {
            $row['tarjeta_salario'] = $banco['numero_tarjeta_salario'];
            $row['cuenta_estandar'] = $banco['numero_cuenta_estandar'];
        }
    } catch (Exception $e) { /* ignore */ }

    $row['salario'] = isset($row['salario']) ? intval($row['salario']) : 0;
    $row['avatar_url'] = '?action=ficha&part=foto';
    $row['empresa_color'] = null;

    // Vacaciones acumuladas: preferimos submayor_vacaciones (1 fila por trab.)
    // y caemos a trabajadores.vacaciones_acc si no hay registro.
    $acc = 0;
    try {
        $sv = $app->db->fetchRow(
            "SELECT vacaciones FROM submayor_vacaciones WHERE id_trabajador = :id",
            ['id' => $tid]
        );
        if ($sv && isset($sv['vacaciones'])) $acc = floatval($sv['vacaciones']);
    } catch (Exception $e) {
        $acc = isset($row['vacaciones_acc']) ? floatval($row['vacaciones_acc']) : 0;
    }
    $row['vacaciones_acumuladas']  = $acc;
    $row['vacaciones_usadas']      = 0; // no hay tabla de uso en esta BD
    $row['vacaciones_disponibles'] = $acc;

    // Recursos asignados: recursos_trabajadores -> recursos (+ categoría)
    $recursos = [];
    try {
        $recursos = $app->db->fetchAll(
            "SELECT rt.id, rt.recurso_id, rt.fecha_entrega_a_t, rt.fecha_entrega_a_rh, rt.estado,
                    r.nombre, r.descripcion, cat.nombre AS categoria
             FROM recursos_trabajadores rt
             LEFT JOIN recursos r          ON r.id = rt.recurso_id
             LEFT JOIN categorias_recurso cat ON cat.id = r.categoria_id
             WHERE rt.trabajador_id = :tid
             ORDER BY rt.fecha_entrega_a_t DESC, rt.id DESC",
            ['tid' => $tid]
        );
    } catch (Exception $e) { /* tabla ausente -> [] */ }
    $recursos = $recursos ?: [];

    // Normalizamos al shape que consume la app.
    $row['recursos'] = array_map(function ($r) {
        $devuelto = !empty($r['fecha_entrega_a_rh']);
        return [
            'id'          => (int)$r['id'],
            'descripcion' => $r['nombre'] ?: ($r['descripcion'] ?: 'Recurso'),
            'categoria'   => $r['categoria'],
            'fecha'       => $r['fecha_entrega_a_t'],
            'cantidad'    => 1,
            'devuelto'    => $devuelto,
            'observacion' => $devuelto
                ? ('Devuelto el ' . $r['fecha_entrega_a_rh'])
                : ($r['descripcion'] ?: null),
        ];
    }, $recursos);

    api_ok(['ficha' => $row]);
}
