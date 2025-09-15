<?php

include($_SERVER['DOCUMENT_ROOT'] . '/admin_mandasaldo_v2/includes/config.php');
include(INCLUDES . '/functions.php');
init_app();
$app = new App();

$val = $_POST;
$data = $val['image'];

list($type, $data) = explode(';', $data);
list(, $data) = explode(',', $data);
$data = base64_decode($data);

//file_put_contents('/tmp/image.png', $data);

file_put_contents(IMG_ENVIOS . "/{$val['referencia']}.jpg", $data);

$data = array(
    'status' => 1,
    'ref' => $val['referencia'],
    'type' => $type,
    'data' => $val['image']
);
print(json_encode($data));
