<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Home
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
            case 'getInitialData':
                try {
                    // Obtener estadísticas rápidas
                    $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN t.trabajador_eliminado = 0 THEN 1 ELSE 0 END) as activos,
                       
                        COUNT(DISTINCT t.cargos_id) as totalCargos
                    FROM trabajadores t
                    LEFT JOIN departamentos d ON d.id = t.departamento_id
                    WHERE t.trabajador_eliminado = 0
                    AND t.empresa_id = {$this->app->empresa_id}
                    ORDER BY t.id DESC
                    ";

                    $result = $this->db->fetchRow($sql);

                    if (!$result) {
                        throw new Exception("Error al obtener datos de trabajadores");
                    }

                    $quickStats = [
                        'total' => (int)$result['total'],
                        'activos' => (int)$result['activos'],
                        'totalCargos' => (int)$result['totalCargos']
                    ];

                    // Distribución por sexo (trabajadores activos)
                    try {
                        $sqlSexo = "SELECT
                                SUM(CASE WHEN t.sexo = 'M' THEN 1 ELSE 0 END) AS hombres,
                                SUM(CASE WHEN t.sexo = 'F' THEN 1 ELSE 0 END) AS mujeres,
                                COUNT(*) AS total
                            FROM trabajadores t
                            WHERE t.trabajador_eliminado = 0
                              AND t.empresa_id = {$this->app->empresa_id}
                              AND t.estatus = 'activo'";

                        $rowSexo = $this->db->fetchRow($sqlSexo);
                        $hombres = $rowSexo ? (int)$rowSexo['hombres'] : 0;
                        $mujeres = $rowSexo ? (int)$rowSexo['mujeres'] : 0;
                        $totalSexo = $rowSexo ? (int)$rowSexo['total'] : 0;
                        $quickStats['sexo'] = [
                            'hombres' => $hombres,
                            'mujeres' => $mujeres,
                            'total' => $totalSexo,
                            'porcentaje_hombres' => $totalSexo > 0 ? round(($hombres / $totalSexo) * 100, 2) : 0,
                            'porcentaje_mujeres' => $totalSexo > 0 ? round(($mujeres / $totalSexo) * 100, 2) : 0
                        ];
                    } catch (Exception $e) {
                        $quickStats['sexo'] = [
                            'hombres' => 0,
                            'mujeres' => 0,
                            'total' => 0,
                            'porcentaje_hombres' => 0,
                            'porcentaje_mujeres' => 0
                        ];
                    }

                    // Contadores adicionales: Subcontratos, Contratos, Capacitaciones
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM subcontratos");
                        $quickStats['totalSubcontratos'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['totalSubcontratos'] = 0;
                    }

                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM contratos");
                        $quickStats['totalContratos'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['totalContratos'] = 0;
                    }
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(DISTINCT d.id) AS val
                                                    FROM departamentos d
                                                    INNER JOIN trabajadores t ON t.departamento_id = d.id
                                                    WHERE t.empresa_id = {$this->app->empresa_id}
                                                    AND t.trabajador_eliminado = 0");
                        $quickStats['departamentos'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['departamentos'] = 0;
                    }
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM programas_capacitacion");
                        $quickStats['totalCapacitaciones'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['totalCapacitaciones'] = 0;
                    }

                    // Bolsas de empleo
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM bolsa_empleo where empresa_id = {$this->app->empresa_id}");
                        $quickStats['totalBolsas'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['totalBolsas'] = 0;
                    }

                    // Usuarios (conteo total)
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM usuarios");
                        $quickStats['totalUsuarios'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['totalUsuarios'] = 0;
                    }

                    // Trabajadores dados de baja (trabajador_eliminado IS NULL o vacío)
                    try {
                        $row = $this->db->fetchRow("SELECT COUNT(*) AS val FROM trabajadores t
                        LEFT JOIN departamentos d ON d.id = t.departamento_id
                        WHERE t.trabajador_eliminado IS NULL OR t.trabajador_eliminado = ''
                        AND t.empresa_id = {$this->app->empresa_id}");
                        $quickStats['totalBajasTrabajadores'] = $row ? (int)$row['val'] : 0;
                    } catch (Exception $e) {
                        $quickStats['totalBajasTrabajadores'] = 0;
                    }

                    // Obtener lista de trabajadores (id, nombre, apellidos, cargos_id, cargo)
                    $workersSql = "SELECT t.id,t.carnet_identidad, t.nombre, t.apellidos, t.cargos_id, c.nombre AS cargo
                                   FROM trabajadores t
                                   LEFT JOIN cargos c ON c.id = t.cargos_id
                                   LEFT JOIN departamentos d ON d.id = t.departamento_id
                                   WHERE t.trabajador_eliminado = 0
                                   AND t.empresa_id = {$this->app->empresa_id}
                                   ORDER BY t.id DESC
                                   LIMIT 1000"; // limit para evitar respuestas enormes

                    $workers = $this->db->fetchAll($workersSql);

                    // Obtener lista de cargos
                    $cargosSql = "SELECT id, nombre, descripcion, salario FROM cargos ORDER BY salario DESC";
                    $cargos = $this->db->fetchAll($cargosSql);

                    $quickStats['totalCargos'] = count($cargos);

                    // Intentar obtener departamentos si existe la tabla 'departamentos'
                    $departamentos = [];
                    try {
                        $depSql = "SELECT 
                            d.id, 
                            d.nombre, 
                            COUNT(t.id) AS cantidad
                        FROM 
                            departamentos d
                        LEFT JOIN trabajadores t ON t.departamento_id = d.id 
                            AND t.trabajador_eliminado = '0'
                        GROUP BY 
                            d.id, d.nombre
                        HAVING 
                            cantidad > 0
                        ORDER BY 
                            cantidad DESC";
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

            case 'calendario_eventos':
                $data = $this->_get_calendario_eventos($param);
                print(json_encode($data));
                break;

            case 'obtener_eventos_personalizados':
                $data = $this->_obtener_eventos_personalizados($param);
                print(json_encode($data));
                break;

            case 'agregar_evento_calendario':
                $data = $this->_agregar_evento_calendario($param);
                print(json_encode($data));
                break;

            case 'actualizar_evento_calendario':
                $data = $this->_actualizar_evento_calendario($param);
                print(json_encode($data));
                break;

            case 'eliminar_evento_calendario':
                $data = $this->_eliminar_evento_calendario($param);
                print(json_encode($data));
                break;
        }
    }

    private function getQuickStats()
    {
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

    private function getDistribucionEdad()
    {
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

    public function controlador($param)
    {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'home':
                // Si el usuario tiene rol de trabajador, redirigir a su ficha
                if ($this->app->rol == 2) {
                    // Obtener el ID del trabajador asociado al usuario
                    $sql = "SELECT id FROM trabajadores WHERE usuario_id = :usuario_id";
                    $row = $this->db->fetchRow($sql, ['usuario_id' => $this->app->user_id]);
                    $trabajador_id = $row ? $row['id'] : null;

                    header("Location: index.php?module=ficha-trabajador&id=" . $trabajador_id);
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


    /**
     * Obtener eventos del calendario (cumpleaños)
     */
    private function _get_calendario_eventos($param = array())
    {
        try {
            $db = $this->db;
            $empresa_id = $this->app->empresa_id;
            $anno = date('Y');

            // Obtener cumpleaños de trabajadores
            $sql = "SELECT 
                        id,
                        CONCAT(nombre, ' ', apellidos) as nombre,
                        fecha_nacimiento
                    FROM trabajadores 
                    WHERE empresa_id = :empresa_id 
                    AND trabajador_eliminado=0
                    AND fecha_nacimiento IS NOT NULL";

            $trabajadores = $db->fetchAll($sql, ['empresa_id' => $empresa_id]);

            $eventos = [];

            // Agrupar cumpleaños por fecha (mes-día)
            foreach ($trabajadores as $trabajador) {
                if (!empty($trabajador['fecha_nacimiento'])) {
                    // Extraer mes y día de la fecha de nacimiento
                    $fecha_nac = new DateTime($trabajador['fecha_nacimiento']);
                    $mes = $fecha_nac->format('m');
                    $dia = $fecha_nac->format('d');

                    // Crear la fecha de cumpleaños para el año actual
                    $fecha_key = $anno . '-' . $mes . '-' . $dia;

                    if (!isset($eventos[$fecha_key])) {
                        $eventos[$fecha_key] = [
                            'vacaciones' => [],
                            'cumpleanos' => [],
                            'incidencias' => []
                        ];
                    }

                    $eventos[$fecha_key]['cumpleanos'][] = [
                        'nombre' => $trabajador['nombre'],
                        'id' => $trabajador['id']
                    ];
                }
            }

            // Obtener vacaciones aprobadas
            $sql_vacaciones = "SELECT
                        pv.fecha_inicio,
                        pv.fecha_fin,
                        pv.dias,
                        pv.trabajador_id,
                        CONCAT(t.nombre, ' ', t.apellidos) as nombre
                    FROM plan_vacaciones pv
                    INNER JOIN trabajadores t ON pv.trabajador_id = t.id
                    WHERE t.empresa_id = :empresa_id
                    AND pv.fecha_aprobacion IS NOT NULL
                    AND t.trabajador_eliminado = 0
                    AND pv.fecha_fin >= :fecha_inicio
                    AND pv.fecha_inicio <= :fecha_fin";

            // Buscar vacaciones para el año actual (enero a diciembre)
            $fecha_inicio = $anno . '-01-01';
            $fecha_fin = $anno . '-12-31';

            $vacaciones = $db->fetchAll($sql_vacaciones, [
                'empresa_id' => $empresa_id,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ]);

            // Agregar vacaciones al calendario: SOLO los días concretos del plan (columna
            // `dias`), no todo el rango fecha_inicio..fecha_fin. Antes se recorría el rango día
            // a día, así que un plan que cruzaba un fin de semana marcaba también sábado y
            // domingo como vacación aunque esos días no formaran parte del plan.
            //
            // OJO: `dias` tiene dos formatos según el origen del plan (ver plan_vacaciones.dias
            // dual format): lista "YYYY-MM-DD,YYYY-MM-DD,..." desde la web, o un simple contador
            // numérico desde la app móvil. Solo se trata como lista cuando trae fechas reales;
            // en cualquier otro caso se deriva del rango como último recurso.
            foreach ($vacaciones as $vacacion) {
                $dates = [];
                $diasRaw = isset($vacacion['dias']) ? trim((string) $vacacion['dias']) : '';

                if ($diasRaw !== '' && !is_numeric($diasRaw)) {
                    foreach (explode(',', $diasRaw) as $d) {
                        $d = trim($d);
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                            $dates[] = $d;
                        }
                    }
                }

                if (empty($dates)) {
                    // Sin lista de días utilizable: generarlos a partir del rango como fallback
                    try {
                        if (!empty($vacacion['fecha_inicio']) && !empty($vacacion['fecha_fin'])
                            && $vacacion['fecha_inicio'] !== '0000-00-00' && $vacacion['fecha_fin'] !== '0000-00-00') {
                            $inicio = new DateTime($vacacion['fecha_inicio']);
                            $fin = new DateTime($vacacion['fecha_fin']);
                            $fin->modify('+1 day'); // Para incluir el último día
                            $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);
                            foreach ($periodo as $fecha) {
                                $dates[] = $fecha->format('Y-m-d');
                            }
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }

                foreach ($dates as $fecha_key) {
                    if (!isset($eventos[$fecha_key])) {
                        $eventos[$fecha_key] = [
                            'vacaciones' => [],
                            'cumpleanos' => [],
                            'contratos' => []
                        ];
                    }

                    $eventos[$fecha_key]['vacaciones'][] = [
                        'nombre' => $vacacion['nombre'],
                        'id' => $vacacion['trabajador_id']
                    ];
                }
            }

            // Obtener vencimientos de contratos determinados activos
            $sql_contratos = "SELECT 
                        c.fecha_fin,
                        c.trabajador_id,
                        CONCAT(t.nombre, ' ', t.apellidos) as nombre
                    FROM contratos c
                    INNER JOIN trabajadores t ON c.trabajador_id = t.id
                    WHERE t.empresa_id = :empresa_id
                    AND t.trabajador_eliminado = 0
                    AND c.es_actual = 1 
                    AND c.tipo = 2 
                    AND c.fecha_fin IS NOT NULL
                    AND YEAR(c.fecha_fin) = :anno";

            $contratos = $db->fetchAll($sql_contratos, [
                'empresa_id' => $empresa_id,
                'anno' => $anno
            ]);

            foreach ($contratos as $contrato) {
                // Verificar que la fecha tenga formato válido
                if ($contrato['fecha_fin']) {
                    $fecha_key = $contrato['fecha_fin'];

                    if (!isset($eventos[$fecha_key])) {
                        $eventos[$fecha_key] = [
                            'vacaciones' => [],
                            'cumpleanos' => [],
                            'contratos' => [],
                            'incidencias' => []
                        ];
                    }
                    // Asegurarnos que existan las llaves si el bucket ya existía
                    if (!isset($eventos[$fecha_key]['contratos'])) {
                        $eventos[$fecha_key]['contratos'] = [];
                    }
                    if (!isset($eventos[$fecha_key]['incidencias'])) {
                        $eventos[$fecha_key]['incidencias'] = [];
                    }

                    $eventos[$fecha_key]['contratos'][] = [
                        'nombre' => $contrato['nombre'],
                        'id' => $contrato['trabajador_id']
                    ];
                }
            }

            // Obtener incidencias de la tabla eventos_calendario
            $sql_incidencias = "SELECT 
                        id,
                        fecha,
                        nombre,
                        descripcion,
                        color
                    FROM eventos_calendario
                    WHERE nombre = 'Incidencias'
                    AND empresa_id = :empresa_id
                    AND activo = 1
                    AND YEAR(fecha) = :anno";

            $incidencias = $db->fetchAll($sql_incidencias, [
                'empresa_id' => $empresa_id,
                'anno' => $anno
            ]);

            foreach ($incidencias as $incidencia) {
                if ($incidencia['fecha']) {
                    $fecha_key = $incidencia['fecha'];

                    if (!isset($eventos[$fecha_key])) {
                        $eventos[$fecha_key] = [
                            'vacaciones' => [],
                            'cumpleanos' => [],
                            'contratos' => [],
                            'incidencias' => []
                        ];
                    }
                    // Asegurarnos que exista la llave 'incidencias' si el bucket ya existía
                    if (!isset($eventos[$fecha_key]['incidencias'])) {
                        $eventos[$fecha_key]['incidencias'] = [];
                    }

                    $eventos[$fecha_key]['incidencias'][] = [
                        'nombre' => $incidencia['nombre'],
                        'descripcion' => $incidencia['descripcion'],
                        'color' => $incidencia['color'],
                        'id' => $incidencia['id']
                    ];
                }
            }

            return [
                'status' => 1,
                'eventos' => $eventos,
                'anno' => (int)$anno
            ];
        } catch (Exception $e) {
            return [
                'status' => 0,
                'msg' => 'Error al obtener eventos del calendario',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Agregar evento personalizado
     */
    private function _agregar_evento_calendario($param = array())
    {
        try {
            $db = $this->db;

            if (empty($param['fecha']) || empty($param['nombre'])) {
                return array(
                    'status' => 0,
                    'msg' => 'Fecha y nombre son requeridos'
                );
            }

            $fecha = $param['fecha'];
            $nombre = $param['nombre'];
            $descripcion = isset($param['descripcion']) ? $param['descripcion'] : '';
            $color = isset($param['color']) ? $param['color'] : '#f0ad4e';
            $usuario_id = isset($param['usuario_id']) ? (int)$param['usuario_id'] : 0;
            $empresa_id = $this->app->empresa_id;
            $es_incidencia = isset($param['es_incidencia']) ? (int)$param['es_incidencia'] : 0;
            $trabajador_id = isset($param['trabajador_id']) ? (int)$param['trabajador_id'] : NULL;

            $insert = array(
                'fecha' => $fecha,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'color' => $color,
                'usuario_id' => $usuario_id,
                'empresa_id' => $empresa_id,
                'es_incidencia' => $es_incidencia,
                'trabajador_id' => $trabajador_id,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'activo' => 1
            );

            if ($db->insert('eventos_calendario', $insert)) {
                return array(
                    'status' => 1,
                    'msg' => 'Evento creado exitosamente',
                    'id' => $db->last_id()
                );
            } else {
                return array(
                    'status' => 0,
                    'msg' => 'Error al crear evento'
                );
            }
        } catch (Exception $e) {
            error_log("[ERROR] Excepción al agregar evento: " . $e->getMessage());
            return array(
                'status' => 0,
                'msg' => 'Error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Obtener eventos personalizados
     */
    private function _obtener_eventos_personalizados($param = array())
    {
        try {
            $db = $this->db;

            $fecha_inicio = isset($param['fecha_inicio']) ? $param['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($param['fecha_fin']) ? $param['fecha_fin'] : date('Y-m-t');
            $usuario_id = isset($param['usuario_id']) ? (int)$param['usuario_id'] : 0;

            $sql = "SELECT id, fecha, nombre, descripcion, color, 
                           COALESCE(es_incidencia, 0) AS es_incidencia,
                           COALESCE(trabajador_id, NULL) AS trabajador_id
                    FROM eventos_calendario 
                    WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                    AND activo = 1
                    AND empresa_id = {$this->app->empresa_id}";

            if ($usuario_id > 0) {
                $sql .= " AND usuario_id = $usuario_id";
            }

            $sql .= " ORDER BY fecha ASC";

            $rows = $db->fetchAll($sql);
            $eventos = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $eventoData = array(
                        'id' => $row['id'],
                        'fecha' => $row['fecha'],
                        'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'],
                        'color' => $row['color'],
                        'es_incidencia' => (int)$row['es_incidencia'],
                        'trabajador_id' => $row['trabajador_id']
                    );
                    
                    // Si es incidencia y tiene trabajador_id, obtener nombre del trabajador
                    if ((int)$row['es_incidencia'] === 1 && !empty($row['trabajador_id'])) {
                        $trabajadorSql = "SELECT CONCAT(nombre, ' ', apellidos) AS nombre_completo 
                                         FROM trabajadores 
                                         WHERE id = {$row['trabajador_id']}";
                        $trabajadorRow = $db->fetchRow($trabajadorSql);
                        if ($trabajadorRow) {
                            $eventoData['trabajador_nombre'] = $trabajadorRow['nombre_completo'];
                        }
                    }
                    
                    $eventos[] = $eventoData;
                }
            }

            return array(
                'status' => 1,
                'eventos' => $eventos
            );
        } catch (Exception $e) {
            return array(
                'status' => 0,
                'msg' => 'Error: ' . $e->getMessage(),
                'eventos' => array()
            );
        }
    }

    /**
     * Actualizar evento personalizado
     */
    private function _actualizar_evento_calendario($param = array())
    {
        try {
            $db = $this->db;

            if (empty($param['id'])) {
                return array(
                    'status' => 0,
                    'msg' => 'ID del evento es requerido'
                );
            }

            $id = (int)$param['id'];
            $updates = array();

            if (!empty($param['nombre'])) {
                $updates['nombre'] = $param['nombre'];
            }

            if (isset($param['descripcion'])) {
                $updates['descripcion'] = $param['descripcion'];
            }

            if (!empty($param['color'])) {
                $updates['color'] = $param['color'];
            }

            if (!empty($param['fecha'])) {
                $updates['fecha'] = $param['fecha'];
            }

            if (isset($param['es_incidencia'])) {
                $updates['es_incidencia'] = (int)$param['es_incidencia'];
            }

            if (isset($param['trabajador_id'])) {
                $updates['trabajador_id'] = !empty($param['trabajador_id']) ? (int)$param['trabajador_id'] : NULL;
            }

            if (empty($updates)) {
                return array(
                    'status' => 0,
                    'msg' => 'No hay campos para actualizar'
                );
            }

            $updates['fecha_actualizacion'] = date('Y-m-d H:i:s');

            if ($db->update('eventos_calendario', $updates, array('id' => $id))) {
                return array(
                    'status' => 1,
                    'msg' => 'Evento actualizado exitosamente'
                );
            } else {
                return array(
                    'status' => 0,
                    'msg' => 'Error al actualizar evento'
                );
            }
        } catch (Exception $e) {
            return array(
                'status' => 0,
                'msg' => 'Error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Eliminar evento personalizado
     */
    private function _eliminar_evento_calendario($param = array())
    {
        try {
            $db = $this->db;

            if (empty($param['id'])) {
                return array(
                    'status' => 0,
                    'msg' => 'ID del evento es requerido'
                );
            }

            $id = (int)$param['id'];

            // Soft delete - marcar como inactivo
            $updates = array(
                'activo' => 0,
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            );

            if ($db->update('eventos_calendario', $updates, array('id' => $id))) {
                return array(
                    'status' => 1,
                    'msg' => 'Evento eliminado exitosamente'
                );
            } else {
                return array(
                    'status' => 0,
                    'msg' => 'Error al eliminar evento'
                );
            }
        } catch (Exception $e) {
            return array(
                'status' => 0,
                'msg' => 'Error: ' . $e->getMessage()
            );
        }
    }
}
