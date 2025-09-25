<?php

/**
 * Módulo Historial
 *
 * @author alvaro
 */
class Historial {

    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'list';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'historial':
                $data = array();
                $page['title'] = 'Historial del Sistema';
                $page['subtitle'] = 'Registro de Actividades del Sistema';

                $data_form = array();
                break;
        }
    }

    private function _list($param) {
        $data = array();
        $sql = "SELECT 
                h.*,
                u.xusuario as usuario_nombre
                FROM historico h 
                LEFT JOIN usuarios u ON h.xuser = u.xusuario_id
                ORDER BY h.xdate DESC, h.id DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }
}
