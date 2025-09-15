$(document).ready(function () {
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=tarifa';
    });
    $('#table-panel').on('click', '.toggle-status', function () {
        var value = '';
        if ($(this).hasClass('fa-check') == true) {
            $(this).removeClass('btn-success');
            $(this).addClass('btn-danger');
            $(this).removeClass('fa-check');
            $(this).addClass('fa-remove');
            value = 'N';
        } else {
            $(this).removeClass('btn-danger');
            $(this).addClass('btn-success');
            $(this).removeClass('fa-remove');
            $(this).addClass('fa-check');
            value = 'S';
        }
        var cmd = 'module=tarifas&method=checked&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        location.href = 'index.php?module=tarifa&id=' + $(this).data('id');
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=tarifas&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
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
        return '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-lg fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-lg fa fa-remove toggle-status"></button>';
}



// Sample Format for Tracking Number Column.
// =================================================================
function toolbarFormatter(value, row) {
    if (value == 'toolbar')
        return '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-lg fa fa-edit"></button>\n<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-lg fa fa-trash"></button>';
}
