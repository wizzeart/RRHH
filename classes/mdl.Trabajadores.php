<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Trabajador {

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
            case 'checked':
                $this->_checked($param);
                break;
            case 'del':
                $this->_del($param);
                break; 
            case 'save':
                $this->_save($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-trabajadores':
                $data = array();
                $page['title'] = 'Trabajadores';
                $page['subtitle'] = 'Listado de Trabajadores';

                $data_form = array();
                //$data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                break;
            case 'trabajadores':
                /*
                  ini_set('display_errors', 1);
                  ini_set('display_startup_errors', 1);
                  error_reporting(E_ALL);
                 * 
                 */

                $data = array();
                $page['title'] = 'Nuevo trabajador';
                $page['subtitle'] = 'Ficha de Trabajador';

                
                $data_form['cargos'] = $this->app->get_list_cargos();
                

                
                $data_form['bolsas'] = $this->app->get_list_bolsa_empleo();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición trabajador';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                            . " from " . 'trabajadores'
                            . " where id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {

                        //$row['almacenes'] = $this->app->get_list_usuarios_almacenes($row['xusuario_id']);
                        //$row['puntos-ventas'] = $this->app->get_list_usuarios_revendedores($row['xusuario_id']);
                        //print_r($row['almacenes']);
                        //die();

                        $data = $row;
                        $page['subtitle'] = 'Trabajador: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                } else {
                    $data['estatus'] = 'S';
                }
                break;
        }
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        $update = array(
            'trabajador_eliminado' => 1
        );
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->update('trabajadores', $update, $where);

        $history = array(
            'xentity' => 'TRABAJADORES',
            'xaction' => 'DEL-TRABAJADORES',
            'xid' => $param['id'],
            'xobs' => 'DEL TRABAJADOR: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }
    
    private function _save($param) {
        /*
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
         * 
         */

        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => $param['action']
        );

        //print_re

        if ($param['xpwd'] == '')
            unset($param['xpwd']);
        else {
            if ($data['action'] == 'insert') {
                $param['xhash'] = $this->app->rndString(100);
                $param['xpwd'] = password_hash(KEYWEB . $param['xpwd'], PASSWORD_DEFAULT);
            } else {
                $val = array(
                    'usr' => $param['xusuario_id']
                );
                $sql = "select xhash from " . "usuarios where xusuario_id=:usr";
                $row = $this->db->fetchRow($sql, $val);
                if ($row) {
                    $param['xhash'] = $row['xhash'];
                    if ($param['xhash'] == '')
                        $param['xhash'] = $this->app->rndString(100);
                    $param['xpwd'] = password_hash(KEYWEB . $param['xpwd'], PASSWORD_DEFAULT);
                }
            }
        }

        $insert = $param;

        $data['action'] = $insert['action'];

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            if ($data['action'] == 'insert') {
                //$insert['xusuario_id'] = $this->app->get_contador('xultimo_usuario');
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['fecha_contratacion'] = date(dateSQL);
                

                $this->app->db->insert('trabajadores', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'TRABAJADOR',
                    'xaction' => 'INSERT-TRABAJADOR',
                    'id' => $data['id'],
                    'xobs' => 'TRABAJADOR: ' . $data['id'] . ' ' . $insert['nombre']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xusuario_id' => $update['xusuario_id']
                );
                unset($update['xusuario_id']);

                $this->app->db->update('usuarios', $update, $where);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $param['xusuario_id'];

                //print_r($data);
                //die();

                $history = array(
                    'xentity' => 'TRABAJADOR',
                    'xaction' => 'UPDATE-TRABAJADOR',
                    'xid' => $insert['id'],
                    'xobs' => 'TRABAJADOR: ' . $insert['id'] . ' ' . $insert['nombre'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "select *"
                . " from " .  "trabajadores"
                . " order by id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

   
}
