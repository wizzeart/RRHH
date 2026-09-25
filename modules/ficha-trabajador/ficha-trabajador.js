var e, tmp, cmd_params;

// Formateadores con botón de edición para fechas en la tabla de recursos del trabajador
function formatoFechaEntregaEditable(value, row) {
    var fecha = (value && String(value).trim() !== '' && value !== 'null' && value !== null) ? value : 'N/A';
    var btn = '';

    // Si el rol no es 2 ni 4, mostrar botón de edición
    if (rol != '2' && rol != '4') {
        btn = '<button class="btn btn-link btn-xs edit-fecha-entrega" title="Editar fecha de entrega" data-id="' + (row && row.id ? row.id : '') + '"><i class="fa fa-pencil"></i></button>';
    }

    return '<span>' + fecha + '</span> ' + btn;
}

// ---------------------------
// Asistencias (tab ficha)
// ---------------------------

function formatoAsistencia(value, row) {
    if (row.ausencia == 3) {
        return '<span class="label label-primary">VACACIONES</span>';
    } else if (row.ausencia == 1) {
        let tooltip = '';
        if (row.justificacion) {
            tooltip = ' data-toggle="tooltip" data-placement="top" title="' + row.justificacion + '"';
        }

        // Botón de justificar - solo si el rol no es 2
        var btnJustificar = '';
        if (rol != '2') {
            var rowData = encodeURIComponent(JSON.stringify(row));
            btnJustificar = '<button class="btn btn-icon btn-xs btn-info fa fa-pencil" title="Justificar" style="margin-left: 5px; padding: 1px 4px; font-size: 10px;" onclick="editarAsistenciaEncoded(\'' + rowData + '\'); return false;"></button>';
        }

        return '<span class="label label-danger"' + tooltip + '>AUSENTE</span>' + btnJustificar + '<br><small>' + (row.tipo_ausencia || '') + '</small>';
    } else if (row.tardanza == 1) {
        let tooltip = '';
        if (row.justificacion) {
            tooltip = ` data-toggle="tooltip" data-placement="top" title="${row.justificacion}"`;
        }

        // Botón de justificar - solo si el rol no es 2
        var btnJustificar = '';
        if (rol != '2') {
            var rowData = encodeURIComponent(JSON.stringify(row));
            btnJustificar = '<button class="btn btn-icon btn-xs btn-info fa fa-pencil" title="Justificar" style="margin-left: 5px; padding: 1px 4px; font-size: 10px;" onclick="editarAsistenciaEncoded(\'' + rowData + '\'); return false;"></button>';
        }

        return `<span class="label label-success"${tooltip}>PRESENTE</span>${btnJustificar}<br><small>Tardanza</small>`;
    }
    return '<span class="label label-success">PRESENTE</span>';
}

// Función auxiliar para manejar datos codificados desde el botón de justificar
window.editarAsistenciaEncoded = function (encodedRow) {
    try {
        var row = JSON.parse(decodeURIComponent(encodedRow));
        editarAsistencia(row);
    } catch (e) {
        console.error('Error al decodificar datos de la fila:', e);
    }
};

