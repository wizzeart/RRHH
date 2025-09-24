
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <form id="form-programa">
            <input type="hidden" id="f-id" name="id" value="<?php if (isset($data['id'])) print($data['id']); ?>">
            
            <!-- Primera fila -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" for="f-tema">Tema de Capacitación <span class="text-danger">*</span></label>
                        <input type="text" id="f-tema" name="tema" class="form-control" placeholder="Tema o nombre del programa de capacitación" value="<?php if (isset($data['tema'])) print($data['tema']); ?>">
                    </div>
                </div>
            </div>

            <!-- Segunda fila -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-dirigido">Dirigido a <span class="text-danger">*</span></label>
                        <select id="f-dirigido" name="dirigido_a" class="form-control">
                            <option value="">Seleccione a quién va dirigido</option>
                            <?php 
                            if (isset($data_form['trabajadores']) && is_array($data_form['trabajadores'])) {
                                foreach ($data_form['trabajadores'] as $t) {
                                    $full = trim(($t['nombre'] ?? '') . ' ' . ($t['apellidos'] ?? ''));
                                    if ($full === '') continue;
                                    $sel = (isset($data['dirigido_a']) && $data['dirigido_a'] == $full) ? 'selected' : '';
                                    print('<option value="' . htmlspecialchars($full, ENT_QUOTES, 'UTF-8') . '" ' . $sel . '>' . htmlspecialchars($full, ENT_QUOTES, 'UTF-8') . '</option>');
                                }
                            }
                            ?>
                        </select>
                        <small class="help-block">Lista generada desde Trabajadores activos</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-responsable">Responsable <span class="text-danger">*</span></label>
                        <input type="text" id="f-responsable" name="responsable" class="form-control" placeholder="Nombre del responsable o instructor" value="<?php if (isset($data['responsable'])) print($data['responsable']); ?>">
                    </div>
                </div>
            </div>

            <!-- Tercera fila -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-fecha-estimada">Fecha Estimada <span class="text-danger">*</span></label>
                        <input type="date" id="f-fecha-estimada" name="fecha_estimada" class="form-control" value="<?php if (isset($data['fecha_estimada'])) print($data['fecha_estimada']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-modalidad">Modalidad <span class="text-danger">*</span></label>
                        <select id="f-modalidad" name="modalidad" class="form-control">
                            <option value="">Seleccione modalidad</option>
                            <option value="Presencial" <?php if (isset($data['modalidad']) && $data['modalidad'] == 'Presencial') print('selected'); ?>>Presencial</option>
                            <option value="Virtual" <?php if (isset($data['modalidad']) && $data['modalidad'] == 'Virtual') print('selected'); ?>>Virtual</option>
                            <option value="Mixta" <?php if (isset($data['modalidad']) && $data['modalidad'] == 'Mixta') print('selected'); ?>>Mixta (Presencial + Virtual)</option>
                            <option value="En línea" <?php if (isset($data['modalidad']) && $data['modalidad'] == 'En línea') print('selected'); ?>>En línea</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-horas">Horas de Duración <span class="text-danger">*</span></label>
                        <input type="number" id="f-horas" name="horas" class="form-control" placeholder="Número de horas" min="1" max="999" value="<?php if (isset($data['horas'])) print($data['horas']); ?>">
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
            <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                <i class="fa fa-plus"></i>
                Nuevo
            </button>
        </div>
    </div>
</div>
