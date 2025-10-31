<script>
    var action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>

<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">General</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Ficha del usuario</p>" data-html="true" title=""></a>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-general">
                <div class="panel">
                    <div class="panel-body orm-padding"><!-- form-horizontal -->

                        <!--Text Input-->
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-title">Código Usuario</label>
                                    <input type="text" id="f-usuario-id" name="xusuario_id" class="form-control" placeholder="ID Usuario" value="<?php if (isset($data['xusuario_id'])) print($data['xusuario_id']); ?>" disabled="true">
                                    <small class="help-block">Código del usuario</small>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="control-label" for="f-usuario">Nombre del Usuario</label>
                                    <input type="text" id="f-usuario" name="xusuario" class="form-control" placeholder="Nombre Usuario" value="<?php if (isset($data['xusuario'])) print($data['xusuario']); ?>" required>
                                    <small class="help-block">Nombre del usuario</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-activo">Activo</label>
                                    <select id="f-activo" name="xactivo" class="form-control" required>
                                        <option value="S" <?php if (isset($data['xactivo']) && $data['xactivo'] == 'S') print('selected'); ?>>Sí</option>
                                        <option value="N" <?php if (isset($data['xactivo']) && $data['xactivo'] == 'N') print('selected'); ?>>No</option>
                                    </select>
                                    <small class="help-block">Indica si está activo</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-email">Email</label>
                                    <input type="text" id="f-email" name="xemail" class="form-control" placeholder="Email" value="<?php if (isset($data['xemail'])) print($data['xemail']); ?>" required>
                                    <small class="help-block">Email del Usuario</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-pass">Contraseña</label>
                                    <input type="password" id="f-pass" name="xpwd" class="form-control" placeholder="Contraseña" value="" required>
                                    <small class="help-block">Contraseña del usuario, si se rellena se cambiará</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-rol">Rol</label>
                                    <select id="f-rol" name="xrol_id" class="form-control" data-placeholder="Selecciona un rol" required>
                                        <option value="">Selecciona un rol</option>
                                        <?php foreach ($data_form['roles'] as $k => $v) { ?>
                                            <?php if ($v['xrol_id'] == $data['xrol_id']) { ?>
                                                <option value="<?php print($v['xrol_id']) ?>" selected><?php print($v['xrol']) ?></option>
                                            <?php } else { ?>
                                                <option value="<?php print($v['xrol_id']) ?>"><?php print($v['xrol']) ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <small class="help-block">Indica el rol del usuario</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea id="f-obs" name="xobs" class="form-control" rows="5"><?php if (isset($data['xobs'])) print($data['xobs']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer text-center">
                        <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
                        <button id="btn-save" class="btn btn-info icon-lg" type="button">
                            <i class="fa fa-check"></i>
                            Guardar
                        </button>
                        <button id="btn-back" class="btn btn-default icon-lg" type="button">
                            <i class="fa fa-undo"></i>
                            Volver
                        </button>
                    </div>
                </div>
            </div><!-- TAB PEDIDOS -->
        </div>
    </div>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->
</div>