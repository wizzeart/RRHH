<div class="panel">
    <?php if ($app->rol != '3' && $app->rol != '4'): ?>
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon" alt="Añadir Nuevo Trabajador" title="Añadir Nuevo Trabajador"><span class="icon-lg fa fa-plus"></span> Añadir Nuevo Trabajador</button>
    </div>
    <?php endif; ?>
</div>
<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   -->
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-listado" data-toggle="tab" aria-expanded="true"><i class="fa fa-list"></i> Listado de Trabajadores</a></li>
                <!-- <li><a href="#" onclick="location.href='?module=delete-trabajadores'">Dar de baja</a></li> -->
                <?php if ($app->rol != '3' && $app->rol != '4'): ?>
                <li><a href="#" onclick="location.href='?module=bajas-trabajadores'"><i class="fa fa-ban"></i> Listado de Bajas</a></li>
                <?php endif; ?>
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
                        if ($app->rol == 4) {
                            // Jefe de área: solo mostrar cargos de sus trabajadores asignados
                            $cargos = $app->db->fetchAll("
                                SELECT DISTINCT c.id, c.nombre 
                                FROM cargos c
                                INNER JOIN trabajadores t ON t.cargos_id = c.id
                                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t.departamento_id 
                                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t.ubicacion
                                WHERE aud.usuario_id = {$app->user_id} 
                                AND auu.usuario_id = {$app->user_id}
                                ORDER BY c.nombre
                            ");
                        } else {
                            // Otros roles: mostrar todos los cargos
                            $cargos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM cargos ORDER BY nombre");
                        }
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
                        if ($app->rol == 4) {
                            // Jefe de área: solo mostrar departamentos de sus trabajadores asignados
                            $deptos = $app->db->fetchAll("
                                SELECT DISTINCT d.id, d.nombre 
                                FROM departamentos d
                                INNER JOIN trabajadores t ON t.departamento_id = d.id
                                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t.departamento_id 
                                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t.ubicacion
                                WHERE aud.usuario_id = {$app->user_id} 
                                AND auu.usuario_id = {$app->user_id}
                                ORDER BY d.nombre
                            ");
                        } else {
                            // Otros roles: mostrar todos los departamentos
                            $deptos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM departamentos ORDER BY nombre");
                        }
                        foreach ($deptos as $depto) {
                            echo "<option value='" . $depto['id'] . "'>" . htmlspecialchars($depto['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Ubicación Filter -->
                <div class="form-group" style="margin-right: 10px;">
                    <select id="filterUbicacion" class="form-control">
                        <option value="">Todas las ubicaciones</option>
                        <?php
                        if ($app->rol == 4) {
                            // Jefe de área: solo mostrar ubicaciones de sus trabajadores asignados
                            $ubicaciones = $app->db->fetchAll("
                                SELECT DISTINCT u.id, u.nombre 
                                FROM ubicaciones u
                                INNER JOIN trabajadores t ON t.ubicacion = u.id
                                INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t.departamento_id 
                                INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t.ubicacion
                                WHERE aud.usuario_id = {$app->user_id} 
                                AND auu.usuario_id = {$app->user_id}
                                ORDER BY u.nombre
                            ");
                        } else {
                            // Otros roles: mostrar todas las ubicaciones
                            $ubicaciones = $app->db->fetchAll("SELECT id, nombre FROM ubicaciones ORDER BY nombre");
                        }
                        foreach ($ubicaciones as $ubi) {
                            echo "<option value='" . $ubi['id'] . "'>" . htmlspecialchars($ubi['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <button id="btn-filter" class="btn btn-primary">Filtrar</button>
                <button id="btn-reset" class="btn btn-default" style="margin-left: 5px;">Limpiar</button>
                <?php if ($app->rol != '3' && $app->rol != '4'): ?>
                <button id="btn-export-excel" class="btn btn-success" style="margin-left: 5px;">Exportar Excel</button>
                <?php endif; ?>
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
                    data-sort-name="id"
                    data-sort-order="desc"
                    data-page-size="10"
                    data-pagination="true"
                    data-page-list="[10]"
                    data-side-pagination="server">
                    <thead>
                        <tr>
                            <th data-field="foto" data-formatter="formatoFoto" data-sortable="false" data-width="100">Foto</th>
                            <th data-field="carnet_identidad" data-sortable="true">CI</th>
                            <th data-field="nombre" data-sortable="true" data-formatter="formatoNombreCompleto">Nombre Completo</th>
                            <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                            <th data-field="departamento_nombre" data-sortable="true">Departamento</th>
                            <th data-field="ubicacion_nombre" data-sortable="true">Ubicación</th>
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

<!-- Modal de confirmación para dar de baja -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Confirmar dar de baja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Para confirmar, escribe <strong>ELIMINAR</strong> en el siguiente campo. Esta acción establecerá la fecha de baja automáticamente.</p>
                <input type="text" id="confirm-delete-text" class="form-control" placeholder="Escribe ELIMINAR" autocomplete="off">
                <small id="confirm-delete-help" class="text-danger" style="display:none;">Debes escribir exactamente "ELIMINAR".</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-confirm-delete" class="btn btn-danger">Confirmar baja</button>
            </div>
        </div>
    </div>
    <!-- Datos temporales -->
    <input type="hidden" id="confirm-delete-id" value="">
    <input type="hidden" id="confirm-delete-row" value="">
</div>

<!-- Modal de error de liquidación (prenomina) -->
<div class="modal fade" id="liquidarErrorModal" tabindex="-1" role="dialog" aria-labelledby="liquidarErrorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="liquidarErrorLabel">No se puede liquidar al trabajador</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span id="liquidar-error-msg"></span>
                </div>
                <p>¿Desea dar de baja al trabajador de todas formas <strong>sin liquidación en prenómina</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-confirm-baja-sin-prenomina" class="btn btn-warning">Confirmar baja sin prenómina</button>
            </div>
        </div>
    </div>
    <input type="hidden" id="liquidar-error-id" value="">
    <input type="hidden" id="liquidar-error-row" value="">
</div>

<script>
    var userRole = '<?php echo $app->rol; ?>';
</script>

<script src="modules/list-trabajadores/list-trabajadores.js"></script>