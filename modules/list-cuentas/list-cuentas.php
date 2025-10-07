<script>
    var rol = '<?php print ($app->rol) ?>';
</script>

<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#"
                data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Listado de cuentas bancarias de trabajadores activos</p>" 
                data-html="true" title=""></a>
        </div>
        <h3 class="panel-title">Listado de Cuentas</h3>
    </div>

    <div class="panel-body">
        <!-- Botones de acción -->
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group" role="group">
                   
                    <a href="?module=list-trabajadores" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
                <hr>
            </div>
        </div>

        <!-- Tabla de cuentas bancarias -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="table-cuentas" class="table table-striped table-bordered table-hover" 
                           data-toggle="table" 
                           data-url="api-app.php?module=cuentas&method=list"
                           data-side-pagination="client"
                           data-pagination="true"
                           data-page-size="25"
                           data-search="true"
                           data-show-refresh="true"
                           data-show-columns="true"
                           data-sort-name="nombre_completo"
                           data-sort-order="asc"
                           data-toolbar="#toolbar"
                           data-show-export="true"
                           data-export-types="['csv', 'excel']"
                           data-export-options='{
                               "fileName": "cuentas_bancarias_" + new Date().toISOString().slice(0,10)
                           }'>
                        <thead>
                            <tr>
                                <th data-field="nombre_completo" data-sortable="true">Nombre Completo</th>
                                <th data-field="carnet_identidad" data-sortable="true">Carnet de Identidad</th>
                                <th data-field="tarjeta_display" data-sortable="true">Tarjeta de Salario</th>
                                <th data-field="cuenta_display" data-sortable="true">Cuenta Estándar</th>
                                <th data-field="estatus_display" data-sortable="true">Estado</th>
                                <th data-field="acciones" data-formatter="accionesFormatter" data-events="accionesEvents">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir JavaScript del módulo -->
<script src="modules/list-cuentas/list-cuentas.js?<?php echo time(); ?>"></script>
