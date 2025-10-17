<?php

class Prenomina {

    var $app;
    var $db;
    // Flag to cache existence of tarjetas_snc225.tiempo_trabajo (current) during this request
    private $_ttCorrectExists = null;  // tiempo_trabajo (current)

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list-prenomina':
                $data = $this->_list_prenomina($param);
                print(json_encode($data));
                break;
            case 'list-departamentos':
                $data = $this->_list_departamentos();
                print(json_encode($data));
                break;
            case 'list-prenomina-id':
                $data = $this->_list_prenomina_id($param);
                print(json_encode($data));
                break;
            case 'save-horas':
                $this->_save_horas();
                break;
            case 'export-excel':
                $this->_export_excel($param);
                break;
        }
    }

    private function _list_prenomina_id($param) {
        $data = array();
        $sql = "SELECT 
                    p.id,
                    p.trabajador_id,
                    p.year,
                    p.month,
                    p.horas,
                    p.tarifa,
                    p.a_cobrar,
                    p.bonif,
                    p.sal_dev,
                    p.ausencias,
                    p.vacaciones,
                    p.pago_vac,
                    p.salario_neto,
                    p.seg_social,
                    p.ing_pers,
                    p.salario_pagar,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre_trabajador,
                    t.carnet_identidad AS ci,
                    c.nombre AS cargo,
                    d.nombre AS departamento
                FROM prenomina p
                INNER JOIN trabajadores t ON p.trabajador_id = t.id
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                WHERE p.trabajador_id = :trabajador_id
                ORDER BY p.year DESC, p.month DESC";
        
        $data = $this->db->fetchAll($sql, array('trabajador_id' => $param['trabajador_id']));
        return $data;
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'prenomina':
                $data = array();
                $page['title'] = 'Prenómina';
                
                $data_form = array();
                break;
        }
    }

    private function _list_prenomina($param) {
        $vals = [];
        $whereParts = ["(t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)"];

        // Filtro por pestaña (departamento por nombre) usando subconsulta contra trabajadores.departamento_id
        if (isset($param['tab']) && $param['tab'] !== '') {
            $vals['tab'] = $param['tab'];
            $whereParts[] = 't.departamento_id IN (SELECT id FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab)))';
        }

        $cond = '';
        if (!empty($whereParts)) {
            $cond = ' WHERE ' . implode(' AND ', $whereParts);
        }

        // Usamos 192 como horas por defecto desde backend; el input de UI podrá ajustarlo sin persistir por ahora
        $sql = "SELECT 
                    t.id,
                    t.id AS expediente,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                    t.carnet_identidad AS ci,
                    COALESCE(c.salario, 0) AS tarifa,                                                                                                                                                    -- Tarifa por hora del cargo
                    192.00 AS horas,                                                                                                                                                                    -- Horas trabajadas por defecto (192 horas mensuales)
                    (192.00 * COALESCE(c.salario, 0)) AS a_cobrar,                                                                                                                                     -- Salario base: horas × tarifa
                    NULL AS bonif,                                                                                                                                                                      -- Bonificaciones (no calculadas automáticamente)
                    (192.00 * COALESCE(c.salario, 0)) AS sal_dev,                                                                                                                                      -- Salario devengado (igual al salario base)
                    (SELECT COUNT(*) FROM registro_asistencia ra WHERE ra.trabajador_id = t.id AND ra.ausencia = '1') AS ausencias,                                                                  -- Conteo de ausencias desde registro_asistencia
                    ROUND(((SELECT COUNT(*) FROM registro_asistencia ra WHERE ra.trabajador_id = t.id AND ra.ausencia = '1') * 8 * COALESCE(c.salario, 0)), 2) AS ausenciasCosto,                -- Costo por ausencias: ausencias × 8 × tarifa
                    (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL AND pv.fecha_aprobacion <> '' AND pv.dias IS NOT NULL AND pv.dias <> ''
                                    THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0)
                        FROM plan_vacaciones pv
                        WHERE pv.trabajador_id = t.id
                    ) AS vacaciones,                                                                                                                           -- Días de vacaciones aprobadas
                    ROUND(((COALESCE(c.salario, 0) * 8) * (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL AND pv.fecha_aprobacion <> '' AND pv.dias IS NOT NULL AND pv.dias <> ''
                                    THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0) FROM plan_vacaciones pv WHERE pv.trabajador_id = t.id
                    )), 2) AS pago_vac,  -- Pago por vacaciones: (tarifa × 8) × días_vacaciones
                    ((192.00 * COALESCE(c.salario, 0)) + ROUND(((COALESCE(c.salario, 0) * 8) * (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL AND pv.fecha_aprobacion <> '' AND pv.dias IS NOT NULL AND pv.dias <> ''
                                    THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0) FROM plan_vacaciones pv WHERE pv.trabajador_id = t.id
                    )), 2)) AS salario_neto,  -- Salario neto: salario_base + pago_vacaciones
                    ROUND((192.00 * COALESCE(c.salario, 0)) * 0.05, 2) AS seg_social,                                                                                                               -- Descuento seguridad social: 5% del salario base
                    ROUND((192.00 * COALESCE(c.salario, 0)) * 0.0375, 2) AS ing_pers,                                                                                                              -- Descuento ingresos personales: 3.75% del salario base
                    ROUND(((192.00 * COALESCE(c.salario, 0)) + ROUND(((COALESCE(c.salario, 0) * 8) * (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL AND pv.fecha_aprobacion <> '' AND pv.dias IS NOT NULL AND pv.dias <> ''
                                    THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0) FROM plan_vacaciones pv WHERE pv.trabajador_id = t.id
                    )), 2)) - (ROUND((192.00 * COALESCE(c.salario, 0)) * 0.05, 2) + ROUND((192.00 * COALESCE(c.salario, 0)) * 0.0375, 2) + ROUND(((SELECT COUNT(*) FROM registro_asistencia ra WHERE ra.trabajador_id = t.id AND ra.ausencia = '1') * 8 * COALESCE(c.salario, 0)), 2)), 2) AS salario_pagar,  -- Salario a pagar: salario_neto - (seg_social + ing_pers + ausenciasCosto)
                    COALESCE(d.nombre, '') AS departamento                                                                                                                                             -- Nombre del departamento
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                " . $cond .
                " ORDER BY t.id ASC";

        if (!empty($vals)) {
            return $this->db->fetchAll($sql, $vals);
        }
        return $this->db->fetchAll($sql);
    }

    private function _save_horas() {
        try {
            // Limpiar cualquier output previo
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json; charset=utf-8');
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);
            $resp = ['status' => 1, 'msg' => 'Guardado', 'errors' => [], 'affected' => 0];
            
            // Debug: verificar que la tabla prenomina existe
            try {
                $testQuery = "SHOW TABLES LIKE 'prenomina'";
                $tableExists = $this->db->fetchAll($testQuery);
                if (empty($tableExists)) {
                    echo json_encode(['status' => 0, 'msg' => 'Error: La tabla prenomina no existe']);
                    exit;
                }
                
                // Verificar que el campo ausencias_costo existe
                $columnQuery = "SHOW COLUMNS FROM prenomina LIKE 'ausencias_costo'";
                $columnExists = $this->db->fetchAll($columnQuery);
                if (empty($columnExists)) {
                    echo json_encode(['status' => 0, 'msg' => 'Error: El campo ausencias_costo no existe en la tabla prenomina']);
                    exit;
                }
                
                // Detectar columna correcta en tarjetas_snc225
                $ttCorrect = false;       // tiempo_trabajo (current)
                try {
                    $col2 = $this->db->fetchAll("SHOW COLUMNS FROM tarjetas_snc225 LIKE 'tiempo_trabajo'");
                    $ttCorrect = !empty($col2);
                } catch (Exception $e) { $ttCorrect = false; }
                // Guardar flag
                $this->_ttCorrectExists = $ttCorrect;
            } catch (Exception $e) {
                echo json_encode(['status' => 0, 'msg' => 'Error verificando tabla: ' . $e->getMessage()]);
                exit;
            }
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'msg' => 'Error inicial: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            exit;
        }
    
        if (!is_array($payload)) {
            $resp['status'] = 0;
            $resp['msg'] = 'JSON inválido';
            echo json_encode($resp);
            exit;
        }
    
        try {
            $year = isset($payload['year']) ? intval($payload['year']) : intval(date('Y'));
            $month = isset($payload['month']) ? intval($payload['month']) : intval(date('n'));
            $rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : [];
            if (empty($rows)) {
                $resp['status'] = 0;
                $resp['msg'] = 'Sin filas a guardar';
                echo json_encode($resp);
                exit;
            }
        
            foreach ($rows as $r) {
                $trabajador_id = isset($r['trabajador_id']) ? intval($r['trabajador_id']) : 0;
                $horas = isset($r['horas']) ? floatval($r['horas']) : 0.0;

                if ($trabajador_id <= 0) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => 'trabajador_id inválido'];
                    continue;
                }

                // Regla solicitada: si el trabajador no existe en tarjetas_snc225, descartar sin insertar nada
                try {
                    $existsSnc = $this->db->fetchRow(
                        "SELECT id FROM tarjetas_snc225 WHERE trabajador_id = :tid LIMIT 1",
                        ['tid' => $trabajador_id]
                    );
                    if (!$existsSnc || !isset($existsSnc['id'])) {
                        // Descartar este trabajador: no insertar prenomina ni tarjetas
                        $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => 'Descartado: trabajador no existe en tarjetas_snc225'];
                        continue;
                    }
                } catch (Exception $e) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => 'Error verificando tarjetas_snc225: ' . $e->getMessage()];
                    continue;
                }
        
                try {
                    // Verificar si ya existe registro del periodo
                    $sqlSel = "SELECT id, tarifa FROM prenomina WHERE trabajador_id=:tid AND `year`=:y AND `month`=:m";
                    $row = $this->db->fetchRow($sqlSel, ['tid' => $trabajador_id, 'y' => $year, 'm' => $month]);
        
                    // Obtener tarifa
                    if ($row) {
                        $tarifa = floatval($row['tarifa']);
                    } else {
                        $sqlTar = "SELECT COALESCE(c.salario,0) AS tarifa 
                                   FROM trabajadores t 
                                   LEFT JOIN cargos c ON t.cargos_id=c.id 
                                   WHERE t.id=:tid";
                        $trow = $this->db->fetchRow($sqlTar, ['tid' => $trabajador_id]);
                        $tarifa = $trow ? floatval($trow['tarifa']) : 0.0;
                    }
        
                    // Calcular salario base
                    $a_cobrar = $horas * $tarifa;
        
                    // Obtener ausencias
                    $sqlAusencias = "SELECT COUNT(*) as total_ausencias 
                                     FROM registro_asistencia 
                                     WHERE trabajador_id = :tid AND ausencia = '1'";
                    $ausenciasRow = $this->db->fetchRow($sqlAusencias, ['tid' => $trabajador_id]);
                    $ausencias = $ausenciasRow ? intval($ausenciasRow['total_ausencias']) : 0;
                    $ausenciasCosto = round($ausencias * 8 * $tarifa, 2);
                    
                    // Obtener vacaciones desde plan_vacaciones: contar comas + 1 cuando fecha_aprobacion esté definida y dias no vacío
                    $sqlVacaciones = "SELECT COALESCE(SUM(
                                            CASE 
                                                WHEN fecha_aprobacion IS NOT NULL AND fecha_aprobacion <> '' AND dias IS NOT NULL AND dias <> ''
                                                    THEN (LENGTH(dias) - LENGTH(REPLACE(dias, ',', '')) + 1)
                                                ELSE 0
                                            END
                                        ), 0) AS total_dias_vacaciones
                                        FROM plan_vacaciones
                                        WHERE trabajador_id = :tid";
                    $vacacionesRow = $this->db->fetchRow($sqlVacaciones, ['tid' => $trabajador_id]);
                    $vacaciones = $vacacionesRow ? intval($vacacionesRow['total_dias_vacaciones']) : 0;
        
                    // Calcular pago por vacaciones
                    $pago_vac = round(($tarifa * 8) * $vacaciones, 2);
        
                    // Salario neto incluye pago por vacaciones
                    $salario_neto = $a_cobrar + $pago_vac;
        
                    // Descuentos
                    $seg_social = round($a_cobrar * 0.05, 2);
                    $ing_pers   = round($a_cobrar * 0.0375, 2);
        
                    // Salario final a pagar
                    $salario_pagar = round($salario_neto - ($seg_social + $ing_pers + $ausenciasCosto), 2);
        
                    if ($row) {
                        // Update
                        $upd = [
                            'horas' => $horas,
                            'a_cobrar' => $a_cobrar,
                            'sal_dev' => $a_cobrar,
                            'salario_neto' => $salario_neto,
                            'seg_social' => $seg_social,
                            'ing_pers' => $ing_pers,
                            'salario_pagar' => $salario_pagar,
                            'ausencias' => $ausencias,
                            'ausencias_costo' => $ausenciasCosto,
                            'vacaciones' => $vacaciones,
                            'pago_vac' => $pago_vac,
                        ];
                        $where = ['trabajador_id' => $trabajador_id, 'year' => $year, 'month' => $month];
                        $this->db->update('prenomina', $upd, $where);
                    } else {
                        // Insert
                        $ins = [
                            'trabajador_id' => $trabajador_id,
                            'year' => $year,
                            'month' => $month,
                            'horas' => $horas,
                            'tarifa' => $tarifa,
                            'a_cobrar' => $a_cobrar,
                            'bonif' => null,
                            'sal_dev' => $a_cobrar,
                            'ausencias' => $ausencias,
                            'ausencias_costo' => $ausenciasCosto,
                            'vacaciones' => $vacaciones,
                            'pago_vac' => $pago_vac,
                            'salario_neto' => $salario_neto,
                            'seg_social' => $seg_social,
                            'ing_pers' => $ing_pers,
                            'salario_pagar' => $salario_pagar,
                        ];
                        $this->db->insert('prenomina', $ins);
                    }

                    // Unificar fecha_inicio en tarjetas_snc225 para este trabajador (si alguna fila la tiene, ponerla en todas)
                    try {
                        $fiRow = $this->db->fetchRow(
                            "SELECT fecha_inicio FROM tarjetas_snc225 WHERE trabajador_id = :tid AND fecha_inicio IS NOT NULL AND fecha_inicio <> '' AND fecha_inicio <> '0000-00-00' ORDER BY fecha_inicio ASC LIMIT 1",
                            ['tid' => $trabajador_id]
                        );
                        if ($fiRow && !empty($fiRow['fecha_inicio'])) {
                            $fiVal = $fiRow['fecha_inicio'];
                            // Actualizar todas las filas de ese trabajador con la misma fecha_inicio
                            $this->db->update('tarjetas_snc225', ['fecha_inicio' => $fiVal], ['trabajador_id' => $trabajador_id]);
                        }
                    } catch (Exception $e) { /* ignore */ }

                    // Actualizar en tarjetas_snc225 (no insertar nuevos): salarios_devengados y (si existe) tiempo_trabajo (días) para el periodo YYYY-MM-01
                    try {
                        $periodo = sprintf('%04d-%02d-01', $year, $month);
                        $valSD = round((float)$salario_pagar, 2);
                        $diasTrab = (int)round($horas / 8);
                        $snc = $this->db->fetchRow(
                            "SELECT id, fecha_cierre FROM tarjetas_snc225 WHERE trabajador_id = :tid AND periodo = :p LIMIT 1",
                            ['tid' => $trabajador_id, 'p' => $periodo]
                        );
                        $includeCorrect = (isset($this->_ttCorrectExists) && $this->_ttCorrectExists === true);
                        // Si la fila del periodo tiene fecha_cierre, no tocar nada
                        if ($snc && isset($snc['id']) && !empty($snc['fecha_cierre']) && $snc['fecha_cierre'] !== '0000-00-00') {
                            // Skip: registro cerrado
                        } elseif ($snc && isset($snc['id'])) {
                            $upd = [ 'salarios_devengados' => $valSD ];
                            if ($includeCorrect) { $upd['tiempo_trabajo'] = $diasTrab; }
                            $this->db->update('tarjetas_snc225', $upd, ['id' => $snc['id']]);
                        } else {
                            // No crear nuevos registros en tarjetas_snc225; descartar
                        }
                    } catch (Exception $e) { /* ignore */ }
        
                    $resp['affected']++;
                    
                } catch (Exception $e) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => $e->getMessage()];
                    continue;
                }
            }
        
            echo json_encode($resp);
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'msg' => 'Error general: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            exit;
        }
    }
    

    private function _export_excel($param) {
        // Parámetros de periodo
        $year = isset($param['year']) ? intval($param['year']) : intval(date('Y'));
        $month = isset($param['month']) ? intval($param['month']) : intval(date('n'));
        $vals = ['y' => $year, 'm' => $month];

        // Filtro por tab (por nombre de departamento) usando subconsulta contra trabajadores.departamento_id
        $where = ["(t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)"];
        if (isset($param['tab']) && trim($param['tab']) !== '') {
            $where[] = 't.departamento_id IN (SELECT id FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab)))';
            $vals['tab'] = $param['tab'];
        }
        $cond = ' WHERE ' . implode(' AND ', $where);

        // Consultar usando prenomina si existe para el periodo (horas guardadas), si no, 192
        $sql = "SELECT 
                    t.id AS expediente,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                    t.carnet_identidad AS ci,
                    COALESCE(c.salario,0) AS tarifa,
                    COALESCE(p.horas, 192.00) AS horas,
                    (COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) AS a_cobrar,
                    NULL AS bonif,
                    (COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) AS sal_dev,
                    COALESCE((SELECT COUNT(*) FROM registro_asistencia ra WHERE ra.trabajador_id = t.id AND ra.ausencia = '1'), 0) AS ausencias,
                    COALESCE((
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL AND pv.fecha_aprobacion <> '' AND pv.dias IS NOT NULL AND pv.dias <> ''
                                    THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0)
                        FROM plan_vacaciones pv
                        WHERE pv.trabajador_id = t.id
                    ), 0) AS vacaciones
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                " . $cond .
                " ORDER BY t.id ASC";

        try {
            $data = $this->db->fetchAll($sql, $vals);
            
            // Calcular campos derivados en PHP para evitar consultas SQL complejas
            foreach ($data as &$row) {
                $horas = floatval($row['horas']);
                $tarifa = floatval($row['tarifa']);
                $ausencias = intval($row['ausencias']);
                $vacaciones = intval($row['vacaciones']);
                
                $a_cobrar = $horas * $tarifa;
                $ausenciasCosto = round($ausencias * 8 * $tarifa, 2);
                $pago_vac = round($tarifa * 8 * $vacaciones, 2);
                $salario_neto = $a_cobrar + $pago_vac;
                $seg_social = round($a_cobrar * 0.05, 2);
                $ing_pers = round($a_cobrar * 0.0375, 2);
                $salario_pagar = round($salario_neto - ($seg_social + $ing_pers + $ausenciasCosto), 2);
                
                $row['a_cobrar'] = $a_cobrar;
                $row['sal_dev'] = $a_cobrar;
                $row['ausenciasCosto'] = $ausenciasCosto;
                $row['pago_vac'] = $pago_vac;
                $row['salario_neto'] = $salario_neto;
                $row['seg_social'] = $seg_social;
                $row['ing_pers'] = $ing_pers;
                $row['salario_pagar'] = $salario_pagar;
            }
        } catch (Exception $e) {
            error_log("Error en consulta Excel prenómina: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error en consulta de datos: ' . $e->getMessage()]);
            exit;
        }

        // Cargar PHPExcel
        try {
            require_once(BASE_CLASS . '/PHPExcel.php');
            $obj = new PHPExcel();
            $obj->getProperties()
                ->setCreator('Sistema')
                ->setTitle('Prenomina');
            $sheet = $obj->setActiveSheetIndex(0);
            $sheet->setTitle('Prenomina');
        } catch (Exception $e) {
            error_log("Error cargando PHPExcel: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error cargando PHPExcel: ' . $e->getMessage()]);
            exit;
        }

        // Obtener nombre del mes
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $nombreMes = isset($meses[$month]) ? $meses[$month] : 'Mes ' . $month;

        // Obtener nombre de usuario (si está disponible en sesión)
        $nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Sistema';

        // Obtener nombre del departamento (si hay filtro por tab)
        $departamento = isset($param['tab']) && trim($param['tab']) !== '' ? $param['tab'] : 'Todos los Departamentos';

        // Encabezado principal
        $sheet->setCellValue('A1', 'Prenómina Correspondiente al Mes de ' . $nombreMes . ' de ' . $year);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:P1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Elaborado Por: ' . $nombreUsuario);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->mergeCells('A2:H2');

        $sheet->setCellValue('I2', 'Área: ' . $departamento);
        $sheet->getStyle('I2')->getFont()->setBold(true);
        $sheet->mergeCells('I2:P2');

        // Línea en blanco
        $sheet->setCellValue('A3', '');

        // Encabezados de columnas
        $headers = [
            'A' => 'No. Exp',
            'B' => 'Nombre y Apellido',
            'C' => 'C.I',
            'D' => 'Horas trabajadas',
            'E' => 'Tarifas x horas',
            'F' => 'A Cobrar',
            'G' => 'Bonif',
            'H' => 'Sal. Dev',
            'I' => 'Ausencias',
            'J' => 'Descuento Ausencias',
            'K' => 'Vacaciones',
            'L' => 'pago x vacaciones',
            'M' => 'Salario Neto',
            'N' => 'importe Seg Social',
            'O' => 'importe Ing Pers',
            'P' => 'Salario a pagar',
        ];
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '4', $title);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
        }

        // Datos
        $rowNum = 5;
        foreach ($data as $r) {
            $sheet->setCellValueExplicit('A' . $rowNum, $r['expediente'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('B' . $rowNum, $r['nombre']);
            $sheet->setCellValueExplicit('C' . $rowNum, $r['ci'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowNum, $r['horas']);
            $sheet->setCellValue('E' . $rowNum, $r['tarifa']);
            $sheet->setCellValue('F' . $rowNum, $r['a_cobrar']);
            $sheet->setCellValue('G' . $rowNum, isset($r['bonif']) ? $r['bonif'] : '');
            $sheet->setCellValue('H' . $rowNum, $r['sal_dev']);
            $sheet->setCellValue('I' . $rowNum, isset($r['ausencias']) ? $r['ausencias'] : '');
            $sheet->setCellValue('J' . $rowNum, isset($r['ausenciasCosto']) ? $r['ausenciasCosto'] : '');
            $sheet->setCellValue('K' . $rowNum, isset($r['vacaciones']) ? $r['vacaciones'] : '');
            $sheet->setCellValue('L' . $rowNum, isset($r['pago_vac']) ? $r['pago_vac'] : '');
            $sheet->setCellValue('M' . $rowNum, $r['salario_neto']);
            $sheet->setCellValue('N' . $rowNum, $r['seg_social']);
            $sheet->setCellValue('O' . $rowNum, $r['ing_pers']);
            $sheet->setCellValue('P' . $rowNum, $r['salario_pagar']);
            $rowNum++;
        }

        // Formatos numéricos
        $sheet->getStyle('D5:D' . ($rowNum-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E5:P' . ($rowNum-1))->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Descarga
        try {
            $filename = 'prenomina_' . $year . '_' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            error_log("Error generando archivo Excel: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error generando archivo Excel: ' . $e->getMessage()]);
            exit;
        }
    }


    private function _list_departamentos() {
        // Devolver solo nombres de departamentos (tabs por nombre)
        if (method_exists($this->app, 'get_list_departamentos')) {
            $rows = $this->app->get_list_departamentos();
        } else {
            $rows = $this->db->fetchAll("SELECT id, nombre FROM departamentos ORDER BY nombre ASC");
        }
        $out = [];
        foreach ($rows as $r) {
            if (is_array($r)) {
                if (isset($r['nombre'])) {
                    $out[] = $r['nombre'];
                } else {
                    $out[] = (string)reset($r);
                }
            } else {
                $out[] = (string)$r;
            }
        }
        return $out;
    }
}
