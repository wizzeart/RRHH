<?php

class Prenomina {

    var $app;
    var $db;
    // Flag to cache existence of tarjetas_snc225.tiempo_trabajo (current) during this request
    private $_ttCorrectExists = null;  // tiempo_trabajo (current)

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list-prenomina':
                $data = $this->_list_prenomina($param);
                print(json_encode($data));
                break;
            case 'list-prenomina2':
                $data = $this->_list_prenomina2($param);
                print(json_encode($data));
                break;
            case 'list-departamentos':
                $data = $this->_list_departamentos();
                print(json_encode($data));
                break;
            case 'list-prenomina-id':
                $data = $this->_list_prenomina_id($param);
                print(json_encode($data));
                break;
            case 'save-horas':
                $this->_save_horas();
                break;
            case 'save-prenomina2':
                $this->_save_prenomina2();
                break;
            case 'export-excel':
                $this->_export_excel($param);
                break;
            case 'export-prenomina2':
                $this->_export_prenomina2($param);
                break;
            case 'export-prenomina2-binary':
                $this->_export_prenomina2_binary($param);
                break;
            case 'export-nomina2-binary':
                $this->_export_nomina2_binary($param);
                break;
            case 'list-export-prenomina':
                $data = $this->_list_export_prenomina();
                print(json_encode($data));
                break;
            case 'download-export-prenomina':
                $this->_download_export_prenomina($param);
                break;
        }
    }

    private function _list_prenomina_id($param) {
        $data = array();
        $sql = "SELECT 
                    p.id,
                    p.trabajador_id,
                    p.year,
                    p.month,
                    p.horas,
                    p.tarifa,
                    p.a_cobrar,
                    p.bonif,
                    p.sal_dev,
                    p.ausencias,
                    p.vacaciones,
                    p.pago_vac,
                    p.salario_neto,
                    p.seg_social,
                    p.ing_pers_3,
                    p.ing_pers_5,
                    p.salario_pagar,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre_trabajador,
                    t.carnet_identidad AS ci,
                    c.nombre AS cargo,
                    d.nombre AS departamento
                FROM prenomina p
                INNER JOIN trabajadores t ON p.trabajador_id = t.id
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                WHERE p.trabajador_id = :trabajador_id
                ORDER BY p.year DESC, p.month DESC";
        
        $data = $this->db->fetchAll($sql, array('trabajador_id' => $param['trabajador_id']));
        return $data;
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'prenomina':
                $data = array();
                $page['title'] = 'Prenómina';
                
                $data_form = array();
                break;
            case 'prenomina-2':
                $data = array();
                $page['title'] = 'Prenómina';
                $page['subtitle'] = 'Prenómina';
                $data_form = array();
                break;
        }
    }

    private function _list_prenomina($param) {
        $vals = [];
        $whereParts = ["(t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)"];
        // Debug: registrar parámetros de entrada para ayudar a diagnosticar pestaña 'Todos'
        try { error_log('Prenomina._list_prenomina called with params: ' . json_encode($param)); } catch (Exception $__) { }
        
        // Get month and year for registro_asistencia filtering
        if(!empty($param['mes'])) {
            $mesSeleccionado = $param['mes']; // formato: YYYY-MM
            list($anio, $mes) = explode('-', $mesSeleccionado);
        } else {
            $mesSeleccionado = date('Y-m');
            list($anio, $mes) = explode('-', $mesSeleccionado);
        }
        $vals['mes'] = $mes;
        $vals['anio'] = $anio;

        // Verificar si existe la columna empresa_id en departamentos
        $hasEmpresaId = false;
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos");
            $hasEmpresaId = !empty($cols);
        } catch (Exception $e) {
            $hasEmpresaId = false;
        }

        // Aplicar filtro por empresa si aplica (una sola vez)
        if ($hasEmpresaId && isset($this->app->empresa_id) && $this->app->empresa_id) {
            $vals['eid'] = $this->app->empresa_id;
            $whereParts[] = 't.empresa_id = :eid';
            try { error_log('Prenomina: Aplicando filtro empresa_id = ' . $this->app->empresa_id); } catch (Exception $__) { }
        } else {
            try { error_log('Prenomina: Sin filtro empresa - hasEmpresaId=' . ($hasEmpresaId ? 'true' : 'false') . ', empresa_id=' . (isset($this->app->empresa_id) ? $this->app->empresa_id : 'null')); } catch (Exception $__) { }
        }

        // Normalizar el valor de la pestaña (tab) para soportar 'Todos' en cualquier casing/espacios
        $tabRaw = isset($param['tab']) ? $param['tab'] : '';
        $tabNorm = trim(mb_strtolower($tabRaw));

        // Filtro adicional por pestaña específica (solo si NO es 'todos')
        if ($tabNorm !== '' && $tabNorm !== 'todos') {
            $vals['tab'] = $param['tab'];
            $whereParts[] = 't.departamento_id IN (SELECT id FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab)))';
        } else {
            // Log que estamos en 'todos' para trazabilidad
            try { error_log('Prenomina._list_prenomina using TODOS tab (no department filter)'); } catch (Exception $__) { }
        }

        $cond = '';
        if (!empty($whereParts)) {
            $cond = ' WHERE ' . implode(' AND ', $whereParts);
        }
        try { error_log('Prenomina._list_prenomina SQL cond: ' . $cond . ' params: ' . json_encode($vals)); } catch (Exception $__) { }
        
        // Debug: verificar si hay trabajadores en total
        try {
            $totalTrabajadores = $this->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores");
            error_log("Prenomina: Total trabajadores en BD: " . ($totalTrabajadores['total'] ?? 0));
            
            $totalNoEliminados = $this->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores WHERE (trabajador_eliminado = 0 OR trabajador_eliminado = '0' OR trabajador_eliminado IS NULL)");
            error_log("Prenomina: Total trabajadores no eliminados: " . ($totalNoEliminados['total'] ?? 0));
        } catch (Exception $e) {
            error_log("Prenomina: Error verificando trabajadores: " . $e->getMessage());
        }

        // Verificar rápidamente si hay registros en registro_asistencia para el periodo
        try {
            $checkSql = "SELECT COUNT(*) as total FROM registro_asistencia WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio AND hora_entrada IS NOT NULL AND hora_salida IS NOT NULL";
            $checkRow = $this->db->fetchRow($checkSql, array('mes' => intval($mes), 'anio' => intval($anio)));
            error_log("Prenomina: registros en registro_asistencia para {$mes}/{$anio}: " . ($checkRow['total'] ?? 0));
        } catch (Exception $e) {
            error_log("Prenomina: error comprobando registro_asistencia: " . $e->getMessage());
        }

        // Usar horas guardadas en prenomina (editables por el usuario)
        $sql = "SELECT 
                    t.id,
                    t.id AS expediente,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                    t.carnet_identidad AS ci,
                    -- Smart tariff detection: if salary looks like monthly (>1000) divide by 192, otherwise assume it's hourly
                    COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                    COALESCE(p.horas, 192) AS horas,
                    c.salario AS a_cobrar,
                    NULL AS bonif,
                    c.salario AS sal_dev,
                    (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL 
                                AND pv.dias IS NOT NULL 
                                AND pv.dias <> ''
                                THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0)
                        FROM plan_vacaciones pv
                        WHERE pv.trabajador_id = t.id
                          AND pv.estado IN ('Aprobado','Procesada')
                          AND YEAR(pv.fecha_inicio) = :anio
                          AND MONTH(pv.fecha_inicio) = :mes
                    ) AS vacaciones,
                    TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv.fecha_aprobacion IS NOT NULL 
                                AND pv.dias IS NOT NULL 
                                AND pv.dias <> ''
                                THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0)
                        FROM plan_vacaciones pv
                        WHERE pv.trabajador_id = t.id
                          AND pv.estado IN ('Aprobado','Procesada')
                          AND YEAR(pv.fecha_inicio) = :anio
                          AND MONTH(pv.fecha_inicio) = :mes
                    )), 0) AS pago_vac,
                    COALESCE(p.salario_neto, TRUNCATE((c.salario + TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN pv3.fecha_aprobacion IS NOT NULL 
                                AND pv3.dias IS NOT NULL 
                                AND pv3.dias <> ''
                                THEN (LENGTH(pv3.dias) - LENGTH(REPLACE(pv3.dias, ',', '')) + 1)
                                ELSE 0
                            END
                        ), 0)
                        FROM plan_vacaciones pv3
                        WHERE pv3.trabajador_id = t.id
                          AND pv3.estado IN ('Aprobado','Procesada')
                          AND YEAR(pv3.fecha_inicio) = :anio
                          AND MONTH(pv3.fecha_inicio) = :mes
                    )), 0)), 0)) AS salario_neto,
                    COALESCE(p.seg_social, TRUNCATE(salario_neto * 0.05, 0)) AS seg_social,
                    COALESCE(p.ing_pers_3, 
                        CASE 
                            WHEN (c.salario + TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                                SELECT COALESCE(SUM(
                                    CASE 
                                        WHEN pv4.fecha_aprobacion IS NOT NULL 
                                        AND pv4.dias IS NOT NULL 
                                        AND pv4.dias <> ''
                                        THEN (LENGTH(pv4.dias) - LENGTH(REPLACE(pv4.dias, ',', '')) + 1)
                                        ELSE 0
                                    END
                                ), 0)
                                FROM plan_vacaciones pv4
                                WHERE pv4.trabajador_id = t.id
                                  AND pv4.estado IN ('Aprobado','Procesada')
                                  AND YEAR(pv4.fecha_inicio) = :anio
                                  AND MONTH(pv4.fecha_inicio) = :mes
                            )), 0)) BETWEEN 3260 AND 9510 
                            THEN TRUNCATE((9510 - 3260) * 0.03, 0)
                            WHEN (c.salario + TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                                SELECT COALESCE(SUM(
                                    CASE 
                                        WHEN pv6.fecha_aprobacion IS NOT NULL 
                                        AND pv6.dias IS NOT NULL 
                                        AND pv6.dias <> ''
                                        THEN (LENGTH(pv6.dias) - LENGTH(REPLACE(pv6.dias, ',', '')) + 1)
                                        ELSE 0
                                    END
                                ), 0)
                                FROM plan_vacaciones pv6
                                WHERE pv6.trabajador_id = t.id
                                  AND pv6.estado IN ('Aprobado','Procesada')
                                  AND YEAR(pv6.fecha_inicio) = :anio
                                  AND MONTH(pv6.fecha_inicio) = :mes
                            )), 0)) > 9510 
                            THEN TRUNCATE((9510 - 3260) * 0.03, 0)
                            ELSE 0
                        END
                    ) AS ing_pers_3,
                    COALESCE(p.ing_pers_5, 
                        CASE 
                            WHEN (c.salario + TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                                SELECT COALESCE(SUM(
                                    CASE 
                                        WHEN pv7.fecha_aprobacion IS NOT NULL 
                                        AND pv7.dias IS NOT NULL 
                                        AND pv7.dias <> ''
                                        THEN (LENGTH(pv7.dias) - LENGTH(REPLACE(pv7.dias, ',', '')) + 1)
                                        ELSE 0
                                    END
                                ), 0)
                                FROM plan_vacaciones pv7
                                WHERE pv7.trabajador_id = t.id
                                  AND pv7.estado IN ('Aprobado','Procesada')
                                  AND YEAR(pv7.fecha_inicio) = :anio
                                  AND MONTH(pv7.fecha_inicio) = :mes
                            )), 0)) > 9510 
                            THEN TRUNCATE(((c.salario + TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                                SELECT COALESCE(SUM(
                                    CASE 
                                        WHEN pv8.fecha_aprobacion IS NOT NULL 
                                        AND pv8.dias IS NOT NULL 
                                        AND pv8.dias <> ''
                                        THEN (LENGTH(pv8.dias) - LENGTH(REPLACE(pv8.dias, ',', '')) + 1)
                                        ELSE 0
                                    END
                                ), 0)
                                FROM plan_vacaciones pv8
                                WHERE pv8.trabajador_id = t.id
                                  AND pv8.estado IN ('Aprobado','Procesada')
                                  AND YEAR(pv8.fecha_inicio) = :anio
                                  AND MONTH(pv8.fecha_inicio) = :mes
                            )), 0)) - 9510) * 0.05, 0)
                            ELSE 0
                        END
                    ) AS ing_pers_5,
                    COALESCE(p.salario_pagar, 
                        TRUNCATE(
                            COALESCE(p.salario_neto, 
                                (c.salario + 
                                 TRUNCATE(((COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario / 192 ELSE c.salario END), 0) * 8) * (
                                    SELECT COALESCE(SUM(
                                        CASE 
                                            WHEN pv2.fecha_aprobacion IS NOT NULL 
                                            AND pv2.dias IS NOT NULL 
                                            AND pv2.dias <> ''
                                            THEN (LENGTH(pv2.dias) - LENGTH(REPLACE(pv2.dias, ',', '')) + 1)
                                            ELSE 0
                                        END
                                    ), 0)
                                    FROM plan_vacaciones pv2
                                    WHERE pv2.trabajador_id = t.id
                                      AND pv2.estado IN ('Aprobado','Procesada')
                                      AND YEAR(pv2.fecha_inicio) = :anio
                                      AND MONTH(pv2.fecha_inicio) = :mes
                                )), 0))
                            ) - 
                            (COALESCE(p.seg_social, TRUNCATE(c.salario * 0.05, 0)) + 
                             COALESCE(p.ing_pers_3, 0) + 
                             COALESCE(p.ing_pers_5, 0) +
                             COALESCE(p.ausencias_costo, 0)
                            ), 0)
                    ) AS salario_pagar,
                    COALESCE(d.nombre, '') AS departamento
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :anio AND p.month = :mes
                " . $cond .
                " ORDER BY t.id ASC";

        if (!empty($vals)) {
            // Debug: probar consulta simple primero
            
            $result = $this->db->fetchAll($sql, $vals);
            try { error_log("Prenomina: trabajadores recuperados: " . count($result) . ' for tab=' . (isset($param['tab']) ? $param['tab'] : '')) ; } catch (Exception $__) { }
            $i = 0;
            foreach ($result as $r) {
                if ($i++ >= 10) break;
                $h = isset($r['horas']) ? $r['horas'] : (isset($r['horas_trabajadas']) ? $r['horas_trabajadas'] : 'n/a');
                error_log("Prenomina sample - trabajador_id: {$r['id']}, horas: {$h}");
            }
            return $result;
        }
        $result = $this->db->fetchAll($sql);
        try { error_log("Prenomina: trabajadores recuperados (sin params): " . count($result)); } catch (Exception $__) { }
        return $result;
    }

    /**
     * _list_prenomina2 - Lista de prenómina con fórmulas IML
     * Idéntica a _list_prenomina pero para el módulo Prenomina 2
     */
    private function _list_prenomina2($param) {
        error_log("🔴 LLAMADA A _list_prenomina2() - INICIANDO");
        // Similar a _list_prenomina pero puede tener variaciones específicas
        $vals = [];
        $whereParts = ["(t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)"];
        
        // Get month and year
        if(!empty($param['mes'])) {
            list($anio, $mes) = explode('-', $param['mes']);
        } else {
            $mesSeleccionado = date('Y-m');
            list($anio, $mes) = explode('-', $mesSeleccionado);
        }
        $vals['mes'] = $mes;
        $vals['anio'] = $anio;

        // Verificar si existe la columna empresa_id en trabajadores
        $hasEmpresaIdTrabajadores = false;
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM trabajadores WHERE Field = 'empresa_id'");
            $hasEmpresaIdTrabajadores = !empty($cols);
        } catch (Exception $e) {
            $hasEmpresaIdTrabajadores = false;
        }

        // Verificar si existe la columna empresa_id en departamentos
        $hasEmpresaIdDepts = false;
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos WHERE Field = 'empresa_id'");
            $hasEmpresaIdDepts = !empty($cols);
        } catch (Exception $e) {
            $hasEmpresaIdDepts = false;
        }

        // Aplicar filtro por empresa si está activa
        if (isset($this->app->empresa_id) && $this->app->empresa_id) {
            $vals['eid'] = $this->app->empresa_id;
            
            // Filtrar por empresa_id en trabajadores si existe la columna
            if ($hasEmpresaIdTrabajadores) {
                $whereParts[] = 't.empresa_id = :eid';
                try { error_log('Prenomina2: Aplicando filtro empresa_id en trabajadores = ' . $this->app->empresa_id); } catch (Exception $__) { }
            }
            // O también filtrar por empresa_id en departamentos si existe
            else if ($hasEmpresaIdDepts) {
                $whereParts[] = 'd.empresa_id = :eid';
                try { error_log('Prenomina2: Aplicando filtro empresa_id en departamentos = ' . $this->app->empresa_id); } catch (Exception $__) { }
            }
        }

        // Filtro por tab/departamento
        $tabRaw = isset($param['tab']) ? $param['tab'] : '';
        $tabNorm = trim(mb_strtolower($tabRaw));

        if ($tabNorm !== '' && $tabNorm !== 'todos') {
            $whereParts[] = 't.departamento_id IN (SELECT id FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab)))';
            $vals['tab'] = $tabRaw;
        }

        // Filtro para Prenomina 2: Mostrar trabajadores con contrato actual
        // Lógica correcta:
        // - Contratos anteriores al mes actual → INCLUIR (ya están activos hace tiempo)
        // - Contratos del mes actual iniciados en o antes del día 15 → INCLUIR
        // - Contratos del mes actual iniciados después del día 15 → EXCLUIR (muy nuevos)
        $whereParts[] = "EXISTS (
            SELECT 1 FROM contratos ct 
            WHERE ct.trabajador_id = t.id 
            AND ct.es_actual = 1 
            AND (
                YEAR(ct.fecha_inicio) < YEAR(CURDATE())
                OR MONTH(ct.fecha_inicio) < MONTH(CURDATE())
                OR (MONTH(ct.fecha_inicio) = MONTH(CURDATE()) AND DAY(ct.fecha_inicio) <= 15)
            )
        )";

        $cond = '';
        if (!empty($whereParts)) {
            $cond = ' WHERE ' . implode(' AND ', $whereParts);
        }

        // SQL para Prenomina 2 con todas las columnas necesarias
        $sql = "SELECT 
                    t.id,
                    t.id AS expediente,
                    t.cargos_id,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                    t.carnet_identidad AS ci,
                    -- c.salario already stores the hourly wage in this environment, do not divide by 192
                    COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                COALESCE(c.salario, 0) AS salario_cargo,
                    COALESCE(p.horas, 192.00) AS horas,
                    COALESCE(p.bonif, 0) AS bonif,
                    COALESCE(p.ausencias, 0) AS ausencias,
                    -- LIQUIDACIÓN: a un trabajador dado de baja pendiente de liquidar hay que
                    -- pagarle TODO lo acumulado, no solo las vacaciones del mes. Mientras
                    -- vacaciones_acc siga cargado la liquidación no se ha procesado y ese valor
                    -- manda; al generarse la prenómina se pone a 0 y a partir de ahí se vuelve a
                    -- leer lo ya guardado en p.vacaciones. Así es idempotente y se autocorrige
                    -- aunque el trabajador se marque para liquidación con la prenómina ya creada.
                    CASE
                        WHEN COALESCE(t.es_liquidacion, 0) = 1 AND COALESCE(t.vacaciones_acc, 0) > 0
                            THEN COALESCE(t.vacaciones_acc, 0)
                        ELSE COALESCE(
                            p.vacaciones,
                            (
                                SELECT COALESCE(SUM(
                                    CASE
                                        WHEN pv.fecha_aprobacion IS NOT NULL
                                             AND pv.dias IS NOT NULL
                                             AND pv.dias <> ''
                                        THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                        ELSE 0
                                    END
                                ), 0)
                                FROM plan_vacaciones pv
                                WHERE pv.trabajador_id = t.id
                                  AND pv.estado IN ('Aprobado','Procesada')
                                  AND YEAR(pv.fecha_inicio) = :anio
                                  AND MONTH(pv.fecha_inicio) = :mes
                            )
                        )
                    END AS vacaciones,
                    COALESCE(p.a_cobrar, 0) AS a_cobrar,
                    COALESCE(p.sal_dev, 0) AS sal_dev,
                    CASE
                        WHEN COALESCE(t.es_liquidacion, 0) = 1 AND COALESCE(t.salario_acc, 0) > 0
                            THEN COALESCE(t.salario_acc, 0)
                        ELSE COALESCE(p.pago_vac, 0)
                    END AS pago_vac,
                    COALESCE(p.salario_neto, 0) AS salario_neto,
                    COALESCE(p.seg_social, 0) AS seg_social,
                    COALESCE(p.ing_pers_3, 0) AS ing_pers_3,
                    COALESCE(p.ing_pers_5, 0) AS ing_pers_5,
                    COALESCE(p.salario_pagar, 0) AS salario_pagar,
                    COALESCE(t.es_liquidacion, 0) AS es_liquidacion,
                    COALESCE(t.vacaciones_acc, 0) AS vacaciones_acc,
                    COALESCE(t.salario_acc, 0) AS salario_acc
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :anio AND p.month = :mes
                $cond
                ORDER BY t.id ASC";

        if (!empty($vals)) {
            $result = $this->db->fetchAll($sql, $vals);
        } else {
            $result = $this->db->fetchAll($sql);
        }
        
        try {
            error_log("Prenomina2: trabajadores recuperados: " . count($result));
            if (!empty($result)) {
                error_log("Prenomina2: PRIMERO 3 trabajadores: " . json_encode(array_slice($result, 0, 3)));
            }
            // Post-process: apply heuristic to correct tarifa when DB returns an hourly rate that looks rounded
            // Heuristic: tarifa between 20 and 1000 (likely hourly) and tarifa * 192 has fractional part > 0.5
            // EXCEPCIÓN: No aplicar heurística para cargo 28 (mantener decimales originales)
            foreach ($result as $idx => $r) {
                $tarifaVal = isset($r['tarifa']) ? floatval($r['tarifa']) : 0.0;
                $salarioCargo = isset($r['salario_cargo']) ? floatval($r['salario_cargo']) : NULL;
                $cargoId = isset($r['cargos_id']) ? intval($r['cargos_id']) : 0;
                $tarifaOriginal = $tarifaVal;
                $tarifaEspecial = false;
                // Forzar tarifa y salario_cargo exactos SOLO si la tarifa original es 44.27 o 41.14
                if (abs($tarifaOriginal - 44.27) < 0.01) {
                    $tarifaVal = 44.270833;
                    $result[$idx]['tarifa'] = number_format($tarifaVal, 6, '.', '');
                    $result[$idx]['salario_cargo'] = number_format(44.270833 * 192, 2, '.', '');
                    $tarifaEspecial = true;
                } elseif (abs($tarifaOriginal - 41.14) < 0.01) {
                    $tarifaVal = 41.145833;
                    $result[$idx]['tarifa'] = number_format($tarifaVal, 6, '.', '');
                    $result[$idx]['salario_cargo'] = number_format(41.145833 * 192, 2, '.', '');
                    $tarifaEspecial = true;
                }
                // Si es tarifa especial, recalcular todos los campos dependientes ANTES de heurística
                if ($tarifaEspecial) {
                    $horas = isset($r['horas']) ? floatval($r['horas']) : 192.0;
                    $a_cobrar = $horas * $tarifaVal;
                    $result[$idx]['a_cobrar'] = number_format($a_cobrar, 2, '.', '');
                    $result[$idx]['sal_dev'] = number_format($a_cobrar, 2, '.', '');
                    $vacaciones = isset($r['vacaciones']) ? floatval($r['vacaciones']) : 0.0;
                    $pago_vac = $tarifaVal * 8 * $vacaciones;
                    $result[$idx]['pago_vac'] = number_format($pago_vac, 2, '.', '');
                    $salario_neto = $a_cobrar + $pago_vac;
                    $result[$idx]['salario_neto'] = number_format($salario_neto, 2, '.', '');
                    $seg_social = round($salario_neto * 0.05, 2);
                    $result[$idx]['seg_social'] = number_format($seg_social, 2, '.', '');
                    $ing_pers_3 = 0;
                    $ing_pers_5 = 0;
                    if ($salario_neto >= 3260 && $salario_neto <= 9510) {
                        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                    } elseif ($salario_neto > 9510) {
                        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                        $ing_pers_5 = round(($salario_neto - 9510) * 0.05, 2);
                    }
                    $result[$idx]['ing_pers_3'] = number_format($ing_pers_3, 2, '.', '');
                    $result[$idx]['ing_pers_5'] = number_format($ing_pers_5, 2, '.', '');
                    $ausencias = isset($r['ausencias']) ? floatval($r['ausencias']) : 0.0;
                    $ausencias_costo = $ausencias * 8 * $tarifaVal;
                    $result[$idx]['ausencias_costo'] = number_format($ausencias_costo, 2, '.', '');
                    $salario_pagar = $salario_neto - ($seg_social + $ing_pers_3 + $ing_pers_5 + $ausencias_costo);
                    $result[$idx]['salario_pagar'] = number_format($salario_pagar, 2, '.', '');
                    // Saltar heurística para estos casos
                    continue;
                }
                // Fix: For specific tarifas, use exact value for 192h
                $tarifaEspecial = false;
                if (abs($tarifaVal - 44.27) < 0.01) {
                    $tarifaVal = 44.270833;
                    $result[$idx]['tarifa'] = number_format($tarifaVal, 6, '.', '');
                    $result[$idx]['salario_cargo'] = number_format(44.270833 * 192, 2, '.', ''); // 8500.00
                    $tarifaEspecial = true;
                } elseif (abs($tarifaVal - 41.14) < 0.01) {
                    $tarifaVal = 41.145833;
                    $result[$idx]['tarifa'] = number_format($tarifaVal, 6, '.', '');
                    $result[$idx]['salario_cargo'] = number_format(41.145833 * 192, 2, '.', ''); // 7900.00
                    $tarifaEspecial = true;
                }
                // Aplicar heurística solo si tarifa está en rango Y el cargo NO es 28 Y NO es tarifa especial
                if ($tarifaVal > 20 && $tarifaVal < 1000 && $cargoId != 28 && !$tarifaEspecial) {
                    $monthly = $tarifaVal * 192.0;
                    $monthlyFloor = floor($monthly);
                    $frac = $monthly - $monthlyFloor;
                    if ($frac > 0.5) {
                        // Adjust tarifa to the floored monthly value divided by 192 → reduces 41.67 => 41.666666
                        $tarifaCorrected = $monthlyFloor / 192.0;
                        $result[$idx]['tarifa_before_heuristic'] = $r['tarifa'];
                        $result[$idx]['tarifa'] = number_format($tarifaCorrected, 6, '.', '');
                        $result[$idx]['tarifa_adjusted_by_heuristic'] = 1;
                        // Also set salario_cargo to the floored monthly salary, so client salario_base computes to 8000
                        $result[$idx]['salario_cargo_before_heuristic'] = $r['salario_cargo'];
                        $result[$idx]['salario_cargo'] = number_format($monthlyFloor, 2, '.', '');
                        try { error_log("Prenomina2 HEURISTIC applied: ID {$r['id']} tarifa {$tarifaVal} -> {$tarifaCorrected}, monthlyFloor={$monthlyFloor}"); } catch (Exception $__) {}
                        $tarifaVal = $tarifaCorrected;
                    }
                }
                // Si es tarifa especial, recalcular todos los campos dependientes al final (después de heurística)
                // Si la tarifa final es exactamente 44.270833 o 41.145833, recalcular todos los campos dependientes
                if (abs($tarifaVal - 44.270833) < 0.00001 || abs($tarifaVal - 41.145833) < 0.00001) {
                    $horas = isset($r['horas']) ? floatval($r['horas']) : 192.0;
                    $a_cobrar = $horas * $tarifaVal;
                    $result[$idx]['a_cobrar'] = number_format($a_cobrar, 2, '.', '');
                    $result[$idx]['sal_dev'] = number_format($a_cobrar, 2, '.', '');
                    // Vacaciones y pago_vac
                    $vacaciones = isset($r['vacaciones']) ? floatval($r['vacaciones']) : 0.0;
                    $pago_vac = $tarifaVal * 8 * $vacaciones;
                    $result[$idx]['pago_vac'] = number_format($pago_vac, 2, '.', '');
                    $salario_neto = $a_cobrar + $pago_vac;
                    $result[$idx]['salario_neto'] = number_format($salario_neto, 2, '.', '');
                    $seg_social = round($salario_neto * 0.05, 2);
                    $result[$idx]['seg_social'] = number_format($seg_social, 2, '.', '');
                    // IP 3% y 5%
                    $ing_pers_3 = 0;
                    $ing_pers_5 = 0;
                    if ($salario_neto >= 3260 && $salario_neto <= 9510) {
                        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                    } elseif ($salario_neto > 9510) {
                        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                        $ing_pers_5 = round(($salario_neto - 9510) * 0.05, 2);
                    }
                    $result[$idx]['ing_pers_3'] = number_format($ing_pers_3, 2, '.', '');
                    $result[$idx]['ing_pers_5'] = number_format($ing_pers_5, 2, '.', '');
                    // Ausencias costo
                    $ausencias = isset($r['ausencias']) ? floatval($r['ausencias']) : 0.0;
                    $ausencias_costo = $ausencias * 8 * $tarifaVal;
                    $result[$idx]['ausencias_costo'] = number_format($ausencias_costo, 2, '.', '');
                    // Salario a pagar
                    $salario_pagar = $salario_neto - ($seg_social + $ing_pers_3 + $ing_pers_5 + $ausencias_costo);
                    $result[$idx]['salario_pagar'] = number_format($salario_pagar, 2, '.', '');
                }
                // Aplicar heurística solo si tarifa está en rango Y el cargo NO es 28
                if ($tarifaVal > 20 && $tarifaVal < 1000 && $cargoId != 28) {
                    $monthly = $tarifaVal * 192.0;
                    $monthlyFloor = floor($monthly);
                    $frac = $monthly - $monthlyFloor;
                    if ($frac > 0.5) {
                        // Adjust tarifa to the floored monthly value divided by 192 → reduces 41.67 => 41.666666
                        $tarifaCorrected = $monthlyFloor / 192.0;
                        $result[$idx]['tarifa_before_heuristic'] = $r['tarifa'];
                        $result[$idx]['tarifa'] = number_format($tarifaCorrected, 6, '.', '');
                        $result[$idx]['tarifa_adjusted_by_heuristic'] = 1;
                        // Also set salario_cargo to the floored monthly salary, so client salario_base computes to 8000
                        $result[$idx]['salario_cargo_before_heuristic'] = $r['salario_cargo'];
                        $result[$idx]['salario_cargo'] = number_format($monthlyFloor, 2, '.', '');
                        try { error_log("Prenomina2 HEURISTIC applied: ID {$r['id']} tarifa {$tarifaVal} -> {$tarifaCorrected}, monthlyFloor={$monthlyFloor}"); } catch (Exception $__) {}
                    }
                }
                // If this is worker 96, add extra logs to inspect values
                if ((int)$r['id'] === 96) {
                    try {
                        error_log("Prenomina2 DEBUG - ID 96: salario_cargo=" . (isset($result[$idx]['salario_cargo']) ? $result[$idx]['salario_cargo'] : 'NULL') . ", tarifa=" . (isset($result[$idx]['tarifa']) ? $result[$idx]['tarifa'] : 'NULL'));
                        $cargoInfo = $this->db->fetchRow("SELECT id, salario FROM cargos WHERE id = (SELECT cargos_id FROM trabajadores WHERE id = :tid)", ['tid' => 96]);
                        if ($cargoInfo) {
                            error_log("Prenomina2 DEBUG - Cargo direct lookup for Trabajador 96: id=" . $cargoInfo['id'] . ", salario_db=" . $cargoInfo['salario']);
                        }
                    } catch (Exception $e) {
                        error_log("Prenomina2 DEBUG - Error fetching cargo lookup for ID 96: " . $e->getMessage());
                    }
                }
            }
        } catch (Exception $__) { }
        return $result;
    }

    private function _save_horas() {
        try {
            // Limpiar cualquier output previo
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json; charset=utf-8');
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);
            $resp = ['status' => 1, 'msg' => 'Guardado', 'errors' => [], 'affected' => 0];
            
            // Debug: verificar que la tabla prenomina existe
            try {
                $testQuery = "SHOW TABLES LIKE 'prenomina'";
                $tableExists = $this->db->fetchAll($testQuery);
                if (empty($tableExists)) {
                    echo json_encode(['status' => 0, 'msg' => 'Error: La tabla prenomina no existe']);
                    exit;
                }
                
                // Verificar que el campo ausencias_costo existe
                $columnQuery = "SHOW COLUMNS FROM prenomina LIKE 'ausencias_costo'";
                $columnExists = $this->db->fetchAll($columnQuery);
                if (empty($columnExists)) {
                    echo json_encode(['status' => 0, 'msg' => 'Error: El campo ausencias_costo no existe en la tabla prenomina']);
                    exit;
                }
                
                // Detectar columna correcta en tarjetas_snc225
                $ttCorrect = false;       // tiempo_trabajo (current)
                try {
                    $col2 = $this->db->fetchAll("SHOW COLUMNS FROM tarjetas_snc225 LIKE 'tiempo_trabajo'");
                    $ttCorrect = !empty($col2);
                } catch (Exception $e) { $ttCorrect = false; }
                // Guardar flag
                $this->_ttCorrectExists = $ttCorrect;
            } catch (Exception $e) {
                echo json_encode(['status' => 0, 'msg' => 'Error verificando tabla: ' . $e->getMessage()]);
                exit;
            }
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'msg' => 'Error inicial: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            exit;
        }
    
        if (!is_array($payload)) {
            $resp['status'] = 0;
            $resp['msg'] = 'JSON inválido';
            echo json_encode($resp);
            exit;
        }
    
        try {
            $year = isset($payload['year']) ? intval($payload['year']) : intval(date('Y'));
            $month = isset($payload['month']) ? intval($payload['month']) : intval(date('n'));
            $rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : [];
            if (empty($rows)) {
                $resp['status'] = 0;
                $resp['msg'] = 'Sin filas a guardar';
                echo json_encode($resp);
                exit;
            }
        
            foreach ($rows as $r) {
                $trabajador_id = isset($r['trabajador_id']) ? intval($r['trabajador_id']) : 0;
                $horas = isset($r['horas']) ? floatval($r['horas']) : 0.0;

                if ($trabajador_id <= 0) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => 'trabajador_id inválido'];
                    continue;
                }
        
                try {
                    // Verificar si ya existe registro del periodo
                    $sqlSel = "SELECT id, tarifa FROM prenomina WHERE trabajador_id=:tid AND `year`=:y AND `month`=:m";
                    $row = $this->db->fetchRow($sqlSel, ['tid' => $trabajador_id, 'y' => $year, 'm' => $month]);
        
                    // Obtener tarifa
                    if ($row) {
                        $tarifa = floatval($row['tarifa']);
                    } else {
                        $sqlTar = "SELECT COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa 
                                   FROM trabajadores t 
                                   LEFT JOIN cargos c ON t.cargos_id=c.id 
                                   WHERE t.id=:tid";
                        $trow = $this->db->fetchRow($sqlTar, ['tid' => $trabajador_id]);
                        $tarifa = $trow ? floatval($trow['tarifa']) : 0.0;
                    }
        
                    // Calcular salario base
                    $a_cobrar = floatval($horas * $tarifa);
        
                    // Obtener ausencias
                    $sqlAusencias = "SELECT COUNT(*) as total_ausencias 
                                     FROM registro_asistencia 
                                     WHERE trabajador_id = :tid AND ausencia = '1'";
                    $ausenciasRow = $this->db->fetchRow($sqlAusencias, ['tid' => $trabajador_id]);
                    $ausencias = $ausenciasRow ? intval($ausenciasRow['total_ausencias']) : 0;
                    $ausenciasCosto = intval($ausencias * 8 * $tarifa);
                    
                    // Obtener vacaciones a descontar en ESTE periodo: solo planes aprobados,
                    // aún no procesados, cuyo MES DE INICIO coincide con el año/mes de la prenómina.
                    // Así el plan se descuenta una sola vez, en la prenómina del mes en que empieza.
                    $sqlVacaciones = "SELECT COALESCE(SUM(LENGTH(dias) - LENGTH(REPLACE(dias, ',', '')) + 1), 0) AS total_dias_vacaciones
                                        FROM plan_vacaciones
                                        WHERE trabajador_id = :tid
                                          AND estado = 'Aprobado'
                                          AND periodo_descuento IS NULL
                                          AND dias IS NOT NULL AND dias <> ''
                                          AND YEAR(fecha_inicio) = :y
                                          AND MONTH(fecha_inicio) = :m";
                    $vacacionesRow = $this->db->fetchRow($sqlVacaciones, ['tid' => $trabajador_id, 'y' => $year, 'm' => $month]);
                    $vacaciones = $vacacionesRow ? floatval($vacacionesRow['total_dias_vacaciones']) : 0.0;

                    // Calcular pago por vacaciones (preservar decimales)
                    $pago_vac = ($tarifa * 8) * $vacaciones;

                    // Salario neto incluye pago por vacaciones
                    $salario_neto = round($a_cobrar + $pago_vac, 2);

                    // Descuentos
                    $seg_social = round(($a_cobrar + $pago_vac) * 0.05, 2);
                    
                    // Calcular ing_pers_3 e ing_pers_5 según rangos salariales
                    $ing_pers_3 = 0;
                    $ing_pers_5 = 0;
                    
                    if ($salario_neto >= 3260 && $salario_neto <= 9510) {
                        // Rango 3260-9510: 3% fijo del rango completo
                        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                    } elseif ($salario_neto > 9510) {
                        // Sobre 9510: 3% fijo + 5% del exceso
                        $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                        $ing_pers_5 = round(($salario_neto - 9510) * 0.05, 2);
                    }

                    // Salario final a pagar
                    $salario_pagar = round($salario_neto - ($seg_social + $ing_pers_3 + $ing_pers_5 + $ausenciasCosto), 2);
        
                    if ($row) {
                        // Update
                        $upd = [
                            'horas' => $horas,
                            'a_cobrar' => round(floatval($a_cobrar), 2),
                            'sal_dev' => round(floatval($a_cobrar), 2),
                            'salario_neto' => round(floatval($salario_neto), 2),
                            'seg_social' => round(floatval($seg_social), 2),
                            'ing_pers_3' => round(floatval($ing_pers_3), 2),
                            'ing_pers_5' => round(floatval($ing_pers_5), 2),
                            'salario_pagar' => round(floatval($salario_pagar), 2),
                            'ausencias' => $ausencias,
                            'ausencias_costo' => round(floatval($ausenciasCosto), 2),
                            'vacaciones' => round(floatval($vacaciones), 2),
                            'pago_vac' => floatval($pago_vac),
                        ];
                        $upd['empresa_id'] = $this->app->empresa_id;
                        $where = ['trabajador_id' => $trabajador_id, 'year' => $year, 'month' => $month];
                        $this->db->update('prenomina', $upd, $where);
                    } else {
                        // Insert
                        $ins = [
                            'trabajador_id' => $trabajador_id,
                            'year' => $year,
                            'month' => $month,
                            'horas' => $horas,
                            'tarifa' => $tarifa,
                            'a_cobrar' => round(floatval($a_cobrar), 2),
                            'bonif' => null,
                            'sal_dev' => $a_cobrar,
                            'ausencias' => $ausencias,
                            'ausencias_costo' => round(floatval($ausenciasCosto), 2),
                            'vacaciones' => round(floatval($vacaciones), 2),
                            'pago_vac' => floatval($pago_vac),
                            'salario_neto' => round(floatval($salario_neto), 2),
                            'seg_social' => round(floatval($seg_social), 2),
                            'ing_pers_3' => round(floatval($ing_pers_3), 2),
                            'ing_pers_5' => round(floatval($ing_pers_5), 2),
                            'salario_pagar' => round(floatval($salario_pagar), 2),
                            'empresa_id' => $this->app->empresa_id
                        ];
                        $this->db->insert('prenomina', $ins);
                    }

                    // Registrar constancia en tarjetas_snc225
                    try {
                        $periodo = sprintf('%04d-%02d-01', $year, $month);
                        $valSD = round((float)$salario_pagar, 2);
                        $diasTrab = (int)round($horas / 8);
                        
                        // Verificar si existe registro para este periodo
                        $snc = $this->db->fetchRow(
                            "SELECT id, fecha_cierre, fecha_inicio FROM tarjetas_snc225 WHERE trabajador_id = :tid AND periodo = :p LIMIT 1",
                            ['tid' => $trabajador_id, 'p' => $periodo]
                        );
                        
                        $includeCorrect = (isset($this->_ttCorrectExists) && $this->_ttCorrectExists === true);
                        
                        // Si la fila del periodo tiene fecha_cierre, no tocar nada (registro cerrado)
                        if ($snc && isset($snc['id']) && !empty($snc['fecha_cierre']) && $snc['fecha_cierre'] !== '0000-00-00') {
                            // Skip: registro cerrado
                            error_log("Prenomina: tarjeta_snc225 cerrada para trabajador {$trabajador_id}, periodo {$periodo}");
                        } elseif ($snc && isset($snc['id'])) {
                            // Actualizar registro existente
                            $upd = [ 'salarios_devengados' => $valSD ];
                            if ($includeCorrect) { 
                                $upd['tiempo_trabajo'] = $diasTrab; 
                            }
                            $this->db->update('tarjetas_snc225', $upd, ['id' => $snc['id']]);
                            error_log("Prenomina: tarjeta_snc225 actualizada - trabajador {$trabajador_id}, periodo {$periodo}, salario {$valSD}, días {$diasTrab}");
                        } else {
                            // Crear nuevo registro en tarjetas_snc225 para dejar constancia
                            $insSnc = [
                                'trabajador_id' => $trabajador_id,
                                'periodo' => $periodo,
                                'salarios_devengados' => $valSD
                            ];
                            
                            // Agregar tiempo_trabajo si la columna existe
                            if ($includeCorrect) {
                                $insSnc['tiempo_trabajo'] = $diasTrab;
                            }
                            
                            // Obtener fecha_inicio del trabajador (fecha de contratación)
                            $trabData = $this->db->fetchRow(
                                "SELECT fecha_contratacion FROM trabajadores WHERE id = :tid",
                                ['tid' => $trabajador_id]
                            );
                            if ($trabData && !empty($trabData['fecha_contratacion']) && $trabData['fecha_contratacion'] !== '0000-00-00') {
                                // Verificar si la columna fecha_inicio existe
                                try {
                                    $colFI = $this->db->fetchAll("SHOW COLUMNS FROM tarjetas_snc225 LIKE 'fecha_inicio'");
                                    if (!empty($colFI)) {
                                        $insSnc['fecha_inicio'] = $trabData['fecha_contratacion'];
                                    }
                                } catch (Exception $e) { /* ignore */ }
                            }
                            
                            $this->db->insert('tarjetas_snc225', $insSnc);
                            error_log("Prenomina: tarjeta_snc225 creada - trabajador {$trabajador_id}, periodo {$periodo}, salario {$valSD}, días {$diasTrab}");
                        }
                    } catch (Exception $e) { 
                        error_log("Prenomina: Error actualizando tarjeta_snc225 para trabajador {$trabajador_id}: " . $e->getMessage());
                    }
        
                    $resp['affected']++;
                    
                } catch (Exception $e) {
                    $resp['errors'][] = ['trabajador_id' => $trabajador_id, 'msg' => $e->getMessage()];
                    continue;
                }
            }
            
            // NOTA: El devengo mensual de días de vacaciones (+2.18 a vacaciones_acc) ya NO se
            // realiza aquí. Ahora es responsabilidad exclusiva del cron independiente
            // cron_devengo_vacaciones.php (se ejecuta el día 1 de cada mes para todos los
            // trabajadores activos). Esto evita el doble devengo que ocurría al guardar prenómina.

            echo json_encode($resp);
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'msg' => 'Error general: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            exit;
        }
    }

    /**
     * _save_prenomina2 - Guardar datos de Prenomina 2 (IML)
     * Recibe JSON con todas las columnas calculadas
     */
    private function _save_prenomina2() {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            $input = file_get_contents('php://input');
            $payload = json_decode($input, true);
            
            if (!is_array($payload) || empty($payload['rows'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'No hay datos para guardar']);
                return;
            }
            
            $year = intval($payload['year'] ?? date('Y'));
            $month = intval($payload['month'] ?? date('n'));
            $rows = $payload['rows'];
            
            // DEBUG: registrar si vienen vacaciones/vac_dias para IDs de interés
            $debugIds = [146,165,167,171,172,173,174,175,176,177,196,197];
            try {
                foreach ($rows as $rdbg) {
                    $tiddbg = intval($rdbg['trabajador_id'] ?? ($rdbg['id'] ?? 0));
                    if (in_array($tiddbg, $debugIds)) {
                        @error_log('Prenomina.save.PAYLOAD DEBUG ID ' . $tiddbg . ' -> vacaciones=' . (isset($rdbg['vacaciones']) ? $rdbg['vacaciones'] : 'N/A') . ', vac_dias=' . (isset($rdbg['vac_dias']) ? $rdbg['vac_dias'] : 'N/A') . ', pago_vac=' . (isset($rdbg['pago_vac']) ? $rdbg['pago_vac'] : 'N/A'));
                    }
                }
            } catch (Exception $e) { /* ignore logging errors */ }

            $affected = 0;
            $errors = [];
            
            foreach ($rows as $row) {
                try {
                    $trabajador_id = intval($row['trabajador_id'] ?? 0);
                    
                    if (!$trabajador_id) continue;
                    
                    // Calcular ing_pers_5 sobre monto que excede 9510 (sin tope máximo)
                    $salario_neto = round(floatval($row['salario_neto'] ?? 0), 2);
                    $ing_pers_5 = round(max(0, $salario_neto - 9510) * 0.05, 2);
                    
                    // Datos a guardar (campos que coinciden con estructura de tabla prenomina)
                    // Normalizar vacaciones aceptando 'vacaciones' o 'vac_dias' desde el cliente
                    $vacaciones_val = isset($row['vacaciones']) ? $row['vacaciones'] : (isset($row['vac_dias']) ? $row['vac_dias'] : 0);
                    $updateData = [
                        'horas' => floatval($row['horas'] ?? 192),
                        'tarifa' => floatval($row['tarifa'] ?? 0),
                        'bonif' => floatval($row['bonif'] ?? 0),
                        'ausencias' => floatval($row['ausencias'] ?? 0),
                        'ausencias_costo' => round(floatval($row['ausencias_costo'] ?? 0), 2),
                        'vacaciones' => round(floatval($vacaciones_val), 2),
                        'a_cobrar' => round(floatval($row['a_cobrar'] ?? 0), 2),
                        'sal_dev' => round(floatval($row['sal_dev'] ?? 0), 2),
                        'pago_vac' => floatval($row['pago_vac'] ?? 0),
                        'salario_neto' => $salario_neto,
                        'seg_social' => round(floatval($row['seg_social'] ?? 0), 2),
                        'ing_pers_3' => round(floatval($row['ing_pers_3'] ?? 0), 2),
                        'ing_pers_5' => round(floatval($ing_pers_5), 2),
                        'salario_pagar' => round(floatval($row['salario_pagar'] ?? 0), 2)
                    ];

                    // LIQUIDACIÓN: para un trabajador pendiente de liquidar los días de
                    // vacaciones y su pago son los acumulados del propio trabajador, no lo que
                    // llegue en la petición. La interfaz ya bloquea esos inputs, pero el importe
                    // se decide aquí: es dinero y el servidor no debe fiarse del cliente.
                    $liq = $this->db->fetchRow(
                        "SELECT COALESCE(es_liquidacion, 0) AS es_liquidacion,
                                COALESCE(vacaciones_acc, 0) AS vacaciones_acc,
                                COALESCE(salario_acc, 0)    AS salario_acc
                           FROM trabajadores WHERE id = :tid LIMIT 1",
                        ['tid' => $trabajador_id]
                    );
                    if ($liq && intval($liq['es_liquidacion']) === 1
                        && (floatval($liq['vacaciones_acc']) > 0 || floatval($liq['salario_acc']) > 0)) {
                        $updateData['vacaciones'] = round(floatval($liq['vacaciones_acc']), 2);
                        $updateData['pago_vac']   = floatval($liq['salario_acc']);
                        @error_log("Prenomina save: trabajador {$trabajador_id} en liquidación,"
                            . " se fuerzan vacaciones={$updateData['vacaciones']} y pago_vac={$updateData['pago_vac']}"
                            . " desde los acumulados");
                    }

                    // Verificar si existe registro con este trabajador_id, year, month
                    $existing = $this->db->fetchRow(
                        "SELECT id FROM prenomina WHERE trabajador_id = :tid AND year = :y AND month = :m",
                        ['tid' => $trabajador_id, 'y' => $year, 'm' => $month]
                    );
                    
                    if ($existing) {
                        // UPDATE: solo datos mutable
                            @error_log('Prenomina save: trabajador_id=' . $trabajador_id . ' vacaciones=' . ($updateData['vacaciones'] ?? 'n/a'));
                        $updateData['empresa_id'] = $this->app->empresa_id;
                        $this->db->update('prenomina', $updateData, ['id' => intval($existing['id'])]);
                        $action = 'UPDATE';
                    } else {
                        // INSERT: incluir campos de identificación
                            @error_log('Prenomina insert: trabajador_id=' . $trabajador_id . ' vacaciones=' . ($updateData['vacaciones'] ?? 'n/a'));
                        $insertData = array_merge([
                            'trabajador_id' => $trabajador_id,
                            'year' => $year,
                            'month' => $month,
                            'empresa_id' => $this->app->empresa_id
                        ], $updateData);
                        $this->db->insert('prenomina', $insertData);
                        $action = 'INSERT';
                    }

                    // Aplicar descuentos de vacaciones a trabajadores (tanto en INSERT como en
                    // UPDATE). El método relee los planes aprobados cuyo mes de inicio coincide
                    // con este periodo y que aún no han sido descontados (periodo_descuento IS NULL),
                    // por lo que es la única fuente de verdad y es seguro frente a reprocesos.
                    $this->_apply_prenomina_deductions_to_trabajador(
                        $trabajador_id,
                        $year,
                        $month
                    );

                    // Si el trabajador estaba pendiente de liquidar, su prenómina acaba de
                    // quedar guardada con el acumulado: se vacían los saldos para no volver a
                    // pagarlos. Va DESPUÉS del insert/update, nunca antes.
                    $this->_vaciar_acumulados_si_liquidacion($trabajador_id, $year, $month);

                    $affected++;
                    
                    // Registrar en historial. La tabla `historico` solo admite
                    // xentity/xaction/xid/xobs (xuser y xdate los rellena add_history):
                    // no existen las columnas xdata ni xuser_id.
                    if (method_exists($this->app, 'add_history')) {
                        $this->app->add_history([
                            'xentity' => 'PRENOMINA2',
                            'xaction' => "SAVE-{$action}",
                            'xid' => $trabajador_id,
                            'xobs' => "PRENÓMINA {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT)
                                . " {$action} - TRABAJADOR ID: {$trabajador_id}"
                                . " - Horas: {$updateData['horas']}"
                                . " - Vacaciones: {$updateData['vacaciones']}"
                                . " - Pago vac: {$updateData['pago_vac']}"
                                . " - Salario a pagar: {$updateData['salario_pagar']}",
                        ]);
                    }
                    
                } catch (Exception $e) {
                    $errors[] = "Trabajador ID {$trabajador_id}: " . $e->getMessage();
                }
            }
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 1,
                'affected' => $affected,
                'errors' => $errors,
                'msg' => "Guardado: {$affected} registros"
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'error' => $e->getMessage()]);
        }
    }

    /**
     * _vaciar_acumulados_si_liquidacion
     * Cierra la liquidación de un trabajador dado de baja pendiente de liquidar: una vez que su
     * prenómina del período quedó guardada con vacaciones_acc y salario_acc, esos saldos se
     * ponen a 0 para que no se vuelvan a pagar en un período posterior.
     *
     * Es idempotente: si los acumulados ya están en 0 no hace nada, de modo que volver a guardar
     * la misma prenómina no tiene efecto. Solo actúa sobre trabajadores con es_liquidacion = 1;
     * el flag lo apaga _liquidar() en mdl.Trabajadores al completar la baja.
     *
     * Los importes no se pierden: quedan registrados en la fila de prenomina del período y en el
     * historial, que es de donde se pueden reconstruir si hiciera falta.
     */
    private function _vaciar_acumulados_si_liquidacion($trabajador_id, $year, $month) {
        try {
            $trab = $this->db->fetchRow(
                "SELECT es_liquidacion, vacaciones_acc, salario_acc
                   FROM trabajadores WHERE id = :tid LIMIT 1",
                ['tid' => $trabajador_id]
            );

            if (!$trab || intval($trab['es_liquidacion'] ?? 0) !== 1) {
                return false; // no está pendiente de liquidar: no se toca nada
            }

            $vac_acc = floatval($trab['vacaciones_acc'] ?? 0);
            $sal_acc = floatval($trab['salario_acc'] ?? 0);

            if ($vac_acc <= 0 && $sal_acc <= 0) {
                return true; // ya liquidado en un guardado anterior
            }

            $this->db->update('trabajadores',
                ['vacaciones_acc' => 0, 'salario_acc' => 0],
                ['id' => $trabajador_id]
            );

            @error_log("Liquidación: trabajador {$trabajador_id} pagado en prenómina {$year}-{$month}"
                . " -> vacaciones_acc {$vac_acc} => 0, salario_acc {$sal_acc} => 0");

            if (method_exists($this->app, 'add_history')) {
                // OJO: la tabla `historico` solo tiene xentity/xaction/xid/xobs (xuser y xdate
                // los pone add_history). No existen xdata ni xuser_id: pasarlos rompe el INSERT.
                $this->app->add_history([
                    'xentity' => 'TRABAJADOR',
                    'xaction' => 'LIQUIDACION-PRENOMINA',
                    'xid' => $trabajador_id,
                    'xobs' => "LIQUIDACIÓN PAGADA EN PRENÓMINA {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT)
                        . " - TRABAJADOR ID: {$trabajador_id}"
                        . " - Vacaciones acumuladas: {$vac_acc} días"
                        . " - Salario acumulado: {$sal_acc}",
                ]);
            }

            return true;

        } catch (Exception $e) {
            @error_log("ERROR en _vaciar_acumulados_si_liquidacion (tid={$trabajador_id}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * _apply_prenomina_deductions_to_trabajador
     * Descuenta de trabajadores los planes de vacaciones APROBADOS cuyo MES DE INICIO
     * coincide con el periodo ($year/$month) de la prenómina y que aún no han sido
     * descontados (periodo_descuento IS NULL). Por cada plan:
     *   - vacaciones_acc        -= dias del plan
     *   - vacaciones_congeladas -= dias del plan (libera lo reservado al aprobar)
     *   - salario_acc           -= pago de esos días de vacaciones
     *   - el plan pasa a estado 'Procesada' con periodo_descuento='YYYY-MM' (candado anti-doble)
     * La acumulación mensual (~2.18 días) NO se hace aquí: la realiza _agregar_dias_vacaciones().
     */
    private function _apply_prenomina_deductions_to_trabajador($trabajador_id, $year, $month) {
        try {
            @error_log("=== APPLYING PRENOMINA DEDUCTIONS for trabajador_id: {$trabajador_id} ({$year}-{$month}) ===");

            // Planes aprobados, no procesados, que inician en este periodo.
            $sql_planes = "SELECT id, (LENGTH(dias) - LENGTH(REPLACE(dias, ',', '')) + 1) AS dias_plan
                           FROM plan_vacaciones
                           WHERE trabajador_id = :tid
                             AND estado = 'Aprobado'
                             AND periodo_descuento IS NULL
                             AND dias IS NOT NULL AND dias <> ''
                             AND YEAR(fecha_inicio) = :y
                             AND MONTH(fecha_inicio) = :m";
            $planes = $this->db->fetchAll($sql_planes, ['tid' => $trabajador_id, 'y' => $year, 'm' => $month]);

            if (empty($planes)) {
                @error_log("No hay planes a descontar para trabajador {$trabajador_id} en {$year}-{$month}");
                return true;
            }

            // Obtener tarifa para calcular el pago de las vacaciones (tarifa * 8 * dias).
            $sqlTar = "SELECT COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa
                       FROM trabajadores t LEFT JOIN cargos c ON t.cargos_id = c.id WHERE t.id = :tid";
            $trow = $this->db->fetchRow($sqlTar, ['tid' => $trabajador_id]);
            $tarifa = $trow ? floatval($trow['tarifa']) : 0.0;

            $periodo = sprintf('%04d-%02d', intval($year), intval($month));

            foreach ($planes as $plan) {
                $plan_id = intval($plan['id']);
                $vac_dias = floatval($plan['dias_plan']);
                $pago_vac = ($tarifa * 8) * $vac_dias;

                // Valores actuales del trabajador (releer por cada plan para acumular correctamente).
                $trab = $this->db->fetchRow(
                    "SELECT vacaciones_acc, vacaciones_congeladas, salario_acc FROM trabajadores WHERE id = :tid LIMIT 1",
                    ['tid' => $trabajador_id]
                );
                if (!$trab) {
                    @error_log("Trabajador {$trabajador_id} no encontrado");
                    return false;
                }

                $vac_acc_actual = floatval($trab['vacaciones_acc'] ?? 0);
                $vac_cong_actual = floatval($trab['vacaciones_congeladas'] ?? 0);
                $sal_acc_actual = floatval($trab['salario_acc'] ?? 0);

                $new_vac_acc  = max(0, $vac_acc_actual - $vac_dias);
                $new_vac_cong = max(0, $vac_cong_actual - $vac_dias); // liberar lo congelado
                $new_sal_acc  = max(0, $sal_acc_actual - $pago_vac);

                $this->db->update('trabajadores',
                    [
                        'vacaciones_acc' => round($new_vac_acc, 2),
                        'vacaciones_congeladas' => round($new_vac_cong, 2),
                        'salario_acc' => round($new_sal_acc, 2)
                    ],
                    ['id' => $trabajador_id]
                );

                // Candado: marcar el plan como descontado en este periodo.
                $this->db->update('plan_vacaciones',
                    ['estado' => 'Procesada', 'periodo_descuento' => $periodo],
                    ['id' => $plan_id]
                );

                @error_log("Plan {$plan_id} descontado a tid={$trabajador_id}: vac_acc {$vac_acc_actual}->{$new_vac_acc}, congeladas {$vac_cong_actual}->{$new_vac_cong}, sal_acc {$sal_acc_actual}->{$new_sal_acc}");
            }

            return true;

        } catch (Exception $e) {
            @error_log("ERROR en _apply_prenomina_deductions_to_trabajador: " . $e->getMessage());
            return false;
        }
    }

    private function _export_excel($param) {
        // CRÍTICO: Suprimir TODOS los outputs antes de generar Excel
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);
        
        // Limpiar cualquier output buffer previo
        while (ob_get_level()) { 
            @ob_end_clean(); 
        }
        
        // Parámetros de periodo
        if (!empty($param['mes']) && strpos($param['mes'], '-') !== false) {
            list($py, $pm) = explode('-', $param['mes']);
            $year = intval($py);
            $month = intval($pm);
        } else {
            $year = isset($param['year']) ? intval($param['year']) : intval(date('Y'));
            $month = isset($param['month']) ? intval($param['month']) : intval(date('n'));
        }
        
        @error_log('=== EXPORT EXCEL INICIADO ===');
        @error_log('Params: ' . json_encode($param));
        @error_log('Year: ' . $year . ', Month: ' . $month);

        // Cargar overrides (valores en caché enviados por el cliente)
        $overrides = [];
        try {
            if (!empty($param['overrides'])) {
                $decoded = json_decode($param['overrides'], true);
                if (is_array($decoded)) $overrides = $decoded;
            } else {
                $raw = @file_get_contents('php://input');
                if ($raw) {
                    $body = json_decode($raw, true);
                    if (!empty($body['overrides']) && is_array($body['overrides'])) {
                        $overrides = $body['overrides'];
                    }
                }
            }
        } catch (Exception $e) {
            $overrides = [];
        }

        // Normalizar overrides a mapa [trabajador_id] => data
        $ovMap = [];
        if (is_array($overrides)) {
            foreach ($overrides as $k => $v) {
                if (is_array($v)) {
                    // Normalizar nombres comunes desde cliente (vac_dias -> vacaciones)
                    if (isset($v['vac_dias']) && !isset($v['vacaciones'])) {
                        $v['vacaciones'] = $v['vac_dias'];
                    }
                    if (isset($v['trabajador_id'])) {
                        $ovMap[intval($v['trabajador_id'])] = $v;
                    } elseif (isset($v['id'])) {
                        $ovMap[intval($v['id'])] = $v;
                    } elseif (is_numeric($k)) {
                        // numeric index with nested object missing id - skip
                    } else {
                        // associative by id -> value
                        if (is_numeric($k)) $ovMap[intval($k)] = $v;
                    }
                } else {
                    // scalar entries ignored
                }
            }
        }

        // Verificar si existe la columna empresa_id en departamentos
        $hasEmpresaId = false;
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos LIKE 'empresa_id'");
            $hasEmpresaId = !empty($cols);
        } catch (Exception $e) {
            $hasEmpresaId = false;
        }

    // Determinar qué departamentos exportar
    $tabRaw = isset($param['tab']) ? trim($param['tab']) : '';
    $tabNorm = mb_strtolower($tabRaw);
    $exportarTodos = ($tabNorm === '' || $tabNorm === 'todos');
    
    $departamentosExportar = [];
    
    if ($exportarTodos) {
        // Obtener todos los departamentos
        $sqlDepts = "SELECT id, nombre FROM departamentos";
        if ($hasEmpresaId && isset($this->app->empresa_id)) {
            $sqlDepts .= " WHERE empresa_id = :eid ORDER BY nombre ASC";
            $depts = $this->db->fetchAll($sqlDepts, ['eid' => $this->app->empresa_id]);
        } else {
            $sqlDepts .= " ORDER BY nombre ASC";
            $depts = $this->db->fetchAll($sqlDepts);
        }
        
        foreach ($depts as $dept) {
            $departamentosExportar[] = [
                'id' => $dept['id'],
                'nombre' => $dept['nombre']
            ];
        }
        @error_log('Exportar TODOS los departamentos: ' . count($departamentosExportar));
    } else {
        // Solo un departamento específico
        $deptParams = ['tab' => $tabRaw];
        $sqlDept = "SELECT id, nombre FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab))";
        if ($hasEmpresaId && isset($this->app->empresa_id)) {
            $sqlDept .= " AND empresa_id = :eid";
            $deptParams['eid'] = $this->app->empresa_id;
        }
        $sqlDept .= " LIMIT 1";
        
        $dept = $this->db->fetchRow($sqlDept, $deptParams);
        if ($dept) {
            $departamentosExportar[] = [
                'id' => $dept['id'],
                'nombre' => $dept['nombre']
            ];
            @error_log('Exportar departamento: ' . $dept['nombre']);
        }
    }
    
    if (empty($departamentosExportar)) {
        @error_log('ERROR: No hay departamentos para exportar');
        header('Content-Type: text/html; charset=utf-8');
        die('Error: No hay departamentos para exportar');
    }

    // SQL base para obtener datos de un departamento específico
    $sqlBase = "SELECT 
                    t.id AS expediente,
                    CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                    t.carnet_identidad AS ci,
                    -- tarifa returned as dynamic (monthly -> /192 else hourly). salary stored in c.salario kept as salario_cargo
                    COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                    COALESCE(c.salario, 0) AS salario_cargo,
                    COALESCE(p.horas, 192.00) AS horas,
                    COALESCE(p.bonif, 0) AS bonif,
                    COALESCE(p.ausencias, 0) AS ausencias,
                    COALESCE(p.vacaciones,
                        COALESCE((
                            SELECT SUM(
                                CASE 
                                    WHEN pv.fecha_aprobacion IS NOT NULL 
                                    AND pv.fecha_aprobacion <> '' 
                                    AND pv.dias IS NOT NULL 
                                    AND pv.dias <> ''
                                    THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                    ELSE 0 
                                END
                            ) 
                            FROM plan_vacaciones pv
                            WHERE pv.trabajador_id = t.id
                              AND pv.estado IN ('Aprobado','Procesada')
                              AND YEAR(pv.fecha_inicio) = :y
                              AND MONTH(pv.fecha_inicio) = :m
                        ), 0)
                    ) AS vacaciones,
                    COALESCE(p.pago_vac, 0) AS pago_vac
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
                AND t.departamento_id = :dept_id
                ORDER BY t.id ASC";

    // Cargar PHPExcel
    try {
        require_once(BASE_CLASS . '/PHPExcel.php');
        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator('Sistema de Prenómina')
            ->setTitle('Prenómina ' . $year . '-' . $month);
        $sheet = $excel->setActiveSheetIndex(0);
        $sheet->setTitle('Prenómina');
    } catch (Exception $e) {
        @error_log('ERROR cargando PHPExcel: ' . $e->getMessage());
        while (ob_get_level()) { @ob_end_clean(); }
        header('Content-Type: text/html; charset=utf-8');
        die('Error cargando PHPExcel: ' . $e->getMessage());
    }
    
    // Obtener nombre de usuario
    $nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Sistema';
    
    // Nombres de meses
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $nombreMes = isset($meses[$month]) ? $meses[$month] : 'Mes ' . $month;
    
    // Encabezados de columnas
    $headers = [
        'A' => 'No. Exp',
        'B' => 'Nombre y Apellido',
        'C' => 'C.I',
        'D' => 'Horas',
        'E' => 'Tarifa',
        'F' => 'A Cobrar',
        'G' => 'Bonif',
        'H' => 'Sal. Dev',
        'I' => 'Vacaciones',
        'J' => 'Pago Vac',
        'K' => 'Sal. Neto',
        'L' => 'Seg Social',
        'M' => 'Ing Pers 3%',
        'N' => 'Ing Pers 5%',
        'O' => 'Sal. a Pagar',
    ];
    
    $rowNum = 1;
    
    // Procesar cada departamento
    foreach ($departamentosExportar as $dept) {
        @error_log('Procesando departamento: ' . $dept['nombre']);
        
        // Espacio entre departamentos (excepto el primero)
        if ($rowNum > 1) {
            $rowNum += 2;
        }
        
        // ENCABEZADO DE SECCIÓN
        $sheet->setCellValue('A' . $rowNum, 'Prenómina Correspondiente al Mes de ' . $nombreMes . ' de ' . $year);
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A' . $rowNum . ':O' . $rowNum);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $rowNum++;
        
        $sheet->setCellValue('A' . $rowNum, 'Elaborado Por: ' . $nombreUsuario);
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
        $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
        
        $sheet->setCellValue('H' . $rowNum, 'Área: ' . $dept['nombre']);
        $sheet->getStyle('H' . $rowNum)->getFont()->setBold(true);
        $sheet->mergeCells('H' . $rowNum . ':M' . $rowNum);
        $rowNum++;
        
        $rowNum++; // Línea en blanco
        
        // ENCABEZADOS DE COLUMNAS
        $headerRow = $rowNum;
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
        }
        $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $rowNum++;
        
        // OBTENER DATOS
        try {
            $vals = ['y' => $year, 'm' => $month, 'dept_id' => $dept['id']];
            $data = $this->db->fetchAll($sqlBase, $vals);
            @error_log('Registros: ' . count($data) . ' para ' . $dept['nombre']);
            
            $firstDataRow = $rowNum;
            
            // ESCRIBIR DATOS
            $rowIndex = 0;
            foreach ($data as $r) {
                $horas = floatval($r['horas']);
                $tarifa_display = floatval($r['tarifa']);
                $bonif = floatval($r['bonif']);
                $ausencias = floatval($r['ausencias']);

                // Aplicar overrides (cache) si el cliente proporcionó valores para este trabajador
                $tid = intval($r['expediente']);
                if (isset($ovMap[$tid]) && is_array($ovMap[$tid])) {
                    $o = $ovMap[$tid];
                    if (isset($o['horas'])) { $horas = floatval($o['horas']); }
                    if (isset($o['tarifa'])) { $tarifa_display = floatval($o['tarifa']); }
                    if (isset($o['bonif'])) { $bonif = floatval($o['bonif']); }
                    if (isset($o['ausencias'])) { $ausencias = floatval($o['ausencias']); }
                    if (isset($o['vacaciones'])) { $r['vacaciones'] = $o['vacaciones']; }
                    if (isset($o['pago_vac'])) { $r['pago_vac'] = $o['pago_vac']; }
                }
                $salario_cargo_db = isset($r['salario_cargo']) ? floatval($r['salario_cargo']) : 0;
                
                // Calculate tarifa_raw using heuristics: if salario_cargo_db > 1000 (monthly) use that /192; else use tarifa_display
                $tarifa_raw = $tarifa_display;
                if ($salario_cargo_db > 1000) {
                    $tarifa_raw = $salario_cargo_db / 192.0;
                }
                // Fix: For specific tarifas, use exact value for 192h
                if (abs($tarifa_raw - 44.27) < 0.01) {
                    $tarifa_raw = 44.270833;
                } elseif (abs($tarifa_raw - 41.14) < 0.01) {
                    $tarifa_raw = 41.145833;
                }
                // Heuristic correction: if tarifa looks rounded (e.g., 41.67) and monthly fractional > 0.5, floor monthly
                if ($tarifa_raw > 20 && $tarifa_raw < 1000) {
                    $monthly = $tarifa_raw * 192.0;
                    $monthlyFloor = floor($monthly);
                    $frac = $monthly - $monthlyFloor;
                    if ($frac > 0.5) {
                        $tarifa_raw = $monthlyFloor / 192.0;
                        $salario_cargo_db = $monthlyFloor; // corrected monthly
                    }
                }
                // For display in Excel, round tarifa to 2 decimals (41.67)
                $tarifa_for_display = number_format(round($tarifa_raw, 2), 2, '.', '');
                
                $vacaciones = floatval($r['vacaciones']);
                $pago_vac_cached = floatval($r['pago_vac']);
                
                // Log de debug para el primer registro
                if ($rowIndex === 0) {
                    @error_log('DEBUG Excel - Primer registro: Exp=' . $r['expediente'] . ', Horas=' . $horas . ', Tarifa=' . $tarifa_raw . ', Vac=' . $vacaciones . ', Pago_Vac_Cached=' . $pago_vac_cached);
                }
                $rowIndex++;
                
                // FÓRMULAS EXACTAS COMO EN LA TABLA (prenomina-2-pro.js)
                $a_cobrar = floatval(intval(($horas / 192) * $salario_cargo_db)) + floatval(intval(($horas / 192) * $salario_cargo_db * 100) % 100) / 100;
                $a_cobrar = round(($horas / 192) * $salario_cargo_db, 2);
                $sal_dev = round($a_cobrar + $bonif, 2);
                
                // ⚠️ PRIORIDAD AL CACHÉ: Si existe pago_vac guardado, usar ese
                if ($pago_vac_cached > 0) {
                    $pago_vac = floatval($pago_vac_cached);
                } else {
                    // Si no existe en caché, calcular
                    $pago_vac = ($vacaciones / 24) * $salario_cargo_db;
                }
                
                $costo_ausencias = round($ausencias * 8 * $tarifa_raw, 2);
                $salario_neto = round($sal_dev - $costo_ausencias + $pago_vac, 2);
                $seg_social = round(($sal_dev + $pago_vac) * 0.05, 2);
                
                // Calcular ing_pers_3 e ing_pers_5 según rangos salariales
                $ing_pers_3 = 0;
                $ing_pers_5 = 0;
                
                if ($salario_neto >= 3260 && $salario_neto <= 9510) {
                    // Rango 3260-9510: 3% fijo del rango completo
                    $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                } elseif ($salario_neto > 9510) {
                    // Sobre 9510: 3% fijo + 5% del exceso
                    $ing_pers_3 = round((9510 - 3260) * 0.03, 2);
                    $ing_pers_5 = round(($salario_neto - 9510) * 0.05, 2);
                }
                
                $total_descuentos = round($seg_social + $ing_pers_3 + $ing_pers_5 + $costo_ausencias, 2);
                $salario_pagar = round($salario_neto - $total_descuentos, 2);
                
                $sheet->setCellValue('A' . $rowNum, $r['expediente']);
                $sheet->setCellValue('B' . $rowNum, $r['nombre']);
                $sheet->setCellValueExplicit('C' . $rowNum, $r['ci'], PHPExcel_Cell_DataType::TYPE_STRING);
                // Escribir valores numéricos como números flotantes puros
                $sheet->setCellValue('D' . $rowNum, floatval($horas));
                $sheet->setCellValue('E' . $rowNum, floatval($tarifa_raw)); // Usar tarifa_raw, no display
                $sheet->setCellValue('F' . $rowNum, floatval($a_cobrar));
                $sheet->setCellValue('G' . $rowNum, floatval($bonif));
                $sheet->setCellValue('H' . $rowNum, floatval($sal_dev));
                $sheet->setCellValue('I' . $rowNum, floatval($vacaciones));
                $sheet->setCellValue('J' . $rowNum, floatval($pago_vac));
                $sheet->setCellValue('K' . $rowNum, floatval($salario_neto));
                $sheet->setCellValue('L' . $rowNum, floatval($seg_social));
                $sheet->setCellValue('M' . $rowNum, floatval($ing_pers_3));
                $sheet->setCellValue('N' . $rowNum, floatval($ing_pers_5));
                $sheet->setCellValue('O' . $rowNum, floatval($salario_pagar));
                
                $rowNum++;
            }
            
            $lastDataRow = $rowNum - 1;
            
            // FORMATOS - Sin separadores de miles, solo 2 decimales
            if ($lastDataRow >= $firstDataRow) {
                // Usar formato que NO incluya separadores de miles y forzar '.' como decimal (locale en-US)
                $sheet->getStyle('D' . $firstDataRow . ':O' . $lastDataRow)->getNumberFormat()->setFormatCode('[$-409]0.00');
            }
            
            // TOTALES
            if ($lastDataRow >= $firstDataRow) {
                $sheet->setCellValue('C' . $rowNum, 'TOTALES:');
                $sheet->getStyle('C' . $rowNum)->getFont()->setBold(true);
                $sheet->setCellValue('D' . $rowNum, '=SUM(D' . $firstDataRow . ':D' . $lastDataRow . ')');
                $sheet->setCellValue('F' . $rowNum, '=SUM(F' . $firstDataRow . ':F' . $lastDataRow . ')');
                $sheet->setCellValue('G' . $rowNum, '=SUM(G' . $firstDataRow . ':G' . $lastDataRow . ')');
                $sheet->setCellValue('H' . $rowNum, '=SUM(H' . $firstDataRow . ':H' . $lastDataRow . ')');
                $sheet->setCellValue('I' . $rowNum, '=SUM(I' . $firstDataRow . ':I' . $lastDataRow . ')');
                $sheet->setCellValue('J' . $rowNum, '=SUM(J' . $firstDataRow . ':J' . $lastDataRow . ')');
                $sheet->setCellValue('K' . $rowNum, '=SUM(K' . $firstDataRow . ':K' . $lastDataRow . ')');
                $sheet->setCellValue('L' . $rowNum, '=SUM(L' . $firstDataRow . ':L' . $lastDataRow . ')');
                $sheet->setCellValue('M' . $rowNum, '=SUM(M' . $firstDataRow . ':M' . $lastDataRow . ')');
                $sheet->setCellValue('N' . $rowNum, '=SUM(N' . $firstDataRow . ':N' . $lastDataRow . ')');
                $sheet->setCellValue('O' . $rowNum, '=SUM(O' . $firstDataRow . ':O' . $lastDataRow . ')');
                // Aplicar formato a totales - forzar '.' decimal y sin separadores de miles
                $sheet->getStyle('D' . $rowNum . ':O' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
                $sheet->getStyle('C' . $rowNum . ':O' . $rowNum)->getFont()->setBold(true);
                $rowNum++;
            }
            
        } catch (Exception $e) {
            @error_log('ERROR consultando datos: ' . $e->getMessage());
        }
    }
    
    // AUTO-AJUSTAR COLUMNAS
    foreach (array_keys($headers) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // GENERAR Y DESCARGAR ARCHIVO
    try {
        $filename = 'Prenomina_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT);
        if (!$exportarTodos && !empty($departamentosExportar)) {
            $filename .= '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $departamentosExportar[0]['nombre']);
        }
        $filename .= '_' . date('Ymd_His') . '.xlsx';
        
        @error_log('Generando archivo: ' . $filename);
        
        // CRÍTICO: Limpiar todos los buffers ANTES de guardar el archivo
        while (ob_get_level()) {
            @ob_end_clean();
        }
        
        // Guardar en archivo temporal primero
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save($tempFile);
        
        @error_log('Archivo guardado en: ' . $tempFile . ' (' . filesize($tempFile) . ' bytes)');
        
        // Limpiar cualquier output buffer OTRA VEZ antes de enviar headers
        while (ob_get_level()) {
            @ob_end_clean();
        }
        
        // Enviar headers y archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        readfile($tempFile);
        @unlink($tempFile);
        
        @error_log('=== EXCEL GENERADO Y ENVIADO EXITOSAMENTE ===');
        exit;
        
    } catch (Exception $e) {
        @error_log('ERROR generando Excel: ' . $e->getMessage());
        @error_log('Stack trace: ' . $e->getTraceAsString());
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

    /**
     * _export_prenomina2 - Exportar Prenomina 2 (IML) a Excel
     * Similar a _export_excel pero con formato IML específico
     */
    private function _export_prenomina2($param) {
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);
        
        while (ob_get_level()) { @ob_end_clean(); }
        
        // Parámetros de período
        if (!empty($param['mes']) && strpos($param['mes'], '-') !== false) {
            list($py, $pm) = explode('-', $param['mes']);
            $year = intval($py);
            $month = intval($pm);
        } else {
            $year = intval($param['year'] ?? date('Y'));
            $month = intval($param['month'] ?? date('n'));
        }
        
        @error_log('=== EXPORT PRENOMINA2 INICIADO ===');
        @error_log('Export: empresaId=' . $empresaId);
        @error_log('Year: ' . $year . ', Month: ' . $month);

        // Verificar si existe empresa_id en departamentos o trabajadores
        $hasEmpresaId = false; // departamentos
        $hasEmpresaIdTrabajadores = false; // trabajadores
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos WHERE Field = 'empresa_id'");
            $hasEmpresaId = !empty($cols);
        } catch (Exception $e) {
            $hasEmpresaId = false;
        }
        try {
            $cols2 = $this->db->fetchAll("SHOW COLUMNS FROM trabajadores WHERE Field = 'empresa_id'");
            $hasEmpresaIdTrabajadores = !empty($cols2);
        } catch (Exception $e) {
            $hasEmpresaIdTrabajadores = false;
        }

        // Determine empresa_id from parameters or session
        $empresaId = isset($param['empresa_id']) && $param['empresa_id'] ? intval($param['empresa_id']) : (isset($this->app->empresa_id) ? intval($this->app->empresa_id) : 0);

        // Determinar departamentos a exportar
        $tabRaw = isset($param['tab']) ? trim($param['tab']) : '';
        $tabNorm = mb_strtolower($tabRaw);
        $exportarTodos = ($tabNorm === '' || $tabNorm === 'todos');
        
        $departamentosExportar = [];
        
        if ($exportarTodos) {
            $sqlDepts = "SELECT id, nombre FROM departamentos";
            if ($hasEmpresaId && $empresaId) {
                $sqlDepts .= " WHERE empresa_id = :eid";
                $depts = $this->db->fetchAll($sqlDepts, ['eid' => $empresaId]);
            } else {
                $depts = $this->db->fetchAll($sqlDepts);
            }
            
            foreach ($depts as $dept) {
                $departamentosExportar[] = $dept;
            }
            @error_log('Exportar TODOS: ' . count($departamentosExportar) . ' depts');
        } else {
            $deptParams = ['tab' => $tabRaw];
            $sqlDept = "SELECT id, nombre FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab))";
            if ($hasEmpresaId && $empresaId) {
                $sqlDept .= " AND empresa_id = :eid";
                $deptParams['eid'] = $empresaId;
            }
            $sqlDept .= " LIMIT 1";
            
            $dept = $this->db->fetchRow($sqlDept, $deptParams);
            if ($dept) {
                $departamentosExportar[] = $dept;
            }
        }
        
        if (empty($departamentosExportar)) {
            @error_log('ERROR: No hay departamentos');
            header('Content-Type: text/html; charset=utf-8');
            die('Error: No hay departamentos para exportar');
        }

        // Cargar PHPExcel
        try {
            require_once(BASE_CLASS . '/PHPExcel.php');
            $excel = new PHPExcel();
            $excel->getProperties()->setTitle('Prenómina ' . $year . '-' . $month);
            $sheet = $excel->setActiveSheetIndex(0);
            $sheet->setTitle('Prenómina');
        } catch (Exception $e) {
            @error_log('ERROR cargando PHPExcel: ' . $e->getMessage());
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error cargando PHPExcel: ' . $e->getMessage());
        }
        
        $nombreUsuario = isset($_SESSION['gname']) ? $_SESSION['gname'] : 'Sistema RRHH';
        $apellidosUsuario = isset($_SESSION['usuario_apellidos']) ? $_SESSION['usuario_apellidos'] : '';
        

        
        $nombreCompletoUsuario = trim($nombreUsuario . ' ' . $apellidosUsuario);
        
        // Obtener nombre de la empresa activa
        $nombreEmpresa = 'Empresa';
        if ($empresaId && $empresaId > 0) {
            try {
                // OJO: la tabla es `empresa` (singular), no `empresas`.
                $empresaRow = $this->db->fetchRow("SELECT nombre FROM empresa WHERE id = :eid LIMIT 1", ['eid' => $empresaId]);
                if ($empresaRow && isset($empresaRow['nombre'])) {
                    $nombreEmpresa = $empresaRow['nombre'];
                }
            } catch (Exception $e) {
                $nombreEmpresa = 'Empresa';
            }
        }
        
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $nombreMes = isset($meses[$month]) ? $meses[$month] : 'Mes ' . $month;
        
        // Calcular primer y último día del mes
        $primerDiaDelMes = date('d-m-Y', mktime(0, 0, 0, $month, 1, $year));
        $ultimoDiaDelMes = date('d-m-Y', mktime(0, 0, 0, $month + 1, 0, $year));
        $fechaActual = date('d-m-Y');
        
        $headers = [
            'A' => 'Exp', 'B' => 'Nombre y Apellido', 'C' => 'C.I',
            'D' => 'Horas', 'E' => 'Tarifa', 'F' => 'A Cobrar', 'G' => 'Bonif',
            'H' => 'Sal Dev', 'I' => 'Vac Días', 'J' => 'Pago Vac',
            'K' => 'Sal Neto', 'L' => 'Seg Social', 'M' => 'IP 3%', 'N' => 'IP 5%',
            'O' => 'A Pagar'
        ];
        
        $rowNum = 1;
        $anyRows = 0;
        
        foreach ($departamentosExportar as $dept) {
            @error_log('Procesando departamento: ' . $dept['nombre']);
            
            if ($rowNum > 1) {
                $rowNum += 2;
            }
            
            // LOGO (if exists)
            $logoPath = BASE_CLASS . '/../images/Imagen1.jpg';
            if (file_exists($logoPath)) {
                try {
                    $drawing = new PHPExcel_Worksheet_Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Logo');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A' . $rowNum);
                    $drawing->setWorksheet($sheet);
                    $rowNum += 3;
                    
                    // Agregar texto debajo del logo
                    $sheet->setCellValue('A' . $rowNum, 'Departamento de Recursos Humanos');
                    $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(11);
                    $sheet->mergeCells('A' . $rowNum . ':C' . $rowNum);
                    $rowNum += 1;
                } catch (Exception $e) {
                    @error_log('Error inserting logo: ' . $e->getMessage());
                }
            }
            
            // ENCABEZADOS
            $sheet->setCellValue('A' . $rowNum, 'Prenomina ' . $nombreEmpresa . ' - ' . $nombreMes . ' de ' . $year);
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells('A' . $rowNum . ':O' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $rowNum++;
            
            $sheet->setCellValue('A' . $rowNum, 'Elaborado Por: ' . $nombreCompletoUsuario);
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
            $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
            
            $sheet->setCellValue('H' . $rowNum, 'Área: ' . $dept['nombre']);
            $sheet->getStyle('H' . $rowNum)->getFont()->setBold(true);
            $sheet->mergeCells('H' . $rowNum . ':M' . $rowNum);
            $rowNum++;
            
            // Período row
            $sheet->setCellValue('A' . $rowNum, 'Período: ' . $primerDiaDelMes . ' al ' . $ultimoDiaDelMes);
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
            $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
            $rowNum++;
            
            // Revisado Por section (signature lines)
            $sheet->setCellValue('A' . $rowNum, 'Revisado Por: ____________________________');
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
            $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
            $rowNum++;
            
            $rowNum++;
            
            // HEADERS DE COLUMNAS
            $headerRow = $rowNum;
            foreach ($headers as $col => $title) {
                $sheet->setCellValue($col . $headerRow, $title);
            }
            $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
            $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $rowNum++;
            
            // DATOS
            try {
                $sql = "SELECT 
                            t.id AS expediente,
                            CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                            t.carnet_identidad AS ci,
                            COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                            COALESCE(c.salario, 0) AS salario_cargo,
                            COALESCE(p.horas, 192.00) AS horas,
                            COALESCE(p.bonif, 0) AS bonif,
                            COALESCE(p.ausencias, 0) AS ausencias,
                            COALESCE(
                                p.vacaciones,
                                (
                                    SELECT COALESCE(SUM(
                                        CASE 
                                            WHEN pv.fecha_aprobacion IS NOT NULL 
                                                 AND pv.dias IS NOT NULL 
                                                 AND pv.dias <> ''
                                            THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                            ELSE 0
                                        END
                                    ), 0)
                                    FROM plan_vacaciones pv
                                    WHERE pv.trabajador_id = t.id
                                      AND pv.estado IN ('Aprobado','Procesada')
                                      AND YEAR(pv.fecha_inicio) = :y
                                      AND MONTH(pv.fecha_inicio) = :m
                                )
                            ) AS vacaciones,
                            COALESCE(p.a_cobrar, 0) AS a_cobrar,
                            COALESCE(p.sal_dev, 0) AS sal_dev,
                            COALESCE(p.pago_vac, 0) AS pago_vac,
                            COALESCE(p.salario_neto, 0) AS salario_neto,
                            COALESCE(p.seg_social, 0) AS seg_social,
                            COALESCE(p.ing_pers_3, 0) AS ing_pers_3,
                            COALESCE(p.ing_pers_5, 0) AS ing_pers_5,
                            COALESCE(p.salario_pagar, 0) AS salario_pagar
                        FROM trabajadores t
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                        WHERE t.departamento_id = :dept_id
                        " . ($hasEmpresaIdTrabajadores && isset($this->app->empresa_id) ? " AND t.empresa_id = :eid" : "") . "
                        AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
                        ORDER BY t.id ASC";
                    $sqlParams = ['y' => $year, 'm' => $month, 'dept_id' => $dept['id']];
                    if ($hasEmpresaIdTrabajadores && $empresaId) { $sqlParams['eid'] = $empresaId; }
                
                    $rows = $this->db->fetchAll($sql, $sqlParams);
                
                foreach ($rows as $data) {
                    // Recalcular como en frontend (IML) con truncamiento. IP 5% sin tope máximo (escalado infinito desde 9510)
                    $expediente = $data['expediente'];
                    $nombre = $data['nombre'];
                    $ci = $data['ci'];
                    $horas = floatval($data['horas'] ?? 192);
                    $tarifa_display = floatval($data['tarifa'] ?? 0);
                    $salario_cargo_db = isset($data['salario_cargo']) ? floatval($data['salario_cargo']) : 0;
                    $tarifa_raw = $tarifa_display;
                    if ($salario_cargo_db > 1000) {
                        $tarifa_raw = $salario_cargo_db / 192.0;
                    }
                    if ($tarifa_raw > 20 && $tarifa_raw < 1000) {
                        $monthly = $tarifa_raw * 192.0;
                        $monthlyFloor = floor($monthly);
                        $frac = $monthly - $monthlyFloor;
                        if ($frac > 0.5) {
                            $tarifa_raw = $monthlyFloor / 192.0;
                            $salario_cargo_db = $monthlyFloor;
                        }
                    }
                    $tarifa_for_display = number_format(round($tarifa_raw, 2), 2, '.', '');
                    $bonif = floatval($data['bonif'] ?? 0);
                    $vacaciones = floatval($data['vacaciones'] ?? 0);
                    $ausencias = floatval($data['ausencias'] ?? 0);

                    // Aplicar overrides si existen para este trabajador
                    $tid = intval($data['expediente']);
                    if (isset($ovMap[$tid]) && is_array($ovMap[$tid])) {
                        $o = $ovMap[$tid];
                        if (isset($o['horas'])) { $horas = floatval($o['horas']); }
                        if (isset($o['tarifa'])) { $tarifa_display = floatval($o['tarifa']); }
                        if (isset($o['bonif'])) { $bonif = floatval($o['bonif']); }
                        if (isset($o['ausencias'])) { $ausencias = floatval($o['ausencias']); }
                        if (isset($o['vacaciones'])) { $vacaciones = floatval($o['vacaciones']); }
                        if (isset($o['pago_vac'])) { $pago_vac = floatval($o['pago_vac']); }
                    }
                    
                    // Compute salario_base (monthly salary) from tarifa and salario_cargo
                    $salario_base = ($salario_cargo_db > 1000) ? $salario_cargo_db : ($tarifa_raw * 192.0);
                    
                    $a_cobrar = $horas * $tarifa_raw;
                    $sal_dev = $a_cobrar + $bonif;
                    $pago_vac = ($vacaciones / 24.0) * $salario_base;
                    $costo_ausencias = $ausencias * 8 * $tarifa_raw;
                    $salario_neto = $sal_dev - $costo_ausencias + $pago_vac;
                    $seg_social = $salario_neto * 0.05;
                    $ing_pers_3 = 0;
                    $ing_pers_5 = 0;
                    if ($salario_neto >= 3260 && $salario_neto <= 9510) {
                        $ing_pers_3 = (9510 - 3260) * 0.03;
                    } elseif ($salario_neto > 9510) {
                        $ing_pers_3 = (9510 - 3260) * 0.03;
                        $ing_pers_5 = ($salario_neto - 9510) * 0.05;
                    }
                    $salario_pagar = $salario_neto - ($seg_social + $ing_pers_3 + $ing_pers_5);
                    
                    $sheet->setCellValue('A' . $rowNum, $expediente);
                    $sheet->setCellValue('B' . $rowNum, $nombre);
                    $sheet->setCellValue('C' . $rowNum, $ci);
                    $sheet->setCellValue('D' . $rowNum, floatval($horas));
                    // Tarifa: escribir valor numérico (raw) y usar formato para mostrar 2 decimales
                    $sheet->setCellValue('E' . $rowNum, floatval($tarifa_raw));
                    $sheet->setCellValue('F' . $rowNum, floatval($a_cobrar));
                    $sheet->setCellValue('G' . $rowNum, floatval($bonif));
                    $sheet->setCellValue('H' . $rowNum, floatval($sal_dev));
                    $sheet->setCellValue('I' . $rowNum, floatval($vacaciones));
                    $sheet->setCellValue('J' . $rowNum, floatval($pago_vac));
                    $sheet->setCellValue('K' . $rowNum, floatval($salario_neto));
                    $sheet->setCellValue('L' . $rowNum, floatval($seg_social));   // Seg Social
                    $sheet->setCellValue('M' . $rowNum, floatval($ing_pers_3));   // IP 3%
                    $sheet->setCellValue('N' . $rowNum, floatval($ing_pers_5));   // IP 5%
                    $sheet->setCellValue('O' . $rowNum, floatval($salario_pagar));
                    
                    // Apply 2-decimal formatting to numeric columns (E through O) and force '.' as decimal
                    $sheet->getStyle('E' . $rowNum . ':O' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
                    
                    $rowNum++;
                    $anyRows++;
                }
                
            } catch (Exception $e) {
                @error_log('ERROR obteniendo datos: ' . $e->getMessage());
            }
        }
        
        // Fallback: si no hubo ninguna fila (p. ej. trabajadores sin departamento), exportar todos
        if ($anyRows === 0) {
            @error_log('Fallback export: sin filas por departamento, exportando todos los trabajadores');
            
            // Encabezado general
            $sheet->setCellValue('A' . $rowNum, 'Prenómina ' . $nombreEmpresa . ' - ' . $nombreMes . ' de ' . $year . ' (General)');
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells('A' . $rowNum . ':O' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $rowNum += 2;
            $headerRow = $rowNum;
            foreach ($headers as $col => $title) { $sheet->setCellValue($col . $headerRow, $title); }
            $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
            $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $rowNum++;
            
            $sqlAll = "SELECT 
                            t.id AS expediente,
                            CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                            t.carnet_identidad AS ci,
                            COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                            COALESCE(c.salario, 0) AS salario_cargo,
                            COALESCE(p.horas, 192.00) AS horas,
                            COALESCE(p.bonif, 0) AS bonif,
                            COALESCE(p.ausencias, 0) AS ausencias,
                            COALESCE(
                                p.vacaciones,
                                (
                                    SELECT COALESCE(SUM(
                                        CASE 
                                            WHEN pv.fecha_aprobacion IS NOT NULL 
                                                 AND pv.dias IS NOT NULL 
                                                 AND pv.dias <> ''
                                            THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                            ELSE 0
                                        END
                                    ), 0)
                                    FROM plan_vacaciones pv
                                    WHERE pv.trabajador_id = t.id
                                      AND pv.estado IN ('Aprobado','Procesada')
                                      AND YEAR(pv.fecha_inicio) = :y
                                      AND MONTH(pv.fecha_inicio) = :m
                                )
                            ) AS vacaciones
                        FROM trabajadores t
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                        WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
                        " . (
                            ($hasEmpresaIdTrabajadores && isset($this->app->empresa_id))
                            ? " AND t.empresa_id = :eid"
                            : (
                                ($hasEmpresaId && isset($this->app->empresa_id))
                                ? " AND t.departamento_id IN (SELECT id FROM departamentos WHERE empresa_id = :eid)"
                                : ""
                            )
                        ) . "
                        ORDER BY t.id ASC";
            $sqlAllParams = ['y' => $year, 'm' => $month];
            if ((($hasEmpresaIdTrabajadores || $hasEmpresaId) && $empresaId)) { $sqlAllParams['eid'] = $empresaId; }
            $rowsAll = $this->db->fetchAll($sqlAll, $sqlAllParams);
            foreach ($rowsAll as $data) {
                $expediente = $data['expediente'];
                $nombre = $data['nombre'];
                $ci = $data['ci'];
                $horas = floatval($data['horas'] ?? 192);
                $tarifa_display = floatval($data['tarifa'] ?? 0);
                $salario_cargo_db = isset($data['salario_cargo']) ? floatval($data['salario_cargo']) : 0;
                $tarifa_raw = $tarifa_display;
                if ($salario_cargo_db > 1000) {
                    $tarifa_raw = $salario_cargo_db / 192.0;
                }
                if ($tarifa_raw > 20 && $tarifa_raw < 1000) {
                    $monthly = $tarifa_raw * 192.0;
                    $monthlyFloor = floor($monthly);
                    $frac = $monthly - $monthlyFloor;
                    if ($frac > 0.5) {
                        $tarifa_raw = $monthlyFloor / 192.0;
                        $salario_cargo_db = $monthlyFloor;
                    }
                }
                $tarifa_for_display = number_format(round($tarifa_raw, 2), 2, '.', '');
                $bonif = floatval($data['bonif'] ?? 0);
                $vacaciones = floatval($data['vacaciones'] ?? 0);
                $ausencias = floatval($data['ausencias'] ?? 0);
                
                // Compute salario_base (monthly salary) from tarifa and salario_cargo
                $salario_base = ($salario_cargo_db > 1000) ? $salario_cargo_db : ($tarifa_raw * 192.0);
                
                $a_cobrar = $horas * $tarifa_raw;
                $sal_dev = $a_cobrar + $bonif;
                $pago_vac = ($vacaciones / 24.0) * $salario_base;
                $costo_ausencias = $ausencias * 8 * $tarifa_raw;
                $salario_neto = $sal_dev - $costo_ausencias + $pago_vac;
                $seg_social = $salario_neto * 0.05;
                $ing_pers_3 = 0;
                $ing_pers_5 = 0;
                if ($salario_neto >= 3260 && $salario_neto <= 9510) {
                    $ing_pers_3 = (9510 - 3260) * 0.03;
                } elseif ($salario_neto > 9510) {
                    $ing_pers_3 = (9510 - 3260) * 0.03;
                    $ing_pers_5 = ($salario_neto - 9510) * 0.05;
                }
                $salario_pagar = $salario_neto - ($seg_social + $ing_pers_3 + $ing_pers_5);
                
                $sheet->setCellValue('A' . $rowNum, $expediente);
                $sheet->setCellValue('B' . $rowNum, $nombre);
                $sheet->setCellValue('C' . $rowNum, $ci);
                $sheet->setCellValue('D' . $rowNum, floatval($horas));
                $sheet->setCellValue('E' . $rowNum, floatval($tarifa_raw));
                $sheet->setCellValue('F' . $rowNum, floatval($a_cobrar));
                $sheet->setCellValue('G' . $rowNum, floatval($bonif));
                $sheet->setCellValue('H' . $rowNum, floatval($sal_dev));
                $sheet->setCellValue('I' . $rowNum, floatval($vacaciones));
                $sheet->setCellValue('J' . $rowNum, floatval($pago_vac));
                $sheet->setCellValue('K' . $rowNum, floatval($salario_neto));
                $sheet->setCellValue('L' . $rowNum, floatval($seg_social));   // Seg Social
                $sheet->setCellValue('M' . $rowNum, floatval($ing_pers_3));   // IP 3%
                $sheet->setCellValue('N' . $rowNum, floatval($ing_pers_5));   // IP 5%
                $sheet->setCellValue('O' . $rowNum, floatval($salario_pagar));
                
                // Apply 2-decimal formatting to numeric columns (E through O) and force '.' as decimal
                $sheet->getStyle('E' . $rowNum . ':O' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
                
                $rowNum++;
            }
        }

        // AUTO-AJUSTAR COLUMNAS
        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // FOOTER con información departamental y fecha
        $rowNum += 2;
        $sheet->setCellValue('A' . $rowNum, 'Emitido por Departamento de Recursos Humanos : ' . $fechaActual);
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setItalic(true)->setSize(10);
        $sheet->mergeCells('A' . $rowNum . ':O' . $rowNum);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // GENERAR Y DESCARGAR
        try {
            $filename = 'Prenomina2_' . preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $nombreEmpresa)) . '_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT);
            if (!$exportarTodos && !empty($departamentosExportar)) {
                $filename .= '_' . str_replace(' ', '_', $departamentosExportar[0]['nombre']);
            }
            $filename .= '_' . date('Ymd_His') . '.xlsx';
            
            @error_log('Generando archivo: ' . $filename);
            
            while (ob_get_level()) { @ob_end_clean(); }
            
            $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save($tempFile);
            
            @error_log('Archivo guardado: ' . $tempFile);
            
            while (ob_get_level()) { @ob_end_clean(); }
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            readfile($tempFile);
            @unlink($tempFile);
            exit;
            
        } catch (Exception $e) {
            @error_log('ERROR generando archivo: ' . $e->getMessage());
            while (ob_get_level()) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=utf-8');
            die('Error generando Excel: ' . $e->getMessage());
        }
    }

    private function _list_departamentos() {
        // Devolver solo nombres de departamentos (tabs por nombre)
        if (method_exists($this->app, 'get_list_departamentos')) {
            $rows = $this->app->get_list_departamentos();
        } else {
            // Verificar si existe la columna empresa_id
            $hasEmpresaId = false;
            try {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos LIKE 'empresa_id'");
                $hasEmpresaId = !empty($cols);
            } catch (Exception $e) {
                // Si hay error, asumimos que no existe la columna
                $hasEmpresaId = false;
            }

            // Construir la consulta según la existencia de empresa_id y el valor de app->empresa_id
            if ($hasEmpresaId && isset($this->app->empresa_id) && $this->app->empresa_id) {
                $rows = $this->db->fetchAll(
                    "SELECT id, nombre FROM departamentos WHERE empresa_id = :eid ORDER BY nombre ASC",
                    array('eid' => $this->app->empresa_id)
                );
            } else {
                $rows = $this->db->fetchAll("SELECT id, nombre FROM departamentos ORDER BY nombre ASC");
            }
        }
        $out = ['Todos'];  // Añadir pestaña "Todos" al inicio
        foreach ($rows as $r) {
            if (is_array($r)) {
                if (isset($r['nombre'])) {
                    $out[] = $r['nombre'];
                } else {
                    $out[] = (string)reset($r);
                }
            } else {
                $out[] = (string)$r;
            }
        }
        return $out;
    }

    /**
     * @deprecated OBSOLETO — Ya no se invoca. El devengo mensual de días de vacaciones
     * (+2.18 a vacaciones_acc) lo realiza ahora el cron independiente
     * cron_devengo_vacaciones.php (día 1 de cada mes, para todos los trabajadores activos).
     * Se conserva solo como referencia histórica y puede eliminarse en una limpieza posterior.
     *
     * Agregar 2.18 días de vacaciones a cada trabajador de la prenómina
     * Solo se ejecuta una vez por año/mes
     *
     * @param int $year Año de la prenómina
     * @param int $month Mes de la prenómina
     * @param array $rows Trabajadores procesados en la prenómina
     */
    private function _agregar_dias_vacaciones($year, $month, $rows) {
        try {
            // Verificar si ya se procesó este período
            $periodo_key = sprintf('%04d-%02d', $year, $month);
            
            // Verificar si existe la tabla de control
            $tableExists = $this->db->fetchAll("SHOW TABLES LIKE 'prenomina_vacaciones_procesadas'");
            
            if (empty($tableExists)) {
                // Crear tabla de control si no existe
                $createTable = "CREATE TABLE IF NOT EXISTS `prenomina_vacaciones_procesadas` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `year` INT(4) NOT NULL,
                    `month` INT(2) NOT NULL,
                    `fecha_proceso` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_periodo` (`year`, `month`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                
                $this->db->directExec($createTable);
                error_log("Prenomina: Tabla prenomina_vacaciones_procesadas creada");
            }
            
            // Verificar si ya se procesó este período
            $sqlCheck = "SELECT id FROM prenomina_vacaciones_procesadas WHERE `year` = :y AND `month` = :m";
            $yaProces = $this->db->fetchRow($sqlCheck, ['y' => $year, 'm' => $month]);
            
            if ($yaProces) {
                // Ya se procesó este período, no hacer nada
                error_log("Prenomina: Días de vacaciones ya agregados para período {$periodo_key}");
                return;
            }
            
            // Procesar cada trabajador
            $trabajadores_procesados = 0;
            foreach ($rows as $r) {
                $trabajador_id = isset($r['trabajador_id']) ? intval($r['trabajador_id']) : 0;
                
                if ($trabajador_id <= 0) {
                    continue;
                }
                
                try {
                    // Obtener días actuales de vacaciones
                    $sqlVac = "SELECT vacaciones_acc FROM trabajadores WHERE id = :tid";
                    $trabVac = $this->db->fetchRow($sqlVac, ['tid' => $trabajador_id]);
                    
                    if ($trabVac) {
                        $vacaciones_actuales = isset($trabVac['vacaciones_acc']) ? floatval($trabVac['vacaciones_acc']) : 0.0;
                        $nuevas_vacaciones = $vacaciones_actuales + 2.18;
                        
                        // Actualizar días de vacaciones
                        $this->db->update('trabajadores', 
                            ['vacaciones_acc' => $nuevas_vacaciones], 
                            ['id' => $trabajador_id]
                        );
                        
                        $trabajadores_procesados++;
                        error_log("Prenomina: Agregados 2.18 días de vacaciones al trabajador {$trabajador_id}. Anterior: {$vacaciones_actuales}, Nuevo: {$nuevas_vacaciones}");
                    }
                } catch (Exception $e) {
                    error_log("Prenomina: Error agregando vacaciones al trabajador {$trabajador_id}: " . $e->getMessage());
                    continue;
                }
            }
            
            // Registrar que este período ya fue procesado
            $this->db->insert('prenomina_vacaciones_procesadas', [
                'year' => $year,
                'month' => $month,
                'fecha_proceso' => date('Y-m-d H:i:s')
            ]);
            
            error_log("Prenomina: Proceso de vacaciones completado para {$periodo_key}. Trabajadores procesados: {$trabajadores_procesados}");
            
        } catch (Exception $e) {
            error_log("Prenomina: Error en _agregar_dias_vacaciones: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * _mayus - strtoupper seguro con acentos. Usa mbstring si está disponible y si no
     * degrada a strtoupper en lugar de provocar un fatal.
     */
    private function _mayus($texto) {
        $texto = (string) $texto;
        return function_exists('mb_strtoupper') ? mb_strtoupper($texto, 'UTF-8') : strtoupper($texto);
    }

    /**
     * _formato_fila_prenomina - Formato numérico de una fila de la hoja de prenómina.
     * Horas (D) y Vacaciones (I) van como ENTEROS, sin decimales ni separador de miles.
     * El resto de columnas numéricas van con 2 decimales y punto decimal.
     */
    private function _formato_fila_prenomina($sheet, $rowNum) {
        $sheet->getStyle('E' . $rowNum . ':H' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
        $sheet->getStyle('J' . $rowNum . ':O' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
        $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('0');
    }

    /**
     * _formato_fila_nomina - Igual que _formato_fila_prenomina pero con las columnas
     * desplazadas una posición a la izquierda (sin "No. Exp") y sin tocar la O (Firma, en
     * blanco). Horas (C) y Vacaciones (H) van como enteros; el resto con 2 decimales.
     */
    private function _formato_fila_nomina($sheet, $rowNum) {
        $sheet->getStyle('D' . $rowNum . ':G' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
        $sheet->getStyle('I' . $rowNum . ':N' . $rowNum)->getNumberFormat()->setFormatCode('[$-409]0.00');
        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('H' . $rowNum)->getNumberFormat()->setFormatCode('0');
    }

    /**
     * _export_prenomina2_binary - Exporta prenómina a Excel y la guarda en base de datos
     */
    private function _export_prenomina2_binary($param) {
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);
        
        while (ob_get_level()) { 
            @ob_end_clean(); 
        }
        
        try {
            // Parámetros
            if (!empty($param['mes']) && strpos($param['mes'], '-') !== false) {
                list($py, $pm) = explode('-', $param['mes']);
                $year = intval($py);
                $month = intval($pm);
            } else {
                $year = isset($param['year']) ? intval($param['year']) : intval(date('Y'));
                $month = isset($param['month']) ? intval($param['month']) : intval(date('n'));
            }
            
            @error_log('=== EXPORT PRENOMINA BINARY INICIADO ===');
            @error_log('Year: ' . $year . ', Month: ' . $month);

            // Cargar overrides (valores actuales de la tabla enviados por el cliente).
            // Esta construcción faltaba: más abajo se consultaba $ovMap sin haberlo definido
            // nunca, así que los valores editados en pantalla se ignoraban al exportar.
            $overrides = [];
            try {
                if (!empty($param['overrides'])) {
                    $decoded = json_decode($param['overrides'], true);
                    if (is_array($decoded)) $overrides = $decoded;
                } else {
                    $raw = @file_get_contents('php://input');
                    if ($raw) {
                        $body = json_decode($raw, true);
                        if (!empty($body['overrides']) && is_array($body['overrides'])) {
                            $overrides = $body['overrides'];
                        }
                    }
                }
            } catch (Exception $e) {
                $overrides = [];
            }

            // Normalizar overrides a mapa [trabajador_id] => data
            $ovMap = [];
            if (is_array($overrides)) {
                foreach ($overrides as $k => $v) {
                    if (!is_array($v)) continue;
                    // Normalizar nombres desde el cliente (vac_dias -> vacaciones)
                    if (isset($v['vac_dias']) && !isset($v['vacaciones'])) {
                        $v['vacaciones'] = $v['vac_dias'];
                    }
                    if (isset($v['trabajador_id'])) {
                        $ovMap[intval($v['trabajador_id'])] = $v;
                    } elseif (isset($v['id'])) {
                        $ovMap[intval($v['id'])] = $v;
                    } elseif (is_numeric($k)) {
                        $ovMap[intval($k)] = $v;
                    }
                }
            }

            // Verificar si existe la columna empresa_id en departamentos y trabajadores
            $hasEmpresaIdDepts = false;
            $hasEmpresaIdTrabajadores = false;
            try {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos LIKE 'empresa_id'");
                $hasEmpresaIdDepts = !empty($cols);
            } catch (Exception $e) {
                $hasEmpresaIdDepts = false;
            }
            try {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM trabajadores LIKE 'empresa_id'");
                $hasEmpresaIdTrabajadores = !empty($cols);
            } catch (Exception $e) {
                $hasEmpresaIdTrabajadores = false;
            }

            // Determinar qué departamentos exportar
            $tabRaw = isset($param['tab']) ? trim($param['tab']) : '';
            $tabNorm = mb_strtolower($tabRaw);
            $exportarTodos = ($tabNorm === '' || $tabNorm === 'todos');
            
            $departamentosExportar = [];
            
            if ($exportarTodos) {
                $sqlDepts = "SELECT id, nombre FROM departamentos";
                if ($hasEmpresaIdDepts && isset($this->app->empresa_id)) {
                    $sqlDepts .= " WHERE empresa_id = :eid ORDER BY nombre ASC";
                    $depts = $this->db->fetchAll($sqlDepts, ['eid' => $this->app->empresa_id]);
                } else {
                    $sqlDepts .= " ORDER BY nombre ASC";
                    $depts = $this->db->fetchAll($sqlDepts);
                }
                
                foreach ($depts as $dept) {
                    $departamentosExportar[] = [
                        'id' => $dept['id'],
                        'nombre' => $dept['nombre']
                    ];
                }
            } else {
                $deptParams = ['tab' => $tabRaw];
                $sqlDept = "SELECT id, nombre FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab))";
                if ($hasEmpresaIdDepts && isset($this->app->empresa_id)) {
                    $sqlDept .= " AND empresa_id = :eid";
                    $deptParams['eid'] = $this->app->empresa_id;
                }
                $sqlDept .= " LIMIT 1";
                
                $dept = $this->db->fetchRow($sqlDept, $deptParams);
                if ($dept) {
                    $departamentosExportar[] = [
                        'id' => $dept['id'],
                        'nombre' => $dept['nombre']
                    ];
                }
            }
            
            if (empty($departamentosExportar)) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'No hay departamentos para exportar']);
                return;
            }

            // SQL base para obtener datos
            $sqlBase = "SELECT 
                            t.id AS expediente,
                            CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                            t.carnet_identidad AS ci,
                            COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                            COALESCE(c.salario, 0) AS salario_cargo,
                            COALESCE(p.horas, 192.00) AS horas,
                            COALESCE(p.vacaciones,
                                COALESCE((
                                    SELECT SUM(
                                        CASE 
                                            WHEN pv.fecha_aprobacion IS NOT NULL 
                                                 AND pv.dias IS NOT NULL 
                                                 AND pv.dias <> ''
                                            THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                            ELSE 0
                                        END
                                    ) 
                                    FROM plan_vacaciones pv
                                    WHERE pv.trabajador_id = t.id
                                      AND pv.estado IN ('Aprobado','Procesada')
                                      AND YEAR(pv.fecha_inicio) = :y
                                      AND MONTH(pv.fecha_inicio) = :m
                                ), 0)
                            ) AS vacaciones,
                            COALESCE(p.a_cobrar, 0) AS a_cobrar,
                            COALESCE(p.sal_dev, 0) AS sal_dev,
                            COALESCE(p.pago_vac, 0) AS pago_vac,
                            COALESCE(p.salario_neto, 0) AS salario_neto,
                            COALESCE(p.seg_social, 0) AS seg_social,
                            COALESCE(p.ing_pers_3, 0) AS ing_pers_3,
                            COALESCE(p.ing_pers_5, 0) AS ing_pers_5,
                            COALESCE(p.salario_pagar, 0) AS salario_pagar,
                            COALESCE(p.bonif, 0) AS bonif
                        FROM trabajadores t
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                        WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
                        AND t.departamento_id = :dept_id
                        " . ($hasEmpresaIdTrabajadores && isset($this->app->empresa_id) ? "AND t.empresa_id = :eid " : "") . "
                        ORDER BY t.id ASC";

            // Cargar PHPExcel
            try {
                require_once(BASE_CLASS . '/PHPExcel.php');
                $excel = new PHPExcel();
                $excel->getProperties()
                    ->setCreator('Sistema de Prenómina')
                    ->setTitle('Prenómina ' . $year . '-' . $month);
                $sheet = $excel->setActiveSheetIndex(0);
                $sheet->setTitle('Prenómina');

                // Fuente base más compacta: 15 columnas entran mejor a lo ancho al imprimir.
                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

                // --- Configuración de impresión (apaisado, ajustado al ancho) ---
                $ps = $sheet->getPageSetup();
                $ps->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                $ps->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
                $ps->setFitToPage(true);
                $ps->setFitToWidth(1);   // todo el ancho en 1 página
                $ps->setFitToHeight(0);  // alto libre: tantas páginas como haga falta
                $ps->setHorizontalCentered(true);

                // Margen superior algo mayor: ahí va el título, que se repite en cada hoja.
                $sheet->getPageMargins()->setTop(0.75)->setBottom(0.4)->setLeft(0.3)->setRight(0.3)
                    ->setHeader(0.25)->setFooter(0.2);

                // Pie de página con numeración
                $sheet->getHeaderFooter()->setOddFooter('&L&9Generado ' . date('d/m/Y H:i') . '&R&9Página &P de &N');
            } catch (Exception $e) {
                @error_log('ERROR cargando PHPExcel: ' . $e->getMessage());
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'Error cargando PHPExcel']);
                return;
            }
            
            $nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Sistema';
            
            // Obtener nombre de la empresa activa
            $nombreEmpresa = 'Empresa';
            if (isset($this->app->empresa_id) && $this->app->empresa_id > 0) {
                try {
                    // OJO: la tabla es `empresa` (singular). Antes decía `empresas` y la query
                    // lanzaba excepción; el catch la absorbía y siempre quedaba el default 'IML'.
                    $empresaRow = $this->db->fetchRow("SELECT nombre FROM empresa WHERE id = :eid LIMIT 1", ['eid' => $this->app->empresa_id]);
                    if ($empresaRow && isset($empresaRow['nombre'])) {
                        $nombreEmpresa = $empresaRow['nombre'];
                    }
                } catch (Exception $e) {
                    $nombreEmpresa = 'Empresa';
                }
            }
            
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $nombreMes = isset($meses[$month]) ? $meses[$month] : 'Mes ' . $month;
            
            $headers = [
                'A' => 'No. Exp',
                'B' => 'Nombre y Apellido',
                'C' => 'C.I',
                'D' => 'Horas',
                'E' => 'Tarifa',
                'F' => 'A Cobrar',
                'G' => 'Bonif',
                'H' => 'Sal. Dev',
                'I' => 'Vacaciones',
                'J' => 'Pago Vac',
                'K' => 'Sal. Neto',
                'L' => 'Seg Social',
                'M' => 'Ing Pers 3%',
                'N' => 'Ing Pers 5%',
                'O' => 'Sal. a Pagar',
            ];
            
            $rowNum = 1;

            // Columnas que llevan totales por área y total general.
            // F=A Cobrar, G=Bonif, H=Sal. Dev, I=Vacaciones, J=Pago Vac, K=Sal. Neto,
            // L=Seg Social, M=Ing Pers 3%, N=Ing Pers 5%, O=Sal. a Pagar
            $colsTotales = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];
            // Campo de datos que alimenta cada columna
            $campoDeCol = [
                'F' => 'a_cobrar',   'G' => 'bonif',      'H' => 'sal_dev',
                'I' => 'vacaciones', 'J' => 'pago_vac',   'K' => 'salario_neto',
                'L' => 'seg_social', 'M' => 'ing_pers_3', 'N' => 'ing_pers_5',
                'O' => 'salario_pagar',
            ];
            $granTotales = array_fill_keys($colsTotales, 0.0);
            $huboAlgunaFila = false;

            // Título general en el ENCABEZADO de página: se imprime en todas las hojas y no
            // consume ninguna fila del cuerpo. En los códigos de encabezado de Excel el '&'
            // es carácter de control, así que hay que duplicarlo si viene en el nombre.
            $tituloImpresion = 'Prenómina ' . $nombreEmpresa . ' - Mes de ' . $nombreMes . ' de ' . $year;
            $sheet->getHeaderFooter()->setOddHeader('&C&12&B' . str_replace('&', '&&', $tituloImpresion));

            // --- Paginación por capacidad ---------------------------------------------
            // En lugar de una página por área, se empaquetan tantas áreas como quepan en la
            // hoja y solo se corta cuando el bloque siguiente no entra. Se lleva la cuenta en
            // PUNTOS (unidad de alto de fila de Excel) para que el cálculo sea determinista.
            // Carta apaisada menos márgenes sup./inf. Se descuenta el alto de una fila como
            // colchón: Excel redondea alturas al renderizar y sin margen un bloque calculado
            // "justo" puede desbordar y dejar media hoja vacía.
            $ALTO_PAGINA = ((8.5 - 0.75 - 0.4) * 72) - 12;
            $H_AREA = 15;   // fila "Área: X / Elaborado por"
            $H_CAB  = 15;   // fila de cabeceras de columna
            $H_DATO = 12;   // fila de trabajador
            $H_TOT  = 15;   // fila de total del área
            $H_SEP  = 8;    // fila separadora entre áreas
            $usadoPag = 0.0;

            // Procesar cada departamento
            foreach ($departamentosExportar as $dept) {
                $vals = ['y' => $year, 'm' => $month, 'dept_id' => $dept['id']];
                if ($hasEmpresaIdTrabajadores && isset($this->app->empresa_id)) {
                    $vals['eid'] = $this->app->empresa_id;
                }
                $result = $this->db->fetchAll($sqlBase, $vals);

                // Un área sin trabajadores solo gastaría papel: se omite.
                if (empty($result)) {
                    continue;
                }

                $primerBloque = ($rowNum === 1);
                $altoBloque = $H_AREA + $H_CAB + (count($result) * $H_DATO) + $H_TOT;

                if (!$primerBloque) {
                    if (($usadoPag + $H_SEP + $altoBloque) > $ALTO_PAGINA) {
                        // No cabe entero: el bloque arranca en página nueva. El corte va en la
                        // última fila del bloque anterior y NO se emite fila separadora: el propio
                        // salto ya separa, y una fila en blanco aquí se desbordaría a la hoja
                        // siguiente dejándola prácticamente vacía.
                        $sheet->setBreak('A' . ($rowNum - 1), PHPExcel_Worksheet::BREAK_ROW);
                        $usadoPag = 0.0;
                    } else {
                        // Cabe en la misma hoja: fila separadora fina entre áreas
                        $sheet->getRowDimension($rowNum)->setRowHeight($H_SEP);
                        $usadoPag += $H_SEP;
                        $rowNum++;
                    }
                }

                // Cabecera del área. El título general (empresa y mes) va en el encabezado de
                // página de Excel: se repite en cada hoja sin consumir filas del cuerpo.
                $sheet->setCellValue('A' . $rowNum, 'Área: ' . $dept['nombre']);
                $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells('A' . $rowNum . ':H' . $rowNum);
                $sheet->setCellValue('I' . $rowNum, 'Elaborado Por: ' . $nombreUsuario);
                $sheet->getStyle('I' . $rowNum)->getFont()->setBold(true);
                $sheet->mergeCells('I' . $rowNum . ':O' . $rowNum);
                $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($rowNum)->setRowHeight($H_AREA);
                $rowNum++;

                $headerRow = $rowNum;
                foreach ($headers as $col => $title) {
                    $sheet->setCellValue($col . $headerRow, $title);
                    $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
                }
                $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
                $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($headerRow)->setRowHeight($H_CAB);
                $rowNum++;

                $usadoPag += $altoBloque;

                $deptTotales = array_fill_keys($colsTotales, 0.0);
                $filasDelDept = 0;

                foreach ($result as $row) {
                    // Aplicar overrides si existen
                    $tid = intval($row['expediente']);
                    if (isset($ovMap[$tid]) && is_array($ovMap[$tid])) {
                        $o = $ovMap[$tid];
                        if (isset($o['horas'])) { $row['horas'] = $o['horas']; }
                        if (isset($o['tarifa'])) { $row['tarifa'] = $o['tarifa']; }
                        if (isset($o['bonif'])) { $row['bonif'] = $o['bonif']; }
                        if (isset($o['ausencias'])) { $row['ausencias'] = $o['ausencias']; }
                        if (isset($o['vacaciones'])) { $row['vacaciones'] = $o['vacaciones']; }
                        if (isset($o['pago_vac'])) { $row['pago_vac'] = $o['pago_vac']; }
                    }

                    $sheet->setCellValue('A' . $rowNum, $row['expediente']);
                    $sheet->setCellValue('B' . $rowNum, $row['nombre']);

                    // C.I. como TEXTO para no perder los ceros a la izquierda: si se escribe como
                    // número, Excel lo interpreta como numérico y se come el 0 inicial.
                    $ci = trim((string) $row['ci']);
                    if ($ci !== '' && ctype_digit($ci) && strlen($ci) < 11) {
                        $ci = str_pad($ci, 11, '0', STR_PAD_LEFT); // CI cubano: 11 dígitos
                    }
                    $sheet->setCellValueExplicit('C' . $rowNum, $ci, PHPExcel_Cell_DataType::TYPE_STRING);

                    $sheet->setCellValue('D' . $rowNum, floatval($row['horas']));
                    $sheet->setCellValue('E' . $rowNum, floatval($row['tarifa']));
                    $sheet->setCellValue('F' . $rowNum, floatval($row['a_cobrar']));
                    $sheet->setCellValue('G' . $rowNum, floatval($row['bonif']));
                    $sheet->setCellValue('H' . $rowNum, floatval($row['sal_dev']));
                    $sheet->setCellValue('I' . $rowNum, floatval($row['vacaciones']));
                    $sheet->setCellValue('J' . $rowNum, floatval($row['pago_vac']));
                    $sheet->setCellValue('K' . $rowNum, floatval($row['salario_neto']));
                    $sheet->setCellValue('L' . $rowNum, floatval($row['seg_social']));
                    $sheet->setCellValue('M' . $rowNum, floatval($row['ing_pers_3']));
                    $sheet->setCellValue('N' . $rowNum, floatval($row['ing_pers_5']));
                    $sheet->setCellValue('O' . $rowNum, floatval($row['salario_pagar']));

                    $this->_formato_fila_prenomina($sheet, $rowNum);
                    // Rejilla: sin bordes la tabla impresa es difícil de seguir
                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getBorders()->getAllBorders()
                        ->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
                    $sheet->getRowDimension($rowNum)->setRowHeight($H_DATO);

                    // Acumular para el total del área
                    foreach ($colsTotales as $col) {
                        $deptTotales[$col] += floatval($row[$campoDeCol[$col]]);
                    }
                    $filasDelDept++;

                    $rowNum++;
                }

                // Fila de TOTAL del área (solo si el área tuvo trabajadores)
                if ($filasDelDept > 0) {
                    $sheet->setCellValue('A' . $rowNum, 'TOTAL ' . $this->_mayus($dept['nombre']));
                    $sheet->mergeCells('A' . $rowNum . ':E' . $rowNum);
                    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    foreach ($colsTotales as $col) {
                        $sheet->setCellValue($col . $rowNum, $deptTotales[$col]);
                        $granTotales[$col] += $deptTotales[$col];
                    }

                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getBorders()->getTop()
                        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $this->_formato_fila_prenomina($sheet, $rowNum);
                    $sheet->getRowDimension($rowNum)->setRowHeight($H_TOT);

                    $huboAlgunaFila = true;
                    $rowNum++;
                }

                // Si el área ocupó más de una página, dejar en $usadoPag solo el sobrante
                // que quedó en la última hoja.
                while ($usadoPag > $ALTO_PAGINA) {
                    $usadoPag -= $ALTO_PAGINA;
                }
            }

            // TOTAL GENERAL de la empresa (suma de todas las áreas)
            if ($huboAlgunaFila) {
                // Si el total general no cabe en lo que queda de hoja, va a la siguiente.
                if (($usadoPag + $H_SEP + $H_TOT) > $ALTO_PAGINA) {
                    $sheet->setBreak('A' . ($rowNum - 1), PHPExcel_Worksheet::BREAK_ROW);
                } else {
                    $sheet->getRowDimension($rowNum)->setRowHeight($H_SEP);
                    $rowNum++;
                }

                $sheet->setCellValue('A' . $rowNum, 'TOTAL GENERAL ' . $this->_mayus($nombreEmpresa));
                $sheet->mergeCells('A' . $rowNum . ':E' . $rowNum);
                $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($rowNum)->setRowHeight($H_TOT);

                foreach ($colsTotales as $col) {
                    $sheet->setCellValue($col . $rowNum, $granTotales[$col]);
                }

                $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFont()->setBold(true)
                    ->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
                $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
                $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getBorders()->getTop()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->_formato_fila_prenomina($sheet, $rowNum);
            }

            // Anchos fijos en lugar de autosize: con autosize el ancho total depende del
            // contenido y al ajustar a 1 página la escala se vuelve impredecible (a veces
            // ilegible). Con anchos fijos el resultado impreso es siempre el mismo.
            $anchos = [
                'A' => 8,   // No. Exp
                'B' => 28,  // Nombre y Apellido
                'C' => 13,  // C.I
                'D' => 6,   // Horas
                'E' => 8,   // Tarifa
                'F' => 10,  // A Cobrar
                'G' => 8,   // Bonif
                'H' => 10,  // Sal. Dev
                'I' => 9,   // Vacaciones
                'J' => 10,  // Pago Vac
                'K' => 10,  // Sal. Neto
                'L' => 10,  // Seg Social
                'M' => 10,  // Ing Pers 3%
                'N' => 10,  // Ing Pers 5%
                'O' => 11,  // Sal. a Pagar
            ];
            foreach ($anchos as $col => $ancho) {
                $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($ancho);
            }

            // Área de impresión: hasta la última fila escrita
            $sheet->getPageSetup()->setPrintArea('A1:O' . max(1, $rowNum));

            // Generar archivo en memoria
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            ob_start();
            $writer->save('php://output');
            $excelBinary = ob_get_clean();
            
            // Guardar en base de datos
            try {
                $fecha = date('Y-m-d');
                $mes_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                
                // Verificar si ya existe
                $existing = $this->db->fetchRow(
                    "SELECT id FROM export_prenomina WHERE mes = :mes",
                    ['mes' => $mes_date]
                );
                
                if ($existing) {
                    $this->db->update('export_prenomina', 
                        ['export' => $excelBinary],
                        ['id' => $existing['id']]
                    );
                    @error_log("Prenomina: Excel actualizado en export_prenomina para {$mes_date}");
                } else {
                    $this->db->insert('export_prenomina', [
                        'mes' => $mes_date,
                        'export' => $excelBinary
                    ]);
                    @error_log("Prenomina: Excel guardado en export_prenomina para {$mes_date}");
                }
                
                // ✅ ACTUALIZAR submayor_vacaciones después de guardar exitosamente
                $this->_update_submayor_vacaciones_from_prenomina($year, $month);
                
                // Enviar archivo
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="Prenomina_' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx"');
                header('Content-Length: ' . strlen($excelBinary));
                echo $excelBinary;
                exit;
                
            } catch (Exception $e) {
                @error_log('Error guardando en export_prenomina: ' . $e->getMessage());
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'Error guardando Excel']);
            }
            
        } catch (Exception $e) {
            @error_log('ERROR en _export_prenomina2_binary: ' . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'error' => $e->getMessage()]);
        }
    }

    /**
     * _export_nomina2_binary - Excel de "Nómina" para imprimir y firmar en papel.
     *
     * Es una vista distinta de LOS MISMOS datos ya guardados en `prenomina` para el período:
     * sin la columna "No. Exp", con el título "Nómina <Empresa>" y un campo "Cheque de Pago"
     * en las primeras filas, y con una columna "Firma" en blanco al final de cada fila.
     *
     * A PROPÓSITO no hace nada de lo que hace _export_prenomina2_binary aparte de leer y
     * generar el Excel: no guarda nada en `export_prenomina` ni llama a
     * _update_submayor_vacaciones_from_prenomina(). Esa función resta vac_dias/pago_vac de
     * submayor_vacaciones cada vez que se invoca, SIN candado de idempotencia — llamarla de
     * nuevo aquí restaría esos días una segunda vez. Esta exportación es solo para imprimir,
     * nunca debe repetir un efecto contable que ya ocurrió al generar la Prenómina original.
     */
    private function _export_nomina2_binary($param) {
        @ini_set('display_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0);

        while (ob_get_level()) {
            @ob_end_clean();
        }

        try {
            if (!empty($param['mes']) && strpos($param['mes'], '-') !== false) {
                list($py, $pm) = explode('-', $param['mes']);
                $year = intval($py);
                $month = intval($pm);
            } else {
                $year = isset($param['year']) ? intval($param['year']) : intval(date('Y'));
                $month = isset($param['month']) ? intval($param['month']) : intval(date('n'));
            }

            @error_log('=== EXPORT NOMINA BINARY INICIADO ===');
            @error_log('Year: ' . $year . ', Month: ' . $month);

            // Overrides (igual que en prenomina binaria; en la práctica el botón de la lista de
            // calendario no envía ninguno, pero se soporta por si se llama con ellos).
            $overrides = [];
            try {
                if (!empty($param['overrides'])) {
                    $decoded = json_decode($param['overrides'], true);
                    if (is_array($decoded)) $overrides = $decoded;
                } else {
                    $raw = @file_get_contents('php://input');
                    if ($raw) {
                        $body = json_decode($raw, true);
                        if (!empty($body['overrides']) && is_array($body['overrides'])) {
                            $overrides = $body['overrides'];
                        }
                    }
                }
            } catch (Exception $e) {
                $overrides = [];
            }

            $ovMap = [];
            if (is_array($overrides)) {
                foreach ($overrides as $k => $v) {
                    if (!is_array($v)) continue;
                    if (isset($v['vac_dias']) && !isset($v['vacaciones'])) {
                        $v['vacaciones'] = $v['vac_dias'];
                    }
                    if (isset($v['trabajador_id'])) {
                        $ovMap[intval($v['trabajador_id'])] = $v;
                    } elseif (isset($v['id'])) {
                        $ovMap[intval($v['id'])] = $v;
                    } elseif (is_numeric($k)) {
                        $ovMap[intval($k)] = $v;
                    }
                }
            }

            $hasEmpresaIdDepts = false;
            $hasEmpresaIdTrabajadores = false;
            try {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM departamentos LIKE 'empresa_id'");
                $hasEmpresaIdDepts = !empty($cols);
            } catch (Exception $e) {
                $hasEmpresaIdDepts = false;
            }
            try {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM trabajadores LIKE 'empresa_id'");
                $hasEmpresaIdTrabajadores = !empty($cols);
            } catch (Exception $e) {
                $hasEmpresaIdTrabajadores = false;
            }

            $tabRaw = isset($param['tab']) ? trim($param['tab']) : '';
            $tabNorm = mb_strtolower($tabRaw);
            $exportarTodos = ($tabNorm === '' || $tabNorm === 'todos');

            $departamentosExportar = [];

            if ($exportarTodos) {
                $sqlDepts = "SELECT id, nombre FROM departamentos";
                if ($hasEmpresaIdDepts && isset($this->app->empresa_id)) {
                    $sqlDepts .= " WHERE empresa_id = :eid ORDER BY nombre ASC";
                    $depts = $this->db->fetchAll($sqlDepts, ['eid' => $this->app->empresa_id]);
                } else {
                    $sqlDepts .= " ORDER BY nombre ASC";
                    $depts = $this->db->fetchAll($sqlDepts);
                }

                foreach ($depts as $dept) {
                    $departamentosExportar[] = ['id' => $dept['id'], 'nombre' => $dept['nombre']];
                }
            } else {
                $deptParams = ['tab' => $tabRaw];
                $sqlDept = "SELECT id, nombre FROM departamentos WHERE TRIM(LOWER(nombre)) = TRIM(LOWER(:tab))";
                if ($hasEmpresaIdDepts && isset($this->app->empresa_id)) {
                    $sqlDept .= " AND empresa_id = :eid";
                    $deptParams['eid'] = $this->app->empresa_id;
                }
                $sqlDept .= " LIMIT 1";

                $dept = $this->db->fetchRow($sqlDept, $deptParams);
                if ($dept) {
                    $departamentosExportar[] = ['id' => $dept['id'], 'nombre' => $dept['nombre']];
                }
            }

            if (empty($departamentosExportar)) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'No hay departamentos para exportar']);
                return;
            }

            // Misma consulta que la prenómina binaria (se sigue seleccionando t.id AS expediente
            // solo para poder cruzar $ovMap por trabajador; no se imprime en esta variante).
            $sqlBase = "SELECT
                            t.id AS expediente,
                            CONCAT(t.nombre, ' ', t.apellidos) AS nombre,
                            t.carnet_identidad AS ci,
                            COALESCE((CASE WHEN COALESCE(c.salario, 0) > 1000 THEN c.salario/192 ELSE c.salario END), 0) AS tarifa,
                            COALESCE(c.salario, 0) AS salario_cargo,
                            COALESCE(p.horas, 192.00) AS horas,
                            COALESCE(p.vacaciones,
                                COALESCE((
                                    SELECT SUM(
                                        CASE
                                            WHEN pv.fecha_aprobacion IS NOT NULL
                                                 AND pv.dias IS NOT NULL
                                                 AND pv.dias <> ''
                                            THEN (LENGTH(pv.dias) - LENGTH(REPLACE(pv.dias, ',', '')) + 1)
                                            ELSE 0
                                        END
                                    )
                                    FROM plan_vacaciones pv
                                    WHERE pv.trabajador_id = t.id
                                      AND pv.estado IN ('Aprobado','Procesada')
                                      AND YEAR(pv.fecha_inicio) = :y
                                      AND MONTH(pv.fecha_inicio) = :m
                                ), 0)
                            ) AS vacaciones,
                            COALESCE(p.a_cobrar, 0) AS a_cobrar,
                            COALESCE(p.sal_dev, 0) AS sal_dev,
                            COALESCE(p.pago_vac, 0) AS pago_vac,
                            COALESCE(p.salario_neto, 0) AS salario_neto,
                            COALESCE(p.seg_social, 0) AS seg_social,
                            COALESCE(p.ing_pers_3, 0) AS ing_pers_3,
                            COALESCE(p.ing_pers_5, 0) AS ing_pers_5,
                            COALESCE(p.salario_pagar, 0) AS salario_pagar,
                            COALESCE(p.bonif, 0) AS bonif
                        FROM trabajadores t
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        LEFT JOIN prenomina p ON p.trabajador_id = t.id AND p.year = :y AND p.month = :m
                        WHERE (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
                        AND t.departamento_id = :dept_id
                        " . ($hasEmpresaIdTrabajadores && isset($this->app->empresa_id) ? "AND t.empresa_id = :eid " : "") . "
                        ORDER BY t.id ASC";

            try {
                require_once(BASE_CLASS . '/PHPExcel.php');
                $excel = new PHPExcel();
                $excel->getProperties()
                    ->setCreator('Sistema de Prenómina')
                    ->setTitle('Nómina ' . $year . '-' . $month);
                $sheet = $excel->setActiveSheetIndex(0);
                $sheet->setTitle('Nómina');

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

                $ps = $sheet->getPageSetup();
                $ps->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                $ps->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
                $ps->setFitToPage(true);
                $ps->setFitToWidth(1);
                $ps->setFitToHeight(0);
                $ps->setHorizontalCentered(true);

                $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3)
                    ->setHeader(0.2)->setFooter(0.2);

                $sheet->getHeaderFooter()->setOddFooter('&L&9Generado ' . date('d/m/Y H:i') . '&R&9Página &P de &N');
            } catch (Exception $e) {
                @error_log('ERROR cargando PHPExcel: ' . $e->getMessage());
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'Error cargando PHPExcel']);
                return;
            }

            $nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Sistema';

            $nombreEmpresa = 'Empresa';
            if (isset($this->app->empresa_id) && $this->app->empresa_id > 0) {
                try {
                    $empresaRow = $this->db->fetchRow("SELECT nombre FROM empresa WHERE id = :eid LIMIT 1", ['eid' => $this->app->empresa_id]);
                    if ($empresaRow && isset($empresaRow['nombre'])) {
                        $nombreEmpresa = $empresaRow['nombre'];
                    }
                } catch (Exception $e) {
                    $nombreEmpresa = 'Empresa';
                }
            }

            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $nombreMes = isset($meses[$month]) ? $meses[$month] : 'Mes ' . $month;

            // Cabeceras SIN "No. Exp" y CON "Firma" al final. Al quitar una columna al inicio y
            // añadir una al final el total de columnas no cambia (15, A-O), así que el resto del
            // diseño (anchos, área de impresión) puede reutilizar el mismo rango de letras.
            $headers = [
                'A' => 'Nombre y Apellido',
                'B' => 'C.I',
                'C' => 'Horas',
                'D' => 'Tarifa',
                'E' => 'A Cobrar',
                'F' => 'Bonif',
                'G' => 'Sal. Dev',
                'H' => 'Vacaciones',
                'I' => 'Pago Vac',
                'J' => 'Sal. Neto',
                'K' => 'Seg Social',
                'L' => 'Ing Pers 3%',
                'M' => 'Ing Pers 5%',
                'N' => 'Sal. a Pagar',
                'O' => 'Firma',
            ];

            // 'H' (Vacaciones) queda FUERA a propósito: no se totaliza ni por área ni en el
            // general. Cada fila sigue mostrando los días de ese trabajador; sumarlos entre
            // varios trabajadores no es un dato significativo en esta hoja de firma.
            $colsTotales = ['E', 'F', 'G', 'I', 'J', 'K', 'L', 'M', 'N'];
            $campoDeCol = [
                'E' => 'a_cobrar', 'F' => 'bonif',      'G' => 'sal_dev',
                'I' => 'pago_vac', 'J' => 'salario_neto',
                'K' => 'seg_social', 'L' => 'ing_pers_3', 'M' => 'ing_pers_5',
                'N' => 'salario_pagar',
            ];
            $granTotales = array_fill_keys($colsTotales, 0.0);
            $granTrabajadores = 0; // conteo acumulado de trabajadores para el TOTAL GENERAL
            $huboAlgunaFila = false;

            // --- Título y "Cheque de Pago" como filas del propio documento (no en el encabezado
            // de página): a diferencia de la prenómina, aquí deben verse igual al abrir el Excel
            // en pantalla, no solo al imprimir. Aparecen una única vez, al principio de todo.
            $rowNum = 1;

            $sheet->setCellValue('A' . $rowNum, 'Nómina ' . $nombreEmpresa . ' - Mes de ' . $nombreMes . ' de ' . $year);
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells('A' . $rowNum . ':O' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;

            $sheet->setCellValue('A' . $rowNum, 'Cheque de Pago: ');
            $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
            $sheet->mergeCells('B' . $rowNum . ':F' . $rowNum); // línea en blanco para rellenar a mano
            $sheet->getStyle('B' . $rowNum . ':F' . $rowNum)->getBorders()->getBottom()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getRowDimension($rowNum)->setRowHeight(18);
            $rowNum++;

            // Fila separadora antes de la primera área
            $sheet->getRowDimension($rowNum)->setRowHeight(8);
            $rowNum++;

            // --- Paginación por capacidad, igual que en la prenómina binaria, pero sembrando el
            // espacio ya consumido por el título y el campo de cheque en la página 1.
            $ALTO_PAGINA = ((8.5 - 0.4 - 0.4) * 72) - 12;
            $H_AREA = 15;
            $H_CAB  = 15;
            $H_DATO = 12;
            $H_TOT  = 15;
            $H_SEP  = 8;
            $usadoPag = 22.0 + 18.0 + 8.0; // título + cheque de pago + separador ya escritos

            $primerBloque = true;

            foreach ($departamentosExportar as $dept) {
                $vals = ['y' => $year, 'm' => $month, 'dept_id' => $dept['id']];
                if ($hasEmpresaIdTrabajadores && isset($this->app->empresa_id)) {
                    $vals['eid'] = $this->app->empresa_id;
                }
                $result = $this->db->fetchAll($sqlBase, $vals);

                if (empty($result)) {
                    continue;
                }

                $altoBloque = $H_AREA + $H_CAB + (count($result) * $H_DATO) + $H_TOT;

                if (!$primerBloque) {
                    if (($usadoPag + $H_SEP + $altoBloque) > $ALTO_PAGINA) {
                        $sheet->setBreak('A' . ($rowNum - 1), PHPExcel_Worksheet::BREAK_ROW);
                        $usadoPag = 0.0;
                    } else {
                        $sheet->getRowDimension($rowNum)->setRowHeight($H_SEP);
                        $usadoPag += $H_SEP;
                        $rowNum++;
                    }
                }
                $primerBloque = false;

                $sheet->setCellValue('A' . $rowNum, 'Área: ' . $dept['nombre']);
                $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
                $sheet->setCellValue('H' . $rowNum, 'Elaborado Por: ' . $nombreUsuario);
                $sheet->getStyle('H' . $rowNum)->getFont()->setBold(true);
                $sheet->mergeCells('H' . $rowNum . ':O' . $rowNum);
                $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($rowNum)->setRowHeight($H_AREA);
                $rowNum++;

                $headerRow = $rowNum;
                foreach ($headers as $col => $title) {
                    $sheet->setCellValue($col . $headerRow, $title);
                    $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
                }
                $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
                $sheet->getStyle('A' . $headerRow . ':O' . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($headerRow)->setRowHeight($H_CAB);
                $rowNum++;

                $usadoPag += $altoBloque;

                $deptTotales = array_fill_keys($colsTotales, 0.0);
                $filasDelDept = 0;

                foreach ($result as $row) {
                    $tid = intval($row['expediente']);
                    if (isset($ovMap[$tid]) && is_array($ovMap[$tid])) {
                        $o = $ovMap[$tid];
                        if (isset($o['horas'])) { $row['horas'] = $o['horas']; }
                        if (isset($o['tarifa'])) { $row['tarifa'] = $o['tarifa']; }
                        if (isset($o['bonif'])) { $row['bonif'] = $o['bonif']; }
                        if (isset($o['ausencias'])) { $row['ausencias'] = $o['ausencias']; }
                        if (isset($o['vacaciones'])) { $row['vacaciones'] = $o['vacaciones']; }
                        if (isset($o['pago_vac'])) { $row['pago_vac'] = $o['pago_vac']; }
                    }

                    $sheet->setCellValue('A' . $rowNum, $row['nombre']);

                    $ci = trim((string) $row['ci']);
                    if ($ci !== '' && ctype_digit($ci) && strlen($ci) < 11) {
                        $ci = str_pad($ci, 11, '0', STR_PAD_LEFT);
                    }
                    $sheet->setCellValueExplicit('B' . $rowNum, $ci, PHPExcel_Cell_DataType::TYPE_STRING);

                    $sheet->setCellValue('C' . $rowNum, floatval($row['horas']));
                    $sheet->setCellValue('D' . $rowNum, floatval($row['tarifa']));
                    $sheet->setCellValue('E' . $rowNum, floatval($row['a_cobrar']));
                    $sheet->setCellValue('F' . $rowNum, floatval($row['bonif']));
                    $sheet->setCellValue('G' . $rowNum, floatval($row['sal_dev']));
                    $sheet->setCellValue('H' . $rowNum, floatval($row['vacaciones']));
                    $sheet->setCellValue('I' . $rowNum, floatval($row['pago_vac']));
                    $sheet->setCellValue('J' . $rowNum, floatval($row['salario_neto']));
                    $sheet->setCellValue('K' . $rowNum, floatval($row['seg_social']));
                    $sheet->setCellValue('L' . $rowNum, floatval($row['ing_pers_3']));
                    $sheet->setCellValue('M' . $rowNum, floatval($row['ing_pers_5']));
                    $sheet->setCellValue('N' . $rowNum, floatval($row['salario_pagar']));
                    // O = Firma: se deja en blanco a propósito, es el espacio donde firma el trabajador.

                    $this->_formato_fila_nomina($sheet, $rowNum);
                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getBorders()->getAllBorders()
                        ->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
                    $sheet->getRowDimension($rowNum)->setRowHeight($H_DATO);

                    foreach ($colsTotales as $col) {
                        $deptTotales[$col] += floatval($row[$campoDeCol[$col]]);
                    }
                    $filasDelDept++;

                    $rowNum++;
                }

                if ($filasDelDept > 0) {
                    $sheet->setCellValue('A' . $rowNum, 'TOTAL ' . $this->_mayus($dept['nombre'])
                        . ' (' . $filasDelDept . ' ' . ($filasDelDept == 1 ? 'trabajador' : 'trabajadores') . ')');
                    $sheet->mergeCells('A' . $rowNum . ':D' . $rowNum);
                    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    foreach ($colsTotales as $col) {
                        $sheet->setCellValue($col . $rowNum, $deptTotales[$col]);
                        $granTotales[$col] += $deptTotales[$col];
                    }
                    $granTrabajadores += $filasDelDept;

                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
                    $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getBorders()->getTop()
                        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $this->_formato_fila_nomina($sheet, $rowNum);
                    $sheet->getRowDimension($rowNum)->setRowHeight($H_TOT);

                    $huboAlgunaFila = true;
                    $rowNum++;
                }

                while ($usadoPag > $ALTO_PAGINA) {
                    $usadoPag -= $ALTO_PAGINA;
                }
            }

            if ($huboAlgunaFila) {
                if (($usadoPag + $H_SEP + $H_TOT) > $ALTO_PAGINA) {
                    $sheet->setBreak('A' . ($rowNum - 1), PHPExcel_Worksheet::BREAK_ROW);
                } else {
                    $sheet->getRowDimension($rowNum)->setRowHeight($H_SEP);
                    $rowNum++;
                }

                $sheet->setCellValue('A' . $rowNum, 'TOTAL GENERAL ' . $this->_mayus($nombreEmpresa)
                    . ' (' . $granTrabajadores . ' ' . ($granTrabajadores == 1 ? 'trabajador' : 'trabajadores') . ')');
                $sheet->mergeCells('A' . $rowNum . ':D' . $rowNum);
                $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($rowNum)->setRowHeight($H_TOT);

                foreach ($colsTotales as $col) {
                    $sheet->setCellValue($col . $rowNum, $granTotales[$col]);
                }

                $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFont()->setBold(true)
                    ->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE));
                $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
                $sheet->getStyle('A' . $rowNum . ':O' . $rowNum)->getBorders()->getTop()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->_formato_fila_nomina($sheet, $rowNum);
            }

            $anchos = [
                'A' => 28,  // Nombre y Apellido
                'B' => 13,  // C.I
                'C' => 6,   // Horas
                'D' => 8,   // Tarifa
                'E' => 10,  // A Cobrar
                'F' => 8,   // Bonif
                'G' => 10,  // Sal. Dev
                'H' => 9,   // Vacaciones
                'I' => 10,  // Pago Vac
                'J' => 10,  // Sal. Neto
                'K' => 10,  // Seg Social
                'L' => 10,  // Ing Pers 3%
                'M' => 10,  // Ing Pers 5%
                'N' => 11,  // Sal. a Pagar
                'O' => 18,  // Firma
            ];
            foreach ($anchos as $col => $ancho) {
                $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($ancho);
            }

            $sheet->getPageSetup()->setPrintArea('A1:O' . max(1, $rowNum));

            // Generar y transmitir. A propósito NO se guarda en export_prenomina ni se llama a
            // _update_submayor_vacaciones_from_prenomina: ver el docblock de esta función.
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            ob_start();
            $writer->save('php://output');
            $excelBinary = ob_get_clean();

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Nomina_' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx"');
            header('Content-Length: ' . strlen($excelBinary));
            echo $excelBinary;
            exit;

        } catch (Exception $e) {
            @error_log('ERROR en _export_nomina2_binary: ' . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'error' => $e->getMessage()]);
        }
    }

    /**
     * _update_submayor_vacaciones_from_prenomina - Actualiza submayor_vacaciones después de exportar prenómina
     * Resta los días y pago de vacaciones utilizados en el período.
     * NOTA: El devengo mensual (+2.1816 días) YA NO se hace aquí. Es responsabilidad exclusiva
     * del cron cron_devengo_vacaciones.php, igual que para trabajadores.vacaciones_acc, para
     * evitar doble devengo (esta función no tenía candado de idempotencia).
     */
    private function _update_submayor_vacaciones_from_prenomina($year, $month) {
        try {
            @error_log('=== STARTING submayor_vacaciones UPDATE for ' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . ' ===');
            
            // Obtener todos los trabajadores con datos de prenómina para este mes
            $sql = "SELECT 
                        p.trabajador_id,
                        COALESCE(p.vacaciones, 0) AS vac_dias,
                        COALESCE(p.pago_vac, 0) AS pago_vac
                    FROM prenomina p
                    WHERE p.year = :year AND p.month = :month
                    ORDER BY p.trabajador_id ASC";
            
            $trabajadores_prenomina = $this->db->fetchAll($sql, ['year' => $year, 'month' => $month]);
            
            $count_actualizado = 0;
            
            // Procesar cada trabajador
            foreach ($trabajadores_prenomina as $row) {
                $trabajador_id = intval($row['trabajador_id']);
                $vac_dias = floatval($row['vac_dias']);
                $pago_vac = floatval($row['pago_vac']);
                
                try {
                    // Obtener registro actual de submayor_vacaciones si existe
                    $current = $this->db->fetchRow(
                        "SELECT vacaciones, pago_vacaciones FROM submayor_vacaciones WHERE id_trabajador = :tid LIMIT 1",
                        ['tid' => $trabajador_id]
                    );
                    
                    // Calcular nuevos valores (sin devengo mensual: eso lo hace el cron)
                    $new_vacaciones = 0;
                    $new_pago_vacaciones = null;

                    if ($current) {
                        // Si existe registro, actualizar
                        $new_vacaciones = floatval($current['vacaciones'] ?? 0);

                        // Restar días de vacaciones si vac_dias > 0
                        if ($vac_dias > 0) {
                            $new_vacaciones -= $vac_dias;
                        }
                        
                        // Restar pago de vacaciones si pago_vac > 0
                        if ($pago_vac > 0) {
                            $current_pago = floatval(!empty($current['pago_vacaciones']) ? $current['pago_vacaciones'] : 0);
                            $new_pago_vacaciones = $current_pago - $pago_vac;
                        } else {
                            $new_pago_vacaciones = $current['pago_vacaciones']; // Mantender valor actual si no hay descuento
                        }
                        
                        // Actualizar registro existente
                        $this->db->update('submayor_vacaciones', 
                            [
                                'vacaciones' => $new_vacaciones,
                                'pago_vacaciones' => $new_pago_vacaciones
                            ],
                            ['id_trabajador' => $trabajador_id]
                        );
                        
                        @error_log("submayor_vacaciones UPDATE: tid={$trabajador_id}, vac_dias={$vac_dias}, pago_vac={$pago_vac}, new_vac={$new_vacaciones}");
                        
                    } else {
                        // Si no existe, crear registro
                        // Restar días si son > 0
                        if ($vac_dias > 0) {
                            $new_vacaciones -= $vac_dias;
                        }
                        
                        // Pago de vacaciones solo si pago_vac > 0
                        if ($pago_vac > 0) {
                            $new_pago_vacaciones = -$pago_vac;
                        }
                        
                        $this->db->insert('submayor_vacaciones', [
                            'id_trabajador' => $trabajador_id,
                            'vacaciones' => $new_vacaciones,
                            'pago_vacaciones' => $new_pago_vacaciones
                        ]);
                        
                        @error_log("submayor_vacaciones INSERT: tid={$trabajador_id}, vac_dias={$vac_dias}, pago_vac={$pago_vac}, new_vac={$new_vacaciones}");
                    }
                    
                    $count_actualizado++;
                    
                } catch (Exception $e) {
                    @error_log("Error actualizando submayor_vacaciones para trabajador {$trabajador_id}: " . $e->getMessage());
                    continue;
                }
            }
            
            @error_log("=== FINISH submayor_vacaciones UPDATE: {$count_actualizado} trabajadores actualizados ===");
            return true;
            
        } catch (Exception $e) {
            @error_log("ERROR en _update_submayor_vacaciones_from_prenomina: " . $e->getMessage());
            return false;
        }
    }

    /**
     * _list_export_prenomina - Lista todos los meses disponibles en la tabla prenomina
     * NOTA: Cambiado para mostrar TODOS los meses con registros, no solo los exportados
     */

    private function _list_export_prenomina() {
        try {
            // Obtener todos los meses únicos de la tabla prenomina FILTRADO POR EMPRESA
            $sql = "SELECT DISTINCT 
                        CONCAT(year, '-', LPAD(month, 2, '0')) AS mes,
                        year,
                        month,
                        COUNT(DISTINCT trabajador_id) AS total_trabajadores
                    FROM prenomina
                    WHERE empresa_id = :empresa_id
                    GROUP BY year, month
                    ORDER BY year DESC, month DESC";
            $result = $this->db->fetchAll($sql, ['empresa_id' => $this->app->empresa_id]);
            
            // Agregar un ID temporal para compatibilidad con el frontend
            $output = [];
            foreach ($result as $idx => $item) {
                $output[] = [
                    'id' => $idx + 1, // ID temporal para compatibilidad
                    'mes' => $item['mes'],
                    'year' => $item['year'],
                    'month' => $item['month'],
                    'total_trabajadores' => $item['total_trabajadores']
                ];
            }
            
            return $output ?: [];
        } catch (Exception $e) {
            @error_log('Error en _list_export_prenomina: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * _download_export_prenomina - Descarga un Excel guardado por ID
     */
    private function _download_export_prenomina($param) {
        try {
            $id = isset($param['id']) ? intval($param['id']) : 0;
            
            if ($id <= 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'ID inválido']);
                return;
            }
            
            $row = $this->db->fetchRow(
                "SELECT export, mes FROM export_prenomina WHERE id = :id",
                ['id' => $id]
            );
            
            if (!$row || empty($row['export'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 0, 'error' => 'Excel no encontrado']);
                return;
            }
            
            // Extraer año-mes de la fecha
            $mesDate = $row['mes'];
            $mesStr = date('Y-m', strtotime($mesDate));
            
            // Enviar archivo
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Prenomina_' . $mesStr . '.xlsx"');
            header('Content-Length: ' . strlen($row['export']));
            echo $row['export'];
            exit;
            
        } catch (Exception $e) {
            @error_log('Error en _download_export_prenomina: ' . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 0, 'error' => $e->getMessage()]);
        }
    }
}
