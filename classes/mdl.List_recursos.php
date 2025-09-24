<?php
class List_recursos {
    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $this->action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list();
                print(json_encode($data));
                break;
        }
    }

    private function _list() {
        $data = array();
        
        // Construir la consulta base
        $sql = "SELECT r.*, CONCAT(t.nombre, ' ', t.apellidos) as nombre_trabajador
                FROM recursos r
                LEFT JOIN trabajadores t ON r.trabajador_id = t.id";
        
        $params = array();
        
        // Filtrar por estado si se proporciona
        if (isset($_GET['estado']) && ($_GET['estado'] === '0' || $_GET['estado'] === '1')) {
            $sql .= " WHERE r.estado = ?";
            $params[] = (int)$_GET['estado'];
        }
        
        // Ordenar según corresponda
        if (isset($_GET['estado']) && $_GET['estado'] === '0') {
            $sql .= " ORDER BY r.fecha_entrega_a_rh DESC, r.id DESC";
        } else {
            $sql .= " ORDER BY r.fecha_entrega_a_t DESC, r.id DESC";
        }
        
        // Ejecutar la consulta
        if (!empty($params)) {
            $data = $this->db->fetchAll($sql, $params);
        } else {
            $data = $this->db->fetchAll($sql);
        }
        
        return $data;
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-recursos':
                $page['title'] = 'Recursos';
                $page['subtitle'] = 'Listado de Recursos';

                $data_form = array();
                break;
        }
    }
}
