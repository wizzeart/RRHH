<!DOCTYPE html>
<html>
<head>
    <title>Debug Submayor Vacaciones</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>🔍 Debug del Módulo Submayor de Vacaciones</h2>
    
    <div id="debug-results"></div>
    
    <script>
    $(document).ready(function() {
        var results = [];
        
        // Test 1: Verificar si jQuery funciona
        results.push('<h3>1. jQuery</h3>');
        if (typeof $ !== 'undefined') {
            results.push('<p style="color: green;">✅ jQuery está cargado</p>');
        } else {
            results.push('<p style="color: red;">❌ jQuery NO está cargado</p>');
        }
        
        // Test 2: Verificar elementos HTML
        results.push('<h3>2. Elementos HTML</h3>');
        
        // Simular los elementos que deberían existir
        $('body').append('<button id="btn-agregar-vacaciones">Test Button</button>');
        $('body').append('<table id="table-submayor-vacaciones"></table>');
        $('body').append('<div id="addVacacionesModal"></div>');
        
        var elementos = [
            'btn-agregar-vacaciones',
            'table-submayor-vacaciones', 
            'addVacacionesModal'
        ];
        
        elementos.forEach(function(id) {
            if ($('#' + id).length > 0) {
                results.push('<p style="color: green;">✅ #' + id + ' encontrado</p>');
            } else {
                results.push('<p style="color: red;">❌ #' + id + ' NO encontrado</p>');
            }
        });
        
        // Test 3: Verificar event handlers
        results.push('<h3>3. Event Handlers</h3>');
        
        // Intentar registrar event handler
        try {
            $('#btn-agregar-vacaciones').on('click', function() {
                alert('¡Event handler funciona!');
            });
            results.push('<p style="color: green;">✅ Event handler registrado correctamente</p>');
            
            // Probar el click programáticamente
            $('#btn-agregar-vacaciones').trigger('click');
            
        } catch (e) {
            results.push('<p style="color: red;">❌ Error registrando event handler: ' + e.message + '</p>');
        }
        
        // Test 4: Verificar funciones
        results.push('<h3>4. Funciones Disponibles</h3>');
        
        var funciones = [
            'waitForJQ',
            'initHandlers',
            'niftyNoty'
        ];
        
        funciones.forEach(function(func) {
            if (typeof window[func] === 'function') {
                results.push('<p style="color: green;">✅ ' + func + '() disponible</p>');
            } else {
                results.push('<p style="color: red;">❌ ' + func + '() NO disponible</p>');
            }
        });
        
        // Test 5: Verificar Bootstrap Table
        results.push('<h3>5. Bootstrap Table</h3>');
        if (typeof $.fn.bootstrapTable !== 'undefined') {
            results.push('<p style="color: green;">✅ Bootstrap Table disponible</p>');
        } else {
            results.push('<p style="color: red;">❌ Bootstrap Table NO disponible</p>');
        }
        
        // Mostrar resultados
        $('#debug-results').html(results.join(''));
        
        // Test final: Simular carga del archivo JS
        results.push('<h3>6. Simulación de Carga JS</h3>');
        
        // Simular waitForJQ
        if (typeof waitForJQ === 'function') {
            waitForJQ(function($) {
                results.push('<p style="color: green;">✅ waitForJQ ejecutado correctamente</p>');
                $('#debug-results').html(results.join(''));
            });
        } else {
            // Simular waitForJQ manualmente
            setTimeout(function() {
                results.push('<p style="color: orange;">⚠️ waitForJQ no disponible, usando setTimeout</p>');
                $('#debug-results').html(results.join(''));
            }, 100);
        }
    });
    </script>
    
    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6;">
        <h3>🔧 Pasos de Solución</h3>
        <ol>
            <li><strong>Si jQuery no funciona:</strong> Verificar que esté incluido en el HTML</li>
            <li><strong>Si elementos no existen:</strong> Verificar IDs en el archivo PHP</li>
            <li><strong>Si event handlers fallan:</strong> Verificar sintaxis JavaScript</li>
            <li><strong>Si funciones no están:</strong> Verificar includes del sistema</li>
        </ol>
        
        <h4>🚀 Prueba Manual</h4>
        <p>Abrir DevTools (F12) y ejecutar:</p>
        <code>
            console.log('jQuery:', typeof $);<br>
            console.log('Botón:', $('#btn-agregar-vacaciones').length);<br>
            $('#btn-agregar-vacaciones').click();
        </code>
    </div>
</body>
</html>
