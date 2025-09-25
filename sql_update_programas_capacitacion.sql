-- Script para agregar la columna fecha_finalizacion a la tabla programas_capacitacion
-- Ejecutar este SQL en tu base de datos para que funcione la opción "Finalizar Programas"

ALTER TABLE programas_capacitacion 
ADD COLUMN fecha_finalizacion DATE NULL 
COMMENT 'Fecha en que se finalizó el programa de capacitación';

-- Verificar que la columna se agregó correctamente
DESCRIBE programas_capacitacion;
