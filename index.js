$(document).ready(function() {
        // Manejar el clic en el botón de cambio de empresa
        $('.cambiar-empresa').click(function(e) {
            e.preventDefault();
            var empresaId = $(this).data('empresa-id');
            var empresaNombre = $(this).text().trim().replace(' ✓', ''); // Remove checkmark if present
            
            // Show loading state
            var $btn = $(this);
            var $icon = $('<i class="fa fa-spinner fa-spin"></i>');
            $btn.prepend($icon);
            console.log(empresaId);
            
            // Make AJAX request to change company
            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: {
                    cambiar_empresa: 1,
                    empresa_id: empresaId
                },
                success: function(response) {
                    // Remove loading icon
                    $icon.remove();
                    console.log(response);
                    // Show success message
                    $.niftyNoty({
                        type: 'success',
                        icon: 'fa fa-check',
                        message: 'Empresa cambiada a: ' + empresaNombre,
                        container: 'floating',
                        timer: 3000
                    });
                    
                    // Trigger custom event to notify other modules
                    $(document).trigger('empresaChanged', [empresaId]);
                    
                    //Refresh the page
                    location.reload(true);
                    
                },
                error: function() {
                    // Remove loading icon
                    $icon.remove();
                    
                    // Show error message
                    $.niftyNoty({
                        type: 'danger',
                        icon: 'fa fa-exclamation',
                        message: 'Error al cambiar de empresa',
                        container: 'floating',
                        timer: 3000
                    });
                }
            });
        });
        
        // Check for birthdays today on page load
        checkCumpleanosHoy();
    });

    function checkCumpleanosHoy() {
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: {
                module: 'trabajadores',
                method: 'cumpleanos-hoy'
            },
            dataType: 'json',
            success: function(cumpleaños) {
                if (cumpleaños && cumpleaños.length > 0) {
                    // Actualizar badge en la campanita
                    $('#cumpleanos-count').text(cumpleaños.length).show();
                    
                    // Crear notificación compacta
                    var nombre = cumpleaños[0].nombre + ' ' + cumpleaños[0].apellidos;
                    var titulo = 'Hoy Cumple años ' + nombre;
                    
                    var $notifDiv = $('<div id="birthday-notification" style="' +
                        'position: fixed; ' +
                        'top: 50px; ' +
                        'right: 20px; ' +
                        'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); ' +
                        'color: white; ' +
                        'padding: 8px 12px; ' +
                        'border-radius: 4px; ' +
                        'box-shadow: 0 2px 8px rgba(0,0,0,0.15); ' +
                        'z-index: 10000; ' +
                        'cursor: pointer; ' +
                        'font-size: 12px; ' +
                        'white-space: nowrap; ' +
                        'font-family: Arial, sans-serif;' +
                        '">' +
                        '<i class="fa fa-birthday-cake" style="margin-right: 6px;"></i>' +
                        '<span>' + titulo + '</span>' +
                        '</div>');
                    
                    $('body').append($notifDiv);
                    
                    // Click handler para ir a list-otros
                    $notifDiv.on('click', function() {
                        location.href = '?module=list-otros';
                    });
                    
                    // Auto-remove after 8 seconds
                    setTimeout(function() {
                        $notifDiv.fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 8000);
                }
            },
            error: function(e) {
                console.log('Error checking birthdays:', e);
            }
        });
    }
