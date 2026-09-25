<?php

/**
 * Módulo: Análisis de Asistencia
 * Proporciona estadísticas y análisis de patrones de asistencia
 * 
 * @author Sistema RH
 */
class AnalisisAsistencia
{
    private $app;
    private $db;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app->db;
    }

    /**
     * Punto de entrada para la API
     * Métodos disponibles:
     * - estadisticas: Obtiene estadísticas generales
     * - kmeans: Ejecuta análisis K-Means (análisis de patrones)
     */
    public function api($param)
    {
        $method = isset($param['method']) ? $param['method'] : '';
        
        switch ($method) {
            case 'estadisticas':
                $data = $this->_get_estadisticas($param);
                print(json_encode($data));
                break;
            case 'kmeans':
                $data = $this->_get_kmeans($param);
                print(json_encode($data));
                break;
            default:
                print(json_encode(['status' => 0, 'msg' => 'Método no válido: ' . $method]));
                break;
        }
    }

    /**
     * Obtiene estadísticas de asistencia por período
     */
    private function _get_estadisticas($param)
    {
        try {
            $mes = isset($param['mes']) ? $param['mes'] : date('Y-m');
            $ubicacion = isset($param['ubicacion']) && !empty($param['ubicacion']) ? $param['ubicacion'] : null;

            // Convertir mes a rango de fechas
            $fecha_inicio = $mes . '-01';
            $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

            $where = [
                "DATE(ra.fecha) >= '$fecha_inicio'",
                "DATE(ra.fecha) <= '$fecha_fin'",
                "t.trabajador_eliminado = 0"
            ];

            if ($ubicacion) {
                $where[] = "t.ubicacion = " . intval($ubicacion);
            }

            // Total de trabajadores activos en el período
            $sql_total = "SELECT COUNT(DISTINCT ra.trabajador_id) as total
                         FROM registro_asistencia ra
                         INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                         WHERE " . implode(' AND ', $where);

            $result_total = $this->db->fetchRow($sql_total);
            $total_trabajadores = $result_total['total'] ?? 0;

            // Presentes
            $where_presentes = array_merge($where, [
                "(ra.ausencia = 0 OR ra.ausencia IS NULL)",
                "ra.hora_entrada IS NOT NULL"
            ]);
            $sql_presentes = "SELECT COUNT(DISTINCT ra.trabajador_id) as presentes
                             FROM registro_asistencia ra
                             INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                             WHERE " . implode(' AND ', $where_presentes);
            $result_presentes = $this->db->fetchRow($sql_presentes);
            $total_presentes = $result_presentes['presentes'] ?? 0;

            // Ausentes
            $sql_ausentes = "SELECT COUNT(DISTINCT t.id) as ausentes
                            FROM trabajadores t
                            LEFT JOIN registro_asistencia ra ON t.id = ra.trabajador_id
                                AND DATE(ra.fecha) >= '$fecha_inicio'
                                AND DATE(ra.fecha) <= '$fecha_fin'
                            WHERE t.trabajador_eliminado = 0
                            AND t.estatus = 'activo'
                            AND ra.id IS NULL";
            if ($ubicacion) {
                $sql_ausentes .= " AND t.ubicacion = " . intval($ubicacion);
            }
            $result_ausentes = $this->db->fetchRow($sql_ausentes);
            $total_ausentes = $result_ausentes['ausentes'] ?? 0;

            // Retrasos
            $where_tardanzas = array_merge($where, [
                "ra.tardanza > 0"
            ]);
            $sql_tardanzas = "SELECT COUNT(DISTINCT ra.trabajador_id) as tardanzas
                             FROM registro_asistencia ra
                             INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                             WHERE " . implode(' AND ', $where_tardanzas);
            $result_tardanzas = $this->db->fetchRow($sql_tardanzas);
            $total_tardanzas = $result_tardanzas['tardanzas'] ?? 0;

            // Promedio de hora de entrada
            $sql_promedio = "SELECT AVG(TIME_TO_SEC(ra.hora_entrada) / 3600) as hora_promedio
                            FROM registro_asistencia ra
                            INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                            WHERE " . implode(' AND ', $where) . "
                            AND ra.hora_entrada IS NOT NULL";
            $result_promedio = $this->db->fetchRow($sql_promedio);
            $hora_promedio = $result_promedio['hora_promedio'] ? round($result_promedio['hora_promedio'], 2) : 0;

            return [
                'status' => 1,
                'mes' => $mes,
                'periodo' => "$fecha_inicio a $fecha_fin",
                'total_trabajadores' => intval($total_trabajadores),
                'presentes' => intval($total_presentes),
                'ausentes' => intval($total_ausentes),
                'tardanzas' => intval($total_tardanzas),
                'hora_promedio_entrada' => $hora_promedio,
                'porcentaje_presencia' => $total_trabajadores > 0 ? round(($total_presentes / $total_trabajadores) * 100, 2) : 0
            ];

        } catch (Exception $e) {
            error_log('Error en AnalisisAsistencia->_get_estadisticas: ' . $e->getMessage());
            return [
                'status' => 0,
                'msg' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene datos para análisis K-Means (patrones de entrada)
     */
    private function _get_kmeans($param)
    {
        try {
            $mes = isset($param['mes']) ? $param['mes'] : date('Y-m');
            $k = isset($param['k']) ? intval($param['k']) : 3;
            $ubicacion = isset($param['ubicacion']) && !empty($param['ubicacion']) ? $param['ubicacion'] : null;

            $fecha_inicio = $mes . '-01';
            $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

            $where = [
                "DATE(ra.fecha) >= '$fecha_inicio'",
                "DATE(ra.fecha) <= '$fecha_fin'",
                "t.trabajador_eliminado = 0",
                "ra.hora_entrada IS NOT NULL"
            ];

            if ($ubicacion) {
                $where[] = "t.ubicacion = " . intval($ubicacion);
            }

            // Obtener datos de horas de entrada por trabajador
            $sql = "SELECT 
                        ra.trabajador_id,
                        t.nombre,
                        t.apellidos,
                        t.carnet_identidad,
                        COUNT(ra.id) as registros,
                        AVG(TIME_TO_SEC(ra.hora_entrada)) as hora_promedio_sec,
                        STD(TIME_TO_SEC(ra.hora_entrada)) as desv_std_sec,
                        MIN(ra.hora_entrada) as hora_min,
                        MAX(ra.hora_entrada) as hora_max
                    FROM registro_asistencia ra
                    INNER JOIN trabajadores t ON ra.trabajador_id = t.id
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY ra.trabajador_id, t.nombre, t.apellidos, t.carnet_identidad
                    HAVING registros >= 5"; // Mínimo 5 registros para análisis confiable

            $data_trabajadores = $this->db->fetchAll($sql);

            // Convertir segundos a horas
            $datos_procesados = [];
            foreach ($data_trabajadores as $row) {
                $hora_prom = $row['hora_promedio_sec'] / 3600; // Convertir a horas
                $desv_std = $row['desv_std_sec'] ? ($row['desv_std_sec'] / 60) : 0; // Convertir a minutos
                
                $datos_procesados[] = [
                    'trabajador_id' => $row['trabajador_id'],
                    'nombre_completo' => $row['nombre'] . ' ' . $row['apellidos'],
                    'carnet_identidad' => $row['carnet_identidad'],
                    'registros' => intval($row['registros']),
                    'hora_promedio' => round($hora_prom, 2),
                    'variabilidad' => round($desv_std, 2), // Desviación en minutos
                    'hora_min' => $row['hora_min'],
                    'hora_max' => $row['hora_max']
                ];
            }

            return [
                'status' => 1,
                'mes' => $mes,
                'k' => $k,
                'total_trabajadores' => count($datos_procesados),
                'datos' => $datos_procesados
            ];

        } catch (Exception $e) {
            error_log('Error en AnalisisAsistencia->_get_kmeans: ' . $e->getMessage());
            return [
                'status' => 0,
                'msg' => 'Error al obtener datos K-Means: ' . $e->getMessage()
            ];
        }
    }
}

?>
