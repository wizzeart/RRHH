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
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li><a href="#" onclick="location.href='?module=list-trabajadores'">Listado de Trabajadores</a></li>
                <li class="active"><a href="#tab-dar-baja" data-toggle="tab" aria-expanded="true">Dar de baja</a></li>
                <li><a href="#" onclick="location.href='?module=bajas-trabajadores'">Listado de Bajas</a></li>
                <li><a href="#" onclick="location.href='?module=list-cuentas'">Listado de Cuentas</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
          <div class="form-inline" style="margin-bottom: 15px;">
                        <!-- Search Box -->
                        <!-- <div class="form-group" style="margin-right: 10px; width: 300px; position: relative;">
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscar-trabajador" placeholder="Buscar por nombre, apellido o CI...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                            <div id="resultados-busqueda" class="list-group" style="position: absolute; z-index: 1000; width: 100%; display: none; max-height: 300px; overflow-y: auto;"></div>
                        </div> -->
                        
                        <!-- Cargo Filter -->
                        <div class="form-group" style="margin-right: 10px;">
                            <select id="filterCargo" class="form-control">
                                <option value="">Todos los cargos</option>
                                <?php
                                $cargos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM cargos ORDER BY nombre");
                                foreach ($cargos as $cargo) {
                                    echo "<option value='" . $cargo['id'] . "'>" . htmlspecialchars($cargo['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Departamento Filter -->
                        <div class="form-group" style="margin-right: 10px;">
                            <select id="filterDepartamento" class="form-control">
                                <option value="">Todos los departamentos</option>
                                <?php
                                $deptos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM departamentos ORDER BY nombre");
                                foreach ($deptos as $depto) {
                                    echo "<option value='" . $depto['id'] . "'>" . htmlspecialchars($depto['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button id="btn-filter" class="btn btn-primary">Filtrar</button>
                        <button id="btn-reset" class="btn btn-default" style="margin-left: 5px;">Limpiar</button>
                        <button id="btn-add-new" class="btn btn-mint btn-icon  pull-right" alt="Añadir Nuevo Trabajador" title="Añadir Nuevo Trabajador"><span class="icon-lg fa fa-plus"></span>  Añadir Nuevo Trabajador</button>
                    </div>
            
            <div class="tab-pane fade active in" id="tab-dar-baja">
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
                  <th data-field="carnet_identidad" data-sortable="true">CI</th>
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="apellidos" data-sortable="true">Apellidos</th>
                    <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                    <!-- <th data-field="sexo" data-sortable="true">Sexo</th>
                    <th data-field="edad" data-sortable="true">Edad</th> -->
                   <!-- <th data-field="estatus" data-align="center" data-formatter="formatoActivo" data-sortable="false">Estado</th>  -->
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th>

                </tr>
            </thead>
        </table>
            </div>
        </div>
    </div>
</div>
<!--===================================================-->



<!-- Modal de confirmación para dar de baja -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirmar dar de baja</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Para confirmar, escribe <strong>ELIMINAR</strong> en el siguiente campo. Esta acción establecerá la fecha de baja automáticamente.</p>
        <input type="text" id="confirm-delete-text" class="form-control" placeholder="Escribe ELIMINAR" autocomplete="off">
        <small id="confirm-delete-help" class="text-danger" style="display:none;">Debes escribir exactamente "ELIMINAR".</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-delete" class="btn btn-danger">Confirmar baja</button>
      </div>
    </div>
  </div>
  <!-- Datos temporales -->
  <input type="hidden" id="confirm-delete-id" value="">
  <input type="hidden" id="confirm-delete-row" value="">
</div>
