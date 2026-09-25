<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/DWclass.php';

try {
    $db = new DWclass();
    
    // Primero, intentar verificar si la tabla existe
    $sql_check = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'eventos_calendario'";
    
    $sql_create = "
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'eventos_calendario')
    BEGIN
        CREATE TABLE eventos_calendario (
            id INT PRIMARY KEY IDENTITY(1,1),
            fecha DATE NOT NULL,
            nombre NVARCHAR(255) NOT NULL,
            descripcion NVARCHAR(MAX),
            color NVARCHAR(20) DEFAULT '#f0ad4e',
            usuario_id INT,
            empresa_id INT,
            fecha_creacion DATETIME DEFAULT GETDATE(),
            fecha_actualizacion DATETIME DEFAULT GETDATE(),
            activo BIT DEFAULT 1
        );
        
        CREATE INDEX idx_fecha ON eventos_calendario(fecha);
        CREATE INDEX idx_usuario ON eventos_calendario(usuario_id);
        CREATE INDEX idx_empresa ON eventos_calendario(empresa_id);
        
        PRINT 'Tabla creada exitosamente';
    END
    ELSE
    BEGIN
        PRINT 'La tabla ya existe';
    END
    ";
    
    if ($db->consulta($sql_create)) {
        echo json_encode([
            'status' => 1,
            'msg' => 'Tabla eventos_calendario lista',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'status' => 0,
            'msg' => 'Error al crear tabla',
            'error' => 'Error en consulta SQL'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'msg' => 'Error',
        'error' => $e->getMessage()
    ]);
}
?>
