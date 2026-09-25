<?php

// Incluir configuración SMS
require_once(__DIR__ . '/../includes/config.php');

class NotificacionesSMS
{
    var $app;
    var $db;
    var $action;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param)
    {
        switch ($param['method']) {
            case 'preview-recipients':
                $data = $this->_preview_recipients($param);
                print(json_encode($data));
                break;
            case 'send-sms':
                $this->_send_sms($param);
                break;
            case 'send-sms-trabajador':
                $data = $this->_send_sms_trabajador($param);
                print(json_encode($data));
                break;
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'get-config':
                $data = $this->_get_config($param);
                print(json_encode($data));
                break;
            case 'save-config':
                $this->_save_config($param);
                break;
            case 'send-vacacion-notification':
                $this->_send_vacacion_notification($param);
                break;
        }
    }

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'notificaciones-sms':
                $data = array();
                $page['title'] = 'Nueva Notificación SMS';
                $page['subtitle'] = 'Envío de SMS Masivo';
                $data_form = array();
                $action = 'insert';
                break;
            case 'list-notificaciones-sms':
                $data = array();
                $page['title'] = 'Notificaciones SMS';
                $page['subtitle'] = 'Historial de Notificaciones Enviadas a la Pasarela SMS';
                $data_form = array();
                break;
        }
    }

    private function _preview_recipients($param)
    {
        $data = array(
            'status' => 1,
            'msg' => '',
            'total' => 0,
            'departamentos' => array(),
            'ubicaciones' => array()
        );

        try {
            $departamentos = isset($param['departamentos']) ? json_decode($param['departamentos'], true) : array();
            $ubicaciones = isset($param['ubicaciones']) ? json_decode($param['ubicaciones'], true) : array();

            $all_trabajadores = array();
            $depto_stats = array();
            $ubic_stats = array();

            // Construir consulta con intersección de criterios
            $sql_conditions = array();
            $params = array();
            $joins = array();

            // Si hay departamentos seleccionados
            if (!empty($departamentos)) {
                $placeholders = implode(',', array_fill(0, count($departamentos), '?'));
                $sql_conditions[] = "t.departamento_id IN ($placeholders)";
                $params = array_merge($params, $departamentos);
                $joins[] = "INNER JOIN departamentos d ON t.departamento_id = d.id";
            }

            // Si hay ubicaciones seleccionadas
            if (!empty($ubicaciones)) {
                $placeholders = implode(',', array_fill(0, count($ubicaciones), '?'));
                $sql_conditions[] = "t.ubicacion IN ($placeholders)";
                $params = array_merge($params, $ubicaciones);
                $joins[] = "INNER JOIN ubicaciones u ON t.ubicacion = u.id";
            }

            // Si no hay criterios, retornar vacío
            if (empty($sql_conditions)) {
                $data['msg'] = 'Debe seleccionar al menos un departamento o ubicación';
                print(json_encode($data));
                return;
            }

            $sql = "SELECT DISTINCT t.id, t.nombre, t.apellidos, t.telefono";
            
            // Agregar campos para estadísticas según lo seleccionado
            if (!empty($departamentos)) {
                $sql .= ", d.nombre as depto_nombre";
            }
            if (!empty($ubicaciones)) {
                $sql .= ", u.nombre as ubic_nombre";
            }
            
            $sql .= " FROM trabajadores t " . implode(' ', $joins);
            $sql .= " WHERE " . implode(' AND ', $sql_conditions);
            $sql .= " AND t.trabajador_eliminado = '0' 
                     AND t.telefono IS NOT NULL 
                     AND t.telefono != ''";

            $workers = $this->db->fetchAll($sql, $params);
            
            foreach ($workers as $worker) {
                $worker_id = $worker['id'];
                if (!isset($all_trabajadores[$worker_id])) {
                    $all_trabajadores[$worker_id] = $worker;
                    
                    // Estadísticas por departamento
                    if (!empty($departamentos) && isset($worker['depto_nombre'])) {
                        $depto_nombre = $worker['depto_nombre'];
                        if (!isset($depto_stats[$depto_nombre])) {
                            $depto_stats[$depto_nombre] = array('nombre' => $depto_nombre, 'cantidad' => 0);
                        }
                        $depto_stats[$depto_nombre]['cantidad']++;
                    }
                    
                    // Estadísticas por ubicación
                    if (!empty($ubicaciones) && isset($worker['ubic_nombre'])) {
                        $ubic_nombre = $worker['ubic_nombre'];
                        if (!isset($ubic_stats[$ubic_nombre])) {
                            $ubic_stats[$ubic_nombre] = array('nombre' => $ubic_nombre, 'cantidad' => 0);
                        }
                        $ubic_stats[$ubic_nombre]['cantidad']++;
                    }
                }
            }

            $data['total'] = count($all_trabajadores);
            $data['departamentos'] = array_values($depto_stats);
            $data['ubicaciones'] = array_values($ubic_stats);

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al obtener vista previa: ' . $e->getMessage();
        }

        return $data;
    }

    private function _send_sms($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => ''
        );

        try {
            $departamentos = isset($param['departamentos']) ? json_decode($param['departamentos'], true) : array();
            $ubicaciones = isset($param['ubicaciones']) ? json_decode($param['ubicaciones'], true) : array();
            $mensaje = trim($param['mensaje']);

            // Validaciones
            if (empty($departamentos) && empty($ubicaciones)) {
                $data['status'] = 0;
                $data['msg'] = 'Debe seleccionar al menos un departamento o ubicación';
                print(json_encode($data));
                return;
            }

            if (empty($mensaje)) {
                $data['status'] = 0;
                $data['msg'] = 'El mensaje no puede estar vacío';
                print(json_encode($data));
                return;
            }

            if (strlen($mensaje) > 160) {
                $data['status'] = 0;
                $data['msg'] = 'El mensaje no puede exceder los 160 caracteres';
                print(json_encode($data));
                return;
            }

            // Construir consulta con intersección de criterios
            $sql_conditions = array();
            $params = array();
            $joins = array();

            // Si hay departamentos seleccionados
            if (!empty($departamentos)) {
                $placeholders = implode(',', array_fill(0, count($departamentos), '?'));
                $sql_conditions[] = "t.departamento_id IN ($placeholders)";
                $params = array_merge($params, $departamentos);
                $joins[] = "INNER JOIN departamentos d ON t.departamento_id = d.id";
            }

            // Si hay ubicaciones seleccionadas
            if (!empty($ubicaciones)) {
                $placeholders = implode(',', array_fill(0, count($ubicaciones), '?'));
                $sql_conditions[] = "t.ubicacion IN ($placeholders)";
                $params = array_merge($params, $ubicaciones);
                $joins[] = "INNER JOIN ubicaciones u ON t.ubicacion = u.id";
            }

            $sql = "SELECT DISTINCT t.id, t.nombre, t.apellidos, t.telefono 
                    FROM trabajadores t " . implode(' ', $joins) . "
                    WHERE " . implode(' AND ', $sql_conditions) . " 
                    AND t.trabajador_eliminado = '0' 
                    AND t.telefono IS NOT NULL 
                    AND t.telefono != ''";

            $workers = $this->db->fetchAll($sql, $params);

            if (empty($workers)) {
                $data['status'] = 0;
                $data['msg'] = 'No se encontraron trabajadores con número de teléfono para los criterios seleccionados';
                print(json_encode($data));
                return;
            }

            // Registrar y enviar SMS
            $enviados = 0;
            $fallidos = 0;
            $usuario_id = isset($_SESSION['guser_id']) ? $_SESSION['guser_id'] : null;

            // Preparar teléfonos para envío en lote
            $telefonos = array();
            foreach ($workers as $worker) {
                $tel = isset($worker['telefono']) ? $worker['telefono'] : '';
                $tel = preg_replace('/\s+/', '', $tel);
                if ($tel !== '') {
                    $telefonos[] = $tel;
                }
            }

            // Insertar registros en sms_notificaciones para todos los trabajadores
            foreach ($workers as $worker) {
                try {
                    $insert = array(
                        'usuario_id' => $usuario_id,
                        'trabajador_id' => $worker['id'],
                        'mensaje' => $mensaje,
                        'fecha' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('sms_notificaciones', $insert);
                } catch (Exception $e) {
                    error_log("Error registrando SMS para trabajador {$worker['id']}: " . $e->getMessage());
                }
            }

            // Enviar SMS en lote usando la API bulk-send
            try {
                $this->_send_sms_bulk($telefonos, $mensaje);
                $enviados = count($telefonos);
            } catch (Exception $e) {
                $fallidos = count($telefonos);
                error_log("Error en envío masivo de SMS: " . $e->getMessage());
            }

            $data['msg_title'] = 'Proceso completado';
            $data['msg'] = "SMS enviados: $enviados exitosos, $fallidos fallidos de un total de " . count($workers) . " destinatarios.";

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg_title'] = 'Error';
            $data['msg'] = 'Error al enviar SMS: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function _send_sms_trabajador($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => ''
        );

        try {
            $mensaje = isset($param['mensaje']) ? trim($param['mensaje']) : '';
            $telefono = isset($param['telefono']) ? trim($param['telefono']) : '';
            $telefono = preg_replace('/\s+/', '', $telefono);
            $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : null;

            if (empty($telefono)) {
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $data['msg'] = 'El trabajador no tiene teléfono válido';
                return $data;
            }

            if (empty($mensaje)) {
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $data['msg'] = 'El mensaje no puede estar vacío';
                return $data;
            }

            if (strlen($mensaje) > 160) {
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $data['msg'] = 'El mensaje no puede exceder los 160 caracteres';
                return $data;
            }

            $usuario_id = isset($_SESSION['guser_id']) ? $_SESSION['guser_id'] : null;

            if (!empty($trabajador_id)) {
                try {
                    $insert = array(
                        'usuario_id' => $usuario_id,
                        'trabajador_id' => $trabajador_id,
                        'mensaje' => $mensaje,
                        'fecha' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('sms_notificaciones', $insert);
                } catch (Exception $e) {
                    error_log("Error registrando SMS para trabajador {$trabajador_id}: " . $e->getMessage());
                }
            }

            $this->_send_sms_bulk(array($telefono), $mensaje);

            $data['msg_title'] = 'Éxito';
            $data['msg'] = 'SMS enviado correctamente';

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg_title'] = 'Error';
            $data['msg'] = 'Error al enviar SMS: ' . $e->getMessage();
        }

        return $data;
    }

    public function enviarSMS($telefonos, $mensaje)
    {
        return $this->_send_sms_bulk($telefonos, $mensaje);
    }

    private function _send_sms_bulk($telefonos, $mensaje)
    {
        try {
            $encabezado = 'Recursos Humanos le notifica:';
            if (is_string($mensaje)) {
                $mensajeTrim = ltrim($mensaje);
                if (stripos($mensajeTrim, $encabezado) !== 0) {
                    $mensaje = $encabezado . "\n" . $mensajeTrim;
                }
            }

            // Preparar datos para la API bulk-send
            $data = array(
                'recipients' => $telefonos,
                'message' => $mensaje,
                'route' => '', // Dejar vacío si no se especifica ruta
                'apikey' => SMS_API_KEY
            );

            // Convertir a JSON
            $json_data = json_encode($data);

            // Inicializar cURL
            $ch = curl_init(SMS_API_URL);
            
            // Configurar cURL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_data)
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Timeout configurable
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL configurable

            // Ejecutar la petición
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            // Verificar respuesta
            if ($curl_error) {
                throw new Exception('Error cURL: ' . $curl_error);
            }

            if ($http_code !== 200) {
                throw new Exception('Error HTTP: ' . $http_code . ' - Response: ' . $response);
            }

            // Decodificar respuesta
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decodificando JSON: ' . json_last_error_msg());
            }

            // Verificar si la API devolvió éxito
            if (!isset($result['success']) || !$result['success']) {
                $error_msg = isset($result['message']) ? $result['message'] : 'Error desconocido de la API';
                throw new Exception('Error API SMS: ' . $error_msg);
            }

            // Registrar en log para debugging
            error_log("SMS MASIVO ENVIADO - Teléfonos: " . implode(', ', $telefonos) . ", Mensaje: $mensaje, Respuesta: " . $response);
            
            return true;

        } catch (Exception $e) {
            // Registrar error detallado
            error_log("Error enviando SMS masivo: " . $e->getMessage());
            
            // Lanzar excepción para que se maneje en el método llamador
            throw new Exception('Error enviando SMS masivo: ' . $e->getMessage());
        }
    }


    private function _list($param)
    {
        $where = [];
        $vals = [];

        // Búsqueda por mensaje si llega el parámetro 'search'
        if (isset($param['search']) && trim($param['search']) !== '') {
            $where[] = 'sn.mensaje LIKE :search';
            $vals['search'] = '%' . $param['search'] . '%';
        }

        $cond = '';
        if (!empty($where)) {
            $cond = ' WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT sn.id, sn.mensaje, sn.fecha,
                       u.xusuario as usuario_nombre,
                       CONCAT(t.nombre, ' ', t.apellidos) as trabajador_nombre,
                       t.id as trabajador_id,
                       t.telefono
                FROM sms_notificaciones sn
                LEFT JOIN usuarios u ON sn.usuario_id = u.xusuario_id
                LEFT JOIN trabajadores t ON sn.trabajador_id = t.id
                WHERE t.empresa_id = {$this->app->empresa_id}" . (!empty($where) ? ' AND ' . implode(' AND ', $where) : '') .
            " ORDER BY sn.fecha DESC";

        if (!empty($vals)) {
            $data = $this->db->fetchAll($sql, $vals);
        } else {
            $data = $this->db->fetchAll($sql);
        }
        return $data;
    }

    private function _get_config($param)
    {
        $data = array(
            'status' => 1,
            'msg' => '',
            'config' => array()
        );

        try {
            $sql = "SELECT notificar_vacaciones, notificar_ausencia, notificar_cumpleanos, notificar_parte_nocturno
                    FROM configuracion_sms 
                    WHERE id = 1 
                    LIMIT 1";
            $config = $this->db->fetchRow($sql);

            if ($config) {
                $data['config'] = array(
                    'notificar_vacaciones' => (bool)$config['notificar_vacaciones'],
                    'notificar_ausencia' => (bool)$config['notificar_ausencia'],
                    'notificar_cumpleanos' => isset($config['notificar_cumpleanos']) ? (bool)$config['notificar_cumpleanos'] : false,
                    'notificar_parte_nocturno' => isset($config['notificar_parte_nocturno']) ? (bool)$config['notificar_parte_nocturno'] : false
                );
            } else {
                // Si no existe, crear registro por defecto
                $insert = array(
                    'id' => 1,
                    'notificar_vacaciones' => 1,
                    'notificar_ausencia' => 0,
                    'notificar_cumpleanos' => 0,
                    'notificar_parte_nocturno' => 0
                );
                $this->db->insert('configuracion_sms', $insert);
                
                $data['config'] = array(
                    'notificar_vacaciones' => true,
                    'notificar_ausencia' => false,
                    'notificar_cumpleanos' => false,
                    'notificar_parte_nocturno' => false
                );
            }

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al cargar configuración: ' . $e->getMessage();
        }

        return $data;
    }

    private function _save_config($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => ''
        );

        try {
            $notificar_vacaciones = isset($param['notificar_vacaciones']) ? (int)$param['notificar_vacaciones'] : 0;
            $notificar_ausencia = isset($param['notificar_ausencia']) ? (int)$param['notificar_ausencia'] : 0;
            $notificar_cumpleanos = isset($param['notificar_cumpleanos']) ? (int)$param['notificar_cumpleanos'] : 0;
            $notificar_parte_nocturno = isset($param['notificar_parte_nocturno']) ? (int)$param['notificar_parte_nocturno'] : 0;

            // Verificar si existe el registro
            $sql_check = "SELECT COUNT(*) as count FROM configuracion_sms WHERE id = 1";
            $check = $this->db->fetchRow($sql_check);

            if ($check['count'] > 0) {
                // Actualizar
                $update = array(
                    'notificar_vacaciones' => $notificar_vacaciones,
                    'notificar_ausencia' => $notificar_ausencia,
                    'notificar_cumpleanos' => $notificar_cumpleanos,
                    'notificar_parte_nocturno' => $notificar_parte_nocturno
                );
                $this->db->update('configuracion_sms', $update, array('id' => 1));
            } else {
                // Insertar
                $insert = array(
                    'id' => 1,
                    'notificar_vacaciones' => $notificar_vacaciones,
                    'notificar_ausencia' => $notificar_ausencia,
                    'notificar_cumpleanos' => $notificar_cumpleanos,
                    'notificar_parte_nocturno' => $notificar_parte_nocturno
                );
                $this->db->insert('configuracion_sms', $insert);
            }

            $data['msg_title'] = 'Configuración guardada';
            $data['msg'] = 'Las configuraciones de notificaciones SMS han sido actualizadas correctamente';

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg_title'] = 'Error';
            $data['msg'] = 'Error al guardar configuración: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    public function _send_vacacion_notification($param)
    {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        try {
            // Verificar si las notificaciones de vacaciones están activadas
            $config_result = $this->_get_config($param);
            if (!$config_result['status'] || !$config_result['config']['notificar_vacaciones']) {
                $data['msg'] = 'Notificaciones de vacaciones desactivadas';
                return $data;
            }

            if (!isset($param['trabajador_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Falta el ID del trabajador';
                return $data;
            }

            $trabajador_id = intval($param['trabajador_id']);
            $fecha_inicio = isset($param['fecha_inicio']) ? $param['fecha_inicio'] : '';
            $fecha_fin = isset($param['fecha_fin']) ? $param['fecha_fin'] : '';
            $accion = isset($param['accion']) ? $param['accion'] : 'aprobada';

            // Obtener datos del trabajador
            $sql_trabajador = "SELECT nombre, apellidos, telefono FROM trabajadores WHERE id = :id";
            $trabajador = $this->db->fetchRow($sql_trabajador, array('id' => $trabajador_id));

            if (!$trabajador) {
                $data['status'] = 0;
                $data['msg'] = 'Trabajador no encontrado';
                return $data;
            }

            if (empty($trabajador['telefono'])) {
                $data['msg'] = 'Trabajador sin teléfono configurado';
                return $data;
            }

            // Construir mensaje según la acción
            $nombre_completo = trim($trabajador['nombre'] . ' ' . $trabajador['apellidos']);
            
            if ($accion === 'rechazada') {
                $mensaje = "Estimado/a {$nombre_completo}, su solicitud de vacaciones ha sido rechazada.";
            } else {
                $mensaje = "Estimado/a {$nombre_completo}, su solicitud de vacaciones ha sido aprobada.";
                
                if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                    $mensaje .= " Período: del {$fecha_inicio} al {$fecha_fin}.";
                }
            }

            // Enviar SMS
            $sms_param = array(
                'trabajador_id' => $trabajador_id,
                'telefono' => $trabajador['telefono'],
                'mensaje' => $mensaje
            );

            $this->_send_sms_trabajador($sms_param);

            $data['msg'] = 'Notificación SMS de vacaciones enviada correctamente';

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error enviando notificación: ' . $e->getMessage();
        }

        return $data;
    }
}
