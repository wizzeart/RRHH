var e, tmp, cmd_params;
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

function firmadoFormatter(value, row, index) {
    var firmado = (value && String(value).trim() !== '' && value !== 'null' && value !== null);
    if (firmado) {
      return '<span class="label label-success">Firmado</span>';
    }
    return '<span class="label label-warning">No firmado</span>';
  }

$(document).ready(function () {
    $('#btn-ver-tarjeta').on('click', function () {
        window.open('?module=list-tarjetas-snc&id='+$('#f-id').val(), '_blank');
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
       

    $('#btn-add-new-doc').on('click', function() {

        $('#docForm').on('submit', function(e) {
            e.preventDefault();
            var tipo_doc = $('#tipo_doc').val();
            var archivo = $('#file_doc')[0].files[0];
            
            
            if (!archivo) {
                notify('danger', 'Error', 'Debe seleccionar un archivo');
                return;
            }
            
            if (!tipo_doc) {
                notify('danger', 'Error', 'Debe escribir un tipo de documento');
                return;
            }
            var formData = new FormData();
            formData.append('trabajador_id', $('#f-id').val());
            formData.append('tipo_doc', tipo_doc);
            formData.append('archivo', archivo);
            
            formData.append('module', 'documentos');
            formData.append('method', 'save');
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#documentoModal').modal('hide');
                    notify('success', 'Documento agregado', 'El documento se ha agregado correctamente');
                    $('#table-documentos').bootstrapTable('refresh');
                },
                error: function(xhr, status, error) {
                    notify('danger', 'Error', 'Hubo un error al agregar el documento');
                }
            });
        });
        
        $('#documentoModal').modal('show');
        
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
            try { alert(text); } catch(e) { console.warn('Notify:', text); }
        }
    }
    // Flag to track if selected photo is a valid image
    var fotoEsValida = true;
    $('#f-almacen').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    $('#f-punto-venta').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    // Hacer que los campos de fecha sean completamente clickeables para abrir el calendario
    function makeDateFieldClickable(fieldId) {
        $(fieldId).on('click', function() {
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
        $(fieldId).parent().on('click', function(e) {
            if (e.target !== $(fieldId)[0]) {
                $(fieldId).focus();
                if ($(fieldId)[0].showPicker) {
                    $(fieldId)[0].showPicker();
                }
            }
        });
    }

    // Control del checkbox para fecha de baja
    $('#check-fecha-baja').on('change', function() {
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
    $('#f-baja').on('click', function() {
        if (!$(this).prop('disabled')) {
            this.focus();
            if (this.showPicker) {
                this.showPicker();
            }
        }
    });

    $('#btn-test').click(function () {
        var cmd = 'module=tools&method=test';
        cmd_params=cmd;
        $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
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
                    alert('Error al guardar: ' + XMLHttpRequest.responseText);                var cmd = 'module=tools&method=log-error&ref=' + encodeURIComponent('ERROR PANEL')
                        + '&data=' + encodeURIComponent(XMLHttpRequest.responseText)
                        + '&params=' + encodeURIComponent(cmd_params);
                $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                    success: function (d) {},
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
    // Función para manejar la previsualización de imágenes
    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(previewId).attr('src', e.target.result);
                $(previewId).parent().show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Validación y previsualización de foto (solo imágenes reales)
    $('#f-foto').change(function() {
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
            img.onload = function() {
                // Es una imagen válida: mostrar preview
                $img.attr('src', objectUrl);
                $input.next('.preview-container').show();
                // Liberar URL cuando la imagen en el DOM termine de cargar
                $img.on('load', function() { URL.revokeObjectURL(objectUrl); });
                fotoEsValida = true;
            };
            img.onerror = function() {
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
            setTimeout(function() {
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
        $('#modalBody2').html(html);
        $('#recursoModal').modal('show');
    });

    // Event listener para eliminar documento
    $('body').on('click', '.delete-document', function() {
        var idRecurso = $(this).data('id');
        var formData = new FormData();
        formData.append('module', 'documentos');
        formData.append('method', 'del');
        formData.append('id', idRecurso);
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
            notify('success', 'Éxito', 'Se eliminó el documento con éxito');
            $('#table-documentos').bootstrapTable('refresh');
            },
            error: function(xhr, status, error) {
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
    html += '<button class="btn btn-warning btn-icon icon-sm fa fa-download download-recurso" ';
    html += 'data-id="' + row.id + '" title="Descargar"></button>';
    
    
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

    return  s;
}

function tipoFormatter(value, row, index) {
  //si tipo es 1, mostrar "Contrato de Trabajo Por Tiempo Determinado";
  //si tipo es 2, mostrar "Contrato de Trabajo Por Tiempo Indeterminado";
  if (value === '1') {
    return 'Contrato de Trabajo Por Tiempo Indeterminado';
  } else if (value === '2') {
    return 'Contrato de Trabajo Por Tiempo Determinado';
  }
}

// Formatter para la columna Opciones -> Ver PDF
function pdfFormatter(value, row, index) {
    var url = value || row.archivo || row.archivo_contrato;
    var filename = (function(u){ 
        try { 
            var p = u.split('?')[0]; 
            var parts = p.split('/'); 
            var parts2 = p.split('\\');
            return parts2[parts2.length-1] || 'contrato.pdf'; 
        } catch(e){ 
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
           '</div>';
}




function pdfFormatter2(value, row, index) {
    var url = value || row.archivo || row.archivo_contrato;
    var filename = (function(u){ 
        try { 
            var p = u.split('?')[0]; 
            var parts = p.split('/'); 
            var parts2 = p.split('\\');
            return parts2[parts2.length-1] || 'contrato.pdf'; 
        } catch(e){ 
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
           '<button data-id="' + row.id + '" class="btn btn-danger btn-sm delete-document" data-toggle="tooltip" title="Eliminar"><i class="fa fa-trash"></i></button>' +
           '</div>';
}

function formatoAprobacion(value, row, index) {
    if (value) {
        return '<span class="label label-success">Aprobado</span>';
    }
    return '<span class="label label-warning">Pendiente</span>';
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
function formatoTransaccion(value, row) {
    if (value == null)
        value = '';
    var s = '<div>' + value + '</div>';
    if (value = 'REVENDEDOR') {
        if (row.xrevendedor == null)
            row.xrevendedor = '';
        s += '<small>' + row.xrevendedor + '</small>';
    }
    return s;
}
function formatoNivel(value, row) {
    var tag = '', n = '', s;
    if (row.xnivel == 1)
        n = 'danger';
    if (row.xnivel == 2)
        n = 'warning';
    if (row.xnivel == 3)
        n = 'success';
    if (row.xnivel == 5)
        n = 'pink';
    if (row.xnivel == 6)
        n = 'info';
    if (row.xnivel == 7)
        n = 'black';
    if (n != '')
        tag = '<span class="pull-right badge badge-' + n + '">' + row.xnivel + '</span>';
    //tag = ' <span class="label label-table label-' + n + '">' + row.xnivel + '</span>';
    //s = value + tag;
    s = '<a target="_blank" class="text-primary" href="?module=clientes&id=' + row.xcliente_id + '">' + value + '</a>' + tag;
    //<span class="label label-table label-success">Enterprise</span>
    //return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
    return s;
}

 // $('#btn-add-new').click(function(){ location.href = 'index.php?module=contratos'; });

  // Abrir modal de contrato anterior
  $('#btn-add-anterior').click(function(){
    $('#form-contrato-anterior')[0].reset();
    $('.fecha-fin-group').hide();
    // Mantener el ID del trabajador después del reset
    var trabajadorId = $('input[name="trabajador_id"]').val();
    $('#modalContratoAnterior').modal('show');
    // Asegurar que el ID del trabajador permanezca
    $('input[name="trabajador_id"]').val(trabajadorId);
  });

  // Mostrar/ocultar fecha fin según tipo de contrato
  $('#tipo_contrato').change(function(){
    if($(this).val() === '2') { // 2 = Contrato Determinado
      $('.fecha-fin-group').slideDown();
      $('#fecha_fin').prop('required', true);
    } else {
      $('.fecha-fin-group').slideUp();
      $('#fecha_fin').prop('required', false).val('');
    }
  });

  // Validar archivo al seleccionarlo
  $('#archivo_contrato').change(function(){
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

  // Guardar contrato anterior
  $('#btn-guardar-contrato-anterior').click(function(){
    var $form = $('#form-contrato-anterior');
    var $submitBtn = $(this);

    // Validar formulario
    if (!$form[0].checkValidity()) {
      notify('warning', 'Formulario incompleto', 'Por favor complete todos los campos requeridos', 3000);
      return;
    }

    // Validaciones adicionales
    var fechaInicio = $('#fecha_inicio').val();
    var fechaFin = $('#fecha_fin').val();
    var tipoContrato = $('#tipo_contrato').val();

    // Validar tipo de contrato
    if (!tipoContrato || (tipoContrato !== '1' && tipoContrato !== '2')) {
      notify('warning', 'Error', 'El tipo de contrato debe ser 1 (indeterminado) o 2 (determinado)', 3000);
      return;
    }

    // Validar fecha fin para contratos determinados (tipo 2)
    if (tipoContrato === '2' && !fechaFin) {
      notify('warning', 'Formulario incompleto', 'Para contratos determinados debe especificar la fecha de fin', 3000);
      return;
    }

    if (fechaFin && new Date(fechaFin) <= new Date(fechaInicio)) {
      notify('warning', 'Error en fechas', 'La fecha de fin debe ser posterior a la fecha de inicio', 3000);
      return;
    }

    var formData = new FormData($form[0]);
    
    // Asegurar que tenemos el ID del trabajador
    var trabajadorId = $('input[name="trabajador_id"]').val();
    if (!trabajadorId) {
        notify('danger', 'Error', 'No se pudo determinar el ID del trabajador', 3000);
        return;
    }

    formData.append('module', 'contratos');
    formData.append('method', 'save-anterior');
    formData.append('firma_digital', '0'); // Por defecto, sin firma digital
    
    // Asegurar que el ID del trabajador esté en el FormData
    formData.delete('trabajador_id'); // Eliminar si existe
    formData.append('trabajador_id', trabajadorId);

    // Debug: mostrar datos que se enviarán
    console.log('Enviando datos del contrato:');
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    

    // Deshabilitar botón mientras se procesa
    $submitBtn.prop('disabled', true);

    $.ajax({
      url: 'api-app.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response){
        if (response.status === 1) {
          notify('success', '¡Éxito!', 'Contrato anterior guardado correctamente', 3000);
          $('#modalContratoAnterior').modal('hide');
          // Recargar tabla
          $('#table-panel').bootstrapTable('refresh');
        } else {
          notify('danger', 'Error', response.msg || 'Error al guardar el contrato', 4000);
        }
      },
      error: function(xhr, status, error){
        notify('danger', 'Error', 'Error al comunicarse con el servidor', 4000);
        console.error('Error:', error);
      },
      complete: function(){
        $submitBtn.prop('disabled', false);
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
      try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
  }