<?php
// Script para verificar y crear las tablas de provincias y municipios
require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🔧 Setup de Provincias y Municipios</h2>";

try {
    // 1. Verificar tabla provincias
    echo "<h3>1. Verificando tabla 'provincias':</h3>";
    $sql = "SHOW TABLES LIKE 'provincias'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "<p style='color: orange;'>⚠️ La tabla 'provincias' no existe. Creando...</p>";
        
        $createProvincias = "
        CREATE TABLE `provincias` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `codigo` varchar(10) DEFAULT NULL,
            `activo` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_nombre` (`nombre`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $app->db->fetchAll($createProvincias);
        echo "<p style='color: green;'>✅ Tabla 'provincias' creada exitosamente.</p>";
        
        // Insertar provincias de ejemplo (Cuba)
        $provincias = [
            "Pinar del Río", "Artemisa", "La Habana", "Mayabeque", "Matanzas",
            "Cienfuegos", "Villa Clara", "Sancti Spíritus", "Ciego de Ávila",
            "Camagüey", "Las Tunas", "Holguín", "Granma", "Santiago de Cuba",
            "Guantánamo", "Isla de la Juventud"
        ];
        
        foreach ($provincias as $provincia) {
            $app->db->fetchAll("INSERT INTO provincias (nombre) VALUES (?)", [$provincia]);
        }
        echo "<p style='color: green;'>✅ Provincias de Cuba insertadas.</p>";
        
    } else {
        echo "<p style='color: green;'>✅ La tabla 'provincias' ya existe.</p>";
    }
    
    // 2. Verificar tabla municipios
    echo "<h3>2. Verificando tabla 'municipios':</h3>";
    $sql = "SHOW TABLES LIKE 'municipios'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "<p style='color: orange;'>⚠️ La tabla 'municipios' no existe. Creando...</p>";
        
        $createMunicipios = "
        CREATE TABLE `municipios` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `provincia_id` int(11) NOT NULL,
            `codigo` varchar(10) DEFAULT NULL,
            `activo` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `fk_municipio_provincia` (`provincia_id`),
            CONSTRAINT `fk_municipio_provincia` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $app->db->fetchAll($createMunicipios);
        echo "<p style='color: green;'>✅ Tabla 'municipios' creada exitosamente.</p>";
        
        // Insertar algunos municipios de ejemplo
        $municipios = [
            // La Habana (id: 3)
            [3, "Playa"], [3, "Plaza de la Revolución"], [3, "Centro Habana"], 
            [3, "La Habana Vieja"], [3, "Regla"], [3, "La Habana del Este"],
            [3, "Guanabacoa"], [3, "San Miguel del Padrón"], [3, "Diez de Octubre"],
            [3, "Cerro"], [3, "Marianao"], [3, "La Lisa"], [3, "Boyeros"],
            [3, "Arroyo Naranjo"], [3, "Cotorro"],
            
            // Santiago de Cuba (id: 14)
            [14, "Santiago de Cuba"], [14, "Contramaestre"], [14, "Mella"],
            [14, "San Luis"], [14, "Segundo Frente"], [14, "Songo-La Maya"],
            [14, "Tercer Frente"], [14, "Guamá"], [14, "Palma Soriano"],
            
            // Matanzas (id: 5)
            [5, "Matanzas"], [5, "Cárdenas"], [5, "Varadero"], [5, "Martí"],
            [5, "Colón"], [5, "Perico"], [5, "Jovellanos"], [5, "Pedro Betancourt"],
            [5, "Limonar"], [5, "Unión de Reyes"], [5, "Ciénaga de Zapata"],
            [5, "Jagüey Grande"], [5, "Calimete"], [5, "Los Arabos"]
        ];
        
        foreach ($municipios as $municipio) {
            $app->db->fetchAll("INSERT INTO municipios (provincia_id, nombre) VALUES (?, ?)", $municipio);
        }
        echo "<p style='color: green;'>✅ Municipios de ejemplo insertados.</p>";
        
    } else {
        echo "<p style='color: green;'>✅ La tabla 'municipios' ya existe.</p>";
    }
    
    // 3. Verificar campos en tabla trabajadores
    echo "<h3>3. Verificando campos en tabla 'trabajadores':</h3>";
    
    // Verificar provincia_id
    $sql = "SHOW COLUMNS FROM trabajadores LIKE 'provincia_id'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "<p style='color: orange;'>⚠️ Campo 'provincia_id' no existe. Agregando...</p>";
        $app->db->fetchAll("ALTER TABLE trabajadores ADD COLUMN provincia_id INT(11) DEFAULT NULL AFTER direccion");
        echo "<p style='color: green;'>✅ Campo 'provincia_id' agregado.</p>";
    } else {
        echo "<p style='color: green;'>✅ Campo 'provincia_id' ya existe.</p>";
    }
    
    // Verificar municipio_id
    $sql = "SHOW COLUMNS FROM trabajadores LIKE 'municipio_id'";
    $result = $app->db->fetchAll($sql);
    
    if (empty($result)) {
        echo "<p style='color: orange;'>⚠️ Campo 'municipio_id' no existe. Agregando...</p>";
        $app->db->fetchAll("ALTER TABLE trabajadores ADD COLUMN municipio_id INT(11) DEFAULT NULL AFTER provincia_id");
        echo "<p style='color: green;'>✅ Campo 'municipio_id' agregado.</p>";
    } else {
        echo "<p style='color: green;'>✅ Campo 'municipio_id' ya existe.</p>";
    }
    
    // 4. Mostrar estadísticas
    echo "<h3>4. Estadísticas:</h3>";
    $countProvincias = $app->db->fetchRow("SELECT COUNT(*) as total FROM provincias");
    $countMunicipios = $app->db->fetchRow("SELECT COUNT(*) as total FROM municipios");
    $countTrabajadores = $app->db->fetchRow("SELECT COUNT(*) as total FROM trabajadores");
    
    echo "<p>📊 <strong>Provincias:</strong> {$countProvincias['total']}</p>";
    echo "<p>📊 <strong>Municipios:</strong> {$countMunicipios['total']}</p>";
    echo "<p>📊 <strong>Trabajadores:</strong> {$countTrabajadores['total']}</p>";
    
    // 5. Mostrar algunas provincias y municipios
    echo "<h3>5. Datos de ejemplo:</h3>";
    $provincias = $app->db->fetchAll("SELECT * FROM provincias LIMIT 5");
    echo "<h4>Provincias (primeras 5):</h4>";
    echo "<ul>";
    foreach ($provincias as $prov) {
        echo "<li>{$prov['id']} - {$prov['nombre']}</li>";
    }
    echo "</ul>";
    
    $municipios = $app->db->fetchAll("
        SELECT m.id, m.nombre, p.nombre as provincia 
        FROM municipios m 
        LEFT JOIN provincias p ON m.provincia_id = p.id 
        LIMIT 10
    ");
    echo "<h4>Municipios (primeros 10):</h4>";
    echo "<ul>";
    foreach ($municipios as $mun) {
        echo "<li>{$mun['id']} - {$mun['nombre']} ({$mun['provincia']})</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>✅ Setup completado</h3>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<p>1. Las tablas y campos necesarios han sido creados/verificados.</p>";
echo "<p>2. Se han insertado datos de ejemplo de provincias y municipios de Cuba.</p>";
echo "<p>3. El formulario de trabajadores ahora debería funcionar correctamente.</p>";
echo "<p>4. Puedes agregar más municipios según sea necesario.</p>";
echo "<p><a href='?module=trabajadores' target='_blank'>🔗 Ir al formulario de trabajadores</a></p>";
?>