// Función para abrir el modal de edición
window.editarAsistencia = function (row) {
    console.log('Editando asistencia:', row); // Para depuración

    // Asegurarse de que row es un objeto
    if (typeof row === 'string') {
        try {
            row = JSON.parse(row.replace(/"/g, '"'));
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

    // Detectar si es presente con tardanza
    var esPresenteConTardanza = (row.tardanza == 1 && row.ausencia != '1');
    var esAusenciaExistente = (row.ausencia == '1' || (row.tipo_ausencia !== null && row.tipo_ausencia !== ''));

    // Marcar automáticamente el checkbox de ausencia para ausencias existentes
    if (esAusenciaExistente && !esPresenteConTardanza) {
        $modal.find('#ausencia').prop('checked', true);
    } else {
        $modal.find('#ausencia').prop('checked', esAusenciaExistente);
    }

    $modal.find('#tipo_ausencia').val(row.tipo_ausencia || '');
    $modal.find('#justificacion').val(row.justificacion || '');

    // Actualizar título del modal
    if (esPresenteConTardanza) {
        $modal.find('.modal-title').text('Justificar Tardanza');
    } else {
        $modal.find('.modal-title').text(row.id ? 'Editar Asistencia' : 'Nueva Asistencia');
    }

    // Mostrar/ocultar campos según el caso
    toggleCamposAsistencia(esPresenteConTardanza);

    // Mostrar el modal
    $modal.modal('show');
};

// Mostrar/ocultar campo de tipo de ausencia
$('#ausencia').change(function () {
    toggleCamposAsistencia(false); // false porque no es presente con tardanza en este caso
});

function toggleCamposAsistencia(esPresenteConTardanza) {
    if (esPresenteConTardanza) {
        // Para presentes con tardanza: ocultar checkbox de ausencia y tipo de ausencia
        $('#ausencia').closest('.form-group').hide();
        $('#tipo-ausencia-container').hide();
        $('#tipo_ausencia').prop('required', false);
        // Mostrar solo el campo de justificación
        $('#justificacion-container').show();
        $('#justificacion').prop('required', true);
        // Cambiar placeholder del campo de justificación
        $('#justificacion').attr('placeholder', 'Ingrese la justificación de la tardanza');
    } else {
        // Comportamiento normal para otros casos
        if ($('#ausencia').is(':checked')) {
            $('#tipo-ausencia-container').show();
            $('#tipo_ausencia').prop('required', true);
            $('#justificacion-container').show();
            $('#justificacion').prop('required', true);
            // Ocultar checkbox de ausencia para ausencias existentes (ya está marcado)
            $('#ausencia').closest('.form-group').hide();
        } else {
            $('#tipo-ausencia-container').hide();
            $('#tipo_ausencia').prop('required', false);
            $('#justificacion-container').hide();
            $('#justificacion').prop('required', false);
            // Mostrar checkbox de ausencia solo para nuevos registros
            $('#ausencia').closest('.form-group').show();
        }
        // Restaurar placeholder original
        $('#justificacion').attr('placeholder', 'Ingrese la descripción de la ausencia');
    }
}

// Guardar asistencia
$('#btn-guardar-asistencia').click(function () {
    var formData = $('#formAsistencia').serialize();
    var method = 'save';

    // Verificar si es un presente con tardanza (checkbox ausencia oculto)
    var $ausenciaCheckbox = $('#ausencia');
    if ($ausenciaCheckbox.is(':hidden') && $('#justificacion-container').is(':visible')) {
        // Es un presente con tardanza, asegurar que se envíe ausencia=0
        if (formData.indexOf('ausencia=') === -1) {
            formData += '&ausencia=0';
        }
    }

    $.ajax({
        url: 'api-app.php?module=asistencias&method=' + method,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            console.log(response);
            if (response.status == 1) {
                $('#modalAsistencia').modal('hide');
                $('#table-asistencias').bootstrapTable('refresh');
                notify('success', 'Éxito', response.msg || 'Registro guardado correctamente');
            } else {
                notify('danger', 'Error', response.msg || 'Error al guardar el registro');
            }
        },
        error: function () {
            notify('danger', 'Error', 'Error al conectar con el servidor');
        }
    });
});

function formatoFechaAsistencia(value, row) {
    if (!value) return 'N/A';

    try {
        // Crear objeto Date desde la fecha (formato YYYY-MM-DD)
        const date = new Date(value + 'T00:00:00');

        // Array con los días de la semana en español
        const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        // Obtener el día de la semana (0 = Domingo, 1 = Lunes, etc.)
        const diaSemana = diasSemana[date.getDay()];

        // Formatear la fecha como DD/MM/YYYY
        const dia = String(date.getDate()).padStart(2, '0');
        const mes = String(date.getMonth() + 1).padStart(2, '0');
        const anio = date.getFullYear();
        const fechaFormateada = `${dia}/${mes}/${anio}`;

        // Retornar fecha + día de la semana
        return `${fechaFormateada}<br><small>${diaSemana}</small>`;
    } catch (e) {
        console.error('Error formatting date:', e);
        return value || 'N/A';
    }
}

function formatoHorasAsist(value, row) {
    if (!row.hora_entrada || !row.hora_salida) {
        return '-';
    }
    try {
        let entradaSegundos = timeStringToSeconds(row.hora_entrada);
        let salidaSegundos = timeStringToSeconds(row.hora_salida);
        if (entradaSegundos > salidaSegundos) {
            salidaSegundos = salidaSegundos + 24 * 3600;
        }
        let diffSegundos = (salidaSegundos - entradaSegundos);
        const horasDecimal = (diffSegundos / 3600).toFixed(1);
        return parseFloat(horasDecimal) + ' h';
    } catch (e) {
        console.error('Error calculating time difference:', e);
        return '-';
    }
}

function buildAsistUrl() {
    var trabajadorId = $('#f-id').val();
    if (!trabajadorId) return '';
    var params = [];
    var desde = $('#asist-fecha-desde').val();
    var hasta = $('#asist-fecha-hasta').val();
    params.push('module=asistencias');
    params.push('method=list-filter');
    params.push('trabajador_id=' + encodeURIComponent(trabajadorId));
    if (desde) params.push('fecha_desde=' + encodeURIComponent(desde));
    if (hasta) params.push('fecha_hasta=' + encodeURIComponent(hasta));
    return 'api-app.php?' + params.join('&');
}

// Construye la URL de descarga del Excel de asistencia por trabajador
// (por año completo o rango de meses).
function buildAsistExportUrl() {
    var trabajadorId = $('#f-id').val();
    if (!trabajadorId) return '';
    var anno = $('#exp-asist-anno').val();
    var mesDesde = $('#exp-asist-mes-desde').val();
    var mesHasta = $('#exp-asist-mes-hasta').val();
    var params = [];
    params.push('module=asistencias');
    params.push('method=export-excel-trabajador');
    params.push('trabajador_id=' + encodeURIComponent(trabajadorId));
    if (anno) params.push('anno=' + encodeURIComponent(anno));
    if (mesDesde) params.push('mes_desde=' + encodeURIComponent(mesDesde));
    if (mesHasta) params.push('mes_hasta=' + encodeURIComponent(mesHasta));
    return 'api-app.php?' + params.join('&');
}

function cargarAsistencias() {
    var $table = $('#table-asistencias');
    if ($table.length === 0) return;
    var url = buildAsistUrl();
    if (!url) return;
    try {
        $table.bootstrapTable('refresh', { url: url });
    } catch (e) {
        // Si aún no está inicializada, inicializar con la URL
        $table.bootstrapTable({ url: url });
    }
}

$(document).ready(function () {
    // Manejar hash de la URL para activar tab específico
    var hash = window.location.hash;
    if (hash) {
        var tabLink = $('a[href="' + hash + '"]');
        if (tabLink.length > 0) {
            // Remover active de todos los tabs
            $('.nav-tabs li').removeClass('active');
            $('.tab-pane').removeClass('active in');
            
            // Activar el tab correspondiente al hash
            tabLink.parent().addClass('active');
            $(hash).addClass('active in');
            
            // Disparar evento shown.bs.tab si es el tab de asistencias
            if (hash === '#tab-asistencias') {
                setTimeout(function() {
                    cargarAsistencias();
                }, 100);
            }
        }
    }
    
    // Cargar al mostrar el tab
    $('a[href="#tab-asistencias"]').on('shown.bs.tab', function () {
        cargarAsistencias();
    });

    // Botón filtrar
    $('#btn-filtrar-asist').on('click', function () {
        cargarAsistencias();
    });

    // Botón exportar asistencia a Excel
    $('#btn-export-asist-excel').on('click', function () {
        var url = buildAsistExportUrl();
        if (!url) {
            alert('Debe guardar el trabajador antes de exportar su asistencia.');
            return;
        }
        window.location = url;
    });
});

function formatoFechaDevolucionEditable(value, row) {
    var tiene = (value && String(value).trim() !== '' && value !== 'null' && value !== null);
    var fecha = tiene ? value : 'N/A';
    var btn = '';

    // Si el rol no es 2 ni 4 y tiene fecha, mostrar botón de edición
    if (rol != '2' && rol != '4' && tiene) {
        btn = ' <button class="btn btn-link btn-xs edit-fecha-devolucion" title="Editar fecha de devolución" data-id="' + (row && row.id ? row.id : '') + '"><i class="fa fa-pencil"></i></button>';
    }

    return '<span>' + fecha + '</span>' + btn;
}

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
        var clase = value == 1 ? 'label label-danger' : 'label label-info';
        return '<span class="' + clase + '">' + estado + '</span>';
    }
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

function firmadoFormatter(value, row, index) {
    var firmado = (value && String(value).trim() !== '' && value !== 'null' && value !== null);
    if (firmado) {
        return '<span class="label label-success">Firmado</span>';
    }
    return '<span class="label label-warning">No firmado</span>';
}

$(document).ready(function () {
    $('#btn-ver-tarjeta').on('click', function () {
        window.open('?module=list-tarjetas-snc&id=' + $('#f-id').val(), '_blank');
    });
    function escapeHtml(str) {
        if (typeof str !== 'string') return str || '';
        return str.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    // Helper de notificaciones: usa Nifty Noty si está disponible, si no, fallback a alert
    $('#btn-pase-acceso').on('click', function () {
        // Obtener los datos del trabajador del formulario
        var t = {
            id: $('#f-id').val(),
            nombre: $('#f-nombre').val(),
            apellidos: $('#f-apellidos').val(),
            carnet_identidad: $('#f-ci').val(),
            cargo_nombre: $('#f-cargo option:selected').text(),
            cargos_id: $('#f-cargo').val(),
            areas_acceso: $('#f-areas-acceso').val() || 'Todas las áreas',
            fecha_generacion: new Date().toLocaleDateString(),
            vigente: '1',
            foto: $('#foto-preview').attr('src') || 'img/avatar.png'
        };

        // Debug: ver los datos que recibimos
        console.log('Datos del trabajador para pase de acceso:', t);
        var fotoHtml = '';
        if (t.foto) {
            fotoHtml = '<img src="' + t.foto + '" alt="Foto del trabajador" style="max-width:200px; margin-bottom:10px;" class="img-thumbnail">';
        }

        // Construir el HTML del modal como tarjeta de identificación
        // Usamos un formulario oculto para enviar los datos al generador de PDF.
        var formId = 'pdfForm_' + (Math.floor(Math.random() * 100000));
        var html = '<div class="id-card-container" style="max-width:700px; margin:0 auto;">'
            + '<form id="' + formId + '" method="POST" action="generate_id_pdf.php" target="_blank">'
            // Hidden inputs to send to the PDF generator
            + '<input type="hidden" name="nombre" value="' + (escapeHtml(t.nombre || '')) + '">'
            + '<input type="hidden" name="apellidos" value="' + (escapeHtml(t.apellidos || '')) + '">'
            + '<input type="hidden" name="carnet_identidad" value="' + (escapeHtml(t.carnet_identidad || '')) + '">'
            + '<input type="hidden" name="cargo_nombre" value="' + (escapeHtml(t.cargo_nombre || '')) + '">'
            + '<input type="hidden" name="areas_acceso" value="' + (escapeHtml(t.areas_acceso || '')) + '">'
            + '<input type="hidden" name="fecha_generacion" value="' + (escapeHtml(t.fecha_generacion || '')) + '">'
            + '<input type="hidden" name="vigente" value="' + (t.vigente || '') + '">'
            + '<input type="hidden" name="foto" value="' + (escapeHtml(t.foto || '')) + '">'
            + '<div class="row">'
            + '<div class="col-md-12 text-center mb-3">'
            + '<h4 style="margin:0;">Tarjeta de Identificación</h4>'
            + '<small class="text-muted">Pase de Control de Acceso</small>'
            + '</div>'
            + '</div>'
            + '<div class="row align-items-center">'
            + '<div class="col-md-4 text-center">'
            + '<div class="photo-box" style="width:200px; height:260px; margin:0 auto; border:2px solid #e9ecef; border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fff;">'
            + (fotoHtml || '<div style="padding:10px;">No hay foto</div>')
            + '</div>'
            + '<div style="margin-top:10px;">'
            + '<span class="badge badge-info">ID: ' + (t.cargos_id || 'N/A') + '</span>'
            + '</div>'
            + '</div>'
            + '<div class="col-md-8">'
            + '<div class="card" style="border:1px solid #e9ecef; box-shadow: none;">'
            + '<div class="card-body p-3">'
            + '<h5 style="font-weight:700; margin-bottom:6px;">' + (t.nombre || '') + ' ' + (t.apellidos || '') + '</h5>'
            + '<p style="margin:0 0 8px 0; color:#6c757d;">' + (t.cargo_nombre || 'Cargo no especificado') + '</p>'
            + '<table class="table table-sm" style="margin-bottom:0; font-size:0.95em;">'
            + '<tr><td><strong>CI</strong></td><td>' + (t.carnet_identidad || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Áreas</strong></td><td>' + (t.areas_acceso || 'No definidas') + '</td></tr>'
            + '<tr><td><strong>Fecha Gen.</strong></td><td>' + (t.fecha_generacion || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Estado Pase</strong></td><td>' + (t.vigente === '1' ? 'Vigente' : 'No Vigente') + '</td></tr>'
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

        $('#modalBody').html(html);
        //$('#trabajadorModal').modal('show');
        // hacerle submit al formulario
        document.getElementById(formId).submit();
    });


    $('#btn-add-new-doc').on('click', function () {
        $('#documentoModal').modal('show');
    });

    $('#btn-contrato-trabajador2').on('click', function () {
        $('#form-contrato-anterior2')[0].reset();
        $('#modalContrato').modal('show');
    });

    $('#btn-add-new-recurso').on('click', function () {
        $('#recursoForm')[0].reset();
        $('#trabajador-rec-nombre').addClass('hidden');
        $('#fecha-rh-section').addClass('hidden');
        $('#recursoModal2').modal('show');
    });
    $('#recursoForm').on('submit', function (e) {
        $('#btn-save-recurso').prop('disabled', true);
        e.preventDefault();
        var recurso_id = $('#f-recurso-id').val();
        var fecha_entrega_a_t = $('#f-fecha-entrega').val();
        var fecha_entrega_a_rh = $('#f-fecha-rh').val();
        var trabajador_id = $('#f-id').val();
        var id_recurso_trabajador = $('#f-id-rec-trab').val();
        var formData = new FormData();
        if (id_recurso_trabajador) {
            formData.append('id', id_recurso_trabajador);
            formData.append('action', 'update');
        }
        formData.append('trabajador_id', trabajador_id);
        formData.append('tipo_asignacion', 'trabajador');
        formData.append('recurso_id', recurso_id);
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
            success: function (response) {

                $('#f-recurso-id option[value="' + recurso_id + '"]').remove();
                $('#recursoForm')[0].reset();
                $('#recursoModal2').modal('hide');
                notify('success', 'Recurso agregado', 'El recurso se ha agregado/actualizado correctamente');
                $('#table-recursos').bootstrapTable('refresh');
                $('#btn-save-recurso').prop('disabled', false);
            },
            error: function (xhr, status, error) {
                notify('danger', 'Error', 'Hubo un error al agregar/actualizar el recurso');
                $('#btn-save-recurso').prop('disabled', false);
            }
        });
    });

    $('#docForm').on('submit', function (e) {
        $('#btn-save-doc').prop('disabled', true);
        e.preventDefault();
        var tipo_doc = $('#tipo_doc').val();
        var archivo = $('#file_doc')[0].files[0];


        if (!archivo) {
            notify('danger', 'Error', 'Debe seleccionar un archivo');
            return;
        }

        if (!tipo_doc) {
            notify('danger', 'Error', 'Debe escribir una descripción del documento');
            return;
        }
        var formData = new FormData();
        formData.append('trabajador_id', $('#f-id').val());
        formData.append('tipo_doc', tipo_doc);
        formData.append('archivo', archivo);

        formData.append('module', 'documentos');
        formData.append('method', 'save');
        //deshabilitar el boton
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#docForm')[0].reset();
                $('#documentoModal').modal('hide');
                notify('success', 'Documento agregado', 'El documento se ha agregado correctamente');
                $('#table-documentos').bootstrapTable('refresh');
                $('#btn-save-doc').prop('disabled', false);
            },
            error: function (xhr, status, error) {
                notify('danger', 'Error', 'Hubo un error al agregar el documento');
                $('#btn-save-doc').prop('disabled', false);
            }
        });
    });

    // Manejador para el botón de descargar
    $('#table-recursos').on('click', '.download-recurso', function () {
        var $tr = $(this).closest('tr');
        var $table = $tr.closest('table');

        var index = $tr.data('index'); // O $tr.index()
        var allData = $table.bootstrapTable('getData');
        var rowData = allData[index];

        descargar_acta(rowData.id);
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
            // Fallback simple para garantizar feedback al usuario
            var text = (title ? (title + ': ') : '') + (message || '');
            try { alert(text); } catch (e) { console.warn('Notify:', text); }
        }
    }
    // Flag to track if selected photo is a valid image
    var fotoEsValida = true;
    $('#f-almacen').chosen({ no_results_text: "!Oops, no hay coincidencias!", width: '90%' });
    $('#f-punto-venta').chosen({ no_results_text: "!Oops, no hay coincidencias!", width: '90%' });

    // Hacer que los campos de fecha sean completamente clickeables para abrir el calendario
    function makeDateFieldClickable(fieldId) {
        $(fieldId).on('click', function () {
            // Forzar el foco y mostrar el selector de fecha
            this.focus();
            if (this.showPicker) {
                this.showPicker();
            } else {
                // Fallback para navegadores que no soportan showPicker()
                this.click();
            }
        });

        // También hacer clickeable el contenedor padre si existe
        $(fieldId).parent().on('click', function (e) {
            if (e.target !== $(fieldId)[0]) {
                $(fieldId).focus();
                if ($(fieldId)[0].showPicker) {
                    $(fieldId)[0].showPicker();
                }
            }
        });
    }

    // Control del checkbox para fecha de baja
    $('#check-fecha-baja').on('change', function () {
        if ($(this).is(':checked')) {
            $('#f-baja').prop('disabled', false);
            $('#f-baja').closest('.form-group').find('.help-block').text('Seleccione la fecha de baja del trabajador');
        } else {
            $('#f-baja').prop('disabled', true).val('');
            $('#f-baja').removeClass('is-invalid');
            $('#f-baja').closest('.form-group').find('.help-block').text('Marque la casilla superior para activar este campo');
        }
    });

    // Aplicar la funcionalidad a todos los campos de fecha
    makeDateFieldClickable('#f-contratacion');

    // Para fecha de baja, solo hacer clickeable si no está deshabilitado
    $('#f-baja').on('click', function () {
        if (!$(this).prop('disabled')) {
            this.focus();
            if (this.showPicker) {
                this.showPicker();
            }
        }
    });

    $('#btn-test').click(function () {
        var cmd = 'module=tools&method=test';
        cmd_params = cmd;
        $.ajax({
            url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
            success: function (d) {
                alert(d.status);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.error('Error en la petición:', {
                    status: XMLHttpRequest.status,
                    statusText: XMLHttpRequest.statusText,
                    responseText: XMLHttpRequest.responseText,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                alert('Error al guardar: ' + XMLHttpRequest.responseText); var cmd = 'module=tools&method=log-error&ref=' + encodeURIComponent('ERROR PANEL')
                    + '&data=' + encodeURIComponent(XMLHttpRequest.responseText)
                    + '&params=' + encodeURIComponent(cmd_params);
                $.ajax({
                    url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                    success: function (d) { },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
        });
    });
    $('#btn-new').click(function () {
        location.href = '?module=trabajadores';
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-trabajadores';
    });

    $('#btn-sms-trabajador').on('click', function () {
        var trabajadorId = $('#f-id').val();
        var telefono = $('#f-telefono').val();

        if (!telefono || String(telefono).trim() === '') {
            notify('warning', 'Advertencia', 'El trabajador no tiene teléfono móvil registrado');
            return;
        }

        $('#sms_trabajador_id').val(trabajadorId || '');
        $('#sms_telefono').val(telefono || '');
        $('#sms_mensaje').val('');
        $('#sms_char_count').text('0');
        $('#modalSmsTrabajador').modal('show');
    });

    $('#sms_mensaje').on('input', function () {
        var length = $(this).val().length;
        $('#sms_char_count').text(length);
        if (length > 160) {
            $('#sms_char_count').addClass('text-danger');
        } else {
            $('#sms_char_count').removeClass('text-danger');
        }
    });

    $('#btn-enviar-sms-trabajador').on('click', function () {
        var trabajadorId = $('#sms_trabajador_id').val();
        var telefono = $('#sms_telefono').val();
        var mensaje = $('#sms_mensaje').val();

        mensaje = String(mensaje || '').trim();
        if (mensaje === '') {
            notify('warning', 'Advertencia', 'El mensaje no puede estar vacío');
            return;
        }
        if (mensaje.length > 160) {
            notify('warning', 'Advertencia', 'El mensaje no puede exceder los 160 caracteres');
            return;
        }

        var cmd = 'module=notificaciones-sms&method=send-sms-trabajador';
        if (trabajadorId) {
            cmd += '&trabajador_id=' + encodeURIComponent(trabajadorId);
        }
        cmd += '&telefono=' + encodeURIComponent(telefono);
        cmd += '&mensaje=' + encodeURIComponent(mensaje);

        $('#btn-enviar-sms-trabajador').prop('disabled', true);
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: cmd,
            dataType: 'json',
            success: function (response) {
                if (response && response.status == 1) {
                    $('#modalSmsTrabajador').modal('hide');
                    notify('success', 'Éxito', response.msg || 'SMS enviado correctamente');
                } else {
                    notify('danger', 'Error', (response && response.msg) ? response.msg : 'Error al enviar el SMS');
                }
            },
            error: function () {
                notify('danger', 'Error', 'Error al conectar con el servidor');
            },
            complete: function () {
                $('#btn-enviar-sms-trabajador').prop('disabled', false);
            }
        });
    });
    // Función para manejar la previsualización de imágenes
    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(previewId).attr('src', e.target.result);
                $(previewId).parent().show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Validación y previsualización de foto (solo imágenes reales)
    $('#f-foto').change(function () {
        var $input = $(this);
        var file = this.files && this.files[0] ? this.files[0] : null;

        // Reset estado previo
        fotoEsValida = true;

        // Crear contenedor de preview si no existe
        if ($input.next('.preview-container').length === 0) {
            $input.after('<div class="preview-container mt-2" style="display:none"><img src="" style="max-width: 100px; height: auto;"></div>');
        }
        var $img = $input.next('.preview-container').find('img');

        if (!file) {
            // Sin archivo: ocultar preview
            $img.attr('src', '');
            $input.next('.preview-container').hide();
            return;
        }

        // Validar por MIME type
        if (!file.type || !file.type.startsWith('image/')) {
            fotoEsValida = false;
            $img.attr('src', '');
            $input.val(''); // limpiar input
            $input.next('.preview-container').hide();
            notify('danger', 'Archivo inválido', 'Solo se permiten archivos en formato imagen (JPEG, PNG, GIF, etc.).', 4000);
            return;
        }

        // Validar que realmente carga como imagen creando un objeto Image
        try {
            var objectUrl = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                // Es una imagen válida: mostrar preview
                $img.attr('src', objectUrl);
                $input.next('.preview-container').show();
                // Liberar URL cuando la imagen en el DOM termine de cargar
                $img.on('load', function () { URL.revokeObjectURL(objectUrl); });
                fotoEsValida = true;
            };
            img.onerror = function () {
                fotoEsValida = false;
                URL.revokeObjectURL(objectUrl);
                $img.attr('src', '');
                $input.val('');
                $input.next('.preview-container').hide();
                notify('danger', 'Archivo inválido', 'El archivo seleccionado no es una imagen válida.', 4000);
            };
            img.src = objectUrl;
        } catch (e) {
            // Fallback si falla la validación por algún motivo
            fotoEsValida = false;
            $img.attr('src', '');
            $input.val('');
            $input.next('.preview-container').hide();
            notify('danger', 'Error al validar imagen', 'No se pudo validar el archivo seleccionado. Intente con otra imagen.', 4000);
        }
    });

    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';
        if ($('#f-apellidos').val() == '') {
            status = 0;
            msg += '<div>El campo Apellidos del Trabajador es obligatorio.</div>';
        }
        if ($('#f-sexo').val() == '') {
            status = 0;
            msg += '<div>El campo Sexo del Trabajador es obligatorio.</div>';
        }
        if ($('#f-ci').val() == '') {
            status = 0;
            msg += '<div>El campo CI del Trabajador es obligatorio.</div>';
        } else if ($('#f-ci').val().length !== 11) {
            status = 0;
            msg += '<div>El Carnet de Identidad debe tener exactamente 11 caracteres.</div>';
        }
        if ($('#f-edad').val() == '') {
            status = 0;
            msg += '<div>El campo Edad del Trabajador es obligatorio.</div>';
        }
        if ($('#f-direccion').val() == '') {
            status = 0;
            msg += '<div>El campo Dirección del Trabajador es obligatorio.</div>';
        }
        if ($('#f-telefono').val() == '') {
            status = 0;
            msg += '<div>El campo Teléfono del Trabajador es obligatorio.</div>';
        } else {
            var phonePattern = /^[0-9+\-\s()]+$/;
            if (!phonePattern.test($('#f-telefono').val())) {
                status = 0;
                msg += '<div>El teléfono debe contener solo números, espacios, guiones, paréntesis o el signo +.</div>';
            }
        }
        if ($('#f-email').val() == '') {
            status = 0;
            msg += '<div>El campo Correo del Trabajador es obligatorio.</div>';
        } else {
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test($('#f-email').val())) {
                status = 0;
                msg += '<div>Por favor ingrese un correo electrónico válido.</div>';
            }
        }
        if ($('#f-nivel').val() == '') {
            status = 0;
            msg += '<div>El campo Nivel del Trabajador es obligatorio.</div>';
        }

        if ($('#f-contratacion').val() == '') {
            status = 0;
            msg += '<div>El campo Contratación del Trabajador es obligatorio.</div>';
        }



        if ($('#f-nombre').val() == '') {
            status = 0;
            msg += '<div>El campo Nombre del Trabajador es obligatorio.</div>';
        }

        if ($('#f-cargo').val() == '') {
            status = 0;
            msg += '<div>El campo Cargo del Trabajador es obligatorio.</div>';
        }

        if ($('#f-bolsa').val() == '') {
            status = 0;
            msg += '<div>El campo Bolsa de Empleo es obligatorio.</div>';
        }

        // Validación de fecha de baja
        if ($('#check-fecha-baja').is(':checked')) {
            if ($('#f-baja').val() == '') {
                status = 0;
                msg += '<div>Debe seleccionar una fecha de baja o desmarcar la casilla.</div>';
            } else if ($('#f-contratacion').val() && $('#f-baja').val()) {
                var fechaContratacion = new Date($('#f-contratacion').val());
                var fechaBaja = new Date($('#f-baja').val());
                if (fechaBaja < fechaContratacion) {
                    status = 0;
                    msg += '<div>La fecha de baja no puede ser anterior a la fecha de contratación.</div>';
                }
            }
        }

        // Validar foto antes de enviar (si se seleccionó)
        var archivoFoto = $('#f-foto')[0].files ? $('#f-foto')[0].files[0] : null;
        if (archivoFoto) {
            if (!archivoFoto.type || !archivoFoto.type.startsWith('image/')) {
                status = 0;
                msg += '<div>El archivo adjunto debe ser una imagen.</div>';
            }
            if (!fotoEsValida) {
                status = 0;
                msg += '<div>La imagen seleccionada no es válida. Por favor seleccione otra.</div>';
            }
        }

        if (status == 1) {
            var param_almacen = '';
            var param_punto_venta = '';
            $('#img-loading').removeClass('hidden');
            setTimeout(function () {
                $('#img-loading').addClass('hidden');
            }, 2000); // 2000 milisegundos = 2 segundos



            $('#btn-save').attr('disabled', true);

            // Crear FormData para el envío del formulario
            var formDataObj = new FormData();

            // Agregar campos obligatorios
            var campos = {
                'module': 'trabajadores',
                'method': 'save',
                'action': action,
                'nombre': $('#f-nombre').val(),
                'apellidos': $('#f-apellidos').val(),
                'sexo': $('#f-sexo').val(),
                'carnet_identidad': $('#f-ci').val(),
                'edad': $('#f-edad').val(),
                'direccion': $('#f-direccion').val(),
                'telefono': $('#f-telefono').val(),
                'email': $('#f-email').val(),
                'nivel_educacional': $('#f-nivel').val(),
                'cargos_id': $('#f-cargo').val(),
                'fecha_contratacion': $('#f-contratacion').val() || new Date().toISOString().split('T')[0],
                'estatus': $('#f-estatus').val() || 'activo'
            };

            // Agregar cada campo al FormData
            for (var key in campos) {
                formDataObj.append(key, campos[key]);
            }

            // Agregar fecha de baja solo si el checkbox está marcado y tiene valor
            if ($('#check-fecha-baja').is(':checked') && $('#f-baja').val()) {
                formDataObj.append('fecha_baja', $('#f-baja').val());
            }
            if ($('#f-bolsa').val()) formDataObj.append('bolsa_empleo_id', $('#f-bolsa').val());

            // Agregar foto si se seleccionó
            if ($('#f-foto')[0].files[0]) {
                formDataObj.append('foto', $('#f-foto')[0].files[0]);
            }

            // Agregar foto si se ha seleccionado
            if ($('#f-foto')[0].files[0]) {
                formDataObj.append('foto', $('#f-foto')[0].files[0]);
            }

            // Agregar parámetros de control
            formDataObj.append('module', 'trabajadores');
            formDataObj.append('method', 'save');
            formDataObj.append('action', action);

            // Agregar ID si es una actualización
            if (action === 'update') {
                formDataObj.append('id', $('#f-id').val());
            }
            // Debug: mostrar datos que se van a enviar
            console.log('Enviando datos:');
            for (var pair of formDataObj.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: formDataObj,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (d) {
                    console.log('Respuesta del servidor:', d);
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        if (d.action == 'insert') {
                            action = 'update';
                            $('#f-id').val(d.id);
                        }
                        $('#f-pass').val('');
                        notify(
                            'success',
                            '¡Éxito!',
                            action === 'insert' ? 'El trabajador ha sido registrado correctamente.' : 'Los datos del trabajador han sido actualizados correctamente.',
                            5000
                        );
                    } else {
                        notify('danger', 'Guardar datos', d.msg, 3000);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                    notify('danger', 'Error al guardar', response, 5000);
                }
            });
        } else {
            notify('danger', 'Guardar datos', msg, 3000);
        }
    });

    // Inicializar estado del checkbox de fecha baja al cargar la página
    $('#check-fecha-baja').trigger('change');

    // Manejador para el botón de ver detalles
    $('#table-recursos').on('click', '.view-recurso', function () {
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
        // html += addRow('Trabajador', rowData.nombre_trabajador || 'No asignado');
        html += addRow('Estado', formatoEstado(rowData.estado, rowData));
        html += addRow('Fecha de Entrega', formatoFecha(rowData.fecha_entrega_a_t));
        html += addRow('Fecha de Devolución', formatoFecha(rowData.fecha_entrega_a_rh) || 'Pendiente');
        html += addRow('Categoría', rowData.categoria);
        html += addRow('Descripción', rowData.descripcion);

        html += '</table>';
        html += '</div>';
        html += '</div>';

        // Mostrar el modal
        $('#modalBody2').html(html);
        $('#recursoModal').modal('show');
    });
    $('#table-recursos').on('click', '.edit-fecha-entrega', function () {
        var $tr = $(this).closest('tr');
        var $table = $tr.closest('table');

        var index = $tr.data('index');
        var allData = $table.bootstrapTable('getData');
        var rowData = allData[index];

        if (!rowData || !rowData.id) {
            notify('danger', 'Editar fecha', 'No se pudo identificar el recurso seleccionado');
            return;
        }

        var valorActual = rowData.fecha_entrega_a_t || '';
        var nuevaFecha = prompt('Nueva fecha de entrega (formato AAAA-MM-DD):', valorActual);
        if (nuevaFecha === null) return;
        nuevaFecha = (nuevaFecha || '').trim();
        if (!nuevaFecha) {
            notify('warning', 'Editar fecha', 'La fecha no puede estar vacía');
            return;
        }
        if (!/^\d{4}-\d{2}-\d{2}$/.test(nuevaFecha)) {
            notify('warning', 'Editar fecha', 'Formato de fecha inválido. Use AAAA-MM-DD.');
            return;
        }

        var formData = new FormData();
        formData.append('module', 'gestion-recursos');
        formData.append('method', 'save');
        formData.append('action', 'update');
        formData.append('id', rowData.id);
        if (rowData.trabajador_id) formData.append('trabajador_id', rowData.trabajador_id);
        if (rowData.recurso_id) formData.append('recurso_id', rowData.recurso_id);
        formData.append('fecha_entrega_a_t', nuevaFecha);
        if (rowData.fecha_entrega_a_rh)
            formData.append('fecha_entrega_a_rh', rowData.fecha_entrega_a_rh);

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (d) {
                $('#table-recursos').bootstrapTable('refresh');
                notify('success', 'Fecha actualizada', 'La fecha de entrega se actualizó correctamente.');
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                notify('danger', 'Error', 'No se pudo actualizar la fecha de entrega: ' + response);
            }
        });
    });
    $('#table-recursos').on('click', '.edit-fecha-devolucion', function () {
        var $tr = $(this).closest('tr');
        var $table = $tr.closest('table');

        var index = $tr.data('index');
        var allData = $table.bootstrapTable('getData');
        var rowData = allData[index];

        if (!rowData || !rowData.id) {
            notify('danger', 'Editar fecha', 'No se pudo identificar el recurso seleccionado');
            return;
        }

        var valorActual = rowData.fecha_entrega_a_rh || '';
        var nuevaFecha = prompt('Nueva fecha de devolución (formato AAAA-MM-DD):', valorActual);
        if (nuevaFecha === null) return;
        nuevaFecha = (nuevaFecha || '').trim();
        if (!nuevaFecha) {
            notify('warning', 'Editar fecha', 'La fecha no puede estar vacía');
            return;
        }
        if (!/^\d{4}-\d{2}-\d{2}$/.test(nuevaFecha)) {
            notify('warning', 'Editar fecha', 'Formato de fecha inválido. Use AAAA-MM-DD.');
            return;
        }

        var formData = new FormData();
        formData.append('module', 'gestion-recursos');
        formData.append('method', 'save');
        formData.append('action', 'update');
        formData.append('id', rowData.id);
        if (rowData.trabajador_id) formData.append('trabajador_id', rowData.trabajador_id);
        if (rowData.recurso_id) formData.append('recurso_id', rowData.recurso_id);
        formData.append('fecha_entrega_a_rh', nuevaFecha);

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (d) {
                $('#table-recursos').bootstrapTable('refresh');
                notify('success', 'Fecha actualizada', 'La fecha de devolución se actualizó correctamente.');
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                notify('danger', 'Error', 'No se pudo actualizar la fecha de devolución: ' + response);
            }
        });
    });
    $('#table-recursos').on('click', '.delete-recurso', function () {
        if (confirm('¿Estás seguro que el trabajador ha retornado el recurso?')) {
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
            formData.append('id', rowData.id);
            if (rowData.trabajador_id) formData.append('trabajador_id', rowData.trabajador_id);
            if (rowData.recurso_id) formData.append('recurso_id', rowData.recurso_id);
            formData.append('fecha_entrega_a_rh', hoy);

            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (d) {
                    // Reagregar el recurso al select de disponibles si no está
                    try {
                        var $sel = $('#f-recurso-id');
                        if ($sel.length && rowData && rowData.recurso_id && rowData.nombre) {
                            if ($sel.find('option[value="' + rowData.recurso_id + '"]').length === 0) {
                                $sel.append(new Option(rowData.nombre, rowData.recurso_id));
                            }
                        }
                    } catch (e) { /* noop */ }

                    notify('success', 'Recurso retornado', 'Se registró la devolución con fecha de hoy.');
                    $('#table-recursos').bootstrapTable('refresh');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                    notify('danger', 'Error', 'No se pudo registrar la devolución: ' + response);
                }
            });
        }
    });

    $('#table-recursos').on('click', '.delete-recurso3', function () {
        if (confirm('¿Estás seguro de eliminar el registro de este recurso?')) {
            var $tr = $(this).closest('tr');
            var $table = $tr.closest('table');
            var index = $tr.data('index');
            var allData = $table.bootstrapTable('getData');
            var rowData = allData[index];

            var hoy = new Date().toISOString().split('T')[0];

            var formData = new FormData();
            formData.append('module', 'gestion-recursos');
            formData.append('method', 'del_registro');
            formData.append('action', 'update');
            formData.append('id', rowData.id);
            if (rowData.trabajador_id) formData.append('trabajador_id', rowData.trabajador_id);
            if (rowData.recurso_id) formData.append('recurso_id', rowData.recurso_id);
            formData.append('fecha_entrega_a_rh', hoy);

            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (d) {
                    notify('success', 'Registro eliminado', 'Se eliminó el registro con éxito.');
                    $('#table-recursos').bootstrapTable('refresh');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                    notify('danger', 'Error', 'No se pudo registrar la devolución: ' + response);
                }
            });
        }
    });


    // Event listener para eliminar documento
    $('body').on('click', '.delete-document', function () {
        var idRecurso = $(this).data('id');
        var formData = new FormData();
        formData.append('module', 'documentos');
        formData.append('method', 'del');
        formData.append('id', idRecurso);
        //alerta de confirmación
        if (!confirm('¿Desea eliminar este documento?')) return;
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                notify('success', 'Éxito', 'Se eliminó el documento con éxito');
                $('#table-documentos').bootstrapTable('refresh');
            },
            error: function (xhr, status, error) {
                notify('warning', 'Error', 'No se pudo eliminar el documento: ' + error);
            }
        });
    });
});

