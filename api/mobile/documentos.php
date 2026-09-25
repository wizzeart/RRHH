<?php
/**
 * Documentos del trabajador autenticado.
 *   GET  /api/mobile/documentos                -> listado
 *   POST /api/mobile/documentos/firmar         (no soportado por este esquema)
 *
 * Tabla real (prod): `documentos_trabajador`
 *   id, trabajador_id, tipo, archivo, fecha_upload
 *
 * Nota: este esquema no tiene columnas de firma, así que los documentos se
 * listan como informativos. La firma digital vive en `contratos.firma_digital`.
 */

function documentos_list(App $app)
{
    $sess = require_worker_session($app);
    $tid  = $sess['trabajador_id'];

    $rows = [];
    try {
        $rows = $app->db->fetchAll(
            "SELECT id, tipo, archivo, fecha_upload
             FROM documentos_trabajador
             WHERE trabajador_id = :tid
             ORDER BY fecha_upload DESC, id DESC",
            ['tid' => $tid]
        );
    } catch (Exception $e) { /* tabla ausente -> [] */ }
    $rows = $rows ?: [];

    foreach ($rows as &$r) {
        $r['firmado']     = 0; // sin soporte de firma en este esquema
        $r['descripcion'] = $r['tipo'] ?? 'Documento';
        $r['fecha']       = $r['fecha_upload'] ?? null;
    }
    unset($r);

    api_ok(['documentos' => $rows]);
}

function documentos_firmar(App $app)
{
    require_writable();
    api_fail('La firma de documentos no está disponible en este entorno.', 501);
}
