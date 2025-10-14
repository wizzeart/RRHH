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
            case 'list-provincias':
                $data = $this->_get_list_provincias();
                print(json_encode($data));
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

        $data = array(
            'status' => 1,
            'msg_title' => 'Éxito',
            'msg' => 'Subcontrato guardado correctamente'
        );

        // Campos del formulario (usar acceso seguro para evitar warnings y romper el JSON)
        $insert = array(
            'persona_nombre'      => isset($param['persona_nombre']) ? trim($param['persona_nombre']) : '',
            'carnet_identidad'    => isset($param['carnet_identidad']) ? trim($param['carnet_identidad']) : null,
            'estatus'             => isset($param['estatus']) ? trim($param['estatus']) : '',
            'entidad_representada'=> isset($param['entidad_representada']) ? trim($param['entidad_representada']) : null,
            'servicio_objeto'     => isset($param['servicio_objeto']) ? trim($param['servicio_objeto']) : '',
            'fecha_inicio'        => isset($param['fecha_inicio']) ? $param['fecha_inicio'] : null,
            'areas_acceso'        => isset($param['areas_acceso']) ? trim($param['areas_acceso']) : null
        );

        // Solo agregar fecha_fin si no está vacía y es una fecha válida
        if (!empty($param['fecha_fin']) && $param['fecha_fin'] !== '' && $param['fecha_fin'] !== '0000-00-00') {
            $insert['fecha_fin'] = $param['fecha_fin'];
        }

        if (!isset($param['id']) || $param['id'] == '') {
            // Validaciones de duplicados (INSERT)
            // CI único si fue proporcionado
            if (!empty($insert['carnet_identidad'])) {
                $sql = "SELECT id FROM subcontratos WHERE carnet_identidad = :ci LIMIT 1";
                $val = array('ci' => $insert['carnet_identidad']);
                $existing = $this->db->fetchRow($sql, $val);
                if ($existing) {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Duplicado';
                    $data['msg'] = 'Ya existe un subcontrato con el mismo Carnet de Identidad (CI).';
                    print(json_encode($data));
                    return;
                }
            }

            // Nombre único (regla de negocio solicitada)
            if (!empty($insert['persona_nombre'])) {
                $sql = "SELECT id FROM subcontratos WHERE LOWER(persona_nombre) = LOWER(:nombre) AND (fecha_fin IS NULL OR fecha_fin = '') LIMIT 1";
                $val = array('nombre' => $insert['persona_nombre']);
                $existing = $this->db->fetchRow($sql, $val);
                if ($existing) {
                    $data['status'] = 0;
                    $data['msg_title'] = 'Duplicado';
                    $data['msg'] = 'Ya existe un subcontrato activo con el mismo Nombre.';
                    print(json_encode($data));
                    return;
                }
            }
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
                // Validaciones de duplicados (UPDATE)
                if (!empty($insert['carnet_identidad'])) {
                    $sql = "SELECT id FROM subcontratos WHERE carnet_identidad = :ci AND id != :id LIMIT 1";
                    $val = array('ci' => $insert['carnet_identidad'], 'id' => $param['id']);
                    $existing = $this->db->fetchRow($sql, $val);
                    if ($existing) {
                        $data['status'] = 0;
                        $data['msg_title'] = 'Duplicado';
                        $data['msg'] = 'Ya existe otro subcontrato con el mismo Carnet de Identidad (CI).';
                        print(json_encode($data));
                        return;
                    }
                }

                if (!empty($insert['persona_nombre'])) {
                    $sql = "SELECT id FROM subcontratos WHERE LOWER(persona_nombre) = LOWER(:nombre) AND id != :id AND (fecha_fin IS NULL OR fecha_fin = '') LIMIT 1";
                    $val = array('nombre' => $insert['persona_nombre'], 'id' => $param['id']);
                    $existing = $this->db->fetchRow($sql, $val);
                    if ($existing) {
                        $data['status'] = 0;
                        $data['msg_title'] = 'Duplicado';
                        $data['msg'] = 'Ya existe otro subcontrato activo con el mismo Nombre.';
                        print(json_encode($data));
                        return;
                    }
                }
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

    /**
     * Listar provincias (id, nombre)
     */
    private function _get_list_provincias() {
        $sql = "SELECT id, nombre FROM provincia ORDER BY nombre ASC";
        try {
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Listar municipios por provincia
     */
    private function _get_municipios_by_provincia($param) {
        $provincia_id = isset($param['provincia_id']) ? intval($param['provincia_id']) : 0;
        if ($provincia_id <= 0) { return array(); }
        $sql = "SELECT id, nombre, provincia_id FROM municipio WHERE provincia_id = :provincia_id ORDER BY nombre ASC";
        try {
            return $this->db->fetchAll($sql, array('provincia_id' => $provincia_id));
        } catch (Exception $e) {
            return array();
        }
    }
}
