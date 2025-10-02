<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Home {

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
            case 'getInitialData':
                try {
                    // Obtener estadísticas rápidas
                    $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN trabajador_eliminado = 0 THEN 1 ELSE 0 END) as activos,
                        ROUND(AVG(edad)) as promedioEdad,
                        COUNT(DISTINCT cargos_id) as totalCargos
                    FROM trabajadores 
                    WHERE trabajador_eliminado = 0";
                    
                    $result = $this->db->fetchRow($sql);
                    
                    if (!$result) {
                        throw new Exception("Error al obtener datos de trabajadores");
                    }

                    $quickStats = [
                        'total' => (int)$result['total'],
                        'activos' => (int)$result['activos'],
                        'promedioEdad' => (int)$result['promedioEdad'],
                        'totalCargos' => (int)$result['totalCargos']
                    ];

                    // Contadores adicionales: Subcontratos, Contratos, Capacitaciones
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM subcontratos");
                        $quickStats['totalSubcontratos'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) { $quickStats['totalSubcontratos'] = 0; }

                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM contratos");
                        $quickStats['totalContratos'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) { $quickStats['totalContratos'] = 0; }

                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM programas_capacitacion");
                        $quickStats['totalCapacitaciones'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) { $quickStats['totalCapacitaciones'] = 0; }

                    // Bolsas de empleo
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM bolsa_empleo");
                        $quickStats['totalBolsas'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) { $quickStats['totalBolsas'] = 0; }

                    // Usuarios (conteo total)
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM usuarios");
                        $quickStats['totalUsuarios'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) { $quickStats['totalUsuarios'] = 0; }

                    // Trabajadores dados de baja (trabajador_eliminado IS NULL o vacío)
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM trabajadores WHERE trabajador_eliminado IS NULL OR trabajador_eliminado = ''");
                        $quickStats['totalBajasTrabajadores'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) { $quickStats['totalBajasTrabajadores'] = 0; }

                    // Obtener lista de trabajadores (id, nombre, apellidos, cargos_id, cargo)
                    $workersSql = "SELECT t.id, t.nombre, t.apellidos, t.cargos_id, c.nombre AS cargo
                                   FROM trabajadores t
                                   LEFT JOIN cargos c ON c.id = t.cargos_id
                                   WHERE t.trabajador_eliminado = 0
                                   ORDER BY t.apellidos, t.nombre
                                   LIMIT 1000"; // limit para evitar respuestas enormes

                    $workers = $this->db->fetchAll($workersSql);

                    // Obtener lista de cargos
                    $cargosSql = "SELECT id, nombre, descripcion, salario FROM cargos ORDER BY nombre";
                    $cargos = $this->db->fetchAll($cargosSql);

                    $quickStats['totalCargos'] = count($cargos);

                    // Intentar obtener departamentos si existe la tabla 'departamentos'
                    $departamentos = [];
                    try {
                        $depSql = "SELECT id, nombre FROM departamentos ORDER BY nombre";
                        $departamentos = $this->db->fetchAll($depSql);
                    } catch (Exception $e) {
                        // Si no existe tabla departamentos, construir lista a partir de cargos (por departamento_id)
                        $tmp = [];
                        // foreach ($cargos as $c) {
                        //     $did = isset($c['departamento_id']) ? $c['departamento_id'] : null;
                        //     if ($did !== null && !isset($tmp[$did])) {
                        //         $tmp[$did] = ['id' => $did, 'nombre' => 'Departamento ' . $did];
                        //     }
                        // }
                        $departamentos = array_values($tmp);
                    }

                    // Obtener pases de acceso (recientes)
                    $pasesSql = "SELECT id, trabajador_id, subcontrato_id, areas_acceso, fecha_generacion, vigente FROM pases_acceso ORDER BY fecha_generacion DESC LIMIT 1000";
                    $pases = $this->db->fetchAll($pasesSql);

                    echo json_encode([
                        'status' => 1,
                        'data' => [
                            'quickStats' => $quickStats,
                            'workers' => $workers,
                            'cargos' => $cargos,
                            'departamentos' => $departamentos,
                            'pases' => $pases
                        ]
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'status' => 0,
                        'error' => $e->getMessage()
                    ]);
                }
                break;
        }
    }

    private function getQuickStats() {
        // Total de trabajadores (no eliminados)
        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM trabajadores WHERE trabajador_eliminado = 0");
        $total = $row ? $row['val'] : 0;

        // Trabajadores activos (no eliminados)
        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM trabajadores WHERE estatus = 'ACTIVO' AND trabajador_eliminado = 0");
        $activos = $row ? $row['val'] : 0;

        // Promedio de edad
        $row = $this->db->fetchRow("SELECT ROUND(AVG(edad)) AS val FROM trabajadores WHERE trabajador_eliminado = 0");
        $promedioEdad = $row ? $row['val'] : 0;

        // Total de cargos únicos
        $row = $this->db->fetchRow("SELECT COUNT(DISTINCT cargos_id) AS val FROM trabajadores WHERE trabajador_eliminado = 0");
        $totalCargos = $row ? $row['val'] : 0;

        return [
            'total' => (int)$total,
            'activos' => (int)$activos,
            'promedioEdad' => (int)$promedioEdad,
            'totalCargos' => (int)$totalCargos
            
        ];
    }

    private function getDistribucionEdad() {
        $sql = "
            SELECT 
                CASE 
                    WHEN edad < 26 THEN '18-25'
                    WHEN edad < 36 THEN '26-35'
                    WHEN edad < 46 THEN '36-45'
                    WHEN edad < 56 THEN '46-55'
                    ELSE '56+'
                END as rango_edad,
                COUNT(*) as total
            FROM (
                SELECT TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad
                FROM trabajadores
                WHERE fecha_nacimiento IS NOT NULL
            ) as edades
            GROUP BY 
                CASE 
                    WHEN edad < 26 THEN '18-25'
                    WHEN edad < 36 THEN '26-35'
                    WHEN edad < 46 THEN '36-45'
                    WHEN edad < 56 THEN '46-55'
                    ELSE '56+'
                END
            ORDER BY 
                CASE rango_edad
                    WHEN '18-25' THEN 1
                    WHEN '26-35' THEN 2
                    WHEN '36-45' THEN 3
                    WHEN '46-55' THEN 4
                    WHEN '56+' THEN 5
                END;
        ";
        
    $result = $this->db->fetchAll($sql);
        
        // Preparar datos para el gráfico
        $labels = [];
        $data = [];
        
        foreach ($result as $row) {
            $labels[] = $row['rango_edad'];
            $data[] = (int)$row['total'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'home':
                // Si el usuario tiene rol de trabajador, redirigir a su ficha
                if ($this->app->rol == 2) {
                    // Obtener el ID del trabajador asociado al usuario

                    header("Location: index.php?module=ficha-trabajador&usuario_id=" . $this->app->user_id);
                    exit();
                }
                
                // Si no es trabajador o no tiene trabajador asociado, cargar el dashboard normal
                $data = array();
                $page['title'] = 'Inicio';
                $page['subtitle'] = 'Resumen situación';

                //die();

                break;
        }
    }
}
