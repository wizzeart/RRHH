<?php

class SubmayorVacaciones {

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
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
                
            case 'save':
                $data = $this->_save($param);
                print(json_encode($data));
                break;
                
            case 'list-trabajadores':
                $data = $this->_list_trabajadores($param);
                print(json_encode($data));
                break;
                
            case 'sumar-vacaciones':
                $data = $this->_sumar_vacaciones($param);
                print(json_encode($data));
                break;
                
            case 'estadisticas':
                $data = $this->_get_estadisticas($param);
                print(json_encode($data));
                break;

            case 'calendario_eventos':
                $data = $this->_get_calendario_eventos($param);
                print(json_encode($data));
                break;
                
            case 'obtener_eventos_personalizados':
                $data = $this->_obtener_eventos_personalizados($param);
                print(json_encode($data));
                break;
                
            case 'export-pdf':
                $this->_export_pdf($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-submayor-vacaciones':
                $data = array();
                $page['title'] = 'Contabilidad';
                $page['subtitle'] = 'Submayor de Vacaciones';
                $data_form = array();
                break;

            case 'submayor-vacaciones':
                $data = array();
                $page['title'] = 'Contabilidad';
                $page['subtitle'] = 'Submayor de Vacaciones';
                $data_form = array();
                $action = 'insert';
                break;
        }
    }

    private function _list($param) {
        $where = [];
        $vals = [];
        
        // Búsqueda por nombre, apellidos o CI
        if (isset($param['search']) && trim($param['search']) !== '') {
            $where[] = '(t.nombre LIKE :search OR t.apellidos LIKE :search OR t.apellidos_segundos LIKE :search OR t.carnet_identidad LIKE :search)';
            $vals['search'] = '%' . $param['search'] . '%';
        }

        $cond = '';
        if (!empty($where)) {
            $cond = ' AND ' . implode(' AND ', $where);
        }


        $sql = "SELECT 
                    t.id,
                    t.foto,
                    CONCAT(t.nombre, ' ', t.apellidos, ' ', COALESCE(t.apellidos_segundos, '')) as nombre_completo,
                    t.carnet_identidad,
                    t.cargos_id,
                    COALESCE(c.nombre, 'Sin cargo') as cargo_nombre,
                    COALESCE(c.salario, 0) as cargo_salario,
                    COALESCE(t.vacaciones_acc, 0) as vacaciones_disponibles,
                    COALESCE(t.salario_acc, 0) as salario_acumulado
                FROM trabajadores t
                LEFT JOIN cargos c ON t.cargos_id = c.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                WHERE t.trabajador_eliminado = '0'" . $cond .
                " AND (t.empresa_id = :empresa_id )
                ORDER BY t.apellidos, t.nombre";

        $vals['empresa_id'] = $this->app->empresa_id;
        $data = $this->db->fetchAll($sql, $vals);
        
        // Procesar fotos a base64
        foreach ($data as &$row) {
            if (!empty($row['foto'])) {
                $fotoBase64 = base64_encode($row['foto']);
                $row['foto'] = 'data:image/jpeg;base64,' . $fotoBase64;
            } else {
                $row['foto'] = null;
            }
        }
        
        return $data;
    }

    private function _save($param) {
        try {
            // Validar parámetros requeridos
            if (empty($param['trabajador_id'])) {
                return array(
                    'status' => 0,
                    'msg' => 'Falta el parámetro requerido: trabajador_id'
                );
            }

            $trabajador_id = intval($param['trabajador_id']);
            
            // Verificar si se está editando vacaciones o salario_acumulado
            if (isset($param['salario_acumulado'])) {
                // Editar salario_acumulado directamente (sin cálculo automático)
                // SIEMPRE se guarda en trabajadores.salario_acc, nunca en submayor_vacaciones
                $salario_acumulado = floatval($param['salario_acumulado']);
                
                error_log("Editando salario_acumulado para trabajador_id: $trabajador_id, nuevo valor: $salario_acumulado");
                
                // Guardar en tabla trabajadores (columna salario_acc)
                $update_trabajador = array('salario_acc' => number_format($salario_acumulado, 2, '.', ''));
                $where_trabajador = array('id' => $trabajador_id);
                $this->db->update('trabajadores', $update_trabajador, $where_trabajador);
                
                error_log("Salario acumulado guardado en DB (trabajadores.salario_acc). Trabajador: $trabajador_id, Valor: $salario_acumulado");
                
                return array(
                    'status' => 1,
                    'msg' => 'Salario acumulado actualizado correctamente en tabla trabajadores',
                    'salario_calculado' => $salario_acumulado
                );
            } elseif (isset($param['vacaciones'])) {
                // Editar vacaciones (código original)
                if (!isset($param['vacaciones'])) {
                    return array(
                        'status' => 0,
                        'msg' => 'Faltan parámetros requeridos: trabajador_id, vacaciones'
                    );
                }

                $vacaciones_nuevas = floatval($param['vacaciones']);
                
                // Obtener el salario del cargo del trabajador
                $sql_cargo = "SELECT c.salario 
                             FROM trabajadores t 
                             LEFT JOIN cargos c ON t.cargos_id = c.id 
                             WHERE t.id = :trabajador_id";
                $cargo_data = $this->db->fetchRow($sql_cargo, array('trabajador_id' => $trabajador_id));
                
                if (!$cargo_data) {
                    return array(
                        'status' => 0,
                        'msg' => 'No se encontró el trabajador o no tiene cargo asignado'
                    );
                }
                
                $salario_cargo = floatval($cargo_data['salario'] ?? 0);
                
                // Calcular salario acumulado automáticamente
                $pago_vacaciones = round($vacaciones_nuevas * round($salario_cargo/24, 2), 0);
                
                // Si se envía pago_vacaciones manualmente, usarlo en su lugar
                if (isset($param['pago_vacaciones']) && !empty($param['pago_vacaciones'])) {
                    $pago_vacaciones = round(floatval($param['pago_vacaciones']), 0);
                }

                // Verificar si ya existe un registro para este trabajador
                $sql_check = "SELECT id_trabajador, vacaciones FROM submayor_vacaciones WHERE id_trabajador = :trabajador_id";
                $existing = $this->db->fetchRow($sql_check, array('trabajador_id' => $trabajador_id));

                // Obtener vacaciones_acc actual del trabajador
                $sql_vac_actual = "SELECT vacaciones_acc FROM trabajadores WHERE id = :trabajador_id";
                $vac_actual = $this->db->fetchRow($sql_vac_actual, array('trabajador_id' => $trabajador_id));
                $vac_disponibles_actual = floatval($vac_actual['vacaciones_acc'] ?? 0);

                if ($existing) {
                    // Actualizar registro existente
                    $vacaciones_anteriores = floatval($existing['vacaciones']);
                    
                    // Calcular la diferencia de días
                    $diferencia = $vacaciones_nuevas;
                    
                    // Actualizar días en submayor_vacaciones
                    $update_data = array(
                        'vacaciones' => $vacaciones_nuevas,
                        'pago_vacaciones' => number_format($pago_vacaciones, 2, '.', '')
                    );
                    $where = array('id_trabajador' => $trabajador_id);
                    
                    $this->db->update('submayor_vacaciones', $update_data, $where);
                    
                    // Actualizar vacaciones_acc en trabajadores (suma/resta la diferencia)
                    $nueva_suma = round($diferencia, 2);
                    if ($nueva_suma < 0) {
                        $nueva_suma = 0; // No permitir valores negativos
                    }
                    
                    $update_trabajador = array('vacaciones_acc' => $nueva_suma);
                    $where_trabajador = array('id' => $trabajador_id);
                    $this->db->update('trabajadores', $update_trabajador, $where_trabajador);
                    
                    return array(
                        'status' => 1,
                        'msg' => 'Registro actualizado y vacaciones disponibles ajustadas',
                        'salario_calculado' => $pago_vacaciones,
                        'salario_cargo' => $salario_cargo,
                        'suma_automatica' => array(
                            'vac_anteriores' => $vac_disponibles_actual,
                            'dias_anteriores_submayor' => $vacaciones_anteriores,
                            'dias_nuevos' => $vacaciones_nuevas,
                            'diferencia_dias' => $diferencia,
                            'nueva_suma' => $nueva_suma
                        )
                    );
                } else {
                    // Insertar nuevo registro
                    $insert_data = array(
                        'id_trabajador' => $trabajador_id,
                        'vacaciones' => $vacaciones_nuevas,
                        'pago_vacaciones' => number_format($pago_vacaciones, 2, '.', '')
                    );
                    
                    $this->db->insert('submayor_vacaciones', $insert_data);
                    
                    // SUMA AUTOMÁTICA: Actualizar vacaciones_acc en tabla trabajadores
                    $nueva_suma = round($vacaciones_nuevas, 2);
                    
                    // Actualizar vacaciones_acc en trabajadores
                    $update_trabajador = array('vacaciones_acc' => $nueva_suma);
                    $where_trabajador = array('id' => $trabajador_id);
                    $this->db->update('trabajadores', $update_trabajador, $where_trabajador);
                    
                    return array(
                        'status' => 1,
                        'msg' => 'Registro guardado y vacaciones actualizadas automáticamente',
                        'salario_calculado' => $pago_vacaciones,
                        'salario_cargo' => $salario_cargo,
                        'suma_automatica' => array(
                            'vac_anteriores' => $vac_disponibles_actual,
                            'dias_agregados' => $vacaciones_nuevas,
                            'nueva_suma' => $nueva_suma
                        )
                    );
                }
            } else {
                return array(
                    'status' => 0,
                    'msg' => 'Debe enviar vacaciones o salario_acumulado para editar'
                );
            }

        } catch (Exception $e) {
            error_log("Error en _save SubmayorVacaciones: " . $e->getMessage());
            return array(
                'status' => 0,
                'msg' => 'Error al guardar el registro: ' . $e->getMessage()
            );
        }
    }

    private function _sumar_vacaciones($param) {
        try {
            // Validar parámetros requeridos
            if (empty($param['trabajador_id'])) {
                return array(
                    'status' => 0,
                    'msg' => 'Falta el parámetro trabajador_id'
                );
            }

            $trabajador_id = intval($param['trabajador_id']);
            
            // Obtener datos actuales del trabajador y submayor
            $sql = "SELECT 
                        t.id,
                        t.vacaciones_acc,
                        CONCAT(t.nombre, ' ', t.apellidos) as nombre_completo,
                        COALESCE(sv.vacaciones, 0) as dias_vacaciones_acum
                    FROM trabajadores t
                    LEFT JOIN submayor_vacaciones sv ON t.id = sv.id_trabajador
                    WHERE t.id = :trabajador_id AND t.trabajador_eliminado = '0'";
            
            $trabajador = $this->db->fetchRow($sql, array('trabajador_id' => $trabajador_id));
            
            if (!$trabajador) {
                return array(
                    'status' => 0,
                    'msg' => 'Trabajador no encontrado'
                );
            }
            
            // Calcular la suma
            $vac_disponibles = floatval($trabajador['vacaciones_acc'] ?? 0);
            $dias_acum = floatval($trabajador['dias_vacaciones_acum'] ?? 0);
            $nueva_suma = round($vac_disponibles + $dias_acum, 2);
            
            // Actualizar vacaciones_acc en tabla trabajadores
            $update_data = array('vacaciones_acc' => $nueva_suma);
            $where = array('id' => $trabajador_id);
            
            $this->db->update('trabajadores', $update_data, $where);
            
            return array(
                'status' => 1,
                'msg' => 'Vacaciones actualizadas correctamente',
                'trabajador' => $trabajador['nombre_completo'],
                'vac_disponibles_anterior' => $vac_disponibles,
                'dias_acum' => $dias_acum,
                'nueva_suma' => $nueva_suma,
                'calculo' => "$vac_disponibles + $dias_acum = $nueva_suma"
            );
            
        } catch (Exception $e) {
            error_log("Error en _sumar_vacaciones SubmayorVacaciones: " . $e->getMessage());
            return array(
                'status' => 0,
                'msg' => 'Error al sumar vacaciones: ' . $e->getMessage()
            );
        }
    }

    private function _export_pdf($param) {
        try {
            error_log("Iniciando generación de PDF Submayor Vacaciones");
            
            // Obtener todos los datos del submayor de vacaciones
            $data = $this->_list($param);
            error_log("Datos obtenidos: " . count($data) . " registros");
            
            if (empty($data)) {
                error_log("No hay datos para exportar");
                header('Content-Type: text/html');
                echo '<script>alert("No hay datos para exportar"); window.close();</script>';
                return;
            }
            
            // Generar HTML para el PDF
            error_log("Generando HTML para PDF");
            $html = $this->_generate_pdf_html($data);
            error_log("HTML generado, longitud: " . strlen($html));
            
            // Usar TCPDF en lugar de mPDF (más simple y sin dependencias)
            if (!file_exists('plugins/tcpdf/tcpdf.php')) {
                header('Content-Type: text/html');
                echo '<script>alert("Error: TCPDF no encontrado"); window.close();</script>';
                return;
            }
            
            error_log("Cargando TCPDF");
            require_once('plugins/tcpdf/tcpdf.php');
            
            // Crear instancia de TCPDF
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
            
            // Configurar propiedades del PDF
            $pdf->SetCreator('Sistema de Gestión');
            $pdf->SetAuthor('Sistema de Gestión');
            $pdf->SetTitle('Submayor de Vacaciones - ' . date('Y-m-d'));
            $pdf->SetSubject('Reporte de Submayor de Vacaciones');
            
            // Configurar márgenes
            $pdf->SetMargins(15, 20, 15);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);
            
            // Agregar página
            $pdf->AddPage();
            
            // Configurar fuente
            $pdf->SetFont('helvetica', '', 10);
            
            // Generar HTML simplificado para TCPDF
            $html_tcpdf = $this->_generate_tcpdf_html($data);
            
            error_log("Escribiendo HTML a TCPDF");
            $pdf->writeHTML($html_tcpdf, true, false, true, false, '');
            
            // Generar nombre del archivo
            $filename = 'Submayor_Vacaciones_' . date('Y-m-d_H-i-s') . '.pdf';
            error_log("Nombre del archivo: " . $filename);
            
            // Limpiar cualquier output previo
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Enviar PDF al navegador
            error_log("Enviando PDF al navegador");
            $pdf->Output($filename, 'D'); // 'D' = Download
            error_log("PDF enviado correctamente");
            exit;
            
        } catch (Exception $e) {
            error_log("Error en _export_pdf SubmayorVacaciones: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            header('Content-Type: text/html');
            echo '<script>';
            echo 'console.error("Error PDF: ' . addslashes($e->getMessage()) . '");';
            echo 'alert("Error al generar PDF: ' . addslashes($e->getMessage()) . '");';
            echo '</script>';
            echo '<h3>Error al generar PDF:</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
    }

    private function _generate_pdf_html($data) {
        $fecha_actual = date('d/m/Y H:i:s');
        $total_trabajadores = count($data);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 10px; 
                    margin: 0; 
                    padding: 0; 
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 20px; 
                    border-bottom: 2px solid #333; 
                    padding-bottom: 10px; 
                }
                .header h1 { 
                    font-size: 18px; 
                    margin: 0; 
                    color: #333; 
                }
                .header h2 { 
                    font-size: 14px; 
                    margin: 5px 0; 
                    color: #666; 
                }
                .info-box { 
                    background-color: #f8f9fa; 
                    padding: 10px; 
                    margin-bottom: 15px; 
                    border: 1px solid #dee2e6; 
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-bottom: 20px; 
                }
                th, td { 
                    border: 1px solid #333; 
                    padding: 6px; 
                    text-align: left; 
                }
                th { 
                    background-color: #007bff; 
                    color: white; 
                    font-weight: bold; 
                    text-align: center; 
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .footer { 
                    margin-top: 20px; 
                    text-align: center; 
                    font-size: 9px; 
                    color: #666; 
                    border-top: 1px solid #ccc; 
                    padding-top: 10px; 
                }
                .total-row { 
                    background-color: #e9ecef; 
                    font-weight: bold; 
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SUBMAYOR DE VACACIONES</h1>
                <h2>Reporte de Trabajadores - ' . $fecha_actual . '</h2>
            </div>
            
            <div class="info-box">
                <strong>Total de Trabajadores:</strong> ' . $total_trabajadores . ' | 
                <strong>Fecha de Generación:</strong> ' . $fecha_actual . '
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">Nombre y Apellidos</th>
                        <th style="width: 12%;">CI</th>
                        <th style="width: 18%;">Cargo</th>
                        <th style="width: 20%;">Salario/Mensual</th>
                        <th style="width: 15%;">Vac. Disponibles</th>
                        <th style="width: 15%;">Salario Acumulado</th>
                    </tr>
                </thead>
                <tbody>';
        
        $total_vac_disponibles = 0;
        $total_salario_acum = 0;
        
        foreach ($data as $row) {
            $vac_disponibles = floatval($row['vacaciones_disponibles'] ?? 0);
            $salario_acum = floatval($row['salario_acumulado'] ?? 0);

            $total_vac_disponibles += $vac_disponibles;
            $total_salario_acum += $salario_acum;

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($row['nombre_completo']) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['carnet_identidad']) . '</td>
                    <td>' . htmlspecialchars($row['cargo_nombre'] ?? 'Sin cargo') . '</td>
                    <td class="text-right">$' . number_format(floatval($row['cargo_salario'] ?? 0), 2) . '</td>
                    <td class="text-center">' . number_format($vac_disponibles, 2) . '</td>
                    <td class="text-right">$' . number_format($salario_acum, 2) . '</td>
                </tr>';
        }
        
        // Fila de totales
        $html .= '
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>TOTALES:</strong></td>
                    <td class="text-center"><strong>' . number_format($total_vac_disponibles, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($total_salario_acum, 2) . '</strong></td>
                </tr>';
        
        $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                <p>Reporte generado el ' . $fecha_actual . ' | Sistema de Gestión de Recursos Humanos</p>
                <p>Total de registros: ' . $total_trabajadores . ' trabajadores</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    private function _generate_tcpdf_html($data) {
        $fecha_actual = date('d/m/Y H:i:s');
        $total_trabajadores = count($data);
        
        // HTML simplificado para TCPDF
        $html = '<style>
            h1 { text-align: center; color: #333; font-size: 16px; margin-bottom: 20px; }
            h2 { text-align: center; color: #666; font-size: 12px; margin-bottom: 15px; }
            .info-box { background-color: #f8f9fa; padding: 8px; margin-bottom: 10px; border: 1px solid #dee2e6; font-size: 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
            th { background-color: #007bff; color: white; font-weight: bold; text-align: center; padding: 5px; font-size: 9px; border: 1px solid #333; }
            td { padding: 4px; text-align: left; font-size: 8px; border: 1px solid #333; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .total-row { background-color: #e9ecef; font-weight: bold; }
        </style>';
        
        $html .= '<h1>SUBMAYOR DE VACACIONES</h1>';
        $html .= '<h2>Reporte de Trabajadores - ' . $fecha_actual . '</h2>';
        
        $html .= '<div class="info-box">';
        $html .= '<strong>Total de Trabajadores:</strong> ' . $total_trabajadores . ' | ';
        $html .= '<strong>Fecha de Generación:</strong> ' . $fecha_actual;
        $html .= '</div>';
        
        // Usar table-layout fixed y colgroup con anchos en porcentajes para
        // alinear las columnas del PDF con las cabeceras de la tabla en la UI
        $html .= '<table style="table-layout: fixed; width:100%;">';
        // Definir columnas según los anchos solicitados (porcentajes)
        $html .= '<colgroup>';
        $html .= '<col style="width:20%">'; // Nombre y Apellidos
        $html .= '<col style="width:12%">'; // CI
        $html .= '<col style="width:18%">'; // Cargo
        $html .= '<col style="width:20%">'; // Salario/Mensual
        $html .= '<col style="width:15%">'; // Vac. Disponibles
        $html .= '<col style="width:15%">'; // Salario Acumulado
        $html .= '</colgroup>';

        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Nombre y Apellidos</th>';
        $html .= '<th>CI</th>';
        $html .= '<th>Cargo</th>';
        $html .= '<th>Salario/Mensual</th>';
        $html .= '<th>Vac. Disponibles</th>';
        $html .= '<th>Salario Acumulado</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        $total_vac_disponibles = 0;
        $total_salario_acum = 0;
        
        foreach ($data as $row) {
            $vac_disponibles = floatval($row['vacaciones_disponibles'] ?? 0);
            $salario_acum = floatval($row['salario_acumulado'] ?? 0);

            $total_vac_disponibles += $vac_disponibles;
            $total_salario_acum += $salario_acum;

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['nombre_completo']) . '</td>';
            $html .= '<td class="text-center">' . htmlspecialchars($row['carnet_identidad']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['cargo_nombre'] ?? 'Sin cargo') . '</td>';
            $html .= '<td class="text-right">$' . number_format(floatval($row['cargo_salario'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-center">' . number_format($vac_disponibles, 2) . '</td>';
            $html .= '<td class="text-right">$' . number_format($salario_acum, 2) . '</td>';
            $html .= '</tr>';
        }
        
        // Fila de totales
        $html .= '<tr class="total-row">';
        $html .= '<td colspan="4" class="text-right"><strong>TOTALES:</strong></td>';
        $html .= '<td class="text-center"><strong>' . number_format($total_vac_disponibles, 2) . '</strong></td>';
        $html .= '<td class="text-right"><strong>$' . number_format($total_salario_acum, 2) . '</strong></td>';
        $html .= '</tr>';
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        $html .= '<div style="margin-top: 15px; text-align: center; font-size: 8px; color: #666;">';
        $html .= 'Reporte generado el ' . $fecha_actual . ' | Sistema de Gestión de Recursos Humanos<br>';
        $html .= 'Total de registros: ' . $total_trabajadores . ' trabajadores';
        $html .= '</div>';
        
        return $html;
    }

    private function _get_estadisticas($param) {
        try {
            $empresa_id = $this->app->empresa_id;
            
            // Obtener datos generales con LEFT JOIN correcto
            $sql_total = "SELECT 
                            COUNT(DISTINCT t.id) as total_trabajadores,
                            COUNT(DISTINCT CASE WHEN sv.id_trabajador IS NOT NULL THEN t.id END) as con_vacaciones_registradas,
                            ROUND(AVG(COALESCE(t.vacaciones_acc, 0)), 2) as promedio_vac_disponibles,
                            ROUND(SUM(COALESCE(t.vacaciones_acc, 0)), 2) as total_vac_disponibles,
                            ROUND(SUM(COALESCE(sv.pago_vacaciones, 0)), 2) as total_salario_acumulado
                        FROM trabajadores t
                        LEFT JOIN departamentos d ON t.departamento_id = d.id
                        LEFT JOIN submayor_vacaciones sv ON t.id = sv.id_trabajador
                        WHERE t.trabajador_eliminado = '0' 
                        AND (t.empresa_id = ? OR t.empresa_id IS NULL OR t.departamento_id IS NULL)";
            
            $totales = $this->db->fetchRow(str_replace('?', ':empresa_id', $sql_total), array('empresa_id' => $empresa_id));
            
            if (!$totales) {
                $totales = array(
                    'total_trabajadores' => 0,
                    'con_vacaciones_registradas' => 0,
                    'promedio_vac_disponibles' => 0,
                    'total_vac_disponibles' => 0,
                    'total_salario_acumulado' => 0
                );
            }

            // Obtener top 5 trabajadores con más vacaciones
            $sql_top = "SELECT 
                            t.id,
                            CONCAT(t.nombre, ' ', t.apellidos, ' ', COALESCE(t.apellidos_segundos, '')) as nombre_completo,
                            t.carnet_identidad,
                            COALESCE(t.vacaciones_acc, 0) as vacaciones_acc,
                            COALESCE(c.nombre, 'Sin cargo') as cargo_nombre,
                            COALESCE(sv.pago_vacaciones, 0) as pago_vacaciones
                        FROM trabajadores t
                        LEFT JOIN departamentos d ON t.departamento_id = d.id
                        LEFT JOIN submayor_vacaciones sv ON t.id = sv.id_trabajador
                        LEFT JOIN cargos c ON t.cargos_id = c.id
                        WHERE t.trabajador_eliminado = '0' 
                        AND (t.empresa_id = :empresa_id )
                        ORDER BY t.vacaciones_acc DESC
                        LIMIT 5";
            
            $top_trabajadores = $this->db->fetchAll($sql_top, array('empresa_id' => $empresa_id));
            if (!$top_trabajadores) {
                $top_trabajadores = array();
            }

            return array(
                'status' => 1,
                'totales' => $totales,
                'top_trabajadores' => $top_trabajadores
            );

        } catch (Exception $e) {
            error_log("Error en _get_estadisticas SubmayorVacaciones: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return array(
                'status' => 0,
                'msg' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'totales' => array(
                    'total_trabajadores' => 0,
                    'con_vacaciones_registradas' => 0,
                    'promedio_vac_disponibles' => 0,
                    'total_vac_disponibles' => 0,
                    'total_salario_acumulado' => 0
                ),
                'top_trabajadores' => array()
            );
        }
    }

    private function _get_calendario_eventos($param) {
        try {
            date_default_timezone_set('America/Havana');
            $anno = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
            $eventos = [];
            $db = new DWclass();

            // ===== VACACIONES DESDE registro_vacaciones =====
            $sql_vacaciones = "SELECT 
                                CONCAT(IFNULL(t.nombre, ''), ' ', IFNULL(t.apellidos, '')) as nombre,
                                sv.fecha_inicio,
                                sv.fecha_fin
                            FROM registro_vacaciones sv
                            LEFT JOIN trabajadores t ON sv.trabajador_id = t.id
                            WHERE YEAR(sv.fecha_inicio) = $anno
                            AND sv.fecha_inicio IS NOT NULL
                            AND sv.fecha_fin IS NOT NULL
                            AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)";
            
            $db->consulta($sql_vacaciones);
            
            if ($db->num_rows() > 0) {
                while ($row = $db->siguiente()) {
                    if (empty($row['fecha_inicio']) || empty($row['fecha_fin'])) continue;
                    
                    try {
                        $inicio = strtotime($row['fecha_inicio']);
                        $fin = strtotime($row['fecha_fin']);
                        
                        if ($inicio === false || $fin === false) continue;
                        
                        for ($timestamp = $inicio; $timestamp <= $fin; $timestamp += 86400) {
                            $fecha_key = date('Y-m-d', $timestamp);
                            
                            if (!isset($eventos[$fecha_key])) {
                                $eventos[$fecha_key] = ['vacaciones' => [], 'cumpleanos' => []];
                            }
                            
                            $eventos[$fecha_key]['vacaciones'][] = [
                                'nombre' => trim($row['nombre']),
                                'tipo' => 'vac'
                            ];
                        }
                    } catch (Exception $ex) {
                        continue;
                    }
                }
            }

            // ===== VACACIONES DESDE plan_vacaciones (si existe columna 'dias') =====
            try {
                $sql_plan = "SELECT 
                                CONCAT(IFNULL(t.nombre, ''), ' ', IFNULL(t.apellidos, '')) as nombre,
                                pv.dias
                            FROM plan_vacaciones pv
                            LEFT JOIN trabajadores t ON pv.trabajador_id = t.id
                            WHERE pv.dias IS NOT NULL 
                            AND pv.dias != ''
                            AND pv.anno = $anno
                            AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado IS NULL)";
                
                $db->consulta($sql_plan);
                
                if ($db->num_rows() > 0) {
                    while ($row = $db->siguiente()) {
                        if (empty($row['dias'])) continue;
                        
                        try {
                            // Separar fechas por comas
                            $dias_array = array_filter(array_map('trim', explode(',', $row['dias'])));
                            
                            foreach ($dias_array as $fecha_str) {
                                // Validar formato YYYY-MM-DD
                                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_str)) continue;
                                
                                $timestamp = strtotime($fecha_str);
                                if ($timestamp === false) continue;
                                
                                // Verificar que esté en el año correcto
                                if ((int)date('Y', $timestamp) !== $anno) continue;
                                
                                $fecha_key = date('Y-m-d', $timestamp);
                                
                                if (!isset($eventos[$fecha_key])) {
                                    $eventos[$fecha_key] = ['vacaciones' => [], 'cumpleanos' => []];
                                }
                                
                                $eventos[$fecha_key]['vacaciones'][] = [
                                    'nombre' => trim($row['nombre']),
                                    'tipo' => 'vac'
                                ];
                            }
                        } catch (Exception $ex) {
                            continue;
                        }
                    }
                }
            } catch (Exception $ex) {
                // Columna 'dias' probablemente no existe, ignorar silenciosamente
                error_log("[CALENDARIO] Plan vacaciones: " . $ex->getMessage());
            }

            // ===== CUMPLEAÑOS =====
            $sql_cumpleanos = "SELECT 
                                CONCAT(IFNULL(nombre, ''), ' ', IFNULL(apellidos, '')) as nombre,
                                fecha_nacimiento
                            FROM trabajadores
                            WHERE fecha_nacimiento IS NOT NULL
                            AND (trabajador_eliminado = 0 OR trabajador_eliminado IS NULL)";
            
            $db->consulta($sql_cumpleanos);
            
            if ($db->num_rows() > 0) {
                while ($row = $db->siguiente()) {
                    if (empty($row['fecha_nacimiento'])) continue;
                    
                    try {
                        $nac = strtotime($row['fecha_nacimiento']);
                        if ($nac === false) continue;
                        
                        $mes = date('m', $nac);
                        $dia = date('d', $nac);
                        $fecha_cumple = strtotime("$anno-$mes-$dia");
                        $fecha_key = date('Y-m-d', $fecha_cumple);
                        
                        if (!isset($eventos[$fecha_key])) {
                            $eventos[$fecha_key] = ['vacaciones' => [], 'cumpleanos' => []];
                        }
                        
                        $eventos[$fecha_key]['cumpleanos'][] = [
                            'nombre' => trim($row['nombre']),
                            'tipo' => 'cumpl'
                        ];
                    } catch (Exception $ex) {
                        continue;
                    }
                }
            }

            return array(
                'status' => 1,
                'anno' => $anno,
                'eventos' => $eventos
            );

        } catch (Exception $e) {
            error_log("[CALENDARIO] Error: " . $e->getMessage());
            return array(
                'status' => 0,
                'msg' => 'Error al cargar calendario: ' . $e->getMessage(),
                'eventos' => array()

            );
        }
    }

    /**
     * Agregar evento personalizado
     */
    public function _agregar_evento_calendario($param = array()) {
        try {
            $db = new DWclass();

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
            $empresa_id = isset($param['empresa_id']) ? (int)$param['empresa_id'] : 0;

            $sql = "INSERT INTO eventos_calendario (fecha, nombre, descripcion, color, usuario_id, empresa_id, fecha_creacion, fecha_actualizacion, activo)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)";

            $stmt = $db->prepare($sql);
            $stmt->bind_param('ssssii', $fecha, $nombre, $descripcion, $color, $usuario_id, $empresa_id);

            if ($stmt->execute()) {
                return array(
                    'status' => 1,
                    'msg' => 'Evento creado exitosamente',
                    'id' => $stmt->insert_id
                );
            } else {
                error_log("[ERROR] Fallo al insertar evento: " . $stmt->error);
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
    public function _obtener_eventos_personalizados($param = array()) {
        try {
            $db = new DWclass();

            $fecha_inicio = isset($param['fecha_inicio']) ? $param['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($param['fecha_fin']) ? $param['fecha_fin'] : date('Y-m-t');
            $usuario_id = isset($param['usuario_id']) ? (int)$param['usuario_id'] : 0;

            $sql = "SELECT id, fecha, nombre, descripcion, color 
                    FROM eventos_calendario 
                    WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                    AND activo = 1";

            if ($usuario_id > 0) {
                $sql .= " AND usuario_id = $usuario_id";
            }

            $sql .= " ORDER BY fecha ASC";

            $db->consulta($sql);
            $eventos = array();

            while ($row = $db->siguiente()) {
                $eventos[] = array(
                    'id' => $row['id'],
                    'fecha' => $row['fecha'],
                    'nombre' => $row['nombre'],
                    'descripcion' => $row['descripcion'],
                    'color' => $row['color']
                );
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
    public function _actualizar_evento_calendario($param = array()) {
        try {
            $db = new DWclass();

            if (empty($param['id'])) {
                return array(
                    'status' => 0,
                    'msg' => 'ID del evento es requerido'
                );
            }

            $id = (int)$param['id'];
            $updates = array();

            if (!empty($param['nombre'])) {
                $updates[] = "nombre = '" . $param['nombre'] . "'";
            }

            if (isset($param['descripcion'])) {
                $updates[] = "descripcion = '" . $param['descripcion'] . "'";
            }

            if (!empty($param['color'])) {
                $updates[] = "color = '" . $param['color'] . "'";
            }

            if (!empty($param['fecha'])) {
                $updates[] = "fecha = '" . $param['fecha'] . "'";
            }

            if (empty($updates)) {
                return array(
                    'status' => 0,
                    'msg' => 'No hay campos para actualizar'
                );
            }

            $updates[] = "fecha_actualizacion = NOW()";
            $sql = "UPDATE eventos_calendario SET " . implode(', ', $updates) . " WHERE id = $id";

            if ($db->consulta($sql)) {
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
    public function _eliminar_evento_calendario($param = array()) {
        try {
            $db = new DWclass();

            if (empty($param['id'])) {
                return array(
                    'status' => 0,
                    'msg' => 'ID del evento es requerido'
                );
            }

            $id = (int)$param['id'];

            // Soft delete - marcar como inactivo
            $sql = "UPDATE eventos_calendario SET activo = 0, fecha_actualizacion = NOW() WHERE id = $id";

            if ($db->consulta($sql)) {
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

