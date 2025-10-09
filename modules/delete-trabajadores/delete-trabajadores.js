$(document).ready(function () {
    // Delegar el clic sobre el botón con data-id (independiente del ícono)
    $('#table-panel').on('click', 'button[data-id], .fa.fa-trash, .fa.fa-ban', function () {
        var $btn = $(this).is('button') ? $(this) : $(this).closest('button[data-id]');
        if ($btn.length === 0) return;
        // Abrir modal de confirmación y almacenar datos temporales
        var rowIndex = $btn.parent().parent().data('index');
        var id = $btn.data('id');
        $('#confirm-delete-id').val(id);
        $('#confirm-delete-row').val(rowIndex);
        $('#confirm-delete-text').val('');
        $('#confirm-delete-help').hide();
        $('#confirmDeleteModal').modal('show');
    });

    // Confirmar eliminación desde el modal
    $('#btn-confirm-delete').on('click', function () {
        var typed = ($('#confirm-delete-text').val() || '').trim();
        if (typed !== 'ELIMINAR') {
            $('#confirm-delete-help').show();
            return;
        }

        var id = $('#confirm-delete-id').val();
        var rowIndex = $('#confirm-delete-row').val();
        var cmd = 'module=trabajadores&method=del&id=' + encodeURIComponent(id) + '&row=' + encodeURIComponent(rowIndex);

        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: cmd,
            dataType: 'json',
            success: function (d) {
                $('#confirmDeleteModal').modal('hide');
                if (d.status == 1) {
                    $('tr[data-index="' + d.row + '"]').fadeOut('slow');
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
                        alert('No se pudo dar de baja al trabajador.');
                    }
                }
            },
            error: function () {
                $('#confirmDeleteModal').modal('hide');
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
