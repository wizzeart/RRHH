$(document).ready(function () {
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=notificacion';
    });
    $('#table-panel').on('click', '.toggle-status', function () {
        if (confirm("Desea confirmar el envío de esta notificación? Es posible que por el volumen de avisos tarde el proceso en enviar todos los avisos. No cambie de pantalla hasta que no reciba un aviso de que el proceso de notificación ha sido enviado correctamente.")) {
            if ($(this).hasClass('fa-remove') == true) {
                var cmd = 'module=notificaciones&method=send-notify&id=' + $(this).data('id');
                $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                    success: function (d) {
                        if (d.status == 1) {
                            $('button[data-id=' + d.id + '].toggle-status').removeClass('btn-danger');
                            $('button[data-id=' + d.id + '].toggle-status').addClass('btn-success');
                            $('button[data-id=' + d.id + '].toggle-status').removeClass('fa-remove');
                            $('button[data-id=' + d.id + '].toggle-status').addClass('fa-check');
                            $('button[data-id=' + d.id + '].toggle-status').removeClass('toggle-status');
                            alert('Notificación envidad satisfactoriamente');
                        }
                    }
                });
            }
        }
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        location.href = 'index.php?module=notificacion&id=' + $(this).data('id');
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=notificaciones&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                    }
                }
            });
        }
    });
});




// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function statusFormatter(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-lg fa fa-check"></button>';
    else
        return '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-lg fa fa-remove toggle-status"></button>';
}



// Sample Format for Tracking Number Column.
// =================================================================
function toolbarFormatter(value, row) {
    if (value == 'toolbar')
        return '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-lg fa fa-edit"></button>\n<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-lg fa fa-trash"></button>';
}

function typeFormatter(value, row) {
    switch (value) {
        case '0':
            return '<div class="label label-table label-success">Todos</div>';
            break;
        case '1':
            return '<div class="label label-table label-info">Usuario: ' + row.usuario + '</div>';
            break;
        case 'MU':
            return '<div class="label label-table label-danger">Murcia</div>';
            break;
        case 'AL':
            return '<div class="label label-table label-danger">Altea</div>';
            break;
        case 'BE':
            return '<div class="label label-table label-danger">Benidorm</div>';
            break;
        case 'PO':
            return '<div class="label label-table label-danger">Ponferrada</div>';
            break;
        case 'MA':
            return '<div class="label label-table label-danger">Majadahonda</div>';
            break;
    }
}
