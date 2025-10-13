<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   -->
<div class="panel">
  <div class="panel-heading">
    <div class="panel-control">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-listado" data-toggle="tab" aria-expanded="true">Listado de Asistencias</a></li>
      </ul>
    </div>
    <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
  </div>
  <div class="panel-body">
    <div class="tab-content">
      <div class="tab-pane fade active in" id="tab-listado">
        <div class="panel">
          <div class="form-control">
            <div class="row">
              <div class="col-md-12">
                <div class="row">
                  <!-- Fecha desde -->
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Fecha desde</label>
                      <input type="date" class="form-control" id="fecha-desde" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <!-- Fecha hasta -->
                      <label>Fecha hasta</label>
                      <input type="date" class="form-control" id="fecha-hasta" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Estado</label>
                      <select class="form-control" id="filtro-estado">
                        <option value="">Todos</option>
                        <option value="1">Presente</option>
                        <option value="2">Ausente</option>
                      </select>
                    </div>
                  </div>
                  <!-- Tipo de ausencia -->
                  <div class="col-md-2" id="filtro-tipo-ausencia-container">
                    <div class="form-group">
                      <label>Tipo de Ausencia</label>
                      <select class="form-control" id="filtro-tipo-ausencia">
                        <option value="">Todos los tipos</option>
                        <option value="RRHH">RRHH</option>
                        <option value="Justificada">Justificada</option>
                        <option value="Enfermedad">Enfermedad</option>
                        <option value="Vacaciones">Vacaciones</option>
                        <option value="Licencia de Maternidad">Licencia de Maternidad</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Buscador de Trabajador -->
              <!-- <div class="col-md-3">
                <div class="form-group">
                  <label>Buscar Trabajador</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="buscar-trabajador" placeholder="Buscar trabajador..." autocomplete="off">
                    <input type="hidden" id="filtrar-trabajador" value="">
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button" id="limpiar-busqueda">
                        <i class="fa fa-times"></i>
                      </button>
                    </span>
                  </div>
                  <div id="resultados-busqueda" class="suggestions-dropdown" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px;"></div>
                </div>
              </div> -->
              <!-- Estado de asistencia -->
            </div>
          </div>
          <div class="col-sm-2">
            <button id="btn-filtrar" class="btn btn-primary">
              <i class="fa fa-search"></i> Filtrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <table
      id="table-panel"
      data-toggle="table"
      data-url="api-app.php?module=asistencias&method=list-filter"
      data-search="true"
      data-show-refresh="true"
      data-show-toggle="false"
      data-show-columns="true"
      data-sort-name="fecha"
      data-sort-order="desc"
      data-page-list="[10, 25, 50, 100]"
      data-page-size="25"
      data-pagination="true"
      data-show-pagination-switch="true"
      data-show-export="true"
      data-export-data-type="all"
      data-export-types="['excel', 'pdf']">
      <thead>
        <tr class="bg-primary">
          <th data-field="fecha" data-sortable="true" data-width="100">
            Fecha
          </th>
          <th data-field="carnet_identidad" data-sortable="true">
            CI
          </th>
          <th data-field="nombre" data-sortable="true" >
            Nombre
          </th>
          <th data-field="apellidos" data-sortable="true">
            Apellidos
          </th>
          <th data-field="cargo_nombre" data-sortable="true">
            Cargo
          </th>
          <th data-field="hora_entrada" data-sortable="true" data-align="center" data-width="120">
            Entrada
          </th>
          <th data-field="hora_salida" data-sortable="true" data-align="center" data-width="120">
            Salida
          </th>
          <th data-field="tipo_ausencia" data-sortable="true" data-align="center" data-width="100" data-formatter="formatoAusencia">
            Ausencia
          </th>
          <th data-field="tipo_ausencia" data-sortable="true" data-visible="false">
            Tipo Ausencia
          </th>
          <th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents" data-align="center" data-width="100">
            Acciones
          </th>
        </tr>
      </thead>
    </table>
  </div>
</div>


<!-- Formulario de registro de asistencia -->
<div class="modal fade" id="modalAsistencia" tabindex="-1" role="dialog" aria-labelledby="modalAsistenciaLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalAsistenciaLabel">Registrar Asistencia</h4>
      </div>
      <div class="modal-body">
        <form id="formAsistencia">
          <input type="hidden" name="id" id="asistencia_id">
          <input type="hidden" name="trabajador_id" id="trabajador_id">

          <div class="form-group">
            <label for="fecha">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" disabled>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="hora_entrada">Hora de Entrada</label>
                <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="hora_salida">Hora de Salida</label>
                <input type="time" class="form-control" id="hora_salida" name="hora_salida" disabled>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="checkbox">
              <label>
                <input type="checkbox" id="ausencia" name="ausencia"> Marcar como ausencia
              </label>
            </div>
          </div>

          <div class="form-group" id="tipo-ausencia-container" style="display: none;">
            <label for="tipo_ausencia">Tipo de Ausencia</label>
            <select class="form-control" id="tipo_ausencia" name="tipo_ausencia">
              <option value="">Seleccione un tipo</option>
              <option value="RRHH">RRHH</option>
              <option value="Justificada">Justificada</option>
              <option value="Enfermedad">Enfermedad</option>
              <option value="Vacaciones">Vacaciones</option>
              <option value="Licencia de Maternidad">Licencia de Maternidad</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar-asistencia">Guardar</button>
      </div>
    </div>
  </div>
</div>


<!-- Formateador para el nombre completo

===================================================-->
<div class="modal fade" id="trabajadorModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="modalLabel">Detalles del Trabajador</h5> -->
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