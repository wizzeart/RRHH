<script>
    //var marca = '<?php print($_GET['id']) ?>';
    var rol = '<?php print($app->rol) ?>';
    var punto_venta = '<?php print($app->punto_venta) ?>';
</script>
<div class="panel">
    <div class="row">
        <div class="col-md-6">
            <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Pedido" title="Añadir Nuevo Pedido"></button>
            <!-- 
            <button id="btn-dl-xls" class="btn btn-purple btn-icon icon-lg fa fa-download" alt="Descargar XLS" title="Descargar XLS"></button>
            <button id="btn-dl-doc" class="btn btn-warning btn-icon icon-lg fa fa-print" alt="Imprimir Pedido" title="Imprimir Pedidos"></button>
            -->
        </div>
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
            data-url="api-app.php?module=pedidos&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="xpedido_id"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="100"
            data-pagination="true"
            data-show-pagination-switch="false"
            data-search-accent-neutralise="true"
            >
            <thead>
                <tr>
                    <th data-field="xpedido_id" data-sortable="true" data-formatter="formatoPedido">NºPedido</th>
                    <th data-field="xfecha_format" data-formatter="formatoFechas" data-sortable="true">Fecha</th>
                    <th data-field="xcliente" data-formatter="formatoNivel" data-sortable="true">Cliente</th>
                    <th data-field="xpedido_id" data-formatter="formatoProducto" data-sortable="true">Producto</th>
                    <th data-field="ximporte" data-align="right" data-sortable="true">Importe</th>
                    <th data-field="xestado_desc" data-align="center" data-formatter="formatoEstado" data-sortable="true">Estado</th>
                    <th data-field="xtransaccion" data-align="center" data-formatter="formatoTransaccion" data-sortable="true">Transacción</th>
                    <th data-field="xcomercial" data-align="center" data-sortable="true">Comercial</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar"></th>
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-revendedor">Agencia/Punto de Venta</label>
                            </div>
                            <select id="ms-revendedor" class="form-control" data-placeholder="Todos los clientes" style="margin-bottom: 0px;">
                                <option value="">Todas las agencias/puntos de ventas</option>
                                <?php foreach ($data_form['revendedores'] as $k => $v) { ?>
                                    <option value="<?php print($v['xrevendedor_id']) ?>"><?php print($v['xrevendedor_id'] . ' - ' . $v['xrevendedor']) ?></option>
                                <?php } ?>
                            </select>
                            <small class="help-block">Selecciona la agencia/punto de venta con el que quiere realizar la búsqueda</small>
                        </div>
                    </div>
                </div>
                <div class="row">
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
                <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-search" type="button" class="btn btn-primary">Buscar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="modalSearchFinalizados" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Búsqueda Pedidos Finalizados</h4>    
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="msf-fecha-desde">Fecha Desde</label>
                            </div>
                            <div id="msf-fecha-desde">
                                <div class="input-group date">
                                    <input type="text" class="form-control" value="<?php if (isset($data_form['fecha-ini-finalizados'])) print($data_form['fecha-ini-finalizados']); ?>" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                </div>
                                <small class="help-block">Seleccione fecha desde</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="msf-fecha-hasta">Fecha Hasta</label>
                            </div>
                            <div id="msf-fecha-hasta">
                                <div class="input-group date">
                                    <input type="text" class="form-control" value="<?php if (isset($data_form['fecha-fin-finalizados'])) print($data_form['fecha-fin-finalizados']); ?>" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                </div>
                                <small class="help-block">Seleccione fecha hasta</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-resultados">Resultados</label>
                            </div>
                            <select id="msf-resultados" class="form-control" data-placeholder="Todos los comerciales" style="margin-bottom: 0px;">
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
                <button id="btn-do-search-finalizados" type="button" class="btn btn-primary">Buscar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalSearchEntregados" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Búsqueda Pedidos Entregados</h4>    
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mse-fecha-desde">Fecha Desde</label>
                            </div>
                            <div id="mse-fecha-desde">
                                <div class="input-group date">
                                    <input type="text" class="form-control" value="<?php if (isset($data_form['fecha-ini-entregados'])) print($data_form['fecha-ini-entregados']); ?>" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                </div>
                                <small class="help-block">Seleccione fecha desde</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mse-fecha-hasta">Fecha Hasta</label>
                            </div>
                            <div id="mse-fecha-hasta">
                                <div class="input-group date">
                                    <input type="text" class="form-control" value="<?php if (isset($data_form['fecha-fin-entregados'])) print($data_form['fecha-fin-entregados']); ?>" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                </div>
                                <small class="help-block">Seleccione fecha hasta</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="ms-resultados">Resultados</label>
                            </div>
                            <select id="mse-resultados" class="form-control" data-placeholder="Todos los comerciales" style="margin-bottom: 0px;">
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
                <button id="btn-do-search-entregados" type="button" class="btn btn-primary">Buscar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
