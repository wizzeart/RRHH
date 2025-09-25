<?php
class List_recursos
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
            case 'checked':
                // $this->_checked($param);
                break;
            case 'del':
                //$this->_del($param);
                break;
            case 'save':
                $this->_save($param);
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

        // Eliminar físicamente el registro de bolsa_empleo
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->delete('bolsa_empleo', $where);

        $history = array(
            'xentity' => 'BOLSA_EMPLEO',
            'xaction' => 'DEL-POSTULACION',
            'xid' => $param['id'],
            'xobs' => 'DEL POSTULACION: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
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
                'nombre'
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
                    'fecha_entrega_a_rh'
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

    private function _insert($data)
    {
        $response = array('success' => false, 'message' => '');

        try {
            // Validar campos requeridos
            $required_fields = array('trabajador_id', 'nombre');
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            // Preparar datos para la inserción
            $insert_data = array(
                'trabajador_id' => $data['trabajador_id'],
                'nombre' => $data['nombre'],
                'estado' => 1, // Por defecto, el recurso está en estado 1 (entregado)
                'fecha_entrega_a_t' => date('Y-m-d H:i:s'), // Fecha actual para el registro
                'fecha_entrega_a_rh' => null // Se actualizará cuando se entregue a RH
            );

            // Insertar en la base de datos
            $result = $this->db->insert('recursos', $insert_data);

            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Recurso registrado correctamente';
                $response['id'] = $this->db->lastInsertId();
            } else {
                throw new Exception('Error al registrar el recurso');
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
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
                        $page['subtitle'] = 'Recurso: ' . $row['id'] . ' - ' . $row['nombre'] ;
                    }
                } else {
                    $data['estado'] = 1;
                    $data['fecha_entrega_a_t'] = date('Y-m-d');
                }
                break;
        }
    }
}
