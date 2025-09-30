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
        }
    }

    public function controlador($param) {
    
    }

    private function _list_id($param) {
        $data = array();
        try {
            $sql = "SELECT * FROM plan_vacaciones WHERE trabajador_id=:id";
            $data = $this->db->fetchAll($sql, array('id' => $param['trabajador_id']));
        } catch (Exception $e) {
            $data = array();
        }
        return $data;
    }

}
