// Formateador para las fechas
function formatoFecha(value, row) {
    if (!value) 
        return 'N/A';
    return value;
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
    
    
    // Botón de editar
    html += '<button class="btn btn-info btn-icon icon-sm fa fa-edit edit-recurso" ';
    html += 'data-id="' + row.id + '" title="Editar"> </button> ';
    
    
    // Botón de ver detalles
    html += '<button class="btn btn-success btn-icon icon-sm fa fa-eye view-recurso" ';
    html += 'data-id="' + row.id + '" title="Ver detalles"></button> ';
    // Botón de descargar
    html += '<button class="btn btn-warning btn-icon icon-sm fa fa-download download-recurso" ';
    html += 'data-id="' + row.id + '" title="Descargar"></button>';
    // Botón de eliminar
    html += '<button class="btn btn-danger btn-icon icon-sm fa fa-trash delete-recurso" ';
    html += 'data-id="' + row.id + '" title="Eliminar"></button>';

    html += '</div>';
    return html;
}
function descargar_acta(idRecurso) {
    // Ruta donde se guardó el archivo (debe coincidir con la ruta en PHP)
    var rutaArchivo = '/docs/actas_guardadas/acta_entrega_' + idRecurso + '.pdf';
    
    // Crear un enlace temporal
    var link = document.createElement('a');
    link.href = rutaArchivo;
    link.target = '_blank';
    link.download = 'acta_entrega_' + idRecurso + '.pdf';
    
    // Simular clic en el enlace
    document.body.appendChild(link);
    link.click();
    
    // Limpiar después de la descarga
    setTimeout(function() {
        document.body.removeChild(link);
        window.URL.revokeObjectURL(link.href);
    }, 100);
}

