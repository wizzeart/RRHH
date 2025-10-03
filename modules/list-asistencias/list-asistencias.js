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

    

    // Handler for 'view' (eye) button - show modal with full worker info
    // Handler for access control pass (tablet icon)
});


function formatoNombreCompleto(value, row) {
    return row.nombre + ' ' + row.apellidos + '<br><small class="text-muted">CI: ' + row.carnet_identidad + '</small>';
}

// Formateador para la columna de ausencia
function formatoAusencia(value, row) {
    if (value || row.ausencia == 1) {
        return '<span class="label label-danger">AUSENTE</span><br><small>' + (row.tipo_ausencia || '') + '</small>';
    } else {
        return '<span class="label label-success">PRESENTE</span>';
    }
}

// Formateador para las acciones
function operateFormatter(value, row, index) {
    // Escapar comillas dobles en el JSON para evitar problemas de sintaxis
    var rowData = JSON.stringify(row).replace(/"/g, '&quot;');
    return [
        '<button class="btn btn-xs btn-primary" title="Editar" onclick="editarAsistencia(' + rowData + '); return false;">',
        '<i class="fa fa-edit"></i>',
        '</button>'
    ].join('');
}

// Eventos para los botones de acción
window.operateEvents = {
    'click .edit': function (e, value, row, index) {
        editarAsistencia(row);
    },
    'click .remove': function (e, value, row, index) {
        eliminarAsistencia(row.id);
    }
};

// Función para abrir el modal de edición
window.editarAsistencia = function(row) {
    console.log('Editando asistencia:', row); // Para depuración
    
    // Asegurarse de que row es un objeto
    if (typeof row === 'string') {
        try {
            row = JSON.parse(row.replace(/&quot;/g, '"'));
        } catch (e) {
            console.error('Error al parsear datos de la fila:', e);
            return;
        }
    }
    
    // Mostrar el modal
    var $modal = $('#modalAsistencia');
    
    // Llenar el formulario con los datos
    $modal.find('#asistencia_id').val(row.id || '');
    $modal.find('#trabajador_id').val(row.trabajador_id || '');
    $modal.find('#fecha').val(row.fecha || new Date().toISOString().split('T')[0]);
    $modal.find('#hora_entrada').val(row.hora_entrada || '');
    $modal.find('#hora_salida').val(row.hora_salida || '');
    $modal.find('#ausencia').prop('checked', row.ausencia == 1 || row.ausencia === '1' || row.ausencia === true);
    $modal.find('#tipo_ausencia').val(row.tipo_ausencia || '');
    
    // Actualizar título del modal
    $modal.find('.modal-title').text(row.id ? 'Editar Asistencia' : 'Nueva Asistencia');
    
    // Mostrar/ocultar campo de tipo de ausencia
    toggleTipoAusencia();
    
    // Mostrar el modal
    $modal.modal('show');
};

// Mostrar/ocultar campo de tipo de ausencia
$('#ausencia').change(function() {
    toggleTipoAusencia();
});

function toggleTipoAusencia() {
    if ($('#ausencia').is(':checked')) {
        $('#tipo-ausencia-container').show();
        $('#tipo_ausencia').prop('required', true);
    } else {
        $('#tipo-ausencia-container').hide();
        $('#tipo_ausencia').prop('required', false);
    }
}

// Guardar asistencia
$('#btn-guardar-asistencia').click(function() {
    var formData = $('#formAsistencia').serialize();
    var method = 'save';
    
    $.ajax({
        url: 'api-app.php?module=asistencias&method=' + method,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log(response);
            if (response.status == 1) {
                $('#modalAsistencia').modal('hide');
                $('#table-panel').bootstrapTable('refresh');
                notify('success', 'Éxito', response.msg || 'Registro guardado correctamente');
            } else {
                notify('danger', 'Error', response.msg || 'Error al guardar el registro');
            }
        },
        error: function() {
            notify('danger', 'Error', 'Error al conectar con el servidor');
        }
    });
});

// Inicializar select2 para el buscador de trabajadores
if ($.fn.select2) {
    $('.select2').select2({
        placeholder: 'Seleccione un trabajador',
        allowClear: true
    });
}

// Mostrar/ocultar el filtro de tipo de ausencia según el estado
$('#filtro-estado').change(function() {
    if ($(this).val() === '2') { // Si es 'Ausente'
        $('#filtro-tipo-ausencia-container').show();
    } else {
        $('#filtro-tipo-ausencia-container').hide();
    }
});

// Función para construir la URL con los filtros
function buildFilterUrl() {
    var params = [];
    var fechaDesde = $('#fecha-desde').val();
    var fechaHasta = $('#fecha-hasta').val();
    var trabajador = $('#filtro-trabajador').val();
    var estado = $('#filtro-estado').val();
    var tipoAusencia = $('#filtro-tipo-ausencia').val();

    if (fechaDesde) params.push('fecha_desde=' + encodeURIComponent(fechaDesde));
    if (fechaHasta) params.push('fecha_hasta=' + encodeURIComponent(fechaHasta));
    if (trabajador) params.push('trabajador_id=' + encodeURIComponent(trabajador));
    if (estado) params.push('estado=' + encodeURIComponent(estado));
    if (tipoAusencia && estado === '2') params.push('tipo_ausencia=' + encodeURIComponent(tipoAusencia));

    return 'api-app.php?module=asistencias&method=list-filter' + (params.length > 0 ? '&' + params.join('&') : '');
}

// Filtrar datos
$('#btn-filtrar').click(function() {
    $('#table-panel').bootstrapTable('refresh', {
        url: buildFilterUrl()
    });
});

// Inicializar la tabla con los filtros por defecto
$(document).ready(function() {
    $('#table-panel').bootstrapTable('refresh', {
        url: buildFilterUrl()
    });
});


// Nuevo registro de asistencia
$('#btn-registrar-asistencia').click(function() {
    // Limpiar el formulario
    $('#formAsistencia')[0].reset();
    $('#asistencia_id').val('');
    $('#fecha').val(new Date().toISOString().split('T')[0]);
    $('#trabajador_id').val('');
    $('#modalAsistenciaLabel').text('Registrar Nueva Asistencia');
    $('#modalAsistencia').modal('show');
});

// Inicialización
$(document).ready(function() {
    // Configuración de la tabla
    $('#table-panel').bootstrapTable({
        // Configuraciones adicionales si son necesarias
    });
    
    // Configurar fecha actual por defecto
    $('#fecha-filtro').val(new Date().toISOString().split('T')[0]);
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
    var s = '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-sm fa fa-edit" title="Editar trabajador"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-warning btn-icon icon-sm fa fa-tablet" title="Pase de acceso"></button>';

    return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
