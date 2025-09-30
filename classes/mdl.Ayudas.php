<?php

class Ayudas {

    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list_ayudas($param);
                print(json_encode($data));
                break;
            case 'get':
                $row = $this->_get_by_id($param);
                print(json_encode($row));
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'del':
                $this->_del($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-ayudas-trabajadores':
                $data = array();
                $page['title'] = 'Ayudas a Trabajadores';
                $page['subtitle'] = 'Gestión de Ayudas';
                $data_form = array();
                // Cargar lista de trabajadores para el formulario
                $data_form['trabajadores'] = $this->_get_trabajadores_activos();
                break;
        }
    }

    private function _get_trabajadores_activos() {
        $sql = "SELECT id, CONCAT(nombre, ' ', apellidos, IF(apellidos_segundos IS NULL OR apellidos_segundos='', '', CONCAT(' ', apellidos_segundos))) AS nombre
                FROM trabajadores
                WHERE trabajador_eliminado = '0'
                ORDER BY nombre ASC";
        return $this->db->fetchAll($sql);
    }

    private function _list_ayudas($param) {
        $where = [];
        $vals = [];

        if (isset($param['search']) && trim($param['search']) !== '') {
            $where[] = "(t.nombre LIKE :s OR t.apellidos LIKE :s OR t.carnet_identidad LIKE :s OR a.tipo_ayuda LIKE :s OR a.descripcion LIKE :s)";
            $vals['s'] = '%' . $param['search'] . '%';
        }
        if (isset($param['trabajador_id']) && trim($param['trabajador_id']) !== '') {
            $where[] = 'a.trabajador_id = :tid';
            $vals['tid'] = intval($param['trabajador_id']);
        }

        $cond = '';
        if (!empty($where)) {
            $cond = ' WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT 
                    a.id,
                    a.trabajador_id,
                    CONCAT(t.nombre, ' ', t.apellidos, IF(t.apellidos_segundos IS NULL OR t.apellidos_segundos='', '', CONCAT(' ', t.apellidos_segundos))) AS trabajador,
                    a.tipo_ayuda,
                    a.valor,
                    a.moneda,
                    a.fecha_entrega,
                    a.descripcion
                FROM ayudas a
                LEFT JOIN trabajadores t ON t.id = a.trabajador_id
                " . $cond .
                " ORDER BY a.fecha_entrega DESC, a.id DESC";

        if (!empty($vals)) return $this->db->fetchAll($sql, $vals);
        return $this->db->fetchAll($sql);
    }

    private function _save($param) {
        $resp = [
            'status' => 1,
            'msg_title' => 'Guardar Ayuda',
            'msg' => 'Operación exitosa'
        ];

        // Validar requeridos
        $required = [
            'trabajador_id' => 'Trabajador',
            'tipo_ayuda' => 'Tipo de Ayuda',
            'valor' => 'Valor',
            'fecha_entrega' => 'Fecha de Entrega',
            'moneda' => 'Moneda'
        ];
        foreach ($required as $k => $label) {
            if (!isset($param[$k]) || trim($param[$k]) === '') {
                $resp['status'] = 0;
                $resp['msg'] = "El campo {$label} es obligatorio";
                print(json_encode($resp));
                return;
            }
        }

        // Validaciones específicas
        $moneda = strtoupper(trim($param['moneda']));
        if (!in_array($moneda, ['USD','CUP'])) {
            $resp['status'] = 0;
            $resp['msg'] = 'La moneda debe ser USD o CUP';
            print(json_encode($resp));
            return;
        }
        if (!is_numeric($param['valor']) || floatval($param['valor']) <= 0) {
            $resp['status'] = 0;
            $resp['msg'] = 'El valor debe ser numérico y mayor que 0';
            print(json_encode($resp));
            return;
        }
        // Verificar existencia de trabajador
        $exists = $this->db->fetchRow("SELECT id FROM trabajadores WHERE id=:id AND (trabajador_eliminado='0' OR trabajador_eliminado=0 OR trabajador_eliminado IS NULL)", ['id'=>intval($param['trabajador_id'])]);
        if (!$exists) {
            $resp['status'] = 0;
            $resp['msg'] = 'El trabajador indicado no existe o está dado de baja';
            print(json_encode($resp));
            return;
        }

        $ins = [
            'trabajador_id' => intval($param['trabajador_id']),
            'tipo_ayuda' => $param['tipo_ayuda'],
            'valor' => floatval($param['valor']),
            'moneda' => $moneda,
            'fecha_entrega' => $param['fecha_entrega'],
            'descripcion' => isset($param['descripcion']) ? $param['descripcion'] : null
        ];

        try {
            if (isset($param['id']) && intval($param['id']) > 0) {
                // Update
                $id = intval($param['id']);
                $allowed = ['trabajador_id','tipo_ayuda','valor','moneda','fecha_entrega','descripcion'];
                $upd = array_intersect_key($ins, array_flip($allowed));
                $this->db->update('ayudas', $upd, ['id'=>$id]);
            } else {
                // Insert
                $this->db->insert('ayudas', $ins);
            }
        } catch (Exception $e) {
            $resp['status'] = 0;
            $friendly = '';
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                $friendly = 'El trabajador indicado no existe.';
            }
            $resp['msg'] = $friendly !== '' ? $friendly : ('Error al guardar ayuda: ' . $e->getMessage());
        }

        print(json_encode($resp));
    }

    private function _get_by_id($param) {
        if (!isset($param['id']) || intval($param['id']) <= 0) return [];
        $sql = "SELECT id, trabajador_id, tipo_ayuda, valor, moneda, fecha_entrega, descripcion FROM ayudas WHERE id=:id";
        return $this->db->fetchRow($sql, ['id'=>intval($param['id'])]);
    }

    private function _del($param) {
        $resp = [
            'status' => 1, 
            'msg' => 'Ayuda eliminada correctamente',
            'id' => isset($param['id']) ? intval($param['id']) : 0
        ];
        
        if (!isset($param['id']) || $resp['id'] <= 0) {
            $resp['status'] = 0;
            $resp['msg'] = 'ID de ayuda no válido';
            print(json_encode($resp));
            return;
        }
        
        try {
            // Usar el método del() de la clase MsSql
            $result = $this->db->del('ayudas', ['id' => $resp['id']]);
            
            if ($result === false) {
                $resp['status'] = 0;
                $resp['msg'] = 'Error al intentar eliminar la ayuda';
            } else {
                // Verificar si se afectó alguna fila
                $sql = "SELECT ROW_COUNT() as affected_rows";
                $affected = $this->db->fetchRow($sql);
                
                if ($affected && $affected['affected_rows'] === 0) {
                    $resp['status'] = 0;
                    $resp['msg'] = 'No se encontró la ayuda o ya fue eliminada';
                }
            }
        } catch (Exception $e) {
            error_log("Error al eliminar ayuda #" . $resp['id'] . ": " . $e->getMessage());
            $resp['status'] = 0;
            $resp['msg'] = 'Error al intentar eliminar la ayuda: ' . $e->getMessage();
        }
        
        print(json_encode($resp));
    }
}
