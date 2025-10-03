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

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <div class="panel-body">
        <div class="panel">
            <div class="panel-body orm-padding"><!-- form-horizontal -->

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
                            <input type="text" id="f-ci" name="carnet_identidad" class="form-control"
                                placeholder="Carnet de Identidad del trabajador"
                                value="<?php if (isset($data['carnet_identidad']))
                                    print ($data['carnet_identidad']); ?>">
                            <!-- <small class="help-block">Documento de identidad</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-edad">Edad <span class="text-danger">*</span></label>
                            <input type="number" id="f-edad" name="edad" class="form-control" placeholder="Edad actual"
                                value="<?php if (isset($data['edad']))
                                    print ($data['edad']); ?>">
                            <!-- <small class="help-block">Edad actual</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-direccion">Dirección <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="f-direccion" name="direccion" class="form-control"
                                placeholder="Dirección del trabajador"
                                value="<?php if (isset($data['direccion']))
                                    print ($data['direccion']); ?>">
                            <!-- <small class="help-block">Dirección del trabajador</small> -->
                        </div>
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
                            <label class="control-label" for="f-bolsa">Bolsa de Empleo <span
                                    class="text-danger">*</span></label>
                            <select id="f-bolsa" name="bolsa_empleo_id" class="form-control">
                                <option value="">Seleccione bolsa de empleo</option>
                                <?php foreach ($data_form['bolsas'] as $k => $v) { ?>
                                    <option value="<?php print ($v['id']) ?>"><?php print ($v['nombre']) ?></option>
                                <?php } ?>
                            </select>
                            <!-- <small class="help-block">Bolsa de empleo asociada</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-cargo">Cargo <span class="text-danger">*</span></label>
                            <select id="f-cargo" name="cargos_id" class="form-control">
                                <option value="">Seleccione cargo</option>
                                <?php foreach ($data_form['cargos'] as $k => $v) { ?>
                                    <option value="<?php print ($v['id']) ?>"><?php print ($v['nombre']) ?></option>
                                <?php } ?>
                            </select>
                            <!-- <small class="help-block">Cargo asignado</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-telefono">Tarjeta de Salario</label>
                            <input type="text" id="f-tarjeta_salario" name="tarjeta_salario" class="form-control"
                                placeholder="Numero de Tarjeta de Salario"
                                value="<?php if (isset($data['tarjeta_salario']))
                                    print ($data['tarjeta_salario']); ?>">
                            <!-- <small class="help-block">Teléfono de contacto</small> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-cuenta_estandar">Cuenta Estándar</label>
                            <input type="number" id="f-cuenta_estandar" name="cuenta_estandar" class="form-control"
                                placeholder="Numero de Cuenta Estándar"
                                value="<?php if (isset($data['cuenta_estandar']))
                                    print ($data['cuenta_estandar']); ?>">
                            <!-- <small class="help-block">Teléfono de contacto</small> -->
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
                    <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                        <i class="fa fa-plus"></i>
                        Nuevo
                    </button>

                    <!-- Sección de Foto del Trabajador -->
                    <hr>

                </div>
            </div>
        </div>
        <!-- =================================================== -->
        <!-- END BASIC FORM ELEMENTS -->
    </div>

</div>
<script>
    // Generar email automáticamente al cambiar los campos de nombre y apellidos
    document.getElementById('f-nombre').addEventListener('input', generarEmail);
    document.getElementById('f-apellidos').addEventListener('input', generarEmail);
    document.getElementById('f-apellidos-segundos').addEventListener('input', generarEmail);

    function generarEmail() {
        const nombre = document.getElementById('f-nombre').value.trim().toLowerCase().replace(/\s+/g, '');
        const apellidos = document.getElementById('f-apellidos').value.trim().toLowerCase().replace(/\s+/g, '');
        const apellidosSegundos = document.getElementById('f-apellidos-segundos').value.trim().toLowerCase().replace(/\s+/g, '');

        if (nombre && apellidos) {
            const email = `${nombre}${apellidos.substring(0, 3)}@allnovu.net`;
            document.getElementById('f-email').value = email;
        }
    }
</script>