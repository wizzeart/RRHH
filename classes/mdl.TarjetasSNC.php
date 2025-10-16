<?php
/**
 * Módulo Tarjetas SNC225 - API
 */
class TarjetasSNC {
    var $app;
    var $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        $method = isset($param['method']) ? $param['method'] : 'list';
        switch ($method) {
            case 'list':
            default:
                $data = $this->_list($param);
                print(json_encode($data));
                break;
        }
    }

    private function _list($param) {
        // Lista de tarjetas, uniendo trabajador para mostrar nombre/CI
        $sql = "SELECT 
                    t225.id,
                    t225.trabajador_id,
                    t225.periodo,
                    t225.tiempo_trabajo,
                    t225.salarios_devengados,
                    t225.archivo_digital,
                    t225.fecha_inicio,
                    t225.fecha_cierre,
                    tr.carnet_identidad,
                    CONCAT(tr.nombre, ' ', tr.apellidos, CASE WHEN COALESCE(tr.apellidos_segundos,'')<>'' THEN CONCAT(' ', tr.apellidos_segundos) ELSE '' END) AS nombre_completo
                FROM tarjetas_snc225 t225
                LEFT JOIN trabajadores tr ON tr.id = t225.trabajador_id
                ORDER BY t225.id DESC";
        try {
            $rows = $this->db->fetchAll($sql);
        } catch (Exception $e) {
            $rows = array();
        }

        // Formateo para bootstrap-table
        foreach ($rows as &$r) {
            // Displays
            $r['periodo_display'] = ($r['periodo'] && $r['periodo']!='0000-00-00') ? date('Y-m', strtotime($r['periodo'])) : '';
            $r['tiempo_trabajo_display'] = is_null($r['tiempo_trabajo']) ? '' : number_format((float)$r['tiempo_trabajo'], 2);
            $r['salarios_devengados_display'] = is_null($r['salarios_devengados']) ? '' : number_format((float)$r['salarios_devengados'], 2);
            $r['fecha_inicio_display'] = ($r['fecha_inicio'] && $r['fecha_inicio']!='0000-00-00') ? date('Y-m-d', strtotime($r['fecha_inicio'])) : '';
            $r['fecha_cierre_display'] = ($r['fecha_cierre'] && $r['fecha_cierre']!='0000-00-00') ? date('Y-m-d', strtotime($r['fecha_cierre'])) : '';

            // Acciones simples: ver tarjeta (usa la plantilla PHP creada)
            $trabId = (int)($r['trabajador_id'] ?? 0);
            $verUrl = "/docs/tarjetaSNC225/TarjetaSNC225.php?trabajador_id={$trabId}";
            $r['acciones'] = '<a class="btn btn-xs btn-primary" target="_blank" href="' . $verUrl . '"><i class="fa fa-eye"></i> Ver</a>';
        }
        unset($r);

        return $rows;
    }
}
