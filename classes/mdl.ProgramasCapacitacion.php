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

        $update = array(
            'fecha_finalizacion' => date('Y-m-d')
        );
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->update('programas_capacitacion', $update, $where);

        $history = array(
            'xentity' => 'PROGRAMAS-CAPACITACION',
            'xaction' => 'FINALIZAR-PROGRAMA',
            'xid' => $param['id'],
            'xobs' => 'FINALIZAR PROGRAMA: ' . $param['id'] . ' - Fecha: ' . date('Y-m-d')
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }
    
    private function _save($param) {
        // Debug: Guardar los parámetros recibidos en un archivo de log
        $log = date('Y-m-d H:i:s') . " - Parámetros recibidos:\n";
        $log .= print_r($param, true) . "\n";
        file_put_contents('debug_programas_capacitacion.log', $log, FILE_APPEND);

        $data = array(
            'status' => 1,
            'msg_title' => 'Éxito',
            'msg' => 'Programa de capacitación guardado correctamente'
        );

        // Campos del formulario - ajustado a la estructura de la tabla
        $insert = array(
            'tema' => $param['tema'],
            'dirigido_a' => $param['dirigido_a'],
            'responsable' => $param['responsable'],
            'fecha_estimada' => $param['fecha_estimada']
        );

        // Agregar campos adicionales si están presentes
        if (!empty($param['modalidad'])) {
            $insert['modalidad'] = $param['modalidad'];
        }
        if (!empty($param['horas'])) {
            $insert['horas'] = $param['horas'];
        }

        // Solo agregar fecha_finalizacion si no está vacía
        if (!empty($param['fecha_finalizacion'])) {
            $insert['fecha_finalizacion'] = $param['fecha_finalizacion'];
        }

        if (!isset($param['id']) || $param['id'] == '') {
            // Insert nuevo
            try {
                $result = $this->app->db->insert('programas_capacitacion', $insert);
                if ($result) {
                    $lastId = $this->app->db->lastInsertId();
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
                // Log del error original para diagnóstico
                $errorLog = date('Y-m-d H:i:s') . " - Error al insertar:\n";
                $errorLog .= $e->getMessage() . "\n";
                file_put_contents('debug_programas_capacitacion.log', $errorLog, FILE_APPEND);

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
                // Log del error original para diagnóstico
                $errorLog = date('Y-m-d H:i:s') . " - Error al actualizar:\n";
                $errorLog .= $e->getMessage() . "\n";
                file_put_contents('debug_programas_capacitacion.log', $errorLog, FILE_APPEND);

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
                WHERE (p.fecha_finalizacion IS NULL OR p.fecha_finalizacion = '')
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
}
