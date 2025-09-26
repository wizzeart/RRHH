<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" title="Añadir Nuevo Departamento">Añadir Nuevo Departamento</button>
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
            data-url="api-app.php?module=departamentos&method=list"
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
                    <th data-field="empresa_nombre" data-sortable="true">Empresa</th>
                    <th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents" data-align="center" data-width="240">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
 
