<?php
require_once 'includes/config.php';
require_once 'classes/MSSql.class.php';

class ActualizarAsistencia {
    private $db;
    
    public function __construct() {
        $this->db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, '3306');
        $this->procesarAsistencia();
    }
    
    private function procesarAsistencia() {
        try {
            // Obtener la fecha actual
            $fecha_actual = date('Y-m-d');
            
            // 1. Actualizar registros existentes para el día actual
            $sql_update = 
               "UPDATE registro_asistencia 
                SET 
                    tardanza = IF(hora_entrada IS NOT NULL AND hora_entrada > '09:00:00' AND hora_entrada < '13:00:00', 1, 0),
                    ausencia = IF((hora_entrada IS NOT NULL AND hora_entrada >= '13:00:00') OR (hora_entrada IS NULL), 1, 0)
                WHERE fecha = '{$fecha_actual}'";

            
            $this->db->directExec($sql_update);
            
            // 2. Obtener todos los trabajadores activos
            $sql_trabajadores = "SELECT id FROM trabajadores WHERE trabajador_eliminado = 0";
            $trabajadores = $this->db->fetchAll($sql_trabajadores);
            
            // 3. Para cada trabajador, verificar si tiene registro en el día
            foreach ($trabajadores as $trabajador) {
                $trabajador_id = $trabajador['id'];
                
                $sql_check = "SELECT COUNT(*) as existe FROM registro_asistencia 
                             WHERE trabajador_id = '{$trabajador_id}' AND fecha = '{$fecha_actual}'";
                $result = $this->db->fetchAll($sql_check);
                
                if ($result[0]['existe'] == 0) {
                    // Insertar registro de ausencia
                    $sql_insert = "INSERT INTO registro_asistencia 
                                  (trabajador_id, fecha, ausencia, tardanza) 
                                  VALUES ('{$trabajador_id}', '{$fecha_actual}', 1, 0)";
                    $this->db->directExec($sql_insert);
                }
            }
            
            echo "Proceso completado exitosamente. Se actualizaron los registros de asistencia para la fecha: " . $fecha_actual;
            
        } catch (Exception $e) {
            echo "Error al procesar la asistencia: " . $e->getMessage();
        }
    }
}

// Ejecutar el script
new ActualizarAsistencia();
