<?php

function init_app() {
    //include(BASE_CLASS . DS . 'Sql.class.php');
    require(BASE_CLASS . DS . 'MSSql.class.php');
    include(BASE_CLASS . DS . 'App.class.php');
    include(BASE_CLASS . DS . 'ImageManager.php');
    include(BASE_CLASS . DS . 'class.phpmailer.php');
    include(BASE_CLASS . DS . 'PHPExcel.php');
    include(BASE_CLASS . DS . 'fpdf/fpdf.php');
}