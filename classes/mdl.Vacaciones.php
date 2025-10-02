<?php
class Vacaciones {
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
            case 'list-id':
                $data = $this->_list_id($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
        }
    }

    public function controlador($param) {
    
    }

    /**
     * Listar vacaciones por trabajador
     * @param array $param
     * @return array
     */
    private function _list_id($param) {
        $data = array();
        $data['status'] = 1;
        try {
            $sql = "SELECT * FROM plan_vacaciones WHERE trabajador_id=:id";
            $data = $this->db->fetchAll($sql, array('id' => $param['trabajador_id']));
            foreach ($data as $key => $value) {
                $data[$key]['dias'] = json_decode($value['dias'], true);
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        return $data;
    }

    private function _save($param) {
        //$data = array();
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        try {
            if(isset($param['fecha_aprobacion'])) {
                $result = $this->db->insert('plan_vacaciones', array('trabajador_id' => $param['trabajador_id'], 'fecha_aprobacion' => $param['fecha_aprobacion'], 'dias' => json_encode($param['fechas'])));
            }
            else {
                $result = $this->db->insert('plan_vacaciones', array('trabajador_id' => $param['trabajador_id'], 'dias' => json_encode($param['fechas'])));
            }
            if ($result) {
                $data['msg'] = 'Vacaciones guardadas correctamente.';
            }
            else {
                $data['msg'] = 'Error al guardar las vacaciones.';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        print(json_encode($data));

    }

}
