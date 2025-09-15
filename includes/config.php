<?php

define('DS', DIRECTORY_SEPARATOR);
define('BASE', $_SERVER['DOCUMENT_ROOT']);
define('INCLUDES', BASE . DS . 'includes');
define('BASE_CLASS', BASE . DS . 'classes');
define('DOCS', BASE . DS . 'docs');
define('TMP', BASE . DS . 'tmp');
define('TPL', BASE . '/tpl');

define('KEYWEB', 'xf0lnlAXMbgK');
define('KEYWEB_PUBLIC', '80gjlfAIyIRJNpl8AOH');

define('OPERATION_SUCCESS', 'Operación satisfactoria!!');
define('OPERATION_ERROR', 'Operación errónea!!');
define('RECORD_INSERT', 'El registro se ha creado correctamente');
define('RECORD_UPDATE', 'El registro se ha modificado correctamente');

/* * **** PRODUCCIÓN  ***** */
define('_DB_SERVER_', '127.0.0.1');
define('_DB_USER_', 'pedroam');
define('_DB_PASSWD_', '2025');
define('_DB_NAME_', 'mb_recursos_humanos');
define('_DB_PREFIX_', 'mb_');

/* * **** PRODUCCIÓN SAGE  ***** */
define('_DB_SERVER_SAGE', '127.0.0.1');
define('_DB_USER_SAGE', 'pedroam');
define('_DB_PASSWD_SAGE', '2025');
define('_DB_NAME_SAGE', 'mb_recursos_humanos');

//define('dateSQL', 'Y-m-d H:i:s'); // mysql
define('dateSQL', 'd/m/Y H:i:s'); // sqlserver
date_default_timezone_set('Europe/Madrid');

