<?php
// Asegurarse de que tenemos acceso a la base de datos
global $data, $page;
?>
<!-- Dashboard Header -->
<div class="row">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Resumen de Personal</h3>
            </div>
        </div>
    </div>
</div>

<!-- Contadores Principales -->
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <!-- Total Trabajadores -->
            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-primary panel-colorful">
                    <div class="pad-all text-center">
                        <span class="text-3x text-thin" id="total-trabajadores">0</span>
                        <p>Total Trabajadores</p>
                    </div>
                </div>
            </div>
            <!-- Trabajadores Activos -->
            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-info panel-colorful">
                    <div class="pad-all text-center">
                        <span class="text-3x text-thin" id="trabajadores-activos">0</span>
                        <p>Trabajadores Activos</p>
                    </div>
                </div>
            </div>
            <!-- Promedio de Edad -->
            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-success panel-colorful">
                    <div class="pad-all text-center">
                        <span class="text-3x text-thin" id="promedio-edad">0</span>
                        <p>Promedio de Edad</p>
                    </div>
                </div>
            </div>
            <!-- Cargos Diferentes -->
            <div class="col-sm-6 col-lg-3">
                <div class="panel panel-warning panel-colorful">
                    <div class="pad-all text-center">
                        <span class="text-3x text-thin" id="total-cargos">0</span>
                        <p>Cantidad de Cargos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Scripts: load jQuery from CDN with local fallback, then the dashboard script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>window.jQuery || document.write('<script src="js/jquery-3.2.1.min.js"><\/script>');</script>
<script type="text/javascript" src="modules/home/home.js"></script>

<!-- Lista de Trabajadores -->
<div class="row" style="margin-top:20px;">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Lista de Trabajadores</h3>
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
                            <div class="panel-heading"><h3 class="panel-title">Cargos</h3></div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="cargos-list">
                                        <thead>
                                            <tr><th>ID</th><th>Departamento</th><th>Nombre</th><th>Salario</th></tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading"><h3 class="panel-title">Departamentos</h3></div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="departamentos-list">
                                        <thead>
                                            <tr><th>ID</th><th>Nombre</th></tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:20px;">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading"><h3 class="panel-title">Pases de Acceso (recientes)</h3></div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="pases-list">
                                        <thead>
                                            <tr><th>ID</th><th>Trabajador ID</th><th>Subcontrato ID</th><th>Áreas</th><th>Fecha</th><th>Vigente</th></tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
