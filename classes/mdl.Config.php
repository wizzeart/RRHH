<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Config {

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
            case 'save':
                $this->_save($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'config':
                $data = array();
                $page['title'] = 'Configuración';
                $page['subtitle'] = 'Configuración';

                $data_form = array();
                //$data_form['secciones'] = $this->app->get_list_secciones();
                //$data_form['proveedores'] = $this->app->get_list_proveedores($filtro);
                //$data_form['tipos'] = $this->app->get_list_tipos_articulos();
                //print_r($data_form['secciones']);
                //die();
                //$action = 'insert';
                $page['title'] = 'Edición de la configuración';
                $action = 'update';

                $val = array(
                    'id' => '1'
                );
                $sql = "select *"
                        . " from ms_configuraciones"
                        . " where xconfig_id=:id";
                $row = $this->db->fetchRow($sql, $val);
                if ($row) {
                    $row['xpeso'] = number_format($row['xpeso'], 2, ',', '');
                    $row['xseguro'] = number_format($row['xseguro'], 2, ',', '');
                    $row['xvalor_cup_format'] = number_format($row['xvalor_cup'], 2, ',', '');
                    $row['xvalor_mlc_format'] = number_format($row['xvalor_mlc'], 2, ',', '');
                    $row['xvalor_otro_format'] = number_format($row['xvalor_otro'], 2, ',', '');
                    $row['xprecio_combo'] = number_format($row['xprecio_combo'], 2, ',', '');
                    $row['xtransporte'] = number_format($row['xtransporte'], 2, ',', '');
                    $row['xtransporte_free'] = number_format($row['xtransporte_free'], 2, ',', '');
                    $row['xporc_servicio'] = number_format($row['xporc_servicio'], 2, ',', '');
                    $row['xpedido_minimo'] = number_format($row['xpedido_minimo'], 2, ',', '');
                    //$row['xcambio_cup'] = number_format($row['xcambio_cup'], 6, ',', '');
                    $data = $row;
                    $page['subtitle'] = 'Configuración General';
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

        $insert = $param;

        $data['action'] = $insert['action'];

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            //update
            $update = $insert;
            $update['xpeso'] = str_replace(',', '.', $update['xpeso']);
            $update['xseguro'] = str_replace(',', '.', $update['xseguro']);
            $update['xvalor_cup'] = str_replace(',', '.', $update['xvalor_cup']);
            $update['xvalor_mlc'] = str_replace(',', '.', $update['xvalor_mlc']);
            //$update['xprecio_combo'] = str_replace(',', '.', $update['xprecio_combo']);
            //$update['xprecio_envio'] = str_replace(',', '.', $update['xprecio_envio']);
            $update['xporc_servicio'] = str_replace(',', '.', $update['xporc_servicio']);
            $update['xtransporte'] = str_replace(',', '.', $update['xtransporte']);
            $update['xtransporte_free'] = str_replace(',', '.', $update['xtransporte_free']);
            $update['xpedido_minimo'] = str_replace(',', '.', $update['xpedido_minimo']);
            //$update['xcambio_cup'] = str_replace(',', '.', $update['xcambio_cup']);

            $update['xusermodif_id'] = $this->app->user_id;
            $update['xdatemodif'] = date(dateSQL);
            $where = array(
                'xconfig_id' => $update['xconfig_id']
            );
            unset($update['xconfig_id']);
            $noquotes = array('xdatemodif');

            $this->app->db->update('ms_configuraciones', $update, $where, $noquotes);
            $data['msg_title'] = OPERATION_SUCCESS;
            $data['msg'] = RECORD_UPDATE;
            $data['id'] = $where['xconfig_id'];

            $history = array(
                'xentity' => 'CONFIG',
                'xaction' => 'UPDATE-CONGIG',
                'xid' => $insert['xconfig_id'],
                'xobs' => 'CONFIG: ' . $insert['xconfig_id'] // . ' ' . $fields_change
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "select a.xcategoria_id as xid,a.xcategoria as xdesc,a.xactivo as activo"
                . ",a.xorden,b.xseccion,'toolbar' as toolbar,c.xproveedor,a.xdestacado as destacado"
                . " from ms_categorias a"
                . " left join ms_secciones b on a.xseccion_id=b.xseccion_id"
                . " left join ms_proveedores c on a.xproveedor_id=c.xproveedor_id"
                . " where a.xeliminado=0"
                . " order by a.xcategoria_id";
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