function formatoPedido(value, row) {
    //var s = '<div><input data-ped="' + value + '" type="checkbox" class="chk-ped"/>&nbsp;' + value + '</div>';
    if (row.xrevendedor == null)
        row.xrevendedor = 'sin agencia';
    var s = '<div>' + value + '</div>';
    s += '<div><small>' + row.xhash + '</small></div>';
    s += '<div>' + row.xrevendedor + '</div>';
    return s;
}
function formatoToolbar2(value, row) {
    var html = '<div class="btn-group">';
    // Botón de ver detalles
    html += '<button class="btn btn-success btn-icon icon-sm fa fa-eye view-recurso" ';
    html += 'data-id="' + row.id + '" title="Ver detalles"></button> ';

    // Botón de descargar
    // html += '<button class="btn btn-warning btn-icon icon-sm fa fa-download download-recurso" ';
    // html += 'data-id="' + row.id + '" title="Descargar"></button>';

    // Si el rol es 2 (trabajador) o 4 (jefe de área), no mostrar botones de eliminar/retornar
    if (rol != '2' && rol != '4') {
        if (row.fecha_entrega_a_rh == null) {
            html += '<button class="btn btn-danger btn-icon icon-sm fa fa-sign-out delete-recurso" ';
            html += 'data-id="' + row.id + '" title="Retornar"></button>';
        }
        else {
            html += '<button class="btn btn-danger btn-icon icon-sm fa fa-trash delete-recurso3" ';
            html += 'data-id="' + row.id + '" title="Eliminar"></button>';
        }
    }

    html += '</div>';
    return html;
}
function formatoToolbar(value, row) {
    var btn_edit = '<button title="Editar" data-id="' + row.xpedido_id + '" data-ref="' + row.xhash + '" class="btn btn-info btn-xs btn-icon icon-sm fa fa-edit"></button>';
    var btn_print = '<button title="Imprimir" data-id="' + row.xpedido_id + '" class="btn btn-warning btn-xs btn-icon icon-sm fa fa-print"></button>';
    var btn_del = '<button title="Eliminar" data-id="' + row.xpedido_id + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-trash"></button>';
    var btn_rescue = '<button title="Rescatar" data-id="' + row.xpedido_id + '" class="btn btn-default btn-xs btn-icon icon-sm fa fa-life-ring"></button>';
    var btn_email = '<button title="Enviar confirmación de pago" data-id="' + row.xpedido_id + '" class="btn btn-purple btn-xs btn-icon icon-sm fa fa-paper-plane"></button>';
    if (rol != '1') {
        btn_del = '';
    }
    if (row.xestado == 'F')
        btn_del = '';
    if (punto_venta != '') {
        btn_rescue = '';
        btn_email = '';
    }
    return btn_edit + '\n' + btn_rescue + '\n' + btn_print + '\n' + btn_email + '\n' + btn_del;
}
function formatoProducto(value, row) {
    var art = [], art_join = '';
    if ('items' in row)
        $.each(row.items, function (i, v) {
            var art_ele = {
                art: v.xarticulo,
                cant: $.number(v.xcantidad, 0, ',', '')
            };

            art.push(art_ele);
        });

    $.each(art, function (i, v) {
        art_join += '<div style="font-size:10px;">' + v.cant + ' ' + v.art + '</div>';
    });

    var s = '<div>' + art_join + '</div>';
    if (row.xobs != '' && row.xobs != null) {
        s += '<div><strong>Notas: </strong>' + row.xobs + '</div>';
    }

    return s;
}

