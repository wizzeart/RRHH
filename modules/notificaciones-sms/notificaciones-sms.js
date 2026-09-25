$(document).ready(function() {
    // Inicializar multiselects
    $('#f-departamentos').chosen({
        no_results_text: "!Oops, no hay coincidencias!", 
        width: '100%'
    });
    $('#f-ubicaciones').chosen({
        no_results_text: "!Oops, no hay coincidencias!", 
        width: '100%'
    });

    // Cargar datos de departamentos y ubicaciones
    cargarDepartamentos();
    cargarUbicaciones();

    // Contador de caracteres
    $('#f-mensaje').on('input', function() {
        var length = $(this).val().length;
        $('#char-count').text(length);
        
        if (length > 160) {
            $('#char-count').addClass('text-danger');
        } else {
            $('#char-count').removeClass('text-danger');
        }
    });

    // Botón de vista previa
    $('#btn-preview').on('click', function() {
        var departamentos = $('#f-departamentos').val();
        var ubicaciones = $('#f-ubicaciones').val();
        
        if ((!departamentos || departamentos.length === 0) && (!ubicaciones || ubicaciones.length === 0)) {
            notify('warning', 'Advertencia', 'Debe seleccionar al menos un departamento o ubicación');
            return;
        }

        $('#img-loading').removeClass('hidden');
        $('#btn-preview').prop('disabled', true);

        var cmd = 'module=notificaciones-sms&method=preview-recipients';
        if (departamentos && departamentos.length > 0) {
            cmd += '&departamentos=' + encodeURIComponent(JSON.stringify(departamentos));
        }
        if (ubicaciones && ubicaciones.length > 0) {
            cmd += '&ubicaciones=' + encodeURIComponent(JSON.stringify(ubicaciones));
        }

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: cmd,
            dataType: 'json',
            success: function(response) {
                $('#img-loading').addClass('hidden');
                $('#btn-preview').prop('disabled', false);
                
                if (response.status == 1) {
                    var html = '<div class="alert alert-info">';
                    html += '<strong>Total de destinatarios: ' + response.total + '</strong><br><br>';
                    html += '<div class="row">';
                    
                    if (response.departamentos && response.departamentos.length > 0) {
                        html += '<div class="col-md-6">';
                        html += '<strong>Por Departamentos:</strong><br>';
                        $.each(response.departamentos, function(i, depto) {
                            html += '<small>' + depto.nombre + ': ' + depto.cantidad + ' trabajadores</small><br>';
                        });
                        html += '</div>';
                    }
                    
                    if (response.ubicaciones && response.ubicaciones.length > 0) {
                        html += '<div class="col-md-6">';
                        html += '<strong>Por Ubicaciones:</strong><br>';
                        $.each(response.ubicaciones, function(i, ubicacion) {
                            html += '<small>' + ubicacion.nombre + ': ' + ubicacion.cantidad + ' trabajadores</small><br>';
                        });
                        html += '</div>';
                    }
                    
                    html += '</div></div>';
                    $('#preview-recipients').html(html);
                } else {
                    notify('danger', 'Error', response.msg);
                }
            },
            error: function(xhr, status, error) {
                $('#img-loading').addClass('hidden');
                $('#btn-preview').prop('disabled', false);
                notify('danger', 'Error', 'Error al obtener vista previa: ' + error);
            }
        });
    });

    // Botón de enviar SMS
    $('#btn-send').on('click', function() {
        var status = 1;
        var msg = '';

        var departamentos = $('#f-departamentos').val();
        var ubicaciones = $('#f-ubicaciones').val();
        var mensaje = $('#f-mensaje').val();

        if ((!departamentos || departamentos.length === 0) && (!ubicaciones || ubicaciones.length === 0)) {
            status = 0;
            msg += '<div>Debe seleccionar al menos un departamento o ubicación.</div>';
        }

        if (mensaje.trim() === '') {
            status = 0;
            msg += '<div>El campo mensaje es obligatorio.</div>';
        }

        if (mensaje.length > 160) {
            status = 0;
            msg += '<div>El mensaje no puede exceder los 160 caracteres.</div>';
        }

        if (status == 1) {
            if (!confirm('¿Está seguro de enviar este SMS a ' + 
                (departamentos ? departamentos.length : 0) + ' departamento(s) y ' + 
                (ubicaciones ? ubicaciones.length : 0) + ' ubicación(es)?')) {
                return;
            }

            $('#img-loading').removeClass('hidden');
            $('#btn-send').prop('disabled', true);

            var cmd = 'module=notificaciones-sms&method=send-sms';
            cmd += '&mensaje=' + encodeURIComponent(mensaje);
            
            if (departamentos && departamentos.length > 0) {
                cmd += '&departamentos=' + encodeURIComponent(JSON.stringify(departamentos));
            }
            if (ubicaciones && ubicaciones.length > 0) {
                cmd += '&ubicaciones=' + encodeURIComponent(JSON.stringify(ubicaciones));
            }

            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: cmd,
                dataType: 'json',
                success: function(response) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-send').prop('disabled', false);
                    
                    if (response.status == 1) {
                        notify('success', 'Éxito', response.msg);
                        // Limpiar formulario
                        $('#f-departamentos').val('').trigger('chosen:updated');
                        $('#f-ubicaciones').val('').trigger('chosen:updated');
                        $('#f-mensaje').val('');
                        $('#char-count').text('0');
                        $('#preview-recipients').html('<p class="text-muted">Seleccione departamentos y/o ubicaciones para ver los destinatarios</p>');
                    } else {
                        notify('danger', 'Error', response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-send').prop('disabled', false);
                    notify('danger', 'Error', 'Error al enviar SMS: ' + error);
                }
            });
        } else {
            notify('danger', 'Error de validación', msg);
        }
    });

    // Botón volver
    $('#btn-back').on('click', function() {
        location.href = '?module=list-notificaciones-sms';
    });
});

function cargarDepartamentos() {
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: { module: 'departamentos', method: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response) {
                var $select = $('#f-departamentos');
                $select.empty();
                
                $.each(response, function(i, depto) {
                    $select.append('<option value="' + depto.id + '">' + depto.nombre + '</option>');
                });
                
                $select.trigger('chosen:updated');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando departamentos:', error);
        }
    });
}

function cargarUbicaciones() {
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: { module: 'ubicaciones', method: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response) {
                var $select = $('#f-ubicaciones');
                $select.empty();
                
                $.each(response, function(i, ubicacion) {
                    $select.append('<option value="' + ubicacion.id + '">' + ubicacion.nombre + '</option>');
                });
                
                $select.trigger('chosen:updated');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando ubicaciones:', error);
        }
    });
}

function notify(type, title, message, timer) {
    if ($.niftyNoty) {
        $.niftyNoty({ 
            type: type||'info', 
            container:'floating', 
            title:title||'', 
            message:message||'', 
            timer: timer!=null?timer:3000, 
            closeBtn:true, 
            focus:true 
        });
    } else { 
        alert((title?title+': ':'')+message); 
    }
}
