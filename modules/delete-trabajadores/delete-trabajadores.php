<!-- <div class="panel">
    <div class="form-control"> -->
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
    <!-- </div>
</div> -->

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
            data-url="api-app.php?module=trabajadores&method=list"
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
                  <!-- <th data-field="id" data-sortable="true">ID</th>  -->
                    <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="apellidos" data-sortable="true">Apellidos</th>
                    <th data-field="carnet_identidad" data-sortable="true">CI</th>
                    <th data-field="sexo" data-sortable="true">Sexo</th>
                    <th data-field="edad" data-sortable="true">Edad</th>
                   <!-- <th data-field="estatus" data-align="center" data-formatter="formatoActivo" data-sortable="false">Estado</th>  -->
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th>

                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->