function tipoFormatter(value, row, index) {
    //si tipo es 1, mostrar "Contrato de Trabajo Por Tiempo Determinado";
    //si tipo es 2, mostrar "Contrato de Trabajo Por Tiempo Indeterminado";
    //si tipo es 3, mostrar "Suplemento de Contrato";
    //si tipo es 4, mostrar "Contrato de Servicios";
    if (value === '4') {
        return 'Contrato de Servicios';
    }
    else if (value === '1') {
        return 'Contrato de Trabajo Por Tiempo Indeterminado';
    } else if (value === '2') {
        return 'Contrato de Trabajo Por Tiempo Determinado';
    }
    else {
        return 'Suplemento de Contrato';
    }
}

// Formatter para la columna Opciones -> Ver PDF
function pdfFormatter(value, row, index) {
    var url = value || row.archivo || row.archivo_contrato;
    var filename = (function (u) {
        try {
            var p = u.split('?')[0];
            var parts = p.split('/');
            var parts2 = p.split('\\');
            return parts2[parts2.length - 1] || 'contrato.pdf';
        } catch (e) {
            return 'contrato.pdf';
        }
    })(url);

    // Verificar si es una URL válida
    if (!url) {
        return '<span class="text-muted">No disponible</span>';
    }

    return '<div class="btn-group">' +
        '<a href="' + url + '" target="_blank" class="btn btn-success btn-sm" data-toggle="tooltip" title="Vista previa"><i class="fa fa-eye"></i></a>' +
        '<a href="' + url + '" download="' + filename + '" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Descargar PDF"><i class="fa fa-download"></i></a>' +
        // Agregar botón eliminar (manejado por evento global)
        '<button data-id="' + row.id + '" class="btn btn-danger btn-sm delete-contract" data-toggle="tooltip" title="Eliminar"><i class="fa fa-trash"></i></button>' +
        '</div>';
}

