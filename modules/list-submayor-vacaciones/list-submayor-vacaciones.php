<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-base">

            <div class="tab-content">


                <div id="tab-listado" class="tab-pane fade active in">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="fa fa-calendar-check-o"></i> Submayor de Vacaciones de Trabajadores
                            </h4>

                        </div>
                        <div class="panel-body">
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-md-6">
                                    <!-- <button type="button" class="btn btn-success" id="btn-agregar-vacaciones">
                                        <i class="fa fa-plus"></i> Agregar Vacaciones
                                    </button> -->
                                    <button type="button" class="btn btn-danger" id="btn-generar-pdf" style="margin-left: 10px;">
                                        <i class="fa fa-file-pdf-o"></i> Generar PDF
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="alert alert-info" style="margin: 0; padding: 8px 12px; display: inline-block;">
                                        <i class="fa fa-info-circle"></i>
                                        <small><strong>Tip:</strong> Haga clic en los valores para editarlos directamente.</small>
                                    </div>
                                </div>
                            </div>

                            <table
                                id="table-submayor-vacaciones"
                                data-toggle="table"
                                data-url="api-app.php?module=submayor-vacaciones&method=list"
                                data-search="true"
                                data-show-refresh="true"
                                data-show-toggle="false"
                                data-show-columns="false"
                                data-sort-name="nombre_completo"
                                data-page-list="[25, 50, 100]"
                                data-page-size="25"
                                data-pagination="true"
                                data-show-pagination-switch="true">
                                <thead>
                                    <tr>
                                        <th data-field="foto" data-formatter="formatoFoto" data-sortable="false" data-width="80">Foto</th>
                                        <th data-field="nombre_completo" data-sortable="true" data-formatter="nombreCompletoFormatter">Nombre y Apellidos</th>
                                        <th data-field="carnet_identidad" data-sortable="true" data-width="120">CI</th>
                                        <th data-field="cargo_nombre" data-sortable="true" data-width="120">Cargo</th>
                                        <th data-field="cargo_salario" data-align="right" data-sortable="true" data-formatter="salarioCargoFormatter" data-width="120">Salario/Mensual</th>
                                        <th data-field="vacaciones_disponibles" data-align="center" data-sortable="true" data-formatter="vacacionesDisponiblesFormatter" data-width="140">Vac. Disponibles</th>
                                        <th data-field="salario_acumulado" data-align="right" data-sortable="true" data-formatter="salarioAcumuladoFormatter" data-width="130">Salario Acumulado</th>

                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar valores -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="editModalLabel">Editar Valor</h4>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit-trabajador-id">
                    <input type="hidden" id="edit-field">

                    <div class="form-group">
                        <label id="edit-label">Campo:</label>
                        <input type="number" class="form-control" id="edit-value" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Trabajador:</label>
                        <p id="edit-trabajador-nombre" class="form-control-static"></p>
                    </div>

                    <div class="form-group" id="edit-cargo-info" style="display: none;">
                        <label>Cargo:</label>
                        <p id="edit-cargo-nombre" class="form-control-static"></p>
                        <label>Salario por día:</label>
                        <p id="edit-cargo-salario" class="form-control-static"></p>
                    </div>

                    <div class="form-group" id="edit-calculo-info" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fa fa-calculator"></i>
                            <strong>Cálculo automático:</strong> <span id="edit-calculo-detalle"></span>
                        </div>
                    </div>

                    <div class="form-group" id="edit-salario-info" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-circle"></i>
                            <strong>Campo Obligatorio:</strong> Ingrese manualmente el salario acumulado deseado. No se calcula automáticamente.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-edit">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Vacaciones -->
<div class="modal fade" id="addVacacionesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plus"></i> Agregar Registro de Vacaciones
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Nuevo registro en tabla submayor_vacaciones</strong><br>
                    Complete los campos requeridos para insertar un nuevo registro de vacaciones.
                </div>

                <form id="form-add-vacaciones">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="add-trabajador">
                                    <i class="fa fa-user"></i> Trabajador: <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="add-trabajador" name="trabajador_id" required>
                                    <option value="">Seleccione un trabajador...</option>
                                </select>
                                <small class="help-block">Campo: <code>id_trabajador</code></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-vacaciones">
                                    <i class="fa fa-calendar"></i> Días de Vacaciones: <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="add-vacaciones" name="vacaciones"
                                    min="1" step="1" required placeholder="Ej: 15">
                                <small class="help-block">Campo: <code>vacaciones</code></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add-pago-vacaciones">
                                    <i class="fa fa-money"></i> Pago Vacaciones (Opcional):
                                </label>
                                <input type="number" class="form-control" id="add-pago-vacaciones" name="pago_vacaciones"
                                    min="0" step="0.01" placeholder="Cálculo automático">
                                <small class="help-block">Campo: <code>pago_vacaciones</code> - Se calcula automáticamente si se deja vacío</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="add-cargo-info" style="display: none;">
                        <div class="alert alert-info">
                            <strong><i class="fa fa-briefcase"></i> Información del Cargo:</strong><br>
                            <strong>Cargo:</strong> <span id="add-cargo-nombre"></span><br>
                            <strong>Salario por dia:</strong> $<span id="add-cargo-salario"></span>
                        </div>
                    </div>
                    *
                    <div class="form-group" id="add-calculo-info" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fa fa-calculator"></i>
                            <strong>Cálculo automático:</strong> <span id="add-calculo-detalle"></span><br>
                            <small>Si no especifica el pago, se calculará como: días × (salario del cargo mensual / 24)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btn-save-add">
                    <i class="fa fa-save"></i> Guardar en Base de Datos
                </button>
            </div>
        </div>
    </div>
</div>

<script src="modules/list-submayor-vacaciones/list-submayor-vacaciones.js"></script>