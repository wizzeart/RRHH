$(document).ready(function () {
    function notify(type, title, message, timer) {
        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
            $.niftyNoty({
                type: type || 'info',
                container: 'floating',
                title: title || '',
                message: message || '',
                timer: timer != null ? timer : 3000,
                closeBtn: true,
                focus: true
            });
        } else {
            // Fallback simple para garantizar feedback al usuario
            var text = (title ? (title + ': ') : '') + (message || '');
            try { alert(text); } catch (e) { console.warn('Notify:', text); }
        }
    }
    // Manejar clic en el botón de observaciones
    $('#table-panel').on('click', '.btn-observaciones', function () {
        var id = $(this).data('id');
        var observaciones = $(this).data('observaciones') || '';

        // Actualizar el formulario del modal
        $('#observacion_id').val(id);
        $('#observacion_texto').val(observaciones);

        // Mostrar el modal
        $('#modalObservaciones').modal('show');
    });

    // Manejar clic en el botón de guardar observación
    $('#btnGuardarObservacion').click(function () {
        var id = $('#observacion_id').val();
        var observaciones = $('#observacion_texto').val();
        formData = new FormData();
        formData.append('module', 'bolsas_empleos');
        formData.append('method', 'save');
        formData.append('action', 'update');
        formData.append('id', id);
        formData.append('observaciones', observaciones);
        // Enviar la petición para guardar
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#modalObservaciones').modal('hide');
                    $('#table-panel').bootstrapTable('refresh');

                    // Mostrar mensaje de éxito
                    notify('success', 'Éxito', 'Las observaciones han sido guardadas correctamente.', 3000);
                }
                else {
                    notify('danger', 'Error', 'Error al guardar las observaciones: ' + (d.message || 'Error desconocido'), 3000);
                }
            },
            error: function (xhr, status, error) {
                var errorMsg = 'Error al guardar las observaciones: ' + (error || 'Error desconocido');
                if (xhr.responseText) {
                    try {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        errorMsg = jsonResponse.message || errorMsg;
                    } catch (e) {
                        // If not JSON, show the response text directly
                        errorMsg += '\n' + xhr.responseText.substring(0, 200);
                    }
                }
                notify('danger', 'Error', errorMsg, 3000);
            }
        });
    });
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=bolsas_empleos';
    });


    // Handler for curriculum download/view button
    $('#table-panel').on('click', '.fa.fa-download', function () {
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

        if (!rowData || !rowData.curriculum || rowData.curriculum === '') {
            alert('No hay currículum disponible para esta postulación.');
            return;
        }

        // Abrir el currículum en una nueva ventana/pestaña
        window.open(rowData.curriculum, '_blank');
    });

    // Handler for contratar button -> redirect to trabajadores with prefilled params
    $('#table-panel').on('click', '.btn-contratar', function () {
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
            alert('No se pudo obtener los datos de la postulación.');
            return;
        }
        var params = new URLSearchParams();
        params.set('module', 'trabajadores');
        params.set('b_id', rowData.id || '');
        params.set('b_nombre', rowData.nombre || '');
        params.set('b_apellidos', rowData.apellidos || '');
        params.set('b_segundos_apellidos', rowData.segundos_apellidos || '');
        params.set('b_ci', rowData.ci_bolsa_empleo || '');
        params.set('b_curriculum', rowData.curriculum || '');
        params.set('b_cargo_postulado_id', rowData.cargo_postulado_id || '');
        params.set('b_telefono', rowData.telefono || '');
        params.set('b_email', rowData.email || '');
        params.set('b_fecha_registro', rowData.fecha_registro || '');
        params.set('b_observaciones', rowData.observaciones || '');
        window.location.href = 'index.php?' + params.toString();
    });

    $('#table-panel').on('click', '.btn-del', function () {


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
            alert('No se pudo obtener los datos de la postulación.');
            return;
        }

        if (!confirm('¿Está seguro de eliminar esta postulación?')) {
            return;
        }


        var params = new URLSearchParams();
        params.set('module', 'bolsas_empleos');
        params.set('id', rowData.id || '');
        params.set('method', 'del');
        params.set('row', idx || '');
        $.ajax({
            url: 'api-app.php', type: 'GET', data: params.toString(), dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#table-panel').bootstrapTable('refresh');

                }
            }
        });
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

// Format for Curriculum Download Column.
// =================================================================
function formatoToolbar(value, row) {
    var curriculumAvailable = row.curriculum && row.curriculum !== '';
    var btnClass = curriculumAvailable ? 'btn-warning' : 'btn-secondary';
    var btnDisabled = curriculumAvailable ? '' : 'disabled';
    var title = curriculumAvailable ? 'Descargar/Ver Currículum' : 'Sin currículum';

    var s = '';
    s += '<button data-id="' + row.id + '" class="btn ' + btnClass + ' btn-icon icon-sm fa fa-download" ' + btnDisabled + ' title="' + title + '"></button> ';
    s += '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-briefcase btn-contratar" title="Contratar"></button> ';
    s += '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-trash btn-del" title="Eliminar"></button>';

    return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}

function formatoObservaciones(value, row) {
    var hasObservations = value && value.trim() !== '';
    var btnClass = hasObservations ? 'btn-success' : 'btn-danger';
    var iconClass = hasObservations ? 'fa-check' : 'fa-remove';
    var title = hasObservations ? 'Ver/Editar observaciones' : 'Agregar observaciones';

    return '<button data-id="' + row.id + '" ' +
        'data-observaciones="' + (value || '') + '" ' +
        'class="btn ' + btnClass + ' btn-icon icon-sm fa ' + iconClass + ' btn-observaciones" ' +
        'title="' + title + '"></button>';
}

// Formateador para nombre completo (sin enlace)
function formatoNombreCompleto(value, row) {
    var nombre = row.nombre || '';
    var apellidos = row.apellidos || '';
    var nombreCompleto = (nombre + ' ' + apellidos).trim();
    return nombreCompleto || value;
}
