$(document).ready(function () {
    // Helper de notificaciones
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
            var text = (title ? (title + ': ') : '') + (message || '');
            try { alert(text); } catch (e) { console.warn('Notify:', text); }
        }
    }

    // Event listener para cambio de empresa
    $(document).on('empresaChanged', function (e, empresaId) {
        console.log('Empresa cambió a:', empresaId);

        // Actualizar tabla de cuentas
        $('#table-cuentas').bootstrapTable('refreshOptions', {
            url: 'api-app.php?module=cuentas&method=list&empresa_id=' + empresaId
        });

        // Actualizar tabla de bancos
        $('#table-bancos').bootstrapTable('refreshOptions', {
            url: 'api/list-cuentas-data.php?empresa_id=' + empresaId
        });
    });

    // Botón de actualizar
    $('#btn-refresh').click(function () {
        $('#table-cuentas').bootstrapTable('refresh');
        notify('info', 'Actualizando', 'Cargando datos actualizados...', 2000);
    });

    // Inicializar tabla cuando se carga
    $('#table-cuentas').on('load-success.bs.table', function (data) {
        console.log('Tabla de cuentas cargada exitosamente');
    });

    $('#table-cuentas').on('load-error.bs.table', function (status) {
        console.error('Error al cargar tabla de cuentas:', status);
        notify('danger', 'Error', 'No se pudieron cargar las cuentas bancarias', 5000);
    });
});

// Formatter para las acciones
function accionesFormatter(value, row, index) {
    return [
        '<div class="btn-group btn-group-xs" role="group">',
        '<button type="button" class="btn btn-success btn-editar" title="Ver detalles">',
        '<i class="fa fa-eye"></i>',
        '</button>',

        '</div>'
    ].join('');
}

// Eventos para las acciones
window.accionesEvents = {
    'click .btn-editar': function (e, value, row, index) {
        // Mostrar detalles de la cuenta bancaria
        var mensaje = '<strong>Trabajador:</strong> ' + row.nombre_completo + '<br>' +
            '<strong>CI:</strong> ' + row.carnet_identidad + '<br>' +
            '<strong>Estado:</strong> ' + row.estatus_display + '<br><br>' +
            '<strong>Tarjeta de Salario:</strong> ' + row.tarjeta_display + '<br>' +
            '<strong>Cuenta Estándar:</strong> ' + row.cuenta_display;

        // Usar modal si está disponible, si no, alert
        if (typeof bootbox !== 'undefined') {
            bootbox.alert({
                title: "Detalles de Cuenta Bancaria",
                message: mensaje
            });
        } else {
            alert('Detalles:\n' + mensaje.replace(/<br>/g, '\n').replace(/<strong>|<\/strong>/g, ''));
        }
    },
    'click .btn-editar-trabajador': function (e, value, row, index) {
        // Redirigir al formulario de edición del trabajador
        window.location.href = '?module=trabajadores&id=' + row.trabajador_id;
    }
};

// Formatters adicionales para mejorar la presentación
function estadoFormatter(value, row, index) {
    var clase = '';
    switch (value.toLowerCase()) {
        case 'activo':
            clase = 'label-success';
            break;
        case 'inactivo':
            clase = 'label-danger';
            break;
        default:
            clase = 'label-default';
    }
    return '<span class="label ' + clase + '">' + value + '</span>';
}

function cuentaFormatter(value, row, index) {
    if (value === 'No registrada') {
        return '<span class="text-muted"><em>' + value + '</em></span>';
    }
    return '<strong>' + value + '</strong>';
}

function formatoNombreLink(value, row, index) {
    if (row.trabajador_id) {
        return '<a href="index.php?module=ficha-trabajador&id=' + row.trabajador_id + '" style="cursor: pointer; color: inherit; text-decoration: none;" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#337ab7\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'inherit\';">' + value + '</a>';
    }
    return value;
}
