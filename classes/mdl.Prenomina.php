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
            case 'save-horas':
                $this->_save_horas();
                break;
            case 'export-excel':
                $this->_export_excel($param);
                break;
        }
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
        $whereParts = ["t.trabajador_eliminado = '0'"];

        // Filtro por pestaña (departamento o categoría)
        if (isset($param['tab']) && $param['tab'] !== '') {
            $vals['tab'] = $param['tab'];
            $whereParts[] = 'LOWER(d.nombre) = LOWER(:tab)';
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
                    COALESCE(c.salario, 0) AS tarifa,
                    192.00 AS horas,
                    (192.00 * COALESCE(c.salario, 0)) AS a_cobrar,
                    NULL AS bonif,
                    (192.00 * COALESCE(c.salario, 0)) AS sal_dev,
                    NULL AS ausencias,
                    NULL AS vacaciones,
                    NULL AS pago_vac,
                    (192.00 * COALESCE(c.salario, 0)) AS salario_neto,
                    ROUND((192.00 * COALESCE(c.salario, 0)) * 0.05, 2) AS seg_social,
                    ROUND((192.00 * COALESCE(c.salario, 0)) * 0.0375, 2) AS ing_pers,
                    ROUND((192.00 * COALESCE(c.salario, 0)) - (((192.00 * COALESCE(c.salario, 0)) * 0.05) + ((192.00 * COALESCE(c.salario, 0)) * 0.0375)), 2) AS salario_pagar,
                    COALESCE(d.nombre, '') AS departamento
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
            print(json_encode($resp));
            return;
        }

        $year = isset($payload['year']) ? intval($payload['year']) : intval(date('Y'));
        $month = isset($payload['month']) ? intval($payload['month']) : intval(date('n'));
        $rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : [];
        if (empty($rows)) {
            $resp['status'] = 0;
            $resp['msg'] = 'Sin filas a guardar';
            print(json_encode($resp));
            return;
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

            if ($row) {
                // Mantener tarifa guardada
                $tarifa = floatval($row['tarifa']);
                $a_cobrar = $horas * $tarifa;
                $seg_social = round($a_cobrar * 0.05, 2);
                $ing_pers = round($a_cobrar * 0.0375, 2);
                $salario_pagar = round($a_cobrar - ($seg_social + $ing_pers), 2);

                $upd = [
                    'horas' => $horas,
                    'a_cobrar' => $a_cobrar,
                    'sal_dev' => $a_cobrar,
                    'salario_neto' => $a_cobrar,
                    'seg_social' => $seg_social,
                    'ing_pers' => $ing_pers,
                    'salario_pagar' => $salario_pagar,
                ];
                $where = [
                    'trabajador_id' => $trabajador_id,
                    '`year`' => $year,
                    '`month`' => $month,
                ];
                // Nuestra utilidad update necesita where sin backticks en claves
                $where = [ 'trabajador_id' => $trabajador_id, 'year' => $year, 'month' => $month ];
                $this->db->update('prenomina', $upd, $where);
                $resp['affected']++;
            } else {
                // Tomar tarifa desde cargos del trabajador
                $sqlTar = "SELECT COALESCE(c.salario,0) AS tarifa FROM trabajadores t LEFT JOIN cargos c ON t.cargos_id=c.id WHERE t.id=:tid";
                $trow = $this->db->fetchRow($sqlTar, ['tid' => $trabajador_id]);
                $tarifa = $trow ? floatval($trow['tarifa']) : 0.0;
                $a_cobrar = $horas * $tarifa;
                $seg_social = round($a_cobrar * 0.05, 2);
                $ing_pers = round($a_cobrar * 0.0375, 2);
                $salario_pagar = round($a_cobrar - ($seg_social + $ing_pers), 2);

                $ins = [
                    'trabajador_id' => $trabajador_id,
                    'year' => $year,
                    'month' => $month,
                    'horas' => $horas,
                    'tarifa' => $tarifa,
                    'a_cobrar' => $a_cobrar,
                    'bonif' => null,
                    'sal_dev' => $a_cobrar,
                    'ausencias' => null,
                    'vacaciones' => null,
                    'pago_vac' => null,
                    'salario_neto' => $a_cobrar,
                    'seg_social' => $seg_social,
                    'ing_pers' => $ing_pers,
                    'salario_pagar' => $salario_pagar,
                ];
                try {
                    $this->db->insert('prenomina', $ins);
                    $resp['affected']++;
                } catch (Exception $e) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => $e->getMessage()];
                }
            }
        }

        print(json_encode($resp));
    }

    private function _export_excel($param) {
        // Parámetros de periodo
        $year = isset($param['year']) ? intval($param['year']) : intval(date('Y'));
        $month = isset($param['month']) ? intval($param['month']) : intval(date('n'));
        $vals = ['y' => $year, 'm' => $month];

        // Filtro por tab
        $where = ["t.trabajador_eliminado='0'"];
        if (isset($param['tab']) && trim($param['tab']) !== '') {
            $where[] = 'LOWER(d.nombre)=LOWER(:tab)';
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
}
