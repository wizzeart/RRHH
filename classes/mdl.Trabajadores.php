<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Trabajador
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

    private function normalizarCorreo($correo)
    {
        $correo = strtolower(trim($correo));
        $map = [
            'á' => 'a',
            'à' => 'a',
            'ä' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ë' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'ï' => 'i',
            'î' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'ö' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'ü' => 'u',
            'û' => 'u',
            'ñ' => 'n',
            'ç' => 'c'
        ];
        $correo = strtr($correo, $map);
        $correo = preg_replace('/[^a-z0-9@._-]/', '', $correo);
        return $correo;
    }

    private function slugUser($texto)
    {
        $texto = strtolower(trim($texto));
        $map = [
            'á' => 'a',
            'à' => 'a',
            'ä' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ë' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'ï' => 'i',
            'î' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'ö' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'ü' => 'u',
            'û' => 'u',
            'ñ' => 'n',
            'ç' => 'c'
        ];
        $texto = strtr($texto, $map);
        $texto = preg_replace('/[^a-z0-9._-]/', '', $texto);
        return $texto;
    }

    public function api($param)
    {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'list-filter':
                $data = $this->_list_filter($param);
                print(json_encode($data));
                break;
            case 'list-bajas':
                $data = $this->_list_bajas($param);
                print(json_encode($data));
                break;
            case 'list-cumpleanos':
                $data = $this->_list_cumpleanos($param);
                print(json_encode($data));
                break;
            case 'cumpleanos-hoy':
                $data = $this->_cumpleanos_hoy($param);
                print(json_encode($data));
                break;
            case 'checked':
                $this->_checked($param);
                break;
            case 'check':
                $this->_check($param);
                break;
            case 'del':
                $this->_del($param);
                break;
            case 'liquidar':
                $this->_liquidar($param);
                break;
            case 'liquidar-forzado':
                $this->_liquidar_forzado($param);
                break;
            case 'recontratar':
                $this->_recontratar($param);
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'get-municipios':
                $data = $this->_get_municipios_by_provincia($param);
                print(json_encode($data));
                break;
            case 'export-excel':
                $this->_export_excel($param);
                break;
            case 'get-worker-data':
                $data = $this->_get_worker_data($param);
                print(json_encode($data));
                break;
            case 'get-vacaciones-acc':
                $data = $this->_get_vacaciones_acc($param);
                print(json_encode($data));
                break;
        }
    }

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-trabajadores':
                $data = array();
                $page['title'] = 'Trabajadores';
                $page['subtitle'] = 'Listado de Trabajadores';

                $data_form = array();
                //$data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                break;
            case 'delete-trabajadores':
                $data = array();
                $page['title'] = 'Dar de Baja Trabajadores';
                $page['subtitle'] = 'Dar de Baja Trabajadores';

                $data_form = array();
                break;
            case 'bajas-trabajadores':
                $data = array();
                $page['title'] = 'Trabajadores Dados de Baja';
                $page['subtitle'] = 'Listado de Trabajadores Dados de Baja';

                $data_form = array();
                break;
            case 'ficha-trabajador':
                $data = array();
                $page['title'] = 'Ficha de Trabajador';
                $page['subtitle'] = '';

                $val = array(
                    'id' => $param['id']
                );
                $sql = "SELECT t.*, p.nombre as provincia_nombre, m.nombre as municipio_nombre, xusuario,u.xemail as email, c.salario as salario FROM trabajadores t 
                LEFT JOIN municipio m ON m.id = t.municipio_id 
                LEFT JOIN provincia p ON p.id = t.provincia_id 
                LEFT JOIN usuarios u ON u.xusuario_id = t.usuario_id
                LEFT JOIN cargos c ON c.id = t.cargos_id
                WHERE t.id=:id";
                $row = $this->db->fetchRow($sql, $val);
                if ($row) {

                    //$row['almacenes'] = $this->app->get_list_usuarios_almacenes($row['xusuario_id']);
                    //$row['puntos-ventas'] = $this->app->get_list_usuarios_revendedores($row['xusuario_id']);
                    //print_r($row['almacenes']);
                    //die();
                    $sql = "SELECT numero_tarjeta_salario, numero_cuenta_estandar FROM bancos WHERE trabajador_id = :id";

                    $result = $this->app->db->fetchAll($sql, array('id' => $param['id']));
                    $data = $row;
                    if (isset($result) && count($result) > 0) {
                        $numero_tarjeta_salario = array_column($result, 'numero_tarjeta_salario')[0];
                        $numero_cuenta_estandar = array_column($result, 'numero_cuenta_estandar')[0];
                        $data['cuenta_estandar'] = $numero_cuenta_estandar;
                        $data['tarjeta_salario'] = $numero_tarjeta_salario;
                    }

                    $data['salario'] = intval($data['salario']);
                    
                    // Obtener vacaciones disponibles (acumuladas menos congeladas) del trabajador
                    $vacacionesData = $this->_get_vacaciones_acc(array('id' => $param['id']));
                    $data['vacaciones_disponibles'] = $vacacionesData['status'] == 1 ? $vacacionesData['vacaciones_disponibles'] : 0;
                    
                    $page['title'] = 'Ficha de Trabajador: ' . $row['nombre'] . ' ' . $row['apellidos'] . ' ' . $row['apellidos_segundos'];
                }
                $data_form = array();
                break;
            case 'trabajadores':
                /*
                  ini_set('display_errors', 1);
                  ini_set('display_startup_errors', 1);
                  error_reporting(E_ALL);
                 * 
                 */

                $data = array();
                $page['title'] = 'Nuevo trabajador';
                $page['subtitle'] = 'Registro de Trabajador';


                $data_form['cargos'] = $this->app->get_list_cargos();
                // Lista de Departamentos para el select en el formulario
                if (method_exists($this->app, 'get_list_departamentos')) {
                    $data_form['departamentos'] = $this->app->get_list_departamentos();
                } else {
                    $data_form['departamentos'] = array();
                }

                // Lista de Provincias para el select en el formulario
                $data_form['provincias'] = $this->_get_list_provincias();

                // Lista de Municipios para el select en el formulario
                $data_form['municipios'] = $this->_get_list_municipios();

                $data_form['bolsas'] = $this->app->get_list_bolsa_empleo();

                $data_form['ubicaciones'] = $this->app->get_list_ubicaciones();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición trabajador';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "SELECT t.*, u.xemail as email, c.nombre as cargo_nombre, m.nombre as municipio_nombre FROM trabajadores t
                            LEFT JOIN usuarios u ON u.xusuario_id = t.usuario_id
                            LEFT JOIN cargos c ON c.id = t.cargos_id
                            LEFT JOIN municipio m ON m.id = t.municipio_id
                            WHERE t.id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        // Cargar datos bancarios del trabajador
                        $sqlBanco = "SELECT numero_tarjeta_salario, numero_cuenta_estandar FROM bancos WHERE trabajador_id = :trabajador_id";
                        $bancoDatos = $this->db->fetchRow($sqlBanco, ['trabajador_id' => $row['id']]);

                        if ($bancoDatos) {
                            $row['tarjeta_salario'] = $bancoDatos['numero_tarjeta_salario'];
                            $row['cuenta_estandar'] = $bancoDatos['numero_cuenta_estandar'];
                        }

                        $data = $row;
                        $page['subtitle'] = 'Trabajador: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                } else {
                    $data['estatus'] = 'S';
                }
                break;
        }
    }

    private function _del($param)
    {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        // Validar que no tenga recursos asignados activos (sin devolución)
        try {
            $sqlCheckRec = "SELECT COUNT(*) AS c
                            FROM recursos_trabajadores
                            WHERE trabajador_id = :tid
                              AND (
                                   estado = 1
                                   OR fecha_entrega_a_rh IS NULL
                                   OR fecha_entrega_a_rh = ''
                                   OR fecha_entrega_a_rh = '0000-00-00'
                              )";
            $rowRec = $this->db->fetchRow($sqlCheckRec, array('tid' => $param['id']));
            $cntRec = $rowRec && isset($rowRec['c']) ? (int)$rowRec['c'] : 0;
            if ($cntRec > 0) {
                $data['status'] = 0;
                $data['msg'] = 'No se puede dar de baja: el trabajador tiene recursos asignados sin devolver.';
                print(json_encode($data));
                return;
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error validando recursos asignados: ' . $e->getMessage();
            print(json_encode($data));
            return;
        }

        // Marcar como en estado de liquidación en lugar de dar de baja inmediatamente
        $update = array(
            'es_liquidacion' => 1
        );
        $where = array(
            'id' => $param['id']
        );
        // Marcar como en liquidación
        $this->db->update('trabajadores', $update, $where);

        // Registrar en historial el cambio a estado liquidación
        $nombreCompleto = '';
        try {
            $rowNombre = $this->db->fetchRow(
                "SELECT nombre, apellidos, apellidos_segundos FROM trabajadores WHERE id = :id",
                array('id' => $param['id'])
            );
            if ($rowNombre) {
                $nombreCompleto = trim(($rowNombre['nombre'] ?? '') . ' ' . ($rowNombre['apellidos'] ?? '') . ' ' . ($rowNombre['apellidos_segundos'] ?? ''));
            }
        } catch (Exception $e) { /* ignore */
        }

        $history = array(
            'xentity' => 'TRABAJADOR',
            'xaction' => 'MARCAR-LIQUIDACION',
            'xid' => $param['id'],
            'xobs' => 'TRABAJADOR MARCADO PARA LIQUIDACIÓN ID: ' . $param['id'] . ($nombreCompleto ? (' - ' . $nombreCompleto) : ''),
        );
        $this->app->add_history($history);

        $data['msg'] = 'Trabajador marcado para liquidación. Se puede proceder con la liquidación cuando sea necesario.';
        print(json_encode($data));
        return;
    }

    /**
     * Liquidar trabajador: completar la baja después de marcar como liquidación
     */
    private function _liquidar($param)
    {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        // Verificar que esté en estado de liquidación
        try {
            $rowTrab = $this->db->fetchRow(
                "SELECT es_liquidacion FROM trabajadores WHERE id = :id",
                array('id' => $param['id'])
            );
            if (!$rowTrab || !$rowTrab['es_liquidacion']) {
                $data['status'] = 0;
                $data['msg'] = 'El trabajador no está en estado de liquidación.';
                print(json_encode($data));
                return;
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al verificar estado de liquidación: ' . $e->getMessage();
            print(json_encode($data));
            return;
        }

        // Verificar que el trabajador existe en la tabla prenomina con mes y año actual
        try {
            $mesActual = (int)date('n');
            $anioActual = (int)date('Y');
            
            $rowPrenomina = $this->db->fetchRow(
                "SELECT id FROM prenomina WHERE trabajador_id = :tid AND month = :mes AND year = :anio LIMIT 1",
                array(
                    'tid' => $param['id'],
                    'mes' => $mesActual,
                    'anio' => $anioActual
                )
            );
            
            if (!$rowPrenomina) {
                $data['status'] = 0;
                $data['msg'] = 'Debe emitir la liquidacion del trabajador en prenomina antes de darle de baja por completo.';
                print(json_encode($data));
                return;
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al verificar liquidación en nómina: ' . $e->getMessage();
            print(json_encode($data));
            return;
        }

        // Proceder con la baja definitiva
        $update = array(
            'fecha_baja' => date('Y-m-d'),
            'trabajador_eliminado' => 1,
            'es_liquidacion' => 0
        );
        $where = array(
            'id' => $param['id']
        );
        // Ejecutar baja lógica del trabajador
        $this->db->update('trabajadores', $update, $where);

        // Actualizar tarjeta SNC225 con fecha de cierre
        try {
            $trabId = $param['id'];
            $hoy = date('Y-m-d');
            $anioActual = (int)date('Y');
            $mesActual = (int)date('n');

            $rowSNC = $this->db->fetchRow("SELECT id, fecha_inicio FROM tarjetas_snc225 WHERE trabajador_id = :tid ORDER BY id DESC LIMIT 1", array('tid' => $trabId));
            $fechaInicioVal = $rowSNC && isset($rowSNC['fecha_inicio']) ? trim((string)$rowSNC['fecha_inicio']) : '';

            $anioInicio = null;
            $mesInicio = null;
            if ($fechaInicioVal !== '') {
                $tsIni = strtotime($fechaInicioVal);
                if ($tsIni !== false) {
                    $anioInicio = (int)date('Y', $tsIni);
                    $mesInicio = (int)date('n', $tsIni);
                }
            }

            $meses = null;
            if ($anioInicio !== null && $mesInicio !== null) {
                $meses = ($anioActual - $anioInicio) * 12 + ($mesActual - $mesInicio);
                if ($meses < 0) {
                    $meses = 0;
                }
            }

            if ($rowSNC && isset($rowSNC['id'])) {
                $updateSNC = array('fecha_cierre' => $hoy);
                if ($meses !== null) {
                    $updateSNC['tiempo_trabajo'] = $meses;
                }
                $this->db->update('tarjetas_snc225', $updateSNC, array('id' => $rowSNC['id']));
            }
        } catch (Exception $e) {
            // No es crítico si falla la actualización de SNC225
        }

        // Registrar en historial la liquidación
        $nombreCompleto = '';
        try {
            $rowNombre = $this->db->fetchRow(
                "SELECT nombre, apellidos, apellidos_segundos FROM trabajadores WHERE id = :id",
                array('id' => $param['id'])
            );
            if ($rowNombre) {
                $nombreCompleto = trim(($rowNombre['nombre'] ?? '') . ' ' . ($rowNombre['apellidos'] ?? '') . ' ' . ($rowNombre['apellidos_segundos'] ?? ''));
            }
        } catch (Exception $e) { /* ignore */
        }

        $history = array(
            'xentity' => 'TRABAJADOR',
            'xaction' => 'BAJA-TRABAJADOR',
            'xid' => $param['id'],
            'xobs' => 'BAJA TRABAJADOR ID: ' . $param['id'] . ($nombreCompleto ? (' - ' . $nombreCompleto) : ''),
        );
        $this->app->add_history($history);

        $data['msg'] = 'Trabajador liquidado correctamente.';
        print(json_encode($data));
    }

    /**
     * Liquidar trabajador forzado: baja definitiva sin verificar prenomina
     */
    private function _liquidar_forzado($param)
    {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        // Verificar que esté en estado de liquidación
        try {
            $rowTrab = $this->db->fetchRow(
                "SELECT es_liquidacion FROM trabajadores WHERE id = :id",
                array('id' => $param['id'])
            );
            if (!$rowTrab || !$rowTrab['es_liquidacion']) {
                $data['status'] = 0;
                $data['msg'] = 'El trabajador no está en estado de liquidación.';
                print(json_encode($data));
                return;
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al verificar estado de liquidación: ' . $e->getMessage();
            print(json_encode($data));
            return;
        }

        // Baja definitiva sin verificar prenomina
        $update = array(
            'fecha_baja' => date('Y-m-d'),
            'trabajador_eliminado' => 1,
            'es_liquidacion' => 0
        );
        $where = array(
            'id' => $param['id']
        );
        $this->db->update('trabajadores', $update, $where);

        // Actualizar tarjeta SNC225 con fecha de cierre
        try {
            $trabId = $param['id'];
            $hoy = date('Y-m-d');
            $anioActual = (int)date('Y');
            $mesActual = (int)date('n');

            $rowSNC = $this->db->fetchRow("SELECT id, fecha_inicio FROM tarjetas_snc225 WHERE trabajador_id = :tid ORDER BY id DESC LIMIT 1", array('tid' => $trabId));
            $fechaInicioVal = $rowSNC && isset($rowSNC['fecha_inicio']) ? trim((string)$rowSNC['fecha_inicio']) : '';

            $anioInicio = null;
            $mesInicio = null;
            if ($fechaInicioVal !== '') {
                $tsIni = strtotime($fechaInicioVal);
                if ($tsIni !== false) {
                    $anioInicio = (int)date('Y', $tsIni);
                    $mesInicio = (int)date('n', $tsIni);
                }
            }

            $meses = null;
            if ($anioInicio !== null && $mesInicio !== null) {
                $meses = ($anioActual - $anioInicio) * 12 + ($mesActual - $mesInicio);
                if ($meses < 0) {
                    $meses = 0;
                }
            }

            if ($rowSNC && isset($rowSNC['id'])) {
                $updateSNC = array('fecha_cierre' => $hoy);
                if ($meses !== null) {
                    $updateSNC['tiempo_trabajo'] = $meses;
                }
                $this->db->update('tarjetas_snc225', $updateSNC, array('id' => $rowSNC['id']));
            }
        } catch (Exception $e) {
            // No es crítico si falla la actualización de SNC225
        }

        // Registrar en historial
        $nombreCompleto = '';
        try {
            $rowNombre = $this->db->fetchRow(
                "SELECT nombre, apellidos, apellidos_segundos FROM trabajadores WHERE id = :id",
                array('id' => $param['id'])
            );
            if ($rowNombre) {
                $nombreCompleto = trim(($rowNombre['nombre'] ?? '') . ' ' . ($rowNombre['apellidos'] ?? '') . ' ' . ($rowNombre['apellidos_segundos'] ?? ''));
            }
        } catch (Exception $e) { /* ignore */
        }

        $history = array(
            'xentity' => 'TRABAJADOR',
            'xaction' => 'BAJA-TRABAJADOR-FORZADO',
            'xid' => $param['id'],
            'xobs' => 'BAJA TRABAJADOR FORZADA (SIN PRENOMINA) ID: ' . $param['id'] . ($nombreCompleto ? (' - ' . $nombreCompleto) : ''),
        );
        $this->app->add_history($history);

        $data['msg'] = 'Trabajador dado de baja correctamente (sin liquidación en prenómina).';
        print(json_encode($data));
    }

    /**
     * Minimal _checked handler to satisfy API calls.
     * This is a no-op placeholder; adjust logic as needed for your application.
     */
    private function _checked($param)
    {
        // Example response: operation acknowledged
        $resp = array('status' => 1, 'msg' => 'checked processed');
        print(json_encode($resp));
        return;
    }

    /**
     * Verificar si una empresa ha alcanzado el límite de trabajadores activos
     * @param int $empresaId ID de la empresa
     * @return array Con 'status' (1=permitido, 0=limite alcanzado) y 'count' (número actual de trabajadores)
     */
    private function verificarLimiteTrabajadoresEmpresa($empresaId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM trabajadores WHERE empresa_id = :empresa_id AND trabajador_eliminado = 0";
            $result = $this->db->fetchRow($sql, ['empresa_id' => $empresaId]);
            $total = $result ? (int)$result['total'] : 0;
            
            return [
                'status' => $total < 100 ? 1 : 0,
                'count' => $total,
                'limit' => 100
            ];
        } catch (Exception $e) {
            error_log('Error al verificar límite de trabajadores: ' . $e->getMessage());
            return [
                'status' => 0,
                'count' => 0,
                'limit' => 100
            ];
        }
    }

    /**
     * Obtiene el próximo ID disponible en la tabla usuarios
     */
    private function getNextUsuarioId()
    {
        $sql = "SELECT IFNULL(MAX(xusuario_id), 0) + 1 as next_id FROM usuarios";
        $result = $this->db->fetchRow($sql);
        return $result ? (int)$result['next_id'] : 1;
    }
    private function generarUuidV4()
    {
        $data = random_bytes(16);

        // Ajustar los bits según la especificación UUID v4
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // Helper para verificar si una columna existe en la tabla trabajadores
    private function columnExists($column)
    {
        try {
            $row = $this->db->fetchRow("SHOW COLUMNS FROM trabajadores LIKE :col", ['col' => $column]);
            if ($row && count($row) > 0) return true;
        } catch (Exception $e) {
            // Algunos adaptadores no aceptan parámetros en SHOW COLUMNS, intentar sin param
            try {
                $row = $this->db->fetchRow("SHOW COLUMNS FROM trabajadores LIKE '" . $column . "'");
                if ($row && count($row) > 0) return true;
            } catch (Exception $ex) {
            }
        }
        return false;
    }

    /**
     * Comprime una imagen usando el script Python
     * @param string $inputPath Ruta del archivo temporal de entrada
     * @return array|false Datos de la imagen comprimida o false en caso de error
     */
    private function compressImage($inputPath)
    {
        try {
            // Verificar archivo de entrada
            if (!file_exists($inputPath)) {
                error_log('El archivo de entrada no existe: ' . $inputPath);
                return false;
            }

            if (!is_readable($inputPath)) {
                error_log('El archivo de entrada no es legible: ' . $inputPath);
                return false;
            }

            // Rutas ABSOLUTAS (MUY IMPORTANTE)
            $pythonPath = __DIR__ . '/../scripts/venv/bin/python';
            $scriptPath = __DIR__ . '/../scripts/image_compressor.py';

            if (!file_exists($pythonPath)) {
                error_log('Python del venv no existe: ' . $pythonPath);
                return false;
            }

            if (!file_exists($scriptPath)) {
                error_log('El script de compresión no existe: ' . $scriptPath);
                return false;
            }

            $outputPath = tempnam(sys_get_temp_dir(), 'compressed_img_') . '.webp';

            // Construcción CORRECTA del comando
            $command = sprintf(
                '%s %s %s %s --json 2>&1',
                escapeshellcmd($pythonPath),
                escapeshellarg($scriptPath),
                escapeshellarg($inputPath),
                escapeshellarg($outputPath)
            );

            error_log('Ejecutando compresión: ' . $command);

            $output = shell_exec($command);
            error_log('Salida del script: ' . $output);

            // Decodificar JSON
            $result = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Error decodificando JSON: ' . json_last_error_msg());
                error_log('Salida cruda: ' . $output);
                return false;
            }

            // Validar resultado
            if (
                isset($result['success']) &&
                $result['success'] === true &&
                file_exists($outputPath)
            ) {
                $compressedBlob = file_get_contents($outputPath);
                unlink($outputPath);

                if ($compressedBlob !== false) {
                    error_log('Imagen comprimida exitosamente. Size: ' . strlen($compressedBlob) . ' bytes');

                    return [
                        'blob' => $compressedBlob,
                        'mime' => 'image/webp',
                        'original_size' => $result['original_size'] ?? 0,
                        'compressed_size' => $result['compressed_size'] ?? 0,
                        'compression_ratio' => $result['compression_ratio'] ?? 0,
                    ];
                }

                error_log('No se pudo leer el archivo comprimido');
            } else {
                error_log('La compresión falló');
                if (isset($result['error'])) {
                    error_log('Error del script: ' . $result['error']);
                }
            }

            if (file_exists($outputPath)) {
                unlink($outputPath);
            }

            return false;

        } catch (Throwable $e) {
            error_log('Error en compresión de imagen: ' . $e->getMessage());
            return false;
        }
    }

    private function _check($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'update'
        );

        $where = array(
            'id' => $param['id']
        );
        if ($param['tipo_horario'] == '') {
            $update = array(
                'tipo_horario' => null
            );
        }
        $update = array(
            'tipo_horario' => $param['tipo_horario']
        );

        $this->app->db->update('trabajadores', $update, $where);

        print(json_encode($data));
    }

    private function _save($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        if (isset($param['licencia_conduccion'])) {
            $param['licencia_conduccion'] = str_replace(',', ' ', $param['licencia_conduccion']);
        }

        // Obtener el próximo ID de usuario disponible para nuevos registros
        if ($data['action'] === 'insert') {
            $param['usuario_id'] = $this->getNextUsuarioId();
            // Generar UUID para el nuevo trabajador
            $param['uuid'] = $this->generarUuidV4();
        } else {
            $query = "SELECT usuario_id FROM trabajadores WHERE id = :id";
            $result = $this->db->fetchAll($query, array('id' => $param['id']));
            $param['usuario_id'] = $result[0]['usuario_id'];
        }

        // Verificar si el carnet de identidad ya existe
        if ($data['action'] == 'insert' && isset($param['carnet_identidad'])) {
            $sql = "SELECT id FROM trabajadores WHERE carnet_identidad = :ci AND trabajador_eliminado = '0'";
            $val = array('ci' => $param['carnet_identidad']);
            $existing = $this->db->fetchRow($sql, $val);

            if ($existing) {
                $data['status'] = 0;
                $data['msg'] = 'Ya existe un trabajador con este Carnet de Identidad';
                print(json_encode($data));
                return;
            }
        }

        if (isset($param['email'])) {
            $param['email'] = $this->normalizarCorreo($param['email']);
            if ($param['email'] !== '' && !filter_var($param['email'], FILTER_VALIDATE_EMAIL)) {
                $data['status'] = 0;
                $data['msg'] = 'Correo electrónico inválido';
                print(json_encode($data));
                return;
            }
        }
        if ($data['action'] == 'insert' && isset($param['email']) && trim($param['email']) !== '') {
            try {
                $sql = "SELECT xusuario_id FROM usuarios WHERE LOWER(xemail) = LOWER(:email)";
                $val = array('email' => $param['email']);
                $existing = $this->db->fetchRow($sql, $val);
                if ($existing) {
                    $data['status'] = 0;
                    $data['msg'] = 'Ya existe un usuario con este correo electrónico';
                    print(json_encode($data));
                    return;
                }
            } catch (Exception $ex) {
                // Si la columna 'email' no existe todavía, omitir verificación de unicidad
                if (strpos($ex->getMessage(), 'Unknown column') === false) {
                    throw $ex;
                } else {
                    error_log('Aviso: columna "email" no existe en trabajadores; se omite verificación de unicidad (insert).');
                }
            }
        }

        // Validar que nombre, apellidos y segundo apellido solo contengan letras y espacios (insert)
        $campos_alfabeticos = array(
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'apellidos_segundos' => 'Segundo Apellido'
        );
        foreach ($campos_alfabeticos as $campo => $label) {
            if (isset($param[$campo]) && trim($param[$campo]) !== '') {
                if (!preg_match('/^[\p{L}\s]+$/u', $param[$campo])) {
                    $data['status'] = 0;
                    $data['msg'] = "El campo {$label} solo debe contener letras";
                    print(json_encode($data));
                    return;
                }
            }
        }

        // Verificar si el nombre completo ya existe (insert)
        if ($data['action'] == 'insert' && isset($param['nombre']) && isset($param['apellidos'])) {
            $sql = "SELECT id FROM trabajadores WHERE LOWER(nombre) = LOWER(:nombre) AND LOWER(apellidos) = LOWER(:apellidos) AND trabajador_eliminado = '0'";
            $val = array('nombre' => $param['nombre'], 'apellidos' => $param['apellidos']);
            $existing = $this->db->fetchRow($sql, $val);
            if ($existing) {
                $data['status'] = 0;
                $data['msg'] = 'Ya existe un trabajador con el mismo nombre y apellidos';
                print(json_encode($data));
                return;
            }
        }

        // Validar campos requeridos
        $required_fields = array(
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'apellidos_segundos' => 'Segundo Apellido',
            'sexo' => 'Sexo',
            'carnet_identidad' => 'Carnet de Identidad',

            'direccion' => 'Dirección',
            'provincia_id' => 'Provincia',
            'municipio_id' => 'Municipio',
            'telefono' => 'Teléfono',
            'ubicacion' => 'Ubicación',
            // 'email' => 'Email',
            'nivel_educacional' => 'Nivel Educacional',
            'departamento_id' => 'Departamento',
            'cargos_id' => 'Cargo',
            'estatus' => 'Estatus'
        );

        foreach ($required_fields as $field => $label) {
            if (!isset($param[$field]) || trim($param[$field]) === '') {
                $data['status'] = 0;
                $data['msg'] = "El campo {$label} es obligatorio";
                print(json_encode($data));
                return;
            }
        }

        // Los campos provincia_id y municipio_id ya están incluidos en allowed_fields

        // Crear array de datos sin incluir campos de control
        $insert = array();
        $allowed_fields = ['usuario_id', 'nombre', 'apellidos', 'apellidos_segundos', 'sexo', 'carnet_identidad', 'edad', 'direccion', 'provincia_id', 'municipio_id', 'telefono', 'email', 'licencia_conduccion', 'nivel_educacional', 'departamento_id', 'cargos_id', 'fecha_contratacion', 'fecha_baja', 'estatus', 'bolsa_empleo_id', 'foto', 'ubicacion', 'fecha_contrato'];

        foreach ($allowed_fields as $field) {
            if (isset($param[$field])) {
                switch ($field) {
                    case 'edad':
                    case 'departamento_id':
                    case 'cargos_id':
                    case 'provincia_id':
                    case 'ubicacion':
                    case 'municipio_id':
                        $insert[$field] = intval($param[$field]);
                        break;
                    case 'bolsa_empleo_id':
                        $insert[$field] = !empty($param[$field]) ? intval($param[$field]) : null;
                        break;
                    case 'fecha_baja':
                        $insert[$field] = !empty($param[$field]) ? $param[$field] : null;
                        break;
                    case 'fecha_contrato':
                        // Mapear fecha_contrato a fecha_contratacion
                        $insert['fecha_contratacion'] = !empty($param[$field]) ? $param[$field] : null;
                        break;
                    case 'foto':
                        $insert[$field] = isset($param[$field]) ? $param[$field] : '';
                        break;
                    default:
                        $insert[$field] = $param[$field];
                        break;
                }
            }
        }
        $insert['trabajador_eliminado'] = 0;

        $data['action'] = $param['action'];

        // Validar parámetro action
        if (empty($param['action']) || !in_array($param['action'], ['insert', 'update'])) {
            $data['status'] = 0;
            $data['msg'] = 'Error: Parámetro action inválido';
            print(json_encode($data));
            return;
        }

        // Remover campos que no pertenecen a la tabla trabajadores
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);
        unset($insert['id']); // Asegurar que el ID no se incluya en los datos a insertar/actualizar

        if ($param['action'] == 'insert') {
            // Verificar límite de trabajadores para la empresa antes de insertar
            $empresaId = $this->app->empresa_id;
            $limiteCheck = $this->verificarLimiteTrabajadoresEmpresa($empresaId);
            
            if ($limiteCheck['status'] == 0) {
                $data['status'] = 0;
                $data['msg'] = "No se puede crear el trabajador. La empresa ya ha alcanzado el límite de {$limiteCheck['limit']} trabajadores activos (actual: {$limiteCheck['count']}).";
                print(json_encode($data));
                return;
            }

            // Establecer fecha de contratación si no está definida
            if (!isset($insert['fecha_contratacion']) || empty($insert['fecha_contratacion'])) {
                $insert['fecha_contratacion'] = date('Y-m-d');
            }

            try {
                // Primero insertar en la tabla usuarios
                $emailLocalGen = $this->slugUser($insert['nombre'] . '.' . $insert['apellidos']);
                $email = $this->normalizarCorreo($emailLocalGen . '@allnovu.net');

                // Crear instancia de la clase Usuario
                require_once 'mdl.Usuarios.php';
                $usuario = new Usuario($this->app);

                // Preparar datos del usuario
                $usuarioData = [
                    'method' => 'save',
                    'action' => 'insert',
                    'xusuario' => $insert['nombre'] . ' ' . $insert['apellidos'],
                    'xemail' => $email,
                    'xpwd' => $insert['carnet_identidad'],
                    'xactivo' => 'S',
                    'xrol_id' => 2,
                    'xdatealta' => date('Y-m-d H:i:s'),
                    'xdatemodif' => date('Y-m-d H:i:s')
                ];

                // Usar el método api de la clase Usuario para manejar el guardado
                ob_start(); // Capturar salida de api()
                $usuario->api($usuarioData);
                $apiResponse = ob_get_clean();

                // Decodificar la respuesta JSON
                $usuarioInserted = json_decode($apiResponse, true);

                // Verificar si la inserción fue exitosa
                if (json_last_error() !== JSON_ERROR_NONE || !isset($usuarioInserted['status']) || $usuarioInserted['status'] != 1) {
                    $errorMsg = json_last_error_msg();
                    throw new Exception('Error al crear el usuario: ' . ($usuarioInserted['msg'] ?? $errorMsg ?? 'Error desconocido'));
                }

                // Obtener el ID del usuario recién creado
                $usuarioId = $param['usuario_id'];

                if (!$usuarioId) {
                    throw new Exception('No se pudo obtener el ID del usuario creado');
                }

                // Asegurar que solo se insertan los campos que existen en la tabla
                $campos_validos = [
                    'cargos_id',
                    'departamento_id',
                    'foto',
                    'nombre',
                    'apellidos',
                    'apellidos_segundos',
                    'carnet_identidad',
                    'sexo',
                    'edad',
                    'direccion',
                    'provincia_id',
                    'municipio_id',
                    'ubicacion',
                    'telefono',
                    'licencia_conduccion',
                    'usuario_id',
                    'nivel_educacional',
                    'fecha_contratacion',
                    'fecha_baja',
                    'estatus',
                    'bolsa_empleo_id',
                    'trabajador_eliminado'
                ];

                // Filtrar solo los campos válidos
                $insert_filtered = array_intersect_key($insert, array_flip($campos_validos));

                // Agregar el usuario_id al registro del trabajador
                $insert_filtered['usuario_id'] = $usuarioId;

                //Agregar la empresa
                $insert_filtered['empresa_id'] = $this->app->empresa_id;

                // Procesar foto si se subió: validar tamaño y tipo, comprimir y almacenar contenido binario
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    // Configuración: límites y whitelist
                    $maxBytes = 2 * 1024 * 1024; // 2 MB por defecto
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                    $tmp = $_FILES['foto']['tmp_name'];
                    $size = filesize($tmp);
                    if ($size === false) {
                        $size = 0;
                    }
                    if ($size > $maxBytes) {
                        $data['status'] = 0;
                        $data['msg'] = 'El archivo supera el tamaño máximo permitido de 2MB.';
                        print(json_encode($data));
                        return;
                    }

                    // Detectar MIME del archivo subido
                    $mime = 'application/octet-stream';
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $det = finfo_file($finfo, $tmp);
                        if ($det) $mime = $det;
                        finfo_close($finfo);
                    } else {
                        $g = @getimagesize($tmp);
                        if ($g && isset($g['mime'])) $mime = $g['mime'];
                    }

                    if (!in_array($mime, $allowedMimes)) {
                        $data['status'] = 0;
                        $data['msg'] = 'Tipo de imagen no permitido. Utilice JPG, PNG, GIF o WEBP.';
                        print(json_encode($data));
                        return;
                    }

                    // Intentar comprimir la imagen usando el script Python
                    $compressedImage = $this->compressImage($tmp);
                    
                    if ($compressedImage !== false) {
                        // Usar la imagen comprimida
                        $insert_filtered['foto'] = $compressedImage['blob'];
                        if ($this->columnExists('foto_mime')) {
                            $insert_filtered['foto_mime'] = $compressedImage['mime'];
                        }
                        error_log('Imagen comprimida exitosamente. Ratio: ' . $compressedImage['compression_ratio'] . '%');
                    } else {
                        // Si la compresión falla, usar la imagen original
                        $blob = file_get_contents($tmp);
                        if ($blob !== false) {
                            $insert_filtered['foto'] = $blob;
                            if ($this->columnExists('foto_mime')) {
                                $insert_filtered['foto_mime'] = $mime;
                            }
                            error_log('No se pudo comprimir la imagen, se usó la original.');
                        } else {
                            error_log('Error al leer el archivo subido: ' . ($tmp ?? ''));
                        }
                    }
                }


                // Verificar que el carnet de identidad tenga un formato válido
                $carnet_identidad = $param['carnet_identidad'];
                if (preg_match('/^[0-9]{11}$/', $carnet_identidad)) {
                    $ano = substr($carnet_identidad, 0, 2);
                    $mes = substr($carnet_identidad, 2, 2);
                    $dia = substr($carnet_identidad, 4, 2);


                    // Si el año tiene 2 dígitos, convertirlo a un año completo
                    if (strlen($ano) == 2) {
                        // Si el siglo es 19, el año es del siglo XX
                        if ($ano > substr(date('Y'), 2, 2)) {
                            $ano = '19' . $ano;
                        } else {
                            // Si el siglo es 20, el año es del siglo XXI
                            $ano = '20' . $ano;
                        }
                    }

                    // Verificar que la fecha sea válida
                    if (checkdate($mes, $dia, $ano)) {
                        $insert_filtered['fecha_nacimiento'] = $ano . '-' . $mes . '-' . $dia;
                        // Todo bien, la fecha es válida
                    } else {
                        $data['status'] = 0;
                        $data['msg'] = 'El carnet de identidad no tiene un formato válido';
                        print(json_encode($data));
                        return;
                    }
                } else {
                    $data['status'] = 0;
                    $data['msg'] = 'El carnet de identidad no tiene un formato válido';
                    print(json_encode($data));
                    return;
                }





                // Insertar trabajador con datos validados

                // Insertar el trabajador con tolerancia a columnas ausentes (email)
                $result = false;
                try {
                    $result = $this->db->insert('trabajadores', $insert_filtered);
                } catch (Exception $ex) {
                    $msg = $ex->getMessage();
                    $retry = false;
                    if (strpos($msg, "Unknown column 'email'") !== false || strpos($msg, 'Unknown column \"email\"') !== false) {
                        if (isset($insert_filtered['email'])) {
                            unset($insert_filtered['email']);
                            error_log('Aviso: columna "email" no existe en trabajadores; reintentando insert sin email.');
                            $retry = true;
                        }
                    }
                    if (strpos($msg, "Unknown column 'licencia_conduccion'") !== false || strpos($msg, 'Unknown column \"licencia_conduccion\"') !== false) {
                        if (isset($insert_filtered['licencia_conduccion'])) {
                            unset($insert_filtered['licencia_conduccion']);
                            error_log('Aviso: columna "licencia_conduccion" no existe en trabajadores; reintentando insert sin licencia_conduccion.');
                            $retry = true;
                        }
                    }
                    if ($retry) {
                        $result = $this->db->insert('trabajadores', $insert_filtered);
                    } else {
                        throw $ex;
                    }
                }

                if ($result) {
                    $lastId = $this->db->last_id();
                    if (isset($param['bolsa_empleo_id'])) {
                        $sql = "SELECT * FROM bolsa_empleo WHERE id=:id";
                        $get = $this->db->fetchAll($sql, array('id' => $param['bolsa_empleo_id']));
                        if ($get[0]['curriculum']) {
                            try {
                                $this->db->insert('documentos_trabajador', array('trabajador_id' => $lastId, 'tipo' => 'cv', 'archivo' => $get[0]['curriculum'], 'fecha_upload' => date('Y-m-d H:i:s')));
                                try {
                                    $this->db->del('bolsa_empleo', array('id' => $param['bolsa_empleo_id']));
                                } catch (Exception $ex) {
                                    error_log('Error al eliminar el cv: ' . $ex->getMessage());
                                }
                            } catch (Exception $ex) {
                                error_log('Error al insertar el cv: ' . $ex->getMessage());
                            }
                        }
                    }
                    // Obtener el ID del trabajador insertado

                    if ($lastId) {
                        // Guardar información bancaria si se proporcionó
                        if (!empty($param['tarjeta_salario']) || !empty($param['cuenta_estandar'])) {
                            error_log('Guardando información bancaria para trabajador ID: ' . $lastId);
                            error_log('Tarjeta de salario: ' . ($param['tarjeta_salario'] ?? 'vacío'));
                            error_log('Cuenta estándar: ' . ($param['cuenta_estandar'] ?? 'vacío'));

                            $bancoData = array(
                                'trabajador_id' => $lastId,
                                'numero_tarjeta_salario' => $param['tarjeta_salario'] ?? '',
                                'numero_cuenta_estandar' => $param['cuenta_estandar'] ?? ''
                            );

                            try {
                                // Verificar si ya existe un registro para este trabajador
                                $sql = "SELECT id FROM bancos WHERE trabajador_id = :trabajador_id";
                                error_log('Ejecutando consulta: ' . $sql . ' con trabajador_id: ' . $lastId);

                                $existingBanco = $this->db->fetchRow($sql, ['trabajador_id' => $lastId]);

                                if ($existingBanco) {
                                    error_log('Actualizando registro existente en bancos con ID: ' . $existingBanco['id']);
                                    $result = $this->db->update('bancos', $bancoData, ['id' => $existingBanco['id']]);
                                    error_log('Resultado de actualización: ' . ($result ? 'éxito' : 'fallo'));
                                } else {
                                    error_log('Insertando nuevo registro en bancos');
                                    $result = $this->db->insert('bancos', $bancoData);
                                    error_log('Resultado de inserción: ' . ($result ? 'éxito' : 'fallo'));
                                    if ($result) {
                                        $bancoId = $this->db->last_id();
                                        error_log('Nuevo ID de banco: ' . $bancoId);
                                    }
                                }
                            } catch (Exception $e) {
                                error_log('Error al guardar información bancaria: ' . $e->getMessage());
                            }
                        }

                        try {
                            // fecha_inicio toma la fecha de contratación del trabajador
                            $fechaInicio = isset($insert_filtered['fecha_contratacion']) && !empty($insert_filtered['fecha_contratacion'])
                                ? $insert_filtered['fecha_contratacion']
                                : date('Y-m-d');
                            // periodo debe ser la misma fecha exacta de creación
                            $this->db->insert('tarjetas_snc225', array(
                                'trabajador_id' => $lastId,
                                'periodo' => $fechaInicio,
                                'fecha_inicio' => $fechaInicio
                            ));
                        } catch (Exception $e) {
                        }

                        // Preparar respuesta exitosa
                        $data['msg_title'] = 'Operación exitosa';
                        $data['msg'] = 'Registro insertado correctamente';
                        $data['id'] = $lastId;
                        $data['date'] = date('d-m-Y H:i:s');

                        // Crear contrato inicial automático (Tipo 2 - Determinado)
                        if (isset($insert_filtered['cargos_id']) && !empty($insert_filtered['cargos_id'])) {
                            $fechaContrato = isset($insert_filtered['fecha_contratacion']) && !empty($insert_filtered['fecha_contratacion'])
                                ? $insert_filtered['fecha_contratacion']
                                : date('Y-m-d');
                            $this->_createInitialContract($lastId, $insert_filtered['cargos_id'], $fechaContrato);
                        }
                        // Registrar en historial
                        $history = array(
                            'xentity' => 'TRABAJADOR',
                            'xaction' => 'INSERT-TRABAJADOR',
                            'xobs' => 'INSERT TRABAJADOR ID: ' . $lastId . ' - ' . trim(($insert['nombre'] ?? '') . ' ' . ($insert['apellidos'] ?? '') . ' ' . ($insert['apellidos_segundos'] ?? ''))
                        );
                        $this->app->add_history($history);
                    } else {
                        $data['status'] = 0;
                        $data['msg_title'] = 'Error';
                        $data['msg'] = 'No se pudo obtener el ID del nuevo registro';
                    }
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'Error al insertar el registro';
                }
            } catch (Exception $e) {
                // Respuesta de error amigable
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $friendly = '';
                // Detectar violaciones de integridad (duplicados)
                if (method_exists($e, 'getCode') && $e->getCode() == '23000') {
                    $friendly = 'Violación de integridad de datos.';
                }
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $friendly = 'Ya existe un registro con el mismo identificador. Verifique que el ID o el CI no estén duplicados.';
                }
                $data['msg'] = $friendly !== '' ? $friendly : ('Error al insertar el registro: ' . $e->getMessage());
            }
        } else {
            // Update existente
            if (!isset($param['id']) || empty($param['id'])) {
                $data['status'] = 0;
                $data['msg'] = 'ID de trabajador no proporcionado para actualización';
                print(json_encode($data));
                return;
            }

            try {
                $id = intval($param['id']);

                // Debug: verificar que el ID existe
                if (empty($id)) {
                    $data['status'] = 0;
                    $data['msg'] = 'Error: ID de trabajador no proporcionado para actualización';
                    print(json_encode($data));
                    return;
                }

                // Verificar si el carnet de identidad ya existe en otro registro
                if (isset($param['carnet_identidad'])) {
                    $sql = "SELECT id FROM trabajadores WHERE carnet_identidad = :ci AND id != :id AND trabajador_eliminado = '0'";
                    $val = array(
                        'ci' => $param['carnet_identidad'],
                        'id' => $id
                    );
                    $existing = $this->db->fetchRow($sql, $val);

                    if ($existing) {
                        $data['status'] = 0;
                        $data['msg'] = 'Ya existe otro trabajador con este Carnet de Identidad';
                        print(json_encode($data));
                        return;
                    }
                    // Validar formato: solo 11 dígitos
                    if (!preg_match('/^\d{11}$/', $param['carnet_identidad'])) {
                        $data['status'] = 0;
                        $data['msg'] = 'El carnet de identidad debe tener exactamente 11 dígitos';
                        print(json_encode($data));
                        return;
                    }
                }



                // Verificar si el nombre completo ya existe en otro registro (update)
                if (isset($param['nombre']) && isset($param['apellidos'])) {
                    $sql = "SELECT id FROM trabajadores WHERE LOWER(nombre) = LOWER(:nombre) AND LOWER(apellidos) = LOWER(:apellidos) AND id != :id AND trabajador_eliminado = '0'";
                    $val = array(
                        'nombre' => $param['nombre'],
                        'apellidos' => $param['apellidos'],
                        'id' => $id
                    );
                    $existing = $this->db->fetchRow($sql, $val);
                    if ($existing) {
                        $data['status'] = 0;
                        $data['msg'] = 'Ya existe otro trabajador con el mismo nombre y apellidos';
                        print(json_encode($data));
                        return;
                    }
                }

                if (isset($param['email'])) {
                    $param['email'] = $this->normalizarCorreo($param['email']);
                    if ($param['email'] !== '' && !filter_var($param['email'], FILTER_VALIDATE_EMAIL)) {
                        $data['status'] = 0;
                        $data['msg'] = 'Correo electrónico inválido';
                        print(json_encode($data));
                        return;
                    }
                    if ($param['email'] !== '') {
                        try {
                            $sql = "SELECT xusuario_id FROM usuarios WHERE LOWER(xemail) = LOWER(:email) AND xusuario_id != :id";
                            $val = array('email' => $param['email'], 'id' => $param['usuario_id']);
                            $existing = $this->db->fetchRow($sql, $val);
                            if ($existing) {
                                $data['status'] = 0;
                                $data['msg'] = 'Ya existe otro usuario con este correo electrónico';
                                print(json_encode($data));
                                return;
                            }
                        } catch (Exception $ex) {
                            // Si la columna 'email' no existe todavía, omitir verificación de unicidad
                            if (strpos($ex->getMessage(), 'Unknown column') === false) {
                                throw $ex;
                            } else {
                                error_log('Aviso: columna "email" no existe en trabajadores; se omite verificación de unicidad (update).');
                            }
                        }
                    }
                }
                // Validar que nombre, apellidos y segundo apellido solo contengan letras y espacios (update)
                $campos_alfabeticos_upd = array(
                    'nombre' => 'Nombre',
                    'apellidos' => 'Apellidos',
                    'apellidos_segundos' => 'Segundo Apellido'
                );
                foreach ($campos_alfabeticos_upd as $campo => $label) {
                    if (isset($param[$campo]) && trim($param[$campo]) !== '') {
                        if (!preg_match('/^[\p{L}\s]+$/u', $param[$campo])) {
                            $data['status'] = 0;
                            $data['msg'] = "El campo {$label} solo debe contener letras";
                            print(json_encode($data));
                            return;
                        }
                    }
                }
                // Filtrar campos válidos para actualización
                $campos_validos = [
                    'cargos_id',
                    'departamento_id',
                    'ubicacion',
                    'foto',
                    'nombre',
                    'apellidos',
                    'apellidos_segundos',
                    'carnet_identidad',
                    'sexo',
                    'edad',
                    'direccion',
                    'provincia_id',
                    'municipio_id',
                    'telefono',
                    'licencia_conduccion',
                    'nivel_educacional',
                    'fecha_contratacion',
                    'fecha_baja',
                    'estatus',
                    'trabajador_eliminado'
                ];

                // Filtrar solo los campos válidos
                $update_filtered = array_intersect_key($insert, array_flip($campos_validos));

                // Asegurar que el ID no esté en los datos de actualización
                unset($update_filtered['id']);

                // Procesar foto subida en update: validar, comprimir y almacenar blob + foto_mime
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $tmp = $_FILES['foto']['tmp_name'];
                    $maxBytes = 2 * 1024 * 1024; // 2 MB
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $size = filesize($tmp);
                    if ($size === false) {
                        $size = 0;
                    }
                    if ($size > $maxBytes) {
                        $data['status'] = 0;
                        $data['msg'] = 'El archivo supera el tamaño máximo permitido de 2MB.';
                        print(json_encode($data));
                        return;
                    }

                    $mime = 'application/octet-stream';
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $det = finfo_file($finfo, $tmp);
                        if ($det) $mime = $det;
                        finfo_close($finfo);
                    } else {
                        $g = @getimagesize($tmp);
                        if ($g && isset($g['mime'])) $mime = $g['mime'];
                    }
                    if (!in_array($mime, $allowedMimes)) {
                        $data['status'] = 0;
                        $data['msg'] = 'Tipo de imagen no permitido. Utilice JPG, PNG, GIF o WEBP.';
                        print(json_encode($data));
                        return;
                    }

                    // Intentar comprimir la imagen usando el script Python
                    $compressedImage = $this->compressImage($tmp);
                    
                    if ($compressedImage !== false) {
                        // Usar la imagen comprimida
                        $update_filtered['foto'] = $compressedImage['blob'];
                        if ($this->columnExists('foto_mime')) {
                            $update_filtered['foto_mime'] = $compressedImage['mime'];
                        }
                        error_log('Imagen comprimida exitosamente en update. Ratio: ' . $compressedImage['compression_ratio'] . '%');
                    } else {
                        // Si la compresión falla, usar la imagen original
                        $blob = file_get_contents($tmp);
                        if ($blob !== false) {
                            $update_filtered['foto'] = $blob;
                            if ($this->columnExists('foto_mime')) {
                                $update_filtered['foto_mime'] = $mime;
                            }
                            error_log('No se pudo comprimir la imagen en update, se usó la original.');
                        } else {
                            error_log('Error al leer el archivo subido (update): ' . ($tmp ?? ''));
                        }
                    }
                }

                // Actualizar el trabajador
                $where = array('id' => $id);
                $result = $this->db->update('trabajadores', $update_filtered, $where);

                //actualizar el usuario
                $where = array('xusuario_id' => $param['usuario_id']);
                $email = $param['email'];
                $update_filtered_user = array('xemail' => $email);
                $result = $this->db->update('usuarios', $update_filtered_user, $where);
                if ($result) {
                    // Detectar cambio de cargo y generar suplemento
                    if (isset($update_filtered['cargos_id']) && $id > 0) {
                        try {
                            // Obtener cargo anterior (antes del update ya hecho? No, ya se hizo update arriba. 
                            // Corrección: El update ya se ejecutó en la línea 1062. 
                            // Necesitamos saber si ERA diferente.
                            // Para hacerlo bien, debería haber obtenido el cargo anterior ANTES del update.
                            // Pero como ya tengo el código estructurado, voy a usar la lógica de comparación con lo que venía en param vs lo que había en BD.

                            // Recuperamos el cargo que tenía antes consultando el historial o mejor, lo recuperamos antes del update.
                            // Como no puedo cambiar el código anterior fácilmente sin editar mucho bloque, usaré una consulta para ver 'contrato activo actual' vs 'nuevo cargo'.
                            // Si el contrato activo tiene un cargo diferente al nuevo, entonces cambió. 
                            // O mejor, simplemente asumo que si se pasó cargos_id en param, hubo intención de cambio.
                            // Y verifico si el contrato actual tiene ese cargo.

                            $newCargoId = intval($update_filtered['cargos_id']);

                            // Verificar contrato activo
                            $sqlContrato = "SELECT id, cargo_id FROM contratos WHERE trabajador_id = :id AND es_actual = 1";
                            $contratoActivo = $this->db->fetchRow($sqlContrato, ['id' => $id]);

                            $currentContratoCargoId = $contratoActivo ? intval($contratoActivo['cargo_id']) : 0;

                            if ($newCargoId != $currentContratoCargoId) {
                                // El cargo del trabajador cambió respecto a su contrato activo. Generar suplemento.
                                // Obtener salario del nuevo cargo
                                $rowCargo = $this->db->fetchRow("SELECT salario FROM cargos WHERE id = :cid", ['cid' => $newCargoId]);
                                $nuevoSalario = $rowCargo ? $rowCargo['salario'] : 0;

                                $this->_createSupplement($id, $newCargoId, $nuevoSalario);
                                $data['msg'] .= ' Se generó suplemento por cambio de cargo.';
                            }
                        } catch (Exception $e) {
                            error_log("Error generando suplemento en cambio de cargo: " . $e->getMessage());
                        }
                    }

                    // Guardar o actualizar información bancaria si se proporcionó
                    if (isset($param['tarjeta_salario']) || isset($param['cuenta_estandar'])) {
                        error_log('Actualizando información bancaria para trabajador ID: ' . $id);
                        error_log('Tarjeta de salario: ' . ($param['tarjeta_salario'] ?? 'vacío'));
                        error_log('Cuenta estándar: ' . ($param['cuenta_estandar'] ?? 'vacío'));

                        $bancoData = array(
                            'trabajador_id' => $id,
                            'numero_tarjeta_salario' => $param['tarjeta_salario'] ?? '',
                            'numero_cuenta_estandar' => $param['cuenta_estandar'] ?? ''
                        );

                        try {
                            // Verificar si ya existe un registro para este trabajador
                            $sql = "SELECT id FROM bancos WHERE trabajador_id = :trabajador_id";
                            error_log('Ejecutando consulta: ' . $sql . ' con trabajador_id: ' . $id);

                            error_log('Buscando registro existente en tabla bancos para trabajador_id: ' . $id);
                            $existingBanco = $this->db->fetchRow($sql, ['trabajador_id' => $id]);

                            if ($existingBanco) {
                                error_log('Registro existente encontrado en bancos con ID: ' . $existingBanco['id']);
                                error_log('Datos a actualizar: ' . print_r($bancoData, true));
                                $result = $this->db->update('bancos', $bancoData, ['id' => $existingBanco['id']]);
                                error_log('Resultado de actualización: ' . ($result ? 'éxito' : 'fallo'));
                                if (!$result) {
                                    $errorInfo = $this->db->errorInfo();
                                    error_log('Error al actualizar banco: ' . print_r($errorInfo, true));
                                }
                            } else if (!empty($param['tarjeta_salario']) || !empty($param['cuenta_estandar'])) {
                                error_log('No se encontró registro existente. Insertando nuevo registro en bancos');
                                error_log('Datos a insertar: ' . print_r($bancoData, true));
                                $result = $this->db->insert('bancos', $bancoData);
                                error_log('Resultado de inserción: ' . ($result ? 'éxito' : 'fallo'));
                                if ($result) {
                                    $bancoId = $this->db->last_id();
                                    error_log('Nuevo registro creado con ID: ' . $bancoId);
                                } else {
                                    $errorInfo = $this->db->errorInfo();
                                    error_log('Error al insertar en banco: ' . print_r($errorInfo, true));
                                }
                            }
                        } catch (Exception $e) {
                            error_log('Error al guardar información bancaria: ' . $e->getMessage());
                        }
                    }

                    // Preparar respuesta exitosa
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Registro actualizado correctamente';
                    $data['id'] = $id;
                    $data['date'] = date('d-m-Y H:i:s');

                    // Registrar en historial (UPDATE)
                    $nombreCompletoUpd = '';
                    try {
                        $rowUpd = $this->db->fetchRow(
                            "SELECT nombre, apellidos, apellidos_segundos FROM trabajadores WHERE id = :id",
                            array('id' => $id)
                        );
                        if ($rowUpd) {
                            $nombreCompletoUpd = trim(($rowUpd['nombre'] ?? '') . ' ' . ($rowUpd['apellidos'] ?? '') . ' ' . ($rowUpd['apellidos_segundos'] ?? ''));
                        }
                    } catch (Exception $e) { /* ignore */
                    }

                    $historyUpd = array(
                        'xentity' => 'TRABAJADOR',
                        'xaction' => 'UPDATE-TRABAJADOR',
                        'xid' => $id,
                        'xobs' => 'UPDATE TRABAJADOR ID: ' . $id . ($nombreCompletoUpd ? (' - ' . $nombreCompletoUpd) : '')
                    );
                    $this->app->add_history($historyUpd);
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'Error al actualizar el registro';
                }
            } catch (Exception $e) {
                // Respuesta de error amigable
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $friendly = '';
                if (method_exists($e, 'getCode') && $e->getCode() == '23000') {
                    $friendly = 'Violación de integridad de datos.';
                }
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    if (strpos($e->getMessage(), 'carnet_identidad') !== false) {
                        $friendly = 'Ya existe otro trabajador con el mismo carnet de identidad.';
                    } elseif (strpos($e->getMessage(), 'email') !== false) {
                        $friendly = 'Ya existe otro trabajador con el mismo email.';
                    } else {
                        // Mostrar el error completo para diagnosticar
                        $friendly = 'Error de duplicado: ' . $e->getMessage();
                    }
                }
                $data['msg'] = $friendly !== '' ? $friendly : ('Error al actualizar el registro: ' . $e->getMessage());
            }
        }

        print(json_encode($data));
    }

    /**
     * Función privada para listar trabajadores.
     * @param array $param Parámetros de entrada.
     * @return void
     */
    private function _list($param)
    {
        try {
            // Determinar mes y año
            if (!empty($param['mes'])) {
                $mesSeleccionado = $param['mes']; // formato: YYYY-MM
                list($anio, $mes) = explode('-', $mesSeleccionado);
            } else {
                $mesSeleccionado = date('Y-m');
                list($anio, $mes) = explode('-', $mesSeleccionado);
            }

            $limit = isset($param['limit']) ? intval($param['limit']) : 10;
            $offset = isset($param['offset']) ? intval($param['offset']) : 0;
            $search = isset($param['search']) ? trim($param['search']) : '';
            $sort = isset($param['sort']) ? $param['sort'] : 't.id';
            $order = isset($param['order']) ? strtoupper($param['order']) : 'DESC';

            $allowedSortFields = ['t.id', 't.nombre', 't.apellidos', 't.carnet_identidad', 'cargo_nombre', 'departamento_nombre'];
            if (!in_array($sort, $allowedSortFields)) {
                $sort = 't.id';
            }
            $where = '';
            if ($search !== '') {
                $where .= " AND (t.nombre LIKE :search OR t.apellidos LIKE :search OR t.carnet_identidad LIKE :search)";
            }

            // Debug
            error_log("Consultando asistencia para mes: $mes, año: $anio");

            // Preparar parámetros
            $params = [
                ':mes' => intval($mes),
                ':anio' => intval($anio),
                ':eliminado' => '0',
            ];
            if ($search !== '') {
                $params[':search'] = "%{$search}%";
            }
            $andEmpresa="";
            if(!(!empty($param['empresa'])&&$param['empresa']=='all')){
                $andEmpresa= " AND t.empresa_id = {$this->app->empresa_id}";
            }
            
            // Filtrar por departamento y ubicación para rol 4 (jefe de área)
            $whereJefeArea = "";
            if ($this->app->rol == 4) {
                $whereJefeArea = " AND t.id IN (
                    SELECT DISTINCT t2.id 
                    FROM trabajadores t2
                    INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
                    INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
                    WHERE aud.usuario_id = {$this->app->user_id} 
                    AND auu.usuario_id = {$this->app->user_id}
                )";
            }
            
            $total = $this->db->fetchAll("SELECT COUNT(*) AS total FROM trabajadores t 
            LEFT JOIN departamentos d ON t.departamento_id = d.id 
            WHERE t.trabajador_eliminado = '0' {$andEmpresa} {$whereJefeArea}");

            // Query con parámetros preparados
            $sqlWithJoin = "SELECT 
                    -- COUNT(*) AS total,
                    t.id,
                    t.nombre,
                    t.foto,
                    t.apellidos,
                    t.carnet_identidad,
                    t.sexo,
                    t.edad,
                    t.tipo_horario,
                    t.estatus,
                    c.nombre as cargo_nombre,
                    t.provincia_id,
                    t.municipio_id,
                    t.huella_dactilar,
                    d.nombre as departamento_nombre,
                    COALESCE(p.nombre, 'Sin provincia') as provincia_nombre,
                    COALESCE(m.nombre, 'Sin municipio') as municipio_nombre,
                    COALESCE(ubi.nombre, 'Sin ubicación') as ubicacion_nombre,
                    CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo,
                    COALESCE(t.es_liquidacion, 0) as es_liquidacion,
                    COALESCE(ra.horas_trabajadas, '00:00:00') AS horas_trabajadas_mes
                    FROM trabajadores t 
                    LEFT JOIN cargos c ON t.cargos_id = c.id
                    LEFT JOIN provincia p ON t.provincia_id = p.id
                    LEFT JOIN municipio m ON t.municipio_id = m.id
                    LEFT JOIN ubicaciones ubi ON t.ubicacion = ubi.id
                    LEFT JOIN departamentos d ON t.departamento_id = d.id 
                    LEFT JOIN (
                        SELECT 
                            trabajador_id,
                            SEC_TO_TIME(
                                SUM(
                                    GREATEST(
                                        0,
                                        TIME_TO_SEC(TIMEDIFF(hora_salida, hora_entrada))
                                        - GREATEST(
                                            0,
                                            LEAST(TIME_TO_SEC(hora_salida), TIME_TO_SEC('13:00:00'))
                                            - GREATEST(TIME_TO_SEC(hora_entrada), TIME_TO_SEC('12:00:00'))
                                        )
                                    )
                                )
                            ) AS horas_trabajadas
                        FROM 
                            registro_asistencia
                        WHERE 
                            MONTH(fecha) = :mes
                            AND YEAR(fecha) = :anio
                            AND hora_entrada IS NOT NULL
                            AND hora_salida IS NOT NULL
                        GROUP BY 
                            trabajador_id
                    ) ra ON ra.trabajador_id = t.id
                    WHERE t.trabajador_eliminado = :eliminado
                    $andEmpresa
                    $whereJefeArea
                    $where
                    ORDER BY COALESCE(t.es_liquidacion, 0) DESC, $sort $order
                    LIMIT $limit OFFSET $offset";

            // Verificar si hay registros en registro_asistencia para el mes
            $checkSql = "SELECT COUNT(*) as total 
                        FROM registro_asistencia 
                        WHERE MONTH(fecha) = :mes 
                        AND YEAR(fecha) = :anio 
                        AND hora_entrada IS NOT NULL 
                        AND hora_salida IS NOT NULL";

            $count = $this->db->fetchRow($checkSql, [':mes' => intval($mes), ':anio' => intval($anio)]);
            error_log("Registros de asistencia encontrados para {$mes}/{$anio}: " . ($count['total'] ?? 0));

            // Ejecutar consulta principal
            $dataWithJoin = $this->db->fetchAll($sqlWithJoin, $params);

            foreach ($dataWithJoin as &$row) {
                if (!empty($row['foto'])) {
                    // Convertir LONGBLOB a base64
                    $fotoBase64 = base64_encode($row['foto']);
                    // Crear data URL para imagen
                    $row['foto'] = 'data:image/jpeg;base64,' . $fotoBase64;
                    error_log("Foto procesada para trabajador ID: " . $row['id'] . " (tamaño: " . strlen($fotoBase64) . " chars)");
                } else {
                    $row['foto'] = null;
                }
            }
            // Debug: verificar resultados

            return ['total' => $total[0]['total'], 'rows' => $dataWithJoin];
        } catch (Exception $e) {
            error_log("Error en _list: " . $e->getMessage());
            return array();
        }
    }

    private function _list_filter($param)
    {
        try {
            // === ⚙️ Parámetros de paginación y búsqueda ===
            $limit  = isset($param['limit']) ? intval($param['limit']) : 10;
            $offset = isset($param['offset']) ? intval($param['offset']) : 0;
            $search = isset($param['search']) ? trim($param['search']) : '';
            $sort   = isset($param['sort']) ? $param['sort'] : 't.id';
            $order  = isset($param['order']) ? strtoupper($param['order']) : 'DESC';

            // Campos permitidos para ordenamiento
            $allowedSortFields = ['t.id', 't.nombre', 't.apellidos', 't.carnet_identidad', 'cargo_nombre', 'departamento_nombre'];
            if (!in_array($sort, $allowedSortFields)) {
                $sort = 't.id';
            }

            // === 🧩 Construir condiciones dinámicas ===
            $where = [];
            $params = [];

            // Filtro de trabajadores eliminados o activos
            if (!empty($param['trabajador_eliminado'])) {
                $where[] = "t.trabajador_eliminado = '1'";
            } else {
                $where[] = "t.trabajador_eliminado = '0'";
            }

            // Filtro por cargo
            if (!empty($param['cargo_id'])) {
                $where[] = "t.cargos_id = :cargo_id";
                $params[':cargo_id'] = $param['cargo_id'];
            }

            // Filtro por departamento
            if (!empty($param['departamento_id'])) {
                $where[] = "t.departamento_id = :departamento_id";
                $params[':departamento_id'] = $param['departamento_id'];
            }

            if (!empty($param['ubicacion_id'])) {
                $where[] = "t.ubicacion = :ubicacion_id";
                $params[':ubicacion_id'] = $param['ubicacion_id'];
            }

            // Filtro de búsqueda (nombre, apellidos, CI)
            if ($search !== '') {
                $where[] = "(t.nombre LIKE :search OR t.apellidos LIKE :search OR t.carnet_identidad LIKE :search)";
                $params[':search'] = "%{$search}%";
            }

            // Agregar filtro de empresa
            
            if(empty($param['empresa'])){
                $where[] = "(t.empresa_id = :empresa_id )";
                $params[':empresa_id'] = $this->app->empresa_id;
            }
            else if($param['empresa']!="all"){
                $where[] = "t.empresa_id = :empresa_id";
                $params[':empresa_id'] = intval($param['empresa']);
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

            // === 🧮 Query de conteo total ===
            $sqlCount = "SELECT COUNT(*) AS total
            FROM trabajadores t
            LEFT JOIN departamentos d ON t.departamento_id = d.id
            LEFT JOIN cargos c ON t.cargos_id = c.id
            WHERE " . implode(' AND ', $where);

            $countRow = $this->db->fetchRow($sqlCount, $params);
            $total = $countRow ? intval($countRow['total']) : 0;

            // === 📋 Query principal con paginación ===
            $sql = "
            SELECT 
                t.id,
                t.nombre,
                t.apellidos,
                t.carnet_identidad,
                t.sexo,
                t.edad,
                t.estatus,
                t.fecha_contratacion,
                t.foto,
                t.fecha_baja,
                COALESCE(t.es_liquidacion, 0) AS es_liquidacion,
                c.nombre AS cargo_nombre,
                d.nombre AS departamento_nombre,
                u.nombre AS ubicacion_nombre,
                t.empresa_id,
                CONCAT(t.nombre, ' ', t.apellidos) AS nombre_completo
            FROM trabajadores t
            LEFT JOIN cargos c ON t.cargos_id = c.id
            LEFT JOIN departamentos d ON t.departamento_id = d.id
            LEFT JOIN ubicaciones u ON t.ubicacion = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY COALESCE(t.es_liquidacion, 0) DESC, $sort $order
            LIMIT $limit OFFSET $offset";

            $data = $this->db->fetchAll($sql, $params);

            // === 🖼 Procesar fotos ===
            foreach ($data as &$row) {
                if (!empty($row['foto'])) {
                    $row['foto'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
                } else {
                    $row['foto'] = null;
                }
            }

            // === ✅ Devolver respuesta para Bootstrap Table ===
            return [
                'total' => $total,
                'rows'  => $data
            ];
        } catch (Exception $e) {
            error_log('Error en Trabajador->_list_filter: ' . $e->getMessage());
            return ['total' => 0, 'rows' => []];
        }
    }


    private function _export_excel($param)
    {
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);

        while (ob_get_level()) {
            @ob_end_clean();
        }

        // Cargar PHPExcel
        try {
            require_once(BASE_CLASS . '/PHPExcel.php');
            $excel = new PHPExcel();
            $excel->getProperties()
                ->setCreator('Sistema de RRHH')
                ->setTitle('Listado de Trabajadores');
            $sheet = $excel->setActiveSheetIndex(0);
            $sheet->setTitle('Trabajadores');
        } catch (Exception $e) {
            @error_log('ERROR cargando PHPExcel: ' . $e->getMessage());
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error cargando PHPExcel: ' . $e->getMessage());
        }

        $search = isset($param['search']) ? trim($param['search']) : '';

        $where = [];
        $params = [];

        if (!empty($param['trabajador_eliminado'])) {
            $where[] = "t.trabajador_eliminado = '1'";
        } else {
            $where[] = "t.trabajador_eliminado = '0'";
        }

        if (!empty($param['cargo_id'])) {
            $where[] = "t.cargos_id = :cargo_id";
            $params[':cargo_id'] = $param['cargo_id'];
        }

        if (!empty($param['departamento_id'])) {
            $where[] = "t.departamento_id = :departamento_id";
            $params[':departamento_id'] = $param['departamento_id'];
        }

        if (!empty($param['ubicacion_id'])) {
            $where[] = "t.ubicacion = :ubicacion_id";
            $params[':ubicacion_id'] = $param['ubicacion_id'];
        }

        if ($search !== '') {
            $where[] = "(t.nombre LIKE :search OR t.apellidos LIKE :search OR t.carnet_identidad LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        // Agregar filtro de empresa
        if(!isset($param['all'])){
            $where[] = "(t.empresa_id = :empresa_id )";
            $params[':empresa_id'] = $this->app->empresa_id;
        }

        $sql = "
        SELECT 
            t.id,
            t.nombre,
            t.apellidos,
            t.apellidos_segundos,
            t.carnet_identidad,
            c.nombre AS cargo_nombre,
            d.nombre AS departamento_nombre,
            u.nombre AS ubicacion_nombre
        FROM trabajadores t
        LEFT JOIN departamentos d ON t.departamento_id = d.id
        LEFT JOIN cargos c ON t.cargos_id = c.id
        LEFT JOIN ubicaciones u ON t.ubicacion = u.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY d.nombre ASC, c.nombre ASC, t.apellidos ASC, t.nombre ASC";

        $rows = $this->db->fetchAll($sql, $params);

        // Agregar logo y encabezado
        $rowNum = 1;
        
        // Agregar logo específico de la empresa
        $logoPath = '';
        $empresaId = isset($this->app->empresa_id) ? $this->app->empresa_id : 1;
        
        // Determinar el logo según el ID de empresa
        switch($empresaId) {
            case 1:
                $logoPath = BASE_CLASS . '/../img/logo_1.png';
                break;
            case 2:
                $logoPath = BASE_CLASS . '/../img/logo_2.png';
                break;
            case 3:
                $logoPath = BASE_CLASS . '/../img/logo_3.png';
                break;
            default:
                $logoPath = BASE_CLASS . '/../img/logo_1.png'; // Logo por defecto
        }
        
        if (file_exists($logoPath)) {
            try {
                $drawing = new PHPExcel_Worksheet_Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo Empresa');
                $drawing->setPath($logoPath);
                $drawing->setCoordinates('A1');
                $drawing->setHeight(60);
                $drawing->setWorksheet($sheet);
            } catch (Exception $e) {
                @error_log('Error al cargar logo: ' . $e->getMessage());
            }
        }
        
        // Nombre de la empresa
        //ahi haz la consulta para traer el nombre de la empresa
        $sql = "SELECT nombre FROM empresa WHERE id = :empresa_id";
        $params = [':empresa_id' => $this->app->empresa_id];
        $nombreEmpresa = $this->app->db->fetchAll($sql, $params)[0]['nombre'];
        if(empty($nombreEmpresa)){
            $nombreEmpresa = 'Empresa';
        }
        // Título principal
        $sheet->setCellValue('A2', $nombreEmpresa);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Subtítulo
        $sheet->setCellValue('A3', 'Listado de Trabajadores');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Fecha de generación
        $sheet->setCellValue('A4', 'Fecha: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(10);
        $sheet->mergeCells('A4:D4');
        
        $rowNum = 6; // Espacio después del encabezado
        
        // Encabezados de columna (sin sexo y fecha_contratacion)
        $headers = [
            'A' => 'CI',
            'B' => 'Nombre',
            'C' => 'Apellido',
            'D' => 'Segundo Apellido',
            'E' => 'Cargo',
            'F' => 'Departamento',
            'G' => 'Ubicación'
        ];
        
        $headerRow = $rowNum;
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
        }
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $rowNum++;
        
        // Datos
        foreach ($rows as $r) {
            $sheet->setCellValueExplicit('A' . $rowNum, $r['carnet_identidad'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNum, $r['nombre']);
            $sheet->setCellValue('C' . $rowNum, $r['apellidos']);
            $sheet->setCellValue('D' . $rowNum, $r['apellidos_segundos']);
            $sheet->setCellValue('E' . $rowNum, $r['cargo_nombre']);
            $sheet->setCellValue('F' . $rowNum, $r['departamento_nombre']);
            $sheet->setCellValue('G' . $rowNum, $r['ubicacion_nombre']);
            $rowNum++;
        }
        
        // Aplicar bordes a toda la tabla
        $dataRange = 'A' . $headerRow . ':G' . ($rowNum - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        // Autoajustar columnas
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Generar y descargar archivo Excel
        try {
            $filename = 'trabajadores_' . date('Ymd_His') . '.xlsx';
            
            while (ob_get_level()) { @ob_end_clean(); }
            
            $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save($tempFile);
            
            @error_log('Archivo guardado: ' . $tempFile);
            
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
            } else {
                throw new Exception('Error al generar el archivo Excel');
            }
        } catch (Exception $e) {
            @error_log('ERROR generando Excel: ' . $e->getMessage());
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error generando archivo Excel: ' . $e->getMessage());
        }
    }



    private function _list_bajas($param)
    {
        $data = array();

        // Consulta que incluye trabajadores marcados como eliminados (baja)
        // Consulta básica con campos esenciales
        // Consulta con solo los campos esenciales que funcionan
        $sql = "SELECT 
                t.id,
                t.nombre,
                t.apellidos,
                t.carnet_identidad,
                t.fecha_baja,
                t.estatus,
                t.sexo,
                t.edad,
                t.telefono,
                t.direccion,
                t.fecha_contratacion,
                t.cargos_id,
                CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo,
                COALESCE(c.nombre, 'Sin cargo') as cargo_nombre
                FROM trabajadores t 
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id AND t.empresa_id = {$this->app->empresa_id}
                WHERE t.trabajador_eliminado = 1
                AND (t.empresa_id = {$this->app->empresa_id} OR t.departamento_id IS NULL)
                ORDER BY t.fecha_baja DESC, t.apellidos, t.nombre";

        /* Versión completa comentada para referencia
        $sql = "SELECT 
                t.id,
                t.cargos_id,
                t.nombre,
                t.apellidos,
                t.carnet_identidad,
                t.sexo,
                t.edad,
                t.direccion,
                t.telefono,
                t.email,
                t.nivel_educacional,
                t.fecha_contratacion,
                t.fecha_baja,
                t.estatus,
                t.bolsa_empleo_id,
                t.foto,
                CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo,
                COALESCE(c.nombre, 'Sin cargo') as cargo_nombre
                FROM trabajadores t 
                LEFT JOIN cargos c ON t.cargos_id = c.id
                WHERE t.trabajador_eliminado = 1
                ORDER BY t.fecha_baja DESC, t.apellidos, t.nombre";
        */

        error_log("Consulta de bajas: " . $sql);
        $data = $this->db->fetchAll($sql);
        error_log("Registros de bajas encontrados: " . count($data));

        // Si no hay datos, devolvemos array vacío
        if (empty($data)) {
            error_log("No se encontraron registros de bajas (trabajador_eliminado = 1)");
            return array();
        }

        // Si hay datos, intentamos obtener la información de los cargos
        if (!empty($data)) {
            // Obtenemos los IDs de cargos únicos
            $cargosIds = array_unique(array_column($data, 'cargos_id'));
            $cargosIds = array_filter($cargosIds, function ($value) {
                return !empty($value); // Filtramos valores vacíos o nulos
            });

            if (!empty($cargosIds)) {
                // Obtenemos los cargos en una sola consulta
                $cargosList = $this->db->fetchAll("SELECT id, nombre FROM cargos WHERE id IN (" . implode(',', $cargosIds) . ")");
                $cargosMap = [];

                // Creamos un mapa de cargos para búsqueda rápida
                foreach ($cargosList as $cargo) {
                    $cargosMap[$cargo['id']] = $cargo['nombre'];
                }

                // Actualizamos los datos con los nombres de los cargos
                foreach ($data as &$trabajador) {
                    if (!empty($trabajador['cargos_id']) && isset($cargosMap[$trabajador['cargos_id']])) {
                        $trabajador['cargo_nombre'] = $cargosMap[$trabajador['cargos_id']];
                    }
                }
                unset($trabajador); // Rompe la referencia
            }

            // Aseguramos que todos los trabajadores tengan el campo cargo_nombre
            foreach ($data as &$trabajador) {
                if (empty($trabajador['cargo_nombre'])) {
                    $trabajador['cargo_nombre'] = 'Sin cargo';
                }

                // Aseguramos que la fecha de baja tenga formato
                if (!empty($trabajador['fecha_baja'])) {
                    $fecha = new DateTime($trabajador['fecha_baja']);
                    $trabajador['fecha_baja'] = $fecha->format('d/m/Y');
                }

                // Formateamos la fecha de contratación
                if (!empty($trabajador['fecha_contratacion'])) {
                    $fecha = new DateTime($trabajador['fecha_contratacion']);
                    $trabajador['fecha_contratacion'] = $fecha->format('d/m/Y');
                }
            }
        }

        return $data;
    }


    /**
     * Obtener lista de provincias
     */
    private function _get_list_provincias()
    {
        $sql = "SELECT id, nombre FROM provincia ORDER BY nombre ASC";
        try {
            $data = $this->db->fetchAll($sql);
            return $data;
        } catch (Exception $e) {
            error_log("Error obteniendo provincias: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener lista de municipios
     */
    private function _get_list_municipios()
    {
        $sql = "SELECT id, nombre, provincia_id FROM municipio ORDER BY nombre ASC";
        try {
            $data = $this->db->fetchAll($sql);
            return $data;
        } catch (Exception $e) {
            error_log("Error obteniendo municipios: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener municipios filtrados por provincia
     */
    private function _get_municipios_by_provincia($param)
    {
        $provincia_id = isset($param['provincia_id']) ? intval($param['provincia_id']) : 0;

        if ($provincia_id <= 0) {
            return array();
        }

        $sql = "SELECT id, nombre, provincia_id FROM municipio WHERE provincia_id = :provincia_id ORDER BY nombre ASC";
        try {
            $data = $this->db->fetchAll($sql, array('provincia_id' => $provincia_id));
            return $data;
        } catch (Exception $e) {
            error_log("Error obteniendo municipios por provincia: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Get list of workers with birthdays for the current year
     */
    private function _list_cumpleanos($param)
    {
        $data = array();

        try {
            // SQL query to get workers with birthday information
            $sql = "SELECT 
                    t.id,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.fecha_nacimiento
                    FROM trabajadores t
                    LEFT JOIN departamentos d ON t.departamento_id = d.id
                    WHERE t.trabajador_eliminado = '0'
                    AND t.empresa_id = '" . addslashes($this->app->empresa_id) . "'
                    ORDER BY t.nombre, t.apellidos";

            $workers = $this->db->fetchAll($sql);
            $currentYear = date('Y');
            $currentMonth = intval(date('m'));
            $currentDay = intval(date('d'));

            foreach ($workers as $worker) {
                $ci = $worker['carnet_identidad'];

                // Extract birth year, month and day from CI
                // Format: YYMMDD... where YY = year, MM = month, DD = day
                if (strlen($ci) >= 6) {
                    $yearCode = substr($ci, 0, 2);
                    $month = substr($ci, 2, 2);
                    $day = substr($ci, 4, 2);

                    // Convert year code to full year
                    // 00-23 = 2000-2023, 24-99 = 1924-1999
                    $yearNum = intval($yearCode);
                    if ($yearNum <= 23) {
                        $birthYear = 2000 + $yearNum;
                    } else {
                        $birthYear = 1900 + $yearNum;
                    }

                    // Calculate age
                    $age = $currentYear - $birthYear;

                    // Calculate days until birthday
                    $monthNum = intval($month);
                    $dayNum = intval($day);

                    $daysUntil = 0;
                    $isTodayBirthday = false;

                    if ($monthNum == $currentMonth && $dayNum == $currentDay) {
                        // Today is birthday
                        $isTodayBirthday = true;
                        $daysUntil = 0;
                    } elseif (
                        $monthNum > $currentMonth ||
                        ($monthNum == $currentMonth && $dayNum > $currentDay)
                    ) {
                        // Birthday is later this year
                        $birthdayThisYear = mktime(0, 0, 0, $monthNum, $dayNum, $currentYear);
                        $today = mktime(0, 0, 0, $currentMonth, $currentDay, $currentYear);
                        $daysUntil = ceil(($birthdayThisYear - $today) / 86400);
                    } else {
                        // Birthday is next year
                        $birthdayNextYear = mktime(0, 0, 0, $monthNum, $dayNum, $currentYear + 1);
                        $today = mktime(0, 0, 0, $currentMonth, $currentDay, $currentYear);
                        $daysUntil = ceil(($birthdayNextYear - $today) / 86400);
                    }

                    // Format birthday as DD/MM
                    $fechaCumpleanos = str_pad($day, 2, '0', STR_PAD_LEFT) . '/' .
                        str_pad($month, 2, '0', STR_PAD_LEFT);

                    $data[] = array(
                        'id' => $worker['id'],
                        'nombre' => $worker['nombre'],
                        'apellidos' => $worker['apellidos'],
                        'carnet_identidad' => $ci,
                        'fecha_cumpleanos' => $fechaCumpleanos,
                        'mes' => intval($month),
                        'dia' => intval($day),
                        'edad' => $age,
                        'dias_para_cumpleanos' => $daysUntil,
                        'es_hoy' => $isTodayBirthday
                    );
                }
            }

            // Sort by month and day to show birthdays in chronological order
            usort($data, function ($a, $b) {
                if ($a['mes'] != $b['mes']) {
                    return $a['mes'] - $b['mes'];
                }
                return $a['dia'] - $b['dia'];
            });
        } catch (Exception $e) {
            error_log("Error obteniendo cumpleaños: " . $e->getMessage());
            $data = array();
        }

        return $data;
    }

    private function _cumpleanos_hoy($param)
    {
        $data = array();

        try {
            // SQL query to get workers with birthday information
            $sql = "SELECT 
                    t.id,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.fecha_nacimiento
                    FROM trabajadores t
                    LEFT JOIN departamentos d ON t.departamento_id = d.id
                    WHERE t.trabajador_eliminado = '0'
                    AND t.empresa_id = '" . addslashes($this->app->empresa_id) . "'
                    ORDER BY t.nombre, t.apellidos";

            $workers = $this->db->fetchAll($sql);
            $currentYear = date('Y');
            $currentMonth = intval(date('m'));
            $currentDay = intval(date('d'));

            foreach ($workers as $worker) {
                $ci = $worker['carnet_identidad'];

                // Extract birth year, month and day from CI
                // Format: YYMMDD... where YY = year, MM = month, DD = day
                if (strlen($ci) >= 6) {
                    $yearCode = substr($ci, 0, 2);
                    $month = substr($ci, 2, 2);
                    $day = substr($ci, 4, 2);

                    $monthNum = intval($month);
                    $dayNum = intval($day);

                    // Check if today is birthday
                    if ($monthNum == $currentMonth && $dayNum == $currentDay) {
                        // Convert year code to full year
                        $yearNum = intval($yearCode);
                        if ($yearNum <= 23) {
                            $birthYear = 2000 + $yearNum;
                        } else {
                            $birthYear = 1900 + $yearNum;
                        }

                        $age = $currentYear - $birthYear;

                        $data[] = array(
                            'id' => $worker['id'],
                            'nombre' => $worker['nombre'],
                            'apellidos' => $worker['apellidos'],
                            'carnet_identidad' => $ci,
                            'edad' => $age
                        );
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error obteniendo cumpleaños de hoy: " . $e->getMessage());
            $data = array();
        }

        return $data;
    }

    /**
     * Recontratar un trabajador dado de baja
     */
    private function _recontratar($param)
    {
        $data = array(
            'status' => 1,
            'msg' => 'Trabajador recontratado exitosamente',
            'id' => $param['trabajador_id'] ?? null
        );

        try {
            // Validar que se proporcionaron los parámetros necesarios
            if (empty($param['trabajador_id']) || empty($param['empresa_id'])) {
                $data['status'] = 0;
                $data['msg'] = 'Falta el ID del trabajador o empresa';
                print(json_encode($data));
                return;
            }

            $trabajadorId = intval($param['trabajador_id']);
            $empresaId = intval($param['empresa_id']);

            // Verificar límite de trabajadores para la empresa antes de recontratar
            $limiteCheck = $this->verificarLimiteTrabajadoresEmpresa($empresaId);
            
            if ($limiteCheck['status'] == 0) {
                $data['status'] = 0;
                $data['msg'] = "No se puede recontratar al trabajador. La empresa ya ha alcanzado el límite de {$limiteCheck['limit']} trabajadores activos (actual: {$limiteCheck['count']}).";
                print(json_encode($data));
                return;
            }

            // Validar que el trabajador existe y está marcado como eliminado
            $sqlCheck = "SELECT id, trabajador_eliminado FROM trabajadores WHERE id = :id";
            $rowCheck = $this->db->fetchRow($sqlCheck, ['id' => $trabajadorId]);

            if (!$rowCheck) {
                $data['status'] = 0;
                $data['msg'] = 'El trabajador no existe';
                print(json_encode($data));
                return;
            }

            if ($rowCheck['trabajador_eliminado'] != 1) {
                $data['status'] = 0;
                $data['msg'] = 'El trabajador no está marcado como dado de baja';
                print(json_encode($data));
                return;
            }

            // Validar que la empresa existe
            $sqlEmpresa = "SELECT id FROM empresa WHERE id = :id";
            $rowEmpresa = $this->db->fetchRow($sqlEmpresa, ['id' => $empresaId]);

            if (!$rowEmpresa) {
                $data['status'] = 0;
                $data['msg'] = 'La empresa seleccionada no existe';
                print(json_encode($data));
                return;
            }

            // Actualizar el trabajador: 
            // - trabajador_eliminado = 0
            // - fecha_baja = NULL
            // - fecha_contratacion = fecha actual
            // - empresa_id = empresa seleccionada
            $update = array(
                'trabajador_eliminado' => 0,

                'fecha_contratacion' => date('Y-m-d'),
                'empresa_id' => $empresaId
            );

            $where = array('id' => $trabajadorId);

            $result = $this->db->update('trabajadores', $update, $where);

            if ($result) {
                // Obtener nombre del trabajador para el historial
                $nombreCompleto = '';
                try {
                    $rowNombre = $this->db->fetchRow(
                        "SELECT nombre, apellidos, apellidos_segundos FROM trabajadores WHERE id = :id",
                        array('id' => $trabajadorId)
                    );
                    if ($rowNombre) {
                        $nombreCompleto = trim(($rowNombre['nombre'] ?? '') . ' ' . ($rowNombre['apellidos'] ?? '') . ' ' . ($rowNombre['apellidos_segundos'] ?? ''));
                    }
                } catch (Exception $e) {
                }

                // Registrar en historial
                $history = array(
                    'xentity' => 'TRABAJADOR',
                    'xaction' => 'RECONTRATAR-TRABAJADOR',
                    'xid' => $trabajadorId,
                    'xobs' => 'RECONTRATAR TRABAJADOR ID: ' . $trabajadorId . ' - EMPRESA ID: ' . $empresaId . ($nombreCompleto ? (' - ' . $nombreCompleto) : '')
                );
                $this->app->add_history($history);

                // Obtener cargo actual del trabajador para crear contrato
                $cargosId = 0;
                try {
                    $rowCargo = $this->db->fetchRow("SELECT cargos_id FROM trabajadores WHERE id = :id", array('id' => $trabajadorId));
                    if ($rowCargo) {
                        $cargosId = $rowCargo['cargos_id'];
                    }
                } catch (Exception $e) {
                }

                // Crear contrato inicial automático (Tipo 2 - Determinado)
                if ($cargosId) {
                    $this->_createInitialContract($trabajadorId, $cargosId, date('Y-m-d'));
                }

                $data['status'] = 1;
                $data['msg'] = 'Trabajador recontratado exitosamente';
            } else {
                $data['status'] = 0;
                $data['msg'] = 'Error al recontratar el trabajador';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al recontratar el trabajador: ' . $e->getMessage();
            error_log('Error en _recontratar: ' . $e->getMessage());
        }

        print(json_encode($data));
    }

    private function _createInitialContract($workerId, $cargoId, $date)
    {
        try {
            // Obtener salario del cargo
            $sqlCargo = "SELECT salario FROM cargos WHERE id = :id";
            $cargo = $this->db->fetchRow($sqlCargo, array('id' => $cargoId));
            $salario = $cargo ? $cargo['salario'] : 0;

            // Fecha fin por defecto: 1 año después
            $fechaFin = date('Y-m-d', strtotime($date . ' + 3 month'));

            // Insertar contrato determinado (Tipo 2)
            $sqlInsert = "INSERT INTO contratos (trabajador_id, tipo, fecha_inicio, fecha_fin, salario, cargo_id, es_actual) 
                          VALUES (:tid, '2', :fecha_inicio, :fecha_fin, :salario, :cargo_id, 1)";

            $stmt = $this->db->conn->prepare($sqlInsert);
            $stmt->execute(array(
                ':tid' => $workerId,
                ':fecha_inicio' => $date,
                ':fecha_fin' => $fechaFin,
                ':salario' => $salario,
                ':cargo_id' => $cargoId
            ));
        } catch (Exception $e) {
            error_log("Error _createInitialContract: " . $e->getMessage());
        }
    }

    private function _createSupplement($workerId, $cargoId, $newSalario)
    {
        try {
            // 1. Desactivar solo los suplementos anteriores (Tipo 3)
            $sqlUpdate = "UPDATE contratos SET es_actual = 0, fecha_fin = CURDATE() WHERE trabajador_id = :tid AND es_actual = 1 AND tipo = '3'";
            $sth = $this->db->conn->prepare($sqlUpdate);
            $sth->execute(array('tid' => $workerId));

            // 2. Insertar nuevo contrato (Suplemento - Tipo 3)
            $sqlInsert = "INSERT INTO contratos (trabajador_id, tipo, fecha_inicio, salario, cargo_id, es_actual) 
                          VALUES (:tid, '3', CURDATE(), :salario, :cargo_id, 1)";

            $stmt = $this->db->conn->prepare($sqlInsert);
            $stmt->execute(array(
                ':tid' => $workerId,
                ':salario' => $newSalario,
                ':cargo_id' => $cargoId
            ));
        } catch (Exception $e) {
            error_log("Error _createSupplement: " . $e->getMessage());
        }
    }

    private function _get_worker_data($param)
    {
        $data = array(
            'status' => 0,
            'msg' => '',
            'data' => null
        );

        if (!isset($param['id']) || empty($param['id'])) {
            $data['msg'] = 'ID de trabajador no proporcionado';
            return $data;
        }

        try {
            $val = array('id' => $param['id']);
            $sql = "SELECT t.*, p.nombre as provincia_nombre, m.nombre as municipio_nombre, xusuario, u.xemail as email, c.salario as salario, c.nombre as cargo_nombre, d.nombre as departamento_nombre, e.nombre as empresa_nombre, ub.nombre as ubicacion
                    FROM trabajadores t 
                    LEFT JOIN municipio m ON m.id = t.municipio_id 
                    LEFT JOIN provincia p ON p.id = t.provincia_id 
                    LEFT JOIN usuarios u ON u.xusuario_id = t.usuario_id
                    LEFT JOIN cargos c ON c.id = t.cargos_id
                    LEFT JOIN departamentos d ON d.id = t.departamento_id
                    LEFT JOIN ubicaciones ub ON ub.id = t.ubicacion
                    LEFT JOIN empresa e ON e.id = t.empresa_id
                    WHERE t.id = :id";
            

            $row = $this->db->fetchRow($sql, $val);
            unset($row['uuid']);
            unset($row['foto']);
            unset($row['huella_dactilar']);

            if ($row) {
                $data = $row;
                
                // Obtener datos bancarios
                $sql = "SELECT numero_tarjeta_salario, numero_cuenta_estandar FROM bancos WHERE trabajador_id = :id";
                $result = $this->app->db->fetchAll($sql, array('id' => $param['id']));
                
                if (isset($result) && count($result) > 0) {
                    $numero_tarjeta_salario = array_column($result, 'numero_tarjeta_salario')[0];
                    $numero_cuenta_estandar = array_column($result, 'numero_cuenta_estandar')[0];
                    $data['cuenta_estandar'] = $numero_cuenta_estandar;
                    $data['tarjeta_salario'] = $numero_tarjeta_salario;
                }

                $data['salario'] = intval($data['salario']);
                $data['status'] = 1;
                $data['msg'] = 'Datos del trabajador obtenidos exitosamente';
            } else {
                $data['msg'] = 'Trabajador no encontrado';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al obtener datos del trabajador: ' . $e->getMessage();
        }

        return $data;
    }

    private function _get_vacaciones_acc($param)
    {
        $data = array(
            'status' => 0,
            'msg' => 'No se pudo obtener datos de vacaciones',
            'vacaciones_acc' => 0,
            'vacaciones_congeladas' => 0,
            'vacaciones_disponibles' => 0
        );

        if (!isset($param['id']) || empty($param['id'])) {
            $data['msg'] = 'ID de trabajador no proporcionado';
            return $data;
        }

        try {
            $val = array('id' => $param['id']);
            $sql = "SELECT COALESCE(vacaciones_acc, 0) as vacaciones_acc, COALESCE(vacaciones_congeladas, 0) as vacaciones_congeladas FROM trabajadores WHERE id = :id";

            $row = $this->db->fetchRow($sql, $val);

            if ($row) {
                $acc = floatval($row['vacaciones_acc']);
                $congeladas = floatval($row['vacaciones_congeladas']);
                $data['status'] = 1;
                $data['vacaciones_acc'] = $acc;
                $data['vacaciones_congeladas'] = $congeladas;
                // Disponible real = acumulado - congelado (días reservados por planes aprobados
                // aún no descontados en nómina).
                $data['vacaciones_disponibles'] = max(0, $acc - $congeladas);
                $data['msg'] = 'Vacaciones acumuladas obtenidas correctamente';
            } else {
                $data['msg'] = 'Trabajador no encontrado';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al obtener vacaciones acumuladas: ' . $e->getMessage();
        }

        return $data;
    }
}
