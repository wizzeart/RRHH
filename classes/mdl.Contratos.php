<?php
class Contrato {
    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    // Lista de trabajadores para poblar el select del formulario
    private function _list_trabajadores() {
        try {
            $sql = "SELECT id, nombre, apellidos, apellidos_segundos
                    FROM trabajadores
                    WHERE trabajador_eliminado = '0'
                    ORDER BY apellidos ASC, nombre ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return array();
        }
    }

    // Lista de departamentos para poblar el select
    private function _list_departamentos() {
        try {
            $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return array();
        }
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'list-id':
                $data = $this->_list_id($param);
                print(json_encode($data));
                break;
            case 'list-trabajadores':
                $data = $this->_list_trabajadores();
                print(json_encode($data));
                break;
            case 'list-departamentos':
                $data = $this->_list_departamentos();
                print(json_encode($data));
                break;
            case 'get-trabajador-data':
                $data = $this->_get_trabajador_data($param);
                print(json_encode($data));
                break;
            case 'getTemplate':
                $this->_getTemplate($param);
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'generatePdfFromHtml':
                $this->_generatePdfFromHtml($param);
                break;
            case 'generatePdfWithFpdf':
                $this->_generatePdfWithFpdf($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;
        switch ($param['module']) {
            case 'list-contratos':
                $data = array();
                $page['title'] = 'Contratos';
                $page['subtitle'] = 'Listado de Contratos';
                break;
            case 'contratos':
                $data = array();
                $page['title'] = 'Nuevo Contrato';
                $page['subtitle'] = 'Registro de Contrato';
                $action = 'insert';

                // Cargar trabajadores para el select (sin filtros restrictivos)
                try {
                    $sql = "SELECT id, nombre, apellidos, apellidos_segundos FROM trabajadores ORDER BY apellidos ASC, nombre ASC";
                    $data_form['trabajadores'] = $this->db->fetchAll($sql);
                } catch (Exception $e) {
                    $data_form['trabajadores'] = array();
                }
                
                // Cargar departamentos para el select de ubicación laboral
                try {
                    $data_form['departamentos'] = $this->app->get_list_departamentos();
                    if (!isset($data_form['departamentos']) || !is_array($data_form['departamentos']) || count($data_form['departamentos']) === 0) {
                        // Fallback directo a la tabla 'departamentos'
                        $data_form['departamentos'] = $this->db->fetchAll("SELECT id, nombre FROM departamentos ORDER BY nombre");
                    }
                } catch (Exception $e) {
                    // Fallback en caso de error usando App
                    try { $data_form['departamentos'] = $this->db->fetchAll("SELECT id, nombre FROM departamentos ORDER BY nombre"); }
                    catch(Exception $e2) { $data_form['departamentos'] = array(); }
                }
                
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Contrato';
                    $action = 'update';
                    $val = array('id' => $param['id']);
                    $sql = "SELECT * FROM contratos WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) { $data = $row; $page['subtitle'] = 'Contrato: ' . $row['id']; }
                }
                break;
        }
    }

    // Devuelve plantilla HTML según tipo (lee de /docs)
    private function _getTemplate($param) {
        $tipo = isset($param['tipo']) ? $param['tipo'] : '';
        $map = array(
            'Contrato por Tiempo Indeterminado' => BASE . '/docs/contrato-de-trabajo-indeterminado/contrato-de-trabajo-indeterminado.html',
            'Contrato por Tiempo Determinado' => BASE . '/docs/cotrato_pedriodo_prueba/contrato-de-trabajo-periodo-de-prueba-1.php',
            'Suplemento' => BASE . '/docs/suplemento/suplemento-al-contrato-de-trabajo.php'
        );
        $path = isset($map[$tipo]) ? $map[$tipo] : null;
        if ($path && file_exists($path)) {
            // Entregamos el HTML tal cual para que el frontend lo procese
            header('Content-Type: text/html; charset=utf-8');
            readfile($path);
            return;
        }
        // No encontrado
        header('HTTP/1.1 404 Not Found');
        print('Plantilla no encontrada');
    }

