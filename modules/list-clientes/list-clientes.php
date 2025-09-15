<script>
    //var marca = '<?php print($_GET['id']) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>

<div class="panel">
    <div class="form-control">
        <!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo Cliente" title="Añadir Nuevo Cliente"></button>
        <?php if ($app->rol == '1') { ?>
            <button id="btn-dl-xls" class="btn btn-purple btn-icon icon-lg fa fa-download" alt="Descarga de Clientes para mailing" title="Descarga de Clienets para mailing"></button>
        <?php } ?>
        <button id="btn-ver-altas" class="btn btn-warning btn-icon icon-lg fa fa-star" alt="Altas por semanas" title="Altas por semanas"></button>
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
            data-url="api-app.php?module=clientes&method=list"
            data-search="false"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="id"
            data-page-list="[20, 50, 100]"
            data-page-size="100"
            data-pagination="true" 
            data-show-pagination-switch="false"
            data-search-accent-neutralise="true"
            >
            <thead>
                <tr>
                    <th data-field="xcliente_id" data-sortable="true">ID</th>
                    <th data-field="xcliente" data-formatter="formatoNivel" data-sortable="true">Nombre</th>
                    <th data-field="xemail" data-sortable="true">Email</th>
                    <th data-field="xmovil" data-formatter="formatoMovil" data-sortable="true">Móvil</th>
                    <th data-field="xactivo" data-align="center" data-formatter="formatoActivo" data-sortable="false">Activo</th>
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
                <div class="well">
                    Los campos a buscar serán por <strong>nombre completo del cliente</strong>, <strong>primer nombre</strong>, <strong>segundo nombre</strong>, <strong>primer apellido</strong>, <strong>segundo nombre</strong>, <strong>email</strong>, <strong>móvil</strong>, <strong>fecha de nacimiento</strong>, buscará parecido en el contenido. Otros campos a buscar por identidad, es decir, que sea igual al valor es por <strong>ID de cliente</strong>.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="ms-texto">Texto a buscar</label>
                            <div class="input-group">
                                <input id="ms-texto" type="text" class="form-control" value="" autocomplete="off">
                                <span class="input-group-btn">
                                    <button id="btn-empty-ms-texto" class="btn btn-default" type="button"><i class="fa fa-times"></i></button>
                                </span>
                            </div>
                            <small class="help-block">Indique el texto a buscar</small>
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

<div id="modalAltas" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-xs">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Altas de clientes</h4>    
            </div>
            <div class="modal-body">
                <div class="well">
                    Tabla que muestra <strong>las altas de clientes en la web</strong> por rangos de fechas de las últimas semanas. 
                </div>

                <div class="table-responsive">
                    <table id="tbl-altas-clientes" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fechas</th>
                                <th class="text-right">
                                    Total registros
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>14</td>
                                <td class="text-right">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

