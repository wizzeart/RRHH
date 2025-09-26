<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" title="Añadir Nuevo Cargo">Añadir Nuevo Cargo</button>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=cargos&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="id"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" data-show-pagination-switch="true">
            <thead>
                <tr>
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="descripcion" data-sortable="false">Descripción</th>
                    <th data-field="salario" data-align="right" data-sortable="true" data-formatter="salarioFormatter">Salario</th>
                    <th data-field="departamento_nombre" data-sortable="true">Departamento</th>
                    <th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents" data-align="center" data-width="240">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>
$(function(){
    $('#btn-add-new').on('click', function(){
        location.href='?module=cargos';
    });
});

function salarioFormatter(value) {
    return value ? '$' + parseFloat(value).toFixed(2) : '-';
}

function operateFormatter(value, row, index) {
    return [
        '<a class="edit btn btn-xs btn-info" href="javascript:void(0)" title="Editar">',
        '<i class="fa fa-edit"></i>',
        '</a>  ',
        '<a class="remove btn btn-xs btn-danger" href="javascript:void(0)" title="Eliminar">',
        '<i class="fa fa-trash"></i>',
        '</a>'
    ].join('');
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
                            $.niftyNoty({
                                type: 'success',
                                title: 'Éxito',
                                message: response.msg,
                                container: 'floating',
                                timer: 3000
                            });
                        } else {
                            alert('Éxito: ' + response.msg);
                        }
                    } else {
                        if ($.niftyNoty) {
                            $.niftyNoty({
                                type: 'danger',
                                title: 'Error',
                                message: response.msg || 'Error al eliminar',
                                container: 'floating',
                                timer: 4000
                            });
                        } else {
                            alert('Error: ' + (response.msg || 'Error al eliminar'));
                        }
                    }
                },
                error: function(xhr) {
                    if ($.niftyNoty) {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Error',
                            message: 'Error de conexión',
                            container: 'floating',
                            timer: 4000
                        });
                    } else {
                        alert('Error de conexión');
                    }
                }
            });
        }
    }
};
</script>
