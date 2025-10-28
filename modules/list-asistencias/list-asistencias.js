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
    return row.nombre + ' ' + row.apellidos ;//+ '<br><small class="text-muted">CI: ' + row.carnet_identidad + '</small>';
}

// Formateador para la columna de ausencia
function formatoAusencia(value, row) {
    if (value || row.ausencia == 1) {
        let tooltip = '';
        if (row.justificacion) {
            tooltip = ` data-toggle="tooltip" data-placement="top" title="${row.justificacion}"`;
        }
        return `<span class="label label-danger"${tooltip}>AUSENTE</span><br><small>${row.tipo_ausencia || ''}</small>`;
    }
    else if (row.tardanza == 1) {
        return '<span class="label label-success">PRESENTE</span><br><small>Tardanza</small>';
    }
    else {
        return '<span class="label label-success">PRESENTE</span>';
    }
}

// Formateador para las acciones
function operateFormatter(value, row, index) {
    // Escapar comillas dobles en el JSON para evitar problemas de sintaxis
    var rowData = JSON.stringify(row).replace(/"/g, '&quot;');
    return [
        '<button class="btn btn-icon icon-sm btn-info fa fa-edit" title="Editar" onclick="editarAsistencia(' + rowData + '); return false;">',
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
    $modal.find('#ausencia').prop('checked', row.ausencia === '1' || (row.tipo_ausencia !== null && row.tipo_ausencia !== ''));
    $modal.find('#tipo_ausencia').val(row.tipo_ausencia || '');
    $modal.find('#justificacion').val(row.justificacion || '');

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

// Mostrar/ocultar campo de justificación según el tipo de ausencia
$('#tipo_ausencia').change(function() {
    
});

function toggleTipoAusencia() {
    if ($('#ausencia').is(':checked')) {
        $('#tipo-ausencia-container').show();
        $('#tipo_ausencia').prop('required', true);
        $('#justificacion-container').show();
        $('#justificacion').prop('required', true);
    } else {
        $('#tipo-ausencia-container').hide();
        $('#tipo_ausencia').prop('required', false);
        $('#justificacion-container').hide();
        $('#justificacion').prop('required', false);
    }
}

function formatoHorasMes(value, row) {
    let horas = timeStringToSeconds(value);
    const horasDecimal = (horas / 3600).toFixed(1);
    return parseFloat(horasDecimal) + ' h / 192 h'; 
}

function formatoPorcentajeHorasMes(value, row) {
    let horas = timeStringToSeconds(value);
    const horasDecimal = (horas / 3600).toFixed(1);
    const porcentaje = (horasDecimal / 192) * 100;
    return parseFloat(porcentaje.toFixed(2)) + '%'; 
}

function timeStringToSeconds(timeStr) {
    if (!timeStr) return 0;
    const [hours, minutes, seconds] = timeStr.split(':').map(Number);
    return hours * 3600 + minutes * 60 + seconds;
}
function formatoHoras(value, row) {
    if (!row.hora_entrada || !row.hora_salida) {
        return '-';
    }
    try {
        // Convert time strings to seconds
        const entradaSegundos = timeStringToSeconds(row.hora_entrada);
        const salidaSegundos = timeStringToSeconds(row.hora_salida);
        
        // Calculate difference in seconds
        let diffSegundos = salidaSegundos - entradaSegundos;
        

        // If the result is negative, the times might be on different days
        if (diffSegundos < 0) {
            // Add 24 hours if the end time is on the next day
            diffSegundos += 86400; // 24 hours in seconds
            
            
        }
        
        const horasDecimal = (diffSegundos / 3600).toFixed(1);
        return parseFloat(horasDecimal) + ' h'; 
    } catch (e) {
        console.error('Error calculating time difference:', e);
        return '-';
    }
}

// Inicializar tooltips
$(document).ready(function() {
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });
});

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
    var trabajador = $('#filtrar-trabajador').val();
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

// Variable para almacenar el timeout de búsqueda
var searchTimeout;

// Función para buscar trabajadores
function buscarTrabajadores(termino) {
    if (termino.length < 2) {
        $('#resultados-busqueda').hide().empty();
        return;
    }

    // Cancelar búsqueda anterior si existe
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Configurar un nuevo timeout para la búsqueda
    searchTimeout = setTimeout(function() {
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: {
                module: 'trabajadores',
                method: 'list',
                termino: termino
            },
            dataType: 'json',
            success: function(response) {
                const resultadosOrdenados = response
                    .map(trabajador => {
                        // Crear un string de búsqueda que incluya todos los campos relevantes
                        const searchStr = (
                            (trabajador.nombre || '') + ' ' +
                            (trabajador.apellidos || '') + ' ' +
                            (trabajador.carnet_identidad || '') + ' ' +
                            (trabajador.cargo || '')
                        ).toLowerCase();
                        
                        // Calcular el índice de la primera coincidencia
                        const index = searchStr.indexOf(termino.toLowerCase());
                        
                        // Si no hay coincidencia, devolver un puntaje bajo
                        if (index === -1) return { ...trabajador, _score: 0 };
                        
                        // Calcular puntaje basado en:
                        // 1. Posición de la coincidencia (más cerca del inicio = mejor)
                        // 2. Longitud del término (términos más largos = mejor coincidencia)
                        const positionScore = 1 / (index + 1);
                        const lengthScore = termino.length / searchStr.length;
                        const score = positionScore * 0.7 + lengthScore * 0.3;
                        
                        return { ...trabajador, _score: score };
                    })
                    .filter(trabajador => trabajador._score > 0) // Filtrar resultados sin coincidencias
                    .sort((a, b) => b._score - a._score) // Ordenar por puntaje descendente
                    .map(({ _score, ...trabajador }) => trabajador); // Eliminar el campo _score del resultado final

                mostrarResultadosBusqueda(resultadosOrdenados);
            },
            error: function() {
                console.error('Error al buscar trabajadores');
            }
        });
    }, 300); // Esperar 300ms después de la última tecla
}

