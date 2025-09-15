<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Tools {

    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        switch ($param['method']) {
            case 'log-error':
                $data = $this->log_error($param);
                print(json_encode($data));
                break;
            case 'test':
                $data = $this->_test($param);
                print(json_encode($data));
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-transitarios':
                $data = array();
                $page['title'] = 'Transitarios';
                $page['subtitle'] = 'Listado de transitarios';
                break;
            case 'transitarios':
                $data = array();
                $page['title'] = 'Nuevo transitario';
                $page['subtitle'] = 'Ficha del transitario';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                //$data_form['proveedores'] = $this->app->get_list_proveedores($filtro);
                //$data_form['tipos'] = $this->app->get_list_tipos_articulos();
                //print_r($data_form['secciones']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición del transitario';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                            . " from ms_transitarios"
                            . " where xtransitario_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Transitario: ' . $row['xtransitario_id'] . ' - ' . $row['xtransitario'];
                    }
                } else {
                    $data['xactivo'] = 'S';
                }
                break;
        }
    }

    private function log_error($param) {
        $data = array(
            'status' => 1
        );

        $insert = array(
            'xreferencia' => $param['ref'],
            'xdatetime' => date(dateSQL),
            'xdata' => $param['data'],
            'xparams' => $param['params']
        );
        $this->db->insert('ms_log_all', $insert);

        return $data;
    }

    private function _test($param) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = array(
            'status' => 1,
            'item' => ''
        );

        $data['item'] = $param['keynoexist'];

        return $data;
    }
}