function pdfFormatter2(value, row, index) {
    var url = value || row.archivo || row.archivo_contrato;
    var filename = (function (u) {
        try {
            var p = u.split('?')[0];
            var parts = p.split('/');
            var parts2 = p.split('\\');
            return parts2[parts2.length - 1] || 'contrato.pdf';
        } catch (e) {
            return 'contrato.pdf';
        }
    })(url);

    // Verificar si es una URL válida
    if (!url) {
        return '<span class="text-muted">No disponible</span>';
    }

    var html = '<div class="btn-group">' +
        '<a href="' + url + '" target="_blank" class="btn btn-success btn-sm" data-toggle="tooltip" title="Vista previa"><i class="fa fa-eye"></i></a>' +
        '<a href="' + url + '" download="' + filename + '" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Descargar PDF"><i class="fa fa-download"></i></a>';

    // Si el rol no es 2, mostrar botón de eliminar
    if (rol != '2') {
        html += '<button data-id="' + row.id + '" class="btn btn-danger btn-sm delete-document" data-toggle="tooltip" title="Eliminar"><i class="fa fa-trash"></i></button>';
    }

    html += '</div>';
    return html;
}

// Formatter para salario
function salarioFormatter(value, row, index) {
    if (!value || value == 0) {
        return '<span class="text-muted">No especificado</span>';
    }
    return '<strong>' + parseFloat(value).toFixed(2) + ' CUP</strong>';
}

// Formatter para estado del contrato (es_actual)
function estadoContratoFormatter(value, row, index) {
    if (value == 1 || value === '1') {
        return '<span class="label label-success">Activo</span>';
    } else {
        var html = '<span class="label label-default">Inactivo</span>';
        // Mostrar fecha fin si está disponible
        if (row.fecha_fin) {
            html += '<br><small class="text-muted">Finalizado: ' + row.fecha_fin + '</small>';
        }
        return html;
    }
}

// Formatter para opciones de contrato (eliminar y descargar)
function opcionesContratoFormatter(value, row, index) {
    var btnDelete = '<button class="btn btn-danger btn-sm delete-contrato" data-id="' + row.id + '" title="Eliminar contrato">' +
        '<i class="fa fa-trash"></i>' +
        '</button>';

    var btnDownload = '<button class="btn btn-primary btn-sm download-contrato" data-id="' + row.id + '" data-trabajador-id="' + row.trabajador_id + '" title="Descargar Contrato">' +
        '<i class="fa fa-download"></i>' +
        '</button>';

    return '<div class="btn-group">' + btnDownload + ' ' + btnDelete + '</div>';
}

// Handler para descargar contrato específico
$(document).on('click', '.download-contrato', function () {
    var contratoId = $(this).data('id');
    var trabajadorId = $(this).data('trabajador-id');

    if (!contratoId || !trabajadorId) {
        notify('danger', 'Error', 'Datos de contrato incompletos', 3000);
        return;
    }

    // Crear formulario invisible para descargar el archivo Word
    var form = $('<form>', {
        'method': 'POST',
        'action': 'api-app.php',
        'target': '_blank'
    });

    form.append($('<input>', { 'type': 'hidden', 'name': 'module', 'value': 'contratos' }));
    form.append($('<input>', { 'type': 'hidden', 'name': 'method', 'value': 'generate' }));
    form.append($('<input>', { 'type': 'hidden', 'name': 'trabajador_id', 'value': trabajadorId }));
    form.append($('<input>', { 'type': 'hidden', 'name': 'contract_id', 'value': contratoId }));

    // Agregar al body, enviar y eliminar
    form.appendTo('body').submit().remove();
});

// Guardar contrato nuevo (btn-guardar-contrato2)
$('#btn-guardar-contrato2').click(function () {
    var $btn = $(this);

    // Obtener valores del formulario
    var trabajadorId = $('input[name="trabajador_id"]').val();
    var tipoContrato = $('#tipo_contrato_contrato').val();
    var fechaInicio = $('#fecha_inicio_contrato').val();
    var salario = $('#salario_contrato').val();
    var cargo = $('#cargo_contrato').val();

    // Validaciones
    if (!tipoContrato) {
        notify('danger', 'Error', 'Debe seleccionar el tipo de contrato', 3000);
        return;
    }

    if (!fechaInicio) {
        notify('danger', 'Error', 'Debe seleccionar la fecha de inicio', 3000);
        return;
    }

    if (!salario || salario <= 0) {
        notify('danger', 'Error', 'Debe ingresar un salario válido', 3000);
        return;
    }

    if (!cargo) {
        notify('danger', 'Error', 'Debe seleccionar un cargo', 3000);
        return;
    }

    // Deshabilitar botón mientras se procesa
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    // Preparar datos para enviar
    var formData = new FormData();
    formData.append('module', 'contratos');
    formData.append('method', 'save-nuevo');
    formData.append('trabajador_id', trabajadorId);
    formData.append('tipo_contrato', tipoContrato);
    formData.append('fecha_inicio', fechaInicio);
    formData.append('salario', salario);
    formData.append('cargo', cargo);

    // Enviar petición AJAX
    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');

            if (response.status === 1) {
                notify('success', '¡Éxito!', response.msg || 'Contrato guardado correctamente', 3000);
                $('#modalContrato').modal('hide');
                $('#form-contrato-anterior2')[0].reset();

                // Refrescar tabla de contratos si existe
                if ($('#table-panel').length) {
                    $('#table-panel').bootstrapTable('refresh');
                }
            } else {
                notify('danger', 'Error', response.msg || 'Error al guardar el contrato', 4000);
            }
        },
        error: function (xhr, status, error) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');

            var errorMsg = 'Error al guardar el contrato';
            if (xhr.responseText) {
                try {
                    var errorData = JSON.parse(xhr.responseText);
                    errorMsg = errorData.msg || errorMsg;
                } catch (e) {
                    errorMsg = xhr.responseText;
                }
            }
            notify('danger', 'Error', errorMsg, 5000);
        }
    });
});

// Handler para eliminar contratos
$(document).on('click', '.delete-contrato', function () {
    var contratoId = $(this).data('id');

    if (!contratoId) {
        notify('danger', 'Error', 'ID de contrato no válido', 3000);
        return;
    }

    // Confirmar eliminación
    if (!confirm('¿Está seguro de que desea eliminar este contrato? Esta acción no se puede deshacer.')) {
        return;
    }

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: {
            module: 'contratos',
            method: 'del',
            id: contratoId
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                notify('success', '¡Éxito!', response.msg || 'Contrato eliminado correctamente', 3000);
                // Refrescar tabla
                if ($('#table-panel').length) {
                    $('#table-panel').bootstrapTable('refresh');
                }
            } else {
                notify('danger', 'Error', response.msg || 'Error al eliminar el contrato', 4000);
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
            }
        },
        error: function (xhr, status, error) {
            var errorMsg = 'Error al eliminar el contrato';
            if (xhr.responseText) {
                try {
                    var errorData = JSON.parse(xhr.responseText);
                    errorMsg = errorData.msg || errorMsg;
                } catch (e) {
                    errorMsg = xhr.responseText;
                }
            }
            notify('danger', 'Error', errorMsg, 5000);
            $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
        }
    });
});

// Validar archivo al seleccionarlo
$('#archivo_contrato').change(function () {
    var file = this.files[0];
    var fileType = file.type;
    var validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    if (!file) {
        return;
    }

    if (validTypes.indexOf(fileType) === -1) {
        notify('danger', 'Error', 'El archivo debe ser PDF o Word (doc/docx)', 4000);
        $(this).val('');
        return;
    }

    // Validar tamaño (máximo 10MB)
    if (file.size > 10 * 1024 * 1024) {
        notify('danger', 'Error', 'El archivo no debe superar los 10MB', 4000);
        $(this).val('');
        return;
    }
});

function timeStringToSeconds(timeStr) {
    if (!timeStr) return 0;
    const [hours, minutes, seconds] = timeStr.split(':').map(Number);
    return hours * 3600 + minutes * 60 + seconds;
}

function dateTimeFormatter(value, row) {
    if (!value) return '-';
    var parts = value.split(' ');
    if (parts.length < 2) return value;
    var dateParts = parts[0].split('-');
    var timeParts = parts[1].split(':');
    if (dateParts.length < 3 || timeParts.length < 2) return value;
    return dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + ' ' + timeParts[0] + ':' + timeParts[1];
}
// Abrir modal de vacaciones
// Función para calcular días laborables (sin fines de semana)
function calcularDiasLaborables(fechaInicio, fechaFin) {
    // Parse seguro para evitar issues de timezone
    function parseYMD(ymd) {
        if (!ymd) return NaN;
        var p = ('' + ymd).split('-');
        if (p.length !== 3) return NaN;
        return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
    }


    var inicio = parseYMD(fechaInicio);
    var fin = parseYMD(fechaFin);
    if (isNaN(inicio) || isNaN(fin) || fin < inicio) return 0;

    // Normalizar a medianoche
    inicio.setHours(0, 0, 0, 0);
    fin.setHours(0, 0, 0, 0);

    var diasLaborables = 0;
    // Clonar para no mutar inicio
    for (var current = new Date(inicio); current <= fin; current.setDate(current.getDate() + 1)) {
        var diaSemana = current.getDay(); // 0=Dom, 6=Sáb
        if (diaSemana !== 0 && diaSemana !== 6) diasLaborables++;
    }
    return diasLaborables;
}

