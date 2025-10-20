<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Usuario {

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
            case 'list-usuarios':
                $data = array();
                $page['title'] = 'Usuarios';
                $page['subtitle'] = 'Listado de Usuarios';

                $data_form = array();
                //$data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                break;
            case 'usuarios':
                /*
                  ini_set('display_errors', 1);
                  ini_set('display_startup_errors', 1);
                  error_reporting(E_ALL);
                 * 
                 */

                $data = array();
                $page['title'] = 'Nuevo usuario';
                $page['subtitle'] = 'Ficha de Usuario';

                $data_form = array();
                $data_form['roles'] = $this->app->get_list_roles();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición usuario';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                            . " from " . 'usuarios'
                            . " where xusuario_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {

                        //$row['almacenes'] = $this->app->get_list_usuarios_almacenes($row['xusuario_id']);
                        //$row['puntos-ventas'] = $this->app->get_list_usuarios_revendedores($row['xusuario_id']);
                        //print_r($row['almacenes']);
                        //die();

                        $data = $row;
                        $page['subtitle'] = 'Usuario: ' . $row['xusuario_id'] . ' - ' . $row['xusuario'];
                    }
                } else {
                    $data['xactivo'] = 'S';
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
            'xeliminado' => 1,
            'xusermodif_id' => $this->app->user_id,
            'xdatemodif' => date(dateSQL)
        );
        $where = array(
            'xusuario_id' => $param['id']
        );
        $this->app->db->update('usuarios', $update, $where);

        $history = array(
            'xentity' => 'USUARIOS',
            'xaction' => 'DEL-USUARIO',
            'xid' => $param['id'],
            'xobs' => 'DEL USUARIO: ' . $param['id']
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
                $insert['xdatealta'] = date(dateSQL);
                $insert['xeliminado'] = '0';
                unset($insert['xusuario_id']);
                $this->app->db->insert('usuarios', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'USUARIOS',
                    'xaction' => 'INSERT-USUARIO',
                    'xid' => $data['id'],
                    'xobs' => 'USUARIO: ' . $data['id'] . ' ' . $insert['xusuario']
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
                    'xentity' => 'USUARIOS',
                    'xaction' => 'UPDATE-USUARIO',
                    'xid' => $insert['xusuario_id'],
                    'xobs' => 'USUARIO: ' . $insert['xusuario_id'] . ' ' . $insert['xusuario'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "select a.*"
                . ",b.xrol"
                . " from " .  "usuarios a"
                . " left join " .  "roles b on a.xrol_id=b.xrol_id"
                . " where a.xeliminado=0"
                . " order by a.xusuario_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _checked($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'activo' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xusuario_id' => $param['id']
            );
            $noquotes = array('xdatemodif');
            if ($this->db->update('usuarios', $update, $where, $noquotes) == 1) {
                $data['id'] = $param['id'];
                $data['activo'] = $param['value'];

                $history = array(
                    'xentity' => 'USUARIOS',
                    'xaction' => 'CHG-ACTIVO',
                    'xid' => $param['id'],
                    'xobs' => 'USUARIO: ' . $data['id'] . ' Activo: ' . $data['activo']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }
}
