<?php

/**
 * Módulo Programas de Capacitación
 *
 * @author alvaro
 */
class ProgramaCapacitacion {

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
            case 'list-finalizados':
                $data = $this->_list_finalizados($param);
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

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-programas-capacitacion':
                $data = array();
                $page['title'] = 'Programas de Capacitación';
                $page['subtitle'] = 'Listado de Programas de Capacitación';

                $data_form = array();
                break;
            case 'delete-programas-capacitacion':
                $data = array();
                $page['title'] = 'Finalizar Programas';
                $page['subtitle'] = 'Finalizar Programas de Capacitación';

                $data_form = array();
                break;
            case 'finalizados-programas-capacitacion':
                $data = array();
                $page['title'] = 'Programas Finalizados';
                $page['subtitle'] = 'Listado de Programas de Capacitación Finalizados';

                $data_form = array();
                break;
            case 'programas-capacitacion':
                $data = array();
                $page['title'] = 'Nuevo Programa';
                $page['subtitle'] = 'Registro de Programa de Capacitación';

                // Cargar lista de trabajadores activos para el select Dirigido a
                try {
                    $data_form['trabajadores'] = $this->db->fetchAll("SELECT id, nombre, apellidos FROM trabajadores WHERE trabajador_eliminado = '0' ORDER BY nombre ASC, apellidos ASC");
                } catch (Exception $e) {
                    $data_form['trabajadores'] = array();
                }

