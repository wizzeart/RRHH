$(function(){
    function notify(type, title, message, timer){
        if ($.niftyNoty) {
            $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
        } else { alert((title?title+': ':'')+message); }
    }

    $('#btn-add-new').on('click', function(){
        location.href='?module=ubicaciones';
    });
});

function salarioFormatter(value) {
    return value ? '$' + parseFloat(value).toFixed(2) : '-';
}

function operateFormatter(value, row, index) {
    return [
        '<button class="edit btn btn-info btn-icon icon-sm fa fa-edit" title="Editar" data-id="' + row.id + '"></button>',
        '<button class="remove btn btn-danger btn-icon icon-sm fa fa-trash" title="Eliminar" data-id="' + row.id + '"></button>'
    ].join('\n');
}

window.operateEvents = {
    'click .edit': function (e, value, row, index) {
        location.href = '?module=ubicaciones&id=' + row.id;
    },
    'click .remove': function (e, value, row, index) {
        if (confirm('¿Está seguro de eliminar la ubicación: ' + row.nombre + '?')) {
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: {module: 'ubicaciones', method: 'del', id: row.id},
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1) {
                        $('#table-panel').bootstrapTable('refresh');
                        if ($.niftyNoty) {
                            $.niftyNoty({ type:'success', title:'Éxito', message: response.msg || 'Eliminado correctamente', container:'floating', timer:3000 });
                        } else { alert('Éxito: ' + (response.msg || 'Eliminado correctamente')); }
                    } else {
                        if ($.niftyNoty) {
                            $.niftyNoty({ type:'danger', title:'Error', message: response.msg || 'Error al eliminar', container:'floating', timer:4000 });
                        } else { alert('Error: ' + (response.msg || 'Error al eliminar')); }
                    }
                },
                error: function(){
                    if ($.niftyNoty) {
                        $.niftyNoty({ type:'danger', title:'Error', message: 'Error de conexión', container:'floating', timer:4000 });
                    } else { alert('Error de conexión'); }
                }
            });
        }
    }
};
