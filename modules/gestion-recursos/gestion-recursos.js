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

    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-recursos';
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

    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';
        
        
         if ($('#f-trabajador').val() == '') {
            status = 0;
            msg += '<div>Debe seleccionar un trabajador.</div>';
        }

        if ($('#f-recurso').val() == '') {
            status = 0;
            msg += '<div>El campo Recurso es obligatorio.</div>';
        }
        
        if ($('#f-fecha-registro').val() == '') {
            status = 0;
            msg += '<div>El campo Fecha de Registro es obligatorio.</div>';
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
            formDataObj.append('module', 'gestion-recursos');
            formDataObj.append('method', 'save');
            formDataObj.append('action', action);

            // Agregar ID si es una actualización
            if (action === 'update') {
                formDataObj.append('id', $('#f-id').val());
                formDataObj.append('fecha_entrega_a_rh', $('#f-fecha-devolucion').val());
            }

            // Agregar todos los campos del formulario
            formDataObj.append('trabajador_id', $('#f-trabajador').val());
            formDataObj.append('fecha_entrega_a_t', $('#f-fecha-registro').val());
            formDataObj.append('nombre', $('#f-recurso').val());
            
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

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
