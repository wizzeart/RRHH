$(document).ready(function () {
    // Helper to escape HTML for safe insertion into hidden inputs
    function escapeHtml(str) {
        if (typeof str !== 'string') return str || '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=subcontratos';
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        location.href = 'index.php?module=subcontratos&id=' + $(this).data('id');
    });

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

        // Modal para visualizar subcontrato
        var html = '<div class="row">'
            + '<div class="col-md-12">'
            + '<h3>Información del Subcontrato</h3>'
            + '<div class="card">'
            + '<div class="card-body">'
            + '<h4 class="card-title mb-4">' + (s.persona_nombre || 'N/A') + '</h4>'
            + '<div class="row mb-3">'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-user mr-2"></i> <strong>Nombre:</strong> ' + (s.persona_nombre || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-tag mr-2"></i> <strong>Estatus:</strong> ' + (s.estatus || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-building mr-2"></i> <strong>Entidad:</strong> ' + (s.entidad_representada || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Inicio:</strong> ' + (s.fecha_inicio || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar-times mr-2"></i> <strong>Fecha Fin:</strong> ' + (s.fecha_fin || 'Activo') + '</li>'
            + '</ul>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-map-marker mr-2"></i> <strong>Áreas Acceso:</strong> ' + (s.areas_acceso || 'No definidas') + '</li>'
            + '</ul>'
            + '<div class="card mt-3">'
            + '<div class="card-header">'
            + '<h6><i class="fa fa-briefcase mr-2"></i>Servicio/Objeto del Contrato</h6>'
            + '</div>'
            + '<div class="card-body">'
            + '<p class="card-text">' + (s.servicio_objeto || 'No especificado') + '</p>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="alert ' + (s.fecha_fin ? 'alert-warning' : 'alert-success') + '">'
            + '<i class="fa ' + (s.fecha_fin ? 'fa-clock-o' : 'fa-check-circle') + ' mr-2"></i> '
            + '<strong>Estado:</strong> ' + (s.fecha_fin ? 'Contrato Finalizado' : 'Contrato Activo')
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
        $('#subcontratoModal').modal('show');
    });

    // Handler for access pass generation (tablet icon) - same behavior as workers
    $('#table-panel').on('click', '.fa.fa-tablet', function () {
        var $tr = $(this).closest('tr');
        var idx = $tr.data('index');

        var allData = $('#table-panel').bootstrapTable('getData');
        var rowData;
        if (typeof idx !== 'undefined' && allData && allData[idx]) {
            rowData = allData[idx];
        } else {
            var id = $(this).data('id');
            if (allData && allData.length) {
                rowData = allData.find(function (r) { return r.id == id; });
            }
        }

        if (!rowData) {
            alert('No se encontró la información del subcontrato.');
            return;
        }

        // Debug: ver los datos que recibimos
        console.log('Datos del subcontrato:', rowData);

        var s = rowData;

        // Construir el HTML del modal como tarjeta de identificación
        // Usamos un formulario oculto para enviar los datos al generador de PDF.
        var formId = 'pdfForm_' + (s.id || Math.floor(Math.random() * 100000));
        var html = '<div class="id-card-container" style="max-width:700px; margin:0 auto;">'
            + '<form id="' + formId + '" method="POST" action="generate_subcontract_pdf.php" target="_blank">'
            // Hidden inputs to send to the PDF generator
            + '<input type="hidden" name="id" value="' + (s.id || '') + '">'
            + '<input type="hidden" name="persona_nombre" value="' + (escapeHtml(s.persona_nombre || '')) + '">'
            + '<input type="hidden" name="estatus" value="' + (escapeHtml(s.estatus || '')) + '">'
            + '<input type="hidden" name="entidad_representada" value="' + (escapeHtml(s.entidad_representada || '')) + '">'
            + '<input type="hidden" name="servicio_objeto" value="' + (escapeHtml(s.servicio_objeto || '')) + '">'
            + '<input type="hidden" name="fecha_inicio" value="' + (s.fecha_inicio || '') + '">'
            + '<input type="hidden" name="fecha_fin" value="' + (s.fecha_fin || '') + '">'
            + '<input type="hidden" name="areas_acceso" value="' + (escapeHtml(s.areas_acceso || '')) + '">'
            + '<input type="hidden" name="fecha_generacion" value="' + new Date().toISOString().split('T')[0] + '">'
            + '<div class="row">'
            + '<div class="col-md-12 text-center mb-3">'
            + '<h4 style="margin:0;">Pase de Acceso - Subcontratista</h4>'
            + '<small class="text-muted">Control de Acceso Temporal</small>'
            + '</div>'
            + '</div>'
            + '<div class="row align-items-center">'
            + '<div class="col-md-4 text-center">'
            + '<div class="photo-box" style="width:200px; height:260px; margin:0 auto; border:2px solid #e9ecef; border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fff;">'
            + '<div style="padding:10px; text-align:center;">'
            + '<i class="fa fa-user fa-5x text-muted"></i>'
            + '<br><small class="text-muted">Foto del Subcontratista</small>'
            + '</div>'
            + '</div>'
            + '<div style="margin-top:10px;">'
            + '<span class="badge badge-info">ID: ' + (s.id || 'N/A') + '</span>'
            + '</div>'
            + '</div>'
            + '<div class="col-md-8">'
            + '<div class="card" style="border:1px solid #e9ecef; box-shadow: none;">'
            + '<div class="card-body p-3">'
            + '<h5 style="font-weight:700; margin-bottom:6px;">' + (s.persona_nombre || 'N/A') + '</h5>'
            + '<p style="margin:0 0 8px 0; color:#6c757d;">' + (s.estatus || 'N/A') + '</p>'
            + '<table class="table table-sm" style="margin-bottom:0; font-size:0.95em;">'
            + '<tr><td><strong>Entidad</strong></td><td>' + (s.entidad_representada || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Áreas</strong></td><td>' + (s.areas_acceso || 'No definidas') + '</td></tr>'
            + '<tr><td><strong>Fecha Gen.</strong></td><td>' + new Date().toLocaleDateString() + '</td></tr>'
            + '<tr><td><strong>Vigencia</strong></td><td>' + (s.fecha_inicio || 'N/A') + ' - ' + (s.fecha_fin || 'Indefinido') + '</td></tr>'
            + '</table>'
            + '</div>'
            + '</div>'
            + '<div class="mt-2">'
            + '<button type="submit" class="btn btn-primary btn-sm" style="margin-right:8px;" onclick="document.getElementById(\'' + formId + '\').submit();">Generar PDF</button>'
            + '<button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Cerrar</button>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</form>'
            + '</div>';

        // Actualizar el contenido del modal
        $('#modalBody').html(html);
        $('#subcontratoModal').modal('show');
    });
});

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-sm fa fa-edit" title="Editar subcontrato"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-warning btn-icon icon-sm fa fa-tablet" title="Pase de acceso"></button>';

    return s;
}
