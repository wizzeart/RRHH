
<!--Basic Toolbar-->
<!--===================================================-->
<div class="panel">
            <div class="form-control">
                <button id="btn-add-new" class="btn btn-mint btn-icon " alt="Agregar a la bolsa" title="Agregar a la bolsa"><span class="icon-lg fa fa-plus"></span> Agregar a la bolsa</button>
            </div>
        </div>
<!-- atributos quitados del tag table:   --> 
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=bolsas_empleos&method=list"
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
                    <!-- <th data-field="id" data-sortable="true">ID</th> -->
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="apellidos" data-sortable="true">Apellidos</th>
                    <th data-field="telefono" data-sortable="true">Teléfono</th>
                    <th data-field="cargo_postulado" data-sortable="true">Cargo Postulado</th>
                    <th data-field="fecha_registro" data-sortable="true">Fecha Registro</th>
                    <th data-field="observaciones" data-sortable="false" data-align="center" data-formatter="formatoObservaciones">Observaciones</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Currículum</th>

                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->


