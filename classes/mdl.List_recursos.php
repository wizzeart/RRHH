<?php
// Incluir TCPDF


class List_recursos {
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
            case 'checked':
                // $this->_checked($param);
                break;
            case 'list-id':
                $data = $this->_list_id($param);
                print(json_encode($data));
                break;
            case 'get':
                $data = $this->_get($param);
                print(json_encode($data));
                break;
            case 'del':
                $this->_del($param);
                break;
            case 'del_registro':
                $this->_del_registro($param);
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'get_recursos':
                $data = $this->_get_recursos($param);
                print(json_encode($data));
                break;
            case 'create_recurso':
                $data = $this->_create_recurso($param);
                print(json_encode($data));
                break;
            case 'update_recurso':
                $data = $this->_update_recurso($param);
                print(json_encode($data));
                break;
            case 'create_categoria':
                $data = $this->_create_categoria($param);
                print(json_encode($data));
                break;
            case 'list_categorias':
                $data = $this->_list_categoria($param);
                print(json_encode($data));
                break;
            case 'export_asignados':
                $this->_export_asignados($param);
                break;
        }
    }
    private function _get($param)
    {
        $data = array();
        $sql = "SELECT r.* FROM recursos r WHERE r.id = :id";
        $params = array(
            'id' => $param['id']
        );
        $data = $this->db->fetchAll($sql, $params);
        return $data;
    }


    private function _list_id($param)
    {
        $data = array();

        // Construir la consulta base
        $sql = "SELECT rt.*,r.nombre as nombre, r.descripcion as descripcion, cr.nombre as categoria, t.foto
                FROM recursos_trabajadores rt
                LEFT JOIN trabajadores t ON rt.trabajador_id = t.id
                LEFT JOIN recursos r ON rt.recurso_id = r.id
                LEFT JOIN categorias_recurso cr ON r.categoria_id = cr.id
                WHERE rt.trabajador_id = :trabajador_id";

        $params = array(
            'trabajador_id' => $param['trabajador_id']
        );

        // Filtrar por estado si se proporciona
        if (isset($_GET['estado']) && ($_GET['estado'] === '0' || $_GET['estado'] === '1')) {
            $sql .= " WHERE rt.estado = ?";
            $params[] = (int)$_GET['estado'];
        }

        // Ordenar según corresponda
        if (isset($_GET['estado']) && $_GET['estado'] === '0') {
            $sql .= " ORDER BY rt.fecha_entrega_a_rh DESC, rt.id DESC";
        } else {
            $sql .= " ORDER BY rt.fecha_entrega_a_t DESC, rt.id DESC";
        }

        // Ejecutar la consulta
        if (!empty($params)) {
            $data = $this->db->fetchAll($sql, $params);
        } else {
            $data = $this->db->fetchAll($sql);
        }

        // Procesar fotos para convertirlas a base64
        foreach ($data as &$row) {
            if (!empty($row['foto'])) {
                // Convertir LONGBLOB a base64
                $fotoBase64 = base64_encode($row['foto']);
                // Crear data URL para imagen
                $row['foto'] = 'data:image/jpeg;base64,' . $fotoBase64;
            } else {
                $row['foto'] = null;
            }
        }

        return $data;
    }

    private function _del($param)
    {
        $resp = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => isset($param['row']) ? $param['row'] : null,
            'msg' => ''
        );

        $recurso_id = isset($param['id']) ? (int)$param['id'] : 0;
        if ($recurso_id <= 0) {
            $resp['status'] = 0;
            $resp['msg'] = 'ID de recurso inválido';
            print(json_encode($resp));
            return;
        }

        // Bloquear eliminación si el recurso está asignado actualmente (activo)
        // Activo = estado=1 Y sin fecha de devolución
        $sqlCheck = "SELECT COUNT(*) AS c FROM recursos_trabajadores WHERE recurso_id = :id AND estado = 1 AND (fecha_entrega_a_rh IS NULL OR fecha_entrega_a_rh = '' OR fecha_entrega_a_rh = '0000-00-00')";
        $row = $this->app->db->fetchRow($sqlCheck, array('id' => $recurso_id));
        $assigned = $row && isset($row['c']) ? (int)$row['c'] : 0;
        if ($assigned > 0) {
            $resp['status'] = 0;
            $resp['msg'] = 'No se puede eliminar: el recurso está asignado actualmente.';
            print(json_encode($resp));
            return;
        }

        // Eliminar solo el recurso (no el historial de asignaciones)
        $this->app->db->del('recursos', array('id' => $recurso_id));

        $history = array(
            'xentity' => 'RECURSOS',
            'xaction' => 'DEL-RECURSO',
            'xobs' => 'DEL RECURSO: ' . $recurso_id
        );
        $this->app->add_history($history);

        print(json_encode($resp));
    }

    private function _del_registro($param)
    {
        $resp = array(
            'status' => 1,
            'id' => isset($param['id']) ? (int)$param['id'] : 0,
            'msg' => ''
        );

        $registro_id = $resp['id'];
        if ($registro_id <= 0) {
            $resp['status'] = 0;
            $resp['msg'] = 'ID de registro inválido';
            print(json_encode($resp));
            return;
        }

        // Verificar que el registro exista y no esté activo (estado=0) o tenga fecha de retorno
        $sql = "SELECT id, recurso_id, estado, fecha_entrega_a_rh FROM recursos_trabajadores WHERE id = :id";
        $row = $this->app->db->fetchRow($sql, array('id' => $registro_id));
        if (!$row) {
            $resp['status'] = 0;
            $resp['msg'] = 'Registro no encontrado';
            print(json_encode($resp));
            return;
        }

        $activo = (int)$row['estado'] === 1 && (empty($row['fecha_entrega_a_rh']) || $row['fecha_entrega_a_rh'] == '0000-00-00');
        if ($activo) {
            $resp['status'] = 0;
            $resp['msg'] = 'No se puede eliminar: el registro está activo';
            print(json_encode($resp));
            return;
        }

        // Eliminar el registro del historial de asignación
        $this->app->db->del('recursos_trabajadores', array('id' => $registro_id));

        // Registrar en historial
        $history = array(
            'xentity' => 'RECURSOS',
            'xaction' => 'DEL-REGISTRO',
            'xobs' => 'DEL REGISTRO RECURSO_TRABAJADORES: ' . $registro_id
        );
        $this->app->add_history($history);

        print(json_encode($resp));
    }

    private function generar_acta_entrega($param)
    {

        // Verificar que se reciba el ID del recurso
        if (!isset($param['id']) || !is_numeric($param['id'])) {
            die('ID de recurso no válido');
        }

        $id_recurso = intval($param['id']);
        // Obtener datos del recurso y del trabajador
        $sql = "SELECT r.*, t.nombre as nombre_trabajador, t.apellidos, t.apellidos_segundos, t.cargos_id, c.nombre as cargo_nombre 
        FROM recursos r 
        LEFT JOIN trabajadores t ON r.trabajador_id = t.id 
        LEFT JOIN cargos c ON t.cargos_id = c.id 
        WHERE r.id = :id";
        $recurso = $this->app->db->fetchAll($sql, array(
            'id'=>$id_recurso
        ));

        if (!$recurso) {
            die('Recurso no encontrado');
        }

        // Obtener el contenido del HTML del acta usando ruta absoluta
        $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/docs/acta_de_entrega/Acta_de_Entrega.html';
        $html = file_get_contents($ruta_absoluta);
        
        if ($html === false) {
            die('No se pudo leer el archivo del acta. Ruta intentada: ' . $ruta_absoluta);
        }

        // Reemplazar los marcadores de posición con los datos reales
        $fecha_actual = new DateTime();
        $meses = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ];


        
        $reemplazos = [
            'id="dias"></span>' => 'id="dias"><b><u>' . $fecha_actual->format('d') . '</u></b></span>',
            'id="mes"></span>' => 'id="mes"><b><u>' . $meses[intval($fecha_actual->format('m'))] . '</u></b></span>',
            'id="ano"></span>' => 'id="ano"><b><u>' . $fecha_actual->format('Y') . '</u></b></span>',
            'id="trabajador_nombre"></span>' => 'id="trabajador_nombre"><b><u>' . htmlspecialchars($recurso[0]['nombre_trabajador'] . ' ' . $recurso[0]['apellidos'] . ' ' . $recurso[0]['apellidos_segundos']) . '</u></b></span>',
            'id="recurso"></span>' => 'id="recurso"><b><u>' . htmlspecialchars($recurso[0]['nombre'] ?? '') . '</u></b></span>',
            'id="marca"></span>' => 'id="marca"><b><u>' . htmlspecialchars($recurso[0]['marca'] ?? '') . '</u></b></span>',
            'id="modelo"></span>' => 'id="modelo"><b><u>' . htmlspecialchars($recurso[0]['modelo'] ?? '') . '</u></b></span>',
            'id="color"></span>' => 'id="color"><b><u>' . htmlspecialchars($recurso[0]['color'] ?? '') . '</u></b></span>',
            'id="cargo_nombre"></span>' => 'id="cargo_nombre"><b><u>' . htmlspecialchars($recurso[0]['cargo_nombre'] ?? '') . '</u></b></span>'
        ];
        if(isset($recurso[0]['otros_recursos']) && !empty($recurso[0]['otros_recursos'])) {
            $reemplazos['id="otros_recursos"></span>'] = 'id="otros_recursos"> con <b><u>' . htmlspecialchars($recurso[0]['otros_recursos'] ?? '') . '</u></b></span>';
        }

        foreach ($reemplazos as $buscar => $reemplazo) {
            $html = str_replace($buscar, $reemplazo, $html);
        }


        $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/plugins/tcpdf/tcpdf.php';
        
        require_once($ruta_absoluta);
        // Crear nuevo documento PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuración del documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('IML Servicios');
        $pdf->SetTitle('Acta de Entrega - ' . $recurso[0]['nombre']);
        $pdf->SetSubject('Acta de Entrega');
        $pdf->SetKeywords('acta, entrega, recurso, IML');

        // Eliminar cabecera y pie de página por defecto
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Añadir una página
        $pdf->AddPage();

        // Escribir el contenido HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Crear directorio si no existe
        $directorio = $_SERVER['DOCUMENT_ROOT'] . '/docs/actas_guardadas/';
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }
        
        // Generar nombre de archivo único
        $nombre_archivo = 'acta_entrega_' . $recurso[0]['id'] . '.pdf';
        $ruta_completa = $directorio . $nombre_archivo;
        
        // Guardar en el servidor
        $pdf->Output($ruta_completa, 'F');
        
    }

    /**
     * Texto del destino de una asignación, para el historial.
     * El recurso puede estar asignado a un trabajador o a un objeto definido a mano.
     */
    private function _destino_texto($row)
    {
        $tipo = isset($row['tipo_asignacion']) ? $row['tipo_asignacion'] : 'trabajador';
        if ($tipo === 'objeto') {
            return 'OBJETO: ' . (isset($row['asignado_a']) ? $row['asignado_a'] : '');
        }
        return 'TRABAJADOR: ' . (isset($row['trabajador_id']) ? $row['trabajador_id'] : '');
    }

    private function _save($param)
    {
        // Debug: Guardar los parámetros recibidos en un archivo de log
        $log = date('Y-m-d H:i:s') . " - Parámetros recibidos:\n";
        $log .= "POST: " . print_r($param, true) . "\n";
        $log .= "FILES: " . print_r($_FILES, true) . "\n";
        $log .= "Action: " . (isset($param['action']) ? $param['action'] : 'NO ACTION') . "\n";
        file_put_contents('debug_recursos.log', $log, FILE_APPEND);

        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );


        // El recurso siempre es obligatorio
        if (!isset($param['recurso_id']) || trim($param['recurso_id']) === '') {
            $data['status'] = 0;
            $data['msg'] = "El campo Recurso es obligatorio";
            print(json_encode($data));
            return;
        }

        // Un recurso solo puede estar asignado a un destino a la vez (hay dos
        // puntos de entrada: el listado de recursos y la ficha del trabajador).
        if ($data['action'] == 'insert') {
            $ocupado = $this->db->fetchRow(
                "SELECT COUNT(*) AS c FROM recursos_trabajadores
                  WHERE recurso_id = :id AND estado = 1
                    AND (fecha_entrega_a_rh IS NULL OR fecha_entrega_a_rh = '' OR fecha_entrega_a_rh = '0000-00-00')",
                array('id' => (int)$param['recurso_id'])
            );
            if ($ocupado && (int)$ocupado['c'] > 0) {
                $data['status'] = 0;
                $data['msg'] = 'El recurso ya está asignado. Regístre primero su devolución.';
                print(json_encode($data));
                return;
            }
        }

        // Destino de la asignación: un trabajador o un objeto definido a mano
        // (por ejemplo una línea asignada a un GPS).
        $tipo_asignacion = isset($param['tipo_asignacion']) && trim($param['tipo_asignacion']) !== ''
            ? strtolower(trim($param['tipo_asignacion']))
            : '';
        $asignado_a = isset($param['asignado_a']) ? trim($param['asignado_a']) : '';
        $trabajador_id = isset($param['trabajador_id']) ? trim($param['trabajador_id']) : '';

        // En las actualizaciones (retorno del recurso, edición de fechas) el
        // cliente no reenvía siempre el destino: se recupera del registro.
        if ((isset($param['action']) && $param['action'] == 'update') && isset($param['id'])) {
            $actual = $this->db->fetchRow(
                "SELECT trabajador_id, tipo_asignacion, asignado_a FROM recursos_trabajadores WHERE id = :id",
                array('id' => (int)$param['id'])
            );
            if ($actual) {
                if ($trabajador_id === '' && $asignado_a === '') {
                    $trabajador_id = $actual['trabajador_id'];
                    $asignado_a = $actual['asignado_a'];
                    if ($tipo_asignacion === '') {
                        $tipo_asignacion = $actual['tipo_asignacion'];
                    }
                }
            }
        }

        // Si no llega el tipo explícito, se deduce de los datos recibidos para
        // no romper las llamadas antiguas (ficha del trabajador).
        if ($tipo_asignacion !== 'trabajador' && $tipo_asignacion !== 'objeto') {
            $tipo_asignacion = (($trabajador_id === '' || $trabajador_id === null) && $asignado_a !== '' && $asignado_a !== null) ? 'objeto' : 'trabajador';
        }

        if ($tipo_asignacion === 'objeto') {
            if ($asignado_a === '' || $asignado_a === null) {
                $data['status'] = 0;
                $data['msg'] = 'Debe indicar a qué objeto se asigna el recurso';
                print(json_encode($data));
                return;
            }
            $trabajador_id = null;
        } else {
            if ($trabajador_id === '' || $trabajador_id === null) {
                $data['status'] = 0;
                $data['msg'] = 'El campo Trabajador es obligatorio';
                print(json_encode($data));
                return;
            }
            $asignado_a = null;
        }

        $insert = array(
            'trabajador_id' => $trabajador_id,
            'tipo_asignacion' => $tipo_asignacion,
            'asignado_a' => $asignado_a,
            'fecha_entrega_a_t' => isset($param['fecha_entrega_a_t']) && !empty($param['fecha_entrega_a_t']) ? $param['fecha_entrega_a_t'] : date('Y-m-d'),
            'recurso_id' => $param['recurso_id'],
            //'fecha_entrega_a_rh' => $param['fecha_entrega_a_rh']
        );

        if(isset($param['action'])) {
            $data['action'] = $param['action'];
        }

        // Remover campos que no pertenecen a la tabla bolsa_empleo
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        if ($data['action'] == 'insert') {
            // Asegurar que solo se insertan los campos que existen en la tabla
            $campos_validos = [
                'trabajador_id',
                'tipo_asignacion',
                'asignado_a',
                'fecha_entrega_a_t',
                'recurso_id'
            ];

            // Filtrar solo los campos válidos
            $insert_filtered = array_intersect_key($insert, array_flip($campos_validos));

            // Debug: Guardar la consulta de inserción
            $log = date('Y-m-d H:i:s') . " - Intentando insertar:\n";
            $log .= print_r($insert_filtered, true) . "\n";
            file_put_contents('debug_recursos.log', $log, FILE_APPEND);

            try {
                // Insertar la postulación usando solo los campos filtrados
                $result = $this->app->db->insert('recursos_trabajadores', $insert_filtered);
                if ($result) {
                    // Obtener el último ID insertado
                    $lastId = $this->app->db->last_id();
                    $param['id'] = $lastId;
                    //$this->generar_acta_entrega($param);

                    if ($lastId) {
                        // Preparar respuesta exitosa
                        $data['msg_title'] = 'Operación exitosa';
                        $data['msg'] = 'Registro insertado correctamente';
                        $data['id'] = $lastId;
                        $data['date'] = date('Y-m-d H:i:s');

                        // Registrar en historial
                        $history = array(
                            'xentity' => 'RECURSOS',
                            'xaction' => 'INSERT-RECURSO',
                            //'id' => $lastId,
                            'xobs' => 'RECURSO: ' . $lastId . ' ' . $this->_destino_texto($insert) . ' ' . $insert['recurso_id']
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
                //update recursos
                $update = array(
                    'disponible' => 0
                );
                $where = array(
                    'id' => $insert_filtered['recurso_id']
                );
                $this->app->db->update('recursos', $update, $where);
            } catch (Exception $e) {
                // Log del error
                $errorLog = date('Y-m-d H:i:s') . " - Error al insertar:\n";
                $errorLog .= $e->getMessage() . "\n";
                file_put_contents('debug_recursos.log', $errorLog, FILE_APPEND);

                // Respuesta de error
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $data['msg'] = 'Error al insertar el registro: ' . $e->getMessage();
            }
        } else {
            // Update existente
            if (!isset($param['id'])) {
                $data['status'] = 0;
                $data['msg'] = 'ID de postulación no proporcionado';
                print(json_encode($data));
                return;
            }

            try {
                $id = $param['id'];
                

                // Filtrar campos válidos para actualización
                $campos_validos = [
                    'trabajador_id',
                    'tipo_asignacion',
                    'asignado_a',
                    'fecha_entrega_a_t',
                    'recurso_id',
                    'fecha_entrega_a_rh',
                    //'otros_recursos'
                ];

                // Filtrar solo los campos válidos
                $update_filtered = array_intersect_key($insert, array_flip($campos_validos));

                if (isset($param['fecha_entrega_a_rh'])&&$param['fecha_entrega_a_rh'] != '') {
                    $update_filtered['estado'] = 0;
                    $update_filtered['fecha_entrega_a_rh'] = $param['fecha_entrega_a_rh'];
                }
                else{
                    $update_filtered['estado'] = 1;
                }

                if(isset($param['otros_recursos'])) {
                    $update_filtered['otros_recursos'] = $param['otros_recursos'];
                }

                // Debug log before update
                $log = date('Y-m-d H:i:s') . " - Intentando actualizar postulación ID: " . $id . "\n";
                $log .= print_r($update_filtered, true) . "\n";
                file_put_contents('debug_recursos.log', $log, FILE_APPEND);

                // Actualizar la postulación
                $where = array('id' => $id);
                $result = $this->app->db->update('recursos_trabajadores', $update_filtered, $where);
                // Actualizar disponibilidad del recurso según si hay devolución
                $disp = (isset($param['fecha_entrega_a_rh']) && $param['fecha_entrega_a_rh'] != '') ? 1 : 0;
                $update = array(
                    'disponible' => $disp
                );
                $where = array(
                    'id' => $update_filtered['recurso_id']
                );
                $this->app->db->update('recursos', $update, $where);

                if ($result) {
                    //$this->generar_acta_entrega($param);
                    // Preparar respuesta exitosa
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Registro actualizado correctamente';
                    $data['id'] = $id;
                    $data['date'] = date('d-m-Y H:i:s');

                    // Determinar si es un retorno de recurso
                    if (isset($param['fecha_entrega_a_rh']) && $param['fecha_entrega_a_rh'] != '') {
                        // Es un retorno de recurso
                        $history = array(
                            'xentity' => 'RECURSOS',
                            'xaction' => 'RETORNO-RECURSO',
                            'xobs' => 'RECURSO RETORNADO ID: ' . $update_filtered['recurso_id'] . ' ' . $this->_destino_texto($update_filtered) . ' FECHA: ' . $param['fecha_entrega_a_rh']
                        );
                        $this->app->add_history($history);
                    } else {
                        // Es una actualización normal
                        $history = array(
                            'xentity' => 'RECURSOS',
                            'xaction' => 'UPDATE-RECURSO',
                            'xobs' => 'RECURSO: ' . $id . ' ' . $this->_destino_texto($update_filtered) . ' RECURSO: ' . $update_filtered['recurso_id']
                        );
                        $this->app->add_history($history);
                    }
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'Error al actualizar el registro';
                }
            } catch (Exception $e) {
                // Log del error
                $errorLog = date('Y-m-d H:i:s') . " - Error al actualizar:\n";
                $errorLog .= $e->getMessage() . "\n";
                file_put_contents('debug_recursos.log', $errorLog, FILE_APPEND);

                // Respuesta de error
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $data['msg'] = 'Error al actualizar el registro: ' . $e->getMessage();
            }
        }

        print(json_encode($data));
    }

    

    public function _get_recursos($param, $incluir_foto = true)
    {
        $data = array();

        // Construir la consulta base con la información del destino asignado
        $sql = "SELECT r.*,
                        cr.nombre as categoria,
                        t.id as trabajador_id,"
                 . ($incluir_foto ? " t.foto," : "") . "
                        CONCAT(t.nombre, ' ', t.apellidos, ' ', COALESCE(t.apellidos_segundos, '')) as trabajador_nombre,
                        rt.id as asignacion_id,
                        rt.tipo_asignacion,
                        rt.asignado_a,
                        COALESCE(NULLIF(CONCAT(t.nombre, ' ', t.apellidos, ' ', COALESCE(t.apellidos_segundos, '')), '  '), rt.asignado_a) as asignado_nombre,
                        rt.estado as asignacion_estado,
                        rt.fecha_entrega_a_t as fecha_asignacion,
                        rt.fecha_entrega_a_t,
                        rt.fecha_entrega_a_rh
                 FROM recursos r
                 LEFT JOIN categorias_recurso cr ON r.categoria_id = cr.id
                 LEFT JOIN recursos_trabajadores rt ON r.id = rt.recurso_id AND rt.estado = 1
                 LEFT JOIN trabajadores t ON rt.trabajador_id = t.id";
        $params = array();
        $where = array();

        // Agregar filtro por disponibilidad si se proporciona
        if (isset($param['disponible']) && $param['disponible'] !== '') {
            $where[] = "r.disponible = :disponible";
            $params['disponible'] = $param['disponible'];
        }

        // Agregar filtro por categoría si se proporciona
        if (isset($param['categoria_recurso_id']) && $param['categoria_recurso_id'] !== '') {
            $where[] = "r.categoria_id = :categoria_recurso_id";
            $params['categoria_recurso_id'] = $param['categoria_recurso_id'];
        }

        // Agregar filtro por destino de la asignación (trabajador u objeto)
        if (isset($param['tipo_asignacion']) && ($param['tipo_asignacion'] === 'trabajador' || $param['tipo_asignacion'] === 'objeto')) {
            $where[] = "rt.tipo_asignacion = :tipo_asignacion";
            $params['tipo_asignacion'] = $param['tipo_asignacion'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Agregar ordenamiento
        $sql .= " ORDER BY r.disponible DESC, r.nombre ASC";

        // Ejecutar la consulta
        if (!empty($params)) {
            $data = $this->app->db->fetchAll($sql, $params);
        } else {
            $data = $this->app->db->fetchAll($sql);
        }

        // Procesar fotos para convertirlas a base64
        if ($incluir_foto) {
            foreach ($data as &$row) {
                if (!empty($row['foto'])) {
                    $row['foto'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
                } else {
                    $row['foto'] = null;
                }
            }
            unset($row);
        }

        return $data;
    }

    private function _create_recurso($param)
    {
        $resp = array('status' => 1, 'msg' => '');
        // Validaciones básicas
        if (!isset($param['nombre']) || trim($param['nombre']) === '') {
            $resp['status'] = 0; $resp['msg'] = 'El nombre del recurso es obligatorio'; return $resp;
        }
        if (!isset($param['categoria_id']) || trim($param['categoria_id']) === '') {
            $resp['status'] = 0; $resp['msg'] = 'La categoría es obligatoria'; return $resp;
        }

        $insert = array(
            'nombre' => $param['nombre'],
            'descripcion' => isset($param['descripcion']) ? $param['descripcion'] : null,
            'categoria_id' => intval($param['categoria_id']),
            'disponible' => 1
        );

        try {
            $this->app->db->insert('recursos', $insert);
            $resp['id'] = $this->app->db->last_id();
            $resp['msg'] = 'Recurso creado correctamente';
        } catch (Exception $e) {
            $resp['status'] = 0;
            $resp['msg'] = 'Error al crear recurso: ' . $e->getMessage();
        }
        return $resp;
    }

    private function _create_categoria($param)
    {
        $resp = array('status' => 1, 'msg' => '');
        if (!isset($param['nombre']) || trim($param['nombre']) === '') {
            $resp['status'] = 0; $resp['msg'] = 'El nombre de la categoría es obligatorio'; return $resp;
        }
        $nombre = trim($param['nombre']);
        try {
            $this->app->db->insert('categorias_recurso', array('nombre' => $nombre));
            $resp['id'] = $this->app->db->last_id();
            $resp['nombre'] = $nombre;
            $resp['msg'] = 'Categoría creada correctamente';
        } catch (Exception $e) {
            $resp['status'] = 0;
            $resp['msg'] = 'Error al crear categoría: ' . $e->getMessage();
        }
        return $resp;
    }

    private function _update_recurso($param)
    {
        $resp = array('status' => 1, 'msg' => '');
        if (!isset($param['id']) || (int)$param['id'] <= 0) {
            $resp['status'] = 0; $resp['msg'] = 'ID de recurso inválido'; return $resp;
        }
        if (!isset($param['nombre']) || trim($param['nombre']) === '') {
            $resp['status'] = 0; $resp['msg'] = 'El nombre del recurso es obligatorio'; return $resp;
        }
        if (!isset($param['categoria_id']) || trim($param['categoria_id']) === '') {
            $resp['status'] = 0; $resp['msg'] = 'La categoría es obligatoria'; return $resp;
        }

        $update = array(
            'nombre' => $param['nombre'],
            'descripcion' => isset($param['descripcion']) ? $param['descripcion'] : null,
            'categoria_id' => (int)$param['categoria_id']
        );

        try {
            $this->app->db->update('recursos', $update, array('id' => (int)$param['id']));
            $resp['msg'] = 'Recurso actualizado correctamente';
            $resp['id'] = (int)$param['id'];
        } catch (Exception $e) {
            $resp['status'] = 0;
            $resp['msg'] = 'Error al actualizar recurso: ' . $e->getMessage();
        }
        return $resp;
    }

    private function _list_categoria($param)
    {
        $data = $this->app->db->fetchAll("SELECT id, nombre FROM categorias_recurso ORDER BY nombre");
        return $data;
    }

    private function _export_asignados($param)
    {
        // Incluir PHPExcel
        require_once(BASE_CLASS . '/PHPExcel.php');
        
        // Crear nuevo objeto PHPExcel
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        
        // Configurar propiedades del documento
        $objPHPExcel->getProperties()->setCreator("IML Servicios")
                                     ->setLastModifiedBy("IML Servicios")
                                     ->setTitle("Recursos")
                                     ->setSubject("Reporte de Recursos")
                                     ->setDescription("Listado de recursos y su asignación")
                                     ->setKeywords("recursos asignados trabajadores")
                                     ->setCategory("Reportes");
        
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
        
        // Obtener nombre de la empresa
        $sql = "SELECT nombre FROM empresa WHERE id = :empresa_id";
        $params = [':empresa_id' => $this->app->empresa_id];
        $nombreEmpresa = $this->app->db->fetchAll($sql, $params);
        $nombreEmpresa = !empty($nombreEmpresa) ? $nombreEmpresa[0]['nombre'] : 'Empresa';
        
        // Título principal
        $sheet->setCellValue('A2', $nombreEmpresa);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Subtítulo
        $sheet->setCellValue('A3', 'Reporte de Recursos');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Fecha de generación
        $sheet->setCellValue('A4', 'Fecha: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(10);
        $sheet->mergeCells('A4:G4');
        
        $rowNum = 6; // Espacio después del encabezado
        
        // Agregar encabezados de la tabla
        $sheet->setCellValue('A' . $rowNum, 'Recurso');
        $sheet->setCellValue('B' . $rowNum, 'Categoría');
        $sheet->setCellValue('C' . $rowNum, 'Descripción');
        $sheet->setCellValue('D' . $rowNum, 'Estado');
        $sheet->setCellValue('E' . $rowNum, 'Asignado a');
        $sheet->setCellValue('F' . $rowNum, 'Tipo');
        $sheet->setCellValue('G' . $rowNum, 'Fecha de Entrega');
        
        $headerRow = $rowNum;
        
        // Obtener los mismos datos que muestra el listado, con sus filtros
        $data = $this->_get_recursos($param, false);

        // Llenar los datos y calcular anchos máximos
        $row = $headerRow + 1;
        $maxWidths = array(
            'A' => strlen('Recurso'),
            'B' => strlen('Categoría'),
            'C' => strlen('Descripción'),
            'D' => strlen('Estado'),
            'E' => strlen('Asignado a'),
            'F' => strlen('Tipo'),
            'G' => strlen('Fecha de Entrega')
        );

        foreach ($data as $item) {
            $asignado = isset($item['asignado_nombre']) ? trim($item['asignado_nombre']) : '';
            $asignado_es_objeto = isset($item['tipo_asignacion']) && $item['tipo_asignacion'] === 'objeto';

            $valores = array(
                'A' => (string)$item['nombre'],
                'B' => (string)$item['categoria'],
                'C' => (string)$item['descripcion'],
                'D' => ($item['disponible'] == 1) ? 'Disponible' : 'Asignado',
                'E' => $asignado !== '' ? $asignado : '-',
                'F' => $asignado !== '' ? ($asignado_es_objeto ? 'Objeto' : 'Trabajador') : '-',
                'G' => !empty($item['fecha_entrega_a_t']) ? (string)$item['fecha_entrega_a_t'] : '-'
            );

            foreach ($valores as $col => $valor) {
                $maxWidths[$col] = max($maxWidths[$col], strlen($valor));
                $sheet->setCellValue($col . $row, $valor);
            }
            $row++;
        }

        // Establecer anchos de columna automáticos basados en el contenido más largo
        foreach ($maxWidths as $column => $maxWidth) {
            // Ajustar ancho con un pequeño margen y limitar a un máximo razonable
            $adjustedWidth = $maxWidth + 2;
            if ($adjustedWidth > 50) {
                $adjustedWidth = 50;
            }
            $sheet->getColumnDimension($column)->setWidth($adjustedWidth);
        }
        
        // Establecer estilo para encabezados
        $headerStyle = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 12,
                'name'  => 'Arial'
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '4F81BD')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        );
        
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->applyFromArray($headerStyle);
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Aplicar bordes a todos los datos
        $dataRange = 'A' . ($headerRow + 1) . ':G' . ($row - 1);
        $borderStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        );
        $sheet->getStyle($dataRange)->applyFromArray($borderStyle);
        
        // Renombrar hoja
        $objPHPExcel->getActiveSheet()->setTitle('Recursos');
        
        // Establecer hoja activa
        $objPHPExcel->setActiveSheetIndex(0);
        
        // Configurar headers para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="recursos_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Limpiar buffer de salida
        if (ob_get_length()) ob_clean();
        
        // Crear writer y guardar
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        
        // Terminar ejecución
        exit;
    }


    private function _list($param)
    {
        $data = array();
// SELECT DISTINCT 
//                     t.id,
//                     t.carnet_identidad,
//                     t.nombre,
//                     t.apellidos,
//                     t.foto,
//                     t.departamento_id,
//                     t.ubicacion AS ubicacion_id,
//                     d.nombre AS departamento,
//                     u.nombre AS ubicacion,
//                     ra.hora_entrada,
//                     ra.hora_salida
//                 FROM trabajadores t
//                 LEFT JOIN ubicaciones u ON t.ubicacion = u.id
//                 LEFT JOIN departamentos d ON t.departamento_id = d.id
//                 INNER JOIN registro_asistencia ra ON ra.trabajador_id = t.id 
//                     AND DATE(ra.fecha) = :fecha
//                     AND ra.hora_entrada IS NOT NULL
//                 WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
//                 AND (t.empresa_id = {$this->app->empresa_id} OR t.departamento_id IS NULL)
//                 ORDER BY d.nombre, t.apellidos, t.nombre";
        // Construir la consulta base
        $sql = "SELECT rt.*, r.nombre, r.descripcion,
                       CONCAT(t.nombre, ' ', t.apellidos) as nombre_trabajador,
                       COALESCE(NULLIF(CONCAT(t.nombre, ' ', t.apellidos), ' '), rt.asignado_a) as asignado_nombre,
                       t.foto, cr.nombre as categoria
                FROM recursos_trabajadores rt
                LEFT JOIN recursos r ON rt.recurso_id = r.id
                LEFT JOIN categorias_recurso cr ON r.categoria_id = cr.id
                LEFT JOIN trabajadores t ON rt.trabajador_id = t.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
              --  WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)
                WHERE (rt.trabajador_id IS NULL OR t.empresa_id = {$this->app->empresa_id} OR t.departamento_id IS NULL)
                ";

        $params = array();

        // Filtrar por estado si se proporciona
        if (isset($_GET['estado']) && ($_GET['estado'] === '0' || $_GET['estado'] === '1')) {
            $sql .= " AND rt.estado = ?";
            $params[] = (int)$_GET['estado'];
        }

        // Filtrar por categoría si se proporciona
        if (isset($_GET['categoria_recurso_id']) && $_GET['categoria_recurso_id'] !== '') {
            $sql .= " AND r.categoria_id = ?";
            $params[] = (int)$_GET['categoria_recurso_id'];
        }

        // Filtrar por destino de la asignación (trabajador u objeto)
        if (isset($_GET['tipo_asignacion']) && ($_GET['tipo_asignacion'] === 'trabajador' || $_GET['tipo_asignacion'] === 'objeto')) {
            $sql .= " AND rt.tipo_asignacion = ?";
            $params[] = $_GET['tipo_asignacion'];
        }

        // Ordenar según corresponda
        if (isset($_GET['estado']) && $_GET['estado'] === '0') {
            $sql .= " ORDER BY rt.fecha_entrega_a_rh DESC, rt.id DESC";
        } else {
            $sql .= " ORDER BY rt.fecha_entrega_a_t DESC, rt.id DESC";
        }

        // Ejecutar la consulta
        if (!empty($params)) {
            $data = $this->db->fetchAll($sql, $params);
        } else {
            $data = $this->db->fetchAll($sql);
        }

        // Procesar fotos para convertirlas a base64
        foreach ($data as &$row) {
            if (!empty($row['foto'])) {
                // Convertir LONGBLOB a base64
                $fotoBase64 = base64_encode($row['foto']);
                // Crear data URL para imagen
                $row['foto'] = 'data:image/jpeg;base64,' . $fotoBase64;
            } else {
                $row['foto'] = null;
            }
        }

        return $data;
    }


    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-recursos':
                $page['title'] = 'Recursos';
                $page['subtitle'] = 'Listado de Recursos';

                $data_form = array();
                break;
            case 'gestion-recursos':
                $data = array();
                $page['title'] = 'Nueva Recurso';
                $page['subtitle'] = 'Recurso';

                $data_form['cargos'] = $this->app->get_list_cargos();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición de Recurso';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "SELECT * FROM recursos WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Recurso: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                } else {
                    $data['estado'] = 1;
                    $data['fecha_entrega_a_t'] = date('Y-m-d');
                }
                break;
        }
    }
}
