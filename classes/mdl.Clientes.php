<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Cliente
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
            case 'list-altas-clientes':
                $data = $this->_list_altas_clientes($param);
                print(json_encode($data));
                break;
            case 'dl-xls-clientes-mailing':
                $this->_dl_xls_clientes_mailing($param);
                break;
            case 'dir-envios':
                $data = $this->_list_envios($param);
                print(json_encode($data));
                break;
            case 'save-envio':
                $this->_save_envio($param);
                break;
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'get_cliente':
                $data = $this->_get_cliente($param);
                print(json_encode($data));
                break;
            case 'list-mr':
                $data = $this->_list_mr($param);
                print(json_encode($data));
                break;
            case 'list-rev':
                $data = $this->_list_rev($param);
                print(json_encode($data));
                break;
            case 'list-empty':
                $data = array();
                print(json_encode($data));
                break;
            case 'checked':
                $this->_checked($param);
                break;
            case 'del':
                $this->_del($param);
                break;
            case 'del-dir':
                $this->_del_dir($param);
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
            case 'list-clientes':
                $data = array();
                $page['title'] = 'Clientes Mandasaldo';
                $page['subtitle'] = 'Listado de clientes de Mandasaldo';
                break;
            case 'list-clientes-com':
                $data = array();
                $page['title'] = 'Clientes para Comerciales';
                $page['subtitle'] = 'Listado de clientes para Comerciales';
                break;
            case 'list-clientes-mr':
                $data = array();
                $page['title'] = 'Clientes Mercarapid';
                $page['subtitle'] = 'Listado de clientes de Mercarapid';
                break;
            case 'clientes':
                $data = array();
                $page['title'] = 'Nuevo cliente';
                $page['subtitle'] = 'Ficha del cliente';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                $data_form['paises'] = $this->app->get_list_paises();
                $data_form['provincias'] = $this->app->get_list_provincias();
                $data_form['municipios'] = $this->app->get_list_municipios();
                $data_form['webs'] = $this->app->get_list_webs();
                //$data_form['tipos'] = $this->app->get_list_tipos_articulos();
                //print_r($data_form['tarifas']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición del cliente';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select a.*,b.xrevendedor"
                        . ",date_format(a.xdatealta,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                        . ",date_format(a.xult_acceso,'%d/%m/%Y %H:%i:%s') as xacceso_format"
                        . " from ms_clientes a"
                        . " left join ms_revendedores b on a.xrevendedor_id=b.xrevendedor_id"
                        . " where a.xcliente_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Cliente: ' . $row['xcliente_id'] . ' - ' . $row['xcliente'];
                        $data['xriesgo'] = number_format($data['xriesgo'], 2, ',', '');

                        $data['envios'] = $this->app->get_list_envios($data);
                        $data['historial'] = $this->app->get_list_historial($data);
                        $data['pedidos'] = $this->app->get_list_pedidos($data);
                    }
                } else {
                    $data['xactivo'] = 'S';
                    $data['xriesgo'] = '0,00';
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
            'xcliente_id' => $param['id']
        );
        $this->app->db->update('ms_clientes', $update, $where);

        $history = array(
            'xentity' => 'CLIENTES',
            'xaction' => 'DEL-CLIENTE',
            'xid' => $param['id'],
            'xobs' => 'DEL CLIENTE: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }

    private function _del_dir($param)
    {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        $update = array(
            'xeliminado' => 1
        );
        $where = array(
            'xenvio_id' => $param['id']
        );
        $this->app->db->update('ms_clientes_envios', $update, $where);

        $history = array(
            'xentity' => 'CLIENTES',
            'xaction' => 'DEL-CLIENTE-DIR',
            'xid' => $param['id'],
            'xobs' => 'DEL CLIENTE DIR: ' . $param['id']
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

        $data['action'] = $param['action'];

        if ($param['xpwd'] == '')
            unset($param['xpwd']);
        else {
            if ($data['action'] == 'insert') {
                $param['xhash'] = $this->app->rndString(100);
                $param['xpwd'] = password_hash(KEYWEB_PUBLIC . $param['xpwd'], PASSWORD_DEFAULT);
            } else {
                $val = array(
                    'cli' => $param['xcliente_id']
                );
                $sql = "select xhash from ms_clientes where xcliente_id=:cli";
                $row = $this->db->fetchRow($sql, $val);
                if ($row) {
                    $param['xhash'] = $row['xhash'];
                    if ($param['xhash'] == '')
                        $param['xhash'] = $this->app->rndString(100);
                    $param['xpwd'] = password_hash(KEYWEB_PUBLIC . $param['xpwd'], PASSWORD_DEFAULT);
                }
            }
        }

        $insert = $param;

        //$data['action'] = $insert['action']; está mas arriba

        if ($data['status'] == 1 && $data['action'] == 'insert' && $this->app->punto_venta != '') {

            $sql = "select * from ms_clientes where xrevendedor_id={$this->app->punto_venta} and xmovil='{$param['xmovil']}'";
            //print($sql);
            //die();
            $row = $this->db->fetchRow($sql, $val);
            if ($row) {
                $data['status'] = 0;
                $data['msg'] = 'El Nº de móvil está registrado en este Punto de Venta.';
            }
        }


        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xfecha_nacimiento'] = $this->_set_fecha_nacimiento();
                $insert['xeliminado'] = '0';

                if ($this->app->punto_venta != '') {
                    $insert['xrevendedor_id'] = $this->app->punto_venta;
                }

                $this->app->db->insert('ms_clientes', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'CLIENTES',
                    'xaction' => 'INSERT-CLIENTE',
                    'xid' => $data['id'],
                    'xobs' => 'CLIENTE: ' . $data['id'] . ' ' . $insert['xcliente']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xcliente_id' => $update['xcliente_id']
                );
                unset($update['xcliente_id']);
                $noquotes = array('xdatemodif');

                $this->app->db->update('ms_clientes', $update, $where, $noquotes);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xcliente_id'];

                //ACTUALIZAR NOMBRE DEL CLIENTE EN PEDIDOS CON ESTADO PAGADO Y EFECTIVO: PAGADO Y VERIFICADOS LOS DOS
                $val = array(
                    'cli_id' => $param['xcliente_id'],
                    'cli' => $update['xcliente']
                );
                $sql = "update ms_pedidos set xcliente=:cli"
                    . " where xcliente_id=:cli_id and xestado in (2,12,16,17)";
                $this->db->directExec($sql, $val);

                $val = array(
                    'cli_id' => $param['xcliente_id'],
                    'n1' => $update['xnombre'],
                    'n2' => $update['xnombre2'],
                    'a1' => $update['xapellido1'],
                    'a2' => $update['xapellido2']
                );

                $sql = "update ms_pedidos_proveedor set xnombre=:n1,xnombre2=:n2,xapellido1=:a1,xapellido2=:a2"
                    . " where xcliente_id=:cli_id and xpreparado='N'";
                $this->db->directExec($sql, $val);

                $history = array(
                    'xentity' => 'CLIENTES',
                    'xaction' => 'UPDATE-CLIENTE',
                    'xid' => $insert['xcliente_id'],
                    'xobs' => 'CLIENTE: ' . $insert['xcliente_id'] . ' ' . $insert['xcliente'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _save_envio($param)
    {
        $data = array(
            'status' => 1,
            'msg' => 'Dirección de Envío guardado correctamente.'
        );

        if ($data['status'] == 1) {
            $cliente = '';

            if ($param['id'] == 'NUEVO') {
                $insert = array(
                    'xcliente_id' => $param['cli'],
                    'xname' => 'Nueva dirección'
                );
                $this->db->insert('ms_clientes_envios', $insert);
                $param['id'] = $this->db->last_id();
            }

            $update = array(
                'xname' => $param['n1'],
                'xname2' => $param['n2'],
                'xapellido1' => $param['a1'],
                'xapellido2' => $param['a2'],
                'xdir1' => $param['d1'],
                'xnumero' => $param['n'],
                'xapartamento' => $param['a'],
                'xpiso' => $param['p'],
                'xentre_calle1' => $param['c1'],
                'xentre_calle2' => $param['c2'],
                'xzipcode' => $param['cp'],
                'xreparto' => $param['rpt'],
                'xcity' => $param['cit'],
                'xprovincia' => $param['prov'],
                'xphone' => $param['tlf'],
                'xci' => $param['ci']
            );
            $where = array(
                'xenvio_id' => $param['id']
            );
            $this->db->update('ms_clientes_envios', $update, $where);

            //ACTUALIZAR DIRECCIONES DE ENVÍO EN PEDIDOS CON ESTADO PAGADO Y EFECTIVO: PAGADO Y VERIFICADOS LOS DOS
            $val = array(
                'id' => $param['id']
            );
            $sql = "select xcliente_id from ms_clientes_envios where xenvio_id=:id";
            $row = $this->db->fetchRow($sql, $val);
            if ($row) {
                $cliente = $row['xcliente_id'];
            }

            $val = array(
                'cli' => $cliente,
                'n1' => $param['n1'],
                'n2' => $param['n2'],
                'a1' => $param['a1'],
                'a2' => $param['a2'],
                'd1' => $param['d1'],
                'n' => $param['n'],
                'a' => $param['a'],
                'p' => $param['p'],
                'c1' => $param['c1'],
                'c2' => $param['c2'],
                'cp' => $param['cp'],
                'cit' => $param['cit'],
                'prov' => $param['prov'],
                'tlf' => $param['tlf'],
                'ci' => $param['ci']
            );
            $sql = "update ms_pedidos set xship_name=:n1,xship_name2=:n2,xship_apellido1=:a1,xship_apellido2=:a2"
                . ",xship_dir1=:d1,xship_numero=:n,xship_apartamento=:a,xship_piso=:p"
                . ",xship_entre_calle1=:c1,xship_entre_calle2=:c2,xship_zipcode=:cp"
                . ",xship_city=:cit,xship_provincia=:prov,xship_phone=:tlf,xship_ci=:ci"
                . " where xcliente_id=:cli and xestado in (2,12,16,17)";
            //$this->db->directExec($sql, $val);

            $sql = "update ms_pedidos_proveedor set xship_name=:n1,xship_name2=:n2,xship_apellido1=:a1,xship_apellido2=:a2"
                . ",xship_dir1=:d1,xship_numero=:n,xship_apartamento=:a,xship_piso=:p"
                . ",xship_entre_calle1=:c1,xship_entre_calle2=:c2,xship_zipcode=:cp"
                . ",xship_city=:cit,xship_provincia=:prov,xship_phone=:tlf,xship_ci=:ci"
                . " where xcliente_id=:cli and xpreparado='N'";
            //$this->db->directExec($sql, $val);

            $history = array(
                'xentity' => 'CLIENTES',
                'xaction' => 'UPDATE-CLIENTE-DIR',
                'xid' => $param['id'],
                'xobs' => 'UPDATE CLIENTE DIR: ' . $param['id']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _list_envios($param)
    {
        $data = array();

        $val = array(
            'cli' => $param['cli']
        );

        $sql = "select a.*"
            . " from ms_clientes_envios a"
            . " where a.xeliminado=0 and xcliente_id=:cli"
            . " order by a.xenvio_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql, $val);
        return $data;
    }

    private function _list($param)
    {
        $data = array();
        $cond = '';

        if (isset($param['q']) && $param['q'] != '') {
            $cond .= " and ("
                . "a.xcliente like '%{$param['q']}%'"
                . "or a.xemail like '%{$param['q']}%'"
                . "or a.xmovil like '%{$param['q']}%'"
                . "or a.xnombre like '%{$param['q']}%'"
                . "or a.xnombre2 like '%{$param['q']}%'"
                . "or a.xapellido1 like '%{$param['q']}%'"
                . "or a.xapellido2 like '%{$param['q']}%'"
                . "or a.xfecha_nacimiento like '%{$param['q']}%'"
                . "or a.xcliente_id='{$param['q']}'"
                //. "or a.xactivo='{$param['q']}'"
                //. "or a.xnivel='{$param['q']}'"
                //. "or a.xcountry='{$param['q']}'"
                . ")";
        }

        if ($this->app->rol == 10) {
            $cond .= " and a.xrevendedor_id={$this->app->punto_venta}";
        } else {
            $cond .= " and a.xrevendedor_id=0 and a.xweb_id=1";
        }

        $sql = "select a.*,'toolbar' as toolbar"
            . " from ms_clientes a"
            . " where a.xeliminado=0$cond"
            . " order by a.xcliente_id desc"
            . " limit 100";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        if (($this->app->rol == 11 || $this->app->rol == 10) && !isset($param['q']) || (isset($param['q']) && $param['q'] == '')) {
            $data = array();
        }

        return $data;
    }

    private function _get_cliente($param)
    {
        $data = array();

        $sql = "select a.xname,
                       a.xname2,
                       a.xapellido1,
                       a.xapellido2,
                       a.xdir1,
                       a.xentre_calle1,
                       a.xentre_calle2,
                       a.xnumero,
                       a.xapartamento,
                       a.xpiso,
                       a.xprovincia,
                       a.xcity,
                       a.xzipcode,
                       a.xreparto,
                       a.xphone,
                       a.xci"
            . " from ms_clientes_envios a"
            . " where a.xcliente_id=:id";
        $data = $this->db->fetchAll($sql, array('id' => $param['id']));

        return $data;
    }

    private function _list_mr($param)
    {
        $data = array();
        $cond = '';

        if (isset($param['q']) && $param['q'] != '') {
            $cond .= " and ("
                . "a.xcliente like '%{$param['q']}%'"
                . "or a.xemail like '%{$param['q']}%'"
                . "or a.xmovil like '%{$param['q']}%'"
                . "or a.xnombre like '%{$param['q']}%'"
                . "or a.xnombre2 like '%{$param['q']}%'"
                . "or a.xapellido1 like '%{$param['q']}%'"
                . "or a.xapellido2 like '%{$param['q']}%'"
                . "or a.xfecha_nacimiento like '%{$param['q']}%'"
                . "or a.xcliente_id='{$param['q']}'"
                //. "or a.xactivo='{$param['q']}'"
                //. "or a.xnivel='{$param['q']}'"
                //. "or a.xcountry='{$param['q']}'"
                . ")";
        }

        $sql = "select a.*,'toolbar' as toolbar"
            . " from ms_clientes a"
            . " where a.xeliminado=0 and a.xrevendedor_id=0 and a.xweb_id=2$cond"
            . " order by a.xcliente_id desc"
            . " limit 100";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _list_rev($param)
    {
        $data = array();
        $cond = '';

        if (isset($param['q']) && $param['q'] != '') {
            $cond .= " and ("
                . "a.xcliente like '%{$param['q']}%'"
                . "or a.xemail like '%{$param['q']}%'"
                . "or a.xmovil like '%{$param['q']}%'"
                . "or a.xnombre like '%{$param['q']}%'"
                . "or a.xnombre2 like '%{$param['q']}%'"
                . "or a.xapellido1 like '%{$param['q']}%'"
                . "or a.xapellido2 like '%{$param['q']}%'"
                . "or a.xfecha_nacimiento like '%{$param['q']}%'"
                . "or a.xcliente_id='{$param['q']}'"
                //. "or a.xactivo='{$param['q']}'"
                //. "or a.xnivel='{$param['q']}'"
                //. "or a.xcountry='{$param['q']}'"
                . ")";
        }

        $sql = "select a.*,'toolbar' as toolbar"
            . ",b.xrevendedor"
            . " from ms_clientes a"
            . " left join ms_revendedores b on a.xrevendedor_id=b.xrevendedor_id"
            . " where a.xeliminado=0 and a.xrevendedor_id!=0$cond"
            . " order by a.xcliente_id desc"
            . " limit 100";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
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
                'xcliente_id' => $param['id']
            );
            $this->db->update('ms_clientes', $update, $where);
            $data['id'] = $param['id'];
            $data['activo'] = $param['value'];

            $history = array(
                'xentity' => 'CLIENTES',
                'xaction' => 'CHG-ACTIVO',
                'xid' => $param['id'],
                'xobs' => 'CLIENTE: ' . $data['id'] . ' Activo: ' . $data['activo']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _dl_xls_clientes_mailing($param)
    {
        $data = array();

        $sql = "select xcliente_id,xcliente,xnombre,xnombre2,xapellido1,xapellido2,xemail,xactivo,xrevendedor_id
            from ms_clientes where xeliminado=0 and xactivo='S' and xrevendedor_id=0";
        $data = $this->app->db->fetchAll($sql);

        $this->app->download_list_clientes_mailing($data);

        //print(json_encode($data));
    }

    private function _list_altas_clientes($param)
    {
        $data = array(
            'status' => 1,
            'items' => array()
        );

        $sql = array();
        for ($i = 1; $i <= 8; $i++) {
            $rango_ini = strtotime('now') - ((date('w') + (7 * $i)) * 24 * 3600);
            $rango_fin = strtotime('now') - ((date('w') + 1 + (7 * ($i - 1))) * 24 * 3600);
            $date_ini = date('Y-m-d', $rango_ini) . ' 00:00:00';
            $date_fin = date('Y-m-d', $rango_fin) . ' 23:59:59';
            $date_ini_format = date('d/m/Y', $rango_ini);
            $date_fin_format = date('d/m/Y', $rango_fin);
            //$data["sem{$i}-ini"] = $date_ini;
            //$data["sem{$i}-fin"] = $date_fin;
            $data['sql' . $i] = "select '$date_ini_format al $date_fin_format' as xrango,count(*) as xcount"
                . " from ms_clientes"
                . " where xdatealta between '$date_ini' and '$date_fin'";
            $sql[] = $data['sql' . $i];
        }
        $data['sql'] = join(' union ', $sql);
        //print($data['sql']);
        //die();
        $data['items'] = $this->app->db->fetchAll($data['sql']);

        return $data;
    }

    private function _set_fecha_nacimiento()
    {
        $y = rand(1940, 2001);
        $m = rand(1, 12);
        $d = rand(1, 28);
        if (strlen($m) == 1)
            $m = '0' . $m;
        if (strlen($d) == 1)
            $d = '0' . $d;

        return $y . '-' . $m . '-' . $d;
    }
}
