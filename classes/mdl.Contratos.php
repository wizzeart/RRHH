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

    // Renderiza la vista PHP del contrato único con variables pasadas desde el formulario
    private function _render_contrato_unico($param) {
        $resp = array('status'=>0, 'html'=>'');
        try {
            $tplPath = __DIR__ . '/../docs/contrato-unico/ContratoDeTrabajo.php';
            if (!file_exists($tplPath)) {
                // Intento alterno relativo desde htdocs
                $tplPath = realpath(__DIR__ . '/../docs/contrato-unico/ContratoDeTrabajo.php');
            }
            if (!file_exists($tplPath)) {
                print(json_encode(array('status'=>0, 'msg'=>'Plantilla no encontrada')));
                return;
            }

            // Mapeo básico de variables desde el formulario y datos del trabajador
            $nombre = isset($param['trabajador_nombre']) ? $param['trabajador_nombre'] : '';
            $apellidos = isset($param['trabajador_apellidos']) ? $param['trabajador_apellidos'] : '';
            $apellidos2 = isset($param['trabajador_apellidos_segundos']) ? $param['trabajador_apellidos_segundos'] : '';
            $direccion = isset($param['trabajador_direccion']) ? $param['trabajador_direccion'] : '';
            $municipio_nombre = isset($param['trabajador_municipio']) ? $param['trabajador_municipio'] : '';
            $provincia_nombre = isset($param['trabajador_provincia']) ? $param['trabajador_provincia'] : '';
            $tipo_contrato_texto = isset($param['tipo_contrato_texto']) ? $param['tipo_contrato_texto'] : '';
            $modalidad_trabajo_texto = isset($param['modalidad_trabajo_texto']) ? $param['modalidad_trabajo_texto'] : '';
            $ubicacion_laboral_texto = isset($param['ubicacion_laboral_texto']) ? $param['ubicacion_laboral_texto'] : '';
            $regimen_descanso = isset($param['regimen_descanso']) ? $param['regimen_descanso'] : '';
            $salario_base = isset($param['salario_base']) ? $param['salario_base'] : '';
            $hora_desde_h = isset($param['hora_desde_h']) ? $param['hora_desde_h'] : '';
            $hora_desde_m = isset($param['hora_desde_m']) ? $param['hora_desde_m'] : '';
            $hora_hasta_h = isset($param['hora_hasta_h']) ? $param['hora_hasta_h'] : '';
            $hora_hasta_m = isset($param['hora_hasta_m']) ? $param['hora_hasta_m'] : '';

            // Variables requeridas por ContratoDeTrabajo.php
            $var1 = trim($nombre . ' ' . $apellidos . ' ' . $apellidos2);
            $var2 = isset($param['trabajador_ci']) ? $param['trabajador_ci'] : '';
            $var3 = $direccion; // dirección
            $var4 = ''; // detalles extra dirección
            $var5 = $municipio_nombre; // municipio
            $var6 = $provincia_nombre; // provincia
            // Marcar tipo de contrato en la plantilla: (___) o (_X_)
            // Plantilla tiene dos marcadores: uno para "determinado" ($var23) y otro para "indeterminado" ($var7)
            if (strcasecmp($tipo_contrato_texto, 'Tiempo Determinado') === 0) {
                $var23 = '_X_';
                $var7  = '___';
            } elseif (strcasecmp($tipo_contrato_texto, 'Tiempo Indeterminado') === 0) {
                $var23 = '___';
                $var7  = '_X_';
            } else {
                // Fallback: si viene un texto libre, no marcamos X y dejamos mostrar texto si la plantilla lo contempla
                $var23 = '___';
                $var7  = '___';
            }

            // Modalidad con tres marcadores: presencial ($var24), a distancia ($var25), teletrabajo ($var26)
            $var24 = '___';
            $var25 = '___';
            $var26 = '___';
            if ($modalidad_trabajo_texto !== '') {
                if (stripos($modalidad_trabajo_texto, 'presencial') !== false) { $var24 = '_X_'; }
                if (stripos($modalidad_trabajo_texto, 'distancia') !== false || stripos($modalidad_trabajo_texto, 'remoto') !== false) { $var25 = '_X_'; }
                if (stripos($modalidad_trabajo_texto, 'teletrabajo') !== false || stripos($modalidad_trabajo_texto, 'tele-trabajo') !== false) { $var26 = '_X_'; }
            }
            // Frecuencia de pago/trabajo: tres marcadores (semanal, quincenal, mensual)
            $var27 = '(_)'; // semanal
            $var28 = '(_)'; // quincenal
            $var29 = '(_)'; // mensual
            if (!empty($param['frecuencia_trabajo'])) {
                $freq = strtolower(trim($param['frecuencia_trabajo']));
                if ($freq === 'semanal') { $var27 = '(X)'; }
                else if ($freq === 'quincenal') { $var28 = '(X)'; }
                else if ($freq === 'mensual') { $var29 = '(X)'; }
            }
            // Mantener compatibilidad con plantilla previa
            $var8 = $modalidad_trabajo_texto; // etiqueta modalidad si se imprime como texto
            $var9 = $modalidad_trabajo_texto; // legado donde se repite
            $var10 = isset($param['cargo_nombre']) ? $param['cargo_nombre'] : '';
            $var11 = $ubicacion_laboral_texto; // lugar de trabajo
            $var12 = isset($param['regimen_trabajo_desde']) ? $param['regimen_trabajo_desde'] : ''; // días de trabajo
            $var13 = isset($param['regimen_trabajo_hasta']) ? $param['regimen_trabajo_hasta'] : ''; // turno
            $var14 = isset($param['hora_rango_label']) ? $param['hora_rango_label'] : 'De';
            // Regimen de Trabajo: días de la semana seleccionados en el formulario
            $var15 = isset($param['hora_desde_h']) ? $param['hora_desde_h'] : '';
            $var16 = isset($param['hora_hasta_h']) ? $param['hora_hasta_h'] : '';
            $var17 = $hora_hasta_m; // hora hasta (M)
            // Descanso: si no viene 'descanso_dias', intentar extraer número de 'regimen_descanso'
            if (isset($param['descanso_dias']) && $param['descanso_dias'] !== '') {
                $var18 = $param['descanso_dias'];
            } else {
                $var18 = '';
                if (!empty($regimen_descanso)) {
                    if (preg_match('/\d+/', $regimen_descanso, $m)) { $var18 = $m[0]; }
                }
            }
            $var19 = $salario_base; // salario
            $var20 = isset($param['extra1']) ? $param['extra1'] : '';
            $var21 = isset($param['extra2']) ? $param['extra2'] : '';
            $var22 = isset($param['extra3']) ? $param['extra3'] : '';
            // Año actual de la máquina (formato 4 dígitos)
            $var25 = date('Y');
            ob_start();
            include $tplPath;
            $html = ob_get_clean();
            print(json_encode(array('status'=>1, 'html'=>$html)));
        } catch (Exception $e) {
            print(json_encode(array('status'=>0, 'msg'=>'Error al renderizar plantilla')));
        }
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
            case 'render-contrato-unico':
                $this->_render_contrato_unico($param);
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

        // Cargar mPDF (si está disponible) o hacer fallback a TCPDF
        $loadedPdfEngine = null;
        // 1) Intentar mPDF
        $autoloads = array(
            BASE . '/plugins/mpdf/vendor/autoload.php',
            BASE . '/plugins/mpdf/autoload.php',
            __DIR__ . '/../plugins/mpdf/vendor/autoload.php',
            __DIR__ . '/../plugins/mpdf/autoload.php'
        );
        foreach ($autoloads as $auto) { if (file_exists($auto)) { @require_once($auto); break; } }
        if (class_exists('Mpdf\\Mpdf')) { $loadedPdfEngine = 'mpdf'; }

        // 2) Si no hay mPDF, intentar TCPDF
        if ($loadedPdfEngine === null) {
            // Intentar include recomendado de TCPDF
            $tcpdfIncludes = array(
                BASE . '/plugins/tcpdf/tcpdf_include.php',
                __DIR__ . '/../plugins/tcpdf/tcpdf_include.php',
                BASE . '/tcpdf/tcpdf_include.php',
                __DIR__ . '/../tcpdf/tcpdf_include.php',
                // Variantes basadas en DOCUMENT_ROOT provistas por el usuario
                (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/plugins/tcpdf/tcpdf_include.php' : null)
            );
            $loaded = false;
            foreach ($tcpdfIncludes as $inc) { if ($inc && file_exists($inc)) { @require_once($inc); $loaded = true; break; } }
            if (!$loaded) {
                // Fallback directo a tcpdf.php
                $tcpdfPaths = array(
                    BASE . '/plugins/tcpdf/tcpdf.php',
                    __DIR__ . '/../plugins/tcpdf/tcpdf.php',
                    BASE . '/tcpdf/tcpdf.php',
                    __DIR__ . '/../tcpdf/tcpdf.php',
                    (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/plugins/tcpdf/tcpdf.php' : null)
                );
                foreach ($tcpdfPaths as $p) { if ($p && file_exists($p)) { @require_once($p); break; } }
            }
            if (class_exists('TCPDF')) { $loadedPdfEngine = 'tcpdf'; }
        }

        if ($loadedPdfEngine === null) { $response['msg']='mPDF/TCPDF no disponible'; print(json_encode($response)); return; }

        // Preparar HTML: inline de CSS externo para que el PDF respete estilos
        $html2 = $html;
        $inlinedCss = '';
        // Helper para obtener contenido de CSS con resolución de rutas locales
        $fetchContent = function($href) {
            // 1) Href absoluto con esquema http/https
            if (preg_match('#^https?://#i', $href)) {
                $ctx = @file_get_contents($href);
                if ($ctx !== false) return $ctx;
                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $href);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    $out = curl_exec($ch);
                    curl_close($ch);
                    if ($out !== false) return $out;
                }
            } else {
                // 2) Ruta absoluta del sitio /... -> DOCUMENT_ROOT
                if (substr($href, 0, 1) === '/' && isset($_SERVER['DOCUMENT_ROOT'])) {
                    $fs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $href;
                    if (is_file($fs) && is_readable($fs)) {
                        $ctx = @file_get_contents($fs);
                        if ($ctx !== false) return $ctx;
                    }
                }
                // 3) Ruta relativa -> intentar BASE
                if (defined('BASE')) {
                    $fs2 = rtrim(BASE, '/\\') . '/' . ltrim($href, '/');
                    if (is_file($fs2) && is_readable($fs2)) {
                        $ctx = @file_get_contents($fs2);
                        if ($ctx !== false) return $ctx;
                    }
                }
            }
            return false;
        };
        if (preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+\.css)["\'][^>]*>/i', $html2, $m)) {
            $cssHrefs = $m[1];
            foreach ($cssHrefs as $href) {
                $cssContent = $fetchContent($href);
                if ($cssContent && is_string($cssContent)) { $inlinedCss .= "\n/* inlined: $href */\n" . $cssContent . "\n"; }
            }
            if ($inlinedCss !== '') {
                // Quitar los <link> y agregar <style> con los CSS recopilados
                $html2 = preg_replace('/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+\.css)["\'][^>]*>/i', '', $html2);
                if (stripos($html2, '</head>') !== false) {
                    $html2 = str_ireplace('</head>', "<style>" . $inlinedCss . "</style></head>", $html2);
                } else {
                    $html2 = "<style>" . $inlinedCss . "</style>" . $html2;
                }
            }
        }

        try {
            // Construir ruta absoluta en FS y URL pública
            $baseRoot = defined('BASE') ? rtrim(BASE, '/\\') : rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/..', '/\\');
            $upload_rel = 'uploads/contratos/';
            $upload_dir_fs = $baseRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload_rel);
            if (!file_exists($upload_dir_fs)) { @mkdir($upload_dir_fs, 0777, true); }
            $fileId = $contrato_id ? $contrato_id : time();
            $safeTipo = preg_replace('/[^a-z0-9_\-]/i','',str_replace(' ','_',substr($tipo,0,50)));
            $fileName = 'contrato_' . $fileId . '_' . $safeTipo . '.pdf';
            $fullPath = rtrim($upload_dir_fs, '/\\') . DIRECTORY_SEPARATOR . $fileName;

            if ($loadedPdfEngine === 'mpdf') {
                $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
                $mpdf->WriteHTML($html2);
                $mpdf->Output($fullPath, 'F');
            } else {
                // TCPDF flujo conforme al ejemplo del usuario
                if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION','P');
                if (!defined('PDF_UNIT')) define('PDF_UNIT','mm');
                if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT','A4');
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // Metadatos estándar
                if (defined('PDF_CREATOR')) { $pdf->SetCreator(PDF_CREATOR); } else { $pdf->SetCreator('Sistema'); }
                $pdf->SetAuthor('Sistema');
                $pdf->SetTitle('Contrato - ' . $tipo);
                $pdf->SetSubject('Contrato de Trabajo');
                $pdf->SetKeywords('contrato, trabajo');
                // Sin cabecera/rodapié y con márgenes consistentes
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                // Página e impresión de HTML
                $pdf->AddPage();
                if (method_exists($pdf, 'SetFont')) { $pdf->SetFont('dejavusans','',10); }
                if (method_exists($pdf, 'SetAutoPageBreak')) { $pdf->SetAutoPageBreak(true, 10); }
                if (method_exists($pdf, 'setImageScale')) { $pdf->setImageScale(1.25); }
                // Si no hay soporte para imágenes con alpha (GD/Imagick), sanitizar HTML de forma agresiva antes de writeHTML
                $hasImageAlphaSupport = (extension_loaded('gd') || class_exists('Imagick'));
                $html_for_tcpdf = $html2;
                if (!$hasImageAlphaSupport) {
                    // Eliminar cualquier etiqueta <img>
                    $html_for_tcpdf = preg_replace('/<img[^>]*>/i', '', $html_for_tcpdf);
                    // Eliminar cualquier url(...) en estilos inline o bloques <style>
                    $html_for_tcpdf = preg_replace('/url\(.*?\)/i', '/* img removed */', $html_for_tcpdf);
                    // Eliminar etiquetas <image ...> de SVG
                    $html_for_tcpdf = preg_replace('/<image[^>]*>/i', '', $html_for_tcpdf);
                    // Eliminar bloques SVG completos por si referencian rasterizados internos
                    $html_for_tcpdf = preg_replace('/<svg[\s\S]*?<\/svg>/i', '', $html_for_tcpdf);
                }
                // Escribir el HTML (o HTML saneado) con soporte a UTF-8, con reintento agresivo si falla por imágenes
                try {
                    $pdf->writeHTML($html_for_tcpdf, true, false, true, false, '');
                } catch (\Throwable $e) {
                    // Fallback agresivo: eliminar todas las imágenes y fondos sin discriminar extensión
                    $html_retry = $html_for_tcpdf;
                    // Quitar cualquier <img>
                    $html_retry = preg_replace('/<img[^>]*>/i', '', $html_retry);
                    // Quitar url(...) en estilos inline y en bloques style
                    $html_retry = preg_replace('/url\(.*?\)/i', '/* img removed */', $html_retry);
                    // Quitar <image ...> de SVG
                    $html_retry = preg_replace('/<image[^>]*>/i', '', $html_retry);
                    // Quitar bloques <svg> completos si aún quedan referencias a raster
                    if (stripos($html_retry, '<image') !== false) {
                        $html_retry = preg_replace('/<svg[\s\S]*?<\/svg>/i', '', $html_retry);
                    }
                    $pdf->writeHTML($html_retry, true, false, true, false, '');
                }
                // Guardar en disco
                $pdf->Output($fullPath, 'F');
            }

            // Construir URL pública (relativa al documento web)
            $fileUrl = rtrim('/', '/') . '/' . $upload_rel . $fileName;
            $response['status']=1; $response['file_url'] = $fileUrl; $response['msg']='PDF generado correctamente';
            if ($contrato_id) {
                try { $this->db->update('contratos', array('archivo_contrato'=>$fileUrl), array('id'=>$contrato_id)); } catch(Exception $e) { /* silencio */ }
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
                            t.id, t.nombre, t.apellidos, t.apellidos_segundos, t.direccion, t.carnet_identidad,
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
                            t.id, t.nombre, t.apellidos, t.apellidos_segundos, t.direccion, t.carnet_identidad,
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
                'municipio_nombre' => $trabajador['municipio_nombre'] ?? 'Sin municipio',
                'carnet_identidad' => $trabajador['carnet_identidad'] ?? ''
            );
        } else {
            $data['msg'] = 'Trabajador no encontrado';
        }
        return $data;
    }

    private function _save($param) {
        $data = array('status'=>1, 'msg_title'=>'Éxito', 'msg'=>'Contrato guardado correctamente');

        // Validaciones básicas - solo campos mínimos solicitados
        $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
        $tipo_contrato = isset($param['tipo_contrato']) ? trim($param['tipo_contrato']) : '';
        
        // La fecha de inicio se toma automáticamente como la fecha actual
        $fecha_inicio = date('Y-m-d');

        if ($trabajador_id <= 0) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El trabajador es obligatorio'; print(json_encode($data)); return; }
        if ($tipo_contrato === '') { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El tipo de contrato es obligatorio'; print(json_encode($data)); return; }

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

        // Solo guardar columnas mínimas: trabajador_id, tipo, fecha_inicio, archivo_contrato, firma_digital
        $insert = array(
            'trabajador_id'     => $trabajador_id,
            'tipo'              => $tipo_contrato, // mapeamos 'tipo_contrato' del form a columna 'tipo'
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
