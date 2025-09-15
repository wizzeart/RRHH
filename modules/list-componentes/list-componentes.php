<script>
    //var marca = '<?php print($_GET['id']) ?>';
    var rol = '<?php print($app->rol) ?>';
    var almacenes =<?php print(json_encode($data_form['almacenes'])) ?>;
</script>

<div class="panel">
    <div class="form-control">
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Componente" title="Añadir Nuevo Componente"></button>
        <!-- <button id="btn-dl-pdf" class="btn btn-purple btn-icon icon-lg fa fa-download" alt="Descargar Catálogo Revendedor" title="Descargar Catálogo Revendedor"></button> -->
    </div>
</div>

<!--Basic Toolbar-->
<!--===================================================-->
<!-- atributos quitados del tag table:   --> 
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
            data-show-footer="true"
            data-side-pagination="client"
            data-show-fullscreen="true"
            >
            <thead>
                <tr>
                    <th data-field="xcomponente_id" data-sortable="true" data-footer-formatter="idFormatter">ID</th>
                    <th data-field="xcomponente" data-sortable="true" data-formatter="formatoDescripcion">Descripción</th>
                    <th data-field="xactivo" data-align="center" data-formatter="formatoActivo" data-sortable="false">Activo</th>
                    <th data-field="xstock" data-align="center" data-sortable="true" data-footer-formatter="stockFormatterFooter">Stock</th>
                    <th data-field="xventas7" data-align="center" data-sortable="true">Ventas 7 días</th>
                    <?php if ($app->rol != 3) { ?>
                        <th data-field="xcoste_format" data-align="right" data-sortable="false" data-footer-formatter="stockValoradoFormatterFooter">Coste</th>
                    <?php } ?>
                    <th data-field="xcomponente_id" data-align="center" data-sortable="false" data-formatter="formatoToolbar"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->


<!-- modales -->
<div id="modalAddStock" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Añadir Stock</h4>    
            </div>
            <div class="modal-body">
                <input type="hidden" id="mas-componente-id" value=""/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="f-preventa">Almacén</label>
                            <select id="mas-almacen" class="form-control">
                                <option value="0">Seleccione un almacén</option>
                            </select>
                            <small class="help-block">Seleccione un almacén</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Componente</label>
                            <input id="mas-componente" type="text" class="form-control" value="" readonly="true">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="mas-cantidad">Cantidad</label>
                            <input id="mas-cantidad" type="text" class="form-control text-right" value="0" autocomplete="off">
                            <small class="help-block">Indique la cantidad en positivo(entrada) o en negativo(salida)</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="mas-coste">Precio Coste</label>
                            <input id="mas-coste" type="text" class="form-control text-right" value="0" autocomplete="off">
                            <small class="help-block">Indique el precio de coste del componente</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="mas-concepto">Concepto</label>
                            <input id="mas-concepto" type="text" class="form-control" value="" autocomplete="off">
                            <small class="help-block">Indique el concepto del a entrada/salida</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-add-stock" type="button" class="btn btn-primary">Añadir</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div id="modalTraspaso" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Traspaso entre almacenes</h4>    
            </div>
            <div class="modal-body">
                <input type="hidden" id="mt-componente-id" value=""/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="f-almacen-src">Almacén origen</label>
                            <select id="mt-almacen-src" class="form-control">
                                <option value="0">Seleccione un almacén origen</option>
                            </select>
                            <small class="help-block">Seleccione un almacén origen</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="f-almacen-dst">Almacén destino</label>
                            <select id="mt-almacen-dst" class="form-control">
                                <option value="0">Seleccione un destino</option>
                            </select>
                            <small class="help-block">Seleccione un almacén destino</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Componente</label>
                            <input id="mt-componente" type="text" class="form-control" value="" readonly="true">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="mt-cantidad">Cantidad</label>
                            <input id="mt-cantidad" type="text" class="form-control text-right" value="0" autocomplete="off">
                            <small class="help-block">Indique la cantidad en positivo(entrada) o en negativo(salida)</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="mas-coste">Precio Coste</label>
                            <input id="mt-coste" type="text" class="form-control text-right" value="0" autocomplete="off">
                            <small class="help-block">Indique el precio de coste del componente</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="mas-concepto">Concepto</label>
                            <input id="mt-concepto" type="text" class="form-control" value="" autocomplete="off">
                            <small class="help-block">Indique el concepto de la entrada/salida</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-traspaso" type="button" class="btn btn-primary">Añadir</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
