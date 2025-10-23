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

    public function save_anterior() {
        $response = array('status' => 0, 'msg' => '', 'id' => 0);
        $connection = $this->db->conn;
        
        try {
            // Log inicial
            error_log("=== Inicio save_anterior ===");
            error_log("POST: " . print_r($_POST, true));
            if (isset($_FILES['archivo_contrato'])) {
                error_log("Archivo: " . print_r($_FILES['archivo_contrato'], true));
            }

            // Validar campos requeridos
            if (!isset($_POST['trabajador_id']) || empty($_POST['trabajador_id'])) {
                throw new Exception('El trabajador es requerido');
            }
            
            // Validar que trabajador_id sea numérico
            if (!is_numeric($_POST['trabajador_id'])) {
                throw new Exception('ID de trabajador inválido');
            }

            if (!isset($_POST['tipo_contrato']) || empty($_POST['tipo_contrato'])) {
                throw new Exception('El tipo de contrato es requerido');
            }

            // Validar que el tipo de contrato sea 1 (indeterminado) o 2 (determinado)
            if (!in_array($_POST['tipo_contrato'], ['1', '2'], true)) {
                throw new Exception('Tipo de contrato inválido. Debe ser 1 (indeterminado) o 2 (determinado)');
            }

            if (!isset($_POST['fecha_inicio']) || empty($_POST['fecha_inicio'])) {
                throw new Exception('La fecha de inicio es requerida');
            }
            
            // Validar que si es contrato determinado (tipo 2) tenga fecha fin
            if ($_POST['tipo_contrato'] === '2' && empty($_POST['fecha_fin'])) {
                throw new Exception('Para contratos determinados la fecha de fin es requerida');
            }

            // Verificar duplicado antes de insertar
            $sqlCheck = "SELECT id FROM contratos WHERE trabajador_id = :trabajador_id AND tipo = :tipo AND fecha_inicio = :fecha_inicio ";
            $paramsCheck = [
                ':trabajador_id' => $_POST['trabajador_id'],
                ':tipo' => $_POST['tipo_contrato'],
                ':fecha_inicio' => $_POST['fecha_inicio']
            ];
            if ($_POST['tipo_contrato'] === '2') {
                $sqlCheck .= " AND fecha_fin = :fecha_fin ";
                $paramsCheck[':fecha_fin'] = $_POST['fecha_fin'];
            } else {
                $sqlCheck .= " AND (fecha_fin IS NULL OR fecha_fin = '') ";
            }
            $stmtCheck = $this->db->conn->prepare($sqlCheck);
            $stmtCheck->execute($paramsCheck);
            if ($stmtCheck->fetch()) {
                throw new Exception('Ya existe un contrato igual para este trabajador, tipo y fecha.');
            }

            // Validar archivo
            if (!isset($_FILES['archivo_contrato'])) {
                throw new Exception('No se recibió ningún archivo');
            }

            $archivo = $_FILES['archivo_contrato'];

            if ($archivo['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = array(
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
                );
                $errorMsg = isset($errorMessages[$archivo['error']]) ? 
                           $errorMessages[$archivo['error']] : 
                           'Error desconocido al subir el archivo';
                throw new Exception($errorMsg);
            }

            if (!is_uploaded_file($archivo['tmp_name'])) {
                throw new Exception('El archivo no se subió correctamente');
            }

            $mimeType = mime_content_type($archivo['tmp_name']);
            if ($mimeType === false) {
                throw new Exception('No se pudo determinar el tipo de archivo');
            }

            $allowedTypes = array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            );

            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido. Solo se permiten archivos PDF y Word');
            }

            if ($archivo['size'] > 10 * 1024 * 1024) {
                throw new Exception('El archivo no debe superar los 10MB');
            }

            // Iniciar transacción
            $this->db->conn->beginTransaction();

            // Preparar la consulta de inserción
            $sql = "INSERT INTO contratos (trabajador_id, tipo, fecha_inicio, fecha_fin, firma_digital) 
                   VALUES (:trabajador_id, :tipo, :fecha_inicio, :fecha_fin, :firma_digital)";
            
            $params = array(
                ':trabajador_id' => $_POST['trabajador_id'],
                ':tipo' => $_POST['tipo_contrato'],
                ':fecha_inicio' => $_POST['fecha_inicio'],
                ':fecha_fin' => $_POST['tipo_contrato'] === '2' ? $_POST['fecha_fin'] : null,
                ':firma_digital' => 0
            );
            
            error_log("SQL a ejecutar: " . $sql);
            error_log("Parámetros: " . print_r($params, true));
            
            $stmt = $this->db->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . implode(', ', $this->db->conn->errorInfo()));
            }
            
            // Ejecutar la consulta una sola vez
            if (!$stmt->execute($params)) {
                throw new Exception('Error al ejecutar la consulta: ' . implode(', ', $stmt->errorInfo()));
            }
            // Obtener el ID del contrato insertado
            $contratoId = $this->db->conn->lastInsertId();
            if (!$contratoId) {
                throw new Exception('Error al obtener el ID del contrato insertado');
            }

            // Definir extensión según mime type
            $extension = $mimeType === 'application/pdf' ? 'pdf' : 
                       ($mimeType === 'application/msword' ? 'doc' : 'docx');
            
            // Crear directorio si no existe
            $uploadDir = __DIR__ . '/../uploads/contratos';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Mover archivo
            $nombreArchivo = $contratoId . '.' . $extension;
            $rutaArchivo = $uploadDir . '/' . $nombreArchivo;
            $rutaRelativa = 'uploads/contratos/' . $nombreArchivo;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                throw new Exception('Error al guardar el archivo');
            }

            // Actualizar ruta del archivo en la base de datos
            $sqlUpdate = "UPDATE contratos SET archivo_contrato = :ruta WHERE id = :id";
            $updateParams = array(
                ':ruta' => $rutaRelativa,
                ':id' => $contratoId
            );
            
            error_log("SQL Update: " . $sqlUpdate);
            error_log("Parámetros Update: " . print_r($updateParams, true));

            $stmtUpdate = $this->db->conn->prepare($sqlUpdate);
            if (!$stmtUpdate) {
                throw new Exception('Error al preparar la consulta de actualización');
            }

            if (!$stmtUpdate->execute($updateParams)) {
                throw new Exception('Error al actualizar la ruta del archivo: ' . implode(', ', $stmtUpdate->errorInfo()));
            }

            // Confirmar transacción
            $this->db->conn->commit();

            $response['status'] = 1;
            $response['msg'] = 'Contrato guardado correctamente';
            $response['id'] = $contratoId;

        } catch (Exception $e) {
            // Revertir transacción si hay error
            if ($this->db->conn && $this->db->conn->inTransaction()) {
                $this->db->conn->rollBack();
            }
            
            error_log("Error en save_anterior: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $response['status'] = 0;
            $response['msg'] = $e->getMessage();
        }

        return $response;
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
            $var26 = '___';
            $var99 = '___';
            if ($modalidad_trabajo_texto !== '') {
                if (stripos($modalidad_trabajo_texto, 'presencial') !== false) { $var24 = '_X_'; }
                if (stripos($modalidad_trabajo_texto, 'distancia') !== false || stripos($modalidad_trabajo_texto, 'remoto') !== false) { $var99 = '_X_'; }
                if (stripos($modalidad_trabajo_texto, 'teletrabajo') !== false || stripos($modalidad_trabajo_texto, 'tele-trabajo') !== false) { $var26 = '_X_'; }
            }
            $var31 = '_____________'; // mensual
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

    // Igual que _render_contrato_unico pero devuelve HTML en lugar de imprimir JSON
    private function _render_contrato_unico_html($param) {
        $html = '';
        try {
            $tplPath = __DIR__ . '/../docs/contrato-unico/ContratoDeTrabajo.php';
            if (!file_exists($tplPath)) { $tplPath = realpath(__DIR__ . '/../docs/contrato-unico/ContratoDeTrabajo.php'); }
            if (!file_exists($tplPath)) { return ''; }

            // Reutilizar el mismo mapeo de variables
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
            $hora_hasta_h = isset($param['hora_hasta_h']) ? $param['hora_hasta_h'] : '';

            $var1 = trim($nombre . ' ' . $apellidos . ' ' . $apellidos2);
            $var2 = isset($param['trabajador_ci']) ? $param['trabajador_ci'] : '';
            $var3 = $direccion;
            $var4 = '';
            $var5 = $municipio_nombre;
            $var6 = $provincia_nombre;
            if (strcasecmp($tipo_contrato_texto, 'Tiempo Determinado') === 0) { $var23 = '_X_'; $var7  = '___'; }
            elseif (strcasecmp($tipo_contrato_texto, 'Tiempo Indeterminado') === 0) { $var23 = '___'; $var7  = '_X_'; }
            else { $var23 = '___'; $var7  = '___'; }
            $var24 = '___'; $var25 = '___'; $var26 = '___';
            if ($modalidad_trabajo_texto !== '') {
                if (stripos($modalidad_trabajo_texto, 'presencial') !== false) { $var24 = '_X_'; }
                if (stripos($modalidad_trabajo_texto, 'distancia') !== false || stripos($modalidad_trabajo_texto, 'remoto') !== false) { $var25 = '_X_'; }
                if (stripos($modalidad_trabajo_texto, 'teletrabajo') !== false || stripos($modalidad_trabajo_texto, 'tele-trabajo') !== false) { $var26 = '_X_'; }
            }
            $var27 = '(_)'; $var28 = '(_)'; $var29 = '(_)';
            if (!empty($param['frecuencia_trabajo'])) {
                $freq = strtolower(trim($param['frecuencia_trabajo']));
                if ($freq === 'semanal') { $var27 = '(X)'; }
                else if ($freq === 'quincenal') { $var28 = '(X)'; }
                else if ($freq === 'mensual') { $var29 = '(X)'; }
            }
            $var8 = $modalidad_trabajo_texto;
            $var9 = $modalidad_trabajo_texto;
            $var10 = isset($param['cargo_nombre']) ? $param['cargo_nombre'] : '';
            $var11 = $ubicacion_laboral_texto;
            $var12 = isset($param['regimen_trabajo_desde']) ? $param['regimen_trabajo_desde'] : '';
            $var13 = isset($param['regimen_trabajo_hasta']) ? $param['regimen_trabajo_hasta'] : '';
            $var14 = isset($param['hora_rango_label']) ? $param['hora_rango_label'] : 'De';
            $var15 = isset($param['hora_desde_h']) ? $param['hora_desde_h'] : '';
            $var16 = isset($param['hora_hasta_h']) ? $param['hora_hasta_h'] : '';
            $var17 = isset($param['hora_hasta_m']) ? $param['hora_hasta_m'] : '';
            if (isset($param['descanso_dias']) && $param['descanso_dias'] !== '') { $var18 = $param['descanso_dias']; }
            else { $var18 = ''; if (!empty($regimen_descanso)) { if (preg_match('/\d+/', $regimen_descanso, $m)) { $var18 = $m[0]; } } }
            $var19 = $salario_base;
            $var20 = isset($param['extra1']) ? $param['extra1'] : '';
            $var21 = isset($param['extra2']) ? $param['extra2'] : '';
            $var22 = isset($param['extra3']) ? $param['extra3'] : '';
            $var25 = date('Y');

            ob_start();
            include $tplPath;
            $html = ob_get_clean();
        } catch (Exception $e) { $html = ''; }
        return $html;
    }

    // Genera PDF únicamente con contrato_id: carga datos desde BD y usa la misma plantilla de vista previa
    private function _generatePdfById($param) {
        $response = array('status'=>0,'msg'=>'','file_url'=>'');
        $cid = isset($param['contrato_id']) ? intval($param['contrato_id']) : 0;
        if ($cid <= 0) { $response['msg']='contrato_id inválido'; print(json_encode($response)); return; }
        try {
            // Intento 1: con columna departamento_id en contratos
            try {
                $row = $this->db->fetchRow(
                    "SELECT c.*, t.nombre, t.apellidos, t.apellidos_segundos, t.direccion, t.carnet_identidad,
                            t.provincia_id, t.municipio_id, t.cargos_id,
                            COALESCE(cg.nombre,'') AS cargo_nombre,
                            COALESCE(p.nombre,'') AS provincia_nombre,
                            COALESCE(m.nombre,'') AS municipio_nombre,
                            COALESCE(d.nombre,'') AS departamento_nombre
                     FROM contratos c
                     LEFT JOIN trabajadores t ON t.id=c.trabajador_id
                     LEFT JOIN cargos cg ON cg.id=t.cargos_id
                     LEFT JOIN provincia p ON p.id=t.provincia_id
                     LEFT JOIN municipio m ON m.id=t.municipio_id
                     LEFT JOIN departamentos d ON d.id=c.departamento_id
                     WHERE c.id = :id",
                    array('id'=>$cid)
                );
            } catch (Exception $eJoin) {
                // Intento 2: sin columna departamento_id
                $row = $this->db->fetchRow(
                    "SELECT c.*, t.nombre, t.apellidos, t.apellidos_segundos, t.direccion, t.carnet_identidad,
                            t.provincia_id, t.municipio_id, t.cargos_id,
                            COALESCE(cg.nombre,'') AS cargo_nombre,
                            COALESCE(p.nombre,'') AS provincia_nombre,
                            COALESCE(m.nombre,'') AS municipio_nombre
                     FROM contratos c
                     LEFT JOIN trabajadores t ON t.id=c.trabajador_id
                     LEFT JOIN cargos cg ON cg.id=t.cargos_id
                     LEFT JOIN provincia p ON p.id=t.provincia_id
                     LEFT JOIN municipio m ON m.id=t.municipio_id
                     WHERE c.id = :id",
                    array('id'=>$cid)
                );
                // agregar alias vacío para compatibilidad
                if ($row) { $row['departamento_nombre'] = ''; }
            }
            if (!$row) { $response['msg']='Contrato no encontrado'; print(json_encode($response)); return; }

            // Mapear a los mismos parámetros de plantilla usados en la vista previa
            $tipoTexto = ($row['tipo'] == '1' ? 'Tiempo Determinado' : ($row['tipo'] == '2' ? 'Tiempo Indeterminado' : ''));
            $mt = isset($row['modalidad_trabajo']) ? (string)$row['modalidad_trabajo'] : '';
            $payload = array(
                'trabajador_nombre' => $row['nombre'] ?? '',
                'trabajador_apellidos' => $row['apellidos'] ?? '',
                'trabajador_apellidos_segundos' => $row['apellidos_segundos'] ?? '',
                'trabajador_direccion' => $row['direccion'] ?? '',
                'trabajador_municipio' => $row['municipio_nombre'] ?? '',
                'trabajador_provincia' => $row['provincia_nombre'] ?? '',
                'trabajador_ci' => $row['carnet_identidad'] ?? '',
                'cargo_nombre' => $row['cargo_nombre'] ?? '',
                'tipo_contrato_texto' => $tipoTexto,
                'modalidad_trabajo_texto' => ($mt==='1' ? 'Presencial' : ($mt==='2' ? 'A distancia' : ($mt==='3' ? 'Teletrabajo' : ''))),
                'ubicacion_laboral_texto' => $row['departamento_nombre'] ?? '',
                'regimen_descanso' => $row['regimen_descanso'] ?? '',
                'salario_base' => $row['salario_base'] ?? '',
                'regimen_trabajo_desde' => $row['regimen_trabajo_desde'] ?? '',
                'regimen_trabajo_hasta' => $row['regimen_trabajo_hasta'] ?? '',
                'hora_desde_h' => $row['hora_desde_h'] ?? '',
                'hora_hasta_h' => $row['hora_hasta_h'] ?? '',
                'frecuencia_trabajo' => $row['frecuencia_trabajo'] ?? ''
            );

            // Render a HTML con la misma plantilla
            $html = $this->_render_contrato_unico_html($payload);
            if (!$html) { $response['msg']='No se pudo renderizar la plantilla'; print(json_encode($response)); return; }

            // Generar PDF con el núcleo reutilizable
            $resp = $this->_generatePdfCore(array('html'=>$html, 'tipo'=>'contrato', 'contrato_id'=>$cid));
            print(json_encode($resp));
        } catch (Exception $e) {
            $response['msg']='Error: '.$e->getMessage();
            print(json_encode($response));
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
        try {
            if (!isset($param['method'])) {
                throw new Exception('Método no especificado');
            }

            // Asegurar que siempre enviamos JSON
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }

            $response = null;

            switch ($param['method']) {
                case 'save-anterior':
                    $response = $this->save_anterior();
                    break;
                case 'list':
                    $response = $this->_list($param);
                    break;
                case 'list-id':
                    $response = $this->_list_id($param);
                    break;
                case 'list-trabajadores':
                    $response = $this->_list_trabajadores();
                    break;
                case 'list-departamentos':
                    $response = $this->_list_departamentos();
                    break;
                case 'get-trabajador-data':
                    $response = $this->_get_trabajador_data($param);
                    break;
                case 'getTemplate':
                    $response = $this->_getTemplate($param);
                    break;
                case 'render-contrato-unico':
                    $response = $this->_render_contrato_unico($param);
                    break;
                case 'save':
                    $response = $this->_save($param);
                    break;
                case 'generatePdfFromHtml':
                    $response = $this->_generatePdfFromHtml($param);
                    break;
                case 'generatePdfById':
                    $response = $this->_generatePdfById($param);
                    break;
                case 'savePdfContrato':
                    $response = $this->_savePdfContrato($param);
                    break;
                case 'generatePdfWithFpdf':
                    $response = $this->_generatePdfWithFpdf($param);
                    break;
                default:
                    throw new Exception('Método no válido: ' . $param['method']);
            }

            if ($response === null) {
                throw new Exception('No se obtuvo respuesta del método');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            error_log("Error en Contratos->api: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            echo json_encode([
                'status' => 0,
                'msg' => $e->getMessage(),
                'error' => true
            ]);
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

    // Núcleo de generación de PDF: retorna array (no imprime)
    private function _generatePdfCore($param) {
        $response = array('status'=>0,'msg'=>'', 'file_url'=>'');
        $html = isset($param['html']) ? $param['html'] : '';
        $tipo = isset($param['tipo']) ? $param['tipo'] : 'contrato';
        $contrato_id = isset($param['contrato_id']) && $param['contrato_id'] !== '' ? intval($param['contrato_id']) : null;

        if (empty($html)) { $response['msg'] = 'HTML no proporcionado'; return $response; }

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
            $tcpdfIncludes = array(
                BASE . '/plugins/tcpdf/tcpdf_include.php',
                __DIR__ . '/../plugins/tcpdf/tcpdf_include.php',
                BASE . '/tcpdf/tcpdf_include.php',
                __DIR__ . '/../tcpdf/tcpdf_include.php',
                (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/plugins/tcpdf/tcpdf_include.php' : null)
            );
            $loaded = false;
            foreach ($tcpdfIncludes as $inc) { if ($inc && file_exists($inc)) { @require_once($inc); $loaded = true; break; } }
            if (!$loaded) {
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

        if ($loadedPdfEngine === null) { $response['msg']='mPDF/TCPDF no disponible'; return $response; }

        // Preparar HTML y CSS embebido
        $html2 = $html;
        $inlinedCss = '';
        $fetchContent = function($href) {
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
                if (substr($href, 0, 1) === '/' && isset($_SERVER['DOCUMENT_ROOT'])) {
                    $fs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $href;
                    if (is_file($fs) && is_readable($fs)) { $ctx = @file_get_contents($fs); if ($ctx !== false) return $ctx; }
                }
                if (defined('BASE')) {
                    $fs2 = rtrim(BASE, '/\\') . '/' . ltrim($href, '/');
                    if (is_file($fs2) && is_readable($fs2)) { $ctx = @file_get_contents($fs2); if ($ctx !== false) return $ctx; }
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
                $html2 = preg_replace('/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+\.css)["\'][^>]*>/i', '', $html2);
                if (stripos($html2, '</head>') !== false) { $html2 = str_ireplace('</head>', "<style>" . $inlinedCss . "</style></head>", $html2); }
                else { $html2 = "<style>" . $inlinedCss . "</style>" . $html2; }
            }
        }

        // Inyectar <base href> para que imágenes (logo) y rutas relativas funcionen igual que en la vista previa
        $scheme = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $baseHref = '';
        if ($host) { $baseHref = $scheme . '://' . $host . '/'; }
        if ($baseHref !== '') {
            if (stripos($html2, '<base ') === false) {
                if (stripos($html2, '<head') !== false && stripos($html2, '</head>') !== false) {
                    $html2 = preg_replace('/<head[^>]*>/i', '$0<base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '">', $html2, 1);
                } else {
                    // Si no hay head explícito, agregarlo para que el base tenga efecto
                    $html2 = '<head><base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '"></head>' . $html2;
                }
            }
        }

        try {
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
                if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION','P');
                if (!defined('PDF_UNIT')) define('PDF_UNIT','mm');
                if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT','A4');
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                if (defined('PDF_CREATOR')) { $pdf->SetCreator(PDF_CREATOR); } else { $pdf->SetCreator('Sistema'); }
                $pdf->SetAuthor('Sistema');
                $pdf->SetTitle('Contrato - ' . $tipo);
                $pdf->SetSubject('Contrato de Trabajo');
                $pdf->SetKeywords('contrato, trabajo');
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();
                if (method_exists($pdf, 'SetFont')) { $pdf->SetFont('dejavusans','',10); }
                if (method_exists($pdf, 'SetAutoPageBreak')) { $pdf->SetAutoPageBreak(true, 10); }
                if (method_exists($pdf, 'setImageScale')) { $pdf->setImageScale(1.25); }
                // Si no hay soporte de GD/Imagick, evitar PNG con alpha: usar JPG local de fallback para el logo si existe
                if (!extension_loaded('gd') && !class_exists('Imagick')) {
                    $fallbackRel = '/img/logo-pdf.jpg'; // coloca aquí un JPG sin transparencia
                    $fsCheck = null;
                    if (isset($_SERVER['DOCUMENT_ROOT'])) {
                        $fsCheck = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $fallbackRel;
                    } elseif (defined('BASE')) {
                        $fsCheck = rtrim(BASE, '/\\') . $fallbackRel;
                    }
                    if ($fsCheck && is_file($fsCheck)) {
                        // Reemplazar src que apunten a logo*.png por el JPG local para evitar alpha channel
                        $html2 = preg_replace('#(<img[^>]+src=["\"])((?:[^"\"]*/)?[^"\"]*logo[^"\"]*\.png)(["\"][^>]*>)#i', '$1' . $fallbackRel . '$3', $html2);
                    }
                }
                // Intentar respetar imágenes y estilos tal cual (solo eliminar imágenes si falla el render)
                $html_for_tcpdf = $html2;
                try { $pdf->writeHTML($html_for_tcpdf, true, false, true, false, ''); }
                catch (\Throwable $e) {
                    $html_retry = preg_replace('/<img[^>]*>/i', '', $html_for_tcpdf);
                    $html_retry = preg_replace('/url\(.*?\)/i', '/* img removed */', $html_retry);
                    $html_retry = preg_replace('/<image[^>]*>/i', '', $html_retry);
                    if (stripos($html_retry, '<image') !== false) { $html_retry = preg_replace('/<svg[\s\S]*?<\/svg>/i', '', $html_retry); }
                    $pdf->writeHTML($html_retry, true, false, true, false, '');
                }
                $pdf->Output($fullPath, 'F');
            }

            $fileUrl = rtrim('/', '/') . '/' . $upload_rel . $fileName;
            $response['status']=1; $response['file_url'] = $fileUrl; $response['msg']='PDF generado correctamente';
            if ($contrato_id) { try { $this->db->update('contratos', array('archivo_contrato'=>$fileUrl), array('id'=>$contrato_id)); } catch(Exception $e) { } }
        } catch (Exception $e) {
            $response['msg'] = 'Error al generar PDF: ' . $e->getMessage();
        }
        return $response;
    }

    // Genera PDF a partir del HTML enviado (ya con valores reemplazados) y imprime JSON (API existente)
    private function _generatePdfFromHtml($param) {
        $resp = $this->_generatePdfCore($param);
        print(json_encode($resp));
    }

    // Nuevo método API: misma estructura de respuesta que _save() de List_recursos
    private function _savePdfContrato($param) {
        // Log opcional similar a List_recursos::_save
        $log = date('Y-m-d H:i:s') . " - savePdfContrato params:\n";
        $log .= "POST: " . print_r($param, true) . "\n";
        file_put_contents('debug_contratos.log', $log, FILE_APPEND);

        $data = array(
            'status' => 1,
            'msg_title' => 'Operación exitosa',
            'msg' => 'PDF generado correctamente',
            'date' => date('Y-m-d H:i:s'),
        );

        // Validación mínima
        if (!isset($param['html']) || trim((string)$param['html']) === '') {
            $data['status'] = 0; $data['msg_title'] = 'Validación'; $data['msg'] = 'HTML no proporcionado'; print(json_encode($data)); return;
        }

        // Generar PDF usando el núcleo reutilizable
        $resp = $this->_generatePdfCore($param);
        if (!$resp['status']) {
            $data['status'] = 0; $data['msg_title'] = 'Error'; $data['msg'] = $resp['msg'];
        } else {
            $data['file_url'] = $resp['file_url'];
        }

        // Si viene contrato_id, incluirlo en la respuesta
        if (isset($param['contrato_id']) && $param['contrato_id'] !== '') { $data['id'] = intval($param['contrato_id']); }

        print(json_encode($data));
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

        try {
            // Iniciar transacción
            if ($this->db->conn) {
                $this->db->conn->beginTransaction();
            } else {
                throw new Exception('No hay conexión a la base de datos disponible');
            }

            // Validaciones básicas - solo campos mínimos solicitados
            $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
            $tipo_contrato = isset($param['tipo_contrato']) ? trim($param['tipo_contrato']) : '';
            $fecha_inicio = date('Y-m-d');

            // Validaciones
            if ($trabajador_id <= 0) {
                throw new Exception('El trabajador es obligatorio');
            }
            if (empty($tipo_contrato)) {
                throw new Exception('El tipo de contrato es obligatorio');
            }

            // Verificar duplicado antes de insertar
            $sqlCheck = "SELECT id FROM contratos WHERE trabajador_id = :trabajador_id AND tipo = :tipo AND fecha_inicio = :fecha_inicio ";
            $paramsCheck = [
                ':trabajador_id' => $trabajador_id,
                ':tipo' => $tipo_contrato,
                ':fecha_inicio' => $fecha_inicio
            ];
            if ($tipo_contrato === '2' && isset($param['fecha_fin'])) {
                $sqlCheck .= " AND fecha_fin = :fecha_fin ";
                $paramsCheck[':fecha_fin'] = $param['fecha_fin'];
            } else {
                $sqlCheck .= " AND (fecha_fin IS NULL OR fecha_fin = '') ";
            }
            $stmtCheck = $this->db->conn->prepare($sqlCheck);
            $stmtCheck->execute($paramsCheck);
            if ($stmtCheck->fetch()) {
                throw new Exception('Ya existe un contrato igual para este trabajador, tipo y fecha.');
            }

        // Las validaciones ya se hicieron arriba

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

        // Solo guardar columnas mínimas: trabajador_id, tipo, fecha_inicio, archivo_contrato, firma_digital (flag int)

            $insert = array(
                'trabajador_id'     => $trabajador_id,
                'tipo'              => $tipo_contrato, // mapeamos 'tipo_contrato' del form a columna 'tipo'
                'fecha_inicio'      => $fecha_inicio,
                'archivo_contrato'  => $archivo_contrato_path,
                // columna firma_digital es entera: 1 si se subió archivo de firma en esta operación, 0 en caso contrario
                'firma_digital'     => ($firma_digital_path ? 1 : 0)
            );
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
                } else { 
                    throw new Exception('Error al insertar el contrato');
                }
            } else {
                $id = intval($param['id']);
                // No sobrescribir archivos si no subieron nuevos
                if (!$archivo_contrato_path) unset($insert['archivo_contrato']);
                if (!$firma_digital_path) unset($insert['firma_digital']);
                $where = array('id' => $id);
                $result = $this->db->update('contratos', $insert, $where);
                if ($result === false) { 
                    throw new Exception('Error al actualizar el contrato');
                }
                
                $data['id'] = $id;
                // Regenerar PDF con datos actualizados (usar ruta de firma si se subió en esta operación)
                $pdfPath = $this->generar_pdf_contrato($upload_dir, $id, $trabajador_id, $tipo_contrato, $fecha_inicio, null, $firma_digital_path);
                if ($pdfPath) {
                    if (!$this->db->update('contratos', array('archivo_contrato' => $pdfPath), array('id' => $id))) {
                        throw new Exception('Error al actualizar la ruta del archivo');
                    }
                    $data['file_url'] = $pdfPath;
                }
            }

            // Si llegamos aquí sin errores, confirmar la transacción
            if ($this->db->conn && $this->db->conn->inTransaction()) {
                $this->db->conn->commit();
            }

        } catch (Exception $e) {
            // Revertir transacción si hay error
            if ($this->db->conn && $this->db->conn->inTransaction()) {
                $this->db->conn->rollBack();
            }
            $data['status'] = 0;
            $data['msg_title'] = 'Error';
            $data['msg'] = 'Error al guardar: ' . $e->getMessage();
            error_log("Error en _save: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return $data;
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
        $css = 'body { font-family: DejaVu Sans, sans-serif; font-size: 12px; } .title { text-align:center; font-weight:bold; font-size:18px; margin-bottom:40px; } .sec h3 { margin: 10px 0 5px; } .row { margin: 6px 0; } .label { color:#666; width: 180px; display:inline-block; }';
        $html = '<html><head><style>' . $css . '</style></head><body>'
              . '<div class="title">Contrato #' . htmlspecialchars((string)$id) . '</div>'
              . '<div class="sec">'
              . '<div class="row"><span class="label">Trabajador:</span> ' . htmlspecialchars($nombreCompleto ?: ('ID ' . $trabajador_id)) . '</div>'
              . '<div class="row"><span class="label">Tipo de Contrato:</span> ' . htmlspecialchars($tipo) . '</div>'
              . '<div class="row"><span class="label">Fecha Inicio:</span> ' . htmlspecialchars($fecha_inicio ?: '') . '</div>'
              . '<div class="row"><span class="label">Fecha Fin:</span> ' . htmlspecialchars($fecha_fin ?: 'Indefinido') . '</div>'
              . '</div>';
        if (!empty($firma_digital_path)) {
            // Subir el doble la sección de firma (título e imagen) y desplazar un poco más a la derecha
            $html .= '<div class="sec" style="position:relative; margin-top:-120px;">
                        <h3 style="margin:0 0 4px 0;">Firma Trabajador</h3>
                        <div class="row" style="text-align:center;">
                            <img src="' . htmlspecialchars($firma_digital_path) . '" style="max-width:120px; height:auto; margin-left:100px; display:inline-block;">
                        </div>
                      </div>';
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
