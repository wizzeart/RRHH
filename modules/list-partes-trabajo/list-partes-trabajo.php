<script>
    //var marca = '<?php print($_GET['id']) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>

<div class="panel hidden">
    <div class="form-control">
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
        <!-- <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Artículo" title="Añadir Nuevo Artículo"></button> -->
        <!-- <button id="btn-dl-pdf" class="btn btn-purple btn-icon icon-lg fa fa-download" alt="Descargar Catálogo Revendedor" title="Descargar Catálogo Revendedor"></button> -->
    </div>
</div>

<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   data-url="api-app.php?module=articulos&method=list"--> 
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-ajax="ajaxRequest"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="true"
            data-show-columns="true"
            data-sort-name="id"
            data-page-list="[50, 100, 200]"
            data-page-size="100"
            data-pagination="true"
            data-show-pagination-switch="true"
            data-search-accent-neutralise="true"
            data-checkbox-header="true"
            >
            <thead>
                <tr>
                    <th data-field="xparte_id" data-sortable="true">ID</th>
                    <th data-field="xfecha_format" data-sortable="true" data-formatter="formatoDescripcion">Fecha</th>
                    <th data-field="xparte_id" data-align="center" data-sortable="false" data-formatter="formatoToolbar"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->


<!-- modales -->
<div id="modalSearch" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-xs">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Búsqueda Avanzada</h4>    
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-categorias">Categorías</label>
                            </div>
                            <select id="ms-categorias" class="form-control" multiple="true" data-placeholder="Seleccione categorías">
                                <?php foreach ($data_form['categorias'] as $k => $v) { ?>
                                    <option value="<?php print($v['xcategoria_id']) ?>" <?php if (isset($data['xcategorias']) && in_array($v['xcategoria_id'], $data['xcategorias'])) print('selected'); ?>><?php print($v['xcategoria']) ?></option>
                                <?php } ?>
                            </select>
                            <small class="help-block">Seleccione las categorías a las que pertenece</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-activo">Activo</label>
                            <select id="ms-activo" class="form-control">
                                <option value="S">Sí</option>
                                <option value="N">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="f-activo">Activo Web MS</label>
                            <select id="ms-activo-web" class="form-control">
                                <option value="S">Sí</option>
                                <option value="N">No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-search" type="button" class="btn btn-primary">Buscar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
