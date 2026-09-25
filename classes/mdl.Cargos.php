<?php

class Cargos
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
            case 'save':
                $this->_save($param);
                break;
            case 'del':
                $this->_del($param);
                break;
        }
    }

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-cargos':
                $data = array();
                $page['title'] = 'Cargos';
                $page['subtitle'] = 'Listado de Cargos';
                $data_form = array();
                break;

            case 'cargos':
                $data = array();
                $page['title'] = 'Nuevo Cargo';
                $page['subtitle'] = 'Formulario de Cargo';

                // Cargar departamentos para el select
                $data_form['departamentos'] = $this->get_departamentos();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Cargo';
                    $action = 'update';

                    $val = array('id' => $param['id']);
                    $sql = "SELECT * FROM cargos WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Cargo: ' . $row['id'] . ' - ' . $row['nombre'];
                    }
                }
                break;
        }
    }

    private function _list($param)
    {
        $where = [];
        $vals = [];
        // if (isset($param['departamento_id']) && trim($param['departamento_id']) !== '') {
        //     $where[] = 'c.departamento_id = :departamento_id';
        //     $vals['departamento_id'] = (int)$param['departamento_id'];
        // }
        // Búsqueda por nombre si llega el parámetro 'nombre' o 'search' (bootstrap-table)
        if (isset($param['nombre']) && trim($param['nombre']) !== '') {
            $where[] = 'c.nombre LIKE :nombre';
            $vals['nombre'] = '%' . $param['nombre'] . '%';
        } elseif (isset($param['search']) && trim($param['search']) !== '') {
            $where[] = '(c.nombre LIKE :search OR c.descripcion LIKE :search)';
            $vals['search'] = '%' . $param['search'] . '%';
        }

        $cond = '';
        if (!empty($where)) {
            $cond = ' WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT c.id, c.nombre, c.descripcion, c.salario, c.funciones_path
                FROM cargos c
                " . $cond .
            " ORDER BY c.nombre ASC";

        if (!empty($vals)) {
            $data = $this->db->fetchAll($sql, $vals);
        } else {
            $data = $this->db->fetchAll($sql);
        }
        return $data;
    }

    private function _save($param)
    {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => isset($param['action']) ? $param['action'] : 'insert'
        );

        // Validaciones
        $required = array(
            'nombre' => 'Nombre',
            //'departamento_id' => 'Departamento'
        );
        foreach ($required as $field => $label) {
            if (!isset($param[$field]) || trim($param[$field]) === '') {
                $data['status'] = 0;
                $data['msg'] = "El campo {$label} es obligatorio";
                print(json_encode($data));
                return;
            }
        }

        // Manejar subida de archivo de funciones
        $funciones_path = isset($param['funciones_path']) ? $param['funciones_path'] : '';
        
        // Si se subió un nuevo archivo
        if (isset($_FILES['funciones_file']) && $_FILES['funciones_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['funciones_file'];
            
            // Validar que sea .docx
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file_ext !== 'docx') {
                $data['status'] = 0;
                $data['msg'] = 'El archivo debe ser de tipo .docx';
                print(json_encode($data));
                return;
            }
            
            // Generar nombre único para el archivo
            $cargo_nombre = preg_replace('/[^a-zA-Z0-9_]/', '_', $param['nombre']);
            $timestamp = date('Y-m-d_H-i-s');
            $filename = 'funciones_' . $cargo_nombre . '_' . $timestamp . '.docx';
            
            // Ruta de destino
            $target_dir = __DIR__ . '/../docs/FUNCIONES/';
            $target_path = $target_dir . $filename;
            
            // Crear directorio si no existe
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Actualizar path relativo para base de datos
                $funciones_path = 'docs/FUNCIONES/' . $filename;
                
                // Eliminar archivo anterior si existe
                if (!empty($param['funciones_path']) && file_exists(__DIR__ . '/../' . $param['funciones_path'])) {
                    unlink(__DIR__ . '/../' . $param['funciones_path']);
                }
            } else {
                $data['status'] = 0;
                $data['msg'] = 'Error al subir el archivo de funciones';
                print(json_encode($data));
                return;
            }
        }

        $insert = array(
            'nombre' => $param['nombre'],
            'descripcion' => isset($param['descripcion']) ? $param['descripcion'] : '',
            'salario' => isset($param['salario']) && $param['salario'] !== '' ? floatval($param['salario']) : null,
            'funciones_path' => $funciones_path,
            //'departamento_id' => intval($param['departamento_id'])
        );

        // Campos de control
        unset($insert['module']);
        unset($insert['method']);
        unset($insert['action']);

        try {
            if ($data['action'] == 'insert') {
                $ok = $this->db->insert('cargos', $insert);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Cargo insertado correctamente';
                    $data['id'] = $this->db->last_id();
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo insertar el cargo';
                }
            } else {
                if (!isset($param['id'])) {
                    $data['status'] = 0;
                    $data['msg'] = 'ID no proporcionado';
                    print(json_encode($data));
                    return;
                }

                // Obtener salario anterior antes de actualizar
                $cargoId = intval($param['id']);
                $currentCargo = $this->db->fetchRow("SELECT salario FROM cargos WHERE id = :id", array('id' => $cargoId));
                $oldSalario = $currentCargo ? floatval($currentCargo['salario']) : 0.0;
                $newSalario = isset($insert['salario']) ? floatval($insert['salario']) : 0.0;

                $where = array('id' => $cargoId);
                $ok = $this->db->update('cargos', $insert, $where);
                if ($ok) {
                    $data['msg_title'] = 'Operación exitosa';
                    $data['msg'] = 'Cargo actualizado correctamente';
                    $data['id'] = $param['id'];

                    // Si el salario cambió, generar suplemento de contrato para los trabajadores
                    // Comparación con epsilon para flotantes o simple !=
                    if (abs($oldSalario - $newSalario) > 0.01) {
                        $this->_generateMassiveSupplements($cargoId, $newSalario);
                        $data['msg'] .= '. Se generaron suplementos para los trabajadores.';
                    }
                } else {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Error';
                    $data['msg'] = 'No se pudo actualizar el cargo';
                }
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg_title'] = 'Error';
            $data['msg'] = 'Error en BD: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function _del($param)
    {
        $data = array(
            'status' => 1,
            'msg' => '',
            'id' => $param['id']
        );

        try {
            if (!isset($param['id'])) {
                throw new Exception('ID no proporcionado');
            }

            $id = intval($param['id']);
            $where = array('id' => $id);

            // Eliminar físicamente el registro
            $result = $this->db->del('cargos', $where);

            if ($result) {
                $data['msg'] = 'Cargo eliminado correctamente';

                // Registrar en historial
                $history = array(
                    'xentity' => 'CARGO',
                    'xaction' => 'DEL-CARGO',
                    'xid' => $id,
                    'xobs' => 'DEL CARGO: ' . $id
                );
                $this->app->add_history($history);
            } else {
                $data['status'] = 0;
                $data['msg'] = 'No se pudo eliminar el cargo';
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['msg'] = 'Error al eliminar el cargo: ' . $e->getMessage();
        }

        print(json_encode($data));
    }

    private function get_departamentos()
    {
        $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre";
        $data = $this->db->fetchAll($sql);
        return $data ? $data : array();
    }

    // Genera suplementos masivos para trabajadores con este cargo
    private function _generateMassiveSupplements($cargoId, $newSalario)
    {
        try {
            // 1. Obtener trabajadores activos con este cargo
            $sqlWorkers = "SELECT id FROM trabajadores WHERE cargos_id = :cargo_id AND trabajador_eliminado = '0'";
            $workers = $this->db->fetchAll($sqlWorkers, array('cargo_id' => $cargoId));

            if (!$workers) return;

            foreach ($workers as $w) {
                $trabajadorId = $w['id'];

                // 2. Desactivar solo los suplementos anteriores (Tipo 3)
                // Los contratos indeterminados (1) o determinados (2) se mantienen activos
                $sqlUpdate = "UPDATE contratos SET es_actual = 0, fecha_fin = CURDATE() WHERE trabajador_id = :tid AND es_actual = 1 AND tipo = '3'";
                $sth = $this->db->conn->prepare($sqlUpdate);
                $sth->execute(array('tid' => $trabajadorId));

                // 3. Insertar nuevo contrato (Suplemento - Tipo 3)
                $sqlInsert = "INSERT INTO contratos (trabajador_id, tipo, fecha_inicio, salario, cargo_id, es_actual) 
                              VALUES (:tid, '3', CURDATE(), :salario, :cargo_id, 1)";

                $stmt = $this->db->conn->prepare($sqlInsert);
                $stmt->execute(array(
                    ':tid' => $trabajadorId,
                    ':salario' => $newSalario,
                    ':cargo_id' => $cargoId
                ));
            }
        } catch (Exception $e) {
            // Log silencioso o manejo de errores
            error_log("Error generando suplementos masivos: " . $e->getMessage());
        }
    }
}
