// Formateador para las fechas
function formatoFecha(value, row) {
    if (!value) return 'N/A';
    var date = new Date(value);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

// Formateador para el estado
function formatoEstado(value, row) {
    // Si el valor es numérico (0 o 1)
    if (value === 0 || value === 1) {
        var estado = value == 1 ? 'Asignado' : 'Retornado';
        var clase = value == 1 ? 'label label-success' : 'label label-info';
        return '<span class="' + clase + '">' + estado + '</span>';
    }
}

// Formateador para las opciones
function formatoToolbar(value, row) {
    var html = '<div class="btn-group">';
    
    // Botón de ver detalles
    html += '<button class="btn btn-info btn-icon icon-lg fa fa-eye view-recurso" ';
    html += 'data-id="' + row.id + '" title="Ver detalles"></button>';
    
    // Botón de editar
    html += '<button class="btn btn-primary btn-icon icon-lg fa fa-edit edit-recurso" ';
    html += 'data-id="' + row.id + '" title="Editar"></button>';
    
    // Botón de eliminar
    html += '<button class="btn btn-danger btn-icon icon-lg fa fa-trash delete-recurso" ';
    html += 'data-id="' + row.id + '" title="Eliminar"></button>';
    
    html += '</div>';
    return html;
}

$(document).ready(function () {
    // Botón para agregar nuevo recurso
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=gestion-recursos&method=new';
    });

    // Manejador para el botón de ver detalles
    $('#table-panel').on('click', '.view-recurso', function () {
        var id = $(this).data('id');
        var $table = $('#table-panel');
        var rowData = $table.bootstrapTable('getRowByUniqueId', id);
        
        if (!rowData) {
            alert('No se encontró la información del recurso.');
            return;
        }
        
        // Construir el HTML del modal con los detalles
        var html = '<div class="row">';
        html += '<div class="col-md-12">';
        html += '<table class="table table-bordered">';
        
        // Función para agregar una fila a la tabla
        function addRow(label, value) {
            return '<tr><td class="active" style="width:30%;"><strong>' + label + '</strong></td><td>' + (value || 'N/A') + '</td></tr>';
        }
        
        // Agregar detalles del recurso
        html += addRow('Recurso', rowData.nombre);
        html += addRow('Trabajador', rowData.nombre_trabajador || 'No asignado');
        html += addRow('Estado', formatoEstado(rowData.estado, rowData));
        html += addRow('Fecha de Entrega', formatoFecha(rowData.fecha_entrega_a_t));
        html += addRow('Fecha de Devolución', formatoFecha(rowData.fecha_entrega_a_rh) || 'Pendiente');
        
        html += '</table>';
        html += '</div>';
        html += '</div>';
        
        // Mostrar el modal
        $('#modalBody').html(html);
        $('#recursoModal').modal('show');
    });

    // Manejador para el botón de editar
    $('#table-panel').on('click', '.edit-recurso', function () {
        var id = $(this).data('id');
        location.href = 'index.php?module=gestion-recursos&method=edit&id=' + id;
    });

    // Manejador para el botón de eliminar
    $('#table-panel').on('click', '.delete-recurso', function () {
        var id = $(this).data('id');
        
        bootbox.confirm({
            message: '¿Está seguro de que desea eliminar este recurso?',
            buttons: {
                confirm: {
                    label: 'Sí, eliminar',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Cancelar',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (result) {
                    // Realizar la petición de eliminación
                    $.ajax({
                        url: 'api-app.php',
                        type: 'POST',
                        data: {
                            module: 'recursos',
                            method: 'delete',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Recargar la tabla
                                $('#table-panel').bootstrapTable('refresh');
                                // Mostrar mensaje de éxito
                                bootbox.alert('El recurso ha sido eliminado correctamente.');
                            } else {
                                bootbox.alert('Error al eliminar el recurso: ' + (response.message || 'Error desconocido'));
                            }
                        },
                        error: function() {
                            bootbox.alert('Error al conectar con el servidor. Por favor, intente nuevamente.');
                        }
                    });
                }
            }
        });
    });

    // Función para formatear la fecha en la búsqueda
    $.fn.bootstrapTable.defaults.formatSearch = function(text) {
        return text ? text.toString().toLowerCase() : '';
    };
});