$('#btn-add-new-vacaciones').click(function () {
    var id = $('#f-id').val();
    if (!id) {
        notify('warning', 'Error', 'No se ha seleccionado un trabajador', 3000);
        return;
    }
    $('#trabajador_id_vac').val(id);
    $('#fecha_inicio_vac').val('');
    $('#fecha_fin_vac').val('');
    $('#info-dias-vacaciones').remove();

    // Obtener vacaciones_acc del trabajador desde la API
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: {
            module: 'trabajadores',
            method: 'get-vacaciones-acc',
            id: id
        },
        dataType: 'json',
        success: function (response) {
            var vacacionesAcc = 0;
            var vacacionesCongeladas = 0;
            if (response.status === 1 && response.vacaciones_acc !== undefined) {
                vacacionesAcc = parseFloat(response.vacaciones_acc) || 0;
                vacacionesCongeladas = parseFloat(response.vacaciones_congeladas) || 0;
            }

            // Base para los cálculos = disponible real (acumulado menos congelado),
            // coherente con la validación del backend al solicitar vacaciones.
            var vacacionesDisponiblesBase = (response.vacaciones_disponibles !== undefined)
                ? (parseFloat(response.vacaciones_disponibles) || 0)
                : Math.max(0, vacacionesAcc - vacacionesCongeladas);

            // Guardar la base disponible para los cálculos dinámicos
            // También guardar acumuladas y congeladas para el desglose, y el mes actual del servidor
            $('#modalPlanificacionVacaciones').data('vacaciones-acc-base', vacacionesDisponiblesBase);
            $('#modalPlanificacionVacaciones').data('vacaciones-acumuladas', vacacionesAcc);
            $('#modalPlanificacionVacaciones').data('vacaciones-congeladas', vacacionesCongeladas);
            
            // Calcular días disponibles para el mes actual usando vacaciones_acc como base
            var hoy = new Date();
            var mesActual = hoy.getMonth(); // 0-11
            var DIAS_POR_MES = 2.18;
            var MAX_DIAS_ANIO = 24;
            
            // Guardar mes actual para los cálculos dinámicos
            $('#modalPlanificacionVacaciones').data('mes-actual', mesActual);

            // Calcular días disponibles partiendo del saldo libre (acumulado - congelado)
            // El mes actual está a 0 meses de distancia, así que suma 0
            var mesDiferencia = mesActual - mesActual; // = 0 para el mes actual
            var diasPorMes = DIAS_POR_MES * mesDiferencia;
            var diasDisponiblesCalculados = Math.min(vacacionesDisponiblesBase + diasPorMes, MAX_DIAS_ANIO);

            console.log('Modal Vacaciones - Acumuladas:', vacacionesAcc, 'Congeladas:', vacacionesCongeladas, 'Disponible base:', vacacionesDisponiblesBase, 'Mes actual:', mesActual + 1, 'Días calculados:', diasDisponiblesCalculados);

            // Mostrar días disponibles calculados
            $('#dias-disponibles-display').text(diasDisponiblesCalculados.toFixed(2));

            // Mostrar desglose de vacaciones con diseño mejorado
            var desgloseHtml = '<div class="desglose-item">' +
                              '<span class="desglose-item-label">Vacaciones Acumuladas</span>' +
                              '<div class="desglose-item-valor">' + vacacionesAcc.toFixed(2) + '</div>' +
                              '<span class="desglose-item-unidad">días</span>' +
                              '</div>' +
                              '<div class="desglose-item">' +
                              '<span class="desglose-item-label">Congeladas (planificadas)</span>' +
                              '<div class="desglose-item-valor">' + vacacionesCongeladas.toFixed(2) + '</div>' +
                              '<span class="desglose-item-unidad">días</span>' +
                              '</div>' +
                              '<div class="desglose-item desglose-total">' +
                              '<span class="desglose-item-label">Total Disponible</span>' +
                              '<div class="desglose-item-valor">' + diasDisponiblesCalculados.toFixed(2) + '</div>' +
                              '<span class="desglose-item-unidad">días</span>' +
                              '</div>';
            $('#desglose-vacaciones').html(desgloseHtml);

            // Cambiar color según disponibilidad
            if (diasDisponiblesCalculados === 0) {
                $('#dias-disponibles-display').removeClass('badge-success badge-warning').addClass('badge-danger');
            } else if (diasDisponiblesCalculados <= 5) {
                $('#dias-disponibles-display').removeClass('badge-success badge-danger').addClass('badge-warning');
            } else {
                $('#dias-disponibles-display').removeClass('badge-warning badge-danger').addClass('badge-success');
            }

            $('#modalPlanificacionVacaciones').modal('show');
        },
        error: function () {
            // Si hay error, usar cálculo por defecto
            var hoy = new Date();
            var mesActual = hoy.getMonth();
            var DIAS_POR_MES = 2.18;
            var MAX_DIAS_ANIO = 24;
            var diasDisponiblesCalculados = 0; // El mes actual es 0
            
            $('#dias-disponibles-display').text(diasDisponiblesCalculados.toFixed(2));
            $('#modalPlanificacionVacaciones').data('vacaciones-acc-base', 0);
            $('#modalPlanificacionVacaciones').data('vacaciones-acumuladas', 0);
            $('#modalPlanificacionVacaciones').data('vacaciones-congeladas', 0);
            $('#modalPlanificacionVacaciones').data('mes-actual', mesActual);
            $('#modalPlanificacionVacaciones').modal('show');
        }
    });
});

function formatoAprobacion(value, row, index) {
    if (value) {
        return '<span class="label label-success">Aprobado</span>';
    }
    return '<span class="label label-warning">Pendiente</span>';
}

function formatoAprobacionVacaciones(value, row, index) {
    // Usar el nuevo campo estado para mostrar el estado actual del flujo de aprobación
    var estado = row.estado || 'Pendiente';
    
    switch(estado) {
        case 'Pendiente':
            return '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pendiente</span>';
        case 'Aprobado Area':
            return '<span class="label label-info"><i class="fa fa-user"></i> Aprobado por Área (Pendiente de aprobación final)</span>';
        case 'Aprobado':
            return '<span class="label label-success"><i class="fa fa-check-circle"></i> Aprobado Final</span>';
        case 'Procesada':
            return '<span class="label label-info"><i class="fa fa-plane"></i> Disfrutada</span>';
        case 'Rechazado Area':
            return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Rechazado por Área</span>';
        case 'Rechazado':
            return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Rechazado Final</span>';
        default:
            return '<span class="label label-default"><i class="fa fa-question-circle"></i> Desconocido</span>';
    }
}

// Formatter para la columna "Días": muestra un icono ojo solo si está aprobada
function diasFormatter(value, row, index) {
    var total = (value || row.dias_totales || 0);
    if (row) {
        var diasEncoded = encodeURIComponent(row.dias || '');
        return '<span>' + total + ' <button class="btn btn-default btn-xs ver-dias" data-dias="' + diasEncoded + '" data-id="' + (row.id || '') + '" title="Ver días"><i class="fa fa-eye"></i></button></span>';
    }
    return '' + total;
}

// Click delegado para mostrar los días en un modal pequeño (reusa el modal de confirmación)
$(document).on('click', '.ver-dias', function (e) {
    e.preventDefault();
    var diasEncoded = $(this).attr('data-dias') || '';
    var diasStr = '';
    try { diasStr = decodeURIComponent(diasEncoded); } catch (ex) { diasStr = diasEncoded; }
    var arr = diasStr ? diasStr.split(',') : [];
    var html = '<ul class="list-unstyled">';
    if (arr.length === 0) {
        html += '<li>No hay días registrados</li>';
    } else {
        arr.forEach(function (d) { html += '<li>' + d + '</li>'; });
    }
    html += '</ul>';

    // Mostrar la lista como notificación (reutiliza notify si está disponible)
    var messageHtml = '<div><strong>Días del Plan de Vacaciones:</strong></div>' + html;
    try {
        // Preferir notificación elegante si está disponible
        notify('info', 'Días de vacaciones', messageHtml, 8000);
    } catch (ex) {
        // Fallback a alert simple
        alert(arr.join('\n') || 'No hay días registrados');
    }
});

function accionesVacaciones(value, row, index) {
    var estado = row.estado || 'Pendiente';
    
    // Si está rechazada (cualquier tipo de rechazo), solo mostrar eliminar para administrador
    if (estado === 'Rechazado' || estado === 'Rechazado Area') {
        // Solo administrador (rol=1) puede eliminar rechazados
        if (rol == '1') {
            return '<div class="btn-group btn-group-sm">'
                + '<button class="btn btn-danger btn-sm btn-eliminar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'data-fecha-inicio="' + (row.fecha_inicio || '') + '" '
                + 'title="Eliminar registro">'
                + '<i class="fa fa-trash"></i>'
                + '</button>'
                + '</div>';
        }
        return '';
    }
    
    // Si está aprobada (finalmente), solo rol=1 puede eliminar
    if (estado === 'Aprobado') {
        if (rol == '1') {
            return '<div class="btn-group btn-group-sm">'
                + '<button class="btn btn-danger btn-sm btn-eliminar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'data-fecha-inicio="' + (row.fecha_inicio || '') + '" '
                + 'title="Eliminar registro">'
                + '<i class="fa fa-trash"></i>'
                + '</button>'
                + '</div>';
        }
        return '';
    }
    
    // Si está pendiente o aprobado por área
    if (estado === 'Pendiente' || estado === 'Aprobado Area') {
        // Para rol=1: mostrar aprobar y rechazar
        if (rol == '1') {
            return '<div class="btn-group btn-group-sm">'
                + '<button class="btn btn-success btn-sm btn-aprobar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'data-dias="' + (row.dias_totales || 0) + '" '
                + 'title="Aprobar vacación">'
                + '<i class="fa fa-check"></i>'
                + '</button>'
                + ' '
                + '<button class="btn btn-warning btn-sm btn-rechazar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'title="Rechazar solicitud">'
                + '<i class="fa fa-times"></i>'
                + '</button>'
                + '</div>';
        }
        // Para rol=4: mostrar aprobar y rechazar (jefe de área)
        else if (rol == '4') {
            return '<div class="btn-group btn-group-sm">'
                + '<button class="btn btn-success btn-sm btn-aprobar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'data-dias="' + (row.dias_totales || 0) + '" '
                + 'title="Aprobar vacación">'
                + '<i class="fa fa-check"></i>'
                + '</button>'
                + ' '
                + '<button class="btn btn-warning btn-sm btn-rechazar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'title="Rechazar solicitud">'
                + '<i class="fa fa-times"></i>'
                + '</button>'
                + '</div>';
        }
        // Para rol=2: mostrar eliminar (solo si está pendiente)
        else if (rol == '2' && estado === 'Pendiente') {
            return '<div class="btn-group btn-group-sm">'
                + '<button class="btn btn-danger btn-sm btn-eliminar-vacacion" '
                + 'data-id="' + row.id + '" '
                + 'data-trabajador-id="' + row.trabajador_id + '" '
                + 'data-fecha-inicio="' + (row.fecha_inicio || '') + '" '
                + 'title="Eliminar registro">'
                + '<i class="fa fa-trash"></i>'
                + '</button>'
                + '</div>';
        }
    }
    
    return '';
}

