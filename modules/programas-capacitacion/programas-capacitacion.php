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
        <form id="form-programa">
            <input type="hidden" id="f-id" name="id" value="<?php if (isset($data['id'])) print($data['id']); ?>">
            
            <!-- Primera fila -->
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="control-label" for="f-tema">Tema de Capacitación <span class="text-danger">*</span></label>
                        <input type="text" id="f-tema" name="tema" class="form-control" placeholder="Tema o nombre del programa de capacitación" value="<?php if (isset($data['tema'])) print($data['tema']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="f-horas">Horas de Duración <span class="text-danger">*</span></label>
                        <input type="number" id="f-horas" name="horas" class="form-control" placeholder="Número de horas" min="1" max="999" value="<?php if (isset($data['horas'])) print($data['horas']); ?>">
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
                            <option value="Todos los trabajadores" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Todos los trabajadores') print('selected'); ?>>Todos los trabajadores</option>
                            <option value="Directivos" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Directivos') print('selected'); ?>>Directivos</option>
                            <option value="Supervisores" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Supervisores') print('selected'); ?>>Supervisores</option>
                            <option value="Personal administrativo" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Personal administrativo') print('selected'); ?>>Personal administrativo</option>
                            <option value="Personal operativo" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Personal operativo') print('selected'); ?>>Personal operativo</option>
                            <option value="Nuevo personal" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Nuevo personal') print('selected'); ?>>Nuevo personal</option>
                            <option value="Área específica" <?php if (isset($data['dirigido_a']) && $data['dirigido_a'] == 'Área específica') print('selected'); ?>>Área específica</option>
                        </select>
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
                        <label class="control-label" for="f-fecha-finalizacion">Fecha de Finalización</label>
                        <input type="date" id="f-fecha-finalizacion" name="fecha_finalizacion" class="form-control" value="<?php if (isset($data['fecha_finalizacion'])) print($data['fecha_finalizacion']); ?>">
                        <small class="help-block">Dejar vacío si el programa está activo</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
