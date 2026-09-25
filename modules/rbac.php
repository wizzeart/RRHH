<?php
/**
 * RBAC (Role-Based Access Control)
 * 
 * REGLAS SIMPLIFICADAS:
 *  - xrol_id = 1 → Admin → acceso total a todos los módulos
 *  - xrol_id = 2 → Trabajador → solo acceso a módulos de trabajador (ficha-trabajador, vacaciones, etc)
 *  - xrol_id = 3 → INVENTARIO → acceso a Recursos y Trabajadores
 *  - xrol_id = 4 → JEFE DE ÁREA → acceso a Trabajadores, Evaluaciones y Asistencias
 *  - Otros roles → acceso denegado
 */

/**
 * Definir módulos permitidos por rol
 */
$MODULE_PERMISSIONS = array(
    1 => array(
        // Admin - acceso a TODO
        'all' => true,
        'home' => true
    ),
    2 => array(
        // Trabajador - solo estos módulos
        'ficha-trabajador' => true,
        'vacaciones' => true,
        'planificacion-vacaciones' => true,
        'list-trabajadores' => true,
        'trabajadores' => true,
        'home' => true,
        'logout' => true,
        'cambiar-contrasena' => true,
        'chat' => true
    ),
    3 => array(
        // INVENTARIO - acceso a Recursos y Trabajadores
        'list-recursos' => true,
        'gestion-recursos' => true,
        'list-trabajadores' => true,
        'ficha-trabajador' => true,
        'logout' => true
    ),
    4 => array(
        // JEFE DE ÁREA - acceso a Trabajadores, Evaluaciones y Asistencias
        'list-trabajadores' => true,
        'ficha-trabajador' => true,
        'list-evaluaciones' => true,
        'evaluaciones' => true,
        'list-asistencias' => true,
        'asistencias' => true,
        'home' => true,
        'logout' => true
    )
);

/**
 * Validar si un rol tiene permiso para acceder a un módulo
 * 
 * @param int $rolId ID del rol (xrol_id)
 * @param string $module Nombre del módulo
 * @return bool true si tiene acceso, false si no
 */
function has_module_access($rolId, $module) {
    global $MODULE_PERMISSIONS;
    
    // Si el rol no existe en las permisos, denegar
    if (!isset($MODULE_PERMISSIONS[$rolId])) {
        return false;
    }
    
    $permissions = $MODULE_PERMISSIONS[$rolId];
    
    // Admin = acceso total
    if (isset($permissions['all']) && $permissions['all'] === true) {
        return true;
    }
    
    // Verificar si el módulo está en la lista de permitidos
    if (isset($permissions[$module]) && $permissions[$module] === true) {
        return true;
    }
    
    return false;
}

/**
 * Bloquear acceso si no tiene permiso
 */
function enforce_access($rolId, $module, $app = null) {
    global $MODULE_PERMISSIONS;
    
    // Si no hay rol definido, no validar RBAC (será redirigido a login en index.php)
    if (empty($rolId)) {
        return;
    }
    
    // Convertir rolId a integer
    $rolId = (int) $rolId;
    
    if (!has_module_access($rolId, $module)) {
        http_response_code(403);
        die(json_encode(array(
            'status' => 0,
            'msg' => 'No tienes permiso para acceder a este módulo (Rol: ' . $rolId . ', Módulo: ' . $module . ')'
        )));
    }
    
    // Validación especial para trabajadores en ficha-trabajador
    if ($rolId == 2 && $module == 'ficha-trabajador' && $app !== null) {
        $requested_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
        
        // Si solicita una ficha específica, debe ser la suya propia
        if ($requested_id) {
            // Obtener el id del trabajador asociado al usuario actual
            // La relación es: trabajadores.usuario_id = xusuario_id (que es $app->user_id)
            $sql = "SELECT id FROM trabajadores WHERE usuario_id = {$app->user_id} LIMIT 1";
            $trabajador = $app->db->fetchRow($sql);
            
            $trabajador_id = $trabajador ? $trabajador['id'] : null;
            
            // Verificar que el ID solicitado coincida con el del trabajador
            if ($requested_id != $trabajador_id) {
                http_response_code(403);
                die(json_encode(array(
                    'status' => 0,
                    'msg' => 'Solo puedes acceder a tu propia ficha'
                )));
            }
        }
    }
}

?>