$(document).on('click', '.btn-rechazar-vacacion', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var trabajadorId = $(this).data('trabajador-id');
    if (!id) return;
    if (!confirm('¿Desea rechazar esta solicitud de vacaciones?')) return;
    
    // Determinar el método según el rol
    var method = (rol == '1') ? 'rechazar-final' : 'rechazar-area';
    
    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: { module: 'vacaciones', method: method, id: id, trabajador_id: trabajadorId },
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                notify('success', 'Éxito', 'Solicitud rechazada correctamente');
                $('#table-vacaciones').bootstrapTable('refresh');
                // Actualizar días disponibles
                $.ajax({
                    url: 'api-app.php',
                    type: 'GET',
                    data: {
                        module: 'vacaciones',
                        method: 'get-dias-disponibles',
                        trabajador_id: trabajadorId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 1) {
                            var diasDisponibles = response.dias_disponibles || 0;
                            $('#dias-disponibles-display').text(diasDisponibles);
                        }
                    }
                });
            } else {
                notify('danger', 'Error', 'Error al rechazar solicitud');
            }
        }
    });
});

$(document).on('click', '.btn-eliminar-vacacion', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var trabajadorId = $(this).data('trabajador-id');
    var fechaInicioStr = $(this).data('fecha-inicio');
    if (!id) return;
    // Validación en cliente: no permitir eliminar si la fecha de inicio es anterior a hoy
    if (fechaInicioStr) {
        try {
            var hoy = new Date(); hoy.setHours(0, 0, 0, 0);
            var partes = ('' + fechaInicioStr).split('-');
            if (partes.length === 3) {
                var fechaInicio = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
                if (fechaInicio < hoy) {
                    notify('warning', 'No permitido', 'No se puede eliminar un plan de vacaciones que ya inició.');
                    return;
                }
            }
        } catch (ex) { /* en caso de error, continuar; el backend valida */ }
    }
    if (!confirm('¿Desea eliminar este registro de vacaciones?')) return;
    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: { module: 'vacaciones', method: 'del', id: id },
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                notify('success', 'Éxito', 'Registro eliminado correctamente');
                $('#table-vacaciones').bootstrapTable('refresh');
                // Actualizar días disponibles
                $.ajax({
                    url: 'api-app.php',
                    type: 'GET',
                    data: {
                        module: 'vacaciones',
                        method: 'get-dias-disponibles',
                        trabajador_id: trabajadorId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 1) {
                            var diasDisponibles = response.dias_disponibles || 0;
                            $('#dias-disponibles-display').text(diasDisponibles);
                        }
                    }
                });
            } else {
                notify('danger', 'Error', 'Error al eliminar registro');
            }
        }
    });
});

// Inicializar calendario y selección de días (solo una vez)
(function () {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    var calendar; // instancia de FullCalendar
    var selectedDates = new Set();
    var plannedDates = new Set(); // días ya planificados en planes existentes (no seleccionables)
    var vacacionesAccBase = 0; // Variable para almacenar el valor base de vacaciones_acc

    function formatYMD(d) {
        var y = d.getFullYear();
        var m = (d.getMonth() + 1).toString().padStart(2, '0');
        var day = d.getDate().toString().padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function renderSelectedInfo() {
        var arr = Array.from(selectedDates).sort();
        var total = arr.length;
        if ($('#info-dias-vacaciones').length === 0) {
            $('#vacation-form').after('<div id="info-dias-vacaciones" class="alert alert-info mt-2"></div>');
        }
        $('#info-dias-vacaciones').html('<i class="fa fa-calendar"></i> <strong>Días seleccionados:</strong> ' + total + '<br/>' + (arr.slice(0, 30).join(', ')) + (arr.length > 30 ? ' ...' : ''));
        // actualizar inputs fecha inicio/fin con el primero y último seleccionado
        if (arr.length > 0) {
            $('#fecha_inicio_vac').val(arr[0]);
            $('#fecha_fin_vac').val(arr[arr.length - 1]);
        } else {
            $('#fecha_inicio_vac').val('');
            $('#fecha_fin_vac').val('');
        }
    }

    function toggleDateSelection(dateStr) {
        // No permitir seleccionar sábado ni domingo: las vacaciones solo cuentan días laborables.
        var diaSemana = new Date(dateStr + 'T00:00:00').getDay(); // 0=domingo, 6=sábado
        if (diaSemana === 0 || diaSemana === 6) {
            notify('warning', 'Día no válido', 'No se pueden seleccionar sábados ni domingos para vacaciones', 3000);
            return;
        }

        // No permitir seleccionar un día que ya está planificado en otro plan del trabajador
        if (plannedDates.has(dateStr)) {
            notify('warning', 'Día ya planificado', 'Este día ya forma parte de un plan de vacaciones existente', 3000);
            return;
        }

        // Verificar límite máximo de 24 días
        if (!selectedDates.has(dateStr) && selectedDates.size >= 24) {
            notify('warning', 'Límite de días', 'No puede seleccionar más de 24 días de vacaciones', 3000);
            return;
        }
        
        if (selectedDates.has(dateStr)) {
            selectedDates.delete(dateStr);
            var ev = calendar.getEventById(dateStr);
            if (ev) ev.remove();
        } else {
            selectedDates.add(dateStr);
            calendar.addEvent({ id: dateStr, title: '', start: dateStr, allDay: true, display: 'background', backgroundColor: '#7ad1ff' });
        }
        renderSelectedInfo();
    }

    function initCalendar() {
        if (calendar) return;

        // Obtener año actual
        var anioActual = new Date().getFullYear();

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            selectable: true,
            showNonCurrentDates: false,
            // Limitar el rango de fechas: desde enero hasta diciembre del año actual
            validRange: {
                start: anioActual + '-01-01', // 1 de enero del año actual
                end: (anioActual + 1) + '-01-01' // 1 de enero del año siguiente (no incluido)
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            dateClick: function (info) {
                var dateStr = info.dateStr; // YYYY-MM-DD
                toggleDateSelection(dateStr);
            },
            // Evento que se dispara cuando cambia la vista (mes)
            datesSet: function (dateInfo) {
                // dateInfo.start contiene la fecha de inicio de la vista actual
                var fechaVista = dateInfo.start;
                var mesVista = fechaVista.getMonth(); // 0-11
                var anioVista = fechaVista.getFullYear();

                var DIAS_POR_MES = 2.18;
                var MAX_DIAS_ANIO = 24;
                var mesActualGuardado = $('#modalPlanificacionVacaciones').data('mes-actual') || new Date().getMonth();

                // Recalcular dinámicamente según el mes visualizado
                // dias_disponibles = vacaciones_acc + (2.18 × (mes_vista - mes_actual))
                // Ejemplo: Si hoy es enero (0) y vemos febrero (1): 5 + (2.18 × (1-0)) = 5 + 2.18 = 7.18
                var mesDiferencia = mesVista - mesActualGuardado;
                var diasPorMes = DIAS_POR_MES * mesDiferencia;
                var diasDisponibles = Math.min(vacacionesAccBase + diasPorMes, MAX_DIAS_ANIO);

                console.log('FullCalendar - Mes visualizado:', mesVista + 1, 'Año:', anioVista, 'Vacaciones_acc base:', vacacionesAccBase, 'Días disponibles:', diasDisponibles);

                // Actualizar el badge con el valor dinámico
                $('#dias-disponibles-display').text(diasDisponibles.toFixed(2));

                // Recuperar acumuladas y congeladas guardadas al abrir el modal para el desglose
                var vacAcumuladas = $('#modalPlanificacionVacaciones').data('vacaciones-acumuladas') || 0;
                var vacCongeladas = $('#modalPlanificacionVacaciones').data('vacaciones-congeladas') || 0;

                // Mostrar desglose actualizado con diseño mejorado
                var desgloseHtml = '<div class="desglose-item">' +
                                  '<span class="desglose-item-label">Vacaciones Acumuladas</span>' +
                                  '<div class="desglose-item-valor">' + vacAcumuladas.toFixed(2) + '</div>' +
                                  '<span class="desglose-item-unidad">días</span>' +
                                  '</div>' +
                                  '<div class="desglose-item">' +
                                  '<span class="desglose-item-label">Congeladas (planificadas)</span>' +
                                  '<div class="desglose-item-valor">' + vacCongeladas.toFixed(2) + '</div>' +
                                  '<span class="desglose-item-unidad">días</span>' +
                                  '</div>' +
                                  '<div class="desglose-item desglose-total">' +
                                  '<span class="desglose-item-label">Total Disponible</span>' +
                                  '<div class="desglose-item-valor">' + diasDisponibles.toFixed(2) + '</div>' +
                                  '<span class="desglose-item-unidad">días</span>' +
                                  '</div>';
                $('#desglose-vacaciones').html(desgloseHtml);

                // Cambiar color según disponibilidad
                var $badge = $('#dias-disponibles-display');
                $badge.removeClass('badge-success badge-warning badge-danger');
                if (diasDisponibles === 0) {
                    $badge.addClass('badge-danger');
                } else if (diasDisponibles <= 5) {
                    $badge.addClass('badge-warning');
                } else {
                    $badge.addClass('badge-success');
                }
            },
            events: []
        });
        calendar.render();
    }

    // Cargar y pintar en el calendario los días ya planificados del trabajador
    // (planes existentes no rechazados), como eventos de fondo informativos.
    function loadPlannedDays(trabajadorId) {
        if (!trabajadorId || !calendar) return;
        // Limpiar marcas de planificados previas
        calendar.getEvents().forEach(function (e) {
            if (e.id && e.id.indexOf('planned-') === 0) e.remove();
        });
        plannedDates.clear();

        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: { module: 'vacaciones', method: 'list-id', trabajador_id: trabajadorId },
            dataType: 'json',
            success: function (planes) {
                if (!Array.isArray(planes)) return;
                planes.forEach(function (plan) {
                    var estado = plan.estado || '';
                    // Ignorar planes rechazados; mostrar Pendiente/Aprobado/Procesada
                    if (estado === 'Rechazado' || estado === 'Rechazado Area') return;
                    if (!plan.dias) return;

                    // Color según estado del plan
                    var color = '#f0ad4e';      // Pendiente -> naranja
                    var titulo = 'Pendiente';
                    if (estado === 'Aprobado' || estado === 'Aprobado Area') { color = '#28a745'; titulo = 'Aprobado'; }
                    else if (estado === 'Procesada') { color = '#1a3e72'; titulo = 'Disfrutada'; }

                    plan.dias.split(',').forEach(function (d) {
                        d = (d || '').trim();
                        if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) return;
                        plannedDates.add(d);
                        calendar.addEvent({
                            id: 'planned-' + plan.id + '-' + d,
                            title: titulo,
                            start: d,
                            allDay: true,
                            display: 'background',
                            backgroundColor: color
                        });
                    });
                });
            }
        });
    }

    // Cuando se abre el modal, inicializar calendario y limpiar selección
    $('#modalPlanificacionVacaciones').on('shown.bs.modal', function () {
        // Obtener PRIMERO el valor de vacaciones_acc que fue calculado al abrir el modal
        // Este valor fue guardado en el handler de #btn-add-new-vacaciones
        vacacionesAccBase = $('#modalPlanificacionVacaciones').data('vacaciones-acc-base') || 0;
        
        console.log('Modal abierto - vacaciones_acc base:', vacacionesAccBase);
        
        // LUEGO inicializar el calendario con el valor correcto de vacacionesAccBase
        initCalendar();
        
        // reset selección
        selectedDates.clear();
        // remover eventos previos
        if (calendar) {
            var evs = calendar.getEvents();
            evs.forEach(function (e) { e.remove(); });
        }
        $('#info-dias-vacaciones').remove();
        // Ocultar los inputs Fecha Inicio y Fecha Fin visuales (los valores seguirán guardándose en los inputs)
        try {
            $('#fecha_inicio_vac').closest('.col-md-3').hide();
        } catch (e) { }
        try {
            $('#fecha_fin_vac').closest('.col-md-3').hide();
        } catch (e) { }

        // Pintar los días ya planificados del trabajador (después de limpiar la selección)
        var trabajadorIdVac = $('#trabajador_id_vac').val() || $('#f-id').val();
        loadPlannedDays(trabajadorIdVac);
    });

    // Exponer método para obtener selección cuando se guarda
    window.__vacaciones_get_selected_dates = function () {
        return Array.from(selectedDates).sort();
    };

})();

