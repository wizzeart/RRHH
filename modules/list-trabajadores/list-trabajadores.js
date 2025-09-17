$(document).ready(function () {
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=trabajadores';
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
        var cmd = 'module=trabajadorescheckedid=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        location.href = 'index.php?module=trabajadores&id=' + $(this).data('id');
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=trabajadores&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                    } else {

                    }
                }
            });
        }
    });

    // Handler for 'view' (eye) button - show modal with full worker info
    $('#table-panel').on('click', '.fa.fa-eye', function () {
        // Try to get the row index from the DOM (bootstrap-table sets data-index on <tr>)
        var $tr = $(this).closest('tr');
        var idx = $tr.data('index');

        // Get all table data via bootstrapTable API
        var allData = $('#table-panel').bootstrapTable('getData');
        var rowData;
        if (typeof idx !== 'undefined' && allData && allData[idx]) {
            rowData = allData[idx];
        } else {
            // Fallback: try to find by data-id attribute
            var id = $(this).data('id');
            if (allData && allData.length) {
                rowData = allData.find(function (r) { return r.id == id || r.xusuario_id == id; });
            }
        }

        if (!rowData) {
            alert('No se encontró la información del trabajador.');
            return;
        }

        var t = rowData;
        var fotoHtml = '';
        if (t.foto) {
            // If the foto value is a relative path, you might need to adjust the prefix
            fotoHtml = '<img src="' + t.foto + '" alt="Foto del trabajador" style="max-width:100%; margin-bottom:10px;">';
        }
//---------------------------------------------------------------------------------------
        // Build the modal content fa-eye click
        var html = '<div class="row">'
            + '<div class="col-md-4 text-center">'
            + (fotoHtml || '<div class="alert alert-info">No hay foto disponible</div>')
            + '</div>'
            + '<div class="col-md-8">'
            + '<div class="card">'
            + '<div class="card-body">'
            + '<h4 class="card-title mb-4">' + (t.nombre || '') + ' ' + (t.apellidos || '') + '</h4>'
            + '<div class="row mb-3">'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-id-card mr-2"></i> <strong>CI:</strong> ' + (t.carnet_identidad || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-user mr-2"></i> <strong>Sexo:</strong> ' + (t.sexo || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-birthday-cake mr-2"></i> <strong>Edad:</strong> ' + (t.edad || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-phone mr-2"></i> <strong>Teléfono:</strong> ' + (t.telefono || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-envelope mr-2"></i> <strong>Email:</strong> ' + (t.email || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-map-marker mr-2"></i> <strong>Dirección:</strong> ' + (t.direccion || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-graduation-cap mr-2"></i> <strong>Nivel Educacional:</strong> ' + (t.nivel_educacional || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Contratación:</strong> ' + (t.fecha_contratacion || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Baja:</strong> ' + (t.fecha_baja || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-info-circle mr-2"></i> <strong>Estatus:</strong> ' + (t.estatus || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="alert ' + (t.trabajador_eliminado == 1 ? 'alert-danger' : 'alert-success') + '">'
            + '<i class="fa ' + (t.trabajador_eliminado == 1 ? 'fa-user-times' : 'fa-user-check') + ' mr-2"></i> '
            + '<strong>Estado:</strong> ' + (t.trabajador_eliminado == 1 ? 'Trabajador Eliminado' : 'Trabajador Activo')
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row mt-3">'
            + '<div class="col-md-6">'
            + '<div class="badge badge-info"><i class="fa fa-briefcase mr-1"></i> Cargo ID: ' + (t.cargos_id || 'N/A') + '</div>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<div class="badge badge-secondary"><i class="fa fa-database mr-1"></i> Bolsa Empleo ID: ' + (t.bolsa_empleo_id || 'N/A') + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>';

        $('#modalBody').html(html);
        $('#trabajadorModal').modal('show');
    });


});





// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-sm fa fa-edit"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-trash"></button>';
                

    return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
