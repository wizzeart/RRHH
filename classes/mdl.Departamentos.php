<?php

class Departamentos {

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
            case 'list-departamentos':
                $data = array();
                $page['title'] = 'Departamentos';
                $page['subtitle'] = 'Listado de Departamentos';
                $data_form = array();
                break;

            case 'departamentos':
                $data = array();
                $page['title'] = 'Nuevo Departamento';
                $page['subtitle'] = 'Formulario de Departamento';

                // Cargar sedes para el select
                $data_form['sedes'] = $this->get_sedes();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Departamento';
                    $action = 'update';

                    $val = array('id' => $param['id']);
                    $sql = "SELECT * FROM departamentos WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Departamento: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                }
                break;
        }
    }

    private function _list($param) {
        $sql = "SELECT d.id, d.nombre, d.descripcion, d.sede_id, COALESCE(e.nombre, 'Sin sede') AS sede_nombre
                FROM departamentos d
                LEFT JOIN sedes e ON d.sede_id = e.id
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
            'sede_id' => 'Sede'
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
            'descripcion' => isset($param['descripcion']) ? $param['descripcion'] : '',
            'sede_id' => intval($param['sede_id'])
        );

        // Campos de control
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        try {
            if ($data['action'] == 'insert') {
                $ok = $this->db->insert('departamentos', $insert);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Departamento insertado correctamente';
                    $data['id'] = $this->db->last_id();
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo insertar el departamento';
                }
            } else {
                if (!isset($param['id'])) {
                    $data['status'] = 0;
                    $data['msg'] = 'ID no proporcionado';
                    print(json_encode($data));
                    return;
                }
                $where = array('id' => intval($param['id']));
                $ok = $this->db->update('departamentos', $insert, $where);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Departamento actualizado correctamente';
                    $data['id'] = $param['id'];
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo actualizar el departamento';
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
            $result = $this->db->delete('departamentos', $where);
            
            if ($result) {
                $data['msg'] = 'Departamento eliminado correctamente';
                
                // Registrar en historial
                $history = array(
                    'xentity' => 'DEPARTAMENTO',
                    'xaction' => 'DEL-DEPARTAMENTO',
                    'xid' => $id,
                    'xobs' => 'DEL DEPARTAMENTO: ' . $id
                );
                $this->app->add_history($history);
            } else {
                $data['status'] = 0;
                $data['msg'] = 'No se pudo eliminar el departamento';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al eliminar el departamento: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function get_sedes() {
        $sql = "SELECT id, nombre FROM sedes ORDER BY nombre";
        $data = $this->db->fetchAll($sql);
        return $data ? $data : array();
    }
}
