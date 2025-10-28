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
    });