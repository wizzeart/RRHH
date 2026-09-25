<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   -->
<div class="panel">
  <div class="panel-heading">
    <div class="panel-control">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-listado" data-toggle="tab" aria-expanded="true">Listado de Asistencias</a></li>
        <li><a href="#tab-presentes" data-toggle="tab" aria-expanded="false">Trabajadores Presentes</a></li>
        <li><a href="#tab-registro" data-toggle="tab" aria-expanded="false">Control de Asistencia</a></li>
        <li><a href="#tab-analisis" data-toggle="tab" aria-expanded="false">Control Puntualidad</a></li>
        <?php if (isset($app->empresa_id) && $app->empresa_id == 3): ?>
        <li><a href="#tab-custodios" data-toggle="tab" aria-expanded="false">Control Custodios</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="panel-body">
    <div class="tab-content">
      <div class="tab-pane fade active in" id="tab-listado">
        <div class="panel">
          <div class="panel-heading">
            <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
          </div>
          <div class="panel-body">
            <div class="form-control">
              <div class="row">
                <div class="col-md-12">
                  <div class="row">
                    <!-- Fecha desde -->
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" class="form-control" id="fecha-desde" value="<?php
                                                                                        $fechaDesde_ = date('Y-m-d');
                                                                                        echo $fechaDesde_;

                                                                                        ?>">
                      </div>
                    </div>
                    <div class="col-md-2 hidden">
                      <div class="form-group">
                        <!-- Fecha hasta -->
                        <label>Fecha hasta</label>
                        <input type="date" class="form-control" id="fecha-hasta" value="<?php
                                                                                        echo date('Y-m-d'); ?>" disabled>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" id="filtro-estado">
                          <option value="">Todos</option>
                          <option value="1">Presente</option>
                          <option value="2">Ausente</option>
                          <option value="3">Vacaciones</option>
                        </select>
                      </div>
                    </div>
                    <!-- Tipo de ausencia -->
                    <div class="col-md-2" id="filtro-tipo-ausencia-container" hidden>
                      <div class="form-group">
                        <label>Tipo de Ausencia</label>
                        <select class="form-control" id="filtro-tipo-ausencia">
                          <option value="">Todos los tipos</option>
                          <option value="Injustificada">Injustificada</option>
                          <option value="Justificada">Justificada</option>
                          <option value="Enfermedad">Enfermedad</option>
                          <option value="Licencia de Maternidad">Licencia de Maternidad</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Ubicación</label>
                        <select id="filterUbicacion" class="form-control">
                          <option value="">Todas las ubicaciones</option>
                          <?php
                          if ($app->rol == 4) {
                              // Jefe de área: solo mostrar ubicaciones de sus trabajadores asignados
                              $ubicaciones = $app->db->fetchAll("
                                  SELECT DISTINCT u.id, u.nombre 
                                  FROM ubicaciones u
                                  INNER JOIN trabajadores t ON t.ubicacion = u.id
                                  INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t.departamento_id 
                                  INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t.ubicacion
                                  WHERE aud.usuario_id = {$app->user_id} 
                                  AND auu.usuario_id = {$app->user_id}
                                  ORDER BY u.nombre
                              ");
                          } else {
                              // Otros roles: mostrar todas las ubicaciones
                              $ubicaciones = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM ubicaciones ORDER BY nombre");
                          }
                          foreach ($ubicaciones as $depto) {
                            echo "<option value='" . $depto['id'] . "'>" . htmlspecialchars($depto['nombre']) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-2">
                    <button id="btn-filtrar" class="btn btn-primary">
                      <i class="fa fa-search"></i> Filtrar
                    </button>
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
          </div>
        </div>
        <table
          id="table-panel"
          data-toggle="table"
          data-url=""
          data-search="true"
          data-show-refresh="true"
          data-show-toggle="false"
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
              <th data-field="foto" data-formatter="formatoFoto" data-sortable="false" data-width="80">Foto</th>
              <th data-field="carnet_identidad" data-sortable="true">
                CI
              </th>
              <th data-field="nombre" data-sortable="true" data-formatter="formatoNombreCompleto">
                Nombre Completo
              </th>
              <th data-field="cargo_nombre" data-sortable="true">
                Cargo
              </th>
              <th data-field="ubicacion" data-sortable="true" data-width="150">
                Ubicación
              </th>
              <th data-field="hora_entrada" data-sortable="true" data-align="center" data-width="120">
                Entrada
              </th>
              <th data-field="hora_salida" data-sortable="true" data-align="center" data-width="120">
                Salida
              </th>
              <th data-field="hora_entrada" data-sortable="true" data-align="center" data-width="120" data-formatter="formatoHoras">
                Horas
              </th>
              <th data-field="tipo_ausencia" data-sortable="true" data-align="center" data-width="150" data-formatter="formatoAusencia">
                Asistencia
              </th>
              <th data-field="tipo_ausencia" data-sortable="true" data-visible="false">
                Tipo Ausencia
              </th>
              <th data-field="justificacion" data-sortable="true" data-visible="false">
                Justificación
              </th>
              <th data-field="tipo_horario" data-sortable="true" data-visible="false">
                Tipo Horario
              </th>
            </tr>
          </thead>
        </table>
      </div>
      <div class="tab-pane fade" id="tab-presentes">
        <div class="panel">
          <div class="panel-heading">
            <h3 class="panel-title">Trabajadores Presentes - <?php echo date('d/m/Y'); ?></h3>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-md-12 text-right" style="margin-bottom: 15px;">
                <button id="btn-reporte-diario" class="btn btn-success">
                  <i class="fa fa-file-pdf-o"></i> Reporte Diario
                </button>
              </div>
            </div>
          </div>
        </div>
        <div id="departamentos-presentes-container">
          <!-- Las tablas por departamento se cargarán aquí dinámicamente -->
        </div>
      </div>
      <div class="tab-pane fade" id="tab-registro">
        <div class="panel">
          <div class="panel-heading">
            <h3 class="panel-title">Control de Asistencia</h3>
          </div>
          <div class="panel-body">
            <div class="form-control">
              <div class="row">
                <div class="col-md-3">
                  <label for="mes-seleccionado">Seleccionar mes:</label>
                  <input
                    type="month"
                    id="mes-seleccionado"
                    class="form-control"
                    value="<?php echo date('Y-m'); ?>">
                </div>
              </div>
            </div>
          </div>
        </div>
        <table
          id="table-panel-registro"
          data-toggle="table"
          data-url="api-app.php?module=trabajadores&method=list"
          data-search="true"
          data-show-refresh="true"
          data-show-toggle="false"
          data-sort-name="id"
          data-sort-order="desc"
          data-page-size="20"
          data-pagination="true"
          data-page-list="[20]"
          data-side-pagination="server">
          <thead>
            <tr>
              <th data-field="foto" data-formatter="formatoFoto" data-sortable="false" data-width="80">Foto</th>
              <th data-field="carnet_identidad" data-sortable="true" data-width="100">CI</th>
              <th data-field="nombre" data-sortable="true" data-width="250" data-formatter="formatoNombreCompleto">Nombre Completo</th>
              <th data-field="huella_dactilar" data-width="150" data-formatter="formatoHuellaDactilar" data-align="center">Huella Dactilar</th>
              <th data-field="tipo_horario" data-formatter="formatoTipoHorario" data-align="center">Horario</th>
              <th data-field="horas_trabajadas_mes" data-align="center" data-width="100" data-formatter="formatoHorasMes">
                Horas / Total Horas Mes
              </th>
              <th data-field="horas_trabajadas_mes" data-align="center" data-width="100" data-formatter="formatoPorcentajeHorasMes">
                Porcentaje de Horas Mes
              </th>

            </tr>
          </thead>
        </table>
      </div>
      <div class="tab-pane fade" id="tab-analisis">
        <?php
        // Incluir el módulo de análisis de asistencia para mostrar su UI dentro de la pestaña
        $analisis_path = __DIR__ . '/../analisis-asistencia/analisis-asistencia.php';
        if (file_exists($analisis_path)) {
            include $analisis_path;
        } else {
            echo '<div class="panel"><div class="panel-body"><div class="text-danger">Módulo de análisis no disponible.</div></div></div>';
        }
        ?>
      </div>
      <?php if (isset($app->empresa_id) && $app->empresa_id == 3): ?>
      <div class="tab-pane fade" id="tab-custodios">
        <?php
        $custodios_path = __DIR__ . '/../control-custodios/control-custodios.php';
        if (file_exists($custodios_path)) {
            include $custodios_path;
        } else {
            echo '<div class="panel"><div class="panel-body"><div class="text-danger">Módulo de Control Custodios no disponible.</div></div></div>';
        }
        ?>
      </div>
      <?php endif; ?>
    </div>
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

          <div class="row" hidden>
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
                <input type="checkbox" id="ausencia" name="ausencia"> Marcar/Describir ausencia
              </label>
            </div>
          </div>

          <div class="form-group" id="tipo-ausencia-container" style="display: none;">
            <label for="tipo_ausencia">Tipo de Ausencia</label>
            <select class="form-control" id="tipo_ausencia" name="tipo_ausencia">
              <option value="">Seleccione un tipo</option>
              <option value="Injustificada">Injustificada</option>
              <option value="Justificada">Justificada</option>
              <option value="Enfermedad">Enfermedad</option>
              <option value="Licencia de Maternidad">Licencia de Maternidad</option>
            </select>
          </div>

          <div class="form-group" id="justificacion-container" style="display: none;">
            <label for="justificacion">Descripción</label>
            <textarea class="form-control" id="justificacion" name="justificacion" rows="3" placeholder="Ingrese la descripción de la ausencia"></textarea>
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
