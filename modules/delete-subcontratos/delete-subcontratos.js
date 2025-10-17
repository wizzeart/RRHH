$(document).ready(function () {
  // Helper de notificaciones: usa Nifty Noty si está disponible, si no, fallback a alert
  function notify(type, title, message, timer) {
    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
        $.niftyNoty({
            type: type || 'info',
            container: 'floating',
            title: title || '',
            message: message || '',
            timer: timer != null ? timer : 3000,
            closeBtn: true,
            focus: true
        });
    } else {
        // Fallback simple para garantizar feedback al usuario
        var text = (title ? (title + ': ') : '') + (message || '');
        try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
}

 $('#btn-add-new').click(function () {
        location.href = 'index.php?module=subcontratos';
    });


    // Delegar el clic sobre el botón con data-id (independiente del ícono)
    $('#table-panel').on('click', 'button[data-id], .fa.fa-trash, .fa.fa-ban', function (e) {
        // Obtener el botón si el clic fue sobre el ícono
        var $btn = $(this).is('button') ? $(this) : $(this).closest('button[data-id]');
        if ($btn.length === 0) return;

        if (confirm('¿Estás seguro de finalizar este subcontrato? Se establecerá la fecha de fin automáticamente.')) {
            var rowIndex = $btn.parent().parent().data('index');
            var cmd = 'module=subcontratos&method=del&id=' + $btn.data('id') + '&row=' + rowIndex;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                        // Mostrar notificación de éxito
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'success',
                                container: 'floating',
                                title: 'Finalizado',
                                message: 'El subcontrato ha sido finalizado correctamente con fecha de hoy.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Subcontrato finalizado correctamente.');
                        }
                    } else {
                        // Mostrar notificación de error
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'danger',
                                container: 'floating',
                                title: 'Error',
                                message: 'No se pudo finalizar el subcontrato.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Error al finalizar el subcontrato.');
                        }
                    }
                },
                error: function() {
                    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                        $.niftyNoty({
                            type: 'danger',
                            container: 'floating',
                            title: 'Error',
                            message: 'Error de conexión al finalizar el subcontrato.',
                            timer: 3000,
                            closeBtn: true,
                            focus: true
                        });
                    } else {
                        alert('Error de conexión.');
                    }
                }
            });
        }
    });
});

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================

// Sample Format for Tracking Number Column - Only Delete Button
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-ban" title="Finalizar subcontrato"></button>';
    return s;
}
