<script>
    action = '<?php print($action) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">Partes de Trabajo</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Information</h4><p style='width:150px'>This is an information bubble to help the user.</p>" data-html="true" title=""></a>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade active in" id="tab-general">
            <div class="panel">
                <!-- BASIC FORM ELEMENTS -->
                <!--===================================================-->
                <div class="panel-body orm-padding"><!-- form-horizontal -->
                    <!--Text Input-->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-parte">NºParte</label>
                                <input type="text" id="f-parte-id" name="xparte_id" class="form-control" placeholder="ID Artículo" value="<?php if (isset($data['xarticulo_id'])) print($data['xarticulo_id']); ?>" disabled="true">
                                <small class="help-block">NºParte</small>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="control-label" for="f-articulo">Descripción</label>
                                <input type="text" id="f-articulo" name="xarticulo" class="form-control" placeholder="Descripción Artículo" value="<?php if (isset($data['xarticulo'])) print($data['xarticulo']); ?>" autocomplete="off">
                                <small class="help-block">Descripción del Artículo</small>
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

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-preventa">Preventa</label>
                                <select id="f-activo" name="xpreventa" class="form-control">
                                    <option value="S" <?php if (isset($data['xpreventa']) && $data['xpreventa'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xpreventa']) && $data['xpreventa'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si se considera preventa</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-precio">Precio</label>
                                <input type="text" id="f-precio" name="xprecio" class="form-control" placeholder="Precio Artículo" value="<?php if (isset($data['xprecio'])) print($data['xprecio']); ?>">
                                <small class="help-block">Precio del Artículo</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-coste">Coste</label>
                                <input type="text" id="f-coste" name="xcoste" class="form-control" placeholder="Coste Artículo" value="<?php if (isset($data['xcoste'])) print($data['xcoste']); ?>">
                                <small class="help-block">Coste del Artículo</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-destacado">Destacado MS</label>
                                <select id="f-destacado" name="xdestacado" class="form-control">
                                    <option value="S" <?php if (isset($data['xdestacado']) && $data['xdestacado'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xdestacado']) && $data['xdestacado'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si es destacado en Mandasaldo</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-activo-web">Activo Web MS</label>
                                <select id="f-activo-web" name="xactivo_web" class="form-control">
                                    <option value="S" <?php if (isset($data['xactivo_web']) && $data['xactivo_web'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xactivo_web']) && $data['xactivo_web'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Activo en web Mandasaldo</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-activo-venta">Activo en Venta</label>
                                <select id="f-activo-venta" name="xactivo_venta" class="form-control">
                                    <option value="S" <?php if (isset($data['xactivo_venta']) && $data['xactivo_venta'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xactivo_venta']) && $data['xactivo_venta'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si está activo para la venta. Este campo afecta a Mandasaldo, Mercarapid y Allnovu.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-activo-rev">Activo Revendedores</label>
                                <select id="f-activo-rev" name="xactivo_rev" class="form-control">
                                    <option value="S" <?php if (isset($data['xactivo_rev']) && $data['xactivo_rev'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xactivo_rev']) && $data['xactivo_rev'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Activo en web Mandasaldo</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-activo-venta-rev">Activo Venta en Revendedores</label>
                                <select id="f-activo-venta-rev" name="xactivo_venta_rev" class="form-control">
                                    <option value="S" <?php if (isset($data['xactivo_venta_rev']) && $data['xactivo_venta_rev'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xactivo_venta_rev']) && $data['xactivo_venta_rev'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si está activo para la venta</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-almacen">Almacén</label>
                                <select id="f-almacen" name="xalmacen_id" class="form-control">
                                    <option value="0" <?php if (isset($data['xalmacen_id']) && $data['xalmacen_id'] == '0') print('selected'); ?>>Ninguno</option>
                                    <?php foreach ($data_form['almacenes'] as $k => $v) { ?>
                                        <option value="<?php print($v['xalmacen_id']) ?>" <?php if ($data['xalmacen_id'] == $v['xalmacen_id']) print('selected'); ?>><?php print($v['xalmacen']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Almacén asociado para control de stock</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-orden">Orden</label>
                                <input type="text" id="f-orden" name="xorden" class="form-control" placeholder="Orden Artículo" value="<?php if (isset($data['xorden'])) print($data['xorden']); ?>">
                                <small class="help-block">Orden/Peso del Artículo. Cuanto mayor peso tenga antes aparece en la lista.</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-seguro">Seguro</label>
                                <input type="text" id="f-seguro" name="xseguro" class="form-control" placeholder="Seguro" value="<?php if (isset($data['xseguro'])) print($data['xseguro']); ?>">
                                <small class="help-block">Seguro del Artículo. Seguro particular asociado al artículo.</small>
                            </div>
                        </div>
                    </div>

                    <h5>Permisos para agencias y puntos de venta aunque el producto esté deshabilitado para agencias/revendedores</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-permiso-agencias">Agencias/Puntos de venta</label>
                                </div>
                                <select id="f-permiso-agencias" class="form-control" multiple="true" data-placeholder="Seleccione agencias/puntos de venta">
                                    <?php foreach ($data_form['revendedores'] as $k => $v) { ?>
                                        <option value="<?php print($v['xrevendedor_id']) ?>" <?php if (in_array($v['xrevendedor_id'], $data['xpermiso_agencias'])) print('selected'); ?>><?php print($v['xrevendedor']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Seleccione las agencias/puntos de venta que tendrán acceso a ver el producto aunque esté desactivado para revendedores</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="f-categoria">Categorías</label>
                                </div>
                                <select id="f-categoria" name="xcategorias" class="form-control" multiple="true" data-placeholder="Seleccione categorías">
                                    <?php foreach ($data_form['categorias'] as $k => $v) { ?>
                                        <option value="<?php print($v['xcategoria_id']) ?>" <?php if (isset($data['xcategorias']) && in_array($v['xcategoria_id'], $data['xcategorias'])) print('selected'); ?>><?php print($v['xcategoria']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Seleccione las categorías a las que pertenece</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-servicio">Servicio</label>
                                <select id="f-servicio" name="xservicio" class="form-control">
                                    <option value="S" <?php if (isset($data['xservicio']) && $data['xservicio'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xservicio']) && $data['xservicio'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si el producto que se entrega ya está en Cuba.</small>
                            </div>
                        </div>
                        <div class="col-md-3 hidden">
                            <div class="form-group">
                                <label class="control-label" for="f-hash">Hash</label>
                                <input type="text" id="f-hash" class="form-control" placeholder="Hash" value="<?php if (isset($data['xhash'])) print($data['xhash']); ?>" readonly="true">
                                <small class="help-block">Identificador de seguridad</small>
                            </div>
                        </div>
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-factor">Factor</label>
                                <select id="f-factor" name="xfactor" class="form-control">
                                    <option value="1" <?php if (isset($data['xfactor']) && $data['xfactor'] == '1') print('selected'); ?>>1</option>
                                    <option value="2" <?php if (isset($data['xfactor']) && $data['xfactor'] == '2') print('selected'); ?>>2</option>
                                    <option value="3" <?php if (isset($data['xfactor']) && $data['xfactor'] == '3') print('selected'); ?>>3</option>
                                    <option value="4" <?php if (isset($data['xfactor']) && $data['xfactor'] == '4') print('selected'); ?>>4</option>
                                    <option value="5" <?php if (isset($data['xfactor']) && $data['xfactor'] == '5') print('selected'); ?>>5</option>
                                </select>
                                <small class="help-block">Factor que implica cuantos combos forma este producto</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-repartidor">Repartidor</label>
                                <select id="f-proveedor" name="xrepartidor_id" class="form-control">
                                    <option value="0" <?php if (isset($data['xrepartidor_id']) && $data['xrepartidor_id'] == '0') print('selected'); ?>>Ninguno</option>
                                    <?php foreach ($data_form['repartidores'] as $k => $v) { ?>
                                        <option value="<?php print($v['xrepartidor_id']) ?>" <?php if ($data['xrepartidor_id'] == $v['xrepartidor_id']) print('selected'); ?>><?php print($v['xrepartidor']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="help-block">Repartidor encargado de la distribución en Cuba</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-articulo-pro">Descripción Proveedor / Repartidor</label>
                                <input type="text" id="f-articulo-pro" name="xarticulo_pro" class="form-control" placeholder="Descripción Artículo Proveedor/Repartidor" value="<?php if (isset($data['xarticulo_pro'])) print($data['xarticulo_pro']); ?>" autocomplete="off">
                                <small class="help-block">Descripción del Artículo para el Proveedor / Repartidor</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-coste-cup">Coste CUP</label>
                                <input type="text" id="f-coste-cup" name="xcoste_cup" class="form-control text-right" placeholder="Precio Transporte" value="<?php if (isset($data['xcoste_cup'])) print($data['xcoste_cup']); ?>">
                                <small class="help-block">Precio Bodeguero CUP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-coste-reparto-cup">Coste Transporte CUP</label>
                                <input type="text" id="f-coste-reparto-cup" name="xcoste_reparto_cup" class="form-control" placeholder="Precio Transporte" value="<?php if (isset($data['xcoste_reparto_cup'])) print($data['xcoste_reparto_cup']); ?>">
                                <small class="help-block">Precio Transporte Bodeguero CUP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="mt-editable-coste-cup">Editable coste CUP</label>
                                </div>
                                <select id="f-editable-coste-cup" name="xeditable_coste_cup" class="form-control" style="margin-bottom: 0px;">
                                    <option value="N" <?php if (isset($data['xeditable_coste_cup']) && $data['xeditable_coste_cup'] == 'S') print('selected'); ?>>No</option>
                                    <option value="S" <?php if (isset($data['xeditable_coste_cup']) && $data['xeditable_coste_cup'] == 'S') print('selected'); ?>>Sí</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-precio1-rev">Precio 1 Agencias</label>
                                <input type="text" id="f-precio1-rev" name="xprecio1_rev" class="form-control" placeholder="Precio 1" value="<?php if (isset($data['xprecio1_rev'])) print($data['xprecio1_rev']); ?>">
                                <small class="help-block">Precio 1 para Agencias</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-precio2-rev">Precio 2 Katapulk</label>
                                <input type="text" id="f-precio2-rev" name="xprecio2_rev" class="form-control" placeholder="Precio 2" value="<?php if (isset($data['xprecio2_rev'])) print($data['xprecio2_rev']); ?>">
                                <small class="help-block">Precio 2 para Katapulk</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-precio3-rev">Precio 3 Tienda y Online</label>
                                <input type="text" id="f-precio3-rev" name="xprecio3_rev" class="form-control" placeholder="Precio 3" value="<?php if (isset($data['xprecio3_rev'])) print($data['xprecio3_rev']); ?>">
                                <small class="help-block">Precio 3 para Tienda y Online</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-precio4-rev">Precio 4 Taguasco</label>
                                <input type="text" id="f-precio4-rev" name="xprecio4_rev" class="form-control" placeholder="Precio 4" value="<?php if (isset($data['xprecio4_rev'])) print($data['xprecio4_rev']); ?>">
                                <small class="help-block">Precio 4 para Taguasco</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-precio-fijo-cup">Precio fijo CUP</label>
                                <input type="text" id="f-precio-fijo-cup" name="xprecio_fijo_cup" class="form-control" placeholder="Precio fijo CUP" value="<?php if (isset($data['xprecio_fijo_cup_format'])) print($data['xprecio_fijo_cup_format']); ?>">
                                <small class="help-block">Precio fijo CUP</small>
                            </div>
                        </div>
                    </div>

                    <h5>Valores Moneda/Divisa CUP</h5>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 1 CUP</label>
                                <input type="text"  class="form-control" value="<?php if (isset($data['xprecio1_cup'])) print($data['xprecio1_cup']); ?>" disabled="true">
                                <small class="help-block">Precio 1 en moneda CUP <?php print($data['xvalor_cup_format']) ?></small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 2 CUP</label>
                                <input type="text" class="form-control" value="<?php if (isset($data['xprecio2_cup'])) print($data['xprecio2_cup']); ?>" disabled="true">
                                <small class="help-block">Precio 2 en moneda CUP <?php print($data['xvalor_cup_format']) ?></small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 3 CUP</label>
                                <input type="text" class="form-control" value="<?php if (isset($data['xprecio3_cup'])) print($data['xprecio3_cup']); ?>" disabled="true">
                                <small class="help-block">Precio 3 en moneda CUP <?php print($data['xvalor_cup_format']) ?></small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 4 CUP</label>
                                <input type="text" class="form-control" value="<?php if (isset($data['xprecio4_cup'])) print($data['xprecio4_cup']); ?>" disabled="true">
                                <small class="help-block">Precio 4 en moneda CUP <?php print($data['xvalor_cup_format']) ?></small>
                            </div>
                        </div>
                    </div>

                    <h5>Valores Moneda/Divisa MLC</h5>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 1 MLC</label>
                                <input type="text"  class="form-control" value="<?php if (isset($data['xprecio1_mlc'])) print($data['xprecio1_mlc']); ?>" disabled="true">
                                <small class="help-block">Precio 1 en moneda MLC <?php print($data['xvalor_mlc_format']) ?></small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 2 MLC</label>
                                <input type="text" class="form-control" value="<?php if (isset($data['xprecio2_mlc'])) print($data['xprecio2_mlc']); ?>" disabled="true">
                                <small class="help-block">Precio 2 en moneda MLC <?php print($data['xvalor_mlc_format']) ?></small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 3 MLC</label>
                                <input type="text" class="form-control" value="<?php if (isset($data['xprecio3_mlc'])) print($data['xprecio3_mlc']); ?>" disabled="true">
                                <small class="help-block">Precio 3 en moneda MLC <?php print($data['xvalor_mlc_format']) ?></small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Precio 4 MLC</label>
                                <input type="text" class="form-control" value="<?php if (isset($data['xprecio4_mlc'])) print($data['xprecio4_mlc']); ?>" disabled="true">
                                <small class="help-block">Precio 4 en moneda MLC <?php print($data['xvalor_mlc_format']) ?></small>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label" for="f-articulo-rev">Descripción Revendedor</label>
                                <input type="text" id="f-articulo-rev" name="xarticulo_rev" class="form-control" placeholder="Descripción Artículo Revendedor" value="<?php if (isset($data['xarticulo_rev'])) print($data['xarticulo_rev']); ?>" autocomplete="off">
                                <small class="help-block">Descripción del Artículo para el Revendedor</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="f-aduana">Descripción Aduana</label>
                                <input type="text" id="f-aduana" name="xaduana" class="form-control" placeholder="Descripción" value="<?php if (isset($data['xaduana'])) print($data['xaduana']); ?>">
                                <small class="help-block">Identificación para aduanas</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-recarga">Recarga</label>
                                <select id="f-recarga" name="xrecarga" class="form-control">
                                    <option value="S" <?php if (isset($data['xrecarga']) && $data['xrecarga'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xrecarga']) && $data['xrecarga'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si el producto es una recarga.</small>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="control-label" for="f-image-alt">Descripción Alt</label>
                                <input type="text" id="f-image-alt" name="ximage_alt" class="form-control" placeholder="Descripción alt imagen" value="<?php if (isset($data['ximage_alt'])) print($data['ximage_alt']); ?>" autocomplete="off">
                                <small class="help-block">Descripción alt de la imagen del Artículo</small>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="f-image-alt">Url Amigable</label>
                                <input type="text" id="f-url-amigable" name="xurl_amigable" class="form-control" placeholder="Url Amigable" value="<?php if (isset($data['xurl_amigable'])) print($data['xurl_amigable']); ?>" autocomplete="off">
                                <small class="help-block">Url amigable del Artículo, debe ser único, no se puede repetir y todo en minúscula. Evitar espacios y símbolos raros. Se permite el guión medio (-) y bajo (_)</small>
                            </div>
                        </div>
                        <div class="col-md-4 hidden">
                            <div class="form-group">
                                <label class="control-label" for="f-nota-stock">Nota Stock</label>
                                <input type="text" id="f-nota-stock" name="xnota_stock" class="form-control" placeholder="Nota Stock" value="<?php if (isset($data['xnota_stock'])) print($data['xnota_stock']); ?>" autocomplete="off">
                                <small class="help-block">Nota relacionada con el stock del producto, SIN REPOSICIÓN, REPOSICIÓN, A PARTIR 23/23/23, cualquier nota de texto aclaratoria</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="f-reposicion">Reposición</label>
                                <select id="f-reposicion" name="xreposicion" class="form-control">
                                    <option value="S" <?php if (isset($data['xreposicion']) && $data['xreposicion'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xreposicion']) && $data['xreposicion'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si el producto tiene reposición.</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="f-cantidad-maxima">Cantidad Máxima</label>
                                <input type="text" id="f-cantidad-maxima" name="xcantidad_maxima" class="form-control" placeholder="Cantidad Máxima" value="<?php if (isset($data['xcantidad_maxima'])) print($data['xcantidad_maxima']); ?>">
                                <small class="help-block">Cantidad máxima en un pedido. Si es superior a 0 controlará en web y agencias que el sumatorio de líneas no supere la cantidad.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="f-keywords">Palabras clave</label>
                                <input type="text" id="f-keywords" name="xkeywords" class="form-control" placeholder="Palabras clave" value="<?php if (isset($data['xkeywords'])) print($data['xkeywords']); ?>" autocomplete="off">
                                <small class="help-block">Palabras clave usadas en las búsquedas, separadas por espacio.</small>
                            </div>
                        </div>
                    </div>

                    <h5 class="">Atributos SEO de la Ficha de Producto Front End</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label" for="f-seo-title">Título de página</label>
                                <input type="text" id="f-seo-title" name="xpage_title" class="form-control" placeholder="Título página" value="<?php if (isset($data['xpage_title'])) print($data['xpage_title']); ?>" autocomplete="off">
                                <small class="help-block">Título de página se usará para el SEO en la ficha pública del artículo.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label" for="">Descripción de Página</label>
                                <textarea id="f-seo-description" name="xpage_description" class="form-control" rows="3"><?php if (isset($data['xpage_description'])) print($data['xpage_description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 text-center">
                            <a id="btn-img" href="javascript:void(0);">
                                <img id="img-upload" class="img-responsive img-thumbnail" style="width: 200px;margin-bottom: 20px;" src="<?php if (isset($data['ximage'])) print($data['ximage']); ?>"/>
                            </a>
                            <small class="help-block">Imagen de MandaSaldo</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <a id="btn-img-rev" href="javascript:void(0);">
                                <img id="img-upload-rev" class="img-responsive img-thumbnail" style="width: 200px;margin-bottom: 20px;" src="<?php if (isset($data['ximage_rev'])) print($data['ximage_rev']); ?>"/>
                            </a>
                            <small class="help-block">Imagen para el Revendedor</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <a id="btn-img-mr" href="javascript:void(0);">
                                <img id="img-upload-mr" class="img-responsive img-thumbnail" style="width: 200px;margin-bottom: 20px;" src="<?php if (isset($data['ximage_mr'])) print($data['ximage_mr']); ?>"/>
                            </a>
                            <small class="help-block">Imagen para Mercarapid.com</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <a id="btn-img-an" href="javascript:void(0);">
                                <img id="img-upload-an" class="img-responsive img-thumbnail" style="width: 200px;margin-bottom: 20px;" src="<?php if (isset($data['ximage_an'])) print($data['ximage_an']); ?>"/>
                            </a>
                            <small class="help-block">Imagen para AllNovu.com</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h3>Recargas de Proveedor</h3>
                                <small class="help-block">Estos precios de recargas serán usados para enviar al proveedor las recargas según qué porductos se compren</small>
                                <div id="gridbox" style="width:100%;height:300px;background-color:white;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3>
                                Componentes del Artículo
                                <a id="btn-add-componente" class="fa fa-plus-circle" href="javascript:void(0);" title="Añadir componente"></a>
                            </h3>
                            <div class="table-responsive">
                                <table id="tbl-componentes" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Componente</th>
                                            <th class="text-right">Cantidad</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['xcomponentes'] as $k => $v) { ?>
                                            <tr data-coste="<?php print($v['xcoste']) ?>">        
                                                <td><?php print($v['xcomponente_id']) ?></td>
                                                <td><?php print($v['xcomponente']) ?></td>
                                                <td class="text-right"><?php print($v['xcantidad']) ?></td>
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

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="">Resumen</label>
                                </div>
                                <!--Wysiwyg editor : Summernote placeholder-->
                                <div id="art-resumen"><?php if (isset($data['xresumen'])) print($data['xresumen']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="">Descripción</label>
                                </div>
                                <!--Wysiwyg editor : Summernote placeholder-->
                                <div id="art-obs"><?php if (isset($data['xobs'])) print($data['xobs']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-infadd-title">Título Información Adicional</label>
                                <input type="text" id="f-infadd-title" name="xinfadd_title" class="form-control" placeholder="Título" value="<?php if (isset($data['xinfadd_title'])) print($data['xinfadd_title']); ?>">
                                <small class="help-block">Título usado en la pestaña de la información adicional del producto</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="f-infadd-activo">Activo Información Adicional</label>
                                <select id="f-infadd-activo" name="xinfadd_activo" class="form-control">
                                    <option value="S" <?php if (isset($data['xinfadd_activo']) && $data['xinfadd_activo'] == 'S') print('selected'); ?>>Sí</option>
                                    <option value="N" <?php if (isset($data['xinfadd_activo']) && $data['xinfadd_activo'] == 'N') print('selected'); ?>>No</option>
                                </select>
                                <small class="help-block">Indica si la pestaña Información Adicional estará visible en la ficha del producto.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div>
                                    <label class="control-label" for="">Contenido Información Adicional</label>
                                </div>
                                <!--Wysiwyg editor : Summernote placeholder-->
                                <div id="art-infadd"><?php if (isset($data['xinfadd_content'])) print($data['xinfadd_content']); ?></div>
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

                </div>
                <!-- =================================================== -->
                <!-- END BASIC FORM ELEMENTS -->
            </div>
        </div> <!-- PARTES DE TRABAJO -->
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
        <button title="Regenerar Imágenes para Revendedor" id="btn-create-images" class="btn btn-purple icon-lg" type="button">
            <i class="fa fa-image"></i>
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
                                <!-- <input type="hidden" name="categoria_id" value="<?php //print($data['xcategoria_id'])                                                ?>"/> -->
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

<div id="modalAddComponente" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Añadir Componente</h4>    
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label" for="mac-componente">Componente</label>
                            <select id="mac-componente" class="form-control">
                                <option value="">Selecciona un componente</option>
                                <?php foreach ($data_form['componentes'] as $k => $v) { ?>
                                    <option data-coste="<?php print($v['xcoste']) ?>" value="<?php print($v['xcomponente_id']) ?>"><?php print($v['xcomponente']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mac-cantidad">Cantidad</label>
                            </div>
                            <input id="mac-cantidad" class="form-control text-right" value=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="btn-do-add-componente" type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->