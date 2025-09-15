<script>
    action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">General</a></li>
                <li class=""><a href="#tab-movimientos" data-toggle="tab" aria-expanded="true">Movimientos</a></li>
                <li class=""><a href="#tab-compras" data-toggle="tab" aria-expanded="true">Compras</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade active in" id="tab-general">
            <div class="panel">
                <!-- BASIC FORM ELEMENTS -->
                <!--===================================================-->
                <form class="panel-body orm-padding"><!-- form-horizontal -->
                    <!--Text Input-->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-title">Código Componente</label>
                                <input type="text" id="f-componente-id" name="xcomponente_id" class="form-control" placeholder="ID Componente" value="<?php if (isset($data['xcomponente_id'])) print($data['xcomponente_id']); ?>" disabled="true">
                                <small class="help-block">Código de Componente</small>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="control-label" for="f-componente">Descripción</label>
                                <input type="text" id="f-componente" name="xcomponente" class="form-control" placeholder="Descripción Componente" value="<?php if (isset($data['xcomponente'])) print($data['xcomponente']); ?>" autocomplete="off">
                                <small class="help-block">Descripción del Componente</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-activo">Activo</label>
                                <select id="f-activo" name="xactivo" class="form-control">
                                    <option value="S" <?php if (isset($data['xactivo']) && $data['xactivo'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xactivo']) && $data['xactivo'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si está activo</small>
                            </div>
                        </div>
                    </div>

                    <div class="row<?php if (!in_array($app->rol, array('1', '5', 12))) print(' hidden') ?>">
                        <div class="col-md-2<?php if (!in_array($app->rol, array('1'))) print(' hidden') ?>">
                            <div class="form-group">
                                <label class="control-label" for="f-precio">Precio</label>
                                <input type="text" id="f-precio" name="xprecio" class="form-control" placeholder="Precio Componente" value="<?php if (isset($data['xprecio'])) print($data['xprecio']); ?>">
                                <small class="help-block">Precio del Componente</small>
                            </div>
                        </div>
                        <div class="col-md-2<?php if (!in_array($app->rol, array('1'))) print(' hidden') ?>">
                            <div class="form-group">
                                <label class="control-label" for="f-coste">Coste</label>
                                <input type="text" id="f-coste" name="xcoste" class="form-control" placeholder="Coste Componente" value="<?php if (isset($data['xcoste'])) print($data['xcoste']); ?>">
                                <small class="help-block">Coste del Componente</small>
                            </div>
                        </div>
                        <div class="col-md-2<?php if (!in_array($app->rol, array('1', '5'))) print(' hidden') ?>">
                            <div class="form-group">
                                <label class="control-label" for="f-stock-min">Stock Mínimo</label>
                                <input type="text" id="f-stock-min" name="xstock_min" class="form-control" placeholder="Stock mínimo" value="<?php if (isset($data['xstock_min'])) print($data['xstock_min']); ?>">
                                <small class="help-block">Stock mínimo del Componente</small>
                            </div>
                        </div>
                        <div class="col-md-3<?php if (!in_array($app->rol, array('1'))) print(' hidden') ?>">
                            <div class="form-group">
                                <label class="control-label" for="f-stock-control">Control Stock</label>
                                <select id="f-stock-control" name="xstock_control" class="form-control">
                                    <option value="S" <?php if (isset($data['xstock_control']) && $data['xstock_control'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xstock_control']) && $data['xstock_control'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si se tendrá en cuenta el stock a cero para desactivar los productos asociados a este componente</small>
                            </div>
                        </div>
                        <div class="col-md-3<?php if (!in_array($app->rol, array(1, 12))) print(' hidden') ?>">
                            <div class="form-group">
                                <label class="control-label" for="f-archivado">Archivado</label>
                                <select id="f-archivado" name="xarchivado" class="form-control">
                                    <option value="S" <?php if (isset($data['xarchivado']) && $data['xarchivado'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xarchivado']) && $data['xarchivado'] == 'N') print('selected'); ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-proveedor">Proveedor</label>
                                <select id="f-proveedor" name="xproveedor_id" class="form-control">
                                    <option value="0" <?php if (isset($data['xproveedor_id']) && $data['xproveedor_id'] == '0') print('selected'); ?>>Ninguno</option>
                                    <?php foreach ($data_form['proveedores'] as $k => $v) { ?>
                                        <option value="<?php print($v['xproveedor_id']) ?>" <?php if ($data['xproveedor_id'] == $v['xproveedor_id']) print('selected'); ?>><?php print($v['xproveedor']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Proveedor por defecto para realizar los envíos de productos que no sean recargas</small>
                            </div>
                        </div>
                    </div>

                    <?php if ($app->rol == 1) { ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-user-modify">Usuario Modificado</label>
                                    <input type="text" id="f-user-modify" class="form-control" placeholder="Url Amigable" value="<?php if (isset($data['xusuario_modificado'])) print($data['xusuario_modificado']); ?>" autocomplete="off" disabled="true">
                                    <small class="help-block">Usuario que ha modificado la ficha del artículo</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-user-modify">Fecha Modificado</label>
                                    <input type="text" id="f-date-modify" class="form-control" placeholder="Url Amigable" value="<?php if (isset($data['xdatemodif_format'])) print($data['xdatemodif_format']); ?>" autocomplete="off" disabled="true">
                                    <small class="help-block">Fecha que ha modificado la ficha del artículo</small>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h3>
                                Stocks del Componente
                            </h3>
                            <div class="table-responsive">
                                <table id="tbl-stocks-componentes" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Almacén</th>
                                            <th class="text-right">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['xstocks_componente'] as $k => $v) { ?>
                                            <tr>        
                                                <td><?php print($v['xalmacen_id'] . ' - ' . $v['xalmacen']) ?></td>
                                                <td class="text-right"><?php print($v['xstock']) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h3>
                                Componente en Artículos
                            </h3>
                            <div class="table-responsive">
                                <table id="tbl-stocks-componentes" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Artículo</th>
                                            <th class="text-right">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['articulos-componentes'] as $k => $v) { ?>
                                            <tr>        
                                                <td><?php print($v['xarticulo_id'] . ' - ' . $v['xarticulo']) ?></td>
                                                <td class="text-right"><?php print($v['xcantidad']) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- =================================================== -->
                <!-- END BASIC FORM ELEMENTS -->
            </div>
        </div> <!-- GENERAL -->
        <div class="tab-pane fade" id="tab-movimientos">
            <div class="panel">
                <div class="row">
                    <div class="col-md-12">
                        <h3>
                            Movimientos del Componente
                            <div class="btn-group">
                                <button class="btn btn-default btn-active-pink dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button" aria-expanded="false">
                                    <span id="btn-filtro-text">Todos los almacenes</span> <i class="dropdown-caret fa fa-caret-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="select-almacen" data-alm="">Todos los almacenes</a></li>
                                    <!-- <li><a href="javascript:void(0)" class="select-almacen" data-alm="Habana">Almacén La Habana</a></li> -->
                                    <!-- <li><a href="javascript:void(0)" class="select-almacen" data-alm="Santa Clara">Almacén Santa Clara</a></li> -->
                                    <?php foreach ($data_form['almacenes'] as $k => $v) { ?>
                                        <li><a href="javascript:void(0)" class="select-almacen" data-alm="<?php print($v['xalmacen']) ?>"><?php print($v['xalmacen']) ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-default btn-active-pink dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button" aria-expanded="false">
                                    <span id="btn-filtro-text-tipo">Todos los tipos</span> <i class="dropdown-caret fa fa-caret-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="select-tipo" data-tipo="">Todos los tipos</a></li>
                                    <li><a href="javascript:void(0)" class="select-tipo" data-tipo="Entrada">Entradas</a></li>
                                    <li><a href="javascript:void(0)" class="select-tipo" data-tipo="Salida">Salidas</a></li>
                                </ul>
                            </div>
                        </h3>
                        <div class="table-responsive">
                            <table id="tbl-movimientos-componentes" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Movimiento</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th class="text-right">Cantidad</th>
                                        <th>Almacén</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['movimientos'] as $k => $v) { ?>
                                        <tr>        
                                            <td><?php print($v['xmov_id']) ?></td>
                                            <td><?php print(($v['xtype'] == 'E') ? 'Entrada' : 'Salida') ?></td>
                                            <td><?php print($v['xfecha_format']) ?></td>
                                            <td><?php print($v['xconcepto']) ?></td>
                                            <td class="text-right"><?php print($v['xcantidad']) ?></td>
                                            <td><?php print($v['xalmacen']) ?></td>
                                            <td><?php print($v['xusuario']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- ENTRADAS-SALIDAS -->

                <div class="row hidden">
                    <div class="col-md-12">
                        <h3>
                            Entradas del Componente
                        </h3>
                        <div class="table-responsive">
                            <table id="tbl-entradas-componentes" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Movimiento</th>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th class="text-right">Cantidad</th>
                                        <th>Almacén</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['xentradas_componente'] as $k => $v) { ?>
                                        <tr>        
                                            <td><?php print($v['xmov_id']) ?></td>
                                            <td><?php print($v['xfecha_format']) ?></td>
                                            <td><?php print($v['xconcepto']) ?></td>
                                            <td class="text-right"><?php print($v['xcantidad']) ?></td>
                                            <td><?php print($v['xalmacen_id']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row hidden">
                    <div class="col-md-12">
                        <h3>
                            Salidas del Componente
                        </h3>
                        <div class="table-responsive">
                            <table id="tbl-entradas-componentes" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Movimiento</th>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th class="text-right">Cantidad</th>
                                        <th>Almacén</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['xsalidas_componente'] as $k => $v) { ?>
                                        <tr>        
                                            <td><?php print($v['xmov_id']) ?></td>
                                            <td><?php print($v['xfecha_format']) ?></td>
                                            <td><?php print($v['xconcepto']) ?></td>
                                            <td class="text-right"><?php print($v['xcantidad']) ?></td>
                                            <td><?php print($v['xalmacen_id']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- MOVIMIENTOS -->
        <div class="tab-pane fade" id="tab-compras">
            <div class="panel">

            </div>
        </div> <!-- COMPRAS -->
    </div>
    <div class="panel-footer text-center">
        <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
        <button id="btn-save" class="btn btn-info icon-lg" type="button">
            <i class="fa fa-check"></i>
            Guardar
        </button>
        <button id="btn-back" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-undo"></i>
            Cerrar pestaña
        </button>
        <button id="btn-new" class="btn btn-warning icon-lg" type="button">
            <i class="fa fa-plus"></i>
            Nuevo
        </button>
    </div>
</div>

<div id="modalUpload" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Asociar Imagen</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Seleccione un documento que quiere asociar a la ficha del cliente.</p>
                <div class="list-group dropzone-img">
                    <div class="list-group-item dropzone-container">
                        <div class="form-group">
                            <form action="file-upload.php" class="dropzone dz-clickable" id="imageGalleryDropzone">
                                <input id="dst-imagen" type="hidden" name="dst" value=""/>
                                <!-- <input type="hidden" name="categoria_id" value="<?php //print($data['xcategoria_id'])                                               ?>"/> -->
                                <div class="dz-message clearfix">
                                    <div>
                                        <i class="fa fa-image fa-3x"></i>
                                    </div>
                                    <span>Haz click para seleccionar<br>máx: 10MB</span>
                                    <div class="hover">
                                        <i class="icon-download"></i>
                                        <span>DROP FILES HERE</span>
                                    </div>
                                </div>
                            </form>                           
                        </div>
                    </div>
                    <div class="list-group-item preview-container">
                        <div class="form-group">
                            <div class="gallery-container">
                                <!-- list image gallery -->
                            </div>                       
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Cerrar</button>
                <!-- <button id="btn-save-temperatura" type="button" class="btn btn-lg btn-primary">Guardar Imagen</button> -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->