<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Asistencia
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
            case 'list-filter':
                $data = $this->_list_filter($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
        }
    }

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-asistencias':
                $data = array();
                $page['title'] = 'Asistencias';
                $page['subtitle'] = 'Listado de Asistencias';

                $data_form = array();
                //$data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                break;
        }
    }

    private function _save($param)
    {
        try {
            if ($param['ausencia'] == 'on') {
                $param['ausencia'] = 1;
            } else {
                $param['ausencia'] = 0;
            }
            if ($param['tipo_ausencia'] != 'Injustificada' && $param['ausencia'] == 1) {
                $param['ausencia'] = 0;
            }
            //remove module and method from array
            unset($param['module']);
            unset($param['method']);
            $id = $param['id'];
            unset($param['id']);
            $result = $this->db->update('registro_asistencia', $param, array('id' => $id));
            if ($result) {
                print(json_encode(array('status' => 1, 'msg' => 'Registro guardado correctamente')));
            } else {
                print(json_encode(array('status' => 0, 'msg' => 'Error al guardar el registro')));
            }
        } catch (Exception $e) {
            error_log('Error en Asistencia->_save: ' . $e->getMessage());
            print(json_encode(array('status' => 0, 'msg' => 'Error al guardar el registro')));
        }
    }
    private function _list_filter($param)
    {
        try {
            $data = array();
            $where = ["t.trabajador_eliminado = '0'"];
            $params = [];

            // Filtro por rango de fechas
            if (!empty($param['fecha_desde'])) {
                $where[] = "ra.fecha >= :fecha_desde";
                $params[':fecha_desde'] = $param['fecha_desde'];
            }
            if (!empty($param['fecha_hasta'])) {
                $where[] = "ra.fecha <= :fecha_hasta";
                $params[':fecha_hasta'] = $param['fecha_hasta'];
            }

            // Filtro por trabajador
            if (!empty($param['trabajador_id'])) {
                $where[] = "ra.trabajador_id = :trabajador_id";
                $params[':trabajador_id'] = $param['trabajador_id'];
            }

            // Filtro por estado (1: Presente, 2: Ausente)
            if (!empty($param['estado'])) {
                if ($param['estado'] == '1' ) { // Presente
                    $where[] = "(ra.ausencia = 0 AND (ra.tipo_ausencia IS NULL OR ra.tipo_ausencia = ''))";
                } elseif ($param['estado'] == '2') { // Ausente
                    $where[] = "(ra.ausencia = 1 OR (ra.tipo_ausencia IS NOT NULL AND ra.tipo_ausencia != ''))";
                    
                    // Filtro por tipo de ausencia si es que se seleccionó
                    if (!empty($param['tipo_ausencia'])) {
                        $where[] = "ra.tipo_ausencia = :tipo_ausencia";
                        $params[':tipo_ausencia'] = $param['tipo_ausencia'];
                    }
                }
            }

            $sql = "SELECT 
                    ra.id,
                    ra.trabajador_id,
                    ra.fecha,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.ausencia,
                    ra.tardanza,
                    ra.tipo_ausencia,
                    ra.justificacion,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    c.nombre as cargo_nombre
                FROM registro_asistencia ra
                INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ra.fecha DESC, t.nombre, t.apellidos";


            $data = $this->db->fetchAll($sql,$params);
            return $data;
        } catch (Exception $e) {
            // Registrar el error en el log
            error_log('Error en Asistencia->_list: ' . $e->getMessage());

            // Devolver un array vacío en caso de error
            return array();
        }
    }

    private function _list($param)
    {
        try {
            $data = array();
            $sql = "SELECT 
                    ra.id,
                    ra.trabajador_id,
                    ra.fecha,
                    ra.hora_entrada,
                    ra.hora_salida,
                    ra.ausencia,
                    ra.tipo_ausencia,
                    ra.justificacion,
                    ra.tardanza,
                    t.nombre,
                    t.apellidos,
                    t.carnet_identidad,
                    c.nombre as cargo_nombre
                FROM registro_asistencia ra
                INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                LEFT JOIN cargos c ON CAST(t.cargos_id AS UNSIGNED) = c.id
                WHERE t.trabajador_eliminado = '0'
                ORDER BY ra.fecha DESC, ra.hora_entrada DESC";

            $data = $this->db->fetchAll($sql);
            return $data;
        } catch (Exception $e) {
            // Registrar el error en el log
            error_log('Error en Asistencia->_list: ' . $e->getMessage());

            // Devolver un array vacío en caso de error
            return array();
        }
    }
}
