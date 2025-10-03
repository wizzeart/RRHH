<?php

class Prenomina {

    var $app;
    var $db;

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
                    (SELECT COALESCE(SUM(dias_disfrutados), 0) FROM registro_vacaciones rv WHERE rv.trabajador_id = t.id AND rv.dias_disfrutados > 0) AS vacaciones,                               -- Suma de días de vacaciones disfrutadas
                    ROUND(((COALESCE(c.salario, 0) * 8) * (SELECT COALESCE(SUM(dias_disfrutados), 0) FROM registro_vacaciones rv WHERE rv.trabajador_id = t.id AND rv.dias_disfrutados > 0)), 2) AS pago_vac,  -- Pago por vacaciones: (tarifa × 8) × días_vacaciones
                    ((192.00 * COALESCE(c.salario, 0)) + ROUND(((COALESCE(c.salario, 0) * 8) * (SELECT COALESCE(SUM(dias_disfrutados), 0) FROM registro_vacaciones rv WHERE rv.trabajador_id = t.id AND rv.dias_disfrutados > 0)), 2)) AS salario_neto,  -- Salario neto: salario_base + pago_vacaciones
                    ROUND((192.00 * COALESCE(c.salario, 0)) * 0.05, 2) AS seg_social,                                                                                                               -- Descuento seguridad social: 5% del salario base
                    ROUND((192.00 * COALESCE(c.salario, 0)) * 0.0375, 2) AS ing_pers,                                                                                                              -- Descuento ingresos personales: 3.75% del salario base
                    ROUND(((192.00 * COALESCE(c.salario, 0)) + ROUND(((COALESCE(c.salario, 0) * 8) * (SELECT COALESCE(SUM(dias_disfrutados), 0) FROM registro_vacaciones rv WHERE rv.trabajador_id = t.id AND rv.dias_disfrutados > 0)), 2)) - (ROUND((192.00 * COALESCE(c.salario, 0)) * 0.05, 2) + ROUND((192.00 * COALESCE(c.salario, 0)) * 0.0375, 2)), 2) AS salario_pagar,                -- Salario a pagar: salario_neto - (seg_social + ing_pers)
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
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        $resp = ['status' => 1, 'msg' => 'Guardado', 'errors' => [], 'affected' => 0];
    
        if (!is_array($payload)) {
            $resp['status'] = 0;
            $resp['msg'] = 'JSON inválido';
            echo json_encode($resp);
            exit;
        }
    
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
    
            // Obtener vacaciones
            $sqlVacaciones = "SELECT SUM(dias_disfrutados) as total_dias_vacaciones 
                              FROM registro_vacaciones 
                              WHERE trabajador_id = :tid AND dias_disfrutados > 0";
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
            $salario_pagar = round($salario_neto - ($seg_social + $ing_pers), 2);
    
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
                    'vacaciones' => $vacaciones,
                    'pago_vac' => $pago_vac,
                    'salario_neto' => $salario_neto,
                    'seg_social' => $seg_social,
                    'ing_pers' => $ing_pers,
                    'salario_pagar' => $salario_pagar,
                ];
                try {
                    $this->db->insert('prenomina', $ins);
                } catch (Exception $e) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => $e->getMessage()];
                    continue;
                }
            }
    
            $resp['affected']++;
        }
    
        echo json_encode($resp);
        exit;
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
                    NULL AS ausencias,
                    NULL AS vacaciones,
                    NULL AS pago_vac,
                    (COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) AS salario_neto,
                    ROUND((COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) * 0.05, 2) AS seg_social,
                    ROUND((COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) * 0.0375, 2) AS ing_pers,
                    ROUND((COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) - (((COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) * 0.05) + ((COALESCE(p.horas, 192.00) * COALESCE(c.salario,0)) * 0.0375)), 2) AS salario_pagar
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                " . $cond .
                " ORDER BY t.id ASC";

        $data = $this->db->fetchAll($sql, $vals);

        // Cargar PHPExcel
        require_once(BASE_CLASS . '/PHPExcel.php');
        $obj = new PHPExcel();
        $obj->getProperties()
            ->setCreator('Sistema')
            ->setTitle('Prenomina');
        $sheet = $obj->setActiveSheetIndex(0);
        $sheet->setTitle('Prenomina');

        // Encabezados
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
            'J' => 'Vacaciones',
            'K' => 'pago x vacaciones',
            'L' => 'Salario Neto',
            'M' => 'importe Seg Social',
            'N' => 'importe Ing Pers',
            'O' => 'Salario a pagar',
        ];
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }

        // Datos
        $rowNum = 2;
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
            $sheet->setCellValue('J' . $rowNum, isset($r['vacaciones']) ? $r['vacaciones'] : '');
            $sheet->setCellValue('K' . $rowNum, isset($r['pago_vac']) ? $r['pago_vac'] : '');
            $sheet->setCellValue('L' . $rowNum, $r['salario_neto']);
            $sheet->setCellValue('M' . $rowNum, $r['seg_social']);
            $sheet->setCellValue('N' . $rowNum, $r['ing_pers']);
            $sheet->setCellValue('O' . $rowNum, $r['salario_pagar']);
            $rowNum++;
        }

        // Formatos numéricos
        $sheet->getStyle('D2:D' . ($rowNum-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E2:O' . ($rowNum-1))->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Descarga
        $filename = 'prenomina_' . $year . '_' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $writer->save('php://output');
        exit;
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
