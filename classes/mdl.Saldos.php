<?php

class Saldos {

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
            case 'list-trabajadores':
                $data = $this->_list_trabajadores($param);
                print(json_encode($data));
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-saldos':
                $data = array();
                $page['title'] = 'Saldos';
                $page['subtitle'] = 'Tarifas por Hora';
                $data_form = array();
                break;

            case 'saldos':
                $data = array();
                $page['title'] = 'Saldos';
                $page['subtitle'] = 'Formulario de Saldos';
                $data_form = array();
                $action = 'insert';
                break;
        }
    }

    private function _list_trabajadores($param) {
        $where = [];
        $vals = [];
        
        // Búsqueda por nombre, apellidos, CI o cargo
        if (isset($param['search']) && trim($param['search']) !== '') {
            $where[] = '(t.nombre LIKE :search OR t.apellidos LIKE :search OR t.carnet_identidad LIKE :search OR c.nombre LIKE :search)';
            $vals['search'] = '%' . $param['search'] . '%';
        }

        $cond = '';
        if (!empty($where)) {
            $cond = ' WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT 
                    t.id,
                    CONCAT(t.nombre, ' ', t.apellidos) as nombre,
                    t.carnet_identidad as ci,
                    t.cargos_id,
                    c.nombre as cargo_nombre,
                    c.salario as cargo_salario
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                " . $cond .
                " ORDER BY t.nombre ASC";

        if (!empty($vals)) {
            $data = $this->db->fetchAll($sql, $vals);
        } else {
            $data = $this->db->fetchAll($sql);
        }
        return $data;
    }
}
