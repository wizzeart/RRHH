<?php

/**
 * Clase para manejar empresas
 *
 * @author Sistema
 */
class Empresa {

    var $app;
    var $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
        }
    }

    /**
     * Obtener lista de empresas
     */
    private function _list($param) {
        try {
            $sql = "SELECT id, nombre FROM empresa ORDER BY nombre ASC";
            $data = $this->db->fetchAll($sql);
            return $data;
        } catch (Exception $e) {
            error_log("Error obteniendo empresas: " . $e->getMessage());
            return array();
        }
    }
}
