<?php
// Debug para ver qué datos llegan al guardar trabajador
echo "<h2>🔍 Debug de datos POST/GET</h2>";

echo "<h3>Datos $_POST:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Datos $_GET:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>Datos $_REQUEST:</h3>";
echo "<pre>";
print_r($_REQUEST);
echo "</pre>";

// Verificar específicamente los campos de provincia y municipio
echo "<h3>Campos específicos:</h3>";
echo "<p><strong>id_provincia:</strong> " . (isset($_REQUEST['id_provincia']) ? $_REQUEST['id_provincia'] : 'NO DEFINIDO') . "</p>";
echo "<p><strong>id_municipio:</strong> " . (isset($_REQUEST['id_municipio']) ? $_REQUEST['id_municipio'] : 'NO DEFINIDO') . "</p>";

// Mostrar formulario de prueba
?>
<hr>
<h3>Formulario de Prueba</h3>
<form method="POST" action="">
    <p>
        <label>Provincia:</label>
        <select name="id_provincia">
            <option value="">Seleccionar</option>
            <option value="1">Provincia 1</option>
            <option value="2">Provincia 2</option>
        </select>
    </p>
    <p>
        <label>Municipio:</label>
        <select name="id_municipio">
            <option value="">Seleccionar</option>
            <option value="1">Municipio 1</option>
            <option value="2">Municipio 2</option>
        </select>
    </p>
    <p>
        <button type="submit">Enviar Prueba</button>
    </p>
</form>