// Actualizar contador de días cuando cambian las fechas
$('#fecha_inicio_vac, #fecha_fin_vac').on('change', function () {
    var fechaInicio = $('#fecha_inicio_vac').val();
    var fechaFin = $('#fecha_fin_vac').val();

    if (fechaInicio && fechaFin) {
        var diasLaborables = calcularDiasLaborables(fechaInicio, fechaFin);

        // Mostrar información al usuario
        if ($('#info-dias-vacaciones').length === 0) {
            $('#vacation-form').after('<div id="info-dias-vacaciones" class="alert alert-info mt-2"></div>');
        }

        $('#info-dias-vacaciones').html(
            '<i class="fa fa-calendar"></i> <strong>Días laborables seleccionados:</strong> ' + diasLaborables +
            ' (no incluye fines de semana)'
        );
    }
});

// Manejador para guardar vacaciones
$('#btn-guardar-vacaciones').click(function () {
    var $btn = $(this);
    var trabajadorId = $('#trabajador_id_vac').val();
    var fechaInicio = $('#fecha_inicio_vac').val();
    var fechaFin = $('#fecha_fin_vac').val();
    // intentar obtener selección de días individual (si el calendario está activo)
    var selected_dates = [];
    if (typeof window.__vacaciones_get_selected_dates === 'function') {
        selected_dates = window.__vacaciones_get_selected_dates() || [];
    }

    // Validaciones
    if (!trabajadorId) {
        notify('warning', 'Error', 'No se ha seleccionado un trabajador', 3000);
        return;
    }

    if (!fechaInicio || !fechaFin) {
        notify('warning', 'Error', 'Debe seleccionar las fechas de inicio y fin', 3000);
        return;
    }

    // Se admite un solo día (fin == inicio); solo se rechaza si el fin es anterior.
    if (new Date(fechaFin) < new Date(fechaInicio)) {
        notify('warning', 'Error', 'La fecha de fin no puede ser anterior a la fecha de inicio', 3000);
        return;
    }
    
    // Validar que no supere 24 días
    var diasSolicitados = selected_dates.length > 0 ? selected_dates.length : calcularDiasLaborables(fechaInicio, fechaFin);
    if (diasSolicitados > 24) {
        notify('warning', 'Error', 'No puede solicitar más de 24 días de vacaciones', 3000);
        return;
    }

    var diasLaborables = 0;
    var diasString = '';
    if (selected_dates && selected_dates.length > 0) {
        diasString = selected_dates.join(',');
        diasLaborables = selected_dates.length;
        // usar first/last como inicio/fin
        fechaInicio = selected_dates[0];
        fechaFin = selected_dates[selected_dates.length - 1];
    } else {
        // fallback a rango de fechas con cálculo de días laborables (sin fines de semana)
        diasLaborables = calcularDiasLaborables(fechaInicio, fechaFin);
        // construir diasString según días laborables entre inicio y fin
        var arrDias = [];
        var cur = new Date(fechaInicio);
        var endd = new Date(fechaFin);
        cur.setHours(0, 0, 0, 0); endd.setHours(0, 0, 0, 0);
        for (var d = new Date(cur); d <= endd; d.setDate(d.getDate() + 1)) {
            var day = d.getDay(); if (day === 0 || day === 6) continue; // saltar fines de semana
            var y = d.getFullYear(); var m = (d.getMonth() + 1).toString().padStart(2, '0'); var da = d.getDate().toString().padStart(2, '0');
            arrDias.push(y + '-' + m + '-' + da);
        }
        diasString = arrDias.join(',');
    }

    // Un rango que solo cubre fin de semana no deja ningún día que solicitar.
    if (diasLaborables === 0) {
        notify('warning', 'Error', 'El rango seleccionado no contiene días laborables', 3000);
        return;
    }

    // Confirmar con el usuario
    var textoDias = diasLaborables === 1 ? '1 día' : diasLaborables + ' días';
    var textoRango = (fechaInicio === fechaFin) ? 'el ' + fechaInicio : 'del ' + fechaInicio + ' al ' + fechaFin;
    if (!confirm('¿Desea solicitar ' + textoDias + ' de vacaciones (' + textoRango + ')?')) {
        return;
    }

    // Deshabilitar botón para evitar múltiples clics
    $btn.prop('disabled', true);

    // Mostrar indicador de carga
    if ($('#img-loading').length) {
        $('#img-loading').removeClass('hidden');
    }

    // Preparar datos para enviar
    var formData = new FormData();
    formData.append('module', 'vacaciones');
    formData.append('method', 'save');
    formData.append('trabajador_id', trabajadorId);
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    if (diasString) formData.append('dias', diasString);

    // Enviar solicitud al servidor
    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                var mensaje = response.msg || 'Vacaciones guardadas correctamente';
                if (response.dias_solicitados && response.dias_disponibles) {
                    var diasRestantes = response.dias_disponibles - response.dias_solicitados;
                    mensaje += '<br>Días solicitados: ' + response.dias_solicitados +
                        '<br>Días disponibles restantes: ' + diasRestantes;

                    // Actualizar el display de días disponibles
                    $('#dias-disponibles-display').text(diasRestantes);
                    if (diasRestantes === 0) {
                        $('#dias-disponibles-display').removeClass('badge-success badge-warning').addClass('badge-danger');
                    } else if (diasRestantes <= 5) {
                        $('#dias-disponibles-display').removeClass('badge-success badge-danger').addClass('badge-warning');
                    } else {
                        $('#dias-disponibles-display').removeClass('badge-warning badge-danger').addClass('badge-success');
                    }
                }
                notify('success', '¡Éxito!', mensaje, 5000);
                $('#modalPlanificacionVacaciones').modal('hide');
                // Limpiar formulario
                $('#fecha_inicio_vac').val('');
                $('#fecha_fin_vac').val('');
                $('#info-dias-vacaciones').remove();
                // Recargar tabla de vacaciones
                if ($('#table-vacaciones').length) {
                    $('#table-vacaciones').bootstrapTable('refresh');
                }
            } else {
                notify('danger', 'Error', response.msg || 'Error al guardar las vacaciones', 5000);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al guardar vacaciones:', error);
            var errorMsg = 'Error al comunicarse con el servidor';
            if (xhr.responseText) {
                try {
                    var errorData = JSON.parse(xhr.responseText);
                    errorMsg = errorData.msg || errorMsg;
                } catch (e) {
                    errorMsg = xhr.responseText;
                }
            }
            notify('danger', 'Error', errorMsg, 5000);
        },
        complete: function () {
            $btn.prop('disabled', false);
            if ($('#img-loading').length) {
                $('#img-loading').addClass('hidden');
            }
        }
    });
});

// Guardar contrato

// Handler para aprobar vacaciones (solo administradores y jefes de área)
$(document).on('click', '.btn-aprobar-vacacion', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var vacacionId = $btn.data('id');
    var trabajadorId = $btn.data('trabajador-id');
    var diasSolicitados = $btn.data('dias');

    if (!confirm('¿Está seguro de aprobar esta solicitud de ' + diasSolicitados + ' días de vacaciones?\n\nEsta acción descontará:\n- Los días del saldo disponible\n- El equivalente en salario acumulado')) {
        return;
    }

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

    // Determinar el método según el rol
    var method = (rol == '1') ? 'aprobar-final' : 'aprobar-area';

    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: {
            module: 'vacaciones',
            method: method,
            id: vacacionId,
            trabajador_id: trabajadorId
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                var mensaje = response.msg || 'Vacación aprobada correctamente';
                if (response.dias_descontados && response.dias_restantes !== undefined) {
                    mensaje += '<br><strong>Días descontados:</strong> ' + response.dias_descontados +
                        '<br><strong>Días restantes:</strong> ' + response.dias_restantes;
                }
                if (response.descuento_salario && response.descuento_salario > 0) {
                    mensaje += '<br><strong>Descuento salario:</strong> $' + parseFloat(response.descuento_salario).toFixed(2) +
                        '<br><strong>Salario anterior:</strong> $' + parseFloat(response.salario_acc_anterior || 0).toFixed(2) +
                        '<br><strong>Salario nuevo:</strong> $' + parseFloat(response.salario_acc_nuevo || 0).toFixed(2);
                }
                notify('success', '¡Aprobado!', mensaje, 6000);

                // Recargar tabla de vacaciones
                if ($('#table-vacaciones').length) {
                    $('#table-vacaciones').bootstrapTable('refresh');
                }

                // Actualizar display de días disponibles si está visible
                if (response.dias_restantes !== undefined && $('#dias-disponibles-display').length) {
                    $('#dias-disponibles-display').text(response.dias_restantes);
                    if (response.dias_restantes === 0) {
                        $('#dias-disponibles-display').removeClass('badge-success badge-warning').addClass('badge-danger');
                    } else if (response.dias_restantes <= 5) {
                        $('#dias-disponibles-display').removeClass('badge-success badge-danger').addClass('badge-warning');
                    } else {
                        $('#dias-disponibles-display').removeClass('badge-warning badge-danger').addClass('badge-success');
                    }
                }
            } else {
                notify('danger', 'Error', response.msg || 'No se pudo aprobar la vacación', 5000);
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Aprobar');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al aprobar vacación:', error);
            var errorMsg = 'Error al comunicarse con el servidor';
            if (xhr.responseText) {
                try {
                    var errorData = JSON.parse(xhr.responseText);
                    errorMsg = errorData.msg || errorMsg;
                } catch (e) {
                    errorMsg = xhr.responseText;
                }
            }
            notify('danger', 'Error', errorMsg, 5000);
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Aprobar');
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

$('#btn-asignar-contrato-trabajador').click(function () {
    // Crear formulario dinámico
    var form = $('#form-contrato-anterior');

    form.append($('<input>', {
        type: 'hidden',
        name: 'module',
        value: 'contratos'
    }));

    form.append($('<input>', {
        type: 'hidden',
        name: 'method',
        value: 'generate'
    }));

    // Agregar el form al body y enviarlo

    form.submit();

});

// Inicializar tooltips
$(document).ready(function () {
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });
});
