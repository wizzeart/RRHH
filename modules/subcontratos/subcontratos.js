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
        $('#form-subcontrato')[0].reset();
        $('#f-id').val('');
        $('#f-estatus').trigger('change'); // Actualizar validación de entidad
        $('#check-fecha-fin').prop('checked', false).trigger('change'); // Desactivar fecha fin
        // Actualizar la URL para nuevo registro
        window.history.replaceState({}, '', 'index.php?module=subcontratos');
    }

    // Control del checkbox para fecha de fin
    $('#check-fecha-fin').on('change', function() {
        if ($(this).is(':checked')) {
            $('#f-fecha-fin').prop('disabled', false);
            $('#f-fecha-fin').closest('.form-group').find('.help-block').text('Seleccione la fecha de finalización del contrato');
        } else {
            $('#f-fecha-fin').prop('disabled', true).val('');
            $('#f-fecha-fin').removeClass('is-invalid');
            $('#f-fecha-fin').closest('.form-group').find('.help-block').text('Marque la casilla superior para activar este campo');
        }
    });

    // Mejorar funcionalidad de campos de fecha
    $('#f-fecha-inicio').on('click focus', function() {
        $(this)[0].showPicker();
    });
    
    $('#f-fecha-fin').on('click focus', function() {
        if (!$(this).prop('disabled')) {
            $(this)[0].showPicker();
        }
    });

    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-subcontratos';
    });

    $('#btn-new').click(function () {
        location.href = 'index.php?module=subcontratos';
    });

    $('#btn-save').click(function () {
        // Validaciones mejoradas con mensajes específicos
        var errores = [];
        
        if ($('#f-nombre').val().trim() == '') {
            errores.push('El nombre de la persona es obligatorio');
            $('#f-nombre').addClass('is-invalid');
        } else {
            $('#f-nombre').removeClass('is-invalid');
        }

        // Validación de CI (Carnet de Identidad)
        var ci = ($('#f-ci').val() || '').trim();
        var ciRegex = /^\d{11}$/;
        if (ci === '') {
            errores.push('El CI (Carnet de Identidad) es obligatorio');
            $('#f-ci').addClass('is-invalid');
        } else if (!ciRegex.test(ci)) {
            errores.push('El CI debe contener exactamente 11 dígitos numéricos');
            $('#f-ci').addClass('is-invalid');
        } else {
            $('#f-ci').removeClass('is-invalid');
        }
        
        if ($('#f-estatus').val() == '') {
            errores.push('El estatus es obligatorio');
            $('#f-estatus').addClass('is-invalid');
        } else {
            $('#f-estatus').removeClass('is-invalid');
        }
        
        if ($('#f-servicio').val().trim() == '') {
            errores.push('El servicio u objeto de contrato es obligatorio');
            $('#f-servicio').addClass('is-invalid');
        } else {
            $('#f-servicio').removeClass('is-invalid');
        }
        
        if ($('#f-fecha-inicio').val() == '') {
            errores.push('La fecha de inicio es obligatoria');
            $('#f-fecha-inicio').addClass('is-invalid');
        } else {
            $('#f-fecha-inicio').removeClass('is-invalid');
        }

        // Validación adicional: fecha de fin no puede ser anterior a fecha de inicio
        if ($('#check-fecha-fin').is(':checked')) {
            if ($('#f-fecha-fin').val() == '') {
                errores.push('Debe seleccionar una fecha de fin o desmarcar la casilla');
                $('#f-fecha-fin').addClass('is-invalid');
            } else if ($('#f-fecha-inicio').val() && $('#f-fecha-fin').val()) {
                var fechaInicio = new Date($('#f-fecha-inicio').val());
                var fechaFin = new Date($('#f-fecha-fin').val());
                if (fechaFin < fechaInicio) {
                    errores.push('La fecha de fin no puede ser anterior a la fecha de inicio');
                    $('#f-fecha-fin').addClass('is-invalid');
                } else {
                    $('#f-fecha-fin').removeClass('is-invalid');
                }
            } else {
                $('#f-fecha-fin').removeClass('is-invalid');
            }
        }

        if (errores.length > 0) {
            notify('warning', 'Validación', errores.join('<br>'), 5000);
            // Enfocar el primer campo con error
            $('.is-invalid').first().focus();
            return;
        }

        var cmd = $('#form-subcontrato').serialize() + '&module=subcontratos&method=save';
        
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
                        notify('success', '¡Registro Exitoso!', 'El subcontrato ha sido registrado correctamente en el sistema. ID: ' + d.id, 5000);
                        showAlert('success', '¡Registro Exitoso!', 'El subcontrato ha sido registrado correctamente. ID: ' + d.id);
                        
                        // Actualizar URL para edición
                        window.history.replaceState({}, '', 'index.php?module=subcontratos&id=' + d.id);
                    } else {
                        // Actualización
                        notify('success', '¡Actualización Exitosa!', 'Los datos del subcontrato han sido actualizados correctamente', 5000);
                        showAlert('success', '¡Actualización Exitosa!', 'Los datos del subcontrato han sido actualizados correctamente.');
                    }
                } else {
                    console.log('Error del servidor:', d.msg); // Debug temporal
                    notify('danger', 'Error al Guardar', d.msg || 'Error al guardar el subcontrato', 5000);
                    showAlert('danger', 'Error al Guardar:', d.msg || 'Error al guardar el subcontrato');
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
                    // Si no es JSON válido, mantener mensaje genérico
                    if (error) message = error;
                }

                notify('danger', title, message, 5000);
                showAlert('danger', title, message);
            }
        });
    });


    // Validación en tiempo real del estatus
    $('#f-estatus').change(function() {
        var estatus = $(this).val();
        if (estatus === 'TCP') {
            $('#f-entidad').prop('disabled', true).val('');
            $('#f-entidad').closest('.form-group').find('label').html('Entidad Representada <small class="text-muted">(No aplica para TCP)</small>');
        } else {
            $('#f-entidad').prop('disabled', false);
            $('#f-entidad').closest('.form-group').find('label').html('Entidad Representada');
        }
    });

    // Validación en tiempo real para campos obligatorios
    $('#f-nombre, #f-servicio, #f-ci').on('blur', function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Filtro de entrada para CI: solo dígitos y máx 11
    $('#f-ci').on('input', function() {
        var val = $(this).val().replace(/[^\d]/g, '').slice(0, 11);
        $(this).val(val);
        if (val.length === 11) {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-estatus').on('change', function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-fecha-inicio').on('change', function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de fechas en tiempo real
    $('#f-fecha-fin').on('change', function() {
        if ($('#check-fecha-fin').is(':checked')) {
            if ($(this).val() === '') {
                $(this).addClass('is-invalid');
            } else if ($('#f-fecha-inicio').val() && $(this).val()) {
                var fechaInicio = new Date($('#f-fecha-inicio').val());
                var fechaFin = new Date($(this).val());
                if (fechaFin < fechaInicio) {
                    $(this).addClass('is-invalid');
                    notify('warning', 'Fecha Inválida', 'La fecha de fin no puede ser anterior a la fecha de inicio', 3000);
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    // Ejecutar validación inicial si hay datos cargados
    if ($('#f-estatus').val() !== '') {
        $('#f-estatus').trigger('change');
    }
    
    // Inicializar estado del checkbox de fecha fin
    $('#check-fecha-fin').trigger('change');

    // Agregar estilos CSS para campos inválidos si no existen
    if (!$('#validation-styles').length) {
        $('<style id="validation-styles">')
            .text('.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }')
            .appendTo('head');
    }
});
