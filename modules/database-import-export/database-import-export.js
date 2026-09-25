// JavaScript para el módulo de importación/exportación de base de datos

// Función que espera a que jQuery esté disponible
function waitForJQuery() {
    if (typeof jQuery !== 'undefined') {
        // jQuery está disponible, ejecutar el código
        (function($) {
            $(document).ready(function() {
                // Manejar exportación
                $('#btn-export').click(function() {
                    if (confirm('¿Está seguro de que desea exportar la base de datos completa?')) {
                        window.location.href = 'index.php?module=database-import-export&action=export';
                    }
                });
                
                // Manejar importación
                $('#btn-import').click(function() {
                    var fileInput = $('#sql-file')[0];
                    
                    if (!fileInput.files || fileInput.files.length === 0) {
                        alert('Por favor seleccione un archivo SQL para importar');
                        return;
                    }
                    
                    var file = fileInput.files[0];
                    
                    // Validar tipo de archivo
                    if (file.name.toLowerCase().indexOf('.sql') === -1) {
                        alert('El archivo debe tener extensión .sql');
                        return;
                    }
                    
                    // Confirmar importación
                    if (!confirm('ADVERTENCIA: Importar una base de datos reemplazará todos los datos actuales. ¿Está seguro de que desea continuar?')) {
                        return;
                    }
                    
                    // Enviar formulario
                    $('#import-form').submit();
                });
                
                // Mostrar nombre de archivo seleccionado
                $('#sql-file').change(function() {
                    var fileName = $(this).val().split('\\').pop();
                    $('#file-name').text(fileName);
                });
            });
        })(jQuery);
    } else {
        // jQuery aún no está disponible, esperar un poco y volver a intentar
        setTimeout(waitForJQuery, 100);
    }
}

// Iniciar la espera de jQuery
waitForJQuery();
