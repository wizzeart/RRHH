<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Trabajador {

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
            case 'list-filter':
                $data = $this->_list_filter($param);
                print(json_encode($data));
                break;
            case 'list-bajas':
                $data = $this->_list_bajas($param);
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
            case 'get-municipios':
                $data = $this->_get_municipios_by_provincia($param);
                print(json_encode($data));
                break;
        }
    }

    public function controlador($param) {
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
                $page['title'] = 'Registro de Trabajador';
                $page['subtitle'] = 'Registro de Trabajador';

                $val = array(
                    'id' => $param['id']
                );
                $sql = "select *"
                        . " from " . 'trabajadores'
                        . " where id=:id";
                $row = $this->db->fetchRow($sql, $val);
                if ($row) {

                    //$row['almacenes'] = $this->app->get_list_usuarios_almacenes($row['xusuario_id']);
                    //$row['puntos-ventas'] = $this->app->get_list_usuarios_revendedores($row['xusuario_id']);
                    //print_r($row['almacenes']);
                    //die();

                    $data = $row;
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
                $page['subtitle'] = 'Ficha de Trabajador';

                
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

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición trabajador';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select *"
                            . " from " . 'trabajadores'
                            . " where id=:id";
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

    private function _del($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        $update = array(
            'fecha_baja' => date('Y-m-d'),
            'trabajador_eliminado' => 1
        );
        $where = array(
            'id' => $param['id']
        );
        // Ejecutar baja lógica del trabajador
        $this->db->update('trabajadores', $update, $where);

        // Registrar en historial
        $history = array(
            'xentity' => 'TRABAJADOR',
            'xaction' => 'BAJA-TRABAJADOR',
            'xid' => $param['id'],
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }
    
    /**
     * Obtiene el próximo ID disponible en la tabla usuarios
     */
    private function getNextUsuarioId() {
        $sql = "SELECT IFNULL(MAX(xusuario_id), 0) + 1 as next_id FROM usuarios";
        $result = $this->db->fetchRow($sql);
        return $result ? (int)$result['next_id'] : 1;
    }
    private function generarUuidV4() {
        $data = random_bytes(16);
    
        // Ajustar los bits según la especificación UUID v4
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );
        
        // Obtener el próximo ID de usuario disponible para nuevos registros
        if ($data['action'] === 'insert') {
            $param['usuario_id'] = $this->getNextUsuarioId();
            // Generar UUID para el nuevo trabajador
            $param['uuid'] = $this->generarUuidV4();
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

            // // Verificar si el email ya existe (insert)
            // if ($data['action'] == 'insert' && isset($param['email']) && trim($param['email']) !== '') {
            //     $sql = "SELECT id FROM trabajadores WHERE LOWER(email) = LOWER(:email) AND trabajador_eliminado = '0'";
            //     $val = array('email' => $param['email']);
            //     $existing = $this->db->fetchRow($sql, $val);
            //     if ($existing) {
            //         $data['status'] = 0;
            //         $data['msg'] = 'Ya existe un trabajador con este correo electrónico';
            //         print(json_encode($data));
            //         return;
            //     }
            // }

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
                'edad' => 'Edad',
                'direccion' => 'Dirección',
                'provincia_id' => 'Provincia',
                'municipio_id' => 'Municipio',
                'telefono' => 'Teléfono',
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
        $allowed_fields = ['usuario_id', 'nombre', 'apellidos', 'apellidos_segundos', 'sexo', 'carnet_identidad', 'edad', 'direccion', 'provincia_id', 'municipio_id', 'telefono', 'nivel_educacional', 'departamento_id', 'cargos_id', 'fecha_contratacion', 'fecha_baja', 'estatus', 'bolsa_empleo_id', 'foto'];
        
        foreach ($allowed_fields as $field) {
            if (isset($param[$field])) {
                switch ($field) {
                    case 'edad':
                    case 'departamento_id':
                    case 'cargos_id':
                    case 'provincia_id':
                    case 'municipio_id':
                        $insert[$field] = intval($param[$field]);
                        break;
                    case 'bolsa_empleo_id':
                        $insert[$field] = !empty($param[$field]) ? intval($param[$field]) : null;
                        break;
                    case 'fecha_baja':
                        $insert[$field] = !empty($param[$field]) ? $param[$field] : null;
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
            // Establecer fecha de contratación si no está definida
            if (!isset($insert['fecha_contratacion']) || empty($insert['fecha_contratacion'])) {
                $insert['fecha_contratacion'] = date('Y-m-d');
            }
            
            try {
                // Primero insertar en la tabla usuarios
                $email = strtolower($insert['nombre'] . substr($insert['apellidos'], 0, 3) . '@allnovu.net');
                
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
                $usuarioId = $this->db->last_id();
                
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
                    'telefono',
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
                
                // Procesar foto si se subió
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/trabajadores/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Obtener la extensión del archivo original
                    $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    
                    // Usar el UUID generado como nombre de archivo
                    if (isset($param['uuid'])) {
                        $foto_name = $param['uuid'] . '.' . $file_extension;
                        $foto_path = $upload_dir . $foto_name;
                        
                        // Mover el archivo temporal a la ubicación final
                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                            $insert_filtered['foto'] = $foto_path;
                        } else {
                            error_log('Error al mover el archivo subido: ' . $_FILES['foto']['tmp_name'] . ' a ' . $foto_path);
                        }
                    } else {
                        // Si por alguna razón no hay UUID, usar un nombre único
                        $foto_name = uniqid('foto_') . '_' . basename($_FILES['foto']['name']);
                        $foto_path = $upload_dir . $foto_name;
                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                            $insert_filtered['foto'] = $foto_path;
                        } else {
                            error_log('Error al mover el archivo subido (sin UUID): ' . $_FILES['foto']['tmp_name'] . ' a ' . $foto_path);
                        }
                    }
                }
                
                // Insertar trabajador con datos validados
                
                // Insertar el trabajador
                $result = $this->db->insert('trabajadores', $insert_filtered);
                
                if ($result) {
                    // Obtener el ID del trabajador insertado
                    $lastId = $this->db->last_id();
                    
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

                        // Preparar respuesta exitosa
                        $data['msg_title'] = 'Operación exitosa';
                        $data['msg'] = 'Registro insertado correctamente';
                        $data['id'] = $lastId;
                        $data['date'] = date('d-m-Y H:i:s');

                        // Registrar en historial
                        $history = array(
                            'xentity' => 'TRABAJADOR',
                            'xaction' => 'INSERT-TRABAJADOR',
                            'xobs' => 'TRABAJADOR: ' . $lastId . ' ' . $insert['nombre']
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
                
                // Filtrar campos válidos para actualización
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
                    'telefono',
                    'email',
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

                // Actualizar el trabajador
                $where = array('id' => $id);
                $result = $this->db->update('trabajadores', $update_filtered, $where);

                if ($result) {
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

                    // Registrar en historial
                    $history = array(
                        'xentity' => 'TRABAJADOR',
                        'xaction' => 'UPDATE-TRABAJADOR',
                        'xid' => $id,
                        'xobs' => 'TRABAJADOR: ' . $id . ' ' . $insert['nombre']
                    );
                    $this->app->add_history($history);
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

    private function _list($param) {
        $data = array();
        
        // Primero probamos sin JOIN para confirmar que funciona
        $sql = "SELECT 
                t.id,
                t.nombre,
                t.apellidos,
                t.carnet_identidad,
                t.sexo,
                t.edad,
                t.estatus,
                t.cargos_id,
                t.provincia_id,
                t.municipio_id,
                CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo
                FROM trabajadores t 
                WHERE t.trabajador_eliminado = '0'
                ORDER BY t.apellidos, t.nombre";
                
        error_log("Consulta sin JOIN: " . $sql);
        $data = $this->db->fetchAll($sql);
        error_log("Registros sin JOIN: " . count($data));
        
        // Si funciona sin JOIN, probamos con JOIN
        if (!empty($data)) {
            $sqlWithJoin = "SELECT 
                    t.id,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.sexo,
                    t.edad,
                    t.estatus,
                    t.cargos_id,
                    t.provincia_id,
                    t.municipio_id,
                    COALESCE(c.nombre, 'Sin cargo') as cargo_nombre,
                    COALESCE(p.nombre, 'Sin provincia') as provincia_nombre,
                    COALESCE(m.nombre, 'Sin municipio') as municipio_nombre,
                    CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo
                    FROM trabajadores t 
                    LEFT JOIN cargos c ON t.cargos_id = c.id
                    LEFT JOIN provincia p ON t.provincia_id = p.id
                    LEFT JOIN municipio m ON t.municipio_id = m.id
                    WHERE t.trabajador_eliminado = '0'
                    ORDER BY t.apellidos, t.nombre";
                    
            $dataWithJoin = $this->db->fetchAll($sqlWithJoin);
            
            // Si el JOIN funciona, usar esos datos
            if (!empty($dataWithJoin)) {    
                $data = $dataWithJoin;
            }
        
        return $data;
    }

    private function _list_filter($param){
        try {
            $data = array();
            $where = ["t.trabajador_eliminado = '0'"];
            $params = [];

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

            $sql = "SELECT 
                    t.id,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    t.sexo,
                    t.edad,
                    t.estatus,
                    c.nombre as cargo_nombre,
                    d.nombre as departamento_nombre,
                    CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo
                    FROM trabajadores t 
                    LEFT JOIN cargos c ON t.cargos_id = c.id
                    LEFT JOIN departamentos d ON t.departamento_id = d.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY t.apellidos, t.nombre";

            $data = $this->db->fetchAll($sql,$params);
            return $data;
        } catch (Exception $e) {
            // Registrar el error en el log
            error_log('Error en Trabajador->_list_filter: ' . $e->getMessage());

            // Devolver un array vacío en caso de error
            return array();
        }
    }


    private function _list_bajas($param) {
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
                WHERE t.trabajador_eliminado = 1
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
            $cargosIds = array_filter($cargosIds, function($value) {
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
    private function _get_list_provincias() {
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
    private function _get_list_municipios() {
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
    private function _get_municipios_by_provincia($param) {
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
   
}
