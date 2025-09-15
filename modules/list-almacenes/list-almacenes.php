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
            data-url="api-app.php?module=almacenes&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="xalmacen_id"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" 
            data-show-pagination-switch="false">
            <thead>
                <tr>
                    <th data-field="xalmacen_id" data-sortable="true">ID</th>
                    <th data-field="xalmacen" data-sortable="true">Almacén</th>
                    <th data-field="xalmacen_web" data-sortable="true">Descripción Web</th>
                    <th data-field="xalmacen_id" data-formatter="formatoMunicipios" data-sortable="true">Municipios Asociados</th>
                    <th data-field="xactivo" data-align="center" data-formatter="formatoActivo" data-sortable="false">Activo</th>
                    <th data-field="xactivo_web" data-align="center" data-formatter="formatoActivoWeb" data-sortable="false">Activo Web MS</th>
                    <th data-field="xactivo_web_an" data-align="center" data-formatter="formatoActivoWebAN" data-sortable="false">Activo Web AN</th>
                    <th data-field="xalmacen_id" data-align="center" data-sortable="false" data-formatter="formatoToolbar"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->

<!-- modales -->
<div id="modalSearch" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Búsqueda Avanzada</h4>    
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-fecha-desde">Fecha Desde</label>
                            </div>
                            <div id="ms-fecha-desde">
                                <div class="input-group date">
                                    <input type="text" class="form-control" value="<?php if (isset($data_form['fecha-ini'])) print($data_form['fecha-ini']); ?>" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                </div>
                                <small class="help-block">Seleccione fecha desde</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-fecha-hasta">Fecha Hasta</label>
                            </div>
                            <div id="ms-fecha-hasta">
                                <div class="input-group date">
                                    <input type="text" class="form-control" value="<?php if (isset($data_form['fecha-fin'])) print($data_form['fecha-fin']); ?>" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                </div>
                                <small class="help-block">Seleccione fecha hasta</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-estado">Estado</label>
                            </div>
                            <select id="ms-estado" class="form-control" data-placeholder="Todos los comerciales" style="margin-bottom: 0px;">
                                <option value="">Todos</option>
                                <?php foreach ($data_form['estados'] as $k => $v) { ?>
                                    <option value="<?php print($v['xestado_id']) ?>"><?php print($v['xestado_id'] . ' - ' . $v['xestado']) ?></option>
                                <?php } ?>

                            </select>
                            <small class="help-block">Selecciona el estado del pedido con el que quiere realizar la búsqueda</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-cliente">Cliente</label>
                            </div>
                            <select id="ms-cliente" class="form-control" data-placeholder="Todos los clientes" style="margin-bottom: 0px;">
                                <option value="">Todos los clientes</option>
                                <?php foreach ($data_form['clientes'] as $k => $v) { ?>
                                    <option value="<?php print($v['xcliente_id']) ?>"><?php print($v['xcliente_id'] . ' - ' . $v['xcliente']) ?></option>
                                <?php } ?>
                            </select>
                            <small class="help-block">Selecciona el cliente con el que quiere realizar la búsqueda</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-pedido">NºPedido</label>
                            </div>
                            <input id="ms-pedido" type="text" class="form-control" value="" autocomplete="off">
                            <small class="help-block">Búsqueda por Nº de Pedido o Pedido Web</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-resultados">Resultados</label>
                            </div>
                            <select id="ms-resultados" class="form-control" data-placeholder="Todos los comerciales" style="margin-bottom: 0px;">
                                <option value="50">50</option>
                                <option value="100" selected>100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                            <small class="help-block">Máximo resultados devueltos en la consulta de búsqueda</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-search" type="button" class="btn btn-primary">Buscar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalAlmacen" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Añadir Almacén</h4>    
            </div>
            <div class="modal-body">
                <input id="ma-almacen-id" type="hidden" value="">
                <input id="ma-row" type="hidden" value="">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ma-almacen">Almacén</label>
                            </div>
                            <input id="ma-almacen" type="text" class="form-control" value="" autocomplete="off">
                            <small class="help-block">Nombre del almacén</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ma-almacen-web">Descripción Web</label>
                            </div>
                            <input id="ma-almacen-web" type="text" class="form-control" value="" autocomplete="off">
                            <small class="help-block">Descripción del almacén usada en la web de mandasaldo</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label text-block" for="ma-municipios">Municipios de <strong>LA HABANA</strong></label>
                            <select id="ma-municipios" class="form-control" multiple="true" data-placeholder="Seleccione Municipios">
                                <?php foreach ($data_form['municipios'] as $k => $v) { ?>
                                    <option value="<?php print($v['xmunicipio']) ?>"><?php print(str_replace('_', ' ', $v['xmunicipio'])) ?></option>
                                <?php } ?>
                            </select>
                            <small class="help-block">Selecciona los municipios de La Habana para asociarlos a este almacén.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-save-almacen" type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->