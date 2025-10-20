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
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-id">ID</label>
                    <input type="text" id="f-id" class="form-control" value="<?php if (isset($data['id'])) print($data['id']); ?>" disabled />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="f-nombre" class="form-control" placeholder="Nombre" value="<?php if (isset($data['nombre'])) print($data['nombre']); ?>" />
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label class="control-label" for="f-sede">Sede <span class="text-danger">*</span></label>
                    <select id="f-sede" class="form-control">
                        <option value="">Seleccione sede</option>
                        <?php if(isset($data_form['sedes'])) { foreach($data_form['sedes'] as $e) { ?>
                            <option value="<?php print($e['id']); ?>" <?php if (isset($data['sede_id']) && $data['sede_id']==$e['id']) print('selected'); ?>><?php print($e['nombre']); ?></option>
                        <?php } } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="f-descripcion">Descripción</label>
                    <textarea id="f-descripcion" class="form-control" rows="3" placeholder="Descripción del departamento"><?php if (isset($data['descripcion'])) print($data['descripcion']); ?></textarea>
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
            <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                <i class="fa fa-plus fa-lg"></i> Nuevo
            </button>
        </div>
    </div>
</div>
