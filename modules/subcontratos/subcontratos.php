
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <form id="form-subcontrato">
            <input type="hidden" id="f-id" name="id" value="<?php if (isset($data['id'])) print($data['id']); ?>">
            
            <!-- Primera fila -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-nombre">Nombre de la Persona /Entidad   <span class="text-danger">*</span></label>
                        <input type="text" id="f-nombre" name="persona_nombre" class="form-control" placeholder="Nombre completo de la persona" value="<?php if (isset($data['persona_nombre'])) print($data['persona_nombre']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-estatus">Estatus <span class="text-danger">*</span></label>
                        <select id="f-estatus" name="estatus" class="form-control">
                            <option value="">Seleccione estatus</option>
                            <option value="TCP" <?php if (isset($data['estatus']) && $data['estatus'] == 'TCP') print('selected'); ?>>TCP (Trabajador por Cuenta Propia)</option>
                            <option value="Trabajador" <?php if (isset($data['estatus']) && $data['estatus'] == 'Trabajador') print('selected'); ?>>Trabajador</option>
                            <option value="Entidad" <?php if (isset($data['estatus']) && $data['estatus'] == 'Entidad') print('selected'); ?>>Entidad</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-entidad">Entidad Representada</label>
                        <input type="text" id="f-entidad" name="entidad_representada" class="form-control" placeholder="Entidad que representa" value="<?php if (isset($data['entidad_representada'])) print($data['entidad_representada']); ?>">
                    </div>
                </div>
            </div>

            <!-- Segunda fila: CI y Áreas de Acceso para simetría -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-ci">CI - Carnet de Identidad <span class="text-danger">*</span></label>
                        <input type="text" id="f-ci" name="carnet_identidad" class="form-control" placeholder="Ej: 12345678901" maxlength="11" value="<?php if (isset($data['carnet_identidad'])) print($data['carnet_identidad']); ?>">
                        <small class="help-block">Debe tener 11 dígitos</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-areas">Áreas de Acceso</label>
                        <input type="text" id="f-areas" name="areas_acceso" class="form-control" placeholder="Áreas permitidas para acceso" value="<?php if (isset($data['areas_acceso'])) print($data['areas_acceso']); ?>">
                        <small class="help-block">Separar múltiples áreas con comas</small>
                    </div>
                </div>
            </div>

            <!-- Nueva fila: Dirección, Provincia y Municipio -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-direccion">Dirección</label>
                        <input type="text" id="f-direccion" name="direccion" class="form-control" placeholder="Calle y número, entre calles (opcional)">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-provincia">Provincia</label>
                        <select id="f-provincia" name="provincia_id" class="form-control">
                            <option value="">Seleccione provincia</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-municipio">Municipio</label>
                        <select id="f-municipio" name="municipio_id" class="form-control" disabled>
                            <option value="">Seleccione municipio</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tercera fila: Servicio/Objeto del contrato a ancho completo -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" for="f-servicio">Servicio u Objeto de Contrato <span class="text-danger">*</span></label>
                        <textarea id="f-servicio" name="servicio_objeto" class="form-control" rows="4" style="resize: none; height: 120px;" placeholder="Descripción del servicio o objeto del contrato"><?php if (isset($data['servicio_objeto'])) print($data['servicio_objeto']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Cuarta fila: Fechas -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-fecha-inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" id="f-fecha-inicio" name="fecha_inicio" class="form-control" value="<?php if (isset($data['fecha_inicio'])) print($data['fecha_inicio']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-fecha-fin">Fecha de Fin</label>
                        <div class="input-group">
                            <input type="date" id="f-fecha-fin" name="fecha_fin" class="form-control" value="<?php if (isset($data['fecha_fin'])) print($data['fecha_fin']); ?>" disabled>
                            <div class="input-group-append" style="display:flex; align-items:center; padding-left:8px;">
                                <div class="checkbox" style="margin:0;">
                                    <label style="margin:0;">
                                        <input type="checkbox" id="check-fecha-fin" <?php if (isset($data['fecha_fin']) && !empty($data['fecha_fin'])) print('checked'); ?>>
                                        Tiene fin
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="help-block">Active la casilla para habilitar la fecha de fin</small>
                    </div>
                </div>
            </div>

        </form>
        
        <!-- Panel Footer con botones estándar -->
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
        </div>
    </div>
</div>

<!-- Vista previa del subcontrato en tiempo real -->
<div class="panel" style="margin-top:20px;">
    <div class="panel-heading">
        <div class="row" style="display:flex; align-items:center;">
            <div class="col-xs-8 col-sm-9">
                <h4 class="panel-title" style="margin:0;">Vista Previa del Subcontrato</h4>
            </div>
            <div class="col-xs-4 col-sm-3 text-right">
                <button id="btn-preview-fullscreen-subcontrato" type="button" class="btn btn-default btn-sm" title="Ver a pantalla completa">
                    <i class="fa fa-arrows-alt"></i> Ver grande
                </button>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div id="subcontrato-preview" style="border:1px solid #e1e1e1; height:90vh; overflow:hidden; background:#fff; font-family: 'Times New Roman', serif; line-height: 1.6;">
            <p class="text-muted text-center">Complete los campos del formulario para ver la vista previa del subcontrato</p>
        </div>
    </div>
</div>

<!-- Modal para generar pase de acceso -->
<div class="modal fade" id="paseModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Pase de Acceso - Subcontratista</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Aquí se insertan los datos del pase -->
      </div>
    </div>
  </div>
</div>
