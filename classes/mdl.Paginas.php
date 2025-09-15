<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Pagina {

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
            case 'list-paginas':
                $data = array();
                $page['title'] = 'Página';
                $page['subtitle'] = 'Listado de Páginas';
                break;
            case 'paginas':
                $data = array();
                $page['title'] = 'Nueva página';
                $page['subtitle'] = 'Ficha de Página';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                $data_form['webs'] = $this->app->get_list_webs();
                //$data_form['categorias'] = $this->app->get_list_categorias();
                //print_r($data_form['categorias']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar página';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                            . " from ms_paginas"
                            . " where xpagina_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Página: ' . $row['xpagina_id'] . ' - ' . $row['xpagina'];
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
            'xarticulo_id' => $param['id']
        );
        $this->app->db->update('ms_paginas', $update, $where);

        $history = array(
            'xentity' => 'PÁGINAS',
            'xaction' => 'DEL-PÁGINA',
            'xid' => $param['id'],
            'xobs' => 'DEL PÁGINA: ' . $param['id']
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

        $insert = $param;

        $data['action'] = $insert['action'];

        if (isset($param['xurl_amigable']) && $param['xurl_amigable'] != '') {
            if ($param['action'] == 'update') {
                $param['xurl_amigable'] = strtolower(str_replace(array('+', '*', 'ñ', '!', '?', '¡', '¿', '.'), '', $param['xurl_amigable']));
                $sql = "select * from ms_seo where xmodule='page' and xid!='{$param['xpagina_id']}'"
                        . " and xurl_amigable='{$param['xurl_amigable']}'";
                $row = $this->db->fetchRow($sql);
                if ($row) {
                    $data['status'] = 0;
                    $data['msg'] = 'La expresión de la url amigable MS ya existe';
                }
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'La expresión de la url amigable MS es obligatoria para todas las webs.';
        }

        if (isset($param['xurl_amigable_mr']) && $param['xurl_amigable_mr'] != '') {
            if ($param['action'] == 'update') {
                $param['xurl_amigable_mr'] = strtolower(str_replace(array('+', '*', 'ñ', '!', '?', '¡', '¿', '.'), '', $param['xurl_amigable_mr']));
                $sql = "select * from ms_seo where xmodule='page' and xid!='{$param['xpagina_id']}'"
                        . " and xurl_amigable_mr='{$param['xurl_amigable_mr']}'";
                $row = $this->db->fetchRow($sql);
                if ($row) {
                    $data['status'] = 0;
                    $data['msg'] = 'La expresión de la url amigable MR ya existe';
                }
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'La expresión de la url amigable MR es obligatoria para todas las webs.';
        }

        if (isset($param['xurl_amigable_an']) && $param['xurl_amigable_an'] != '') {
            if ($param['action'] == 'update') {
                $param['xurl_amigable_an'] = strtolower(str_replace(array('+', '*', 'ñ', '!', '?', '¡', '¿', '.'), '', $param['xurl_amigable_an']));
                $sql = "select * from ms_seo where xmodule='page' and xid!='{$param['xpagina_id']}'"
                        . " and xurl_amigable_an='{$param['xurl_amigable_an']}'";
                $row = $this->db->fetchRow($sql);
                if ($row) {
                    $data['status'] = 0;
                    $data['msg'] = 'La expresión de la url amigable AN ya existe';
                }
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'La expresión de la url amigable MR es obligatoria para todas las webs.';
        }

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xeliminado'] = '0';
                $insert['xhash'] = $this->app->rndString(20);
                $this->app->db->insert('ms_paginas', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'PÁGINAS',
                    'xaction' => 'INSERT-PÁGINA',
                    'xid' => $data['id'],
                    'xobs' => 'PÁGINA: ' . $data['id'] . ' ' . $insert['xpagina']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xpagina_id' => $update['xpagina_id']
                );
                unset($update['xpagina_id']);

                $this->app->db->update('ms_paginas', $update, $where);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xpagina_id'];

                $history = array(
                    'xentity' => 'PÁGINAS',
                    'xaction' => 'UPDATE-PÁGINA',
                    'xid' => $insert['xpagina_id'],
                    'xobs' => 'PÁGINAS: ' . $insert['xpagina_id'] . ' ' . $insert['xpagina'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }

            //ELIMINAMOS Y ACTUALIZAMOS SEO
            $where = array(
                'xmodule' => 'page',
                'xid' => $data['id']
            );
            $this->db->del('ms_seo', $where);
            $sql = "select xhash from ms_paginas where xpagina_id={$data['id']}";
            $obj = $this->db->fetchRow($sql);
            if ($obj) {
                $insert_seo = array(
                    'xmodule' => 'page',
                    'xid' => $data['id'],
                    'xhash' => $obj['xhash'],
                    'xurl_amigable' => $param['xurl_amigable'],
                    'xurl_amigable_mr' => $param['xurl_amigable_mr'],
                    'xurl_amigable_an' => $param['xurl_amigable_an']
                );
                $this->db->insert('ms_seo', $insert_seo);
            }
        }
        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "select a.xpagina_id as xid,a.xpagina as xdesc,b.xweb"
                . ",'toolbar' as toolbar"
                . " from ms_paginas a"
                . " left join ms_webs b on a.xweb_id=b.xweb_id"
                . " where a.xeliminado=0"
                . " order by a.xpagina_id";
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
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['activo'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-ACTIVO',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Activo: ' . $data['activo']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }
}

?>
