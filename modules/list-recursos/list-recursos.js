// Formateador para las fechas
function formatoFecha(value, row) {
    if (!value)
        return 'N/A';
    return value;
}

// Destino de la asignación: un trabajador o un objeto definido a mano
function formatoAsignadoLink(value, row) {
    if (!value || String(value).trim() === '') {
        return '<span class="text-muted">Sin asignar</span>';
    }
    if (row.tipo_asignacion === 'objeto') {
        var iconoHtml = '<div style="width: 40px; height: 40px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-right: 12px; flex-shrink: 0;" class="img-thumbnail"><i class="fa fa-cube" style="font-size: 20px; color: #999;"></i></div>';
        var textoHtml = '<span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">'
            + (value || 'Sin nombre')
            + ' <span class="label label-default" style="margin-left:6px;">Objeto</span></span>';
        return '<div style="display: flex; align-items: center; gap: 8px;">' + iconoHtml + textoHtml + '</div>';
    }
    return formatoTrabajadorLink(value, row);
}

function formatoTrabajadorLink(value, row) {
    var fotaHtml = '';
    if (row.foto) {
        fotaHtml = '<img src="' + row.foto + '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 12px; flex-shrink: 0; cursor: zoom-in;" class="img-thumbnail" alt="Foto del trabajador">';
    } else {
        fotaHtml = '<div style="width: 40px; height: 40px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-right: 12px; flex-shrink: 0;" class="img-thumbnail"><i class="fa fa-user" style="font-size: 20px; color: #999;"></i></div>';
    }

    var enlaceHtml = '';
    if (row.trabajador_id) {
        enlaceHtml = '<a href="index.php?module=ficha-trabajador&id=' + row.trabajador_id + '" style="cursor: pointer; color: inherit; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#337ab7\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'inherit\';">' + value + '</a>';
    } else {
        enlaceHtml = '<span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">' + value + '</span>';
    }

    return '<div style="display: flex; align-items: center; gap: 8px;">' + fotaHtml + enlaceHtml + '</div>';
}

// Evita que la celda de "Opciones" haga wrap de los botones
function celdaSinWrap(value, row, index) {
    return { css: { 'white-space': 'nowrap', 'overflow': 'visible' } };
}

// Formateador para las opciones
function formatoToolbar(value, row) {
    var html = '<div class="btn-group" style="white-space:nowrap; display:inline-flex; flex-wrap:nowrap; gap:2px;">';

    // Ver detalles
    html += '<button class="btn btn-success btn-icon icon-sm fa fa-eye view-recurso" ';
    html += 'data-id="' + row.id + '" title="Ver detalles"></button> ';

    if (row.disponible == 1) {
        // Asignar (a un trabajador o a un objeto)
        html += '<button class="btn btn-mint btn-icon icon-sm fa fa-share-square-o assign-recurso" ';
        html += 'data-id="' + row.id + '" title="Asignar"></button> ';
    } else if (row.asignacion_id && !row.fecha_entrega_a_rh) {
        // Registrar la devolución
        html += '<button class="btn btn-warning btn-icon icon-sm fa fa-sign-out return-recurso" ';
        html += 'data-id="' + row.id + '" title="Retornar"></button> ';
    }

    // Editar
    html += '<button class="btn btn-info btn-icon icon-sm fa fa-edit edit-recurso" ';
    html += 'data-id="' + row.id + '" title="Editar"> </button> ';

    // Eliminar
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
    setTimeout(function () {
        document.body.removeChild(link);
        window.URL.revokeObjectURL(link.href);
    }, 100);
}

