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
        location.href = 'index.php?module=programas-capacitacion';
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        location.href = 'index.php?module=programas-capacitacion&id=' + $(this).data('id');
    });

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

        // Modal para visualizar programa
        var html = '<div class="row">'
            + '<div class="col-md-12">'
            + '<h3>Información del Programa de Capacitación</h3>'
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
            + '</ul>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<ul class="list-group">'
            + '<li class="list-group-item"><i class="fa fa-desktop mr-2"></i> <strong>Modalidad:</strong> ' + (p.modalidad || 'N/A') + '</li>'
            + '<li class="list-group-item"><i class="fa fa-clock-o mr-2"></i> <strong>Horas:</strong> ' + (p.horas || 'N/A') + ' horas</li>'
            + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Finalización:</strong> ' + (p.fecha_finalizacion || 'Programa Activo') + '</li>'
            + '</ul>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-12">'
            + '<div class="alert ' + (p.fecha_finalizacion ? 'alert-warning' : 'alert-success') + '">'
            + '<i class="fa ' + (p.fecha_finalizacion ? 'fa-clock-o' : 'fa-check-circle') + ' mr-2"></i> '
            + '<strong>Estado:</strong> ' + (p.fecha_finalizacion ? 'Programa Finalizado' : 'Programa Activo')
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
        $('#programaModal').modal('show');
    });

    // Handler for certificate generation (certificate icon)
    $('#table-panel').on('click', '.fa.fa-certificate', function () {
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
            alert('No se encontró la información del programa.');
            return;
        }

        var p = rowData;

        // Construir el HTML del certificado/reporte
        var formId = 'pdfForm_' + (p.id || Math.floor(Math.random() * 100000));
        var html = '<div class="certificate-container" style="max-width:700px; margin:0 auto;">'
            + '<form id="' + formId + '" method="POST" action="generate_training_certificate.php" target="_blank">'
            // Hidden inputs to send to the PDF generator
            + '<input type="hidden" name="id" value="' + (p.id || '') + '">'
            + '<input type="hidden" name="tema" value="' + (escapeHtml(p.tema || '')) + '">'
            + '<input type="hidden" name="dirigido_a" value="' + (escapeHtml(p.dirigido_a || '')) + '">'
            + '<input type="hidden" name="responsable" value="' + (escapeHtml(p.responsable || '')) + '">'
            + '<input type="hidden" name="fecha_estimada" value="' + (p.fecha_estimada || '') + '">'
            + '<input type="hidden" name="modalidad" value="' + (escapeHtml(p.modalidad || '')) + '">'
            + '<input type="hidden" name="horas" value="' + (p.horas || '') + '">'
            + '<input type="hidden" name="fecha_generacion" value="' + new Date().toISOString().split('T')[0] + '">'
            + '<div class="row">'
            + '<div class="col-md-12 text-center mb-3">'
            + '<h4 style="margin:0;">Certificado de Capacitación</h4>'
            + '<small class="text-muted">Programa de Desarrollo Profesional</small>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-md-12">'
            + '<div class="card" style="border:1px solid #e9ecef; box-shadow: none;">'
            + '<div class="card-body p-4">'
            + '<h5 style="font-weight:700; margin-bottom:15px; text-align:center;">' + (p.tema || 'N/A') + '</h5>'
            + '<table class="table table-sm" style="margin-bottom:0; font-size:1em;">'
            + '<tr><td><strong>Dirigido a:</strong></td><td>' + (p.dirigido_a || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Responsable:</strong></td><td>' + (p.responsable || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Modalidad:</strong></td><td>' + (p.modalidad || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Duración:</strong></td><td>' + (p.horas || 'N/A') + ' horas</td></tr>'
            + '<tr><td><strong>Fecha Programada:</strong></td><td>' + (p.fecha_estimada || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Fecha Generación:</strong></td><td>' + new Date().toLocaleDateString() + '</td></tr>'
            + '</table>'
            + '</div>'
            + '</div>'
            + '<div class="mt-3 text-center">'
            + '<button type="submit" class="btn btn-primary btn-sm" style="margin-right:8px;" onclick="document.getElementById(\'' + formId + '\').submit();">Generar Certificado PDF</button>'
            + '<button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Cerrar</button>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</form>'
            + '</div>';

        $('#modalBody').html(html);
        $('#programaModal').modal('show');
    });
    $('#table-panel').on('click', '.fa.fa-ban', function () {
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

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-sm fa fa-edit" title="Editar programa"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-ban" title="Finalizar programa"></button>';
    return s;
}

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
function formatoToolbar2(value, row) {
    var s = '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles del programa finalizado"></button>';
    return s;
}
