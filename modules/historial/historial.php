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
            data-url="api-app.php?module=historial&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="xdate"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" data-show-pagination-switch="true">
            <thead>
                <tr>
                    <!-- <th data-field="xdate" data-sortable="true">Fecha</th> -->
                    <th data-field="xentity" data-sortable="true">Entidad</th>
                    <th data-field="xaction" data-sortable="true">Acción</th>
                    <th data-field="xobs" data-sortable="true">Observaciones</th>
                    <th data-field="usuario_nombre" data-sortable="true">Usuario</th>
                    <!-- <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th> -->
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->

<!--Modal para ver detalles del historial-->
<div class="modal fade" id="historialModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Detalles del Registro Histórico</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Aquí se insertan los datos -->
      </div>
    </div>
  </div>
</div>
