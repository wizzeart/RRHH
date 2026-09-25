
<?php

/**
 * Clase para manejar evaluaciones de trabajadores
 *
 * @author Sistema
 */
class Evaluaciones {

    var $app;
    var $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'save_batch':
                $this->_save_batch($param);
                break;
            case 'get_evaluaciones_mes':
                $data = $this->_get_evaluaciones_mes($param);
                print(json_encode($data));
                break;
            case 'export_excel':
                $this->_export_excel($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-evaluaciones':
                $data = array();
                $page['title'] = 'Evaluaciones';
                $page['subtitle'] = 'Listado de Evaluaciones';
                
                // Obtener lista de trabajadores y subaspectos para el formulario
                $data_form['trabajadores'] = $this->get_trabajadores();
                $data_form['subaspectos'] = $this->get_subaspectos();
                $data_form['cargos'] = $this->get_cargos();
                $data_form['mes_actual'] = date('Y-m');
                break;
        }
    }

    private function _list($param) {
        $mes = isset($param['mes']) ? $param['mes'] : date('Y-m');
        $cargo_id = isset($param['cargo_id']) ? $param['cargo_id'] : null;
        $pagina = isset($param['pagina']) ? intval($param['pagina']) : 1;
        $limit = isset($param['limit']) ? intval($param['limit']) : 20;
        $offset = ($pagina - 1) * $limit;
        
        // Construir WHERE dinámicamente
        $where_conditions = ["t.empresa_id = {$this->app->empresa_id}", "t.trabajador_eliminado = 0"];
        $params = [];
        
        // Filtrar por departamento y ubicación para rol 4 (jefe de área)
        if ($this->app->rol == 4) {
            $where_conditions[] = "t.id IN (
                SELECT DISTINCT t2.id 
                FROM trabajadores t2
                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                WHERE aud.usuario_id = {$this->app->user_id} 
                AND auu.usuario_id = {$this->app->user_id}
            )";
        }
        
        if ($cargo_id && $cargo_id !== 'all') {
            $where_conditions[] = "t.cargos_id = ?";
            $params[] = $cargo_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Obtener el total de trabajadores para la paginación
        $sql_count = "SELECT COUNT(DISTINCT t.id) as total
                     FROM trabajadores t
                     WHERE $where_clause";
        $total_result = $this->db->fetchRow($sql_count, $params);
        $total_trabajadores = $total_result['total'];
        
        // Primero obtener los trabajadores paginados
        $sql_trabajadores = "SELECT DISTINCT t.id as trabajador_id, t.nombre as trabajador_nombre, t.apellidos as trabajador_apellidos
                            FROM trabajadores t
                            WHERE $where_clause
                            ORDER BY t.nombre, t.apellidos
                            LIMIT $offset, $limit";
        $trabajadores_paginados = $this->db->fetchAll($sql_trabajadores, $params);
        
        // Luego obtener las evaluaciones para esos trabajadores
        if (empty($trabajadores_paginados)) {
            $resultados = [];
        } else {
            $trabajador_ids = array_column($trabajadores_paginados, 'trabajador_id');
            $placeholders = str_repeat('?,', count($trabajador_ids) - 1) . '?';
            
            $sql = "SELECT t.id as trabajador_id, t.nombre as trabajador_nombre, t.apellidos as trabajador_apellidos, t.foto,
                           s.id as subaspecto_id, s.descripcion as subaspecto_nombre, s.calificacion_max as subaspecto_calificacion_max,
                           a.id as aspecto_id, a.nombre as aspecto_nombre, a.calificacion_max as aspecto_calificacion_max,
                           e.calificacion, e.fecha
                    FROM trabajadores t
                    CROSS JOIN subaspectos s
                    LEFT JOIN aspectos a ON s.aspecto_id = a.id
                    LEFT JOIN evaluaciones e ON t.id = e.trabajador_id
                                              AND s.id = e.subaspecto_id
                                              AND DATE_FORMAT(e.fecha, '%Y-%m') = ?
                    WHERE t.id IN ($placeholders)
                    ORDER BY t.nombre, a.nombre, s.descripcion";
            
            $eval_params = array_merge([$mes], $trabajador_ids);
            $resultados = $this->db->fetchAll($sql, $eval_params);
        }
        
        // Agrupar aspectos con sus subaspectos
        $aspectos_agrupados = array();
        $todos_subaspectos = array();
        
        foreach ($resultados as $row) {
            $aspecto_id = $row['aspecto_id'];
            
            // Agrupar aspectos
            if (!isset($aspectos_agrupados[$aspecto_id])) {
                $aspectos_agrupados[$aspecto_id] = array(
                    'id' => $aspecto_id,
                    'nombre' => $row['aspecto_nombre'],
                    'calificacion_max' => $row['aspecto_calificacion_max'],
                    'subaspectos' => array(),
                    'subaspectos_count' => 0
                );
            }
            
            // Agregar subaspecto al aspecto si no existe
            if (!isset($aspectos_agrupados[$aspecto_id]['subaspectos'][$row['subaspecto_id']])) {
                $aspectos_agrupados[$aspecto_id]['subaspectos'][$row['subaspecto_id']] = array(
                    'id' => $row['subaspecto_id'],
                    'descripcion' => $row['subaspecto_nombre'],
                    'calificacion_max' => $row['subaspecto_calificacion_max']
                );
                $aspectos_agrupados[$aspecto_id]['subaspectos_count']++;
            }
            
            // Lista plana de subaspectos para los datos de trabajadores
            if (!isset($todos_subaspectos[$row['subaspecto_id']])) {
                $todos_subaspectos[$row['subaspecto_id']] = array(
                    'subaspecto_id' => $row['subaspecto_id'],
                    'subaspecto_nombre' => $row['subaspecto_nombre'],
                    'aspecto_nombre' => $row['aspecto_nombre']
                );
            }
        }
        
        // Convertir a array indexado y ordenar subaspectos dentro de cada aspecto
        $aspectos = array();
        foreach ($aspectos_agrupados as $aspecto) {
            $aspecto['subaspectos'] = array_values($aspecto['subaspectos']);
            $aspectos[] = $aspecto;
        }
        
        // Agrupar por trabajador
        $evaluaciones_por_trabajador = array();
        foreach ($resultados as $row) {
            $trabajador_id = $row['trabajador_id'];
            if (!isset($evaluaciones_por_trabajador[$trabajador_id])) {
                // Procesar la foto
                $foto = null;
                if (!empty($row['foto'])) {
                    // Convertir LONGBLOB a base64
                    $fotoBase64 = base64_encode($row['foto']);
                    // Crear data URL para imagen
                    $foto = 'data:image/jpeg;base64,' . $fotoBase64;
                }

                $evaluaciones_por_trabajador[$trabajador_id] = array(
                    'trabajador_id' => $trabajador_id,
                    'trabajador_nombre' => $row['trabajador_nombre'] . ' ' . $row['trabajador_apellidos'],
                    'foto' => $foto,
                    'subaspectos' => array(),
                    'aspectos_agrupados' => array()
                );
            }
            
            // Agregar subaspecto con información de su aspecto
            $evaluaciones_por_trabajador[$trabajador_id]['subaspectos'][] = array(
                'subaspecto_id' => $row['subaspecto_id'],
                'subaspecto_nombre' => $row['subaspecto_nombre'],
                'aspecto_id' => $row['aspecto_id'],
                'aspecto_nombre' => $row['aspecto_nombre'],
                'calificacion' => $row['calificacion'],
                'fecha' => $row['fecha']
            );
            
            // Agrupar por aspecto para cálculos de promedio
            $aspecto_id = $row['aspecto_id'];
            if (!isset($evaluaciones_por_trabajador[$trabajador_id]['aspectos_agrupados'][$aspecto_id])) {
                $evaluaciones_por_trabajador[$trabajador_id]['aspectos_agrupados'][$aspecto_id] = array(
                    'aspecto_id' => $aspecto_id,
                    'aspecto_nombre' => $row['aspecto_nombre'],
                    'calificacion_max' => $row['aspecto_calificacion_max'],
                    'subaspectos' => array(),
                    'total_calificaciones' => 0,
                    'count_calificaciones' => 0
                );
            }
            
            $evaluaciones_por_trabajador[$trabajador_id]['aspectos_agrupados'][$aspecto_id]['subaspectos'][] = array(
                'subaspecto_id' => $row['subaspecto_id'],
                'subaspecto_nombre' => $row['subaspecto_nombre'],
                'calificacion_max' => $row['subaspecto_calificacion_max'],
                'calificacion' => $row['calificacion'],
                'trabajador_id' => $trabajador_id
            );
            
            if ($row['calificacion'] !== null) {
                $evaluaciones_por_trabajador[$trabajador_id]['aspectos_agrupados'][$aspecto_id]['total_calificaciones'] += $row['calificacion'];
                $evaluaciones_por_trabajador[$trabajador_id]['aspectos_agrupados'][$aspecto_id]['count_calificaciones']++;
            }
        }
        
        // Convertir a array indexado
        foreach ($evaluaciones_por_trabajador as &$trabajador) {
            $trabajador['aspectos_agrupados'] = array_values($trabajador['aspectos_agrupados']);
        }
        
        return array(
            'data' => array_values($evaluaciones_por_trabajador),
            'mes' => $mes,
            'es_mes_actual' => ($mes === date('Y-m')),
            'subaspectos' => array_values($todos_subaspectos),
            'aspectos' => $aspectos,
            'total' => $total_trabajadores,
            'pagina' => $pagina,
            'limit' => $limit,
            'total_paginas' => ceil($total_trabajadores / $limit)
        );
    }

    private function _save_batch($param) {
        $data = array(
            'status' => 1,
            'msg' => '',
            'guardadas' => 0,
            'errores' => 0
        );
        
        try {
            $mes = isset($param['mes']) ? $param['mes'] : date('Y-m');
            $evaluaciones = json_decode($param['evaluaciones'], true);
            
            if (!$evaluaciones || !is_array($evaluaciones)) {
                $data['status'] = 0;
                $data['msg'] = 'Datos de evaluaciones inválidos';
                print(json_encode($data));
                return;
            }
            
            $fecha = $mes . '-01'; // Primer día del mes
            
            // Iniciar transacción para mayor eficiencia
            $this->db->directExec('START TRANSACTION');
            
            // Obtener calificaciones máximas de todos los subaspectos para validación
            $subaspecto_ids = array_column($evaluaciones, 'subaspecto_id');
            $sql_max_batch = "SELECT id, calificacion_max FROM subaspectos WHERE id IN (" . str_repeat('?,', count($subaspecto_ids) - 1) . "?)";
            $max_results = $this->db->fetchAll($sql_max_batch, $subaspecto_ids);
            
            $calificaciones_max = array();
            foreach ($max_results as $row) {
                $calificaciones_max[$row['id']] = $row['calificacion_max'];
            }
            
            foreach ($evaluaciones as $evaluacion) {
                $trabajador_id = $evaluacion['trabajador_id'];
                $subaspecto_id = $evaluacion['subaspecto_id'];
                $calificacion = $evaluacion['calificacion'];
                
                // Validar calificación según calificacion_max del subaspecto
                if (!isset($calificaciones_max[$subaspecto_id])) {
                    $data['errores']++;
                    continue;
                }
                
                $calificacion_max_subaspecto = $calificaciones_max[$subaspecto_id];
                
                if ($calificacion < 0 || $calificacion > $calificacion_max_subaspecto) {
                    $data['errores']++;
                    continue;
                }
                
                // Verificar si ya existe una evaluación para este trabajador, subaspecto y mes
                $sql = "SELECT id FROM evaluaciones 
                        WHERE trabajador_id = ? AND subaspecto_id = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?";
                $existente = $this->db->fetchRow($sql, [$trabajador_id, $subaspecto_id, $mes]);
                
                if ($existente) {
                    // Actualizar
                    $sql = "UPDATE evaluaciones SET calificacion = ?, fecha = ? 
                           WHERE trabajador_id = ? AND subaspecto_id = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?";
                    $ok = $this->db->directExec($sql, [$calificacion, $fecha, $trabajador_id, $subaspecto_id, $mes]);
                } else {
                    // Insertar
                    $insert = array(
                        'trabajador_id' => $trabajador_id,
                        'subaspecto_id' => $subaspecto_id,
                        'calificacion' => $calificacion,
                        'fecha' => $fecha
                    );
                    $ok = $this->db->insert('evaluaciones', $insert);
                }
                
                if ($ok) {
                    $data['guardadas']++;
                } else {
                    $data['errores']++;
                }
            }
            
            // Confirmar transacción
            $this->db->directExec('COMMIT');
            
            if ($data['errores'] > 0) {
                $data['msg'] = "Se guardaron {$data['guardadas']} evaluaciones correctamente y {$data['errores']} con errores";
            } else {
                $data['msg'] = "Se guardaron {$data['guardadas']} evaluaciones correctamente";
            }
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->db->directExec('ROLLBACK');
            $data['status'] = 0;
            $data['msg'] = 'Error al guardar las evaluaciones: ' . $e->getMessage();
        }
        
        print(json_encode($data));
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg' => ''
        );
        
        try {
            $trabajador_id = intval($param['trabajador_id']);
            $subaspecto_id = intval($param['subaspecto_id']);
            $calificacion = intval($param['calificacion']);
            $mes = $param['mes'];
            
            // Validar que sea el mes actual
            if ($mes !== date('Y-m')) {
                $data['status'] = 0;
                $data['msg'] = 'Solo se pueden editar evaluaciones del mes actual';
                print(json_encode($data));
                return;
            }
            
            // Validar calificación según calificacion_max del subaspecto
            $sql_max = "SELECT s.calificacion_max FROM subaspectos s WHERE s.id = ?";
            $result_max = $this->db->fetchRow($sql_max, [$subaspecto_id]);
            
            if (!$result_max) {
                $data['status'] = 0;
                $data['msg'] = 'Subaspecto no encontrado';
                print(json_encode($data));
                return;
            }
            
            $calificacion_max_subaspecto = $result_max['calificacion_max'];
            
            if ($calificacion < 0 || $calificacion > $calificacion_max_subaspecto) {
                $data['status'] = 0;
                $data['msg'] = 'La calificación debe estar entre 0 y ' . $calificacion_max_subaspecto;
                print(json_encode($data));
                return;
            }
            
            // Crear fecha para el primer día del mes
            $fecha = $mes . '-01';
            
            // Verificar si ya existe una evaluación
            $sql_check = "SELECT id FROM evaluaciones 
                         WHERE trabajador_id = ? AND subaspecto_id = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?";
            $existente = $this->db->fetchRow($sql_check, [$trabajador_id, $subaspecto_id, $mes]);
            
            if ($existente) {
                // Actualizar
                $sql = "UPDATE evaluaciones SET calificacion = ?, fecha = ? 
                       WHERE trabajador_id = ? AND subaspecto_id = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?";
                $ok = $this->db->directExec($sql, [$calificacion, $fecha, $trabajador_id, $subaspecto_id, $mes]);
                $data['msg'] = 'Evaluación actualizada correctamente';
            } else {
                // Insertar
                $insert = array(
                    'trabajador_id' => $trabajador_id,
                    'subaspecto_id' => $subaspecto_id,
                    'calificacion' => $calificacion,
                    'fecha' => $fecha
                );
                $ok = $this->db->insert('evaluaciones', $insert);
                $data['msg'] = 'Evaluación guardada correctamente';
            }
            
            if (!$ok) {
                $data['status'] = 0;
                $data['msg'] = 'Error al guardar la evaluación';
            }
            
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = $e->getMessage();
        }
        
        print(json_encode($data));
    }

    private function _get_evaluaciones_mes($param) {
        $mes = isset($param['mes']) ? $param['mes'] : date('Y-m');
        
        $sql = "SELECT e.id, e.trabajador_id, e.subaspecto_id, e.calificacion, e.fecha,
                       t.nombre as trabajador_nombre, t.apellido as trabajador_apellido,
                       s.descripcion as subaspecto_nombre, a.nombre as aspecto_nombre
                FROM evaluaciones e
                JOIN trabajadores t ON e.trabajador_id = t.id
                JOIN subaspectos s ON e.subaspecto_id = s.id
                JOIN aspectos a ON s.aspecto_id = a.id
                WHERE DATE_FORMAT(e.fecha, '%Y-%m') = ?
                ORDER BY t.nombre, t.apellido, a.nombre, s.descripcion";
        
        return $this->db->fetchAll($sql, [$mes]);
    }

    private function _export_excel($param) {
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);

        while (ob_get_level()) {
            @ob_end_clean();
        }

        $mes = isset($param['mes']) ? $param['mes'] : date('Y-m');
        $cargo_id = isset($param['cargo_id']) ? $param['cargo_id'] : null;

        $where_conditions = ["t.empresa_id = {$this->app->empresa_id}", "t.trabajador_eliminado = 0"];
        $params = [$mes];

        if ($this->app->rol == 4) {
            $where_conditions[] = "t.id IN (
                SELECT DISTINCT t2.id
                FROM trabajadores t2
                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id
                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                WHERE aud.usuario_id = {$this->app->user_id}
                AND auu.usuario_id = {$this->app->user_id}
            )";
        }

        if ($cargo_id && $cargo_id !== 'all') {
            $where_conditions[] = "t.cargos_id = ?";
            $params[] = $cargo_id;
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Obtener todos los subaspectos para crear las columnas
        $sql_subaspectos = "SELECT s.id, s.descripcion, a.nombre as aspecto_nombre
                            FROM subaspectos s
                            JOIN aspectos a ON s.aspecto_id = a.id
                            ORDER BY a.nombre, s.descripcion";
        $subaspectos = $this->db->fetchAll($sql_subaspectos);

        // Obtener datos de evaluaciones
        $sql = "SELECT
                    t.id as trabajador_id,
                    CONCAT(t.nombre, ' ', t.apellidos) AS trabajador,
                    c.nombre AS cargo,
                    s.id as subaspecto_id,
                    s.descripcion AS subaspecto,
                    a.nombre AS aspecto,
                    e.calificacion
                FROM trabajadores t
                CROSS JOIN subaspectos s
                LEFT JOIN aspectos a ON s.aspecto_id = a.id
                LEFT JOIN evaluaciones e ON t.id = e.trabajador_id 
                                          AND s.id = e.subaspecto_id 
                                          AND DATE_FORMAT(e.fecha, '%Y-%m') = ?
                LEFT JOIN cargos c ON t.cargos_id = c.id
                WHERE $where_clause
                ORDER BY t.nombre, t.apellidos, a.nombre, s.descripcion";

        $rows = $this->db->fetchAll($sql, $params);

        // Agrupar datos por trabajador
        $trabajadores_data = array();
        foreach ($rows as $row) {
            $trabajador_id = $row['trabajador_id'];
            if (!isset($trabajadores_data[$trabajador_id])) {
                $trabajadores_data[$trabajador_id] = array(
                    'trabajador' => $row['trabajador'],
                    'cargo' => $row['cargo'],
                    'evaluaciones' => array(),
                    'total' => 0
                );
            }
            $trabajadores_data[$trabajador_id]['evaluaciones'][$row['subaspecto_id']] = $row['calificacion'];
            if ($row['calificacion'] !== null) {
                $trabajadores_data[$trabajador_id]['total'] += $row['calificacion'];
            }
        }

        try {
            require_once(BASE_CLASS . '/PHPExcel.php');
            $excel = new PHPExcel();
            $excel->getProperties()
                ->setCreator('Sistema')
                ->setTitle('Evaluaciones ' . $mes);
            $sheet = $excel->setActiveSheetIndex(0);
            $sheet->setTitle('Evaluaciones');
        } catch (Exception $e) {
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error cargando PHPExcel: ' . $e->getMessage());
        }

        // Crear encabezados dinámicamente
        $col = 'A';
        $headers = array();
        
        $headers[$col++] = 'Trabajador';
        $headers[$col++] = 'Cargo';
        
        // Agregar columna para cada subaspecto
        foreach ($subaspectos as $subaspecto) {
            $headers[$col++] = $subaspecto['descripcion'];
        }
        
        $headers[$col++] = 'Evaluación Final';

        // Escribir encabezados
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }

        // Escribir datos
        $rowNum = 2;
        foreach ($trabajadores_data as $trabajador_data) {
            $col = 'A';
            $sheet->setCellValue($col++ . $rowNum, $trabajador_data['trabajador']);
            $sheet->setCellValue($col++ . $rowNum, $trabajador_data['cargo']);
            
            // Escribir calificación para cada subaspecto
            foreach ($subaspectos as $subaspecto) {
                $calificacion = isset($trabajador_data['evaluaciones'][$subaspecto['id']]) ? 
                               $trabajador_data['evaluaciones'][$subaspecto['id']] : '';
                $sheet->setCellValue($col++ . $rowNum, $calificacion);
            }
            
            // Escribir evaluación final
            $sheet->setCellValue($col . $rowNum, $trabajador_data['total']);
            $rowNum++;
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'evaluaciones_' . $mes . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    public function get_trabajadores() {
        $sql = "SELECT id, nombre, apellidos FROM trabajadores WHERE trabajador_eliminado = 0";
        
        // Filtrar por departamento y ubicación para rol 4 (jefe de área)
        if ($this->app->rol == 4) {
            $sql .= " AND id IN (
                SELECT DISTINCT t2.id 
                FROM trabajadores t2
                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                WHERE aud.usuario_id = {$this->app->user_id} 
                AND auu.usuario_id = {$this->app->user_id}
            )";
        }
        
        $sql .= " ORDER BY nombre, apellidos";
        return $this->db->fetchAll($sql);
    }

    public function get_cargos() {
        $sql = "SELECT DISTINCT c.id, c.nombre 
                FROM cargos c
                INNER JOIN trabajadores t ON c.id = t.cargos_id
                WHERE t.empresa_id = {$this->app->empresa_id} 
                AND t.trabajador_eliminado = 0";
                
        // Filtrar por departamento y ubicación para rol 4 (jefe de área)
        if ($this->app->rol == 4) {
            $sql .= " AND t.id IN (
                SELECT DISTINCT t2.id 
                FROM trabajadores t2
                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                WHERE aud.usuario_id = {$this->app->user_id} 
                AND auu.usuario_id = {$this->app->user_id}
            )";
        }
        
        $sql .= " ORDER BY c.nombre";
        return $this->db->fetchAll($sql);
    }

    public function get_subaspectos() {
        $sql = "SELECT s.id, s.descripcion, s.aspecto_id, a.nombre as aspecto_nombre
                FROM subaspectos s
                JOIN aspectos a ON s.aspecto_id = a.id
                ORDER BY a.nombre, s.descripcion";
        return $this->db->fetchAll($sql);
    }

    public function get_evaluaciones_trabajador_mes($trabajador_id, $mes) {
        $sql = "SELECT e.subaspecto_id, e.calificacion
                FROM evaluaciones e
                WHERE e.trabajador_id = ? AND DATE_FORMAT(e.fecha, '%Y-%m') = ?";
        return $this->db->fetchAll($sql, [$trabajador_id, $mes]);
    }
}
