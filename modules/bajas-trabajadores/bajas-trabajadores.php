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
                <li><a href="#" onclick="location.href='?module=list-trabajadores'">Listado de Trabajadores</a></li>
                <li><a href="#" onclick="location.href='?module=delete-trabajadores'">Dar de baja</a></li>
                <li class="active"><a href="#tab-listado-bajas" data-toggle="tab" aria-expanded="true">Listado de Bajas</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-listado-bajas">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=trabajadores&method=list-bajas"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="fecha_baja"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" data-show-pagination-switch="true">
            <thead>
                <tr>
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="apellidos" data-sortable="true">Apellidos</th>
                    <th data-field="carnet_identidad" data-sortable="true">CI</th>
                    <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                    <th data-field="fecha_contratacion" data-sortable="true">Fecha Contratación</th>
                    <th data-field="fecha_baja" data-sortable="true">Fecha Baja</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th>
                </tr>
            </thead>
        </table>
            </div>
        </div>
    </div>
</div>
<!--===================================================-->

<!--Modal para ver detalles del trabajador dado de baja-->
<div class="modal fade" id="trabajadorBajaModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Detalles del Trabajador Dado de Baja</h5>
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
