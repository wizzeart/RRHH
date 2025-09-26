<style>
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

    /* Aumentar tamaño de fuente para los campos del formulario */
    #tab-general .form-control {
        font-size: 1.1em;
        height: auto;
        padding: 8px 12px;
    }

    /* Aumentar tamaño de las etiquetas */
    #tab-general .control-label {
        font-size: 1.05em;
        font-weight: 500;
        margin-bottom: 5px;
    }

    /* Ajustar el espaciado entre campos */
    #tab-general .form-group {
        margin-bottom: 15px;
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
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">Trabajador</a></li>
                <li><a href="#tab-contacto" data-toggle="tab" aria-expanded="false">Contacto</a></li>
                <li><a href="#tab-recursos" data-toggle="tab" aria-expanded="false">Recursos Asignados</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Ficha del usuario</p>" data-html="true" title=""></a>
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
                            <!-- <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="f-id">Código del trabajador</label>
                <input type="text" id="f-id" name="id" class="form-control" placeholder="ID" value="<?php if (isset($data['id'])) print($data['id']); ?>" disabled>
                <small class="help-block">Identificador único</small>
            </div>  
                
        </div> -->
                            <div class="col-md-1">
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
                                            <label class="control-label" for="f-apellidos">Apellidos </label>
                                            <input type="text" id="f-apellidos" name="apellidos" class="form-control" placeholder="Apellidos del trabajador" value="<?php if (isset($data['apellidos'])) print($data['apellidos']); ?>" readonly>
                                            <!-- <small class="help-block">Apellidos del trabajador</small> -->
                                        </div>
                                    </div>
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
                                </div>
                                <div class="row">
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




                        <!-- Tercera fila -->
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
                                        <option value=""></option>
                                        <?php foreach ($data_form['cargos'] as $k => $v) { ?>
                                            <option value="<?php print($v['id']) ?>"><?php print($v['nombre']) ?></option>
                                        <?php } ?>
                                    </select>
                                    <!-- <small class="help-block">Cargo asignado</small> -->
                                </div>
                            </div>
                        </div>

                        <!-- Cuarta fila -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-contratacion">Fecha Contratación </label>
                                    <input type="date" id="f-contratacion" name="fecha_contratacion" class="form-control" value="<?php if (isset($data['fecha_contratacion'])) print($data['fecha_contratacion']); ?>" readonly>
                                    <!-- <small class="help-block">Inicio del Contrato</small> -->
                                </div>
                            </div>
                            <div class="col-md-3">
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
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-estatus">Estatus </label>
                                    <select id="f-estatus" name="estatus" class="form-control" disabled>
                                        <option value=""></option>
                                        <option value="activo" <?php if (isset($data['estatus']) && $data['estatus'] == 'activo') print('selected'); ?>>Activo</option>
                                        <option value="inactivo" <?php if (isset($data['estatus']) && $data['estatus'] == 'inactivo') print('selected'); ?>>Inactivo</option>
                                    </select>
                                    <!-- <small class="help-block">Estado actual</small> -->
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-bolsa">Bolsa de Empleo </label>
                                    <select id="f-bolsa" name="bolsa_empleo_id" class="form-control" disabled>
                                        <option value=""></option>
                                        <?php foreach ($data_form['bolsas'] as $k => $v) { ?>
                                            <option value="<?php print($v['id']) ?>"><?php print($v['nombre']) ?></option>
                                        <?php } ?>
                                    </select>
                                    <!-- <small class="help-block">Bolsa de empleo asociada</small> -->
                                </div>
                            </div>

                        </div>
                        <div class="row">



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
                    </div>
                </div><!-- TAB GENERAL -->
            </div>

            <!-- TAB CONTACTO -->
            <div class="tab-pane fade" id="tab-contacto">
                <div class="panel">
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
                    <div id="todos" class="tab-pane fade in active">
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
            </div><!-- TAB RECURSOS ASIGNADOS -->
        </div>
    </div>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->
</div>

</div>