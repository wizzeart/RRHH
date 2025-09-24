<script>
    var action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">Trabajador</a></li>
                <li class="inactive"><a href="#tab-huella" data-toggle="tab" aria-expanded="true">Registrar Huella</a></li>
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

    <!-- Primera fila -->
    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label class="control-label" for="f-id">Código del trabajador</label>
                <input type="text" id="f-id" name="id" class="form-control" placeholder="ID" value="<?php if (isset($data['id'])) print($data['id']); ?>" disabled>
                <small class="help-block">Identificador único</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-nombre">Nombre <span class="text-danger">*</span></label>
                <input type="text" id="f-nombre" name="nombre" class="form-control" placeholder="Nombre del trabajador" value="<?php if (isset($data['nombre'])) print($data['nombre']); ?>">
                <!-- <small class="help-block">Nombre del trabajador</small> -->
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-apellidos">Apellidos <span class="text-danger">*</span></label>
                <input type="text" id="f-apellidos" name="apellidos" class="form-control" placeholder="Apellidos del trabajador" value="<?php if (isset($data['apellidos'])) print($data['apellidos']); ?>">
                <!-- <small class="help-block">Apellidos del trabajador</small> -->
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="control-label" for="f-sexo">Sexo</label>
                <select id="f-sexo" name="sexo" class="form-control">
                    <option value=""></option>
                    <option value="M" <?php if (isset($data['sexo']) && $data['sexo'] == 'M') print('selected'); ?>>Masculino</option>
                    <option value="F" <?php if (isset($data['sexo']) && $data['sexo'] == 'F') print('selected'); ?>>Femenino</option>
                </select>
                <!-- <small class="help-block">Sexo del trabajador</small> -->
            </div>
        </div>
    </div>

    <!-- Segunda fila -->
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="f-ci">Carnet de Identidad <span class="text-danger">*</span></label>
                <input type="text" id="f-ci" name="carnet_identidad" class="form-control" placeholder="Carnet de Identidad del trabajador" value="<?php if (isset($data['carnet_identidad'])) print($data['carnet_identidad']); ?>">
                <!-- <small class="help-block">Documento de identidad</small> -->
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="control-label" for="f-edad">Edad <span class="text-danger">*</span></label>
                <input type="number" id="f-edad" name="edad" class="form-control" placeholder="Edad actual" value="<?php if (isset($data['edad'])) print($data['edad']); ?>">
                <!-- <small class="help-block">Edad actual</small> -->
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-direccion">Dirección <span class="text-danger">*</span></label>
                <input type="text" id="f-direccion" name="direccion" class="form-control" placeholder="Dirección del trabajador" value="<?php if (isset($data['direccion'])) print($data['direccion']); ?>">
                <!-- <small class="help-block">Dirección del trabajador</small> -->
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="f-telefono">Teléfono <span class="text-danger">*</span></label>
                <input type="text" id="f-telefono" name="telefono" class="form-control" placeholder="Teléfono de contacto" value="<?php if (isset($data['telefono'])) print($data['telefono']); ?>">
                <!-- <small class="help-block">Teléfono de contacto</small> -->
            </div>
        </div>
    </div>



    <!-- Tercera fila -->
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-email">Email <span class="text-danger">*</span></label>
                <input type="email" id="f-email" name="email" class="form-control" placeholder="Correo electrónico" value="<?php if (isset($data['email'])) print($data['email']); ?>">
                <!-- <small class="help-block">Correo electrónico</small> -->
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-nivel">Nivel Educacional <span class="text-danger">*</span></label>
                <select id="f-nivel" name="nivel_educacional" class="form-control">
                    <option value="">Seleccione nivel educacional</option>
                    <option value="Universitario" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Universitario') print('selected'); ?>>Universitario</option>
                    <option value="Preuniversitario" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Preuniversitario') print('selected'); ?>>Preuniversitario</option>
                    <option value="Técnico Superior" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Técnico Superior') print('selected'); ?>>Técnico Superior</option>
                    <option value="Técnico Medio" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Técnico Medio') print('selected'); ?>>Técnico Medio</option>
                    <option value="9no Grado" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == '9no Grado') print('selected'); ?>>9no Grado</option>
                </select>
                <!-- <small class="help-block">Nivel académico alcanzado</small> -->
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-cargo">Cargo <span class="text-danger">*</span></label>
                <select id="f-cargo" name="cargos_id" class="form-control">
                    <option value=""></option>
                    <?php foreach ($data_form['cargos'] as $k => $v) { ?>
                        <option value="<?php print($v['id']) ?>" ><?php print($v['nombre']) ?></option>
                    <?php } ?>
                </select>
                <!-- <small class="help-block">Cargo asignado</small> -->
            </div>
        </div>
    </div>

    <!-- Cuarta fila -->
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="f-contratacion">Fecha Contratación <span class="text-danger">*</span></label>
                <input type="date" id="f-contratacion" name="fecha_contratacion" class="form-control" value="<?php if (isset($data['fecha_contratacion'])) print($data['fecha_contratacion']); ?>">
                <!-- <small class="help-block">Inicio del Contrato</small> -->
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div class="checkbox" style="margin-bottom: 10px;">
                    <label>
                        <input type="checkbox" id="check-fecha-baja" <?php if (isset($data['fecha_baja']) && !empty($data['fecha_baja'])) print('checked'); ?>>
                        <strong>El trabajador tiene fecha de baja</strong>
                    </label>
                </div>
                <label class="control-label" for="f-baja">Fecha Baja</label>
                <input type="date" id="f-baja" name="fecha_baja" class="form-control" value="<?php if (isset($data['fecha_baja'])) print($data['fecha_baja']); ?>" disabled>
                <small class="help-block">Marque la casilla superior para activar este campo</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="f-estatus">Estatus <span class="text-danger">*</span></label>
                <select id="f-estatus" name="estatus" class="form-control">
                    <option value=""></option>
                    <option value="activo" <?php if (isset($data['estatus']) && $data['estatus'] == 'activo') print('selected'); ?>>Activo</option>
                    <option value="inactivo" <?php if (isset($data['estatus']) && $data['estatus'] == 'inactivo') print('selected'); ?>>Inactivo</option>
                </select>
                <!-- <small class="help-block">Estado actual</small> -->
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="f-bolsa">Bolsa de Empleo <span class="text-danger">*</span></label>
                <select id="f-bolsa" name="bolsa_empleo_id" class="form-control">
                    <option value=""></option>
                    <?php foreach ($data_form['bolsas'] as $k => $v) { ?>
                       <option value="<?php print($v['id']) ?>" ><?php print($v['nombre']) ?></option>
                    <?php } ?>
                </select>
                <!-- <small class="help-block">Bolsa de empleo asociada</small> -->
            </div>
        </div>
        </div
         <div class="row">
                          
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="f-foto">Adjuntar Foto</label>
                                    <input type="file" id="f-foto" name="foto" class="form-control" accept="image/*">
                                    <?php if (isset($data['foto']) && !empty($data['foto'])) { ?>
                                        <div class="mar-top">
                                            <img src="<?php print($data['foto']); ?>" alt="Foto del trabajador" class="img-thumbnail" style="max-width: 150px; height: auto;">
                                        </div>
                                    <?php } ?>
                                    <small class="help-block">Seleccione una foto del trabajador (opcional)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="preview-container" style="display:none">
                                    <h5>Vista previa:</h5>
                                    <img src="" class="img-thumbnail" style="max-width: 150px; height: auto;">
                                </div>
                            </div>
                        </div>
                    <div class="panel-footer text-center">
                        <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
                        <button id="btn-save" class="btn btn-info icon-lg" type="button">
                            <i class="fa fa-check"></i>
                            Guardar
                        </button>
                        <button id="btn-back" class="btn btn-default icon-lg" type="button">
                            <i class="fa fa-undo"></i>
                            Volver
                        </button>
                        <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                            <i class="fa fa-plus"></i>
                            Nuevo
                        </button>
                      
                        <!-- Sección de Foto del Trabajador -->
                        <hr>
                       
                    </div>
                </div>
            </div><!-- TAB PEDIDOS -->
        </div>
    </div>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->
</div>
<div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-huella">
                <div class="panel">
                    <div class="panel-body orm-padding"><!-- form-horizontal -->
                    </div>
                </div>
           </div>
        </div>
</div>
                    