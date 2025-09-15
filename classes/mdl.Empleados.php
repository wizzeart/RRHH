<?php

use setasign\Fpdi\Fpdi;

class Empleado {

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

            /*             * ************************************************************************************************ */
            case 'add-justificante':
                $data = $this->_add_justificante($param);
                print(json_encode($data));
                break;
            case 'solicitud-firma':
                $data = $this->_solicitud_firma($param);
                print(json_encode($data));
                break;
            case 'list-documentos':
                $data = $this->_list_documentos($param);
                print(json_encode($data));
                break;
            case 'show-docs-obra-rel':
                $data = $this->_list_docs_obra_rel($param);
                print(json_encode($data));
                break;
            case 'form':
                $data = $this->_get_form($param);
                print($data);
                break;
            case 'save-nota-gastos':
                $data = $this->_create_doc_nota_gastos($param);
                print(json_encode($data));
                break;
            case 'remove-entrada-gastos':
                $data = $this->_remove_entrada_gastos($param);
                print(json_encode($data));
                break;
            case 'load-entradas-gastos':
                $data = $this->_load_entradas_gastos();
                print(json_encode($data));
                break;
            case 'add-entrada-gastos':
                $data = $this->_add_entrada_gastos($param);
                print(json_encode($data));
                break;
            case 'create-doc-efectivo':
                $data = $this->_create_doc_efectivo($param);
                print(json_encode($data));
                break;
            case 'create-doc-ausencia':
                $data = $this->_create_doc_ausencia($param);
                print(json_encode($data));
                break;
            case 'list-rechazo':
                $data = $this->_list_rechazo($param);
                print(json_encode($data));
                break;
            case 'send-gastos-registro':
                $data = $this->_send_gastos_registro($param);
                print(json_encode($data));
                break;
            case 'rechazar':
                $data = $this->_rechazar($param);
                print(json_encode($data));
                break;
            case 'aprobar':
                $data = $this->_aprobar($param);
                print(json_encode($data));
                break;
            case 'send-aprobacion':
                $data = $this->_send_aprobacion($param);
                print(json_encode($data));
                break;
            case 'add-comunicacion':
                $data = $this->_add_comunicacion($param);
                print(json_encode($data));
                break;
            case 'add-ausencia':
                $data = $this->_add_ausencia($param);
                print(json_encode($data));
                break;
            case 'add-parte':
                $data = $this->_add_parte($param);
                print(json_encode($data));
                break;
            case 'fin-obra':
                $data = $this->_fin_obra($param);
                print(json_encode($data));
                break;
            case 'add-formacion':
                $data = $this->_add_formacion($param);
                print(json_encode($data));
                break;
            case 'add-nomina':
                $data = $this->_add_nomina($param);
                print(json_encode($data));
                break;
            case 'add-gastos':
                $data = $this->_add_gastos($param);
                print(json_encode($data));
                break;
            case 'add-ticket':
                $data = $this->_add_ticket($param);
                print(json_encode($data));
                break;
            case 'list-comunicaciones':
                $data = $this->_list_comunicaciones($param);
                print(json_encode($data));
                break;
            case 'list-ausencias':
                $data = $this->_list_ausencias($param);
                print(json_encode($data));
                break;
            case 'list-bajasmedicas':
                $data = $this->_list_bajasmedicas($param);
                print(json_encode($data));
                break;
            case 'list-procedimientos':
                $data = $this->_list_procedimientos($param);
                print(json_encode($data));
                break;
            case 'list-plantillas':
                $data = $this->_list_plantillas($param);
                print(json_encode($data));
                break;
            case 'list-fichas-tecnicas':
                $data = $this->_list_fichas_tecnicas($param);
                print(json_encode($data));
                break;
            case 'list-fichas-seguridad':
                $data = $this->_list_fichas_seguridad($param);
                print(json_encode($data));
                break;
            case 'list-partes':
                $data = $this->_list_partes($param);
                print(json_encode($data));
                break;
            case 'list-gastos':
                $data = $this->_list_gastos($param);
                print(json_encode($data));
                break;
            case 'list-tickets':
                $data = $this->_list_tickets($param);
                print(json_encode($data));
                break;
            case 'list-gastos-tickets':
                $data = $this->_list_gastos_tickets($param);
                print(json_encode($data));
                break;
            case 'list-recursos':
                $data = $this->_list_recursos($param);
                print(json_encode($data));
                break;
            case 'list-justificantes':
                $data = $this->_list_justificantes($param);
                print(json_encode($data));
                break;
            case 'list-solicitudes':
                $data = $this->_list_solicitudes($param);
                print(json_encode($data));
                break;
            case 'list-contratos':
                $data = $this->_list_contratos($param);
                print(json_encode($data));
                break;
            case 'list-prl':
                $data = $this->_list_prl($param);
                print(json_encode($data));
                break;
            case 'list-documentacion-general':
                $data = $this->_list_documentacion_general($param);
                print(json_encode($data));
                break;
            case 'list-epis':
                $data = $this->_list_epis($param);
                print(json_encode($data));
                break;
            case 'list-proteccion-datos':
                $data = $this->_list_proteccion_datos($param);
                print(json_encode($data));
                break;
            case 'list-otros':
                $data = $this->_list_otros($param);
                print(json_encode($data));
                break;
            case 'list-nominas':
                $data = $this->_list_nominas($param);
                print(json_encode($data));
                break;
            case 'list-formacion':
                $data = $this->_list_formacion($param);
                print(json_encode($data));
                break;
            case 'dl-doc':
            case 'dl-ticket':
                $data = $this->_dl_doc($param);
                print(json_encode($data));
                break;
            case 'dl-doc-pdf':
                $data = $this->_dl_doc_pdf($param);
                print(json_encode($data));
                break;
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'list-dw':
                $data = $this->_list_dw($param);
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
            case 'save-doc':
                $this->_save_doc($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'partes-trabajo':
                $data = array();
                $page['title'] = 'Ficha del Empleado';
                $page['subtitle'] = 'Mantenimiento de Empleados';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Empleado';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select a.*"
                            . ",date_format(a.xdatealta,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                            . ",date_format(a.xult_acceso,'%d/%m/%Y %H:%i:%s') as xacceso_format"
                            . " from dw_empleados a"
                            . " where a.xempleado_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Empleado: ' . $row['xempleado_id'] . ' - ' . $row['xempleado'];
                    }
                } else {
                    $data['xactivo'] = 'S';
                }
                break;
            case 'list-partes-trabajo':
                $data = array();
                $page['title'] = 'Partes de trabajo';
                $page['subtitle'] = 'Listado de partes de trabajo de personas';
                break;

            /*             * *********************************************************************************************** */
            case 'mis-datos':
                $data = array();
                $page['title'] = 'Tus datos';
                $page['subtitle'] = 'Datos del empleado';

                $data = $this->_mis_datos($param);
                break;
            case 'list-documentos-emp':
                $data = array();
                $page['title'] = 'Documentos para Empleados';
                $page['subtitle'] = 'Listado de documentos';
                break;
            case 'list-prl-emp':
                $data = array();
                $page['title'] = 'PRL';
                $page['subtitle'] = 'Listado PRL';
                break;
            case 'list-epis-emp':
                $data = array();
                $page['title'] = 'EPIS';
                $page['subtitle'] = 'Listado EPIS';
                break;
            case 'list-otros-emp':
                $data = array();
                $page['title'] = 'Otros';
                $page['subtitle'] = 'Listado Otros';
                break;
            case 'list-documentacion-general-emp':
                $data = array();
                $page['title'] = 'Documentación General';
                $page['subtitle'] = 'Listado Documentación General';
                break;
            case 'list-proteccion-datos-emp':
                $data = array();
                $page['title'] = 'Protección de Datos';
                $page['subtitle'] = 'Listado Protección de Datos';
                break;
            case 'list-comunicaciones-emp':
                $data = array();
                $page['title'] = 'Comunicaciones';
                $page['subtitle'] = 'Listado de comunicaciones';
                break;
            case 'list-ausencias-emp':
                $data = array();
                $page['title'] = 'Solicitud de permiso';
                $page['subtitle'] = 'Listado de solicitudes';
                break;
            case 'list-bajamedica-emp':
                $data = array();
                $page['title'] = 'Solicitud de baja médica';
                $page['subtitle'] = 'Listado de solicitudes';
                break;
            case 'list-partes-emp':
                $data = array();
                $page['title'] = 'Partes de Trabajo';
                $page['subtitle'] = 'Listado de partes de trabajo';
                break;
            case 'list-contratos-emp':
                $data = array();
                $page['title'] = 'Empleado/Contratos';
                $page['subtitle'] = 'Listado de contratos';
                break;
            case 'list-nominas-emp':
                $data = array();
                $page['title'] = 'Empleado/Nóminas';
                $page['subtitle'] = 'Listado de nóminas';

                $month = date('m') + 1;
                //print($month);
                //die();
                //die($month);
                $tmp = strtotime(date('Y') . '-' . $month . '-01') - (1 * 24 * 3600);

                $data['fecha_ini_month'] = date('Y-m-01');
                $data['fecha_fin_month'] = date('Y-m-d', $tmp);
                //print($fecha_ini_month);
                //die();
                break;
            case 'list-plantillas-emp':
                $data = array();
                $page['title'] = 'Plantillas';
                $page['subtitle'] = 'Listado de plantillas';
                break;
            case 'list-procedimientos-emp':
                $data = array();
                $page['title'] = 'Procedimientos';
                $page['subtitle'] = 'Listado de procedimientos';
                break;
            case 'list-formacion-emp':
                $data = array();
                $page['title'] = 'Formación';
                $page['subtitle'] = 'Listado de formación';
                break;
            case 'list-empleados':
                $data = array();
                $page['title'] = 'Empleados';
                $page['subtitle'] = 'Listado de empleados asociados al portal';
                break;
            case 'empleados':
                $data = array();
                $page['title'] = 'Ficha del Empleado';
                $page['subtitle'] = 'Mantenimiento de Empleados';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Empleado';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select a.*"
                            . ",date_format(a.xdatealta,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                            . ",date_format(a.xult_acceso,'%d/%m/%Y %H:%i:%s') as xacceso_format"
                            . " from dw_empleados a"
                            . " where a.xempleado_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Empleado: ' . $row['xempleado_id'] . ' - ' . $row['xempleado'];
                    }
                } else {
                    $data['xactivo'] = 'S';
                }

                break;
        }
    }

    private function _dl_doc_email($param) {
        $data = array(
            'status' => 200
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            //$data = $dw->dw_download_document(DW_FC_PROVEEDORES, $param['doc-id'], $param['doc-ext']);
            //DOCUMENTO COMUNICACIÓN HAY QUE MARCAR COMO LEÍDO
            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'SOLICITADO POR EMAIL'
            );

            $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['doc-id']);

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _dl_doc($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            $archivador = DW_FC_EMPLEADOS;
            $res = $dw->dw_get_sections($archivador, $param['doc-id']);
            //print_r($res);
            //die();
            $data = $dw->dw_download_section($archivador, $res['items'][0]['id'], 'PDF');
            //$data = $dw->dw_download_document($archivador, $param['doc-id'], $param['doc-ext'], 'PDF');
            //print_r($data);
            //die();

            if (in_array($param['his'], array('DL-FORMACION', 'DL-DOCUMENTO', 'DL-COMUNICACION'))) {
                $fields = array(
                    "Fields" => array()
                );
                $fields['Fields'][] = array(
                    "FieldName" => "DOCUMENT_TYPE",
                    "Item" => $param['type']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "CIF",
                    "Item" => $this->app->nif
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NOMBRE_EMPLEADO",
                    "Item" => $this->app->name
                );
                $fields['Fields'][] = array(
                    "FieldName" => "DOCID_DOCUMENTO",
                    "Item" => $param['doc-id']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NOMBRE_DOCUMENTO",
                    "Item" => $param['title']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "FECHA",
                    "Item" => date(dateSQL)
                );
                $fields['Fields'][] = array(
                    "FieldName" => "FECHA_HORA",
                    //"Item" => date(dateSQL)
                    "Item" => gmdate(dateSQL)
                );
                $dw->dw_add_doc(DW_FC_LOGPORTAL, $fields, '');
            } else {
                $fields = array(
                    "Field" => array()
                );
                $fields['Field'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'Leído'
                );
                $fields['Field'][] = array(
                    "FieldName" => "FECHA_DESCARGA",
                    "Item" => date(dateSQL)
                );
                $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['doc-id']);
            }

            $dw->logoutDW();

            if ($data['status'] == 200) {
                file_put_contents(TMP . '/' . $data['doc'], base64_decode($data['doc_base64']));

                $obs = '';
                if ($param['his'] == 'DL-CONTRATO') {
                    $obs = "El usuario {$this->app->name} descarga un documento Contrato DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-PLANTILLA') {
                    $obs = "El usuario {$this->app->name} descarga un documento Plantilla DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-NOMINA') {
                    $obs = "El usuario {$this->app->name} descarga un documento Nómina DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-PROCEDIMIENTO') {
                    $obs = "El usuario {$this->app->name} descarga un documento Procedimiento DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-GASTO') {
                    $obs = "El usuario {$this->app->name} descarga un documento Gasto DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-AUSENCIA') {
                    $obs = "El usuario {$this->app->name} descarga un documento Solicitud de Permiso DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-FORMACION') {
                    $obs = "El usuario {$this->app->name} descarga un documento Formación DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-DOCUMENTO') {
                    $obs = "El usuario {$this->app->name} descarga un documento Documentación para Empleado DOCID: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-COMUNICACION') {
                    $obs = "El usuario {$this->app->name} descarga un documento Comunicación DOCID: {$param['doc-id']}";
                }

                //INSERTAMOS EN HISTORICO
                $history = array(
                    'xentity' => 'EMPLEADOS',
                    'xaction' => $param['his'],
                    'xid' => $param['doc-id'],
                    'xobs' => $obs
                );
                $this->app->add_history($history);
            }
            unset($data['doc_base64']);
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _dl_pro_doc($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            $data = $dw->dw_download_document(DW_FC_PROVEEDORES, $param['doc-id'], $param['doc-ext']);

            $dw->logoutDW();

            if ($data['status'] == 200) {
                file_put_contents(TMP . '/' . $data['doc'], base64_decode($data['doc_base64']));

                $obs = '';
                if ($param['his'] == 'DL-FICHA-TECNICA') {
                    $obs = "El usuario {$this->app->name} descarga un documento Ficha Técnica de Producto: {$param['doc-id']}";
                }
                if ($param['his'] == 'DL-FICHA-SEGURIDAD') {
                    $obs = "El usuario {$this->app->name} descarga un documento Ficha Seguridad de Producto: {$param['doc-id']}";
                }

                //INSERTAMOS EN HISTORICO
                $history = array(
                    'xentity' => 'EMPLEADOS',
                    'xaction' => $param['his'],
                    'xid' => $param['doc-id'],
                    'xobs' => $obs
                );
                $this->app->add_history($history);
            }
            unset($data['doc_base64']);
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _dl_doc_pdf($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            $data = $dw->dw_download_document(DW_FC_EMPLEADOS, $param['doc-id'], 'PDF');
            $dw->logoutDW();

            if ($data['status'] == 200) {
                file_put_contents(TMP . '/' . $data['doc'], base64_decode($data['doc_base64']));
            }
//unset($data['doc']);
            unset($data['doc_base64']);
        } else {
//print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_formacion($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            $doc_id = $param['ndoc'];

            $res_doc = $dw->dw_get_doc(DW_FC_EMPLEADOS, $doc_id);
            $doc = $res_doc['response']['Fields'];

            //print_r($doc);
            //die();

            $fields = array(
                "Fields" => array()
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOCUMENT_TYPE",
                "Item" => 'Formación/PRL'
            );
            $fields['Fields'][] = array(
                "FieldName" => "NIF",
                "Item" => $this->app->nif
            );
            $fields['Fields'][] = array(
                "FieldName" => "_SUBTIPO",
                "Item" => $dw->getValue($doc, '_SUBTIPO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "DIAS_PARA_AVISO",
                "Item" => $dw->getValue($doc, 'DIAS_PARA_AVISO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA_DOCUMENTO",
                "Item" => $dw->getValue($doc, 'FECHA_DOCUMENTO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "EMPLEADO",
                "Item" => $dw->getValue($doc, 'EMPLEADO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA_VENCIMIENTO",
                "Item" => $dw->getValue($doc, 'FECHA_VENCIMIENTO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "NOTIFICAR_EMPLEADO",
                "Item" => $dw->getValue($doc, 'NOTIFICAR_EMPLEADO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "OBSERVACIONES",
                "Item" => $dw->getValue($doc, 'OBSERVACIONES')
            );

            $fields['Fields'][] = array(
                "FieldName" => "NOMBRE",
                "Item" => $this->app->name
            );
            $fields['Fields'][] = array(
                "FieldName" => "NO_DOCUMENTO",
                "Item" => $dw->getValue($doc, 'NO_DOCUMENTO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "TITULO",
                "Item" => $dw->getValue($doc, 'TITULO')
            );
            $fields['Fields'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'PENDIENTE VALIDAR'
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA_DOCUMENTO",
                "Item" => date(dateSQL)
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOC_PADRE",
                "Item" => $doc_id
            );

            $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            //print_r($data);
            //die();
            if ($data['status'] == 200) {
                //ACTUALIZAMOS DOCUMENTO SIN FIRMA a COPIA SIN FIRMA
                $fields = array(
                    "Field" => array()
                );
                $fields['Field'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'COPIA SIN FIRMA'
                );

                $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $doc_id);
                //print_r($data);
                //die();
            }

            //$data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc'], $doc_id);
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_nomina($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            $doc_id = $param['ndoc'];

            $res_doc = $dw->dw_get_doc(DW_FC_EMPLEADOS, $doc_id);
            $doc = $res_doc['response']['Fields'];

            //print_r($doc);
            //die();

            $fields = array(
                "Fields" => array()
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOCUMENT_TYPE",
                "Item" => 'Nómina'
            );
            $fields['Fields'][] = array(
                "FieldName" => "NIF",
                "Item" => $this->app->nif
            );
            $fields['Fields'][] = array(
                "FieldName" => "EMPLEADO",
                "Item" => $this->app->name
            );
            $fields['Fields'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'PENDIENTE VALIDAR'
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA_DOCUMENTO",
                "Item" => date(dateSQL)
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOC_PADRE",
                "Item" => $doc_id
            );

            $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            //print_r($data);
            //die();
            if ($data['status'] == 200) {
                //ACTUALIZAMOS DOCUMENTO SIN FIRMA a COPIA SIN FIRMA
                $fields = array(
                    "Field" => array()
                );
                $fields['Field'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'COPIA SIN FIRMA'
                );

                $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $doc_id);
                //print_r($data);
                //die();
            }

            //$data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc'], $doc_id);
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_gastos($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            if ($param['nobra'] != '') {
                //BUSCAMOS OBRA
                $res = $dw->dw_query_search_dialog(DW_FC_VISTA_LOCAL_OBRAS, DW_SD_VISTA_LOCAL_OBRAS, 'CODIGO:' . strtoupper($param['nobra']));
                //print_r($res);
                if ($res['response']['Count']['Value'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'Obra no encontrada.';
                }
            }

            if ($data['status'] == 1) {

                $fields = array(
                    "Fields" => array()
                );
                $fields['Fields'][] = array(
                    "FieldName" => "DOCUMENT_TYPE",
                    "Item" => 'GASTOS'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NIF",
                    "Item" => $this->app->nif
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NOMBRE",
                    "Item" => $this->app->name
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NO_OBRA",
                    "Item" => strtoupper($param['nobra'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NO_DOCUMENTO",
                    "Item" => strtoupper($param['ndoc'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "TITULO",
                    "Item" => strtoupper($param['titulo'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "IMPORTE",
                    "Item" => strtoupper($param['importe'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "SUBTIPO",
                    "Item" => $param['gasto']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "ANTICIPO",
                    "Item" => $param['tipo']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'PENDIENTE VALIDAR'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "FECHA",
                    "Item" => date(dateSQL)
                );

                $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            }
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _aprobar($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
            //RECOGEMOS DATOS TAREA
            $val = array(
                'tar' => $param['tar-id']
            );
            $sql = "select a.*"
                    . " from dw_tareas a"
                    . " where a.xtarea_id=:tar";
            //print_r($sql);
            //die();
            $tarea = $this->db->fetchRow($sql, $val);
            //print_r($tarea);
            //die();
            //RECOGEMOS TODOS LOS DATOS DEL RECURSO
            $response = $dw->dw_query_search_dialog(DW_FC_VISTA_LOCAL_RECURSOS, DW_SD_VISTA_LOCAL_RECURSOS, 'NIF:' . $tarea['xnif_dst']);

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            //print_r($items);
            //die();
            $recurso = 'Desconocido';
            if (isset($items[0])) {
                $recurso = $dw->getValue($items[0]['Fields'], 'NOMBRE');
            }

            //print_r($recurso);
            //die();
            //RECOGEMOS TODOS LOS ELEMENTOS DE LA TABLA FIRMAS
            $res_doc = $dw->dw_get_all_index_table(DW_FC_EMPLEADOS, $param['doc-id'], 'FIRMAS');
            if ($res_doc['status'] == 200) {
                $rows = $res_doc['response']['Row'];
                $row = array(
                    'ColumnValue' => array()
                );
                $row['ColumnValue'][] = array(
                    'FieldName' => 'FIRMA_RECURSO',
                    'Item' => $recurso
                );
                $row['ColumnValue'][] = array(
                    'FieldName' => 'FIRMA_FECHA',
                    'Item' => date(dateSQL)
                );
                $rows[] = $row;
            }

            //ACTUALIZAMOS EL ESTADO DEL DOCUMENTO PENDIENTE APROBACIÓN
            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'APROBADO'
            );
            $fields['Field'][] = array(
                "FieldName" => "FIRMAS",
                "Item" => array(
                    '$type' => 'DocumentIndexFieldTable',
                    'Row' => $rows
                )
            );
            //print_r($fields);
            //die();

            $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['doc-id']);
            if ($data['status'] == 200) {
                unset($data['response']);
                $data['msg'] = 'Documento aprobado correctamente.';

                $update = array(
                    'xresultado' => 'acepted',
                    'xfecha_result' => date(dateSQL)
                );
                $where = array(
                    'xtarea_id' => $param['tar-id']
                );
                $this->db->update('dw_tareas', $update, $where);
            }



            //RECOGEMOS TODOS LOS TICKETS DEL DOCUMENTO
            $cond = 'NIF:' . $this->app->nif . ';DOC_PADRE:' . $param['doc-id'];
            //print($cond);
            //die();
            $response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, $cond, 'And');

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            //CARGAMOS TICKETS
            foreach ($items as $k => $v) {
                if ($dw->getValue($v['Fields'], 'SUBTIPO') == 'TICKET' || $dw->getValue($v['Fields'], 'SUBTIPO') == 'FACTURA') {
                    //ACTUALIZMOS ESTADO DE TICKETS Y FACTURAS DEL DOCUMENTO PADRE
                    $fields = array(
                        "Field" => array()
                    );
                    $fields['Field'][] = array(
                        "FieldName" => "ESTADO",
                        "Item" => 'APROBADO'
                    );

                    $response = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $v['Id']);
                }
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_entrada_gastos($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Entrada añadida correctamente'
        );

        $tmp = explode('/', $param['fecha']);
        $fecha = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';

        $insert = array(
            'xnif' => $this->app->nif,
            'xfecha' => $fecha,
            'xconcepto' => $param['concepto'],
            'xorigen' => $param['origen'],
            'xdestino' => $param['destino'],
            'xnobra' => $param['obra'],
            'xtransporte_hoteles' => $param['transporte'],
            'xpeajes_parking' => $param['peaje'],
            'xcombustible' => $param['combustible'],
            'xdietas' => $param['dietas'],
            'xinvitaciones' => $param['invitaciones'],
            'xmaterial' => $param['materiales'],
            'xotros' => $param['otros']
        );
        $this->app->db->insert('dw_notas_gastos', $insert);

        return $data;
    }

    private function _remove_entrada_gastos($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Entrada eliminada correctamente'
        );

        $where = array(
            'xnif' => $this->app->nif,
            'xentrada_id' => $param['ent-id']
        );
        $this->app->db->del('dw_notas_gastos', $where);

        return $data;
    }

    private function _load_entradas_gastos() {
        $data = array(
            'status' => 1,
            'items' => array()
        );
        $sql = "select a.*"
                . ",date_format(a.xfecha,'%d/%m/%Y') as xfecha_format"
                . " from dw_notas_gastos a"
                . " where a.xnif='{$this->app->nif}'"
                . " order by a.xentrada_id";
        //print_r($sql);
        //die();
        $data['items'] = $this->db->fetchAll($sql);

        foreach ($data['items'] as $k => $v) {
            $data['items'][$k]['xtransporte_hoteles'] = number_format($v['xtransporte_hoteles'], 2, ',', '');
            $data['items'][$k]['xpeajes_parking'] = number_format($v['xpeajes_parking'], 2, ',', '');
            $data['items'][$k]['xcombustible'] = number_format($v['xcombustible'], 2, ',', '');
            $data['items'][$k]['xdietas'] = number_format($v['xdietas'], 2, ',', '');
            $data['items'][$k]['xinvitaciones'] = number_format($v['xinvitaciones'], 2, ',', '');
            $data['items'][$k]['xmaterial'] = number_format($v['xmaterial'], 2, ',', '');
            $data['items'][$k]['xotros'] = number_format($v['xotros'], 2, ',', '');

            if ($data['items'][$k]['xtransporte_hoteles'] == '0,00')
                $data['items'][$k]['xtransporte_hoteles'] = '';
            if ($data['items'][$k]['xpeajes_parking'] == '0,00')
                $data['items'][$k]['xpeajes_parking'] = '';
            if ($data['items'][$k]['xcombustible'] == '0,00')
                $data['items'][$k]['xcombustible'] = '';
            if ($data['items'][$k]['xdietas'] == '0,00')
                $data['items'][$k]['xdietas'] = '';
            if ($data['items'][$k]['xinvitaciones'] == '0,00')
                $data['items'][$k]['xinvitaciones'] = '';
            if ($data['items'][$k]['xmaterial'] == '0,00')
                $data['items'][$k]['xmaterial'] = '';
            if ($data['items'][$k]['xotros'] == '0,00')
                $data['items'][$k]['xotros'] = '';
        }

        return $data;
    }

    private function _rechazar($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            //ACTUALIZAMOS EL ESTADO DEL DOCUMENTO PENDIENTE APROBACIÓN
            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'RECHAZADO'
            );
            //print_r($fields);
            //die();

            $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['doc-id']);
            if ($data['status'] == 200) {
                unset($data['response']);
                $data['msg'] = 'Documento marcado como rechazado.';

                $update = array(
                    'xresultado' => 'rejected',
                    'xfecha_result' => date(dateSQL),
                    'xobs' => $param['obs']
                );
                $where = array(
                    'xtarea_id' => $param['tar-id']
                );
                $this->db->update('dw_tareas', $update, $where);
            }

            //RECOGEMOS TODOS LOS TICKETS DEL DOCUMENTO
            $cond = 'NIF:' . $this->app->nif . ';DOC_PADRE:' . $param['doc-id'];
            //print($cond);
            //die();
            $response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, $cond, 'And');

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            //CARGAMOS TICKETS
            foreach ($items as $k => $v) {
                if ($dw->getValue($v['Fields'], 'SUBTIPO') == 'TICKET' || $dw->getValue($v['Fields'], 'SUBTIPO') == 'FACTURA') {
                    //ACTUALIZMOS ESTADO DE TICKETS Y FACTURAS DEL DOCUMENTO PADRE
                    $fields = array(
                        "Field" => array()
                    );
                    $fields['Field'][] = array(
                        "FieldName" => "ESTADO",
                        "Item" => 'RECHAZADO'
                    );

                    $response = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $v['Id']);
                }
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _list_rechazo($param) {
        $data = array(
            'status' => 200,
            'items' => array(),
            'motivo' => ''
        );
        $val = array(
            'doc' => $param['doc-id']
        );
        $sql = "select a.*,b.xusuario"
                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                . ",date_format(a.xfecha_result,'%d/%m/%Y %H:%i:%s') as xfecha_result_format"
                . " from dw_tareas a"
                . " left join dw_usuarios b on a.xnif_dst=b.xnif and b.xrol_id=3"
                . " where a.xdoc_id=:doc"
                . " order by a.xtarea_id desc";
        //print_r($sql);
        //die();
        $data['items'] = $this->db->fetchAll($sql, $val);
        if (count($data['items']) == 0) {
            $dw = new DocuwareApi(DW_URL);
            $res = $dw->loginDW(DW_USER, DW_PASS);
            if ($res['status'] == 200) {
                $res_doc = $dw->dw_get_all_index(DW_FC_EMPLEADOS, $param['doc-id']);
                if ($res_doc['status'] == 200) {
                    $fields = $res_doc['response']['Field'];
                    $data['motivo'] = $dw->getValue($fields, 'OBSERVACIONES');
                }

                $dw->logoutDW();
            } else {
                $data['status'] = $res['status'];
                $data['msg'] = $res['response']['Message'];
            }
        }

        return $data;
    }

    private function _send_gastos_registro($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            //ACTUALIZAMOS EL ESTADO DEL DOCUMENTO PENDIENTE APROBACIÓN
            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'PENDIENTE REGISTRAR'
            );
            //print_r($fields);
            //die();

            $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['doc-id']);
            if ($data['status'] == 200) {
                unset($data['response']);
                $data['msg'] = 'Documento enviado a registro.';
            }

            //RECOGEMOS TODOS LOS TICKETS DEL DOCUMENTO
            $cond = 'NIF:' . $this->app->nif . ';DOC_PADRE:' . $param['doc-id'];
            //print($cond);
            //die();
            $response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, $cond, 'And');

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            //CARGAMOS TICKETS
            foreach ($items as $k => $v) {
                if ($dw->getValue($v['Fields'], 'SUBTIPO') == 'TICKET' || $dw->getValue($v['Fields'], 'SUBTIPO') == 'FACTURA') {
                    //ACTUALIZMOS ESTADO DE TICKETS Y FACTURAS DEL DOCUMENTO PADRE
                    $fields = array(
                        "Field" => array()
                    );
                    $fields['Field'][] = array(
                        "FieldName" => "ESTADO",
                        "Item" => 'PENDIENTE REGISTRAR'
                    );

                    $response = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $v['Id']);
                }
            }

            $dw->logoutDW();
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_ticket($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            $fields = array(
                "Fields" => array()
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOCUMENT_TYPE",
                "Item" => 'GASTOS'
            );
            $fields['Fields'][] = array(
                "FieldName" => "NIF",
                "Item" => $this->app->nif
            );
            $fields['Fields'][] = array(
                "FieldName" => "NOMBRE",
                "Item" => $this->app->name
            );
            $fields['Fields'][] = array(
                "FieldName" => "NO_DOCUMENTO",
                "Item" => strtoupper($param['ndoc'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "TITULO",
                "Item" => strtoupper($param['titulo'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "IMPORTE",
                "Item" => strtoupper($param['importe'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "SUBTIPO",
                "Item" => 'TICKET'
            );
            $fields['Fields'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'PENDIENTE VALIDAR'
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOC_PADRE",
                "Item" => $param['parent']
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA",
                "Item" => date(dateSQL)
            );

            $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            $dw->logoutDW();
        } else {
//print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_parte($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            if ($param['nobra'] != '') {
                //BUSCAMOS OBRA
                $res = $dw->dw_query_search_dialog(DW_FC_VISTA_LOCAL_OBRAS, DW_SD_VISTA_LOCAL_OBRAS, 'CODIGO:' . strtoupper($param['nobra']));
                //print_r($res);
                if ($res['response']['Count']['Value'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'Obra no encontrada.';
                }
            }

            if ($data['status'] == 1) {
                $fields = array(
                    "Fields" => array()
                );
                $fields['Fields'][] = array(
                    "FieldName" => "DOCUMENT_TYPE",
                    "Item" => 'PARTE DE TRABAJO'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NIF",
                    "Item" => $this->app->nif
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NOMBRE",
                    "Item" => $this->app->name
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NO_OBRA",
                    "Item" => strtoupper($param['nobra'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NO_DOCUMENTO",
                    "Item" => strtoupper($param['ndoc'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "TITULO",
                    "Item" => strtoupper($param['titulo'])
                );
                $fields['Fields'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'PENDIENTE VALIDAR'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "FECHA",
                    "Item" => date(dateSQL)
                );

                $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            }
            $dw->logoutDW();
        } else {
//print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _fin_obra($param) {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            if ($param['obra'] != '') {
                //BUSCAMOS OBRA
                //$res = $dw->dw_query_search_dialog(DW_FC_CONTROL_TRABAJOS, DW_SD_CONTROL_TRABAJOS, 'CODIGO:' . strtoupper($param['nobra']));

                $filtro = array(
                    'Operation' => 'And',
                    'Condition' => array(
                        array(
                            'DBName' => 'NO_DE_TRABAJO',
                            'Value' => array($param['obra'])
                        ),
                        array(
                            'DBName' => 'TIPO_DE_DOCUMENTO',
                            'Value' => array('ORDEN DE TRABAJO')
                        )
                    )
                );

                //print_r($filtro);
                //die();

                $res_query = $dw->dwGetDialogExpression(DW_FC_CONTROL_TRABAJOS, DW_SD_CONTROL_TRABAJOS, $filtro);
                //print_r($res_query);
                //die();

                $item = array();
                $ndoc = 0;
                if ($res_query['status'] == 1) {
                    $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                    //print_r($response);
                    //die();

                    if ($response['status'] = 200) {
                        if ($response['response']['Count']['Value'] == 1) {
                            $data['status'] = 1;
                            $item = $response['response']['Items'][0];
                            $ndoc = $item['Id'];
                        } else {
                            $data['status'] = 0;
                            $data['msg'] = 'Obra no encontrada.';
                        }
                    }

                    //$items = $response['response']['Items'];
                    //print_r($item);
                    //die();
                }
                //print($ndoc);
                //die();
            }

            if ($data['status'] == 1) {
                $fields = array(
                    "Field" => array()
                );
                $fields['Field'][] = array(
                    "FieldName" => "CIERRE_TRABAJO",
                    "Item" => 'CIERRE SOLICITADO'
                );
                $fields['Field'][] = array(
                    "FieldName" => "EMPLEADO_CIERRE",
                    "Item" => $this->app->nif
                );

                $data = $dw->dw_update_doc_indexes(DW_FC_CONTROL_TRABAJOS, $fields, $ndoc);
                $data = $dw->dw_update_doc_indexes(DW_FC_CONTROL_TRABAJOS, $fields, $param['doc']);
            }
            $dw->logoutDW();
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_parte_v2($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            if ($param['nobra'] != '') {
                //BUSCAMOS OBRA
                $res = $dw->dw_query_search_dialog(DW_FC_VISTA_LOCAL_OBRAS, DW_SD_VISTA_LOCAL_OBRAS, 'CODIGO:' . strtoupper($param['nobra']));
                //print_r($res);
                if ($res['response']['Count']['Value'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'Obra no encontrada.';
                }
            }

            if ($data['status'] == 1) {

                $fields = array(
                    'DOCUMENT_TYPE' => 'PARTE DE TRABAJO',
                    'NIF' => $this->app->nif,
                    'NOMBRE' => $this->app->name,
                    'NO_OBRA' => strtoupper($param['nobra']),
                    'NO_DOCUMENTO' => strtoupper($param['ndoc']),
                    'TITULO' => strtoupper($param['titulo']),
                    'ESTADO' => 'PENDIENTE VALIDAR',
                    'FECHA' => date(dateSQL)
                );

                $data = $dw->dw_add_doc_v2(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            }
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_comunicacion($param) {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print(json_encode($res));
        //die();
        if ($res['status'] == 200) {
            $fields = array(
                "Fields" => array()
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOCUMENT_TYPE",
                "Item" => 'Comunicación'
            );
            $fields['Fields'][] = array(
                "FieldName" => "NIF",
                "Item" => $this->app->nif
            );
            $fields['Fields'][] = array(
                "FieldName" => "EMPLEADO",
                "Item" => $this->app->name
            );
            $fields['Fields'][] = array(
                "FieldName" => "OBSERVACIONES",
                "Item" => $param['msg']
            );
            $fields['Fields'][] = array(
                "FieldName" => "NOMBRE_ARCHIVO",
                "Item" => $param['title']
            );
            $fields['Fields'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'Pendiente'
            );
            $fields['Fields'][] = array(
                "FieldName" => "EMISOR",
                "Item" => 'Empleado'
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA_DOCUMENTO",
                "Item" => date(dateSQL)
            );

            /* $fields['Fields'][] = array(
              "FieldName" => "STATUS",
              "Item" => $param['estado']
              );
             * 
             */

            $pdf = new FPDF();

            $pdf->AddPage();
            $pdf->SetAutoPageBreak(FALSE, 0);
            $pdf->SetMargins(25, 25);
            $pdf->SetFont('Arial', 'B', 10);

            $pdf->Image(IMG_COMUNICACION, 15, 15, 20, 0, 'PNG');
            $pdf->SetXY(20, 20);
            $pdf->SetFont('Arial', 'B', 18);
            $pdf->Cell(160, 6, utf8_decode('COMUNICACIÓN EMPLEADO'), 0, 0, 'C');
            $pdf->Ln(8);
            $pdf->Line(30, $pdf->GetY(), 180, $pdf->GetY());
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(8);
            $pdf->Cell(150, 5, "Empleado: {$this->app->nif}", 0, 0, 'L');
            $pdf->Ln(8);
            $pdf->Cell(150, 5, "Fecha: " . date('d/m/Y'), 0, 0, 'L');
            $pdf->Ln(8);
            $pdf->Cell(150, 5, "Asunto: {$param['title']}", 0, 0, 'L');
            $pdf->Ln(8);
            $pdf->Cell(150, 5, utf8_decode("Comunicación: "), 0, 0, 'L');
            $pdf->Ln(8);
            $pdf->MultiCell(160, 5, utf8_decode("{$param['msg']}"), 0, 'L');

            $file = TMP . "/tpl-" . $this->app->rndString(10) . ".pdf";

            $pdf->Output($file, 'F');

            //die($file);

            $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, $file);

            print(json_encode($data));
            die();

            $dw->logoutDW();
        } else {
//print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_ausencia($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            $fields = array(
                "Fields" => array()
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOCUMENT_TYPE",
                "Item" => 'AUSENCIA'
            );
            $fields['Fields'][] = array(
                "FieldName" => "NIF",
                "Item" => $this->app->nif
            );
            $fields['Fields'][] = array(
                "FieldName" => "NOMBRE",
                "Item" => $this->app->name
            );
            $fields['Fields'][] = array(
                "FieldName" => "NO_DOCUMENTO",
                "Item" => strtoupper($param['ndoc'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "TITULO",
                "Item" => strtoupper($param['titulo'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "SUBTIPO",
                "Item" => strtoupper($param['tipo'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'PENDIENTE VALIDAR'
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA",
                "Item" => date(dateSQL)
            );

            $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc']);
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _add_justificante($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'JUSTIFICANTE PENDIENTE REVISAR'
            );
            //print(TMP . '/' . $param['doc']);
            //die();

            $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, TMP . '/' . $param['doc'], $param['ndoc']);
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _list_solicitudes($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Solicitud de Permiso')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                //if ($d['estado'] == 'VERIFICADA' || $d['estado'] == 'SOLICITADO POR EMAIL')
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_justificantes($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Justificante')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                //if ($d['estado'] == 'VERIFICADA' || $d['estado'] == 'SOLICITADO POR EMAIL')
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_nominas($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Nómina')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                //if ($d['estado'] == 'VERIFICADA' || $d['estado'] == 'SOLICITADO POR EMAIL')
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_contratos($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Contrato')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
//print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
//print_r($data);
//die();
        return $data;
    }

    private function _list_prl($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('PRL')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_documentacion_general($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Documentación General')
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'status' => 0, //POR DEFECTO NO SE MUESTRA NINGÚN DOCUMENTO
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'departamento' => $dw->getValue($v['Fields'], 'DEPARTAMENTO'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );

                if ($d['nif'] == '' && $d['departamento'] == 'TODAS LAS PERSONAS') {
                    $d['status'] = 1;
                }
                if ($d['nif'] == $this->app->nif) {
                    $d['status'] = 1;
                }
                if ($d['nif'] == '' && $d['departamento'] == $this->app->departamento) {
                    $d['status'] = 1;
                }

                if ($d['status'] == 1)
                    $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_epis($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('EPIS')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_proteccion_datos($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Protección de Datos')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_otros($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_NOMINAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Otros')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();

            //print_r($response);
            //die();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_formacion($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_FORMACION_PRL, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Formación')
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            /*
             * array(
              'DBName' => 'NIF',
              'Value' => array($this->app->nif)
              ),
             */

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'status' => 0, //POR DEFECTO NO SE MUESTRA NINGÚN DOCUMENTO
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'titulo' => $dw->getValue($v['Fields'], 'NOMBRE_ARCHIVO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'departamento' => $dw->getValue($v['Fields'], 'DEPARTAMENTO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'secciones' => $dw->getValue($v['Fields'], 'DWSECTIONCOUNT'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );

                if ($d['nif'] == '' && $d['departamento'] == 'TODAS LAS PERSONAS') {
                    $d['status'] = 1;
                }
                if ($d['nif'] == $this->app->nif) {
                    $d['status'] = 1;
                }
                if ($d['nif'] == '' && $d['departamento'] == $this->app->departamento) {
                    $d['status'] = 1;
                }

                if ($d['status'] == 1)
                    $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_gastos($param) {
        $data = array();

        $sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='G'";
        //print($sql);
        //die();
        $tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Gasto de Empleado')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($response);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO')),
                    'fecha_gasto' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_GASTO')),
                    'fecha_solicitud' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_SOLICITUD')),
                    'fecha_inicio' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_INICIO')),
                    'fecha_fin' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_FIN'))
                );
                //if ($d['estado'] == 'VERIFICADA' || $d['estado'] == 'SOLICITADO POR EMAIL')
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_tickets($param) {
        $data = array(
            'status' => 200,
            'items' => array()
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
//print_r($res);
//die();
        if ($res['status'] == 200) {
            $cond = 'NIF:' . $this->app->nif . ';DOC_PADRE:' . $param['doc-id'];
//print($cond);
//die();
            $response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, $cond, 'And');
            $dw->logoutDW();

//print_r($response);
//die();

            $items = $response['response']['Items'];
            //CARGAMOS TICKETS
            foreach ($items as $k => $v) {
                if ($dw->getValue($v['Fields'], 'SUBTIPO') == 'TICKET' || $dw->getValue($v['Fields'], 'SUBTIPO') == 'FACTURA') {
                    $importe = $dw->getValue($v['Fields'], 'IMPORTE');
                    if (is_numeric($importe)) {
                        $importe = number_format($dw->getValue($v['Fields'], 'IMPORTE'), 2, ',', '');
                    }

                    $d = array(
                        'ndoc' => $v['Id'],
                        'tipo' => $dw->getValue($v['Fields'], 'SUBTIPO'),
                        'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                        'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                        'nif' => $dw->getValue($v['Fields'], 'NIF'),
                        'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                        'importe' => $importe,
                        'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA')),
                    );
                    $data['items'][] = $d;
                }
            }
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
//print_r($data);
//die();
        return $data;
    }

    private function _list_firmas($param) {
        $data = array(
            'status' => 200,
            'items' => array(),
            'solicitudes' => array()
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            $res_doc = $dw->dw_get_all_index_table(DW_FC_EMPLEADOS, $param['doc-id'], 'FIRMAS');
            $dw->logoutDW();

            //print_r($res_doc);
            //die();

            if ($res_doc['status'] == 200) {
                $rows = $res_doc['response']['Row'];

                foreach ($rows as $kr => $vr) {
                    $d = array(
                        'recurso' => $dw->getValue($vr['ColumnValue'], 'FIRMA_RECURSO'),
                        'fecha' => $dw->getDateValue($dw->getValue($vr['ColumnValue'], 'FIRMA_FECHA'))
                    );
                    $data['items'][] = $d;
                }
            }

            $sql = "select a.*,b.xusuario as xrecurso"
                    . ",date_format(a.xfecha,'%d/%m/%Y') as xfecha_format"
                    . " from dw_tareas a"
                    . " left join dw_usuarios b on a.xnif_dst=b.xnif"
                    . " where a.xnif_src='{$this->app->nif}' and a.xdoc_id={$param['doc-id']}"
                    . " and a.xresultado='pending' limit 1";
            //print($sql);
            //die();
            $row = $this->db->fetchRow($sql);
            if ($row) {
                $data['solicitudes'][] = $row;
            }
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_gastos_tickets($param) {
        $data = array(
            'status' => 200,
            'items' => array()
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {

            //COGEMOS LOS INDICES DEL DOCUMENTO
            $res_doc = $dw->dw_get_all_index(DW_FC_EMPLEADOS, $param['doc-id']);
            if ($res_doc['status'] == 200) {
                $fields = $res_doc['response']['Field'];

                $importe = '';
                if ($dw->getValue($fields, 'IMPORTE') != '')
                    number_format($dw->getValue($fields, 'IMPORTE'), 2, ',', '');

                $d = array(
                    'ndoc' => $dw->getValue($fields, 'DWDOCID'),
                    'tipo' => $dw->getValue($fields, 'SUBTIPO'),
                    'titulo' => $dw->getValue($fields, 'TITULO'),
                    'nif' => $dw->getValue($fields, 'NIF'),
                    'ext' => $dw->getValue($fields, 'DWEXTENSION'),
                    'importe' => $importe,
                    'fecha' => $dw->getDateValue($dw->getValue($fields, 'FECHA')),
                );
                $data['items'][] = $d;
            }

//COGEMOS TODOS LOS TICKETS
            $cond = 'NIF:' . $this->app->nif . ';DOC_PADRE:' . $param['doc-id'];
//print($cond);
//die();
            $response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, $cond, 'And');
            $dw->logoutDW();

//print_r($response);
//die();

            $items = $response['response']['Items'];
//CARGAMOS TICKETS
            foreach ($items as $k => $v) {
                if ($dw->getValue($v['Fields'], 'SUBTIPO') == 'TICKET' || $dw->getValue($v['Fields'], 'SUBTIPO') == 'FACTURA') {
                    $d = array(
                        'ndoc' => $v['Id'],
                        'tipo' => $dw->getValue($v['Fields'], 'SUBTIPO'),
                        'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                        'nif' => $dw->getValue($v['Fields'], 'NIF'),
                        'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                        'importe' => number_format($dw->getValue($v['Fields'], 'IMPORTE'), 2, ',', ''),
                        'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA')),
                    );
                    $data['items'][] = $d;
                }
            }
        } else {
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_partes($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_PARTES_TRABAJO, 'NIF:' . $this->app->nif);
            //print_r($response);
            //die();
            $parte = 'OBRA';
            if ($this->app->rol == 6) {
                $parte = 'TALLER';
            }
            //print($this->app->rol);
            //print($parte);
            //die();

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'PARTE_DE_OBRA',
                        'Value' => array($parte)
                    )
                )
            );

            $res_query = $dw->dwGetDialogExpression(DW_FC_PARTES_TRABAJO, DW_SD_PARTES_TRABAJO, $param);
            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            //$items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'cliente' => $dw->getValue($v['Fields'], 'CLIENTE'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'nobra' => $dw->getValue($v['Fields'], 'OBRA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA')),
                );
                $data[] = $d;
            }
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        $dw->logoutDW();
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_ausencias($param) {
        $data = array();

        //$sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='A'";
        //print($sql);
        //die();
        //$tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_AUSENCIAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Solicitud de Permiso')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($response);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO')),
                    'fecha_solicitud' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_SOLICITUD')),
                    'fecha_inicio' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_INICIO')),
                    'fecha_fin' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_FIN')),
                    'tipo_permiso' => $dw->getValue($v['Fields'], 'TIPO_PERMISO'),
                    'motivo' => $dw->getValue($v['Fields'], 'MOTIVO'),
                );
                //if ($d['estado'] == 'VERIFICADA' || $d['estado'] == 'SOLICITADO POR EMAIL')
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        //print_r($data);
        //die();
        return $data;
    }

    private function _list_control_trabajos($param) {
        $data = array();

        //$sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='A'";
        //print($sql);
        //die();
        //$tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_AUSENCIAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUWARE',
                        'Value' => array('BASE DE DATOS')
                    ),
                    array(
                        'DBName' => 'ESTADO',
                        'Value' => array('Trabajo en curso')
                    ),
                    array(
                        'DBName' => 'FECHA',
                        'Value' => array($param['fi'], $param['ff'])
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_CONTROL_TRABAJOS, DW_SD_CONTROL_TRABAJOS, $param);

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //print(json_encode($response));
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'descripcion' => $dw->getValue($v['Fields'], 'DESCRIPCION'),
                    'nombre_cliente' => $dw->getValue($v['Fields'], 'NOMBRE_CLIENTE'),
                    'hora' => $dw->getValue($v['Fields'], 'HORA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'nobra' => $dw->getValue($v['Fields'], 'NO_DE_TRABAJO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA')),
                    'tipo_trabajo' => $dw->getValue($v['Fields'], 'TIPO_DE_TRABAJO'),
                    'cliente' => $dw->getValue($v['Fields'], 'NOMBRE_CLIENTE'),
                    'cierre' => $dw->getValue($v['Fields'], 'CIERRE_TRABAJO')
                );
                if ($d['tipo_trabajo'] != '4' && $d['cierre'] != 'CIERRE SOLICITADO')
                    $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        //print_r($data);
        //die();
        return $data;
    }

    private function _list_docs_obra_rel($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Consulta realizada con éxito',
            'cliente' => array(),
            'decoman' => array(),
            'licencias' => array(),
            'calidad' => array(),
            'almacen' => array()
        );

        //$sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='A'";
        //print($sql);
        //die();
        //$tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_AUSENCIAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'NO_DE_TRABAJO',
                        'Value' => array($param['obra'])
                    )
                )
            );

            //print_r($response);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_CONTROL_TRABAJOS, DW_SD_CONTROL_TRABAJOS, $param);

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //print(json_encode($items));
                //die();
            }

            foreach ($items as $k => $v) {
                if ($dw->getValue($v['Fields'], 'DOCUWARE') != 'BASE DE DATOS') {

                    $d = array(
                        'ndoc' => $v['Id'],
                        'tipo' => $dw->getValue($v['Fields'], 'TIPO_DE_DOCUMENTO'),
                        'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                        'subtipo2' => $dw->getValue($v['Fields'], 'SUBTIPO_DE_DOCUMENTO'),
                        'descripcion' => $dw->getValue($v['Fields'], 'DESCRIPCION'),
                        'nombre_cliente' => $dw->getValue($v['Fields'], 'NOMBRE_CLIENTE'),
                        'hora' => $dw->getValue($v['Fields'], 'HORA'),
                        'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                        'nobra' => $dw->getValue($v['Fields'], 'NO_DE_TRABAJO'),
                        'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                        'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA')),
                    );

                    if ($d['tipo'] == 'Cliente' && $d['subtipo'] == 'Incidencias/quejas de cliente') {
                        $data['cliente'][] = $d;
                    }
                    if ($d['tipo'] == 'Decoman' && $d['subtipo'] != 'Planificación de compras') {
                        $data['decoman'][] = $d;
                    }
                    if ($d['tipo'] == 'Parte Técnica y Licencias' && $d['subtipo'] != 'Declaración responsable') {
                        $data['licencias'][] = $d;
                    }
                    if ($d['tipo'] == 'Calidad') {
                        if ($d['subtipo'] == 'Chequeo control de obra' || $d['subtipo'] == 'Control medioambiental')
                            $data['calidad'][] = $d;
                    }
                    if ($d['tipo'] == 'Entrada y salida material de almacén') {
                        $data['almacen'][] = $d;
                    }
                }
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        //print_r($data);
        //die();
        return $data;
    }

    private function _list_bajasmedicas($param) {
        $data = array();

        //$sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='A'";
        //print($sql);
        //die();
        //$tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_AUSENCIAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Baja médica enfermedad común')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    ),
                    array(
                        'DBName' => 'FECHA_DOCUMENTO',
                        'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                    )
                )
            );

            //print_r($response);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'subtipo' => $dw->getValue($v['Fields'], '_SUBTIPO'),
                    'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                    //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                    'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO')),
                    'fecha_solicitud' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_SOLICITUD')),
                    'fecha_inicio' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_INICIO')),
                    'fecha_fin' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_FIN'))
                );
                //if ($d['estado'] == 'VERIFICADA' || $d['estado'] == 'SOLICITADO POR EMAIL')
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        //print_r($data);
        //die();
        return $data;
    }

    private function _list_documentos($param) {
        $data = array();

        //$sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='A'";
        //print($sql);
        //die();
        //$tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_AUSENCIAS, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'PORTAL',
                        'Value' => array('SI')
                    )
                )
            );

            //print_r($response);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_DOCUMENTACION_PROPIA, DW_SD_DOCUMENTACION_PROPIA, $param);

            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'TIPO_DE_DOCUMENTO'),
                    'num_doc' => $dw->getValue($v['Fields'], 'NO_DOCUMENTO'),
                    'subtipo' => $dw->getValue($v['Fields'], 'SUBTIPO_DE_DOCUMENTO'),
                    'cantidad' => $dw->getValue($v['Fields'], 'CANTIDAD'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA')),
                    'fecha_vencimiento' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_VENCIMIENTO')),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'dias_aviso' => $dw->getValue($v['Fields'], 'DIAS_PARA_AVISO'),
                    'dias_restantes' => $dw->getValue($v['Fields'], 'DIAS_RESTANTES'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION')
                        //'nombre' => $dw->getValue($v['Fields'], 'EMPLEADO'),
                        //'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                        //'nif' => $dw->getValue($v['Fields'], 'NIF'),
                        //'empresa_cif' => $dw->getValue($v['Fields'], 'NIF_EMPRESA'),
                        //'empresa_nombre' => $dw->getValue($v['Fields'], 'EMPRESA'),
                        //'fecha_inicio' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_INICIO')),
                        //'fecha_fin' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_FIN'))
                );
                if ($d['cantidad'] != '') {
                    $d['cantidad_format'] = number_format($d['cantidad'], 2, ',', '.');
                }
                if ($d['estado'] != 'ELIMINAR')
                    $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        //print_r($data);
        //die();
        return $data;
    }

    private function _solicitud_firma($param) {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //$sql = "select * from dw_tareas where xnif_src='{$this->app->nif}' and xresultado='pending' and xtipo='A'";
        //print($sql);
        //die();
        //$tareas = $this->db->fetchAll($sql);

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {

            //ACTUALIZAMOS EL ESTADO DEL DOCUMENTO
            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'Solicitud Firma'
            );

            $res = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['id']);
            $data['status'] = $res['status'];
            $data['msg'] = $res['msg'];

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        //print_r($data);
        //die();
        return $data;
    }

    private function _mis_datos($param) {
        $data = array();

        $sql = "select a.*"
                . ",date_format(a.xfecha_nacimiento,'%d/%m/%Y') as xfecha_nacimiento_format"
                . " from dw_usuarios a"
                . " where a.xusuario_id={$this->app->user_id}";
        //print($sql);
        //die();
        $data = $this->db->fetchRow($sql);

        //print_r($data);
        //die();
        return $data;
    }

    private function _list_comunicaciones($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_COMUNICACIONES, 'NIF:' . $this->app->nif);

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Comunicación')
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'status' => 0, //POR DEFECTO NO SE MUESTRA NINGÚN DOCUMENTO
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'titulo' => $dw->getValue($v['Fields'], 'NOMBRE_ARCHIVO'),
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'departamento' => $dw->getValue($v['Fields'], 'DEPARTAMENTO'),
                    'estado' => $dw->getValue($v['Fields'], 'ESTADO'),
                    'emisor' => $dw->getValue($v['Fields'], 'EMISOR'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );

                if ($d['nif'] == '' && $d['departamento'] == 'TODAS LAS PERSONAS') {
                    $d['status'] = 1;
                }
                if ($d['nif'] == $this->app->nif) {
                    $d['status'] = 1;
                }
                if ($d['nif'] == '' && $d['departamento'] == $this->app->departamento) {
                    $d['status'] = 1;
                }

                if ($d['status'] == 1)
                    $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _get_saldos($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    )
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS_BD, DW_SD_EMPLEADOS_BD, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'nif' => $dw->getValue($v['Fields'], 'NIF'),
                    'saldo_hoy' => $dw->getValue($v['Fields'], 'SALDO_HOY'),
                    'saldo_fin_ano' => $dw->getValue($v['Fields'], 'SALDO_FIN_DE_ANO'),
                    'dias_a_compensar_clima' => $dw->getValue($v['Fields'], 'DIAS_A_COMPENSAR__CIRCUNSTANC'),
                    'horas_debe' => $dw->getValue($v['Fields'], 'HORAS_DEBE')
                );

                if (is_numeric($d['saldo_hoy'])) {
                    $d['saldo_hoy'] = number_format($d['saldo_hoy'], 2, ',', '.');
                }
                if (is_numeric($d['saldo_fin_ano'])) {
                    $d['saldo_fin_ano'] = number_format($d['saldo_fin_ano'], 2, ',', '.');
                }
                if (is_numeric($d['dias_a_compensar_clima'])) {
                    $d['dias_a_compensar_clima'] = number_format($d['dias_a_compensar_clima'], 2, ',', '.');
                }
                if (is_numeric($d['horas_debe'])) {
                    $d['horas_debe'] = number_format($d['horas_debe'], 2, ',', '.');
                }

                //$data[] = $d;
                $data = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_fichas_tecnicas($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_PLANTILLAS, '');

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Fichas técnicas de productos')
                    )
                /* ,
                  array(
                  'DBName' => 'NIF',
                  'Value' => array($this->app->nif)
                  )
                  /* ,
                  array(
                  'DBName' => 'FECHA_DOCUMENTO',
                  'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                  )
                 * 
                 */
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_PROVEEDORES, DW_SD_PROVEEDORES, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }



            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'DATE')),
                    'nombre_producto' => $dw->getValue($v['Fields'], 'NOMBRE_PRODUCTO'),
                    'marca' => $dw->getValue($v['Fields'], 'MARCA'),
                    'fabricante' => $dw->getValue($v['Fields'], 'COMPANY'),
                    'idioma' => $dw->getValue($v['Fields'], 'IDIOMA'),
                );
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_fichas_seguridad($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_PLANTILLAS, '');

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Fichas de seguridad de productos')
                    )
                /* ,
                  array(
                  'DBName' => 'NIF',
                  'Value' => array($this->app->nif)
                  )
                  /* ,
                  array(
                  'DBName' => 'FECHA_DOCUMENTO',
                  'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                  )
                 * 
                 */
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_PROVEEDORES, DW_SD_PROVEEDORES, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }



            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'DATE')),
                    'nombre_producto' => $dw->getValue($v['Fields'], 'NOMBRE_PRODUCTO'),
                    'marca' => $dw->getValue($v['Fields'], 'MARCA'),
                    'fabricante' => $dw->getValue($v['Fields'], 'COMPANY'),
                    'idioma' => $dw->getValue($v['Fields'], 'IDIOMA'),
                );
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_plantillas($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_PLANTILLAS, '');

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Plantilla')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    )
                /* ,
                  array(
                  'DBName' => 'FECHA_DOCUMENTO',
                  'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                  )
                 * 
                 */
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }



            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'departamento' => $dw->getValue($v['Fields'], 'SUBTIPO'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _list_procedimientos($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {
            //$response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_PROCEDIMIENTOS, '');

            $param = array(
                'Operation' => 'And',
                'Condition' => array(
                    array(
                        'DBName' => 'DOCUMENT_TYPE',
                        'Value' => array('Procedimiento')
                    ),
                    array(
                        'DBName' => 'NIF',
                        'Value' => array($this->app->nif)
                    )
                /* ,
                  array(
                  'DBName' => 'FECHA_DOCUMENTO',
                  'Value' => array($param['fi'], $param['ff'])//$param['fi'] 2022-03-29
                  )
                 * 
                 */
                )
            );

            //print_r($param);
            //die();

            $res_query = $dw->dwGetDialogExpression(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS, $param);
            //print_r($res_query);
            //die();

            $items = array();
            if ($res_query['status'] == 1) {
                $response = $dw->dw_get_all_docs_query($res_query['response'], 500);
                //print_r($response);
                //die();
                if ($response['status'] = 200)
                    $items = $response['response']['Items'];
                //print_r($items);
                //die();
            }

            //print_r($response);
            //die();

            $items = $response['response']['Items'];
            foreach ($items as $k => $v) {
                $d = array(
                    'ndoc' => $v['Id'],
                    'tipo' => $dw->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'ext' => $dw->getValue($v['Fields'], 'DWEXTENSION'),
                    'titulo' => $dw->getValue($v['Fields'], 'TITULO'),
                    'fecha' => $dw->getDateValue($dw->getValue($v['Fields'], 'FECHA_DOCUMENTO'))
                );
                $data[] = $d;
            }

            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }
        //print_r($data);
        //die();
        return $data;
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'msg' => 'El documento ha cambiado el estado a ELIMINAR, en un plazo de un día el documento será eliminado.'
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        //print_r($res);
        //die();
        if ($res['status'] == 200) {

            //ACTUALIZAMOS EL ESTADO DEL DOCUMENTO PENDIENTE APROBACIÓN
            $fields = array(
                "Field" => array()
            );
            $fields['Field'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'ELIMINAR'
            );

            $res = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['ndoc']);
        }

        print(json_encode($data));
    }

    private function _send_aprobacion($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => ''
        );

        if ($data['status'] == 1) {
            $insert = array(
                'xnif_src' => $this->app->nif,
                'xnif_dst' => $param['recurso'],
                'xdoc_id' => $param['ndoc'],
                'xfecha' => date(dateSQL),
                'xtipo' => $param['type']
            );
            $this->app->db->insert('dw_tareas', $insert);
            $data['msg_title'] = OPERATION_SUCCESS;
            $data['msg'] = RECORD_INSERT;
            $data['id'] = $this->db->last_id();
            $data['date'] = date('d-m-Y H:i:s');

            $history = array(
                'xentity' => 'EMPLEADOS',
                'xaction' => 'INSERT-TASK',
                'xid' => $data['id'],
                'xobs' => 'TASK: ' . $data['id']
            );
            $this->app->add_history($history);

            $dw = new DocuwareApi(DW_URL);
            $res = $dw->loginDW(DW_USER, DW_PASS);
            //print_r($res);
            //die();
            if ($res['status'] == 200) {

                //ACTUALIZAMOS EL ESTADO DEL DOCUMENTO PENDIENTE APROBACIÓN
                $fields = array(
                    "Field" => array()
                );
                $fields['Field'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'PENDIENTE APROBACIÓN'
                );

                $data = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $param['ndoc']);

                if ($param['type'] == 'G') {
                    //RECOGEMOS TODOS LOS TICKETS DEL DOCUMENTO
                    $cond = 'NIF:' . $this->app->nif . ';DOC_PADRE:' . $param['ndoc'];
                    //print($cond);
                    //die();
                    $response = $dw->dw_query_search_dialog(DW_FC_EMPLEADOS, DW_SD_EMPLEADOS_GASTOS, $cond, 'And');

                    //print_r($response);
                    //die();

                    $items = $response['response']['Items'];
                    //CARGAMOS TICKETS
                    foreach ($items as $k => $v) {
                        if ($dw->getValue($v['Fields'], 'SUBTIPO') == 'TICKET' || $dw->getValue($v['Fields'], 'SUBTIPO') == 'FACTURA') {
                            //ACTUALIZMOS ESTADO DE TICKETS Y FACTURAS DEL DOCUMENTO PADRE
                            $fields = array(
                                "Field" => array()
                            );
                            $fields['Field'][] = array(
                                "FieldName" => "ESTADO",
                                "Item" => 'PENDIENTE APROBACIÓN'
                            );

                            $response = $dw->dw_update_doc(DW_FC_EMPLEADOS, $fields, '', $v['Id']);
                        }
                    }
                }

                $dw->logoutDW();
            }
        }
        return $data;
    }

    private function _save($param) {
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
                    'pro' => $param['xempleado_id']
                );
                $sql = "select xhash from dw_empleados where xempleado_id=:pro";
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

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xeliminado'] = '0';
                $this->app->db->insert('dw_empleados', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'EMPLEADOS',
                    'xaction' => 'INSERT-EMPLEADO',
                    'xid' => $data['id'],
                    'xobs' => 'EMPLEADO: ' . $data['id'] . ' ' . $insert['xempleado']
                );
                $this->app->add_history($history);
            } else {
//update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xempleado_id' => $update['xempleado_id']
                );
                unset($update['xempleado_id']);
                $noquotes = array('xdatemodif');

                $this->app->db->update('dw_empleados', $update, $where, $noquotes);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xempleado_id'];

                $history = array(
                    'xentity' => 'EMPLEADOS',
                    'xaction' => 'UPDATE-EMPLEADO',
                    'xid' => $insert['xempleado_id'],
                    'xobs' => 'EMPLEADO: ' . $insert['xempleado_id'] . ' ' . $insert['xempleado'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _save_doc($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => 'Documento subido correctamente. Espere unos días para verificar el documento.',
            'action' => ''
        );

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
//print('<div>iniciado sesión</div>');
//$data = $dw->querySampleDW('75dcbfbb-db44-4068-8686-ed178ab5c5ac');
            $res = $dw->dw_add_doc_empleados(DW_FC_EMPLEADOS, $param);
//print('in');

            $dw->logoutDW();
        }


        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $cond = '';

        $sql = "select a.*,'toolbar' as toolbar"
                . " from dw_empleados a"
                . " where a.xeliminado=0 $cond"
                . " order by a.xempleado_id desc";
//print($sql);
//die();
        $data = $this->db->fetchAll($sql);

        return $data;
    }

    private function _list_dw($param) {
        $data = array();

        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {
//print('<div>iniciado sesión</div>');
//$data = $dw->querySampleDW('75dcbfbb-db44-4068-8686-ed178ab5c5ac');
            $data = $dw->dw_query_empleados(DW_FC_EMPLEADOS);
//print('in');

            $dw->logoutDW();
//if ($res['status'] == 200)
//    print('<div>cerrar sesión</div>');

            $tmp = $data;
            $data = array();
            if ($tmp['status'] == 200)
                foreach ($tmp['items'] as $k => $v)
                    $data[] = $v;
        } else {
//print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

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
                'xempleado_id' => $param['id']
            );
            $this->db->update('dw_empleados', $update, $where);
            $data['id'] = $param['id'];
            $data['activo'] = $param['value'];

            $history = array(
                'xentity' => 'EMPLEADOS',
                'xaction' => 'CHG-ACTIVO',
                'xid' => $param['id'],
                'xobs' => 'EMPLEADO: ' . $data['id'] . ' Activo: ' . $data['activo']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    public function print_vacaciones($data) {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding("UTF-8");
        define('EURO', chr(128));

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pdf->AddPage();
        $pdf->setSourceFile(TPL . '/tpl-vacaciones.pdf');
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        $pdf->SetFont('Helvetica', '', 8);

        $pdf->SetXY(160, 34);
        $pdf->Write(0, date('d/m/Y'));

        $pdf->SetXY(78, 50);
        $pdf->Write(0, $this->app->name);

        $pdf->SetXY(78, 59);
        $pdf->Write(0, $data['departamento']);

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(45, 86);
        $pdf->Write(0, $data['fi']);

        $pdf->SetXY(98, 86);
        $pdf->Write(0, $data['ff']);

        $pdf->SetXY(160, 86);
        $pdf->Write(0, $data['dias']);

        $pdf->Output($data['filename'], 'F');
        return true;
    }

    public function print_ausencia($data) {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding("UTF-8");
        define('EURO', chr(128));

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pdf->AddPage();
        $pdf->setSourceFile(TPL . '/tpl-ausencia.pdf');
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        $pdf->SetFont('Helvetica', '', 8);

        $pdf->SetXY(160, 34);
        $pdf->Write(0, date('d/m/Y'));

        $pdf->SetXY(78, 50);
        $pdf->Write(0, $this->app->name);

        $pdf->SetXY(78, 59);
        $pdf->Write(0, $data['departamento']);

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(45, 86);
        $pdf->Write(0, $data['fi']);

        $pdf->SetXY(98, 86);
        $pdf->Write(0, $data['ff']);

        $pdf->SetXY(160, 86);
        $pdf->Write(0, $data['horas']);

        $pdf->SetXY(40, 140);
        $pdf->Write(0, strtoupper(utf8_decode($data['obs'])));

        $pdf->Output($data['filename'], 'F');
        return true;
    }

    public function print_efectivo($data) {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding("UTF-8");
        define('EURO', chr(128));

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pdf->AddPage();
        $pdf->setSourceFile(TPL . '/tpl-efectivo.pdf');
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        $pdf->SetFont('Helvetica', '', 8);

        $pdf->SetXY(140, 12);
        $pdf->Write(0, date('d/m/Y'));

        $pdf->SetXY(20, 38);
        $pdf->Write(0, number_format($data['importe-total'], 2, ',', ''));

        $pdf->SetXY(75, 38);
        $pdf->Write(0, $this->app->name);

        $pdf->SetXY(13, 52);
        $pdf->Write(0, strtoupper(utf8_decode($data['obs'])));

        $pdf->SetXY(115, 93);
        if ($data['dietas'] != '')
            $data['dietas'] = number_format($data['dietas'], 2, ',', '');
        $pdf->Write(0, $data['dietas']);
        $pdf->SetXY(115, 112);
        if ($data['gasolina'] != '')
            $data['gasolina'] = number_format($data['gasolina'], 2, ',', '');
        $pdf->Write(0, $data['gasolina']);
        $pdf->SetXY(115, 131);
        if ($data['peajes'] != '')
            $data['peajes'] = number_format($data['peajes'], 2, ',', '');
        $pdf->Write(0, $data['peajes']);
        $pdf->SetXY(115, 149);
        if ($data['transporte'] != '')
            $data['transporte'] = number_format($data['transporte'], 2, ',', '');
        $pdf->Write(0, $data['transporte']);
        $pdf->SetXY(115, 170);
        if ($data['hoteles'] != '')
            $data['hoteles'] = number_format($data['hoteles'], 2, ',', '');
        $pdf->Write(0, $data['hoteles']);
        $pdf->SetXY(115, 190);
        if ($data['material'] != '')
            $data['material'] = number_format($data['material'], 2, ',', '');
        $pdf->Write(0, $data['material']);
        $pdf->SetXY(115, 208);
        if ($data['otros'] != '')
            $data['otros'] = number_format($data['otros'], 2, ',', '');
        $pdf->Write(0, $data['otros']);

        $pdf->Output($data['filename'], 'F');
        return true;
    }

    private function _create_doc_ausencia($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );
        $titulo = '';
        $param['filename'] = TMP . '/' . $this->app->rndString(10) . '.pdf';
        if ($param['tipo'] == 'vacaciones') {
            $this->print_vacaciones($param);
            $titulo = strtoupper($param['titulo'] . ' del ' . $param['fi'] . ' al ' . $param['ff'] . '  -  ' . $param['dias'] . ' días');
        }
        if ($param['tipo'] == 'ausencia') {
            $titulo = strtoupper($param['titulo'] . ' del ' . $param['fi'] . ' al ' . $param['ff'] . '  -  ' . $param['horas'] . ' horas');
            $this->print_ausencia($param);
        }
        //print_r($param);
        //die();


        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            $fields = array(
                "Fields" => array()
            );
            $fields['Fields'][] = array(
                "FieldName" => "DOCUMENT_TYPE",
                "Item" => 'AUSENCIA'
            );
            $fields['Fields'][] = array(
                "FieldName" => "NIF",
                "Item" => $this->app->nif
            );
            $fields['Fields'][] = array(
                "FieldName" => "NOMBRE",
                "Item" => $this->app->name
            );
            /*
             * $fields['Fields'][] = array(
              "FieldName" => "NO_DOCUMENTO",
              "Item" => strtoupper($param['ndoc'])
              );
             * 
             */
            $fields['Fields'][] = array(
                "FieldName" => "TITULO",
                "Item" => $titulo
            );
            $fields['Fields'][] = array(
                "FieldName" => "SUBTIPO",
                "Item" => strtoupper($param['departamento'])
            );
            $fields['Fields'][] = array(
                "FieldName" => "ESTADO",
                "Item" => 'PENDIENTE VALIDAR'
            );
            $fields['Fields'][] = array(
                "FieldName" => "FECHA",
                "Item" => date(dateSQL)
            );

            $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, $param['filename']);
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _create_doc_efectivo($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );
        $titulo = '';
        $param['filename'] = TMP . '/' . $this->app->rndString(10) . '.pdf';
        $this->print_efectivo($param);
        $titulo = strtoupper($param['titulo'] . '  -  ' . $param['importe-total']);
        //print_r($param);
        //die();


        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            if (isset($param['nobra']) && $param['nobra'] != '') {
                //BUSCAMOS OBRA
                $res = $dw->dw_query_search_dialog(DW_FC_VISTA_LOCAL_OBRAS, DW_SD_VISTA_LOCAL_OBRAS, 'CODIGO:' . strtoupper($param['nobra']));
                //print_r($res);
                if ($res['response']['Count']['Value'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'Obra no encontrada.';
                }
            }

            if ($data['status'] == 1) {

                $fields = array(
                    "Fields" => array()
                );
                $fields['Fields'][] = array(
                    "FieldName" => "DOCUMENT_TYPE",
                    "Item" => 'GASTOS'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NIF",
                    "Item" => $this->app->nif
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NOMBRE",
                    "Item" => $this->app->name
                );
                /*
                  $fields['Fields'][] = array(
                  "FieldName" => "NO_OBRA",
                  "Item" => strtoupper($param['nobra'])
                  );
                  $fields['Fields'][] = array(
                  "FieldName" => "NO_DOCUMENTO",
                  "Item" => strtoupper($param['ndoc'])
                  );
                 * 
                 */
                $fields['Fields'][] = array(
                    "FieldName" => "TITULO",
                    "Item" => strtoupper($param['titulo']) . ' Importe: ' . number_format($param['importe-total'], 2, ',', '')
                );
                $fields['Fields'][] = array(
                    "FieldName" => "IMPORTE",
                    "Item" => $param['importe-total']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "SUBTIPO",
                    "Item" => 'SOLICITUD'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "ANTICIPO",
                    "Item" => $param['fpago']
                );
                $fields['Fields'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'PENDIENTE VALIDAR'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "FECHA",
                    "Item" => date(dateSQL)
                );

                $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, $param['filename']);
            }
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    public function print_nota_gastos($data) {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding("UTF-8");
        define('EURO', chr(128));

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pdf->AddPage('L');
        $pdf->setSourceFile(TPL . '/tpl-nota-gastos.pdf');
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        $pdf->SetFont('Helvetica', '', 8);

        $pdf->SetXY(140, 12);
        $pdf->Write(0, date('d/m/Y'));

        //$pdf->SetXY(20, 38);
        //$pdf->Write(0, number_format($data['importe-total'], 2, ',', ''));

        $pdf->SetXY(55, 24);
        $pdf->Write(0, $this->app->name);

        $pdf->SetXY(13, 43);
        $pdf->SetFont('Helvetica', '', 6);

        foreach ($data['items'] as $k => $v) {
            $pdf->SetX(13);
            $pdf->Write(0, $v['xfecha_format']);
            $pdf->SetX(25);
            $pdf->Write(0, $v['xconcepto']);
            $pdf->SetX(87);
            $pdf->Write(0, $v['xorigen']);
            $pdf->SetX(108);
            $pdf->Write(0, $v['xdestino']);
            $pdf->SetX(130);
            $pdf->Write(0, $v['xtransporte_hoteles']);
            $pdf->SetX(146);
            $pdf->Write(0, $v['xpeajes_parking']);
            $pdf->SetX(172);
            $pdf->Write(0, $v['xcombustible']);
            $pdf->SetX(190);
            $pdf->Write(0, $v['xdietas']);
            $pdf->SetX(207);
            $pdf->Write(0, $v['xinvitaciones']);
            $pdf->SetX(225);
            $pdf->Write(0, $v['xmaterial']);
            $pdf->SetX(240);
            $pdf->Write(0, $v['xotros']);
            $pdf->SetX(254);
            $pdf->Write(0, $v['xnobra']);
            $pdf->Ln(4);
        }

        $pdf->SetY(164);

        $pdf->SetX(130);
        $pdf->Write(0, $data['xtransporte_total']);
        $pdf->SetX(146);
        $pdf->Write(0, $data['xpeaje_total']);
        $pdf->SetX(172);
        $pdf->Write(0, $data['xcombustible_total']);
        $pdf->SetX(190);
        $pdf->Write(0, $data['xdietas_total']);
        $pdf->SetX(207);
        $pdf->Write(0, $data['xinvitaciones_total']);
        $pdf->SetX(225);
        $pdf->Write(0, $data['xmateriales_total']);
        $pdf->SetX(240);
        $pdf->Write(0, $data['xotros_total']);

        $pdf->SetXY(267, 177);
        $pdf->Write(0, $data['xtotal']);

        //$pdf->SetXY(13, 52);
        //$pdf->Write(0, strtoupper(utf8_decode($data['obs'])));

        /*
          $pdf->SetXY(115, 93);
          if ($data['dietas'] != '')
          $data['dietas'] = number_format($data['dietas'], 2, ',', '');
          $pdf->Write(0, $data['dietas']);
          $pdf->SetXY(115, 112);
          if ($data['gasolina'] != '')
          $data['gasolina'] = number_format($data['gasolina'], 2, ',', '');
          $pdf->Write(0, $data['gasolina']);
          $pdf->SetXY(115, 131);
          if ($data['peajes'] != '')
          $data['peajes'] = number_format($data['peajes'], 2, ',', '');
          $pdf->Write(0, $data['peajes']);
          $pdf->SetXY(115, 149);
          if ($data['transporte'] != '')
          $data['transporte'] = number_format($data['transporte'], 2, ',', '');
          $pdf->Write(0, $data['transporte']);
          $pdf->SetXY(115, 170);
          if ($data['hoteles'] != '')
          $data['hoteles'] = number_format($data['hoteles'], 2, ',', '');
          $pdf->Write(0, $data['hoteles']);
          $pdf->SetXY(115, 190);
          if ($data['material'] != '')
          $data['material'] = number_format($data['material'], 2, ',', '');
          $pdf->Write(0, $data['material']);
          $pdf->SetXY(115, 208);
          if ($data['otros'] != '')
          $data['otros'] = number_format($data['otros'], 2, ',', '');
          $pdf->Write(0, $data['otros']);
         * 
         */

        $pdf->Output($data['filename'], 'F');
        return true;
    }

    private function _create_doc_nota_gastos($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        $param = $this->_load_entradas_gastos();

        $param['xtransporte_total'] = 0;
        $param['xpeaje_total'] = 0;
        $param['xcombustible_total'] = 0;
        $param['xdietas_total'] = 0;
        $param['xinvitaciones_total'] = 0;
        $param['xmateriales_total'] = 0;
        $param['xotros_total'] = 0;
        $param['xtotal'] = 0;
        foreach ($param['items'] as $k => $v) {
            $param['xtransporte_total'] += (float) str_replace(',', '.', $v['xtransporte_hoteles']);
            $param['xpeaje_total'] += (float) str_replace(',', '.', $v['xpeajes_parking']);
            $param['xcombustible_total'] += (float) str_replace(',', '.', $v['xcombustible']);
            $param['xdietas_total'] += (float) str_replace(',', '.', $v['xdietas']);
            $param['xinvitaciones_total'] += (float) str_replace(',', '.', $v['xinvitaciones']);
            $param['xmateriales_total'] += (float) str_replace(',', '.', $v['xmaterial']);
            $param['xotros_total'] += (float) str_replace(',', '.', $v['xotros']);

            $param['xtotal'] += (float) str_replace(',', '.', $v['xtransporte_hoteles']) + (float) str_replace(',', '.', $v['xpeajes_parking']) + (float) str_replace(',', '.', $v['xcombustible']) + (float) str_replace(',', '.', $v['xdietas']) + (float) str_replace(',', '.', $v['xinvitaciones']) + (float) str_replace(',', '.', $v['xmaterial']) + (float) str_replace(',', '.', $v['xotros']);
        }

        $param['xtransporte_total'] = number_format($param['xtransporte_total'], 2, ',', '');
        $param['xpeaje_total'] = number_format($param['xpeaje_total'], 2, ',', '');
        $param['xcombustible_total'] = number_format($param['xcombustible_total'], 2, ',', '');
        $param['xdietas_total'] = number_format($param['xdietas_total'], 2, ',', '');
        $param['xinvitaciones_total'] = number_format($param['xinvitaciones_total'], 2, ',', '');
        $param['xmateriales_total'] = number_format($param['xmateriales_total'], 2, ',', '');
        $param['xotros_total'] = number_format($param['xotros_total'], 2, ',', '');

        $param['xtotal_number'] = $param['xtotal'];
        $param['xtotal'] = number_format($param['xtotal'], 2, ',', '');

        $titulo = '';
        $param['filename'] = TMP . '/' . $this->app->rndString(10) . '.pdf';
        $this->print_nota_gastos($param);
        $titulo = 'NOTA DE GASTOS - IMPORTE: ' . $param['xtotal'];
        //print_r($param);
        //die();


        $dw = new DocuwareApi(DW_URL);
        $res = $dw->loginDW(DW_USER, DW_PASS);
        if ($res['status'] == 200) {

            if (isset($param['nobra']) && $param['nobra'] != '') {
                //BUSCAMOS OBRA
                $res = $dw->dw_query_search_dialog(DW_FC_VISTA_LOCAL_OBRAS, DW_SD_VISTA_LOCAL_OBRAS, 'CODIGO:' . strtoupper($param['nobra']));
                //print_r($res);
                if ($res['response']['Count']['Value'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'Obra no encontrada.';
                }
            }

            if ($data['status'] == 1) {

                $fields = array(
                    "Fields" => array()
                );
                $fields['Fields'][] = array(
                    "FieldName" => "DOCUMENT_TYPE",
                    "Item" => 'GASTOS'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NIF",
                    "Item" => $this->app->nif
                );
                $fields['Fields'][] = array(
                    "FieldName" => "NOMBRE",
                    "Item" => $this->app->name
                );
                $fields['Fields'][] = array(
                    "FieldName" => "TITULO",
                    "Item" => $titulo
                );
                $fields['Fields'][] = array(
                    "FieldName" => "IMPORTE",
                    "Item" => $param['xtotal_number']
                );
                /*
                 * $fields['Fields'][] = array(
                  "FieldName" => "ANTICIPO",
                  "Item" => $param['fpago']
                  );
                 * * 
                 */
                $fields['Fields'][] = array(
                    "FieldName" => "SUBTIPO",
                    "Item" => 'NOTA DE GASTOS'
                );

                $fields['Fields'][] = array(
                    "FieldName" => "ESTADO",
                    "Item" => 'PENDIENTE VALIDAR'
                );
                $fields['Fields'][] = array(
                    "FieldName" => "FECHA",
                    "Item" => date(dateSQL)
                );

                $data = $dw->dw_add_doc(DW_FC_EMPLEADOS, $fields, $param['filename']);

                if ($data['status'] == 200) {
                    $where = array(
                        'xnif' => $this->app->nif
                    );
                    $this->db->del('dw_notas_gastos', $where);
                }
            }
            $dw->logoutDW();
        } else {
            //print_r($res);
            $data['status'] = $res['status'];
            $data['msg'] = $res['response']['Message'];
        }

        return $data;
    }

    private function _get_form($param) {
        header('Content-type: text/html; charset=utf-8');
        $data = '';
        $url = DW_URL . DW_FORMS . '/' . $param['name'];

        die($url);

        $header = array();
        $header[] = 'Accept: text/html';
        $header[] = 'Content-type: text/html';
        $header[] = 'User-Agent: PHP/ALV1.0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print($httpCode);
        //print($res);
        //die();

        if ($httpCode == 200)
            $data = $res;
        else
            $data = '<h1>No form active.</h1>';

        return $data;
    }
}

?>
