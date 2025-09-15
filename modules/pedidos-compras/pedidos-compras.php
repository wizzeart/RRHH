<script>
    var action = '<?php print($action) ?>';
    var ped_lin = <?php print(((isset($data['items']) && count($data['items']) > 0) ? json_encode($data['items']) : '[]')) ?>;
    var ped_estado = '<?php print($data['xestado']) ?>';
    var lin_editable = 1;
    var rol = <?php print($app->rol) ?>;
</script>

<style>
    #modalUploadMultiple .modal-body {
        max-height: 70vh;
        /* Ajusta según necesidad */
        overflow-y: auto;
    }
</style>

<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">General</a></li>
                <li class=""><a href="#tab-documentos" data-toggle="tab" aria-expanded="false">Documentos</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <div id="alert-widget"></div>

    <div class="tab-content">
        <div class="tab-pane fade active in" id="tab-general">
            <div class="panel">

                <!-- BASIC FORM ELEMENTS -->
                <!--===================================================-->
                <div class="panel-body form-padding"><!-- form-horizontal -->
                    <!--Text Input-->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-pedido">NºPedido</label>
                                <input type="text" id="f-pedido" name="xpedido_id" class="form-control" placeholder="NºPedido" value="<?php if (isset($data['xpedido_id'])) print($data['xpedido_id']); ?>" disabled="true">
                                <small class="help-block">Nºde pedido asignado.</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-empresa">Empresa</label>
                                <select id="f-empresa" name="xempresa_id" class="form-control">
                                    <option value="">Selecciona Empresa</option>
                                    <?php foreach ($data_form['empresas'] as $k => $v) { ?>
                                        <option value="<?php print($v['xempresa_id']) ?>" <?php if ($data['xempresa_id'] == $v['xempresa_id']) print('selected'); ?>><?php print($v['xempresa']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Empresa seleccionada</small>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-proveedor">Proveedor</label>
                                </div>
                                <div class="input-group">
                                    <input data-id="<?php if (isset($data['xproveedor_id']) && $data['xproveedor_id'] != '') print($data['xproveedor_id']); ?>"
                                        data-cli="<?php if (isset($data['xproveedor']) && $data['xproveedor'] != '') print($data['xproveedor']); ?>"
                                        type="text" id="f-proveedor" class="form-control"
                                        value="<?php if (isset($data['xproveedor_sel']) && $data['xproveedor_sel'] != '') print($data['xproveedor_sel']); ?>">
                                    <span class="input-group-addon">
                                        <a id="proveedor-clear-autocomplete" href="javascript:void(0)"><i class="fa fa-times"></i></a>
                                    </span>
                                </div>
                                <small class="help-block">Selecciona un proveedor</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-fecha-registro">Fecha Registro</label>
                                </div>
                                <input id="f-fecha-registro" type="text" class="form-control" value="<?php if (isset($data['xfecha_registro_format'])) print($data['xfecha_registro_format']); ?>" disabled="true" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-fecha">Fecha</label>
                                </div>
                                <div id="f-fecha-pedido">
                                    <div class="input-group date">
                                        <input id="f-fecha" name="xfecha" type="text" class="form-control" value="<?php if (isset($data['xfecha_format'])) print($data['xfecha_format']); ?>" autocomplete="off">
                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                    </div>
                                    <small class="help-block">Seleccione fecha del pedido</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-fecha">Fecha Salida</label>
                                </div>
                                <div id="f-fecha-salida">
                                    <div class="input-group date">
                                        <input name="xfecha_salida" type="text" class="form-control" value="<?php if (isset($data['xfecha_salida_format'])) print($data['xfecha_salida_format']); ?>" autocomplete="off">
                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                    </div>
                                    <small class="help-block">Seleccione fecha salida del pedido de compra</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-eta">ETA</label>
                                </div>
                                <div id="f-eta">
                                    <div class="input-group date">
                                        <input name="xeta" type="text" class="form-control" value="<?php if (isset($data['xeta_format'])) print($data['xeta_format']); ?>" autocomplete="off">
                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                    </div>
                                    <small class="help-block">Seleccione fecha llegada prevista del pedido de compra</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-eta-real">ETA real</label>
                                </div>
                                <div id="f-eta-real">
                                    <div class="input-group date">
                                        <input name="xeta_real" type="text" class="form-control" value="<?php if (isset($data['xeta_real_format'])) print($data['xeta_real_format']); ?>" autocomplete="off">
                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                    </div>
                                    <small class="help-block">Seleccione fecha llegada real del pedido de compra</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-mbl">Nro.MBL</label>
                                <input type="text" id="f-mbl" name="xmbl" class="form-control" placeholder="NºMBL" value="<?php if (isset($data['xmbl'])) print($data['xmbl']); ?>">
                                <small class="help-block">NºMBL.</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-factura">Nro.Factura</label>
                                <input type="text" id="f-factura" name="xnum_factura" class="form-control" placeholder="NºFactura" value="<?php if (isset($data['xnum_factura'])) print($data['xnum_factura']); ?>">
                                <small class="help-block">NºMBL.</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-contenedor">Nro.Contenedor</label>
                                <input type="text" id="f-contenedor" name="xnum_contenedor" class="form-control" placeholder="NºContenedor" value="<?php if (isset($data['xnum_contenedor'])) print($data['xnum_contenedor']); ?>">
                                <small class="help-block">NºContenedor</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-oferta">Nro.Oferta</label>
                                <input type="text" id="f-oferta" name="xnum_oferta" class="form-control" placeholder="NºOferta" value="<?php if (isset($data['xnum_contenedor'])) print($data['xnum_oferta']); ?>">
                                <small class="help-block">NºOferta</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-transitario">Transitario</label>
                                <select id="f-transitario" name="xtransitario_id" class="form-control">
                                    <option value="">Selecciona Transitario</option>
                                    <?php foreach ($data_form['transitarios'] as $k => $v) { ?>
                                        <option value="<?php print($v['xtransitario_id']) ?>" <?php if ($data['xtransitario_id'] == $v['xtransitario_id']) print('selected'); ?>><?php print($v['xtransitario']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Transitario seleccionado</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-importadora">Importadora</label>
                                <select id="f-importadora" name="ximportadora_id" class="form-control">
                                    <option value="">Selecciona Importadora</option>
                                    <?php foreach ($data_form['importadoras'] as $k => $v) { ?>
                                        <option value="<?php print($v['ximportadora_id']) ?>" <?php if ($data['ximportadora_id'] == $v['ximportadora_id']) print('selected'); ?>><?php print($v['ximportadora']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Importadora seleccionada</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-destino">Naviera</label>
                                <select id="f-naviera" name="xnaviera_id" class="form-control">
                                    <option value="">Selecciona Naviera</option>
                                    <?php foreach ($data_form['navieras'] as $k => $v) { ?>
                                        <option value="<?php print($v['xnaviera_id']) ?>" <?php if ($data['xnaviera_id'] == $v['xnaviera_id']) print('selected'); ?>><?php print($v['xnaviera']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Naviera seleccionada</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-puerto">Puerto</label>
                                <select id="f-puerto" name="xpuerto_id" class="form-control">
                                    <option value="">Selecciona Puerto</option>
                                    <?php foreach ($data_form['puertos'] as $k => $v) { ?>
                                        <option value="<?php print($v['xpuerto_id']) ?>" <?php if ($data['xpuerto_id'] == $v['xpuerto_id']) print('selected'); ?>><?php print($v['xpuerto']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Puerto seleccionado</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="f-estado">Estado</label>
                                <div class="input-group mar-btm">
                                    <input type="text" id="f-estado" class="form-control" placeholder="Estado" value="<?php if (isset($data['xestado_desc'])) print($data['xestado'] . ' - ' . $data['xestado_desc']); ?>" disabled="true">
                                    <div class="input-group-btn">
                                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false">
                                            Cambiar <i class="dropdown-caret fa fa-caret-down"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <?php foreach ($data_form['estados'] as $k => $v) { ?>
                                                <li><a data-estado="<?php print($v['xestado_id']) ?>" class="chg-estado" href="javascript:void(0);"><?php print($v['xestado_id'] . ' - ' . $v['xestado']) ?></a></li>
                                            <?php } ?>
                                            <li class="divider"></li>
                                            <li><a href="javascript:void(0);">No cambiar nada</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <small class="help-block">Estado actual del pedido.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Observaciones del pedido</label>
                                <textarea id="f-obs" name="xobs" class="form-control" rows="5"><?php if (isset($data['xobs'])) print($data['xobs']) ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Observaciones del embarque</label>
                                <textarea id="f-obs-embarque" name="xobs_embarque" class="form-control" rows="5"><?php if (isset($data['xobs_embarque'])) print($data['xobs_embarque']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <h3>
                                    Detalle de las líneas
                                    <a style="font-size: 15px;display: none;" id="btn-cancel-lin" href="javascript:void(0);" title="Cancelar Línea">
                                        <i class="fa fa-minus-circle"></i>
                                    </a>
                                </h3>
                                <div id="gridbox" style="width:100%;height:300px;background-color:white;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-10"></div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label text-right" for="f-importe-total">Importe</label>
                                <input type="text" id="f-importe-total" class="form-control text-right" placeholder="Importe" value="<?php if (isset($data['ximporte'])) print($data['ximporte']); ?>" disabled="true">
                                <small class="help-block text-right">Importe total del pedido.</small>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- =================================================== -->
                <!-- END BASIC FORM ELEMENTS -->
            </div>
        </div> <!-- GENERAL -->
        <div class="tab-pane fade" id="tab-documentos">
            <div class="panel">
                <div class="panel-body form-padding">
                    <div class="row">
                        <div class="col-md-12">
                            <h3>
                                Documentos asociados
                                <a id="btn-add-documento" class="fa fa-plus-circle" href="javascript:void(0);" title="Añadir documento"></a>
                            </h3>
                            <div class="table-responsive">
                                <table id="tbl-documentos" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Fecha subida</th>
                                            <th>Fecha descarga</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['documentos'] as $k => $v) { ?>
                                            <tr data-id="<?php print($v['xdoc_id']) ?>">
                                                <td><?php print($v['xtipo']) ?></td>
                                                <td><?php print($v['xdescripcion']) ?></td>
                                                <td class="text-center">
                                                    <a class="btn btn-xs btn-danger del-componente" href="javascript:void(0)" title="Eliminar Componente"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="img-content" style="display:none;">
                        <h3>Visualización de inspección</h3>
                        <div id="list-images" class="row">
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- DOCUMENTOS -->
    </div>

    <div class="panel-footer text-center">
        <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
        <button id="btn-save" class="btn btn-info icon-lg" type="button">
            <i class="fa fa-check"></i>
            Guardar
        </button>
        <button id="btn-print" class="btn btn-warning btn-icon icon-lg fa fa-print" alt="Imprimir Pedido" title="Imprimir Pedido"></button>
        <!-- <button id="btn-copy" class="btn btn-purple btn-icon icon-lg fa fa-copy" alt="Duplicar Pedido" title="Duplicar Pedido"></button> -->
        <button id="btn-close" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-undo"></i>
            Cerrar
        </button>
        <!--
        <button id="btn-back" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-undo"></i>
            Volver
        </button>
        -->
        <button id="btn-new" class="btn btn-warning icon-lg" type="button">
            <i class="fa fa-plus"></i>
            Nuevo
        </button>
        <!--
        <button id="btn-movs" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-truck"></i>
            Movimientos
        </button>
        <button id="btn-his" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-history"></i>
            Histórico
        </button>
        -->
    </div>
</div>

<!-- modales -->
<div id="mdlChgEstado" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Cambiar Estado</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label>Estado actual del pedido</label>
                        <input type="text" id="mdl-ce-estado-old" class="form-control" value="" disabled="true">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label>Estado nuevo al que se quiere cambiar</label>
                        <input type="hidden" id="mdl-ce-estado-id" value="">
                        <input type="text" id="mdl-ce-estado-new" class="form-control" value="" disabled="true">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea id="mdl-ce-obs" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <img id="img-loading-chg-estado" class="hidden" src="img/spinners/282.gif" />
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-chg-estado" type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div id="modalSearchProduct" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Buscar Componente</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label>Introduzca aquí el componente a buscar.</label>
                        <select id="msp-art" class="form-control" data-placeholder="Seleccione artículos">
                            <option value="">Selecciona componente</option>
                            <?php foreach ($data_form['componentes'] as $k => $v) { ?>
                                <option data-precio="<?php print($v['xcoste']) ?>" value="<?php print($v['xcomponente_id']) ?>"><?php print($v['xcomponente_id'] . ' - ' . $v['xcomponente']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-add-product-search" type="button" class="btn btn-primary">Seleccionar Producto</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div id="modalMovs" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Movimientos del Pedido</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="tbl-movs" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Doc.</th>
                                        <th>Artículo</th>
                                        <th>Componente</th>
                                        <th>Cantidad</th>
                                        <th>Almacén</th>
                                        <th>Concepto</th>
                                        <th>Stock Antes</th>
                                        <th>Stock Después</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalHis" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Histórico del Pedido</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="tbl-his" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Usuario</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalUpload" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Asociar Documento</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Seleccione un documento que quiere asociar al pedido de compra.</p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label text-right" for="mu-desc">Descripción</label>
                            <input type="text" id="mu-desc" class="form-control" placeholder="Descripción" value="" />
                            <input type="hidden" id="mu-filename" value="" />
                        </div>
                    </div>
                </div>
                <div class="list-group dropzone-img">
                    <div class="list-group-item dropzone-container">
                        <div class="form-group">
                            <form action="file-upload-doc.php" class="dropzone dz-clickable" id="imageGalleryDropzone">
                                <input id="dst-doc" type="hidden" name="dst" value="" />
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
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button id="btn-save-doc" type="button" class="btn btn-primary">Guardar Documento</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalUploadMultiple" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Asociar Documentos</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Seleccione uno o varios documentos que quiere asociar al pedido de compra.</p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label text-right" for="mu-desc">Descripción</label>
                            <input type="text" id="mu-desc-multiple" class="form-control" placeholder="Descripción" value="" />
                            <input type="hidden" id="mu-filename-multiple" value="" />
                        </div>
                    </div>
                </div>
                <div class="list-group dropzone-img">
                    <div class="list-group-item dropzone-container">
                        <div class="form-group">
                            <form action="file-upload-doc-multiple.php" class="dropzone dz-clickable" id="imageGalleryDropzoneMultiple">
                                <input id="dst-doc-multiple" type="hidden" name="dst" value="" />
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
                            <div class="gallery-container-multiple">
                                <!-- list image gallery -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button id="btn-save-doc-multiple" type="button" class="btn btn-primary">Guardar Documento</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalTipo" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Tipo de documento</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Seleccione el tipo de documento que quiere asociar al pedido de compra.</p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="f-transitario">Tipo de documento</label>
                            <select id="mt-tipo" class="form-control">
                                <option value="">Selecciona tipo de documento</option>
                                <?php foreach ($documentos_tipos as $k => $v) { ?>
                                    <option data-multiple="<?php print($v['multiple']) ?>" value="<?php print($k) ?>"><?php print($v['name']) ?></option>
                                <?php } ?>
                            </select>
                            <small class="help-block">Transitario seleccionado</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button id="btn-select-tipo" type="button" class="btn btn-primary">Seleccionar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->