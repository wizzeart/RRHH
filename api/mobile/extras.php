<?php
/**
 * Endpoints auxiliares: logo de la empresa, etc.
 */

function empresa_logo_handler(App $app)
{
    $sess = require_worker_session($app);
    $eid  = $sess['empresa_id'];
    if ($eid > 0) {
        $row = $app->db->fetchRow("SELECT foto FROM empresa WHERE id = :id", ['id' => $eid]);
        if ($row && !empty($row['foto'])) {
            header_remove('Content-Type');
            header('Content-Type: image/png');
            header('Cache-Control: private, max-age=600');
            echo $row['foto'];
            exit;
        }
    }
    http_response_code(404);
    exit;
}
