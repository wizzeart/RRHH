$(document).ready(function () {
    var $table = $('#table-panel');
    function applyFilters() {
        var cargoId = $('#filterCargo').val();
        var deptoId = $('#filterDepartamento').val();

        // Build the URL with filters
        var url = 'api-app.php?module=trabajadores&method=list-filter';
        if (cargoId) url += '&cargo_id=' + cargoId;
        if (deptoId) url += '&departamento_id=' + deptoId;
        url += '&trabajador_eliminado=true';

        // Reload table with new URL
        $table.bootstrapTable('refresh', {
            url: url,
            silent: true
        });
    }

    // Filter button click handler
    $('#btn-filter').on('click', function () {
        applyFilters();
    });

    // Reset button click handler
    $('#btn-reset').on('click', function () {
        // Clear all filters
        $('#filterCargo, #filterDepartamento').val('');
        $('#buscar-trabajador').val('');
        $('#resultados-busqueda').hide().empty();

        // Reset the table to show all records
        $table.bootstrapTable('filterBy', {});
        $table.bootstrapTable('refresh');
    });

    // Handler for worker details (eye icon)
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
                rowData = allData.find(function (r) { return r.id == id; });
            }
        }

        if (!rowData) {
            alert('No se encontró la información del trabajador.');
            return;
        }

        var t = rowData;
        var fotoHtml = '';
        if (t.foto) {
            fotoHtml = '<img src="' + t.foto + '" alt="Foto del trabajador" style="max-width:100%; margin-bottom:10px;" class="img-thumbnail">';
        }

        // Modal para trabajador dado de baja
        var html = '<div class="row">'
            + '<div class="col-md-4 text-center">'
            + (fotoHtml || '<div class="alert alert-info">No hay foto disponible</div>')
            + '</div>'
            + '<div class="col-md-8">'
            + '<h3>Información de Trabajador Dado de Baja</h3>'
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
            + '<li class="list-group-item"><i class="fa fa-briefcase mr-2"></i> <strong>Cargo:</strong> ' + (t.cargo_nombre || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Contratación:</strong> ' + (t.fecha_contratacion || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Baja:</strong> ' + (t.fecha_baja || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="alert alert-warning">'
            + '<i class="fa fa-user-times mr-2"></i> '
            + '<strong>Estado:</strong> Trabajador Dado de Baja'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row mt-3">'
            + '<div class="col-md-12">'
            + '<div class="badge badge-secondary"><i class="fa fa-user mr-1"></i> ID Trabajador: ' + (t.id || 'N/A') + '</div>'
            + '<div class="badge badge-info"><i class="fa fa-briefcase mr-1"></i> ID Cargo: ' + (t.cargos_id || 'N/A') + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>';

        $('#modalBody').html(html);
        $('#trabajadorBajaModal').modal('show');
    });
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=trabajadores';
    });
});

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================

// Sample Format for Tracking Number Column - Only View Button for terminated workers
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles del trabajador dado de baja"></button>';
    return s;
}

function formatoNombreCompleto(value, row) {
    var nombreCompleto = (row.nombre || '') + ' ' + (row.apellidos || '');
    if (value && !nombreCompleto.trim()) { nombreCompleto = value; }
    return nombreCompleto.trim();
}
