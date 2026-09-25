<?php

/**
 * Módulo CambiarContrasena
 * Reescritura limpia y segura - Nivel Empresarial
 */
class CambiarContrasena
{
    private $app;
    private $db;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app->db;
    }

    /**
     * Router API principal
     */
    public function api($param)
    {
        // Asegurar cabeceras JSON
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $action = isset($param['method']) ? $param['method'] : '';

        switch ($action) {
            case 'cambiar':
                $this->procesarCambio($param);
                break;
            default:
                echo json_encode(['status' => 0, 'msg' => 'Acción no válida']);
                exit();
        }
    }

    /**
     * Procesa el cambio de contraseña con validaciones estrictas
     */
    private function procesarCambio($param)
    {
        try {
            // 1. RECEPCIÓN Y LIMPIEZA DE DATOS
            $user_id = isset($param['user_id']) ? intval($param['user_id']) : 0;
            $pass_actual = isset($param['passwordActual']) ? $param['passwordActual'] : '';
            $pass_nueva = isset($param['passwordNueva']) ? $param['passwordNueva'] : '';

            // 2. VALIDACIONES BÁSICAS
            if ($user_id <= 0 || empty($pass_actual) || empty($pass_nueva)) {
                $this->enviarRespuesta(0, 'Todos los campos son obligatorios.');
            }

            // 3. VALIDACIÓN DE SESIÓN (SEGURIDAD CRÍTICA)
            // Solo permitir que el usuario logueado cambie SU propia contraseña
            if ($user_id !== intval($this->app->user_id)) {
                error_log("Security Warning: User {$this->app->user_id} tried to change password for {$user_id}");
                $this->enviarRespuesta(0, 'Acción no autorizada.');
            }

            // 4. RECUPERACIÓN DE DATOS DEL USUARIO
            // Obtenemos pwd y xhash para validar y preservar integridad
            $sql = "SELECT xusuario_id, xpwd, xhash FROM usuarios WHERE xusuario_id = :uid LIMIT 1";
            $usuario = $this->db->fetchRow($sql, ['uid' => $user_id]);

            if (!$usuario) {
                $this->enviarRespuesta(0, 'Usuario no encontrado.');
            }

            // 5. VERIFICACIÓN DE CONTRASEÑA ACTUAL
            // Usamos KEYWEB + password_verify como estándar del sistema
            if (!password_verify(KEYWEB . $pass_actual, $usuario['xpwd'])) {
                $this->enviarRespuesta(0, 'La contraseña actual es incorrecta.');
            }

            // 6. VALIDACIONES DE NUEVA CONTRASEÑA
            if ($pass_actual === $pass_nueva) {
                $this->enviarRespuesta(0, 'La nueva contraseña debe ser diferente a la actual.');
            }

            if (strlen($pass_nueva) < 8) {
                $this->enviarRespuesta(0, 'La contraseña debe tener al menos 8 caracteres.');
            }

            // 7. PREPARACIÓN DE DATOS PARA ACTUALIZACIÓN

            // Gestión de xhash (token de seguridad)
            // Si ya existe, se mantiene. Si no, se genera uno nuevo (lógica legacy compatible)
            $xhash = !empty($usuario['xhash']) ? $usuario['xhash'] : $this->app->rndString(100);

            // Generación del nuevo hash seguro
            $nuevo_hash = password_hash(KEYWEB . $pass_nueva, PASSWORD_DEFAULT);

            // 8. PERSISTENCIA EN BASE DE DATOS
            $update_data = [
                'xpwd' => $nuevo_hash,
                'xhash' => $xhash,
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL) // Usamos constante global dateSQL
            ];

            $where_data = [
                'xusuario_id' => $user_id
            ];

            // Ejecutamos update
            $resultado = $this->db->update('usuarios', $update_data, $where_data);

            if ($resultado) {
                // 9. AUDITORÍA
                $this->registrarHistorial($user_id);

                // 10. RESPUESTA EXITOSA
                // 10. RESPUESTA EXITOSA
                $this->enviarRespuesta(1, 'Contraseña actualizada correctamente.');
            } else {
                error_log("Db Error: Failed to update password for user {$user_id}");
                $this->enviarRespuesta(0, 'Error interno al guardar los cambios.');
            }

        } catch (Exception $e) {
            error_log("Exception in CambiarContrasena: " . $e->getMessage());
            $this->enviarRespuesta(0, 'Ocurrió un error inesperado al procesar la solicitud.');
        }
    }

    /**
     * Helper para enviar respuestas JSON y salir
     */
    private function enviarRespuesta($status, $msg)
    {
        echo json_encode(['status' => $status, 'msg' => $msg]);
        exit();
    }

    /**
     * Helper para registrar en historial
     */
    private function registrarHistorial($user_id)
    {
        if (method_exists($this->app, 'add_history')) {
            $history = [
                'xentity' => 'USUARIOS',
                'xaction' => 'UPDATE-PWD',
                'xid' => $user_id,
                'xobs' => 'CAMBIO DE CONTRASEÑA USUARIO'
            ];
            $this->app->add_history($history);
        }
    }
}