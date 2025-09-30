<style>
    /* Estilos para el campo de estatus */
    .estatus-activo {
        color: #28a745 !important;
        font-weight: bold;
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    .estatus-inactivo {
        color: #dc3545 !important;
        font-weight: bold;
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    /* Estilos específicos para campos deshabilitados y de solo lectura en la ficha del trabajador */
    #tab-general input[disabled],
    #tab-general input[readonly],
    #tab-general select[disabled],
    #tab-general select[readonly],
    #tab-general textarea[disabled],
    #tab-general textarea[readonly] {
        cursor: default !important;
    }

    #tab-general input[disabled]:hover,
    #tab-general input[readonly]:hover,
    #tab-general select[disabled]:hover,
    #tab-general select[readonly]:hover,
    #tab-general textarea[disabled]:hover,
    #tab-general textarea[readonly]:hover {
        cursor: default !important;
    }

    /* Ocultar flecha en select deshabilitados */
    #tab-general select[disabled] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none !important;
        padding-right: 8px;
    }

    /* Para Firefox */
    @-moz-document url-prefix() {
        #tab-general select[disabled] {
            text-indent: 0.01px;
            text-overflow: '';
        }
    }

</style>

<script>
    var action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true"><i class="fa fa-user"></i> General</a></li>
                <li><a href="#tab-contacto" data-toggle="tab" aria-expanded="false"><i class="fa fa-phone"></i> Contacto</a></li>
                <li><a href="#tab-salario" data-toggle="tab" aria-expanded="false"><i class="fa fa-dollar"></i> Salario</a></li>
                <li><a href="#tab-contratacion" data-toggle="tab" aria-expanded="false"><i class="fa fa-file-text"></i> Contratación</a></li>
                <li><a href="#tab-recursos" data-toggle="tab" aria-expanded="false"><i class="fa fa-cubes"></i> Recursos</a></li>
                <li><a href="#tab-vacaciones" data-toggle="tab" aria-expanded="false"><i class="fa fa-umbrella"></i> Vacaciones</a></li>
                <li><a href="#tab-capacitaciones" data-toggle="tab" aria-expanded="false"><i class="fa fa-graduation-cap"></i> Capacitaciones</a></li>
                <li><a href="#tab-documentos" data-toggle="tab" aria-expanded="false"><i class="fa fa-folder"></i> Documentos</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Ficha del trabajador</p>" data-html="true" title=""></a>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-general">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">General</h3>
                    </div>
                    <div class="panel-body orm-padding"><!-- form-horizontal -->

                        <!-- Primera fila -->
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?php if (isset($data['foto']) && !empty($data['foto'])) { ?>
                                        <div class="mar-top">
                                            <img src="<?php print($data['foto']); ?>" alt="Foto del trabajador" class="img-thumbnail" style="max-width: 150px; height: auto;">
                                        </div>
                                    <?php } ?>
                                    <small class="help-block">Foto del trabajador</small>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-nombre">Nombre </label>
                                            <input type="text" id="f-nombre" name="nombre" class="form-control" placeholder="Nombre del trabajador" value="<?php if (isset($data['nombre'])) print($data['nombre']); ?>" readonly>
                                            <!-- <small class="help-block">Nombre del trabajador</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-apellidos">1er Apellido </label>
                                            <input type="text" id="f-apellidos" name="apellidos" class="form-control" placeholder="Apellidos del trabajador" value="<?php if (isset($data['apellidos'])) print($data['apellidos']); ?>" readonly>
                                            <!-- <small class="help-block">Apellidos del trabajador</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-apellidos-segundos">2do Apellido </label>
                                            <input type="text" id="f-apellidos-segundos" name="apellidos_segundos" class="form-control" placeholder="Apellidos del trabajador" value="<?php if (isset($data['apellidos_segundos'])) print($data['apellidos_segundos']); ?>" readonly>
                                            <!-- <small class="help-block">Apellidos del trabajador</small> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-sexo">Sexo</label>
                                            <select id="f-sexo" name="sexo" class="form-control" disabled>
                                                <option value=""></option>
                                                <option value="M" <?php if (isset($data['sexo']) && $data['sexo'] == 'M') print('selected'); ?>>Masculino</option>
                                                <option value="F" <?php if (isset($data['sexo']) && $data['sexo'] == 'F') print('selected'); ?>>Femenino</option>
                                            </select>
                                            <!-- <small class="help-block">Sexo del trabajador</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-ci">Carnet de Identidad </label>
                                            <input type="text" id="f-ci" name="carnet_identidad" class="form-control" placeholder="Carnet de Identidad del trabajador" value="<?php if (isset($data['carnet_identidad'])) print($data['carnet_identidad']); ?>" readonly>
                                            <!-- <small class="help-block">Documento de identidad</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-edad">Edad </label>
                                            <input type="number" id="f-edad" name="edad" class="form-control" placeholder="Edad actual" value="<?php if (isset($data['edad'])) print($data['edad']); ?>" readonly>
                                            <!-- <small class="help-block">Edad actual</small> -->
                                        </div>
                                    </div>



                                </div>
                            </div>
                        </div>

                        <!-- Segunda fila -->
                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-nivel">Nivel Educacional </label>
                                    <select id="f-nivel" name="nivel_educacional" class="form-control" disabled>
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
                                    <label class="control-label" for="f-cargo">Cargo </label>
                                    <select id="f-cargo" name="cargos_id" class="form-control" disabled>
                                        <?php
                                        $sql = 'SELECT * FROM cargos';
                                        $res = $app->db->fetchAll($sql);
                                        $data_form['cargos'] = $res;

                                        foreach ($data_form['cargos'] as $k => $v) { ?>
                                            <option value="<?php print($v['id']) ?>" <?php if (isset($data['cargos_id']) && $data['cargos_id'] == $v['id']) print('selected'); ?>><?php print($v['nombre']) ?></option>
                                        <?php } ?>
                                    </select>

                                    <!-- <small class="help-block">Cargo asignado</small> -->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-departamento">Departamento </label>
                                    <select id="f-departamento" name="departamento_id" class="form-control" disabled>
                                        <?php
                                        $sql = 'SELECT * FROM departamentos';
                                        $res = $app->db->fetchAll($sql);
                                        $data_form['departamentos'] = $res;

                                        foreach ($data_form['departamentos'] as $k => $v) { ?>
                                            <option value="<?php print($v['id']) ?>" <?php if (isset($data['departamento_id']) && $data['departamento_id'] == $v['id']) print('selected'); ?>><?php print($v['nombre']) ?></option>
                                        <?php } ?>
                                    </select>

                                </div>
                            </div>
                        </div>

                        <!-- 3ra fila -->
                        <div class="row">
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-contratacion">Fecha Contratación </label>
                                    <input type="date" id="f-contratacion" name="fecha_contratacion" class="form-control" value="<?php if (isset($data['fecha_contratacion'])) print($data['fecha_contratacion']); ?>" readonly>
                                </div>
                            </div> -->
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-baja">Fecha Baja</label>
                                    <div class="input-group">
                                        <input type="date" id="f-baja" name="fecha_baja" class="form-control" value="<?php if (isset($data['fecha_baja'])) print($data['fecha_baja']); ?>" disabled>
                                        <div class="input-group-append" style="display:flex; align-items:center; padding-left:8px;">
                                            <div class="checkbox" style="margin:0;">
                                                <label style="margin:0;">
                                                    <input type="checkbox" id="check-fecha-baja" <?php if (isset($data['fecha_baja']) && !empty($data['fecha_baja'])) print('checked'); ?>>
                                                    Tiene baja
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-estatus">Estatus </label>
                                    <?php
                                    $estatusClase = '';
                                    if (isset($data['estatus'])) {
                                        $estatusClase = ($data['estatus'] == 'activo') ? 'estatus-activo' : 'estatus-inactivo';
                                    }
                                    ?>
                                    <select id="f-estatus" name="estatus" class="form-control <?php echo $estatusClase; ?>" disabled>
                                        <option value=""></option>
                                        <option value="activo" class="estatus-activo" <?php if (isset($data['estatus']) && $data['estatus'] == 'activo') print('selected'); ?>>Activo</option>
                                        <option value="inactivo" class="estatus-inactivo" <?php if (isset($data['estatus']) && $data['estatus'] == 'inactivo') print('selected'); ?>>Inactivo</option>
                                    </select>
                                    <!-- <small class="help-block">Estado actual</small> -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="f-licencia">Licencia de Conducción</label>
                                    <?php
                                    // Convertir la cadena de licencias a un array
                                    $licencias = [];
                                    if (isset($data['licencia_conduccion']) && !empty($data['licencia_conduccion']) && strtoupper($data['licencia_conduccion']) !== 'N/A') {
                                        $licencias = array_map('trim', explode(' ', $data['licencia_conduccion']));
                                    }

                                    // Si no hay licencias, forzar a que muestre N/A
                                    $title = empty($licencias) ? 'N/A' : 'Seleccione los tipos de licencia';
                                    ?>
                                    <select id="f-licencia" name="licencia_conduccion[]" class="form-control selectpicker" multiple title="<?php echo $title; ?>" disabled>
                                        <?php if (empty($licencias)): ?>
                                            <option value="N/A" selected>N/A - No posee licencia</option>
                                        <?php endif; ?>
                                        <option value="A1" <?php echo in_array('A1', $licencias) ? 'selected' : ''; ?>>A1 - Ciclomotor</option>
                                        <option value="A" <?php echo in_array('A', $licencias) ? 'selected' : ''; ?>>A - Motocicleta</option>
                                        <option value="B" <?php echo in_array('B', $licencias) ? 'selected' : ''; ?>>B - Automóvil</option>
                                        <option value="C1" <?php echo in_array('C1', $licencias) ? 'selected' : ''; ?>>C1 - Camión ligero</option>
                                        <option value="C" <?php echo in_array('C', $licencias) ? 'selected' : ''; ?>>C - Camión pesado</option>
                                        <option value="D1" <?php echo in_array('D1', $licencias) ? 'selected' : ''; ?>>D1 - Microbús</option>
                                        <option value="D" <?php echo in_array('D', $licencias) ? 'selected' : ''; ?>>D - Omnibus</option>
                                        <option value="E" <?php echo in_array('E', $licencias) ? 'selected' : ''; ?>>E - Articulado</option>
                                        <option value="F" <?php echo in_array('F', $licencias) ? 'selected' : ''; ?>>F - Agroindustrial y de construcción</option>
                                        <option value="FE" <?php echo in_array('FE', $licencias) ? 'selected' : ''; ?>>FE - Tractor con remolque</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-bolsa">Bolsa de Empleo </label>
                                    <select id="f-bolsa" name="bolsa_empleo_id" class="form-control" disabled>
                                        <option value=""></option>
                                        <?php foreach ($data_form['bolsas'] as $k => $v) { ?>
                                            <option value="<?php print($v['id']) ?>"><?php print($v['nombre']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> -->

                        </div>


                    </div>
                </div><!-- TAB GENERAL -->
            </div>

            <!-- TAB CONTACTO -->
            <div class="tab-pane fade" id="tab-contacto">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Contacto</h3>
                    </div>
                    <div class="panel-body orm-padding">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="f-email">Correo Electrónico</label>
                                    <input type="email" id="f-email" name="email" class="form-control" placeholder="correo@ejemplo.com" value="<?php if (isset($data['email'])) print(htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8')); ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="f-telefono">Teléfono Móvil</label>
                                    <input type="text" id="f-telefono" name="telefono" class="form-control" placeholder="Teléfono de contacto" value="<?php if (isset($data['telefono'])) print($data['telefono']); ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="f-telefono-fijo">Teléfono Fijo</label>
                                    <input type="text" id="f-telefono-fijo" name="telefono_fijo" class="form-control" placeholder="+56 X XXXX XXXX" value="<?php if (isset($data['telefono_fijo'])) print(htmlspecialchars($data['telefono_fijo'], ENT_QUOTES, 'UTF-8')); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="f-direccion">Dirección</label>
                                    <textarea id="f-direccion" name="direccion" class="form-control" rows="3" placeholder="Dirección completa" readonly><?php if (isset($data['direccion'])) print(htmlspecialchars($data['direccion'], ENT_QUOTES, 'UTF-8')); ?></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- TAB CONTACTO -->

            <!-- TAB RECURSOS ASIGNADOS -->
            <div class="tab-pane fade" id="tab-recursos">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Recursos Asignados</h3>
                    </div>
                    <div class="panel-body">
                        <table
                            id="table-todos"
                            data-toggle="table"
                            data-url="api-app.php?module=gestion-recursos&method=list-id&trabajador_id=<?php print($data['id']) ?>"
                            data-search="true"
                            data-show-refresh="true"
                            data-show-toggle="false"
                            data-show-columns="false"
                            data-sort-name="fecha_entrega_a_t"
                            data-sort-order="desc"
                            data-page-list="[20, 50, 100]"
                            data-page-size="50"
                            data-pagination="true"
                            data-show-pagination-switch="true">
                            <thead>
                                <tr>
                                    <th data-field="nombre" data-sortable="true">Recurso</th>
                                    <th data-field="estado" data-sortable="true" data-formatter="formatoEstado">Estado</th>
                                    <th data-field="fecha_entrega_a_t" data-sortable="true" data-formatter="formatoFecha">Fecha Entrega</th>
                                    <th data-field="fecha_entrega_a_rh" data-sortable="true" data-formatter="formatoFecha">Fecha Devolución</th>
                                    <!--<th data-field="toolbar" data-align="center" data-formatter="formatoToolbar" data-sortable="false">Opciones</th>-->
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="tab-contratacion">
                <div>
                    <!--agregar cargo y departamento-->
    
                </div>
    
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Listado de contratos</h3>
                    </div>
    
                    <div class="panel-body orm-padding">
    
    
                        <table
                            id="table-panel"
                            data-toggle="table"
                            data-url="api-app.php?module=contratos&method=list-id&trabajador_id=<?php print($data['id']); ?>"
                            data-search="true"
                            data-show-refresh="true"
                            data-show-toggle="false"
                            data-show-columns="false"
                            data-sort-name="id"
                            data-sort-order="desc"
                            data-page-list="[20, 50, 100]"
                            data-page-size="50"
                            data-pagination="true" data-show-pagination-switch="true">
                            <thead>
                                <tr>
                                    <!-- <th data-field="id" data-sortable="true" data-width="80">ID</th> -->
                                    <th data-field="tipo" data-sortable="true" data-width="260">Tipo</th>
                                    <th data-field="fecha_inicio" data-sortable="true" data-width="140">Fecha Inicio</th>
    
                                    <th data-field="firma_digital" data-formatter="firmadoFormatter" data-align="center" data-width="140">Firmado</th>
                                    <th data-field="archivo_contrato" data-formatter="pdfFormatter" data-align="center" data-width="140">Opciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
    
            <div class="tab-pane fade" id="tab-capacitaciones">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Capacitaciones</h3>
                    </div>
                    <div class="panel-body">
                        <table
                            id="table-panel"
                            data-toggle="table"
                            data-url="api-app.php?module=programas-capacitacion&method=list-id&trabajador_id=<?php print($data['id']); ?>"
                            data-search="true"
                            data-show-refresh="true"
                            data-show-toggle="false"
                            data-show-columns="false"
                            data-sort-name="id"
                            data-sort-order="desc"
                            data-page-list="[20, 50, 100]"
                            data-page-size="50"
                            data-pagination="true" data-show-pagination-switch="true">
                            <thead>
                                <tr>
                                    <th data-field="tema" data-sortable="true">Tema</th>
                                    <th data-field="responsable" data-sortable="true">Responsable</th>
                                    <th data-field="fecha_estimada" data-sortable="true">Fecha Estimada</th>
                                    <th data-field="fecha_finalizacion" data-sortable="true">Fecha Finalización</th>
                                    <th data-field="modalidad" data-sortable="true">Modalidad</th>
                                    <th data-field="horas" data-sortable="true">Horas</th>
                                    <th data-field="resultados" data-sortable="true">Resultados</th>
                                    <!-- <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th> -->
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
    
            </div>
            <div class="tab-pane fade" id="tab-salario">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Salario</h3>
                    </div>
                    <div class="panel-body">

                    </div>
                </div>
            
            </div>

            <div class="panel-footer text-center">
                <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
                <!-- <button id="btn-save" class="btn btn-info icon-lg" type="button">
                                <i class="fa fa-check"></i>
                                Guardar
                            </button> -->
                <button id="btn-back" class="btn btn-default icon-lg" type="button">
                    <i class="fa fa-undo"></i>
                    Volver
                </button>
                <!-- <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                                <i class="fa fa-plus"></i>
                                Nuevo
                            </button> -->

                <!-- Sección de Foto del Trabajador -->
                <hr>

            </div>
        </div><!-- TAB RECURSOS ASIGNADOS -->
        <!-- TAB CONTRATACIÓN -->

    </div>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->
</div>