// Mostrar resultados de búsqueda
function mostrarResultadosBusqueda(resultados) {
    var $resultados = $('#resultados-busqueda');
    $resultados.empty();
    
    if (resultados.length === 0) {
        $resultados.append('<div class="list-group-item">No se encontraron resultados</div>');
    } else {
        resultados.forEach(function(trabajador) {
            $resultados.append(
                '<div class="list-group-item list-group-item-action" data-id="' + trabajador.id + '">' +
                '   <strong>' + trabajador.nombre + ' ' + trabajador.apellidos + '</strong><br>' +
                '   <small class="text-muted">' + (trabajador.cargo || '') + ' - CI: ' + trabajador.carnet_identidad + '</small>' +
                '</div>'
            );
        });
    }
    
    $resultados.show();
}

// Eventos para el buscador de trabajadores
$(document).on('input', '#buscar-trabajador', function() {
    var termino = $(this).val().trim();
    buscarTrabajadores(termino);
});

// Seleccionar un trabajador de los resultados
$(document).on('click', '#resultados-busqueda .list-group-item', function() {
    var id = $(this).data('id');
    var nombre = $(this).find('strong').text();
    
    $('#filtrar-trabajador').val(id);
    $('#buscar-trabajador').val(nombre);
    $('#resultados-busqueda').hide();
    
    // Opcional: Aplicar filtro automáticamente al seleccionar
    // $('#btn-filtrar').click();
});

// Limpiar búsqueda
$('#limpiar-busqueda').click(function() {
    $('#buscar-trabajador').val('');
    $('#filtrar-trabajador').val('');
    $('#resultados-busqueda').hide();
    // Opcional: Aplicar filtro automáticamente al limpiar
    // $('#btn-filtrar').click();
});

// Ocultar resultados al hacer clic fuera
$(document).on('click', function(e) {
    if (!$(e.target).closest('#buscar-trabajador, #resultados-busqueda').length) {
        $('#resultados-busqueda').hide();
    }
});

// Inicializar la tabla con los filtros por defecto
$(document).ready(function() {
    $('#table-panel').bootstrapTable('refresh', {
        url: buildFilterUrl()
    });
    
    // Asegurarse de que el campo de búsqueda esté vacío al cargar la página
    $('#buscar-trabajador').val('');
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

    $('#table-panel-registro').on('click', '.toggle-status', function () {
        var $button = $(this);
        var rowId = $button.data('id');
        var newValue = $button.hasClass('fa-check') ? '0' : '1';
        var $row = $button.closest('tr');
        var rowIndex = $row.data('index');
        
        // Update the button appearance immediately for better UX
        if ($button.hasClass('fa-check')) {
            $button.removeClass('btn-success fa-check').addClass('btn-warning fa-remove');
        } else {
            $button.removeClass('btn-warning fa-remove').addClass('btn-success fa-check');
        }

        // Make the AJAX call
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: {
                module: 'trabajadores',
                method: 'check',
                action: 'update',
                id: rowId,
                tipo_horario: newValue
            },
            success: function(response) {
                console.log('Tipo de horario actualizado:', response);
                // Update the row data in the table
                var $table = $('#table-panel-registro');
                var rowData = $table.bootstrapTable('getData')[rowIndex];
                rowData.tipo_horario = newValue;
                $table.bootstrapTable('updateRow', {
                    index: rowIndex,
                    row: rowData
                });
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar el tipo de horario:', error);
                // Revert the button state on error
                if (newValue === '1') {
                    $button.removeClass('btn-success fa-check').addClass('btn-danger fa-remove');
                } else {
                    $button.removeClass('btn-danger fa-remove').addClass('btn-success fa-check');
                }
            }
        });
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

function formatoTipoHorario(value, row) {
    //button check
    if (value == '0') {
        return '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
    } else {
        return '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    }
}
