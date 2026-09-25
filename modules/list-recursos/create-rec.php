<?php
    $cats = isset($app) && isset($app->db) 
        ? $app->db->fetchAll("SELECT id, nombre FROM categorias_recurso ORDER BY nombre") 
        : array();
?>

<!-- Modal Creación de Recursos -->

<div class="modal fade" id="recursoModal3" tabindex="-1" role="dialog" aria-labelledby="recursoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document"> 
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="recursoModalLabel">Creación de Recursos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="modalRecursoBody3">
                <form id="recursoCreateForm" enctype="multipart/form-data" method="POST">
                    <input type="hidden" id="rec-id" name="id" value="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="rec-categoria">Categoría <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select id="rec-categoria" name="categoria_id" class="form-control" required>
                                        <option value="">Seleccione una categoría</option>
                                        <?php foreach ($cats as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="button" id="btn-add-categoria" class="btn btn-success" title="Crear categoría">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </span>
                                </div>
                                <div class="input-group" id="nueva-categoria-wrap" style="margin-top:6px; display:none;">
                                    <input type="text" id="nueva-categoria-nombre" class="form-control" placeholder="Nombre de la nueva categoría" maxlength="100">
                                    <span class="input-group-btn">
                                        <button type="button" id="btn-guardar-categoria" class="btn btn-success" title="Guardar categoría">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button type="button" id="btn-cancelar-categoria" class="btn btn-default" title="Cancelar">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="rec-nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" id="rec-nombre" name="nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="rec-descripcion">Descripción</label>
                                <textarea id="rec-descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button id="btn-save-recurso-create" type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Guardar
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