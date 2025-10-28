<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Bolsas_empleos {

    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'checked':
                $this->_checked($param);
                break;
            case 'del':
                $this->_del($param);
                break; 
            case 'save':
                $this->_save($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-bolsas_empleos':
                $data = array();
                $page['title'] = 'Bolsa de Empleo';
                $page['subtitle'] = 'Listado Bolsa de Empleo';

                $data_form = array();
                //$data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                break;
            case 'bolsas_empleos':
                $data = array();
                $page['title'] = 'Nueva Postulación';
                $page['subtitle'] = 'Postulación a Empleo';

                $data_form['cargos'] = $this->app->get_list_cargos();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición de Postulación';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "SELECT * FROM bolsa_empleo WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Postulación: ' . $row['id'] . ' - ' . $row['nombre'] . ' ' . $row['apellidos'];
                    }
                } else {
                    $data['estatus'] = 'pendiente';
                    $data['fecha_registro'] = date('Y-m-d');
                }
                break;
        }
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        // Eliminar físicamente el registro de bolsa_empleo
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->del('bolsa_empleo', $where);

        $history = array(
            'xentity' => 'BOLSA_EMPLEO',
            'xaction' => 'DEL-POSTULACION',
            'xid' => $param['id'],
            'xobs' => 'DEL POSTULACION: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }
    
    private function _save($param) {
        // Debug: Guardar los parámetros recibidos en un archivo de log
        $log = date('Y-m-d H:i:s') . " - Parámetros recibidos:\n";
        $log .= "POST: " . print_r($param, true) . "\n";
        $log .= "FILES: " . print_r($_FILES, true) . "\n";
        $log .= "Action: " . (isset($param['action']) ? $param['action'] : 'NO ACTION') . "\n";
        file_put_contents('debug_bolsas_empleos.log', $log, FILE_APPEND);
        $param['empresa_id'] = $this->app->empresa_id;
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        // Procesar curriculum si se subió
        if (isset($_FILES['curriculum']) && $_FILES['curriculum']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/curriculos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $curriculum_name = uniqid('cv_') . '_' . basename($_FILES['curriculum']['name']);
            $curriculum_path = $upload_dir . $curriculum_name;
            if (move_uploaded_file($_FILES['curriculum']['tmp_name'], $curriculum_path)) {
                $param['curriculum'] = $curriculum_path;
            }
        }

        // Validar campos requeridos
        $required_fields = array(
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'telefono' => 'Teléfono',
            'segundos_apellidos' => 'Segundo Apellido',
            'cargo_postulado_id' => 'Cargo al que postula',
        );
        // Validación de solo letras en nombre/apellidos/segundos_apellidos si se proveen
        $soloLetras = array(
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'segundos_apellidos' => 'Segundo Apellido'
        );

        if ($data['action'] == 'update') {
            $sql = "SELECT * FROM bolsa_empleo WHERE id=:id";
            $get = $this->db->fetchAll($sql, array('id' => $param['id']));
            $param['nombre'] = $get[0]['nombre'];
            $param['apellidos'] = $get[0]['apellidos'];
            $param['segundos_apellidos'] = $get[0]['segundos_apellidos'];
            $param['cargo_postulado_id'] = $get[0]['cargo_postulado_id'];
            $param['telefono'] = $get[0]['telefono'];
            $param['fecha_registro'] = $get[0]['fecha_registro'];
            $param['curriculum'] = $get[0]['curriculum'];
            $param['empresa_id'] = $get[0]['empresa_id'];
        }
        foreach ($soloLetras as $f => $label) {
            if (isset($param[$f]) && trim($param[$f]) !== '') {
                if (!preg_match('/^[\p{L}\s]+$/u', $param[$f])) {
                    $data['status'] = 0;
                    $data['msg'] = "El campo {$label} solo debe contener letras";
                    print(json_encode($data));
                    return;
                }
            }
        }
        
        
        
        foreach ($required_fields as $field => $label) {
            if (!isset($param[$field]) || trim($param[$field]) === '') {
                $data['status'] = 0;
                $data['msg'] = "El campo {$label} es obligatorio";
                print(json_encode($data));
                return;
            }
        }

        $insert = array(
            'nombre' => $param['nombre'],
            'apellidos' => $param['apellidos'],
            'segundos_apellidos' => isset($param['segundos_apellidos']) ? $param['segundos_apellidos'] : '',
            'curriculum' => isset($param['curriculum']) ? $param['curriculum'] : '',
            'cargo_postulado_id' => intval($param['cargo_postulado_id']),
            'telefono' => $param['telefono'],
            'fecha_registro' => isset($param['fecha_registro']) && !empty($param['fecha_registro']) ? $param['fecha_registro'] : date('Y-m-d'),
            'empresa_id' => $param['empresa_id'],
        );

        $data['action'] = $param['action'];

        // Remover campos que no pertenecen a la tabla bolsa_empleo
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);
        unset($insert['id']);

        if ($data['action'] == 'insert') {
            // Asegurar que solo se insertan los campos que existen en la tabla
            $campos_validos = [
                'nombre',
                'apellidos',
                'segundos_apellidos',
                'curriculum',
                'cargo_postulado_id',
                'telefono',
                'fecha_registro',
                'empresa_id',
            ];
            
            // Filtrar solo los campos válidos
            $insert_filtered = array_intersect_key($insert, array_flip($campos_validos));
            
            // Debug: Guardar la consulta de inserción
            $log = date('Y-m-d H:i:s') . " - Intentando insertar:\n";
            $log .= print_r($insert_filtered, true) . "\n";
            file_put_contents('debug_bolsas_empleos.log', $log, FILE_APPEND);

            try {
                // Insertar la postulación usando solo los campos filtrados
                $result = $this->db->insert('bolsa_empleo', $insert_filtered);
                
                if ($result) {
                    // Obtener el último ID insertado
                    $lastId = $this->db->last_id();
                    
                    if ($lastId) {
                        // Preparar respuesta exitosa
                        $data['msg_title'] = 'Operación exitosa';
                        $data['msg'] = 'Registro insertado correctamente';
                        $data['id'] = $lastId;
                        $data['date'] = date('d-m-Y H:i:s');

                        // Registrar en historial
                        $history = array(
                            'xentity' => 'BOLSA_EMPLEO',
                            'xaction' => 'INSERT-POSTULACION',
                            'xid' => $lastId,
                            'xobs' => 'POSTULACION: ' . $lastId . ' ' . $insert['nombre'] . ' ' . $insert['apellidos']
                        );
                        try {
                            $this->app->add_history($history);
                        } catch (Exception $eHist) {
                            // No romper el flujo si el historial falla
                            error_log('Historial bolsas_empleos insert falló: ' . $eHist->getMessage());
                        }
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
                file_put_contents('debug_bolsas_empleos.log', $errorLog, FILE_APPEND);
                
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
                
               $insert['observaciones'] = $param['observaciones'];

                // Filtrar campos válidos para actualización
                $campos_validos = [
                    'nombre',
                    'apellidos',
                    'segundos_apellidos',
                    'curriculum',
                    'cargo_postulado_id',
                    'telefono',
                    'fecha_registro',
                    'observaciones',
                    'empresa_id',
                ];
                
                // Filtrar solo los campos válidos
                $update_filtered = array_intersect_key($insert, array_flip($campos_validos));

                // Debug log before update
                $log = date('Y-m-d H:i:s') . " - Intentando actualizar postulación ID: " . $id . "\n";
                $log .= print_r($update_filtered, true) . "\n";
                file_put_contents('debug_bolsas_empleos.log', $log, FILE_APPEND);

                // Actualizar la postulación
                $where = array('id' => $id);
                $result = $this->app->db->update('bolsa_empleo', $update_filtered, $where);

                if ($result) {
                    // Preparar respuesta exitosa
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Registro actualizado correctamente';
                    $data['id'] = $id;
                    $data['date'] = date('d-m-Y H:i:s');

                    // Registrar en historial
                    $history = array(
                        'xentity' => 'BOLSA_EMPLEO',
                        'xaction' => 'UPDATE-POSTULACION',
                        'xid' => $id,
                        'xobs' => 'POSTULACION: ' . $id . ' ' . $insert['nombre'] . ' ' . $insert['apellidos']
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
                file_put_contents('debug_bolsas_empleos.log', $errorLog, FILE_APPEND);
                
                // Respuesta de error
                $data['status'] = 0;
                $data['msg_title'] = 'Error';
                $data['msg'] = 'Error al actualizar el registro: ' . $e->getMessage();
            }
        }

        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "SELECT b.id, b.nombre, b.apellidos, b.segundos_apellidos, b.ci_bolsa_empleo, b.curriculum, c.nombre as cargo_postulado, b.telefono, b.fecha_registro, b.observaciones
                 FROM bolsa_empleo b
                 LEFT JOIN cargos c ON b.cargo_postulado_id = c.id
                 WHERE (b.empresa_id = {$this->app->empresa_id} OR b.empresa_id IS NULL)
                 ORDER BY b.id DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }

   
}
