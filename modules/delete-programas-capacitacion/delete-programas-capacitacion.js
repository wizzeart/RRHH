$(document).ready(function () {
    // UI alert helper (Bootstrap-like)
    function showAlert(type, title, message) {
        // type: 'success' | 'danger' | 'warning' | 'info'
        var $container = $('.panel-body').first();
        if ($container.length === 0) { $container = $('body'); }
        var html = '\n<div class="alert alert-' + type + ' alert-dismissible" role="alert" style="margin-bottom:12px;">\n'
                 + '  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n'
                 + '  <strong>' + (title || '') + '</strong> ' + (message || '') + '\n'
                 + '</div>';
        // remove previous alerts of same type to reduce clutter
        $container.find('.alert.alert-' + type).remove();
        $container.prepend(html);
        // auto dismiss after 5s
        setTimeout(function(){ $container.find('.alert').first().fadeOut(400, function(){ $(this).remove(); }); }, 5000);
    }
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('¿Estás seguro de finalizar este programa de capacitación? Se establecerá la fecha de finalización automáticamente.')) {
            var cmd = 'module=programas-capacitacion&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                        // Alerta visual en verde
                        showAlert('success', 'Finalizado:', 'El programa de capacitación ha sido finalizado correctamente con fecha de hoy.');
                        // Notificación opcional
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
                        }
                    } else {
                        // Alerta visual en rojo
                        showAlert('danger', 'Error:', d.msg || 'No se pudo finalizar el programa de capacitación.');
                        // Notificación opcional
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'danger',
                                container: 'floating',
                                title: 'Error',
                                message: d.msg || 'No se pudo finalizar el programa de capacitación.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        }
                    }
                },
                error: function() {
                    // Alerta visual en rojo
                    showAlert('danger', 'Error de Conexión:', 'Error de conexión al finalizar el programa de capacitación.');
                    // Notificación opcional
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
