<?php
/**
 * classes/mdl.Deudas.php
 * Modelo para gestionar deudas_trabajador
 */
class Deudas {
    var $app;
    var $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        $method = isset($param['method']) ? $param['method'] : '';
        switch ($method) {
            case 'list':
                print json_encode($this->_list_deudas($param));
                break;
            case 'get':
                print json_encode($this->_get_by_id($param) ?: new stdClass());
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'del':
                $this->_del($param);
                break;
            default:
                print json_encode(['status' => 0, 'msg' => 'Método no especificado']);
        }
    }

    public function controlador($param) {
        global $page, $data_form;
        if (isset($param['module']) && $param['module'] === 'list-deudas') {
            $page['title'] = 'Deudas a Trabajadores';
            $page['subtitle'] = 'Gestión de Deudas';
            $data_form = [];
            $data_form['trabajadores'] = $this->_get_trabajadores_activos();
        }
    }

    private function _get_trabajadores_activos() {
        $sql = "SELECT id, CONCAT(nombre, ' ', apellidos, IF(apellidos_segundos IS NULL OR apellidos_segundos='', '', CONCAT(' ', apellidos_segundos))) AS nombre
                FROM trabajadores
                WHERE IFNULL(trabajador_eliminado,'0') IN ('0',0)
                ORDER BY nombre ASC";
        return $this->db->fetchAll($sql);
    }

    private function _list_deudas($param) {
        $where = [];
        $vals = [];
        if (!empty($param['search'])) {
            $where[] = "(t.nombre LIKE :s OR t.apellidos LIKE :s OR t.carnet_identidad LIKE :s OR d.descripcion LIKE :s)";
            $vals['s'] = '%' . $param['search'] . '%';
        }
        if (!empty($param['trabajador_id'])) { $where[] = 'd.trabajador_id = :tid'; $vals['tid'] = intval($param['trabajador_id']); }
        $cond = '';
        if (!empty($where)) $cond = ' WHERE ' . implode(' AND ', $where);
        $sql = "SELECT d.id, d.trabajador_id, CONCAT(t.nombre, ' ', t.apellidos, IF(t.apellidos_segundos IS NULL OR t.apellidos_segundos='', '', CONCAT(' ', t.apellidos_segundos))) AS trabajador, d.monto, d.descripcion, d.fecha_registro, d.saldada, d.fecha_saldo
                FROM deudas_trabajador d
                LEFT JOIN trabajadores t ON t.id = d.trabajador_id
                " . $cond . " ORDER BY d.fecha_registro DESC, d.id DESC";
        if (!empty($vals)) return $this->db->fetchAll($sql, $vals);
        return $this->db->fetchAll($sql);
    }

    private function _get_by_id($param) {
        if (empty($param['id'])) return null;
        $sql = "SELECT id, trabajador_id, monto, descripcion, fecha_registro, saldada, fecha_saldo FROM deudas_trabajador WHERE id=:id";
        return $this->db->fetchRow($sql, ['id' => intval($param['id'])]);
    }

    private function _save($param) {
        $resp = ['status' => 1, 'msg_title' => 'Guardar Deuda', 'msg' => 'Operación exitosa'];
        // Validar requeridos
        $required = ['trabajador_id' => 'Trabajador', 'monto' => 'Monto', 'fecha_registro' => 'Fecha de Registro'];
        foreach ($required as $k => $label) {
            if (!isset($param[$k]) || trim((string)$param[$k]) === '') {
                $resp['status'] = 0; $resp['msg'] = "El campo {$label} es obligatorio"; print json_encode($resp); return;
            }
        }
        if (!is_numeric($param['monto']) || floatval($param['monto']) <= 0) { $resp['status'] = 0; $resp['msg'] = 'El monto debe ser numérico y mayor que 0'; print json_encode($resp); return; }
        // Verificar existencia de trabajador
        $exists = $this->db->fetchRow("SELECT id FROM trabajadores WHERE id=:id AND (trabajador_eliminado='0' OR trabajador_eliminado=0 OR trabajador_eliminado IS NULL)", ['id' => intval($param['trabajador_id'])]);
        if (!$exists) { $resp['status'] = 0; $resp['msg'] = 'El trabajador indicado no existe o está dado de baja'; print json_encode($resp); return; }

        $ins = [
            'trabajador_id' => intval($param['trabajador_id']),
            'monto' => floatval($param['monto']),
            'descripcion' => isset($param['descripcion']) ? $param['descripcion'] : null,
            'fecha_registro' => $param['fecha_registro'],
            'saldada' => isset($param['saldada']) ? (intval($param['saldada'])?1:0) : 0,
            'fecha_saldo' => isset($param['fecha_saldo']) && trim($param['fecha_saldo']) !== '' ? $param['fecha_saldo'] : null
        ];

        try {
            if (isset($param['id']) && intval($param['id']) > 0) {
                $id = intval($param['id']);
                $allowed = ['trabajador_id','monto','descripcion','fecha_registro','saldada','fecha_saldo'];
                $upd = array_intersect_key($ins, array_flip($allowed));
                $this->db->update('deudas_trabajador', $upd, ['id' => $id]);
            } else {
                $this->db->insert('deudas_trabajador', $ins);
            }
        } catch (Exception $e) {
            $resp['status'] = 0;
            $friendly = '';
            if (stripos($e->getMessage(), 'foreign key') !== false) { $friendly = 'El trabajador indicado no existe.'; }
            $resp['msg'] = $friendly !== '' ? $friendly : ('Error al guardar deuda: ' . $e->getMessage());
        }

        print json_encode($resp);
    }

    private function _del($param) {
        $resp = ['status' => 1, 'msg' => 'Deuda eliminada correctamente', 'id' => isset($param['id']) ? intval($param['id']) : 0];
        if (!isset($param['id']) || $resp['id'] <= 0) { $resp['status'] = 0; $resp['msg'] = 'ID de deuda no válido'; print json_encode($resp); return; }
        try {
            $result = $this->db->del('deudas_trabajador', ['id' => $resp['id']]);
            if ($result === false) {
                $resp['status'] = 0; $resp['msg'] = 'Error al intentar eliminar la deuda';
            } else {
                // Si el driver no retorna afected rows, intentamos verificar
                try {
                    $sql = "SELECT ROW_COUNT() AS affected_rows";
                    $affected = $this->db->fetchRow($sql);
                    if ($affected && isset($affected['affected_rows']) && intval($affected['affected_rows']) === 0) {
                        $resp['status'] = 0; $resp['msg'] = 'No se encontró la deuda o ya fue eliminada';
                    }
                } catch (Exception $inner) { /* ignorar comprobación adicional */ }
            }
        } catch (Exception $e) {
            error_log("Error al eliminar deuda #" . $resp['id'] . ": " . $e->getMessage());
            $resp['status'] = 0; $resp['msg'] = 'Error al intentar eliminar la deuda: ' . $e->getMessage();
        }
        print json_encode($resp);
    }
}
