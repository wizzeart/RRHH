<div class="panel">
<div class="panel-heading">
        <h3 class="panel-title">Registrar Contrato</h3>
    </div>
    <div class="panel-body">
        <form id="form-contrato" enctype="multipart/form-data">
            <input type="hidden" id="f-id" name="id" value="<?php if (isset($data['id'])) print($data['id']); ?>">
            <input type="hidden" id="f-archivo-contrato" value="<?php echo isset($data['archivo_contrato']) ? htmlspecialchars($data['archivo_contrato'], ENT_QUOTES, 'UTF-8') : ''; ?>">

            <!-- Fila 1: Trabajador y Tipo de Contrato -->
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
                                    $full = trim(($t['nombre'] ?? '') . ' ' . ($t['apellidos'] ?? '') . ' ' . ($t['apellidos_segundos'] ?? ''));
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
                        <label class="control-label" for="f-tipo-contrato">Tipo de Contrato <span class="text-danger">*</span></label>
                        <select id="f-tipo-contrato" name="tipo_contrato" class="form-control">
                            <option value="">Seleccione tipo</option>
                            <option value="1" <?php echo (isset($data['tipo']) && $data['tipo'] == '1') ? 'selected' : ''; ?>>1 - Tiempo Determinado</option>
                            <option value="2" <?php echo (isset($data['tipo']) && $data['tipo'] == '2') ? 'selected' : ''; ?>>2 - Tiempo Indeterminado</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Datos del Trabajador (Solo lectura) -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h4 class="panel-title">Datos del Trabajador</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Nombre:</label>
                                    <div id="trabajador-nombre" class="form-control-static">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label>Apellidos:</label>
                                    <div id="trabajador-apellidos" class="form-control-static">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label>Segundos Apellidos:</label>
                                    <div id="trabajador-apellidos-segundos" class="form-control-static">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label>Cargo:</label>
                                    <div id="trabajador-cargo" class="form-control-static">-</div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-4">
                                    <label>Provincia:</label>
                                    <div id="trabajador-provincia" class="form-control-static">-</div>
                                </div>
                                <div class="col-md-4">
                                    <label>Municipio:</label>
                                    <div id="trabajador-municipio" class="form-control-static">-</div>
                                </div>
                                <div class="col-md-4">
                                    <label>Dirección:</label>
                                    <div id="trabajador-direccion" class="form-control-static">-</div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 15px;">
                                <div class="col-md-12">
                                    <label class="control-label">Régimen de Trabajo</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="f-regimen-trabajo-desde" class="control-label">Desde (día)</label>
                                        <select id="f-regimen-trabajo-desde" name="regimen_trabajo_desde" class="form-control">
                                            <option value="">Seleccione día</option>
                                            <option value="Lunes">Lunes</option>
                                            <option value="Martes">Martes</option>
                                            <option value="Miércoles">Miércoles</option>
                                            <option value="Jueves">Jueves</option>
                                            <option value="Viernes">Viernes</option>
                                            <option value="Sábado">Sábado</option>
                                            <option value="Domingo">Domingo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="f-regimen-trabajo-hasta" class="control-label">Hasta (día)</label>
                                        <select id="f-regimen-trabajo-hasta" name="regimen_trabajo_hasta" class="form-control">
                                            <option value="">Seleccione día</option>
                                            <option value="Lunes">Lunes</option>
                                            <option value="Martes">Martes</option>
                                            <option value="Miércoles">Miércoles</option>
                                            <option value="Jueves">Jueves</option>
                                            <option value="Viernes">Viernes</option>
                                            <option value="Sábado">Sábado</option>
                                            <option value="Domingo">Domingo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="f-desde-hora" class="control-label">Desde Hora</label>
                                        <input type="time" id="f-desde-hora" name="hora_desde_h" class="form-control" step="3600" value="" placeholder="HH" />
                                        <small class="text-muted">Solo horas (sin minutos ni segundos)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="f-hasta-hora" class="control-label">Hasta Hora</label>
                                        <input type="time" id="f-hasta-hora" name="hora_hasta_h" class="form-control" step="3600" value="" placeholder="HH" />
                                        <small class="text-muted">Solo horas (sin minutos ni segundos)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 3: Campos del Contrato -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-ubicacion-laboral">Ubicación Laboral <span class="text-danger">*</span></label>
                        <select id="f-ubicacion-laboral" name="departamento_id" class="form-control">
                            <option value="">Seleccione departamento</option>
                            <?php 
                            if (isset($data_form['departamentos']) && is_array($data_form['departamentos'])) {
                                foreach ($data_form['departamentos'] as $d) {
                                    $sel = (isset($data['departamento_id']) && $data['departamento_id'] == $d['id']) ? 'selected' : '';
                                    print('<option value="' . intval($d['id']) . '" ' . $sel . '>' . htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8') . '</option>');
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-regimen-descanso">Dias de Descanso <span class="text-danger">*</span></label>
                        <input type="text" id="f-regimen-descanso" name="regimen_descanso" class="form-control" value="<?php echo isset($data['regimen_descanso']) ? htmlspecialchars($data['regimen_descanso'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Ej: Dias de descanso">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-salario-base">Salario Base <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="f-salario-base" name="salario_base" class="form-control" value="<?php echo isset($data['salario_base']) ? $data['salario_base'] : ''; ?>" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-frecuencia-trabajo">Frecuencia de Pago/Trabajo</label>
                        <select id="f-frecuencia-trabajo" name="frecuencia_trabajo" class="form-control">
                            <option value="">Seleccione frecuencia</option>
                            <option value="semanal">Semanal</option>
                            <option value="quincenal">Quincenal</option>
                            <option value="mensual">Mensual</option>
                        </select>
                        <small class="text-muted">Se reflejará como (X) en el contrato.</small>
                    </div>
                </div>
            </div>

            <!-- Fila 4: Modalidad de Trabajo -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-modalidad-trabajo">Modalidad de Trabajo <span class="text-danger">*</span></label>
                        <select id="f-modalidad-trabajo" name="modalidad_trabajo" class="form-control">
                            <option value="">Seleccione modalidad</option>
                            <option value="1" <?php echo (isset($data['modalidad_trabajo']) && $data['modalidad_trabajo'] == '1') ? 'selected' : ''; ?>>Presencial</option>
                            <option value="2" <?php echo (isset($data['modalidad_trabajo']) && $data['modalidad_trabajo'] == '2') ? 'selected' : ''; ?>>A distancia</option>
                            <option value="3" <?php echo (isset($data['modalidad_trabajo']) && $data['modalidad_trabajo'] == '3') ? 'selected' : ''; ?>>Teletrabajo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-firma">Firma Digital</label>
                        <input type="file" id="f-firma" name="firma_digital" class="form-control" accept=".png,.jpg,.jpeg,.pdf">
                        <?php if (!empty($data['firma_digital'])) { echo '<small><a href="' . htmlspecialchars($data['firma_digital'], ENT_QUOTES, 'UTF-8') . '" target="_blank">Ver firma actual</a></small>'; } ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
           
            <button id="btn-save" class="btn btn-success icon-lg" type="button">
                <i class="fa fa-save"></i>
                Guardar Contrato
            </button>
            
            <button id="btn-generate-pdf" class="btn btn-primary icon-lg" type="button">
                <i class="fa fa-file-pdf-o"></i>
                Generar PDF
            </button>

            <?php if (isset($data['archivo_contrato']) && !empty($data['archivo_contrato'])): ?>
            <a href="<?php echo htmlspecialchars($data['archivo_contrato'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-info icon-lg" id="btn-download-pdf">
                <i class="fa fa-download"></i>
                Descargar PDF
            </a>
            <?php endif; ?>
            
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo"></i>
                Volver
            </button>
        </div>
    </div>
</div>

<!-- Vista previa del contrato en tiempo real -->
<div class="panel" style="margin-top:20px;">
    <div class="panel-heading">
        <div class="row" style="display:flex; align-items:center;">
            <div class="col-xs-8 col-sm-9">
                <h4 class="panel-title" style="margin:0;">Vista Previa del Contrato</h4>
            </div>
            <div class="col-xs-4 col-sm-3 text-right">
                <button id="btn-preview-fullscreen" type="button" class="btn btn-default btn-sm" title="Ver a pantalla completa">
                    <i class="fa fa-arrows-alt"></i> Ver grande
                </button>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div id="contrato-preview" style="border:1px solid #e1e1e1; height:90vh; overflow:hidden; background:#fff; font-family: 'Times New Roman', serif; line-height: 1.6;">
            <p class="text-muted text-center">Complete los campos del formulario para ver la vista previa del contrato</p>
        </div>
    </div>
</div>

