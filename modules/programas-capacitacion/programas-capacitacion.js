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
    // UI alert helper (Bootstrap-like)

    function showAlert(type, title, message) {
        // type: 'success' | 'danger' | 'warning' | 'info'
        var $container = $('.panel-body').first();
        if ($container.length === 0) {
            $container = $('.panel').last();
        }
        if ($container.length === 0) {
            $container = $('body');
        }
        
        var html = '<div class="alert alert-' + type + ' alert-dismissible" role="alert" style="margin-bottom:12px; margin-top:12px;">'
                 + '  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                 + '  <strong>' + (title || '') + '</strong> ' + (message || '')
                 + '</div>';
        
        // remove previous alerts of same type to reduce clutter
        $container.find('.alert.alert-' + type).remove();
        
        // Insert after the first panel or at the beginning of container
        if ($container.hasClass('panel-body')) {
            $container.prepend(html);
        } else {
            $container.find('.panel').first().after(html);
            if ($container.find('.alert').length === 0) {
                $container.prepend(html);
            }
        }
        
        // auto dismiss after 6s
        setTimeout(function(){ 
            $container.find('.alert.alert-' + type).fadeOut(400, function(){ 
                $(this).remove(); 
            }); 
        }, 6000);
    }
    // Helper to escape HTML for safe insertion into hidden inputs
    function escapeHtml(str) {
        if (typeof str !== 'string') return str || '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // Función para limpiar el formulario
    function limpiarFormulario() {
        $('#form-programa')[0].reset();
        $('#f-id').val('');
        $('#f-modalidad').val('Presencial'); // Valor por defecto
        // Actualizar la URL para nuevo registro
        window.history.replaceState({}, '', 'index.php?module=programas-capacitacion');
    }

    // Mejorar funcionalidad de campos de fecha
    $('#f-fecha-estimada').on('click focus', function() {
        $(this)[0].showPicker();
    });

    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-programas-capacitacion';
    });

    $('#btn-new').click(function () {
        location.href = 'index.php?module=programas-capacitacion';
    });

    $('#btn-save').click(function () {
        // Validaciones mejoradas con mensajes específicos
        var errores = [];
        
        if ($('#f-tema').val().trim() == '') {
            errores.push('El tema de capacitación es obligatorio');
            $('#f-tema').addClass('is-invalid');
        } else {
            $('#f-tema').removeClass('is-invalid');
        }
        
        if ($('#f-dirigido').val() == '') {
            errores.push('Debe especificar a quién va dirigido el programa');
            $('#f-dirigido').addClass('is-invalid');
        } else {
            $('#f-dirigido').removeClass('is-invalid');
        }
        
        if ($('#f-responsable').val().trim() == '') {
            errores.push('El responsable del programa es obligatorio');
            $('#f-responsable').addClass('is-invalid');
        } else {
            $('#f-responsable').removeClass('is-invalid');
        }
        
        if ($('#f-fecha-estimada').val() == '') {
            errores.push('La fecha estimada es obligatoria');
            $('#f-fecha-estimada').addClass('is-invalid');
        } else {
            $('#f-fecha-estimada').removeClass('is-invalid');
        }

        if ($('#f-modalidad').val() == '') {
            errores.push('La modalidad del programa es obligatoria');
            $('#f-modalidad').addClass('is-invalid');
        } else {
            $('#f-modalidad').removeClass('is-invalid');
        }

        if ($('#f-horas').val() == '' || $('#f-horas').val() <= 0) {
            errores.push('Las horas de duración son obligatorias y deben ser mayor a 0');
            $('#f-horas').addClass('is-invalid');
        } else {
            $('#f-horas').removeClass('is-invalid');
        }

        if (errores.length > 0) {
            notify('warning', 'Validación', errores.join('<br>'), 5000);
            // Enfocar el primer campo con error
            $('.is-invalid').first().focus();
            return;
        }

        var cmd = $('#form-programa').serialize() + '&module=programas-capacitacion&method=save';
        
        // Mostrar spinner y deshabilitar botón
        $('#img-loading').removeClass('hidden');
        $('#btn-save').attr('disabled', true);
        
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: cmd,
            dataType: 'json',
            success: function (d) {
                $('#img-loading').addClass('hidden');
                $('#btn-save').attr('disabled', false);
                
                console.log('Respuesta del servidor:', d); // Debug temporal
                if (d.status == 1) {
                    // Mensaje de éxito mejorado
                    if ($('#f-id').val() == '') {
                        // Nuevo registro
                        $('#f-id').val(d.id);
                        notify('success', '¡Registro Exitoso!', 'El programa de capacitación ha sido registrado correctamente en el sistema. ID: ' + d.id, 5000);
                        showAlert('success', '¡Registro Exitoso!', 'El programa de capacitación ha sido registrado correctamente. ID: ' + d.id);
                        
                        // Actualizar URL para edición
                        window.history.replaceState({}, '', 'index.php?module=programas-capacitacion&id=' + d.id);
                    } else {
                        // Actualización
                        notify('success', '¡Actualización Exitosa!', 'Los datos del programa de capacitación han sido actualizados correctamente', 5000);
                        showAlert('success', '¡Actualización Exitosa!', 'Los datos del programa han sido actualizados correctamente.');
                    }
                } else {
                    console.log('Error del servidor:', d.msg); // Debug temporal
                    notify('danger', 'Error al Guardar', d.msg || 'Error al guardar el programa de capacitación', 5000);
                    showAlert('danger', 'Error al Guardar:', d.msg || 'Error al guardar el programa de capacitación');
                }
            },
            error: function(xhr, status, error) {
                $('#img-loading').addClass('hidden');
                $('#btn-save').attr('disabled', false);

                var responseText = xhr && xhr.responseText ? xhr.responseText.trim() : '';
                var title = 'Error al Guardar';
                var message = 'No se pudo conectar con el servidor';
                // Intentar parsear JSON válido si existe
                try {
                    if (responseText && responseText.charAt(0) === '{') {
                        var jd = JSON.parse(responseText);
                        if (typeof jd === 'object') {
                            if (jd.msg_title) title = jd.msg_title;
                            if (jd.msg) message = jd.msg;
                        }
                    } else if (error) {
                        message = error;
                    }
                } catch (e) {
                    if (error) message = error;
                }

                notify('danger', title, message, 5000);
                showAlert('danger', title, message);
            }
        });
    });

    // Validación en tiempo real para campos obligatorios
    $('#f-tema, #f-responsable').on('blur', function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-dirigido, #f-modalidad').on('change', function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-fecha-estimada').on('change', function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-horas').on('blur', function() {
        if ($(this).val() === '' || $(this).val() <= 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Agregar estilos CSS para campos inválidos si no existen
    if (!$('#validation-styles').length) {
        $('<style id="validation-styles">')
            .text('.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }')
            .appendTo('head');
    }
});
