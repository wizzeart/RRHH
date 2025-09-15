<script>
    action = '<?php print($action) ?>';
    var municipios =<?php print(json_encode($data_form['municipios'])); ?>;
    var action_web = '<?php if (isset($_GET['action'])) print($_GET['action']) ?>';
</script>
<h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">General</a></li>
                <li class=""><a href="#tab-envio" data-toggle="tab" aria-expanded="false">Direcciones de Envío</a></li>
                <li class=""><a href="#tab-historial" data-toggle="tab" aria-expanded="false">Historial de Conexiones</a></li>
                <li class=""><a href="#tab-pedidos" data-toggle="tab" aria-expanded="false">Pedidos</a></li>
                <li class=""><a href="#tab-notas" data-toggle="tab" aria-expanded="false">Notas</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Information</h4><p style='width:150px'>This is an information bubble to help the user.</p>" data-html="true" title=""></a>
        </div>
        <h3 class="panel-title">Ficha Cliente</h3>
    </div>

    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-general">
                <div class="panel">
                    <!-- BASIC FORM ELEMENTS -->
                    <!--===================================================-->
                    <form class="panel-body form-padding"><!-- form-horizontal -->
                        <!--Text Input-->
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-title">Código Cliente</label>
                                    <input type="text" id="f-cliente-id" name="xcliente_id" class="form-control" placeholder="ID Cliente" value="<?php if (isset($data['xcliente_id'])) print($data['xcliente_id']); ?>" disabled="true">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="control-label" for="f-cliente">Nombre del Cliente</label>
                                    <input type="text" id="f-cliente" name="xcliente" class="form-control" placeholder="Nombre Cliente" value="<?php if (isset($data['xcliente'])) print($data['xcliente']); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-activo">Activo</label>
                                    <select id="f-activo" name="xactivo" class="form-control">
                                        <option value="S" <?php if (isset($data['xactivo']) && $data['xactivo'] == 'S') print('selected'); ?>>Sí</option>
                                        <option value="N" <?php if (isset($data['xactivo']) && $data['xactivo'] == 'N') print('selected'); ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-pass">Contraseña</label>
                                    <input type="password" id="f-pass" name="xpwd" class="form-control" placeholder="Contraseña" value="">
                                    <small class="help-block">Contraseña del cliente, si se rellena se cambiará</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-nombre">Primer Nombre</label>
                                    <input type="text" id="f-nombre" name="xnombre" class="form-control" placeholder="Nombre" value="<?php if (isset($data['xnombre'])) print($data['xnombre']); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-nombre2">Segundo Nombre</label>
                                    <input type="text" id="f-nombre2" name="xnombre2" class="form-control" placeholder="Segundo Nombre" value="<?php if (isset($data['xnombre2'])) print($data['xnombre2']); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-apellido1">Primer Apellido</label>
                                    <input type="text" id="f-apellido1" name="xapellido1" class="form-control" placeholder="Primer Apellido" value="<?php if (isset($data['xapellido1'])) print($data['xapellido1']); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-apellido2">Segundo Apellido</label>
                                    <input type="text" id="f-apellido2" name="xapellido2" class="form-control" placeholder="Segundo Apellido" value="<?php if (isset($data['xapellido2'])) print($data['xapellido2']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-email">Email</label>
                                    <input type="text" id="f-email" name="xemail" class="form-control" placeholder="Email" value="<?php if (isset($data['xemail'])) print($data['xemail']); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-prefijo">Prefijo</label>
                                    <select id="f-prefijo" name="xprefijo" class="form-control">
                                        <option value="">Seleccione un prefijo</option>
                                        <?php foreach ($data_form['paises'] as $k => $v) { ?>
                                            <option data-country="<?php print($v['xalias']) ?>" value="<?php print(str_replace('+', '', $v['xprefijo'])) ?>" <?php if ($v['xalias'] == $data['xcountry']) print('selected'); ?>><?php print($v['xpais'] . ' (' . $v['xprefijo'] . ')') ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-movil">Móvil</label>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <button id="btn-view-movil" class="btn btn-default" type="button"><i class="fa fa-eye"></i></button>
                                        </span>
                                        <input type="text" id="f-movil" name="xmovil" class="form-control" placeholder="Movil" value="<?php if (isset($data['xmovil'])) print($data['xmovil']); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-nivel">Nivel</label>
                                    <select id="f-nivel" name="xnivel" class="form-control">
                                        <option value="1" <?php if (isset($data['xnivel']) && $data['xnivel'] == '1') print('selected'); ?>>1</option>
                                        <option value="2" <?php if (isset($data['xnivel']) && $data['xnivel'] == '2') print('selected'); ?>>2</option>
                                        <option value="3" <?php if (isset($data['xnivel']) && $data['xnivel'] == '3') print('selected'); ?>>3</option>
                                        <option value="4" <?php if (isset($data['xnivel']) && $data['xnivel'] == '4') print('selected'); ?>>4</option>
                                        <option value="5" <?php if (isset($data['xnivel']) && $data['xnivel'] == '5') print('selected'); ?>>5</option>
                                        <option value="6" <?php if (isset($data['xnivel']) && $data['xnivel'] == '6') print('selected'); ?>>6</option>
                                        <option value="7" <?php if (isset($data['xnivel']) && $data['xnivel'] == '7') print('selected'); ?>>7</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-ip">IP Registro</label>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <button id="btn-view-ip" class="btn btn-default" type="button"><i class="fa fa-eye"></i></button>
                                        </span>
                                        <input type="text" id="f-ip" class="form-control" placeholder="IP" value="<?php if (isset($data['xip'])) print($data['xip']); ?>" readonly="true">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 hidden">
                                <div class="form-group">
                                    <label class="control-label" for="f-code-email">Código Email</label>
                                    <input type="text" id="f-code-email" class="form-control" placeholder="Code Email" value="<?php if (isset($data['xemail_code'])) print($data['xemail_code']); ?>" readonly="true">
                                    <small class="help-block">Código de verificación de Email</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-email-verificado">Email Verificado</label>
                                    <select id="f-activo" name="xemail_verificado" class="form-control">
                                        <option value="S" <?php if (isset($data['xemail_verificado']) && $data['xemail_verificado'] == 'S') print('selected'); ?>>Sí</option>
                                        <option value="N" <?php if (isset($data['xemail_verificado']) && $data['xemail_verificado'] == 'N') print('selected'); ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 hidden">
                                <div class="form-group">
                                    <label class="control-label" for="f-code-movil">Código Móvil</label>
                                    <input type="text" id="f-code-movil" class="form-control" placeholder="Code Móvil" value="<?php if (isset($data['xsms_code'])) print($data['xsms_code']); ?>" readonly="true">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-movil-verificado">Móvil Verificado</label>
                                    <select id="f-activo" name="xsms_verificado" class="form-control">
                                        <option value="S" <?php if (isset($data['xsms_verificado']) && $data['xsms_verificado'] == 'S') print('selected'); ?>>Sí</option>
                                        <option value="N" <?php if (isset($data['xsms_verificado']) && $data['xsms_verificado'] == 'N') print('selected'); ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="f-riesgo">Riesgo Cliente</label>
                                    <input type="text" id="f-riesgo" name="xriesgo" class="form-control text-right" placeholder="Riesgo" value="<?php if (isset($data['xriesgo'])) print($data['xriesgo']); ?>">
                                    <small class="help-block">Importe asignado y definido como límite máximo en compras en un corte si el cliente es de nivel 3</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div>
                                        <label class="control-label" for="f-web">Web</label>
                                    </div>
                                    <select id="f-web" name="xweb_id" class="form-control" data-placeholder="Seleccione una posición">
                                        <option value="">Seleccione una web</option>
                                        <?php foreach ($data_form['webs'] as $k => $v) { ?>
                                            <option value="<?php print($v['xweb_id']) ?>" <?php if (isset($data['xweb_id']) && ($v['xweb_id'] == $data['xweb_id'])) print('selected'); ?>><?php print($v['xweb']) ?></option>
                                        <?php } ?>
                                    </select>
                                    <small class="help-block">Web de procedencia del registro</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-revendedor">Revendedor</label>
                                    <input type="text" id="f-revendedor" class="form-control" value="<?php print($data['xrevendedor']) ?>" readonly="true">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-fecha">Fecha Registro</label>
                                    <input type="text" id="f-fecha" class="form-control" value="<?php if (isset($data['xfecha_format'])) print($data['xfecha_format']); ?>" readonly="true">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="f-fecha-alta">Fecha Último acceso</label>
                                    <input type="text" id="f-acceso" class="form-control" value="<?php if (isset($data['xacceso_format'])) print($data['xacceso_format']); ?>" readonly="true">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea id="f-obs" name="xobs" class="form-control" rows="5" ><?php if (isset($data['xobs'])) print($data['xobs']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- =================================================== -->
                    <!-- END BASIC FORM ELEMENTS -->
                </div>
            </div>
            <div class="tab-pane fade" id="tab-envio">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Direcciones de Envío
                            <a id="btn-add-dir" class="fa fa-plus-circle" href="javascript:void(0)" title="Añadir nueva dirección"></a>
                        </h3>
                    </div>

                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="tbl-envios" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Primer Nombre</th>
                                        <th>Segundo Nombre</th>
                                        <th>Primer Apellido</th>
                                        <th>Segundo Apellido</th>
                                        <th>Calle</th>
                                        <th>Número</th>
                                        <th>Apartamento</th>
                                        <th>Piso</th>
                                        <th>Cód.Postal</th>
                                        <th>Municipio</th>
                                        <th>Reparto Zona</th>
                                        <th>Entre Calle 1</th>
                                        <th>Entre Calle 2</th>
                                        <th>Provincia</th>
                                        <th>Teléfono</th>
                                        <th>Carnet de Identidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['envios'] as $k => $v) { ?>
                                        <tr data-id="<?php print($v['xenvio_id']) ?>">        
                                            <td><?php print($v['xname']) ?></td>
                                            <td><?php print($v['xname2']) ?></td>
                                            <td><?php print($v['xapellido1']) ?></td>
                                            <td><?php print($v['xapellido2']) ?></td>
                                            <td><?php print($v['xdir1']) ?></td>
                                            <td><?php print($v['xnumero']) ?></td>
                                            <td><?php print($v['xapartamento']) ?></td>
                                            <td><?php print($v['xpiso']) ?></td>
                                            <td><?php print($v['xzipcode']) ?></td>
                                            <td><?php print($v['xcity']) ?></td>
                                            <td><?php print($v['xreparto']) ?></td>
                                            <td><?php print($v['xentre_calle1']) ?></td>
                                            <td><?php print($v['xentre_calle2']) ?></td>
                                            <td><?php print($v['xprovincia']) ?></td>
                                            <td>(+53) <?php print($v['xphone']) ?></td>
                                            <td><?php print($v['xci']) ?></td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-default add-tooltip edit-dir" data-toggle="tooltip" href="javascript:void(0)" data-original-title="Editar" data-container="body"><i class="fa fa-pencil"></i></a>
                                                <!--
                                                <a class="btn btn-xs btn-danger add-tooltip remove-dir" data-toggle="tooltip" href="javascript:void(0)" data-original-title="Eliminar" data-container="body"><i class="fa fa-trash"></i></a>
                                                -->
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-historial">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Historial de Conexiones</h3>
                    </div>

                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="tbl-accesos" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                        <th>IP</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['historial'] as $k => $v) { ?>
                                        <tr>        
                                            <td><?php print($v['xfecha_format']) ?></td>
                                            <td><?php print($v['xaction']) ?></td>
                                            <td><?php print($v['xip']) ?></td>
                                            <td><?php print($v['xobs']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-pedidos">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Pedidos</h3>
                    </div>

                    <div class="panel-body">
                        <form style="display: none;" target="_blank" action="https://www.correos.cu/rastreador-de-envios/" method="POST">
                            <input id="cuba-tracking" name="c" value=""/>
                            <input id="btn-cuba-tracking" type="submit" value="Enviar"/>
                        </form>

                        <div class="table-responsive">
                            <table id="tbl-pedidos" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>NºPedido</th>
                                        <th>Pedido Web</th>
                                        <th>Fecha</th>
                                        <th>Destinatario</th>
                                        <th>IP</th>
                                        <th>Forma de Pago</th>
                                        <th>Transaccion</th>
                                        <th>Importe</th>
                                        <th>Estado</th>
                                        <th class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['pedidos'] as $k => $v) { ?>
                                        <tr>        
                                            <td><a class="text-info" target="_blank" href="?module=pedidos&id=<?php print($v['xpedido_id']) ?>"><?php print($v['xpedido_id']) ?></a></td>
                                            <td><?php print($v['xhash']) ?></td>
                                            <td><?php print($v['xfecha_format']) ?></td>
                                            <td><?php print($v['xdestinatario']) ?></td>
                                            <td><?php print($v['xip']) ?></td>
                                            <td><?php print($v['xfpago']) ?></td>
                                            <td><?php print($v['xtransaccion']) ?></td>
                                            <td><?php print(number_format($v['ximporte'], 2, ',', '')) ?></td>
                                            <td><?php print($v['xestado_desc']) ?></td>
                                            <td class="text-right">
                                                <?php if ($v['xestado'] == '1' || $v['xestado'] == '15') { ?>
                                                    <a class="btn btn-xs btn-default add-tooltip rescue" data-toggle="tooltip" href="javascript:void(0)" data-original-title="Rescatar" data-container="body"><i class="fa fa-life-ring"></i></a>
                                                <?php } ?>
                                                <a class="btn btn-xs btn-primary add-tooltip view-details" data-toggle="tooltip" href="javascript:void(0)" data-original-title="Ver detalles" data-container="body"><i class="fa fa-eye"></i></a>
                                                <a class="btn btn-xs btn-danger add-tooltip remove" data-toggle="tooltip" href="javascript:void(0)" data-original-title="Eliminar" data-container="body"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-notas">
                <h4 class="text-thin">Notas</h4>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <!--Wysiwyg editor : Summernote placeholder-->
                            <div id="cli-notas"><?php if (isset($data['xnotas'])) print($data['xnotas']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer text-center">
        <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>

        <button id="btn-save" class="btn btn-info icon-lg" type="button">
            <i class="fa fa-check"></i>
            Guardar
        </button>
        <?php if (!isset($_GET['action'])) { ?>
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo"></i>
                Volver
            </button>
            <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                <i class="fa fa-plus"></i>
                Nuevo
            </button>
        <?php } ?>
        <?php if (isset($_GET['action']) && in_array($_GET['action'], array('new-cli', 'new-dir'))) { ?>
            <button id="btn-close" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo"></i>
                Cerrar Ventana
            </button>
        <?php } ?>
    </div>
</div>



<!-- modales -->
<div id="modalDirEnvio" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Dirección de Envío</h4>    
            </div>
            <div class="modal-body">
                <div id="mt-alert" class="alert alert-danger fade in" style="display: none;">
                    <button class="close" data-dismiss="alert"><span>×</span></button>
                    <span class="content"></span>
                </div>
                <input id="mt-row" type="hidden"/>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-id">ID</label>
                            </div>
                            <input id="mde-id" class="form-control" value="" disabled="true"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-nombre">Primer Nombre</label>
                            </div>
                            <input id="mde-ship-nombre" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-nombre2">Segundo Nombre</label>
                            </div>
                            <input id="mde-ship-nombre2" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-apellido1">Primer Apellido</label>
                            </div>
                            <input id="mde-ship-apellido1" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-apellido2">Segundo Apellido</label>
                            </div>
                            <input id="mde-ship-apellido2" class="form-control" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-dir1">Dirección</label>
                            </div>
                            <input id="mde-ship-dir1" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-numero">Número</label>
                            </div>
                            <input id="mde-ship-numero" class="form-control" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-piso">Piso</label>
                            </div>
                            <input id="mde-ship-piso" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-apartamento">Apartamento</label>
                            </div>
                            <input id="mde-ship-apartamento" class="form-control" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="mde-ship-provincia">Provincia</label>
                            <select id="mde-ship-provincia" class="form-control">
                                <option value="">Selecciona Provincia</option>
                                <?php foreach ($data_form['provincias'] as $k => $v) { ?>
                                    <option value="<?php print($v['xprovincia']) ?>" <?php if ($data['xship_provincia'] == $v['xprovincia']) print('selected'); ?>><?php print($v['xprovincia']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="mde-ship-city">Municipio</label>
                            <select id="mde-ship-city" class="form-control">
                                <option value="">Selecciona Municipio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-zipcode">Cód.Postal</label>
                            </div>
                            <input id="mde-ship-zipcode" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-reparto">Reparto</label>
                            </div>
                            <input id="mde-ship-reparto" class="form-control" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-entre-calle1">Entre calle 1</label>
                            </div>
                            <input id="mde-ship-entre-calle1" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-entre-calle2">Entre calle 2</label>
                            </div>
                            <input id="mde-ship-entre-calle2" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-phone">Teléfono</label>
                            </div>
                            <input id="mde-ship-phone" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div>
                                <label class="control-label" for="mde-ship-ci">CI</label>
                            </div>
                            <input id="mde-ship-ci" class="form-control" value=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button id="mde-btn-save" type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="mdlValidate" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validación de la Dirección de Envío</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="mdlViewDetails" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de envíos del pedido <span id="mvd-pedido"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tbl-pedidos-lin" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NºPedido</th>
                                <th>Fecha</th>
                                <th>NºLínea</th>
                                <th>Producto</th>
                                <th>Corte</th>
                                <th>Tracking</th>
                                <th>Fecha Aéreo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>