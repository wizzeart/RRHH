<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Contacto {

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
            case 'checked-destacado':
                $this->_checked_destacado($param);
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
            case 'list-contactos':
                $data = array();
                $page['title'] = 'Contactos';
                $page['subtitle'] = 'Listado de emails recibidos';
                break;
            case 'contactos':
                $data = array();
                $page['title'] = 'Nueva categoría';
                $page['subtitle'] = 'Ficha de la categoría';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                //$data_form['secciones'] = $this->app->get_list_secciones();
                //$data_form['proveedores'] = $this->app->get_list_proveedores($filtro);
                //$data_form['tipos'] = $this->app->get_list_tipos_articulos();
                //print_r($data_form['secciones']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición de la categoría';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                            . " from ms_log_contactos"
                            . " where xlog_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $update = array(
                            'xvisto' => 'S',
                            'xfecha_visto' => date(dateSQL)
                        );
                        $where = array(
                            'xlog_id' => $param['id']
                        );
                        $this->app->db->update('ms_log_contactos', $update, $where);
                        $data = $row;
                        $page['subtitle'] = 'Categoría: ' . $row['xcategoria_id'] . ' - ' . $row['xcategoria'];
                    }
                } else {
                    
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
            'xcategoria_id' => $param['id']
        );
        $this->app->db->update('ms_categorias', $update, $where);

        $history = array(
            'xentity' => 'CATEGORIAS',
            'xaction' => 'DEL-CATEGORIA',
            'xid' => $param['id'],
            'xobs' => 'DEL CATEGORIA: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => ''
        );

        $img = $param['ximg'];
        unset($param['ximg']);

        $insert = $param;

        $data['action'] = $insert['action'];

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xeliminado'] = '0';
                $insert['xhash'] = $this->app->rndString(20);
                $this->app->db->insert('ms_categorias', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'CATEGORIAS',
                    'xaction' => 'INSERT-CATEGORIA',
                    'xid' => $data['id'],
                    'xobs' => 'CATEGORIA: ' . $data['id'] . ' ' . $insert['xcategoria']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xcategoria_id' => $update['xcategoria_id']
                );
                unset($update['xcategoria_id']);
                $noquotes = array('xdatemodif');

                $this->app->db->update('ms_categorias', $update, $where, $noquotes);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xcategoria_id'];

                $history = array(
                    'xentity' => 'CATEGORIAS',
                    'xaction' => 'UPDATE-CATEGORIA',
                    'xid' => $insert['xcategoria_id'],
                    'xobs' => 'CATEGORIA: ' . $insert['xcategoria_id'] . ' ' . $insert['xcategoria'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "select *"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . " from ms_log_contactos a"
                . " where a.xeliminado=0"
                . " order by a.xlog_id desc"
                . " limit 50";
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
                'xcategoria_id' => $param['id']
            );
            $this->db->update('ms_categorias', $update, $where);
            $data['id'] = $param['id'];
            $data['activo'] = $param['value'];

            $history = array(
                'xentity' => 'CATEGORIAS',
                'xaction' => 'CHG-ACTIVO',
                'xid' => $param['id'],
                'xobs' => 'CATEGORIA: ' . $data['id'] . ' Activo: ' . $data['activo']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _checked_destacado($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xdestacado' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xcategoria_id' => $param['id']
            );
            $this->db->update('ms_categorias', $update, $where);
            $data['id'] = $param['id'];
            $data['value'] = $param['value'];

            $history = array(
                'xentity' => 'CATEGORIAS',
                'xaction' => 'CHG-DESTACADO',
                'xid' => $param['id'],
                'xobs' => 'CATEGORIA: ' . $data['id'] . ' Destacado: ' . $data['value']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

}

?>
