<?php

class ActualizarAsistencia {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->procesarAsistencia();
    }
    
    private function procesarDia($fecha_actual) {

        // tipo_horario = 0 → Diurno especial
        // tipo_horario = 1 → Diurno
        // tipo_horario = 2 → Nocturno

        // ==========================================================
        // 1. ACTUALIZAR REGISTROS EXISTENTES PARA EL DÍA ACTUAL
        // ==========================================================

        $sql_update = 
        "UPDATE registro_asistencia 
            LEFT JOIN trabajadores t ON t.id = registro_asistencia.trabajador_id
        SET 
            tardanza = IF(
                t.tipo_horario != 1, 
                0, 
                IF(hora_entrada IS NOT NULL 
                    AND hora_entrada > '09:00:00' 
                    AND hora_entrada < '18:00:00', 
                    1, 
                    0
                )
            ),

            ausencia = CASE
                -- VACACIONES (ausencia = 3)
                WHEN EXISTS (
                    SELECT 1 FROM plan_vacaciones v
                    WHERE v.trabajador_id = t.id
                    AND '{$fecha_actual}' BETWEEN v.fecha_inicio AND v.fecha_fin
                    AND v.fecha_aprobacion IS NOT NULL
                ) THEN 3

                -- AUSENCIA NORMAL
                WHEN hora_entrada IS NULL THEN 1
                ELSE 0
            END

        WHERE fecha = '{$fecha_actual}' 
          AND t.trabajador_eliminado = 0";

        $this->db->directExec($sql_update);

        // ==========================================================
        // 2. OBTENER TODOS LOS TRABAJADORES ACTIVOS
        // ==========================================================
        $sql_trabajadores = "SELECT id FROM trabajadores WHERE trabajador_eliminado = 0";
        $trabajadores = $this->db->fetchAll($sql_trabajadores);

        // ==========================================================
        // 3. INSERTAR REGISTRO SI NO EXISTE
        // ==========================================================

        foreach ($trabajadores as $trabajador) {

            $trabajador_id = $trabajador['id'];

            // verificar si existe registro para el trabajador
            $sql_check = "SELECT COUNT(*) AS existe
                FROM registro_asistencia 
                LEFT JOIN trabajadores t ON t.id = registro_asistencia.trabajador_id
                WHERE trabajador_id = '{$trabajador_id}' 
                  AND fecha = '{$fecha_actual}' 
                  AND (t.tipo_horario = 1 OR t.tipo_horario IS NULL) 
                  AND t.trabajador_eliminado = 0;
            ";
            $result = $this->db->fetchAll($sql_check);

            if ($result[0]['existe'] == 0) {

                // INSERT con regla de VACACIONES → ausencia = 3
                $sql_insert = "INSERT INTO registro_asistencia (trabajador_id, fecha, ausencia, tardanza)
                    SELECT 
                        t.id,
                        '{$fecha_actual}',
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM plan_vacaciones v
                                WHERE v.trabajador_id = t.id
                                  AND '{$fecha_actual}' BETWEEN v.fecha_inicio AND v.fecha_fin
                                  AND v.fecha_aprobacion IS NOT NULL
                            ) THEN 3
                            ELSE 1
                        END,
                        0
                    FROM trabajadores t
                    WHERE t.id = '{$trabajador_id}'
                      AND t.trabajador_eliminado = 0
                      AND (t.tipo_horario = 1 OR t.tipo_horario IS NULL)
                ";

                $this->db->directExec($sql_insert);
            }
        }
    }

    private function procesarAsistencia() {
        try {
            // Fecha actual
            date_default_timezone_set('America/Havana');
            $fecha_actual = date('Y-m-d');

            // Procesar día actual
            $this->procesarDia($fecha_actual);

            // Procesar día anterior
            $fecha_actual2 = date('Y-m-d', strtotime('-1 day', strtotime($fecha_actual)));
            $this->procesarDia($fecha_actual2);

            // ==========================================================
            // 3. PROCESAR HORARIO NOCTURNO (tipo_horario = 2)
            // ==========================================================
            $sql_turno2 = "SELECT 
                    t.id AS trabajador_id, 
                    r1.id AS id_dia1, 
                    r2.id AS id_dia2,
                    r1.fecha AS fecha1, 
                    r2.fecha AS fecha2, 
                    r1.hora_entrada AS hora_entrada_dia1,
                    r2.hora_entrada AS hora_entrada_dia2,
                    r2.hora_salida AS hora_salida_dia2
                FROM trabajadores t
                INNER JOIN registro_asistencia r1 ON r1.trabajador_id = t.id
                INNER JOIN registro_asistencia r2 ON r2.trabajador_id = t.id
                WHERE t.tipo_horario = 2
                  AND t.trabajador_eliminado = 0
                  AND DATEDIFF(r2.fecha, r1.fecha) = 1
                  AND r1.hora_entrada IS NOT NULL
                  AND r2.hora_entrada IS NOT NULL
                ORDER BY t.id, r1.fecha;
            ";
            
            $pares = $this->db->fetchAll($sql_turno2);

            foreach ($pares as $p) {

                $id_dia1 = $p['id_dia1'];
                $id_dia2 = $p['id_dia2'];

                $hora1 = new DateTime($p['hora_entrada_dia1']);
                $hora2 = new DateTime($p['hora_entrada_dia2']);

                $h1 = (int)$hora1->format('H') + ((int)$hora1->format('i') / 60);
                $h2 = (int)$hora2->format('H') + ((int)$hora2->format('i') / 60);

                // corrección por madrugada
                if ($h2 < $h1) {
                    $diff_horas = (24 - $h1) + $h2;
                } else {
                    $diff_horas = $h2 - $h1;
                }

                // unir si la diferencia es <= 15 horas
                if ($diff_horas > 0 && $diff_horas <= 15) {

                    // actualizar salida del primer día
                    $sql_upd_salida = "
                        UPDATE registro_asistencia 
                        SET hora_salida = '{$p['hora_entrada_dia2']}' 
                        WHERE id = '{$id_dia1}'
                    ";
                    $this->db->directExec($sql_upd_salida);

                    // eliminar registro del segundo día
                    $sql_del = "DELETE FROM registro_asistencia WHERE id = '{$id_dia2}'";
                    $this->db->directExec($sql_del);
                }
            }

            //echo "Proceso completado exitosamente. Se actualizaron los registros para las fechas: {$fecha_actual2} y {$fecha_actual}";

        } catch (Exception $e) {
            //echo "Error al procesar la asistencia: " . $e->getMessage();
        }
    }
}


