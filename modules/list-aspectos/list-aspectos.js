$(function(){
    function notify(type, title, message, timer){
        if ($.niftyNoty) {
            $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
        } else { alert((title?title+': ':'')+message); }
    }

    $('#btn-add-new').on('click', function(){
        location.href='?module=aspectos';
    });
});

// Formatter y eventos para columna Acciones
function operateFormatter(value, row, index) {
    return [
        '<button class="edit btn btn-info btn-icon icon-sm fa fa-edit" title="Editar" data-id="' + row.id + '"></button>',
        '<button class="remove btn btn-danger btn-icon icon-sm fa fa-trash" title="Eliminar" data-id="' + row.id + '"></button>'
    ].join('\n');
}

window.operateEvents = {
    'click .edit': function (e, value, row, index) {
        location.href = '?module=aspectos&id=' + row.id;
    },
    'click .remove': function (e, value, row, index) {
        if (confirm('¿Está seguro de eliminar el aspecto: ' + row.nombre + '?')) {
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: {module: 'aspectos', method: 'del', id: row.id},
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1) {
                        $('#table-panel').bootstrapTable('refresh');
                        notify('success','Éxito', response.msg || 'Eliminado correctamente', 3000);
                    } else {
                        notify('danger','Error', response.msg || 'Error al eliminar', 4000);
                    }
                },
                error: function(){
                    notify('danger','Error', 'Error de conexión', 4000);
                }
            });
        }
    }
};
