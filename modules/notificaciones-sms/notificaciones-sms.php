<script>
    var action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="f-departamentos">Departamentos <span class="text-danger">*</span></label>
                    <select id="f-departamentos" name="departamentos[]" multiple="multiple" class="form-control" data-placeholder="Seleccione departamentos">
                    </select>
                    <small class="help-block">Seleccione los departamentos a los que enviará el SMS</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="f-ubicaciones">Ubicaciones <span class="text-danger">*</span></label>
                    <select id="f-ubicaciones" name="ubicaciones[]" multiple="multiple" class="form-control" data-placeholder="Seleccione ubicaciones">
                    </select>
                    <small class="help-block">Seleccione las ubicaciones a las que enviará el SMS</small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="f-mensaje">Mensaje SMS <span class="text-danger">*</span></label>
                    <textarea id="f-mensaje" class="form-control" rows="4" placeholder="Escriba aquí el mensaje SMS..." maxlength="160"></textarea>
                    <small class="help-block">
                        <span id="char-count">0</span>/160 caracteres (límite estándar SMS)
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4 class="panel-title">Vista Previa de Destinatarios</h4>
                    </div>
                    <div class="panel-body">
                        <div id="preview-recipients">
                            <p class="text-muted">Seleccione departamentos y/o ubicaciones para ver los destinatarios</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
            <button id="btn-preview" class="btn btn-primary icon-lg" type="button">
                <i class="fa fa-eye fa-lg"></i> Vista Previa
            </button>
            <button id="btn-send" class="btn btn-success icon-lg" type="button">
                <i class="fa fa-paper-plane fa-lg"></i> Enviar SMS
            </button>
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo fa-lg"></i> Volver
            </button>
        </div>
    </div>
</div>
