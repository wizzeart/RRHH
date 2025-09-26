<!-- <div class="panel">
    <div class="form-control"> -->
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
    <!-- </div>
</div> -->



<!--Basic Toolbar-->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-listado" data-toggle="tab" aria-expanded="true">Listado de Subcontratos</a></li>
                <li><a href="#" onclick="location.href='?module=delete-subcontratos'">Finalizar Subcontrato</a></li>
                <li><a href="#" onclick="location.href='?module=bajas-subcontratos'">Listado de Finalizados</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-listado">
                <div class="panel">
                    <div class="form-control">
                        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Subcontrato" title="Añadir Nuevo Subcontrato">Añadir Nuevo Subcontrato</button>
                    </div>
                </div>
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
                    <th data-field="carnet_identidad" data-sortable="true">CI</th>
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
    </div>
</div>
<!--===================================================-->
<!--Modal para ver detalles del subcontrato-->
<div class="modal fade" id="subcontratoModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Detalles del Subcontrato</h5>
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
