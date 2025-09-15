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
        $_FILES['file']['name'] = strtolower($_FILES['file']['name']);
        $ext_arr = explode('.', $_FILES['file']['name']);
        $ext = $ext_arr[count($ext_arr) - 1];
        $src = $_FILES['file']['tmp_name'];

        //$dst = BASE . '/tmp/' . $_FILES['file']['name'];
        $dst = PATH_TMP . '/' . $_FILES['file']['name'];

        move_uploaded_file($src, $dst);
    }
    $data = array(
        'status' => 'success',
        'module' => $_POST['module'],
        'files' => $_FILES,
        'ext' => $ext,
        'src' => $src,
        'dst' => $dst,
        'filename' => $_FILES['file']['name'],
        'url' => "/tmp/{$_FILES['file']['name']}"
    );
    print(json_encode($data));
}