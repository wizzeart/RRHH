var e, tmp, cmd_params;

$(document).ready(function () {
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
    makeDateFieldClickable('#f-fecha-registro');

    function soloLetrasYEspacios(s) {
        s = (s || '');
        s = s.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
        s = s.replace(/\s{2,}/g, ' ');
        return s.trimStart();
    }
    // Función para validar solo números
    function soloNumeros(valor) {
        return valor.replace(/[^0-9]/g, '');
    }

    // Aplicar validación a campos de texto para solo letras
    $('#f-nombre, #f-apellidos, #f-apellidos-segundos').on('input', function(){
        var val = $(this).val();
        var filtrado = soloLetrasYEspacios(val);
        if (val !== filtrado) $(this).val(filtrado);
    });

    // Aplicar validación a campo de teléfono para solo números
    $('#f-telefono').on('input', function(){
        var val = $(this).val();
        var filtrado = soloNumeros(val);
        if (val !== filtrado) $(this).val(filtrado);
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
        location.href = '?module=bolsas_empleos';
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-bolsas_empleos';
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

    // Previsualización de foto
    $('#f-foto').change(function() {
        if ($(this).next('.preview-container').length === 0) {
            $(this).after('<div class="preview-container mt-2" style="display:none"><img src="" style="max-width: 100px; height: auto;"></div>');
        }
        readURL(this, $(this).next('.preview-container').find('img'));
    });

    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';
        
        
         if ($('#f-nombre').val() == '') {
            status = 0;
            msg += '<div>El campo Nombre del Postulante es obligatorio.</div>';
        }

        if ($('#f-apellidos').val() == '') {
            status = 0;
            msg += '<div>El campo Apellidos del Postulante es obligatorio.</div>';
        }
        if ($('#f-nombre').val() && !/^[\p{L}\s]+$/u.test($('#f-nombre').val())) {
            status = 0;
            msg += '<div>El campo Nombre solo debe contener letras.</div>';
        }
        if ($('#f-apellidos').val() && !/^[\p{L}\s]+$/u.test($('#f-apellidos').val())) {
            status = 0;
            msg += '<div>El campo Apellidos solo debe contener letras.</div>';
        }
        if ($('#f-apellidos-segundos').val() && !/^[\p{L}\s]+$/u.test($('#f-apellidos-segundos').val())) {
            status = 0;
            msg += '<div>El campo Segundo Apellido solo debe contener letras.</div>';
        }

        if ($('#f-telefono').val() == '' && !/^[0-9]+$/.test($('#f-telefono').val())) {
            status = 0;
            msg += '<div>El campo Teléfono del Postulante es obligatorio y debe ser numérico.</div>';
        }

        if ($('#f-cargo').val() == '') {
            status = 0;
            msg += '<div>El campo Cargo del Postulante es obligatorio.</div>';
        }
       
        if ($('#f-curriculum').val() == '') {
            status = 0;
            msg += '<div>El campo Currículum es obligatorio.</div>';
        }

        if ($('#f-fecha-registro').val() == '') {
            status = 0;
            msg += '<div>El campo Fecha de Registro es obligatorio.</div>';
        }

        if ($('#f-estatus').val() == '') {
            status = 0;
            msg += '<div>El campo Estatus es obligatorio.</div>';
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

            // Agregar parámetros de control
            formDataObj.append('module', 'bolsas_empleos');
            formDataObj.append('method', 'save');
            formDataObj.append('action', action);

            // Agregar ID si es una actualización
            if (action === 'update') {
                formDataObj.append('id', $('#f-id').val());
            }

            // Agregar todos los campos del formulario
            formDataObj.append('nombre', $('#f-nombre').val());
            formDataObj.append('apellidos', $('#f-apellidos').val());
            formDataObj.append('segundos_apellidos', $('#f-apellidos-segundos').val());
            formDataObj.append('telefono', $('#f-telefono').val());
            formDataObj.append('email', $('#f-email').val());
            formDataObj.append('cargo_postulado_id', $('#f-cargo').val());
            formDataObj.append('fecha_registro', $('#f-fecha-registro').val());
            formDataObj.append('estatus', $('#f-estatus').val());
            
            // Agregar currículum si se seleccionó
            if ($('#f-curriculum')[0].files[0]) {
                formDataObj.append('curriculum', $('#f-curriculum')[0].files[0]);
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
                        $.niftyNoty({
                            type: 'success',
                            container: 'floating',
                            title: '¡Éxito!',
                            message: action === 'insert' ? 
                                'La postulación ha sido registrada correctamente.' :
                                'Los datos han sido actualizados correctamente.',
                            timer: 5000,
                            closeBtn: true,
                            focus: true
                        });
                        //esperar unos segundos
                        setTimeout(function() {
                            location.href = 'index.php?module=list-bolsas_empleos';
                        }, 1000);
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error('Error en la petición:', {
                        status: XMLHttpRequest.status,
                        statusText: XMLHttpRequest.statusText,
                        responseText: XMLHttpRequest.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    $.niftyNoty({
                        type: 'danger',
                        title: 'Error de conexión',
                        message: 'Error al guardar: ' + XMLHttpRequest.responseText,
                        container: 'floating',
                        timer: 5000
                    });
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Guardar datos',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
});


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


function formatoEstado(value, row) {
    var s = '';
    s = '<span class="label label-' + row.xcolor + '">' + value + '</span>';
    //console.debug(s);
    return s;
}
function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
