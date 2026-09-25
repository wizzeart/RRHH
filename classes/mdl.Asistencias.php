<?php
require_once("actualizar_asistencia.php");
/**
 * Módulo Template
 *
 * @author alvaro
 */
class Asistencia
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
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'list-filter':
                new ActualizarAsistencia($this->db);
                $data = $this->_list_filter($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'list-presentes':
                $data = $this->_list_presentes($param);
                print(json_encode($data));
                break;
            case 'list-ausentes':
                $data = $this->_list_ausentes($param);
                print(json_encode($data));
                break;
            case 'export-pdf':
                $this->_export_pdf($param);
                break;
            case 'export-excel-trabajador':
                $this->_export_excel_trabajador($param);
                break;
            case 'test-pdf':
                echo json_encode(['status' => 1, 'msg' => 'Test PDF endpoint working', 'fecha' => date('Y-m-d')]);
                break;
            case 'list-vacaciones':
                $data = $this->_list_vacaciones($param);
                print(json_encode($data));
                break;
            case 'list-especiales':
                $data = $this->_list_especiales($param);
                print(json_encode($data));
                break;
            case 'estadisticas':
                $data = $this->_get_estadisticas_asistencias($param);
                print(json_encode($data));
                break;
            case 'list-horas-custodios':
                $data = $this->_list_horas_custodios($param);
                print(json_encode($data));
                break;
        }
    }

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-asistencias':
                $data = array();
                $page['title'] = 'Asistencias';
                $page['subtitle'] = 'Listado de Asistencias';

                $data_form = array();
                //$data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                break;
        }
    }

    /**
     * Exporta a Excel TODAS las asistencias (por día) de UN trabajador.
     * Filtros: año completo, o rango de meses (mes_desde..mes_hasta) dentro de un año.
     *   api-app.php?module=asistencias&method=export-excel-trabajador
     *       &trabajador_id=ID [&anno=YYYY] [&mes_desde=1..12] [&mes_hasta=1..12]
     *       [&fecha_desde=YYYY-MM-DD&fecha_hasta=YYYY-MM-DD]  (rango explícito opcional)
     */
    private function _export_excel_trabajador($param)
    {
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);
        while (ob_get_level()) { @ob_end_clean(); }

        if ($this->app->rol != 1) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/html; charset=utf-8');
            die('No tiene permisos para exportar la asistencia.');
        }

        $trabajadorId = isset($param['trabajador_id']) ? (int)$param['trabajador_id'] : 0;
        if ($trabajadorId <= 0) {
            header('Content-Type: text/html; charset=utf-8');
            die('Trabajador no especificado.');
        }

        // --- Resolver rango de fechas ---
        $anno = (isset($param['anno']) && $param['anno'] !== '') ? (int)$param['anno'] : (int)date('Y');
        if ($anno < 2000 || $anno > 2100) $anno = (int)date('Y');

        if (!empty($param['fecha_desde']) && !empty($param['fecha_hasta'])) {
            // Rango explícito por fechas (opcional)
            $fechaDesde = $param['fecha_desde'];
            $fechaHasta = $param['fecha_hasta'];
        } else {
            $mesDesde = (isset($param['mes_desde']) && $param['mes_desde'] !== '') ? (int)$param['mes_desde'] : 1;
            $mesHasta = (isset($param['mes_hasta']) && $param['mes_hasta'] !== '') ? (int)$param['mes_hasta'] : 12;
            if ($mesDesde < 1 || $mesDesde > 12) $mesDesde = 1;
            if ($mesHasta < 1 || $mesHasta > 12) $mesHasta = 12;
            if ($mesHasta < $mesDesde) { $t = $mesDesde; $mesDesde = $mesHasta; $mesHasta = $t; }
            $fechaDesde = sprintf('%04d-%02d-01', $anno, $mesDesde);
            $ultimoDia  = (int)date('t', mktime(0, 0, 0, $mesHasta, 1, $anno));
            $fechaHasta = sprintf('%04d-%02d-%02d', $anno, $mesHasta, $ultimoDia);
        }

        // --- Datos del trabajador ---
        $trab = $this->db->fetchRow(
            "SELECT t.id, t.nombre, t.apellidos, t.apellidos_segundos, t.carnet_identidad,
                    c.nombre AS cargo_nombre, d.nombre AS departamento_nombre, u.nombre AS ubicacion_nombre
             FROM trabajadores t
             LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
             LEFT JOIN departamentos d ON t.departamento_id = d.id
             LEFT JOIN ubicaciones u ON t.ubicacion = u.id
             WHERE t.id = :id",
            [':id' => $trabajadorId]
        );
        if (!$trab) {
            $trab = ['nombre' => '', 'apellidos' => '', 'apellidos_segundos' => '', 'carnet_identidad' => '',
                     'cargo_nombre' => '', 'departamento_nombre' => '', 'ubicacion_nombre' => ''];
        }
        $nombreTrab = trim(($trab['nombre'] ?? '') . ' ' . ($trab['apellidos'] ?? '') . ' ' . ($trab['apellidos_segundos'] ?? ''));

        // --- Asistencias del trabajador en el rango ---
        $rows = $this->db->fetchAll(
            "SELECT ra.fecha, ra.hora_entrada, ra.hora_salida, ra.ausencia, ra.tipo_ausencia, ra.tardanza, ra.justificacion
             FROM registro_asistencia ra
             WHERE ra.trabajador_id = :tid AND ra.fecha BETWEEN :fd AND :fh
             ORDER BY ra.fecha ASC, ra.hora_entrada ASC",
            [':tid' => $trabajadorId, ':fd' => $fechaDesde, ':fh' => $fechaHasta]
        );
        $rows = $rows ?: [];

        // --- Construir Excel (mismo patrón que Trabajador->_export_excel) ---
        try {
            require_once(BASE_CLASS . '/PHPExcel.php');
            $excel = new PHPExcel();
            $excel->getProperties()->setCreator('Sistema de RRHH')->setTitle('Asistencias ' . $nombreTrab);
            $sheet = $excel->setActiveSheetIndex(0);
            $sheet->setTitle('Asistencias');
        } catch (Exception $e) {
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error cargando PHPExcel: ' . $e->getMessage());
        }

        $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

        // Encabezado
        $sheet->setCellValue('A1', 'Reporte de Asistencia');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $nombreTrab . '   (CI: ' . ($trab['carnet_identidad'] ?? '') . ')');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Cargo: ' . ($trab['cargo_nombre'] ?? '-') . '   |   Depto: ' . ($trab['departamento_nombre'] ?? '-') . '   |   Ubicación: ' . ($trab['ubicacion_nombre'] ?? '-'));
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A4', 'Período: ' . date('d/m/Y', strtotime($fechaDesde)) . ' a ' . date('d/m/Y', strtotime($fechaHasta)) . '     —     Generado: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(10);
        $sheet->mergeCells('A4:I4');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Cabecera de tabla
        $headers = [
            'A' => 'Fecha', 'B' => 'Día', 'C' => 'Entrada', 'D' => 'Salida', 'E' => 'Horas',
            'F' => 'Estado', 'G' => 'Tipo de Ausencia', 'H' => 'Tardanza', 'I' => 'Justificación'
        ];
        $headerRow = 6;
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
        }
        $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowNum = $headerRow + 1;
        $totalHoras = 0.0;
        $totalPresentes = 0;
        $totalAusentes = 0;
        foreach ($rows as $r) {
            $ausente = !empty($r['ausencia']);
            $entrada = $ausente ? '' : substr((string)$r['hora_entrada'], 0, 5);
            $salida  = $ausente ? '' : substr((string)$r['hora_salida'], 0, 5);

            // Horas trabajadas
            $horasTxt = '';
            if (!$ausente && !empty($r['hora_entrada']) && !empty($r['hora_salida'])) {
                $ini = strtotime($r['fecha'] . ' ' . $r['hora_entrada']);
                $fin = strtotime($r['fecha'] . ' ' . $r['hora_salida']);
                if ($ini !== false && $fin !== false && $fin > $ini) {
                    $h = ($fin - $ini) / 3600;
                    $totalHoras += $h;
                    $horasTxt = number_format($h, 2);
                }
            }

            $diaSemana = isset($diasSemana[(int)date('N', strtotime($r['fecha']))]) ? $diasSemana[(int)date('N', strtotime($r['fecha']))] : '';
            $estado = $ausente ? 'Ausente' : 'Presente';
            if ($ausente) $totalAusentes++; else $totalPresentes++;

            $sheet->setCellValueExplicit('A' . $rowNum, date('d/m/Y', strtotime($r['fecha'])), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNum, $diaSemana);
            $sheet->setCellValueExplicit('C' . $rowNum, $entrada, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNum, $salida, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $horasTxt);
            $sheet->setCellValue('F' . $rowNum, $estado);
            $sheet->setCellValue('G' . $rowNum, $ausente ? (string)$r['tipo_ausencia'] : '');
            $sheet->setCellValue('H' . $rowNum, !empty($r['tardanza']) ? 'Sí' : '');
            $sheet->setCellValue('I' . $rowNum, (string)$r['justificacion']);
            $rowNum++;
        }

        if (empty($rows)) {
            $sheet->setCellValue('A' . $rowNum, 'Sin registros de asistencia en el período seleccionado.');
            $sheet->mergeCells('A' . $rowNum . ':I' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getFont()->setItalic(true);
            $rowNum++;
        }

        // Fila de totales
        $rowNum++;
        $sheet->setCellValue('A' . $rowNum, 'Totales:');
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $rowNum, 'Presentes: ' . $totalPresentes . '   |   Ausencias: ' . $totalAusentes);
        $sheet->setCellValue('E' . $rowNum, number_format($totalHoras, 2));
        $sheet->getStyle('E' . $rowNum)->getFont()->setBold(true);

        // Bordes y autoajuste
        $lastDataRow = $headerRow + max(count($rows), 1);
        $sheet->getStyle('A' . $headerRow . ':I' . $lastDataRow)->applyFromArray([
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Descargar
        try {
            $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $this->app->eliminar_acentos($nombreTrab));
            $filename = 'asistencias_' . ($safeName ?: 'trabajador') . '_' . $fechaDesde . '_a_' . $fechaHasta . '.xlsx';
            while (ob_get_level()) { @ob_end_clean(); }
            $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'asist_' . uniqid() . '.xlsx';
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save($tempFile);
            if (file_exists($tempFile) && filesize($tempFile) > 0) {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                header('Expires: 0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tempFile));
                readfile($tempFile);
                @unlink($tempFile);
                exit;
            }
            throw new Exception('El archivo Excel se generó vacío.');
        } catch (Exception $e) {
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error generando archivo Excel: ' . $e->getMessage());
        }
    }

    private function _save($param)
    {
        try {
            // Manejar el checkbox de ausencia (puede no venir si está oculto en presentes con tardanza)
            if (!isset($param['ausencia'])) {
                // Si no se envía ausencia, verificar si hay justificación (caso de presente con tardanza)
                if (!empty($param['justificacion'])) {
                    // Es un presente con tardanza, mantener ausencia = 0
                    $param['ausencia'] = 0;
                } else {
                    // No hay justificación, asumir que no hay ausencia
                    $param['ausencia'] = 0;
                }
            } else {
                // Checkbox se envió, procesar normalmente
                if ($param['ausencia'] == 'on') {
            $param['ausencia'] = 1;
                } else {
                    $param['ausencia'] = 0;
                }
            }

            //remove module and method from array
            unset($param['module']);
            unset($param['method']);
            $id = $param['id'];
            unset($param['id']);
            
            // Verificar si es un nuevo registro o una actualización
            if (empty($id)) {
                // Crear nuevo registro - asegurar que la fecha esté presente
                if (empty($param['fecha'])) {
                    $param['fecha'] = date('Y-m-d'); // Usar fecha actual si no se envía
                }
                $result = $this->db->insert('registro_asistencia', $param);
                $message = 'Registro creado correctamente';
            } else {
                // Actualizar registro existente
                $result = $this->db->update('registro_asistencia', $param, array('id' => $id));
                $message = 'Registro actualizado correctamente';
            }
            
            if ($result) {
                print(json_encode(array('status' => 1, 'msg' => $message)));
            } else {
                print(json_encode(array('status' => 0, 'msg' => 'Error al guardar el registro')));
            }
        } catch (Exception $e) {
            error_log('Error en Asistencia->_save: ' . $e->getMessage());
            print(json_encode(array('status' => 0, 'msg' => 'Error al guardar el registro')));
        }
    }
    private function _list_filter($param)
    {
        try {
            $data = array();
            $where = ["t.trabajador_eliminado = '0'"];
            $params = [];

            // La columna `ausencia` solo se recalcula para hoy y ayer (ActualizarAsistencia),
            // así que si el marcaje llegó después del último recálculo la fila queda
            // congelada en 1 pese a tener hora de entrada. Se corrige en la consulta,
            // sin modificar los datos: si hay marcaje, no puede ser ausente.
            $estadoSql = "CASE WHEN ra.hora_entrada IS NOT NULL AND ra.ausencia = 1
                               THEN 0 ELSE ra.ausencia END";

            // Filtro por rango de fechas
            if (!empty($param['fecha_desde'])) {
                $where[] = "ra.fecha >= :fecha_desde";
                $params[':fecha_desde'] = $param['fecha_desde'];
            }
            if (!empty($param['fecha_hasta'])) {
                $where[] = "ra.fecha <= :fecha_hasta";
                $params[':fecha_hasta'] = $param['fecha_hasta'];
            }

            // Filtro por trabajador
            if (!empty($param['trabajador_id'])) {
                $where[] = "ra.trabajador_id = :trabajador_id";
                $params[':trabajador_id'] = $param['trabajador_id'];
            }

            // Filtro por ubicacion
            if (!empty($param['ubicacion'])) {
                $where[] = "t.ubicacion = :ubicacion";
                $params[':ubicacion'] = $param['ubicacion'];
            }
            
            // Filtro por empresa
            if (empty($param['empresa'])) {
                $where[] = "t.empresa_id = :empresa_id";
                $params[':empresa_id'] = $this->app->empresa_id;
            }
            
            // Filtrar por departamento y ubicación para rol 4 (jefe de área)
            if ($this->app->rol == 4) {
                $where[] = "t.id IN (
                    SELECT DISTINCT t2.id 
                    FROM trabajadores t2
                    INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                    INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                    WHERE aud.usuario_id = {$this->app->user_id} 
                    AND auu.usuario_id = {$this->app->user_id}
                )";
            }

            // Filtro por estado (1: Presente, 2: Ausente)
            if (!empty($param['estado'])) {
                if ($param['estado'] == '1') { // Presente
                    $where[] = "(({$estadoSql}) = 0 OR ra.ausencia IS NULL)";
                } elseif ($param['estado'] == '2') { // Ausente
                    $where[] = "({$estadoSql}) = 1";

                    // Filtro por tipo de ausencia si es que se seleccionó
                    if (!empty($param['tipo_ausencia'])) {
                        $where[] = "ra.tipo_ausencia = :tipo_ausencia";
                        $params[':tipo_ausencia'] = $param['tipo_ausencia'];
                    }
                } else {
                    $where[] = "({$estadoSql}) = 3";
                }
            }

            $sql = "SELECT 
                    ra.id,
                    ra.trabajador_id,
                    ra.fecha,
                    ra.hora_entrada,
                    ra.hora_salida,
                    {$estadoSql} AS ausencia,
                    CASE WHEN ra.hora_entrada IS NOT NULL AND ra.ausencia = 1
                              AND ra.hora_entrada > '09:00:00'
                              AND ra.hora_entrada < '18:00:00'
                         THEN 1 ELSE ra.tardanza END AS tardanza,
                    ra.tipo_ausencia,
                    ra.justificacion,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.foto,
                    c.nombre as cargo_nombre,
                    t.tipo_horario,
                    u.nombre as ubicacion
                FROM registro_asistencia ra
                INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                LEFT JOIN ubicaciones u ON t.ubicacion = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ra.hora_entrada DESC, ubicacion";


            $data = $this->db->fetchAll($sql, $params);
            
            // Procesar fotos a base64
            foreach ($data as &$row) {
                if (!empty($row['foto'])) {
                    $fotoBase64 = base64_encode($row['foto']);
                    $row['foto'] = 'data:image/jpeg;base64,' . $fotoBase64;
                } else {
                    $row['foto'] = null;
                }
            }
            
            return $data;
        } catch (Exception $e) {
            // Registrar el error en el log
            error_log('Error en Asistencia->_list: ' . $e->getMessage());

            // Devolver un array vacío en caso de error
            return array();
        }
    }

    private function _list($param)
    {
        try {
            $data = array();
            $sql = "SELECT 
                    ra.id,
                    ra.trabajador_id,
                    ra.fecha,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.ausencia,
                    ra.tipo_ausencia,
                    ra.justificacion,
                    ra.tardanza,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.foto,
                    c.nombre as cargo_nombre,
                    t.tipo_horario
                FROM registro_asistencia ra
                INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id AND t.empresa_id = {$this->app->empresa_id}
                WHERE t.trabajador_eliminado = '0'
                AND (t.empresa_id = {$this->app->empresa_id} OR t.departamento_id IS NULL)";
                
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
        
        $sql .= " ORDER BY ra.fecha DESC, ra.hora_entrada DESC";

            $data = $this->db->fetchAll($sql);
            
            // Procesar fotos a base64
            foreach ($data as &$row) {
                if (!empty($row['foto'])) {
                    $fotoBase64 = base64_encode($row['foto']);
                    $row['foto'] = 'data:image/jpeg;base64,' . $fotoBase64;
                } else {
                    $row['foto'] = null;
                }
            }
            
            return $data;
        } catch (Exception $e) {
            // Registrar el error en el log
            error_log('Error en Asistencia->_list: ' . $e->getMessage());

            // Devolver un array vacío en caso de error
            return array();
        }
    }

    private function _list_presentes($param)
    {

        // Configurar la zona horaria en función de la configuración de la empresa
        date_default_timezone_set('America/Havana');

        // Obtener fecha - hoy por defecto
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');


        //error_log("_list_presentes - Fecha solicitada: " . $fecha . " | Empresa: " . $this->app->empresa_id);

        $andEmpresa = "";
        if(!(!empty($param['empresa'])&&$param['empresa']=='all')){
            $andEmpresa= " AND t.empresa_id = {$this->app->empresa_id}";
        }
        // Consulta mejorada: PRESENTE = hora_entrada registrada Y ausencia = 0 o NULL
        $sql = "SELECT DISTINCT 
                    t.id,
                    t.carnet_identidad,
                    t.nombre,
                    t.apellidos,
                    t.foto,
                    t.departamento_id,
                    t.ubicacion AS ubicacion_id,
                    d.nombre AS departamento,
                    u.nombre AS ubicacion,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.ausencia
                FROM trabajadores t
                LEFT JOIN ubicaciones u ON t.ubicacion = u.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                INNER JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                    AND DATE(ra.fecha) = :fecha
                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                AND t.estatus = 'activo'
                AND (t.tipo_horario IS NULL OR t.tipo_horario != 0)
                $andEmpresa";
                
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
        
        $sql .= " AND ra.hora_entrada IS NOT NULL
                AND (ra.ausencia = 0 OR ra.ausencia IS NULL)
                ORDER BY u.nombre, d.nombre, t.apellidos, t.nombre";

        try {
            $params = [
                ':fecha' => $fecha
            ];

            $result = $this->db->fetchAll($sql, $params);

            error_log("_list_presentes - Registros encontrados: " . count($result));

            // Group by location
            $ubicaciones = [];

            foreach ($result as $row) {
                // Format the hours
                if ($row['hora_entrada']) {
                    $row['hora_entrada'] = substr($row['hora_entrada'], 0, 5);
                }
                if ($row['hora_salida']) {
                    $row['hora_salida'] = substr($row['hora_salida'], 0, 5);
                }
                // Convert binary photo to base64
                if ($row['foto']) {
                    $row['foto'] = base64_encode($row['foto']);
                } else {
                    $row['foto'] = null;
                }

                $ubName = $row['ubicacion'] ?: 'Sin Ubicación';
                $ubId = $row['ubicacion_id'] ?: 0;

                if (!isset($ubicaciones[$ubId])) {
                    $ubicaciones[$ubId] = [
                        'id' => $ubId,
                        'nombre' => $ubName,
                        'trabajadores' => []
                    ];
                }

                $ubicaciones[$ubId]['trabajadores'][] = $row;
            }

            // Convert to indexed array
            $ubicaciones = array_values($ubicaciones);

            error_log("_list_presentes - Ubicaciones retornadas: " . count($ubicaciones));

            return $ubicaciones;
        } catch (Exception $e) {
            error_log('Error en _list_presentes: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            return [];
        }
    }

    private function _list_ausentes($param)
    {

        // Configurar la zona horaria en función de la configuración de la empresa
        date_default_timezone_set('America/Havana');

        // Obtener fecha - hoy por defecto
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

        error_log("_list_ausentes - Fecha solicitada: " . $fecha . " | Empresa: " . $this->app->empresa_id);

        // Consulta mejorada: AUSENTE = SIN registro de hora_entrada para hoy y NO está de vacaciones
        // Incluir LEFT JOIN con registro_asistencia y plan_vacaciones para filtrar correctamente
        $sql = "SELECT DISTINCT 
                    t.id,
                    t.carnet_identidad,
                    t.nombre,
                    t.apellidos,
                    t.foto,
                    t.departamento_id,
                    t.ubicacion AS ubicacion_id,
                    d.nombre AS departamento,
                    u.nombre AS ubicacion,
                    t.id AS trabajador_id,
                    :fecha AS fecha,
                    ra.id AS registro_id,
                    ra.ausencia,
                    ra.tipo_ausencia,
                    ra.justificacion,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.tardanza
                FROM trabajadores t
                LEFT JOIN ubicaciones u ON t.ubicacion = u.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                    AND DATE(ra.fecha) = :fecha_join
                LEFT JOIN plan_vacaciones v ON v.trabajador_id = t.id
                    AND v.estado IN ('Aprobado', 'Procesada')
                    AND v.fecha_inicio <= :fecha_vac
                    AND v.fecha_fin >= :fecha_vac
                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                AND t.estatus = 'activo'
                AND (t.tipo_horario IS NULL OR t.tipo_horario != 0)
                AND t.empresa_id = :empresa_id 
                AND v.id IS NULL";
                
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
        
        $sql .= " AND NOT EXISTS (
                    SELECT 1 FROM registro_asistencia ra 
                    WHERE ra.trabajador_id = t.id 
                    AND DATE(ra.fecha) = :fecha
                    AND ra.hora_entrada IS NOT NULL
                )
                ORDER BY u.nombre, d.nombre, t.apellidos, t.nombre";

        try {
            $params = [
                ':fecha' => $fecha,
                ':empresa_id' => $this->app->empresa_id,
                ':fecha_join' => $fecha,
                ':fecha_vac' => $fecha
            ];

            $result = $this->db->fetchAll($sql, $params);

            error_log("_list_ausentes - Registros encontrados: " . count($result));

            // Group by location
            $ubicaciones = [];

            foreach ($result as $row) {
                // Convert binary photo to base64
                if ($row['foto']) {
                    $row['foto'] = base64_encode($row['foto']);
                } else {
                    $row['foto'] = null;
                }

                $ubName = $row['ubicacion'] ?: 'Sin Ubicación';
                $ubId = $row['ubicacion_id'] ?: 0;

                if (!isset($ubicaciones[$ubId])) {
                    $ubicaciones[$ubId] = [
                        'id' => $ubId,
                        'nombre' => $ubName,
                        'trabajadores' => []
                    ];
                }

                $ubicaciones[$ubId]['trabajadores'][] = $row;
            }

            // Convert to indexed array
            $ubicaciones = array_values($ubicaciones);

            error_log("_list_ausentes - Ubicaciones retornadas: " . count($ubicaciones));

            return $ubicaciones;
        } catch (Exception $e) {
            error_log('Error en _list_ausentes: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            return [];
        }
    }

    private function _export_pdf($param)
    {
        // Suprimir outputs antes de generar PDF
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);

        // Limpiar cualquier output buffer previo
        while (ob_get_level()) {
            @ob_end_clean();
        }

        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : (isset($param['fecha']) ? $param['fecha'] : date('Y-m-d'));

        // Log para debug
        error_log("PDF Export - Fecha: " . $fecha);
        error_log("PDF Export - Parámetros: " . json_encode($param));

        try {
            // Obtener datos de trabajadores presentes
            $ubicaciones = $this->_list_presentes(['fecha' => $fecha]);
            error_log("PDF Export - Ubicaciones encontradas: " . count($ubicaciones));

            // Intentar cargar TCPDF, si no está disponible usar FPDF
            if (file_exists(BASE_CLASS . '/tcpdf/tcpdf.php')) {
                require_once(BASE_CLASS . '/tcpdf/tcpdf.php');
                $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
                $useTCPDF = true;
            } else {
                // Usar FPDF como alternativa
                require_once(BASE_CLASS . '/fpdf/fpdf.php');
                $pdf = new FPDF('L', 'mm', 'A4');
                $useTCPDF = false;
            }

            if ($useTCPDF) {
                // Configurar propiedades del documento TCPDF
                $pdf->SetCreator('Sistema de Asistencia');
                $pdf->SetAuthor('Sistema de Asistencia');
                $pdf->SetTitle('Reporte Diario de Asistencia - ' . date('d/m/Y', strtotime($fecha)));
                $pdf->SetSubject('Reporte de Trabajadores Presentes');

                // Configurar márgenes
                $pdf->SetMargins(10, 15, 10);
                $pdf->SetHeaderMargin(5);
                $pdf->SetFooterMargin(10);

                // Configurar auto page breaks
                $pdf->SetAutoPageBreak(TRUE, 15);

                // Configurar fuente por defecto
                $pdf->SetFont('helvetica', '', 10);

                // Agregar página
                $pdf->AddPage();

                // Generar contenido del PDF
                $this->_generate_tcpdf_content($pdf, $ubicaciones, $fecha);
            } else {
                // Configurar FPDF
                $pdf->AddPage();
                $pdf->SetFont('Arial', '', 10);

                // Generar contenido del PDF
                $this->_generate_fpdf_content($pdf, $ubicaciones, $fecha);
            }

            // Generar nombre del archivo
            $filename = 'Reporte_Diario_' . date('Y-m-d', strtotime($fecha)) . '.pdf';

            // Limpiar buffers antes de enviar headers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Mostrar PDF en navegador
            $pdf->Output($filename, 'I');
        } catch (Exception $e) {
            error_log('Error generando PDF de asistencia: ' . $e->getMessage());

            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: text/html; charset=utf-8');
            die('Error generando PDF: ' . $e->getMessage());
        }
    }

    private function _generate_tcpdf_content($pdf, $ubicaciones, $fecha)
    {
        $fechaFormateada = date('d/m/Y', strtotime($fecha));
        $totalTrabajadores = 0;

        // Contar total de trabajadores
        foreach ($ubicaciones as $ub) {
            $totalTrabajadores += count($ub['trabajadores']);
        }

        // Título principal
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'REPORTE DIARIO DE ASISTENCIA', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Trabajadores Presentes - ' . $fechaFormateada, 0, 1, 'C');
        $pdf->Ln(5);

        if (empty($ubicaciones)) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay trabajadores presentes registrados para esta fecha.', 0, 1, 'C');
        } else {
            foreach ($ubicaciones as $ub) {
                // Título del departamento
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetFillColor(79, 129, 189);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(280, 8, $ub['nombre'] . ' (' . count($ub['trabajadores']) . ' trabajadores)', 1, 1, 'L', true);

                // Encabezados de tabla
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(217, 226, 243);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->Cell(60, 7, 'CI', 1, 0, 'C', true);
                $pdf->Cell(150, 7, 'Nombre y Apellidos', 1, 0, 'C', true);
                $pdf->Cell(70, 7, 'Entrada', 1, 1, 'C', true);

                // Datos de trabajadores
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetFillColor(255, 255, 255);

                foreach ($ub['trabajadores'] as $trabajador) {
                    $nombreCompleto = $trabajador['nombre'] . ' ' . $trabajador['apellidos'];
                    $horaEntrada = $trabajador['hora_entrada'] ? $trabajador['hora_entrada'] : '--:--';

                    $pdf->Cell(60, 6, $trabajador['carnet_identidad'], 1, 0, 'C');
                    $pdf->Cell(150, 6, $nombreCompleto, 1, 0, 'L');
                    $pdf->Cell(70, 6, $horaEntrada, 1, 1, 'C');
                }

                $pdf->Ln(5);
            }

            // Resumen
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 10, 'TOTAL DE TRABAJADORES PRESENTES: ' . $totalTrabajadores, 1, 1, 'C', true);
        }

        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s') . ' por el Sistema de Asistencia', 0, 1, 'C');
    }

    private function _generate_fpdf_content($pdf, $ubicaciones, $fecha)
    {
        $fechaFormateada = date('d/m/Y', strtotime($fecha));
        $totalTrabajadores = 0;

        // Contar total de trabajadores
        foreach ($ubicaciones as $ub) {
            $totalTrabajadores += count($ub['trabajadores']);
        }

        // Título principal
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'REPORTE DIARIO DE ASISTENCIA', 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Trabajadores Presentes - ' . $fechaFormateada, 0, 1, 'C');
        $pdf->Ln(5);

        if (empty($ubicaciones)) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'No hay trabajadores presentes registrados para esta fecha.', 0, 1, 'C');
        } else {
            foreach ($ubicaciones as $ub) {
                // Título del departamento
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(79, 129, 189);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(280, 8, utf8_decode($ub['nombre'] . ' (' . count($ub['trabajadores']) . ' trabajadores)'), 1, 1, 'L', true);

                // Encabezados de tabla
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(217, 226, 243);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->Cell(60, 7, 'CI', 1, 0, 'C', true);
                $pdf->Cell(150, 7, 'Nombre y Apellidos', 1, 0, 'C', true);
                $pdf->Cell(70, 7, 'Entrada', 1, 1, 'C', true);

                // Datos de trabajadores
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetTextColor(0, 0, 0);

                foreach ($ub['trabajadores'] as $trabajador) {
                    $nombreCompleto = utf8_decode($trabajador['nombre'] . ' ' . $trabajador['apellidos']);
                    $horaEntrada = $trabajador['hora_entrada'] ? $trabajador['hora_entrada'] : '--:--';

                    $pdf->Cell(60, 6, $trabajador['carnet_identidad'], 1, 0, 'C');
                    $pdf->Cell(150, 6, $nombreCompleto, 1, 0, 'L');
                    $pdf->Cell(70, 6, $horaEntrada, 1, 1, 'C');
                }

                $pdf->Ln(5);
            }

            // Resumen
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 10, 'TOTAL DE TRABAJADORES PRESENTES: ' . $totalTrabajadores, 1, 1, 'C', true);
        }

        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s') . ' por el Sistema de Asistencia', 0, 1, 'C');
    }

    private function _get_estadisticas_asistencias($param)
    {
        try {
            date_default_timezone_set('America/Havana');
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
            $empresa_id = $this->app->empresa_id;

            // Obtener trabajadores esperados por departamento
            $sql_total = "SELECT 
                            COUNT(DISTINCT t.id) as total_trabajadores,
                            COUNT(DISTINCT CASE WHEN ra.hora_entrada IS NOT NULL AND (t.tipo_horario IS NULL OR t.tipo_horario != 0) THEN t.id END) as presentes,
                            COUNT(DISTINCT CASE WHEN ra.hora_entrada IS NULL AND (t.tipo_horario IS NULL OR t.tipo_horario != 0) AND v.id IS NULL THEN t.id END) as ausentes,
                            COUNT(DISTINCT CASE WHEN v.estado IN ('Aprobado', 'Procesada') AND v.fecha_inicio <= :fecha AND v.fecha_fin >= :fecha THEN t.id END) as vacaciones,
                            COUNT(DISTINCT CASE WHEN t.tipo_horario = 0 THEN t.id END) as especiales
                        FROM trabajadores t
                        LEFT JOIN departamentos d ON t.departamento_id = d.id
                        LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                            AND DATE(ra.fecha) = :fecha
                        LEFT JOIN plan_vacaciones v ON v.trabajador_id = t.id
                            AND v.estado IN ('Aprobado', 'Procesada')
                            AND v.fecha_inicio <= :fecha
                            AND v.fecha_fin >= :fecha
                        WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                        AND t.empresa_id = :empresa_id";
                        
            // Filtrar por departamento y ubicación para rol 4 (jefe de área)
            if ($this->app->rol == 4) {
                $sql_total .= " AND t.id IN (
                    SELECT DISTINCT t2.id 
                    FROM trabajadores t2
                    INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                    INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                    WHERE aud.usuario_id = {$this->app->user_id} 
                    AND auu.usuario_id = {$this->app->user_id}
                )";
            }

            $totales = $this->db->fetchRow($sql_total, [
                ':fecha' => $fecha,
                ':empresa_id' => $empresa_id
            ]);

            // Porcentaje de asistencia (excluyendo vacaciones del total)
            $total_excluyendo_vacaciones = $totales['total_trabajadores'] - $totales['vacaciones'];
            $porcentaje = $total_excluyendo_vacaciones > 0
                ? round(($totales['presentes'] / $total_excluyendo_vacaciones) * 100, 2)
                : 0;

            // Estadísticas por departamento
            $sql_departamentos = "SELECT 
                                    d.id,
                                    d.nombre as departamento,
                                    COUNT(DISTINCT t.id) as total,
                                    COUNT(DISTINCT CASE WHEN ra.hora_entrada IS NOT NULL THEN t.id END) as presentes,
                                    COUNT(DISTINCT CASE WHEN ra.hora_entrada IS NULL THEN t.id END) as ausentes
                                FROM trabajadores t
                                LEFT JOIN departamentos d ON t.departamento_id = d.id
                                LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                                    AND DATE(ra.fecha) = :fecha
                                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                                AND t.empresa_id = :empresa_id
                                AND (t.tipo_horario IS NULL OR t.tipo_horario != 0)";
                                
            // Filtrar por departamento y ubicación para rol 4 (jefe de área)
            if ($this->app->rol == 4) {
                $sql_departamentos .= " AND t.id IN (
                    SELECT DISTINCT t2.id 
                    FROM trabajadores t2
                    INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                    INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                    WHERE aud.usuario_id = {$this->app->user_id} 
                    AND auu.usuario_id = {$this->app->user_id}
                )";
            }
            
            $sql_departamentos .= " GROUP BY d.id, d.nombre
                                ORDER BY d.nombre";

            $departamentos = $this->db->fetchAll($sql_departamentos, [
                ':fecha' => $fecha,
                ':empresa_id' => $empresa_id
            ]);

            // Top 5 trabajadores con entradas más tempranas
            $sql_puntualidad = "SELECT DISTINCT
                                    t.id,
                                    CONCAT(t.nombre, ' ', t.apellidos, ' ', COALESCE(t.apellidos_segundos, '')) as nombre_completo,
                                    t.carnet_identidad,
                                    d.nombre as departamento,
                                    ra.hora_entrada,
                                    ra.hora_salida
                                FROM trabajadores t
                                LEFT JOIN departamentos d ON t.departamento_id = d.id
                                LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                                    AND DATE(ra.fecha) = :fecha
                                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                                AND t.empresa_id = :empresa_id
                                AND (t.tipo_horario IS NULL OR t.tipo_horario != 0)";
                                
            // Filtrar por departamento y ubicación para rol 4 (jefe de área)
            if ($this->app->rol == 4) {
                $sql_puntualidad .= " AND t.id IN (
                    SELECT DISTINCT t2.id 
                    FROM trabajadores t2
                    INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                    INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                    WHERE aud.usuario_id = {$this->app->user_id} 
                    AND auu.usuario_id = {$this->app->user_id}
                )";
            }
            
            $sql_puntualidad .= " AND ra.hora_entrada IS NOT NULL
                                ORDER BY ra.hora_entrada ASC
                                LIMIT 5";

            $puntualidad = $this->db->fetchAll($sql_puntualidad, [
                ':fecha' => $fecha,
                ':empresa_id' => $empresa_id
            ]);

            // Estadísticas por ubicación
            $sql_ubicaciones = "SELECT 
                                    u.id,
                                    u.nombre as ubicacion,
                                    COUNT(DISTINCT t.id) as total,
                                    COUNT(DISTINCT CASE WHEN ra.hora_entrada IS NOT NULL THEN t.id END) as presentes,
                                    COUNT(DISTINCT CASE WHEN ra.hora_entrada IS NULL THEN t.id END) as ausentes
                                FROM trabajadores t
                                LEFT JOIN ubicaciones u ON t.ubicacion = u.id
                                LEFT JOIN departamentos d ON t.departamento_id = d.id
                                LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                                    AND DATE(ra.fecha) = :fecha
                                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                                AND t.empresa_id = :empresa_id
                                AND (t.tipo_horario IS NULL OR t.tipo_horario != 0)";
                                
            // Filtrar por departamento y ubicación para rol 4 (jefe de área)
            if ($this->app->rol == 4) {
                $sql_ubicaciones .= " AND t.id IN (
                    SELECT DISTINCT t2.id 
                    FROM trabajadores t2
                    INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                    INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                    WHERE aud.usuario_id = {$this->app->user_id} 
                    AND auu.usuario_id = {$this->app->user_id}
                )";
            }
            
            $sql_ubicaciones .= " GROUP BY u.id, u.nombre
                                ORDER BY u.nombre";

            $ubicaciones = $this->db->fetchAll($sql_ubicaciones, [
                ':fecha' => $fecha,
                ':empresa_id' => $empresa_id
            ]);

            return array(
                'status' => 1,
                'fecha' => $fecha,
                'totales' => $totales,
                'porcentaje_asistencia' => $porcentaje,
                'departamentos' => $departamentos,
                'ubicaciones' => $ubicaciones,
                'puntualidad' => $puntualidad
            );
        } catch (Exception $e) {
            error_log("Error en _get_estadisticas_asistencias: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return array(
                'status' => 0,
                'msg' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'totales' => array(
                    'total_trabajadores' => 0,
                    'presentes' => 0,
                    'ausentes' => 0
                ),
                'porcentaje_asistencia' => 0,
                'departamentos' => array(),
                'puntualidad' => array()
            );
        }
    }

    private function _list_especiales($param)
    {
        // Configurar la zona horaria en función de la configuración de la empresa
        date_default_timezone_set('America/Havana');

        // Obtener fecha - hoy por defecto
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

        error_log("_list_especiales - Fecha solicitada: " . $fecha . " | Empresa: " . $this->app->empresa_id);

        // Consulta para obtener trabajadores con horario especial (tipo 0)
        $sql = "SELECT DISTINCT 
                    t.id,
                    t.carnet_identidad,
                    t.nombre,
                    t.apellidos,
                    t.foto,
                    t.departamento_id,
                    t.ubicacion AS ubicacion_id,
                    d.nombre AS departamento,
                    u.nombre AS ubicacion,
                    t.id AS trabajador_id,
                    :fecha AS fecha,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.ausencia
                FROM trabajadores t
                LEFT JOIN ubicaciones u ON t.ubicacion = u.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                    AND DATE(ra.fecha) = :fecha_join
                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                AND t.estatus = 'activo'
                AND t.tipo_horario = 0
                AND t.empresa_id = :empresa_id";
                
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
        
        $sql .= " ORDER BY u.nombre, d.nombre, t.apellidos, t.nombre";

        try {
            $params = [
                ':fecha' => $fecha,
                ':empresa_id' => $this->app->empresa_id,
                ':fecha_join' => $fecha
            ];

            $result = $this->db->fetchAll($sql, $params);

            error_log("_list_especiales - Registros encontrados: " . count($result));

            // Group by location
            $ubicaciones = [];

            foreach ($result as $row) {
                // Format the hours
                if ($row['hora_entrada']) {
                    $row['hora_entrada'] = substr($row['hora_entrada'], 0, 5);
                }
                if ($row['hora_salida']) {
                    $row['hora_salida'] = substr($row['hora_salida'], 0, 5);
                }
                // Convert binary photo to base64
                if ($row['foto']) {
                    $row['foto'] = base64_encode($row['foto']);
                } else {
                    $row['foto'] = null;
                }

                $ubName = $row['ubicacion'] ?: 'Sin Ubicación';
                $ubId = $row['ubicacion_id'] ?: 0;

                if (!isset($ubicaciones[$ubId])) {
                    $ubicaciones[$ubId] = [
                        'id' => $ubId,
                        'nombre' => $ubName,
                        'trabajadores' => []
                    ];
                }

                $ubicaciones[$ubId]['trabajadores'][] = $row;
            }

            // Convert to indexed array
            $ubicaciones = array_values($ubicaciones);

            error_log("_list_especiales - Ubicaciones retornadas: " . count($ubicaciones));

            return $ubicaciones;
        } catch (Exception $e) {
            error_log('Error en _list_especiales: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            return [];
        }
    }

    private function _list_vacaciones($param)
    {
        // Configurar la zona horaria en función de la configuración de la empresa
        date_default_timezone_set('America/Havana');

        // Obtener fecha - hoy por defecto
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

        error_log("_list_vacaciones - Fecha solicitada: " . $fecha . " | Empresa: " . $this->app->empresa_id);

        // Consulta para obtener trabajadores de vacaciones aprobadas
        $sql = "SELECT DISTINCT 
                    t.id,
                    t.carnet_identidad,
                    t.nombre,
                    t.apellidos,
                    t.foto,
                    t.departamento_id,
                    t.ubicacion AS ubicacion_id,
                    d.nombre AS departamento,
                    u.nombre AS ubicacion,
                    t.id AS trabajador_id,
                    :fecha AS fecha,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.ausencia,
                    v.fecha_inicio,
                    v.fecha_fin,
                    v.estado as estado_vacacion
                FROM trabajadores t
                LEFT JOIN ubicaciones u ON t.ubicacion = u.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
                    AND DATE(ra.fecha) = :fecha_join
                INNER JOIN plan_vacaciones v ON v.trabajador_id = t.id
                    AND v.estado IN ('Aprobado', 'Procesada')
                    AND v.fecha_inicio <= :fecha_vac
                    AND v.fecha_fin >= :fecha_vac
                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                AND t.estatus = 'activo'
                AND t.empresa_id = :empresa_id";
                
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
        
        $sql .= " ORDER BY u.nombre, d.nombre, t.apellidos, t.nombre";

        try {
            $params = [
                ':fecha' => $fecha,
                ':empresa_id' => $this->app->empresa_id,
                ':fecha_join' => $fecha,
                ':fecha_vac' => $fecha
            ];

            $result = $this->db->fetchAll($sql, $params);

            error_log("_list_vacaciones - Registros encontrados: " . count($result));

            // Group by location
            $ubicaciones = [];

            foreach ($result as $row) {
                // Format the hours
                if ($row['hora_entrada']) {
                    $row['hora_entrada'] = substr($row['hora_entrada'], 0, 5);
                }
                if ($row['hora_salida']) {
                    $row['hora_salida'] = substr($row['hora_salida'], 0, 5);
                }
                // Convert binary photo to base64
                if ($row['foto']) {
                    $row['foto'] = base64_encode($row['foto']);
                } else {
                    $row['foto'] = null;
                }

                $ubName = $row['ubicacion'] ?: 'Sin Ubicación';
                $ubId = $row['ubicacion_id'] ?: 0;

                if (!isset($ubicaciones[$ubId])) {
                    $ubicaciones[$ubId] = [
                        'id' => $ubId,
                        'nombre' => $ubName,
                        'trabajadores' => []
                    ];
                }

                $ubicaciones[$ubId]['trabajadores'][] = $row;
            }

            // Convert to indexed array
            $ubicaciones = array_values($ubicaciones);

            error_log("_list_vacaciones - Ubicaciones retornadas: " . count($ubicaciones));

            return $ubicaciones;
        } catch (Exception $e) {
            error_log('Error en _list_vacaciones: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            return [];
        }
    }

    /**
     * Lista de registros de horas de custodios
     * Agrupa las horas por trabajador para la fecha dada
     * Solo para empresa_id = 3
     */
    private function _list_horas_custodios($param)
    {
        try {
            date_default_timezone_set('America/Havana');
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
            $empresa_id = $this->app->empresa_id;

            // Solo permitir para empresa_id = 3
            if ($empresa_id != 3) {
                return [];
            }

            // Filtro por tipo de horario (0=Especial, 1=Diurno, 2=Nocturno)
            $tipoHorarioFilter = "";
            $params = [
                ':fecha' => $fecha,
                ':empresa_id' => $empresa_id
            ];
            if (isset($_GET['tipo_horario']) && $_GET['tipo_horario'] !== '') {
                $tipoHorarioFilter = " AND t.tipo_horario = :tipo_horario";
                $params[':tipo_horario'] = $_GET['tipo_horario'];
            }

            // Obtener todos los registros de horas para la fecha dada
            // agrupados por trabajador
            $sql = "SELECT 
                        rah.id as registro_hora_id,
                        rah.trabajador_id,
                        rah.hora,
                        rah.fecha,
                        t.nombre,
                        t.apellidos,
                        t.foto,
                        t.carnet_identidad,
                        t.tipo_horario,
                        c.nombre as cargo_nombre
                    FROM registro_asistencia_horas rah
                    INNER JOIN trabajadores t ON rah.trabajador_id = t.id
                    LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                    WHERE rah.fecha = :fecha
                    AND t.empresa_id = :empresa_id
                    AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                    {$tipoHorarioFilter}
                    ORDER BY t.apellidos, t.nombre, rah.id ASC";

            $result = $this->db->fetchAll($sql, $params);

            // Agrupar por trabajador
            $workers = [];
            foreach ($result as $row) {
                $wId = $row['trabajador_id'];

                if (!isset($workers[$wId])) {
                    // Procesar foto
                    $foto = null;
                    if (!empty($row['foto'])) {
                        $foto = base64_encode($row['foto']);
                    }

                    $workers[$wId] = [
                        'trabajador_id' => $wId,
                        'nombre' => $row['nombre'],
                        'apellidos' => $row['apellidos'],
                        'carnet_identidad' => $row['carnet_identidad'],
                        'cargo_nombre' => $row['cargo_nombre'],
                        'tipo_horario' => $row['tipo_horario'],
                        'foto' => $foto,
                        'horas' => []
                    ];
                }

                $workers[$wId]['horas'][] = [
                    'id' => $row['registro_hora_id'],
                    'hora' => $row['hora'],
                    'fecha' => $row['fecha']
                ];
            }

            // Convertir a array indexado
            return array_values($workers);
        } catch (Exception $e) {
            error_log('Error en _list_horas_custodios: ' . $e->getMessage());
            return [];
        }
    }
}
