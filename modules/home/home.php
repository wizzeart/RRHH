<?php
// Asegurarse de que tenemos acceso a la base de datos
global $data, $page;
?>

<!-- Dashboard Header -->
<div class="row">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Resumen Estadísticas</h3>
            </div>
        </div>
    </div>

    <style>
        /* Hover sutil para paneles clicables */
        a .panel.panel-colorful {
            transition: transform .12s ease-in-out, box-shadow .12s ease-in-out;
        }

        a .panel.panel-colorful:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .mar-btm {
            margin-bottom: 8px;
        }
    </style>

    <!-- Contadores fila 1: existentes (4 elementos) -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <!-- Total Trabajadores -->
                <div class="col-sm-6 col-lg-3">
                    <a href="?module=list-trabajadores" style="text-decoration:none;" data-toggle="tooltip" title="Ver lista de trabajadores">
                        <div class="panel panel-primary panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-users fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-trabajadores">0</span>
                                <p>Trabajadores</p>
                            </div>
                        </div>
                    </a>
                </div>
               
               
                <!-- Promedio de Edad -->
                <div class="col-sm-6 col-lg-3">
                    <a href="?module=list-departamentos" style="text-decoration:none;" data-toggle="tooltip" title="Ver detalle de departamentos">
                        <div class="panel panel-success panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-bar-chart fa-2x"></i></div>
                                <span class="text-3x text-thin" id="departamentos">0</span>
                                <p>Departamentos</p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Cargos Diferentes -->
                <div class="col-sm-6 col-lg-3">
                    <a href="?module=list-cargos" style="text-decoration:none;" data-toggle="tooltip" title="Ver cargos en trabajadores">
                        <div class="panel panel-warning panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-briefcase fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-cargos">0</span>
                                <p>Cargos</p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Usuarios -->
                <div class="col-sm-6 col-lg-3">
                    <a href="?module=list-usuarios" style="text-decoration:none;" data-toggle="tooltip" title="Ver listado de usuarios">
                        <div class="panel panel-info panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-user fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-usuarios">0</span>
                                <p>Usuarios</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contadores fila 2: Subcontratos, Contratos, Capacitaciones, Bolsas (clickables) -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <!-- Subcontratos -->
                <div class="col-sm-6 col-lg-3">
                    <a href="#" style="text-decoration:none;" data-toggle="tooltip" title="Ver listado de subcontratos">
                        <div class="panel panel-purple panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-sort-desc fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-subcontratos">0</span>
                                <p>Subcontratos</p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Contratos -->
                <div class="col-sm-6 col-lg-3">
                    <a href="#" style="text-decoration:none;" data-toggle="tooltip" title="Ver listado de contratos">
                        <div class="panel panel-danger panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-file-text-o fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-contratos">0</span>
                                <p>Contratos</p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Capacitaciones -->
                <div class="col-sm-6 col-lg-3">
                    <a href="#" style="text-decoration:none;" data-toggle="tooltip" title="Ver programas de capacitación">
                        <div class="panel panel-dark panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-graduation-cap fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-capacitaciones">0</span>
                                <p>Capacitaciones</p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Bolsas de Empleo -->
                <div class="col-sm-6 col-lg-3">
                    <a href="?module=list-bolsas_empleos" style="text-decoration:none;" data-toggle="tooltip" title="Ver postulaciones de bolsa de empleo">
                        <div class="panel panel-mint panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-database fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-bolsas">0</span>
                                <p>Bolsas de Empleo</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contadores fila 3: centrados (Usuarios, Bajas Trabajadores) -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                
                
                <!-- Trabajadores dados de baja -->
                <!-- <div class="col-sm-6 col-lg-3">
                    <a href="?module=bajas-trabajadores" style="text-decoration:none;" data-toggle="tooltip" title="Ver trabajadores dados de baja">
                        <div class="panel panel-warning panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-user-times fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-bajas-trabajadores">0</span>
                                <p>Trabajadores dados de baja</p>
                            </div>
                        </div>
                    </a>
                </div> -->
                <div class="col-sm-6 col-lg-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts: load jQuery from CDN with local fallback, then the dashboard script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.jQuery || document.write('<script src="js/jquery-3.2.1.min.js"><\/script>');
</script>
<script type="text/javascript" src="modules/home/home.js"></script>

<!-- Lista de Trabajadores -->
<div class="row" style="margin-top:20px;">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Últimos Trabajadores</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="trabajadores-list">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cargo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- filas generadas por JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listas adicionales: Cargos, Departamentos, Pases de Acceso -->
<div class="row" style="margin-top:20px;">
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Cargos</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="cargos-list">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <th>Nombre</th>
                                <th class="text-center">Salario (CUP/Hora)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Departamentos</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="departamentos-list">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Cantidad de Trabajadores</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