$(document).ready(function () {
    $('#recursoForm').on('submit', function(e) {
        $('#btn-save-recurso').prop('disabled', true);
        e.preventDefault();
        var recurso = $('#f-recurso').val();
        var marca = $('#f-marca').val();
        var modelo = $('#f-modelo').val();
        var color = $('#f-color').val();
        var otros_recursos = $('#f-otros').val();
        var fecha_entrega_a_t = $('#f-fecha-entrega').val();
        var fecha_entrega_a_rh = $('#f-fecha-rh').val();
        if($('#f-trabajador_id-rec').val()){
            var trabajador_id = $('#f-trabajador_id-rec').val();
        }else{
            var trabajador_id = $('#f-id').val();
        }
        var id_recurso = $('#f-id-rec').val();
        var formData = new FormData();
        if(id_recurso){
            formData.append('id', id_recurso);
            formData.append('action', 'update');
        }
        formData.append('trabajador_id', trabajador_id);
        formData.append('nombre', recurso);
        formData.append('marca', marca);
        formData.append('modelo', modelo);
        formData.append('color', color);
        formData.append('otros_recursos', otros_recursos);
        formData.append('fecha_entrega_a_t', fecha_entrega_a_t);
        formData.append('fecha_entrega_a_rh', fecha_entrega_a_rh);
        formData.append('module', 'gestion-recursos');
        formData.append('method', 'save');
        //ponerle hidden a la seccion que aparece en el modal
        
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#recursoForm')[0].reset();
                $('#recursoModal2').modal('hide');
                notify('success', 'Recurso agregado', 'El recurso se ha agregado/actualizado correctamente');
                $('#table-todos, #table-asignados, #table-retornados').bootstrapTable('refresh');
                $('#btn-save-recurso').prop('disabled', false);
            },
            error: function(xhr, status, error) {
                notify('danger', 'Error', 'Hubo un error al agregar/actualizar el recurso');
                $('#btn-save-recurso').prop('disabled', false);
            }
        });
    });
    // Botón para agregar nuevo recurso
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=gestion-recursos&method=new';
    });

    // Manejador para el botón de descargar
    $('#table-todos, #table-asignados, #table-retornados').on('click', '.download-recurso', function () {
        var $tr = $(this).closest('tr');
        var $table = $tr.closest('table');

        var index = $tr.data('index'); // O $tr.index()
        var allData = $table.bootstrapTable('getData');
        var rowData = allData[index];

        descargar_acta(rowData.id);
    });

    // Manejador para el botón de ver detalles
    $('#table-todos, #table-asignados, #table-retornados').on('click', '.view-recurso', function () {
        var $tr = $(this).closest('tr');
        var $table = $tr.closest('table');

        var index = $tr.data('index'); // O $tr.index()
        var allData = $table.bootstrapTable('getData');
        var rowData = allData[index];
        
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
        html += addRow('Marca', rowData.marca);
        html += addRow('Modelo', rowData.modelo);
        html += addRow('Color', rowData.color);
        html += addRow('Otros Recursos', rowData.otros_recursos);
        
        html += '</table>';
        html += '</div>';
        html += '</div>';
        
        // Mostrar el modal
        $('#modalBody').html(html);
        $('#recursoModal').modal('show');
    });

    // Manejador para el botón de editar
    $('#table-todos, #table-asignados, #table-retornados').on('click', '.edit-recurso', function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: 'module=gestion-recursos&method=get&id=' + id,
            dataType: 'json',
            success: function(d) {
                d = d[0];
                $('#f-trabajador_id-rec').val(d.trabajador_id);
                $('#f-trabajador-rec-nombre').val(d.nombre_trabajador);
                $('#trabajador-rec-nombre').removeClass('d-none');
                $('#f-id-rec').val(d.id);
                $('#f-recurso').val(d.nombre);
                $('#f-marca').val(d.marca);
                $('#f-modelo').val(d.modelo);
                $('#f-color').val(d.color);
                $('#f-otros').val(d.otros_recursos);
                $('#f-fecha-entrega').val(d.fecha_entrega_a_t);
                $('#f-fecha-rh').val(d.fecha_entrega_a_rh);
                $('#recursoModal2').modal('show');
            }
        });

    });

    // Manejador para el botón de eliminar
    $('#table-todos, #table-asignados, #table-retornados').on('click', '.delete-recurso', function () {
        var $btn = $(this).is('button') ? $(this) : $(this).closest('button[data-id]');
        if ($btn.length === 0) return;
        
        var id = $(this).data('id');
        
        if (confirm('¿Estás seguro de dar de baja a este registro?')) {
            var rowIndex = $btn.parent().parent().parent().data('index');
            var cmd = 'module=gestion-recursos&method=del&id=' + id + '&row=' + rowIndex;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                        // Mostrar notificación de éxito
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'success',
                                container: 'floating',
                                title: 'Dado de Baja',
                                message: 'El registro ha sido dado de baja correctamente con fecha de hoy.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Registro eliminado correctamente.');
                        }
                        $('#table-todos').bootstrapTable('refresh');
                        $('#table-asignados').bootstrapTable('refresh');
                        $('#table-retornados').bootstrapTable('refresh');
                    } else {
                        // Mostrar notificación de error
                        if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                            $.niftyNoty({
                                type: 'danger',
                                container: 'floating',
                                title: 'Error',
                                message: 'No se pudo dar de baja al registro.',
                                timer: 3000,
                                closeBtn: true,
                                focus: true
                            });
                        } else {
                            alert('Error al eliminar el registro.');
                        }
                    }
                },
                error: function() {
                    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                        $.niftyNoty({
                            type: 'danger',
                            container: 'floating',
                            title: 'Error',
                            message: 'Error de conexión al dar de baja al registro.',
                            timer: 3000,
                            closeBtn: true,
                            focus: true
                        });
                    } else {
                        alert('Error de conexión.');
                    }
                }
            });
        }
    });

    // Función para formatear la fecha en la búsqueda
    $.fn.bootstrapTable.defaults.formatSearch = function(text) {
        return text ? text.toString().toLowerCase() : '';
    };
});

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
      var text = (title ? (title + ': ') : '') + (message || '');
      try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
  }