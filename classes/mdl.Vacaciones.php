<?php
include_once(__DIR__ . '/mdl.NotificacionesSMS.php');

class Vacaciones {
    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list-id':
                $data = $this->_list_id($param);
                print(json_encode($data));
                break;
            case 'list-historial':
                $data = $this->_list_historial($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'get-estadisticas':
                $this->_get_estadisticas($param);
                break;
            case 'get-dias-disponibles':
                $this->_get_dias_disponibles($param);
                break;
           
            case 'aprobar-area':
                $this->_aprobar_area($param);
                break;
            case 'aprobar-final':
                $this->_aprobar_final($param);
                break;
            case 'rechazar-area':
                $this->_rechazar_area($param);
                break;
            case 'rechazar-final':
                $this->_rechazar_final($param);
                break;
            
            case 'del':
                $this->_del($param);
                break;
        }
    }

    public function controlador($param) {
        global $page, $app;
        
        $action = isset($param['action']) ? $param['action'] : 'view';
        
        if ($action === 'api') {
            $this->api($param);
            return;
        }
        
        // Vista principal
        $page['title'] = 'Gestión de Vacaciones';
        $page['subtitle'] = 'Vacaciones del Trabajador';
        
        // Obtener ID del trabajador del usuario actual
        $trabajador_id = $this->app->user_id;
        
        // Obtener datos del trabajador
        $sql = "SELECT id, nombre, apellidos, apellidos_segundos, carnet_identidad, cargos_id, departamento_id 
                FROM trabajadores WHERE id = {$trabajador_id} LIMIT 1";
        $data = $this->db->fetchRow($sql);
        
        if (!$data) {
            $data = array('id' => $trabajador_id);
        }
        
        include_once(BASE . '/modules/vacaciones/ficha_vacaciones_resumen.php');
    }
    

    /**
     * Listar vacaciones por trabajador
     * @param array $param
     * @return array
     */
    private function _list_id($param) {
        $data = array();
        $data['status'] = 1;
        try {
            $sql = "SELECT * FROM plan_vacaciones WHERE trabajador_id=:id ORDER BY id DESC";
            $data = $this->db->fetchAll($sql, array('id' => $param['trabajador_id']));
            foreach ($data as $key => $value) {
                // Contar días laborables
                if (!empty($value['dias'])) {
                    $dias_array = explode(',', $value['dias']);
                    $data[$key]['dias_totales'] = count($dias_array);
                } else {
                    $data[$key]['dias_totales'] = 0;
                }
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        return $data;
    }

    private function _save($param) {

        date_default_timezone_set('America/Havana');
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        try {
            // Validar parámetros requeridos
            if (!isset($param['trabajador_id']) || !isset($param['fecha_inicio']) || !isset($param['fecha_fin'])) {
                $data['status'] = 0;
                $data['msg'] = 'Faltan parámetros requeridos (trabajador_id, fecha_inicio, fecha_fin)';
                print(json_encode($data));
                return;
            }

            $trabajador_id = intval($param['trabajador_id']);
            $fecha_inicio = $param['fecha_inicio'];
            $fecha_fin = $param['fecha_fin'];

            // Si se envía explícitamente 'dias' (selección día a día desde el calendario), usarlo
            $dias_string = '';
            $total_dias = 0;
            if (isset($param['dias']) && trim($param['dias']) !== '') {
                // Esperamos formato: YYYY-MM-DD,YYYY-MM-DD,...
                $dias_array = array_filter(array_map('trim', explode(',', $param['dias'])));
                // Validar formato de fecha y descartar sábados/domingos. Esta es la única
                // barrera real contra fines de semana cuando el cliente selecciona días sueltos
                // (calendario de la ficha del trabajador, app móvil, etc.): validar solo el
                // formato no bastaba, un sábado o domingo bien formado pasaba tal cual.
                $valid_dates = array();
                foreach ($dias_array as $d) {
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) continue;
                    $dia_semana = (int) (new DateTime($d))->format('w'); // 0=domingo, 6=sábado
                    if ($dia_semana === 0 || $dia_semana === 6) continue;
                    $valid_dates[] = $d;
                }
                sort($valid_dates);
                $dias_array = $valid_dates;
                $total_dias = count($dias_array);
                $dias_string = implode(',', $dias_array);

                if ($total_dias === 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'La selección no contiene días laborables (no se permiten sábados ni domingos).';
                    print(json_encode($data));
                    return;
                }
                // Si no vienen fecha_inicio/fecha_fin válidas, derivarlas
                if (empty($fecha_inicio) && !empty($dias_array)) $fecha_inicio = $dias_array[0];
                if (empty($fecha_fin) && !empty($dias_array)) $fecha_fin = $dias_array[count($dias_array)-1];
                // Validar fechas derivadas
                $inicio = new DateTime($fecha_inicio);
                $fin = new DateTime($fecha_fin);
                if ($fin < $inicio) {
                    $data['status'] = 0;
                    $data['msg'] = 'Fechas inválidas derivadas de la selección de días';
                    print(json_encode($data));
                    return;
                }
            } else {
                // Validar que las fechas sean válidas
                $inicio = new DateTime($fecha_inicio);
                $fin = new DateTime($fecha_fin);
                
                // Se admite un solo día: fecha_fin == fecha_inicio es válido. Solo se rechaza
                // cuando el fin es ANTERIOR al inicio.
                if ($fin < $inicio) {
                    $data['status'] = 0;
                    $data['msg'] = 'La fecha de fin no puede ser anterior a la fecha de inicio';
                    print(json_encode($data));
                    return;
                }

                // Calcular días laborables (sin contar fines de semana)
                $dias_laborables = array();
                $current = clone $inicio;
                
                while ($current <= $fin) {
                    // 0 = Domingo, 6 = Sábado
                    $dia_semana = (int)$current->format('w');
                    if ($dia_semana != 0 && $dia_semana != 6) {
                        $dias_laborables[] = $current->format('Y-m-d');
                    }
                    $current->modify('+1 day');
                }

                $total_dias = count($dias_laborables);
                $dias_string = implode(',', $dias_laborables);

                // Un rango que solo cubre fin de semana no deja ningún día que descontar y
                // crearía un plan de 0 días que la prenómina ignoraría en silencio.
                if ($total_dias === 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'El rango seleccionado no contiene días laborables.';
                    print(json_encode($data));
                    return;
                }
            }

            // Validar el tope de 24 días por año sumando los planes ya registrados del
            // mismo año (Pendiente/Aprobado/Aprobado Area/Procesada; se excluyen Rechazados).
            $MAX_DIAS_ANIO = 24;
            $anio_solicitud = intval((new DateTime($fecha_inicio))->format('Y'));
            $sql_anio = "SELECT COALESCE(SUM(LENGTH(dias) - LENGTH(REPLACE(dias, ',', '')) + 1), 0) AS dias_anio
                         FROM plan_vacaciones
                         WHERE trabajador_id = :tid
                           AND YEAR(fecha_inicio) = :anio
                           AND estado IN ('Pendiente','Aprobado','Aprobado Area','Procesada')";
            $row_anio = $this->db->fetchRow($sql_anio, array('tid' => $trabajador_id, 'anio' => $anio_solicitud));
            $dias_anio = $row_anio ? floatval($row_anio['dias_anio']) : 0;

            if (($dias_anio + $total_dias) > $MAX_DIAS_ANIO) {
                $data['status'] = 0;
                $data['msg'] = 'Supera el máximo de ' . $MAX_DIAS_ANIO . ' días de vacaciones por año. '
                    . 'Ya planificados en ' . $anio_solicitud . ': ' . number_format($dias_anio, 0)
                    . ', solicitados: ' . $total_dias
                    . ', disponibles este año: ' . number_format(max(0, $MAX_DIAS_ANIO - $dias_anio), 0) . '.';
                print(json_encode($data));
                return;
            }

            // Verificar vacaciones disponibles del trabajador
            $sql_trabajador = "SELECT vacaciones_acc, vacaciones_congeladas FROM trabajadores WHERE id = :id";
            $trabajador = $this->db->fetchRow($sql_trabajador, array('id' => $trabajador_id));

            if (!$trabajador) {
                $data['status'] = 0;
                $data['msg'] = 'Trabajador no encontrado';
                print(json_encode($data));
                return;
            }

            $vacaciones_acc = isset($trabajador['vacaciones_acc']) ? floatval($trabajador['vacaciones_acc']) : 0;
            // Días ya comprometidos por planes aprobados pendientes de descontar en nómina.
            $vacaciones_congeladas = isset($trabajador['vacaciones_congeladas']) ? floatval($trabajador['vacaciones_congeladas']) : 0;
            // Días de planes aún en estado 'Pendiente' (no congelan saldo todavía, pero ya están
            // comprometidos por solicitudes en cola). Se restan para no exceder el saldo real.
            $sql_pend = "SELECT COALESCE(SUM(LENGTH(dias) - LENGTH(REPLACE(dias, ',', '')) + 1), 0) AS dias_pendientes
                         FROM plan_vacaciones
                         WHERE trabajador_id = :tid AND estado = 'Pendiente'";
            $row_pend = $this->db->fetchRow($sql_pend, array('tid' => $trabajador_id));
            $dias_pendientes = $row_pend ? floatval($row_pend['dias_pendientes']) : 0;
            // Saldo realmente libre = acumulado - congelado - pendiente.
            $vacaciones_libres = $vacaciones_acc - $vacaciones_congeladas - $dias_pendientes;

            // Calcular vacaciones disponibles dinámicamente según el mes de la solicitud
            // dias_disponibles = vacaciones_libres + (2.18 × (mes_solicitud - mes_actual))
            $hoy = new DateTime('now');
            $mes_actual = intval($hoy->format('m')) - 1; // Convertir a 0-11
            $inicio_solicitud = new DateTime($fecha_inicio);
            $mes_solicitud = intval($inicio_solicitud->format('m')) - 1; // Convertir a 0-11

            $DIAS_POR_MES = 2.18;
            $mes_diferencia = $mes_solicitud - $mes_actual;
            $dias_por_mes = $DIAS_POR_MES * $mes_diferencia;
            $vacaciones_disponibles = min($vacaciones_libres + $dias_por_mes, $MAX_DIAS_ANIO);

            // Validar que tenga vacaciones disponibles
            if ($vacaciones_disponibles < $total_dias) {
                $data['status'] = 0;
                $data['msg'] = 'No tienes vacaciones disponibles. Días solicitados: ' . $total_dias . ', Días disponibles: ' . number_format($vacaciones_disponibles, 2);
                print(json_encode($data));
                return;
            }

            // Validar que las fechas solicitadas no se solapen con otro plan no rechazado
            // del mismo trabajador (se permiten varios planes, pero sin compartir días).
            $sql_solapa = "SELECT COUNT(*) AS c FROM plan_vacaciones
                          WHERE trabajador_id = :trabajador_id
                          AND estado NOT IN ('Rechazado','Rechazado Area')
                          AND DATE(fecha_inicio) <= :fin
                          AND DATE(fecha_fin) >= :inicio";
            $solapa = $this->db->fetchRow($sql_solapa, array(
                'trabajador_id' => $trabajador_id,
                'inicio' => $fecha_inicio,
                'fin' => $fecha_fin
            ));

            if ($solapa && intval($solapa['c']) > 0) {
                $data['status'] = 0;
                $data['msg'] = 'Las fechas solicitadas se solapan con otro plan de vacaciones existente. Elija un rango de fechas que no coincida con planes ya registrados.';
                print(json_encode($data));
                return;
            }

            // $dias_string ya está definido según la rama (selección por días o rango)

            // Agrupar los días por MES CALENDARIO: un plan_vacaciones no puede abarcar más de un
            // mes, porque tanto el descuento en nómina
            // (Prenomina::_apply_prenomina_deductions_to_trabajador) como el candado
            // periodo_descuento operan por el mes de fecha_inicio y procesan TODOS los días del
            // campo `dias` de una sola vez. El selector de días del calendario permite marcar
            // fechas de meses distintos en una misma solicitud (el array de selección no se
            // reinicia al navegar entre meses), así que sin este agrupamiento un plan que cruza
            // de mes se pagaría y descontaría de golpe en la nómina del primer mes, adelantando
            // el pago de días que todavía no han ocurrido.
            $dias_array = explode(',', $dias_string);
            $grupos_mes = array();
            foreach ($dias_array as $d) {
                $mes = substr($d, 0, 7); // 'YYYY-MM'
                if (!isset($grupos_mes[$mes])) {
                    $grupos_mes[$mes] = array();
                }
                $grupos_mes[$mes][] = $d;
            }
            ksort($grupos_mes);

            $planes_creados = array();
            foreach ($grupos_mes as $dias_mes) {
                $insert_data = array(
                    'trabajador_id' => $trabajador_id,
                    'fecha_inicio' => $dias_mes[0],
                    'fecha_fin' => $dias_mes[count($dias_mes) - 1],
                    'dias' => implode(',', $dias_mes),
                    'estado' => 'Pendiente',
                    'fecha_creacion' => date('Y-m-d H:i:s')
                );

                // Si viene fecha de aprobación, agregarla
                if (isset($param['fecha_aprobacion']) && !empty($param['fecha_aprobacion'])) {
                    $insert_data['fecha_aprobacion'] = $param['fecha_aprobacion'];
                }

                if (!$this->db->insert('plan_vacaciones', $insert_data)) {
                    $data['status'] = 0;
                    $data['msg'] = 'Error al guardar las vacaciones en la base de datos';
                    print(json_encode($data));
                    return;
                }

                $planes_creados[] = array(
                    'id' => method_exists($this->db, 'last_id') ? $this->db->last_id() : null,
                    'fecha_inicio' => $insert_data['fecha_inicio'],
                    'fecha_fin' => $insert_data['fecha_fin'],
                    'dias' => count($dias_mes)
                );
            }

            // Si se dividió en más de un plan, dejar constancia en `observaciones` de cada uno
            // enlazando a los demás, para que quien aprueba sepa que forman parte de la misma
            // solicitud original y no son peticiones independientes.
            if (count($planes_creados) > 1) {
                $ids_hermanos = array_map(function ($p) { return $p['id']; }, $planes_creados);
                foreach ($planes_creados as $idx => $p) {
                    $otros = array_values(array_diff($ids_hermanos, array($p['id'])));
                    $nota = 'Solicitud dividida automáticamente en ' . count($planes_creados)
                        . ' partes por abarcar más de un mes (parte ' . ($idx + 1) . ' de ' . count($planes_creados) . '). '
                        . 'Solicitudes relacionadas: #' . implode(', #', $otros) . '.';
                    $this->db->update('plan_vacaciones', array('observaciones' => $nota), array('id' => $p['id']));
                }
            }

            // Notificar por SMS que se ha solicitado un plan de vacaciones (un solo mensaje
            // resumiendo todas las partes, aunque se hayan dividido en varios planes)
            try {
                $sms = new NotificacionesSMS($this->app);
                $config = $this->db->fetchRow("SELECT notificar_vacaciones FROM configuracion_sms WHERE id = 1 LIMIT 1");

                if ($config && !empty($config['notificar_vacaciones'])) {
                    $trabajador_info = $this->db->fetchRow(
                        "SELECT nombre, apellidos FROM trabajadores WHERE id = :id",
                        array('id' => $trabajador_id)
                    );
                    $nombre_completo = trim($trabajador_info['nombre'] . ' ' . $trabajador_info['apellidos']);
                    $inicio_fmt = date('d-m-Y', strtotime($fecha_inicio));
                    $fin_fmt = date('d-m-Y', strtotime($fecha_fin));

                    $sms->enviarSMS(
                        ['63511090'],
                        "{$nombre_completo} ha solicitado vacaciones del {$inicio_fmt} al {$fin_fmt}."
                    );
                }
            } catch (Exception $e) {
                error_log("Error enviando SMS de solicitud de vacaciones: " . $e->getMessage());
            }

            if (count($planes_creados) > 1) {
                $partes_txt = array();
                foreach ($planes_creados as $p) {
                    $ini_fmt = date('d/m', strtotime($p['fecha_inicio']));
                    $fin_fmt = date('d/m/Y', strtotime($p['fecha_fin']));
                    $rango = ($p['fecha_inicio'] === $p['fecha_fin']) ? $fin_fmt : ($ini_fmt . '-' . $fin_fmt);
                    $partes_txt[] = $rango . ' (' . $p['dias'] . ' día' . ($p['dias'] == 1 ? '' : 's') . ')';
                }
                $data['msg'] = 'Vacaciones guardadas correctamente. Como las fechas seleccionadas abarcan más '
                    . 'de un mes, se dividió automáticamente en ' . count($planes_creados) . ' solicitudes: '
                    . implode('; ', $partes_txt) . '. Total de días laborables: ' . $total_dias;
            } else {
                $data['msg'] = 'Vacaciones guardadas correctamente. Total de días laborables: ' . $total_dias;
            }
            $data['dias_solicitados'] = $total_dias;
            $data['dias_disponibles'] = $vacaciones_disponibles;
            $data['planes_creados'] = $planes_creados;

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }
        
        print(json_encode($data));
    }

    private function _get_dias_disponibles($param) {
        $data = array(
            'status' => 1,
            'dias_disponibles' => 0
        );

        try {
            if (!isset($param['trabajador_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Falta el ID del trabajador';
                print(json_encode($data));
                return;
            }

            $trabajador_id = intval($param['trabajador_id']);
            
            // Obtener días disponibles del trabajador (acumulado menos congelado)
            $sql = "SELECT vacaciones_acc, vacaciones_congeladas FROM trabajadores WHERE id = :id";
            $trabajador = $this->db->fetchRow($sql, array('id' => $trabajador_id));

            if ($trabajador) {
                $acc = isset($trabajador['vacaciones_acc']) ? floatval($trabajador['vacaciones_acc']) : 0;
                $congeladas = isset($trabajador['vacaciones_congeladas']) ? floatval($trabajador['vacaciones_congeladas']) : 0;
                $data['dias_disponibles'] = max(0, $acc - $congeladas);
            } else {
                $data['status'] = 0;
                $data['msg'] = 'Trabajador no encontrado';
            }

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }

        print(json_encode($data));
    }


    private function _aprobar_area($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        try {
            if (!isset($param['id']) || !isset($param['trabajador_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Faltan parámetros requeridos (id, trabajador_id)';
                print(json_encode($data));
                return;
            }

            $vacacion_id = intval($param['id']);
            $trabajador_id = intval($param['trabajador_id']);

            // Obtener información de la vacación
            $sql_vac = "SELECT pv.id, pv.estado, pv.trabajador_id, t.nombre, t.apellidos
                        FROM plan_vacaciones pv 
                        JOIN trabajadores t ON pv.trabajador_id = t.id 
                        WHERE pv.id = :id AND pv.trabajador_id = :tid";
            $vacacion = $this->db->fetchRow($sql_vac, array('id' => $vacacion_id, 'tid' => $trabajador_id));
            
            // Verificar si el jefe de área tiene permiso para aprobar esta vacación
            if ($this->app->rol == 4) {
                $sql_check = "SELECT COUNT(*) as count 
                             FROM trabajadores t2
                             INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                             INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                             WHERE t2.id = :trabajador_id 
                             AND aud.usuario_id = {$this->app->user_id} 
                             AND auu.usuario_id = {$this->app->user_id}";
                $check = $this->db->fetchRow($sql_check, array('trabajador_id' => $trabajador_id));
                
                if ($check['count'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'No tiene permiso para aprobar vacaciones de este trabajador';
                    print(json_encode($data));
                    return;
                }
            }

            if (!$vacacion) {
                $data['status'] = 0;
                $data['msg'] = 'Vacación no encontrada';
                print(json_encode($data));
                return;
            }

            // Verificar si está en estado Pendiente
            if ($vacacion['estado'] !== 'Pendiente') {
                $data['status'] = 0;
                $data['msg'] = 'Esta vacación ya fue procesada';
                print(json_encode($data));
                return;
            }

            // Actualizar estado a 'Aprobado Area' y fecha de aprobación del área
            $fecha_aprobacion = date('Y-m-d');
            $this->db->update('plan_vacaciones', 
                array('estado' => 'Aprobado Area', 'fecha_aprobacion_area' => $fecha_aprobacion), 
                array('id' => $vacacion_id)
            );

            $data['msg'] = 'Vacación aprobada por el jefe de área correctamente.';

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }

        print(json_encode($data));
    }


    private function _del($param) {
        date_default_timezone_set('America/Havana');

        $data = array(
            'status' => 1,
            'msg' => ''
        );

        try {
            if (!isset($param['id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Falta el parámetro id';
                print(json_encode($data));
                return;
            }

            $vacacion_id = intval($param['id']);

            $sql_vac = "SELECT trabajador_id, dias, estado, periodo_descuento, fecha_aprobacion, fecha_inicio FROM plan_vacaciones WHERE id = :id";
            $vacacion = $this->db->fetchRow($sql_vac, array('id' => $vacacion_id));

            if (!$vacacion) {
                $data['status'] = 0;
                $data['msg'] = 'Vacación no encontrada';
                print(json_encode($data));
                return;
            }

            // No permitir eliminar si la fecha de inicio es anterior a hoy
            if (!empty($vacacion['fecha_inicio'])) {
                $fi = new DateTime($vacacion['fecha_inicio']);
                $hoy = new DateTime(date('Y-m-d'));
                if ($fi < $hoy) {
                    $data['status'] = 0;
                    $data['msg'] = 'No se puede eliminar un plan de vacaciones que ya inició (fecha de inicio anterior a hoy).';
                    print(json_encode($data));
                    return;
                }
            }

            // Si el plan estaba aprobado (congelado) y aún no fue descontado en nómina,
            // liberar los días congelados para no inflar el saldo comprometido.
            $estado_plan = isset($vacacion['estado']) ? $vacacion['estado'] : '';
            $ya_procesado = !empty($vacacion['periodo_descuento']);
            if ($estado_plan === 'Aprobado' && !$ya_procesado) {
                $dias_str = isset($vacacion['dias']) ? $vacacion['dias'] : '';
                $dias_congelados = ($dias_str === '' || $dias_str === null)
                    ? 0
                    : (substr_count($dias_str, ',') + 1);
                if ($dias_congelados > 0) {
                    $this->db->directExec(
                        "UPDATE trabajadores SET vacaciones_congeladas = GREATEST(0, COALESCE(vacaciones_congeladas, 0) - :dias) WHERE id = :tid",
                        array('dias' => $dias_congelados, 'tid' => intval($vacacion['trabajador_id']))
                    );
                }
            }

            // Eliminar la vacación (el saldo acumulado no se devuelve; solo se libera lo congelado)
            $this->db->del('plan_vacaciones', array('id' => $vacacion_id));
            $data['msg'] = 'Registro de vacaciones eliminado correctamente';


        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    /**
     * Obtener estadísticas de vacaciones
     */
    private function _get_estadisticas($param) {
        $data = array('status' => 0);
        
        try {
            $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
            
            if ($trabajador_id <= 0) {
                $data['msg'] = 'ID de trabajador inválido';
                print(json_encode($data));
                return;
            }

            $year = date('Y');
            
            // Días disponibles
            $sql_disponibles = "
                SELECT COALESCE(vacaciones_acc, 0) as dias_disponibles 
                FROM trabajadores 
                WHERE id = {$trabajador_id}
            ";
            $result_disponibles = $this->db->fetchRow($sql_disponibles);
            $dias_disponibles = isset($result_disponibles['dias_disponibles']) ? intval($result_disponibles['dias_disponibles']) : 0;

            // Días pendientes de aprobación
            $sql_pendientes = "
                SELECT COUNT(*) as dias_pendientes 
                FROM plan_vacaciones 
                WHERE trabajador_id = {$trabajador_id} 
                AND (fecha_aprobacion IS NULL OR fecha_aprobacion = '0000-00-00')
                AND YEAR(fecha_inicio) = {$year}
            ";
            $result_pendientes = $this->db->fetchRow($sql_pendientes);
            $dias_pendientes = isset($result_pendientes['dias_pendientes']) ? intval($result_pendientes['dias_pendientes']) : 0;

            // Días aprobados este año
            $sql_aprobados = "
                SELECT COUNT(*) as dias_aprobados 
                FROM plan_vacaciones 
                WHERE trabajador_id = {$trabajador_id} 
                AND fecha_aprobacion IS NOT NULL 
                AND fecha_aprobacion != '0000-00-00'
                AND YEAR(fecha_inicio) = {$year}
            ";
            $result_aprobados = $this->db->fetchRow($sql_aprobados);
            $dias_aprobados = isset($result_aprobados['dias_aprobados']) ? intval($result_aprobados['dias_aprobados']) : 0;

            $data = array(
                'status' => 1,
                'dias_disponibles' => $dias_disponibles,
                'dias_pendientes' => $dias_pendientes,
                'dias_aprobados_anio' => $dias_aprobados
            );
        } catch (Exception $e) {
            $data['msg'] = 'Error: ' . $e->getMessage();
        }
        
        print(json_encode($data));
    }

    /**
     * Listar historial completo
     */
    private function _list_historial($param) {
        $data = array();
        
        try {
            $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
            
            if ($trabajador_id <= 0) {
                return $data;
            }

            $sql = "
                SELECT 
                    id,
                    YEAR(fecha_inicio) as year,
                    fecha_inicio,
                    fecha_fin,
                    DATEDIFF(fecha_fin, fecha_inicio) + 1 as dias_totales,
                    CASE 
                        WHEN fecha_aprobacion IS NOT NULL AND fecha_aprobacion != '0000-00-00' THEN 'Aprobado'
                        ELSE 'Pendiente'
                    END as estado,
                    fecha_aprobacion
                FROM plan_vacaciones 
                WHERE trabajador_id = {$trabajador_id}
                ORDER BY fecha_inicio DESC
            ";
            
            $data = $this->db->fetchAll($sql);
        } catch (Exception $e) {
            // Retornar array vacío en caso de error
        }
        
        return $data ?: array();
    }

    private function _aprobar_final($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        try {
            if (!isset($param['id']) || !isset($param['trabajador_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Faltan parámetros requeridos (id, trabajador_id)';
                print(json_encode($data));
                return;
            }

            $vacacion_id = intval($param['id']);
            $trabajador_id = intval($param['trabajador_id']);

            // Obtener información de la vacación
            $sql_vac = "SELECT pv.id, pv.estado, pv.trabajador_id, t.nombre, t.apellidos
                        FROM plan_vacaciones pv 
                        JOIN trabajadores t ON pv.trabajador_id = t.id 
                        WHERE pv.id = :id AND pv.trabajador_id = :tid";
            $vacacion = $this->db->fetchRow($sql_vac, array('id' => $vacacion_id, 'tid' => $trabajador_id));

            if (!$vacacion) {
                $data['status'] = 0;
                $data['msg'] = 'Vacación no encontrada';
                print(json_encode($data));
                return;
            }

            // El administrador (rol 1) puede aprobar desde "Pendiente" o "Aprobado Area"
            // El jefe de área (rol 4) solo puede aprobar desde "Pendiente" a "Aprobado Area"
            if ($this->app->rol == 1) {
                // Administrador puede aprobar directamente desde Pendiente o Aprobado Area
                if ($vacacion['estado'] !== 'Pendiente' && $vacacion['estado'] !== 'Aprobado Area') {
                    $data['status'] = 0;
                    $data['msg'] = 'Esta vacación ya fue procesada o no se puede aprobar en el estado actual';
                    print(json_encode($data));
                    return;
                }
            } else {
                // Para otros roles, debe estar aprobada por área primero
                if ($vacacion['estado'] !== 'Aprobado Area') {
                    $data['status'] = 0;
                    $data['msg'] = 'Esta vacación debe ser aprobada primero por el jefe de área';
                    print(json_encode($data));
                    return;
                }
            }

            // Actualizar estado a 'Aprobado' y fecha de aprobación final
            $fecha_aprobacion = date('Y-m-d');
            $this->db->update('plan_vacaciones',
                array('estado' => 'Aprobado', 'fecha_aprobacion' => $fecha_aprobacion),
                array('id' => $vacacion_id)
            );

            // Congelar los días del plan en el saldo del trabajador.
            // No se descuenta vacaciones_acc (eso ocurre en la prenómina del mes de inicio),
            // solo se reservan en vacaciones_congeladas para limitar nuevas solicitudes.
            // Idempotente: solo congelar si el plan no estaba ya 'Aprobado'.
            if ($vacacion['estado'] !== 'Aprobado') {
                $sql_dias = "SELECT CASE WHEN dias IS NULL OR dias = '' THEN 0
                                    ELSE (LENGTH(dias) - LENGTH(REPLACE(dias, ',', '')) + 1) END AS total_dias
                             FROM plan_vacaciones WHERE id = :id";
                $dias_row = $this->db->fetchRow($sql_dias, array('id' => $vacacion_id));
                $total_dias = $dias_row ? floatval($dias_row['total_dias']) : 0;
                if ($total_dias > 0) {
                    $this->db->directExec(
                        "UPDATE trabajadores SET vacaciones_congeladas = COALESCE(vacaciones_congeladas, 0) + :dias WHERE id = :tid",
                        array('dias' => $total_dias, 'tid' => $trabajador_id)
                    );
                }
            }

            // Enviar notificación SMS si está configurado
            try {
                $notificacionesSMS = new NotificacionesSMS($this->app);
                
                // Obtener fechas de la vacación para el mensaje
                $sql_fechas = "SELECT fecha_inicio, fecha_fin FROM plan_vacaciones WHERE id = :id";
                $fechas = $this->db->fetchRow($sql_fechas, array('id' => $vacacion_id));
                
                $sms_param = array(
                    'trabajador_id' => $trabajador_id,
                    'fecha_inicio' => $fechas['fecha_inicio'],
                    'fecha_fin' => $fechas['fecha_fin']
                );
                $sms_result = $notificacionesSMS->_send_vacacion_notification($sms_param);
                
                // Si las notificaciones están desactivadas, incluir mensaje en la respuesta
                if (!$sms_result['status']) {
                    $data['sms_msg'] = $sms_result['msg'];
                }
            } catch (Exception $e) {
                // No fallar la aprobación si falla el SMS
                error_log("Error enviando notificación SMS de vacaciones: " . $e->getMessage());
                $data['sms_msg'] = 'Error enviando notificación SMS';
            }

            if ($this->app->rol == 1 && $vacacion['estado'] === 'Pendiente') {
                $data['msg'] = 'Vacación aprobada directamente por el administrador.';
            } else {
                $data['msg'] = 'Vacación aprobada finalmente por el administrador.';
            }

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function _rechazar_area($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        try {
            if (!isset($param['id']) || !isset($param['trabajador_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Faltan parámetros requeridos (id, trabajador_id)';
                print(json_encode($data));
                return;
            }

            $vacacion_id = intval($param['id']);
            $trabajador_id = intval($param['trabajador_id']);

            // Obtener información de la vacación
            $sql_vac = "SELECT pv.id, pv.estado, pv.trabajador_id, t.nombre, t.apellidos
                        FROM plan_vacaciones pv 
                        JOIN trabajadores t ON pv.trabajador_id = t.id 
                        WHERE pv.id = :id AND pv.trabajador_id = :tid";
            $vacacion = $this->db->fetchRow($sql_vac, array('id' => $vacacion_id, 'tid' => $trabajador_id));
            
            // Verificar si el jefe de área tiene permiso para rechazar esta vacación
            if ($this->app->rol == 4) {
                $sql_check = "SELECT COUNT(*) as count 
                             FROM trabajadores t2
                             INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                             INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                             WHERE t2.id = :trabajador_id 
                             AND aud.usuario_id = {$this->app->user_id} 
                             AND auu.usuario_id = {$this->app->user_id}";
                $check = $this->db->fetchRow($sql_check, array('trabajador_id' => $trabajador_id));
                
                if ($check['count'] == 0) {
                    $data['status'] = 0;
                    $data['msg'] = 'No tiene permiso para rechazar vacaciones de este trabajador';
                    print(json_encode($data));
                    return;
                }
            }

            if (!$vacacion) {
                $data['status'] = 0;
                $data['msg'] = 'Vacación no encontrada';
                print(json_encode($data));
                return;
            }

            // Verificar si está en estado Pendiente
            if ($vacacion['estado'] !== 'Pendiente') {
                $data['status'] = 0;
                $data['msg'] = 'Esta vacación ya fue procesada';
                print(json_encode($data));
                return;
            }

            // Actualizar estado a 'Rechazado Area'
            $this->db->update('plan_vacaciones', 
                array('estado' => 'Rechazado Area'), 
                array('id' => $vacacion_id)
            );

            // Enviar notificación SMS si está configurado
            try {
                $notificacionesSMS = new NotificacionesSMS($this->app);
                
                $sms_param = array(
                    'trabajador_id' => $trabajador_id,
                    'accion' => 'rechazada'
                );
                $sms_result = $notificacionesSMS->_send_vacacion_notification($sms_param);
                
                // Si las notificaciones están desactivadas, incluir mensaje en la respuesta
                if (!$sms_result['status']) {
                    $data['sms_msg'] = $sms_result['msg'];
                }
            } catch (Exception $e) {
                // No fallar el rechazo si falla el SMS
                error_log("Error enviando notificación SMS de vacaciones: " . $e->getMessage());
                $data['sms_msg'] = 'Error enviando notificación SMS';
            }

            $data['msg'] = 'Vacación rechazada por el jefe de área.';

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function _rechazar_final($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );

        try {
            if (!isset($param['id']) || !isset($param['trabajador_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Faltan parámetros requeridos (id, trabajador_id)';
                print(json_encode($data));
                return;
            }

            $vacacion_id = intval($param['id']);
            $trabajador_id = intval($param['trabajador_id']);

            // Obtener información de la vacación
            $sql_vac = "SELECT pv.id, pv.estado, pv.trabajador_id, t.nombre, t.apellidos
                        FROM plan_vacaciones pv 
                        JOIN trabajadores t ON pv.trabajador_id = t.id 
                        WHERE pv.id = :id AND pv.trabajador_id = :tid";
            $vacacion = $this->db->fetchRow($sql_vac, array('id' => $vacacion_id, 'tid' => $trabajador_id));

            if (!$vacacion) {
                $data['status'] = 0;
                $data['msg'] = 'Vacación no encontrada';
                print(json_encode($data));
                return;
            }

            // El administrador (rol 1) puede rechazar desde "Pendiente" o "Aprobado Area"
            // El jefe de área (rol 4) solo puede rechazar desde "Pendiente" a "Rechazado Area"
            if ($this->app->rol == 1) {
                // Administrador puede rechazar directamente desde Pendiente o Aprobado Area
                if ($vacacion['estado'] !== 'Pendiente' && $vacacion['estado'] !== 'Aprobado Area') {
                    $data['status'] = 0;
                    $data['msg'] = 'Esta vacación ya fue procesada o no se puede rechazar en el estado actual';
                    print(json_encode($data));
                    return;
                }
            } else {
                // Para otros roles, debe estar aprobada por área primero
                if ($vacacion['estado'] !== 'Aprobado Area') {
                    $data['status'] = 0;
                    $data['msg'] = 'Esta vacación debe estar aprobada por el jefe de área para rechazo final';
                    print(json_encode($data));
                    return;
                }
            }

            // Actualizar estado a 'Rechazado'
            $this->db->update('plan_vacaciones', 
                array('estado' => 'Rechazado'), 
                array('id' => $vacacion_id)
            );

            // Enviar notificación SMS si está configurado
            try {
                $notificacionesSMS = new NotificacionesSMS($this->app);
                
                $sms_param = array(
                    'trabajador_id' => $trabajador_id,
                    'accion' => 'rechazada'
                );
                $sms_result = $notificacionesSMS->_send_vacacion_notification($sms_param);
                
                // Si las notificaciones están desactivadas, incluir mensaje en la respuesta
                if (!$sms_result['status']) {
                    $data['sms_msg'] = $sms_result['msg'];
                }
            } catch (Exception $e) {
                // No fallar el rechazo si falla el SMS
                error_log("Error enviando notificación SMS de vacaciones: " . $e->getMessage());
                $data['sms_msg'] = 'Error enviando notificación SMS';
            }

            if ($this->app->rol == 1 && $vacacion['estado'] === 'Pendiente') {
                $data['msg'] = 'Vacación rechazada directamente por el administrador.';
            } else {
                $data['msg'] = 'Vacación rechazada finalmente por el administrador.';
            }

        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

}

