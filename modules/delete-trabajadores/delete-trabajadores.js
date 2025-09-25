$(document).ready(function () {
    // Delegar el clic sobre el botón con data-id (independiente del ícono)
    $('#table-panel').on('click', 'button[data-id], .fa.fa-trash, .fa.fa-ban', function () {
        var $btn = $(this).is('button') ? $(this) : $(this).closest('button[data-id]');
        if ($btn.length === 0) return;

        if (confirm('¿Estás seguro de dar de baja a este trabajador? Se establecerá la fecha de baja automáticamente.')) {
            var rowIndex = $btn.parent().parent().data('index');
            var cmd = 'module=trabajadores&method=del&id=' + $btn.data('id') + '&row=' + rowIndex;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                        // Mostrar notificación de éxito
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'success',
                                container: 'floating',
                                title: 'Dado de Baja',
                                message: 'El trabajador ha sido dado de baja correctamente con fecha de hoy.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Trabajador eliminado correctamente.');
                        }
                    } else {
                        // Mostrar notificación de error
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'danger',
                                container: 'floating',
                                title: 'Error',
                                message: 'No se pudo dar de baja al trabajador.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Error al eliminar el trabajador.');
                        }
                    }
                },
                error: function() {
                    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                        $.niftyNoty({
                            type: 'danger',
                            container: 'floating',
                            title: 'Error',
                            message: 'Error de conexión al dar de baja al trabajador.',
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
    var s = '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-ban" title="Dar de baja trabajador"></button>';
    return s;
}
