<div class="panel">
<div class="panel-heading">
        <h3 class="panel-title">Registrar Contrato</h3>
    </div>
    <div class="panel-body">
        <form id="form-contrato" enctype="multipart/form-data">
            <input type="hidden" id="f-id" name="id" value="<?php if (isset($data['id'])) print($data['id']); ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-trabajador">Trabajador <span class="text-danger">*</span></label>
                        <select id="f-trabajador" name="trabajador_id" class="form-control">
                            <option value="">Seleccione trabajador</option>
                            <?php 
                            if (isset($data_form['trabajadores']) && is_array($data_form['trabajadores'])) {
                                foreach ($data_form['trabajadores'] as $t) {
                                    $id = $t['id'];
                                    $full = trim(($t['nombre'] ?? '') . ' ' . ($t['apellidos'] ?? ''));
                                    if ($full === '') continue;
                                    $sel = (isset($data['trabajador_id']) && $data['trabajador_id'] == $id) ? 'selected' : '';
                                    print('<option value="' . intval($id) . '" ' . $sel . '>' . htmlspecialchars($full, ENT_QUOTES, 'UTF-8') . '</option>');
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-tipo">Tipo <span class="text-danger">*</span></label>
                        <select id="f-tipo" name="tipo" class="form-control">
                            <option value="">Seleccione tipo</option>
                            <?php 
                                $tipos = array('Contrato por Tiempo Indeterminado', 'Contrato por Tiempo Determinado');
                                $tipoSel = isset($data['tipo']) ? $data['tipo'] : '';
                                foreach ($tipos as $tp) {
                                    $sel = ($tipoSel === $tp) ? 'selected' : '';
                                    print('<option value="' . htmlspecialchars($tp, ENT_QUOTES, 'UTF-8') . '" ' . $sel . '>' . htmlspecialchars($tp, ENT_QUOTES, 'UTF-8') . '</option>');
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-fecha-inicio">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" id="f-fecha-inicio" name="fecha_inicio" class="form-control" value="<?php if (isset($data['fecha_inicio'])) print($data['fecha_inicio']); ?>">
                    </div>
                </div>
             
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Archivo del Contrato</label>
                        <div class="form-control" style="height:auto;">
                            <small class="text-muted">Se generará automáticamente en PDF al guardar.</small><br>
                            <?php if (!empty($data['archivo_contrato'])) { echo '<a class="btn btn-link p-0" href="' . htmlspecialchars($data['archivo_contrato'], ENT_QUOTES, 'UTF-8') . '" target="_blank">Ver contrato generado</a>'; } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-firma">Firma Digital</label>
                        <input type="file" id="f-firma" name="firma_digital" class="form-control" accept=".png,.jpg,.jpeg,.pdf">
                        <?php if (!empty($data['firma_digital'])) { echo '<small><a href="' . htmlspecialchars($data['firma_digital'], ENT_QUOTES, 'UTF-8') . '" target="_blank">Ver firma actual</a></small>'; } ?>
                    </div>
                </div>
            </div>
            <!-- Contenedor para campos extras según tipo de contrato -->
            <div id="extra-contrato-fields" class="row" style="margin-top:10px;"></div>
        </form>

        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
           
            <button id="btn-open-template" class="btn btn-primary icon-lg" type="button">
                <i class="fa fa-file-pdf-o"></i>
                Generar Contrato
            </button>
            
            
            <button id="btn-generate-fpdf" class="btn btn-info icon-lg" type="button">
                <i class="fa fa-file-pdf-o"></i>
                Generar PDF (FPDF)
            </button>
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo"></i>
                Volver
            </button>
          
        </div>
    </div>
</div>

<!-- In-page template preview (full page) -->
<div id="contrato-template-container" style="margin-top:20px; border:1px solid #e1e1e1; padding:12px; max-height:80vh; overflow:auto; background:#fff;">Seleccione tipo y haga clic en "Generar Contrato" para cargar la plantilla aquí.</div>

