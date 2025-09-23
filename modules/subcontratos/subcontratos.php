<div class="panel">
    <div class="form-control">
        <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button>
        <button id="btn-save" class="btn btn-success btn-icon icon-lg fa fa-save" alt="Guardar" title="Guardar"></button>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <form id="form-subcontrato">
            <input type="hidden" id="f-id" name="id" value="<?php if (isset($data['id'])) print($data['id']); ?>">
            
            <!-- Primera fila -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-nombre">Nombre de la Persona <span class="text-danger">*</span></label>
                        <input type="text" id="f-nombre" name="persona_nombre" class="form-control" placeholder="Nombre completo de la persona" value="<?php if (isset($data['persona_nombre'])) print($data['persona_nombre']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-entidad">Entidad Representada</label>
                        <input type="text" id="f-entidad" name="entidad_representada" class="form-control" placeholder="Entidad que representa" value="<?php if (isset($data['entidad_representada'])) print($data['entidad_representada']); ?>">
                    </div>
                </div>
            </div>

            <!-- Segunda fila -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" for="f-servicio">Servicio u Objeto de Contrato <span class="text-danger">*</span></label>
                        <textarea id="f-servicio" name="servicio_objeto" class="form-control" rows="3" placeholder="Descripción del servicio o objeto del contrato"><?php if (isset($data['servicio_objeto'])) print($data['servicio_objeto']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Tercera fila -->
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
                        <input type="date" id="f-fecha-fin" name="fecha_fin" class="form-control" value="<?php if (isset($data['fecha_fin'])) print($data['fecha_fin']); ?>">
                        <small class="help-block">Dejar vacío si el contrato está activo</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-areas">Áreas de Acceso</label>
                        <input type="text" id="f-areas" name="areas_acceso" class="form-control" placeholder="Áreas permitidas para acceso" value="<?php if (isset($data['areas_acceso'])) print($data['areas_acceso']); ?>">
                        <small class="help-block">Separar múltiples áreas con comas</small>
                    </div>
                </div>
            </div>

        </form>
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
