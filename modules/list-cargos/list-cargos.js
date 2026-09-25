$(function(){
    function notify(type, title, message, timer){
        if ($.niftyNoty) {
            $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
        } else { alert((title?title+': ':'')+message); }
    }

    $('#btn-add-new').on('click', function(){
        location.href='?module=cargos';
    });
});

function salarioFormatter(value) {
    return value ? '$' + parseFloat(value/192).toFixed(2) : '-';
}

function operateFormatter(value, row, index) {
    var buttons = [
        '<button class="edit btn btn-info btn-icon icon-sm fa fa-edit" title="Editar" data-id="' + row.id + '"></button>',
        '<button class="remove btn btn-danger btn-icon icon-sm fa fa-trash" title="Eliminar" data-id="' + row.id + '"></button>'
    ];

    if (row.funciones_path && row.funciones_path.trim() !== '') {
        buttons.push('<button class="download btn btn-success btn-icon icon-sm fa fa-download" title="Descargar Funciones" data-id="' + row.id + '" data-path="' + row.funciones_path + '"></button>');
    }

    return buttons.join('\n');
}

window.operateEvents = {
    'click .edit': function (e, value, row, index) {
        location.href = '?module=cargos&id=' + row.id;
    },
    'click .remove': function (e, value, row, index) {
        if (confirm('¿Está seguro de eliminar el cargo: ' + row.nombre + '?')) {
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                data: {module: 'cargos', method: 'del', id: row.id},
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
    },
    'click .download': function (e, value, row, index) {
        var path = row.funciones_path;
        if (path) {
            window.open(path, '_blank');
        }
    }
};