    // Genera PDF a partir del HTML enviado (ya con valores reemplazados)
    private function _generatePdfFromHtml($param) {
        $response = array('status'=>0,'msg'=>'', 'file_url'=>'');
        $html = isset($param['html']) ? $param['html'] : '';
        $tipo = isset($param['tipo']) ? $param['tipo'] : 'contrato';
        $contrato_id = isset($param['contrato_id']) && $param['contrato_id'] !== '' ? intval($param['contrato_id']) : null;

        if (empty($html)) { $response['msg'] = 'HTML no proporcionado'; print(json_encode($response)); return; }

        // Cargar mPDF
        $autoloads = array(
            BASE . '/plugins/mpdf/vendor/autoload.php',
            BASE . '/plugins/mpdf/autoload.php',
            __DIR__ . '/../plugins/mpdf/vendor/autoload.php',
            __DIR__ . '/../plugins/mpdf/autoload.php'
        );
        foreach ($autoloads as $auto) { if (file_exists($auto)) { require_once($auto); break; } }
        if (!class_exists('Mpdf\\Mpdf')) { $response['msg']='mPDF no disponible'; print(json_encode($response)); return; }

        try {
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
            // opcional: añadir CSS base
            $mpdf->WriteHTML($html);

            $upload_dir = 'uploads/contratos/'; if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }
            $fileId = $contrato_id ? $contrato_id : time();
            $fileName = 'contrato_' . $fileId . '_' . preg_replace('/[^a-z0-9_\-]/i','',str_replace(' ','_',substr($tipo,0,50))) . '.pdf';
            $fullPath = rtrim($upload_dir, '/\\') . '/' . $fileName;
            $mpdf->Output($fullPath, 'F');

            $response['status']=1; $response['file_url'] = $fullPath; $response['msg']='PDF generado correctamente';
            // Si contrato_id fue proporcionado actualizamos la columna archivo_contrato
            if ($contrato_id) {
                try { $this->db->update('contratos', array('archivo_contrato'=>$fullPath), array('id'=>$contrato_id)); } catch(Exception $e) { /* silencio */ }
            }
        } catch (Exception $e) {
            $response['msg'] = 'Error al generar PDF: ' . $e->getMessage();
        }
        print(json_encode($response));
    }


    // Genera PDF usando la librería FPDF (texto plano a partir de plantilla PHP)
    private function _generatePdfWithFpdf($param) {
        $response = array('status'=>0,'msg'=>'', 'file_url'=>'');
        $tipo = isset($param['tipo']) ? $param['tipo'] : '';
        $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
        $contrato_id = isset($param['contrato_id']) && $param['contrato_id'] !== '' ? intval($param['contrato_id']) : null;

        // Recover extras (JSON string expected)
        $extras = array();
        if (isset($param['extras']) && $param['extras']) {
            $e = json_decode($param['extras'], true);
            if (is_array($e)) $extras = $e;
        }

        // Map tipo to PHP template path (use the php files)
        $map = array(
            'Contrato por Tiempo Indeterminado' => BASE . '/docs/contrato-de-trabajo-indeterminado/contrato-de-trabajo-indeterminado.php',
            'Contrato por Tiempo Determinado' => BASE . '/docs/cotrato_pedriodo_prueba/contrato-de-trabajo-periodo-de-prueba-1.php',
            'Suplemento' => BASE . '/docs/suplemento/suplemento-al-contrato-de-trabajo.php'
        );
        $path = isset($map[$tipo]) ? $map[$tipo] : null;
        if (!$path || !file_exists($path)) { $response['msg']='Plantilla no encontrada'; print(json_encode($response)); return; }

        // Make template variables available: extras keys + trabajador data
        $tplData = array();
        foreach ($extras as $k=>$v) { $tplData[$k] = $v; }
        if ($trabajador_id) {
            try { $trab = $this->db->fetchRow("SELECT nombre, apellidos, carnet_identidad FROM trabajadores WHERE id = :id", array('id'=>$trabajador_id));
                if ($trab) { $tplData['trabajador_nombre'] = trim(($trab['nombre'] ?? '') . ' ' . ($trab['apellidos'] ?? '')); $tplData['trabajador_ci'] = $trab['carnet_identidad'] ?? ''; }
            } catch(Exception $e) { /* ignore */ }
        }

        // Render template into HTML string by capturing include output and replacing named placeholders
        ob_start();
        // make $tplData available to template
        $__tpl = $tplData;
        // include template in separate scope
        try {
            include $path;
        } catch(Exception $e) {
            ob_end_clean();
            $response['msg'] = 'Error al procesar plantilla: ' . $e->getMessage(); print(json_encode($response)); return;
        }
        $html = ob_get_clean();

        // Normalize extras: map formas_pago array to readable text and ensure keys match placeholders
        if (isset($tplData['formas_pago']) && is_array($tplData['formas_pago'])) {
            $mapFormas = array('1' => 'A sueldo', '2' => 'Por tarifa horaria', '3' => 'Por resultados', '4' => 'A Destajo');
            $pieces = array();
            foreach ($tplData['formas_pago'] as $f) { $pieces[] = isset($mapFormas[$f]) ? $mapFormas[$f] : $f; }
            $tplData['formas_pago'] = implode(', ', $pieces);
        }
        // Also set common aliases
        if (isset($tplData['cargo_nombre'])) { $tplData['cargo'] = $tplData['cargo_nombre']; }

        // Replace placeholders like {{DE_NOMBRE}} etc with extras (if appear)
        foreach ($tplData as $k=>$v) {
            $ph = '{{' . strtoupper($k) . '}}';
            $html = str_replace($ph, $v, $html);
        }

        // Load FPDF
        $fpdfPath = __DIR__ . '/fpdf/fpdf.php';
        if (!file_exists($fpdfPath)) { $response['msg'] = 'FPDF no encontrado en classes/fpdf/'; print(json_encode($response)); return; }
        require_once($fpdfPath);

        // Very simple approach: strip tags and write lines to PDF
        $text = strip_tags($html);
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        try {
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial','',12);
            $maxWidth = 190; // approx mm
            $pdf->SetAutoPageBreak(true, 10);
            // split text into words and assemble lines
            $words = explode(' ', $text);
            $line = '';
            foreach ($words as $w) {
                $test = trim($line . ' ' . $w);
                if ($pdf->GetStringWidth($test) > $maxWidth) {
                    $pdf->Cell(0, 6, utf8_decode(trim($line)), 0, 1);
                    $line = $w;
                } else { $line = $test; }
            }
            if (trim($line) !== '') $pdf->Cell(0, 6, utf8_decode(trim($line)), 0, 1);

            $upload_dir = 'uploads/contratos/'; if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }
            $fileId = $contrato_id ? $contrato_id : time();
            $fileName = 'contrato_fpdf_' . $fileId . '.pdf';
            $fullPath = rtrim($upload_dir, '/\\') . '/' . $fileName;
            $pdf->Output('F', $fullPath);

            $response['status'] = 1; $response['file_url'] = $fullPath; $response['msg'] = 'OK';
            if ($contrato_id) { try { $this->db->update('contratos', array('archivo_contrato'=>$fullPath), array('id'=>$contrato_id)); } catch(Exception $e) { }
            }
        } catch(Exception $e) {
            $response['msg'] = 'Error FPDF: ' . $e->getMessage();
        }

        print(json_encode($response));
    }

    private function _list($param) {
        $data = array();
        try {
            $sql = "SELECT c.*, t.nombre, t.apellidos, CONCAT(t.nombre, ' ', t.apellidos) as trabajador_nombre
                    FROM contratos c
                    LEFT JOIN trabajadores t ON t.id = c.trabajador_id
                    ORDER BY c.id DESC";
            $data = $this->db->fetchAll($sql);
        } catch (Exception $e) {
            $data = array();
        }
        return $data;
    }

    private function _list_id($param) {
        $data = array();
        try {
            $sql = "SELECT c.*
                    FROM contratos c
                    LEFT JOIN trabajadores t ON t.id = c.trabajador_id
                    WHERE c.trabajador_id=:id
                    ORDER BY c.id DESC";
            $data = $this->db->fetchAll($sql, array('id' => $param['trabajador_id']));
        } catch (Exception $e) {
            $data = array();
        }
        return $data;
    }
    
    // Nuevo método para obtener datos completos del trabajador
    private function _get_trabajador_data($param) {
        $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
        $data = array('status' => 0, 'data' => array());
        if ($trabajador_id <= 0) { $data['msg'] = 'ID de trabajador inválido'; return $data; }

        // Primer intento: tablas en singular (provincia, municipio)
        $sqlSingular = "SELECT 
                            t.id, t.nombre, t.apellidos, t.apellidos_segundos, t.direccion,
                            t.cargos_id, t.provincia_id, t.municipio_id,
                            c.nombre as cargo_nombre,
                            p.nombre as provincia_nombre,
                            m.nombre as municipio_nombre
                        FROM trabajadores t
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        LEFT JOIN provincia p ON t.provincia_id = p.id
                        LEFT JOIN municipio m ON t.municipio_id = m.id
                        WHERE t.id = :id AND t.trabajador_eliminado = '0'";
        // Segundo intento: tablas en plural (provincias, municipios)
        $sqlPlural = "SELECT 
                            t.id, t.nombre, t.apellidos, t.apellidos_segundos, t.direccion,
                            t.cargos_id, t.provincia_id, t.municipio_id,
                            c.nombre as cargo_nombre,
                            p.nombre as provincia_nombre,
                            m.nombre as municipio_nombre
                        FROM trabajadores t
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        LEFT JOIN provincias p ON t.provincia_id = p.id
                        LEFT JOIN municipios m ON t.municipio_id = m.id
                        WHERE t.id = :id AND t.trabajador_eliminado = '0'";

        $trabajador = null;
        try {
            $trabajador = $this->db->fetchRow($sqlSingular, array('id' => $trabajador_id));
        } catch (Exception $e1) {
            try {
                $trabajador = $this->db->fetchRow($sqlPlural, array('id' => $trabajador_id));
            } catch (Exception $e2) {
                $data['msg'] = 'Error al consultar trabajador';
                return $data;
            }
        }

        if ($trabajador) {
            $data['status'] = 1;
            $data['data'] = array(
                'id' => $trabajador['id'],
                'nombre' => $trabajador['nombre'] ?? '',
                'apellidos' => $trabajador['apellidos'] ?? '',
                'apellidos_segundos' => $trabajador['apellidos_segundos'] ?? '',
                'direccion' => $trabajador['direccion'] ?? '',
                'cargos_id' => $trabajador['cargos_id'] ?? '',
                'cargo_nombre' => $trabajador['cargo_nombre'] ?? 'Sin cargo',
                'provincia_id' => $trabajador['provincia_id'] ?? '',
                'provincia_nombre' => $trabajador['provincia_nombre'] ?? 'Sin provincia',
                'municipio_id' => $trabajador['municipio_id'] ?? '',
                'municipio_nombre' => $trabajador['municipio_nombre'] ?? 'Sin municipio'
            );
        } else {
            $data['msg'] = 'Trabajador no encontrado';
        }
        return $data;
    }

    private function _save($param) {
        $data = array('status'=>1, 'msg_title'=>'Éxito', 'msg'=>'Contrato guardado correctamente');

        // Validaciones básicas - nuevos campos
        $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
        $tipo_contrato = isset($param['tipo_contrato']) ? trim($param['tipo_contrato']) : '';
        $departamento_id = isset($param['departamento_id']) ? intval($param['departamento_id']) : 0;
        $regimen_descanso = isset($param['regimen_descanso']) ? trim($param['regimen_descanso']) : '';
        $salario_base = isset($param['salario_base']) ? floatval($param['salario_base']) : 0;
        $modalidad_trabajo = isset($param['modalidad_trabajo']) ? trim($param['modalidad_trabajo']) : '';
        
        // La fecha de inicio se toma automáticamente como la fecha actual
        $fecha_inicio = date('Y-m-d');

        if ($trabajador_id <= 0) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El trabajador es obligatorio'; print(json_encode($data)); return; }
        if ($tipo_contrato === '') { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El tipo de contrato es obligatorio'; print(json_encode($data)); return; }
        if ($departamento_id <= 0) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='La ubicación laboral es obligatoria'; print(json_encode($data)); return; }
        if ($regimen_descanso === '') { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El régimen de descanso es obligatorio'; print(json_encode($data)); return; }
        if ($salario_base <= 0) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El salario base debe ser mayor a 0'; print(json_encode($data)); return; }
        if ($modalidad_trabajo === '') { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='La modalidad de trabajo es obligatoria'; print(json_encode($data)); return; }

        // Manejo de uploads (solo firma, el contrato se genera como PDF automáticamente)
        $upload_dir = 'uploads/contratos/';
        if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }
        $archivo_contrato_path = null; // será el PDF generado
        $firma_digital_path = null;

        if (isset($_FILES['archivo_contrato']) && $_FILES['archivo_contrato']['error'] === UPLOAD_ERR_OK) {
            $name = uniqid('contrato_') . '_' . basename($_FILES['archivo_contrato']['name']);
            $path = $upload_dir . $name;
            if (move_uploaded_file($_FILES['archivo_contrato']['tmp_name'], $path)) { $archivo_contrato_path = $path; }
        }
        if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] === UPLOAD_ERR_OK) {
            $name = uniqid('firma_') . '_' . basename($_FILES['firma_digital']['name']);
            $path = $upload_dir . $name;
            if (move_uploaded_file($_FILES['firma_digital']['tmp_name'], $path)) { $firma_digital_path = $path; }
        }

        $insert = array(
            'trabajador_id'     => $trabajador_id,
            'tipo_contrato'     => $tipo_contrato,
            'departamento_id'   => $departamento_id,
            'regimen_descanso'  => $regimen_descanso,
            'salario_base'      => $salario_base,
            'modalidad_trabajo' => $modalidad_trabajo,
            'fecha_inicio'      => $fecha_inicio,
            'archivo_contrato'  => $archivo_contrato_path,
            'firma_digital'     => $firma_digital_path
        );

        try {
            if (!isset($param['id']) || $param['id'] == '') {
                // 1) Insertar sin archivo_contrato para obtener el ID
                $result = $this->db->insert('contratos', $insert);
                if ($result) {
                    $lastId = method_exists($this->db, 'last_id') ? $this->db->last_id() : (method_exists($this->db, 'lastInsertId') ? $this->db->lastInsertId() : null);
                    if ($lastId) {
                        $data['id'] = $lastId;
                        // 2) Generar PDF con mPDF
                        $pdfPath = $this->generar_pdf_contrato($upload_dir, $lastId, $trabajador_id, $tipo_contrato, $fecha_inicio, null, $firma_digital_path);
                        if ($pdfPath) {
                            // 3) Actualizar ruta del archivo en BD
                            $this->db->update('contratos', array('archivo_contrato' => $pdfPath), array('id' => $lastId));
                            $data['file_url'] = $pdfPath;
                        }
                    }
                } else { $data['status']=0; $data['msg_title']='Error'; $data['msg']='Error al insertar'; }
            } else {
                $id = intval($param['id']);
                // No sobrescribir archivos si no subieron nuevos
                if (!$archivo_contrato_path) unset($insert['archivo_contrato']);
                if (!$firma_digital_path) unset($insert['firma_digital']);
                $where = array('id' => $id);
                $result = $this->db->update('contratos', $insert, $where);
                if ($result === false) { $data['status']=0; $data['msg_title']='Error'; $data['msg']='Error al actualizar'; }
                else {
                    $data['id'] = $id;
                    // Regenerar PDF con datos actualizados
                    $pdfPath = $this->generar_pdf_contrato($upload_dir, $id, $trabajador_id, $tipo_contrato, $fecha_inicio, null, isset($insert['firma_digital']) ? $insert['firma_digital'] : $firma_digital_path);
                    if ($pdfPath) {
                        $this->db->update('contratos', array('archivo_contrato' => $pdfPath), array('id' => $id));
                        $data['file_url'] = $pdfPath;
                    }
                }
            }
        } catch (Exception $e) {
            $data['status']=0; $data['msg_title']='Error'; $data['msg']='Error al guardar: ' . $e->getMessage();
        }
        print(json_encode($data));
    }

    // Helper: Genera el PDF del contrato con mPDF
    private function generar_pdf_contrato($upload_dir, $id, $trabajador_id, $tipo, $fecha_inicio, $fecha_fin, $firma_digital_path) {
        // Cargar datos del trabajador (usar nombres de columnas reales)
        $trab = $this->db->fetchRow("SELECT nombre, apellidos, carnet_identidad FROM trabajadores WHERE id = :id", array('id' => $trabajador_id));
        $nombreCompleto = '';
        if ($trab) {
            $nombreCompleto = trim(($trab['nombre'] ?? '') . ' ' . ($trab['apellidos'] ?? ''));
        }

        // Cargar mPDF
        $autoloads = array(
            BASE . '/plugins/mpdf/vendor/autoload.php',
            BASE . '/plugins/mpdf/autoload.php',
            __DIR__ . '/../plugins/mpdf/vendor/autoload.php',
            __DIR__ . '/../plugins/mpdf/autoload.php'
        );
        foreach ($autoloads as $auto) {
            if (file_exists($auto)) { require_once($auto); break; }
        }
        if (!class_exists('Mpdf\\Mpdf')) {
            return null; // mPDF no disponible
        }
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);

        // Estilos simples (puedes reemplazar por plantilla propia)
        $css = 'body { font-family: DejaVu Sans, sans-serif; font-size: 12px; } .title { text-align:center; font-weight:bold; font-size:18px; margin-bottom:10px; } .sec h3 { margin: 10px 0 5px; } .row { margin: 6px 0; } .label { color:#666; width: 180px; display:inline-block; }';
        $html = '<html><head><style>' . $css . '</style></head><body>'
              . '<div class="title">Contrato #' . htmlspecialchars((string)$id) . '</div>'
              . '<div class="sec">'
              . '<div class="row"><span class="label">Trabajador:</span> ' . htmlspecialchars($nombreCompleto ?: ('ID ' . $trabajador_id)) . '</div>'
              . '<div class="row"><span class="label">Tipo de Contrato:</span> ' . htmlspecialchars($tipo) . '</div>'
              . '<div class="row"><span class="label">Fecha Inicio:</span> ' . htmlspecialchars($fecha_inicio ?: '') . '</div>'
              . '<div class="row"><span class="label">Fecha Fin:</span> ' . htmlspecialchars($fecha_fin ?: 'Indefinido') . '</div>'
              . '</div>';
        if (!empty($firma_digital_path)) {
            $html .= '<div class="sec"><h3>Firma Digital</h3><div class="row"><img src="' . htmlspecialchars($firma_digital_path) . '" style="max-width:250px; max-height:120px;"></div></div>';
        }
        $html .= '<div class="sec"><h3>Cláusulas</h3><div class="row">Este documento ha sido generado automáticamente por el sistema.</div></div>';
        $html .= '</body></html>';

        $mpdf->WriteHTML($html);
        $fileName = 'contrato_' . $id . '.pdf';
        $fullPath = rtrim($upload_dir, '/\\') . '/' . $fileName;
        $mpdf->Output($fullPath, 'F');
        return $fullPath;
    }
}