                if (isset($param['id'])) {
                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "SELECT * FROM programas_capacitacion"
                            . " where id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Programa: ' . $row['id'] . ' - ' . $row['tema'];
                    }
                } else {
                    $data['modalidad'] = 'Presencial';
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

        try {
            $update = array('fecha_finalizacion' => date('Y-m-d'));
            $where = array('id' => $param['id']);
            $this->app->db->update('programas_capacitacion', $update, $where);

            $history = array(
                'xentity' => 'PROGRAMAS-CAPACITACION',
                'xaction' => 'FINALIZAR-PROGRAMA',
                'xid' => $param['id'],
                'xobs' => 'FINALIZAR PROGRAMA: ' . $param['id'] . ' - Fecha: ' . date('Y-m-d')
            );
            $this->app->add_history($history);
        } catch (Exception $e) {
            $data['status'] = 0;
            $msg = $e->getMessage();
            if (strpos($msg, 'Unknown column') !== false && strpos($msg, 'fecha_finalizacion') !== false) {
                $data['msg'] = 'La columna fecha_finalizacion no existe en la tabla. Agregue la columna o desactive la función de finalizar programas.';
            } else {
                $data['msg'] = 'Error al finalizar el programa: ' . $msg;
            }
        }

        print(json_encode($data));
    }
    
    private function _save($param) {

        $data = array(
            'status' => 1,
            'msg_title' => 'Éxito',
            'msg' => 'Programa de capacitación guardado correctamente'
        );

        // Construir campos a insertar solo con columnas existentes en la tabla
        $possible = array('tema','dirigido_a','responsable','fecha_estimada','modalidad','horas','fecha_finalizacion');
        $cols = $this->_getTableColumns('programas_capacitacion');
        $allowed = array_intersect($possible, $cols);
        $insert = array();
        foreach ($allowed as $col) {
            if (isset($param[$col]) && $param[$col] !== '') {
                $insert[$col] = $param[$col];
            }
        }
        // Asegurar campos obligatorios mínimos
        foreach (array('tema','dirigido_a','responsable','fecha_estimada') as $req) {
            if (!isset($insert[$req]) && isset($param[$req])) {
                $insert[$req] = $param[$req];
            }
        }

        if (!isset($param['id']) || $param['id'] == '') {
            // Insert nuevo
            try {
                // Validación de duplicado por (tema, dirigido_a)
                $temaVal = isset($insert['tema']) ? $insert['tema'] : (isset($param['tema']) ? $param['tema'] : '');
                $dirigidoVal = isset($insert['dirigido_a']) ? $insert['dirigido_a'] : (isset($param['dirigido_a']) ? $param['dirigido_a'] : '');
                if ($temaVal !== '' && $dirigidoVal !== '') {
                    $sql = "SELECT id FROM programas_capacitacion WHERE LOWER(tema) = LOWER(:tema) AND LOWER(dirigido_a) = LOWER(:dirigido) LIMIT 1";
                    $val = array('tema' => $temaVal, 'dirigido' => $dirigidoVal);
                    $exists = $this->db->fetchRow($sql, $val);
                    if ($exists) {
                        $data['status'] = 0;
                        $data['msg_title'] = 'Duplicado';
                        $data['msg'] = 'Ya existe un programa de capacitación con el mismo Tema y Dirigido a.';
                        print(json_encode($data));
                        return;
                    }
                }

                $result = $this->app->db->insert('programas_capacitacion', $insert);
                if ($result) {
                    $lastId = method_exists($this->app->db, 'last_id') ? $this->app->db->last_id() : (method_exists($this->app->db, 'lastInsertId') ? $this->app->db->lastInsertId() : null);
                    if ($lastId) {
                        $data['status'] = 1;
                        $data['msg_title'] = '¡Registro Exitoso!';
                        $data['msg'] = 'El programa de capacitación ha sido registrado correctamente en el sistema';
                        $data['id'] = $lastId;

                        // Agregar al historial
                        $history = array(
                            'xentity' => 'PROGRAMAS-CAPACITACION',
                            'xaction' => 'INSERT-PROGRAMA',
                            'id' => $lastId,
                            'xobs' => 'PROGRAMA: ' . $lastId . ' ' . $insert['tema']
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
                    $friendly = 'Ya existe un registro con el mismo identificador.';
                }
                $data['msg'] = $friendly !== '' ? $friendly : ('Error al insertar el registro: ' . $e->getMessage());
            }
        } else {
            // Update existente
            if (!isset($param['id'])) {
                $data['status'] = 0;
                $data['msg'] = 'ID de programa no proporcionado';
                print(json_encode($data));
                return;
            }

            try {
                // Validación de duplicado por (tema, dirigido_a) excluyendo el propio ID
                $temaVal = isset($insert['tema']) ? $insert['tema'] : (isset($param['tema']) ? $param['tema'] : '');
                $dirigidoVal = isset($insert['dirigido_a']) ? $insert['dirigido_a'] : (isset($param['dirigido_a']) ? $param['dirigido_a'] : '');
                if ($temaVal !== '' && $dirigidoVal !== '') {
                    $sql = "SELECT id FROM programas_capacitacion WHERE LOWER(tema) = LOWER(:tema) AND LOWER(dirigido_a) = LOWER(:dirigido) AND id != :id LIMIT 1";
                    $val = array('tema' => $temaVal, 'dirigido' => $dirigidoVal, 'id' => $param['id']);
                    $exists = $this->db->fetchRow($sql, $val);
                    if ($exists) {
                        $data['status'] = 0;
                        $data['msg_title'] = 'Duplicado';
                        $data['msg'] = 'Ya existe otro programa con el mismo Tema y Dirigido a.';
                        print(json_encode($data));
                        return;
                    }
                }
                $where = array('id' => $param['id']);
                $result = $this->app->db->update('programas_capacitacion', $insert, $where);
                
                if ($result !== false) {
                    $data['status'] = 1;
                    $data['msg_title'] = '¡Actualización Exitosa!';
                    $data['msg'] = 'El programa de capacitación ha sido actualizado correctamente en el sistema';
                    $data['id'] = $param['id'];

                    // Agregar al historial
                    $history = array(
                        'xentity' => 'PROGRAMAS-CAPACITACION',
                        'xaction' => 'UPDATE-PROGRAMA',
                        'id' => $param['id'],
                        'xobs' => 'PROGRAMA: ' . $param['id'] . ' ' . $insert['tema']
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
                p.*
                FROM programas_capacitacion p 
                ORDER BY p.id DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _list_finalizados($param) {
        $data = array();
        $sql = "SELECT 
                p.*
                FROM programas_capacitacion p 
                WHERE p.fecha_finalizacion IS NOT NULL 
                AND p.fecha_finalizacion != ''
                ORDER BY p.fecha_finalizacion DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    // Obtiene y cachea los nombres de las columnas de una tabla
    private function _getTableColumns($tableName) {
        static $cache = array();
        if (isset($cache[$tableName])) {
            return $cache[$tableName];
        }
        $cols = array();
        try {
            $rows = $this->db->fetchAll('DESCRIBE ' . $tableName);
            if ($rows && is_array($rows)) {
                foreach ($rows as $r) {
                    if (isset($r['Field'])) {
                        $cols[] = $r['Field'];
                    } elseif (isset($r[0])) {
                        // Por compatibilidad si fetchAll devuelve índices numéricos
                        $cols[] = $r[0];
                    }
                }
            }
        } catch (Exception $e) {
            // Si falla, devolver arreglo vacío para no romper flujo
            $cols = array();
        }
        $cache[$tableName] = $cols;
        return $cols;
    }
}
