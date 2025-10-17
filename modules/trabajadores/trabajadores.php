<script>
    var action = '<?php print ($action) ?>';
    var rol = '<?php print ($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#"
                data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'></p>" data-html="true"
                title=""></a>
        </div>
        <h3 class="panel-title"><?php print ($page['subtitle']); ?></h3>
    </div>

    <!-- TABS NAVIGATION -->
    <!--===================================================-->
    <div class="panel-body">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#datos-personales" aria-controls="datos-personales" role="tab" data-toggle="tab">
                    <i class="fa fa-user"></i> Datos Personales
                </a>
            </li>
            <li role="presentation">
                <a href="#cuentas-bancarias" aria-controls="cuentas-bancarias" role="tab" data-toggle="tab">
                    <i class="fa fa-credit-card"></i> Cuentas Bancarias
                </a>
            </li>
         
        </ul>

        <!-- Tab panes -->
        <div class="tab-content" style="margin-top: 20px;">
            <!-- TAB: DATOS PERSONALES -->
            <div role="tabpanel" class="tab-pane active" id="datos-personales">
                <div class="panel">
                    <div class="panel-body orm-padding">
                        <!-- Primera fila -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-id">Código del trabajador</label>
                                    <input type="text" id="f-id" name="id" class="form-control" placeholder="ID" value="<?php if (isset($data['id']))
                                        print ($data['id']); ?>" disabled>
                                    <small class="help-block">Identificador único</small>
                                </div>
                            </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="f-nombre" name="nombre" class="form-control" placeholder="Nombre del trabajador" value="<?php if (isset($data['nombre'])) print ($data['nombre']); ?>">
                            <!-- <small class="help-block">Nombre del trabajador</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-apellidos">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" id="f-apellidos" name="apellidos" class="form-control" placeholder="Primer apellido" value="<?php if (isset($data['apellidos'])) print ($data['apellidos']); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-apellidos-segundos">Segundo Apellido <span class="text-danger">*</span></label>
                            <input type="text" id="f-apellidos-segundos" name="apellidos_segundos" class="form-control" placeholder="Segundo apellido" value="<?php if (isset($data['apellidos_segundos'])) print ($data['apellidos_segundos']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Segunda fila -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-sexo">Sexo</label>
                            <select id="f-sexo" name="sexo" class="form-control">
                                <option value="">Seleccione sexo</option>
                                <option value="M" <?php if (isset($data['sexo']) && $data['sexo'] == 'M') print ('selected'); ?>>Masculino</option>
                                <option value="F" <?php if (isset($data['sexo']) && $data['sexo'] == 'F') print ('selected'); ?>>Femenino</option>
                            </select>
                            <!-- <small class="help-block">Sexo del trabajador</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">


                        <div class="form-group">
                            <label class="control-label" for="f-ci">Carnet de Identidad <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="f-ci" name="carnet_identidad" class="form-control" inputmode="numeric" pattern="[0-9]*" maxlength="11" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)"
                                placeholder="Carnet de Identidad del trabajador"
                                maxlength="11"
                                value="<?php if (isset($data['carnet_identidad']))
                                    print ($data['carnet_identidad']); ?>">
                            <!-- <small class="help-block">Documento de identidad</small> -->
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-edad">Edad <span class="text-danger">*</span></label>
                            <input type="number" id="f-edad" name="edad" class="form-control" placeholder="Edad actual"
                                value="<?php if (isset($data['edad']))
                                    print ($data['edad']); ?>">
                        </div>
                    </div> -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-direccion">Dirección <span
                                    class="text-danger">*</span></label>
                            <textarea id="f-direccion" name="direccion" class="form-control" rows="2"
                                placeholder="Dirección del trabajador"
                                ><?php if (isset($data['direccion']))
                                    echo ($data['direccion']);?></textarea>
                            <!-- <small class="help-block">Dirección del trabajador</small> -->
                        </div>
                    </div>

                </div>

                <!-- Nueva fila - Provincia y Municipio -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-provincia">Provincia <span class="text-danger">*</span></label>
                            <select id="f-provincia" name="provincia_id" class="form-control">
                                <option value="">Seleccionar Provincia</option>
                                <?php if (isset($data_form['provincias']) && is_array($data_form['provincias'])): ?>
                                    <?php foreach ($data_form['provincias'] as $provincia): ?>
                                        <option value="<?php echo $provincia['id']; ?>" 
                                                <?php echo (isset($data['provincia_id']) && $data['provincia_id'] == $provincia['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($provincia['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-municipio">Municipio <span class="text-danger">*</span></label>
                            <select id="f-municipio" name="municipio_id" class="form-control">
                                <option value="">Seleccionar Municipio</option>
                                <?php if (isset($data_form['municipios']) && is_array($data_form['municipios'])): ?>
                                    <?php foreach ($data_form['municipios'] as $municipio): ?>
                                        <option value="<?php echo $municipio['id']; ?>" 
                                                data-provincia="<?php echo $municipio['provincia_id']; ?>"
                                                <?php echo (isset($data['municipio_id']) && $data['municipio_id'] == $municipio['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($municipio['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Espacio vacío para mantener el layout -->
                    </div>
                </div>

                <!-- Tercera fila -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="f-email" name="email" class="form-control"
                                placeholder="Correo electrónico" value="<?php if (isset($data['email']))
                                    print ($data['email']); ?>">
                            <!-- <small class="help-block">Correo electrónico</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-telefono">Teléfono <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="f-telefono" name="telefono" class="form-control"
                                placeholder="Teléfono de contacto"
                                value="<?php if (isset($data['telefono']))
                                    print ($data['telefono']); ?>">
                            <!-- <small class="help-block">Teléfono de contacto</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-nivel">Nivel Educacional <span
                                    class="text-danger">*</span></label>
                            <select id="f-nivel" name="nivel_educacional" class="form-control">
                                <option value="">Seleccione nivel educacional</option>
                                <option value="Universitario" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Universitario')
                                    print ('selected'); ?>>
                                    Universitario</option>
                                <option value="Técnico Superior" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Técnico Superior')
                                    print ('selected'); ?>>
                                    Técnico Superior</option>
                                <option value="Preuniversitario" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Preuniversitario')
                                    print ('selected'); ?>>
                                    Preuniversitario</option>

                                <option value="Técnico Medio" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Técnico Medio')
                                    print ('selected'); ?>>Técnico
                                    Medio</option>
                                <option value="Secundaria Básica" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Secundaria Básica')
                                    print ('selected'); ?>>Secundaria Básica
                                </option>
                            </select>
                            <!-- <small class="help-block">Nivel académico alcanzado</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-departamento">Departamento <span
                                    class="text-danger">*</span></label>
                            <select id="f-departamento" name="departamento_id" class="form-control">
                                <option value="">Seleccione departamento</option>
                                <?php if (isset($data_form['departamentos']) && is_array($data_form['departamentos'])) { ?>
                                    <?php foreach ($data_form['departamentos'] as $k => $v) { ?>
                                        <option value="<?php print ($v['id']) ?>" <?php if (isset($data['departamento_id']) && $data['departamento_id'] == $v['id'])
                                               print ('selected'); ?>>
                                            <?php print ($v['nombre']) ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                            <!-- <small class="help-block">Departamento donde trabajará</small> -->
                        </div>
                    </div>

                </div>

                <!-- Cuarta fila -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-contratacion">Fecha Contratación <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="f-contratacion" name="fecha_contratacion" class="form-control" value="<?php if (isset($data['fecha_contratacion']))
                                print ($data['fecha_contratacion']); ?>">
                            <!-- <small class="help-block">Inicio del Contrato</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-estatus">Estatus <span
                                    class="text-danger">*</span></label>
                            <select id="f-estatus" name="estatus" class="form-control">
                                <option value="">Seleccione estatus</option>
                                <option value="activo" <?php if (isset($data['estatus']) && $data['estatus'] == 'activo')
                                    print ('selected'); ?>>Activo</option>
                                <option value="inactivo" <?php if (isset($data['estatus']) && $data['estatus'] == 'inactivo')
                                    print ('selected'); ?>>Inactivo</option>
                            </select>
                            <!-- <small class="help-block">Estado actual</small> -->
                        </div>
                    </div>
                   
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-cargo">Cargo <span class="text-danger">*</span></label>
                            <select id="f-cargo" name="cargos_id" class="form-control">
                                <option value="">Seleccione cargo</option>
                                <?php foreach ($data_form['cargos'] as $k => $v) { ?>
                              <option value="<?php print($v['id']) ?>" <?php if (isset($data['cargos_id']) && $data['cargos_id'] == $v['id']) print('selected'); ?>><?php print($v['nombre']) ?></option>
                                        <?php } ?>
                            </select>
                            <!-- <small class="help-block">Cargo asignado</small> -->
                        </div>
                    </div>
                </div>

                <!-- Licencia de Conducción (select simple) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="f-licencia">Licencia de Conducción</label>
                            <?php
                            $licencia_val = 'N/A';
                            if (isset($data['licencia_conduccion']) && !empty($data['licencia_conduccion'])) {
                                // Si viene con múltiples valores (separados por espacio), tomar el primero
                                $parts = array_map('trim', explode(' ', $data['licencia_conduccion']));
                                $licencia_val = !empty($parts[0]) ? $parts[0] : 'N/A';
                            }
                            ?>
                            <select id="f-licencia" name="licencia_conduccion" class="form-control">
                                <option value="N/A" <?php echo ($licencia_val === 'N/A') ? 'selected' : ''; ?>>N/A - No posee licencia</option>
                                <option value="A1" <?php echo ($licencia_val === 'A1') ? 'selected' : ''; ?>>A1 - Ciclomotor</option>
                                <option value="A" <?php echo ($licencia_val === 'A') ? 'selected' : ''; ?>>A - Motocicleta</option>
                                <option value="B" <?php echo ($licencia_val === 'B') ? 'selected' : ''; ?>>B - Automóvil</option>
                                <option value="C1" <?php echo ($licencia_val === 'C1') ? 'selected' : ''; ?>>C1 - Camión ligero</option>
                                <option value="C" <?php echo ($licencia_val === 'C') ? 'selected' : ''; ?>>C - Camión pesado</option>
                                <option value="D1" <?php echo ($licencia_val === 'D1') ? 'selected' : ''; ?>>D1 - Microbús</option>
                                <option value="D" <?php echo ($licencia_val === 'D') ? 'selected' : ''; ?>>D - Omnibus</option>
                                <option value="E" <?php echo ($licencia_val === 'E') ? 'selected' : ''; ?>>E - Articulado</option>
                                <option value="F" <?php echo ($licencia_val === 'F') ? 'selected' : ''; ?>>F - Agroindustrial y de construcción</option>
                                <option value="FE" <?php echo ($licencia_val === 'FE') ? 'selected' : ''; ?>>FE - Tractor con remolque</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección de foto -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="f-foto">Foto del Trabajador</label>
                            <input type="file" id="f-foto" name="foto" class="form-control" accept="image/*">
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
                    </div>
                </div>
            </div>
            <!-- END TAB: DATOS PERSONALES -->

            <!-- TAB: CUENTAS BANCARIAS -->
            <div role="tabpanel" class="tab-pane" id="cuentas-bancarias">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4><i class="fa fa-credit-card"></i> Información Bancaria del Trabajador</h4>
                                <hr>
                            </div>
                        </div>
                        
                        <!-- Campos bancarios -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="f-tarjeta_salario">Tarjeta de Salario</label>
                                    <input type="text" id="f-tarjeta_salario" name="tarjeta_salario" class="form-control"
                                        placeholder="Número de Tarjeta de Salario"
                                        value="<?php if (isset($data['tarjeta_salario']))
                                            print ($data['tarjeta_salario']); ?>">
                                    <small class="help-block">Número de la tarjeta de salario del trabajador</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="f-cuenta_estandar">Cuenta Estándar</label>
                                    <input type="text" id="f-cuenta_estandar" name="cuenta_estandar" class="form-control"
                                        placeholder="Número de Cuenta Estándar"
                                        value="<?php if (isset($data['cuenta_estandar']))
                                            print ($data['cuenta_estandar']); ?>">
                                    <small class="help-block">Número de la cuenta estándar del trabajador</small>
                                </div>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Información:</strong> Los datos bancarios son opcionales pero recomendados para el procesamiento de nóminas y pagos.
                                </div>
                            </div>
                        </div>

                        <!-- Mostrar datos existentes si está editando -->
                        <?php if (isset($data['id']) && !empty($data['id'])): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h5><i class="fa fa-database"></i> Datos Actuales en Base de Datos</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Valor Actual</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Tarjeta de Salario</strong></td>
                                                <td>
                                                    <?php 
                                                    echo !empty($data['tarjeta_salario']) 
                                                        ? htmlspecialchars($data['tarjeta_salario']) 
                                                        : '<em class="text-muted">No registrada</em>'; 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($data['tarjeta_salario'])): ?>
                                                        <span class="label label-success">Registrada</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cuenta Estándar</strong></td>
                                                <td>
                                                    <?php 
                                                    echo !empty($data['cuenta_estandar']) 
                                                        ? htmlspecialchars($data['cuenta_estandar']) 
                                                        : '<em class="text-muted">No registrada</em>'; 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($data['cuenta_estandar'])): ?>
                                                        <span class="label label-success">Registrada</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- END TAB: CUENTAS BANCARIAS -->
        </div>
        <!-- END TAB CONTENT -->

        <!-- Botones de acción (fuera de los tabs) -->
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

</div>
<script>
    // Generar email automáticamente al cambiar los campos de nombre y apellidos
    document.getElementById('f-nombre').addEventListener('input', generarEmail);
    document.getElementById('f-apellidos').addEventListener('input', generarEmail);
    document.getElementById('f-apellidos-segundos').addEventListener('input', generarEmail);

    function generarEmail() {
        function normalizarLocal(s) {
            s = (s || '').toLowerCase();
            var mapa = {'á':'a','à':'a','ä':'a','â':'a','ã':'a','é':'e','è':'e','ë':'e','ê':'e','í':'i','ì':'i','ï':'i','î':'i','ó':'o','ò':'o','ö':'o','ô':'o','õ':'o','ú':'u','ù':'u','ü':'u','û':'u','ñ':'n','ç':'c'};
            s = s.replace(/[áàäâãéèëêíìïîóòöôõúùüûñç]/g, function(ch){ return mapa[ch] || ch; });
            s = s.replace(/\s+/g, '');
            s = s.replace(/[^a-z0-9._-]/g, '');
            return s;
        }
        function normalizarCorreoCompleto(c) {
            c = (c || '').toLowerCase();
            try { c = c.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch(e) {}
            c = c.replace(/ñ/g, 'n');
            c = c.replace(/ç/g, 'c');
            c = c.replace(/[^a-z0-9@._-]/g, '');
            return c;
        }
        const nombreRaw = document.getElementById('f-nombre').value.trim();
        const apellidosRaw = document.getElementById('f-apellidos').value.trim();
        const apellidosSegundosRaw = document.getElementById('f-apellidos-segundos').value.trim();
        const nombre = normalizarLocal(nombreRaw);
        const apellidos = normalizarLocal(apellidosRaw);
        const apellidosSegundos = normalizarLocal(apellidosSegundosRaw);

        if (nombre && apellidos) {
            var email = nombre + apellidos.substring(0, 3) + '@allnovu.net';
            email = normalizarCorreoCompleto(email);
            document.getElementById('f-email').value = email;
        }
    }
</script>