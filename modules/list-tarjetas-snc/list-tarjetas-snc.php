<script>
    var rol = '<?php print ($app->rol) ?>';
</script>

<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li><a href="#" onclick="location.href='?module=list-trabajadores'">Listado de Trabajadores</a></li>
                <li><a href="#" onclick="location.href='?module=list-contratos'">Contratos</a></li>
                <li class="active"><a href="#tab-listado" data-toggle="tab" aria-expanded="true">Listado Tarjeta SNC</a></li>
            </ul>
        </div>
        <h3 class="panel-title">Tarjeta SNC 225</h3>
    </div>

    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-listado">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="table-tarjetas-snc" class="table table-striped table-bordered table-hover"
                                   data-toggle="table"
                                   data-url="api-app.php?module=tarjetas-snc&method=list"
                                   data-side-pagination="client"
                                   data-pagination="true"
                                   data-page-size="25"
                                   data-search="true"
                                   data-show-refresh="true"
                                   data-show-columns="true"
                                   data-sort-name="id"
                                   data-sort-order="desc"
                                   data-toolbar="#toolbar"
                                   data-show-export="true"
                                   data-export-types="['csv','excel']"
                                   data-export-options='{"fileName":"tarjetas_snc225_" + new Date().toISOString().slice(0,10)}'>
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">ID</th>
                                        <th data-field="carnet_identidad" data-sortable="true">CI</th>
                                        <th data-field="nombre_completo" data-sortable="true">Nombre Completo</th>
                                        <th data-field="periodo_display" data-sortable="true">Período</th>
                                        <th data-field="tiempo_trabajo_display" data-sortable="true">Tiempo Trabajo</th>
                                        <th data-field="salarios_devengados_display" data-sortable="true">Salarios Devengados</th>
                                        <th data-field="fecha_inicio_display" data-sortable="true">Fecha Inicio</th>
                                        <th data-field="fecha_cierre_display" data-sortable="true">Fecha Cierre</th>
                                        <th data-field="acciones" data-escape="false">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS del módulo -->
<script src="modules/list-tarjetas-snc/list-tarjetas-snc.js?<?php echo time(); ?>"></script>
