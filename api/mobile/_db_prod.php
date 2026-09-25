<?php
/**
 * ⚠️ NO COMMITEAR — credenciales reales.
 *
 * Conexión SOLO-LECTURA que usa la API móvil contra la BD de producción
 * que el cliente ya tiene copiada en local. La web sigue usando la BD que
 * define `includes/config.php` (no la tocamos).
 */

define('MOBILE_DB_HOST', '212.104.172.216');
define('MOBILE_DB_NAME', 'mb_recursos_humanos');
define('MOBILE_DB_USER', 'allnovu-team');
define('MOBILE_DB_PASS', 'Clave123!*');
define('MOBILE_DB_PORT', '13306');

// Si está activo, ningún endpoint de la API móvil podrá ESCRIBIR.
// Login seguirá funcionando, pero no actualizará xult_acceso.
// asistencias/marcar, vacaciones/solicitar y documentos/firmar
// devolverán 403.
// ⚠️ Desactivado: la app necesita escribir (solicitar vacaciones, marcar
// asistencia, firmar). Las escrituras van a la BD de PRODUCCIÓN. Pon `true`
// si quieres volver a blindar la API en modo solo-lectura.
define('MOBILE_READONLY', false);
