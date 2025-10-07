<?php
// Script para ver los logs de debug en tiempo real
echo "<h2>📋 Logs de Debug - Trabajadores</h2>";

// Buscar el archivo de log de Apache/PHP
$possible_logs = [
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/php/logs/php_error_log',
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log'
];

$log_file = null;
foreach ($possible_logs as $file) {
    if (file_exists($file)) {
        $log_file = $file;
        break;
    }
}

if ($log_file) {
    echo "<p><strong>Archivo de log encontrado:</strong> $log_file</p>";
    
    // Leer las últimas 50 líneas del log
    $lines = file($log_file);
    $recent_lines = array_slice($lines, -50);
    
    echo "<h3>Últimas 50 líneas del log:</h3>";
    echo "<div style='background: #f0f0f0; padding: 10px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: scroll;'>";
    
    foreach ($recent_lines as $line) {
        // Resaltar líneas que contengan "DEBUG _save"
        if (strpos($line, 'DEBUG _save') !== false) {
            echo "<div style='background: yellow; padding: 2px;'>" . htmlspecialchars($line) . "</div>";
        } else {
            echo htmlspecialchars($line) . "<br>";
        }
    }
    
    echo "</div>";
    
    // Filtrar solo líneas de DEBUG _save
    echo "<h3>Solo logs de DEBUG _save:</h3>";
    echo "<div style='background: #e8f5e8; padding: 10px; font-family: monospace; font-size: 12px;'>";
    
    foreach ($recent_lines as $line) {
        if (strpos($line, 'DEBUG _save') !== false) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
    
    echo "</div>";
    
} else {
    echo "<p style='color: red;'>❌ No se encontró archivo de log en las ubicaciones comunes.</p>";
    echo "<p>Ubicaciones buscadas:</p>";
    echo "<ul>";
    foreach ($possible_logs as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    
    // Mostrar configuración de PHP para logs
    echo "<h3>Configuración de logs de PHP:</h3>";
    echo "<p><strong>log_errors:</strong> " . (ini_get('log_errors') ? 'Activado' : 'Desactivado') . "</p>";
    echo "<p><strong>error_log:</strong> " . ini_get('error_log') . "</p>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<p>1. Actualiza esta página después de intentar guardar un trabajador</p>";
echo "<p>2. Busca las líneas resaltadas en amarillo que contienen 'DEBUG _save'</p>";
echo "<p>3. Verifica qué valores están llegando para id_provincia e id_municipio</p>";
?>

<script>
// Auto-refresh cada 5 segundos
setTimeout(function() {
    location.reload();
}, 5000);
</script>
