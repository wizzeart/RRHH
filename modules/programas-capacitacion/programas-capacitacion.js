$(document).ready(function () {
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
    $('#f-fecha-estimada, #f-fecha-finalizacion').on('click focus', function() {
        $(this)[0].showPicker();
    });

    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-programas-capacitacion';
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

        // Validación adicional: fecha de finalización no puede ser anterior a fecha estimada
        if ($('#f-fecha-estimada').val() && $('#f-fecha-finalizacion').val()) {
            var fechaEstimada = new Date($('#f-fecha-estimada').val());
            var fechaFinalizacion = new Date($('#f-fecha-finalizacion').val());
            if (fechaFinalizacion < fechaEstimada) {
                errores.push('La fecha de finalización no puede ser anterior a la fecha estimada');
                $('#f-fecha-finalizacion').addClass('is-invalid');
            } else {
                $('#f-fecha-finalizacion').removeClass('is-invalid');
            }
        }

        if (errores.length > 0) {
            notify('warning', 'Validación', errores.join('<br>'), 5000);
            // Enfocar el primer campo con error
            $('.is-invalid').first().focus();
            return;
        }

        var cmd = $('#form-programa').serialize() + '&module=programas-capacitacion&method=save';
        
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: cmd,
            dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    // Mensaje de éxito mejorado
                    if ($('#f-id').val() == '') {
                        // Nuevo registro
                        notify('success', '¡Éxito!', 'Programa de capacitación registrado correctamente. ID: ' + d.id, 4000);
                        
                        // Preguntar si desea agregar otro programa
                        setTimeout(function() {
                            if (confirm('¿Desea registrar otro programa de capacitación?')) {
                                limpiarFormulario();
                                $('#f-tema').focus();
                            } else {
                                // Actualizar el ID en el formulario para edición
                                $('#f-id').val(d.id);
                                window.history.replaceState({}, '', 'index.php?module=programas-capacitacion&id=' + d.id);
                            }
                        }, 1000);
                    } else {
                        // Actualización
                        notify('success', '¡Actualizado!', 'Programa de capacitación actualizado correctamente', 3000);
                    }
                } else {
                    notify('danger', 'Error', d.msg || 'Error al guardar el programa de capacitación', 4000);
                }
            },
            error: function(xhr, status, error) {
                notify('danger', 'Error de Conexión', 'No se pudo conectar con el servidor: ' + error, 4000);
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

    // Validación de fechas en tiempo real
    $('#f-fecha-finalizacion').on('change', function() {
        if ($('#f-fecha-estimada').val() && $(this).val()) {
            var fechaEstimada = new Date($('#f-fecha-estimada').val());
            var fechaFinalizacion = new Date($(this).val());
            if (fechaFinalizacion < fechaEstimada) {
                $(this).addClass('is-invalid');
                notify('warning', 'Fecha Inválida', 'La fecha de finalización no puede ser anterior a la fecha estimada', 3000);
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    // Agregar estilos CSS para campos inválidos si no existen
    if (!$('#validation-styles').length) {
        $('<style id="validation-styles">')
            .text('.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }')
            .appendTo('head');
    }
});
