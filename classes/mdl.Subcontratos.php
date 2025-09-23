<?php

/**
 * Módulo Subcontratos
 *
 * @author alvaro
 */
class Subcontrato {

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
            case 'list-subcontratos':
                $data = array();
                $page['title'] = 'Subcontratos';
                $page['subtitle'] = 'Listado de Subcontratos de Servicios';

                $data_form = array();
                break;
            case 'delete-subcontratos':
                $data = array();
                $page['title'] = 'Dar de Baja Subcontratos';
                $page['subtitle'] = 'Dar de Baja Subcontratos';

                $data_form = array();
                break;
            case 'bajas-subcontratos':
                $data = array();
                $page['title'] = 'Subcontratos Dados de Baja';
                $page['subtitle'] = 'Listado de Subcontratos Dados de Baja';

                $data_form = array();
                break;
            case 'subcontratos':
                $data = array();
                $page['title'] = 'Nuevo Subcontrato';
                $page['subtitle'] = 'Registro de Subcontrato de Servicios';

                if (isset($param['id'])) {
                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "SELECT * FROM subcontratos"
                            . " where id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $data = $row;
                        $page['subtitle'] = 'Subcontrato: ' . $row['id'] . ' - ' . $row['persona_nombre'];
                    }
                } else {
                    $data['estatus'] = 'TCP';
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
            'fecha_fin' => date('Y-m-d')
        );
        $where = array(
            'id' => $param['id']
        );
        $this->app->db->update('subcontratos', $update, $where);

        $history = array(
            'xentity' => 'SUBCONTRATOS',
            'xaction' => 'BAJA-SUBCONTRATO',
            'xid' => $param['id'],
            'xobs' => 'BAJA SUBCONTRATO: ' . $param['id'] . ' - Fecha: ' . date('Y-m-d')
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }
    
    private function _save($param) {
        // Debug: Guardar los parámetros recibidos en un archivo de log
        $log = date('Y-m-d H:i:s') . " - Parámetros recibidos:\n";
        $log .= print_r($param, true) . "\n";
        file_put_contents('debug_subcontratos.log', $log, FILE_APPEND);

        $data = array(
            'status' => 1,
            'msg_title' => 'Éxito',
            'msg' => 'Subcontrato guardado correctamente'
        );

        // Campos del formulario
        $insert = array(
            'persona_nombre' => $param['persona_nombre'],
            'estatus' => $param['estatus'],
            'entidad_representada' => $param['entidad_representada'],
            'servicio_objeto' => $param['servicio_objeto'],
            'fecha_inicio' => $param['fecha_inicio'],
            'areas_acceso' => $param['areas_acceso']
        );

        // Solo agregar fecha_fin si no está vacía
        if (!empty($param['fecha_fin'])) {
            $insert['fecha_fin'] = $param['fecha_fin'];
        }

        if (!isset($param['id']) || $param['id'] == '') {
            // Insert nuevo
            try {
                $result = $this->app->db->insert('subcontratos', $insert);
                if ($result) {
                    $lastId = $this->app->db->lastInsertId();
                    if ($lastId) {
                        $data['status'] = 1;
                        $data['msg_title'] = '¡Registro Exitoso!';
                        $data['msg'] = 'El subcontrato ha sido registrado correctamente en el sistema';
                        $data['id'] = $lastId;

                        // Agregar al historial
                        $history = array(
                            'xentity' => 'SUBCONTRATOS',
                            'xaction' => 'INSERT-SUBCONTRATO',
                            'id' => $lastId,
                            'xobs' => 'SUBCONTRATO: ' . $lastId . ' ' . $insert['persona_nombre']
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
                file_put_contents('debug_subcontratos.log', $errorLog, FILE_APPEND);

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
                $data['msg'] = 'ID de subcontrato no proporcionado';
                print(json_encode($data));
                return;
            }

            try {
                $where = array('id' => $param['id']);
                $result = $this->app->db->update('subcontratos', $insert, $where);
                
                if ($result !== false) {
                    $data['status'] = 1;
                    $data['msg_title'] = '¡Actualización Exitosa!';
                    $data['msg'] = 'El subcontrato ha sido actualizado correctamente en el sistema';
                    $data['id'] = $param['id'];

                    // Agregar al historial
                    $history = array(
                        'xentity' => 'SUBCONTRATOS',
                        'xaction' => 'UPDATE-SUBCONTRATO',
                        'id' => $param['id'],
                        'xobs' => 'SUBCONTRATO: ' . $param['id'] . ' ' . $insert['persona_nombre']
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
                file_put_contents('debug_subcontratos.log', $errorLog, FILE_APPEND);

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
                s.*
                FROM subcontratos s 
                WHERE (s.fecha_fin IS NULL OR s.fecha_fin = '')
                ORDER BY s.id DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    private function _list_bajas($param) {
        $data = array();
        $sql = "SELECT 
                s.*
                FROM subcontratos s 
                WHERE s.fecha_fin IS NOT NULL 
                AND s.fecha_fin != ''
                ORDER BY s.fecha_fin DESC";
        
        $data = $this->db->fetchAll($sql);
        return $data;
    }
}
