<?php
$catsFiltro = isset($app) && isset($app->db)
    ? $app->db->fetchAll("SELECT id, nombre FROM categorias_recurso ORDER BY nombre")
    : array();
?>
<div class="panel">
    <div class="form-control" style="gap:8px; display:flex; align-items:center;">
        <button id="btn-add-new" class="btn btn-mint btn-icon " alt="Crear Recurso" title="Crear Recurso"><span class="icon-lg fa fa-plus"></span> Crear Recurso</button>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="form-inline" style="margin-bottom: 15px;">
            <!-- Filtro de Categoría -->
            <div class="form-group" style="margin-right: 10px;">
                <select id="filtro-categoria" class="form-control">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($catsFiltro as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['id']); ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro de Estado -->
            <div class="form-group" style="margin-right: 10px;">
                <select id="filtro-disponible" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="1">Disponibles</option>
                    <option value="0">Asignados</option>
                </select>
            </div>

            <!-- Filtro por destino de la asignación -->
            <div class="form-group" style="margin-right: 10px;">
                <select id="filtro-tipo-asignacion" class="form-control">
                    <option value="">Todos los destinos</option>
                    <option value="trabajador">Trabajadores</option>
                    <option value="objeto">Objetos</option>
                </select>
            </div>

            <button id="btn-filtrar-recursos" class="btn btn-primary">Filtrar</button>
            <button id="btn-reset-categoria" class="btn btn-default" style="margin-left: 5px;">Limpiar</button>
            <button id="btn-exportar-asignados" class="btn btn-success" style="margin-left: 5px;">Exportar Excel</button>
        </div>
        <div>
            <table
                id="table-todos"
                data-toggle="table"
                data-url="api-app.php?module=gestion-recursos&method=get_recursos"
                data-search="true"
                data-show-refresh="true"
                data-show-toggle="false"
                data-show-columns="false"
                data-sort-name="fecha_entrega_a_t"
                data-sort-order="desc"
                data-page-list="[20, 50, 100]"
                data-page-size="50"
                data-pagination="true"
                data-show-pagination-switch="true"
                data-filter-control="true"
                data-filter-show-clear="true">
                <thead>
                    <tr>
                        <th data-field="categoria" data-sortable="true">Categoría</th>
                        <th data-field="nombre" data-sortable="true">Recurso</th>
                        <th data-field="disponible" data-sortable="true" data-align="center" data-formatter="formatoDisponible">Estado</th>
                        <th data-field="asignado_nombre" data-sortable="true" data-formatter="formatoAsignadoLink">Asignado a</th>
                        <th data-field="fecha_entrega_a_t" data-sortable="true" data-formatter="formatoFecha">Fecha Entrega</th>
                        <th data-field="descripcion" data-sortable="true">Descripción</th>
                        <th data-field="toolbar" data-align="center" data-cell-style="celdaSinWrap" data-formatter="formatoToolbar" data-sortable="false">Opciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?php include_once('create-rec.php'); ?>
<?php include_once('assign-rec.php'); ?>

<!-- Modal para detalles del recurso -->
<div class="modal fade" id="recursoModaldetalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Detalles del Recurso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBodydetalle">
                <!-- Aquí se insertan los datos -->
            </div>
        </div>
    </div>
</div>
