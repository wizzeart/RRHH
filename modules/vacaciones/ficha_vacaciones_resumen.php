<style>
    /* Estilos para badges de días disponibles */
    .badge-vacaciones {
        font-size: 18px;
        padding: 8px 15px;
        border-radius: 5px;
    }

    .info-trabajador {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #0078d7;
    }

    .info-trabajador h4 {
        margin-top: 0;
        color: #0078d7;
    }

    .stat-card {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .stat-card .icon {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .stat-card .value {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .stat-card .label {
        color: #6c757d;
        font-size: 14px;
    }
</style>

<script>
    var action = '<?php print ($action) ?>';
    var rol = '<?php print ($app->rol) ?>';
</script>

<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#"
                data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Gestión de Vacaciones del Trabajador</p>"
                data-html="true" title=""></a>
        </div>
        <h3 class="panel-title">
            <i class="fa fa-umbrella"></i> <?php print ($page['subtitle']); ?>
        </h3>
    </div>

    <div class="panel-body">
        <!-- Información del Trabajador -->
        <div class="info-trabajador">
            <div class="row">
                <div class="col-md-2 text-center">
                    <?php if (isset($data['id'])) {
                        $imgSrc = 'api-app.php?module=imagenes-trabajadores&method=get-image&id=' . intval($data['id']);
                        ?>
                        <img id="foto-preview" src="<?php print ($imgSrc); ?>" alt="Foto del trabajador"
                            class="img-thumbnail" style="max-width: 120px; height: auto;"
                            onerror="this.src='./images/default-user.png';">
                    <?php } ?>
                </div>
                <div class="col-md-10">
                    <h4>
                        <?php
                        if (isset($data['nombre'])) {
                            echo htmlspecialchars($data['nombre'] . ' ' . $data['apellidos'] . ' ' . $data['apellidos_segundos']);
                        }
                        ?>
                    </h4>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>CI:</strong>
                                <?php echo isset($data['carnet_identidad']) ? $data['carnet_identidad'] : 'N/A'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Cargo:</strong>
                                <?php
                                if (isset($data['cargos_id'])) {
                                    $sql = 'SELECT nombre FROM cargos WHERE id = ' . intval($data['cargos_id']);
                                    $cargo = $app->db->fetchOne($sql);
                                    echo $cargo ? htmlspecialchars($cargo['nombre']) : 'N/A';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Departamento:</strong>
                                <?php
                                if (isset($data['departamento_id'])) {
                                    $sql = 'SELECT nombre FROM departamentos WHERE id = ' . intval($data['departamento_id']);
                                    $depto = $app->db->fetchOne($sql);
                                    echo $depto ? htmlspecialchars($depto['nombre']) : 'N/A';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Vacaciones -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon text-success">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="value text-success" id="dias-disponibles">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <div class="label">Días Disponibles</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon text-warning">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div class="value text-warning" id="dias-pendientes">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <div class="label">Días Pendientes Aprobación</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon text-info">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <div class="value text-info" id="dias-aprobados">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <div class="label">Días Aprobados Este Año</div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-calendar"></i> Gestión de Vacaciones
                </h3>
            </div>
            <div class="panel-body">
                <div class="form-control" style="border: none; box-shadow: none;">
                    <button id="btn-add-new-vacaciones" class="btn btn-primary btn-lg">
                        <i class="fa fa-plus fa-lg"></i> Solicitar Vacaciones
                    </button>
                    <button id="btn-historial-vacaciones" class="btn btn-info btn-lg">
                        <i class="fa fa-history fa-lg"></i> Ver Historial Completo
                    </button>
                </div>

                <!-- Tabla de Vacaciones -->
                <table id="table-vacaciones" data-toggle="table"
                    data-url="api-app.php?module=vacaciones&action=api&method=list-id&trabajador_id=<?php print ($data['id']); ?>"
                    data-search="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true"
                    data-sort-name="fecha_inicio" data-sort-order="desc" data-page-list="[10, 25, 50, 100]"
                    data-page-size="10" data-pagination="true" data-show-pagination-switch="true">
                    <thead>
                        <tr>
                            <th data-field="fecha_inicio" data-sortable="true" data-width="120">Fecha Inicio</th>
                            <th data-field="fecha_fin" data-sortable="true" data-width="120">Fecha Fin</th>
                            <th data-field="dias_totales" data-formatter="diasFormatter" data-align="center"
                                data-width="100">Días</th>
                            <th data-field="fecha_aprobacion" data-formatter="formatoAprobacionVacaciones"
                                data-sortable="true" data-align="center" data-width="130">Estado</th>
                            <th data-field="fecha_aprobacion" data-sortable="true" data-width="140">Fecha Aprobación
                            </th>
                            <?php if ($app->rol == 1): ?>
                                <th data-field="id" data-formatter="accionesVacaciones" data-align="center"
                                    data-width="150">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Botones de Navegación -->
        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
            <button id="btn-back" class="btn btn-default btn-lg" type="button">
                <i class="fa fa-undo"></i> Volver al Listado
            </button>
        </div>
    </div>
</div>

<!-- Modal para solicitar vacaciones -->
<div class="modal fade" id="modalPlanificacionVacaciones" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-calendar-o"></i> Solicitar Vacaciones
                </h4>
            </div>
            <div class="modal-body">
                <form id="form-vacaciones">
                    <input type="hidden" id="trabajador_id_vac" name="trabajador_id">

                    <!-- Información de días disponibles -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-4">
                            <div class="alert alert-info text-center" style="margin-bottom: 0;">
                                <strong>Días Actuales:</strong><br>
                                <span id="dias-base-display" class="badge badge-info" style="font-size: 18px;">--</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-success text-center" style="margin-bottom: 0;">
                                <strong>Días Disponibles:</strong><br>
                                <span id="dias-disponibles-calendar" class="badge badge-success"
                                    style="font-size: 18px;">--</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning text-center" style="margin-bottom: 0;">
                                <strong>Días Seleccionados:</strong><br>
                                <span id="dias-seleccionados-display" class="badge badge-warning"
                                    style="font-size: 18px;">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Controles del calendario -->
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-12">
                            <div class="btn-toolbar" style="justify-content: center; display: flex;">
                                <button type="button" id="btn-prev-month" class="btn btn-default">
                                    <i class="fa fa-chevron-left"></i> Mes Anterior
                                </button>
                                <h4 id="calendar-month-year"
                                    style="margin: 0 20px; line-height: 34px; min-width: 200px; text-align: center;">
                                    <!-- Mes y Año -->
                                </h4>
                                <button type="button" id="btn-next-month" class="btn btn-default">
                                    Mes Siguiente <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Calendario -->
                    <div id="vacation-calendar" style="margin-bottom: 15px;">
                        <!-- El calendario se genera dinámicamente -->
                    </div>

                    <!-- Días seleccionados -->
                    <div class="form-group">
                        <label>Días Seleccionados:</label>
                        <div id="selected-dates-list" class="well well-sm"
                            style="max-height: 150px; overflow-y: auto; min-height: 50px;">
                            <em class="text-muted">Haga clic en los días del calendario para seleccionarlos</em>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" id="btn-guardar-vacaciones" class="btn btn-primary">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .vacation-calendar {
        width: 100%;
        border-collapse: collapse;
    }

    .vacation-calendar th {
        background-color: #f5f5f5;
        padding: 10px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #ddd;
    }

    .vacation-calendar td {
        padding: 8px;
        text-align: center;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: all 0.2s;
    }

    .vacation-calendar td.empty {
        background-color: #fafafa;
        cursor: default;
    }

    .vacation-calendar td.weekend {
        background-color: #f0f0f0;
        color: #999;
        cursor: not-allowed;
    }

    .vacation-calendar td.selectable:hover {
        background-color: #e3f2fd;
        transform: scale(1.05);
    }

    .vacation-calendar td.selected {
        background-color: #4caf50;
        color: white;
        font-weight: bold;
    }

    .vacation-calendar td.past {
        background-color: #fafafa;
        color: #ccc;
        cursor: not-allowed;
    }

    .vacation-calendar td.current-day {
        border: 2px solid #2196f3;
    }
</style>

<!-- Modal para mostrar historial detallado -->
<div class="modal fade" id="modalHistorialVacaciones" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-history"></i> Historial Completo de Vacaciones
                </h4>
            </div>
            <div class="modal-body">
                <table id="table-historial-vacaciones" data-toggle="table" data-search="true" data-show-refresh="true"
                    data-pagination="true" data-page-size="20">
                    <thead>
                        <tr>
                            <th data-field="year" data-sortable="true">Año</th>
                            <th data-field="fecha_inicio" data-sortable="true">Inicio</th>
                            <th data-field="fecha_fin" data-sortable="true">Fin</th>
                            <th data-field="dias_totales" data-sortable="true">Días</th>
                            <th data-field="estado" data-sortable="true">Estado</th>
                            <th data-field="fecha_aprobacion" data-sortable="true">F. Aprobación</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="modules/vacaciones/ficha_vacaciones_resumen.js?v=<?php echo time(); ?>"></script>