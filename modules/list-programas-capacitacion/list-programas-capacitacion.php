<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Programa" title="Añadir Nuevo Programa de Capacitación">Añadir Nuevo Programa</button>
    </div>
</div>

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
            data-url="api-app.php?module=programas-capacitacion&method=list"
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
                    <th data-field="tema" data-sortable="true">Tema</th>
                    <th data-field="dirigido_a" data-sortable="true">Dirigido a</th>
                    <th data-field="responsable" data-sortable="true">Responsable</th>
                    <th data-field="fecha_estimada" data-sortable="true">Fecha Estimada</th>
                    <th data-field="modalidad" data-sortable="true">Modalidad</th>
                    <th data-field="horas" data-sortable="true">Horas</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->

<!--Modal para ver detalles del programa-->
<div class="modal fade" id="programaModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Detalles del Programa de Capacitación</h5>
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
