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
        $('#form-subcontrato')[0].reset();
        $('#f-id').val('');
        $('#f-estatus').trigger('change'); // Actualizar validación de entidad
        // Actualizar la URL para nuevo registro
        window.history.replaceState({}, '', 'index.php?module=subcontratos');
    }

    // Mejorar funcionalidad de campos de fecha
    $('#f-fecha-inicio, #f-fecha-fin').on('click focus', function() {
        $(this)[0].showPicker();
    });

    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-subcontratos';
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
        if ($('#f-fecha-inicio').val() && $('#f-fecha-fin').val()) {
            var fechaInicio = new Date($('#f-fecha-inicio').val());
            var fechaFin = new Date($('#f-fecha-fin').val());
            if (fechaFin < fechaInicio) {
                errores.push('La fecha de fin no puede ser anterior a la fecha de inicio');
                $('#f-fecha-fin').addClass('is-invalid');
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
                        notify('success', '¡Éxito!', 'Subcontrato registrado correctamente. ID: ' + d.id, 4000);
                        
                        // Preguntar si desea agregar otro subcontrato
                        setTimeout(function() {
                            if (confirm('¿Desea registrar otro subcontrato?')) {
                                limpiarFormulario();
                                $('#f-nombre').focus();
                            } else {
                                // Actualizar el ID en el formulario para edición
                                $('#f-id').val(d.id);
                                window.history.replaceState({}, '', 'index.php?module=subcontratos&id=' + d.id);
                            }
                        }, 1000);
                    } else {
                        // Actualización
                        notify('success', '¡Actualizado!', 'Subcontrato actualizado correctamente', 3000);
                    }
                } else {
                    notify('danger', 'Error', d.msg || 'Error al guardar el subcontrato', 4000);
                }
            },
            error: function(xhr, status, error) {
                notify('danger', 'Error de Conexión', 'No se pudo conectar con el servidor: ' + error, 4000);
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
    $('#f-nombre, #f-servicio').on('blur', function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('is-invalid');
        } else {
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
        if ($('#f-fecha-inicio').val() && $(this).val()) {
            var fechaInicio = new Date($('#f-fecha-inicio').val());
            var fechaFin = new Date($(this).val());
            if (fechaFin < fechaInicio) {
                $(this).addClass('is-invalid');
                notify('warning', 'Fecha Inválida', 'La fecha de fin no puede ser anterior a la fecha de inicio', 3000);
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    // Ejecutar validación inicial si hay datos cargados
    if ($('#f-estatus').val() !== '') {
        $('#f-estatus').trigger('change');
    }

    // Agregar estilos CSS para campos inválidos si no existen
    if (!$('#validation-styles').length) {
        $('<style id="validation-styles">')
            .text('.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }')
            .appendTo('head');
    }
});
