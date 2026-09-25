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
                    <label class="control-label" for="f-nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="f-nombre" class="form-control" placeholder="Nombre del aspecto" value="<?php if (isset($data['nombre'])) print($data['nombre']); ?>" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-calificacion-max">Calificación Máxima <span class="text-danger">*</span></label>
                    <input type="number" id="f-calificacion-max" class="form-control" placeholder="100" min="1" max="100" value="<?php if (isset($data['calificacion_max'])) print($data['calificacion_max']); else print('100'); ?>" />
                    <small class="text-muted">Todos los aspectos deben sumar 100</small>
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
