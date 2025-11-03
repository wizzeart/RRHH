<!-- Modal Asignación de Recursos -->
<div class="modal fade" id="recursoModal2" tabindex="-1" role="dialog" aria-labelledby="recursoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document"> <!-- modal-lg para más espacio -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="recursoModalLabel">Asignación de Recursos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="modalRecursoBody2">
                <form id="recursoForm" enctype="multipart/form-data" method="POST">

                    <div class="row">
                        <!-- ID oculto -->
                        <div class="col-md-3 d-none hidden">
                            <div class="form-group">
                                <label for="f-trabajador_id-rec">Código del Trabajador</label>
                                <input type="text" id="f-trabajador_id-rec" name="trabajador_id" class="form-control" disabled>
                            </div>
                        </div>

                        <div id="trabajador-rec-nombre" class="col-md-3 d-none">
                            <div class="form-group">
                                <label for="f-trabajador-rec-nombre">Trabajador</label>
                                <input type="text" id="f-trabajador-rec-nombre" name="trabajador_id" class="form-control" disabled>
                            </div>
                        </div>

                        <!-- ID oculto -->
                        <div class="col-md-3 d-none hidden">
                            <div class="form-group">
                                <label for="f-id-rec">Código del Recurso</label>
                                <input type="text" id="f-id-rec" name="id_rec" class="form-control" disabled>
                            </div>
                        </div>

                        <!-- Fecha de Entrega -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="f-fecha-entrega">Fecha de Entrega <span class="text-danger">*</span></label>
                                <input type="date" id="f-fecha-entrega" name="fecha_entrega_a_t" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Fecha Entrega RH -->
                        <div class="col-md-4" id="fecha-rh-section">
                            <div class="form-group">
                                <label for="f-fecha-rh">Fecha Entrega a RH</label>
                                <input type="date" id="f-fecha-rh" name="fecha_entrega_a_rh" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Datos del recurso -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="f-recurso">Recurso <span class="text-danger">*</span></label>
                                <input type="text" id="f-recurso" name="recurso" class="form-control" placeholder="Recurso" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="f-marca">Marca <span class="text-danger">*</span></label>
                                <input type="text" id="f-marca" name="marca" class="form-control" placeholder="Marca" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="f-modelo">Modelo <span class="text-danger">*</span></label>
                                <input type="text" id="f-modelo" name="modelo" class="form-control" placeholder="Modelo" required>
                            </div>
                        </div>
                    </div>

                    <!-- Segunda fila -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="f-color">Color <span class="text-danger">*</span></label>
                                <input type="text" id="f-color" name="color" class="form-control" placeholder="Color" required>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="f-otros">Otros recursos</label>
                                <input type="text" id="f-otros" name="otros_recursos" class="form-control" placeholder="Otros recursos (opcional)">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <!-- <img id="img-loading" class="d-none" src="img/spinners/282.gif" /> -->
                        <button id="btn-save-recurso" type="submit" class="btn btn-primary ml-2">
                            <i class="fa fa-check"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>