$(document).ready(function () {
    // Handler for subcontract details (eye icon)
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
            alert('No se encontró la información del subcontrato.');
            return;
        }

        var s = rowData;

        // Modal para subcontrato finalizado
        var html = '<div class="row">'
            + '<div class="col-md-12">'
            + '<h3>Información del Subcontrato Finalizado</h3>'
            + '<div class="card">'
            + '<div class="card-body">'
            + '<h4 class="card-title mb-4">' + (s.persona_nombre || 'N/A') + '</h4>'
            + '<div class="row mb-3">'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-user mr-2"></i> <strong>Nombre:</strong> ' + (s.persona_nombre || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-id-card mr-2"></i> <strong>CI:</strong> ' + (s.carnet_identidad || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-tag mr-2"></i> <strong>Estatus:</strong> ' + (s.estatus || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-building mr-2"></i> <strong>Entidad:</strong> ' + (s.entidad_representada || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Inicio:</strong> ' + (s.fecha_inicio || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Fin:</strong> ' + (s.fecha_fin || 'N/A') + '</li>'
            + '</ul>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-map-marker mr-2"></i> <strong>Áreas Acceso:</strong> ' + (s.areas_acceso || 'No definidas') + '</li>'
            + '</ul>'
            + '<div class="card mt-3">'
            + '<div class="card-header">'
            + '<li class="list-group-item"><i class="fa fa-briefcase mr-2"></i> Servicio/Objeto del Contrato</li>'
            + '</div>'
            + '<div class="card-body">'
            + '<p class="card-text">' + (s.servicio_objeto || 'No especificado') + '</p>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="alert alert-warning">'
            + '<i class="fa fa-clock-o mr-2"></i> '
            + '<strong>Estado:</strong> Subcontrato Finalizado'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row mt-3">'
            + '<div class="col-md-12">'
            + '<div class="badge badge-secondary"><i class="fa fa-hashtag mr-1"></i> ID Subcontrato: ' + (s.id || 'N/A') + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>';

        $('#modalBody').html(html);
        $('#subcontratoBajaModal').modal('show');
    });
});

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================

// Sample Format for Tracking Number Column - Only View Button for terminated subcontracts
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles del subcontrato finalizado"></button>';
    return s;
}
