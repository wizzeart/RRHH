<?php
$trabajadoresAsignacion = isset($app) && isset($app->db)
    ? $app->db->fetchAll("SELECT id, CONCAT(nombre, ' ', apellidos) as nombre_completo
                            FROM trabajadores
                           WHERE trabajador_eliminado = 0
                        ORDER BY nombre, apellidos")
    : array();
?>

<!-- Modal Asignación de Recursos (a un trabajador o a un objeto definido a mano) -->

<div class="modal fade" id="recursoModalAsignar" tabindex="-1" role="dialog" aria-labelledby="recursoModalAsignarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="recursoModalAsignarLabel">Asignación de Recurso</h4>
            </div>

            <div class="modal-body">
                <form id="recursoAsignarForm" method="POST">
                    <input type="hidden" id="asig-recurso-id" name="recurso_id" value="">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="asig-recurso-nombre">Recurso</label>
                                <input type="text" id="asig-recurso-nombre" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Asignar a <span class="text-danger">*</span></label>
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="tipo_asignacion" value="trabajador" checked> Trabajador
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="tipo_asignacion" value="objeto"> Objeto
                                    </label>
                                </div>
                                <small class="help-block">Elija "Objeto" para asignar el recurso a algo que no es una
                                    persona (por ejemplo, una línea a un GPS).</small>
                            </div>
                        </div>

                        <div class="col-md-12" id="asig-bloque-trabajador">
                            <div class="form-group">
                                <label for="asig-trabajador-id">Trabajador <span class="text-danger">*</span></label>
                                <select id="asig-trabajador-id" name="trabajador_id" class="form-control">
                                    <option value="">Seleccione un trabajador</option>
                                    <?php foreach ($trabajadoresAsignacion as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t['id']); ?>"><?php echo htmlspecialchars($t['nombre_completo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12 hidden" id="asig-bloque-objeto">
                            <div class="form-group">
                                <label for="asig-asignado-a">Objeto <span class="text-danger">*</span></label>
                                <input type="text" id="asig-asignado-a" name="asignado_a" class="form-control"
                                    maxlength="150" placeholder="Ej: GPS Camión 12, Oficina Central, Vehículo P-345...">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="asig-fecha-entrega">Fecha de Entrega <span class="text-danger">*</span></label>
                                <input type="date" id="asig-fecha-entrega" name="fecha_entrega_a_t" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button id="btn-save-recurso-asignar" type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Asignar
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
