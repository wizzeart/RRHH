<script>
    action = '<?php print($action) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <form class="panel-body orm-padding"><!-- form-horizontal -->
        <!--Text Input-->
        <input id="f-config-id" type="hidden" name="xconfig_id" value="1">

        <h5 class="">Formas de Pago para Revendedores</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-zelle">Zelle</label>
                    <input type="text" id="f-zelle" name="xzelle" class="form-control" placeholder="Pago Zelle" value="<?php if (isset($data['xzelle'])) print($data['xzelle']); ?>">
                    <small class="help-block">Pago por Zelle</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-cashapp">CashApp</label>
                    <input type="text" id="f-cashapp" name="xcashapp" class="form-control" placeholder="Pago CashApp" value="<?php if (isset($data['xcashapp'])) print($data['xcashapp']); ?>">
                    <small class="help-block">Pago por CashApp</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="f-cuenta">Cuenta Bancaria</label>
                    <textarea id="f-cuenta" name="xcuenta" class="form-control" rows="5" placeholder="Datos de la cuenta bancaria" ><?php if (isset($data['xcuenta'])) print($data['xcuenta']) ?></textarea>
                </div>
            </div>
        </div>

        <h5 class="">Activación de botones/acciones en mandasaldo</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="f-activo-authorize">Activo Pago por Authorize.Net</label>
                    <select id="f-activo-authorize" name="xactivo_authorize" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_authorize']) && $data['xactivo_authorize'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_authorize']) && $data['xactivo_authorize'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se podrá pagar por Authorize.Net</small>
                </div>
            </div>
            <div class="col-md-2 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-activo-square">Activo Pago por Square</label>
                    <select id="f-activo-square" name="xactivo_square" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_square']) && $data['xactivo_square'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_square']) && $data['xactivo_square'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se podrá pagar por Square</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-activo-paypal">Activo Pago por Paypal</label>
                    <select id="f-activo-paypal" name="xactivo_paypal" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_paypal']) && $data['xactivo_paypal'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_paypal']) && $data['xactivo_paypal'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se podrá pagar por paypal</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-activo-cash">Activo Pago por Cash</label>
                    <select id="f-activo-cash" name="xactivo_cash" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_cash']) && $data['xactivo_cash'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_cash']) && $data['xactivo_cash'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se podrá pagar por cash</small>
                </div>
            </div>
            <div class="col-md-2 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-activo-stripe">Activo Pago por Stripe</label>
                    <select id="f-activo-stripe" name="xactivo_stripe" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_stripe']) && $data['xactivo_stripe'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_stripe']) && $data['xactivo_stripe'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se podrá pagar por stripe</small>
                </div>
            </div>
            <div class="col-md-2 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-activo-bitcoin">Activo Pago por Bitcoin</label>
                    <select id="f-activo-bitcoin" name="xactivo_bitcoin" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_bitcoin']) && $data['xactivo_bitcoin'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_bitcoin']) && $data['xactivo_bitcoin'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se podrá pagar por bitcoin</small>
                </div>
            </div>
            <div class="col-md-2 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-aero">Activo Aerovaradero</label>
                    <select id="f-activo-cash" name="xactivo_aero" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_aero']) && $data['xactivo_aero'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_aero']) && $data['xactivo_aero'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si se activa los envíos por aerovaradero</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-seguro">Seguro</label>
                    <input type="text" id="f-seguro" name="xseguro" class="form-control" placeholder="Seguro" value="<?php if (isset($data['xseguro'])) print($data['xseguro']); ?>">
                    <small class="help-block">Precio del seguro por combo en $</small>
                </div>
            </div>
            <div class="col-md-2 hidden">
                <div class="form-group">
                    <label class="control-label" for="f-activo-cash">Nº de combos por sacas</label>
                    <input id="f-nsacas" name="xsacas" class="form-control text-right" value="<?php print($data['xsacas']) ?>"/>
                    <small class="help-block">Ayuda a calcular el número de sacas en las hojas de sacas para el corte</small>
                </div>
            </div>
        </div>

        <h5 class="">Valores Moneda/Divisas</h5>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-cup">CUP</label>
                    <input type="text" id="f-cup" name="xvalor_cup" class="form-control" placeholder="Valor CUP" value="<?php if (isset($data['xvalor_cup_format'])) print($data['xvalor_cup_format']); ?>">
                    <small class="help-block">Valor de 1$ en CUP</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-mlc">MLC</label>
                    <input type="text" id="f-mlc" name="xvalor_mlc" class="form-control" placeholder="Valor MLC" value="<?php if (isset($data['xvalor_mlc_format'])) print($data['xvalor_mlc_format']); ?>">
                    <small class="help-block">Valor de 1$ en MLC</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-otro">Otro</label>
                    <input type="text" id="f-otro" name="xvalor_otro" class="form-control" placeholder="Valor Otro" value="<?php if (isset($data['xvalor_otro_format'])) print($data['xvalor_otro_format']); ?>">
                    <small class="help-block">Valor de 1 moneda desconocida</small>
                </div>
            </div>
        </div>

        <h5 class="">Configuración Combos Personalizados</h5>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-peso">Peso</label>
                    <input type="text" id="f-peso" name="xpeso" class="form-control" placeholder="Peso Combo" value="<?php if (isset($data['xpeso'])) print($data['xpeso']); ?>">
                    <small class="help-block">Peso máximo del Combo Personalizado</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-transporte">Transporte</label>
                    <input type="text" id="f-transporte" name="xtransporte" class="form-control" placeholder="Importe trasnporte" value="<?php if (isset($data['xtransporte'])) print($data['xtransporte']); ?>">
                    <small class="help-block">Importe del transporte del pedido</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-transporte-free">Transporte Gratis</label>
                    <input type="text" id="f-transporte-free" name="xtransporte_free" class="form-control" placeholder="Importe mínimo" value="<?php if (isset($data['xtransporte_free'])) print($data['xtransporte_free']); ?>">
                    <small class="help-block">Importe mínimo del pedido para que el Transporte sea gratis</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-pedido-minimo">Pedido mínimo en web/agencias</label>
                    <input type="text" id="f-pedido-minimo" name="xpedido_minimo" class="form-control" placeholder="Pedido mínimo" value="<?php if (isset($data['xpedido_minimo'])) print($data['xpedido_minimo']); ?>">
                    <small class="help-block">Importe mínimo del pedido. Si el valor es cero no se tendrá en cuenta pedido mínimo.</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-porc-servicio">% Impuesto Servicio</label>
                    <input type="text" id="f-porc-servicio" name="xporc_servicio" class="form-control" placeholder="% Impuesto Servicio" value="<?php if (isset($data['xporc_servicio'])) print($data['xporc_servicio']); ?>">
                    <small class="help-block">% de incremento a los precios de agencias asignadas</small>
                </div>
            </div>
        </div>

        <h5 class="">Recargas para Revendedores</h5>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-activo-recargas">Activo Recargas</label>
                    <select id="f-activo-recargas" name="xactivo_recargas" class="form-control">
                        <option value="S" <?php if (isset($data['xactivo_recargas']) && $data['xactivo_recargas'] == 'S') print('selected'); ?>>Sí</option>
                        <option value="N" <?php if (isset($data['xactivo_recargas']) && $data['xactivo_recargas'] == 'N') print('selected'); ?>>No</option>
                    </select>
                    <small class="help-block">Indica si las recargas en revendedores está activa.</small>
                </div>
            </div>
        </div>

        <h5 class="">SEO MS</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Título de Página</label>
                    <textarea id="f-seo-title-ms" name="xtitle_ms" class="form-control" rows="3"><?php if (isset($data['xtitle_ms'])) print($data['xtitle_ms']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Descripción de Página</label>
                    <textarea id="f-seo-description-ma" name="xdescription_ms" class="form-control" rows="3"><?php if (isset($data['xdescription_ms'])) print($data['xdescription_ms']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Keywords de Página</label>
                    <textarea id="f-seo-keywords-ms" name="xkeywords_ms" class="form-control" rows="3"><?php if (isset($data['xkeywords_ms'])) print($data['xkeywords_ms']); ?></textarea>
                </div>
            </div>
        </div>

        <h5 class="">SEO MR</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Título de Página</label>
                    <textarea id="f-seo-title-mr" name="xtitle_mr" class="form-control" rows="3"><?php if (isset($data['xtitle_mr'])) print($data['xtitle_mr']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Descripción de Página</label>
                    <textarea id="f-seo-description-mr" name="xdescription_mr" class="form-control" rows="3"><?php if (isset($data['xdescription_mr'])) print($data['xdescription_mr']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Keywords de Página</label>
                    <textarea id="f-seo-keywords-mr" name="xkeywords_mr" class="form-control" rows="3"><?php if (isset($data['xkeywords_mr'])) print($data['xkeywords_mr']); ?></textarea>
                </div>
            </div>
        </div>

        <h5 class="">SEO AN</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Título de Página</label>
                    <textarea id="f-seo-title-an" name="xtitle_an" class="form-control" rows="3"><?php if (isset($data['xtitle_an'])) print($data['xtitle_an']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Descripción de Página</label>
                    <textarea id="f-seo-description-an" name="xdescription_an" class="form-control" rows="3"><?php if (isset($data['xdescription_an'])) print($data['xdescription_an']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="">Keywords de Página</label>
                    <textarea id="f-seo-keywords-an" name="xkeywords_an" class="form-control" rows="3"><?php if (isset($data['xkeywords_an'])) print($data['xkeywords_an']); ?></textarea>
                </div>
            </div>
        </div>

        <h5 class="">Textos y contenidos legales MS</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="">Contenido de las condiciones incluidas en el email en Mandasaldo.com</label>
                    </div>
                    <!--Wysiwyg editor : Summernote placeholder-->
                    <div id="f-condiciones-email"><?php if (isset($data['xcondiciones'])) print($data['xcondiciones']); ?></div>
                </div>
            </div>
        </div>

        <h5 class="">Textos y contenidos legales MR</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="">Contenido de las condiciones incluidas en el email en Mercarapid.com</label>
                    </div>
                    <!--Wysiwyg editor : Summernote placeholder-->
                    <div id="f-condiciones-email-mr"><?php if (isset($data['xcondiciones_mr'])) print($data['xcondiciones_mr']); ?></div>
                </div>
            </div>
        </div>

        <h5 class="">Textos y contenidos legales Revendedores</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="">Contenido de las condiciones incluidas en Revendedores</label>
                    </div>
                    <!--Wysiwyg editor : Summernote placeholder-->
                    <div id="f-condiciones-email-rev"><?php if (isset($data['xcondiciones_rev'])) print($data['xcondiciones_rev']); ?></div>
                </div>
            </div>
        </div>
        
        <h5 class="">Textos y contenidos legales AN</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="">Contenido de las condiciones incluidas en el email en AllNovu.com</label>
                    </div>
                    <!--Wysiwyg editor : Summernote placeholder-->
                    <div id="f-condiciones-email-an"><?php if (isset($data['xcondiciones_an'])) print($data['xcondiciones_an']); ?></div>
                </div>
            </div>
        </div>

        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
            <button id="btn-save" class="btn btn-info icon-lg" type="button">
                <i class="fa fa-check"></i>
                Guardar
            </button>
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo"></i>
                Volver
            </button>
        </div>

    </form>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->

</div>
