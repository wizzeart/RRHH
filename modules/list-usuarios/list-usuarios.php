<div class="panel">
    <div class="form-control">
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Usuario" title="Añadir Nuevo Usuario">Añadir Nuevo Usuario</button>
    </div>
</div>

<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   --> 
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=usuarios&method=list"
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
                    <th data-field="xusuario_id" data-sortable="true">ID</th>
                    <th data-field="xusuario" data-sortable="true">Nombre</th>
                    <th data-field="xrol" data-sortable="true">Rol</th>
                    <th data-field="xactivo" data-align="center" data-formatter="formatoActivo" data-sortable="false">Activo</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->

