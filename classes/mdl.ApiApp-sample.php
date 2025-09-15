<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class ApiApp {

    var $app;
    var $page;

    //var $db;
    //var $action;

    public function __construct() {
        //define('BASE_ADMIN', BASE);
        //define('INCLUDES', BASE . '/includes');
        //define('BASE_CLASS', BASE . '/classes');
        //require(BASE_CLASS . '/Sql.class.php');
    }

    public function api($param) {
        switch ($param['method']) {
            case 'fecha-declaracion':
                $data = $this->_fechaDeclaracion($param);
                print(json_encode($data));
                break;
            case 'check-contrato':
                $data = $this->_checkContrato($param);
                print(json_encode($data));
                break;
            case 'insert-compras':
                $data = $this->_insertDocCompras($param);
                print(json_encode($data));
                break;
            case 'update-compras':
                $data = $this->_updateDocComprasFromExpertis($param);
                print(json_encode($data));
                break;
            case 'get-empleados':
                $data = $this->_get_list_empleados($param);
                print(json_encode($data));
                break;
            case 'traspasar-pedido-expertis':
                $data = $this->_create_order_expertis($param);
                print(json_encode($data));
                break;
            case 'update-pedido':
                $data = $this->_updateDocFromExpertis($param);
                print(json_encode($data));
                break;
            case 'validar-pedido-cliente':
                $data = $this->_validar_pedido_cliente($param);
                print(json_encode($data));
                break;
            case 'indexar-doc':
                $data = $this->_indexar_doc($param);
                print(json_encode($data));
                break;
            case 'test':
                $data = $this->_test($param);
                print(json_encode($data));
                break;
            case 'move-doc':
                $data = $this->_move_doc($param);
                print(json_encode($data));
                break;
        }
    }

    private function _move_doc($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Documento movido de archivador'
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();


        if ($res['status'] == 200) {

            //$res=$dw->dw_org();
            //$res=$dw->dw_org();
            $res = $dw->dw_get_all_index(DW_FC_PROVEEDORES, $param['docid']);

            $fields = $res['response'];
            //print(json_encode($indexes));
            //die();

            $res = $dw->dw_download_document(DW_FC_PROVEEDORES, $param['docid'], '.pdf', 'pdf');
            if ($res['status'] == 200) {
                $docfile = FILES . "/{$res['doc']}";
                file_put_contents($docfile, base64_decode($res['doc_base64']));
                unset($res['doc_base64']);

                //print(json_encode($d));
                //die();
                //$res = $dw->dw_add_doc_basket(DW_B_PEDIDOS, $fields, $docfile);
                $res = $dw->dw_add_doc_basket(DW_B_FACTURAS_PEDIDOS, $fields, $docfile);
            }

            //print(json_encode($res));
            //print(json_encode($indexes));
            //die();
        }

        return $data;
    }

    private function _get_lineas_doc($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Documento indexado correctamente'
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();


        if ($res['status'] == 200) {


            //$res=$dw->dw_org();
            $res = $dw->dw_get_all_index(DW_FC_PROVEEDORES, $param['docid']);

            //print_r($res);
            $fields = $res['response'];
            $tipo = $dw->getValue($fields['Field'], 'TIPO_DOCUMENTO');
            if ($tipo == 'PEDIDO COMPRA')
                $ndoc = $dw->getValue($fields['Field'], 'NO_DE_DOCUMENTO');
            if ($tipo == 'ALBARÁN COMPRA')
                $ndoc = $dw->getValue($fields['Field'], 'NODOCUMENTO_INTERNO');

            //print(json_encode($fields));
            //print($ord);
            //print($tipo);
            //die();

            $exp = new ExpertisApi(EX_URL);

            $res = $exp->loginEXP(EX_USER, EX_PASS);

            //print(json_encode($res));
            //print(json_encode($indexes));
            //die();

            if ($res['status'] == 200) {
                if ($tipo == 'PEDIDO COMPRA') {
                    $res = $exp->getIndexesOrder($ndoc);
                    //print_r($res);
                    //die();
                    if (isset($res['status']) && $res['status'] == 200) {

                        $cab = $res['response']['result']['tbPedidoCompraCabecera'][0];
                        $lin = $res['response']['result']['tbPedidoCompraLinea'];

                        //print_r($cab);
                        //die();

                        $fields = array();
                        $fields['Field'][] = array(
                            "FieldName" => 'CIF',
                            "Item" => $cab['cifProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'NOMBRE_PROVEEDOR',
                            "Item" => $cab['descProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'IMPORTE_TOTAL',
                            "Item" => $cab['impTotal']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'BASE_1',
                            "Item" => $cab['baseImponible']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'COD__PROVEEDOR',
                            "Item" => $cab['idProveedor']
                        );

                        $fecha = '';
                        if ($cab['fechaPedido'] != '') {
                            $fecha = explode('T', $cab['fechaPedido'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $cab['fechaPedido']
                            );
                        }

                        /*
                          $fields['Field'][] = array(
                          "FieldName" => 'ESTADO',
                          "Item" => 'NUEVO'
                          );
                         * 
                         */

                        $rows = array();
                        foreach ($lin as $k => $v) {
                            $row = array(
                                'ColumnValue' => array()
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_NO_ORDEN",
                                "Item" => $ndoc,
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ARTICULO",
                                "Item" => $v['idArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESCRIPCION",
                                "Item" => $v['descArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $v['qPedida'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_PRECIO",
                                "Item" => $v['precio'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_IMPORTE",
                                "Item" => $v['importe'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD_SERVIDA",
                                "Item" => 0,
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CUENTA_CONTABLE",
                                "Item" => $v['cContable'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CENTRO_DE_GESTION",
                                "Item" => $v['idCentroGestion'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ESTADO",
                                "Item" => 'NO PROCESADO',
                                "ItemElementName" => "String"
                            );
                            $rows[] = $row;
                        }

                        $fields['Field'][] = array(
                            "FieldName" => 'LINEAS',
                            "Item" => array(
                                '$type' => "DocumentIndexFieldTable",
                                "Row" => $rows
                            )
                        );

                        $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);

                        //print(json_encode($res));
                        //die();
                    }
                }
                if ($tipo == 'ALBARÁN COMPRA') {
                    $res = $exp->getIndexesDeliveryNote($ndoc);
                    //print($ndoc);
                    //print(json_encode($res));
                    //die();

                    if (isset($res['status']) && $res['status'] == 200) {

                        $cab = $res['response']['result']['tbAlbaranCompraCabecera'][0];
                        $lin = $res['response']['result']['tbAlbaranCompraLinea'];

                        //print_r($cab);
                        //print_r($lin);
                        //die();

                        $fields = array();
                        $fields['Field'][] = array(
                            "FieldName" => 'NO_DE_DOCUMENTO',
                            "Item" => $cab['suAlbaran']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'CIF',
                            "Item" => $cab['cifProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'NOMBRE_PROVEEDOR',
                            "Item" => $cab['descProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'IMPORTE_TOTAL',
                            "Item" => $cab['impTotal']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'BASE_1',
                            "Item" => $cab['baseImponible']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'CUOTA_IVA_1',
                            "Item" => $cab['impIva']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'COD__PROVEEDOR',
                            "Item" => $cab['idProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'DOCID_EXPERTIS',
                            "Item" => $cab['idAlbaran']
                        );

                        $fecha = '';
                        if ($cab['fechaAlbaran'] != '') {
                            $fecha = explode('T', $cab['fechaAlbaran'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $cab['fechaAlbaran']
                            );
                        }

                        /*
                          $fields['Field'][] = array(
                          "FieldName" => 'ESTADO',
                          "Item" => 'NUEVO'
                          );
                         * 
                         */

                        $rows = array();
                        foreach ($lin as $k => $v) {
                            $row = array(
                                'ColumnValue' => array()
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_NO_ORDEN",
                                "Item" => $v['nPedido'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_NODOCUMENTO",
                                "Item" => $v['nPedido'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ARTICULO",
                                "Item" => $v['idArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_SU_REFERENCIA",
                                "Item" => $v['refProveedor'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESCRIPCION",
                                "Item" => $v['descArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $v['qServida'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_PRECIO",
                                "Item" => $v['precio'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_IMPORTE",
                                "Item" => $v['importe'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD_SERVIDA",
                                "Item" => 0,
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CUENTA_CONTABLE",
                                "Item" => $v['cContable'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CENTRO_DE_GESTION",
                                "Item" => $v['idCentroGestion'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DOCID_LIN_EXPERTIS",
                                "Item" => $v['idLineaAlbaran'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ESTADO",
                                "Item" => 'NO PROCESADO',
                                "ItemElementName" => "String"
                            );
                            $rows[] = $row;
                        }

                        $fields['Field'][] = array(
                            "FieldName" => 'LINEAS',
                            "Item" => array(
                                '$type' => "DocumentIndexFieldTable",
                                "Row" => $rows
                            )
                        );

                        //print_r($fields);
                        //die();

                        $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);

                        //print(json_encode($res));
                        //die();
                    }
                }
                if ($tipo == 'FACTURA PEDIDO') {
                    $rows = $dw->getTable($fields['Field'], 'LINEAS');
                    //print(json_encode($rows));
                    //die();
                    //$res = $exp->getIndexesDeliveryNote($ndoc);
                    //print($ndoc);
                    //print(json_encode($res));
                    //die();
                    $albs = array();
                    foreach ($rows as $kl => $vl) {
                        //LINEA_NODOCUMENTO
                        //LINEA_IMPORTE
                        //LINEA_IMPORTE_TOTAL_DOC
                        //print($dw->getValue($vl['ColumnValue'], 'LINEA_IMPORTE'));
                        //die();

                        $alb = $dw->getValue($vl['ColumnValue'], 'LINEA_NODOCUMENTO');
                        $imp = 1 * (($dw->getValue($vl['ColumnValue'], 'LINEA_IMPORTE') != '') ? $dw->getValue($vl['ColumnValue'], 'LINEA_IMPORTE') : 0);
                        //print("$alb .. $imp");
                        //die();

                        if (!isset($albs[$alb])) {
                            $albs[$alb] = $imp;
                        } else {
                            $albs[$alb] += $imp;
                        }

                        //print(json_encode($vl));
                        //die();
                    }

                    foreach ($rows as $kl => $vl) {
                        $alb = $dw->getValue($vl['ColumnValue'], 'LINEA_NODOCUMENTO');
                        if (isset($albs[$alb])) {
                            $rows[$kl]['ColumnValue'] = $dw->setValue($vl['ColumnValue'], 'LINEA_IMPORTE_TOTAL_DOC', $albs[$alb]);
                        }
                    }


                    //print(json_encode($albs));
                    //print(json_encode($rows));
                    //die();

                    $fields = array();
                    $fields['Field'][] = array(
                        "FieldName" => 'LINEAS',
                        "Item" => array(
                            '$type' => "DocumentIndexFieldTable",
                            "Row" => $rows
                        )
                    );

                    $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);
                }
            }
        }

        return $data;
    }

    private function _indexar_doc($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Documento indexado correctamente'
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();


        if ($res['status'] == 200) {


            //$res=$dw->dw_org();
            $res = $dw->dw_get_all_index(DW_FC_PROVEEDORES, $param['docid']);

            //print_r($res);
            $fields = $res['response'];
            $tipo = $dw->getValue($fields['Field'], 'TIPO_DOCUMENTO');
            if ($tipo == 'PEDIDO COMPRA')
                $ndoc = $dw->getValue($fields['Field'], 'NO_DE_DOCUMENTO');
            if ($tipo == 'ALBARÁN COMPRA')
                $ndoc = $dw->getValue($fields['Field'], 'NODOCUMENTO_INTERNO');

            //print(json_encode($fields));
            //print($ndoc);
            //print($tipo);
            //die();

            $exp = new ExpertisApi(EX_URL);

            $res = $exp->loginEXP(EX_USER, EX_PASS);

            //print(json_encode($res));
            //print(json_encode($indexes));
            //die();

            if ($res['status'] == 200) {
                if ($tipo == 'PEDIDO COMPRA') {
                    $res = $exp->getIndexesOrder($ndoc);
                    //print_r($res);
                    //die();
                    if (isset($res['status']) && $res['status'] == 200) {

                        $cab = $res['response']['result']['tbPedidoCompraCabecera'][0];
                        $lin = $res['response']['result']['tbPedidoCompraLinea'];

                        //print_r($cab);
                        //die();

                        $fields = array();
                        $fields['Field'][] = array(
                            "FieldName" => 'CIF',
                            "Item" => $cab['cifProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'NOMBRE_PROVEEDOR',
                            "Item" => $cab['descProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'IMPORTE_TOTAL',
                            "Item" => $cab['impTotal']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'BASE_1',
                            "Item" => $cab['baseImponible']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'COD__PROVEEDOR',
                            "Item" => $cab['idProveedor']
                        );

                        $fecha = '';
                        if ($cab['fechaPedido'] != '') {
                            $fecha = explode('T', $cab['fechaPedido'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $cab['fechaPedido']
                            );
                        }

                        /*
                          $fields['Field'][] = array(
                          "FieldName" => 'ESTADO',
                          "Item" => 'NUEVO'
                          );
                         * 
                         */

                        $rows = array();
                        foreach ($lin as $k => $v) {
                            $row = array(
                                'ColumnValue' => array()
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_NO_ORDEN",
                                "Item" => $ndoc,
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ARTICULO",
                                "Item" => $v['idArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESCRIPCION",
                                "Item" => $v['descArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $v['qPedida'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_PRECIO",
                                "Item" => $v['precio'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_IMPORTE",
                                "Item" => $v['importe'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD_SERVIDA",
                                "Item" => 0,
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CUENTA_CONTABLE",
                                "Item" => $v['cContable'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CENTRO_DE_GESTION",
                                "Item" => $v['idCentroGestion'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ESTADO",
                                "Item" => 'NO PROCESADO',
                                //"Item" => strtoupper($v['']),
                                "ItemElementName" => "String"
                            );
                            $rows[] = $row;
                        }

                        $fields['Field'][] = array(
                            "FieldName" => 'LINEAS',
                            "Item" => array(
                                '$type' => "DocumentIndexFieldTable",
                                "Row" => $rows
                            )
                        );

                        $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);

                        //print(json_encode($res));
                        //die();
                    }
                }
                if ($tipo == 'ALBARÁN COMPRA') {
                    $res = $exp->getIndexesDeliveryNote($ndoc);
                    //print($ndoc);
                    if (isset($param['debug']) && $param['debug'] == 'expertis') {
                        print(json_encode($res));
                        die();
                    }

                    if (isset($res['status']) && $res['status'] == 200 && isset($res['response']['result']['tbAlbaranCompraCabecera'])) {

                        $cab = $res['response']['result']['tbAlbaranCompraCabecera'][0];
                        $lin = $res['response']['result']['tbAlbaranCompraLinea'];

                        //print_r($cab);
                        //print_r($lin);
                        //die();

                        $fields = array();
                        $fields['Field'][] = array(
                            "FieldName" => 'NO_DE_DOCUMENTO',
                            "Item" => $cab['suAlbaran']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'CIF',
                            "Item" => $cab['cifProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'NOMBRE_PROVEEDOR',
                            "Item" => $cab['descProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'IMPORTE_TOTAL',
                            "Item" => $cab['impTotal']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'BASE_1',
                            "Item" => $cab['baseImponible']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'CUOTA_IVA_1',
                            "Item" => $cab['impIva']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'COD__PROVEEDOR',
                            "Item" => $cab['idProveedor']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'DOCID_EXPERTIS',
                            "Item" => $cab['idAlbaran']
                        );

                        $fecha = '';
                        if ($cab['fechaAlbaran'] != '') {
                            $fecha = explode('T', $cab['fechaAlbaran'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $cab['fechaAlbaran']
                            );
                        }

                        /*
                          $fields['Field'][] = array(
                          "FieldName" => 'ESTADO',
                          "Item" => 'NUEVO'
                          );
                         * 
                         */

                        $rows = array();
                        foreach ($lin as $k => $v) {
                            $row = array(
                                'ColumnValue' => array()
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_NO_ORDEN",
                                "Item" => $v['nPedido'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_NODOCUMENTO",
                                "Item" => $v['nPedido'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ARTICULO",
                                "Item" => $v['idArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_SU_REFERENCIA",
                                "Item" => $v['refProveedor'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESCRIPCION",
                                "Item" => $v['descArticulo'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $v['qServida'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_PRECIO",
                                "Item" => $v['precio'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_IMPORTE",
                                "Item" => $v['importe'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD_SERVIDA",
                                "Item" => 0,
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CUENTA_CONTABLE",
                                "Item" => $v['cContable'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CENTRO_DE_GESTION",
                                "Item" => $v['idCentroGestion'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DOCID_EXPERTIS",
                                "Item" => $cab['idAlbaran'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DOCID_LIN_EXPERTIS",
                                "Item" => $v['idLineaAlbaran'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ESTADO",
                                "Item" => 'NO PROCESADO',
                                "ItemElementName" => "String"
                            );
                            $rows[] = $row;
                        }

                        $fields['Field'][] = array(
                            "FieldName" => 'LINEAS',
                            "Item" => array(
                                '$type' => "DocumentIndexFieldTable",
                                "Row" => $rows
                            )
                        );

                        //print_r($fields);
                        //die();

                        $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);

                        //print(json_encode($res));
                        //die();
                    }
                }
                if ($tipo == 'FACTURA PEDIDO') {
                    $rows = $dw->getTable($fields['Field'], 'LINEAS');

                    //print(json_encode($rows));
                    //die();
                    //$res = $exp->getIndexesDeliveryNote($ndoc);
                    //print($ndoc);
                    //print(json_encode($res));
                    //die();
                    //RECOGEMOS LAS LÍNEAS DEL ALBARÁN
                    $rows_fra = array();
                    foreach ($rows as $kl => $vl) {
                        //BUSCAMOS LÍNEAS DE ALBARAN Y SUSTITUIMOS
                        $alb = $dw->getValue($vl['ColumnValue'], 'LINEA_NODOCUMENTO');
                        if ($alb != '') {
                            $param_cond = array(
                                'Operation' => 'And',
                                'Condition' => array(
                                    array(
                                        'DBName' => 'NO_DE_DOCUMENTO',
                                        'Value' => array($alb)
                                    )
                                )
                            );
                            //print_r($param_cond);
                            //die();

                            $res_query = $dw->dwGetDialogExpression(DW_FC_PROVEEDORES, DW_SD_PROVEEDORES, $param_cond);
                            //print_r($res_query);
                            //die();

                            $items = array();
                            $dwalb = 0;
                            if ($res_query['status'] == 1) {
                                $response = $dw->dw_get_all_docs_query($res_query['response'], 5);
                                //print_r($response);
                                //die();
                                if ($response['status'] = 200)
                                    $items = $response['response']['Items'];
                                //print_r($items);
                                //die();

                                if (count($items) > 0) {
                                    $doctmp = $items[0]['Fields'];
                                    $dwalb = $dw->getValue($doctmp, 'DWDOCID');
                                    if ($dwalb > 0) {
                                        $doctmp = $dw->dw_get_all_index(DW_FC_PROVEEDORES, $dwalb);
                                        if (isset($doctmp['response']['Field'])) {
                                            $doctmp = $doctmp['response']['Field'];
                                        }
                                    }
                                    $lineastmp = $dw->getTable($doctmp, 'LINEAS');
                                    if (count($lineastmp) > 0) {
                                        foreach ($lineastmp as $ka => $va) {
                                            //print_r($va);//LINEA_NOINTERNO
                                            //print_r($doctmp);//NODOCUMENTO_INTERNO
                                            //die();
                                            $va['ColumnValue'] = $dw->setValue($va['ColumnValue'], 'LINEA_NODOCUMENTO', $alb);
                                            $va['ColumnValue'] = $dw->setValue($va['ColumnValue'], 'LINEA_NOINTERNO', $dw->getValue($doctmp, 'NODOCUMENTO_INTERNO'));
                                            //print_r($va);
                                            //die();
                                            $rows_fra[] = $va;
                                        }
                                    } else {
                                        $rows_fra[] = $vl;
                                    }
                                    //print_r($rows_fra);
                                    //print_r($rows);
                                    //die();
                                }
                            }
                        }


                        //print_r($alb);
                        //die();
                    }
                    $rows = $rows_fra;

                    //print_r($rows);
                    //die();
                    //REPASAMOS LOS DATOS DE ALBARANES PARA SACAR TOTALES BASE
                    $albs = array();
                    foreach ($rows as $kl => $vl) {
                        //LINEA_NODOCUMENTO
                        //LINEA_IMPORTE
                        //LINEA_IMPORTE_TOTAL_DOC
                        //print($dw->getValue($vl['ColumnValue'], 'LINEA_IMPORTE'));
                        //die();

                        $alb = $dw->getValue($vl['ColumnValue'], 'LINEA_NODOCUMENTO');
                        $imp = 1 * (($dw->getValue($vl['ColumnValue'], 'LINEA_IMPORTE') != '') ? $dw->getValue($vl['ColumnValue'], 'LINEA_IMPORTE') : 0);
                        //print("$alb .. $imp");
                        //die();

                        if (!isset($albs[$alb])) {
                            $albs[$alb] = $imp;
                        } else {
                            $albs[$alb] += $imp;
                        }

                        //print(json_encode($vl));
                        //die();
                    }

                    foreach ($rows as $kl => $vl) {
                        $alb = $dw->getValue($vl['ColumnValue'], 'LINEA_NODOCUMENTO');
                        if (isset($albs[$alb])) {
                            $rows[$kl]['ColumnValue'] = $dw->setValue($vl['ColumnValue'], 'LINEA_IMPORTE_TOTAL_DOC', $albs[$alb]);
                        }
                    }


                    //print(json_encode($albs));
                    //print(json_encode($rows));
                    //die();

                    $fields = array();
                    $fields['Field'][] = array(
                        "FieldName" => 'LINEAS',
                        "Item" => array(
                            '$type' => "DocumentIndexFieldTable",
                            "Row" => $rows
                        )
                    );

                    $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);
                    //die('okokok');
                }
            }
        }

        return $data;
    }

    private function _checkContrato($param) {
        $data = array(
            'status' => 1,
            'accion' => '',
            'msg' => 'checkContrato'
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();


        if ($res['status'] == 200) {


            //$res=$dw->dw_org();
            $res = $dw->dw_get_all_index(DW_FC_PROVEEDORES, $param['docid']);

            //print_r($res);
            $fields = $res['response']['Field'];
            //print_r($fields);
            $contrato = $dw->getValue($fields, 'NOCONTRATO');

            //$contrato = '';
            if ($contrato != '') {
                $param_cond = array(
                    'Operation' => 'And',
                    'Condition' => array(
                        array(
                            'DBName' => 'NOCONTRATO',
                            'Value' => array($contrato)
                        ),
                        array(
                            'DBName' => 'ACTIVO',
                            'Value' => array('Sí')
                        )
                    )
                );
                //print_r($param_cond);
                //die();

                $res_query = $dw->dwGetDialogExpression(DW_FC_CONTRATOS, DW_SD_CONTRATOS, $param_cond);
                //print_r($res_query);
                //die();

                $items = array();
                if ($res_query['status'] == 1) {
                    $response = $dw->dw_get_all_docs_query($res_query['response'], 10);
                    //print_r($response);
                    //die();
                    if ($response['status'] = 200)
                        $items = $response['response']['Items'];
                    //print_r($items);
                    //die();
                }

                if (count($items) == 1) {
                    $con = $items[0]['Fields'];
                    //$con_lineas = $dw->getTable($fields, 'LINEAS');
                    //print_r($con_lineas);
                    //print_r($fields);
                    //die();
                    $vigente = 0;
                    $fecha_inicio = $dw->getDateValue($dw->getValue($con, 'INICIO_CONTRATO'));
                    $fecha_fin = $dw->getDateValue($dw->getValue($con, 'FIN_CONTRATO'));
                    $importe_contrato = $dw->getValue($con, 'IMPORTE');
                    $importe_documento = $dw->getValue($fields, 'BASE_1');
                    if ($fecha_inicio == '' || $fecha_fin == '') {
                        $data['status'] = 0;
                        $data['accion'] = 'TAREA';
                        $data['msg'] = 'Fechas del contrato no especificadas. No se puede determinar si está vigente el contrato.';
                    }

                    if ($data['status'] == 1) {
                        $fecha_actual = date('Y-m-d') . ' 00:00:00';
                        $fecha_inicio_format = $fecha_inicio;
                        $tmp = explode('/', $fecha_inicio);
                        $fecha_inicio = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                        //$fecha_fin = '21/05/2025';
                        $fecha_fin_format = $fecha_fin;
                        $tmp = explode('/', $fecha_fin);
                        $fecha_fin = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';

                        $fecha_inicio_mt = strtotime($fecha_inicio);
                        $fecha_fin_mt = strtotime($fecha_fin);
                        $fecha_actual_mt = strtotime($fecha_actual);
                        //print("{$fecha_inicio} -- {$fecha_fin} -- {$fecha_actual}");
                        //print("{$fecha_inicio_mt} -- {$fecha_fin_mt} -- {$fecha_actual_mt}");
                        //die();

                        if ($fecha_inicio_mt <= $fecha_actual_mt && $fecha_fin_mt >= $fecha_actual_mt) {
                            //CONTRATO VIGENTE, COMPROBAMOS IMPORTES INTRODUCIDOS
                            $importesCorrectos = true;
                            $motivo = "Contrato validado correctamente.";

                            if ($importe_contrato != $importe_documento) {
                                $importesCorrectos = false;
                                $motivo = "La base de la factura no corresponde con la base del contrato que es: {$importe_contrato}";
                            }

                            //ACTUALIZAMOS CAMPOS SI Y SI
                            $fields = array();
                            $fields['Field'][] = array(
                                "FieldName" => 'TIPO_DOCUMENTO',
                                "Item" => "FACTURA CONTRATO"
                            );
                            $fields['Field'][] = array(
                                "FieldName" => 'CUENTA_CONTABLE',
                                "Item" => $dw->getValue($con, 'CUENTA_CONTABLE')
                            );
                            $fields['Field'][] = array(
                                "FieldName" => 'TIPO_IVA',
                                "Item" => $dw->getValue($con, 'TIPO_IVA')
                            );
                            $fields['Field'][] = array(
                                "FieldName" => 'TIPO_FACTURA',
                                "Item" => $dw->getValue($con, 'TIPO_FACTURA')
                            );
                            $fields['Field'][] = array(
                                "FieldName" => 'CENTRO',
                                "Item" => $dw->getValue($con, 'CENTRO_GESTION')
                            );
                            $fields['Field'][] = array(
                                "FieldName" => 'MOTIVO',
                                "Item" => $motivo
                            );
                            /*
                              $fields['Field'][] = array(
                              "FieldName" => 'NO_DE_DOCUMENTO',
                              "Item" => $cab['suAlbaran']
                              );
                              $fields['Field'][] = array(
                              "FieldName" => 'CIF',
                              "Item" => $cab['cifProveedor']
                              );
                              $fields['Field'][] = array(
                              "FieldName" => 'NOMBRE_PROVEEDOR',
                              "Item" => $cab['descProveedor']
                              );
                             * 
                             */

                            $rows = array();

                            $row = array(
                                'ColumnValue' => array()
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ARTICULO",
                                "Item" => $dw->getValue($con, 'ARTICULO'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESCRIPCION",
                                "Item" => $dw->getValue($con, 'DESCRIPCION_ARTICULO'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CUENTA_CONTABLE",
                                "Item" => $dw->getValue($con, 'CUENTA_CONTABLE'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CENTRO_DE_GESTION",
                                "Item" => $dw->getValue($con, 'CENTRO_GESTION'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_TIPO_IVA",
                                "Item" => $dw->getValue($con, 'TIPO_IVA'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESC_CLASIFICACION",
                                "Item" => $dw->getValue($con, 'TIPO_FACTURA'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $dw->getValue($con, 'CANTIDAD'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_PRECIO",
                                "Item" => $dw->getValue($con, 'PRECIO'),
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_IMPORTE",
                                "Item" => $dw->getValue($con, 'IMPORTE'),
                                "ItemElementName" => "String"
                            );
                            $rows[] = $row;

                            if ((int) $dw->getValue($con, 'NOLINEAS') == 2) {
                                $row = array(
                                    'ColumnValue' => array()
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_ARTICULO",
                                    "Item" => $dw->getValue($con, 'ARTICULO'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_DESCRIPCION",
                                    "Item" => $dw->getValue($con, 'DESCRIPCION_ARTICULO'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_CUENTA_CONTABLE",
                                    "Item" => $dw->getValue($con, 'CUENTA_CONTABLE_2'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_CENTRO_DE_GESTION",
                                    "Item" => $dw->getValue($con, 'CENTRO_GESTION_2'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_TIPO_IVA",
                                    "Item" => $dw->getValue($con, 'TIPO_IVA_2'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_DESC_CLASIFICACION",
                                    "Item" => $dw->getValue($con, 'TIPO_FACTURA'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_CANTIDAD",
                                    "Item" => $dw->getValue($con, 'CANTIDAD_2'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_PRECIO",
                                    "Item" => $dw->getValue($con, 'PRECIO_2'),
                                    "ItemElementName" => "String"
                                );
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_IMPORTE",
                                    "Item" => $dw->getValue($con, 'IMPORTE_2'),
                                    "ItemElementName" => "String"
                                );
                                $rows[] = $row;
                            }

                            $fields['Field'][] = array(
                                "FieldName" => 'LINEAS',
                                "Item" => array(
                                    '$type' => "DocumentIndexFieldTable",
                                    "Row" => $rows
                                )
                            );

                            //print_r($fields);
                            //print($dw->getValue($con, 'DWDOCID'));
                            //die();

                            $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, '', $param['docid']);

                            if ($importesCorrectos) {
                                //IMPORTES CORRECTOS
                                //print('in');
                                //die();
                                $data['status'] = 1;
                                $data['accion'] = 'EXPORTAR';
                            } else {
                                $data['status'] = 0;
                                $data['accion'] = 'TAREA';
                                $data['msg'] = "Los importes introducidos asociados a la tarea no coinciden";
                            }
                        } else {
                            $data['status'] = 0;
                            $data['accion'] = 'TAREA';
                            $data['msg'] = "Las fechas del contrato encontrado no está vigente al día de hoy. Fecha inicio: {$fecha_inicio_format} Fecha fin: {$fecha_fin_format}";
                        }

                        //print_r(microtime($fecha_actual));
                        //die();
                    }
                } else {
                    $data['status'] = 1;
                    $data['accion'] = 'TAREA';
                    $data['msg'] = "Contratos encontrados: " . count($items);
                }
            } else {
                $data['status'] = 1;
                $data['accion'] = 'CONTINUAR';
                $data['msg'] = 'Contrato no informado.';
            }

            //print($contrato);
            //print(json_encode($fields));
            //print($ord);
            //print($tipo);
            //die();
        }

        return $data;
    }

    private function _fechaDeclaracion($param) {
        $data = array(
            'status' => 1,
            'msg' => 'fechaDeclaracion',
            'items' => array()
        );

        $str = file_get_contents("php://input");

        if ($data['status'] == 1 && $str == '') {
            $data['status'] = 0;
            $data['msg'] = 'Body not found.';
        } else {
            //$param = array_merge($param, json_decode($str, true));
            $items = json_decode($str, true);
        }

        if ($data['status'] == 1) {
            file_put_contents(HISTORY . '/' . date('YmdHis') . ".txt", $str);
        }

        if (count($items) == 0) {
            $data['status'] = 0;
            $data['msg'] = 'Body without lines.';
        }


        if ($data['status'] == 1) {
            $dw = new DocuwareApi(DW_URL);
            $res = $dw->loginDW_v2(DW_USER, DW_PASS);
            //print(json_encode($res));
            //die();


            if ($res['status'] == 200) {

                foreach ($items as $k => $v) {
                    $item = array(
                        'status' => 0,
                        'docid' => 0,
                        'ADQ' => $v['ADQ'],
                        'msg' => ''
                    );
                    $param_cond = array(
                        'Operation' => 'And',
                        'Condition' => array(
                            array(
                                'DBName' => 'NO_ADQ',
                                'Value' => array($v['ADQ'])
                            )
                        /* ,
                          array(
                          'DBName' => 'NO_DE_DOCUMENTO',
                          'Value' => array($v['NFacturaProveedor'])
                          )
                         * 
                         */
                        )
                    );
                    //print_r($param_cond);
                    //die();

                    $res_query = $dw->dwGetDialogExpression(DW_FC_PROVEEDORES, DW_SD_PROVEEDORES, $param_cond);

                    $response = $dw->dw_get_all_docs_query($res_query['response'], 10);
                    //print_r($response);
                    //die();
                    if ($response['status'] = 200 && isset($response['response']['Items'][0])) {
                        $item['status'] = 1;
                        $item['msg'] = 'Factura encontrada';
                        $doc = $response['response']['Items'][0]['Fields'];

                        $docid = $dw->getValue($doc, 'DWDOCID');
                        $item['docid'] = $docid;
                        $base = $dw->getValue($doc, 'BASE_1');
                        $cuota = $dw->getValue($doc, 'CUOTA_IVA_1');
                        $importe = $dw->getValue($doc, 'IMPORTE_TOTAL');
                        //$fecha_declaracion = $dw->getValue($doc, 'FECHA_DECLARACION');
                        $fecha_declaracion = $v['FechaParaDeclaracion'];

                        //print_r($doc);
                        //print_r($docid);
                        //die();

                        if ($fecha_declaracion != '') {
                            //print_r($fecha_declaracion);
                            //die();
                            $tmp = $fecha_declaracion;
                            $tmp = explode(' ', $tmp);
                            $tmp = explode('/', $tmp[0]);
                            //$fecha_declaracion = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';
                            //print($fecha_declaracion);
                            //die();
                            $fields_update = array();
                            if ($importe == $v['ImporteTotal']) {

                                /*
                                  $fields_update['Field'][] = array(
                                  "FieldName" => 'ESTADO',
                                  "Item" => 'VALIDANDO PEDIDO'
                                  );
                                 * 
                                 */
                                $fields_update['Field'][] = array(
                                    "FieldName" => 'FECHA_DECLARACION',
                                    "Item" => $fecha_declaracion
                                );
                                $fields_update['Field'][] = array(
                                    "FieldName" => 'ESTADO_DECLARACION',
                                    "Item" => 'ASIGNADA ' . date('d/m/Y')
                                );
                                $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields_update, '', $docid);
                            } else {
                                $item['msg'] = 'Factura encontrada, error en importes';
                                $fields_update['Field'][] = array(
                                    "FieldName" => 'ESTADO_DECLARACION',
                                    "Item" => 'ERROR IMPORTES'
                                );
                                $fields_update['Field'][] = array(
                                    "FieldName" => 'TMP_FECHA_DECLARACION',
                                    "Item" => $fecha_declaracion
                                );

                                $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields_update, '', $docid);
                            }
                        }
                    } else {
                        $item['status'] = 0;
                        $item['msg'] = 'Factura no encontrada.';
                    }
                    $data['items'][] = $item;
                }
            }


            //$contrato = '';
            //print($contrato);
            //print(json_encode($fields));
            //print($ord);
            //print($tipo);
            //die();
        }
        return $data;
    }

    public function _importContratos($param) {
        //https://docuware.pipex.es/api/v1/tools/import-contratos.php
        $data = array(
            'status' => 1,
            'msg' => 'importContratos',
            'errores' => array(),
            'items' => array()
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();


        if ($res['status'] == 200) {
            $items = array();

            $file_csv = TOOLS . '/contratos.csv';
            if (file_exists($file_csv)) {
                if (($f = fopen($file_csv, "r")) !== FALSE) {
                    while (($d = fgetcsv($f, 2000, ";")) !== FALSE) {
                        //print_r($d);
                        //die();
                        $reg = array(
                            'nlineas' => $d[0],
                            'activo' => 'Sí',
                            'periocidad' => $d[2],
                            'proveedor' => $d[3],
                            'razonsocial' => $d[4],
                            'cifproveedor' => $d[5],
                            'articulo' => $d[6],
                            'descripcion' => $d[7],
                            'cc1' => $d[8],
                            'cc2' => $d[9],
                            'tipoiva1' => $d[10],
                            'tipoiva2' => $d[11],
                            'cg1' => $d[12],
                            'cg2' => $d[13],
                            'cant1' => str_replace(',', '.', $d[14]),
                            'cant2' => str_replace(',', '.', $d[15]),
                            'precio1' => str_replace(',', '.', str_replace('.', '', $d[16])),
                            'precio2' => str_replace(',', '.', str_replace('.', '', $d[17])),
                            'importe1' => str_replace(',', '.', str_replace('.', '', $d[18])),
                            'importe2' => str_replace(',', '.', str_replace('.', '', $d[19])),
                            'tipofactura' => $d[20],
                            'moneda' => $d[21],
                            'ncontrato' => $d[22],
                            'contrato_ini' => $d[23],
                            'contrato_fin' => $d[24],
                            'mescargo' => $d[25],
                            'responsable' => $d[26],
                            'detalladofactura' => 'S',
                            'baseanual' => str_replace(',', '.', $d[28]),
                            'modorenovacion' => $d[29],
                            'preaviso' => $d[30],
                            'contrato' => 'S',
                            'obs' => $d[32]
                        );
                        //print_r($reg);
                        //die();
                        $items[] = $reg;
                    }
                    fclose($f);
                }
            }


            if (count($items) > 0) {
                //print(count($items));
                //print_r($items);
                //die();
                unset($items[0]);
                sort($items);

                //print(count($items));
                //print_r($items);
                //die();

                foreach ($items as $k => $v) {
                    $fields = array(
                        "Fields" => array()
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "DOCUMENT_TYPE",
                        "Item" => 'CONTRATO'
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "NOCONTRATO",
                        "Item" => $v['ncontrato']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "PERIOCIDAD",
                        "Item" => $v['periocidad']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "PROVEEDOR",
                        "Item" => $v['proveedor']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "RAZON_SOCIAL",
                        "Item" => $v['razonsocial']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CIF_PROVEEDOR",
                        "Item" => $v['cifproveedor']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "ARTICULO",
                        "Item" => $v['articulo']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "DESCRIPCION_ARTICULO",
                        "Item" => $v['descripcion']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CUENTA_CONTABLE",
                        "Item" => $v['cc1']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "TIPO_IVA",
                        "Item" => $v['tipoiva1']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CENTRO_GESTION",
                        "Item" => $v['cg1']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CANTIDAD",
                        "Item" => $v['cant1']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "PRECIO",
                        //"Item" => str_replace(', ', '.', str_replace('.', '', $v['precio1']))
                        "Item" => $v['precio1']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "IMPORTE",
                        //"Item" => str_replace(', ', '.', str_replace('.', '', $v['importe1']))
                        "Item" => $v['importe1']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "TIPO_FACTURA",
                        "Item" => $v['tipofactura']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "MONEDA",
                        "Item" => $v['moneda']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "MES_CARGO",
                        "Item" => $v['mescargo']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "RESPONSABLE",
                        "Item" => $v['responsable']
                    );
                    /*
                      $fields['Fields'][] = array(
                      "FieldName" => "FECHA_RENOVACION",
                      "Item" => $v['']
                      );
                     * 
                     */
                    $fields['Fields'][] = array(
                        "FieldName" => "BASE_ANUAL",
                        "Item" => $v['baseanual']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "MODO_RENOVACION",
                        "Item" => $v['modorenovacion']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "PREAVISO",
                        "Item" => $v['preaviso']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "ACTIVO",
                        "Item" => 'Sí'
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "NOLINEAS",
                        "Item" => $v['nlineas']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CUENTA_CONTABLE_2",
                        "Item" => $v['cc2']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "TIPO_IVA_2",
                        "Item" => $v['tipoiva2']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CENTRO_GESTION_2",
                        "Item" => $v['cg2']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "CANTIDAD_2",
                        "Item" => $v['cant2']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "PRECIO_2",
                        "Item" => $v['precio2']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "IMPORTE_2",
                        "Item" => $v['importe2']
                    );
                    $fields['Fields'][] = array(
                        "FieldName" => "OBSERVACIONES",
                        "Item" => $v['obs']
                    );

                    $fecha = '';
                    if ($v['contrato_ini'] != '') {
                        //$fecha = explode('/', $v['contrato_ini']);
                        //$fecha = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                        $fecha = explode('-', $v['contrato_ini']);
                        if (isset($fecha[2]) && isset($fecha[1])) {
                            $fecha = '20' . $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                        } else {
                            $data['errores'][] = $v['ncontrato'];
                        }
                        //print($fecha);
                        //die();
                    }
                    $fields['Fields'][] = array(
                        "FieldName" => "INICIO_CONTRATO",
                        "Item" => $fecha
                    );
                    $fecha = '';
                    if ($v['contrato_fin'] != '') {
                        //$fecha = explode('/', $v['contrato_fin']);
                        //$fecha = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                        $fecha = explode('-', $v['contrato_fin']);
                        if (isset($fecha[2]) && isset($fecha[1])) {
                            $fecha = '20' . $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                        } else {
                            $data['errores'][] = $v['ncontrato'];
                        }
                    }
                    $fields['Fields'][] = array(
                        "FieldName" => "FIN_CONTRATO",
                        "Item" => $fecha
                    );

                    //print_r($fields);
                    //die();
                    //POR SEGURIDAD$data['items'][] = $dw->dw_add_doc(DW_FC_CONTRATOS, $fields, '');
                    //print_r($data);
                    //die();
                }
            }
            //print($contrato);
            //print(json_encode($fields));
            //print($ord);
            //print($tipo);
            //die();
        }

        return $data;
    }

    private function _validar_pedido_cliente($param) {
        set_time_limit(120);

        $data = array(
            'status' => 1,
            'msg' => 'Documento validado correctamente'
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();
        $error = 0;

        if ($res['status'] == 200) {

            //ACTUALIZAMOS ESTADO DE DOCUMENTO PARA EVITAR QUE SE DISPARE LA TAREA NUEVAMENTE
            $fields_update = array();
            $fields_update['Field'][] = array(
                "FieldName" => 'ESTADO',
                "Item" => 'VALIDANDO PEDIDO'
            );
            $res = $dw->dw_update_doc(DW_FC_CLIENTES, $fields_update, '', $param['docid']);

            //$res=$dw->dw_org();
            //COGEMOS TODOS LOS INDICES DEL DOCUMENTO
            $res = $dw->dw_get_all_index(DW_FC_CLIENTES, $param['docid']);
            //print_r($res);
            //die();

            $fields = $res['response'];
            $ord = array(
                'dwdoc_id' => $dw->getValue($fields['Field'], 'DWDOCID'),
                'tipo' => $dw->getValue($fields['Field'], 'TIPO_DOCUMENTO'),
                'ndoc' => $dw->getValue($fields['Field'], 'NO_DE_DOCUMENTO'),
                'cif' => str_replace(array('_', '-', ' '), '', $dw->getValue($fields['Field'], 'CIF')),
                'fecha' => $dw->getDateValue($dw->getValue($fields['Field'], 'FECHA')),
                'fecha_entrega' => $dw->getDateValue($dw->getValue($fields['Field'], 'FECHA_DE_ENTREGA')),
                'obs' => $dw->getValue($fields['Field'], 'OBSERVACIONES'),
                'fuente' => $dw->getValue($fields['Field'], 'FUENTE'),
                'id' => '',
                'nombre' => $dw->getValue($fields['Field'], 'NOMBRE_CLIENTE'),
                'estado' => '',
                'motivo' => '',
                'moneda' => '',
                'idioma' => '',
                'pais' => '',
                'duplicado' => 0,
                'lineas' => array()
            );
            if ($dw->getValue($fields['Field'], 'COD_CLIENTE') != '') {
                $ord['id'] = $dw->getValue($fields['Field'], 'COD_CLIENTE');
            }

            //COMPROBAMOS QUE EL PEDIDO NO ESTÉ DUPLICADO
            $param_cond = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'TIPO_DOCUMENTO',
                        'Value' => array('PEDIDO CLIENTE')
                    ),
                    array(
                        'DBName' => 'NO_DE_DOCUMENTO',
                        'Value' => array($ord['ndoc'])
                    )
                )
            );
            //print_r($param_cond);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_CLIENTES, DW_SD_CLIENTES, $param_cond);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 10);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            if (count($items) > 0) {
                foreach ($items as $ki => $vi) {
                    if ($vi['Id'] != $ord['dwdoc_id'])
                        $ord['duplicado'] = 1;
                }
            }

            $lineas = $dw->getTable($fields['Field'], 'LINEAS');
            foreach ($lineas as $k => $v) {
                $lin = array(
                    'referencia' => strtoupper($dw->getValue($v['ColumnValue'], 'LINEA_SU_REFERENCIA')),
                    'articulo' => strtoupper($dw->getValue($v['ColumnValue'], 'LINEA_ARTICULO')),
                    'formato' => strtoupper($dw->getValue($v['ColumnValue'], 'LINEA_FORMATO')),
                    'cantidad' => $dw->getValue($v['ColumnValue'], 'LINEA_CANTIDAD'),
                    'precio' => $dw->getValue($v['ColumnValue'], 'LINEA_PRECIO'),
                    'precio_formato' => $dw->getValue($v['ColumnValue'], 'LINEA_PRECIO_FORMATO'),
                    'desc_pedido' => $dw->getValue($v['ColumnValue'], 'LINEA_DESCRIPCION_PEDIDO'),
                    'desc_articulo' => '',
                    'desc_referencia' => $dw->getValue($v['ColumnValue'], 'LINEA_DESCRIPCION'),
                    'estado' => '',
                    'motivo' => '',
                    'descripcion' => ''
                );

                $ord['lineas'][] = $lin;
                //print($dw->getValue($v['ColumnValue'], 'LINEA_ARTICULO'));
                //die();
            }
            //print(json_encode($fields));
            //print_r($ord);
            //print(json_encode($ord));
            //print_r($lineas);
            //die();

            if (isset($param['debug']) && $param['debug'] == 'docuware') {
                print(json_encode($ord));
                die();
            }

            $exp = new ExpertisApi(EX_URL);

            $res = $exp->loginEXP(EX_USER, EX_PASS);

            //print(json_encode($res));
            //print(json_encode($indexes));
            //die();

            $envio_default = '';

            if ($res['status'] == 200) {
                $res = $exp->getCustomerData($ord['cif'], $ord['id'], $ord['nombre']);

                //print_r($res);
                //print(json_encode($res));
                //die();

                if ($res['status'] == 200 && $res['response']['message'] == 'Success') {
                    $cli = $res['response']['result'];
                    //print(json_encode($cli));
                    //die();
                    $ficha = $cli['tbMaestroCliente'][0];
                    $articulos = $cli['articuloCliente'];
                    $referencias = $cli['articuloFormatoCliente'];
                    $fittings = $cli['articuloClienteSinFormato'];
                    $envios = $cli['clienteDireccion'];
                    $envio_default = $envios[0]['idDireccion'];

                    foreach ($envios as $ke => $ve) {
                        if ($ve['predeterminadaEnvio']) {
                            $envio_default = $ve['idDireccion'];
                        }
                    }
                    //print(json_encode($envios));
                    //print($envio_default);
                    //print(json_encode($ficha));
                    //print(json_encode($articulos));
                    //print(json_encode($referencias));
                    //die();

                    if (isset($param['debug']) && $param['debug'] == 'articulos') {
                        print(json_encode(($articulos)));
                        die();
                    }
                    if (isset($param['debug']) && $param['debug'] == 'referencias') {
                        print(json_encode(($referencias)));
                        die();
                    }
                    if (isset($param['debug']) && $param['debug'] == 'fittings') {
                        print(json_encode(($fittings)));
                        die();
                    }

                    $ord['id'] = $ficha['idCliente'];
                    $ord['cif'] = $ficha['cifCliente'];
                    $ord['nombre'] = $ficha['descCliente'];
                    $ord['moneda'] = $ficha['idMoneda'];
                    $ord['idioma'] = $ficha['idIdioma'];
                    $ord['pais'] = $ficha['idPais'];

                    //print_r($articulos);
                    //print_r($referencias);
                    //print_r($ord);
                    //print_r($ord['lineas']);
                    //die();
                    if ($ord['duplicado'] > 0) {
                        $error = 1;
                        $ord['estado'] = 'ERROR VALIDACIÓN';
                        $ord['motivo'] = 'Pedido de cliente ya existente en el archivador.';
                    }
                    //REVISAMOS LÍNEAS REFERENCIAS, ARTICULOS Y FORMATOS
                    //SI NO HAY ERROR DE DUPLICIDAD DE PEDIDO
                    if ($error == 0) {
                        foreach ($ord['lineas'] as $ko => $vo) {
                            //EXISTEN DOS TIPOS DE LÍNEAS
                            //1-ARTÍCULO-FORMATO SI NO ARTÍCULO
                            //2-REFERENCIA SI NO REFERENCIA EN ARTÍCULO
                            //3-ARTÍCULO SI NO ARTÍCULO EN REFERENCIA
                            //4-LINEA DE TEXTO CON CÓDIGO TXT001
                            $tipo = 0;
                            $error_lin = 0;
                            $count = 0;

                            if ($vo['cantidad'] <= 0 || $vo['cantidad'] == '') {
                                //if ($vo['articulo'] != 'TXT001') {
                                $error = 1;
                                $error_lin = 1;
                                $ord['estado'] = 'ERROR VALIDACIÓN';
                                $ord['motivo'] = 'En la líneas se encuentra un valor de cantidad no permitido.';
                                $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                $ord['lineas'][$ko]['motivo'] = "El campo cantidad tiene que ser positivo no vacío.";
                                //}
                            }

                            if ($error_lin == 0 && $vo['referencia'] != '' && $vo['articulo'] == '') {
                                //BUSCAMOS EN FORMATOS Y EN FITTINGS
                                $tipo = 2;
                            }
                            //if ($error_lin == 0 && $vo['referencia'] == '' && $vo['articulo'] != '' && $vo['formato'] == '') {
                            if ($error_lin == 0 && $vo['referencia'] == '' && $vo['articulo'] != '') {
                                //BUSCAMOS EN FORMATOS Y EN FITTINGS
                                $tipo = 3;
                            }
                            if ($error_lin == 0 && $vo['articulo'] != '' && $vo['formato'] != '') {
                                //BUSCAMOS EN FORMATOS
                                $tipo = 1;
                            }
                            if ($error_lin == 0 && in_array($vo['articulo'], array('TXT001', 'TTE001'))) {
                                $tipo = 4;
                            }
                            //if ($error_lin == 0 && $vo['referencia'] != '' && $vo['articulo'] != '' && $vo['formato'] == '') {
                            if ($error_lin == 0 && $vo['referencia'] != '' && $vo['articulo'] != '' && $vo['formato'] == '') {
                                //BUSCAMOS EN FORMATOS Y FITTINGS
                                $tipo = 5;
                            }

                            if (isset($param['debug']) && isset($param['lin']) && $param['debug'] == 'tipo-linea' && $ko == $param['lin']) {
                                //print("{$error_lin}-{$tipo}");
                                print("tipo linea $ko: $tipo");
                                die();
                            }

                            if ($error_lin == 0 && $tipo == 0) {
                                $error = 1;
                                $error_lin = 1;
                                $ord['estado'] = 'ERROR VALIDACIÓN';
                                $ord['motivo'] = 'Tipo de línea no reconocida. La combinación referencia, artículo, formato no ha podido ser clasificado';
                                $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                $ord['lineas'][$ko]['motivo'] = "Tipo de línea no localizada, combinación de referencia, artículo, formato no detectado.";
                            }

                            //TIPO 1
                            if ($error_lin == 0 && $tipo == 1) {
                                $encontrado = false;

                                //PRIMERO MIRAMOS SI COINCIDE ARTICULO Y FORMATO
                                $count = 0;
                                foreach ($referencias as $ka => $va) {
                                    if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo']) && strtoupper($va['idFormato']) == strtoupper($vo['formato'])) {
                                        $ord['lineas'][$ko]['descripcion'] = $va['descFormato'];
                                        $ord['lineas'][$ko]['referencia'] = (!is_null($va['refCliente']) ? $va['refCliente'] : '');
                                        $encontrado = true;
                                        $count++;
                                    }
                                }

                                //SI NO HEMOS ENCONTRADO ARTICULO-FORMATO BUSCAMOS SOLO POR ARTICULO
                                $count = 0;
                                $index_art = -1;
                                if (!$encontrado) {
                                    foreach ($referencias as $ka => $va) {
                                        if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo'])) {
                                            $encontrado = true;
                                            $index_art = $ka;
                                            $count++;
                                        }
                                    }
                                    if ($encontrado) {
                                        if ($count == 1) {
                                            $ord['lineas'][$ko]['descripcion'] = $referencias[$index_art]['descFormato'];
                                            $ord['lineas'][$ko]['formato'] = $referencias[$index_art]['idFormato'];
                                            $ord['lineas'][$ko]['referencia'] = (!is_null($referencias[$index_art]['refCliente']) ? $referencias[$index_art]['refCliente'] : '');
                                        }
                                        if ($count > 1) {
                                            $error_lin = 1;
                                            $error = 1;
                                            $ord['estado'] = 'ERROR VALIDACIÓN';
                                            $ord['motivo'] = 'Artículo encontrado repetido y no se puede asignar formato.';
                                            $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                            $ord['lineas'][$ko]['motivo'] = "Artículo encontrado repetido en las referencias del cliente y no se puede asignar formato.";
                                        }
                                    } else {
                                        $error_lin = 1;
                                        $error = 1;
                                        $ord['estado'] = 'ERROR VALIDACIÓN';
                                        $ord['motivo'] = 'Relación Artículo-Formato y Artículo no localizado.';
                                        $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                        $ord['lineas'][$ko]['motivo'] = "Artículo-Formato y Artículo no encontrado en las referencias del cliente.";
                                    }
                                }
                            }

                            //TIPO 2 -- $vo['referencia'] != '' && $vo['articulo'] == ''
                            if ($error_lin == 0 && $tipo == 2) {
                                //BUSCAMOS EN FITTINGS A VER SI EXISTE LA REFERENCIA
                                $isFittings = false;
                                $encontrado = false;
                                foreach ($fittings as $kf => $vf) {
                                    if (strtoupper($vf['refCliente']) == strtoupper($vo['referencia'])) {
                                        $ord['lineas'][$ko]['descripcion'] = $vf['descRefCliente'];
                                        $ord['lineas'][$ko]['articulo'] = $vf['idArticulo'];
                                        $ord['lineas'][$ko]['formato'] = '';
                                        $ord['lineas'][$ko]['medida'] = 'Un';
                                        $ord['lineas'][$ko]['referencia'] = $vf['refCliente'];
                                        $encontrado = true;
                                        $isFittings = true;
                                    }
                                }

                                if (!$isFittings) {
                                    //BUSCAMOS EN REFERENCIAS
                                    $encontrado = false;
                                    foreach ($referencias as $kr => $vr) {
                                        if (strtoupper($vr['refCliente']) == strtoupper($vo['referencia'])) {
                                            $ord['lineas'][$ko]['descripcion'] = $vr['descFormato'];
                                            $ord['lineas'][$ko]['articulo'] = $vr['idArticulo'];
                                            $ord['lineas'][$ko]['formato'] = $vr['idFormato'];
                                            $ord['lineas'][$ko]['referencia'] = $vr['refCliente'];
                                            $encontrado = true;
                                        }
                                    }

                                    //SI NO HEMOS ENCONTRADO ARTICULO-FORMATO BUSCAMOS SOLO POR ARTICULO
                                    $count = 0;
                                    $index_art = -1;
                                    if (!$encontrado) {
                                        foreach ($referencias as $ka => $va) {
                                            if (strtoupper($va['idArticulo']) == strtoupper($vo['referencia'])) {
                                                //$ord['lineas'][$ko]['articulio'] = $va['refCliente'];
                                                //$ord['lineas'][$ko]['descripcion'] = $va['descArticulo'];
                                                $encontrado = true;
                                                $index_art = $ka;
                                                $count++;
                                            }
                                        }
                                        if ($encontrado) {
                                            if ($count == 1) {
                                                $ord['lineas'][$ko]['formato'] = $referencias[$index_art]['idFormato'];
                                                $ord['lineas'][$ko]['referencia'] = (!is_null($referencias[$index_art]['refCliente']) ? $referencias[$index_art]['refCliente'] : '');
                                                $ord['lineas'][$ko]['articulo'] = $referencias[$index_art]['idArticulo'];
                                                $ord['lineas'][$ko]['descripcion'] = $referencias[$index_art]['descFormato'];
                                            }
                                            if ($count > 1) {
                                                $error_lin = 1;
                                                $error = 1;
                                                $ord['estado'] = 'ERROR VALIDACIÓN';
                                                $ord['motivo'] = 'Artículo encontrado repetido y no se puede asignar formato.';
                                                $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                                $ord['lineas'][$ko]['motivo'] = "Artículo encontrado repetido en las referencias del cliente y no se puede asignar formato.";
                                            }
                                        } else {
                                            $error = 1;
                                            $error_lin = 1;
                                            $ord['estado'] = 'ERROR VALIDACIÓN';
                                            $ord['motivo'] = 'Artículo/Referencia no coincide con la lista asociada de artículos del cliente.';
                                            $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                            $ord['lineas'][$ko]['motivo'] = "Referencia/Artículo no encontrado en los artículos del cliente.";
                                        }
                                    }
                                }
                            }

                            //TIPO 3 -- $vo['referencia'] == '' && $vo['articulo'] != ''
                            if ($error_lin == 0 && $tipo == 3) {
                                $fittingsDescription = '';
                                //DETERMINAMOS SI TIENE FORMATO(FITTINGS)
                                $isFittings = false;
                                foreach ($articulos as $ka => $va) {
                                    if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo'])) {
                                        $isFittings = !$va['tieneFormato'];
                                        $fittingsDescription = $va['descArticulo'];
                                    }
                                }

                                //print_r($isFittings);
                                //die();

                                if ($isFittings) {
                                    $encontrado = false;
                                    foreach ($fittings as $ka => $va) {
                                        if (strtoupper($vo['referencia']) != '' && strtoupper($va['idArticulo']) == strtoupper($vo['articulo']) && strtoupper($va['refCliente']) == strtoupper($vo['referencia'])) {
                                            //$ord['lineas'][$ko]['articulio'] = $va['refCliente'];
                                            //$ord['lineas'][$ko]['descripcion'] = $va['descArticulo'];
                                            $encontrado = true;
                                            $index_art = $ka;
                                            //$count++;
                                        }
                                    }
                                    if ($encontrado) {
                                        $ord['lineas'][$ko]['formato'] = '';
                                        $ord['lineas'][$ko]['referencia'] = $fittings[$index_art]['refCliente'];
                                        $ord['lineas'][$ko]['articulo'] = $fittings[$index_art]['idArticulo'];
                                        $ord['lineas'][$ko]['descripcion'] = $fittingsDescription;
                                        $ord['lineas'][$ko]['medida'] = 'Un';
                                    } else {
                                        $error = 1;
                                        $error_lin = 1;
                                        $ord['estado'] = 'ERROR VALIDACIÓN';
                                        $ord['motivo'] = 'Artículo/Referencia no coincide con la lista asociada de artículos del cliente sin formato.';
                                        $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                        $ord['lineas'][$ko]['motivo'] = "Referencia/Artículo no encontrado en los artículos del cliente sin formato.";
                                    }
                                }

                                if (!$isFittings) {
                                    //BUSCAMOS EN REFERENCIAS
                                    $encontrado = false;
                                    foreach ($referencias as $kr => $vr) {
                                        if (strtoupper($vr['refCliente']) == strtoupper($vo['articulo'])) {
                                            $ord['lineas'][$ko]['descripcion'] = $vr['descFormato'];
                                            $ord['lineas'][$ko]['articulo'] = $vr['idArticulo'];
                                            $ord['lineas'][$ko]['formato'] = $vr['idFormato'];
                                            $ord['lineas'][$ko]['referencia'] = $vr['refCliente'];
                                            $encontrado = true;
                                        }
                                    }

                                    //SI NO HEMOS ENCONTRADO ARTICULO-FORMATO BUSCAMOS SOLO POR ARTICULO
                                    $count = 0;
                                    $index_art = -1;
                                    if (!$encontrado) {
                                        foreach ($referencias as $ka => $va) {
                                            if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo'])) {
                                                //$ord['lineas'][$ko]['articulio'] = $va['refCliente'];
                                                //$ord['lineas'][$ko]['descripcion'] = $va['descArticulo'];
                                                $encontrado = true;
                                                $index_art = $ka;
                                                $count++;
                                            }
                                        }
                                        if ($encontrado) {
                                            if ($count == 1) {
                                                $ord['lineas'][$ko]['formato'] = $referencias[$index_art]['idFormato'];
                                                $ord['lineas'][$ko]['referencia'] = (!is_null($referencias[$index_art]['refCliente']) ? $referencias[$index_art]['refCliente'] : '');
                                                $ord['lineas'][$ko]['articulo'] = $referencias[$index_art]['idArticulo'];
                                                $ord['lineas'][$ko]['descripcion'] = $referencias[$index_art]['descFormato'];
                                            }
                                            if ($count > 1) {
                                                $error_lin = 1;
                                                $error = 1;
                                                $ord['estado'] = 'ERROR VALIDACIÓN';
                                                $ord['motivo'] = 'Artículo encontrado repetido y no se puede asignar formato.';
                                                $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                                $ord['lineas'][$ko]['motivo'] = "Artículo encontrado repetido en las referencias del cliente y no se puede asignar formato.";
                                            }
                                        } else {
                                            $error = 1;
                                            $error_lin = 1;
                                            $ord['estado'] = 'ERROR VALIDACIÓN';
                                            $ord['motivo'] = 'Artículo/Referencia no coincide con la lista asociada de artículos del cliente.';
                                            $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                            $ord['lineas'][$ko]['motivo'] = "Referencia/Artículo no encontrado en los artículos del cliente.";
                                        }
                                    }
                                }
                            }

                            //TIPO 4
                            if ($error_lin == 0 && $tipo == 4) {
                                $encontrado = true;
                            }

                            //print(json_encode($ord));
                            //die();
                            //TIPO 5
                            if ($error_lin == 0 && $tipo == 5) {

                                $fittingsDescription = '';
                                //DETERMINAMOS SI TIENE FORMATO(FITTINGS)
                                $isFittings = false;
                                foreach ($articulos as $ka => $va) {
                                    if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo'])) {
                                        $isFittings = !$va['tieneFormato'];
                                        $fittingsDescription = $va['descArticulo'];
                                    }
                                }

                                if ($isFittings) {
                                    $encontrado = false;
                                    foreach ($fittings as $ka => $va) {
                                        if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo']) && strtoupper($va['refCliente']) == strtoupper($vo['referencia'])) {
                                            //$ord['lineas'][$ko]['articulio'] = $va['refCliente'];
                                            //$ord['lineas'][$ko]['descripcion'] = $va['descArticulo'];
                                            $encontrado = true;
                                            $index_art = $ka;
                                            //$count++;
                                        }
                                    }
                                    if ($encontrado) {
                                        $ord['lineas'][$ko]['formato'] = '';
                                        $ord['lineas'][$ko]['referencia'] = $fittings[$index_art]['refCliente'];
                                        $ord['lineas'][$ko]['articulo'] = $fittings[$index_art]['idArticulo'];
                                        $ord['lineas'][$ko]['descripcion'] = $fittingsDescription;
                                    } else {
                                        $error = 1;
                                        $error_lin = 1;
                                        $ord['estado'] = 'ERROR VALIDACIÓN';
                                        $ord['motivo'] = 'Artículo/Referencia no coincide con la lista asociada de artículos del cliente sin formato.';
                                        $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                        $ord['lineas'][$ko]['motivo'] = "Referencia/Artículo no encontrado en los artículos del cliente sin formato.";
                                    }
                                }

                                if (!$isFittings) {
                                    //BUSCAMOS POR REFERENCIAS
                                    $encontrado = false;
                                    //print_r($vo);
                                    //die();
                                    $count_ref = 0;
                                    $index_art = -1;
                                    foreach ($referencias as $kr => $vr) {
                                        if (strtoupper($vr['refCliente']) == strtoupper($vo['referencia'])) {
                                            $encontrado = true;
                                            $index_art = $kr;
                                            $count_ref++;

                                            if ($count_ref == 1) {
                                                //print($vr['idFormato']);
                                                //die();
                                            }
                                        }
                                    }

                                    if ($encontrado) {
                                        if ($count_ref == 1) {
                                            $ord['lineas'][$ko]['descripcion'] = $referencias[$index_art]['descFormato'];
                                            $ord['lineas'][$ko]['articulo'] = $referencias[$index_art]['idArticulo'];
                                            $ord['lineas'][$ko]['formato'] = $referencias[$index_art]['idFormato'];
                                            $ord['lineas'][$ko]['referencia'] = $referencias[$index_art]['refCliente'];
                                        }
                                        if ($count_ref > 1) {
                                            $encontrado = false;
                                        }
                                    } else {
                                        $encontrado = false;
                                    }

                                    //print($encontrado);
                                    //die();
                                    //print(json_encode($ord));
                                    //die();
                                    //SI NO HEMOS ENCONTRADO ARTICULO-FORMATO BUSCAMOS SOLO POR ARTICULO
                                    $count = 0;
                                    $index_art = -1;
                                    if (!$encontrado) {
                                        //die('find');
                                        foreach ($referencias as $ka => $va) {
                                            if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo'])) {
                                                //$ord['lineas'][$ko]['articulio'] = $va['refCliente'];
                                                //$ord['lineas'][$ko]['descripcion'] = $va['descArticulo'];
                                                $encontrado = true;
                                                $index_art = $ka;
                                                $count++;
                                            }
                                        }
                                        if ($encontrado) {
                                            if ($count == 1) {
                                                $ord['lineas'][$ko]['formato'] = $referencias[$index_art]['idFormato'];
                                                $ord['lineas'][$ko]['referencia'] = (!is_null($referencias[$index_art]['refCliente']) ? $referencias[$index_art]['refCliente'] : '');
                                                $ord['lineas'][$ko]['articulo'] = $referencias[$index_art]['idArticulo'];
                                                $ord['lineas'][$ko]['descripcion'] = $referencias[$index_art]['descFormato'];
                                            }
                                            if ($count > 1) {
                                                $error_lin = 1;
                                                $error = 1;
                                                $ord['estado'] = 'ERROR VALIDACIÓN';
                                                $ord['motivo'] = 'Artículo encontrado repetido y no se puede asignar formato.';
                                                $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                                $ord['lineas'][$ko]['motivo'] = "Artículo encontrado repetido en las referencias del cliente y no se puede asignar formato.";
                                            }
                                        } else {
                                            $error = 1;
                                            $error_lin = 1;
                                            $ord['estado'] = 'ERROR VALIDACIÓN';
                                            $ord['motivo'] = 'Artículo/Referencia no coincide con la lista asociada de artículos del cliente.';
                                            $ord['lineas'][$ko]['estado'] = "ERROR VALIDACIÓN";
                                            $ord['lineas'][$ko]['motivo'] = "Referencia/Artículo no encontrado en los artículos del cliente.";
                                        }
                                    }
                                }
                            }

                            //CALCULAMOS DESCRIPCIÓN DE ARTÍCULOS RECORRIENDO ARTICULOS
                            if ($error_lin == 0) {
                                //print(json_encode($articulos));
                                //die();
                                foreach ($articulos as $ka => $va) {
                                    if (strtoupper($va['idArticulo']) == strtoupper($vo['articulo'])) {
                                        $ord['lineas'][$ko]['desc_articulo'] = $va['descArticulo'];
                                    }
                                }
                            }

                            if ($error_lin == 0) {
                                $ord['lineas'][$ko]['estado'] = "VALIDACIÓN PASADA";
                                $ord['lineas'][$ko]['motivo'] = "";
                            }
                        }
                    }


                    if ($ord['estado'] == '') {
                        $ord['estado'] = 'VALIDACIÓN PASADA';
                        $ord['motivo'] = '';
                    }

                    /* SALTAMOS LA PARTE DE RELLENAR ========================================================== */
                    if ($error == 1 && $ord['id'] != '') {//ESTA PARTE EN OCASIONES SI EL CLIENTE TIENE MUCHAS REFERENCIAS TARDA MUCHO
                        //ACTUALIZAMOS VALORES EN EL ARCHIVADOR ARTÍCULOS-FORMATOS
                        //RECOGEMOS INDICES ARTÍCULOS-FORMATOS
                        //CIFCLIENTE IDCLIENTE ESTADO ACTIVO
                        $param_cond = array(
                            'Operation' => 'And',
                            'Condition' => array(
                                array(
                                    'DBName' => 'IDCLIENTE',
                                    'Value' => array($ord['id'])
                                ),
                                array(
                                    'DBName' => 'ESTADO',
                                    'Value' => array('ACTIVO')
                                )
                            )
                        );
                        //print_r($param_cond);
                        //die();

                        $res_query = $dw->dwGetDialogExpression(DW_FC_ARTICULOS_FORMATOS, DW_SD_ARTICULOS_FORMATOS, $param_cond);
                        //print_r($res_query);
                        //die();

                        $items = array();
                        if ($res_query['status'] == 1) {
                            $response = $dw->dw_get_all_docs_query($res_query['response'], 100);
                            //print_r($response);
                            //die();
                            if ($response['status'] = 200)
                                $items = $response['response']['Items'];
                            //print_r($items);
                            //die();
                        }

                        //print_r($items);
                        //die();
                        if (count($items) > 0) {
                            $artfor = array();
                            foreach ($items as $k => $v) {
                                //print_r($v);
                                //die();

                                $d = array(
                                    'id' => $dw->getValue($v['Fields'], 'IDCLIENTE'),
                                    'cif' => $dw->getValue($v['Fields'], 'CIFCLIENTE'),
                                    'referencia' => $dw->getValue($v['Fields'], 'REFERENCIA'),
                                    'articulo' => $dw->getValue($v['Fields'], 'ARTICULO'),
                                    'formato' => $dw->getValue($v['Fields'], 'FORMATO'),
                                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                                    'precio' => $dw->getValue($v['Fields'], 'PRECIO'),
                                    'descripcion' => $dw->getValue($v['Fields'], 'DESCRIPCION'),
                                    'descripcion_referencia' => $dw->getValue($v['Fields'], 'DESCRIPCION_REFERENCIA'),
                                    'docid' => $dw->getValue($v['Fields'], 'DWDOCID')
                                );
                                $artfor[] = $d;
                            }

                            //print_r($artfor);
                            //die();
                            $dwaf = $this->_compare_articulos_formatos($ord['id'], $ord['cif'], $artfor, $articulos, $referencias);

                            //print(json_encode($dwaf));
                            //die();

                            foreach ($dwaf as $kaf => $vaf) {
                                if ($vaf['estado'] == 'ACTIVO') {
                                    //ELIMINAR DOCUMENTO, NO SE HA ENCONTRADO ENTRE ARTICULOS Y REFERENCIAS
                                }
                                if ($vaf['estado'] == 'INSERT') {
                                    //ELIMINAR DOCUMENTO, NO SE HA ENCONTRADO ENTRE ARTICULOS Y REFERENCIAS
                                    //CIFCLIENTE IDCLIENTE ESTADO ACTIVO FORMATO ARTICULO REFERENCIA
                                    $fields = array(
                                        "Fields" => array()
                                    );
                                    $fields['Fields'][] = array(
                                        "FieldName" => "IDCLIENTE",
                                        "Item" => $ord['id']
                                    );
                                    $fields['Fields'][] = array(
                                        "FieldName" => "CIFCLIENTE",
                                        "Item" => $ord['cif']
                                    );
                                    $fields['Fields'][] = array(
                                        "FieldName" => "ESTADO",
                                        "Item" => 'ACTIVO'
                                    );
                                    $fields['Fields'][] = array(
                                        "FieldName" => "ARTICULO",
                                        "Item" => $vaf['articulo']
                                    );
                                    $fields['Fields'][] = array(
                                        "FieldName" => "REFERENCIA",
                                        "Item" => $vaf['referencia']
                                    );
                                    if ($vaf['formato'] != '') {
                                        $fields['Fields'][] = array(
                                            "FieldName" => "FORMATO",
                                            "Item" => $vaf['formato']
                                        );
                                    }
                                    if (isset($vaf['precio']) && $vaf['precio'] != '') {
                                        $fields['Fields'][] = array(
                                            "FieldName" => "PRECIO",
                                            "Item" => $vaf['precio']
                                        );
                                    }

                                    $data = $dw->dw_add_doc(DW_FC_ARTICULOS_FORMATOS, $fields, '');
                                }
                            }
                        } else {
                            //SI NO HAY ARTÍCULOS-FORMATOS DEL CLIENTE SE AÑADEN TODOS
                            foreach ($articulos as $ka => $va) {
                                //print_r($va);
                                //die();
                                //ELIMINAR DOCUMENTO, NO SE HA ENCONTRADO ENTRE ARTICULOS Y REFERENCIAS
                                //CIFCLIENTE IDCLIENTE ESTADO ACTIVO FORMATO ARTICULO REFERENCIA
                                $fields = array(
                                    "Fields" => array()
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "IDCLIENTE",
                                    "Item" => $ord['id']
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "CIFCLIENTE",
                                    "Item" => $ord['cif']
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "ESTADO",
                                    "Item" => 'ACTIVO'
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "ARTICULO",
                                    "Item" => $va['idArticulo']
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "DESCRIPCION",
                                    "Item" => $va['descArticulo']
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "DESCRIPCION",
                                    "Item" => $va['descArticulo']
                                );
                                if ($va['refCliente'] != '') {
                                    $fields['Fields'][] = array(
                                        "FieldName" => "REFERENCIA",
                                        "Item" => $va['refCliente']
                                    );
                                }

                                //$data = $dw->dw_add_doc(DW_FC_ARTICULOS_FORMATOS, $fields, '');
                            }
                            foreach ($referencias as $kr => $vr) {
                                //ELIMINAR DOCUMENTO, NO SE HA ENCONTRADO ENTRE ARTICULOS Y REFERENCIAS
                                //CIFCLIENTE IDCLIENTE ESTADO ACTIVO FORMATO ARTICULO REFERENCIA
                                //print_r($vr);
                                //die();
                                $fields = array(
                                    "Fields" => array()
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "IDCLIENTE",
                                    "Item" => $ord['id']
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "CIFCLIENTE",
                                    "Item" => $ord['cif']
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "ESTADO",
                                    "Item" => 'ACTIVO'
                                );
                                $fields['Fields'][] = array(
                                    "FieldName" => "ARTICULO",
                                    "Item" => $vr['idArticulo']
                                );
                                if ($vaf['referencia'] != '') {
                                    $fields['Fields'][] = array(
                                        "FieldName" => "REFERENCIA",
                                        "Item" => $vr['refCliente']
                                    );
                                }
                                $fields['Fields'][] = array(
                                    "FieldName" => "FORMATO",
                                    "Item" => $vr['idFormato']
                                );

                                if (!isset($va['descFormato'])) {
                                    $va['descFormato'] = '';
                                }
                                $fields['Fields'][] = array(
                                    "FieldName" => "DESCRIPCION",
                                    "Item" => $va['descFormato']
                                );

                                $fields['Fields'][] = array(
                                    "FieldName" => "PRECIO",
                                    "Item" => $va['precio']
                                );

                                if (!isset($vaf['descRefCliente'])) {
                                    $vaf['descRefCliente'] = '';
                                }
                                if ($vaf['descRefCliente'] != '') {
                                    $fields['Fields'][] = array(
                                        "FieldName" => "DESCRIPCION_REFERENCIA",
                                        "Item" => $va['descRefCliente']
                                    );
                                }

                                $data = $dw->dw_add_doc(DW_FC_ARTICULOS_FORMATOS, $fields, '');
                            }
                        }


                        //AHORA RELLENAMOS TABLA DE REFERENCIAS ARTICULOS FORMATOS QUE HAY EN EL DOCUMENTO PEDIDO
                        //QUE AYUDE A LA INDEXACIÓN DE LOS PEDIDOS ARCHIVADOS
                        $fields_update = array();
                        $rows = array();

                        foreach ($referencias as $kr => $vr) {
                            $row = array(
                                'ColumnValue' => array()
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_ARTICULO1",
                                "Item" => $vr['idArticulo'],
                                "ItemElementName" => "String"
                            );
                            if ($vr['refCliente'] != '') {
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_SU_REFERENCIA1",
                                    "Item" => $vr['refCliente'],
                                    "ItemElementName" => "String"
                                );
                            }
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_FORMATO1",
                                "Item" => $vr['idFormato'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_DESCRIPCION1",
                                "Item" => $vr['descFormato'],
                                "ItemElementName" => "String"
                            );
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_PRECIO1",
                                "Item" => $vr['precio'],
                                "ItemElementName" => "String"
                            );
                            if ($vr['descRefCliente'] != '') {
                                $row['ColumnValue'][] = array(
                                    "FieldName" => "LINEA_DESCRIPCION_REFERENCIA",
                                    "Item" => $vr['descRefCliente'],
                                    "ItemElementName" => "String"
                                );
                            }

                            $rows[] = $row;
                        }

                        //print_r($rows);
                        //die();

                        $fields_update['Field'][] = array(
                            "FieldName" => 'LINEAS_REFERENCIAS',
                            "Item" => array(
                                '$type' => "DocumentIndexFieldTable",
                                "Row" => $rows
                            )
                        );

                        //print_r($fields_update);
                        //die();
                        //print_r($param);
                        //die();

                        $res = $dw->dw_update_doc(DW_FC_CLIENTES, $fields_update, '', $param['docid']);
                    }
                } else {
                    $error = 1;
                    $data['status'] = 0;
                    $ord['estado'] = 'ERROR VALIDACIÓN';
                    $ord['motivo'] = 'No se ha encontrado ficha del cliente con el identificador ' . $ord['cif'];
                }

                if (isset($param['debug']) && $param['debug'] == 'final') {
                    print(json_encode($ord));
                    die();
                }


                //DEPURAMOS LINEAS PARA ACTUALIZAR
                foreach ($lineas as $kl => $vl) {
                    foreach ($vl['ColumnValue'] as $kc => $vc) {
                        unset($lineas[$kl]['ColumnValue'][$kc]['SystemField']);
                        //unset($lineas[$kl]['ColumnValue'][$kc]['IsNull']);
                        unset($lineas[$kl]['ColumnValue'][$kc]['ReadOnly']);
                        unset($lineas[$kl]['ColumnValue'][$kc]['FieldLabel']);
                    }
                }

                //print_r($lineas);
                //print_r($res);
                //print_r($ord);
                //print(json_encode($ord));
                //die();
                //GUARDAMOS RESULTADOS
                $fields_update = array();
                $fields_update['Field'][] = array(
                    "FieldName" => 'CIF',
                    "Item" => $ord['cif']
                );
                if ($ord['id'] != '') {
                    $fields_update['Field'][] = array(
                        "FieldName" => 'COD_CLIENTE',
                        "Item" => $ord['id']
                    );
                }
                if ($ord['moneda'] != '') {
                    $fields_update['Field'][] = array(
                        "FieldName" => 'MONEDA',
                        "Item" => $ord['moneda']
                    );
                }
                if ($ord['idioma'] != '') {
                    $fields_update['Field'][] = array(
                        "FieldName" => 'IDIOMA',
                        "Item" => $ord['idioma']
                    );
                }
                if ($ord['pais'] != '') {
                    $fields_update['Field'][] = array(
                        "FieldName" => 'PAIS',
                        "Item" => $ord['pais']
                    );
                }
                $fields_update['Field'][] = array(
                    "FieldName" => 'ESTADO',
                    "Item" => $ord['estado']
                );
                $fields_update['Field'][] = array(
                    "FieldName" => 'NOMBRE_CLIENTE',
                    "Item" => $ord['nombre']
                );
                $fields_update['Field'][] = array(
                    "FieldName" => 'DIRECCION_DE_ENVIO_ID',
                    "Item" => $envio_default
                );
                $fields_update['Field'][] = array(
                    "FieldName" => 'MOTIVO',
                    "Item" => $ord['motivo']
                );

                $rows = array();
                foreach ($ord['lineas'] as $kl => $vl) {
                    $row = $lineas[$kl];

                    $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_ESTADO', $vl['estado']);
                    $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_MOTIVO', $vl['motivo']);
                    if ($vl['descripcion'] != '') {
                        $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_DESCRIPCION', $vl['descripcion']);
                    }
                    if ($vl['desc_articulo'] != '') {
                        $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_DESCRIPCION_ARTICULO', $vl['desc_articulo']);
                    }
                    if ($vl['articulo'] != '') {
                        $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_ARTICULO', $vl['articulo']);
                    }
                    if ($vl['formato'] != '') {
                        $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_FORMATO', $vl['formato']);
                    }
                    if ($vl['precio_formato'] != '') {
                        $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_PRECIO_FORMATO', $vl['precio_formato']);
                    }
                    $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_SU_REFERENCIA', $vl['referencia']);

                    if (isset($vl['medida']) && $vl['medida'] != '') {
                        $row['ColumnValue'] = $dw->setValue($row['ColumnValue'], 'LINEA_MEDIDA', $vl['medida']);
                    }

                    //print_r($row);
                    //die();

                    $rows[] = $row;
                }

                //print_r($rows);
                //die();

                $fields_update['Field'][] = array(
                    "FieldName" => 'LINEAS',
                    "Item" => array(
                        '$type' => "DocumentIndexFieldTable",
                        "Row" => $rows
                    )
                );

                //print_r($fields_update);
                //die();
                //print_r($param);
                //die();

                $res = $dw->dw_update_doc(DW_FC_CLIENTES, $fields_update, '', $param['docid']);

                //print_r($res);
                //die();

                if ($ord['estado'] == 'VALIDACIÓN PASADA') {
                    //GENERAMOS DOCUMENTO PDF 
                    $filepath_pdf = $this->_create_pdf_ord($ord);
                    //$res=$dw->dw_get_all_index(DW_FC_CLIENTES,$param['docid']); //SOLO CAMPOS
                    $res = $dw->dw_get_doc(DW_FC_CLIENTES, $param['docid']);
                    $sections = $res['response']['Sections'];
                    if (count($sections) == 1) {
                        //ADD SECTION
                        $res = $dw->dw_add_section(DW_FC_CLIENTES, TMP . '/' . $filepath_pdf, $param['docid']);
                        //print('add');
                    } else {
                        //UPDATE SECTION
                        //print('update');
                        $section = $sections[1]['Id'];
                        //print($section);
                        $res = $dw->dw_update_section(DW_FC_CLIENTES, TMP . '/' . $filepath_pdf, $section);
                        //print(json_encode($res));
                        //die();
                    }
                    //print(json_encode($res));
                    //print(json_encode($sections));
                    //die();
                }

                if ($error == 1)
                    $data['status'] = 0;
                $data['msg'] = $ord['estado'] . ' ' . $ord['motivo'];
            }
        }

        return $data;
    }

    private function _updateDocFromExpertis($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Información actualizada correctamente.'
        );

        $str = file_get_contents("php://input");

        if ($data['status'] == 1 && $str == '') {
            $data['status'] = 0;
            $data['msg'] = 'Body not found.';
        } else {
            $param = array_merge($param, json_decode($str, true));
        }

        if ($data['status'] == 1) {
            file_put_contents(HISTORY . "/{$param['doc']}.txt", $str);
        }

        //print_r($param);
        //die();

        if ($data['status'] == 1 && !isset($param['token'])) {
            $data['status'] = 0;
            $data['msg'] = 'Token not found.';
        }

        if ($data['status'] == 1 && $param['token'] != '2VtYWlsYWRkcmVzcyI6ImluZm9AYXVy') {
            $data['status'] = 0;
            $data['msg'] = 'Token not valid.';
        }

        if ($data['status'] == 1 && $param['Eliminar'] == 'N' && !isset($param['Cabecera'])) {
            $data['status'] = 0;
            $data['msg'] = 'Cabecera param not found.';
        }

        if ($data['status'] == 1 && $param['Eliminar'] == 'N' && !isset($param['Lineas'])) {
            $data['status'] = 0;
            $data['msg'] = 'Líneas param not found.';
        }

        if ($data['status'] == 1 && !isset($param['pdf_base64'])) {
            $data['status'] = 0;
            $data['msg'] = 'PDF in base64 not found.';
        }

        if ($data['status'] == 1) {
            //$data['params'] = $param;
            //$data['params'] = $param;
            $dw = new DocuwareApi(DW_URL);
            $res = $dw->loginDW_v2(DW_USER, DW_PASS);
            //print(json_encode($res));
            //die();
            $error = 0;

            if ($res['status'] == 200) {
                //COMPROBAMOS QUE EL PEDIDO NO ESTÉ DUPLICADO
                $param_cond = array(
                    'Operation' => 'And',
                    'Condition' => array(
                        array(
                            'DBName' => 'TIPO_DOCUMENTO',
                            'Value' => array('PEDIDO CLIENTE')
                        ),
                        array(
                            //'DBName' => 'NO_DE_DOCUMENTO',
                            'DBName' => 'NO_DE_DOCUMENTO_INTERNO',
                            'Value' => array($param['doc'])
                        )
                    )
                );
                //print(json_encode($param_cond));
                //die();

                $res_query = $dw->dwGetDialogExpression(DW_FC_CLIENTES, DW_SD_CLIENTES, $param_cond);
                //print(json_encode($res_query));
                //die();

                $docId = 0;
                $response = $dw->dw_get_all_docs_query($res_query['response'], 10);
                //print(json_encode($response));
                //die();

                if ($response['status'] = 200) {
                    $items = $response['response']['Items'];
                    //print_r($items);
                    //die();
                    if (count($items) > 0) {
                        $item = $items[0];
                        //print(json_encode($item));
                        //die();
                        $docId = $items[0]['Id'];
                    }
                }

                //print($docId);
                //die();

                if ($docId > 0) {
                    $eliminar = $param['Eliminar'];
                    $cab = $param['Cabecera'];
                    $lin = $param['Lineas'];
                    $pdf = $param['pdf_base64'];

                    $fields = array();
                    if ($cab != null) {
                        $fields['Field'][] = array(
                            "FieldName" => 'COD_CLIENTE',
                            "Item" => $cab['IDCliente']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'DIRECCION_DE_ENVIO_ID',
                            "Item" => $cab['IDDireccionEnvio']
                        );
                        /*
                          $fields['Field'][] = array(
                          "FieldName" => 'FECHA',
                          "Item" => $cab['FechaPedido']
                          );
                          $fields['Field'][] = array(
                          "FieldName" => 'FECHA_DE_ENTREGA',
                          "Item" => $cab['FechaEntrega']
                          );
                         * 
                         */
                        $fields['Field'][] = array(
                            "FieldName" => 'MONEDA',
                            "Item" => $cab['IDMoneda']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'NOPROFORMA',
                            "Item" => $cab['PedidoCliente']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'RESPONSABLE_EXPERTIS',
                            "Item" => $cab['Responsable']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'OBSERVACIONES',
                            "Item" => $cab['Texto']
                        );
                        $fields['Field'][] = array(
                            "FieldName" => 'CENTRO_DE_GESTION_ID',
                            "Item" => $cab['IDCentroGestion']
                        );

                        $fecha = '';
                        if ($cab['FechaPedido'] != '') {
                            $fecha = explode('T', $cab['FechaPedido'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $cab['FechaPedido']
                            );
                        }

                        $fecha = '';
                        if ($cab['FechaEntrega'] != '') {
                            $fecha = explode('T', $cab['FechaEntrega'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA_DE_ENTREGA',
                                "Item" => $cab['FechaEntrega']
                            );
                        }
                    }

                    if ($eliminar == 'S') {//$param['Eliminar']
                        $fields['Field'][] = array(
                            "FieldName" => 'ESTADO',
                            "Item" => 'Eliminar Expertis'
                        );
                    } else {
                        if ($param['Confirmado'] == 'S') {
                            $fields['Field'][] = array(
                                "FieldName" => 'ESTADO',
                                "Item" => 'Confirmado Expertis'
                            );
                        } else {
                            if ($param['Validado'] == 'S') {
                                $fields['Field'][] = array(
                                    "FieldName" => 'ESTADO',
                                    "Item" => 'Validado Expertis'
                                );
                            }
                        }
                    }

                    $rows = array();
                    foreach ($lin as $k => $v) {
                        $row = array(
                            'ColumnValue' => array()
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_NO_ORDEN",
                            "Item" => $param['doc'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_ARTICULO",
                            "Item" => $v['IDArticulo'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_DESCRIPCION",
                            "Item" => $v['DescArticulo'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CANTIDAD",
                            "Item" => $v['QPedida'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_PRECIO",
                            "Item" => $v['Precio'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_IMPORTE",
                            "Item" => $v['Importe'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CANTIDAD_SERVIDA",
                            "Item" => 0,
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CUENTA_CONTABLE",
                            "Item" => $v['CContable'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CENTRO_DE_GESTION",
                            "Item" => $v['IDCentroGestion'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_ESTADO",
                            "Item" => 'NO PROCESADO',
                            "ItemElementName" => "String"
                        );

                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_IMPORTE1",
                            "Item" => $v['Importe'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_FORMATO",
                            "Item" => $v['IDFormato'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_SU_REFERENCIA",
                            "Item" => $v['RefCliente'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_MEDIDA",
                            "Item" => $v['IDUdMedida'],
                            "ItemElementName" => "String"
                        );

                        $rows[] = $row;
                    }

                    $fields['Field'][] = array(
                        "FieldName" => 'LINEAS',
                        "Item" => array(
                            '$type' => "DocumentIndexFieldTable",
                            "Row" => $rows
                        )
                    );

                    $pdf_path = '';
                    if ($eliminar == 'N' && $pdf != '') {
                        $pdf_path = TMP . "/{$param['doc']}.pdf";
                        file_put_contents($pdf_path, base64_decode($pdf));
                        //print($pdf_path);
                        //die();
                    }

                    //die('kdk3');
                    //print(json_encode($fields));
                    //die();

                    $res = $dw->dw_update_doc(DW_FC_CLIENTES, $fields, $pdf_path, $docId);
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'Documento no encontrado.';
                }
            }
        }

        return $data;
    }

    private function _updateDocComprasFromExpertis($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Información actualizada correctamente.'
        );

        $str = file_get_contents("php://input");

        $tipo_documento = '';

        if ($data['status'] == 1 && ($str == '' || $str == null)) {
            $data['status'] = 0;
            $data['msg'] = 'Body not found.';
        } else {
            try {
                $param = array_merge($param, json_decode($str, true));
            } catch (Exception $e) {
                file_put_contents(HISTORY . "/error-" . date('YmdHis') . ".txt", $str);
                $data['status'] = 0;
                $data['msg'] = 'Error inesperado.';
            }
        }

        if ($data['status'] == 1) {
            file_put_contents(HISTORY . "/{$param['doc']}.txt", $str);
        }

        //print(json_encode($param));
        //die();

        if ($data['status'] == 1 && !isset($param['token'])) {
            $data['status'] = 0;
            $data['msg'] = 'Token not found.';
        }

        if ($data['status'] == 1 && $param['token'] != '2VtYWlsYWRkcmVzcyI6ImluZm9AYXVy') {
            $data['status'] = 0;
            $data['msg'] = 'Token not valid.';
        }

        if ($data['status'] == 1 && !isset($param['doc'])) {
            $data['status'] = 0;
            $data['msg'] = 'Doc param not found.';
        }

        if ($data['status'] == 1 && !isset($param['Cabecera'])) {
            $data['status'] = 0;
            $data['msg'] = 'Cabecera param not found.';
        }

        if ($data['status'] == 1 && !isset($param['Lineas'])) {
            $data['status'] = 0;
            $data['msg'] = 'Líneas param not found.';
        }

        if ($data['status'] == 1 && !isset($param['Eliminar'])) {
            $data['status'] = 0;
            $data['msg'] = 'Eliminar param not found.';
        }

        if ($data['status'] == 1 && !isset($param['pdf_base64'])) {
            $data['status'] = 0;
            $data['msg'] = 'PDF in base64 not found.';
        }

        if ($data['status'] == 1 && (!isset($param['doc']) || $param['doc'] == '')) {
            $data['status'] = 0;
            $data['msg'] = 'doc param not found.';
        } else {
            if ($data['status'] == 1) {
                if (substr($param['doc'], 0, 3) == 'ORD') {
                    $tipo_documento = 'PEDIDO COMPRA';
                    $campo = 'NO_DE_DOCUMENTO';
                    $cgestion = 'IDCentroGestion';
                }
                if (substr($param['doc'], 0, 2) == 'AC') {
                    $tipo_documento = 'ALBARÁN COMPRA';
                    $campo = 'NODOCUMENTO_INTERNO';
                    $cgestion = 'IdCentroGestion';
                }
            }
        }

        if ($data['status'] == 1) {
            //$data['params'] = $param;
            $dw = new DocuwareApi(DW_URL);
            $res = $dw->loginDW_v2(DW_USER, DW_PASS);
            //print(json_encode($res));
            //die();
            $error = 0;

            if ($res['status'] == 200) {
                //COMPROBAMOS QUE EL PEDIDO NO ESTÉ DUPLICADO
                $param_cond = array(
                    'Operation' => 'And',
                    'Condition' => array(
                        array(
                            'DBName' => 'TIPO_DOCUMENTO',
                            'Value' => array($tipo_documento)
                        ),
                        array(
                            'DBName' => $campo,
                            'Value' => array($param['doc'])
                        )
                    )
                );
                //print(json_encode($param_cond));
                //die();

                $res_query = $dw->dwGetDialogExpression(DW_FC_PROVEEDORES, DW_SD_PROVEEDORES, $param_cond);
                //print(json_encode($res_query));
                //die();

                $docId = 0;
                $response = $dw->dw_get_all_docs_query($res_query['response'], 10);
                //print(json_encode($response));
                //die();

                if ($response['status'] = 200) {
                    $items = $response['response']['Items'];
                    //print_r($items);
                    //die();
                    if (count($items) > 0) {
                        $docId = $items[0]['Id'];
                    }
                }

                //print($docId);
                //die();

                if ($docId > 0) {
                    $eliminar = $param['Eliminar'];
                    $cab = $param['Cabecera'];
                    $lin = $param['Lineas'];
                    $pdf = $param['pdf_base64'];

                    $var_doc = array(
                        'FechaPedido' => '',
                    );
                    $fields = array();
                    $fields['Field'][] = array(
                        "FieldName" => 'CIF',
                        "Item" => $cab['CifProveedor']
                    );
                    $fields['Field'][] = array(
                        "FieldName" => 'NOMBRE_PROVEEDOR',
                        "Item" => $cab['DescProveedor']
                    );
                    $fields['Field'][] = array(
                        "FieldName" => 'IMPORTE_TOTAL',
                        "Item" => $cab['ImpTotal']
                    );
                    $fields['Field'][] = array(
                        "FieldName" => 'BASE_1',
                        "Item" => $cab['BaseImponible']
                    );
                    $fields['Field'][] = array(
                        "FieldName" => 'COD__PROVEEDOR',
                        "Item" => $cab['IDProveedor']
                    );

                    if ($tipo_documento == 'PEDIDO COMPRA') {
                        $fecha = '';
                        if ($cab['FechaPedido'] != '') {
                            $fecha = explode('T', $cab['FechaPedido'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $fecha
                            );
                        }
                    }

                    if ($tipo_documento == 'ALBARÁN COMPRA') {
                        $fecha = '';
                        if ($cab['FechaAlbaran'] != '') {
                            $fecha = explode('T', $cab['FechaAlbaran'])[0];
                        }
                        if ($fecha != '') {
                            $fields['Field'][] = array(
                                "FieldName" => 'FECHA',
                                "Item" => $fecha
                            );
                        }

                        $fields['Field'][] = array(
                            "FieldName" => 'NO_DE_DOCUMENTO',
                            "Item" => $cab['SuAlbaran']
                        );
                    }

                    if ($eliminar == 'S') {
                        $fields['Field'][] = array(
                            "FieldName" => 'ESTADO',
                            "Item" => 'Eliminar'
                        );
                    }

                    $rows = array();
                    foreach ($lin as $k => $v) {
                        $row = array(
                            'ColumnValue' => array()
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_NO_ORDEN",
                            "Item" => $param['doc'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_ARTICULO",
                            "Item" => $v['IDArticulo'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_DESCRIPCION",
                            "Item" => $v['DescArticulo'],
                            "ItemElementName" => "String"
                        );

                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_PRECIO",
                            "Item" => $v['Precio'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_IMPORTE",
                            "Item" => $v['Importe'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CANTIDAD_SERVIDA",
                            "Item" => $v['QServida'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CUENTA_CONTABLE",
                            "Item" => $v['CContable'],
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_CENTRO_DE_GESTION",
                            "Item" => $cgestion,
                            "ItemElementName" => "String"
                        );
                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_ESTADO",
                            "Item" => strtoupper($v['EstadoDocuware']),
                            "ItemElementName" => "String"
                        );

                        $row['ColumnValue'][] = array(
                            "FieldName" => "LINEA_TIPO_IVA",
                            "Item" => strtoupper($v['IDTipoIva']),
                            "ItemElementName" => "String"
                        );

                        if ($tipo_documento == 'PEDIDO COMPRA') {
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $v['QPedida'],
                                "ItemElementName" => "String"
                            );
                        }
                        if ($tipo_documento == 'ALBARÁN COMPRA') {
                            $row['ColumnValue'][] = array(
                                "FieldName" => "LINEA_CANTIDAD",
                                "Item" => $v['QServida'],
                                "ItemElementName" => "String"
                            );
                        }

                        $rows[] = $row;
                    }

                    $fields['Field'][] = array(
                        "FieldName" => 'LINEAS',
                        "Item" => array(
                            '$type' => "DocumentIndexFieldTable",
                            "Row" => $rows
                        )
                    );

                    //print(json_encode($fields));
                    //die();

                    $pdf_path = '';
                    if ($eliminar == 'N' && $pdf != '') {
                        $pdf_path = TMP . "/{$param['doc']}.pdf";
                        file_put_contents($pdf_path, base64_decode($pdf));
                        //print($pdf_path);
                        //die();
                    }

                    $res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, $pdf_path, $docId);
                }
            }
        }

        return $data;
    }

    private function _create_order_expertis($param) {
        //https://docuware.pipex.es/api/v1/index.php/?module=docuware&method=traspasar-pedido-expertis&docid=390
        $data = array(
            'status' => 1,
            'msg' => 'Operación realizada correctamente.',
            'ord' => ''
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW_v2(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();
        $error = 0;

        if ($res['status'] == 200) {
            $res = $dw->dw_get_all_index(DW_FC_CLIENTES, $param['docid']);
            //print_r($res);
            //die();

            if ($res['status'] == '200') {
                $fields = $res['response'];

                $tmp_fecha_entrega = '';
                if ($dw->getValue($fields['Field'], 'FECHA_DE_ENTREGA') != '') {
                    $tmp_fecha_entrega = $dw->getDateValueISO($dw->getValue($fields['Field'], 'FECHA_DE_ENTREGA')) . ' 00:00:00';
                }

                $ord = array(
                    'tipo' => $dw->getValue($fields['Field'], 'TIPO_DOCUMENTO'),
                    'ndoc' => $dw->getValue($fields['Field'], 'NO_DE_DOCUMENTO'),
                    'proforma' => $dw->getValue($fields['Field'], 'NOPROFORMA'),
                    'cif' => str_replace(array('_', '-', ' '), '', $dw->getValue($fields['Field'], 'CIF')),
                    'cliente_id' => $dw->getValue($fields['Field'], 'COD_CLIENTE'),
                    'pedido' => $dw->getValue($fields['Field'], 'COD_CLIENTE'),
                    'envio_id' => $dw->getValue($fields['Field'], 'DIRECCION_DE_ENVIO_ID'),
                    'moneda' => $dw->getValue($fields['Field'], 'MONEDA'),
                    'obs' => $dw->getValue($fields['Field'], 'OBSERVACIONES'),
                    'fecha_pedido' => $dw->getDateValueISO($dw->getValue($fields['Field'], 'FECHA')) . ' 00:00:00',
                    'fecha_entrega' => $tmp_fecha_entrega,
                    'responsable' => $dw->getValue($fields['Field'], 'RESPONSABLE_EXPERTIS'),
                    'instrucciones' => $dw->getValue($fields['Field'], 'INSTRUCCIONES'),
                    'email_fecha_pedido' => $dw->getDateValueISO($dw->getValue($fields['Field'], 'FECHA_EMAIL')) . ' 00:00:00',
                    'lineas' => array()
                );

                if (trim($ord['email_fecha_pedido']) == '00:00:00')
                    $ord['email_fecha_pedido'] = '';

                //print_r($ord);
                //die();
                $lineas = $dw->getTable($fields['Field'], 'LINEAS');
                foreach ($lineas as $k => $v) {
                    $lin = array(
                        'referencia' => $dw->getValue($v['ColumnValue'], 'LINEA_SU_REFERENCIA'),
                        'articulo' => $dw->getValue($v['ColumnValue'], 'LINEA_ARTICULO'),
                        'formato' => $dw->getValue($v['ColumnValue'], 'LINEA_FORMATO'),
                        'cantidad' => $dw->getValue($v['ColumnValue'], 'LINEA_CANTIDAD'),
                        'medida' => $dw->getValue($v['ColumnValue'], 'LINEA_MEDIDA'),
                        'desc_articulo' => $dw->getValue($v['ColumnValue'], 'LINEA_DESCRIPCION_PEDIDO'),
                        'desc_referencia' => $dw->getValue($v['ColumnValue'], 'LINEA_DESCRIPCION'),
                        'precio_cliente' => $dw->getValue($v['ColumnValue'], 'LINEA_PRECIO'),
                        'precio_formato' => $dw->getValue($v['ColumnValue'], 'LINEA_PRECIO_FORMATO')
                    );
                    if ($lin['medida'] == '') {
                        $lin['medida'] = 'Mt';
                    }
                    if ($lin['desc_articulo'] == '') {
                        $lin['desc_articulo'] = '- sin descripción -';
                    }
                    if ($lin['desc_referencia'] == '') {
                        $lin['desc_referencia'] = '- sin descripción -';
                    }
                    $ord['lineas'][] = $lin;
                }

                if (isset($param['debug']) && $param['debug'] == 'docuware') {
                    print(json_encode($ord));
                    die();
                }

                $ord_exp = array(
                    'tbPedidoVentaCabecera' => array(
                        'idCliente' => $ord['cliente_id'],
                        'PedidoCliente' => $ord['ndoc'],
                        'NProforma' => $ord['proforma'],
                        'IDDireccionEnvio' => $ord['envio_id'],
                        'FechaPedido' => $ord['fecha_pedido'],
                        'FechaEntrega' => $ord['fecha_entrega'],
                        'IDMoneda' => $ord['moneda'],
                        'Responsable' => $ord['responsable'],
                        'Instrucciones' => $ord['instrucciones'],
                        'IDMoneda' => $ord['moneda'],
                        'Texto' => $ord['obs'],
                        'EmailFechaPedido' => $ord['email_fecha_pedido']
                    ),
                    'tbPedidoVentaLineas' => array(),
                );

                foreach ($ord['lineas'] as $k => $v) {
                    $lin = array(
                        'IdOrdenLinea' => $k + 1,
                        'RefCliente' => $v['referencia'],
                        'IDArticulo' => $v['articulo'],
                        'IdFormato' => $v['formato'],
                        'QPedida' => $v['cantidad'],
                        'IDUdMedida' => $v['medida'],
                        'Precio' => $v['precio_cliente'],
                        'PrecioFormato' => $v['precio_formato'],
                        'DescArticulo' => $v['desc_articulo'],
                        'DescRefcliente' => $v['desc_referencia']
                    );
                    $ord_exp['tbPedidoVentaLineas'][] = $lin;
                }


                if (isset($param['debug']) && $param['debug'] == 'expertis') {
                    print(json_encode($ord_exp));
                    die();
                }

                $exp = new ExpertisApi(EX_URL);
                $res = $exp->loginEXP(EX_USER, EX_PASS);
                if ($res['status'] == 200) {
                    //print(json_encode($ord_exp));
                    //die();
                    $res = $exp->setCreateOrder($ord_exp);
                    //print($res['response']['success'] == '');
                    //print_r($res);
                    //die();

                    if ($res['status'] == 200 && isset($data['ord']) && $res['response']['success'] != '') {
                        //if ($res['response']['message'] == 'Error') {
                        if ($res['response']['success'] == '') {
                            $data['codehttp'] = $res['status'];
                            $data['status'] = 0;
                            $data['msg'] = $res['response']['result'];
                        }
                        if ($res['response']['success'] != '') {
                            $data['ord'] = $res['response']['result'];

                            if (isset($res['response']['pdf']) && $res['response']['pdf'] != '') {
                                $data['pdf'] = $res['response']['pdf'];

                                //DAMOS DE ALTA EL DOCUMENTO PDF COMO UNA NUEVA SECCIÓN
                                //$param['docid']
                                $filepdf = TMP . '/' . $data['ord'] . '.pdf';
                                $data['file'] = $filepdf;
                                file_put_contents($filepdf, base64_decode($data['pdf']));
                                if (file_exists($filepdf)) {
                                    //$data['exist_filepdf'] = true;
                                    //$data['add-sec'] = $dw->dw_add_section(DW_FC_CLIENTES, $filepdf, $param['docid']);
                                    $dw->dw_add_section(DW_FC_CLIENTES, $filepdf, $param['docid']);
                                }
                            }
                        }
                    } else {
                        //die('kdkdk');
                        $data['codehttp'] = $res['status'];
                        $data['status'] = 0;
                        $data['msg'] = $res['response']['message'];

                        //GUARDAMOS RESULTADOS
                        $fields_update = array();
                        $fields_update['Field'][] = array(
                            "FieldName" => 'ESTADO',
                            "Item" => 'ERROR TRASPASO EXPERTIS'
                        );
                        $fields_update['Field'][] = array(
                            "FieldName" => 'MOTIVO',
                            "Item" => $data['msg']
                        );

                        //print_r($fields_update);
                        //die();
                        //print_r($param);
                        //die();

                        $res = $dw->dw_update_doc(DW_FC_CLIENTES, $fields_update, '', $param['docid']);
                    }

                    //print_r($res);
                    //die();
                }
            } else {
                $data['status'] = 0;
                $data['msg'] = 'Documento no encontrado';
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'Login incorrecto.';
        }

        return $data;
    }

    private function _insertDocCompras($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Información actualizada correctamente.'
        );

        $tpl_fra = array(
            'Tipo' => array(
                'TipoDocumento' => ''
            ),
            'tbFacturaCompraCabecera' => array(
                'SuFactura' => '',
                'FechaFactura' => '',
                'SuFechaFactura' => '',
                'CifProveedor' => '',
                'IDProveedor' => '',
                'TipoFactura' => '',
                'FechaVencimiento' => '',
                'IDMoneda' => '',
                'ImpTotal' => '',
                'ImpTotalA' => '',
                'ImpTotalB' => '',
                'ImpLineas' => '',
                'ImpLineasA' => '',
                'ImpLineasB' => '',
                'BaseImponible' => '',
                'BaseImponibleA' => '',
                'BaseImponibleB' => '',
                'ImpIva' => '',
                'ImpIvaA' => '',
                'ImpIvaB' => ''
            ),
            'tbFacturaCompraLinea' => array()
        );

        $tpl_lin = array(
            //'IdOrdenLinea' => 0,
            'IDLineaAlbaran' => '',
            'IDAlbaran' => '',
            'IDLineapedido' => '',
            'IDPedido' => '',
            'IDArticulo' => '',
            'DescArticulo' => '',
            //'IDUdMedida' => '',
            //'IDUdInterna' => '',
            'Cantidad' => '',
            'Precio' => '',
            'PrecioA' => '',
            'PrecioB' => '',
            'DtoProntoPago' => '',
            'Dto1' => '',
            'Dto2' => '',
            'Dto3' => '',
            'Importe' => '',
            'ImporteA' => '',
            'ImporteB' => '',
            'IDCentroGestion' => '',
            'CContable' => '',
            'IDTipoIva' => ''
        );

        $tipo_documento = 'FACTURA_PEDIDO';

        if ($data['status'] == 1 && !isset($param['doc'])) {
            $data['status'] = 0;
            $data['msg'] = 'doc param not found.';
        }

        //print(json_encode($param));
        //die();

        if ($data['status'] == 1) {
            //$data['params'] = $param;
            $dw = new DocuwareApi(DW_URL);
            $res = $dw->loginDW_v2(DW_USER, DW_PASS);
            //print(json_encode($res));
            //die();
            $error = 0;

            if ($res['status'] == 200) {
                $response = $dw->dw_get_all_index(DW_FC_PROVEEDORES, $param['doc']);
                //print(json_encode($response));
                //die();

                $item = array();
                if ($response['status'] = 200) {
                    $item = $response['response'];
                    //print(json_encode($item));
                    //die();
                }

                if (isset($item['Field'])) {

                    $param_api = $tpl_fra;

                    $base1 = 0;
                    $base2 = 0;
                    $base3 = 0;
                    $retencion = 0;
                    $base_final = 0;
                    $iva1 = 0;
                    $iva2 = 0;
                    $iva3 = 0;
                    $iva_final = 0;

                    if ($dw->getValue($item['Field'], 'BASE_1') != '')
                        $base1 = $dw->getValue($item['Field'], 'BASE_1');
                    if ($dw->getValue($item['Field'], 'BASE_2') != '')
                        $base2 = $dw->getValue($item['Field'], 'BASE_2');
                    if ($dw->getValue($item['Field'], 'BASE_3') != '')
                        $base3 = $dw->getValue($item['Field'], 'BASE_3');
                    if ($dw->getValue($item['Field'], 'RETENCION') != '')
                        $retencion = $dw->getValue($item['Field'], 'RETENCION');

                    if ($dw->getValue($item['Field'], 'CUOTA_IVA_1') != '')
                        $iva1 = $dw->getValue($item['Field'], 'CUOTA_IVA_1');
                    if ($dw->getValue($item['Field'], 'CUOTA_IVA_2') != '')
                        $iva2 = $dw->getValue($item['Field'], 'CUOTA_IVA_2');
                    if ($dw->getValue($item['Field'], 'CUOTA_IVA_3') != '')
                        $iva3 = $dw->getValue($item['Field'], 'CUOTA_IVA_3');

                    $base_final = ($base1 + $base2 + $base3) - $retencion;
                    $iva_final = $iva1 + $iva2 + $iva3;

                    $tipo_documento = $dw->getValue($item['Field'], 'TIPO_DOCUMENTO');
                    $param_api['Tipo']['TipoDocumento'] = $dw->getValue($item['Field'], 'TIPO_DOCUMENTO');
                    $param_api['tbFacturaCompraCabecera']['SuFactura'] = $dw->getValue($item['Field'], 'NO_DE_DOCUMENTO');
                    $param_api['tbFacturaCompraCabecera']['FechaFactura'] = $dw->getDateValueISO($dw->getValue($item['Field'], 'FECHA')) . ' 00:00:00';
                    $param_api['tbFacturaCompraCabecera']['SuFechaFactura'] = $dw->getDateValueISO($dw->getValue($item['Field'], 'FECHA')) . ' 00:00:00';
                    $param_api['tbFacturaCompraCabecera']['CifProveedor'] = $dw->getValue($item['Field'], 'CIF');
                    $param_api['tbFacturaCompraCabecera']['IDProveedor'] = $dw->getValue($item['Field'], 'COD__PROVEEDOR');
                    $param_api['tbFacturaCompraCabecera']['TipoFactura'] = $dw->getValue($item['Field'], 'ID_TIPO_FACTURA');
                    $param_api['tbFacturaCompraCabecera']['FechaVencimiento'] = $dw->getDateValueISO($dw->getValue($item['Field'], 'FECHA_VENCIMIENTO')) . ' 00:00:00';
                    $param_api['tbFacturaCompraCabecera']['IDMoneda'] = $dw->getValue($item['Field'], 'MONEDA');
                    $param_api['tbFacturaCompraCabecera']['ImpTotal'] = (float) $dw->getValue($item['Field'], 'IMPORTE_TOTAL');
                    $param_api['tbFacturaCompraCabecera']['ImpTotalA'] = (float) $dw->getValue($item['Field'], 'IMPORTE_TOTAL');
                    $param_api['tbFacturaCompraCabecera']['ImpTotalB'] = (float) $dw->getValue($item['Field'], 'IMPORTE_TOTAL');
                    /*
                      $param_api['tbFacturaCompraCabecera']['ImpLineas'] = (float) $dw->getValue($item['Field'], 'IMPORTE_TOTAL');
                      $param_api['tbFacturaCompraCabecera']['ImpLineasA'] = (float) $dw->getValue($item['Field'], 'IMPORTE_TOTAL');
                      $param_api['tbFacturaCompraCabecera']['ImpLineasB'] = (float) $dw->getValue($item['Field'], 'IMPORTE_TOTAL');
                     * 
                     */
                    $param_api['tbFacturaCompraCabecera']['ImpLineas'] = $base_final;
                    $param_api['tbFacturaCompraCabecera']['ImpLineasA'] = $base_final;
                    $param_api['tbFacturaCompraCabecera']['ImpLineasB'] = $base_final;

                    $param_api['tbFacturaCompraCabecera']['BaseImponible'] = $base_final;
                    $param_api['tbFacturaCompraCabecera']['BaseImponibleA'] = $base_final;
                    $param_api['tbFacturaCompraCabecera']['BaseImponibleB'] = $base_final;
                    $param_api['tbFacturaCompraCabecera']['ImpIva'] = $iva_final;
                    $param_api['tbFacturaCompraCabecera']['ImpIvaA'] = $iva_final;
                    $param_api['tbFacturaCompraCabecera']['ImpIvaB'] = $iva_final;
                    //$param_api['tbFacturaCompraCabecera']['IDAlbaran'] = $dw->getValue($item['Field'], 'DOCID_EXPERTIS');
                    //$param_api['tbFacturaCompraCabecera'][''] = $dw->getValue($item['Field'], '');

                    $rows = $dw->getValue($item['Field'], 'LINEAS');
                    $rows = $rows['Row'];

                    $nlin = 1;
                    foreach ($rows as $k => $v) {
                        $lin = $v['ColumnValue'];
                        $param_lin = $tpl_lin;

                        //$param_lin['IdOrdenLinea'] = $nlin;
                        $param_lin['IDLineaAlbaran'] = '';
                        $param_lin['IDAlbaran'] = $dw->getValue($lin, 'LINEA_DOCID_EXPERTIS');
                        $param_lin['IDLineapedido'] = '';
                        $param_lin['IDPedido'] = '';
                        $param_lin['IDArticulo'] = ($dw->getValue($lin, 'LINEA_ARTICULO') != '') ? $dw->getValue($lin, 'LINEA_ARTICULO') : '00';
                        $param_lin['DescArticulo'] = ($dw->getValue($lin, 'LINEA_DESCRIPCION')) ? $dw->getValue($lin, 'LINEA_DESCRIPCION') : '00';
                        //$param_lin['IDUdMedida'] = '';
                        //$param_lin['IDUdInterna'] = '';
                        $param_lin['Cantidad'] = ($dw->getValue($lin, 'LINEA_CANTIDAD') != '') ? $dw->getValue($lin, 'LINEA_CANTIDAD') : '1';
                        $param_lin['Precio'] = ($dw->getValue($lin, 'LINEA_PRECIO') != '') ? $dw->getValue($lin, 'LINEA_PRECIO') : '0';
                        $param_lin['PrecioA'] = ($dw->getValue($lin, 'LINEA_PRECIO') != '') ? $dw->getValue($lin, 'LINEA_PRECIO') : '0';
                        $param_lin['PrecioB'] = ($dw->getValue($lin, 'LINEA_PRECIO') != '') ? $dw->getValue($lin, 'LINEA_PRECIO') : '0';
                        $param_lin['DtoProntoPago'] = '0';
                        $param_lin['Dto1'] = '0';
                        $param_lin['Dto2'] = '0';
                        $param_lin['Dto3'] = '0';
                        $param_lin['Importe'] = ($dw->getValue($lin, 'LINEA_IMPORTE') != '') ? $dw->getValue($lin, 'LINEA_IMPORTE') : '0';
                        $param_lin['ImporteA'] = ($dw->getValue($lin, 'LINEA_IMPORTE') != '') ? $dw->getValue($lin, 'LINEA_IMPORTE') : '0';
                        $param_lin['ImporteB'] = ($dw->getValue($lin, 'LINEA_IMPORTE') != '') ? $dw->getValue($lin, 'LINEA_IMPORTE') : '0';
                        $param_lin['IDCentroGestion'] = $dw->getValue($lin, 'LINEA_CENTRO_DE_GESTION');
                        $param_lin['CContable'] = $dw->getValue($lin, 'LINEA_CUENTA_CONTABLE');
                        $param_lin['IDTipoIva'] = $dw->getValue($lin, 'LINEA_TIPO_IVA');

                        if ($tipo_documento == 'FACTURA RESTO') {
                            $param_lin['Cantidad'] = '1';
                            $param_lin['Precio'] = $param_api['tbFacturaCompraCabecera']['BaseImponible'];
                            $param_lin['PrecioA'] = $param_api['tbFacturaCompraCabecera']['BaseImponible'];
                            $param_lin['PrecioB'] = $param_api['tbFacturaCompraCabecera']['BaseImponible'];
                            /*
                              $param_lin['Importe'] = $param_api['tbFacturaCompraCabecera']['ImpTotal'];
                              $param_lin['ImporteA'] = $param_api['tbFacturaCompraCabecera']['ImpTotal'];
                              $param_lin['ImporteB'] = $param_api['tbFacturaCompraCabecera']['ImpTotal'];
                             * 
                             */
                            $param_lin['Importe'] = $param_api['tbFacturaCompraCabecera']['BaseImponible'];
                            $param_lin['ImporteA'] = $param_api['tbFacturaCompraCabecera']['BaseImponible'];
                            $param_lin['ImporteB'] = $param_api['tbFacturaCompraCabecera']['BaseImponible'];
                        }

                        //print(json_encode($param_lin));
                        //print(json_encode($lin));
                        //die();

                        $param_api['tbFacturaCompraLinea'][] = $param_lin;
                        $nlin++;
                    }

                    if (isset($param['debug']) && $param['debug'] == 'expertis') {
                        print(json_encode($param_api));
                        die();
                    }

                    //print(json_encode($param_api));
                    //print(json_encode($rows));
                    //die();
                    //print(json_encode($fields));
                    //die();
                    //$res = $dw->dw_update_doc(DW_FC_PROVEEDORES, $fields, $pdf_path, $docId);
                    //HACEMOS LOGIN EN EXPERTIS Y REGISTRAMOS FACTURA

                    $exp = new ExpertisApi(EX_URL);
                    //$res = $exp->loginEXP(EX_USER, EX_PASS, 'T');
                    $res = $exp->loginEXP(EX_USER, EX_PASS, 'P');
                    if ($res['status'] == 200) {
                        //print(json_encode($ord_exp));
                        //die();
                        $res = $exp->setCreateInvoice($param_api);

                        //print_r($res);
                        //die();

                        if ($res['status'] == 200) {
                            //if ($res['response']['message'] == 'Error') {
                            //{"result": "ADQ-24-5465","success": true,"message": ""}

                            if (isset($res['response']['success']) && $res['response']['success'] == true) {
                                $data['status'] = 1;
                                $data['ADQ'] = $res['response']['result'];
                                $data['msg'] = $res['response']['message'];
                            }

                            if (isset($res['response']['success']) && $res['response']['success'] == false) {
                                //ERROR EN LA CONTABILIZACIÓN
                                $data['status'] = 0;
                                $data['msg'] = $res['response']['result'];
                            }
                        } else {
                            $data['status'] = 0;
                            $data['msg'] = $res['response'];
                        }

                        //print_r($res);
                        //die();
                    }
                }
            }
        }

        return $data;
    }

    private function _get_list_empleados($param) {
        //https://docuware.pipex.es/api/v1/index.php/?module=docuware&method=get-empleados
        $data = array(
            'status' => 1,
            'codehttp' => 0,
            'msg' => 'Operación realizada correctamente.',
            'items' => ''
        );

        $exp = new ExpertisApi(EX_URL);
        $res = $exp->loginEXP(EX_USER, EX_PASS);
        if ($res['status'] == 200) {
            //print(json_encode($ord_exp));
            //die();
            $res = $exp->getEmpleados();
            $data['codehttp'] = $res['status'];

            if ($res['status'] == 200) {
                if ($res['response']['success']) {
                    $data['items'] = $res['response']['result']['tbMaestroOperario'];

                    foreach ($data['items'] as $k => $v) {
                        unset($data['items'][$k]['fechaAlta']);
                        unset($data['items'][$k]['direccion']);
                        unset($data['items'][$k]['codPostal']);
                        unset($data['items'][$k]['poblacion']);
                        unset($data['items'][$k]['provincia']);
                        unset($data['items'][$k]['idPais']);
                        unset($data['items'][$k]['telefono']);
                        unset($data['items'][$k]['fax']);
                        unset($data['items'][$k]['idCategoria']);
                        unset($data['items'][$k]['horarioInicio']);
                        unset($data['items'][$k]['horarioFin']);
                        unset($data['items'][$k]['fechaCreacionAudi']);
                        unset($data['items'][$k]['fechaModificacionAudi']);
                        unset($data['items'][$k]['curriculum']);
                        unset($data['items'][$k]['idFormaPago']);
                        unset($data['items'][$k]['idBanco']);
                        unset($data['items'][$k]['sucursal']);
                        unset($data['items'][$k]['digitoControl']);
                        unset($data['items'][$k]['nCuenta']);
                        unset($data['items'][$k]['externo']);
                        unset($data['items'][$k]['idProveedor']);
                        unset($data['items'][$k]['facturacionObras']);
                        unset($data['items'][$k]['permisoGD']);
                        unset($data['items'][$k]['tasaHorariaA']);
                        unset($data['items'][$k]['tasaHorariaB']);
                        unset($data['items'][$k]['idUsuario']);
                        unset($data['items'][$k]['estadoMaquina']);
                        unset($data['items'][$k]['programable']);
                        unset($data['items'][$k]['progVisible']);
                        unset($data['items'][$k]['fechaNacimiento']);
                        unset($data['items'][$k]['lugarNacimiento']);
                        unset($data['items'][$k]['idPaisNacimiento']);
                        unset($data['items'][$k]['sexo']);
                        unset($data['items'][$k]['idEstadoCivil']);
                        unset($data['items'][$k]['nHijos']);
                        unset($data['items'][$k]['telefono2']);
                        unset($data['items'][$k]['telefono3']);
                        unset($data['items'][$k]['grado']);
                        unset($data['items'][$k]['diagnosticoDiscapacidad']);
                        unset($data['items'][$k]['certificado']);
                        unset($data['items'][$k]['fechaCertificado']);
                        unset($data['items'][$k]['fechaRenovacionCertificado']);
                        unset($data['items'][$k]['carnetConducir']);
                        unset($data['items'][$k]['vehiculoPropio']);
                        unset($data['items'][$k]['vehiculoAdaptado']);
                        unset($data['items'][$k]['idCarnet']);
                        unset($data['items'][$k]['idNivelAcademico']);
                        unset($data['items'][$k]['idColectivo']);
                        unset($data['items'][$k]['idEmpresa']);
                        unset($data['items'][$k]['idSeccion']);
                        unset($data['items'][$k]['idSituacion']);
                        unset($data['items'][$k]['idSituacionDetalle']);
                        unset($data['items'][$k]['prefijoNSS']);
                        unset($data['items'][$k]['nss']);
                        unset($data['items'][$k]['sufijoNSS']);
                        unset($data['items'][$k]['horasReferencia']);
                        unset($data['items'][$k]['horasPendientes']);
                        unset($data['items'][$k]['idDependeDe']);
                        unset($data['items'][$k]['idContador']);
                        unset($data['items'][$k]['idCalendario']);
                        unset($data['items'][$k]['foto']);
                        unset($data['items'][$k]['periodoObjetivos']);
                        unset($data['items'][$k]['tipoDocIdentidad']);
                        unset($data['items'][$k]['vendedor']);
                        unset($data['items'][$k]['bonosProduccion']);
                        unset($data['items'][$k]['bonosMantenimiento']);
                        unset($data['items'][$k]['bonosProyectos']);
                        unset($data['items'][$k]['idDiscapacidad']);
                        unset($data['items'][$k]['vendedorTPV']);
                        unset($data['items'][$k]['tarjetaControl']);
                        unset($data['items'][$k]['pinControl']);
                        unset($data['items'][$k]['texto']);
                        unset($data['items'][$k]['idContadorTicket']);
                        unset($data['items'][$k]['idContadorFactura']);
                        unset($data['items'][$k]['codigoIBAN']);
                        unset($data['items'][$k]['swift']);
                        unset($data['items'][$k]['asesor']);
                        unset($data['items'][$k]['carneBasico']);
                        unset($data['items'][$k]['carneCualif']);
                        unset($data['items'][$k]['carneFumig']);
                        unset($data['items'][$k]['carnePiloto']);
                        unset($data['items'][$k]['idGestionPlagas']);
                        unset($data['items'][$k]['numeroIdentificacionAsesor']);
                        unset($data['items'][$k]['numeroIncripcionROPO']);
                    }
                }
            } else {
                $data['status'] = 0;
                $data['msg'] = $res['response'];
            }

            //print_r($res);
            //die();
        }

        return $data;
    }

    private function _test($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Prueba de conexión realizada correctamente'
        );

        return $data;
    }

    private function _compare_articulos_formatos($idcli, $cifcli, $dwaf, $expart, $expref) {
        $data = array(
            'status' => 1
        );

        $data = array(
            'dw' => $dwaf,
            'art' => $expart,
            'ref' => $expref
        );

        //print_r($dwaf);
        //print_r($expart);
        //print_r($expref);
        //print(json_encode($data));
        //die();

        foreach ($expart as $ka => $va) {
            $encontrado = false;
            foreach ($dwaf as $kd => $vd) {
                if ($va['idArticulo'] == $vd['articulo']) {
                    $encontrado = true;
                    $dwaf[$kd]['estado'] == 'ENCONTRADO';
                }
            }
            if (!$encontrado) {
                $dwaf[] = array(
                    'id' => $idcli,
                    'cif' => $cifcli,
                    'referencia' => '',
                    'articulo' => $va['idArticulo'],
                    'formato' => '',
                    'estado' => 'INSERT',
                    'descripcion' => $va['descArticulo'],
                    'docid' => 0
                );
            }
        }

        foreach ($expref as $kr => $vr) {
            $encontrado = false;
            foreach ($dwaf as $kd => $vd) {
                if ($vr['idArticulo'] == $vd['articulo'] && $vr['idFormato'] == $vd['formato'] && $vr['refCliente'] == $vd['referencia']) {
                    $encontrado = true;
                    $dwaf[$kd]['estado'] = 'ENCONTRADO';
                }
            }
            if (!$encontrado) {
                //print(json_encode($vr));
                //die();

                $dwaf[] = array(
                    'id' => $idcli,
                    'cif' => $cifcli,
                    'referencia' => $vr['refCliente'],
                    'articulo' => $vr['idArticulo'],
                    'formato' => $vr['idFormato'],
                    'estado' => 'INSERT',
                    'descripcion' => $vr['descFormato'],
                    'descripcion_referencia' => $vr['descRefCliente'],
                    'docid' => 0
                );
            }
        }

        //print(json_encode($dwaf));
        //die();

        return $dwaf;
    }

    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN PEDIDO">
    //
    private function _create_pdf_ord($param) {
        $this->page = 1;

        //$clidata = $this->app->clidata;
        //print(json_encode($param));
        //die();

        $cli = $param;

        $data['cliName'] = $cli['nombre'];
        $data['cliId'] = $cli['id'];
        $data['cliCif'] = $cli['cif'];
        $data['cliDir'] = '';
        //$data['cliDir'] = "{$cli['direccion']} {$cli['poblacion']} {$cli['codPostal']} {$cli['descpais']}";
        //$data['cliDirEnvio'] = $clidata['clienteDireccion'];
        //$data['cliArt'] = $clidata['articuloCliente'];
        //$data['cliRef'] = $clidata['articuloFormatoCliente'];

        $data['fecha'] = $cli['fecha'];
        $data['fecha_entrega'] = $cli['fecha_entrega'];
        $data['obs'] = $cli['obs'];
        //$data['direnvio'] = $cli['direnvio'];
        $data['direnvio'] = '';
        $data['items'] = $cli['lineas'];

        $h = 3;
        $c1 = 30;
        $c2 = 25;
        $c3 = 40;
        $c4 = 50;
        $c5 = 15;
        $c6 = 15;
        $c7 = 15;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_ord_header($pdf, $data);

        $pdf->SetFont('Arial', '', 6);
        $importe_total = 0;
        foreach ($data['items'] as $k => $v) {
            //print_r($v);
            //die();
            //$item = explode('|', $v);

            $cantidad = '';
            if ($v['cantidad'] != '') {
                $cantidad = number_format($v['cantidad'], 2, ', ', '');
            }
            $precio = '';
            if ($v['precio'] != '' && is_numeric($v['precio'])) {
                $precio = number_format($v['precio'], 4, ', ', '');
            }
            $importe = '';
            if ($v['cantidad'] != '' && is_numeric($v['cantidad']) && $v['precio'] != '' && is_numeric($v['precio'])) {
                $tmp = $v['cantidad'] * $v['precio'];
                $importe_total += $tmp;
                $importe = number_format($tmp, 2, ', ', '');
            }

            $pdf->Cell($c1, $h, $v['referencia'], 0, 0, 'L', false);

            $pdf->Cell($c2, $h, $v['articulo'], 0, 0, 'L', false);
            $pdf->Cell($c3, $h, $v['formato'], 0, 0, 'L', false);
            //$pdf->SetFont('Arial', '', 7);

            $desc = $v['desc_articulo'];
            if (strlen($desc) > 35) {
                $desc = substr($desc, 0, 35) . '...';
            }
            $pdf->Cell($c4, $h, mb_convert_encoding($desc, CODE_ISO, mb_detect_encoding($desc)), 0, 0, 'L', false);
            //$pdf->SetFont('Arial', '', 7);
            $pdf->Cell($c5, $h, $cantidad, 0, 0, 'R', false);
            $pdf->Cell($c6, $h, $precio, 0, 0, 'R', false);
            $pdf->Cell($c7, $h, $importe, 0, 1, 'R', false);
            $pdf->Ln(1);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

            if (($k + 1) % 47 == 0) {
                $this->_ord_page($pdf);
                //$this->_fra_footer_detalle($pdf);
                $pdf->AddPage();
                $this->_ord_header($pdf, $data);
            }
        }

        $pdf->Cell($c1 + $c2 + $c3 + $c4 + $c5 + $c6, $h, '', 0, 0, 'R', false);
        $pdf->Cell($c7, $h, number_format($importe_total, 2, ', ', ''), 0, 1, 'R', false);

        $this->_ord_page($pdf);
        //$this->_fra_footer_detalle($pdf, 'F');

        $filepdf = 'ORD-' . $data['cliId'] . '.pdf';

        $pdf->Output(TMP . '/' . $filepdf, 'F');

        return $filepdf;
    }

    private function _ord_header($pdf, $data) {
        $h = 4;
        $c1 = 30;
        $c2 = 25;
        $c3 = 40;
        $c4 = 50;
        $c5 = 15;
        $c6 = 15;
        $c7 = 15;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->Image(BASE . '/img/logo-pipex.jpg', 20, 5, 20, 0, 'JPEG');

        $pdf->SetLeftMargin(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(10, 10);
        $pdf->Cell(190, 7, "PEDIDO CLIENTE", 0, 1, 'C', false);

        $pdf->Rect(10, 30, 90, 50);
        $pdf->SetFont('Arial', '', 12);
        $pdf->setXY(10, 30);
        $pdf->Cell(90, 10, "ID Cliente: {$data['cliId']}", 1, 1, 'C', false);
        $pdf->Cell(90, 10, "CIF Cliente: {$data['cliCif']}", 1, 1, 'C', false);
        $pdf->Cell(90, 10, "Fecha: {$data['fecha']}", 1, 1, 'C', false);
        $pdf->Cell(90, 10, "Fecha Entrega: {$data['fecha_entrega']}", 1, 1, 'C', false);

        $pdf->Rect(110, 30, 90, 50);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->setXY(110, 40);
        $pdf->SetLeftMargin(110);
        $pdf->Cell(90, 8, "{$data['cliName']}", 0, 1, 'C', false);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 4, mb_convert_encoding("{$data['cliDir']}", CODE_ISO, mb_detect_encoding($data['cliDir'])), 0, 1, 'C', false);
        $pdf->SetFont('Arial', 'B', 12);
        //$pdf->Cell(90, 8, mb_convert_encoding("Dir.Envío", CODE_ISO, mb_detect_encoding("Dir.Envío")), 0, 1, 'C', false);
        //$pdf->SetFont('Arial', '', 10);
        //$pdf->Cell(90, 4, mb_convert_encoding("{$data['direnvio']}", CODE_ISO, mb_detect_encoding($data['direnvio'])), 0, 1, 'C', false);
        //$pdf->Ln(2);
        $pdf->SetFont('Arial', '', 9);
        $pdf->setXY(10, 85);
        $pdf->SetLeftMargin(10);
        $pdf->Cell(90, 5, "Observaciones:", 0, 1, 'L', false);
        $pdf->MultiCell(190, 5, mb_convert_encoding($data['obs'], CODE_ISO, mb_detect_encoding($data['obs'])), 1, 'L', false);

        $pdf->SetLeftMargin(10);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 7);

        $pdf->Cell($c1, $h, 'Referencia', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, mb_convert_encoding('Artículo', CODE_ISO, mb_detect_encoding('Artículo')), 1, 0, 'C', false);
        $pdf->Cell($c3, $h, 'Formato', 1, 0, 'C', false);
        $pdf->Cell($c4, $h, mb_convert_encoding('Descripción', CODE_ISO, mb_detect_encoding('Descripción')), 1, 0, 'C', false);
        $pdf->Cell($c5, $h, 'Qty', 1, 0, 'R', false);
        $pdf->Cell($c6, $h, 'Precio', 1, 0, 'R', false);
        $pdf->Cell($c7, $h, 'Importe', 1, 1, 'R', false);
        //$pdf->Cell($c6, $h, '', 1, 1, 'L', false);

        $pdf->SetLeftMargin(10);
    }

    private function _ord_page($pdf) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->setXY(10, 270);
        $pdf->Cell(50, 4, mb_convert_encoding("Página: {$this->page}", CODE_ISO, mb_detect_encoding("Página: {$this->page}")), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _ord_footer_detalle($pdf, $type = 'P') {
        $s = "Suma y sigue...:    " . number_format($this->total, 2, ', ', '') . ' USD';
        if ($type == 'F')
            $s = "Total...:    " . number_format($this->total, 2, ', ', '') . ' USD';

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setXY(140, 270);
        $pdf->Cell(60, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    private function _ord_footer($pdf) {
        $s = "Total...:    " . number_format($this->total, 2, ', ', '') . ' USD';

        $pdf->SetFont('Arial', 'B', 14);
        //$pdf->setXY(140, 270);
        $pdf->Ln(7);
        $pdf->Cell(190, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    //
    //</editor-fold>

    function rndString($length = 10, $uc = TRUE, $n = TRUE, $sc = FALSE) {
        $source = 'abcdefghijklmnopqrstuvwxyz';
        if ($uc == 1)
            $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($n == 1)
            $source .= '1234567890';
        if ($sc == 1)
            $source .= '|@#~$%()=^*+[]{}-_';
        if ($length > 0) {
            $rstr = "";
            $source = str_split($source, 1);
            for ($i = 1;
                    $i <= $length;
                    $i++) {
                $num = rand(1, count($source));
                $rstr .= $source[$num - 1];
            }
        }
        return $rstr;
    }
}

?>
