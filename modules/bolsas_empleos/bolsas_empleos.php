<script>
    var action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">Postulante</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Ficha del postulante</p>" data-html="true" title=""></a>
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
                <label class="control-label" for="f-id">Código de Postulación</label>
                <input type="text" id="f-id" name="id" class="form-control" placeholder="ID" value="<?php if (isset($data['id'])) print($data['id']); ?>" disabled>
                <small class="help-block">Identificador único de la postulación</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-nombre">Nombre</label>
                <input type="text" id="f-nombre" name="nombre" class="form-control" placeholder="Nombre" value="<?php if (isset($data['nombre'])) print($data['nombre']); ?>">
                <small class="help-block">Nombre del postulante</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-apellidos">Apellidos</label>
                <input type="text" id="f-apellidos" name="apellidos" class="form-control" placeholder="Apellidos" value="<?php if (isset($data['apellidos'])) print($data['apellidos']); ?>">
                <small class="help-block">Apellidos del postulante</small>
            </div>
        </div>
    </div>

    <!-- Fila adicional: Segundo Apellido -->
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label" for="f-apellidos-segundos">Segundo Apellido</label>
                <input type="text" id="f-apellidos-segundos" name="segundos_apellidos" class="form-control" placeholder="Segundo Apellido" value="<?php if (isset($data['segundos_apellidos'])) print($data['segundos_apellidos']); ?>">
                <small class="help-block">Segundo apellido del postulante</small>
            </div>
        </div>
    </div>

    <!-- Segunda fila -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="f-telefono">Teléfono</label>
                <input type="text" id="f-telefono" name="telefono" class="form-control" placeholder="Teléfono" value="<?php if (isset($data['telefono'])) print($data['telefono']); ?>">
                <small class="help-block">Teléfono de contacto</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="f-email">Email</label>
                <input type="email" id="f-email" name="email" class="form-control" placeholder="Email" value="<?php if (isset($data['email'])) print($data['email']); ?>">
                <small class="help-block">Correo electrónico</small>
            </div>
        </div>
    </div>



    <!-- Tercera fila -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="f-cargo">Cargo al que Postula</label>
                <select id="f-cargo" name="cargo_postulado_id" class="form-control">
                    <option value="">Seleccione un cargo</option>
                    <?php foreach ($data_form['cargos'] as $k => $v) { ?>
                        <option value="<?php print($v['id']) ?>" <?php if (isset($data['cargo_postulado_id']) && $data['cargo_postulado_id'] == $v['id']) print('selected'); ?>><?php print($v['nombre']) ?></option>
                    <?php } ?>
                </select>
                <small class="help-block">Cargo al que se postula</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="f-estatus">Estatus</label>
                <select id="f-estatus" name="estatus" class="form-control">
                    <option value="">Seleccione</option>
                    <option value="pendiente" <?php if (isset($data['estatus']) && $data['estatus'] == 'pendiente') print('selected'); ?>>Pendiente</option>
                    <option value="en_revision" <?php if (isset($data['estatus']) && $data['estatus'] == 'en_revision') print('selected'); ?>>En Revisión</option>
                    <option value="aprobado" <?php if (isset($data['estatus']) && $data['estatus'] == 'aprobado') print('selected'); ?>>Aprobado</option>
                    <option value="rechazado" <?php if (isset($data['estatus']) && $data['estatus'] == 'rechazado') print('selected'); ?>>Rechazado</option>
                </select>
                <small class="help-block">Estado de la postulación</small>
            </div>
        </div>
    </div>

    <!-- Cuarta fila -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="f-curriculum">Currículum Vitae</label>
                <input type="file" id="f-curriculum" name="curriculum" class="form-control" accept=".pdf,.doc,.docx">
                <?php if (isset($data['curriculum']) && !empty($data['curriculum'])) { ?>
                    <div class="mar-top">
                        <a href="<?php print($data['curriculum']); ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-file-pdf-o"></i> Ver Currículum Actual
                        </a>
                    </div>
                <?php } ?>
                <small class="help-block">Adjunte su currículum en formato PDF, DOC o DOCX</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="f-fecha-registro">Fecha Registro</label>
                <input type="date" id="f-fecha-registro" name="fecha_registro" class="form-control" value="<?php if (isset($data['fecha_registro'])) print($data['fecha_registro']); else print(date('Y-m-d')); ?>">
                <small class="help-block">Fecha de registro</small>
            </div>
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