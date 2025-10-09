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
            case 'del':
                $this->_del($param);
                break;
            case 'save':
                $this->_save($param);
                break;
        }
    }

    private function _list_id($param)
    {
        $data = array();

        // Construir la consulta base
        $sql = "SELECT r.*
                FROM recursos r
                LEFT JOIN trabajadores t ON r.trabajador_id = t.id WHERE r.trabajador_id = :trabajador_id";

        $params = array(
            'trabajador_id' => $param['trabajador_id']
        );

        // Filtrar por estado si se proporciona
        if (isset($_GET['estado']) && ($_GET['estado'] === '0' || $_GET['estado'] === '1')) {
            $sql .= " WHERE r.estado = ?";
            $params[] = (int)$_GET['estado'];
        }

        // Ordenar según corresponda
        if (isset($_GET['estado']) && $_GET['estado'] === '0') {
            $sql .= " ORDER BY r.fecha_entrega_a_rh DESC, r.id DESC";
        } else {
            $sql .= " ORDER BY r.fecha_entrega_a_t DESC, r.id DESC";
        }

        // Ejecutar la consulta
        if (!empty($params)) {
            $data = $this->db->fetchAll($sql, $params);
        } else {
            $data = $this->db->fetchAll($sql);
        }

        return $data;
    }

    private function _del($param)
    {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        // Eliminar físicamente el registro de bolsa_empleo
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->del('recursos', $where);

        $history = array(
            'xentity' => 'RECURSOS',
            'xaction' => 'DEL-RECURSO',
            //'xid' => $param['id'],
            'xobs' => 'DEL RECURSO: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }

    private function generar_acta_entrega($param)
    {

        // Verificar que se reciba el ID del recurso
        if (!isset($param['id']) || !is_numeric($param['id'])) {
            die('ID de recurso no válido');
        }

        $id_recurso = intval($param['id']);
        // Obtener datos del recurso y del trabajador
        $sql = "SELECT r.*, t.nombre as nombre_trabajador, t.apellidos, t.cargos_id, c.nombre as cargo_nombre 
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
            'id="trabajador_nombre"></span>' => 'id="trabajador_nombre"><b><u>' . htmlspecialchars($recurso[0]['nombre_trabajador'] . ' ' . $recurso[0]['apellidos']) . '</u></b></span>',
            'id="recurso"></span>' => 'id="recurso"><b><u>' . htmlspecialchars($recurso[0]['nombre'] ?? '') . '</u></b></span>',
            'id="marca"></span>' => 'id="marca"><b><u>' . htmlspecialchars($recurso[0]['marca'] ?? '') . '</u></b></span>',
            'id="modelo"></span>' => 'id="modelo"><b><u>' . htmlspecialchars($recurso[0]['modelo'] ?? '') . '</u></b></span>',
            'id="color"></span>' => 'id="color"><b><u>' . htmlspecialchars($recurso[0]['color'] ?? '') . '</u></b></span>',
            'id="otros_recursos"></span>' => 'id="otros_recursos"><b><u>' . htmlspecialchars($recurso[0]['otros_recursos'] ?? '') . '</u></b></span>',
            'id="cargo_nombre"></span>' => 'id="cargo_nombre"><b><u>' . htmlspecialchars($recurso[0]['cargo_nombre'] ?? '') . '</u></b></span>'
        ];

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


        // Validar campos requeridos
        $required_fields = array(
            'trabajador_id' => 'Trabajador',
            'fecha_entrega_a_t' => 'Fecha de Entrega',
            'nombre' => 'Recurso',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'color' => 'Color',
            'otros_recursos' => 'Otros recursos'
        );

        foreach ($required_fields as $field => $label) {
            if (!isset($param[$field]) || trim($param[$field]) === '') {
                $data['status'] = 0;
                $data['msg'] = "El campo {$label} es obligatorio";
                print(json_encode($data));
                return;
            }
        }

        $insert = array(
            'trabajador_id' => $param['trabajador_id'],
            'fecha_entrega_a_t' => isset($param['fecha_entrega_a_t']) && !empty($param['fecha_entrega_a_t']) ? $param['fecha_entrega_a_t'] : date('Y-m-d'),
            'nombre' => $param['nombre'],
            'marca' => $param['marca'],
            'modelo' => $param['modelo'],
            'color' => $param['color'],
            'otros_recursos' => $param['otros_recursos'],
            //'fecha_entrega_a_rh' => $param['fecha_entrega_a_rh']
        );

        $data['action'] = $param['action'];

        // Remover campos que no pertenecen a la tabla bolsa_empleo
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        if ($data['action'] == 'insert') {
            // Asegurar que solo se insertan los campos que existen en la tabla
            $campos_validos = [
                'trabajador_id',
                'fecha_entrega_a_t',
                'nombre',
                'marca',
                'modelo',
                'color',
                'otros_recursos'
            ];

            // Filtrar solo los campos válidos
            $insert_filtered = array_intersect_key($insert, array_flip($campos_validos));

            // Debug: Guardar la consulta de inserción
            $log = date('Y-m-d H:i:s') . " - Intentando insertar:\n";
            $log .= print_r($insert_filtered, true) . "\n";
            file_put_contents('debug_recursos.log', $log, FILE_APPEND);

            try {
                // Insertar la postulación usando solo los campos filtrados
                $result = $this->db->insert('recursos', $insert_filtered);

                if ($result) {
                    // Obtener el último ID insertado
                    $lastId = $this->db->last_id();
                    $param['id'] = $lastId;
                    $this->generar_acta_entrega($param);

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
                            'xobs' => 'RECURSO: ' . $lastId . ' ' . $insert['trabajador_id'] . ' ' . $insert['nombre']
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
                    'fecha_entrega_a_t',
                    'nombre',
                    'fecha_entrega_a_rh',
                    'marca',
                    'modelo',
                    'color',
                    'otros_recursos'
                ];

                // Filtrar solo los campos válidos
                $update_filtered = array_intersect_key($insert, array_flip($campos_validos));

                $update_filtered['fecha_entrega_a_rh'] = $param['fecha_entrega_a_rh'];
                if (isset($param['fecha_entrega_a_rh'])) {
                    $update_filtered['estado'] = 0;
                }

                // Debug log before update
                $log = date('Y-m-d H:i:s') . " - Intentando actualizar postulación ID: " . $id . "\n";
                $log .= print_r($update_filtered, true) . "\n";
                file_put_contents('debug_recursos.log', $log, FILE_APPEND);

                // Actualizar la postulación
                $where = array('id' => $id);
                $result = $this->app->db->update('recursos', $update_filtered, $where);

                if ($result) {
                    $this->generar_acta_entrega($param);
                    // Preparar respuesta exitosa
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Registro actualizado correctamente';
                    $data['id'] = $id;
                    $data['date'] = date('d-m-Y H:i:s');

                    // Registrar en historial
                    $history = array(
                        'xentity' => 'RECURSOS',
                        'xaction' => 'UPDATE-RECURSO',
                        'xobs' => 'RECURSO: ' . $id . ' ' . $insert['trabajador_id'] . ' ' . $insert['nombre']
                    );
                    $this->app->add_history($history);
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

    

    private function _list($param)
    {
        $data = array();

        // Construir la consulta base
        $sql = "SELECT r.*, CONCAT(t.nombre, ' ', t.apellidos) as nombre_trabajador
                FROM recursos r
                LEFT JOIN trabajadores t ON r.trabajador_id = t.id";

        $params = array();

        // Filtrar por estado si se proporciona
        if (isset($_GET['estado']) && ($_GET['estado'] === '0' || $_GET['estado'] === '1')) {
            $sql .= " WHERE r.estado = ?";
            $params[] = (int)$_GET['estado'];
        }

        // Ordenar según corresponda
        if (isset($_GET['estado']) && $_GET['estado'] === '0') {
            $sql .= " ORDER BY r.fecha_entrega_a_rh DESC, r.id DESC";
        } else {
            $sql .= " ORDER BY r.fecha_entrega_a_t DESC, r.id DESC";
        }

        // Ejecutar la consulta
        if (!empty($params)) {
            $data = $this->db->fetchAll($sql, $params);
        } else {
            $data = $this->db->fetchAll($sql);
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
