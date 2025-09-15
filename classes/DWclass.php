<?php

/**
 * Herramientas de acceso a la API de Docuware
 *
 * @author Alvaro Muñoz Alarcón 626425472 alvaro76es@gmail.com alvaro.ma@alvsoftware.es
 */
class DocuwareApi {

    var $url; //dirección completa de docuware
    var $sdk; //ruta del sdk
    var $url_only;
    var $token;

    public function __construct($url) {
        $this->sdk = '/Docuware/Platform';
        $this->url = $url . $this->sdk;
        $this->url_only = $url;
        $token = '';
    }

    public function querySampleDW($guid = '') {
        $data = array(
            'status' => 0,
            'response' => array()
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents?start=0&count=10";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();

        $data['status'] = $httpCode;
        if ($httpCode == 200)
            $data['response'] = json_decode($res, true);
        else
            $data['response'] = $res;

        return $data;
    }

    public function dw_download_doc_nomina($guid = '', $doc_id = '') {
        $data = array(
            'status' => 0,
            'doc' => "$doc_id.pdf"
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents/$doc_id/FileDownload?targetFileType=Auto&keepAnnotations=false";
        //print($url);
        //die();
        //url = "{0}FileCabinets/{1}/Documents/{2}/FileDownload?targetFileType=Auto&keepAnnotations=false".format(mUrl, cfg['guid_archivador'], param['DWDOCID'])
        #print(url)
        #sys.exit()
        //r = s.get(url, headers=header_data, stream=True)

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Content-Type: application/octet-stream';
        //$header[] = 'Content-Disposition: attachment; filename="' . $data['doc'] . '"';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();
        if ($httpCode == 200) {
            header('Content-type: application/pdf');
            //header('Content-Disposition: attachment; filename="' . $data['doc'] . '"');
            header('Content-Disposition: inline; filename="' . $data['doc'] . '"');
            print($res);
        } else {
            $data['status'] = $httpCode;
            $data['response'] = $res;
            print(json_encode($data));
        }
        die();

        //return $data;
    }

    public function dw_download_document($guid = '', $doc_id = '', $ext = '.pdf', $target = 'Auto') {
        $data = array(
            'status' => 0,
            'doc' => $this->rndString(10) . $ext,
            'doc_base64' => ''
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents/$doc_id/FileDownload?targetFileType=$target&keepAnnotations=false";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Content-Type: application/octet-stream';
        $header[] = 'Authorization: Bearer ' . $this->token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();
        $data['status'] = $httpCode;

        if ($httpCode == 200) {

            //header('Content-type: application/pdf');
            //header('Content-Disposition: attachment; filename="' . $data['doc'] . '"');
            //header('Content-Disposition: inline; filename="' . $data['doc'] . '"');
            //print($res);
            //die();
            $data['doc_base64'] = base64_encode($res);
        } else {
            $data['status'] = $httpCode;
            $data['response'] = $res;
            print(json_encode($data));
        }

        return $data;
    }

    public function dw_download_section($guid = '', $sec_id = '', $ext = '.pdf', $target = 'Auto') {
        $data = array(
            'status' => 0,
            'doc' => $this->rndString(10) . $ext,
            'doc_base64' => ''
        );

        $url = "{$this->url}/FileCabinets/$guid/Sections/$sec_id/FileDownload?targetFileType=$target&keepAnnotations=false";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Content-Type: application/octet-stream';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();
        $data['status'] = $httpCode;

        if ($httpCode == 200) {

            //header('Content-type: application/pdf');
            //header('Content-Disposition: attachment; filename="' . $data['doc'] . '"');
            //header('Content-Disposition: inline; filename="' . $data['doc'] . '"');
            //print($res);
            //die();
            $data['doc_base64'] = base64_encode($res);
        } else {
            $data['status'] = $httpCode;
            $data['response'] = $res;
            print(json_encode($data));
        }

        return $data;
    }

    public function dw_query_proveedores($guid = '') {
        $data = array(
            'status' => 0,
            'items' => array()
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents?start=0&count=100";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();

        $data['status'] = $httpCode;
        if ($httpCode == 200) {
            $response = json_decode($res, true);
            $items = $response['Items'];
            foreach ($items as $k => $v) {
                //$this->getValue($v, 'DWDOCID')
                $d = array(
                    'doc-id' => $v['Id'],
                    'tipo' => $this->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'estado' => $this->getValue($v['Fields'], 'STATUS')
                );
                $data['items'][] = $d;
            }
        } else {
            $data['items'] = $res;
        }

        return $data;
    }

    public function dw_add_doc($guid = '', $fields = array(), $filepath = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //print_r($fields);
        //print($filepath);
        //die();
        //DAMOS DE ALTA EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        $tmp = explode('/', $filepath);
        $filename = $tmp[count($tmp) - 1];

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();
        //$data['status'] = $httpCode;
        if ($httpCode == 200) {
            $response = json_decode($res, true);
            $data['DOCID'] = $response['Id'];
            //print_r($response);
            //die();
            if ($filepath != '') {
                //ASOCIAMOS EL DOCUMENTO PDF AL DOCUMENTO DOCUWARE COMO UNA SECCIÓN
                $url = "{$this->url}/FileCabinets/$guid/Sections?docid={$data['DOCID']}";
                //print($url);
                //die();

                $header = array();
                $header[] = "Content-Disposition: file; filename=\"{$filename}\"";
                $header[] = 'Accept: application/json';
                $header[] = 'Content-Type: octet-stream';
                $header[] = 'User-Agent: PHP/ALV1.0';
                $header[] = 'Authorization: Bearer ' . $this->token;

                //print_r($header);
                //die();
                //print(json_encode($fields));
                //die();
                $file = file_get_contents($filepath);
                //$file = curl_file_create($filepath, 'application/msword', 'file');
                //print_r($file);
                //die();
                //$file = new CURLFile($filepath, 'application/msword', 'test_name');
                //$fields = array();
                //$fields = curl_file_create($filepath);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
                //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
                //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
                //curl_setopt($ch, CURLOPT_POSTFIELDS, curl_file_create($filepath, 'application/msword', 'file'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
                //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                //curl_setopt($ch, CURLOPT_HEADER, true);

                $res = curl_exec($ch);
                //print_r($res);
                //die();
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $data['status'] = $httpCode;
            }
            if ($httpCode == 200) {
                $data['status'] = 1;
                $data['msg'] = 'Documento subido correctamente.';
            }
            //print($httpCode);
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res . ' json:' . json_encode($fields);
        }

        return $data;
    }

    public function dw_add_doc_v2($guid = '', $fields = array(), $filepath = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //DAMOS DE ALTA EL DOCUMENTO FICHERO
        $url = "{$this->url}/FileCabinets/$guid/Documents";
        //print($url);
        //die();

        $tmp = explode('/', $filepath);
        $filename = $tmp[count($tmp) - 1];

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/octet-stream';
        //$header[] = 'Content-Type: application/vnd.docuware.platform.inputdocument+json';

        $header[] = 'Cache-Control: no-cache';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Content-Disposition: file; filename="' . $filename . '"';

        $file = file_get_contents($filepath);
        /* $index = array(
          'file' => $file
          );
         * 
         */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //CURLOPT_FILE
        //print_r($httpCode);
        //print_r($res);
        //die();
        //$data['status'] = $httpCode;
        if ($httpCode == 200) {
            $response = json_decode($res, true);
            $data['DOCID'] = $response['Id'];
            //print_r($response);
            //die();
            //ACTUALIZAMOS DOCUMENTO CON LOS INDICES OBTENIDOS
            $index = array(
                "Field" => array()
            );
            foreach ($fields as $k => $v) {
                $index['Field'][] = array(
                    "FieldName" => $k,
                    "Item" => $v
                );
            }
            //print_r($index);
            //die();

            $url = "{$this->url}/FileCabinets/$guid/Documents/{$data['DOCID']}/Fields";
            //print($url);
            //die();

            $header = array();
            $header[] = 'Accept: application/json';
            $header[] = 'Content-Type: application/json';
            $header[] = 'User-Agent: PHP/ALV1.0';
            $header[] = 'Cache-Control: no-cache';

            //print(json_encode($fields));
            //die();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
            curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($index));
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data['status'] = $httpCode;
            if ($httpCode == 200) {
                $data['msg'] = 'Documento subido correctamente.';
            }
            //print($httpCode);
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_add_section($guid = '', $filepath = '', $doc_id = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //DAMOS DE ALTA EL DOCUMENTO FICHERO
        $url = "{$this->url}/FileCabinets/$guid/Sections?docid={$doc_id}";
        //print($url);
        //die();

        $tmp = explode('/', $filepath);
        $filename = $tmp[count($tmp) - 1];

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/octet-stream';
        //$header[] = 'Content-Type: application/vnd.docuware.platform.inputdocument+json';

        $header[] = 'Cache-Control: no-cache';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Disposition: file; filename="' . $filename . '"';

        $file = file_get_contents($filepath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //CURLOPT_FILE
        //print_r($httpCode);
        //print_r($res);
        //die();
        //$data['status'] = $httpCode;
        $data['response'] = $res;

        return $data;
    }

    public function dw_update_section($guid = '', $filepath = '', $sec_id = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //DAMOS DE ALTA EL DOCUMENTO FICHERO
        $url = "{$this->url}/FileCabinets/$guid/Sections/$sec_id/Data";
        //print($url);
        //die();

        $tmp = explode('/', $filepath);
        $filename = $tmp[count($tmp) - 1];

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/octet-stream';
        //$header[] = 'Content-Type: application/vnd.docuware.platform.inputdocument+json';

        $header[] = 'Cache-Control: no-cache';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Disposition: file; filename="' . $filename . '"';

        $file = file_get_contents($filepath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //CURLOPT_FILE
        //print_r($httpCode);
        //print_r($res);
        //die();
        //$data['status'] = $httpCode;
        if ($httpCode == 200) {
            $data['response'] = json_decode($res, true);
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_add_doc_basket($guid = '', $fields = array(), $filepath = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //print_r($fields);
        //print($filepath);
        //die();
        //DAMOS DE ALTA EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents";
        //print($url);
        //die();

        $tmp = explode('/', $filepath);
        $filename = $tmp[count($tmp) - 1];
        //print($filename);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Content-Type: application/pdf';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Disposition: file; filename="' . $filename . '"';

        $file = file_get_contents($filepath);

        //print_r($file);
        //die();
        $fields_file = array(
            'file' => $file
        );

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_file);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();
        //$data['status'] = $httpCode;
        if ($httpCode == 200) {
            $response = json_decode($res, true);
            $data['DOCID'] = $response['Id'];

            /*
             * {
              "Field":
              [
              {
              "SystemField": false,
              "FieldName": "EXENTIS",
              "FieldLabel": "Nº Documento",
              "IsNull": false,
              "ReadOnly": false,
              "Item": "xxx",
              "ItemElementName": "String"
              }
              ]
              }
             */

            //print_r(json_encode($fields));
            //die();

            $url = "{$this->url}/FileCabinets/$guid/Documents/{$data['DOCID']}/Fields";

            $header = array();
            $header[] = 'Accept: application/json';
            $header[] = 'User-Agent: PHP/ALV1.0';
            $header[] = 'Content-Type: application/json';
            $header[] = 'Authorization: Bearer ' . $this->token;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
            //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
            //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //print_r($res);
            //die();

            $data['status'] = $httpCode;
            if ($httpCode == 200) {
                $data['msg'] = 'Documento subido correctamente.';
            }
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_update_doc($guid = '', $fields = array(), $filepath = '', $doc_id = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //print_r($fields);
        //print($filepath);
        //die();
        //ACTUALIZAMOS EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents/$doc_id/Fields";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die('OK');
        $data['status'] = $httpCode;
        if ($httpCode == 200) {
            $data['msg'] = 'Documento actualizado correctamente.';
        }

        if ($httpCode == 200 && $filepath != '') {
            $tmp = explode('/', $filepath);
            $filename = $tmp[count($tmp) - 1];

            $response = json_decode($res, true);
            $data['DOCID'] = $doc_id;
            //print_r($response);
            //die();
            //ASOCIAMOS EL DOCUMENTO PDF AL DOCUMENTO DOCUWARE COMO UNA SECCIÓN
            $url = "{$this->url}/FileCabinets/$guid/Sections?docid={$data['DOCID']}";
            //print($url);
            //die();

            $header = array();
            $header[] = 'Accept: application/json';
            $header[] = 'Content-Type: application/json';
            $header[] = 'User-Agent: PHP/ALV1.0';
            $header[] = "Content-Disposition: file; filename=\"{$filename}\"";
            $header[] = 'Authorization: Bearer ' . $this->token;

            //print(json_encode($fields));
            //die();
            $file = file_get_contents($filepath);
            //print_r($file);
            //die();
            //$cfile = new CURLFile(PATH_TMP . '/' . $param['image'], 'application/pdf', $param['image']);
            $fields = array(
                'file' => $file
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
            //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
            //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data['status'] = $httpCode;
            if ($httpCode == 200) {
                $data['msg'] = 'Documento subido y actualizado correctamente.';
            }
            //print($httpCode);
            //print_r($res);
            //die();
        } else {
            $data['response'] = json_decode($res, true);
        }

        return $data;
    }

    public function dw_get_doc($guid = '', $doc_id = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //DAMOS DE ALTA EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents/$doc_id";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die('OK');
        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            $response = json_decode($res, true);
            $data['response'] = $response;
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_get_all_docs($guid = '', $cond = '', $count = 500) {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents?count=$count";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die('OK');
        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            $response = json_decode($res, true);
            $data['response'] = $response;
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_get_all_index($guid = '', $doc_id = '') {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents/$doc_id/Fields";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die('OK');
        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            $response = json_decode($res, true);
            $data['response'] = $response;
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_get_all_index_table($guid = '', $doc_id = '', $field = array()) {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents/$doc_id/TableFieldRows?tableFieldName=$field";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die('OK');
        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            $response = json_decode($res, true);
            $data['response'] = $response;
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_add_doc_proveedores($guid = '', $param = array()) {
        $data = array(
            'status' => 0,
            'items' => array()
        );

        //DAMOS DE ALTA EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        $fields = array(
            "Fields" => array()
        );
        $fields['Fields'][] = array(
            "FieldName" => "DOCUMENT_TYPE",
            "Item" => $param['tipo']
        );
        $fields['Fields'][] = array(
            "FieldName" => "STATUS",
            "Item" => $param['estado']
        );

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();

        $data['status'] = $httpCode;
        if ($httpCode == 200) {
            $response = json_decode($res, true);
            $data['DOCID'] = $response['Id'];
            //print_r($response);
            //die();
            //ASOCIAMOS EL DOCUMENTO PDF AL DOCUMENTO DOCUWARE COMO UNA SECCIÓN
            $url = "{$this->url}/FileCabinets/$guid/Sections?docid={$data['DOCID']}";
            //print($url);
            //die();

            $header = array();
            $header[] = 'Accept: application/json';
            $header[] = 'Content-Type: application/json';
            $header[] = 'User-Agent: PHP/ALV1.0';
            $header[] = "Content-Disposition: file; filename=\"{$param['image']}\"";

            //print(json_encode($fields));
            //die();
            $file = file_get_contents(PATH_TMP . '/' . $param['image']);
            //print_r($file);
            //die();
            //$cfile = new CURLFile(PATH_TMP . '/' . $param['image'], 'application/pdf', $param['image']);
            $fields = array(
                'file' => $file
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
            curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //print($httpCode);
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_query_albaranes($guid = '') {
        $data = array(
            'status' => 0,
            'items' => array()
        );

        $url = "{$this->url}/FileCabinets/$guid/Documents?start=0&count=500";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();

        $data['status'] = $httpCode;
        if ($httpCode == 200) {
            $response = json_decode($res, true);
            $items = $response['Items'];
            foreach ($items as $k => $v) {
                //$this->getValue($v, 'DWDOCID')
                $d = array(
                    'doc-id' => $v['Id'],
                    'tipo' => $this->getValue($v['Fields'], 'DOCUMENT_TYPE'),
                    'estado' => $this->getValue($v['Fields'], 'STATUS'),
                    'ndoc' => $this->getValue($v['Fields'], 'DOCUMENT_NUMBER'),
                    'empresa' => $this->getValue($v['Fields'], 'COMPANY'),
                    'fecha' => $this->getValue($v['Fields'], 'DATE')
                );
                $data['items'][] = $d;
            }
        } else {
            $data['items'] = $res;
        }

        return $data;
    }

    public function dw_add_doc_proveedores_new($guid = '', $param = array()) {
        $data = array(
            'status' => 0,
            'items' => array()
        );

        //DAMOS DE ALTA EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/pdf';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Content-Disposition: file; filename="' . $param['doc'] . '"';

        $file = file_get_contents(PATH_TMP . '/' . $param['doc']);
        //$cfile = new CURLFile(PATH_TMP . '/' . $param['image'], 'application/pdf', $param['image']);
        $fields = array(
            'file' => $file
        );

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();

        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            //print($httpCode);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_add_doc_test_new($guid = '', $param = array(), $filepath = '') {
        $data = array(
            'status' => 0,
            'items' => array()
        );

        //DAMOS DE ALTA EL DOCUMENTO CON LOS DATOS OBTENIDOS
        $url = "{$this->url}/FileCabinets/$guid/Documents";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        //$header[] = 'Content-Type: multipart/form-data';
        $header[] = 'Content-Type: application/pdf';
        //$header[] = 'X-File-ModifiedDate: 2020-08-26T00:00:00.000Z';
        $header[] = 'User-Agent: PHP/ALV1.0';
        //$header[] = 'Content-Disposition: attachment; name="file"';
        $header[] = 'Content-Disposition: attachment; name=file; filename="parte.doc"';

        $file = file_get_contents($filepath);
        //$cfile = new CURLFile(PATH_TMP . '/' . $param['image'], 'application/pdf', $param['image']);
        $fields = array(
            'document' => json_encode($param),
            'file' => $file
        );
        /*
          $fields = array(
          'file' => $file
          ); */
        //print_r($fields);
        //die();
        //print(json_encode($fields));
        //die('1');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        print_r($res);
        die();

        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            $data['response'] = $res;
            //print($httpCode);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function loginDW_v2($usr, $pwd) {
        $data = array(
            'status' => 200,
            'login' => 0,
            'msg' => '',
            'response' => array()
        );

        if (file_exists(DW_TOKEN)) {
            $token = json_decode(file_get_contents(DW_TOKEN), true);
            if (time() > $token['time'] + $token['expires_in']) {
                $data['login'] = 1;
            } else {
                $this->token = $token['access_token'];

                $data['status'] = 200;
                $data['msg'] = 'Token recuperado.';
            }
        } else {
            $data['login'] = 1;
        }


        if ($data['login'] == 1) {
            $url = 'https://login-emea.docuware.cloud/69fae1b4-13d6-4906-ac47-2b1e75c68e21/connect/token';
            //$url = "{$this->url}/Account/Logon";
            //print($url);
            //die();

            $header = array();
            $header[] = 'Accept: application/json';
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
            $header[] = 'User-Agent: PHP/ALV1.0';

            /*
              $val = array(
              'UserName' => $usr,
              'Password' => $pwd,
              'RedirectToMyselfInCaseOfError' => false,
              'RememberMe' => true,
              'Organization' => '',
              'LicenseType' => ''
              );
             */

            $val = array(
                'grant_type' => 'password',
                'scope' => 'docuware.platform',
                'client_id' => 'docuware.platform.net.client',
                'username' => 'Administrator',
                'password' => 'Simersa@Alvaro2023'
            );
            $fields = http_build_query($val);
            //print(json_encode($fields));
            //die();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            //curl_setopt($ch, CURLOPT_USERAGENT, 'PHP/ALV1.0');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            //curl_setopt($ch, CURLOPT_HEADER, true);
            //curl_setopt($ch, CURLOPT_HEADERFUNCTION, "curlResponseHeaderCallback");
            //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
            //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            /*
              if (curl_errno($ch)) {
              $httpCode=curl_error($ch);
              } else {
              // Muestra la respuesta
              echo $res;
              }
             */

            //print_r($res);
            //die();

            curl_close($ch);

            $token = json_decode($res, true);
            $this->token = $token['access_token'];
            $token['time'] = time();
            file_put_contents(DW_TOKEN, json_encode($token));

            $data['status'] = $httpCode;
            $data['response'] = json_decode($res, true);
        }


        //$data['cookies'] = $cookies_result;
        //print_r($data);
        //die();

        return $data;
    }

    public function loginDW($usr, $pwd) {
        $data = array(
            'status' => 0,
            'response' => array()
        );

        $url = "{$this->url}/Account/Logon";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        $header[] = 'User-Agent: PHP/ALV1.0';

        /*
          $val = array();
          $val[] = "UserName = $usr";
          $val[] = "Password = $pwd";
          $val[] = "RedirectToMyselfInCaseOfError = false";
          $val[] = "RememberMe = true";
          $val[] = "Organization = ''";
          $val[] = "LicenseType = ''";
          $fields = implode('&', $val);
         * 
         */

        $val = array(
            'UserName' => $usr,
            'Password' => $pwd,
            'RedirectToMyselfInCaseOfError' => false,
            'RememberMe' => true,
            'Organization' => '',
            'LicenseType' => ''
        );
        $fields = http_build_query($val);
        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_USERAGENT, 'PHP/ALV1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_HEADERFUNCTION, "curlResponseHeaderCallback");

        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /*
          if (curl_errno($ch)) {
          $httpCode=curl_error($ch);
          } else {
          // Muestra la respuesta
          echo $res;
          }
         */

        curl_close($ch);

        $data['status'] = $httpCode;
        $data['response'] = json_decode($res, true);
        //$data['cookies'] = $cookies_result;
        //print_r($data);
        //die();

        return $data;
    }

    public function logoutDW() {
        $data = array(
            'status' => 0,
            'response' => array()
        );

        $url = "{$this->url}/Account/LogOff";

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data['status'] = $httpCode;
        $data['response'] = json_decode($res, true);

        return $data;
    }

    public function dw_org() {
        $data = array(
            'status' => 0,
            'response' => array()
        );

        $url = "{$this->url}/Organizations";

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data['status'] = $httpCode;
        $data['response'] = json_decode($res, true);

        return $data;
    }

    public function getValue($lst, $name) {
        $value = '';
        foreach ($lst as $k => $v) {
            if ($v['FieldName'] == $name && isset($v['Item']))
                $value = $v['Item'];
        }
        return $value;
    }

    /*
      public function setValue($lst, $name, $value) {
      foreach ($lst as $k => $v) {
      if ($v['FieldName'] == $name) {
      if (!isset($v['Item'])) {
      $lst[$k]['IsNull'] = false;
      $lst[$k]['Item'] = '';
      }
      $lst[$k]['Item'] = $value;
      }
      }
      return $value;
      }
     * 
     */

    public function setValue($lst, $name, $value) {
        foreach ($lst as $k => $v) {
            if ($v['FieldName'] == $name) {
                if (!isset($v['Item'])) {
                    //$lst[$k]['IsNull'] = '';
                    $lst[$k]['IsNull'] = false;
                    $lst[$k]['Item'] = $value;
                } else {
                    $lst[$k]['Item'] = $value;
                }
            }
        }
        return $lst;
    }

    public function getTable($lst, $name) {
        //DEVUELVE TODAS LAS LINEAS DE UN CAMPO TABLA DE UN DOCUMENTO
        $value = array();
        foreach ($lst as $k => $v) {
            if ($v['FieldName'] == $name && isset($v['Item'])) {
                $value = $v['Item']['Row'];
            }
        }
        return $value;
    }

    public function getDateValue($s) {
        #s="/Date(1598572800000)/".replace('/Date(','').replace(')/','')
        $d = '';
        if (strlen($s) > 0) {

            $s = str_replace('/Date(', '', $s);
            $s = str_replace(')/', '', $s);
            $ms = $s / 1000;
            $d = date('d/m/Y', $ms);
        }
        return $d;
    }

    public function getDateValueISO($s) {
        #s="/Date(1598572800000)/".replace('/Date(','').replace(')/','')
        $d = '';
        if (strlen($s) > 0) {

            $s = str_replace('/Date(', '', $s);
            $s = str_replace(')/', '', $s);
            $ms = $s / 1000;
            $d = date('Y-m-d', $ms);
        }
        return $d;
    }

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
            for ($i = 1; $i <= $length; $i++) {
                //mt_srand((double) microtime() * 1000000);
                //$num = mt_rand(1, count($source));
                $num = rand(1, count($source));
                $rstr .= $source[$num - 1];
            }
        }
        return $rstr;
    }

    public function dwGetDialogExpression($guidArchivador = '', $guidBuscador = '', $param = array()) {
        $data = array(
            'status' => 0,
            'response' => ''
        );

        $url = "{$this->url}/FileCabinets/$guidArchivador/Query/DialogExpressionLink?dialogId=$guidBuscador";
        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        $fields = $param;

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print($httpCode);
        //print_r($res);
        //die();

        $data['status'] = $httpCode;
        if ($httpCode == 200) {
            $data['status'] = 1;
            $data['response'] = $res;

            //print($httpCode);
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }

    public function dw_get_all_docs_query($query, $count = 500) {
        $data = array(
            'status' => 0,
            'msg' => ''
        );

        //$query = str_replace('Platform', 'Search', $query);

        $url = "{$this->url_only}$query&start=0&count=$count";

        //print($url);
        //die();

        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-Type: application/json';
        $header[] = 'User-Agent: PHP/ALV1.0';
        $header[] = 'Authorization: Bearer ' . $this->token;

        //print(json_encode($fields));
        //die();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DW_CURLOPT_SSL_VERIFYPEER);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, DW_COOKIES);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //print_r($httpCode);
        //print_r($res);
        //die();
        $data['status'] = $httpCode;
        if ($httpCode == 200) {

            $response = json_decode($res, true);
            $data['response'] = $response;
            //print_r($res);
            //die();
        } else {
            $data['response'] = $res;
        }

        return $data;
    }
}

function curlResponseHeaderCallback($ch, $headerLine) {
    global $cookies_result;

    if (strpos($headerLine, 'DWPLATFORMAUTH') !== FALSE && !strpos($headerLine, 'DWPLATFORMAUTH=;') !== FALSE) {
        $tmp = explode(';', $headerLine);
        $tmp = explode(' ', $tmp[0]);
        $cookies_result[] = $tmp[1];
    }
    if (strpos($headerLine, 'DWPLATFORMBROWSERID') !== FALSE && !strpos($headerLine, 'DWPLATFORMBROWSERID=;') !== FALSE) {
        $tmp = explode(';', $headerLine);
        $tmp = explode(' ', $tmp[0]);
        $cookies_result[] = $tmp[1];
    }

//print($headerLine);
//if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1)
//    $cookies_result[] = $cookie;
    return strlen($headerLine); // Needed by curl
}
