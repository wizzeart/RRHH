<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle'] ?? 'Lista de Contratos'); ?></h3>
    </div>
    <div class="panel-body">
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Contrato" title="Añadir Nuevo Contrato"></button>
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=contratos&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="id"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" data-show-pagination-switch="true">
            <thead>
                <tr>
                    <th data-field="id" data-sortable="true">ID</th>
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="fecha" data-sortable="true">Fecha</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
