<script>
    var action = '<?php print ($action) ?>';
    var rol = '<?php print ($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print ($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3" style="display: none;">
                <div class="form-group">
                    <label class="control-label" for="f-id">ID</label>
                    <input type="text" id="f-id" class="form-control" value="<?php if (isset($data['id'])) print($data['id']); ?>" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="f-aspecto_id">Aspecto <span class="text-danger">*</span></label>
                    <select id="f-aspecto_id" class="form-control">
                        <option value="">Seleccione aspecto</option>
                        <?php if(isset($data_form['aspectos'])) { foreach($data_form['aspectos'] as $a) { ?>
                            <option value="<?php print($a['id']); ?>" <?php if (isset($data['aspecto_id']) && $data['aspecto_id']==$a['id']) print('selected'); ?>><?php print($a['nombre']); ?></option>
                        <?php } } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="f-descripcion">Descripción <span class="text-danger">*</span></label>
                    <textarea id="f-descripcion" class="form-control" rows="3" placeholder="Descripción del subaspecto"><?php if (isset($data['descripcion'])) print($data['descripcion']); ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-calificacion-max">Calificación Máxima <span class="text-danger">*</span></label>
                    <input type="number" id="f-calificacion-max" class="form-control" placeholder="100" min="1" max="100" value="<?php if (isset($data['calificacion_max'])) print($data['calificacion_max']); else print('100'); ?>" />
                    <small class="text-muted">La suma de todos los subaspectos debe igualar la calificación máxima del aspecto</small>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label class="control-label">Información del Aspecto</label>
                    <div id="aspecto-info" class="alert alert-info" style="display: none;">
                        <strong>Aspecto:</strong> <span id="aspecto-nombre"></span><br>
                        <strong>Calificación Máxima:</strong> <span id="aspecto-calificacion-max"></span><br>
                        <strong>Suma actual de subaspectos:</strong> <span id="subaspectos-suma"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
            <button id="btn-save" class="btn btn-info icon-lg" type="button">
                <i class="fa fa-check fa-lg"></i> Guardar
            </button>
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo fa-lg"></i> Volver
            </button>
        </div>
    </div>
</div>
