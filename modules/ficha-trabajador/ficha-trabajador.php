<?php
// Enforce RBAC access control
require_once(__DIR__ . '/../rbac.php');
enforce_access($app->rol, 'ficha-trabajador', $app);

// Function to check if user can access specific tab content
// Function to check if user can access specific tab content
function can_access_tab($tab_name, $app)
{
    $user_role = $app->rol;

    // Ya no se ocultan tabs por empresa: TCP (empresa ID 3) tenía Contratación y Vacaciones
    // ocultos y ambas restricciones se retiraron a petición del área. El acceso queda
    // determinado únicamente por el rol del usuario.

    // INVENTARIO role (id=3) can only access specific tabs
    if ($user_role == 3) {
        $allowed_tabs = array('general', 'contacto', 'recursos');
        return in_array($tab_name, $allowed_tabs);
    }
    
    // JEFE DE AREA role (id=4) can only access specific tabs
    if ($user_role == 4) {
        $allowed_tabs = array('general', 'contacto', 'recursos', 'vacaciones', 'asistencias', 'documentos');
        return in_array($tab_name, $allowed_tabs);
    }
    // Other roles have full access (or implement other restrictions as needed)
    return true;
}
?>
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

    /* ========================================
       ESTILOS RESPONSIVE PARA MOBILE
    ======================================== */
    
    /* Base para tablets y pantallas medianas */
    @media (max-width: 992px) {
        body {
            font-size: 14px;
        }

        .panel {
            margin-bottom: 15px;
        }

        .panel-heading h3 {
            font-size: 16px;
        }
    }

    /* Estilos para tabs en pantallas pequeñas (768px) */
    @media (max-width: 768px) {
        body {
            font-size: 13px;
        }

        .panel-heading .nav-tabs {
            flex-wrap: wrap;
            gap: 2px;
            padding-left: 0;
        }

        .panel-heading .nav-tabs li {
            flex: 0 1 calc(50% - 1px);
            margin-bottom: 2px;
        }

        .panel-heading .nav-tabs li a {
            padding: 10px 8px;
            font-size: 11px;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            line-height: 1.2;
        }

        .panel-heading .nav-tabs li a i {
            font-size: 16px;
        }
    }

    /* Estilos para dispositivos móviles pequeños (576px) - SOLO ICONOS */
    @media (max-width: 576px) {
        html {
            font-size: 13px;
        }

        body {
            font-size: 13px;
            line-height: 1.4;
        }

        .panel {
            margin-bottom: 10px;
        }

        /* Tabs - Solo iconos */
        .panel-heading .nav-tabs {
            flex-wrap: wrap;
            gap: 1px;
            padding: 0;
        }

        .panel-heading .nav-tabs li {
            flex: 0 1 calc(25% - 1px);
            margin-bottom: 1px;
        }

        .panel-heading .nav-tabs li a {
            padding: 10px 0;
            font-size: 0;
            justify-content: center;
            align-items: center;
            height: 45px;
            display: flex;
        }

        .panel-heading .nav-tabs li a i {
            font-size: 18px;
            margin: 0;
        }

        /* Panel heading */
        .panel-heading {
            padding: 10px;
        }

        .panel-heading h3 {
            font-size: 15px;
            margin: 5px 0;
        }

        .panel-heading .fa-question-circle {
            font-size: 16px;
        }

    /* Asegurar que el contenedor de tabs sea responsive */
    .panel-control {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .panel-control .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        min-height: auto;
        border-bottom: 1px solid #ddd;
    }

    .panel-control .nav-tabs li {
        margin-bottom: -1px;
    }

    /* Mejor visualización de tabs activos en mobile */
    .panel-control .nav-tabs li.active a {
        border-radius: 4px 4px 0 0;
        font-weight: 500;
    }

    }

    /* Optimizar contenido de tabs para mobile y tablet */
    @media (max-width: 768px) {
        .tab-content {
            padding: 12px;
        }

        .panel-body {
            padding: 12px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
            color: #333;
        }

        .form-control {
            font-size: 13px;
            padding: 8px 10px;
            height: auto;
            line-height: 1.4;
        }

        .form-control:focus {
            font-size: 13px;
        }

        .btn {
            padding: 8px 12px;
            font-size: 13px;
            line-height: 1.2;
        }

        /* Hacer que los campos de formulario ocupen todo el ancho */
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 100%;
        }

        /* Mejorar botones en mobile */
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .btn-group .btn {
            flex: 1;
            min-width: 70px;
        }

        /* Mejorar visualización de tablas en mobile */
        table {
            font-size: 12px;
        }

        table td,
        table th {
            padding: 6px 8px;
        }
    }

    /* Estilos adicionales para dispositivos muy pequeños (576px) */
    @media (max-width: 576px) {
        .tab-content {
            padding: 10px;
        }

        .panel-body {
            padding: 10px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .form-control {
            font-size: 13px;
            padding: 8px 8px;
            height: auto;
            line-height: 1.4;
            border-radius: 3px;
        }

        .btn {
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.2;
            border-radius: 3px;
        }

        .btn-sm {
            padding: 6px 8px;
            font-size: 11px;
        }

        /* Hacer que los campos de formulario ocupen todo el ancho */
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Mejorar botones en mobile */
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .btn-group .btn {
            flex: 1 1 45%;
            min-width: 60px;
        }

        /* Mejorar visualización de tablas en mobile */
        table {
            font-size: 11px;
            width: 100%;
            overflow-x: auto;
        }

        table td,
        table th {
            padding: 5px 6px;
        }

        /* Títulos y encabezados */
        h1, h2, h3, h4, h5, h6 {
            line-height: 1.2;
            margin: 10px 0;
        }

        h3 {
            font-size: 15px;
        }

        /* Mejorar espaciado en filas de formulario */
        .row {
            margin-left: -5px;
            margin-right: -5px;
        }

        .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5,
        .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10,
        .col-sm-11, .col-sm-12 {
            padding-left: 5px;
            padding-right: 5px;
        }

        /* Alert boxes */
        .alert {
            padding: 10px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        /* Badges */
        .badge {
            font-size: 11px;
            padding: 4px 6px;
        }
    }

    /* Mejorar visibilidad de iconos en tabs */
    .nav-tabs li a i {
        margin-right: 4px;
    }

    /* Evitar que el panel se desborde */
    .panel {
        overflow-x: hidden;
    }
</style>

<script>
    var action = '<?php print ($action) ?>';
    var rol = '<?php print ($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true"><i
                            class="fa fa-user"></i> General</a></li>
                <?php if (can_access_tab('contacto', $app)): ?>
                    <li><a href="#tab-contacto" data-toggle="tab" aria-expanded="false"><i class="fa fa-phone"></i>
                            Contacto</a></li>
                <?php endif; ?>
                <?php if (can_access_tab('salario', $app)): ?>
                    <li><a href="#tab-salario" data-toggle="tab" aria-expanded="false"><i class="fa fa-dollar"></i>
                            Salario</a></li>
                <?php endif; ?>
                <?php if (can_access_tab('contratacion', $app)): ?>
                    <li><a href="#tab-contratacion" data-toggle="tab" aria-expanded="false"><i class="fa fa-file-text"></i>
                            Contratación</a></li>
                <?php endif; ?>
                <li><a href="#tab-recursos" data-toggle="tab" aria-expanded="false"><i class="fa fa-cubes"></i>
                        Recursos</a></li>
                <?php if (can_access_tab('vacaciones', $app)): ?>
                    <li><a href="#tab-vacaciones" data-toggle="tab" aria-expanded="false"><i class="fa fa-umbrella"></i>
                            Vacaciones</a></li>
                <?php endif; ?>
                <?php if (can_access_tab('asistencias', $app)): ?>
                    <li><a href="#tab-asistencias" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i>
                            Asistencia</a></li>
                <?php endif; ?>
                <!-- <li><a href="#tab-capacitaciones" data-toggle="tab" aria-expanded="false"><i class="fa fa-graduation-cap"></i> Capacitaciones</a></li> -->
                <?php if (can_access_tab('documentos', $app)): ?>
                    <li><a href="#tab-documentos" data-toggle="tab" aria-expanded="false"><i class="fa fa-folder"></i>
                            Documentos</a></li>
                <?php endif; ?>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#"
                data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Ficha del trabajador</p>"
                data-html="true" title=""></a>
        </div>
        <h3 class="panel-title"><?php print ($page['subtitle']); ?></h3>
    </div>

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-general">
                <div class="panel">
                    <div class="panel-heading">
                        <style>
                            .panel-title span {
                                color: #0053b3 !important;
                            }
                        </style>

                        <h3 class="panel-title">General (<span style="color:#0078d7; ">
                                <?php print ($data['xusuario'] . ':'); ?> <?php print ($data['email']); ?> </span>)
                            <?php if ($app->rol != 2): ?>
                                <!-- <button id="btn-contrato-trabajador" class="btn btn-primary btn-icon" alt="Generar Contrato" title="Generar Contrato">
                                <span class="icon-lg fa fa-file-text"></span> Generar Contrato
                            </button> -->
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="panel-body orm-padding"><!-- form-horizontal -->

                        <!-- Primera fila -->
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?php if (isset($data['id'])) {
                                        // Prefer serving the image via the API which handles BLOBs
                                        $imgSrc = 'api-app.php?module=imagenes-trabajadores&method=get-image&id=' . intval($data['id']);
                                        // If you need to pass token add &token=... but the API validates KEYWEB header by default
                                        ?>
                                        <div class="mar-top">
                                            <img id="foto-preview" src="<?php print ($imgSrc); ?>" alt="Foto del trabajador"
                                                class="img-thumbnail" style="max-width: 150px; height: auto;" onerror="
                                                    console.error('Error cargando imagen:', this.src); 
                                                    fetch(this.src)
                                                        .then(resp => {
                                                            if (!resp.ok) throw new Error('HTTP ' + resp.status);
                                                            return resp.text();
                                                        })
                                                        .then(text => console.log('Respuesta del servidor:', text))
                                                        .catch(err => console.error('Error en fetch:', err));
                                                    this.src='./images/default-user.png';">
                                        </div>
                                        <script>
                                            document.getElementById('foto-preview').addEventListener('load', function () {
                                                console.log('Imagen cargada exitosamente:', this.src);
                                            });
                                        </script>
                                    <?php } ?>
                                    <small class="help-block">Foto del trabajador</small>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-3" hidden>
                                        <div class="form-group">
                                            <label class="control-label" for="f-id">Código del trabajador</label>
                                            <input type="text" id="f-id" name="id" class="form-control" placeholder="ID"
                                                value="<?php if (isset($data['id']))
                                                    print ($data['id']); ?>" disabled>
                                            <small class="help-block">Identificador único</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-nombre">Nombre </label>
                                            <input type="text" id="f-nombre" name="nombre" class="form-control"
                                                placeholder="Nombre del trabajador" value="<?php if (isset($data['nombre']))
                                                    print ($data['nombre']); ?>" readonly>
                                            <!-- <small class="help-block">Nombre del trabajador</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-apellidos">1er Apellido </label>
                                            <input type="text" id="f-apellidos" name="apellidos" class="form-control"
                                                placeholder="Apellidos del trabajador" value="<?php if (isset($data['apellidos']))
                                                    print ($data['apellidos']); ?>" readonly>
                                            <!-- <small class="help-block">Apellidos del trabajador</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-apellidos-segundos">2do Apellido
                                            </label>
                                            <input type="text" id="f-apellidos-segundos" name="apellidos_segundos"
                                                class="form-control" placeholder="Apellidos del trabajador" value="<?php if (isset($data['apellidos_segundos']))
                                                    print ($data['apellidos_segundos']); ?>" readonly>
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
                                                <option value="M" <?php if (isset($data['sexo']) && $data['sexo'] == 'M')
                                                    print ('selected'); ?>>Masculino</option>
                                                <option value="F" <?php if (isset($data['sexo']) && $data['sexo'] == 'F')
                                                    print ('selected'); ?>>Femenino</option>
                                            </select>
                                            <!-- <small class="help-block">Sexo del trabajador</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-ci">Carnet de Identidad </label>
                                            <input type="text" id="f-ci" name="carnet_identidad" class="form-control"
                                                placeholder="Carnet de Identidad del trabajador" value="<?php if (isset($data['carnet_identidad']))
                                                    print ($data['carnet_identidad']); ?>" readonly>
                                            <!-- <small class="help-block">Documento de identidad</small> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label" for="f-edad">Edad </label>
                                            <input type="number" id="f-edad" name="edad" class="form-control"
                                                placeholder="Edad actual" value="<?php
                                                if (isset($data['fecha_nacimiento'])) {
                                                    $fecha_nacimiento = $data['fecha_nacimiento'];
                                                    $fecha_actual = date('Y-m-d');
                                                    $diferencia = date_diff(date_create($fecha_nacimiento), date_create($fecha_actual));
                                                    $edad = $diferencia->y;
                                                    print ($edad);
                                                }
                                                ?>" readonly>
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
                                        <option value="Universitario" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Universitario')
                                            print ('selected'); ?>>
                                            Universitario</option>
                                        <option value="Preuniversitario" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Preuniversitario')
                                            print ('selected'); ?>>
                                            Preuniversitario</option>
                                        <option value="Técnico Superior" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Técnico Superior')
                                            print ('selected'); ?>>
                                            Técnico Superior</option>
                                        <option value="Técnico Medio" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == 'Técnico Medio')
                                            print ('selected'); ?>>Técnico
                                            Medio</option>
                                        <option value="9no Grado" <?php if (isset($data['nivel_educacional']) && $data['nivel_educacional'] == '9no Grado')
                                            print ('selected'); ?>>9no Grado
                                        </option>
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
                                            <option value="<?php print ($v['id']) ?>" <?php if (isset($data['cargos_id']) && $data['cargos_id'] == $v['id'])
                                                   print ('selected'); ?>>
                                                <?php print ($v['nombre']) ?>
                                            </option>
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
                                            <option value="<?php print ($v['id']) ?>" <?php if (isset($data['departamento_id']) && $data['departamento_id'] == $v['id'])
                                                   print ('selected'); ?>><?php print ($v['nombre']) ?></option>
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
                                    <input type="date" id="f-contratacion" name="fecha_contratacion" class="form-control" value="<?php if (isset($data['fecha_contratacion']))
                                        print ($data['fecha_contratacion']); ?>" readonly>
                                </div>
                            </div> -->
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-baja">Fecha Baja</label>
                                    <div class="input-group">
                                        <input type="date" id="f-baja" name="fecha_baja" class="form-control" value="<?php if (isset($data['fecha_baja']))
                                            print ($data['fecha_baja']); ?>" disabled>
                                        <div class="input-group-append" style="display:flex; align-items:center; padding-left:8px;">
                                            <div class="checkbox" style="margin:0;">
                                                <label style="margin:0;">
                                                    <input type="checkbox" id="check-fecha-baja" <?php if (isset($data['fecha_baja']) && !empty($data['fecha_baja']))
                                                        print ('checked'); ?>>
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
                                    <select id="f-estatus" name="estatus"
                                        class="form-control <?php echo $estatusClase; ?>" disabled>
                                        <option value=""></option>
                                        <option value="activo" class="estatus-activo" <?php if (isset($data['estatus']) && $data['estatus'] == 'activo')
                                            print ('selected'); ?>>Activo</option>
                                        <option value="inactivo" class="estatus-inactivo" <?php if (isset($data['estatus']) && $data['estatus'] == 'inactivo')
                                            print ('selected'); ?>>Inactivo</option>
                                    </select>
                                    <!-- <small class="help-block">Estado actual</small> -->
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-ubicacion">Ubicación </label>
                                    <select id="f-ubicacion" name="ubicacion" class="form-control" disabled>
                                        <?php
                                        $sql = 'SELECT * FROM ubicaciones';
                                        $res = $app->db->fetchAll($sql);
                                        $data_form['ubicaciones'] = $res;

                                        foreach ($data_form['ubicaciones'] as $k => $v) { ?>
                                            <option value="<?php print ($v['id']) ?>" <?php if (isset($data['ubicacion']) && $data['ubicacion'] == $v['id'])
                                                   print ('selected'); ?>>
                                                <?php print ($v['nombre']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
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
                                    <select id="f-licencia" name="licencia_conduccion[]"
                                        class="form-control selectpicker" multiple title="<?php echo $title; ?>"
                                        disabled>
                                        <?php if (empty($licencias)): ?>
                                            <option value="N/A" selected>N/A - No posee licencia</option>
                                        <?php endif; ?>
                                        <option value="A1" <?php echo in_array('A1', $licencias) ? 'selected' : ''; ?>>
                                            A1 - Ciclomotor</option>
                                        <option value="A" <?php echo in_array('A', $licencias) ? 'selected' : ''; ?>>A -
                                            Motocicleta</option>
                                        <option value="B" <?php echo in_array('B', $licencias) ? 'selected' : ''; ?>>B -
                                            Automóvil</option>
                                        <option value="C1" <?php echo in_array('C1', $licencias) ? 'selected' : ''; ?>>
                                            C1 - Camión ligero</option>
                                        <option value="C" <?php echo in_array('C', $licencias) ? 'selected' : ''; ?>>C -
                                            Camión pesado</option>
                                        <option value="D1" <?php echo in_array('D1', $licencias) ? 'selected' : ''; ?>>
                                            D1 - Microbús</option>
                                        <option value="D" <?php echo in_array('D', $licencias) ? 'selected' : ''; ?>>D -
                                            Omnibus</option>
                                        <option value="E" <?php echo in_array('E', $licencias) ? 'selected' : ''; ?>>E -
                                            Articulado</option>
                                        <option value="F" <?php echo in_array('F', $licencias) ? 'selected' : ''; ?>>F -
                                            Agroindustrial y de construcción</option>
                                        <option value="FE" <?php echo in_array('FE', $licencias) ? 'selected' : ''; ?>>
                                            FE - Tractor con remolque</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-bolsa">Bolsa de Empleo </label>
                                    <select id="f-bolsa" name="bolsa_empleo_id" class="form-control" disabled>
                                        <option value=""></option>
                                        <?php foreach ($data_form['bolsas'] as $k => $v) { ?>
                                            <option value="<?php print ($v['id']) ?>"><?php print ($v['nombre']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> -->

                        </div>


                        <!-- <div class="row panel-footer" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <button id="btn-pase-acceso" class="btn btn-warning icon-lg" type="button">
                                    <i class="fa fa-plus"></i>
                                    Descargar Pase de Acceso
                                </button>
                            </div>
                        </div> -->


                    </div>
                </div><!-- TAB GENERAL -->
                <div class="modal fade" id="modalContratoAnterior" tabindex="-1" role="dialog"
                    aria-labelledby="modalContratoAnteriorLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="modalContratoAnteriorLabel">Generar Contrato</h4>
                            </div>
                            <div class="modal-body">
                                <form id="form-contrato-anterior" enctype="multipart/form-data" method='POST'
                                    action='api-app.php'>
                                    <input type="hidden" name="trabajador_id"
                                        value="<?php echo isset($data['id']) ? intval($data['id']) : ''; ?>">
                                    <!-- Información del trabajador actual -->
                                    <div class="alert alert-info">
                                        <strong>Trabajador:</strong> <?php
                                        echo isset($data['nombre']) ?
                                            htmlspecialchars(trim($data['nombre'] . ' ' . $data['apellidos'] . ' ' . $data['apellidos_segundos']), ENT_QUOTES, 'UTF-8') :
                                            '';
                                        ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="tipo_contrato">Tipo de Contrato <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="tipo_contrato" name="tipo_contrato" required>
                                            <option value="">Seleccione tipo de contrato</option>
                                            <option value="2">Determinado</option>
                                            <option value="1">Indeterminado</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="fecha_inicio">Fecha de Inicio <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                            required>
                                    </div>

                                    <!-- <div class="form-group fecha-fin-group" style="display: none;">
                                    <label for="fecha_fin">Fecha de Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                    <small class="text-muted">Solo para contratos determinados</small>
                                </div> -->

                                    <!-- <div class="form-group">
                                    <label for="archivo_contrato">Archivo del Contrato (PDF o WORD) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="archivo_contrato" name="archivo_contrato" 
                                        accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX</small>
                                </div> -->
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <i class="fa fa-undo"></i> Volver
                                </button>
                                <button type="button" class="btn btn-success" id="btn-guardar-contrato-anterior">
                                    <i class="fa fa-save"></i> Generar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
                                    <label class="control-label" for="f-telefono">Teléfono Móvil</label>
                                    <div class="input-group">
                                        <input type="text" id="f-telefono" name="telefono" class="form-control"
                                            placeholder="Teléfono de contacto" value="<?php if (isset($data['telefono']))
                                                print ($data['telefono']); ?>" readonly>
                                        <?php if ($app->rol == 1): ?>
                                            <span class="input-group-btn">
                                                <button type="button" id="btn-sms-trabajador" class="btn btn-primary" title="Enviar mensaje">
                                                    <i class="fa fa-comment"></i>
                                                </button>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label class="control-label" for="f-direccion">Dirección</label>
                                    <textarea id="f-direccion" name="direccion" class="form-control" rows="2"
                                        placeholder="Dirección completa"
                                        readonly><?php if (isset($data['direccion']))
                                            print (htmlspecialchars($data['direccion'], ENT_QUOTES, 'UTF-8')); ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="f-provincia">Provincia</label>
                                    <input type="text" id="f-provincia" name="provincia" class="form-control"
                                        placeholder="Provincia" readonly
                                        value="<?php if (isset($data['provincia_nombre']))
                                            print (htmlspecialchars($data['provincia_nombre'], ENT_QUOTES, 'UTF-8')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="control-label" for="f-municipio">Municipio</label>
                                    <input type="text" id="f-municipio" name="municipio" class="form-control"
                                        placeholder="Municipio" readonly
                                        value="<?php if (isset($data['municipio_nombre']))
                                            print (htmlspecialchars($data['municipio_nombre'], ENT_QUOTES, 'UTF-8')); ?>">
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
                        <?php if ($app->rol != 2 && $app->rol != 4): ?>
                            <div class="form-control">
                                <button id="btn-add-new-recurso" class="btn btn-mint" alt="Asignar Recurso"
                                    title="Asignar Recurso"><i class="fa fa-plus fa-lg"></i> Asignar Recurso</button>
                            </div>
                        <?php endif; ?>
                        <table id="table-recursos" data-toggle="table"
                            data-url="api-app.php?module=gestion-recursos&method=list-id&trabajador_id=<?php print ($data['id']) ?>"
                            data-search="true" data-show-refresh="true" data-show-toggle="false"
                            data-show-columns="false" data-sort-name="fecha_entrega_a_t" data-sort-order="desc"
                            data-page-list="[20, 50, 100]" data-page-size="50" data-pagination="true"
                            data-show-pagination-switch="true">
                            <thead>
                                <tr>
                                    <!--<th data-field="id" id="f-id-rec" data-sortable="true" data-visible="false">ID</th>-->
                                    <th data-field="marca" data-sortable="true" data-visible="false">Marca</th>
                                    <th data-field="modelo" data-sortable="true" data-visible="false">Modelo</th>
                                    <th data-field="color" data-sortable="true" data-visible="false">Color</th>
                                    <th data-field="otros_recursos" data-sortable="true" data-visible="false">Otros
                                        Recursos</th>
                                    <th data-field="nombre" data-sortable="true">Recurso</th>
                                    <th data-field="estado" data-sortable="true" data-formatter="formatoEstado">Estado
                                    </th>
                                    <th data-field="fecha_entrega_a_t" data-sortable="true"
                                        data-formatter="formatoFechaEntregaEditable">Fecha Entrega</th>
                                    <th data-field="fecha_entrega_a_rh" data-sortable="true"
                                        data-formatter="formatoFechaDevolucionEditable">Fecha Devolución</th>
                                    <th data-field="toolbar" data-align="center" data-formatter="formatoToolbar2"
                                        data-sortable="false">Opciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
            <?php if (can_access_tab('contratacion', $app)): ?>
                <div class="tab-pane fade" id="tab-contratacion">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Listado de contratos</h3>
                        </div>

                        <div class="panel-body orm-padding">


                            <?php if ($app->rol != 2): ?>
                                <div class="form-control">

                                    <button id="btn-contrato-trabajador2" class="btn btn-mint btn-icon" alt="Generar Contrato"
                                        title="Generar Contrato">
                                        <span class="icon-lg fa fa-plus"></span> Asignar Contrato
                                    </button>

                                </div>
                            <?php endif; ?>



                            <table id="table-panel" data-toggle="table"
                                data-url="api-app.php?module=contratos&method=list-id&trabajador_id=<?php print ($data['id']); ?>"
                                data-search="true" data-show-refresh="true" data-show-toggle="false"
                                data-show-columns="false" data-sort-name="id" data-sort-order="desc"
                                data-page-list="[20, 50, 100]" data-page-size="50" data-pagination="true"
                                data-show-pagination-switch="true">
                                <thead>
                                    <tr>
                                        <th data-field="tipo" data-sortable="true" data-width="200"
                                            data-formatter="tipoFormatter">Tipo</th>
                                        <th data-field="fecha_inicio" data-sortable="true" data-width="120">Fecha Inicio
                                        </th>
                                        <th data-field="salario" data-sortable="true" data-width="120"
                                            data-formatter="salarioFormatter">Salario</th>
                                        <th data-field="cargo_nombre" data-sortable="true" data-width="150">Cargo</th>
                                        <th data-field="es_actual" data-sortable="true" data-width="100" data-align="center"
                                            data-formatter="estadoContratoFormatter">Estado</th>
                                        <?php if ($app->rol != 2): ?>
                                            <th data-field="opciones" data-formatter="opcionesContratoFormatter"
                                                data-align="center" data-width="100">Opciones</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                            </table>
                            <!-- Modal para Contratos Anteriores -->
                            <div class="modal fade" id="modalContrato" tabindex="-1" role="dialog"
                                aria-labelledby="modalContratoLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="modalContratoLabel">Asignar Contrato</h4>
                                        </div>
                                        <div class="modal-body">
                                            <form id="form-contrato-anterior2" enctype="multipart/form-data" method='POST'
                                                action='api-app.php'>
                                                <input type="hidden" name="trabajador_id"
                                                    value="<?php echo isset($data['id']) ? intval($data['id']) : ''; ?>">
                                                <!-- Información del trabajador actual -->
                                                <div class="alert alert-info">
                                                    <strong>Trabajador:</strong> <?php
                                                    echo isset($data['nombre']) ?
                                                        htmlspecialchars(trim($data['nombre'] . ' ' . $data['apellidos'] . ' ' . $data['apellidos_segundos']), ENT_QUOTES, 'UTF-8') :
                                                        '';
                                                    ?>
                                                </div>

                                                <?php if (isset($data['empresa_id']) && $data['empresa_id'] == 3): ?>
                                                    <!-- TCP (custodios): solo Contrato de Servicios -->
                                                    <div class="form-group">
                                                        <label for="tipo_contrato">Tipo de Contrato <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-control" id="tipo_contrato_contrato"
                                                            name="tipo_contrato" required>
                                                            <option value="4">Contrato de Servicios</option>
                                                        </select>
                                                    </div>
                                                <?php else: ?>
                                                <div class="form-group">
                                                    <label for="tipo_contrato">Tipo de Contrato <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" id="tipo_contrato_contrato"
                                                        name="tipo_contrato" required>
                                                        <option value="">Seleccione tipo de contrato</option>
                                                        <option value="3">Suplemento</option>
                                                        <option value="2">Determinado</option>
                                                        <option value="1">Indeterminado</option>
                                                    </select>
                                                </div>
                                                <?php endif; ?>

                                                <div class="form-group">
                                                    <label for="fecha_inicio">Fecha de Inicio <span
                                                            class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="fecha_inicio_contrato"
                                                        name="fecha_inicio" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="salario">Salario <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="salario_contrato"
                                                        name="salario" required>
                                                </div>



                                                <div class="form-group">
                                                    <label for="cargo">Cargo <span class="text-danger">*</span></label>
                                                    <select class="form-control" id="cargo_contrato" name="cargo" required>
                                                        <option value="">Seleccione un cargo</option>
                                                        <?php
                                                        $cargos = $app->db->fetchAll('SELECT id, nombre FROM cargos ORDER BY nombre');
                                                        foreach ($cargos as $cargo) {
                                                            echo '<option value="' . $cargo['id'] . '">' . htmlspecialchars($cargo['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <!-- <div class="form-group fecha-fin-group" style="display: none;">
                                    <label for="fecha_fin">Fecha de Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                    <small class="text-muted">Solo para contratos determinados</small>
                                </div> -->

                                                <!-- <div class="form-group">
                                    <label for="archivo_contrato">Archivo del Contrato (PDF o WORD) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="archivo_contrato" name="archivo_contrato" 
                                        accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX</small>
                                </div> -->
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                                <i class="fa fa-undo"></i> Volver
                                            </button>
                                            <button type="button" class="btn btn-success" id="btn-guardar-contrato2">
                                                <i class="fa fa-save"></i> Guardar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tab-pane fade" id="tab-capacitaciones">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Capacitaciones</h3>
                    </div>
                    <div class="panel-body">
                        <table id="table-panel" data-toggle="table"
                            data-url="api-app.php?module=programas-capacitacion&method=list-id&trabajador_id=<?php print ($data['id']); ?>"
                            data-search="true" data-show-refresh="true" data-show-toggle="false"
                            data-show-columns="false" data-sort-name="id" data-sort-order="desc"
                            data-page-list="[20, 50, 100]" data-page-size="50" data-pagination="true"
                            data-show-pagination-switch="true">
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
            <?php if (can_access_tab('salario', $app)): ?>
                <div class="tab-pane fade" id="tab-salario">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Salario</h3>

                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="control-label" for="f-salario-actual">Salario Mensual</label>
                                    <input type="text" id="f-salario-actual" name="salario-actual" class="form-control"
                                        placeholder="Salario Mensual" readonly
                                        value="<?php if (isset($data['salario']))
                                            print (htmlspecialchars($data['salario'], ENT_QUOTES, 'UTF-8') . ' CUP'); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="control-label" for="f-num-tarjeta">Número de Tarjeta</label>
                                    <input type="text" id="f-num-tarjeta" name="num-tarjeta" class="form-control"
                                        placeholder="Tarjeta Salario" readonly
                                        value="<?php if (isset($data['tarjeta_salario']))
                                            print (htmlspecialchars($data['tarjeta_salario'], ENT_QUOTES, 'UTF-8')); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="control-label" for="f-num-cuenta">Número de Cuenta Estándar</label>
                                    <input type="text" id="f-num-cuenta" name="num-cuenta" class="form-control"
                                        placeholder="Cuenta Estándar" readonly
                                        value="<?php if (isset($data['cuenta_estandar']))
                                            print (htmlspecialchars($data['cuenta_estandar'], ENT_QUOTES, 'UTF-8')); ?>">
                                </div>
                            </div>
                            <br>
                            <?php if ($app->rol != 2 && $app->empresa_id != 3): ?>
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                            <ul class="nav nav-tabs">
                                                <?php if ($app->rol != 2): ?>
                                                    <li class="active"><a data-toggle="tab" href="#tab-salarios">Salarios</a></li>
                                                <?php endif; ?>
                                                <!-- <li><a data-toggle="tab" href="#tab-tarjeta-snc">Tarjeta SNC225</a></li> -->
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="panel-body">
                                        <div class="tab-content">
                                            <div id="tab-salarios" class="tab-pane fade in active">
                                                <table id="table-salarios" data-toggle="table"
                                                    data-url="api-app.php?module=prenomina&method=list-prenomina-id&trabajador_id=<?php print ($data['id']); ?>"
                                                    data-search="true" data-show-refresh="true" data-show-toggle="true"
                                                    data-show-columns="true" data-sort-name="year" data-sort-order="desc"
                                                    data-page-list="[10, 25, 50]" data-page-size="10" data-pagination="true"
                                                    data-show-pagination-switch="true">
                                                    <thead>
                                                        <tr>
                                                            <th data-field="year" data-sortable="true">Período</th>
                                                            <th data-field="month" data-sortable="true">Mes</th>
                                                            <th data-field="horas" data-sortable="true">Horas trabajadas</th>
                                                            <th data-field="tarifa" data-sortable="true">Tarifa/hora (CUP)</th>
                                                            <th data-field="a_cobrar" data-sortable="true">Total Bruto (CUP)
                                                            </th>
                                                            <!-- <th data-field="bonif" data-sortable="true">Bonif.</th> -->
                                                            <!-- <th data-field="sal_dev" data-sortable="true">Sal. Dev.</th> -->
                                                            <th data-field="ausencias" data-sortable="true">Ausencias</th>
                                                            <th data-field="ausencias_costo" data-sortable="true">Costo
                                                                Ausencias</th>
                                                            <th data-field="vacaciones" data-sortable="true">Vacaciones</th>
                                                            <th data-field="pago_vac" data-sortable="true">Pago Vac.</th>
                                                            <th data-field="salario_neto" data-sortable="true">Sal. Neto (CUP)
                                                            </th>
                                                            <th data-field="seg_social" data-sortable="true">Seg. Social (CUP)
                                                            </th>
                                                            <th data-field="ing_pers" data-sortable="true">Ing. Pers. (CUP)</th>
                                                            <th data-field="salario_pagar" data-sortable="true">Neto a Pagar
                                                                (CUP)</th>
                                                            <th data-field="cargo" data-sortable="true">Cargo</th>
                                                            <th data-field="departamento" data-sortable="true">Departamento</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>

                                            <div id="tab-tarjeta-snc" class="tab-pane fade in">
                                                <table id="table-tarjetas-snc"
                                                    class="table table-striped table-bordered table-hover" data-toggle="table"
                                                    data-url="api-app.php?module=tarjetas-snc&method=list-id&trabajador_id=<?php print ($data['id']); ?>"
                                                    data-side-pagination="client" data-pagination="true" data-page-size="25"
                                                    data-search="true" data-show-refresh="true" data-show-columns="true"
                                                    data-sort-name="id" data-sort-order="desc" data-toolbar="#toolbar"
                                                    data-show-export="true" data-export-types="['csv','excel']"
                                                    data-export-options='{"fileName":"tarjetas_snc225_" + new Date().toISOString().slice(0,10)}'>
                                                    <thead>
                                                        <tr>
                                                            <th data-field="id" data-sortable="true">ID</th>
                                                            <th data-field="periodo_display" data-sortable="true">Período</th>
                                                            <th data-field="tiempo_trabajo_display" data-sortable="true">Tiempo
                                                                Trabajo</th>
                                                            <th data-field="salarios_devengados_display" data-sortable="true">
                                                                Salarios Devengados</th>
                                                            <th data-field="fecha_inicio_display" data-sortable="true">Fecha
                                                                Inicio</th>
                                                            <th data-field="fecha_cierre_display" data-sortable="true">Fecha
                                                                Cierre</th>
                                                            <th data-field="acciones" data-escape="false">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            <?php endif; ?>

            <?php if (can_access_tab('vacaciones', $app)): ?>
                <div class="tab-pane fade" id="tab-vacaciones">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Vacaciones</h3>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info">
                                <strong>Vacaciones Disponibles: </strong>
                                <span class="badge badge-primary" style="font-size: 14px; padding: 6px 12px;">
                                    <?php echo isset($data['vacaciones_disponibles']) ? number_format($data['vacaciones_disponibles'], 2) : '0.00'; ?> días
                                </span>
                            </div>
                            <?php if ($app->rol != 4): ?>
                            <div class="form-control">
                                <button id="btn-add-new-vacaciones" class="btn btn-mint" alt="Asignar Vacaciones"
                                    title="Asignar Vacaciones"><i class="fa fa-plus fa-lg"></i> Asignar Vacaciones</button>
                            </div>
                            <?php endif; ?>
                            <table id="table-vacaciones" data-toggle="table"
                                data-url="api-app.php?module=vacaciones&method=list-id&trabajador_id=<?php print ($data['id']); ?>"
                                data-search="true" data-show-refresh="true" data-show-toggle="false"
                                data-show-columns="false" data-sort-name="year" data-sort-order="desc"
                                data-page-list="[10, 25, 50]" data-page-size="10" data-pagination="true">
                                <thead>
                                    <tr>
                                        <th data-field="fecha_inicio" data-sortable="true">Fecha Inicio</th>
                                        <th data-field="fecha_fin" data-sortable="true">Fecha Fin</th>
                                        <th data-field="dias_totales" data-formatter="diasFormatter" data-escape="false"
                                            data-align="center" data-width="100">Días</th>
                                        <th data-field="fecha_aprobacion" data-formatter="formatoAprobacionVacaciones"
                                            data-sortable="true" data-align="center">Estado</th>
                                        <th data-field="fecha_aprobacion" data-sortable="true" data-width="140">Fecha
                                            Aprobación</th>
                                        <?php if ($app->rol == 1): ?>
                                            <th data-field="fecha_creacion" data-sortable="true" data-formatter="dateTimeFormatter" data-width="160">Fecha de Petición</th>
                                        <?php endif; ?>
                                        <?php if ($app->rol == 1 || $app->rol == 2 || $app->rol == 4): ?>
                                            <th data-field="id" data-formatter="accionesVacaciones" data-align="center"
                                                data-width="120">Acciones</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (can_access_tab('asistencias', $app)): ?>
                <div class="tab-pane fade" id="tab-asistencias">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Asistencia</h3>
                        </div>
                        <div class="panel-body">
                            <?php if (!isset($data['id'])): ?>
                                <div class="alert alert-info">
                                    Debe guardar el trabajador para ver su asistencia.
                                </div>
                            <?php else: ?>
                                <div class="form-control">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Fecha desde</label>
                                                <input type="date" id="asist-fecha-desde" class="form-control"
                                                    value="<?php echo date('Y-m-01'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Fecha hasta</label>
                                                <input type="date" id="asist-fecha-hasta" class="form-control"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-md-offset-3 text-right">
                                            <label>&nbsp;</label>
                                            <div class="form-group">
                                                <button id="btn-filtrar-asist" class="btn btn-primary" type="button">
                                                    <i class="fa fa-search"></i> Filtrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($app->rol == '1'): ?>
                                <!-- Exportar asistencia a Excel (por año completo o rango de meses) - Solo Administradores -->
                                <div class="form-control" style="margin-top:10px;">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Año (exportar)</label>
                                                <select id="exp-asist-anno" class="form-control">
                                                    <?php $yActual = (int)date('Y'); for ($yy = $yActual + 1; $yy >= $yActual - 4; $yy--): ?>
                                                        <option value="<?php echo $yy; ?>" <?php echo $yy === $yActual ? 'selected' : ''; ?>><?php echo $yy; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php $mesesExp = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre']; ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Desde mes</label>
                                                <select id="exp-asist-mes-desde" class="form-control">
                                                    <?php foreach ($mesesExp as $mnum => $mnom): ?>
                                                        <option value="<?php echo $mnum; ?>"><?php echo $mnom; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hasta mes</label>
                                                <select id="exp-asist-mes-hasta" class="form-control">
                                                    <?php foreach ($mesesExp as $mnum => $mnom): ?>
                                                        <option value="<?php echo $mnum; ?>" <?php echo $mnum === 12 ? 'selected' : ''; ?>><?php echo $mnom; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <label>&nbsp;</label>
                                            <div class="form-group">
                                                <button id="btn-export-asist-excel" class="btn btn-success btn-block" type="button">
                                                    <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                                </button>
                                                <small class="help-block" style="margin:2px 0 0;">Enero–Diciembre = año completo.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <table id="table-asistencias" data-toggle="table" data-search="true" data-show-refresh="true"
                                    data-show-toggle="false" data-sort-name="fecha" data-sort-order="desc"
                                    data-page-list="[10, 25, 50]" data-page-size="10" data-pagination="true">
                                    <thead>
                                        <tr>
                                            <th data-field="fecha" data-sortable="true" data-width="120"
                                                data-formatter="formatoFechaAsistencia">Fecha</th>
                                            <th data-field="hora_entrada" data-sortable="true" data-align="center"
                                                data-width="120">Entrada</th>
                                            <th data-field="hora_salida" data-sortable="true" data-align="center"
                                                data-width="120">Salida</th>
                                            <th data-field="hora_entrada" data-formatter="formatoHorasAsist" data-align="center"
                                                data-width="100">Horas</th>
                                            <th data-field="tipo_ausencia" data-sortable="true" data-align="center"
                                                data-width="120" data-formatter="formatoAsistencia">Asistencia</th>
                                            <th data-field="justificacion" data-visible="false">Justificación</th>
                                        </tr>
                                    </thead>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (can_access_tab('documentos', $app)): ?>
                <div class="tab-pane fade" id="tab-documentos">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Documentos</h3>
                        </div>
                        <div class="panel-body">
                            <?php if ($app->rol != 2): ?>
                                <div class="form-control">
                                    <button id="btn-add-new-doc" class="btn btn-mint" alt="Añadir Nuevo Documento"
                                        title="Añadir Nuevo Documento"><i class="fa fa-plus fa-lg"></i> Añadir
                                        Documento</button>
                                </div>
                            <?php endif; ?>
                            <table id="table-documentos" data-toggle="table"
                                data-url="api-app.php?module=documentos&method=list-id&trabajador_id=<?php print ($data['id']); ?>"
                                data-search="true" data-show-refresh="true" data-show-toggle="true"
                                data-show-columns="false" data-sort-name="year" data-sort-order="desc"
                                data-page-list="[10, 25, 50]" data-page-size="10" data-pagination="true">
                                <thead>
                                    <tr>
                                        <th data-field="tipo" data-sortable="true">Descripción</th>
                                        <!--<th data-field="archivo"data-align="center" data-width="140">Archivo</th>-->
                                        <th data-field="fecha_upload" data-sortable="true">Fecha Subida</th>
                                        <th data-field="archivo" data-formatter="pdfFormatter2" data-align="center"
                                            data-width="140">Opciones</th>

                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="panel-footer text-center">
                <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
                <!-- <button id="btn-save" class="btn btn-info icon-lg" type="button">
                                <i class="fa fa-check"></i>
                                Guardar
                            </button> -->
                <?php if ($app->rol != 2): ?>
                    <button id="btn-back" class="btn btn-default icon-lg" type="button">
                        <i class="fa fa-undo"></i>
                        Volver
                    </button>
                <?php endif; ?>

            </div>
        </div><!-- TAB RECURSOS ASIGNADOS -->

    </div>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->
</div>

<!-- Modal para agregar documento -->

<!-- Modal para detalles del recurso -->
<div class="modal fade" id="recursoModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Detalles del Recurso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody2">
                <!-- Aquí se insertan los datos -->
            </div>
        </div>
    </div>
</div>
<!-- llamar a add-doc.php -->
<?php include 'add-doc.php'; ?>
<?php include 'add-rec.php'; ?>
<?php include 'add-vac.php'; ?>

<!-- Modal para detalles del recurso -->
<div class="modal fade" id="trabajadorModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="modalLabel">Detalles del Trabajador</h5> -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Aquí se insertan los datos -->
            </div>
        </div>
    </div>
</div>

<!-- Formulario de registro de asistencia -->
<div class="modal fade" id="modalAsistencia" tabindex="-1" role="dialog" aria-labelledby="modalAsistenciaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalAsistenciaLabel">Registrar Asistencia</h4>
            </div>
            <div class="modal-body">
                <form id="formAsistencia">
                    <input type="hidden" name="id" id="asistencia_id">
                    <input type="hidden" name="trabajador_id" id="trabajador_id">

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" disabled>
                    </div>

                    <div class="row" hidden>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hora_entrada">Hora de Entrada</label>
                                <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hora_salida">Hora de Salida</label>
                                <input type="time" class="form-control" id="hora_salida" name="hora_salida" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="ausencia" name="ausencia"> Marcar/Describir ausencia
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="tipo-ausencia-container" style="display: none;">
                        <label for="tipo_ausencia">Tipo de Ausencia</label>
                        <select class="form-control" id="tipo_ausencia" name="tipo_ausencia">
                            <option value="">Seleccione un tipo</option>
                            <option value="Injustificada">Injustificada</option>
                            <option value="Justificada">Justificada</option>
                            <option value="Enfermedad">Enfermedad</option>
                            <option value="Licencia de Maternidad">Licencia de Maternidad</option>
                        </select>
                    </div>

                    <div class="form-group" id="justificacion-container" style="display: none;">
                        <label for="justificacion">Descripción</label>
                        <textarea class="form-control" id="justificacion" name="justificacion" rows="3"
                            placeholder="Ingrese la descripción de la ausencia"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-asistencia">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSmsTrabajador" tabindex="-1" role="dialog" aria-labelledby="modalSmsTrabajadorLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalSmsTrabajadorLabel">Enviar SMS</h4>
            </div>
            <div class="modal-body">
                <form id="formSmsTrabajador">
                    <input type="hidden" id="sms_trabajador_id" value="">
                    <input type="hidden" id="sms_telefono" value="">
                    <div class="form-group">
                        <label class="control-label" for="sms_mensaje">Mensaje SMS <span class="text-danger">*</span></label>
                        <textarea id="sms_mensaje" class="form-control" rows="4" placeholder="Escriba aquí el mensaje SMS..." maxlength="160"></textarea>
                        <small class="help-block">
                            <span id="sms_char_count">0</span>/160 caracteres (límite estándar SMS)
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-enviar-sms-trabajador">Enviar SMS</button>
            </div>
        </div>
    </div>
</div>
