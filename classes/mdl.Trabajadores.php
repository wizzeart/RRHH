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
                $page['title'] = 'Ficha de Trabajador';
                $page['subtitle'] = 'Ficha de Trabajador';

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
                    $page['subtitle'] = 'Trabajador: ' . $row['nombre'];
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

                        //$row['almacenes'] = $this->app->get_list_usuarios_almacenes($row['xusuario_id']);
                        //$row['puntos-ventas'] = $this->app->get_list_usuarios_revendedores($row['xusuario_id']);
                        //print_r($row['almacenes']);
                        //die();

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
            'trabajador_eliminado' => 1,
            'fecha_baja' => date('Y-m-d')
        );
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->update('trabajadores', $update, $where);

        $history = array(
            'xentity' => 'TRABAJADORES',
            'xaction' => 'DEL-TRABAJADORES',
            'xid' => $param['id'],
            'xobs' => 'BAJA TRABAJADOR: ' . $param['id'] . ' - Fecha: ' . date('Y-m-d')
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }
    
    private function _save($param) {

        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        // Procesar foto si se subió
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/trabajadores/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $foto_name = uniqid('foto_') . '_' . basename($_FILES['foto']['name']);
            $foto_path = $upload_dir . $foto_name;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                $param['foto'] = $foto_path;
            }
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

            // Verificar si el email ya existe (insert)
            if ($data['action'] == 'insert' && isset($param['email']) && trim($param['email']) !== '') {
                $sql = "SELECT id FROM trabajadores WHERE LOWER(email) = LOWER(:email) AND trabajador_eliminado = '0'";
                $val = array('email' => $param['email']);
                $existing = $this->db->fetchRow($sql, $val);
                if ($existing) {
                    $data['status'] = 0;
                    $data['msg'] = 'Ya existe un trabajador con este correo electrónico';
                    print(json_encode($data));
                    return;
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
                'sexo' => 'Sexo',
                'carnet_identidad' => 'Carnet de Identidad',
                'edad' => 'Edad',
                'direccion' => 'Dirección',
                'telefono' => 'Teléfono',
                'email' => 'Email',
                'nivel_educacional' => 'Nivel Educacional',
                'departamento_id' => 'Departamento',
                'cargos_id' => 'Cargo',
                'estatus' => 'Estatus'
            );        foreach ($required_fields as $field => $label) {
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
            'sexo' => $param['sexo'],
            'carnet_identidad' => $param['carnet_identidad'],
            'edad' => intval($param['edad']),
            'direccion' => $param['direccion'],
            'telefono' => $param['telefono'],
            'email' => $param['email'],
            'nivel_educacional' => $param['nivel_educacional'],
            'departamento_id' => intval($param['departamento_id']),
            'cargos_id' => intval($param['cargos_id']),
            'fecha_contratacion' => $param['fecha_contratacion'],
            'fecha_baja' => !empty($param['fecha_baja']) ? $param['fecha_baja'] : null,
            'estatus' => $param['estatus'],
            'bolsa_empleo_id' => !empty($param['bolsa_empleo_id']) ? intval($param['bolsa_empleo_id']) : null,
            'foto' => isset($param['foto']) ? $param['foto'] : '',
            'trabajador_eliminado' => '0'
        );

        $data['action'] = $param['action'];

        // Remover campos que no pertenecen a la tabla trabajadores
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        if ($data['action'] == 'insert') {
            // Establecer fecha de contratación si no está definida
            if (!isset($insert['fecha_contratacion']) || empty($insert['fecha_contratacion'])) {
                $insert['fecha_contratacion'] = date('Y-m-d');
            }
            
            // Asegurar que solo se insertan los campos que existen en la tabla
            $campos_validos = [
                'cargos_id',
                'departamento_id',
                'foto',
                'nombre',
                'apellidos',
                'carnet_identidad',
                'sexo',
                'edad',
                'direccion',
                'telefono',
                'email',
                'nivel_educacional',
                'fecha_contratacion',
                'fecha_baja',
                'estatus',
                'bolsa_empleo_id',
                'trabajador_eliminado'
            ];
            
            // Filtrar solo los campos válidos
            $insert_filtered = array_intersect_key($insert, array_flip($campos_validos));
            

            try {
                // Insertar el trabajador usando solo los campos filtrados
                $result = $this->db->insert('trabajadores', $insert_filtered);
                
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
                            'xentity' => 'TRABAJADOR',
                            'xaction' => 'INSERT-TRABAJADOR',
                            'id' => $lastId,
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

                // Verificar si el email ya existe en otro registro (update)
                if (isset($param['email']) && trim($param['email']) !== '') {
                    $sql = "SELECT id FROM trabajadores WHERE LOWER(email) = LOWER(:email) AND id != :id AND trabajador_eliminado = '0'";
                    $val = array(
                        'email' => $param['email'],
                        'id' => $id
                    );
                    $existing = $this->db->fetchRow($sql, $val);
                    if ($existing) {
                        $data['status'] = 0;
                        $data['msg'] = 'Ya existe otro trabajador con este correo electrónico';
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
                    'carnet_identidad',
                    'sexo',
                    'edad',
                    'direccion',
                    'telefono',
                    'email',
                    'nivel_educacional',
                    'fecha_contratacion',
                    'fecha_baja',
                    'estatus',
                    'bolsa_empleo_id',
                    'trabajador_eliminado'
                ];
                
                // Filtrar solo los campos válidos
                $update_filtered = array_intersect_key($insert, array_flip($campos_validos));


                // Actualizar el trabajador
                $where = array('id' => $id);
                $result = $this->app->db->update('trabajadores', $update_filtered, $where);

                if ($result) {
                    // Preparar respuesta exitosa
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Registro actualizado correctamente';
                    $data['id'] = $id;
                    $data['date'] = date('d-m-Y H:i:s');

                    // Registrar en historial
                    $history = array(
                        'xentity' => 'TRABAJADOR',
                        'xaction' => 'UPDATE-TRABAJADOR',
                        'id' => $id,
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
                    $friendly = 'Conflicto de clave primaria. El registro ya existe con ese identificador.';
                }
                $data['msg'] = $friendly !== '' ? $friendly : ('Error al actualizar el registro: ' . $e->getMessage());
            }
        }

        print(json_encode($data));
    }

    private function _list($param) {
        $data = array();
        $sql = "SELECT 
                t.*,
                p.id as pase_id, 
                p.areas_acceso, 
                p.fecha_generacion, 
                p.vigente,
                c.nombre as cargo_nombre,
                c.id as cargo_id_original
                FROM trabajadores t 
                LEFT JOIN pases_acceso p ON t.id = p.trabajador_id 
                LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                WHERE t.trabajador_eliminado = '0'
                ORDER BY t.id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _list_bajas($param) {
        $data = array();
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
                c.nombre as cargo_nombre
                FROM trabajadores t 
                LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                WHERE t.fecha_baja IS NOT NULL 
                AND t.fecha_baja != ''
                ORDER BY t.fecha_baja DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }

   
}
