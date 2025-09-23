$(document).ready(function () {
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('¿Estás seguro de finalizar este programa de capacitación? Se establecerá la fecha de finalización automáticamente.')) {
            var cmd = 'module=programas-capacitacion&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
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
                                message: 'El programa de capacitación ha sido finalizado correctamente con fecha de hoy.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Programa de capacitación finalizado correctamente.');
                        }
                    } else {
                        // Mostrar notificación de error
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'danger',
                                container: 'floating',
                                title: 'Error',
                                message: 'No se pudo finalizar el programa de capacitación.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Error al finalizar el programa de capacitación.');
                        }
                    }
                },
                error: function() {
                    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                        $.niftyNoty({
                            type: 'danger',
                            container: 'floating',
                            title: 'Error',
                            message: 'Error de conexión al finalizar el programa de capacitación.',
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
    var s = '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-trash" title="Finalizar programa"></button>';
    return s;
}
