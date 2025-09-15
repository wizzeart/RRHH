<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Notificaciones {

    var $app;
    var $db;
    var $action;
    var $total = 0;
    var $lin = 0;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'envio-emails':
                $this->_envio_emails($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list':
                break;
        }
    }

    private function _envio_emails($param) {
        switch ($param['xdestino']) {
            // un email
            case '1':
                $tpl = file_get_contents(TPL_ADMIN . '/tpl-notificaciones.html');
                $tpl = str_replace('{{title}}', $param['xtitulo'], $tpl);
                $tpl = str_replace('{{asunto}}', $param['xasunto'], $tpl);
                $tpl = str_replace('{{body}}', $param['xcontenido'], $tpl);

                $data_email = array(
                    'xtitle' => $param['xtitulo'],
                    'xemail' => $param['xemail'],
                    'xcliente_id' => ((isset($param['xcliente'])) ? $param['xcliente'] : ''),
                    'xpedido_id' => ((isset($param['xpedido_id'])) ? $param['pedido'] : ''),
                    'xlin_id' => ((isset($param['linea'])) ? $param['linea'] : ''),
                    'xadded' => date(dateSQL),
                    'xtracking' => ((isset($param['tracking'])) ? $param['tracking'] : ''),
                    'xrevendedor_id' => ((isset($content['revendedor'])) ? $content['revendedor'] : ''),
                    'xweb_id' => ((isset($content['revendedor'])) ? $content['revendedor'] : ''),
                    'xcontent' => $tpl
                );
                $this->db->insert('ms_emails', $data_email);

                //print_r($tpl);
                //die();
                $mail = new PHPMailer();
                $mail->CharSet = 'utf-8';
                $mail->IsSMTP();                                      // set mailer to use SMTP
                $mail->Host = "smtp.dominioabsoluto.net";  // specify main and backup server
                $mail->Port = 25;
                $mail->SMTPAuth = true;     // turn on SMTP authentication
                $mail->Username = "no-reply@mandasaldo.com";
                $mail->Password = "nORep2020!!"; // SMTP password
                $mail->From = 'no-reply@growsolutions.biz';
                $mail->FromName = 'Grow Solutions';
                //$mail->addBCC('alvaro.ma@alvsoftware.es');
                //$mail->addBCC('fernando.garcia@conocea.com');				
                $mail->AddAddress($param['xemail']);
                $mail->IsHTML(true);                                  // set email format to TEXT
                $mail->Subject = $param['xasunto'];
                $mail->Body = $tpl;
                $mail->AltBody = "Desde Grow Solutions te hemos enviado un email en formato HTML";

                //0 - no debug
                //1 - Server debug
                //2 - Server and Client debug
                //$mail->SMTPDebug = 1;
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
                if ($mail->Send()) {
                    $data['status'] = 1;
                    $data['msg'] = 'Enviado con éxito eamil a ' . $param['xemail'];
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'Envio fállido';
                    $data['error'] = $mail->ErrorInfo;
                }
                print(json_encode($data));
                break;

            // envio masivo a los clientes
            case '2':
            // envio masivo a los revendedores
            case '3':
                $sql = "select * from ms_revendedores where xactivo='S' and xeliminado=0";
                $data = $this->db->fetchAll($sql);
                print(json_encode($data));
                break;
        }
    }

}

?>
