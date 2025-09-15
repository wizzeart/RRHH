<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Logs {

    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['module']) {
            case 'ms-logs-emails':
                switch ($_REQUEST['method']) {
                    case 'resend':
                        $data = $this->_resend($param);
                        print(json_encode($data));
                        break;
                    case 'list':
                        $data = $this->_get_ms_logs_emails($param);
                        print(json_encode($data));
                        break;
                    case 'view':
                        $data = array(
                            'status' => 1,
                            'file' => 'email-' . $param['id'] . '.html'
                        );
                        $val = array(
                            'email' => $param['id']
                        );

                        $sql = "select * from ms_emails where xemail_id=:email";
                        $row = $this->db->fetchRow($sql, $val);
                        if ($row) {
                            file_put_contents(PATH_TMP . "/{$data['file']}", $row['xcontent']);
                        }
                        print(json_encode($data));
                        break;
                }
                break;
            case 'ms-logs-revendedor':
                switch ($_REQUEST['method']) {
                    case 'list':
                        $data = $this->_get_ms_logs_revendedor($param);
                        print(json_encode($data));
                        break;
                }
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'ms-logs-emails':
                $page['title'] = 'Logs Emails';
                $page['subtitle'] = 'Logs Emails';

                $filtro = array(
                    'activo' => 'S',
                    'fields' => 'xcliente_id,xcliente'
                );

                $data_form = array();

                $data_form['clientes'] = $this->app->get_list_clientes($filtro);
                $data_form['revendedores'] = $this->app->get_list_revendedores($filtro);

                $data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - (150 * 24 * 3600));
                $data_form['fecha-fin'] = date('d/m/Y');

                break;
        }
    }

    private function _list($param) {
        $data = array();
        $sql = "select a.xpagina_id as xid,a.xpagina as xdesc"
                . ",'toolbar' as toolbar"
                . " from ms_paginas a"
                . " where a.xeliminado=0"
                . " order by a.xpagina_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _get_ms_logs_emails($param) {
        $data = array();
        $cond = '';
        $limit = '100';

        if (isset($param['q']) && $param['q'] != '') {
            $cond .= " and ("
                    . "a.xtitle like '%{$param['q']}%'"
                    . "or a.xemail like '%{$param['q']}%'"
                    . "or a.xadded like '%{$param['q']}%'"
                    . "or a.xcliente_id='{$param['q']}'"
                    . "or a.xpedido_id='{$param['q']}'"
                    . "or a.xtracking='{$param['q']}'"
                    . ")";
        }
        if (isset($param['pedido']) && $param['pedido'] != '') {
            $cond .= " and a.xpedido_id='{$param['pedido']}'";
        }
        if (isset($param['cli']) && $param['cli'] != '') {
            $cond .= " and a.xcliente_id='{$param['cli']}'";
        }
        if (isset($param['rev']) && $param['rev'] != '') {
            $cond .= " and a.xrevendedor_id='{$param['rev']}'";
        }
        if (isset($param['email']) && $param['email'] != '') {
            $cond .= " and a.xemail='{$param['email']}'";
        }
        if (isset($param['track']) && $param['track'] != '') {
            $cond .= " and a.xtracking='{$param['track']}'";
        }
        if (isset($param['fecha-ini']) && $param['fecha-ini'] != '') {
            $tmp = explode('/', $param['fecha-ini']);
            $cond .= " and a.xadded>='{$tmp[2]}-{$tmp[1]}-{$tmp[0]} 00:00:00'";
        }
        if (isset($param['fecha-fin']) && $param['fecha-fin'] != '') {
            $tmp = explode('/', $param['fecha-fin']);
            $cond .= " and a.xadded<='{$tmp[2]}-{$tmp[1]}-{$tmp[0]} 23:59:59'";
        }
        if (isset($param['resultados']) && $param['resultados'] != '')
            $limit = $param['resultados'];

        $sql = "select a.xemail_id,a.xtitle,a.xadded,a.xcliente_id,a.xpedido_id,a.xtracking,a.xemail,a.xrevendedor_id
            ,a.xweb_id
            ,date_format(a.xadded,'%d/%m/%Y %H:%i:%s') as xfecha_format
            from " . _DB_PREFIX_ . "emails a"
                . " where 1=1$cond"
                . " order by xemail_id desc"
                . " limit $limit";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _get_ms_logs_revendedor($param) {
        $data = array();
        $cond = '';

        $tmp = explode(',', $param['tipo']);
        $param['tipo'] = "'" . implode("','", $tmp) . "'";
        //print($param['tipo']);
        //print_r($param);
        //die();
        if (isset($param['tipo']) && $param['tipo'] != "''") {
            $cond .= " and xtipo in ({$param['tipo']})";
        } else {
            $cond .= " and xtipo in ('AC','EC','SC')";
        }

        $val = array(
            'rev' => $param['rev']
        );
        $sql = "select *
            ,date_format(a.xdatetime,'%d/%m/%Y %H:%i:%s') as xfecha_format
            from ms_log_revendedor a"
                . " where xrevendedor_id=:rev$cond"
                . " order by xlog_id desc limit 500";
        //print($sql);
        //print_r($val);
        //die();
        $data = $this->db->fetchAll($sql, $val);
        foreach ($data as $k => $v) {
            $data[$k]['xtipo_format'] = '';
            if ($v['xtipo'] == 'SC') {
                $data[$k]['xtipo_format'] = 'Salida';
            }
            if ($v['xtipo'] == 'EC') {
                $data[$k]['xtipo_format'] = 'Entrada';
            }
            if ($v['xtipo'] == 'AC') {
                $data[$k]['xtipo_format'] = 'Entrada';
            }
            $data[$k]['ximporte_format'] = number_format($v['ximporte'], 2, ',', '');
            $data[$k]['xdeposito_before_format'] = number_format($v['xdeposito_before'], 2, ',', '');
            $data[$k]['xdeposito_after_format'] = number_format($v['xdeposito_after'], 2, ',', '');
        }

        return $data;
    }

    private function _resend($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Email enviado correctamente.'
        );
        $val = array(
            'email' => $param['id']
        );

        $sql = "select * from ms_emails where xemail_id=:email";
        $email = $this->db->fetchRow($sql, $val);
        if ($email) {
            $tpl = $email['xcontent'];

            $data_email = $email;
            $data_email['xtitle'] = 'Resend: ' . $data_email['xtitle'];
            unset($data_email['xemail_id']);
            $this->db->insert('ms_emails', $data_email);

            $empresa = 'MandaSaldo';
            $userfrom = 'no-reply@mandasaldo.com';
            if ($email['xrevendedor_id'] > '0') {
                $userfrom = 'no-reply@growsolutions.biz';
                $sql = "select * from ms_revendedores where xrevendedor_id={$email['xrevendedor_id']}";
                $rev = $this->db->fetchRow($sql, $val);
                $empresa = $rev['xempresa'];
            }

            $mail = new PHPMailer();
            $mail->CharSet = 'utf-8';
            $mail->IsSMTP();                                      // set mailer to use SMTP
            $mail->Host = "smtp.dominioabsoluto.net";  // specify main and backup server
            $mail->Port = 25;
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = $userfrom;  // SMTP username
            $mail->Password = "nORep2020!!"; // SMTP password
            $mail->From = $userfrom;
            $mail->FromName = "$empresa";
            //$mail->addBCC('alvaro.ma@alvsoftware.es');
            //$mail->AddAddress('alvaro.ma@alvsoftware.es');
            $mail->AddAddress($param['email']);
            $mail->IsHTML(true);                                  // set email format to TEXT
            $mail->Subject = "$empresa: {$data_email['xtitle']}";
            $mail->Body = $tpl;
            $mail->AltBody = "Desde $empresa te re-enviamos un email.";
            //0 - no debug
            //1 - Server debug
            //2 - Server and Client debug
            //$mail->SMTPDebug = 1;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            if ($mail->Send()) {
                $data['send'] = 'S';
            } else {
                $data['status'] = 0;
                $data['error'] = $mail->ErrorInfo;
            }
        }
        return $data;
    }
}

?>
