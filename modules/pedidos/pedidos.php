<script>
    var action = '<?php print($action) ?>';
    var ped_lin = <?php print(((isset($data['items']) && count($data['items']) > 0) ? json_encode($data['items']) : '[]')) ?>;
    var ped_estado = '<?php print($data['xestado']) ?>';
    var lin_editable = 1;
    var municipios = <?php print(json_encode($data_form['municipios'])); ?>;
    var municipio_value = '<?php print($data['xship_city']) ?>';
    var revendedor = '<?php print($data['xrevendedor_id']) ?>';
    var transporte = <?php print($data['xtransporte']) ?>;
    var transporte_free = <?php print($data['xtransporte_free']) ?>;
    var rol = <?php print($app->rol) ?>;
</script>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <div id="alert-widget"></div>

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
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-hash">Referencia</label>
                    <input type="text" id="f-hash" class="form-control" placeholder="Hash" value="<?php if (isset($data['xhash'])) print($data['xhash']); ?>" disabled="true">
                    <small class="help-block">Referencia del pedido</small>
                </div>
            </div>
            <div class="col-md-7">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="f-cliente">Cliente</label>
                    </div>
                    <div class="input-group">
                        <input data-id="<?php if (isset($data['xcliente_id']) && $data['xcliente_id'] != '') print($data['xcliente_id']); ?>"
                            data-cli="<?php if (isset($data['xcliente']) && $data['xcliente'] != '') print($data['xcliente']); ?>"
                            type="text" id="f-cliente" class="form-control"
                            value="<?php if (isset($data['xcliente_sel']) && $data['xcliente_sel'] != '') print($data['xcliente_sel']); ?>">
                        <span class="input-group-addon">
                            <a id="cliente-clear-autocomplete" href="javascript:void(0)"><i class="fa fa-times"></i></a>
                        </span>
                    </div>

                    <small class="help-block">Selecciona un cliente</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="f-fecha">Fecha</label>
                    </div>
                    <div id="f-fecha">
                        <div class="input-group date">
                            <input name="xfecha" type="text" class="form-control" value="<?php if (isset($data['xfecha_format'])) print($data['xfecha_format']); ?>" autocomplete="off">
                            <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                        </div>
                        <small class="help-block">Seleccione fecha del pedido</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-fpago">Forma de Pago*</label>
                    <input type="text" id="f-fpago" class="form-control" placeholder="F.Pago" value="<?php if (isset($data['xfpago'])) print($data['xfpago']); ?>">
                    <small class="help-block">*Valores: CUP efectivo, CUP transferencia, USD efectivo, Zelle, Otro</small>
                </div>
            </div>
            <div class="col-md-3 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-refpay">Referencia del pago</label>
                    <input type="text" id="f-refpay" name="xpayment_ref" class="form-control" placeholder="Ref.Pago" value="<?php if (isset($data['xpayment_ref'])) print($data['xpayment_ref']); ?>">
                    <small class="help-block">Referencia del pago.</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-transaccion">Transacción</label>
                    <input type="text" id="f-transaccion" name="xtransaccion" class="form-control" placeholder="Transacción" value="<?php if (isset($data['xtransaccion'])) print($data['xtransaccion']); ?>">
                    <small class="help-block">Id de la transacción del pedido</small>
                </div>
            </div>
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
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-fecha-pago">Fecha de verificación</label>
                    <input type="text" id="f-fecha-pago" class="form-control" placeholder="Fecha verificación" value="<?php if (isset($data['xfecha_pago_format'])) print($data['xfecha_pago_format']); ?>" disabled="true">
                    <small class="help-block">Fecha de verificación</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-fecha-entregado">Fecha entregado</label>
                    <input type="text" id="f-fecha-entregado" class="form-control" placeholder="Fecha entregado" value="<?php if (isset($data['xfecha_entregado_format'])) print($data['xfecha_entregado_format']); ?>" disabled="true">
                    <small class="help-block">Fecha entregado</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-fecha-entregado">Fecha finalizado</label>
                    <input type="text" id="f-fecha-finalizado" class="form-control" placeholder="Fecha finalizado" value="<?php if (isset($data['xfecha_finalizado_format'])) print($data['xfecha_finalizado_format']); ?>" disabled="true">
                    <small class="help-block">Fecha finalizado</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-ip">IP</label>
                    <input type="text" id="f-ip" class="form-control" placeholder="IP" value="<?php if (isset($data['xip'])) print($data['xip']); ?>" disabled="true">
                    <small class="help-block">IP desde donde se realizó el pedido</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-corte">Corte</label>
                    <input type="text" id="f-corte" class="form-control" placeholder="Corte" value="<?php if (isset($data['xcorte_id'])) print($data['xcorte_id']); ?>" disabled="true">
                    <small class="help-block">Corte asignado</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-tracking">Tracking</label>
                    <div class="input-group date">
                        <input type="text" id="f-tracking" class="form-control" placeholder="Tracking" value="<?php if (isset($data['xtracking'])) print($data['xtracking']); ?>" disabled="true">
                        <span class="input-group-addon"><a id="view-tracking" href="javascript:void(0)"><i class="fa fa-eye"></i></a></span>
                    </div>
                    <small class="help-block">Trackings asociados al pedido</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-chofer">Chofer</label>
                    <input type="text" id="f-chofer" class="form-control" placeholder="Chofer" value="<?php if (isset($data['xchofer'])) print($data['xchofer']); ?>" disabled="true">
                    <small class="help-block">Chofer del pedido</small>
                </div>
            </div>
            <div class="col-md-4 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-destino">Medio de Transporte</label>
                    <select id="f-destino" name="xdestino_id" class="form-control">
                        <option value="">Selecciona Medio de Transporte</option>
                        <?php foreach ($data_form['destinos'] as $k => $v) { ?>
                            <option value="<?php print($v['xdestino_id']) ?>" <?php if ($data['xdestino_id'] == $v['xdestino_id']) print('selected'); ?>><?php print($v['xdestino']) ?></option>
                        <?php } ?>
                    </select>
                    <small class="help-block">Medio de Transporte seleccionado</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-fecha-pago">Fecha de Pago</label>
                    <input type="text" id="f-fecha-pago" class="form-control" placeholder="Fecha Pago" value="<?php if (isset($data['xfecha_pago_format'])) print($data['xfecha_pago_format']); ?>" disabled="true">
                    <small class="help-block">Fecha de pago. Coincide con la fecha de verificación</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-web">Web Procedencia</label>
                    <input data-web="<?php if (isset($data['xweb_id'])) print($data['xweb_id']); ?>" type="text" id="f-web" class="form-control" placeholder="Procedencia Web" value="<?php if (isset($data['xweb'])) print($data['xweb']); ?>" disabled="true">
                    <small class="help-block">Procedencia de la web</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-seguro">Seguro</label>
                    <div class="input-group date">
                        <input data-seguro="<?php if (isset($data['xseguro-defecto'])) print($data['xseguro-defecto']); ?>" type="text" id="f-seguro" class="form-control" placeholder="Seguro" value="<?php if (isset($data['xseguro'])) print($data['xseguro']); ?>" disabled="true">
                        <span class="input-group-addon">
                            <a id="aceptar-seguro" href="javascript:void(0)"><i class="fa fa-check"></i></a>
                            <a id="rechazar-seguro" href="javascript:void(0)"><i class="fa fa-times"></i></a>
                        </span>
                    </div>
                    <small class="help-block">Seguro del pedido, si el importe es superior a cero se considera que tiene seguro.</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-transporte">Transporte</label>
                    <input type="text" id="f-transporte" class="form-control" placeholder="Transporte" value="<?php if (isset($data['xtransporte_format'])) print($data['xtransporte_format']); ?>" disabled="true">
                    <small class="help-block">Transporte asignado</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Tipo de Pedido</label>
                    <input type="hidden" id="f-tipo-pedido" value="<?php if (isset($data['xtipo_pedido'])) print($data['xtipo_pedido']); ?>">
                    <input type="text" class="form-control" value="<?php if (isset($data['xtipo_pedido_format'])) print($data['xtipo_pedido_format']); ?>" disabled="true">
                    <small class="help-block">Agencia/Tienda asociada al pedido</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="f-revendedor">Agencia</label>
                    <input type="text" id="f-revendedor" class="form-control" placeholder="Revendedor" value="<?php if (isset($data['xrevendedor'])) print($data['xrevendedor']); ?>" disabled="true">
                    <small class="help-block">Agencia/Tienda asociada al pedido</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="f-almacen">Almacén</label>
                    <select id="f-almacen" name="xalmacen_id" class="form-control" disabled="true">
                        <option value="">Selecciona Almacén</option>
                        <?php foreach ($data_form['almacenes'] as $k => $v) { ?>
                            <option value="<?php print($v['xalmacen_id']) ?>" <?php if ($data['xalmacen_id'] == $v['xalmacen_id']) print('selected'); ?>><?php print($v['xalmacen']) ?></option>
                        <?php } ?>
                    </select>
                    <!-- <input type="text" id="f-provincia" name="xship_provincia" class="form-control" placeholder="Provincia" value="<?php if (isset($data['xship_provincia'])) print($data['xship_provincia']); ?>"> -->
                    <small class="help-block">Almacén del Pedido</small>
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
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <h4>Dirección de Envío/Datos de Recogida</h4>
                </div>
            </div>
        </div>
        <section id="envio"> <!-- /.envio -->
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-name">Primer Nombre</label>
                        <input type="text" id="f-name" name="xship_name" class="form-control" placeholder="Primer Nombre" value="<?php if (isset($data['xship_name'])) print($data['xship_name']); ?>">
                        <small class="help-block">Primero Nombre del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-name2">Segundo Nombre</label>
                        <input type="text" id="f-name2" name="xship_name2" class="form-control" placeholder="Segundo Nombre" value="<?php if (isset($data['xship_name2'])) print($data['xship_name2']); ?>">
                        <small class="help-block">Segundo Nombre del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-apellido1">Apellido 1</label>
                        <input type="text" id="f-apellido1" name="xship_apellido1" class="form-control" placeholder="Apellido 1" value="<?php if (isset($data['xship_apellido1'])) print($data['xship_apellido1']); ?>">
                        <small class="help-block">Apellido 1 del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-apellido2">Apellido 2</label>
                        <input type="text" id="f-apellido2" name="xship_apellido2" class="form-control" placeholder="Apellido 2" value="<?php if (isset($data['xship_apellido2'])) print($data['xship_apellido2']); ?>">
                        <small class="help-block">Apellido 2 del Envío</small>
                    </div>
                </div>
                <!-- <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-dir2">Dirección 2</label>
                    <input type="text" id="f-dir2" name="xship_dir2" class="form-control" placeholder="Dirección 2" value="<?php if (isset($data['xship_dir2'])) print($data['xship_dir2']); ?>">
                    <small class="help-block">Código de envío del pedido</small>
                </div>
            </div> -->
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-dir1">Dirección 1</label>
                        <input type="text" id="f-dir1" name="xship_dir1" class="form-control" placeholder="Dirección 1" value="<?php if (isset($data['xship_dir1'])) print($data['xship_dir1']); ?>">
                        <small class="help-block">Dirección 1 del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-entre-calle1">Entre calle 1</label>
                        <input type="text" id="f-entre_calle1" name="xship_entre_calle1" class="form-control" placeholder="Entre Calle 1" value="<?php if (isset($data['xship_entre_calle1'])) print($data['xship_entre_calle1']); ?>">
                        <small class="help-block">Entre Calle 1 del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-entre-calle2">Entre calle 2</label>
                        <input type="text" id="f-entre_calle2" name="xship_entre_calle2" class="form-control" placeholder="Entre Calle 2" value="<?php if (isset($data['xship_entre_calle2'])) print($data['xship_entre_calle2']); ?>">
                        <small class="help-block">Entre Calle 2 del Envío</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-numero">Número</label>
                        <input type="text" id="f-numero" name="xship_numero" class="form-control" placeholder="Número 1" value="<?php if (isset($data['xship_numero'])) print($data['xship_numero']); ?>">
                        <small class="help-block">Número del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-apartamento">Apartamento</label>
                        <input type="text" id="f-apartamento" name="xship_apartamento" class="form-control" placeholder="Apartamento" value="<?php if (isset($data['xship_apartamento'])) print($data['xship_apartamento']); ?>">
                        <small class="help-block">Apartamento del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-piso">Piso</label>
                        <input type="text" id="f-piso" name="xship_piso" class="form-control" placeholder="Piso" value="<?php if (isset($data['xship_piso'])) print($data['xship_piso']); ?>">
                        <small class="help-block">Piso del Envío</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-provincia">Provincia</label>
                        <select id="f-provincia" name="xship_provincia" class="form-control">
                            <option value="">Selecciona Provincia</option>
                            <?php foreach ($data_form['provincias'] as $k => $v) { ?>
                                <option value="<?php print($v['xprovincia']) ?>" <?php if ($data['xship_provincia'] == $v['xprovincia']) print('selected'); ?>><?php print($v['xprovincia']) ?></option>
                            <?php } ?>
                        </select>
                        <!-- <input type="text" id="f-provincia" name="xship_provincia" class="form-control" placeholder="Provincia" value="<?php if (isset($data['xship_provincia'])) print($data['xship_provincia']); ?>"> -->
                        <small class="help-block">Provincia del Envío. Valor: <?php if (isset($data['xship_provincia'])) print($data['xship_provincia']); ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-city">Municipio</label>
                        <select id="f-city" name="xship_city" class="form-control">
                            <option value="">Selecciona Municipio</option>
                            <!-- Agregación de municipios en el select -->
                            <?php foreach ($data_form['municipios'] as $k => $v) { ?>
                                <option value="<?php print($v['xmunicipio']) ?>" <?php if ($data['xship_city'] == $v['xmunicipio']) print('selected'); ?>><?php print($v['xmunicipio']) ?></option>
                            <?php } ?>
                        </select>
                        <!-- <input type="text" id="f-city" name="xship_city" class="form-control" placeholder="Municipio" value="<?php if (isset($data['xship_city'])) print($data['xship_city']); ?>"> -->
                        <small class="help-block">Municipio del Envío. Valor: <?php if (isset($data['xship_city'])) print($data['xship_city']) ?></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-zipcode">Cód.Postal</label>
                        <input type="text" id="f-zipcode" name="xship_zipcode" class="form-control" placeholder="Cód.Postal" value="<?php if (isset($data['xship_zipcode'])) print($data['xship_zipcode']); ?>">
                        <small class="help-block">Cód.Postal del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-reparto">Reparto</label>
                        <input type="text" id="f-reparto" name="xship_reparto" class="form-control" placeholder="Reparto" value="<?php if (isset($data['xship_reparto'])) print($data['xship_reparto']); ?>">
                        <small class="help-block">Reparto del Envío</small>
                    </div>
                </div>
            </div>
            <div class="row hidden">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-country">País</label>
                        <input type="text" id="f-country" name="xship_country" class="form-control" placeholder="País" value="<?php if (isset($data['xship_country'])) print($data['xship_country']); ?>" disabled="true">
                        <small class="help-block">País del Envío</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-phone">Teléfono</label>
                        <div class="input-group mar-btm">
                            <div class="input-group-addon bg-gray-light">
                                (+53)
                            </div>
                            <input type="text" id="f-phone" name="xship_phone" class="form-control" placeholder="Teléfono" value="<?php if (isset($data['xship_phone'])) print($data['xship_phone']); ?>">
                        </div>
                        <small class="help-block">Teléfono del Envío</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="f-ci">Carnet de Identidad</label>
                        <input type="text" id="f-ci" name="xship_ci" class="form-control" placeholder="Carnet de Identidad" value="<?php if (isset($data['xship_ci'])) print($data['xship_ci']); ?>">
                        <small class="help-block">Carnet de Identidad del Envío</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="f-ship-obs">Notas del Pedido</label>
                        <input type="text" id="f-ship-obs" name="xship_obs" class="form-control" placeholder="Notas" value="<?php if (isset($data['xship_obs'])) print($data['xship_obs']); ?>">
                        <small class="help-block">Anotaciones realizadas por el cliente</small>
                    </div>
                </div>
            </div>
        </section> <!-- /.envio -->

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <h3>
                        Detalle de las líneas
                        <a style="font-size: 15px;" id="btn-cancel-lin" href="javascript:void(0);" title="Cancelar Línea">
                            <i class="fa fa-minus-circle"></i>
                        </a>
                    </h3>
                    <div id="gridbox" style="width:100%;height:300px;background-color:white;"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive hidden">
                    <table id="tbl-iva" class="table table-striped">
                        <thead>
                            <tr>
                                <th>IVA</th>
                                <th>Base</th>
                                <th>Imp.IVA</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group hidden">
                    <label class="control-label text-right" for="f-importe-base">Base</label>
                    <input type="text" id="f-importe-base" class="form-control text-right" placeholder="Importe" value="<?php if (isset($data['xbase'])) print($data['xbase']); ?>" disabled="true">
                    <small class="help-block text-right">Importe base del pedido.</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group hidden">
                    <label class="control-label text-right" for="f-importe-iva">Importe IVA</label>
                    <input type="text" id="f-importe-iva" class="form-control text-right" placeholder="Importe" value="<?php if (isset($data['ximporte_iva'])) print($data['ximporte_iva']); ?>" disabled="true">
                    <small class="help-block text-right">Importe iva del pedido.</small>
                </div>
            </div>
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

    <div class="panel-footer text-center">
        <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
        <button id="btn-save" class="btn btn-info icon-lg" type="button">
            <i class="fa fa-check"></i>
            Guardar
        </button>
        <button id="btn-print" class="btn btn-warning btn-icon icon-lg fa fa-print" alt="Imprimir Pedido" title="Imprimir Pedido"></button>
        <button id="btn-copy" class="btn btn-purple btn-icon icon-lg fa fa-copy" alt="Duplicar Pedido" title="Duplicar Pedido"></button>
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
        <button id="btn-movs" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-truck"></i>
            Movimientos
        </button>
        <button id="btn-his" class="btn btn-default icon-lg" type="button">
            <i class="fa fa-history"></i>
            Histórico
        </button>
        <button id="btn-cancel-all" class="btn btn-danger icon-lg hidden" type="button">
            <i class="fa fa-check"></i>
            Cancelar todo el pedido
        </button>
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
<div id="mdlCancelLin" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Cancelar Línea</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label>Seleccione Línea a Cancelar</label>
                        <select id="mcl-lin" class="form-control">
                            <option value="">Selecciona Línea</option>
                        </select>
                    </div>
                </div>

                <div id="mcl-rev" class="well well-lg hidden" style="margin-top: 10px;">
                    <p>El pedido está asociado a un revendedor. Al cancelar la línea será devuelto el crédito.</p>
                    <div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button id="btn-cancel-lin-accept" type="button" class="btn btn-primary">Cancelar Línea</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div id="modalSearchProduct" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Buscar Producto</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label>Introduzca aquí el producto a buscar.</label>
                        <select id="msp-art" class="form-control" data-placeholder="Seleccione artículos">
                            <option value="">Selecciona producto</option>
                            <?php foreach ($data_form['articulos'] as $k => $v) { ?>
                                <option data-hash="<?php print($v['xhash']) ?>" data-seguro="<?php print($v['xseguro']) ?>" data-tipo="<?php print($v['xtipo']) ?>" data-articulo-mr="articulo-mr" data-precio-mr="0" data-precio="<?php print($v['xprecio']) ?>" value="<?php print($v['xarticulo_id']) ?>"><?php print($v['xarticulo']) ?></option>
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
<div id="modalViewTracking" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-hdd-o"></i> <span id="mvt-title">Ver Tracking</span></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <img class="img-responsive" src="/img/envios/0032-3438-0012-015-01.jpg" />
                        <p>Tracking CU000349888RT Referencia 0032-3438-0012-015-01</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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