<?php

class Cargos {

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
            case 'list-cargos':
                $data = array();
                $page['title'] = 'Cargos';
                $page['subtitle'] = 'Listado de Cargos';
                $data_form = array();
                break;

            case 'cargos':
                $data = array();
                $page['title'] = 'Nuevo Cargo';
                $page['subtitle'] = 'Formulario de Cargo';

                // Cargar departamentos para el select
                $data_form['departamentos'] = $this->get_departamentos();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Cargo';
                    $action = 'update';

                    $val = array('id' => $param['id']);
                    $sql = "SELECT * FROM cargos WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Cargo: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                }
                break;
        }
    }

    private function _list($param) {
        $where = [];
        $vals = [];
        if (isset($param['departamento_id']) && trim($param['departamento_id']) !== '') {
            $where[] = 'c.departamento_id = :departamento_id';
            $vals['departamento_id'] = (int)$param['departamento_id'];
        }
        // Búsqueda por nombre si llega el parámetro 'nombre' o 'search' (bootstrap-table)
        if (isset($param['nombre']) && trim($param['nombre']) !== '') {
            $where[] = 'c.nombre LIKE :nombre';
            $vals['nombre'] = '%' . $param['nombre'] . '%';
        } elseif (isset($param['search']) && trim($param['search']) !== '') {
            $where[] = '(c.nombre LIKE :search OR c.descripcion LIKE :search)';
            $vals['search'] = '%' . $param['search'] . '%';
        }

        $cond = '';
        if (!empty($where)) {
            $cond = ' WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT c.id, c.nombre, c.descripcion, c.salario, c.departamento_id,
                       COALESCE(d.nombre, 'Sin departamento') AS departamento_nombre
                FROM cargos c
                LEFT JOIN departamentos d ON c.departamento_id = d.id" .
                $cond .
                " ORDER BY c.nombre ASC";

        if (!empty($vals)) {
            $data = $this->db->fetchAll($sql, $vals);
        } else {
            $data = $this->db->fetchAll($sql);
        }
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
            'departamento_id' => 'Departamento'
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
            'salario' => isset($param['salario']) && $param['salario'] !== '' ? floatval($param['salario']) : null,
            'departamento_id' => intval($param['departamento_id'])
        );

        // Campos de control
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        try {
            if ($data['action'] == 'insert') {
                $ok = $this->db->insert('cargos', $insert);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Cargo insertado correctamente';
                    $data['id'] = $this->db->last_id();
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo insertar el cargo';
                }
            } else {
                if (!isset($param['id'])) {
                    $data['status'] = 0;
                    $data['msg'] = 'ID no proporcionado';
                    print(json_encode($data));
                    return;
                }
                $where = array('id' => intval($param['id']));
                $ok = $this->db->update('cargos', $insert, $where);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Cargo actualizado correctamente';
                    $data['id'] = $param['id'];
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo actualizar el cargo';
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
            $result = $this->db->delete('cargos', $where);
            
            if ($result) {
                $data['msg'] = 'Cargo eliminado correctamente';
                
                // Registrar en historial
                $history = array(
                    'xentity' => 'CARGO',
                    'xaction' => 'DEL-CARGO',
                    'xid' => $id,
                    'xobs' => 'DEL CARGO: ' . $id
                );
                $this->app->add_history($history);
            } else {
                $data['status'] = 0;
                $data['msg'] = 'No se pudo eliminar el cargo';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al eliminar el cargo: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function get_departamentos() {
        $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre";
        $data = $this->db->fetchAll($sql);
        return $data ? $data : array();
    }
}
