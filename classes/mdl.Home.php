<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Home {

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
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'home':
                $data = array();
                $page['title'] = 'Inicio';
                $page['subtitle'] = 'Resumen situación';

                //die();

                if ($this->app->user_id == 1) {
                    
                      header('Content-type: application/json; charset=utf-8');
                      print(json_encode($data));
                      die();
                    
                }

                break;
        }
    }
}
