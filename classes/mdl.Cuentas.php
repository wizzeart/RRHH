<?php

class Cuentas {

    public $db;
    public $app;

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
            default:
                print(json_encode(array('status' => 0, 'msg' => 'Método no encontrado')));
                break;
        }
    }

    public function controlador($param) {
        global $data, $page;
        $data = array();
        // Mantener consistencia con otros controladores que setean $page global
        switch ($param['module']) {
            case 'list-cuentas':
                $page['title'] = 'Cuentas Bancarias';
                $page['subtitle'] = 'Listado de Cuentas Bancarias de Trabajadores';
                break;
            default:
                $page['title'] = 'Cuentas Bancarias';
                $page['subtitle'] = 'Gestión de Cuentas Bancarias';
                break;
        }
        $page['module'] = $param['module'];
    }

    private function _list($param) {
        $data = array();
        
        // Get empresa_id from parameter or session
        $empresaId = isset($param['empresa_id']) ? $param['empresa_id'] : $this->app->empresa_id;
        
        // Consulta con JOIN entre bancos y trabajadores
        $sql = "SELECT 
                b.id,
                b.trabajador_id,
                b.numero_tarjeta_salario,
                b.numero_cuenta_estandar,
                CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo,
                t.carnet_identidad,
                t.estatus
                FROM bancos b
                INNER JOIN trabajadores t ON b.trabajador_id = t.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                WHERE t.trabajador_eliminado = '0'";
        
        // Add empresa filter
        if ($empresaId) {
            $sql .= " AND t.empresa_id = '" . addslashes($empresaId) . "'";
        }
        
        $sql .= " ORDER BY t.apellidos, t.nombre";
        
        try {
            $data = $this->db->fetchAll($sql);
            
            // Formatear datos para mostrar
            foreach ($data as &$row) {
                // Formatear números de tarjeta y cuenta para mostrar solo si existen
                $row['tarjeta_display'] = !empty($row['numero_tarjeta_salario']) ? $row['numero_tarjeta_salario'] : 'No registrada';
                $row['cuenta_display'] = !empty($row['numero_cuenta_estandar']) ? $row['numero_cuenta_estandar'] : 'No registrada';
                
                // Estado del trabajador
                $row['estatus_display'] = ucfirst($row['estatus']);
            }
            
        } catch (Exception $e) {
            error_log("Error en consulta de cuentas bancarias: " . $e->getMessage());
            $data = array();
        }
        
        return $data;
    }
}
?>
