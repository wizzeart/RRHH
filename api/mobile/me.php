<?php
/**
 * GET /api/mobile/me  -> datos compactos del trabajador autenticado.
 */

function me_handler(App $app)
{
    $sess = require_user_session($app);
    $tid  = $sess['trabajador_id'];

    // Administrador (u otro usuario sin ficha): devolvemos datos de cuenta.
    if ($tid <= 0) {
        api_ok(['me' => [
            'id'                 => 0,
            'uuid'               => null,
            'nombre'             => $sess['username'],
            'apellidos'          => '',
            'apellidos_segundos' => '',
            'estatus'            => null,
            'fecha_contratacion' => null,
            'empresa_id'         => null,
            'departamento_id'    => null,
            'cargos_id'          => null,
            'has_foto'           => false,
            'departamento'       => null,
            'cargo'              => ((int)$sess['rol_id'] === MOBILE_ADMIN_ROL) ? 'Administrador' : null,
            'empresa'            => null,
            'avatar_url'         => null,
            'email'              => $sess['email'],
            'empresa_color'      => null,
        ]]);
        return;
    }

    // No seleccionamos t.foto en el JSON (es longblob, rompería json_encode).
    // Vamos a saber si hay foto con un COUNT/IF aparte.
    $sql = "SELECT t.id, HEX(t.uuid) AS uuid, t.nombre, t.apellidos, t.apellidos_segundos,
                   t.estatus, t.fecha_contratacion, t.empresa_id,
                   t.departamento_id, t.cargos_id,
                   IF(t.foto IS NULL OR LENGTH(t.foto) = 0, 0, 1) AS has_foto,
                   d.nombre AS departamento, c.nombre AS cargo,
                   e.nombre AS empresa
            FROM trabajadores t
            LEFT JOIN departamentos d ON d.id = t.departamento_id
            LEFT JOIN cargos c        ON c.id = t.cargos_id
            LEFT JOIN empresa e       ON e.id = t.empresa_id
            WHERE t.id = :tid";
    $row = $app->db->fetchRow($sql, ['tid' => $tid]);
    if (!$row) api_fail('Ficha no encontrada.', 404);

    $hasFoto = (int)$row['has_foto'] === 1;
    $row['has_foto']      = $hasFoto;
    $row['avatar_url']    = $hasFoto ? '?action=ficha&part=foto' : null;
    $row['email']         = $sess['email'];
    $row['empresa_color'] = null;

    api_ok(['me' => $row]);
}
