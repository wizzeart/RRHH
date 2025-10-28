<div class="panel">
                <div class="form-control">
                    <button id="btn-add-new" class="btn btn-mint btn-icon" alt="Añadir Nuevo Trabajador" title="Añadir Nuevo Trabajador"><span class="icon-lg fa fa-plus"></span> Añadir Nuevo Trabajador</button>
                </div>
            </div>
<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   -->
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-listado" data-toggle="tab" aria-expanded="true">Listado de Trabajadores</a></li>
                <li><a href="#" onclick="location.href='?module=delete-trabajadores'">Dar de baja</a></li>
                <li><a href="#" onclick="location.href='?module=bajas-trabajadores'">Listado de Bajas</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
            <div class="form-inline" style="margin-bottom: 15px;">
                        <!-- Search Box -->
                        <!-- <div class="form-group" style="margin-right: 10px; width: 300px; position: relative;">
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscar-trabajador" placeholder="Buscar por nombre, apellido o CI...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                            <div id="resultados-busqueda" class="list-group" style="position: absolute; z-index: 1000; width: 100%; display: none; max-height: 300px; overflow-y: auto;"></div>
                        </div> -->
                        
                        <!-- Cargo Filter -->
                        <div class="form-group" style="margin-right: 10px;">
                            <select id="filterCargo" class="form-control">
                                <option value="">Todos los cargos</option>
                                <?php
                                $cargos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM cargos ORDER BY nombre");
                                foreach ($cargos as $cargo) {
                                    echo "<option value='" . $cargo['id'] . "'>" . htmlspecialchars($cargo['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Departamento Filter -->
                        <div class="form-group" style="margin-right: 10px;">
                            <select id="filterDepartamento" class="form-control">
                                <option value="">Todos los departamentos</option>
                                <?php
                                $deptos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM departamentos WHERE empresa_id = {$app->empresa_id} ORDER BY nombre");
                                foreach ($deptos as $depto) {
                                    echo "<option value='" . $depto['id'] . "'>" . htmlspecialchars($depto['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button id="btn-filter" class="btn btn-primary">Filtrar</button>
                        <button id="btn-reset" class="btn btn-default" style="margin-left: 5px;">Limpiar</button>
                    </div>
            <div class="tab-pane fade active in" id="tab-listado">
                <div class="panel">
                    
                </div>
                <table
                    id="table-panel"
                    data-toggle="table"
                    data-url="api-app.php?module=trabajadores&method=list"
                    data-search="true"
                    data-show-refresh="true"
                    data-show-toggle="false"
                    data-show-columns="false"
                    data-sort-name="id"
                    data-page-list="[20, 50, 100]"
                    data-page-size="50"
                    data-pagination="true" data-show-pagination-switch="true">
                    <thead>
                        <tr>
                            <!-- <th data-field="id" data-sortable="true">ID</th>  -->
                            <th data-field="carnet_identidad" data-sortable="true">CI</th>
                            <th data-field="nombre" data-sortable="true">Nombre</th>
                            <th data-field="apellidos" data-sortable="true">Apellidos</th>
                            <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                            <th data-field="departamento_nombre" data-sortable="true">Departamento</th>
                            <!-- <th data-field="estatus" data-align="center" data-formatter="formatoActivo" data-sortable="false">Estado</th>  -->
                            <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Acciones</th>

                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!--===================================================-->
<div class="modal fade" id="trabajadorModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
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