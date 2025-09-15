<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Proveedor
{

    var $app;
    var $db;
    var $action;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param)
    {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'view': //detalle de un registro proveedor
                $data = $this->_view($param);
                print(json_encode($data));
                break;
            case 'recargas':
                $data = $this->_recargas($param);
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

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-proveedores':
                $data = array();
                $page['title'] = 'Proveedores';
                $page['subtitle'] = 'Listado de proveedores';
                break;
            case 'view-proveedores': //detalle de un registro aviso
                $data = array();
                $page['title'] = 'Detalles Proveedor';
                $page['subtitle'] = 'Detalles del proveedor';
                break;
            case 'proveedores':
                $data = array();
                $page['title'] = 'Nuevo proveedor';
                $page['subtitle'] = 'Ficha del proveedor';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                //$data_form['secciones'] = $this->app->get_list_secciones();
                //$data_form['tipos'] = $this->app->get_list_tipos_articulos();
                //print_r($data_form['secciones']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición del proveedor';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                        . " from ms_proveedores"
                        . " where xproveedor_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Proveedor: ' . $row['xproveedor_id'] . ' - ' . $row['xproveedor'];
                    }
                } else {
                    $data['xactivo'] = 'S';
                }
                break;
        }
    }

    private function _del($param)
    {
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
            'xproveedor_id' => $param['id']
        );
        $this->app->db->update('ms_proveedores', $update, $where);

        $history = array(
            'xentity' => 'PROVEEDORES',
            'xaction' => 'DEL-PROVEEDOR',
            'xid' => $param['id'],
            'xobs' => 'DEL PROVEEDOR: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }

    private function _save($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => ''
        );

        $lin = $param['lin'];
        unset($param['lin']);

        $pwd = $param['xpwd'];
        unset($param['xpwd']);

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
                $this->app->db->insert('ms_proveedores', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'PROVEEDORES',
                    'xaction' => 'INSERT-PROVEEDOR',
                    'xid' => $data['id'],
                    'xobs' => 'PROVEEDOR: ' . $data['id'] . ' ' . $insert['xproveedor']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xproveedor_id' => $update['xproveedor_id']
                );
                unset($update['xproveedor_id']);
                $noquotes = array('xdatemodif');

                $this->app->db->update('ms_proveedores', $update, $where, $noquotes);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xproveedor_id'];

                $history = array(
                    'xentity' => 'PROVEEDORES',
                    'xaction' => 'UPDATE-PROVEEDOR',
                    'xid' => $insert['xproveedor_id'],
                    'xobs' => 'PROVEEDOR: ' . $insert['xproveedor_id'] . ' ' . $insert['xproveedor'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }

            if ($pwd != '') {
                $update = array(
                    'xpwd' => password_hash(KEYWEB . $pwd, PASSWORD_DEFAULT)
                );
                $where = array(
                    'xproveedor_id' => $data['id']
                );
                $this->app->db->update('ms_proveedores', $update, $where);
            }

            if ($lin != '') {
                $rl = explode('#', $lin);
                $where = array(
                    'xproveedor_id' => $data['id']
                );
                $this->db->del('ms_proveedores_recargas', $where);
                foreach ($rl as $k => $v) {
                    $d = explode('|', $v);
                    $insert = array(
                        'xproveedor_id' => $data['id'],
                        'xarticulo_id' => $d[0],
                        'xrecargas' => $d[1],
                        'xcoste' => $d[2]
                    );
                    $this->db->insert('ms_proveedores_recargas', $insert);
                }
            }
        }
        print(json_encode($data));
    }

    private function _list($param)
    {
        $data = array();
        $sql = "select a.xproveedor_id as xid,a.xproveedor as xdesc,a.xactivo as activo"
            . ",a.xorden,'toolbar' as toolbar"
            . " from ms_proveedores a"
            . " where a.xeliminado=0"
            . " order by a.xproveedor_id desc";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    /**
     * Detalles de un registro de proveedor
     *
     * @param [string] $param
     * @return array
     */
    private function _view($param)
    {
        $id = ['id' => $param['id']];
        $sql = "select *"
            . " from ms_proveedores a"
            . " where a.xeliminado=0"
            . " and a.xproveedor_id=:id"
            . " order by a.xproveedor_id desc";
        $data = $this->db->fetchAll($sql, $id);
        return $data;
    }

    private function _recargas($param)
    {
        $data = array();
        $cond = '';
        if (isset($param['id']) && $param['id'] != '') {
            $cond .= " and b.xproveedor_id={$param['id']}";
        }

        if ($cond != '') {
            $sql = "select a.xarticulo_id,a.xarticulo,ifnull(b.xrecargas,'') as xrecargas"
                . ",ifnull(b.xcoste,0) as xcoste"
                . " from ms_articulos a"
                . " left join ms_proveedores_recargas b on a.xarticulo_id=b.xarticulo_id$cond"
                . " where a.xeliminado=0"
                . " order by a.xarticulo_id";
            //print($sql);
            //die();
        } else {
            $sql = "select a.*,'' as xrecargas,'0' as xcoste"
                . " from ms_articulos a"
                . " where a.xeliminado=0 $cond"
                . " order by a.xarticulo_id";
        }
        $data = $this->db->fetchAll($sql);

        foreach ($data as $k => $v) {
            $data[$k]['xcoste'] = number_format($v['xcoste'], 2, ',', '');
        }
        return $data;
    }

    private function _checked($param)
    {
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
                'xproveedor_id' => $param['id']
            );
            $this->db->update('ms_proveedores', $update, $where);
            $data['id'] = $param['id'];
            $data['activo'] = $param['value'];

            $history = array(
                'xentity' => 'PROVEEDORES',
                'xaction' => 'CHG-ACTIVO',
                'xid' => $param['id'],
                'xobs' => 'PROVEEDOR: ' . $data['id'] . ' Activo: ' . $data['activo']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }
}
