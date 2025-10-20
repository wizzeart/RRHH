<?php

class Sedes {

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
                $data = $this->_list($param);
                print(json_encode($data));
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
            case 'list-sedes':
                $data = array();
                $page['title'] = 'Sedes';
                $page['subtitle'] = 'Listado de Sedes';
                $data_form = array();
                break;

            case 'sedes':
                $data = array();
                $page['title'] = 'Nueva Sede';
                $page['subtitle'] = 'Formulario de Sede';

                // Cargar empresas para el select
                $data_form['empresas'] = $this->get_empresas();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Sede';
                    $action = 'update';

                    $val = array('id' => $param['id']);
                    $sql = "SELECT * FROM sedes WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Sede: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                }
                break;
        }
    }

    private function _list($param) {
        $sql = "SELECT d.id, d.nombre, d.direccion, d.empresa_id, COALESCE(e.nombre, 'Sin empresa') AS empresa_nombre
                FROM sedes d
                LEFT JOIN empresa e ON d.empresa_id = e.id
                ORDER BY d.nombre ASC";
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        // Validaciones
        $required = array(
            'nombre' => 'Nombre',
            'empresa_id' => 'Empresa'
        );
        foreach ($required as $field => $label) {
            if (!isset($param[$field]) || trim($param[$field]) === '') {
                $data['status'] = 0;
                $data['msg'] = "El campo {$label} es obligatorio";
                print(json_encode($data));
                return;
            }
        }

        $insert = array(
            'nombre' => $param['nombre'],
            'direccion' => isset($param['direccion']) ? $param['direccion'] : '',
            'empresa_id' => intval($param['empresa_id'])
        );

        // Campos de control
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        try {
            if ($data['action'] == 'insert') {
                $ok = $this->db->insert('sedes', $insert);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Sede insertada correctamente';
                    $data['id'] = $this->db->last_id();
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo insertar la sede';
                }
            } else {
                if (!isset($param['id'])) {
                    $data['status'] = 0;
                    $data['msg'] = 'ID no proporcionado';
                    print(json_encode($data));
                    return;
                }
                $where = array('id' => intval($param['id']));
                $ok = $this->db->update('sedes', $insert, $where);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Sede actualizada correctamente';
                    $data['id'] = $param['id'];
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo actualizar la sede';
                }
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg_title'] = 'Error';
            $data['msg'] = 'Error en BD: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'msg' => '',
            'id' => $param['id']
        );

        try {
            if (!isset($param['id'])) {
                throw new Exception('ID no proporcionado');
            }
            
            $id = intval($param['id']);
            $where = array('id' => $id);
            
            // Eliminar físicamente el registro
            $result = $this->db->delete('sedes', $where);
            
            if ($result) {
                $data['msg'] = 'Sede eliminada correctamente';
                
                // Registrar en historial
                $history = array(
                    'xentity' => 'SEDE',
                    'xaction' => 'DEL-SEDE',
                    'xid' => $id,
                    'xobs' => 'DEL SEDE: ' . $id
                );
                $this->app->add_history($history);
            } else {
                $data['status'] = 0;
                $data['msg'] = 'No se pudo eliminar la sede';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al eliminar la sede: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function get_empresas() {
        $sql = "SELECT id, nombre FROM empresa ORDER BY nombre";
        $data = $this->db->fetchAll($sql);
        return $data ? $data : array();
    }
}
