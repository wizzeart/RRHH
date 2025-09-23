$(document).ready(function () {
    // Handler for history details (eye icon)
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
            alert('No se encontró la información del registro histórico.');
            return;
        }

        var h = rowData;

        // Modal para detalles del historial
        var html = '<div class="row">'
            + '<div class="col-md-12">'
            + '<h3>Detalles del Registro Histórico</h3>'
            + '<div class="card">'
            + '<div class="card-body">'
            + '<div class="row mb-3">'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha:</strong> ' + (h.xdate || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-database mr-2"></i> <strong>Entidad:</strong> ' + (h.xentity || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-cog mr-2"></i> <strong>Acción:</strong> ' + (h.xaction || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-user mr-2"></i> <strong>Usuario:</strong> ' + (h.usuario_nombre || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-hashtag mr-2"></i> <strong>ID Registro:</strong> ' + (h.xid || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-server mr-2"></i> <strong>IP:</strong> ' + (h.xip || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-eye mr-2"></i> <strong>Visto:</strong> ' + (h.xvisto == '1' ? 'Sí' : 'No') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Visto:</strong> ' + (h.xvisto_date || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="card">'
            + '<div class="card-header">'
            + '<h5><i class="fa fa-file-text mr-2"></i>Observaciones</h5>'
            + '</div>'
            + '<div class="card-body">'
            + '<p class="card-text">' + (h.xobs || 'Sin observaciones') + '</p>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row mt-3">'
            + '<div class="col-md-12">'
            + '<div class="badge badge-secondary"><i class="fa fa-key mr-1"></i> ID Histórico: ' + (h.id || 'N/A') + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>';

        $('#modalBody').html(html);
        $('#historialModal').modal('show');
    });
});

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================

// Sample Format for Tracking Number Column - Only View Button for history
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles del registro histórico"></button>';
    return s;
}
