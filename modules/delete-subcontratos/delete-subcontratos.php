<!-- <div class="panel">
    <div class="form-control"> -->
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
    <!-- </div>
</div> -->

<!--Basic Toolbar-->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=subcontratos&method=list"
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
                    <th data-field="persona_nombre" data-sortable="true">Nombre</th>
                    <th data-field="estatus" data-sortable="true">Estatus</th>
                    <th data-field="entidad_representada" data-sortable="true">Entidad</th>
                    <th data-field="servicio_objeto" data-sortable="true">Servicio/Objeto</th>
                    <th data-field="fecha_inicio" data-sortable="true">Fecha Inicio</th>
                    <th data-field="areas_acceso" data-sortable="true">Áreas Acceso</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->
