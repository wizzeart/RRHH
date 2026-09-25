<?php

class Subaspectos {

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
            case 'get_aspecto_info':
                $this->_get_aspecto_info($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-subaspectos':
                $data = array();
                $page['title'] = 'Subaspectos';
                $page['subtitle'] = 'Listado de Subaspectos';
                $data_form = array();
                break;

            case 'subaspectos':
                $data = array();
                $page['title'] = 'Nuevo Subaspecto';
                $page['subtitle'] = 'Formulario de Subaspecto';

                // Cargar aspectos para el select
                $data_form['aspectos'] = $this->get_aspectos();

                $action = 'insert';
                if (isset($param['id'])) {
                    $data = $this->get_by_id($param['id']);
                    $page['title'] = 'Editar Subaspecto';
                    $page['subtitle'] = 'Editar Subaspecto - ' . $data['descripcion'];
                    $action = 'update';
                }
                break;
        }
    }

    private function _list($param) {
        $sql = "SELECT s.id, s.descripcion, s.aspecto_id, s.calificacion_max, a.nombre as aspecto_nombre 
                FROM subaspectos s 
                LEFT JOIN aspectos a ON s.aspecto_id = a.id 
                ORDER BY a.nombre, s.descripcion";
        return $this->db->fetchAll($sql);
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg' => '',
            'action' => $param['action']
        );
        
        $aspecto_id = intval($param['aspecto_id']);
        $descripcion = trim($param['descripcion']);
        $calificacion_max = intval($param['calificacion_max']) ?? 100;
        
        if (empty($aspecto_id)) {
            $data['status'] = 0;
            $data['msg'] = "El campo Aspecto es obligatorio";
            print(json_encode($data));
            return;
        }
        
        if (empty($descripcion)) {
            $data['status'] = 0;
            $data['msg'] = "El campo Descripción es obligatorio";
            print(json_encode($data));
            return;
        }

        if ($calificacion_max <= 0) {
            $data['status'] = 0;
            $data['msg'] = "La calificación máxima debe ser mayor a 0";
            print(json_encode($data));
            return;
        }

        // Obtener la calificación máxima del aspecto padre
        $sql_aspecto = "SELECT calificacion_max FROM aspectos WHERE id = ?";
        $aspecto = $this->db->fetchRow($sql_aspecto, [$aspecto_id]);
        
        if (!$aspecto) {
            $data['status'] = 0;
            $data['msg'] = "El aspecto seleccionado no existe";
            print(json_encode($data));
            return;
        }

        $calificacion_max_aspecto = $aspecto['calificacion_max'];

        $insert = array(
            'aspecto_id' => $aspecto_id,
            'descripcion' => $descripcion,
            'calificacion_max' => $calificacion_max
        );

        try {
            if ($param['action'] == 'insert') {
                // Validar que la suma de subaspectos no exceda la calificación máxima del aspecto
                $sql_sum = "SELECT COALESCE(SUM(calificacion_max), 0) as total FROM subaspectos WHERE aspecto_id = ?";
                $result = $this->db->fetchRow($sql_sum, [$aspecto_id]);
                $total_actual = $result['total'];
                
                if ($total_actual + $calificacion_max > $calificacion_max_aspecto) {
                    $data['status'] = 0;
                    $data['msg'] = "No se puede agregar el subaspecto. La suma total sería " . ($total_actual + $calificacion_max) . " y excede la calificación máxima del aspecto (" . $calificacion_max_aspecto . ").";
                    print(json_encode($data));
                    return;
                }
                
                $ok = $this->db->insert('subaspectos', $insert);
                if ($ok) {
                    $data['msg'] = 'Subaspecto guardado correctamente';
                    $data['id'] = $this->db->last_id();
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'No se pudo insertar el subaspecto';
                }
            } else {
                if (!isset($param['id'])) {
                    $data['status'] = 0;
                    $data['msg'] = 'ID no proporcionado';
                    print(json_encode($data));
                    return;
                }
                
                // Validar que la suma de subaspectos no exceda la calificación máxima del aspecto (excluyendo el actual)
                $sql_sum = "SELECT COALESCE(SUM(calificacion_max), 0) as total FROM subaspectos WHERE aspecto_id = ? AND id != ?";
                $result = $this->db->fetchRow($sql_sum, [$aspecto_id, $param['id']]);
                $total_otros = $result['total'];
                
                if ($total_otros + $calificacion_max > $calificacion_max_aspecto) {
                    $data['status'] = 0;
                    $data['msg'] = "No se puede actualizar el subaspecto. La suma total sería " . ($total_otros + $calificacion_max) . " y excede la calificación máxima del aspecto (" . $calificacion_max_aspecto . ").";
                    print(json_encode($data));
                    return;
                }
                
                $where = array('id' => intval($param['id']));
                $ok = $this->db->update('subaspectos', $insert, $where);
                if ($ok) {
                    $data['msg'] = 'Subaspecto actualizado correctamente';
                    $data['id'] = $param['id'];
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'No se pudo actualizar el subaspecto';
                }
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        print(json_encode($data));
    }

    private function _get_aspecto_info($param) {
        $data = array(
            'status' => 1,
            'msg' => '',
            'data' => null
        );
        
        try {
            $aspecto_id = intval($param['aspecto_id']);
            
            // Obtener información del aspecto
            $sql_aspecto = "SELECT id, nombre, calificacion_max FROM aspectos WHERE id = ?";
            $aspecto = $this->db->fetchRow($sql_aspecto, [$aspecto_id]);
            
            if (!$aspecto) {
                $data['status'] = 0;
                $data['msg'] = 'Aspecto no encontrado';
                print(json_encode($data));
                return;
            }
            
            // Obtener suma de calificaciones máximas de los subaspectos existentes
            $sql_sum = "SELECT COALESCE(SUM(calificacion_max), 0) as subaspectos_suma 
                       FROM subaspectos WHERE aspecto_id = ?";
            $result = $this->db->fetchRow($sql_sum, [$aspecto_id]);
            
            $data['data'] = array(
                'id' => $aspecto['id'],
                'nombre' => $aspecto['nombre'],
                'calificacion_max' => $aspecto['calificacion_max'],
                'subaspectos_suma' => $result['subaspectos_suma']
            );
            
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

            $sql = "DELETE FROM subaspectos WHERE id = ?";
            $ok = $this->db->directExec($sql, [$id]);
            
            if ($ok) {
                $data['msg'] = 'Subaspecto eliminado correctamente';
            } else {
                $data['status'] = 0;
                $data['msg'] = 'No se pudo eliminar el subaspecto';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        print(json_encode($data));
    }

    public function get_by_id($id) {
        $sql = "SELECT id, aspecto_id, descripcion FROM subaspectos WHERE id = ?";
        return $this->db->fetchRow($sql, [$id]);
    }

    public function get_aspectos() {
        $sql = "SELECT id, nombre FROM aspectos ORDER BY nombre";
        return $this->db->fetchAll($sql);
    }

    public function get_all() {
        $sql = "SELECT s.id, s.descripcion, s.aspecto_id, a.nombre as aspecto_nombre 
                FROM subaspectos s 
                LEFT JOIN aspectos a ON s.aspecto_id = a.id 
                ORDER BY a.nombre, s.descripcion";
        return $this->db->fetchAll($sql);
    }
}
