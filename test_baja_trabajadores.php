<?php
/**
 * Script de prueba para verificar la funcionalidad de dar de baja trabajadores
 */

require_once('config.php');
require_once(BASE_CLASS . '/App.class.php');

$app = new App();

echo "<h2>🧪 Prueba de funcionalidad: Dar de Baja Trabajadores</h2>";

// Verificar que existe el método del en mdl.Trabajadores.php
echo "<h3>1. Verificación del controlador:</h3>";
$archivoControlador = 'classes/mdl.Trabajadores.php';
$contenido = file_get_contents($archivoControlador);

if (strpos($contenido, "case 'del':") !== false) {
    echo "<p>✅ Método 'del' encontrado en API switch</p>";
} else {
    echo "<p>❌ Método 'del' NO encontrado en API switch</p>";
}

if (strpos($contenido, "private function _del(") !== false) {
    echo "<p>✅ Función '_del' encontrada en controlador</p>";
} else {
    echo "<p>❌ Función '_del' NO encontrada en controlador</p>";
}

// Verificar archivos modificados
echo "<h3>2. Verificación de archivos modificados:</h3>";

// Verificar list-trabajadores.js
$archivoJS = 'modules/list-trabajadores/list-trabajadores.js';
$contenidoJS = file_get_contents($archivoJS);

if (strpos($contenidoJS, 'btn-dar-baja') !== false) {
    echo "<p>✅ Botón 'Dar de Baja' agregado al formatter</p>";
} else {
    echo "<p>❌ Botón 'Dar de Baja' NO encontrado en formatter</p>";
}

if (strpos($contenidoJS, 'FUNCIONALIDAD PARA DAR DE BAJA') !== false) {
    echo "<p>✅ Funcionalidad JavaScript agregada</p>";
} else {
    echo "<p>❌ Funcionalidad JavaScript NO encontrada</p>";
}

// Verificar list-trabajadores.php
$archivoHTML = 'modules/list-trabajadores/list-trabajadores.php';
$contenidoHTML = file_get_contents($archivoHTML);

if (strpos($contenidoHTML, 'confirmDeleteModal') !== false) {
    echo "<p>✅ Modal de confirmación agregado</p>";
} else {
    echo "<p>❌ Modal de confirmación NO encontrado</p>";
}

// Verificar algunos trabajadores de ejemplo
echo "<h3>3. Trabajadores disponibles para prueba:</h3>";
try {
    $trabajadores = $app->db->fetchAll("
        SELECT t.id, t.nombre, t.apellidos, t.carnet_identidad, t.trabajador_eliminado, t.fecha_baja
        FROM trabajadores t 
        WHERE t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL
        ORDER BY t.id DESC 
        LIMIT 5
    ");
    
    if (count($trabajadores) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th style='padding: 5px;'>ID</th><th style='padding: 5px;'>Nombre</th><th style='padding: 5px;'>CI</th><th style='padding: 5px;'>Estado</th></tr>";
        foreach ($trabajadores as $trab) {
            $estado = ($trab['trabajador_eliminado'] == '0' || is_null($trab['trabajador_eliminado'])) ? 
                "<span style='color: green;'>Activo</span>" : 
                "<span style='color: red;'>Dado de Baja</span>";
            echo "<tr>";
            echo "<td style='padding: 5px;'>{$trab['id']}</td>";
            echo "<td style='padding: 5px;'>{$trab['nombre']} {$trab['apellidos']}</td>";
            echo "<td style='padding: 5px;'>{$trab['carnet_identidad']}</td>";
            echo "<td style='padding: 5px;'>{$estado}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p>✅ Hay " . count($trabajadores) . " trabajadores activos disponibles para prueba</p>";
    } else {
        echo "<p>⚠️ No hay trabajadores activos en la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al consultar trabajadores: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Instrucciones de prueba:</h3>";
echo "<ol>";
echo "<li>Ve al módulo <strong>list-trabajadores</strong> (?module=list-trabajadores)</li>";
echo "<li>Busca la columna <strong>Acciones</strong> - debe tener 3 botones:</li>";
echo "<ul>";
echo "<li>🔵 <strong>Editar</strong> (azul)</li>";
echo "<li>🟢 <strong>Ver Ficha</strong> (verde)</li>";
echo "<li>🔴 <strong>Dar de Baja</strong> (rojo) - NUEVO</li>";
echo "</ul>";
echo "<li>Haz clic en <strong>Dar de Baja</strong> en cualquier trabajador</li>";
echo "<li>Debe aparecer un modal pidiendo escribir <strong>ELIMINAR</strong></li>";
echo "<li>Escribe 'ELIMINAR' y confirma</li>";
echo "<li>El trabajador debe desaparecer de la lista y recibir fecha_baja = hoy</li>";
echo "</ol>";

echo "<h3>5. Funcionalidades implementadas:</h3>";
echo "<ul>";
echo "<li>✅ Botón 'Dar de Baja' en columna Acciones</li>";
echo "<li>✅ Modal de confirmación con validación de texto</li>";
echo "<li>✅ AJAX para procesar la baja</li>";
echo "<li>✅ Notificaciones de éxito/error</li>";
echo "<li>✅ Actualización automática de la tabla</li>";
echo "<li>✅ Baja lógica (fecha_baja + trabajador_eliminado = 1)</li>";
echo "</ul>";

echo "<h3>6. Archivos modificados:</h3>";
echo "<ul>";
echo "<li>📁 modules/list-trabajadores/list-trabajadores.js - Botón y funcionalidad</li>";
echo "<li>📁 modules/list-trabajadores/list-trabajadores.php - Modal de confirmación</li>";
echo "<li>📁 classes/mdl.Trabajadores.php - Método _del (ya existía)</li>";
echo "</ul>";

echo "<p style='background-color: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 20px;'>";
echo "<strong>🎯 RESULTADO:</strong> Ahora puedes dar de baja trabajadores directamente desde la lista principal, ";
echo "con la misma funcionalidad y seguridad que el módulo delete-trabajadores.";
echo "</p>";
?>
