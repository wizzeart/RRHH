$(document).ready(function () {
    // Handler for program details (eye icon)
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
            alert('No se encontró la información del programa.');
            return;
        }

        var p = rowData;

        // Modal para programa finalizado
        var html = '<div class="row">'
            + '<div class="col-md-12">'
            + '<h3>Información del Programa Finalizado</h3>'
            + '<div class="card">'
            + '<div class="card-body">'
            + '<h4 class="card-title mb-4">' + (p.tema || 'N/A') + '</h4>'
            + '<div class="row mb-3">'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-graduation-cap mr-2"></i> <strong>Tema:</strong> ' + (p.tema || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-users mr-2"></i> <strong>Dirigido a:</strong> ' + (p.dirigido_a || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-user mr-2"></i> <strong>Responsable:</strong> ' + (p.responsable || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Estimada:</strong> ' + (p.fecha_estimada || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Finalización:</strong> ' + (p.fecha_finalizacion || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-desktop mr-2"></i> <strong>Modalidad:</strong> ' + (p.modalidad || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-clock-o mr-2"></i> <strong>Horas:</strong> ' + (p.horas || 'N/A') + ' horas</li>'
            + '</ul>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="alert alert-warning">'
            + '<i class="fa fa-clock-o mr-2"></i> '
            + '<strong>Estado:</strong> Programa de Capacitación Finalizado'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row mt-3">'
            + '<div class="col-md-12">'
            + '<div class="badge badge-secondary"><i class="fa fa-hashtag mr-1"></i> ID Programa: ' + (p.id || 'N/A') + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>';

        $('#modalBody').html(html);
        $('#programaFinalizadoModal').modal('show');
    });
});

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================

// Sample Format for Tracking Number Column - Only View Button for finalized programs
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles del programa finalizado"></button>';
    return s;
}