$(document).ready(function () {
    // Crear nueva categoría desde el botón + al lado del select:
    // se abre un campo dentro del propio modal, sin usar prompt() del navegador.
    $(document).on('click', '#btn-add-categoria', function () {
        $('#btn-add-categoria').prop('disabled', true);
        $('#nueva-categoria-wrap').css('display', 'table');
        $('#nueva-categoria-nombre').val('').focus();
    });

    function cancelarNuevaCategoria() {
        $('#nueva-categoria-wrap').css('display', 'none');
        $('#nueva-categoria-nombre').val('');
        $('#btn-add-categoria').prop('disabled', false);
    }

    $(document).on('click', '#btn-cancelar-categoria', function (e) {
        e.preventDefault();
        cancelarNuevaCategoria();
    });

    $(document).on('keydown', '#nueva-categoria-nombre', function (e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            cancelarNuevaCategoria();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            $('#btn-guardar-categoria').click();
        }
    });

    $(document).on('click', '#btn-guardar-categoria', function () {
        var nombre = $.trim($('#nueva-categoria-nombre').val());
        if (!nombre) {
            notify('danger', 'Validación', 'Escriba el nombre de la categoría');
            $('#nueva-categoria-nombre').focus();
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true);

        var fd = new FormData();
        fd.append('module', 'gestion-recursos');
        fd.append('method', 'create_categoria');
        fd.append('nombre', nombre);

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (d) {
                if (d && d.status == 1 && d.id) {
                    var $sel = $('#rec-categoria');
                    // Agregar opción si no existe
                    if ($sel.find('option[value="' + d.id + '"]').length === 0) {
                        $sel.append(new Option(d.nombre || nombre, d.id));
                    }
                    // Seleccionar la nueva categoría
                    $sel.val(String(d.id));
                    // Refresh si usa plugin
                    try { $sel.trigger('change').trigger('chosen:updated').selectpicker('refresh'); } catch (e) { }
                    notify('success', 'Categoría', 'Categoría creada correctamente');
                    cancelarNuevaCategoria();
                } else {
                    notify('danger', 'Error', (d && d.msg) ? d.msg : 'No se pudo crear la categoría');
                }
            },
            error: function (xhr, status, err) {
                var msg = (xhr && xhr.responseText) ? xhr.responseText : (err || status || 'Error desconocido');
                notify('danger', 'Error', 'No se pudo crear la categoría: ' + msg);
            },
            complete: function () { btn.prop('disabled', false); }
        });
    });

    // --- Filtros del listado de recursos ---
    function urlRecursos(metodo) {
        var url = 'api-app.php?module=gestion-recursos&method=' + (metodo || 'get_recursos');
        var categoria = $('#filtro-categoria').val();
        var disponible = $('#filtro-disponible').val();
        var tipo = $('#filtro-tipo-asignacion').val();
        if (categoria && categoria !== '') {
            url += '&categoria_recurso_id=' + encodeURIComponent(categoria);
        }
        if (disponible && disponible !== '') {
            url += '&disponible=' + encodeURIComponent(disponible);
        }
        if (tipo && tipo !== '') {
            url += '&tipo_asignacion=' + encodeURIComponent(tipo);
        }
        return url;
    }

    $('#btn-filtrar-recursos').on('click', function (e) {
        e.preventDefault();
        $('#table-todos').bootstrapTable('refreshOptions', { url: urlRecursos() });
    });

    $('#btn-reset-categoria').on('click', function (e) {
        e.preventDefault();
        $('#filtro-categoria').val('');
        $('#filtro-disponible').val('');
        $('#filtro-tipo-asignacion').val('');
        $('#table-todos').bootstrapTable('refreshOptions', { url: urlRecursos() });
    });

    // Exportar a Excel lo mismo que muestra el listado
    $('#btn-exportar-asignados').on('click', function (e) {
        e.preventDefault();

        // Crear un enlace temporal para descargar el archivo
        var link = document.createElement('a');
        link.href = urlRecursos('export_asignados');
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // --- Asignación de un recurso a un trabajador o a un objeto ---
    function actualizarDestinoAsignacion() {
        var tipo = $('#recursoAsignarForm input[name="tipo_asignacion"]:checked').val();
        if (tipo === 'objeto') {
            $('#asig-bloque-trabajador').addClass('hidden');
            $('#asig-bloque-objeto').removeClass('hidden');
        } else {
            $('#asig-bloque-objeto').addClass('hidden');
            $('#asig-bloque-trabajador').removeClass('hidden');
        }
    }

    $('#recursoAsignarForm').on('change', 'input[name="tipo_asignacion"]', actualizarDestinoAsignacion);

    $('#table-todos').on('click', '.assign-recurso', function () {
        var $tr = $(this).closest('tr');
        var allData = $tr.closest('table').bootstrapTable('getData');
        var rowData = allData[$tr.data('index')];
        if (!rowData) return;

        $('#recursoAsignarForm')[0].reset();
        $('#asig-recurso-id').val(rowData.id);
        $('#asig-recurso-nombre').val(rowData.nombre);
        $('#recursoAsignarForm input[name="tipo_asignacion"][value="trabajador"]').prop('checked', true);
        actualizarDestinoAsignacion();
        $('#recursoModalAsignar').modal('show');
    });

    $('#recursoAsignarForm').on('submit', function (e) {
        e.preventDefault();

        var tipo = $('#recursoAsignarForm input[name="tipo_asignacion"]:checked').val();
        var recurso_id = $('#asig-recurso-id').val();
        var trabajador_id = $('#asig-trabajador-id').val();
        var asignado_a = $.trim($('#asig-asignado-a').val());
        var fecha = $('#asig-fecha-entrega').val();

        if (!recurso_id) {
            notify('danger', 'Validación', 'No se ha identificado el recurso');
            return;
        }
        if (tipo === 'objeto' && asignado_a === '') {
            notify('danger', 'Validación', 'Debe indicar a qué objeto se asigna el recurso');
            return;
        }
        if (tipo !== 'objeto' && (!trabajador_id || trabajador_id === '')) {
            notify('danger', 'Validación', 'Debe seleccionar un trabajador');
            return;
        }
        if (!fecha) {
            notify('danger', 'Validación', 'La fecha de entrega es obligatoria');
            return;
        }

        var formData = new FormData();
        formData.append('module', 'gestion-recursos');
        formData.append('method', 'save');
        formData.append('action', 'insert');
        formData.append('recurso_id', recurso_id);
        formData.append('tipo_asignacion', tipo);
        formData.append('fecha_entrega_a_t', fecha);
        if (tipo === 'objeto') {
            formData.append('asignado_a', asignado_a);
        } else {
            formData.append('trabajador_id', trabajador_id);
        }

        $('#btn-save-recurso-asignar').prop('disabled', true);
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (d) {
                if (d && d.status == 1) {
                    $('#recursoModalAsignar').modal('hide');
                    notify('success', 'Recurso asignado', 'El recurso se ha asignado correctamente');
                    $('#table-todos').bootstrapTable('refresh');
                } else {
                    notify('danger', 'Error', (d && d.msg) ? d.msg : 'No se pudo asignar el recurso');
                }
            },
            error: function (xhr, status, err) {
                var msg = (xhr && xhr.responseText) ? xhr.responseText : (err || status || 'Error desconocido');
                notify('danger', 'Error', 'No se pudo asignar el recurso: ' + msg);
            },
            complete: function () { $('#btn-save-recurso-asignar').prop('disabled', false); }
        });
    });

    $('#recursoCreateForm').on('submit', function (e) {
        $('#btn-save-recurso-create').prop('disabled', true);
        e.preventDefault();
        var nombre = $('#rec-nombre').val();
        var descripcion = $('#rec-descripcion').val();
        var categoria_id = $('#rec-categoria').val();
        var rec_id = $('#rec-id').val();


        // Validación básica
        if (!nombre || !categoria_id) {
            notify('danger', 'Validación', 'Nombre y Categoría son obligatorios');
            $('#btn-save-recurso-create').prop('disabled', false);
            return;
        }

        var formData = new FormData();
        formData.append('module', 'gestion-recursos');
        if (rec_id) {
            formData.append('method', 'update_recurso');
            formData.append('id', rec_id);
        } else {
            formData.append('method', 'create_recurso');
        }
        formData.append('nombre', nombre);
        if (descripcion) formData.append('descripcion', descripcion);
        formData.append('categoria_id', categoria_id);


        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#recursoCreateForm')[0].reset();
                $('#rec-id').val('');
                $('#recursoModal3').modal('hide');
                notify('success', 'Recurso', 'El recurso se ha guardado correctamente');
                $('#table-todos').bootstrapTable('refresh');
                $('#btn-save-recurso-create').prop('disabled', false);
            },

            error: function (xhr, status, error) {
                notify('danger', 'Error', 'Hubo un error al agregar/actualizar el recurso');
                $('#btn-save-recurso-create').prop('disabled', false);
            }
        });
    });
    // Botón para agregar nuevo recurso
    $('#btn-add-new').click(function () {
        $('#recursoModal3').modal('show');
    });

    // Al cerrar el modal de recurso, ocultar también el campo de nueva categoría
    $('#recursoModal3').on('hidden.bs.modal', function () {
        $('#nueva-categoria-wrap').css('display', 'none');
        $('#nueva-categoria-nombre').val('');
        $('#btn-add-categoria').prop('disabled', false);
    });

    // Manejador para el botón de descargar
    $('#table-todos').on('click', '.download-recurso', function () {
        var $tr = $(this).closest('tr');
        var $table = $tr.closest('table');

        var index = $tr.data('index'); // O $tr.index()
        var allData = $table.bootstrapTable('getData');
        var rowData = allData[index];

        descargar_acta(rowData.id);
    });

    // Manejador para el botón de ver detalles
    $('#table-todos').on('click', '.view-recurso', function () {
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
        html += addRow('Estado', formatoDisponible(rowData.disponible, rowData));
        if (rowData.asignado_nombre) {
            html += addRow(
                rowData.tipo_asignacion === 'objeto' ? 'Objeto' : 'Trabajador',
                rowData.asignado_nombre
            );
            html += addRow('Fecha de Entrega', formatoFecha(rowData.fecha_entrega_a_t));
        }
        html += addRow('Categoría', rowData.categoria);
        html += addRow('Descripción', rowData.descripcion);

        html += '</table>';
        html += '</div>';
        html += '</div>';

        // Mostrar el modal
        $('#modalBodydetalle').html(html);
        $('#recursoModaldetalle').modal('show');
    });

    // Manejador para el botón de editar
    $('#table-todos').on('click', '.edit-recurso', function () {
        var id = $(this).data('id');
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: 'module=gestion-recursos&method=get&id=' + id,
            dataType: 'json',
            success: function (d) {
                if (!d || !d.length) return;
                d = d[0];
                // Rellenar modal de creación/edición
                $('#rec-id').val(d.id || '');
                $('#rec-categoria').val(d.categoria_id || '');
                $('#rec-nombre').val(d.nombre || '');
                $('#rec-descripcion').val(d.descripcion || '');
                $('#recursoModal3').modal('show');
            }
        });

    });

    // Manejador para el botón de eliminar (recursos)
    $('#table-todos').on('click', '.delete-recurso', function () {
        var $btn = $(this).is('button') ? $(this) : $(this).closest('button[data-id]');
        if ($btn.length === 0) return;

        var id = $(this).data('id');
        if (!id) return;

        if (!confirm('¿Estás seguro de eliminar este recurso? Esta acción no se puede deshacer.')) return;

        var rowIndex = $btn.parent().parent().parent().data('index');
        var cmd = 'module=gestion-recursos&method=del&id=' + encodeURIComponent(id) + '&row=' + encodeURIComponent(rowIndex);
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: cmd,
            dataType: 'json',
            success: function (d) {
                if (d && d.status == 1) {
                    notify('success', 'Eliminado', 'El recurso fue eliminado correctamente');
                    $('#table-todos').bootstrapTable('refresh');
                } else {
                    notify('danger', 'Error', (d && d.msg) ? d.msg : 'No se pudo eliminar el recurso');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                notify('danger', 'Error', 'Error al eliminar el recurso: ' + response);
            }
        });
    });

    // Función para formatear la fecha en la búsqueda
    $.fn.bootstrapTable.defaults.formatSearch = function (text) {
        return text ? text.toString().toLowerCase() : '';
    };
    $('#table-todos').on('click', '.return-recurso', function () {
        if (confirm('¿Estás seguro de registrar la devolución de este recurso?')) {
            var $tr = $(this).closest('tr');
            var $table = $tr.closest('table');
            var index = $tr.data('index');
            var allData = $table.bootstrapTable('getData');
            var rowData = allData[index];

            var hoy = new Date().toISOString().split('T')[0];

            var formData = new FormData();
            formData.append('module', 'gestion-recursos');
            formData.append('method', 'save');
            formData.append('action', 'update');
            formData.append('id', rowData.asignacion_id);
            if (rowData.trabajador_id) formData.append('trabajador_id', rowData.trabajador_id);
            if (rowData.tipo_asignacion) formData.append('tipo_asignacion', rowData.tipo_asignacion);
            if (rowData.asignado_a) formData.append('asignado_a', rowData.asignado_a);
            formData.append('recurso_id', rowData.id);
            formData.append('fecha_entrega_a_rh', hoy);

            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (d) {
                    notify('success', 'Recurso retornado', 'Se registró la devolución con fecha de hoy.');
                    $('#table-todos').bootstrapTable('refresh');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                    notify('danger', 'Error', 'No se pudo registrar la devolución: ' + response);
                }
            });
        }
    });
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
        try { alert(text); } catch (e) { console.warn('Notify:', text); }
    }
}

