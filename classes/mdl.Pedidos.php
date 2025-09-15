<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Pedido {

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
            case 'list-pedidos-almacen':
                $data = $this->_list_pedidos_almacen($param);
                print(json_encode($data));
                break;
            case 'liberar-chofer':
                $data = $this->_liberar_chofer($param);
                print(json_encode($data));
                break;
            case 'check-envios':
                $data = $this->_check_envios($param);
                print(json_encode($data));
                break;
            case 'get-his':
                $data = $this->_list_historico_doc($param);
                print(json_encode($data));
                break;
            case 'copy-order':
                $this->_copy_order($param);
                break;
            case 'view-details':
                $data = $this->_view_details($param);
                print(json_encode($data));
                break;
            case 'cancel-line':
                $this->_cancel_line($param);
                break;
            case 'load-lines':
                $this->_load_lines($param);
                break;
            case 'cancel-order':
                $this->_cancel_order($param);
                break;
            case 'view-tracking':
                $data = $this->_view_tracking($param);
                print(json_encode($data));
                break;
            case 'send-confirm-efectivo':
                $this->_send_confirm_efectivo($param);
                break;
            case 'rescue':
                $this->_rescue($param);
                break;
            case 'dl-xls':
                $this->_dl_xls($param);
                break;
            case 'search-product':
                $this->_search_product($param);
                break;
            case 'search-client':
                $this->_search_client($param);
                break;
            case 'chg-estado':
                $this->_chg_estado($param);
                break;
            case 'chg-almacen':
                $this->_chg_almacen($param);
                break;
            case 'dl-doc':
                $this->_dl_order($param);
                break;
            case 'dl-doc-ms':
                $this->_dl_order_ms($param);
                break;
            case 'dl-doc-an':
                $this->_dl_order_an($param);
                break;
            case 'dl-doc-pv':
                $this->_dl_order_pv($param);
                break;
            case 'dl-fra':
                $this->_dl_fra($param);
                break;
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'list-user':
                $data = $this->_list_user($param);
                print(json_encode($data));
                break;
            case 'list-finalizados':
                $data = $this->_list_finalizados($param);
                print(json_encode($data));
                break;
            case 'list-entregados':
                $data = $this->_list_entregados($param);
                print(json_encode($data));
                break;
            case 'list-ptes':
                $data = $this->_list_ptes($param);
                print(json_encode($data));
                break;
            case 'list-hoy':
                $data = $this->_list_hoy($param);
                print(json_encode($data));
                break;
            case 'list-rev':
                $data = $this->_list_rev($param);
                print(json_encode($data));
                break;
            case 'del':
                $this->_del($param);
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'save-pv':
                $this->_save_pv($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-pedidos-rev':
                $data = array();
                $page['title'] = 'Pedidos de Revendedores';
                $page['subtitle'] = 'Listado de Revendedores';

                $filtro = array(
                    'activo' => 'S',
                    'estado' => '1,2,3,12',
                    'fields' => 'xcliente_id,xcliente'
                );

                $data_form = array();
                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['revendedores'] = $this->app->get_list_revendedores($filtro);
                $data_form['estados'] = $this->app->get_list_estados($filtro);

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (90 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                break;
            case 'list-pedidos-ptes':
                $data = array();
                $page['title'] = 'Pedidos Pendientes';
                $page['subtitle'] = 'Listado de Pedidos pendientes de verificar';

                $filtro = array(
                    'activo' => 'S',
                    'fields' => 'xcliente_id,xcliente'
                );

                $data_form = array();
                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                //$data_form['clientes'] = array();
                $data_form['estados'] = $this->app->get_list_estados($filtro);

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (90 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                break;
            case 'list-pedidos-hoy':
                $data = array();
                $page['title'] = 'Pedidos Hoy';
                $page['subtitle'] = 'Listado de Pedidos realizados hoy';

                $filtro = array(
                    'activo' => 'S',
                    'fields' => 'xcliente_id,xcliente',
                    'estado' => '1,2,3,12,15,17,25'
                );

                $data_form = array();
                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['estados'] = $this->app->get_list_estados($filtro);

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (90 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                $data['fi'] = date('Y-m-d') . ' 00:00:00';
                $data['ff'] = date('Y-m-d') . ' 23:59:59';

                //print(json_encode($data_form));
                //die();


                break;
            case 'list-pedidos-hoy-mc':
                $data = array();
                $page['title'] = 'Pedidos Hoy';
                $page['subtitle'] = 'Listado de Pedidos realizados hoy';

                $filtro = array(
                    'activo' => 'S',
                    'fields' => 'xcliente_id,xcliente',
                    'estado' => '1,2,3,12,15,17,25'
                );

                $data_form = array();
                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['estados'] = $this->app->get_list_estados($filtro);

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (90 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                $data['fi'] = date('Y-m-d') . ' 00:00:00';
                $data['ff'] = date('Y-m-d') . ' 23:59:59';

                //print(json_encode($data_form));
                //die();


                break;
            case 'list-pedidos':
                $data = array();
                $page['title'] = 'Pedidos';
                $page['subtitle'] = 'Listado de Pedidos de Ventas';

                $filtro = array(
                    'activo' => 'S',
                    'fields' => 'xcliente_id,xcliente'
                );

                $data_form = array();
                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['revendedores'] = $this->app->get_list_revendedores($filtro);
                //$data_form['clientes'] = array();
                $data_form['estados'] = $this->app->get_list_estados();

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (90 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                $data_form['fecha-ini-finalizados'] = date('d/m/Y', strtotime('now') - (30 * 24 * 3600));
                $data_form['fecha-fin-finalizados'] = date('d/m/Y');

                $data_form['fecha-ini-entregados'] = date('d/m/Y', strtotime('now') - (15 * 24 * 3600));
                $data_form['fecha-fin-entregados'] = date('d/m/Y');

                break;
            case 'list-pedidos-mc':
                $data = array();
                $page['title'] = 'Pedidos';
                $page['subtitle'] = 'Listado de Pedidos de Ventas';

                $filtro = array(
                    'activo' => 'S',
                    'fields' => 'xcliente_id,xcliente'
                );

                $data_form = array();
                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                //$data_form['clientes'] = array();
                $data_form['estados'] = $this->app->get_list_estados();

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (90 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                $data_form['fecha-ini-finalizados'] = date('d/m/Y', strtotime('now') - (30 * 24 * 3600));
                $data_form['fecha-fin-finalizados'] = date('d/m/Y');

                $data_form['fecha-ini-entregados'] = date('d/m/Y', strtotime('now') - (15 * 24 * 3600));
                $data_form['fecha-fin-entregados'] = date('d/m/Y');

                break;
            case 'pedidos':
                /*
                  ini_set('display_errors', 1);
                  ini_set('display_startup_errors', 1);
                  error_reporting(E_ALL);
                 * 
                 */

                $data = array();
                $page['title'] = 'Nuevo Pedido';
                $page['subtitle'] = 'Detalles del Pedido';

                $filtro = array(
                    'activo' => 'S',
                    //'tipo' => 4, //TIPO DE REVENDEDOR: PUNTO DE VENTA
                    'usuario' => $this->app->user_id,
                    'fields' => 'xcliente_id,xcliente',
                    'estado' => '1,2,3,12,15,17,25'
                );

                if ($this->app->rol == 10) {
                    //unset($filtro['estado'][2]);
                    $filtro['estado'] = str_replace(',3', '', $filtro['estado']);
                    //print($filtro['estado']);
                    //die();
                }

                //print_r($filtro);
                //die();

                $data_form = array();
                $data_form['articulos'] = $this->app->get_list_articulos($filtro);
                $data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                //$data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['puntos-ventas'] = $this->app->get_list_revendedores($filtro);
                //$data_form['fpagos'] = json_decode(FORMAS_PAGO_PV_JSON, true);
                //print_r($data_form['clientes']);
                //print(json_encode($data_form['clientes']));
                //die();

                $data_form['estados'] = $this->app->get_list_estados($filtro);
                $data_form['provincias'] = $this->app->get_list_provincias();
                $data_form['municipios'] = $this->app->get_list_municipios();
                $data_form['destinos'] = $this->app->get_list_medios();
                $data_form['config'] = $this->app->get_configuraciones();
                //print_r($data_form['config']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select a.*,b.xestado as xestado_desc,d.xweb,e.xchofer,f.xcliente"
                            . ",date_format(a.xfecha,'%d/%m/%Y') as xfecha_format"
                            . ",date_format(a.xfecha_pago,'%d/%m/%Y %H:%i:%s') as xfecha_pago_format"
                            . ",date_format(a.xdate_entregado,'%d/%m/%Y %H:%i:%s') as xfecha_entregado_format"
                            . ",date_format(a.xdate_finalizado,'%d/%m/%Y %H:%i:%s') as xfecha_finalizado_format"
                            . ",ifnull(xrevendedor,'Ninguno') as xrevendedor"
                            . " from ms_pedidos a"
                            . " left join ms_estados b on a.xestado=b.xestado_id"
                            . " left join ms_revendedores c on a.xrevendedor_id=c.xrevendedor_id"
                            . " left join ms_webs d on a.xweb_id=d.xweb_id"
                            . " left join ms_chofers e on a.xchofer_id=e.xchofer_id"
                            . " left join ms_clientes f on a.xcliente_id=f.xcliente_id"
                            . " where a.xpedido_id=:id";
                    $row = $this->app->db->fetchRow($sql, $val);
                    if ($row) {
                        $row['xcliente_sel'] = $row['xcliente_id'] . ' - ' . $row['xcliente'];
                        $row['ximporte'] = number_format($row['ximporte'], 2, ',', '');
                        $row['xseguro'] = number_format($row['xseguro'], 2, ',', '');
                        $row['xtransporte_format'] = number_format($row['xtransporte'], 2, ',', '');

                        $row['xtipo_pedido_format'] = 'Pedido con envío a domicilio';
                        if ($row['xtipo_pedido'] == 'R')
                            $row['xtipo_pedido_format'] = 'Pedido con entrega en punto de recogida';

                        $data = $row;
                        $data['xseguro-defecto'] = $data_form['config']['xseguro'];

                        $data['items'] = $this->app->get_list_pedido_lineas($data);
                        //print_r($data['items']);
                        //die();

                        $page['title'] = 'Pedido: ' . $row['xpedido_id'];
                        $page['subtitle'] = 'Pedido: ' . $row['xpedido_id'];

                        $data['xtracking'] = '';
                        $data['xcorte_id'] = '';
                        $data['xtransporte_free'] = $data_form['config']['xtransporte_free'];
                        $sql = "select * from ms_pedidos_proveedor where xpedido_id={$data['xpedido_id']} and xpreparado='S' and xservicio='N'";
                        //print($sql);
                        //die();
                        $rr = $this->db->fetchAll($sql);
                        $tmp = array();
                        foreach ($rr as $ke => $ve) {
                            //$tmp[] = '<a href="tracking.ms?ref=' . $data['xhash'] . '&track=' . $ve['xtracking'] . '">' . $ve['xtracking'] . '</a>';
                            $tmp[] = $ve['xtracking'];
                            $data['xcorte_id'] = $ve['xcorte_id'];
                        }
                        if (count($tmp) > 0) {
                            $data['xtracking'] = join(', ', $tmp);
                        }
                    }

                    //print(json_encode($data));
                    //die();
                } else {
                    $data['xfecha_format'] = date('d/m/Y');
                    $data['ximporte'] = '0,00';
                    $data['xseguro'] = '0,00';
                    $data['xseguro-defecto'] = $data_form['config']['xseguro'];
                    $data['xdestino_id'] = '1';
                    $data['xestado'] = '1';
                    $data['xestado_desc'] = 'Pedido pendiente de Pago';
                    $data['xcliente_id'] = '';
                    $data['xpreparado'] = 'N';
                    $data['xship_country'] = 'CU';
                    $data['xip'] = $_SERVER['REMOTE_ADDR'];
                    $data['xtransporte'] = $data_form['config']['xtransporte'];
                    $data['xtransporte_free'] = $data_form['config']['xtransporte_free'];
                    $data['xtransporte_format'] = number_format($data_form['config']['xtransporte'], 2, ',', '');
                    $data['xrevendedor_id'] = '';
                    $data['xship_city'] = '';
                    $data['xship_provincia'] = '';

                    if ($this->app->punto_venta != '') {
                        $data['xrevendedor_id'] = $this->app->punto_venta;
                    }
                }

                break;
            case 'pedidos-pv':
            case 'pedidos-pv.test':
                /*
                  ini_set('display_errors', 1);
                  ini_set('display_startup_errors', 1);
                  error_reporting(E_ALL);
                 * 
                 */

                $data = array();
                $page['title'] = 'Nuevo Pedido PV';
                $page['subtitle'] = 'Detalles del Pedido PV';

                $filtro = array(
                    'activo' => 'S',
                    'activo-rev' => 'S',
                    'tipo' => 4, //TIPO DE REVENDEDOR: PUNTO DE VENTA
                    'usuario' => $this->app->user_id,
                    'fields' => 'xcliente_id,xcliente,xmovil',
                    'estado' => '1,2,3,12,15,17,25'
                );
                if ($this->app->rol == 10) {
                    //unset($filtro['estado'][2]);
                    $filtro['estado'] = str_replace(',3', '', $filtro['estado']);
                    //print($filtro['estado']);
                    //die();
                }

                $data_form = array();
                $data_form['articulos'] = $this->app->get_list_articulos($filtro);
                //if($this->app->user_id==1){
                //print_r($data_form['articulos']);
                //die();
                //}
                $data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                //$data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['puntos-ventas'] = $this->app->get_list_revendedores($filtro);
                $data_form['formas_pago'] = json_decode(FORMAS_PAGO_PV_JSON, true);

                foreach ($data_form['clientes'] as $k => $v) {
                    $data_form['clientes'][$k]['xcliente'] = $this->app->eliminar_acentos($v['xcliente']);
                }

                //print_r($data_form['clientes']);
                //print(json_encode($data_form['puntos-ventas']));
                //die();
                //ASIGNAMOS LOS PRECIOS DEL PUNTO DE VENTA A LOS ARTICULOS PARA NO TENER
                //QUE MODIFICAR MUCHO EN LA PANTALLA
                if ($this->app->punto_venta != '') {
                    $sql = "select * from ms_revendedores where xrevendedor_id={$this->app->punto_venta}";
                    $rev = $this->db->fetchRow($sql);

                    foreach ($data_form['articulos'] as $k => $v) {
                        $data_form['articulos'][$k]['xprecio'] = $v["xprecio{$rev['xtarifa_id']}_rev"];
                    }

                    $data_form['formas_pago_permitidas'] = $this->app->get_list_revendedores_fpago($this->app->punto_venta);
                    //print_r($data_form['formas_pago']);
                    //die();
                    foreach ($data_form['formas_pago'] as $k => $v) {
                        if (!in_array($v['name'], $data_form['formas_pago_permitidas'])) {
                            unset($data_form['formas_pago'][$k]);
                        }
                    }
                }

                //print_r($data_form['clientes']);
                //print(json_encode($data_form['clientes']));
                //die();
                $data_form['estados'] = $this->app->get_list_estados($filtro);
                $data_form['provincias'] = $this->app->get_list_provincias();
                $data_form['municipios'] = $this->app->get_list_municipios();
                $data_form['destinos'] = $this->app->get_list_medios();
                $data_form['config'] = $this->app->get_configuraciones();
                //print_r($data_form['config']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $action = 'update';

                    $filtro = array(
                        'activo' => 'S'
                    );
                    $data_form['puntos-ventas'] = $this->app->get_list_revendedores($filtro);

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select a.*,b.xestado as xestado_desc,d.xweb,e.xchofer,f.xcliente"
                            . ",date_format(a.xfecha,'%d/%m/%Y') as xfecha_format"
                            . ",date_format(a.xfecha_pago,'%d/%m/%Y %H:%i:%s') as xfecha_pago_format"
                            . ",date_format(a.xdate_entregado,'%d/%m/%Y %H:%i:%s') as xfecha_entregado_format"
                            . ",date_format(a.xdate_finalizado,'%d/%m/%Y %H:%i:%s') as xfecha_finalizado_format"
                            . ",ifnull(xrevendedor,'Ninguno') as xrevendedor"
                            . " from ms_pedidos a"
                            . " left join ms_estados b on a.xestado=b.xestado_id"
                            . " left join ms_revendedores c on a.xrevendedor_id=c.xrevendedor_id"
                            . " left join ms_webs d on a.xweb_id=d.xweb_id"
                            . " left join ms_chofers e on a.xchofer_id=e.xchofer_id"
                            . " left join ms_clientes f on a.xcliente_id=f.xcliente_id"
                            . " where a.xpedido_id=:id";
                    $row = $this->app->db->fetchRow($sql, $val);
                    if ($row) {
                        $row['xcliente_sel'] = $row['xcliente_id'] . ' - ' . $row['xcliente'];
                        $row['ximporte'] = number_format($row['ximporte'], 2, ',', '');
                        $row['ximporte_cup'] = number_format($row['ximporte_cup'], 2, ',', '');
                        $row['ximporte_mlc'] = number_format($row['ximporte_mlc'], 2, ',', '');
                        $row['ximporte_otro'] = number_format($row['ximporte_otro'], 2, ',', '');
                        $row['xseguro'] = number_format($row['xseguro'], 2, ',', '');
                        $row['xtransporte_format'] = number_format($row['xtransporte'], 2, ',', '');
                        $row['xtransporte_cup_format'] = number_format($row['xtransporte_cup'], 2, ',', '');
                        $row['xtransporte_mlc_format'] = number_format($row['xtransporte_mlc'], 2, ',', '');
                        $row['xtransporte_otro_format'] = number_format($row['xtransporte_otro'], 2, ',', '');
                        $row['xbase_format'] = number_format($row['xbase'], 2, ',', '');
                        $row['xbase_cup_format'] = number_format($row['xbase_cup'], 2, ',', '');
                        $row['xbase_mlc_format'] = number_format($row['xbase_mlc'], 2, ',', '');
                        $row['xbase_otro_format'] = number_format($row['xbase_otro'], 2, ',', '');
                        $row['xporc_servicio_format'] = number_format($row['xporc_servicio'], 2, ',', '');
                        $row['ximp_servicio_format'] = number_format($row['ximp_servicio'], 2, ',', '');
                        $row['ximp_servicio_cup_format'] = number_format($row['ximp_servicio_cup'], 2, ',', '');
                        $row['ximp_servicio_mlc_format'] = number_format($row['ximp_servicio_mlc'], 2, ',', '');
                        $row['ximp_servicio_otro_format'] = number_format($row['ximp_servicio_otro'], 2, ',', '');

                        $row['xestado_contabilidad'] = 'Sin contabilizar';
                        if ($row['xpendiente_contabilizar'] == 'S')
                            $row['xestado_contabilidad'] = 'Pendiente de contabilizar';
                        if ($row['xcontabilizado'] == 'S') {
                            $sql = "select *"
                                    . ",date_format(xdatetime,'%d/%m/%Y %H:%i:%s') as xfecha_contabilizado_format"
                                    . " from ms_movimientos_caja"
                                    . " where xobs like '%{$row['xpedido_id']}%'";
                            //print($sql);
                            //die();
                            $conta = $this->db->fetchRow($sql);

                            $row['xestado_contabilidad'] = "Contabilizado fecha: {$conta['xfecha_contabilizado_format']}";
                        }


                        $data = $row;
                        $data['xseguro-defecto'] = $data_form['config']['xseguro'];
                        $data['xvalor_cup_format'] = number_format($data['xvalor_cup'], 2, ',', '');
                        $data['xvalor_mlc_format'] = number_format($data['xvalor_mlc'], 2, ',', '');
                        $data['xvalor_otro_format'] = number_format($data['xvalor_otro'], 2, ',', '');
                        $data['xalmacenes_municipios'] = $rev['xalmacenes_municipios'];

                        $data['items'] = $this->app->get_list_pedido_lineas($data);
                        //print_r($data['items']);
                        //die();

                        $page['title'] = 'Pedido: ' . $row['xpedido_id'];
                        $page['subtitle'] = 'Pedido: ' . $row['xpedido_id'];

                        $data['xtracking'] = '';
                        $data['xcorte_id'] = '';
                        $data['xtransporte_free'] = $data_form['config']['xtransporte_free'];
                        $sql = "select * from ms_pedidos_proveedor where xpedido_id={$data['xpedido_id']} and xpreparado='S' and xservicio='N'";
                        //print($sql);
                        //die();
                        $rr = $this->db->fetchAll($sql);
                        $tmp = array();
                        foreach ($rr as $ke => $ve) {
                            //$tmp[] = '<a href="tracking.ms?ref=' . $data['xhash'] . '&track=' . $ve['xtracking'] . '">' . $ve['xtracking'] . '</a>';
                            $tmp[] = $ve['xtracking'];
                            $data['xcorte_id'] = $ve['xcorte_id'];
                        }
                        if (count($tmp) > 0) {
                            $data['xtracking'] = join(', ', $tmp);
                        }
                    }
                } else {
                    $data['xfecha_format'] = date('d/m/Y');

                    $data['xbase'] = '0,00';
                    $data['xbase_format'] = '0,00';
                    $data['ximp_servicio'] = '0,00';
                    $data['ximp_servicio_format'] = '0,00';
                    $data['xvalor_cup'] = $data_form['config']['xvalor_cup'];
                    $data['xvalor_cup_format'] = number_format($data_form['config']['xvalor_cup'], 2, ',', '');
                    $data['xvalor_mlc'] = $data_form['config']['xvalor_mlc'];
                    $data['xvalor_mlc_format'] = number_format($data_form['config']['xvalor_mlc'], 2, ',', '');
                    $data['xvalor_otro'] = $data_form['config']['xvalor_otro'];
                    $data['xvalor_otro_format'] = number_format($data_form['config']['xvalor_otro'], 2, ',', '');
                    $data['xbase_cup_format'] = '0,00';
                    $data['xbase_mlc_format'] = '0,00';
                    $data['xbase_otro_format'] = '0,00';
                    $data['ximp_servicio_cup_format'] = '0,00';
                    $data['xseguro'] = '0,00';
                    $data['xseguro-defecto'] = $data_form['config']['xseguro'];
                    $data['xdestino_id'] = '1';
                    $data['xpreparado'] = 'N';
                    $data['xship_country'] = 'CU';
                    $data['xip'] = $_SERVER['REMOTE_ADDR'];
                    $data['xtransporte_free'] = $data_form['config']['xtransporte_free'];
                    $data['xtransporte_format'] = number_format($data_form['config']['xtransporte'], 2, ',', '');
                    $data['ximporte'] = $data['xtransporte_format'];
                    $data['xtransporte'] = $data_form['config']['xtransporte'];
                    $data['xrevendedor_id'] = '';
                    if ($this->app->punto_venta != '') {
                        $data['xrevendedor_id'] = $this->app->punto_venta;
                    }
                    if ($this->app->almacen != '') {
                        $data['xalmacen_id'] = $this->app->almacen;
                    }
                    //$data['xfpago'] = 'USD efectivo';
                    $data['xfpago'] = '';
                    $data['xpagado'] = 'N';

                    $data['xporc_servicio'] = $data_form['config']['xporc_servicio'];
                    $data['xporc_servicio_format'] = number_format($data_form['config']['xporc_servicio'], 2, ',', '');
                    $data['xtransporte_cup_format'] = number_format(number_format($data_form['config']['xtransporte'], 0, '', '') * $data['xvalor_cup'], 2, ',', '');
                    $data['xtransporte_mlc_format'] = number_format($data_form['config']['xtransporte'] * $data['xvalor_mlc'], 2, ',', '');
                    $data['xtransporte_otro_format'] = number_format($data_form['config']['xtransporte'] * $data['xvalor_otro'], 2, ',', '');
                    $data['ximporte_cup'] = $data['xtransporte_cup_format'];
                    $data['ximporte_mlc'] = $data['xtransporte_mlc_format'];
                    $data['ximporte_otro'] = $data['xtransporte_otro_format'];

                    $data['xestado_contabilidad'] = 'Sin contabilizar';
                    $data['xalmacenes_municipios'] = $rev['xalmacenes_municipios'];

                    //print_r($data);
                    //die();
                }

                //print_r($data);
                //die();

                break;
        }
    }

    private function _view_details($param) {
        $data = array(
            'status' => 1,
            'items' => array()
        );

        $val = array(
            'ord' => $param['id']
        );

        $sql = "select a.*,b.xestado as xestado_desc"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_pedido_format"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " where a.xeliminado=0 and xpedido_id=:ord";
        //print($sql);
        //die();
        $data['ord'] = $this->db->fetchRow($sql, $val);

        $sql = "select *"
                . " from ms_pedidos_lin a"
                . " where xpedido_id=:ord";
        $data['ord-lin'] = $this->db->fetchAll($sql, $val);

        $data['items'] = $this->_calcular_estado($data);

        return $data;
    }

    private function _dl_xls($param) {
        $data = array();
        $sql = "select a.*,b.*,c.xestado as xestado_desc,d.xnivel
            ,a.xhash as xref_pedido
            ,a.ximporte as ximporte_total,b.ximporte as ximporte_lin
            ,date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format
            from ms_pedidos a
            left join ms_pedidos_lin b on a.xpedido_id=b.xpedido_id
            left join ms_estados c on a.xestado=c.xestado_id
            left join ms_clientes d on a.xcliente_id=d.xcliente_id
            where a.xeliminado=0";
        $rr = $this->app->db->fetchAll($sql);

        foreach ($rr as $k => $v) {
            for ($i = 1; $i <= $v['xcantidad']; $i++) {
                $v['xorden'] = $i;
                $v['xref_tracking'] = $this->app->get_ref_track($v);
                $data[] = $v;
            }
        }

        //$this->app->download_list_orders($data);
        //print(json_encode($data));
    }

    private function dl_doc($param) {
        include_once(BASE_CLASS . '/class.print-pedidos.php');
        //include_once(BASE_CLASS . '/class.print-pedidos.php');
        //$a = new Print_Pedidos($this->app);
        //die('222');
        //$a->printAlb($this->app->empresa_id, $param['albs']);
    }

    private function _chg_estado($param) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = array(
            'status' => 1,
            'msg' => 'Pedido cambiado de estado correctamente.',
            'estado' => 0
        );

        $cfg = $this->app->get_configuraciones();

        //CUP efectivo
        //print_r($fp['CUP efectivo']['moneda']==='cup');
        //die();

        $val = array(
            'ord' => $param['ord']
        );
        $sql = "select * from ms_pedidos where xpedido_id=:ord";
        //print_r($val);
        //die($sql);
        $ord = $this->db->fetchRow($sql, $val);
        $ord['ximporte_format'] = number_format($ord['ximporte'], 2, ',', '');
        $ord['xcoste_revendedor_format'] = number_format($ord['xcoste_revendedor'], 2, ',', '');

        //print_r($ord);
        //die();
        //print_r($fp[$ord['xfpago']]['moneda']);
        //print($formas_pago_pv[$ord['xfpago']]['moneda']);
        //print_r($formas_pago_pv);
        //print_r($ord['xestado']);
        //print(FORMAS_PAGO_PV_JSON);
        //print($cfg['xvalor_cup']);
        //die();
        //COMPROBAMOS QUE NO TIENE TRACKING Y QUE SE PUEDE CAMBIAR DE ESTADO
        $val = array(
            'ord' => $param['ord']
        );
        $sql = "select * from ms_pedidos_proveedor where xpedido_id=:ord and xtracking is not null";
        $row = $this->app->db->fetchRow($sql, $val);
        if ($row) {
            $data['status'] = 0;
            $data['msg'] = 'No se puede modificar el estado ya que tiene tracking asignado.';
            if (in_array($this->app->rol, array(1)) && $param['id'] == 3) {
                $data['emergency'] = 1;
                $data['msg'] .= ' <strong>Activado botón de emergencia.</strong>';
            }
        } else {
            $where = array(
                'xpedido_id' => $param['ord']
            );
            $row = $this->app->db->del('ms_pedidos_proveedor', $where);
        }

        if ($data['status'] == 1 && ($ord['xestado'] == 4 || $ord['xestado'] == 5 || $ord['xestado'] == 6 || $ord['xestado'] == 7 || $ord['xestado'] == 12 || $ord['xestado'] == 17 || $ord['xestado'] == 23 || $ord['xestado'] == 24)) {
            $data['status'] = 0;
            $data['msg'] = 'El estado actual del pedido no permite cambiar de estado.';
            //if ($this->app->rol == 1) {
            if (in_array($this->app->rol, array(1)) && $param['id'] == 3) {
                $data['emergency'] = 1;
                $data['msg'] .= ' <strong>Activado botón de emergencia.</strong>';
            }
        }

        if ($data['status'] == 1 && ($param['id'] == 22 || $param['id'] == 23 || $param['id'] == 24)) {
            $data['status'] = 0;
            $data['msg'] = 'No puede cambiar a este estado.';
        }

        if ($data['status'] == 1 && in_array($param['id'], array(12, 17))) {
            //SI EL CAMBIO DE ESTADO ES PARA VERIFICADO COMPROBAMOS LOS ALMACENES QUE TENGAN STOCK SI NO TIENE
            //STOCK NO PUEDE CONTINUAR CON EL PEDIDO
            //RECOGEMOS TODOS LOS ARTICULOS DEL PEDIDO Y RECORREMOS SI TIENEN COMPONENTES
            $sql = "select * from ms_pedidos_lin where xpedido_id={$ord['xpedido_id']}";
            $arts = $this->db->fetchAll($sql);
            //print_r($arts);
            //die();
            foreach ($arts as $ka => $va) {
                //print($va['xarticulo_id']);
                //die();
                $sql = "select * from ms_articulos where xarticulo_id={$va['xarticulo_id']}";
                $art = $this->db->fetchRow($sql);

                $sql = "select * from ms_articulos_componentes where xarticulo_id={$va['xarticulo_id']}";
                $art_com = $this->db->fetchAll($sql);
                //if ($art['xalmacen_id'] != '0' && count($art_com) > 0) {
                if (count($art_com) > 0) {
                    //SI EXISTE ALMACEN DE SALIDA Y COMPONENTES ASOCIADOS REALIZAMOS LA SALIDA DE COMPONENTES
                    foreach ($art_com as $k => $v) {
                        //$v['xcantidad'] = $v['xcantidad'] * (-1);
                        //CANTIDAD A DESCONTAR ES LA CANTIDAD DE LA LINEA MENOS LA CANTIDAD INCLUIDA DEL COMPONENTE EN ESE ARTÍCULO
                        $cantidad_pedida = ($v['xcantidad']) * $va['xcantidad'];

                        //DE QUÉ ALMACÉN RESTAMOS
                        $almacen = $art['xalmacen_id'];
                        if ($ord['xrevendedor_id'] > 0 && isset($rev['xrevendedor_id']) && $rev['xalmacen_id'] > 0) {
                            //SI EL PEDIDO ES DE UNA AGENCIA Y TENEMOS LOS DATOS DEL REVENDEDOR
                            // Y SI TIENE UN ALMACEN ASIGNADO QUE TIENE QUE SER MAYOR QUE CERO
                            $almacen = $rev['xalmacen_id'];
                        }
                        if ($ord['xalmacen_id'] > 0) {
                            //SI EL PEDIDO TIENE UN ALMACÉN SE LE ASIGNA EL ALMACÉN DEL PEDIDO
                            $almacen = $ord['xalmacen_id'];
                        }

                        $sql = "select * from ms_componentes_almacen"
                                . " where xalmacen_id={$almacen} and xcomponente_id={$v['xcomponente_id']}";
                        $sto = $this->db->fetchRow($sql);
                        if (!isset($sto['xstock'])) {
                            $sto['xstock'] = 0;
                        }
                        //print($cantidad_pedida);
                        //print_r($sto);
                        //die();
                        if ($cantidad_pedida > $sto['xstock']) {
                            $data['status'] = 0;
                            $data['msg'] = "<div>El producto {$art['xarticulo_id']} {$art['xarticulo']} que contiene el componente {$v['xcomponente_id']} supera la cantidad pedida {$cantidad_pedida} al stock existente {$sto['xstock']} en el almacén {$almacen} .</div>";
                        }
                    }
                }
            }
        }

        //###############################REVENDEDORES
        if ($ord['xrevendedor_id'] > 0) {
            $deposito_before = 0;
            $deposito_after = 0;
            $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
            $rev = $this->db->fetchRow($sql);
            $deposito_before = $rev['xdeposito'];
            //print_r($rev);
            //die();

            if ($data['status'] == 1 && $param['id'] == 12) {
                if ($rev['xdeposito'] >= $ord['xcoste_revendedor']) {
                    //RESTAMOS CRÉDITO AL REVENDEDOR POR IMPORTE DEL PEDIDO
                    $update = array(
                        'xdeposito' => $rev['xdeposito'] - $ord['xcoste_revendedor']
                    );
                    $where = array(
                        'xrevendedor_id' => $ord['xrevendedor_id']
                    );
                    $this->db->update('ms_revendedores', $update, $where);

                    $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
                    $revendedor = $this->db->fetchRow($sql);
                    $deposito_after = $revendedor['xdeposito'];

                    $insert = array(
                        'xrevendedor_id' => $ord['xrevendedor_id'],
                        'xobs' => "Actualización de crédito por pedido en el sistema código: {$ord['xhash']} crédito: {$ord['xcoste_revendedor_format']} USD",
                        'xdatetime' => date(dateSQL),
                        'xtipo' => 'SC',
                        'xpedido_id' => $ord['xpedido_id'],
                        'xreferencia' => $ord['xhash'],
                        'ximporte' => $ord['xcoste_revendedor'],
                        'xdeposito_before' => $deposito_before,
                        'xdeposito_after' => $deposito_after
                    );
                    $this->db->insert('ms_log_revendedor', $insert);
                } else {

                    $data['status'] = 0;
                    $data['msg'] = 'Crédito insuficiente para verificar el pedido.';

                    $insert = array(
                        'xrevendedor_id' => $ord['xrevendedor_id'],
                        'xobs' => "Crédito insuficiente para verificar pedido en el sistema código: {$ord['xhash']} crédito: {$ord['xcoste_revendedor_format']} USD",
                        'xdatetime' => date(dateSQL),
                        'xcolor' => 'danger',
                        'xtipo' => 'ERR',
                        'ximporte' => $ord['xcoste_revendedor'],
                        'xpedido_id' => $ord['xpedido_id'],
                        'xreferencia' => $ord['xhash']
                    );
                    $this->db->insert('ms_log_revendedor', $insert);
                }
            } else {
                if ($data['status'] == 1 && $ord['xestado'] == 12) {
                    //AÑADIMOS CRÉDITO AL REVENDEDOR POR IMPORTE DEL PEDIDO
                    $update = array(
                        'xdeposito' => $rev['xdeposito'] + $ord['ximporte']
                    );
                    $where = array(
                        'xrevendedor_id' => $ord['xrevendedor_id']
                    );
                    $this->db->update('ms_revendedores', $update, $where);

                    $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
                    $revendedor = $this->db->fetchRow($sql);
                    $deposito_after = $revendedor['xdeposito'];

                    $ord['ximporte_format'] = number_format($ord['ximporte'], 2, ',', '');

                    $insert = array(
                        'xrevendedor_id' => $ord['xrevendedor_id'],
                        'xobs' => "Reembolso de crédito por gestores de Growsolutions Orden: {$ord['xhash']} crédito: {$ord['xcoste_revendedor_format']} USD",
                        'xdatetime' => date(dateSQL),
                        'xtipo' => 'AC', //ANULACION DE CREDITO
                        'xpedido_id' => $ord['xpedido_id'],
                        'xreferencia' => $ord['xhash'],
                        'ximporte' => $ord['xcoste_revendedor'],
                        'xdeposito_before' => $deposito_before,
                        'xdeposito_after' => $deposito_after
                    );
                    $this->db->insert('ms_log_revendedor', $insert);
                }
            }

            //CONTABILIDAD DE PEDIDOS DE PUNTO DE VENTA (PV)
            if ($data['status'] == 1 && in_array($param['id'], array(12, 17))) {
                //AÑADIR A CONTABILIDAD ENTRADA DE DINERO
                $fp = json_decode(FORMAS_PAGO_PV_JSON, true);
                $imp_contabilidad = $ord['ximporte'];
                $cambio = 0;
                if ($fp[$ord['xfpago']]['moneda'] == 'cup') {
                    $cambio = $cfg['xvalor_cup'];
                    //$imp_contabilidad = $ord['ximporte'] * $cambio;
                    $imp_contabilidad = $ord['ximporte_cup'];
                }

                if ($fp[$ord['xfpago']]['moneda'] == 'mlc') {
                    $cambio = $cfg['xvalor_mlc'];
                    //$imp_contabilidad = $ord['ximporte'] * $cambio;
                    $imp_contabilidad = $ord['ximporte_mlc'];
                }

                if ($fp[$ord['xfpago']]['moneda'] == 'otro') {
                    $cambio = $cfg['xvalor_otro'];
                    //$imp_contabilidad = $ord['ximporte'] * $cambio;
                    $imp_contabilidad = $ord['ximporte_otro'];
                }
                $imp_contabilidad_format = number_format($imp_contabilidad, 2, ',', '');
                $insert = array(
                    'xrevendedor_id' => $ord['xrevendedor_id'],
                    'xobs' => "Verificación PV {$ord['xpedido_id']} {$ord['xhash']} de {$imp_contabilidad_format} en " . strtoupper($fp[$ord['xfpago']]['moneda']),
                    'xtransaccion' => '',
                    'xdatetime' => date(dateSQL),
                    'xtipo' => 'E',
                    'xfpago' => $ord['xfpago'],
                    'ximporte' => $imp_contabilidad,
                    'xpedido_id' => $ord['xpedido_id']
                );
                $this->app->db->insert('ms_movimientos_caja_pv', $insert);
            }
        }
        //#############################/REVENDEDORES


        if ($data['status'] == 1) {
            $update = array(
                'xestado' => $param['id'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );

            //SI EL ESTADO ES VERIFICADO SE PONE LA FECHA DE PAGO
            if ($param['id'] == 12 || $param['id'] == 17) {
                $update['xfecha_pago'] = date(dateSQL);
            }

            $where = array(
                'xpedido_id' => $param['ord']
            );
            $this->app->db->update('ms_pedidos', $update, $where);

            $data['estado'] = $param['id'];

            $insert = array(
                'xpedido_id' => $param['ord'],
                'xuseralta_id' => $this->app->user_id,
                'xdatealta' => date(dateSQL),
                'xestado' => $param['id'],
                'xobs' => $param['obs']
            );
            $this->app->db->insert('ms_pedidos_historico', $insert);

            //$this->_generate_new_ref_servicios($ord['xpedido_id']);

            if ($param['id'] == 17) {
                $sql = "select * from ms_pedidos where xpedido_id={$param['ord']}";
                $ord = $this->db->fetchRow($sql);

                $sql = "select * from ms_clientes where xcliente_id={$ord['xcliente_id']}";
                $cli = $this->db->fetchRow($sql);

                $sql = "select * from ms_cortes_envios where xactivo='S' order by xcorte_id desc limit 1";
                $corte = $this->db->fetchRow($sql);

                $sql = "select * from ms_cortes_destinos where xcorte_id={$corte['xcorte_id']} and xdestino_id={$ord['xdestino_id']}";
                $destino = $this->db->fetchRow($sql);

                $content = array(
                    'asunto' => 'MandaSaldo: Confirmación de pago recibido.',
                    'email' => $cli['xemail'],
                    'cliente' => $cli['xcliente_id'],
                    'pedido' => $ord['xpedido_id'],
                    'title' => 'Confirmación de pago recibido',
                    'hello' => "Estimado {$cli['xnombre']}" . (($cli['xnombre2'] != '') ? ' ' . $cli['xnombre2'] : '') . ",",
                    'header' => "¡Nueva actualización de estado de su compra!",
                    'body' => "Le confirmamos que hemos recibido el pago de <strong>" . number_format($ord['ximporte'], 2, ',', '') . " USD</strong> de su pedido con referencia <strong>{$ord['xhash']}</strong> y ya lo tiene disponible para consulta dentro de su perfil de usuario.<br><br>Para más información sobre el estado de su pedido puede acceder a su perfil de usuario dentro de nuestra página web.<br><br>https://mandasaldo.com/perfil.ms<br><br>Le estaremos enviando en los próximos días más actualizaciones de estado de su compra.<br><br>Atentamente,<br>Departamento de Pedidos MandaSaldo."
                );
                if ($ord['xrevendedor_id'] == 0)
                    $this->app->send_aviso_email($content);
            }

            //CONTROL STOCK EN EL CASO QUE SEA UN ARTÍCULO CON COMPONENTES
            if ($param['id'] == 12 || $param['id'] == 17) {
                //$ord YA VIENE CON DATOS RELLENADOS
                //$sql = "select * from ms_pedidos where xpedido_id={$param['ord']}";
                //$ord = $this->db->fetchRow($sql);
                //RECOGEMOS LOS DATOS DEL REPARTO Y SUS COMPONENTES Y HACEMOS SALIDA DEL ALMACÉN
                if ($ord['xmov'] == 'N') {
                    //GENERAMOS MOVIMIENTOS DE CAJA EN EL CASO QUE SEA UN PV ASOCIADA A UNA CAJA
                    //if ($this->app->user_id == 1) {
                    /*
                      $fp = json_decode(FORMAS_PAGO_PV_JSON, true);
                      $fpago = $ord['xfpago'];
                      $moneda = $fp[$ord['xfpago']]['moneda'];
                      $importe = 0; //INICIALIZAMOS IMPORTE
                      if ($moneda == 'cup') {
                      $importe = $ord['ximporte_cup'];
                      }
                      if ($moneda == 'usd') {
                      $importe = $ord['ximporte'];
                      }

                      $insert = array(
                      'xpedido_id' => $ord['xpedido_id'],
                      'xusuario_id' => $this->app->user_id,
                      'xfecha' => date(dateSQL),
                      'xconcepto' => "Verificación Pedido {$ord['xpedido_id']}/{$ord['xhash']} FPago: {$fpago} Moneda: {$moneda} Importe: {$importe}",
                      'xtype' => 'E',
                      'xrevendedor_id' => $ord['xrevendedor_id'],
                      'xfpago' => $ord['xfpago'],
                      'ximporte_before' => 0,
                      'ximporte_after' => 0,
                      'ximporte' => $importe,
                      'xmoneda' => $moneda
                      );
                      $this->app->db->insert('ms_cajas_movimientos', $insert);
                      $data['mov-con'] = $this->db->last_id();

                      include_once(BASE_CLASS . '/mdl.Contabilidad.php');
                      $con = new Contabilidad($this->app);
                      $con->exec_movimiento_contabilidad($data['mov-con']);
                     * 
                     */
                    //}
                    //RECOGEMOS TODOS LOS ARTICULOS DEL PEDIDO Y RECORREMOS SI TIENEN COMPONENTES
                    $sql = "select * from ms_pedidos_lin where xpedido_id={$ord['xpedido_id']}";
                    $arts = $this->db->fetchAll($sql);
                    //print_r($arts);
                    //die();
                    foreach ($arts as $ka => $va) {
                        //print($va['xarticulo_id']);
                        //die();
                        $sql = "select * from ms_articulos where xarticulo_id={$va['xarticulo_id']}";
                        $art = $this->db->fetchRow($sql);

                        $sql = "select * from ms_articulos_componentes where xarticulo_id={$va['xarticulo_id']}";
                        $art_com = $this->db->fetchAll($sql);
                        //if ($art['xalmacen_id'] != '0' && count($art_com) > 0) {
                        if (count($art_com) > 0) {
                            //SI EXISTE ALMACEN DE SALIDA Y COMPONENTES ASOCIADOS REALIZAMOS LA SALIDA DE COMPONENTES
                            foreach ($art_com as $k => $v) {
                                //$v['xcantidad'] = $v['xcantidad'] * (-1);
                                //CANTIDAD A DESCONTAR ES LA CANTIDAD DE LA LINEA MENOS LA CANTIDAD INCLUIDA DEL COMPONENTE EN ESE ARTÍCULO
                                $v['xcantidad'] = ($v['xcantidad'] * (-1)) * $va['xcantidad'];
                                $va['xcantidad'] = number_format($va['xcantidad'], 0);

                                //DE QUÉ ALMACÉN RESTAMOS
                                $almacen = $art['xalmacen_id'];
                                if ($ord['xrevendedor_id'] > 0 && isset($rev['xrevendedor_id']) && $rev['xalmacen_id'] > 0) {
                                    //SI EL PEDIDO ES DE UNA AGENCIA Y TENEMOS LOS DATOS DEL REVENDEDOR
                                    // Y SI TIENE UN ALMACEN ASIGNADO QUE TIENE QUE SER MAYOR QUE CERO
                                    $almacen = $rev['xalmacen_id'];
                                }
                                if ($ord['xalmacen_id'] > 0) {
                                    //SI EL PEDIDO TIENE UN ALMACÉN SE LE ASIGNA EL ALMACÉN DEL PEDIDO
                                    $almacen = $ord['xalmacen_id'];
                                }

                                $sql = "select * from ms_componentes_almacen"
                                        . " where xalmacen_id={$almacen} and xcomponente_id={$v['xcomponente_id']}";
                                $sto = $this->db->fetchRow($sql);
                                if (!isset($sto['xstock'])) {
                                    $sto['xstock'] = 0;
                                }

                                $type = 'E';
                                if ($v['xcantidad'] < 0)
                                    $type = 'S';

                                //GENERAMOS MOVIMIENTO ALMACÉN
                                $insert = array(
                                    'xdoc_id' => $ord['xpedido_id'],
                                    'xcliente_id' => $ord['xcliente_id'],
                                    'xusuario_id' => $this->app->user_id,
                                    'xfecha' => date(dateSQL),
                                    'xcantidad' => $v['xcantidad'],
                                    'xconcepto' => "Salida Pedido {$ord['xpedido_id']} Art: {$art['xarticulo_id']} Cant: {$va['xcantidad']}",
                                    'xtype' => $type,
                                    'xalmacen_id' => $almacen,
                                    'xcomponente_id' => $v['xcomponente_id'],
                                    'xarticulo_id' => $art['xarticulo_id'],
                                    'xstock_before' => $sto['xstock'],
                                    'ximporte' => 0
                                );
                                $this->app->db->insert('ms_movimientos', $insert);
                                $data['id'] = $this->db->last_id();

                                if ($data['id'] > 0) {
                                    include_once(BASE_CLASS . '/mdl.Almacenes.php');
                                    $alm = new Almacen($this->app);
                                    $alm->exec_movimiento($data['id']);
                                    $param_csc = array(
                                        //'almacen' => $art['xalmacen_id'],
                                        'almacen' => $almacen,
                                        'articulo' => $v['xcomponente_id']
                                    );
                                    //print(json_encode($param_csc));
                                    //die();
                                    $alm->check_stock_componente($param_csc);
                                }
                            }
                        }
                    }

                    //MARCAMOS PEDIDO COMO MOVIMIENTO GENERADO PARA TODOS LOS ARTÍCULOS
                    $update = array(
                        'xmov' => 'S'
                    );
                    $where = array(
                        'xpedido_id' => $ord['xpedido_id']
                    );
                    $this->app->db->update('ms_pedidos', $update, $where);
                }
            } else {
                if ($ord['xmov'] == 'S') {
                    //GENERAMOS MOVIMIENTOS SALIDA DE CAJA EN EL CASO QUE SEA UN PV ASOCIADA A UNA CAJA
                    //if ($this->app->user_id == 1) {
                    /*
                      $fp = json_decode(FORMAS_PAGO_PV_JSON, true);
                      $fpago = $ord['xfpago'];
                      $moneda = $fp[$ord['xfpago']]['moneda'];
                      $importe = 0; //INICIALIZAMOS IMPORTE
                      if ($moneda == 'cup') {
                      $importe = $ord['ximporte_cup'];
                      }
                      if ($moneda == 'usd') {
                      $importe = $ord['ximporte'];
                      }

                      $insert = array(
                      'xpedido_id' => $ord['xpedido_id'],
                      'xusuario_id' => $this->app->user_id,
                      'xfecha' => date(dateSQL),
                      'xconcepto' => "Devolución Pago Pedido {$ord['xpedido_id']}/{$ord['xhash']} FPago: {$fpago} Moneda: {$moneda} Importe: {$importe}",
                      'xtype' => 'S',
                      'xrevendedor_id' => $ord['xrevendedor_id'],
                      'xfpago' => $ord['xfpago'],
                      'ximporte_before' => 0,
                      'ximporte_after' => 0,
                      'ximporte' => ($importe * (-1)),
                      'xmoneda' => $moneda
                      );
                      $this->app->db->insert('ms_cajas_movimientos', $insert);
                      $data['mov-con'] = $this->db->last_id();

                      include_once(BASE_CLASS . '/mdl.Contabilidad.php');
                      $con = new Contabilidad($this->app);
                      $con->exec_movimiento_contabilidad($data['mov-con']);
                     * 
                     */
                    //}
                    //RECOGEMOS TODOS LOS ARTICULOS DEL PEDIDO Y RECORREMOS SI TIENEN COMPONENTES
                    $sql = "select * from ms_pedidos_lin where xpedido_id={$ord['xpedido_id']}";
                    $arts = $this->db->fetchAll($sql);
                    foreach ($arts as $ka => $va) {
                        $sql = "select * from ms_articulos where xarticulo_id={$va['xarticulo_id']}";
                        $art = $this->db->fetchRow($sql);

                        $sql = "select * from ms_articulos_componentes where xarticulo_id={$va['xarticulo_id']}";
                        $art_com = $this->db->fetchAll($sql);
                        if ($art['xalmacen_id'] != '0' && count($art_com) > 0) {
                            //SI EXISTE ALMACEN DE SALIDA Y COMPONENTES ASOCIADOS REALIZAMOS LA SALIDA DE COMPONENTES
                            foreach ($art_com as $k => $v) {
                                //$v['xcantidad'] = $v['xcantidad'] * (-1);
                                //CANTIDAD A DESCONTAR ES LA CANTIDAD DE LA LINEA MENOS LA CANTIDAD INCLUIDA DEL COMPONENTE EN ESE ARTÍCULO
                                $v['xcantidad'] = ($v['xcantidad'] * (1)) * $va['xcantidad'];
                                $va['xcantidad'] = number_format($va['xcantidad'], 0);

                                //A QUÉ ALMACÉN AÑADIMOS
                                $almacen = $art['xalmacen_id'];
                                if ($ord['xrevendedor_id'] > 0 && isset($rev['xrevendedor_id']) && $rev['xalmacen_id'] > 0) {
                                    //SI EL PEDIDO ES DE UNA AGENCIA Y TENEMOS LOS DATOS DEL REVENDEDOR
                                    // Y SI TIENE UN ALMACEN ASIGNADO QUE TIENE QUE SER MAYOR QUE CERO
                                    $almacen = $rev['xalmacen_id'];
                                }
                                if ($ord['xalmacen_id'] > 0) {
                                    //SI EL PEDIDO TIENE UN ALMACÉN SE LE ASIGNA EL ALMACÉN DEL PEDIDO
                                    $almacen = $ord['xalmacen_id'];
                                }

                                $sql = "select * from ms_componentes_almacen"
                                        . " where xalmacen_id={$almacen} and xcomponente_id={$v['xcomponente_id']}";
                                $sto = $this->db->fetchRow($sql);

                                $type = 'E';
                                if ($v['xcantidad'] < 0)
                                    $type = 'S';

                                //DE QUÉ ALMACÉN RESTAMOS
                                $almacen = $art['xalmacen_id'];
                                if ($ord['xrevendedor_id'] > 0 && isset($rev['xrevendedor_id']) && $rev['xalmacen_id'] > 0) {
                                    //SI EL PEDIDO ES DE UNA AGENCIA Y TENEMOS LOS DATOS DEL REVENDEDOR
                                    // Y SI TIENE UN ALMACEN ASIGNADO QUE TIENE QUE SER MAYOR QUE CERO
                                    $almacen = $rev['xalmacen_id'];
                                }
                                if ($ord['xalmacen_id'] > 0) {
                                    //SI EL PEDIDO TIENE UN ALMACÉN SE LE ASIGNA EL ALMACÉN DEL PEDIDO
                                    $almacen = $ord['xalmacen_id'];
                                }

                                $insert = array(
                                    'xdoc_id' => $ord['xpedido_id'],
                                    'xcliente_id' => $ord['xcliente_id'],
                                    'xusuario_id' => $this->app->user_id,
                                    'xfecha' => date(dateSQL),
                                    'xcantidad' => $v['xcantidad'],
                                    'xconcepto' => "Devolución Pedido {$ord['xpedido_id']} Art: {$art['xarticulo_id']} Cant: {$va['xcantidad']}",
                                    'xtype' => $type,
                                    'xalmacen_id' => $almacen,
                                    'xcomponente_id' => $v['xcomponente_id'],
                                    'xarticulo_id' => $art['xarticulo_id'],
                                    'xstock_before' => $sto['xstock'],
                                    'ximporte' => 0
                                );
                                $this->app->db->insert('ms_movimientos', $insert);
                                $data['id'] = $this->db->last_id();

                                if ($data['id'] > 0) {
                                    include_once(BASE_CLASS . '/mdl.Almacenes.php');
                                    $alm = new Almacen($this->app);
                                    $alm->exec_movimiento($data['id']);
                                    $param_csc = array(
                                        'almacen' => $almacen,
                                        'articulo' => $v['xcomponente_id']
                                    );
                                    $alm->check_stock_componente($param_csc);
                                }
                            }
                        }
                    }

                    //MARCAMOS PEDIDO COMO MOVIMIENTO GENERADO PARA TODOS LOS ARTÍCULOS
                    $update = array(
                        'xmov' => 'N'
                    );
                    $where = array(
                        'xpedido_id' => $ord['xpedido_id']
                    );
                    $this->app->db->update('ms_pedidos', $update, $where);
                }
            }

            if (in_array($param['id'], array(12, 17, 23, 24, 4, 5, 6, 7))) {
                //GENERAMOS NEW REFERENCIAS
                $this->_generate_new_ref_servicios($ord['xpedido_id']);
            }
        }

        print(json_encode($data));
    }

    private function _chg_almacen($param) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = array(
            'status' => 1,
            'msg' => 'Almacén cambiado correctamente, movimientos creados correctamente.',
        );

        //$cfg = $this->app->get_configuraciones();
        $val = array(
            'ord' => $param['pedido']
        );
        $sql = "select * from ms_pedidos where xpedido_id=:ord";
        //print_r($val);
        //die($sql);
        $ord = $this->db->fetchRow($sql, $val);
        if ($ord) {

            $val = array(
                'ord' => $param['pedido']
            );
            $sql = "select * from ms_pedidos_lin where xpedido_id=:ord";
            //print_r($val);
            //die($sql);
            $ord['items'] = $this->db->fetchAll($sql, $val);
        }
        //print_r($ord);
        //die();
        //DEJAMOS RASTRO EN HISTORICO
        $insert = array(
            'xpedido_id' => $ord['xpedido_id'],
            'xuseralta_id' => $this->app->user_id,
            'xdatealta' => date(dateSQL),
            'xestado' => '0',
            'xobs' => "Cambio de almacén: del almacén {$ord['xalmacen_id']} al almacén {$param['alm-dst']}"
        );
        $this->app->db->insert('ms_pedidos_historico', $insert);

        //RECORREMOS LINEAS PEDIDO Y HACEMOS ENTRADA DE ALMACEN
        foreach ($ord['items'] as $ka => $va) {
            //$sql = "select * from ms_articulos where xarticulo_id={$va['xarticulo_id']}";
            //$art = $this->db->fetchRow($sql);
            //print_r($va);
            //die();

            $sql = "select * from ms_articulos_componentes where xarticulo_id=" . $va['xarticulo_id'];
            $art_com = $this->db->fetchAll($sql);
            //SI EXISTE ALMACEN DE SALIDA Y COMPONENTES ASOCIADOS REALIZAMOS LA ENTRADA DE COMPONENTES
            foreach ($art_com as $k => $v) {
                //CANTIDAD A AÑADIR ES LA CANTIDAD DE LA LINEA MENOS LA CANTIDAD INCLUIDA DEL COMPONENTE EN ESE ARTÍCULO
                $v['xcantidad'] = ($v['xcantidad'] * (1)) * $va['xcantidad'];
                $va['xcantidad'] = number_format($va['xcantidad'], 0);

                $sql = "select * from ms_componentes_almacen"
                        . " where xalmacen_id={$ord['xalmacen_id']} and xcomponente_id={$v['xcomponente_id']}";
                $sto = $this->db->fetchRow($sql);
                if (!isset($sto['xstock'])) {
                    $sto['xstock'] = 0;
                    $insert_alm = array(
                        'xcomponente_id' => $v['xcomponente_id'],
                        'xalmacen_id' => $ord['xalmacen_id'],
                        'xstock' => 0
                    );
                    $this->db->insert('ms_componentes_almacen', $insert_alm);
                }

                $type = 'E';
                if ($v['xcantidad'] < 0)
                    $type = 'S';
                $insert = array(
                    'xdoc_id' => $ord['xpedido_id'],
                    'xcliente_id' => $ord['xcliente_id'],
                    'xusuario_id' => $this->app->user_id,
                    'xfecha' => date(dateSQL),
                    'xcantidad' => $v['xcantidad'],
                    'xconcepto' => "Cambio almacén Pedido {$ord['xpedido_id']} Art: {$va['xarticulo_id']} Cant: {$va['xcantidad']} almacén: {$ord['xalmacen_id']}",
                    'xtype' => $type,
                    'xalmacen_id' => $ord['xalmacen_id'],
                    'xcomponente_id' => $v['xcomponente_id'],
                    'xarticulo_id' => $va['xarticulo_id'],
                    'xstock_before' => $sto['xstock'],
                    'ximporte' => 0
                );
                $this->app->db->insert('ms_movimientos', $insert);
                $data['id'] = $this->db->last_id();
            }
        }

        //ACTUALIZAMOS ALMACEN
        $update = array(
            'xalmacen_id' => $param['alm-dst']
        );
        $where = array(
            'xpedido_id' => $ord['xpedido_id']
        );
        $this->db->update('ms_pedidos', $update, $where);
        $ord['xalmacen_id'] = $param['alm-dst'];

        //RECORREMOS LINEAS PEDIDO Y HACEMOS ENTRADA DE ALMACEN
        foreach ($ord['items'] as $ka => $va) {
            //print($va['xarticulo_id']);
            //die();
            //$sql = "select * from ms_articulos where xarticulo_id={$va['xarticulo_id']}";
            //$art = $this->db->fetchRow($sql);

            $sql = "select * from ms_articulos_componentes where xarticulo_id={$va['xarticulo_id']}";
            $art_com = $this->db->fetchAll($sql);
            //SI EXISTE ALMACEN DE SALIDA Y COMPONENTES ASOCIADOS REALIZAMOS LA SALIDA DE COMPONENTES
            foreach ($art_com as $k => $v) {
                //CANTIDAD A DESCONTAR ES LA CANTIDAD DE LA LINEA MENOS LA CANTIDAD INCLUIDA DEL COMPONENTE EN ESE ARTÍCULO
                $v['xcantidad'] = ($v['xcantidad'] * (-1)) * $va['xcantidad'];
                $va['xcantidad'] = number_format($va['xcantidad'], 0);

                $sql = "select * from ms_componentes_almacen"
                        . " where xalmacen_id={$ord['xalmacen_id']} and xcomponente_id={$v['xcomponente_id']}";
                $sto = $this->db->fetchRow($sql);
                if (!isset($sto['xstock'])) {
                    $sto['xstock'] = 0;
                    $insert_alm = array(
                        'xcomponente_id' => $v['xcomponente_id'],
                        'xalmacen_id' => $ord['xalmacen_id'],
                        'xstock' => 0
                    );
                    $this->db->insert('ms_componentes_almacen', $insert_alm);
                }

                $type = 'E';
                if ($v['xcantidad'] < 0)
                    $type = 'S';

                //GENERAMOS MOVIMIENTO ALMACÉN
                $insert = array(
                    'xdoc_id' => $ord['xpedido_id'],
                    'xcliente_id' => $ord['xcliente_id'],
                    'xusuario_id' => $this->app->user_id,
                    'xfecha' => date(dateSQL),
                    'xcantidad' => $v['xcantidad'],
                    'xconcepto' => "Cambio de almacén Pedido {$ord['xpedido_id']} Art: {$va['xarticulo_id']} Cant: {$va['xcantidad']} almacén: {$ord['xalmacen_id']}",
                    'xtype' => $type,
                    'xalmacen_id' => $ord['xalmacen_id'],
                    'xcomponente_id' => $v['xcomponente_id'],
                    'xarticulo_id' => $va['xarticulo_id'],
                    'xstock_before' => $sto['xstock'],
                    'ximporte' => 0
                );
                $this->app->db->insert('ms_movimientos', $insert);
                $data['id'] = $this->db->last_id();
            }
        }

        print(json_encode($data));
    }

    private function _load_lines($param) {
        $data = array(
            'status' => 1,
            'items' => array(),
            'rev_id' => $param['rev'],
            'rev' => ''
        );

        $val = array(
            'ord' => $param['ord']
        );

        $sql = "select * from ms_pedidos_lin where xestado=0 and xpedido_id=:ord";
        $data['items'] = $this->db->fetchAll($sql, $val);

        if ($param['rev'] > 0) {
            $val = array(
                'rev' => $param['rev']
            );
            $sql = "select * from ms_revendedores where xrevendedor_id=:rev";
            $rev = $this->db->fetchRow($sql, $val);

            if ($rev) {
                $imp = number_format($rev['xdeposito'], 2, ',', '');
                $data['rev'] = "{$rev['xrevendedor_id']} - {$rev['xrevendedor']} Crédito actual: $imp USD";
            }
        }

        print(json_encode($data));
    }

    private function _cancel_order($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Pedido cancelado correctamente.'
        );

        $cfg = $this->app->get_configuraciones();

        $val = array(
            'ord' => $param['ord']
        );

        $sql = "select * from ms_pedidos where xpedido_id=:ord";
        $ord = $this->db->fetchRow($sql, $val);
        $ord['ximporte_format'] = number_format($ord['ximporte'], 2, ',', '');
        $ord['xcoste_revendedor_format'] = number_format($ord['xcoste_revendedor'], 2, ',', '');

        //ELIMINAR ENVIOS CON TRACKING O SIN TRACKING
        $where = array(
            'xpedido_id' => $param['ord']
        );
        $row = $this->app->db->del('ms_pedidos_proveedor', $where);

        //ESTADO PEDIDO: CANCELADO
        $update = array(
            'xestado' => '3',
            'xchofer_id' => '0',
            'xusermodif_id' => $this->app->user_id,
            'xdatemodif' => date(dateSQL)
        );
        $where = array(
            'xpedido_id' => $param['ord']
        );
        $this->app->db->update('ms_pedidos', $update, $where);

        $sql = "update ms_pedidos set xfecha_pago=null,xchofer_id=0 where xpedido_id={$param['ord']}";
        $this->app->db->directExec($sql);

        //DEJAMOS RASTRO EN HISTORICO
        $insert = array(
            'xpedido_id' => $param['ord'],
            'xuseralta_id' => $this->app->user_id,
            'xdatealta' => date(dateSQL),
            'xestado' => '3',
            'xobs' => 'Cancelación Pedido'
        );
        $this->app->db->insert('ms_pedidos_historico', $insert);

        //###############################REVENDEDORES
        if ($ord['xrevendedor_id'] > 0) {
            $deposito_before = 0;
            $deposito_after = 0;
            $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
            $rev = $this->db->fetchRow($sql);
            $deposito_before = $rev['xdeposito'];

            //AÑADIMOS CRÉDITO AL REVENDEDOR POR IMPORTE DEL PEDIDO
            $update = array(
                'xdeposito' => $rev['xdeposito'] + $ord['ximporte']
            );
            $where = array(
                'xrevendedor_id' => $ord['xrevendedor_id']
            );
            $this->db->update('ms_revendedores', $update, $where);

            $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
            $revendedor = $this->db->fetchRow($sql);
            $deposito_after = $revendedor['xdeposito'];

            $ord['ximporte_format'] = number_format($ord['ximporte'], 2, ',', '');

            $insert = array(
                'xrevendedor_id' => $ord['xrevendedor_id'],
                'xobs' => "Reembolso de crédito por gestores de Growsolutions Orden: {$ord['xhash']} crédito: {$ord['xcoste_revendedor_format']} USD",
                'xdatetime' => date(dateSQL),
                'xtipo' => 'AC', //ANULACION DE CREDITO
                'xpedido_id' => $ord['xpedido_id'],
                'xreferencia' => $ord['xhash'],
                'ximporte' => $ord['xcoste_revendedor'],
                'xdeposito_before' => $deposito_before,
                'xdeposito_after' => $deposito_after
            );
            $this->db->insert('ms_log_revendedor', $insert);

            //CONTABILIDAD PV
            //AÑADIR A CONTABILIDAD SALIDA DE DINERO, AÑADIR EN NEGATIVO
            $fp = json_decode(FORMAS_PAGO_PV_JSON, true);
            $imp_contabilidad = $ord['ximporte'];
            $cambio = 0;
            if ($fp[$ord['xfpago']]['moneda'] == 'cup') {
                $cambio = $cfg['xvalor_cup'];
//                $imp_contabilidad = $ord['ximporte'] * $cambio;
                $imp_contabilidad = $ord['ximporte_cup'];
            }

            if ($fp[$ord['xfpago']]['moneda'] == 'mlc') {
                $cambio = $cfg['xvalor_mlc'];
                //$imp_contabilidad = $ord['ximporte'] * $cambio;
                $imp_contabilidad = $ord['ximporte_mlc'];
            }

            if ($fp[$ord['xfpago']]['moneda'] == 'otro') {
                $cambio = $cfg['xvalor_otro'];
                //$imp_contabilidad = $ord['ximporte'] * $cambio;
                $imp_contabilidad = $ord['ximporte_otro'];
            }
            $insert = array(
                'xrevendedor_id' => $ord['xrevendedor_id'],
                'xobs' => "Anulación Verificación PV {$ord['xpedido_id']} {$ord['xhash']} de {$imp_contabilidad} en " . strtoupper($fp[$ord['xfpago']]['moneda']),
                'xtransaccion' => '',
                'xdatetime' => date(dateSQL),
                'xtipo' => 'S',
                'xfpago' => $ord['xfpago'],
                'ximporte' => $imp_contabilidad * (-1),
                'xpedido_id' => $ord['xpedido_id']
            );
            $this->app->db->insert('ms_movimientos_caja_pv', $insert);
        }
        //#############################/REVENDEDORES

        if ($ord['xmov'] == 'S') {
            //GENERAMOS MOVIMIENTOS SALIDA DE CAJA EN EL CASO QUE SEA UN PV ASOCIADA A UNA CAJA
            //if ($this->app->user_id == 1) {
            /*
              $fp = json_decode(FORMAS_PAGO_PV_JSON, true);
              $fpago = $ord['xfpago'];
              $moneda = $fp[$ord['xfpago']]['moneda'];
              $importe = 0; //INICIALIZAMOS IMPORTE
              if ($moneda == 'cup') {
              $importe = $ord['ximporte_cup'];
              }
              if ($moneda == 'usd') {
              $importe = $ord['ximporte'];
              }

              $insert = array(
              'xpedido_id' => $ord['xpedido_id'],
              'xusuario_id' => $this->app->user_id,
              'xfecha' => date(dateSQL),
              'xconcepto' => "Cancelación Pedido {$ord['xpedido_id']}/{$ord['xhash']} FPago: {$fpago} Moneda: {$moneda} Importe: {$importe}",
              'xtype' => 'S',
              'xrevendedor_id' => $ord['xrevendedor_id'],
              'xfpago' => $ord['xfpago'],
              'ximporte_before' => 0,
              'ximporte_after' => 0,
              'ximporte' => ($importe * (-1)),
              'xmoneda' => $moneda
              );
              $this->app->db->insert('ms_cajas_movimientos', $insert);
              $data['mov-con'] = $this->db->last_id();

              include_once(BASE_CLASS . '/mdl.Contabilidad.php');
              $con = new Contabilidad($this->app);
              $con->exec_movimiento_contabilidad($data['mov-con']);
             * 
             */
            //}



            $alm_rev = $ord['xalmacen_id'];

            /*
              if ($ord['xrevendedor_id'] > 0 && isset($revendedor) && isset($revendedor['xalmacen_id']) && $revendedor['xalmacen_id'] > 0) {
              $alm_rev = $revendedor['xalmacen_id'];
              }
             * 
             */

            //RECOGEMOS TODOS LOS ARTICULOS DEL PEDIDO Y RECORREMOS SI TIENEN COMPONENTES
            $sql = "select * from ms_pedidos_lin where xpedido_id={$ord['xpedido_id']}";
            $arts = $this->db->fetchAll($sql);
            foreach ($arts as $ka => $va) {
                $sql = "select * from ms_articulos where xarticulo_id={$va['xarticulo_id']}";
                $art = $this->db->fetchRow($sql);

                if ($alm_rev == 0) {
                    $alm_rev = $art['xalmacen_id'];
                }

                $sql = "select * from ms_articulos_componentes where xarticulo_id={$va['xarticulo_id']}";
                $art_com = $this->db->fetchAll($sql);
                if ($alm_rev > 0 && count($art_com) > 0) {
                    //SI EXISTE ALMACEN DE SALIDA Y COMPONENTES ASOCIADOS REALIZAMOS LA ENTRADA DE COMPONENTES
                    foreach ($art_com as $k => $v) {
                        //$v['xcantidad'] = $v['xcantidad'] * (-1);
                        //CANTIDAD A AÑADIR ES LA CANTIDAD DE LA LINEA MENOS LA CANTIDAD INCLUIDA DEL COMPONENTE EN ESE ARTÍCULO
                        $v['xcantidad'] = ($v['xcantidad'] * (1)) * $va['xcantidad'];
                        $va['xcantidad'] = number_format($va['xcantidad'], 0);

                        $sql = "select * from ms_componentes_almacen"
                                . " where xalmacen_id={$alm_rev} and xcomponente_id={$v['xcomponente_id']}";
                        $sto = $this->db->fetchRow($sql);

                        $type = 'E';
                        if ($v['xcantidad'] < 0)
                            $type = 'S';
                        $insert = array(
                            'xdoc_id' => $ord['xpedido_id'],
                            'xcliente_id' => $ord['xcliente_id'],
                            'xusuario_id' => $this->app->user_id,
                            'xfecha' => date(dateSQL),
                            'xcantidad' => $v['xcantidad'],
                            'xconcepto' => "Cancelación Pedido {$ord['xpedido_id']} Art: {$art['xarticulo_id']} Cant: {$va['xcantidad']} en almacén: {$alm_rev}",
                            'xtype' => $type,
                            'xalmacen_id' => $alm_rev,
                            'xcomponente_id' => $v['xcomponente_id'],
                            'xarticulo_id' => $art['xarticulo_id'],
                            'xstock_before' => $sto['xstock'],
                            'ximporte' => 0
                        );
                        $this->app->db->insert('ms_movimientos', $insert);
                        $data['id'] = $this->db->last_id();

                        if ($data['id'] > 0) {
                            include_once(BASE_CLASS . '/mdl.Almacenes.php');
                            $alm = new Almacen($this->app);
                            $alm->exec_movimiento($data['id']);
                            $param_csc = array(
                                'almacen' => $alm_rev,
                                'articulo' => $v['xcomponente_id']
                            );
                            $alm->check_stock_componente($param_csc);
                        }
                    }
                }
            }

            //MARCAMOS PEDIDO COMO MOVIMIENTO GENERADO PARA TODOS LOS ARTÍCULOS
            $update = array(
                'xmov' => 'N'
            );
            $where = array(
                'xpedido_id' => $ord['xpedido_id']
            );
            $this->app->db->update('ms_pedidos', $update, $where);
        }

        print(json_encode($data));
    }

    private function _cancel_line($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Línea de pedido cancelado correctamente.',
            'lin_id' => $param['lin']
        );

        $val = array(
            'lin' => $param['lin']
        );
        $sql = "select * from ms_pedidos_lin where xlin_id=:lin";
        $lin = $this->db->fetchRow($sql, $val);

        $val = array(
            'ord' => $lin['xpedido_id']
        );
        $sql = "select * from ms_pedidos where xpedido_id=:ord";
        $ord = $this->db->fetchRow($sql, $val);
        $ord['ximporte_format'] = number_format($ord['ximporte'], 2, ',', '');
        $ord['xcoste_revendedor_format'] = number_format($ord['xcoste_revendedor'], 2, ',', '');

        //ELIMINAR ENVIOS CON TRACKING O SIN TRACKING
        $where = array(
            'xlin_id' => $param['lin']
        );
        $row = $this->app->db->del('ms_pedidos_proveedor', $where);

        //ESTADO LÍNEA: CANCELADO
        $update = array(
            'xestado' => '3'
        );
        $where = array(
            'xpedido_id' => $lin['xpedido_id'],
            'xlin_id' => $lin['xlin_id']
        );
        $this->app->db->update('ms_pedidos_lin', $update, $where);

        $insert_ph = array(
            'xpedido_id' => $lin['xpedido_id'],
            'xuseralta_id' => $this->app->user_id,
            'xdatealta' => date(dateSQL),
            'xestado' => '3',
            'xobs' => 'Cancelación de línea'
        );
        $this->app->db->insert('ms_pedidos_historico', $insert_ph);

        $update = array(
            'xusermodif_id' => $this->app->user_id,
            'xdatemodif' => date(dateSQL)
        );
        $where = array(
            'xpedido_id' => $lin['xpedido_id']
        );
        $this->app->db->update('ms_pedidos', $update, $where);

        //###############################REVENDEDORES
        if ($ord['xrevendedor_id'] > 0) {
            $deposito_before = 0;
            $deposito_after = 0;
            $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
            $rev = $this->db->fetchRow($sql);
            $deposito_before = $rev['xdeposito'];

            //AÑADIMOS CRÉDITO AL REVENDEDOR POR IMPORTE DEL PEDIDO
            $update = array(
                'xdeposito' => $rev['xdeposito'] + $lin['ximporte']
            );
            $where = array(
                'xrevendedor_id' => $ord['xrevendedor_id']
            );
            $this->db->update('ms_revendedores', $update, $where);

            $sql = "select * from ms_revendedores where xrevendedor_id={$ord['xrevendedor_id']}";
            $revendedor = $this->db->fetchRow($sql);
            $deposito_after = $revendedor['xdeposito'];

            $ord['ximporte_format'] = number_format($lin['ximporte'], 2, ',', '');

            $insert = array(
                'xrevendedor_id' => $ord['xrevendedor_id'],
                'xobs' => "Reembolso de crédito por gestores de Growsolutions Orden: {$ord['xhash']} Línea: {$lin['xlin_id']} crédito: {$ord['xcoste_revendedor_format']} USD",
                'xdatetime' => date(dateSQL),
                'xtipo' => 'AC', //ANULACION DE CREDITO
                'xpedido_id' => $ord['xpedido_id'],
                'xreferencia' => $ord['xhash'],
                'ximporte' => $ord['xcoste_revendedor'],
                'xdeposito_before' => $deposito_before,
                'xdeposito_after' => $deposito_after
            );
            $this->db->insert('ms_log_revendedor', $insert);
        }
        //#############################/REVENDEDORES

        print(json_encode($data));
    }

    private function _copy_order($param) {
        /*
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
         * 
         */

        $data = array(
            'status' => 1,
            'msg' => 'Pedido duplicado correctamente.',
            'order_old' => $param['ord'],
            'order_new' => '',
            'hash' => ''
        );

        $val = array(
            'ord' => $param['ord']
        );
        $sql = "select * from ms_pedidos where xpedido_id=:ord";
        $ord = $this->db->fetchRow($sql, $val);

        $sql = "select * from ms_pedidos_lin where xpedido_id=:ord";
        $ord_lin = $this->db->fetchAll($sql, $val);

        //GENERAMOS PEDIDO
        unset($ord['xpedido_id']);
        $ord['xuseralta_id'] = $this->app->user_id;
        $ord['xdatealta'] = date(dateSQL);
        $ord['xfecha'] = date(dateSQL);
        $ord['xestado'] = '1';
        $ord['xresponse'] = '';
        $ord['xpayment_ref'] = '';
        $ord['xfpago'] = '';
        $ord['xmov'] = 'N';
        $ord['xrevendedor_id'] = '0';
        $ord['xhash'] = strtoupper($this->app->rndString(6));
        $this->app->db->insert('ms_pedidos', $ord);
        $data['msg_title'] = OPERATION_SUCCESS;
        $data['msg'] = RECORD_INSERT;
        $data['order_new'] = $this->app->db->last_id();
        $data['hash'] = $ord['xhash'];

        $insert_ph = array(
            'xpedido_id' => $data['order_new'],
            'xuseralta_id' => $this->app->user_id,
            'xdatealta' => date(dateSQL),
            'xestado' => '1',
            'xobs' => 'Copia pedido de: ' . $param['ord']
        );
        $this->app->db->insert('ms_pedidos_historico', $insert_ph);

        $insert_ph = array(
            'xpedido_id' => $param['ord'],
            'xuseralta_id' => $this->app->user_id,
            'xdatealta' => date(dateSQL),
            'xestado' => '0',
            'xobs' => 'Copia pedido a: ' . $data['order_new']
        );
        $this->app->db->insert('ms_pedidos_historico', $insert_ph);

        $history = array(
            'xentity' => 'PEDIDOS',
            'xaction' => 'COPY-PEDIDO',
            'xid' => $data['order_new'],
            'xobs' => 'PEDIDO: ' . $data['order_old'] . ' a ' . $data['order_new']
        );
        $this->app->add_history($history);

        //GENERAMOS LAS LINEAS
        foreach ($ord_lin as $k => $v) {
            unset($v['xlin_id']);
            $v['xpedido_id'] = $data['order_new'];
            $v['xhash'] = strtoupper($this->app->rndString(20));
            $this->app->db->insert('ms_pedidos_lin', $v);
        }

        print(json_encode($data));
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        $val = array(
            'ord' => $param['id']
        );
        $sql = "select * from ms_pedidos where xpedido_id=:ord";
        $ord = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'ord' => $param['id']
        );
        $sql = "select * from ms_pedidos_proveedor where xpedido_id=:ord and xtracking is not null";
        $row = $this->app->db->fetchRow($sql, $val);
        if ($row) {
            $data['status'] = 0;
            $data['msg'] = 'No se puede modificar el pedido ya que tiene tracking asignado.';
        } else {
            $where = array(
                'xpedido_id' => $param['id']
            );
            $row = $this->app->db->del('ms_pedidos_proveedor', $where);
        }

        if ($data['status'] == 1 && $ord && $ord['xrevendedor_id'] > 0) {
            if ($ord['xestado'] == 12 || $ord['xestado'] == 4 || $ord['xestado'] == 5) {
                $data['status'] = 0;
                $data['msg'] = 'No se puede eliminar el pedido por el estado actual en el que se encuentra.';
            }
        }

        if ($data['status'] == 1) {
            $where = array(
                'xpedido_id' => $param['id']
            );
            if ($this->app->user_id == 1) {
                $this->app->db->del('ms_pedidos_lin', $where);
                $this->app->db->del('ms_pedidos', $where);
            }

            $history = array(
                'xentity' => 'PEDIDOS',
                'xaction' => 'DEL-PEDIDO',
                'xid' => $param['id'],
                'xobs' => 'DEL PEDIDO: ' . $param['id']
            );
            //$this->app->add_history($history);
        }

        print(json_encode($data));
    }

    private function _send_confirm_efectivo($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Email enviado correctamente.'
        );

        $val = array(
            'ord' => $param['ord']
        );
        $sql = "select a.* "
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . " from ms_pedidos a "
                . " where a.xpedido_id=:ord";
        $ord = $this->db->fetchRow($sql, $val);
        //print_r($ord);
        //die();

        if ($ord && $ord['xrevendedor_id'] == 0) {

            //SEND MAIL TO CLIENT
            $val = array(
                'cli' => $ord['xcliente_id'],
            );
            $sql = "select * from ms_clientes where xcliente_id=:cli";
            $cli = $this->db->fetchRow($sql, $val);

            $sql = "select * from ms_cortes_envios where xactivo='S' order by xcorte_id desc limit 1";
            $corte = $this->db->fetchRow($sql);

            $sql = "select * from ms_cortes_destinos where xcorte_id={$corte['xcorte_id']} and xdestino_id={$ord['xdestino_id']}";
            $destino = $this->db->fetchRow($sql);

            $content = array(
                'title' => 'Confirmación de pago realizado',
                'hello' => "Hola {$cli['xcliente']},",
                'header' => "¡Gracias por comprar en Mandasaldo!",
                'body' => "Hemos recibido su pago de su pedido <strong>{$ord['xhash']}</strong> en fecha <strong>{$ord['xfecha_format']}</strong>, por un importe de <strong>" . $this->app->format_price($ord['ximporte']) . "</strong> y procederemos a revisar y validar su orden y su pago.<br><br><i>Importante: Le informamos que, en algunos casos, podemos requerir información adicional con propósitos de verificación de identidad.  De no recibir la información solicitada MandaSaldo se reserva el derecho de anular este pedido.</i><br><br>Para más información sobre el estado de su pedido puede acceder a su perfil de usuario dentro de nuestra página web.<br>https://mandasaldo.com/perfil.ms<br><br>Le estaremos enviando en los próximos días más actualizaciones de estado de su compra.<br><br>Atentamente,<br>Departamento de Pedidos MandaSaldo."
            );
            $tpl = file_get_contents(TPL . '/order-tpl.html');
            $tpl = str_replace('{{title}}', $content['title'], $tpl);
            $tpl = str_replace('{{hello}}', $content['hello'], $tpl);
            $tpl = str_replace('{{header}}', $content['header'], $tpl);
            $tpl = str_replace('{{body}}', $content['body'], $tpl);

            $val = array(
                'ord' => $ord['xpedido_id'],
            );
            $sql = "select * from ms_pedidos_lin where xpedido_id=:ord";
            $ord_lin = $this->db->fetchAll($sql, $val);

            $tbl_lin_tpl = '<tr><td>{{prod}}</td><td class="text-center">{{qty}}</td><td class="text-right">{{pre}}</td><td class="text-right">{{imp}}</td></tr>';
            $tbl_lin = '';
            foreach ($ord_lin as $k => $v) {
                $art = $v['xarticulo'];
                if ($v['xtipo'] == 'R')
                    $art = "{$v['xarticulo']} al número: (+53) {$v['xphone']}";
                if ($v['xtipo'] == 'N')
                    $art = "{$v['xarticulo']} a la cuenta: {$v['xphone']}@nauta.com.cu";

                $tmp = $tbl_lin_tpl;
                $tmp = str_replace('{{prod}}', $art, $tmp);
                $tmp = str_replace('{{qty}}', number_format($v['xcantidad'], 0), $tmp);
                $tmp = str_replace('{{pre}}', $this->app->format_price($v['xprecio']), $tmp);
                $tmp = str_replace('{{imp}}', $this->app->format_price($v['ximporte']), $tmp);
                $tbl_lin .= $tmp;
            }
            $tpl = str_replace('{{tbl-lin}}', $tbl_lin, $tpl);
            $tpl = str_replace('{{transporte}}', $app->format_price($ord['xtransporte']), $tpl);
            $tpl = str_replace('{{total}}', $this->app->format_price($ord['ximporte']), $tpl);

            $obs = '';
            if ($ord['xship_obs'] != '') {
                $obs_tpl = '<div class="row"><div class="col-md-12"><p><strong>Observaciones: </strong>{{obs-ord}}</p></div></div>';
                $obs = str_replace('{{obs-ord}}', $ord['xship_obs'], $obs_tpl);
            }
            $tpl = str_replace('{{obs}}', $obs, $tpl);

            $envio = '';
            if ($ord['xship_name'] != '') {
                $envio_lin_tpl = '<div>{{dir}}</div>';
                $envio_tpl = '<div class="row"><div class="col-md-4"><h6>Dirección de envío</h6><div class="p-4" style="border: 2px #eeeeee solid;">{{envio-lin}}</div></div></div>';
                $envio_lin = '';
                $envio_lin .= str_replace('{{dir}}', $ord['xship_name'] . ' ' . $ord['xship_name2'] . ' ' . $ord['xship_apellido1'] . ' ' . $ord['xship_apellido2'], $envio_lin_tpl);
                $envio_lin .= str_replace('{{dir}}', $ord['xship_dir1'] . ' Nº ' . $ord['xship_numero'] . (($ord['xship_apartamento'] != '') ? ' Apt.: ' . $ord['xship_apartamento'] : '') . (($ord['xship_piso'] != '') ? ' Piso: ' . $ord['xship_piso'] : ''), $envio_lin_tpl);
                //$envio_lin.=str_replace('{{dir}}', $ord['xship_dir2'], $envio_lin_tpl);
                $envio_lin .= str_replace('{{dir}}', $ord['xship_zipcode'] . ' - ' . str_replace('_', ' ', $ord['xship_city']), $envio_lin_tpl);
                $envio_lin .= str_replace('{{dir}}', 'Entre calles: ' . $ord['xship_entre_calle1'] . ' y ' . $ord['xship_entre_calle2'], $envio_lin_tpl);
                $envio_lin .= str_replace('{{dir}}', str_replace('_', ' ', $ord['xship_provincia']), $envio_lin_tpl);
                $envio_lin .= str_replace('{{dir}}', $ord['xship_country'], $envio_lin_tpl);
                $envio = str_replace('{{envio-lin}}', $envio_lin, $envio_tpl);
            }
            $tpl = str_replace('{{envio}}', $envio, $tpl);

            $sql = "select * from ms_configuraciones where xconfig_id=1";
            $cfg = $this->db->fetchRow($sql);

            $tpl = str_replace('{{condiciones}}', $cfg['xcondiciones'], $tpl);

            $data_email = array(
                'xtitle' => 'Pago recibido',
                'xemail' => $cli['xemail'],
                'xcliente_id' => $cli['xcliente_id'],
                'xpedido_id' => $ord['xpedido_id'],
                //'xlin_id' => $track['xlin_id'],
                'xadded' => date(dateSQL),
                //'xtracking' => $track['xtracking'],
                'xcontent' => $tpl
            );
            $this->db->insert('ms_emails', $data_email);

            $mail = new PHPMailer();
            $mail->CharSet = 'utf-8';
            $mail->IsSMTP();                                      // set mailer to use SMTP
            $mail->Host = "smtp.dominioabsoluto.net";  // specify main and backup server
            $mail->Port = 25;
            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->Username = "no-reply@mandasaldo.com";  // SMTP username
            $mail->Password = "nORep2020!!"; // SMTP password
            $mail->From = 'no-reply@mandasaldo.com';
            $mail->FromName = 'MandaSaldo';
            //$mail->addBCC('alvaro.ma@alvsoftware.es');
            $mail->AddAddress($cli['xemail']);
            $mail->IsHTML(true);                                  // set email format to TEXT
            $mail->Subject = "MandaSaldo: Confirmación de pago recibido";
            $mail->Body = $tpl;
            $mail->AltBody = "Desde Mandasaldo te confirmamos un nuevo pago: {$ord['xhash']}";
            //0 - no debug
            //1 - Server debug
            //2 - Server and Client debug
            //$mail->SMTPDebug = 1;
            $mail->SMTPSecure = false;
            //$mail->SMTPAutoTLS = false;
            if ($mail->Send()) {
                $data['send'] = 'S';
            } else {
                $data['status'] = 0;
                $data['error'] = $mail->ErrorInfo;
            }
        }

        print(json_encode($data));
    }

    private function _rescue($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Rescate generado y enviado por email.',
            'cli' => '',
            'link' => ''
        );

        if ($data['status'] == 1) {
            $val = array(
                'ord' => $param['id']
            );
            $sql = "select * from ms_pedidos where xpedido_id=:ord";
            $ord = $this->db->fetchRow($sql, $val);
            $ord['ximporte_format'] = $this->app->format_price($ord['ximporte']);

            $val = array(
                'cli' => $ord['xcliente_id']
            );
            $sql = "select * from ms_clientes where xcliente_id=:cli";
            $cli = $this->db->fetchRow($sql, $val);
            $data['cli'] = $cli['xcliente_id'];

            $insert = array(
                'xpedido_id' => $param['id'],
                'xhash' => strtoupper($this->app->rndString(40)),
                'xuser_id' => $this->app->user_id,
                'xdate_added' => date(dateSQL)
            );
            $this->app->db->insert('ms_pedidos_rescates', $insert);

            $data['link'] = "https://mandasaldo.com/rescue-stripe.ms?ref={$insert['xhash']}";

            $history = array(
                'xentity' => 'PEDIDOS',
                'xaction' => 'INSERT-RESCUE',
                'xid' => $param['id'],
                'xobs' => 'PEDIDO: ' . $param['id']
            );
            $this->app->add_history($history);

            $content = array(
                'title' => 'Rescate de Pedido',
                'hello' => "Hola {$cli['xcliente']},",
                'header' => "¡Gracias por usar los servicios de Mandasaldo!",
                'body' => "A continuación le adjunto un link para completar el pago de su orden con referencia <strong>{$ord['xhash']}</strong> por un importe total de <strong>{$ord['ximporte_format']}</strong><br><br><a href=\"{$data['link']}\">{$data['link']}</a><br><br>¡Gracias por comprar en Mandasaldo!"
            );
            $tpl = file_get_contents(TPL . '/simple-tpl.html');
            $tpl = str_replace('{{title}}', $content['title'], $tpl);
            $tpl = str_replace('{{hello}}', $content['hello'], $tpl);
            $tpl = str_replace('{{header}}', $content['header'], $tpl);
            $tpl = str_replace('{{body}}', $content['body'], $tpl);

            $data_email = array(
                'xtitle' => 'Rescate de Pedido',
                'xemail' => $cli['xemail'],
                'xcliente_id' => $cli['xcliente_id'],
                'xpedido_id' => $ord['xpedido_id'],
                //'xlin_id' => $track['xlin_id'],
                'xadded' => date(dateSQL),
                //'xtracking' => $track['xtracking'],
                'xcontent' => $tpl
            );
            $this->db->insert('ms_emails', $data_email);

            $mail = new PHPMailer();
            $mail->CharSet = 'utf-8';
            $mail->IsSMTP();                                      // set mailer to use SMTP
            $mail->Host = "smtp.dominioabsoluto.net";  // specify main and backup server
            $mail->Port = 25;
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = "no-reply@mandasaldo.com";  // SMTP username
            $mail->Password = "nORep2020!!"; // SMTP password
            $mail->From = 'no-reply@mandasaldo.com';
            $mail->FromName = 'MandaSaldo';
            //$mail->AddAddress('alvaro.ma@alvsoftware.es');
            //$mail->addBCC('alvaro.ma@alvsoftware.es');
            $mail->AddAddress($cli['xemail']);
            $mail->IsHTML(true);                                  // set email format to TEXT
            $mail->Subject = "MandaSaldo: Completar Pago del Pedido";
            $mail->Body = $tpl;
            $mail->AltBody = "Mandasaldo te ha enviado el código de verificación de email: {$cli['xemail_code']}";
            //0 - no debug
            //1 - Server debug
            //2 - Server and Client debug
            //$mail->SMTPDebug = 1;
            $mail->SMTPSecure = false;
            //$mail->SMTPAutoTLS = false;
            if ($mail->Send()) {
                $data['send'] = 'S';
            } else {
                $data['status'] = 0;
                $data['error'] = $mail->ErrorInfo;
            }
        }
        print(json_encode($data));
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg' => '',
            'id' => '',
            'action' => ''
        );

        $ord = array(
            'xrevendedor_id' => '0'
        );
        if (isset($param['xpedido_id'])) {
            $val = array(
                'ord' => $param['xpedido_id']
            );
            $sql = "select * from ms_pedidos where xpedido_id=:ord";
            $ord = $this->app->db->fetchRow($sql, $val);
        }

        if (isset($param['lin'])) {
            $lin_raw = htmlspecialchars_decode($param['lin']);
            unset($param['lin']);
        }

        $insert = $param;

        $data['action'] = $insert['action'];

        $fecha = $insert['xfecha'];
        if (isset($fecha) && $fecha != '' && strlen($fecha) == 10) {
            $f = explode('/', $fecha);
            //$fecha = $f[2] . '-' . $f[1] . '-' . $f[0] . ' 00:00:00';
            $fecha = $f[2] . '-' . $f[1] . '-' . $f[0] . ' ' . date('H:i:s');
        }
        $insert['xfecha'] = $fecha;

        if ($data['action'] == 'update') {
            $val = array(
                'ord' => $param['xpedido_id']
            );
            $sql = "select * from ms_pedidos_proveedor where xpedido_id=:ord and xtracking is not null";
            $row = $this->app->db->fetchRow($sql, $val);
            if ($row) {
                $data['status'] = 0;
                $data['msg'] = 'No se puede modificar el pedido ya que tiene tracking asignado.';
            } else {
                $where = array(
                    'xpedido_id' => $param['xpedido_id']
                );
                $row = $this->app->db->del('ms_pedidos_proveedor', $where);
            }

            $val = array(
                'ord' => $param['xpedido_id']
            );
            $sql = "select * from ms_pedidos where xpedido_id=:ord";
            $ord = $this->app->db->fetchRow($sql, $val);
            if ($ord) {
                $data['estado'] = $ord['xestado'];
                if (in_array($ord['xestado'], array(3, 4, 5, 6, 7, 12, 17, 20, 23, 24))) {
                    $data['status'] = 0;
                    $data['msg'] = 'No se puede modificar el pedido ya que su estado actual no lo permite.';
                }
            }
        }

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            $insert['xcliente'] = '';
            $val = array(
                'cli' => $insert['xcliente_id']
            );
            $sql = "select xcliente from ms_clientes where xcliente_id=:cli";
            $row = $this->app->db->fetchRow($sql, $val);
            if ($row)
                $insert['xcliente'] = $row['xcliente'];

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xhash'] = 'MA' . strtoupper($this->app->rndString(6));
                $insert['xeliminado'] = '0';
                $this->app->db->insert('ms_pedidos', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->app->db->last_id();
                $data['hash'] = $insert['xhash'];

                $insert_ph = array(
                    'xpedido_id' => $data['id'],
                    'xuseralta_id' => $this->app->user_id,
                    'xdatealta' => date(dateSQL),
                    'xestado' => '1',
                    'xobs' => 'Registro Pedido'
                );
                $this->app->db->insert('ms_pedidos_historico', $insert_ph);

                $history = array(
                    'xentity' => 'PEDIDOS',
                    'xaction' => 'INSERT-PEDIDO',
                    'xid' => $data['id'],
                    'xobs' => 'PEDIDO: ' . $data['id']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $data['id'] = $update['xpedido_id'];

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xpedido_id' => $data['id']
                );
                unset($update['xpedido_id']);
                $noquotes = array('xdatemodif');

                $this->app->db->update('ms_pedidos', $update, $where, $noquotes);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;

                $insert_ph = array(
                    'xpedido_id' => $data['id'],
                    'xuseralta_id' => $this->app->user_id,
                    'xdatealta' => date(dateSQL),
                    'xestado' => '0',
                    'xobs' => 'Actualizado Pedido'
                );
                $this->app->db->insert('ms_pedidos_historico', $insert_ph);

                $history = array(
                    'xentity' => 'PEDIDOS',
                    'xaction' => 'UPDATE-PEDIDO',
                    'xid' => $data['id'],
                    'xobs' => 'PEDIDO: ' . $data['id']
                );
                $this->app->add_history($history);
            }

            //SAVE LINEAS
            if (isset($ord['xrevendedor_id']) && $ord['xrevendedor_id'] == 0) {
                $where = array(
                    'xpedido_id' => $data['id']
                );
                $this->app->db->del('ms_pedidos_lin', $where);
                $lin = explode(';', $lin_raw);
                foreach ($lin as $k => $v) {
                    $d = explode('|', $v);

                    $insert = array(
                        'xpedido_id' => $data['id'],
                        'xarticulo_id' => $d[0],
                        'xarticulo' => $d[1],
                        'xphone' => $d[2],
                        'xcantidad' => $d[3],
                        'xprecio' => $d[4],
                        'ximporte' => $d[5],
                        'xhash' => $d[6],
                        'xdate' => $d[7],
                        'xtipo' => $d[8],
                        'xcanjeado' => $d[9]
                    );
                    $this->app->db->insert('ms_pedidos_lin', $insert);
                    //print_r($insert);
                    //die();
                }
            }

            if (isset($ord) && in_array($ord['xestado'], array(12, 17, 23, 24, 4, 5, 6, 7))) {
                //GENERAMOS NEW REFERENCIAS
                $this->_generate_new_ref_servicios($ord['xpedido_id']);
            }
        }



        print(json_encode($data));
    }

    private function _save_pv($param) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = array(
            'status' => 1,
            'msg' => '',
            'id' => '',
            'action' => '',
            'estado' => '1'
        );

        $ord = array(
            'xrevendedor_id' => '0'
        );
        if (isset($param['xpedido_id'])) {
            $val = array(
                'ord' => $param['xpedido_id']
            );
            $sql = "select * from ms_pedidos where xpedido_id=:ord";
            $ord = $this->app->db->fetchRow($sql, $val);
        }

        if (isset($param['lin'])) {
            $lin_raw = htmlspecialchars_decode($param['lin']);
            unset($param['lin']);
        }

        $insert = $param;

        $data['action'] = $insert['action'];

        $fecha = $insert['xfecha'];
        if (isset($fecha) && $fecha != '' && strlen($fecha) == 10) {
            $f = explode('/', $fecha);
            //$fecha = $f[2] . '-' . $f[1] . '-' . $f[0] . ' 00:00:00';
            $fecha = $f[2] . '-' . $f[1] . '-' . $f[0] . ' ' . date('H:i:s');
        }
        $insert['xfecha'] = $fecha;

        if ($data['action'] == 'update') {
            $val = array(
                'ord' => $param['xpedido_id']
            );
            $sql = "select * from ms_pedidos_proveedor where xpedido_id=:ord and xtracking is not null";
            $row = $this->app->db->fetchRow($sql, $val);
            if ($row) {
                $data['status'] = 0;
                $data['msg'] = 'No se puede modificar el pedido ya que tiene tracking asignado.';
            } else {
                $where = array(
                    'xpedido_id' => $param['xpedido_id']
                );
                $row = $this->app->db->del('ms_pedidos_proveedor', $where);
            }

            $val = array(
                'ord' => $param['xpedido_id']
            );
            $sql = "select * from ms_pedidos where xpedido_id=:ord";
            $ord = $this->app->db->fetchRow($sql, $val);
            if ($ord) {
                $data['estado'] = $ord['xestado'];
                if (in_array($ord['xestado'], array(3, 4, 5, 6, 7, 12, 17, 20, 23, 24))) {
                    $data['status'] = 0;
                    $data['msg'] = 'No se puede modificar el pedido ya que su estado actual no lo permite.';
                }
            }
        }

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            $insert['xcliente'] = '';
            $val = array(
                'cli' => $insert['xcliente_id']
            );
            $sql = "select xcliente from ms_clientes where xcliente_id=:cli";
            $row = $this->app->db->fetchRow($sql, $val);
            if ($row)
                $insert['xcliente'] = $row['xcliente'];

            //SI ES PUNTO DE VENTA, REVENDEDOR SE ASIGNA REVENDEDOR
            if ($this->app->punto_venta != '') {
                $insert['xrevendedor_id'] = $this->app->punto_venta;
                //$insert['xtransaccion'] = 'PUNTO DE VENTA';
                //$insert['xfpago'] = 'AGENCIA';
                $insert['ximporte_rev'] = $insert['ximporte'];
                $insert['xcoste_revendedor'] = $insert['ximporte'];
            }

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xhash'] = 'PV-' . strtoupper($this->app->rndString(6));
                $insert['xeliminado'] = '0';

                $this->app->db->insert('ms_pedidos', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->app->db->last_id();
                $data['hash'] = $insert['xhash'];

                $insert_ph = array(
                    'xpedido_id' => $data['id'],
                    'xuseralta_id' => $this->app->user_id,
                    'xdatealta' => date(dateSQL),
                    'xestado' => '1',
                    'xobs' => 'Registro Pedido PV'
                );
                $this->app->db->insert('ms_pedidos_historico', $insert_ph);

                $history = array(
                    'xentity' => 'PEDIDOS',
                    'xaction' => 'INSERT-PEDIDO',
                    'xid' => $data['id'],
                    'xobs' => 'PEDIDO: ' . $data['id']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $data['id'] = $update['xpedido_id'];

                //SI ES PUNTO DE VENTA, REVENDEDOR SE ASIGNA REVENDEDOR
                if ($ord['xrevendedor_id'] != '') {
                    $update['ximporte_rev'] = $insert['ximporte'];
                    $update['xcoste_revendedor'] = $insert['ximporte'];
                }

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xpedido_id' => $data['id']
                );
                unset($update['xpedido_id']);
                $noquotes = array('xdatemodif');

                $this->app->db->update('ms_pedidos', $update, $where, $noquotes);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;

                $insert_ph = array(
                    'xpedido_id' => $data['id'],
                    'xuseralta_id' => $this->app->user_id,
                    'xdatealta' => date(dateSQL),
                    'xestado' => 0,
                    'xobs' => 'Actualizado Pedido PV'
                );
                $this->app->db->insert('ms_pedidos_historico', $insert_ph);

                $history = array(
                    'xentity' => 'PEDIDOS',
                    'xaction' => 'UPDATE-PEDIDO',
                    'xid' => $data['id'],
                    'xobs' => 'PEDIDO: ' . $data['id']
                );
                $this->app->add_history($history);
            }

            //SAVE LINEAS
            $where = array(
                'xpedido_id' => $data['id']
            );
            $this->app->db->del('ms_pedidos_lin', $where);
            $lin = explode(';', $lin_raw);
            foreach ($lin as $k => $v) {
                $d = explode('|', $v);

                $insert = array(
                    'xpedido_id' => $data['id'],
                    'xarticulo_id' => $d[0],
                    'xarticulo' => $d[1],
                    'xcantidad' => $d[2],
                    'xprecio' => $d[3],
                    'xprecio_rev' => $d[3],
                    'xprecio_cup' => $d[4],
                    'xprecio_mlc' => $d[5],
                    'xprecio_otro' => $d[6],
                    'ximporte' => $d[7],
                    'ximporte_rev' => $d[7],
                    'ximporte_cup' => $d[8],
                    'ximporte_mlc' => $d[9],
                    'ximporte_otro' => $d[10],
                    'xhash' => $d[11],
                    'xprecio_base' => $d[12],
                    'xrevendedor_id' => $this->app->punto_venta
                );
                //print_r($insert);
                //die();
                $this->app->db->insert('ms_pedidos_lin', $insert);
                //print_r($insert);
                //die();
            }

            if (isset($ord) && isset($ord['xestado']) && in_array($ord['xestado'], array(12, 17, 23, 24, 4, 5, 6, 7))) {
                //GENERAMOS NEW REFERENCIAS
                $this->_generate_new_ref_servicios($ord['xpedido_id']);
            }
        }



        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $cond = '';
        $limit = '100';

        if (isset($param['m']) && $param['m'] != '') {
            $cond .= " and a.xfpago in ('CUP efectivo','CUP transferencia')";
        }

        if (isset($param['estado']) && $param['estado'] != '') {
            $cond .= " and a.xestado='{$param['estado']}'";
        }

        if (isset($param['pedido']) && $param['pedido'] != '') {
            $cond .= " and (a.xpedido_id='{$param['pedido']}' or a.xhash='{$param['pedido']}')";
        }

        if ($this->app->punto_venta != '') {
            $cond .= " and a.xrevendedor_id={$this->app->punto_venta}";
        } else {
            if (isset($param['revendedor']) && $param['revendedor'] != '') {
                $cond .= " and a.xrevendedor_id={$param['revendedor']}";
            }
        }

        /*
          print($this->app->rol);
          die();
         * 
         */

        if ($this->app->rol == 10) {
            $cond .= " and a.xuseralta_id={$this->app->user_id}";
        }

        if (isset($param['cli']) && $param['cli'] != '') {
            $cond .= " and a.xcliente_id={$param['cli']}";
        }

        if (isset($param['fi']) && $param['fi'] != '') {
            if (strlen($param['fi']) == 10) {
                $tmp = explode('/', $param['fi']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and a.xfecha>='{$fe}'";
            }
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            if (strlen($param['ff']) == 10) {
                $tmp = explode('/', $param['ff']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
                $cond .= " and a.xfecha<='{$fe}'";
            }
        }
        if (isset($param['resultados']) && $param['resultados'] != '')
            $limit = $param['resultados'];

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel,d.xrevendedor,e.xchofer"
                . ",'toolbar' as toolbar,f.xweb,g.xusuario as xcomercial"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",date_format(a.xdate_finalizado,'%d/%m/%Y %H:%i:%s') as xfecha_finalizado_format"
                . ",date_format(a.xdate_entregado,'%d/%m/%Y %H:%i:%s') as xfecha_entregado_format"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " left join ms_revendedores d on a.xrevendedor_id=d.xrevendedor_id"
                . " left join ms_chofers e on a.xchofer_id=e.xchofer_id"
                . " left join ms_webs f on a.xweb_id=f.xweb_id"
                . " left join ms_usuarios g on a.xuseralta_id=g.xusuario_id"
                . " where a.xeliminado=0$cond"
                . " order by a.xpedido_id desc"
                . " limit $limit";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
            $data[$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            $data[$k]['ximporte_cup_format'] = number_format($v['ximporte_cup'], 0, ',', '');

            $data[$k]['items'] = array();
            $sql = "select * from ms_pedidos_lin where xpedido_id={$v['xpedido_id']}";
            $data[$k]['items'] = $this->db->fetchAll($sql);
        }
        return $data;
    }

    private function _list_user($param) {
        $data = array();
        $cond = '';
        $limit = '100';

        if (isset($param['estado']) && $param['estado'] != '') {
            $cond .= " and a.xestado='{$param['estado']}'";
        }


        if (isset($param['pedido']) && $param['pedido'] != '') {
            $cond .= " and (a.xpedido_id='{$param['pedido']}' or a.xhash='{$param['pedido']}')";
        }

        /*
          if ($this->app->punto_venta != '') {
          $cond .= " and a.xrevendedor_id={$this->app->punto_venta}";
          }
         * 
         */

        if (isset($param['cli']) && $param['cli'] != '') {
            $cond .= " and a.xcliente_id={$param['cli']}";
        }

        if (isset($param['fi']) && $param['fi'] != '') {
            if (strlen($param['fi']) == 10) {
                $tmp = explode('/', $param['fi']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and xfecha>='{$fe}'";
            }
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            if (strlen($param['ff']) == 10) {
                $tmp = explode('/', $param['ff']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
                $cond .= " and xfecha<='{$fe}'";
            }
        }
        if (isset($param['resultados']) && $param['resultados'] != '')
            $limit = $param['resultados'];

        //$cond .= " and a.xuseralta_id=" . $this->app->user_id;
        $cond .= " and a.xuseralta_id=" . $param['user'];

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel,d.xrevendedor"
                . ",'toolbar' as toolbar"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",date_format(a.xdate_finalizado,'%d/%m/%Y %H:%i:%s') as xfecha_finalizado_format"
                . ",date_format(a.xdate_entregado,'%d/%m/%Y %H:%i:%s') as xfecha_entregado_format"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " left join ms_revendedores d on a.xrevendedor_id=d.xrevendedor_id"
                . " where a.xeliminado=0$cond"
                . " order by a.xpedido_id desc"
                . " limit $limit";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');

            $data[$k]['items'] = array();
            $sql = "select * from ms_pedidos_lin where xpedido_id={$v['xpedido_id']}";
            $data[$k]['items'] = $this->db->fetchAll($sql);
        }
        return $data;
    }

    private function _list_finalizados($param) {
        $data = array();
        $cond = '';
        $limit = '100';

        if (isset($param['fi']) && $param['fi'] != '') {
            if (strlen($param['fi']) == 10) {
                $tmp = explode('/', $param['fi']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and xdate_finalizado>='{$fe}'";
            }
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            if (strlen($param['ff']) == 10) {
                $tmp = explode('/', $param['ff']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
                $cond .= " and xdate_finalizado<='{$fe}'";
            }
        }
        if (isset($param['resultados']) && $param['resultados'] != '')
            $limit = $param['resultados'];

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel"
                . ",'toolbar' as toolbar"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",date_format(a.xdate_finalizado,'%d/%m/%Y %H:%i:%s') as xfecha_finalizado_format"
                . ",date_format(a.xdate_entregado,'%d/%m/%Y %H:%i:%s') as xfecha_entregado_format"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " where a.xeliminado=0$cond"
                . " order by a.xpedido_id desc"
                . " limit $limit";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
        }
        return $data;
    }

    private function _list_entregados($param) {
        $data = array();
        $cond = '';
        $limit = '100';

        if (isset($param['fi']) && $param['fi'] != '') {
            if (strlen($param['fi']) == 10) {
                $tmp = explode('/', $param['fi']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and xdate_entregado>='{$fe}'";
            }
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            if (strlen($param['ff']) == 10) {
                $tmp = explode('/', $param['ff']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
                $cond .= " and xdate_entregado<='{$fe}'";
            }
        }
        if (isset($param['resultados']) && $param['resultados'] != '')
            $limit = $param['resultados'];

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel"
                . ",'toolbar' as toolbar"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",date_format(a.xdate_finalizado,'%d/%m/%Y %H:%i:%s') as xfecha_finalizado_format"
                . ",date_format(a.xdate_entregado,'%d/%m/%Y %H:%i:%s') as xfecha_entregado_format"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " where a.xeliminado=0$cond"
                . " order by a.xpedido_id desc"
                . " limit $limit";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
        }
        return $data;
    }

    private function _view_tracking($param) {
        $data = array(
            'status' => 1,
            'items' => array()
        );
        if ($param['tracking'] != 'extra') {
            //BÚSQUEDA POR TRACKING
            $tmp = explode(',', $param['tracking']);
            foreach ($tmp as $k => $v) {
                $tracking = trim($v);
                $sql = "select xreferencia from ms_pedidos_proveedor where xtracking='{$tracking}'";
                $row = $this->db->fetchRow($sql);
                $d = array(
                    'tracking' => $tracking,
                    'referencia' => $row['xreferencia'],
                    'image' => $row['xreferencia'] . '.jpg'
                );
                if (file_exists(IMG_ENVIOS . DS . "{$row['xreferencia']}-2.jpg")) {
                    $d['image2'] = "{$row['xreferencia']}-2.jpg";
                }
                if (file_exists(IMG_ENVIOS . DS . "{$row['xreferencia']}-recibo.jpg")) {
                    $d['image2'] = "{$row['xreferencia']}-recibo.jpg";
                }
                $data['items'][] = $d;
            }
        } else {
            //BÚSQUEDA POR PEDIDO
            $d = array(
                'tracking' => 'No tiene',
                'referencia' => 'No encontrada',
                'image' => $param['pedido'] . '.jpg'
            );
            if (file_exists(IMG_ENVIOS . DS . "{$param['pedido']}-2.jpg")) {
                $d['image2'] = "{$param['pedido']}-2.jpg";
            }
            if (file_exists(IMG_ENVIOS . DS . "{$param['pedido']}-recibo.jpg")) {
                $d['image2'] = "{$param['pedido']}-recibo.jpg";
            }
            $data['items'][] = $d;
            /*
              $val = array(
              'ped' => $param['pedido']
              );
              $sql = "select xreferencia from ms_pedidos_proveedor where xpedido_id=:ped";
              $tmp = $this->db->fetchAll($sql, $val);
              foreach ($tmp as $k => $v) {
              $ref = $v['xreferencia'];
              $sql = "select xreferencia from ms_pedidos_proveedor where xreferencia='{$ref}'";
              $row = $this->db->fetchRow($sql);
              $d = array(
              'tracking' => $tracking,
              'referencia' => $row['xreferencia'],
              'image' => $row['xreferencia'] . '.jpg'
              );
              if (file_exists(IMG_ENVIOS . DS . "{$row['xreferencia']}-2.jpg")) {
              $d['image2'] = "{$row['xreferencia']}-2.jpg";
              }
              if (file_exists(IMG_ENVIOS . DS . "{$row['xreferencia']}-recibo.jpg")) {
              $d['image2'] = "{$row['xreferencia']}-recibo.jpg";
              }
              $data['items'][] = $d;
              }
             * 
             */
        }



        return $data;
    }

    private function _list_ptes($param) {
        $data = array();
        $cond = '';

        //if (isset($param['estado']) && $param['estado'] != '') {
        //$cond .= " and a.xestado in (2,15)";
        $cond .= " and (a.xestado in (0,2,16) or (a.xestado=15 and datediff(now(),a.xfecha)<=4))";
        //}

        if (isset($param['cli']) && $param['cli'] != '') {
            $cond .= " and a.xcliente_id={$param['cli']}";
        }

        if ($this->app->punto_venta != '') {
            $cond .= " and a.xrevendedor_id={$this->app->punto_venta}";
        }

        if (isset($param['fi']) && $param['fi'] != '') {
            if (strlen($param['fi']) == 10) {
                $tmp = explode('/', $param['fi']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and xfecha>='{$fe}'";
            }
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            if (strlen($param['ff']) == 10) {
                $tmp = explode('/', $param['ff']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
                $cond .= " and xfecha<='{$fe}'";
            }
        }

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel,d.xchofer"
                . ",'toolbar' as toolbar"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",datediff(now(),a.xfecha) as xdiff"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " left join ms_chofers d on a.xchofer_id=d.xchofer_id"
                . " where a.xrevendedor_id=0 and a.xeliminado=0$cond"
                . " order by a.xpedido_id desc"
                . " limit 50";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
        }
        return $data;
    }

    private function _list_hoy($param) {
        $data = array();
        $cond = '';
        //$hoy = date('Y-m-d');
        //if (isset($param['estado']) && $param['estado'] != '') {
        //$cond .= " and a.xestado in (2,15)";
        //$cond .= " and (a.xestado in (0,2,16) or (a.xestado=15 and datediff(now(),a.xfecha)<=4))";
        //}
        //print_r($param);
        //die();

        if (isset($param['m']) && $param['m'] != '') {
            $cond .= " and a.xfpago in ('CUP efectivo','CUP transferencia')";
        }

        if (isset($param['cli']) && $param['cli'] != '') {
            $cond .= " and a.xcliente_id={$param['cli']}";
        }

        if ($this->app->punto_venta != '') {
            $cond .= " and a.xrevendedor_id={$this->app->punto_venta}";
        }

        if (isset($param['fi']) && $param['fi'] != '') {
            $cond .= " and xfecha>='{$param['fi']}'";
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            $cond .= " and xfecha<='{$param['ff']}'";
        }

        /*
          if (isset($param['fi']) && $param['fi'] != '') {
          if (strlen($param['fi']) == 10) {
          $tmp = explode('/', $param['fi']);
          $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
          $cond .= " and xfecha>='{$fe}'";
          }
          }
          if (isset($param['ff']) && $param['ff'] != '') {
          if (strlen($param['ff']) == 10) {
          $tmp = explode('/', $param['ff']);
          $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
          $cond .= " and xfecha<='{$fe}'";
          }
          }
         * 
         */

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel"
                . ",'toolbar' as toolbar"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",datediff(now(),a.xfecha) as xdiff"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " where a.xeliminado=0$cond"
                . " order by a.xpedido_id desc"
                . " limit 50";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
            $data[$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            $data[$k]['ximporte_cup_format'] = number_format($v['ximporte_cup'], 0, ',', '');

            $data[$k]['items'] = array();
            $sql = "select * from ms_pedidos_lin where xpedido_id={$v['xpedido_id']}";
            $data[$k]['items'] = $this->db->fetchAll($sql);
        }
        return $data;
    }

    private function _list_rev($param) {
        $data = array();
        $cond = '';

        //if (isset($param['estado']) && $param['estado'] != '') {
        //$cond .= " and a.xestado in (2,15)";
        //$cond.=" and (a.xestado in (2,16) or (a.xestado=15 and datediff(now(),a.xfecha)<=4))";
        //}

        if (isset($param['cli']) && $param['cli'] != '')
            $cond .= " and a.xcliente_id={$param['cli']}";
        if (isset($param['rev']) && $param['rev'] != '')
            $cond .= " and a.xrevendedor_id={$param['rev']}";

        if (isset($param['fi']) && $param['fi'] != '') {
            if (strlen($param['fi']) == 10) {
                $tmp = explode('/', $param['fi']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and xfecha>='{$fe}'";
            }
        }
        if (isset($param['ff']) && $param['ff'] != '') {
            if (strlen($param['ff']) == 10) {
                $tmp = explode('/', $param['ff']);
                $fe = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';
                $cond .= " and xfecha<='{$fe}'";
            }
        }

        $sql = "select a.*,b.xestado as xestado_desc,b.xcolor,c.xnivel"
                . ",'toolbar' as toolbar,d.xrevendedor"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",datediff(now(),a.xfecha) as xdiff"
                . " from ms_pedidos a"
                . " left join ms_estados b on a.xestado=b.xestado_id"
                . " left join ms_clientes c on a.xcliente_id=c.xcliente_id"
                . " left join ms_revendedores d on a.xrevendedor_id=d.xrevendedor_id"
                . " where a.xeliminado=0 and a.xrevendedor_id>0$cond"
                . " order by a.xpedido_id desc"
                . " limit 50";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        foreach ($data as $k => $v) {
            $data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
        }
        return $data;
    }

    private function _search_product($param) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        //print($this->app->punto_venta);
        //die();

        $sql = "select * from ms_revendedores where xrevendedor_id={$this->app->punto_venta}";
        $rev = $this->db->fetchRow($sql);

        $data = array(
            'data' => null
        );
        $where = array(
            'w0' => "%{$_GET['t']}%",
            'w1' => $_GET['t']
        );
        $sql = "select a.xarticulo_id,a.xarticulo,a.xprecio,a.xservicio,a.xseguro"
                . ",a.xprecio1_rev,a.xprecio2_rev,a.xprecio3_rev,a.xprecio4_rev
            from ms_articulos a
            where a.xeliminado=0 and a.xactivo='S' and a.xactivo_rev='S' and (a.xarticulo like :w0 or a.xarticulo_id=:w1)
            limit 30";
        //print($sql);
        //die();
        $rr = $this->db->fetchAll($sql, $where);

        foreach ($rr as $k => $v) {
            $rr[$k]['xhash'] = $this->app->rndString(20);
            $rr[$k]['xtipo'] = $this->app->get_tipo_art($v);
            $rr[$k]['xprecio'] = $v["xprecio{$rev['xtarifa_id']}_rev"];
        }

        $data['data'] = $rr;

        print(json_encode($data));
    }

    private function _search_client($param) {
        $cond = '';

        if ($this->app->punto_venta != '') {
            $cond .= " and a.xrevendedor_id={$this->app->punto_venta}";
        }

        $data = array(
            'data' => null
        );
        $where = array(
            'cli' => "%{$_GET['t']}%"
        );
        $sql = "select a.xcliente_id,a.xcliente
            from ms_clientes a
            where a.xeliminado=0 and a.xactivo='S'$cond
            and a.xcliente like :cli
            limit 30";
        //print($sql);
        //die();
        //$this->app->db->debug=true;
        $data['data'] = $this->db->fetchAll($sql, $where);

        print(json_encode($data));
    }

    private function _calcular_estado($param) {
        $data = $param;
        $items = array();
        foreach ($data['ord-lin'] as $k => $v) {
            for ($i = 1; $i <= (int) $v['xcantidad']; $i++) {
                $v['xorden'] = $i;
                $v['xestado-actual'] = '';

                if ($v['xestado'] == '3') {
                    $v['xestado-actual'] = 'Cancelado';
                }
                if ($v['xestado'] == '0') {
                    $v['xestado-actual'] = 'Cancelado';
                    //SI EL PEDIDO CABECERA NO ESTÁ VERIFICADO
                    if ($data['ord']['xestado'] != '12' && $data['ord']['xestado'] != '17') {
                        $v['xestado-actual'] = $data['ord']['xestado_desc'];
                    }
                    if ($data['ord']['xestado'] == '12' || $data['ord']['xestado'] == '17' || $data['ord']['xestado'] == '4' || $data['ord']['xestado'] == '5') {
                        $v['xestado-actual'] = $data['ord']['xestado_desc'];
                        $v['xreferencia'] = str_pad($v['xpedido_id'], 4, '0', STR_PAD_LEFT) . '-' .
                                str_pad($v['xlin_id'], 4, '0', STR_PAD_LEFT) . '-' .
                                str_pad($data['ord']['xcliente_id'], 4, '0', STR_PAD_LEFT) . '-' .
                                str_pad($v['xarticulo_id'], 3, '0', STR_PAD_LEFT) . '-' .
                                str_pad($v['xorden'], 2, '0', STR_PAD_LEFT);

                        $sql = "select * from ms_pedidos_proveedor where xreferencia='{$v['xreferencia']}'";
                        //print($sql);
                        //die();
                        $env = $this->db->fetchRow($sql);
                        if ($env) {
                            //SI ES COMBO
                            if ($env['xservicio'] == 'N') {
                                if ($env['xproveedor_id'] != 4)
                                    $v['xestado-actual'] = 'En preparación';

                                if ($env['xtracking'] != '') {
                                    //$v['xtracking'] = '<a class="text-info" href="tracking.ms?ref=' . $data['ord']['xhash'] . '&track=' . $env['xtracking'] . '">' . $env['xtracking'] . '</a>';
                                    $v['xtracking'] = '<a class="text-info view-tracking" data-track="' . $env['xtracking'] . '" href="javascript:void(0);">' . $env['xtracking'] . '</a>';

                                    $v['xestado-actual'] = 'Preparado para enviar a transportista';

                                    if ($env['xcorte_id'] > 0) {
                                        $sql = "select a.xcorte_id,a.xestado_id,b.xestado,c.xguia_aerea,c.xfecha_salida"
                                                . " from ms_cortes_envios a"
                                                . " left join ms_cortes_estados b on a.xestado_id=b.xestado_id"
                                                . " left join ms_cortes_destinos c on a.xcorte_id=c.xcorte_id and c.xdestino_id=1"
                                                . " where a.xcorte_id={$env['xcorte_id']}";
                                        $corte = $this->db->fetchRow($sql);
                                        if ($corte) {
                                            $v['xcorte_id'] = $corte['xcorte_id'];
                                            $v['xguia_aerea'] = $corte['xguia_aerea'];
                                            $v['xfecha_salida'] = $corte['xfecha_salida'];
                                            $v['xfecha_pedido_format'] = $data['ord']['xfecha_pedido_format'];
                                            if ($corte['xestado_id'] >= 3)
                                                $v['xestado-actual'] = $corte['xestado'];
                                        }
                                    }
                                }
                            }

                            //SI ES SERVICIO EN CUBA
                            if ($env['xservicio'] == 'S') {
                                if ($env['xestado_id'] == 6)
                                    $v['xestado-actual'] = 'Entregado';
                                if ($env['xestado_id'] == 19)
                                    $v['xestado-actual'] = 'Solicitado';
                                if ($env['xestado_id'] == 20)
                                    $v['xestado-actual'] = 'En proceso';
                                if ($env['xestado_id'] == 22)
                                    $v['xestado-actual'] = 'Envío Cancelado';
                            }
                        }
                    }
                }

                $items[] = $v;
            }
        }

        return $items;
    }

    private function _generate_new_ref_servicios($order_id) {
        $cfg = $this->app->get_configuraciones();

        $where = array(
            'xpedido_id' => $order_id
        );
        $this->db->del('ms_pedidos_proveedor', $where);

        $sql = "select a.*,c.*,d.xnombre,d.xnombre2,d.xapellido1,d.xapellido2
            ,date_format(c.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format
            ,b.xarticulo as xarticulo_art,b.xprecio as xprecio_art,b.xcoste as xcoste_art
            ,b.xservicio,b.xproveedor_id,b.xrepartidor_id
            ,b.xcoste_cup,b.xcoste_reparto_cup,b.xeditable_coste_cup,e.xbodeguero
            ,c.xobs as xobs_pedido
            from ms_pedidos_lin a
            left join ms_articulos b on a.xarticulo_id=b.xarticulo_id
            left join ms_pedidos c on a.xpedido_id=c.xpedido_id
            left join ms_clientes d on c.xcliente_id=d.xcliente_id
            left join ms_repartidores e on b.xrepartidor_id=e.xrepartidor_id
            where a.xpedido_id={$order_id}";
        //print($sql);
        //die();
        $rr = $this->db->fetchAll($sql);
        foreach ($rr as $k => $v) {
            //$data[$k]['ximporte'] = number_format($v['ximporte'], 2, ',', '');
            for ($i = 1; $i <= $v['xcantidad']; $i++) {
                $v['xorden'] = $i;
                $ref = $this->app->get_ref_track($v);
                $sql = "select a.*"
                        . " from ms_pedidos_proveedor a"
                        . " where a.xreferencia='$ref'";
                $row = $this->db->fetchRow($sql);
                if (!$row) {
                    //AÑADIR AQUÍ EL CALCULO DEL COSTE POR PROVEEDOR QUE ACTUALMENTE LO COGE DEL ARTICULO 
                    //Y ESO ESTA EN DESUSO / OBSOLETO

                    if ($v['xship_apellido2'] == '')
                        $v['xship_apellido2'] = 'Rodríguez';
                    $coste_art = $v['xcoste_art'];
                    $insert = array(
                        'xreferencia' => $ref,
                        'xdate_added' => date(dateSQL),
                        'xcliente_id' => $v['xcliente_id'],
                        'xpedido_id' => $v['xpedido_id'],
                        'xlin_id' => $v['xlin_id'],
                        'xpreparado' => 'N',
                        'xarticulo_id' => $v['xarticulo_id'],
                        'xproveedor_id' => $v['xproveedor_id'],
                        'xnombre' => $v['xnombre'],
                        'xnombre2' => $v['xnombre2'],
                        'xapellido1' => $v['xapellido1'],
                        'xapellido2' => $v['xapellido2'],
                        'xship_name' => $v['xship_name'],
                        'xship_name2' => $v['xship_name2'],
                        'xship_apellido1' => $v['xship_apellido1'],
                        'xship_apellido2' => $v['xship_apellido2'],
                        'xship_dir1' => $v['xship_dir1'],
                        'xship_country' => $v['xship_country'],
                        'xship_phone' => $v['xship_phone'],
                        'xship_ci' => $v['xship_ci'],
                        'xship_provincia' => $v['xship_provincia'],
                        'xship_city' => $v['xship_city'],
                        'xship_zipcode' => $v['xship_zipcode'],
                        'xship_reparto' => $v['xship_reparto'],
                        'xship_numero' => $v['xship_numero'],
                        'xship_apartamento' => $v['xship_apartamento'],
                        'xship_piso' => $v['xship_piso'],
                        'xship_entre_calle1' => $v['xship_entre_calle1'],
                        'xship_entre_calle2' => $v['xship_entre_calle2'],
                        'xarticulo' => $v['xarticulo_art'],
                        'xprecio' => $v['xprecio_art'],
                        'xcoste' => $coste_art,
                        'xrevendedor_id' => $v['xrevendedor_id'],
                        'xdestino_id' => $v['xdestino_id'],
                        'xservicio' => $v['xservicio'],
                        'xestado_id' => 20,
                        'xfecha_pago' => $v['xfecha_pago'],
                        'xweb_id' => $v['xweb_id'],
                        'xrepartidor_id' => $v['xrepartidor_id'],
                        'xcoste_cup' => $v['xcoste_cup'],
                        'xcoste_reparto_cup' => $v['xcoste_reparto_cup'],
                        'xeditable_coste_cup' => $v['xeditable_coste_cup'],
                        'xbodeguero' => $v['xbodeguero'],
                        'xseguro' => '0',
                        'xobs' => $v['xobs_pedido']
                    );
                    if ($insert['xnombre'] == '')
                        $insert['xnombre'] = $v['xcliente'];
                    //print_r($insert);
                    //die();
                    $this->db->insert('ms_pedidos_proveedor', $insert);
                    //die('done');

                    $insert_ph = array(
                        'xpedido_id' => $v['xpedido_id'],
                        'xuseralta_id' => $this->app->user_id,
                        'xdatealta' => date(dateSQL),
                        'xestado' => '20',
                        'xobs' => 'Generado envío: ' . $ref
                    );
                    $this->app->db->insert('ms_pedidos_historico', $insert_ph);

                    $history = array(
                        'xentity' => 'ENVIOS',
                        'xaction' => 'INSERT-ENVIO',
                        'xid' => $ref,
                        'xobs' => 'ENVIO: ' . $ref
                    );
                    $this->app->add_history($history);
                }
            }
        }
    }

    private function _check_envios($param) {
        $data = array(
            'status' => 1
        );

        $sql = "select * from ms_pedidos where xpedido_id={$param['doc']}";
        $ord = $this->db->fetchRow($sql);
        $sql = "select * from ms_pedidos_proveedor where xpedido_id={$param['doc']}";
        $envios = $this->db->fetchAll($sql);

        $insert = array(
            'xpedido_id' => $ord['xpedido_id'],
            'xuseralta_id' => $this->app->user_id,
            'xdatealta' => date(dateSQL),
            'xestado' => '0',
            'xobs' => 'Revisión chequeo de Envíos desde la ficha de pedido'
        );
        $this->app->db->insert('ms_pedidos_historico', $insert);

        if (count($envios) > 0) {
            $insert = array(
                'xpedido_id' => $ord['xpedido_id'],
                'xuseralta_id' => $this->app->user_id,
                'xdatealta' => date(dateSQL),
                'xestado' => '0',
                'xobs' => 'Existen envíos ya creados'
            );
            $this->app->db->insert('ms_pedidos_historico', $insert);

            if (in_array($ord['xestado'], array(23, 24))) {
                $update = array(
                    'xestado_id' => $ord['xestado']
                );
                $where = array(
                    'xpedido_id' => $ord['xpedido_id']
                );
                //print_r($update);
                //print_r($where);
                //die();
                //$this->db->debug=true;
                $this->db->update('ms_pedidos_proveedor', $update, $where);

                $insert = array(
                    'xpedido_id' => $ord['xpedido_id'],
                    'xuseralta_id' => $this->app->user_id,
                    'xdatealta' => date(dateSQL),
                    'xestado' => '0',
                    'xobs' => 'Actualización de los estados 23 - 24'
                );
                $this->app->db->insert('ms_pedidos_historico', $insert);
            }
        } else {
            $this->_generate_new_ref_servicios($param['doc']);

            if (in_array($ord['xestado'], array(23, 24))) {
                $update = array(
                    'xestado_id' => $ord['xestado']
                );
                $where = array(
                    'xpedido_id' => $ord['xpedido_id']
                );
                $this->db->update('ms_pedidos_proveedor', $update, $where);

                $insert = array(
                    'xpedido_id' => $ord['xpedido_id'],
                    'xuseralta_id' => $this->app->user_id,
                    'xdatealta' => date(dateSQL),
                    'xestado' => '0',
                    'xobs' => 'Actualización de los estados 23 - 24'
                );
                $this->app->db->insert('ms_pedidos_historico', $insert);
            }
        }

        return $data;
    }

    private function _list_historico_doc($param) {
        $data = array();
        $cond = '';

        $sql = "select a.*,b.xestado as xestado_desc,c.xusuario
            ,date_format(a.xdatealta,'%d/%m/%Y %H:%i:%s') as xfecha_format
            from ms_pedidos_historico a
            left join ms_estados b on a.xestado=b.xestado_id
            left join ms_usuarios c on a.xuseralta_id=c.xusuario_id
            where a.xpedido_id={$param['doc']}";
        //print($sql);
        //die();
        //$data = $this->db->fetchAll($sql, $val);
        $rr = $this->db->fetchAll($sql);

        foreach ($rr as $k => $v) {
            $data[] = $v;
        }

        return $data;
    }

    private function _liberar_chofer($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Pedido liberaro de Chofer y cambiado su estado actual a En preparación'
        );

        $sql = "select * from ms_pedidos where xpedido_id={$param['pedido']}";
        $ord = $this->db->fetchRow($sql);

        if ($ord['xestado'] != 24) {
            $data['status'] = 0;
            $data['msg'] = 'No puede liberar el chofer pues el estado del pedido ya no se encuentra en reparto.';
        }

        if ($data['status'] == 1) {
            $update = array(
                'xchofer_id' => 0,
                'xestado' => 23
            );
            $where = array(
                'xpedido_id' => $param['pedido']
            );
            $this->db->update('ms_pedidos', $update, $where);

            $insert = array(
                'xpedido_id' => $param['pedido'],
                'xuseralta_id' => $this->app->user_id,
                'xdatealta' => date(dateSQL),
                'xestado' => 23,
                'xobs' => 'Pedido liberado de Chofer'
            );
            $this->app->db->insert('ms_pedidos_historico', $insert);
        }

        return $data;
    }

    private function _list_pedidos_almacen($param) {
        $data = array(
            'rows' => array()
        );
        $cond = '';

        //$this->_generate_new_ref_servicios();
        $estados = '6,12,17,23,24';
        if ($param['finalizados'] == 'S') {
            $estados = '7';

            if (isset($param['fecha-ini']) && $param['fecha-ini'] != '') {
                $tmp = explode('/', $param['fecha-ini']);
                $tmp = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and a.xfecha>='{$tmp}'";
            }
            if (isset($param['fecha-fin']) && $param['fecha-fin'] != '') {
                $tmp = explode('/', $param['fecha-fin']);
                $tmp = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                $cond .= " and a.xfecha<='{$tmp}'";
            }

            if (isset($param['articulo']) && $param['articulo'] != '') {
                $cond .= " and a.xpedido_id in (select xpedido_id from ms_pedidos_lin where xarticulo_id={$param['articulo']})";
            }

            if (isset($param['pedido']) && $param['pedido'] != '') {
                $cond .= " and a.xpedido_id {$param['pedido']}";
            }
            if (isset($param['referencia']) && $param['referencia'] != '') {
                $cond .= " and a.xpedido_id {$param['referencia']}";
            }
        }

        $sql = "select a.xpedido_id,a.xhash,a.xcliente,a.xrevendedor_id,a.xobs,a.xchofer_id,a.xestado,a.xalmacen_id
            ,a.xship_apartamento,a.xship_apellido1,a.xship_apellido2,a.xship_ci,a.xship_city,a.xship_country,a.xship_dir1,a.xship_dir2
            ,a.xship_entre_calle1,a.xship_entre_calle2,a.xship_name,a.xship_name2,a.xship_numero,a.xship_obs,a.xship_phone
            ,a.xship_piso,a.xship_provincia,a.xship_reparto,a.xship_zipcode
            ,date_format(a.xfecha_pago,'%d/%m/%Y %H:%i:%s') as xfecha_pago_format
            ,b.xestado as xestado_desc,b.xcolor as xestado_color,c.xrevendedor,d.xweb,e.xchofer
            ,a.xuseralta_id,f.xusuario
            from ms_pedidos a
            left join ms_estados b on a.xestado=b.xestado_id
            left join ms_revendedores c on a.xrevendedor_id=c.xrevendedor_id
            left join ms_webs d on a.xweb_id=d.xweb_id
            left join ms_chofers e on a.xchofer_id=e.xchofer_id
            left join ms_usuarios f on a.xuseralta_id=f.xusuario_id
            where a.xalmacen_id={$param['almacen']} and a.xestado in ($estados)$cond
            order by a.xpedido_id desc
            limit 500";
        //print($sql);
        //die();
        $rows = $this->db->fetchAll($sql);

        $ords = array();
        foreach ($rows as $k => $v) {
            $rows[$k]['items'] = array();
            $ords[] = $v['xpedido_id'];
        }

        if (count($ords) > 0) {
            $sql = "select xpedido_id,xarticulo_id,xarticulo,xcantidad from ms_pedidos_lin where xpedido_id in (" . join(',', $ords) . ")";
            $arts = $this->db->fetchAll($sql);

            foreach ($rows as $k => $v) {
                foreach ($arts as $ka => $va) {
                    if ($va['xpedido_id'] == $v['xpedido_id']) {
                        $va['xcantidad'] = (int) $va['xcantidad'];
                        $rows[$k]['items'][] = $va;
                    }
                }
            }
        }

        $data['rows'] = $rows;

        return $data;
    }

    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN DE DOCUMENTO PEDIDO MANDASALDO">
    //############################ DOCUMENTO PEDIDO MANDASALDO
    private function _dl_order_an($param) {
        $this->page = 1;

        $data = array(
            'id' => $param['id'],
            'items' => array(),
            'rev' => array(),
            'ord' => array(),
            'cli' => array()
        );

        $config = $this->app->get_configuraciones();

        $val = array(
            'rev' => $this->app->punto_venta
        );
        $sql = "select a.*"
                . " from ms_revendedores a"
                . " where a.xrevendedor_id=:rev";
        $data['rev'] = $this->app->db->fetchRow($sql, $val);

        //print_r($data['rev']);
        //die();

        $val = array(
            'ord' => $param['id'],
                //'rev' => $this->app->punto_venta
        );
        $sql = "select a.*,b.xusuario"
                . ",date_format(a.xdatealta,'%d/%m/%Y') as xfecha_format"
                . " from ms_pedidos a"
                . " left join ms_usuarios b on a.xuseralta_id=b.xusuario_id"
                . " where a.xpedido_id=:ord"; //and a.xrevendedor_id=:rev
        //print_r($val);
        //print($sql);
        //die();
        $data['ord'] = $this->app->db->fetchRow($sql, $val);

        //print_r($data['ord']);
        //die();

        if (isset($data['ord'])) {
            //$data['ord']['xbase_format'] = number_format($data['ord']['xbase'], 2, ',', '');
            $data['ord']['xbase_format'] = number_format($data['ord']['ximporte'] - $data['ord']['xtransporte'], 2, ',', '');
            $data['ord']['xbase_cup_format'] = number_format($data['ord']['xbase_cup'] - $data['ord']['xtransporte_cup'], 0, ',', '');
            $data['ord']['ximp_servicio_format'] = number_format($data['ord']['ximp_servicio'], 2, ',', '');
            $data['ord']['ximp_servicio_cup_format'] = number_format($data['ord']['ximp_servicio_cup'], 0, ',', '');
            $data['ord']['xtransporte_format'] = number_format($data['ord']['xtransporte'], 2, ',', ' ');
            $data['ord']['xtransporte_cup_format'] = number_format($data['ord']['xtransporte_cup'], 0, ',', '');
            $data['ord']['ximporte_format'] = number_format($data['ord']['ximporte'], 2, ',', '');
            $data['ord']['ximporte_cup_format'] = number_format($data['ord']['ximporte_cup'], 0, ',', '');

            //QR CODE
            require_once BASE_CLASS . '/phpqrcode/qrlib.php';
            $data['ord']['qrfile'] = PATH_TMP . "/{$data['ord']['xpedido_id']}.png";
            $content = "{$data['ord']['xhash']}";
            QRcode::png($content, $data['ord']['qrfile'], 'L', 3, 2);
        }

        $val = array(
            'cli' => $data['ord']['xcliente_id'],
        );
        $sql = "select a.*"
                . " from ms_clientes a"
                . " where a.xcliente_id=:cli";
        $data['cli'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'ord' => $data['ord']['xpedido_id']
        );
        $sql = "select a.*"
                . " from ms_pedidos_lin a"
                . " where a.xpedido_id=:ord"
                . " order by a.xlin_id";
        //print_r($val);
        //print($sql);
        //die();
        $data['items'] = $this->app->db->fetchAll($sql, $val);

        foreach ($data['items'] as $k => $v) {
            $data['items'][$k]['xcantidad_format'] = number_format($v['xcantidad'], 0, ',', '');
            $data['items'][$k]['xprecio_format'] = number_format($v['xprecio'], 2, ',', '');
            $data['items'][$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            //$data['items'][$k]['xprecio_cup_format'] = number_format($v['xprecio_cup'], 0, ',', '');
            //$data['items'][$k]['ximporte_cup_format'] = number_format($v['ximporte_cup'], 0, ',', '');
        }

        //FORMA DE PAGO DEL PEDIDO
        $fpagos = json_decode(FORMAS_PAGO_PV_JSON, true);
        $data['ord']['fp_moneda'] = $fpagos[$data['ord']['xfpago']]['moneda'];

        //print_r($data);
        //die();
        //PARA DEMO RELLENA LINEAS PARA DEBUG **** NO ELIMINAR ****
        //for ($i = 1; $i < 60; $i++)
        //    $data['items'][] = $data['items'][0];

        $h = 5;
        $c1 = 130;
        $c2 = 17;
        $c3 = 21;
        $c4 = 21;
        $c5 = 23;
        $c6 = 23;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_order_header_an($pdf, $data);

        //$this->_order_header_detalle($pdf, $param['id']);
        $pdf->SetFillColor(243, 243, 243);
        $this->total = 0;
        $this->lin = 0;
        $max_lines_page = 20;
        foreach ($data['items'] as $k => $v) {
            $fill = false;
            if (($k + 1) % 2 == 0)
                $fill = true;
            $this->lin++;

            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($c1, $h, utf8_decode($v['xarticulo']), 'LR', 0, 'L', $fill);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($c2, $h, $v['xcantidad_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c3, $h, $v['xprecio_format'], 'LR', 0, 'R', $fill);
            //$pdf->Cell($c4, $h, $v['xprecio_cup_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c5, $h, $v['ximporte_format'], 'LR', 1, 'R', $fill);
            //$pdf->Cell($c6, $h, $v['ximporte_cup_format'], 'LR', 1, 'R', $fill);

            $this->total += $v['ximporte'];

            if (($k + 1) % $max_lines_page == 0) {
                $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
                //$pdf->Cell($c4, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c5, $h, '', 'LRB', 1, 'R', false);
                //$pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);

                $this->lin = 0;
                $this->_order_page_an($pdf);
                $this->_order_footer_detalle_an($pdf, 'P', $data);
                $pdf->AddPage();
                $this->_order_header_an($pdf, $data);
            }
        }

        if ($this->lin < $max_lines_page) {
            for ($i = 1; $i < ($max_lines_page - $this->lin); $i++) {
                $pdf->Cell($c1, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LR', 0, 'R', false);
                //$pdf->Cell($c4, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c5, $h, '', 'LR', 1, 'R', false);
                //$pdf->Cell($c6, $h, '', 'LR', 1, 'R', false);
            }
            $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
            //$pdf->Cell($c4, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c5, $h, '', 'LRB', 1, 'R', false);
            //$pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);
        }

        //$this->transporte = ($this->total < $config['xtransporte_free']) ? $data['ord']['xtransporte'] : 0;
        //$this->seguro = $data['ord']['xseguro'];
        //$this->servicio = $data['ord']['ximp_servicio'];

        $this->_order_page_an($pdf, $data);
        $this->_order_footer_detalle_an($pdf, 'F', $data);

        $pdf->Output('pedido-' . $data['ord']['xhash'] . '.pdf', 'I');
    }

    private function _order_header_an($pdf, $data) {
        $h = 5;
        $c1 = 130;
        $c2 = 17;
        $c3 = 21;
        $c4 = 21;
        $c5 = 23;
        $c6 = 23;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        //https://mandasaldo.com/admin_mandasaldo_v2/img/rev/1.jpg
        $img_rev = "https://mandasaldo.com/admin_mandasaldo_v2/img/rev/{$data['rev']['xrevendedor_id']}.jpg";
        $img_ms = IMG_PUBLIC . "/logo-an.jpg";
        //print($img_ms);
        //die();
        if (file_exists($img_ms)) {
            $pdf->Image($img_ms, 40, 3, 30, 0, 'JPG');
        }

        $pdf->SetLeftMargin(90);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(90, 10);
        $pdf->Cell(120, 7, utf8_decode(mb_strtoupper('AllNovu.com')), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xdir1'])), 0, 1, 'C', false);
        //$pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xzipcode']) . ' ' . mb_strtoupper($data['rev']['xcity']) . ' Tlf:' . $data['rev']['xmovil']), 0, 1, 'C', false);

        $pdf->SetLeftMargin(10);

        $pdf->Rect(10, 30, 90, 50);
        $pdf->SetFont('Arial', '', 12);
        $pdf->setXY(10, 30);
        $pdf->Cell(90, 7, "Fecha: {$data['ord']['xfecha_format']}", 1, 1, 'L', false);
        $pdf->Cell(90, 6, "Pedido:", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "{$data['ord']['xhash']}", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "", 0, 1, 'L', false);
        $pdf->Ln(20);
        //$pdf->Cell(90, 5, "PAGADO: {$data['ord']['pagado_format']}", 0, 1, 'L', false);
        //$pdf->Cell(90, 7, "{$data['ord']['xusuario']}", 'T', 1, 'L', false);
        $pdf->Cell(90, 7, "{$data['ord']['xuseralta_id']}", 'T', 1, 'L', false);

        if (isset($data['ord']['qrfile']) && $data['ord']['qrfile'] != '' && file_exists($data['ord']['qrfile'])) {
            $pdf->Image($data['ord']['qrfile'], 60, $pdf->GetY() - 55 + 15, 30, 0, 'PNG');
        }

        /*
         * //DATOS DEL REMITENTE
          $pdf->Ln(2);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 7, "Datos del Remitente", 0, 1, 'C', false);
          $pdf->Ln(2);
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xnombre']} {$data['cli']['xnombre2']} {$data['cli']['xapellido1']} {$data['cli']['xapellido2']}"), 0, 1, 'C', false);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xemail']}"), 0, 1, 'C', false);
          $pdf->Cell(90, 5, utf8_decode("+{$data['cli']['xprefijo']} {$data['cli']['xmovil']}"), 0, 1, 'C', false);
         * 
         */

        $pdf->Rect(105, 30, 95, 50);
        $pdf->setXY(105, 32);
        $pdf->SetLeftMargin(105);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 7, "Datos del Destinatario", 0, 1, 'C', false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(90, 7, utf8_decode("{$data['ord']['xship_name']} {$data['ord']['xship_name2']} {$data['ord']['xship_apellido1']} {$data['ord']['xship_apellido2']}"), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_dir1']}" . (($data['ord']['xship_numero'] != '') ? " Nº {$data['ord']['xship_numero']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_apartamento'] != '') ? " Apt. {$data['ord']['xship_apartamento']}" : '') . (($data['ord']['xship_piso'] != '') ? " Piso {$data['ord']['xship_piso']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_entre_calle1'] != '') ? " Entre {$data['ord']['xship_entre_calle1']}" : '') . (($data['ord']['xship_entre_calle2'] != '') ? " y {$data['ord']['xship_entre_calle2']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_zipcode']} {$data['ord']['xship_city']}"), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_provincia']} - CUBA"), 0, 1, 'C', false);
        $pdf->SetLeftMargin(10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 85);
        $pdf->Cell($c1, $h, 'Producto', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c3, $h, 'Precio USD', 1, 0, 'R', false);
        //$pdf->Cell($c4, $h, 'Precio CUP', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe USD', 1, 1, 'R', false);
        //$pf->Cell($c6, $h, 'Importe CUP', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_header_detalle_an($pdf, $corte) {
        $h = 5;
        $c1 = 50;
        $c2 = 50;
        $c3 = 30;
        $c4 = 30;
        $c5 = 30;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->SetLeftMargin(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(10, 10);
        $pdf->Cell(190, 7, "Detalle Corte {$corte}", 0, 1, 'C', false);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 20);
        $pdf->Cell($c1, $h, 'Tracking', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, utf8_decode('Artículo'), 1, 0, 'C', false);
        $pdf->Cell($c3, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Precio', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_page_an($pdf) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(10, 280);
        $pdf->Cell(50, 4, utf8_decode("Página: {$this->page}"), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _order_footer_detalle_an($pdf, $type = 'P', $data) {
        //VALORES DE $TYPE
        //P - PAGINA
        //F - FINAL
        if ($type == 'F') {
            //$pdf->setXY(100, 220);
            $pdf->setY(225);
            $pdf->SetLeftMargin(120);
            $pdf->SetFont('Arial', 'B', 10);

            $d = $data['ord'];

            /*
              $pdf->Cell(40, 7, '', 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('USD'), 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('CUP'), 0, 1, 'R', false);
             * 
             */

            $marca_fpago = '';
            if ($data['ord']['fp_moneda'] == 'usd') {
                $marca_fpago = '.';
            }

            $pdf->Cell(40, 7, utf8_decode('Suma líneas'), 'B', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['xbase_format']) . ' USD', 'B', 1, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['xbase_cup_format']) . ' CUP', 'B', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Transporte'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['xtransporte_format']) . $marca_fpago . ' USD', 'TB', 1, 'R', false); //PUNTO ANTES DE USD SI ES PAGO USD
            //$pdf->Cell(35, 7, utf8_decode($d['xtransporte_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Servicio'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_format']) . ' USD', 'TB', 1, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            $pdf->Cell(35, 7, '', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Total'), 'T', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximporte_format']) . ' USD', 'T', 0, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximporte_cup_format']) . ' CUP', 'T', 1, 'R', false);
        }

        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(165, 258);
        $pdf->Cell(60, 5, utf8_decode('*Pago desde el exterior'), 0, 0, 'L', false);

        $s = 'Consultar términos de garantía AllNovu en https://allnovu.com/garantia-de-los-equipos/';
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->setXY(20, 270);
        $pdf->Cell(170, 5, utf8_decode($s), 1, 0, 'C', false);
    }

    private function _order_footer_an($pdf) {
        $s = "Total...:    " . number_format($this->total, 2, ',', '') . ' USD';

        $pdf->SetFont('Arial', '', 8);
        //$pdf->setXY(140, 270);
        $pdf->Ln(7);
        $pdf->Cell(190, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    //############################ /DOCUMENTO PEDIDO
    //</editor-fold>
    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN DE DOCUMENTO PEDIDO MANDASALDO">
    //############################ DOCUMENTO PEDIDO MANDASALDO
    private function _dl_order_ms($param) {
        $this->page = 1;

        $data = array(
            'id' => $param['id'],
            'items' => array(),
            'rev' => array(),
            'ord' => array(),
            'cli' => array()
        );

        $config = $this->app->get_configuraciones();

        $val = array(
            'rev' => $this->app->punto_venta
        );
        $sql = "select a.*"
                . " from ms_revendedores a"
                . " where a.xrevendedor_id=:rev";
        $data['rev'] = $this->app->db->fetchRow($sql, $val);

        //print_r($data['rev']);
        //die();

        $val = array(
            'ord' => $param['id'],
                //'rev' => $this->app->punto_venta
        );
        $sql = "select a.*,b.xusuario"
                . ",date_format(a.xdatealta,'%d/%m/%Y') as xfecha_format"
                . " from ms_pedidos a"
                . " left join ms_usuarios b on a.xuseralta_id=b.xusuario_id"
                . " where a.xpedido_id=:ord"; //and a.xrevendedor_id=:rev
        //print_r($val);
        //print($sql);
        //die();
        $data['ord'] = $this->app->db->fetchRow($sql, $val);

        //print_r($data['ord']);
        //die();

        if (isset($data['ord'])) {
            //$data['ord']['xbase_format'] = number_format($data['ord']['xbase'], 2, ',', '');
            $data['ord']['xbase_format'] = number_format($data['ord']['ximporte'] - $data['ord']['xtransporte'], 2, ',', '');
            $data['ord']['xbase_cup_format'] = number_format($data['ord']['xbase_cup'] - $data['ord']['xtransporte_cup'], 0, ',', '');
            $data['ord']['ximp_servicio_format'] = number_format($data['ord']['ximp_servicio'], 2, ',', '');
            $data['ord']['ximp_servicio_cup_format'] = number_format($data['ord']['ximp_servicio_cup'], 0, ',', '');
            $data['ord']['xtransporte_format'] = number_format($data['ord']['xtransporte'], 2, ',', ' ');
            $data['ord']['xtransporte_cup_format'] = number_format($data['ord']['xtransporte_cup'], 0, ',', '');
            $data['ord']['ximporte_format'] = number_format($data['ord']['ximporte'], 2, ',', '');
            $data['ord']['ximporte_cup_format'] = number_format($data['ord']['ximporte_cup'], 0, ',', '');

            //QR CODE
            require_once BASE_CLASS . '/phpqrcode/qrlib.php';
            $data['ord']['qrfile'] = PATH_TMP . "/{$data['ord']['xpedido_id']}.png";
            $content = "{$data['ord']['xhash']}";
            QRcode::png($content, $data['ord']['qrfile'], 'L', 3, 2);
        }

        $val = array(
            'cli' => $data['ord']['xcliente_id'],
        );
        $sql = "select a.*"
                . " from ms_clientes a"
                . " where a.xcliente_id=:cli";
        $data['cli'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'ord' => $data['ord']['xpedido_id']
        );
        $sql = "select a.*"
                . " from ms_pedidos_lin a"
                . " where a.xpedido_id=:ord"
                . " order by a.xlin_id";
        //print_r($val);
        //print($sql);
        //die();
        $data['items'] = $this->app->db->fetchAll($sql, $val);

        foreach ($data['items'] as $k => $v) {
            $data['items'][$k]['xcantidad_format'] = number_format($v['xcantidad'], 0, ',', '');
            $data['items'][$k]['xprecio_format'] = number_format($v['xprecio'], 2, ',', '');
            $data['items'][$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            //$data['items'][$k]['xprecio_cup_format'] = number_format($v['xprecio_cup'], 0, ',', '');
            //$data['items'][$k]['ximporte_cup_format'] = number_format($v['ximporte_cup'], 0, ',', '');
        }

        //FORMA DE PAGO DEL PEDIDO
        $fpagos = json_decode(FORMAS_PAGO_PV_JSON, true);
        $data['ord']['fp_moneda'] = $fpagos[$data['ord']['xfpago']]['moneda'];

        //print_r($data);
        //die();
        //PARA DEMO RELLENA LINEAS PARA DEBUG **** NO ELIMINAR ****
        //for ($i = 1; $i < 60; $i++)
        //    $data['items'][] = $data['items'][0];

        $h = 5;
        $c1 = 130;
        $c2 = 17;
        $c3 = 21;
        $c4 = 21;
        $c5 = 23;
        $c6 = 23;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_order_header_ms($pdf, $data);

        //$this->_order_header_detalle($pdf, $param['id']);
        $pdf->SetFillColor(243, 243, 243);
        $this->total = 0;
        $this->lin = 0;
        $max_lines_page = 20;
        foreach ($data['items'] as $k => $v) {
            $fill = false;
            if (($k + 1) % 2 == 0)
                $fill = true;
            $this->lin++;

            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($c1, $h, utf8_decode($v['xarticulo']), 'LR', 0, 'L', $fill);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($c2, $h, $v['xcantidad_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c3, $h, $v['xprecio_format'], 'LR', 0, 'R', $fill);
            //$pdf->Cell($c4, $h, $v['xprecio_cup_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c5, $h, $v['ximporte_format'], 'LR', 1, 'R', $fill);
            //$pdf->Cell($c6, $h, $v['ximporte_cup_format'], 'LR', 1, 'R', $fill);

            $this->total += $v['ximporte'];

            if (($k + 1) % $max_lines_page == 0) {
                $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
                //$pdf->Cell($c4, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c5, $h, '', 'LRB', 1, 'R', false);
                //$pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);

                $this->lin = 0;
                $this->_order_page_ms($pdf);
                $this->_order_footer_detalle_ms($pdf, 'P', $data);
                $pdf->AddPage();
                $this->_order_header_ms($pdf, $data);
            }
        }

        if ($this->lin < $max_lines_page) {
            for ($i = 1; $i < ($max_lines_page - $this->lin); $i++) {
                $pdf->Cell($c1, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LR', 0, 'R', false);
                //$pdf->Cell($c4, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c5, $h, '', 'LR', 1, 'R', false);
                //$pdf->Cell($c6, $h, '', 'LR', 1, 'R', false);
            }
            $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
            //$pdf->Cell($c4, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c5, $h, '', 'LRB', 1, 'R', false);
            //$pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);
        }

        //$this->transporte = ($this->total < $config['xtransporte_free']) ? $data['ord']['xtransporte'] : 0;
        //$this->seguro = $data['ord']['xseguro'];
        //$this->servicio = $data['ord']['ximp_servicio'];

        $this->_order_page_ms($pdf, $data);
        $this->_order_footer_detalle_ms($pdf, 'F', $data);

        $pdf->Output('pedido-' . $data['ord']['xhash'] . '.pdf', 'I');
    }

    private function _order_header_ms($pdf, $data) {
        $h = 5;
        $c1 = 130;
        $c2 = 17;
        $c3 = 21;
        $c4 = 21;
        $c5 = 23;
        $c6 = 23;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        //https://mandasaldo.com/admin_mandasaldo_v2/img/rev/1.jpg
        $img_rev = "https://mandasaldo.com/admin_mandasaldo_v2/img/rev/{$data['rev']['xrevendedor_id']}.jpg";
        $img_ms = IMG_PUBLIC . "/logo.jpg";
        //print($img_ms);
        //die();
        if (file_exists($img_ms)) {
            $pdf->Image($img_ms, 15, 5, 80, 0, 'JPG');
        }

        $pdf->SetLeftMargin(90);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(90, 10);
        $pdf->Cell(120, 7, utf8_decode(mb_strtoupper('Mandasaldo.com')), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xdir1'])), 0, 1, 'C', false);
        //$pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xzipcode']) . ' ' . mb_strtoupper($data['rev']['xcity']) . ' Tlf:' . $data['rev']['xmovil']), 0, 1, 'C', false);

        $pdf->SetLeftMargin(10);

        $pdf->Rect(10, 30, 90, 50);
        $pdf->SetFont('Arial', '', 12);
        $pdf->setXY(10, 30);
        $pdf->Cell(90, 7, "Fecha: {$data['ord']['xfecha_format']}", 1, 1, 'L', false);
        $pdf->Cell(90, 6, "Pedido:", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "{$data['ord']['xhash']}", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "", 0, 1, 'L', false);
        $pdf->Ln(20);
        //$pdf->Cell(90, 5, "PAGADO: {$data['ord']['pagado_format']}", 0, 1, 'L', false);
        //$pdf->Cell(90, 7, "{$data['ord']['xusuario']}", 'T', 1, 'L', false);
        $pdf->Cell(90, 7, "{$data['ord']['xuseralta_id']}", 'T', 1, 'L', false);

        if (isset($data['ord']['qrfile']) && $data['ord']['qrfile'] != '' && file_exists($data['ord']['qrfile'])) {
            $pdf->Image($data['ord']['qrfile'], 60, $pdf->GetY() - 55 + 15, 30, 0, 'PNG');
        }

        /*
         * //DATOS DEL REMITENTE
          $pdf->Ln(2);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 7, "Datos del Remitente", 0, 1, 'C', false);
          $pdf->Ln(2);
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xnombre']} {$data['cli']['xnombre2']} {$data['cli']['xapellido1']} {$data['cli']['xapellido2']}"), 0, 1, 'C', false);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xemail']}"), 0, 1, 'C', false);
          $pdf->Cell(90, 5, utf8_decode("+{$data['cli']['xprefijo']} {$data['cli']['xmovil']}"), 0, 1, 'C', false);
         * 
         */

        $pdf->Rect(105, 30, 95, 50);
        $pdf->setXY(105, 32);
        $pdf->SetLeftMargin(105);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 7, "Datos del Destinatario", 0, 1, 'C', false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(90, 7, utf8_decode("{$data['ord']['xship_name']} {$data['ord']['xship_name2']} {$data['ord']['xship_apellido1']} {$data['ord']['xship_apellido2']}"), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_dir1']}" . (($data['ord']['xship_numero'] != '') ? " Nº {$data['ord']['xship_numero']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_apartamento'] != '') ? " Apt. {$data['ord']['xship_apartamento']}" : '') . (($data['ord']['xship_piso'] != '') ? " Piso {$data['ord']['xship_piso']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_entre_calle1'] != '') ? " Entre {$data['ord']['xship_entre_calle1']}" : '') . (($data['ord']['xship_entre_calle2'] != '') ? " y {$data['ord']['xship_entre_calle2']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_zipcode']} {$data['ord']['xship_city']}"), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_provincia']} - CUBA"), 0, 1, 'C', false);
        $pdf->SetLeftMargin(10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 85);
        $pdf->Cell($c1, $h, 'Producto', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c3, $h, 'Precio USD', 1, 0, 'R', false);
        //$pdf->Cell($c4, $h, 'Precio CUP', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe USD', 1, 1, 'R', false);
        //$pf->Cell($c6, $h, 'Importe CUP', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_header_detalle_ms($pdf, $corte) {
        $h = 5;
        $c1 = 50;
        $c2 = 50;
        $c3 = 30;
        $c4 = 30;
        $c5 = 30;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->SetLeftMargin(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(10, 10);
        $pdf->Cell(190, 7, "Detalle Corte {$corte}", 0, 1, 'C', false);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 20);
        $pdf->Cell($c1, $h, 'Tracking', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, utf8_decode('Artículo'), 1, 0, 'C', false);
        $pdf->Cell($c3, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Precio', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_page_ms($pdf) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(10, 280);
        $pdf->Cell(50, 4, utf8_decode("Página: {$this->page}"), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _order_footer_detalle_ms($pdf, $type = 'P', $data) {
        //VALORES DE $TYPE
        //P - PAGINA
        //F - FINAL
        if ($type == 'F') {
            //$pdf->setXY(100, 220);
            $pdf->setY(225);
            $pdf->SetLeftMargin(120);
            $pdf->SetFont('Arial', 'B', 10);

            $d = $data['ord'];

            /*
              $pdf->Cell(40, 7, '', 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('USD'), 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('CUP'), 0, 1, 'R', false);
             * 
             */

            $marca_fpago = '';
            if ($data['ord']['fp_moneda'] == 'usd') {
                $marca_fpago = '.';
            }

            $pdf->Cell(40, 7, utf8_decode('Suma líneas'), 'B', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['xbase_format']) . ' USD', 'B', 1, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['xbase_cup_format']) . ' CUP', 'B', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Transporte'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['xtransporte_format']) . $marca_fpago . ' USD', 'TB', 1, 'R', false); //PUNTO ANTES DE USD SI ES PAGO USD
            //$pdf->Cell(35, 7, utf8_decode($d['xtransporte_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Servicio'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_format']) . ' USD', 'TB', 1, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            $pdf->Cell(35, 7, '', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Total'), 'T', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximporte_format']) . ' USD', 'T', 0, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximporte_cup_format']) . ' CUP', 'T', 1, 'R', false);
        }

        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(165, 258);
        $pdf->Cell(60, 5, utf8_decode('*Pago desde el exterior'), 0, 0, 'L', false);

        $s = 'Consultar términos de garantía AllNovu en https://allnovu.com/garantia-de-los-equipos/';
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->setXY(20, 270);
        //$pdf->Cell(170, 5, utf8_decode($s), 1, 0, 'C', false);
    }

    private function _order_footer_ms($pdf) {
        $s = "Total...:    " . number_format($this->total, 2, ',', '') . ' USD';

        $pdf->SetFont('Arial', '', 8);
        //$pdf->setXY(140, 270);
        $pdf->Ln(7);
        $pdf->Cell(190, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    //############################ /DOCUMENTO PEDIDO
    //</editor-fold>
    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN DE DOCUMENTO PEDIDO">
    //############################ DOCUMENTO PEDIDO
    private function _dl_order($param) {
        $this->page = 1;

        $data = array(
            'id' => $param['id'],
            'items' => array(),
            'rev' => array(),
            'ord' => array(),
            'cli' => array()
        );

        $config = $this->app->get_configuraciones();

        $val = array(
            'ord' => $param['id'],
                //'rev' => $this->app->punto_venta
        );
        $sql = "select a.*,b.xusuario"
                . ",date_format(a.xdatealta,'%d/%m/%Y') as xfecha_format"
                . " from ms_pedidos a"
                . " left join ms_usuarios b on a.xuseralta_id=b.xusuario_id"
                . " where a.xpedido_id=:ord"; //and a.xrevendedor_id=:rev
        //print_r($val);
        //print($sql);
        //die();
        $data['ord'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            //'rev' => $this->app->punto_venta
            'rev' => $data['ord']['xrevendedor_id']
        );
        $sql = "select a.*"
                . " from ms_revendedores a"
                . " where a.xrevendedor_id=:rev";
        $data['rev'] = $this->app->db->fetchRow($sql, $val);

        //print_r($data['rev']);
        //die();
        //print_r($data['ord']);
        //die();

        if (isset($data['ord'])) {
            $data['ord']['xbase_format'] = number_format($data['ord']['xbase'], 2, ',', '');
            //$data['ord']['xbase_format'] = number_format($data['ord']['ximporte'], 2, ',', '');
            $data['ord']['xbase_cup_format'] = number_format($data['ord']['xbase_cup'], 0, ',', '');
            $data['ord']['ximp_servicio_format'] = number_format($data['ord']['ximp_servicio'], 2, ',', '');
            $data['ord']['ximp_servicio_cup_format'] = number_format($data['ord']['ximp_servicio_cup'], 0, ',', '');
            $data['ord']['xtransporte_format'] = number_format($data['ord']['xtransporte'], 2, ',', ' ');
            $data['ord']['xtransporte_cup_format'] = number_format($data['ord']['xtransporte_cup'], 0, ',', '');
            $data['ord']['ximporte_format'] = number_format($data['ord']['ximporte'], 2, ',', '');
            $data['ord']['ximporte_cup_format'] = number_format($data['ord']['ximporte_cup'], 0, ',', '');

            //QR CODE
            require_once BASE_CLASS . '/phpqrcode/qrlib.php';
            $data['ord']['qrfile'] = PATH_TMP . "/{$data['ord']['xpedido_id']}.png";
            $content = "{$data['ord']['xhash']}";
            QRcode::png($content, $data['ord']['qrfile'], 'L', 3, 2);
        }

        $val = array(
            'cli' => $data['ord']['xcliente_id'],
        );
        $sql = "select a.*"
                . " from ms_clientes a"
                . " where a.xcliente_id=:cli";
        $data['cli'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'ord' => $data['ord']['xpedido_id']
        );
        $sql = "select a.*"
                . " from ms_pedidos_lin a"
                . " where a.xpedido_id=:ord"
                . " order by a.xlin_id";
        //print_r($val);
        //print($sql);
        //die();
        $data['items'] = $this->app->db->fetchAll($sql, $val);

        foreach ($data['items'] as $k => $v) {
            $data['items'][$k]['xcantidad_format'] = number_format($v['xcantidad'], 0, ',', '');
            $data['items'][$k]['xprecio_format'] = number_format($v['xprecio'], 2, ',', '');
            $data['items'][$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            $data['items'][$k]['xprecio_cup_format'] = number_format($v['xprecio_cup'], 0, ',', '');
            $data['items'][$k]['ximporte_cup_format'] = number_format($v['ximporte_cup'], 0, ',', '');
        }

        //FORMA DE PAGO DEL PEDIDO
        $fpagos = json_decode(FORMAS_PAGO_PV_JSON, true);
        $data['ord']['fp_moneda'] = $fpagos[$data['ord']['xfpago']]['moneda'];

        //print_r($data);
        //die();
        //PARA DEMO RELLENA LINEAS PARA DEBUG **** NO ELIMINAR ****
        //for ($i = 1; $i < 60; $i++)
        //    $data['items'][] = $data['items'][0];

        $h = 5;
        $c1 = 85;
        $c2 = 17;
        $c3 = 21;
        $c4 = 21;
        $c5 = 23;
        $c6 = 23;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_order_header($pdf, $data);

        //$this->_order_header_detalle($pdf, $param['id']);
        $pdf->SetFillColor(243, 243, 243);
        $this->total = 0;
        $this->lin = 0;
        $max_lines_page = 20;
        foreach ($data['items'] as $k => $v) {
            $fill = false;
            if (($k + 1) % 2 == 0)
                $fill = true;
            $this->lin++;

            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($c1, $h, utf8_decode($v['xarticulo']), 'LR', 0, 'L', $fill);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($c2, $h, $v['xcantidad_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c3, $h, $v['xprecio_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c4, $h, $v['xprecio_cup_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c5, $h, $v['ximporte_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c6, $h, $v['ximporte_cup_format'], 'LR', 1, 'R', $fill);

            $this->total += $v['ximporte'];

            if (($k + 1) % $max_lines_page == 0) {
                $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c4, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c5, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);

                $this->lin = 0;
                $this->_order_page($pdf);
                $this->_order_footer_detalle($pdf, 'P', $data);
                $pdf->AddPage();
                $this->_order_header($pdf, $data);
            }
        }

        if ($this->lin < $max_lines_page) {
            for ($i = 1; $i < ($max_lines_page - $this->lin); $i++) {
                $pdf->Cell($c1, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c4, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c5, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c6, $h, '', 'LR', 1, 'R', false);
            }
            $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c4, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c5, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);
        }

        //$this->transporte = ($this->total < $config['xtransporte_free']) ? $data['ord']['xtransporte'] : 0;
        //$this->seguro = $data['ord']['xseguro'];
        //$this->servicio = $data['ord']['ximp_servicio'];

        $this->_order_page($pdf, $data);
        $this->_order_footer_detalle($pdf, 'F', $data);

        $pdf->Output('pedido-' . $data['ord']['xhash'] . '.pdf', 'I');
    }

    private function _order_header($pdf, $data) {
        //print_r($data);
        //die();
        $h = 5;
        $c1 = 85;
        $c2 = 17;
        $c3 = 21;
        $c4 = 21;
        $c5 = 23;
        $c6 = 23;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        //https://mandasaldo.com/admin_mandasaldo_v2/img/rev/1.jpg
        $img_rev = "https://mandasaldo.com/admin_mandasaldo_v2/img/rev/{$data['rev']['xrevendedor_id']}.jpg";
        if (file_exists($img_rev)) {
            $pdf->Image($img_rev, 15, 5, 80, 0, 'JPG');
        }

        $pdf->SetLeftMargin(90);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(90, 10);
        $pdf->Cell(120, 7, utf8_decode(mb_strtoupper($data['rev']['xempresa'])), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xdir1'])), 0, 1, 'C', false);
        $pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xzipcode']) . ' ' . mb_strtoupper($data['rev']['xcity']) . ' Tlf:' . $data['rev']['xmovil']), 0, 1, 'C', false);

        $pdf->SetLeftMargin(10);

        $pdf->Rect(10, 30, 90, 50);
        $pdf->SetFont('Arial', '', 12);
        $pdf->setXY(10, 30);
        $pdf->Cell(90, 7, "Fecha: {$data['ord']['xfecha_format']}", 1, 1, 'L', false);
        $pdf->Cell(90, 6, "Pedido:", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "{$data['ord']['xhash']}", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "", 0, 1, 'L', false);
        $pdf->Ln(15);
        $pdf->Cell(90, 5, "PAGADO: {$data['ord']['pagado_format']}", 0, 1, 'L', false);
        //$pdf->Cell(90, 7, "{$data['ord']['xusuario']}", 'T', 1, 'L', false);
        $pdf->Cell(90, 7, "{$data['ord']['xuseralta_id']}", 'T', 1, 'L', false);

        if (isset($data['ord']['qrfile']) && $data['ord']['qrfile'] != '' && file_exists($data['ord']['qrfile'])) {
            $pdf->Image($data['ord']['qrfile'], 60, $pdf->GetY() - 55 + 15, 30, 0, 'PNG');
        }

        /*
         * //DATOS DEL REMITENTE
          $pdf->Ln(2);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 7, "Datos del Remitente", 0, 1, 'C', false);
          $pdf->Ln(2);
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xnombre']} {$data['cli']['xnombre2']} {$data['cli']['xapellido1']} {$data['cli']['xapellido2']}"), 0, 1, 'C', false);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xemail']}"), 0, 1, 'C', false);
          $pdf->Cell(90, 5, utf8_decode("+{$data['cli']['xprefijo']} {$data['cli']['xmovil']}"), 0, 1, 'C', false);
         * 
         */

        $pdf->Rect(105, 30, 95, 50);
        $pdf->setXY(105, 32);
        $pdf->SetLeftMargin(105);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 7, "Datos del Destinatario", 0, 1, 'C', false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(90, 7, utf8_decode("{$data['ord']['xship_name']} {$data['ord']['xship_name2']} {$data['ord']['xship_apellido1']} {$data['ord']['xship_apellido2']}"), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_dir1']}" . (($data['ord']['xship_numero'] != '') ? " Nº {$data['ord']['xship_numero']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_apartamento'] != '') ? " Apt. {$data['ord']['xship_apartamento']}" : '') . (($data['ord']['xship_piso'] != '') ? " Piso {$data['ord']['xship_piso']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_entre_calle1'] != '') ? " Entre {$data['ord']['xship_entre_calle1']}" : '') . (($data['ord']['xship_entre_calle2'] != '') ? " y {$data['ord']['xship_entre_calle2']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_zipcode']} {$data['ord']['xship_city']}"), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_provincia']} - CUBA"), 0, 1, 'C', false);
        $pdf->SetLeftMargin(10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 85);
        $pdf->Cell($c1, $h, 'Producto', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c3, $h, 'Precio USD', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Precio CUP', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe USD', 1, 0, 'R', false);
        $pdf->Cell($c6, $h, 'Importe CUP', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_header_detalle($pdf, $corte) {
        $h = 5;
        $c1 = 50;
        $c2 = 50;
        $c3 = 30;
        $c4 = 30;
        $c5 = 30;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->SetLeftMargin(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(10, 10);
        $pdf->Cell(190, 7, "Detalle Corte {$corte}", 0, 1, 'C', false);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 20);
        $pdf->Cell($c1, $h, 'Tracking', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, utf8_decode('Artículo'), 1, 0, 'C', false);
        $pdf->Cell($c3, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Precio', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_page($pdf) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(10, 280);
        $pdf->Cell(50, 4, utf8_decode("Página: {$this->page}"), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _order_footer_detalle($pdf, $type = 'P', $data) {
        //VALORES DE $TYPE
        //P - PAGINA
        //F - FINAL
        if ($type == 'F') {
            //$pdf->setXY(100, 220);
            $pdf->setY(225);
            $pdf->SetLeftMargin(95);
            $pdf->SetFont('Arial', 'B', 10);

            $d = $data['ord'];

            /*
              $pdf->Cell(40, 7, '', 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('USD'), 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('CUP'), 0, 1, 'R', false);
             * 
             */

            $marca_fpago = '';
            if ($data['ord']['fp_moneda'] == 'usd') {
                $marca_fpago = '.';
            }

            $pdf->Cell(40, 7, utf8_decode('Suma líneas'), 'B', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['xbase_format']) . ' USD', 'B', 0, 'R', false);
            $pdf->Cell(35, 7, utf8_decode($d['xbase_cup_format']) . ' CUP', 'B', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Transporte'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['xtransporte_format']) . $marca_fpago . ' USD', 'TB', 0, 'R', false); //PUNTO ANTES DE USD SI ES PAGO USD
            $pdf->Cell(35, 7, utf8_decode($d['xtransporte_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Servicio'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_format']) . ' USD', 'TB', 0, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            $pdf->Cell(35, 7, '', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Total'), 'T', 0, 'L', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximporte_format']) . ' USD', 'T', 0, 'R', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximporte_cup_format']) . ' CUP', 'T', 0, 'R', false);
        }

        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(145, 251);
        $pdf->Cell(60, 5, utf8_decode('*Pago desde el exterior'), 0, 0, 'L', false);

        $s = 'Consultar términos de garantía AllNovu en https://allnovu.com/garantia-de-los-equipos/';
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->setXY(20, 270);
        $pdf->Cell(170, 5, utf8_decode($s), 1, 0, 'C', false);
    }

    private function _order_footer($pdf) {
        $s = "Total...:    " . number_format($this->total, 2, ',', '') . ' USD';

        $pdf->SetFont('Arial', '', 8);
        //$pdf->setXY(140, 270);
        $pdf->Ln(7);
        $pdf->Cell(190, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    //############################ /DOCUMENTO PEDIDO
    //</editor-fold>
    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN DE DOCUMENTO FACTURA">
//############################ DOCUMENTO FACTURA
    private function _dl_fra($param) {
        $this->page = 1;

        $data = array(
            'id' => $param['id'],
            'items' => array(),
            'rev' => array(),
            'ord' => array(),
            'cli' => array()
        );

        $val = array(
            'rev' => $this->app->user_id
        );
        $sql = "select a.*"
                . " from ms_revendedores a"
                . " where a.xrevendedor_id=:rev";
        $data['rev'] = $this->app->db->fetchRow($sql, $val);

//print_r($data);
//die();

        $val = array(
            'ord' => $param['id'],
            'rev' => $this->app->user_id
        );
        $sql = "select a.*"
                . ",date_format(xdatealta,'%d/%m/%Y') as xfecha_format"
                . " from ms_pedidos a"
                . " where a.xhash=:ord and a.xrevendedor_id=:rev";
        $data['ord'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'rev' => $this->app->user_id
        );
        $sql = "select a.*"
                . " from ms_revendedores a"
                . " where a.xrevendedor_id=:rev";
        $data['cli'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'ord' => $data['ord']['xpedido_id'],
            'rev' => $this->app->user_id
        );
        $sql = "select a.*"
                . " from ms_pedidos_lin a"
                . " where a.xpedido_id=:ord and a.xrevendedor_id=:rev";

        $data['items'] = $this->app->db->fetchAll($sql, $val);

        foreach ($data['items'] as $k => $v) {
            $data['items'][$k]['xcantidad_format'] = number_format($v['xcantidad'], 2, ',', '');
            $data['items'][$k]['xprecio_format'] = number_format($v['xprecio'], 2, ',', '');
            $data['items'][$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
        }

//print_r($data);
//die();
//PARA DEMO RELLENA LINEAS PARA DEBUG
//for ($i = 1; $i < 60; $i++)
//    $data['items'][] = $data['items'][0];

        $h = 5;
        $c1 = 100;
        $c2 = 30;
        $c3 = 30;
        $c4 = 30;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_fra_header($pdf, $data);

//$this->_order_header_detalle($pdf, $param['id']);
        $pdf->SetFillColor(243, 243, 243);
        $this->total = 0;
        $this->seguro = 0;
        $this->transporte = $data['ord']['xtransporte'];
        $this->lin = 0;
        $max_lines_page = 33;
        foreach ($data['items'] as $k => $v) {
            $fill = false;
            if (($k + 1) % 2 == 0)
                $fill = true;
            $this->lin++;
            $pdf->Cell($c1, $h, utf8_decode($v['xarticulo_rev']), 'LR', 0, 'C', $fill);
            $pdf->Cell($c2, $h, $v['xcantidad_format'], 'LR', 0, 'C', $fill);
            $pdf->Cell($c3, $h, $v['xprecio_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c4, $h, $v['ximporte_format'], 'LR', 1, 'R', $fill);

            $this->total += $v['ximporte'];

            if (($k + 1) % $max_lines_page == 0) {
                $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c4, $h, '', 'LRB', 1, 'R', false);

                $this->lin = 0;
                $this->_fra_page($pdf);
                $this->_fra_footer_detalle($pdf);
                $pdf->AddPage();
                $this->_fra_header($pdf, $data);
            }
        }

        if ($this->lin < $max_lines_page) {
            for ($i = 1; $i < ($max_lines_page - $this->lin); $i++) {
                $pdf->Cell($c1, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c4, $h, '', 'LR', 1, 'R', false);
            }
            $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c4, $h, '', 'LRB', 1, 'R', false);
        }

        $this->_fra_page($pdf);
        $this->_fra_footer_detalle($pdf, 'F');

        $pdf->Output('factura-' . $data['ord']['xhash'] . '.pdf', 'I');
    }

    private function _fra_header($pdf, $data) {
        $h = 5;
        $c1 = 100;
        $c2 = 30;
        $c3 = 30;
        $c4 = 30;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->SetLeftMargin(15);

//https://mandasaldo.com/admin_mandasaldo_v2/img/rev/1.jpg
//$img_rev = "https://mandasaldo.com/admin_mandasaldo_v2/img/rev/{$data['rev']['xrevendedor_id']}.jpg";
//$pdf->Image($img_rev, 10, 5, 60, 0, 'JPG');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(15, 10);
        $pdf->Cell(80, 7, 'GROW SOLUTIONS LLC', 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(80, 4, "6303 BLUE LAGON DRIVE, SUITE 200", 0, 0, 'C', false);
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->Cell(110, 4, "INVOICE", 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(80, 4, "33126 MIAMI Tlf: +17863292467", 0, 1, 'C', false);

        $pdf->SetLeftMargin(10);

        $pdf->Rect(10, 30, 90, 50);
        $pdf->SetFont('Arial', '', 12);
        $pdf->setXY(10, 30);
        $pdf->Cell(90, 7, "Fecha: {$data['ord']['xfecha_format']}", 1, 1, 'C', false);
        $pdf->Cell(90, 7, utf8_decode("Invoice Nº: {$data['ord']['xhash']}"), 1, 1, 'C', false);

        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 10);
//$pdf->Cell(90, 7, "Datos del Adicionales", 0, 1, 'C', false);


        $pdf->Rect(105, 30, 95, 50);
        $pdf->setXY(105, 32);
        $pdf->SetLeftMargin(105);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 7, "Datos del Cliente", 0, 1, 'C', false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(90, 7, utf8_decode("{$data['cli']['xempresa']}"), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xdir1']}"), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode($data['cli']['xzipcode'] . ' - ' . $data['cli']['xcity'] . ' Tlf: ' . $data['cli']['xmovil']), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xnif']}"), 0, 1, 'C', false);
//$pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_zipcode']} {$data['ord']['xship_city']}"), 0, 1, 'C', false);
//$pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_provincia']} - CUBA"), 0, 1, 'C', false);
        $pdf->SetLeftMargin(10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 85);
        $pdf->Cell($c1, $h, 'Producto', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, 'Cantidad', 1, 0, 'C', false);
        $pdf->Cell($c3, $h, 'Precio', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Importe', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _fra_page($pdf) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->setXY(10, 270);
        $pdf->Cell(50, 4, utf8_decode("Página: {$this->page}"), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _fra_footer_detalle($pdf, $type = 'P') {
        $s = "Suma y sigue...:    " . number_format($this->total, 2, ',', '') . ' USD';
        if ($type == 'F') {
            $pdf->setXY(140, 255);
            $pdf->SetFont('Arial', 'B', 10);

            //IMPRIMIMOS BASE
            $s = "Base...:    " . number_format($this->total, 2, ',', '') . ' USD';
            $pdf->Cell(60, 5, utf8_decode($s), 0, 2, 'R', false);

            //IMPRIMIMOS SEGURO
            $s = "Seguro...:    " . number_format($this->seguro, 2, ',', '') . ' USD';
            $pdf->Cell(60, 5, utf8_decode($s), 0, 2, 'R', false);

            //IMPRIMIMOS TRANSPORTE
            $s = "Transporte...:    " . number_format($this->transporte, 2, ',', '') . ' USD';
            $pdf->Cell(60, 5, utf8_decode($s), 0, 0, 'R', false);

            $s = "Total...:    " . number_format($this->total + $this->transporte, 2, ',', '') . ' USD';
        }


        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setXY(140, 272);
        $pdf->Cell(60, 5, utf8_decode($s), 0, 0, 'R', false);
    }

//############################ /DOCUMENTO FACTURA
//</editor-fold>
    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN DE DOCUMENTO PEDIDO PARA PUNTO DE VENTA SOLO UNA MONEDA">
    //############################ DOCUMENTO PEDIDO
    private function _dl_order_pv($param) {
        $this->page = 1;

        $data = array(
            'id' => $param['id'],
            'items' => array(),
            'rev' => array(),
            'ord' => array(),
            'cli' => array()
        );

        $config = $this->app->get_configuraciones();

        $val = array(
            'ord' => $param['id'],
                //'rev' => $this->app->punto_venta
        );
        $sql = "select a.*,b.xusuario"
                . ",date_format(a.xdatealta,'%d/%m/%Y') as xfecha_format"
                . " from ms_pedidos a"
                . " left join ms_usuarios b on a.xuseralta_id=b.xusuario_id"
                //  . " where a.xpedido_id=:ord and a.xrevendedor_id=:rev";
                . " where a.xpedido_id=:ord";
        $data['ord'] = $this->app->db->fetchRow($sql, $val);

        $val = array(
            'rev' => $data['ord']['xrevendedor_id']
        );
        $sql = "select a.*"
                . " from ms_revendedores a"
                . " where a.xrevendedor_id=:rev";
        $data['rev'] = $this->app->db->fetchRow($sql, $val);

        //print_r($data['rev']);
        //print_r($data['ord']);
        //die();



        if (isset($data['ord'])) {
            $data['ord']['pagado_format'] = 'NO';
            //print($data['ord']['xpagado']);
            //die();
            if ($data['ord']['xpagado'] == 'S')
                $data['ord']['pagado_format'] = 'SI';
            $data['ord']['xbase_format'] = number_format($data['ord']['xbase'], 2, ',', '');
            $data['ord']['xbase_cup_format'] = number_format($data['ord']['xbase_cup'], 0, ',', '');
            $data['ord']['ximp_servicio_format'] = number_format($data['ord']['ximp_servicio'], 2, ',', '');
            $data['ord']['ximp_servicio_cup_format'] = number_format($data['ord']['ximp_servicio_cup'], 0, ',', '');
            $data['ord']['xtransporte_format'] = number_format($data['ord']['xtransporte'], 2, ',', ' ');
            $data['ord']['xtransporte_cup_format'] = number_format($data['ord']['xtransporte_cup'], 0, ',', '');
            $data['ord']['ximporte_format'] = number_format($data['ord']['ximporte'], 2, ',', '');
            $data['ord']['ximporte_cup_format'] = number_format($data['ord']['ximporte_cup'], 0, ',', '');

            //QR CODE
            require_once BASE_CLASS . '/phpqrcode/qrlib.php';
            $data['ord']['qrfile'] = PATH_TMP . "/{$data['ord']['xpedido_id']}.png";
            $content = "{$data['ord']['xhash']}";
            QRcode::png($content, $data['ord']['qrfile'], 'L', 3, 2);
        }

        $val = array(
            'cli' => $data['ord']['xcliente_id'],
        );
        $sql = "select a.*"
                . " from ms_clientes a"
                . " where a.xcliente_id=:cli";
        $data['cli'] = $this->app->db->fetchRow($sql, $val);

        //************************
        //FORMA DE PAGO DEL PEDIDO
        $fpagos = json_decode(FORMAS_PAGO_PV_JSON, true);
        $data['ord']['fp_moneda'] = $fpagos[$data['ord']['xfpago']]['moneda'];
        //************************

        $val = array(
            'ord' => $data['ord']['xpedido_id']
        );
        $sql = "select a.*"
                . " from ms_pedidos_lin a"
                . " where a.xpedido_id=:ord"
                . " order by a.xlin_id";
        //print_r($val);
        //print($sql);
        //die();
        $data['items'] = $this->app->db->fetchAll($sql, $val);

        foreach ($data['items'] as $k => $v) {
            $data['items'][$k]['xcantidad_format'] = number_format($v['xcantidad'], 0, ',', '');
            $data['items'][$k]['xprecio_format'] = number_format($v['xprecio'], 2, ',', '');
            $data['items'][$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            $data['items'][$k]['xprecio_cup_format'] = number_format($v['xprecio_cup'], 0, ',', '');
            $data['items'][$k]['ximporte_cup_format'] = number_format($v['ximporte_cup'], 0, ',', '');

            $data['items'][$k]['xprecio_moneda'] = 0;
            $data['items'][$k]['ximporte_moneda'] = 0;

            //usd
            if ($data['ord']['fp_moneda'] == 'usd') {
                $data['items'][$k]['xprecio_moneda'] = $v['xprecio'];
                $data['items'][$k]['ximporte_moneda'] = $v['ximporte'];
            }

            //cup
            if ($data['ord']['fp_moneda'] == 'cup') {
                $data['items'][$k]['xprecio_moneda'] = $v['xprecio_cup'];
                $data['items'][$k]['ximporte_moneda'] = $v['ximporte_cup'];
            }

            //mlc
            if ($data['ord']['fp_moneda'] == 'mlc') {
                $data['items'][$k]['xprecio_moneda'] = $v['xprecio_mlc'];
                $data['items'][$k]['ximporte_moneda'] = $v['ximporte_mlc'];
            }

            //otro
            if ($data['ord']['fp_moneda'] == 'otro') {
                $data['items'][$k]['xprecio_moneda'] = $v['xprecio_otro'];
                $data['items'][$k]['ximporte_moneda'] = $v['ximporte_otro'];
            }

            $data['items'][$k]['xprecio_moneda_format'] = number_format($data['items'][$k]['xprecio_moneda'], 2, ',', '');
            $data['items'][$k]['ximporte_moneda_format'] = number_format($data['items'][$k]['ximporte_moneda'], 2, ',', '');
        }

        $data['ord']['xbase_moneda'] = 0;
        $data['ord']['ximporte_moneda'] = 0;
        $data['ord']['xtransporte_moneda'] = 0;

        //usd
        if ($data['ord']['fp_moneda'] == 'usd') {
            $data['ord']['xbase_moneda'] = $data['ord']['xbase'];
            $data['ord']['xtransporte_moneda'] = $data['ord']['xtransporte'];
            $data['ord']['ximporte_moneda'] = $data['ord']['ximporte'];
        }

        //cup
        if ($data['ord']['fp_moneda'] == 'cup') {
            $data['ord']['xbase_moneda'] = $data['ord']['xbase_cup'];
            $data['ord']['xtransporte_moneda'] = $data['ord']['xtransporte_cup'];
            $data['ord']['ximporte_moneda'] = $data['ord']['ximporte_cup'];
        }

        //mlc
        if ($data['ord']['fp_moneda'] == 'mlc') {
            $data['ord']['xbase_moneda'] = $data['ord']['xbase_mlc'];
            $data['ord']['xtransporte_moneda'] = $data['ord']['xtransporte_mlc'];
            $data['ord']['ximporte_moneda'] = $data['ord']['ximporte_mlc'];
        }

        //otro
        if ($data['ord']['fp_moneda'] == 'otro') {
            $data['ord']['xbase_moneda'] = $data['ord']['xbase_otro'];
            $data['ord']['xtransporte_moneda'] = $data['ord']['xtransporte_otro'];
            $data['ord']['ximporte_moneda'] = $data['ord']['ximporte_otro'];
        }

        $data['ord']['xbase_moneda_format'] = number_format($data['ord']['xbase_moneda'], 2, ',', '');
        $data['ord']['xtransporte_moneda_format'] = number_format($data['ord']['xtransporte_moneda'], 2, ',', '');
        $data['ord']['ximporte_moneda_format'] = number_format($data['ord']['ximporte_moneda'], 2, ',', '');

        //print_r($data);
        //die();
        //PARA DEMO RELLENA LINEAS PARA DEBUG **** NO ELIMINAR ****
        //for ($i = 1; $i < 60; $i++)
        //    $data['items'][] = $data['items'][0];

        $h = 5;
        $c1 = 129;
        $c2 = 17;
        $c3 = 21;
        $c4 = 23;
        //$c5 = 23;
        //$c6 = 23;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_order_header_pv($pdf, $data);

        //$this->_order_header_detalle($pdf, $param['id']);
        $pdf->SetFillColor(243, 243, 243);
        $this->total = 0;
        $this->lin = 0;
        $max_lines_page = 20;
        foreach ($data['items'] as $k => $v) {
            $fill = false;
            if (($k + 1) % 2 == 0)
                $fill = true;
            $this->lin++;

            $pdf->Cell($c1, $h, utf8_decode($v['xarticulo']), 'LR', 0, 'L', $fill);
            $pdf->Cell($c2, $h, $v['xcantidad_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c3, $h, $v['xprecio_moneda_format'], 'LR', 0, 'R', $fill);
            //$pdf->Cell($c4, $h, $v['xprecio_cup_format'], 'LR', 0, 'R', $fill);
            $pdf->Cell($c4, $h, $v['ximporte_moneda_format'], 'LR', 1, 'R', $fill);
            //$pdf->Cell($c6, $h, $v['ximporte_cup_format'], 'LR', 1, 'R', $fill);

            $this->total += $v['ximporte_moneda'];

            if (($k + 1) % $max_lines_page == 0) {
                $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
                $pdf->Cell($c4, $h, '', 'LRB', 1, 'R', false);
                //$pdf->Cell($c5, $h, '', 'LRB', 0, 'R', false);
                //$pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);

                $this->lin = 0;
                $this->_order_page_pv($pdf);
                $this->_order_footer_detalle_pv($pdf, 'P', $data);
                $pdf->AddPage();
                $this->_order_header_pv($pdf, $data);
            }
        }

        if ($this->lin < $max_lines_page) {
            for ($i = 1; $i < (($max_lines_page + 1) - $this->lin); $i++) {
                $pdf->Cell($c1, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c2, $h, '', 'LR', 0, 'C', false);
                $pdf->Cell($c3, $h, '', 'LR', 0, 'R', false);
                $pdf->Cell($c4, $h, '', 'LR', 1, 'R', false);
                //$pdf->Cell($c5, $h, '', 'LR', 0, 'R', false);
                //$pdf->Cell($c6, $h, '', 'LR', 1, 'R', false);
            }
            $pdf->Cell($c1, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c2, $h, '', 'LRB', 0, 'C', false);
            $pdf->Cell($c3, $h, '', 'LRB', 0, 'R', false);
            $pdf->Cell($c4, $h, '', 'LRB', 1, 'R', false);
            //$pdf->Cell($c5, $h, '', 'LRB', 0, 'R', false);
            //$pdf->Cell($c6, $h, '', 'LRB', 1, 'R', false);
        }

        //$this->transporte = ($this->total < $config['xtransporte_free']) ? $data['ord']['xtransporte'] : 0;
        //$this->seguro = $data['ord']['xseguro'];
        //$this->servicio = $data['ord']['ximp_servicio'];

        $this->_order_page_pv($pdf, $data);
        $this->_order_footer_detalle_pv($pdf, 'F', $data);

        $pdf->Output('pedido-' . $data['ord']['xhash'] . '.pdf', 'I');
    }

    private function _order_header_pv($pdf, $data) {
        $h = 5;
        $c1 = 129;
        $c2 = 17;
        $c3 = 21;
        $c4 = 23;
        //$c5 = 23;
        //$c6 = 23;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        //https://mandasaldo.com/admin_mandasaldo_v2/img/rev/1.jpg

        if ($data['rev']['xrevendedor_id'] != '0' && $data['rev']['xrevendedor_id'] != '') {
            $img_rev = "https://mandasaldo.com/admin_mandasaldo_v2/img/rev/{$data['rev']['xrevendedor_id']}.jpg";
            //if (file_exists($img_rev)) {
            $pdf->Image($img_rev, 15, 5, 80, 0, 'JPG');
        }

        $pdf->SetLeftMargin(90);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(90, 10);
        $pdf->Cell(120, 7, utf8_decode(mb_strtoupper($data['rev']['xempresa'])), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xdir1'])), 0, 1, 'C', false);
        $pdf->Cell(120, 4, utf8_decode(mb_strtoupper($data['rev']['xzipcode']) . ' ' . mb_strtoupper($data['rev']['xcity']) . ' Tlf:' . $data['rev']['xmovil']), 0, 1, 'C', false);

        $pdf->SetLeftMargin(10);

        $pdf->Rect(10, 30, 90, 50);
        $pdf->SetFont('Arial', '', 12);
        $pdf->setXY(10, 30);
        $pdf->Cell(90, 7, "Fecha: {$data['ord']['xfecha_format']}", 1, 1, 'L', false);
        $pdf->Cell(90, 6, "Pedido:", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "{$data['ord']['xhash']}", 0, 1, 'L', false);
        $pdf->Cell(90, 5, "", 0, 1, 'L', false);
        $pdf->Ln(15);
        $pdf->Cell(90, 5, "PAGADO: {$data['ord']['pagado_format']}", 0, 1, 'L', false);
        $pdf->Cell(90, 7, "{$data['ord']['xusuario']}", 'T', 1, 'L', false);
        //$pdf->Cell(90, 7, "{$data['ord']['xuseralta_id']}", 'T', 1, 'L', false);

        if (isset($data['ord']['qrfile']) && $data['ord']['qrfile'] != '') {
            $pdf->Image($data['ord']['qrfile'], 60, $pdf->GetY() - 55 + 15, 30, 0, 'PNG');
        }

        /*
         * //DATOS DEL REMITENTE
          $pdf->Ln(2);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 7, "Datos del Remitente", 0, 1, 'C', false);
          $pdf->Ln(2);
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xnombre']} {$data['cli']['xnombre2']} {$data['cli']['xapellido1']} {$data['cli']['xapellido2']}"), 0, 1, 'C', false);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(90, 5, utf8_decode("{$data['cli']['xemail']}"), 0, 1, 'C', false);
          $pdf->Cell(90, 5, utf8_decode("+{$data['cli']['xprefijo']} {$data['cli']['xmovil']}"), 0, 1, 'C', false);
         * 
         */

        $pdf->Rect(105, 30, 95, 50);
        $pdf->setXY(105, 32);
        $pdf->SetLeftMargin(105);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 7, "Datos del Destinatario", 0, 1, 'C', false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 12);
        //$texto = mb_convert_encoding($data['ord']['xship_apellido1'], 'ISO-8859-1', 'UTF-8');
        //$texto = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data['ord']['xship_apellido1']);
        $pdf->Cell(90, 7, utf8_decode("{$data['ord']['xship_name']} {$data['ord']['xship_name2']} {$data['ord']['xship_apellido1']} {$data['ord']['xship_apellido2']}"), 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_dir1']}" . (($data['ord']['xship_numero'] != '') ? " Nº {$data['ord']['xship_numero']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_apartamento'] != '') ? " Apt. {$data['ord']['xship_apartamento']}" : '') . (($data['ord']['xship_piso'] != '') ? " Piso {$data['ord']['xship_piso']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode((($data['ord']['xship_entre_calle1'] != '') ? " Entre {$data['ord']['xship_entre_calle1']}" : '') . (($data['ord']['xship_entre_calle2'] != '') ? " y {$data['ord']['xship_entre_calle2']}" : '')), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_zipcode']} {$data['ord']['xship_city']}"), 0, 1, 'C', false);
        $pdf->Cell(90, 5, utf8_decode("{$data['ord']['xship_provincia']} - CUBA"), 0, 1, 'C', false);
        $pdf->SetLeftMargin(10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 85);
        $pdf->Cell($c1, $h, 'Producto', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c3, $h, 'Precio', 1, 0, 'R', false);
        //$pdf->Cell($c4, $h, 'Precio CUP', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Importe', 1, 1, 'R', false);
        //$pdf->Cell($c6, $h, 'Importe CUP', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_header_detalle_pv($pdf, $corte) {
        $h = 5;
        $c1 = 50;
        $c2 = 50;
        $c3 = 30;
        $c4 = 30;
        $c5 = 30;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->SetLeftMargin(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(10, 10);
        $pdf->Cell(190, 7, "Detalle Corte {$corte}", 0, 1, 'C', false);

        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 20);
        $pdf->Cell($c1, $h, 'Tracking', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, utf8_decode('Artículo'), 1, 0, 'C', false);
        $pdf->Cell($c3, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Precio', 1, 0, 'R', false);
        $pdf->Cell($c5, $h, 'Importe', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _order_page_pv($pdf) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->setXY(10, 280);
        $pdf->Cell(50, 4, utf8_decode("Página: {$this->page}"), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _order_footer_detalle_pv($pdf, $type = 'P', $data) {
        //VALORES DE $TYPE
        //P - PAGINA
        //F - FINAL
        if ($type == 'F') {
            //$pdf->setXY(100, 220);
            $pdf->setY(225);
            $pdf->SetLeftMargin(95);
            $pdf->SetFont('Arial', 'B', 10);

            $d = $data['ord'];

            /*
              $pdf->Cell(40, 7, '', 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('USD'), 0, 0, 'R', false);
              $pdf->Cell(35, 7, utf8_decode('CUP'), 0, 1, 'R', false);
             * 
             */

            $marca_fpago = '';
            if ($data['ord']['fp_moneda'] == 'usd') {
                $marca_fpago = '.';
            }

            $pdf->Cell(40, 7, utf8_decode('Suma líneas'), 'B', 0, 'L', false);
            $pdf->Cell(35, 7, '', 'B', 0, 'R', false);
            $pdf->Cell(35, 7, utf8_decode($d['xbase_moneda_format']), 'B', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Transporte'), 'TB', 0, 'L', false);
            $pdf->Cell(35, 7, '', 'TB', 0, 'R', false); //PUNTO ANTES DE USD SI ES PAGO USD
            $pdf->Cell(35, 7, utf8_decode($d['xtransporte_moneda_format']), 'TB', 1, 'R', false);
            //$pdf->Cell(40, 7, utf8_decode('Servicio'), 'TB', 0, 'L', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_format']) . ' USD', 'TB', 0, 'R', false);
            //$pdf->Cell(35, 7, utf8_decode($d['ximp_servicio_cup_format']) . ' CUP', 'TB', 1, 'R', false);
            //$pdf->Cell(35, 7, '', 'TB', 1, 'R', false);
            $pdf->Cell(40, 7, utf8_decode('Total'), 'T', 0, 'L', false);
            $pdf->Cell(35, 7, '', 'T', 0, 'R', false);
            $pdf->Cell(35, 7, utf8_decode($d['ximporte_moneda_format']), 'T', 1, 'R', false);

            $moneda = $data['ord']['fp_moneda'];

            if ($moneda == 'cup') {
                $moneda = strtoupper($moneda);
            }
            if ($moneda == 'usd') {
                $moneda = strtoupper($moneda) . ' (Pago en el exterior)';
            }
            if ($moneda == 'mlc') {
                $moneda = strtoupper($moneda) . ' (T pago en el exterior)';
            }
            if ($moneda == 'otro') {
                $moneda = strtoupper($moneda) . ' (Pago en el exterior)';
            }

            $pdf->Cell(40, 7, utf8_decode('Moneda'), 'T', 0, 'L', false);
            $pdf->Cell(35, 7, '', 'T', 0, 'R', false);
            $pdf->Cell(35, 7, utf8_decode($moneda), 'T', 0, 'R', false);
        }

        /*
          $pdf->SetFont('Arial', '', 6);
          $pdf->setXY(145, 251);
          $pdf->Cell(60, 5, utf8_decode('*Pago desde el exterior'), 0, 0, 'L', false);
         * 
         */

        $s = 'ACEPTACIÓN DE LA GARANTÍA: No reciba equipos de la marca AllNovu si antes no ha leído y está conforme con los términos de la garantía . EL PAGO DE ESTA FACTURA es la conformidad de que el cliente afirma que ha leído y acepta los términos de la garantía de la marca en la página de allnovu.com y está conforme con el producto recibido.';
        $pdf->SetFont('Arial', '', 8);
        $pdf->setXY(20, 196);
        $pdf->MultiCell(170, 3, utf8_decode($s), 0, 'L', false);

        $s = 'Consultar términos de garantía AllNovu en https://allnovu.com/garantia-de-los-equipos/';
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->setXY(20, 265);
        $pdf->Cell(170, 5, utf8_decode($s), 1, 0, 'C', false);
    }

    private function _order_footer_pv($pdf) {
        $s = "Total...:    " . number_format($this->total, 2, ',', '') . ' USD';

        $pdf->SetFont('Arial', '', 8);
        //$pdf->setXY(140, 270);
        $pdf->Ln(7);
        $pdf->Cell(190, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    //############################ /DOCUMENTO PEDIDO PARA PUNTO DE VENTA SOLO UNA MONEDA
    //</editor-fold>
}
