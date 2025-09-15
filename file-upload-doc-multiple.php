<?php

//header('Content-type: application/json; charset=utf-8');
include($_SERVER['DOCUMENT_ROOT'] . '/admin_mandasaldo_v2/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

$val = $_POST;
if ($app->user_id == '') {
    $data = array(
        'status' => 0,
        'msg_title' => 'Operación incorrecta!!',
        'msg' => 'Sesión finalizada. Vuelva a iniciar sesión.'
    );
    die(json_encode($data));
} else {
    if (!empty($_FILES)) {
        //print_r($_FILES['file']);
        //die();
        foreach ($_FILES['file']['name'] as $k => $v) {
            //print_r($v);
            //die();
            //$v['name'] = strtolower($v['name']);
            //$ext_arr = explode('.', $v['name']);
            //$ext = $ext_arr[count($ext_arr) - 1];
            $src = $_FILES['file']['tmp_name'][$k];

            $dst = BASE . '/tmp/' . $v;
            //$dst = PATH_TMP . $_FILES['file']['name'];
            //print($src);
            //print($dst);
            //die();
            move_uploaded_file($src, $dst);
        }
    }
    $data = array(
        'status' => 'success',
        //'dst_image' => $_POST['dst'],
        //'files' => $_FILES,
        //'ext' => $ext,
        //'src' => $src,
        //'dst' => $dst,
        'filenames' => $_FILES['file'],
            //'url' => "/tmp/???"
    );
    print(json_encode($data));
}