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

$(document).ready(function () {
    // Helper de notificaciones: usa Nifty Noty si está disponible, si no, fallback a alert
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
         if ($('#f-estatus').val() == '') {
            status = 0;
            msg += '<div>El campo Estatus del Trabajador es obligatorio.</div>';
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

// Formatter para la columna Opciones -> Ver PDF
function pdfFormatter(value, row, index) {
    var url = value || row.archivo_contrato;
    if (url && String(url).trim() !== '' && url !== 'null') {
      var filename = (function(u){ try { var p = u.split('?')[0]; var parts = p.split('/'); return parts[parts.length-1] || 'contrato.pdf'; } catch(e){ return 'contrato.pdf'; } })(url);
      return '<button type="button" class="btn btn-danger btn-sm btn-view-pdf" data-url="' + url + '" data-toggle="tooltip" title="' + filename + '"><i class="fa fa-file-pdf-o"></i></button>';
    }
    return '<button type="button" class="btn btn-default btn-sm" disabled data-toggle="tooltip" title="Sin archivo"><i class="fa fa-file-o"></i></button>';
  }
  

function firmadoFormatter(value, row, index) {
    var firmado = (value && String(value).trim() !== '' && value !== 'null' && value !== null);
    if (firmado) {
      return '<span class="label label-success">Firmado</span>';
    }
    return '<span class="label label-warning">No firmado</span>';
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