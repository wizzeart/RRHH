<?php

class Aspectos {

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
            case 'list-aspectos':
                $data = array();
                $page['title'] = 'Aspectos';
                $page['subtitle'] = 'Listado de Aspectos';
                $data_form = array();
                break;

            case 'aspectos':
                $data = array();
                $page['title'] = 'Nuevo Aspecto';
                $page['subtitle'] = 'Formulario de Aspecto';

                $action = 'insert';
                if (isset($param['id'])) {
                    $data = $this->get_by_id($param['id']);
                    $page['title'] = 'Editar Aspecto';
                    $page['subtitle'] = 'Editar Aspecto - ' . $data['nombre'];
                    $action = 'update';
                }
                break;
        }
    }

    private function _list($param) {
        $sql = "SELECT id, nombre, calificacion_max FROM aspectos ORDER BY nombre";
        return $this->db->fetchAll($sql);
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg' => '',
            'action' => $param['action']
        );
        
        $nombre = trim($param['nombre']);
        $calificacion_max = intval($param['calificacion_max']) ?? 100;
        
        if (empty($nombre)) {
            $data['status'] = 0;
            $data['msg'] = "El nombre del aspecto es obligatorio";
            print(json_encode($data));
            return;
        }

        if ($calificacion_max <= 0) {
            $data['status'] = 0;
            $data['msg'] = "La calificación máxima debe ser mayor a 0";
            print(json_encode($data));
            return;
        }

        $insert = array(
            'nombre' => $nombre,
            'calificacion_max' => $calificacion_max
        );

        try {
            if ($param['action'] == 'insert') {
                // Validar que la suma total no exceda 100
                $sql_sum = "SELECT COALESCE(SUM(calificacion_max), 0) as total FROM aspectos";
                $result = $this->db->fetchRow($sql_sum);
                $total_actual = $result['total'];
                
                if ($total_actual + $calificacion_max > 100) {
                    $data['status'] = 0;
                    $data['msg'] = "No se puede agregar el aspecto. La suma total de calificaciones máximas sería " . ($total_actual + $calificacion_max) . ". El máximo permitido es 100.";
                    print(json_encode($data));
                    return;
                }
                
                $ok = $this->db->insert('aspectos', $insert);
                if ($ok) {
                    $data['msg'] = 'Aspecto guardado correctamente';
                    $data['id'] = $this->db->last_id();
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'No se pudo insertar el aspecto';
                }
            } else {
                if (!isset($param['id'])) {
                    $data['status'] = 0;
                    $data['msg'] = 'ID no proporcionado';
                    print(json_encode($data));
                    return;
                }
                
                // Validar que la suma total no exceda 100 (excluyendo el aspecto actual)
                $sql_sum = "SELECT COALESCE(SUM(calificacion_max), 0) as total FROM aspectos WHERE id != ?";
                $result = $this->db->fetchRow($sql_sum, [$param['id']]);
                $total_otros = $result['total'];
                
                if ($total_otros + $calificacion_max > 100) {
                    $data['status'] = 0;
                    $data['msg'] = "No se puede actualizar el aspecto. La suma total de calificaciones máximas sería " . ($total_otros + $calificacion_max) . ". El máximo permitido es 100.";
                    print(json_encode($data));
                    return;
                }
                
                $where = array('id' => intval($param['id']));
                $ok = $this->db->update('aspectos', $insert, $where);
                if ($ok) {
                    $data['msg'] = 'Aspecto actualizado correctamente';
                    $data['id'] = $param['id'];
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'No se pudo actualizar el aspecto';
                }
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        print(json_encode($data));
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );
        
        try {
            $id = $param['id'];
            
            // Verificar si hay subaspectos asociados
            $sql_check = "SELECT COUNT(*) as count FROM subaspectos WHERE aspecto_id = ?";
            $count_result = $this->db->fetchRow($sql_check, [$id]);
            $count = $count_result['count'];
            
            if ($count > 0) {
                $data['status'] = 0;
                $data['msg'] = "No se puede eliminar el aspecto porque tiene subaspectos asociados";
                print(json_encode($data));
                return;
            }

            $sql = "DELETE FROM aspectos WHERE id = ?";
            $ok = $this->db->directExec($sql, [$id]);
            
            if ($ok) {
                $data['msg'] = 'Aspecto eliminado correctamente';
            } else {
                $data['status'] = 0;
                $data['msg'] = 'No se pudo eliminar el aspecto';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        print(json_encode($data));
    }

    public function get_by_id($id) {
        $sql = "SELECT id, nombre FROM aspectos WHERE id = ?";
        return $this->db->fetchRow($sql, [$id]);
    }

    public function get_all() {
        $sql = "SELECT id, nombre FROM aspectos ORDER BY nombre";
        return $this->db->fetchAll($sql);
    }
}
