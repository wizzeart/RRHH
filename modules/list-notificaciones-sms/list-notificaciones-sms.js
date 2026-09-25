$(document).ready(function() {
    $('#btn-add-new').on('click', function() {
        location.href = '?module=notificaciones-sms';
    });

    // Botón de configuración automática
    $('#btn-config-auto').on('click', function() {
        cargarConfiguracionAutomatica();
        $('#modalConfiguracionAutomatica').modal('show');
    });

    // Guardar configuración
    $('#btn-guardar-configuracion').on('click', function() {
        guardarConfiguracionAutomatica();
    });
});

function cargarConfiguracionAutomatica() {
    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: {
            module: 'notificaciones-sms',
            method: 'get-config'
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 1) {
                $('#chk-notificar-vacaciones').prop('checked', response.config.notificar_vacaciones);
                $('#chk-notificar-ausencia').prop('checked', response.config.notificar_ausencia);
                $('#chk-notificar-cumpleanos').prop('checked', response.config.notificar_cumpleanos);
                $('#chk-notificar-parte-nocturno').prop('checked', response.config.notificar_parte_nocturno);
            } else {
                console.error('Error cargando configuración:', response.msg);
                alert('Error cargando configuración: ' + response.msg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            alert('Error de conexión al cargar configuración');
        }
    });
}

function guardarConfiguracionAutomatica() {
    var configuracion = {
        module: 'notificaciones-sms',
        method: 'save-config',
        notificar_vacaciones: $('#chk-notificar-vacaciones').is(':checked') ? 1 : 0,
        notificar_ausencia: $('#chk-notificar-ausencia').is(':checked') ? 1 : 0,
        notificar_cumpleanos: $('#chk-notificar-cumpleanos').is(':checked') ? 1 : 0,
        notificar_parte_nocturno: $('#chk-notificar-parte-nocturno').is(':checked') ? 1 : 0
    };

    $.ajax({
        url: 'api-app.php',
        type: 'POST',
        data: configuracion,
        dataType: 'json',
        success: function(response) {
            if (response.status === 1) {
                alert(response.msg);
                $('#modalConfiguracionAutomatica').modal('hide');
            } else {
                alert('Error guardando configuración: ' + response.msg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            alert('Error de conexión al guardar configuración');
        }
    });
}

function dateTimeFormatter(value, row) {
    if (!value) return '-';
    // Parsear la fecha manualmente para evitar conversión de zona horaria
    // El formato de la BD es 'YYYY-MM-DD HH:MM:SS'
    var parts = value.split(' ');
    if (parts.length < 2) return value;
    var dateParts = parts[0].split('-');
    var timeParts = parts[1].split(':');
    if (dateParts.length < 3 || timeParts.length < 2) return value;
    // Formatear como DD/MM/YYYY HH:MM
    return dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + ' ' + timeParts[0] + ':' + timeParts[1];
}

function messageFormatter(value, row) {
    if (!value) return '-';
    
    // Truncar mensaje si es muy largo
    var maxLength = 50;
    var truncated = value.length > maxLength ? value.substring(0, maxLength) + '...' : value;

    var encoded = encodeURIComponent(value);
    var eyeBtn = '<button type="button" class="btn btn-primary btn-xs btn-ver-mensaje-sms" title="Ver mensaje" data-mensaje="' + encoded + '" style="padding:2px 6px; float:right; margin-right:10px;">'
        + '<i class="fa fa-eye"></i>'
        + '</button>';

    return '<span title="' + value + '">' + truncated + '</span>' + eyeBtn;
}

function formatoTrabajadorLink(value, row) {
    if (!value || !row.trabajador_id) return value;
    
    var workerId = row.trabajador_id;
    return '<a href="?module=ficha-trabajador&id=' + workerId + '" style="color: inherit; text-decoration: none;" ' +
           'onmouseover="this.style.textDecoration=\'underline\'" ' +
           'onmouseout="this.style.textDecoration=\'none\'">' +
           value + '</a>';
}

$(document).ready(function() {
    $(document).on('click', '.btn-ver-mensaje-sms', function () {
        var msg = $(this).attr('data-mensaje') || '';
        try {
            msg = decodeURIComponent(msg);
        } catch (e) {
            // dejar como está
        }
        $('#verMensajeSmsTexto').text(msg);
        $('#modalVerMensajeSms').modal('show');
    });
});
