<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon " alt="Asignar Recurso" title="Asignar Recurso"><span class="icon-lg fa fa-plus"></span> Asignar Recurso</button>
    </div>
</div>

<!-- Pestañas -->
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#todos">Todos los Recursos</a></li>
                <li><a data-toggle="tab" href="#asignados">Asignados</a></li>
                <li><a data-toggle="tab" href="#retornados">Retornados</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
            <!-- Pestaña de Todos los Recursos -->
            <div id="todos" class="tab-pane fade in active">
                <div class="panel-body">
                    <table
                        id="table-todos"
                        data-toggle="table"
                        data-url="api-app.php?module=gestion-recursos&method=list"
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
                                <th data-field="nombre_trabajador" data-sortable="true">Trabajador</th>
                                <th data-field="estado" data-sortable="true" data-formatter="formatoEstado">Estado</th>
                                <th data-field="fecha_entrega_a_t" data-sortable="true" data-formatter="formatoFecha">Fecha Entrega</th>
                                <th data-field="fecha_entrega_a_rh" data-sortable="true" data-formatter="formatoFecha">Fecha Devolución</th>
                                <th data-field="toolbar" data-align="center" data-formatter="formatoToolbar" data-sortable="false">Opciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <!-- Pestaña de Recursos Asignados -->
            <div id="asignados" class="tab-pane fade">
                <div class="panel-body">
                    <table
                        id="table-asignados"
                        data-toggle="table"
                        data-url="api-app.php?module=gestion-recursos&method=list&estado=1"
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
                                <th data-field="nombre_trabajador" data-sortable="true">Trabajador</th>
                                <th data-field="fecha_entrega_a_t" data-sortable="true" data-formatter="formatoFecha">Fecha Entrega</th>
                                <th data-field="toolbar" data-align="center" data-formatter="formatoToolbar" data-sortable="false">Opciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <!-- Pestaña de Recursos Retornados -->
            <div id="retornados" class="tab-pane fade">
                <div class="panel-body">
                    <table
                        id="table-retornados"
                        data-toggle="table"
                        data-url="api-app.php?module=gestion-recursos&method=list&estado=0"
                        data-search="true"
                        data-show-refresh="true"
                        data-show-toggle="false"
                        data-show-columns="false"
                        data-sort-name="fecha_entrega_a_rh"
                        data-sort-order="desc"
                        data-page-list="[20, 50, 100]"
                        data-page-size="50"
                        data-pagination="true"
                        data-show-pagination-switch="true">
                        <thead>
                            <tr>
                                <th data-field="nombre" data-sortable="true">Recurso</th>
                                <th data-field="nombre_trabajador" data-sortable="true">Trabajador</th>
                                <th data-field="fecha_entrega_a_rh" data-sortable="true" data-formatter="formatoFecha">Fecha Devolución</th>
                                <th data-field="toolbar" data-align="center" data-formatter="formatoToolbar" data-sortable="false">Opciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
            <div class="modal-body" id="modalBody">
                <!-- Aquí se insertan los datos -->
            </div>
        </div>
    </div>
</div>