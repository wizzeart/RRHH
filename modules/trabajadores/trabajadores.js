var e, tmp, cmd_params;

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


    // Aplicar la funcionalidad a todos los campos de fecha
    makeDateFieldClickable('#f-contratacion');
    
    // Quitado el manejo de fecha de baja del formulario

    function normalizarCorreoJs(c) {
        c = (c || '').toLowerCase().trim();
        var mapa = {'á':'a','à':'a','ä':'a','â':'a','ã':'a','é':'e','è':'e','ë':'e','ê':'e','í':'i','ì':'i','ï':'i','î':'i','ó':'o','ò':'o','ö':'o','ô':'o','õ':'o','ú':'u','ù':'u','ü':'u','û':'u','ñ':'n','ç':'c'};
        c = c.replace(/[áàäâãéèëêíìïîóòöôõúùüûñç]/g, function(ch){ return mapa[ch] || ch; });
        c = c.replace(/[^a-z0-9@._-]/g, '');
        return c;
    }
    function esEmailValido(c) {
        return /^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(c);
    }
    function soloLetrasYEspacios(s) {
        s = (s || '');
        s = s.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
        s = s.replace(/\s{2,}/g, ' ');
        return s.trimStart();
    }
    $('#f-nombre, #f-apellidos, #f-apellidos-segundos').on('input', function(){
        var val = $(this).val();
        var filtrado = soloLetrasYEspacios(val);
        if (val !== filtrado) $(this).val(filtrado);
    });
    $('#f-email').on('input', function(){
        var norm = normalizarCorreoJs($(this).val());
        if (norm !== $(this).val()) {
            $(this).val(norm);
        }
    });
    $('#f-email').on('blur', function(){
        var norm = normalizarCorreoJs($(this).val());
        $(this).val(norm);
        if (norm && !esEmailValido(norm)) {
            notify('warning', 'Correo inválido', 'Por favor ingrese un correo electrónico válido.', 3000);
            $(this).focus();
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

    $('#f-foto').change(function() {
        var $input = $(this);
        var file = this.files && this.files[0] ? this.files[0] : null;

        fotoEsValida = true;

        if ($input.next('.preview-container').length === 0) {
            $input.after('<div class="preview-container mt-2" style="display:none"><img src="" style="max-width: 100px; height: auto;"></div>');
        }
        var $img = $input.next('.preview-container').find('img');

        if (!file) {
            $img.attr('src', '');
            $input.next('.preview-container').hide();
            return;
        }

        if (!file.type || !file.type.startsWith('image/')) {
            fotoEsValida = false;
            $img.attr('src', '');
            $input.val('');
            $input.next('.preview-container').hide();
            notify('danger', 'Archivo inválido', 'Solo se permiten archivos en formato imagen (JPEG, PNG, GIF, etc.).', 4000);
            return;
        }

        try {
            var objectUrl = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function() {
                $img.attr('src', objectUrl);
                $input.next('.preview-container').show();
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
        if ($('#f-nombre').val() && !/^([\p{L}\s])+$/u.test($('#f-nombre').val())) {
            status = 0;
            msg += '<div>El campo Nombre solo debe contener letras.</div>';
        }
        if ($('#f-apellidos').val() && !/^([\p{L}\s])+$/u.test($('#f-apellidos').val())) {
            status = 0;
            msg += '<div>El campo Apellidos solo debe contener letras.</div>';
        }
        if ($('#f-apellidos-segundos').val() && !/^([\p{L}\s])+$/u.test($('#f-apellidos-segundos').val())) {
            status = 0;
            msg += '<div>El campo Segundo Apellido solo debe contener letras.</div>';
        }
        if ($('#f-apellidos-segundos').val() == '') {
            status = 0;
            msg += '<div>El campo Segundo Apellido del Trabajador es obligatorio.</div>';
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
        var emailNorm = normalizarCorreoJs($('#f-email').val());
        $('#f-email').val(emailNorm);
         if ($('#f-email').val() == '') {
            status = 0;
            msg += '<div>El campo Correo del Trabajador es obligatorio.</div>';
        } else {
            if (!esEmailValido($('#f-email').val())) {
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

        if ($('#f-departamento').val() == '') {
            status = 0;
            msg += '<div>El campo Departamento del Trabajador es obligatorio.</div>';
        }

        if ($('#f-cargo').val() == '') {
            status = 0;
            msg += '<div>El campo Cargo del Trabajador es obligatorio.</div>';
        }

        if ($('#f-bolsa').val() == '') {
            status = 0;
            msg += '<div>El campo Bolsa de Empleo es obligatorio.</div>';
        }

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
            }, 2000);
            
            $('#btn-save').attr('disabled', true);

            var formDataObj = new FormData();

            var campos = {
                'module': 'trabajadores',
                'method': 'save',
                'action': action,
                'nombre': $('#f-nombre').val(),
                'apellidos': $('#f-apellidos').val(),
                'apellidos_segundos': $('#f-apellidos-segundos').val(),
                'sexo': $('#f-sexo').val(),
                'carnet_identidad': $('#f-ci').val(),
                'edad': $('#f-edad').val(),
                'direccion': $('#f-direccion').val(),
                'provincia_id': $('#f-provincia').val(),
                'municipio_id': $('#f-municipio').val(),
                'telefono': $('#f-telefono').val(),
                'tarjeta_salario': $('#f-tarjeta_salario').val(),
                'cuenta_estandar': $('#f-cuenta_estandar').val(),
                'email': $('#f-email').val(),
                'nivel_educacional': $('#f-nivel').val(),
                'departamento_id': $('#f-departamento').val(),
                'cargos_id': $('#f-cargo').val(),
                'fecha_contratacion': $('#f-contratacion').val() || new Date().toISOString().split('T')[0],
                'estatus': $('#f-estatus').val() || 'activo'
            };

            for (var key in campos) {
                formDataObj.append(key, campos[key]);
            }

            if ($('#f-bolsa').val()) formDataObj.append('bolsa_empleo_id', $('#f-bolsa').val());
            
            if ($('#f-foto')[0].files[0]) {
                formDataObj.append('foto', $('#f-foto')[0].files[0]);
            }
            
            formDataObj.append('module', 'trabajadores');
            formDataObj.append('method', 'save');
            formDataObj.append('action', action);

            if (action === 'update') {
                formDataObj.append('id', $('#f-id').val());
            }
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
                            notify(
                                'success',
                                '¡Éxito!',
                                'El trabajador ha sido registrado correctamente. Redirigiendo...',
                                1500
                            );
                            setTimeout(function() {
                                window.location.href = '?module=list-trabajadores';
                            }, 1500);
                        } else {
                            notify(
                                'success',
                                '¡Éxito!',
                                'Los datos del trabajador han sido actualizados correctamente.',
                                3000
                            );
                        }
                        $('#f-pass').val('');
                    } else {
                        notify('danger', 'Error al guardar', d.msg, 3000);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
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
function formatoFechas(value, row) {
    var s = '<div>' + value + '</div>';
    if (row.xfecha_entregado_format != '' && row.xfecha_entregado_format != null) {
        s += '<div><small>Fecha Entregado: ' + row.xfecha_entregado_format + '</small></div>';
    }
    if (row.xfecha_finalizado_format != '' && row.xfecha_finalizado_format != null) {
        s += '<div><small>Fecha Finalizado: ' + row.xfecha_finalizado_format + '</small></div>';
    }
    return s;
}
function formatoCantidad(value, row) {
    var s = '';
    if (row.xestado == 'P')
        value = '';
    return s;
}
function formatoEstado(value, row) {
    var s = '';
    s = '<span class="label label-' + row.xcolor + '">' + value + '</span>';
    //console.debug(s);
    return s;
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

// Filtrado dinámico de municipios basado en provincia seleccionada
$(document).ready(function() {
    // Función para filtrar municipios
    function filterMunicipios(provinciaId) {
        var $municipioSelect = $('#f-municipio');
        var $municipioOptions = $municipioSelect.find('option');
        
        // Mostrar solo la opción por defecto
        $municipioOptions.hide();
        $municipioSelect.find('option[value=""]').show();
        
        if (provinciaId && provinciaId !== '') {
            // Mostrar municipios de la provincia seleccionada
            $municipioOptions.each(function() {
                var $option = $(this);
                if ($option.data('provincia') == provinciaId || $option.val() === '') {
                    $option.show();
                }
            });
        } else {
            // Si no hay provincia seleccionada, mostrar todos los municipios
            $municipioOptions.show();
        }
        
        // Resetear selección de municipio
        $municipioSelect.val('');
    }
    
    // Función para cargar municipios vía AJAX (alternativa más eficiente)
    function loadMunicipiosAjax(provinciaId) {
        var $municipioSelect = $('#f-municipio');
        
        if (!provinciaId || provinciaId === '') {
            // Limpiar select de municipios
            $municipioSelect.html('<option value="">Seleccionar Municipio</option>');
            return;
        }
        
        // Mostrar loading
        $municipioSelect.html('<option value="">Cargando...</option>');
        
        // Hacer petición AJAX
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: {
                module: 'trabajadores',
                method: 'get-municipios',
                provincia_id: provinciaId
            },
            dataType: 'json',
            success: function(data) {
                var options = '<option value="">Seleccionar Municipio</option>';
                
                if (data && Array.isArray(data)) {
                    data.forEach(function(municipio) {
                        options += '<option value="' + municipio.id + '">' + municipio.nombre + '</option>';
                    });
                }
                
                $municipioSelect.html(options);
            },
            error: function() {
                $municipioSelect.html('<option value="">Error cargando municipios</option>');
                console.error('Error cargando municipios para provincia:', provinciaId);
            }
        });
    }
    
    // Event listener para cambio de provincia
    $('#f-provincia').on('change', function() {
        var provinciaId = $(this).val();
        
        // Usar filtrado por atributos data (más rápido) o AJAX (más eficiente en memoria)
        // Puedes cambiar entre filterMunicipios() y loadMunicipiosAjax()
        loadMunicipiosAjax(provinciaId);
    });
    
    // Inicializar filtrado al cargar la página si hay una provincia preseleccionada
    var initialProvincia = $('#f-provincia').val();
    if (initialProvincia && initialProvincia !== '') {
        loadMunicipiosAjax(initialProvincia);
    }
});