function formatoDisponible(value, row, index) {
    if (value == 1) {
        return '<span class="label label-success">Disponible</span>';
    } else {
        return '<span class="label label-danger">Asignado</span>';
    }
}

// --- Modal de vista previa de foto al pasar el mouse (list-recursos) ---
(function () {
    var css = '\n'
        + '.lr-photo-modal-overlay { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 2000; pointer-events: none; }\n'
        + '.lr-photo-modal { pointer-events: auto; background: rgba(255,255,255,1); padding: 8px; border-radius: 6px; box-shadow: 0 8px 30px rgba(0,0,0,0.45); transition: opacity 220ms ease, transform 220ms ease; opacity: 0; transform: scale(0.96); }\n'
        + '.lr-photo-modal.show { opacity: 1; transform: scale(1); }\n'
        + '.lr-photo-modal img { display:block; max-width: 640px; max-height: 640px; width: auto; height: auto; border-radius:4px; }\n';

    var style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    var overlay = document.createElement('div');
    overlay.className = 'lr-photo-modal-overlay';
    overlay.style.display = 'none';

    var modal = document.createElement('div');
    modal.className = 'lr-photo-modal';
    var img = document.createElement('img');
    img.alt = 'Foto ampliada';
    modal.appendChild(img);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    var hideTimer = null;

    function showModal(src) {
        if (!src) return;
        img.src = src;
        overlay.style.display = 'flex';
        void modal.offsetWidth;
        modal.classList.add('show');
    }

    function hideModal() {
        modal.classList.remove('show');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () { overlay.style.display = 'none'; img.src = ''; }, 240);
    }

    // Delegated listeners: images in resources table have class img-thumbnail
    document.addEventListener('mouseover', function (e) {
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && t.classList.contains('img-thumbnail')) {
            var src = t.getAttribute('src');
            if (src) {
                clearTimeout(hideTimer);
                showModal(src);
            }
        }
    });

    document.addEventListener('mouseout', function (e) {
        var from = e.relatedTarget || e.toElement;
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && t.classList.contains('img-thumbnail')) {
            if (!from || !(from === modal || modal.contains(from))) {
                hideModal();
            }
        }
    });

    overlay.addEventListener('mouseleave', function () { hideModal(); });
    overlay.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
})();