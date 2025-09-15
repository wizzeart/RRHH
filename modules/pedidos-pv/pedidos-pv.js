var libgrid = '/plugins/dhtmlxGrid/codebase';
var spd, cmd_params; //datos del producto seleccionado
var rowId;//almacena temporal el id de la línea para poder tratarla
var mygrid;
var imprimir = 0;
var style_row_cancel = "color: red; text-decoration: line-through;";
var dir_envios = [];
//mygrid.setRowAttribute(rowId, 'medida', $('#msl-lote option:selected').data('medida-id'));
//mygrid.getRowAttribute(id, 'medida')

$(document).ready(function () {
    checkPermisoAlmacen();
    $('#btn-check-envios').click(function () {
        var cmd = 'module=pedidos&method=check-envios&doc=' + $('#f-pedido').val();
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                $.niftyNoty({
                    type: 'success',
                    title: 'Revisión de Envíos',
                    message: 'Se ha revisado correctamente los envíos.',
                    container: 'floating',
                    timer: 3000
                });
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
                /*
                 $.niftyNoty({
                 type: 'danger',
                 title: 'Direcciones de Envío',
                 message: 'No existen direcciones de envío para seleccionar.',
                 container: 'floating',
                 timer: 3000
                 });
                 */
            }
        });
    });
    $('#btn-his').click(function () {
        var status = 1, msg = '';

        if ($('#f-pedido').val() == '') {
            status = 0;
        }

        if (status == 1) {
            var cmd = 'module=pedidos&method=get-his&doc=' + $('#f-pedido').val();
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#tbl-his tbody').empty();
                    $.each(d, function (i, v) {
                        var e = $(tpl_lin_his);

                        $(e).find('td:eq(0)').html(v.xfecha_format);
                        $(e).find('td:eq(1)').html(v.xestado + '-' + v.xestado_desc);
                        $(e).find('td:eq(2)').html(v.xusuario);
                        $(e).find('td:eq(3)').html(v.xobs);

                        $('#tbl-his tbody').append(e);
                    });
                    $('#modalHis').modal('show');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
    });
    $('#btn-movs').click(function () {
        var status = 1, msg = '';

        if ($('#f-pedido').val() == '') {
            status = 0;
        }

        if (status == 1) {
            var cmd = 'module=almacenes&method=get-movs&doc=' + $('#f-pedido').val();
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#tbl-movs tbody').empty();
                    $.each(d, function (i, v) {
                        var e = $(tpl_lin_mov);

                        $(e).find('td:eq(0)').html(v.xfecha_format);
                        $(e).find('td:eq(1)').html(v.xdoc_id);
                        $(e).find('td:eq(2)').html(v.xarticulo_id + '-' + v.xarticulo);
                        $(e).find('td:eq(3)').html(v.xcomponente_id + '-' + v.xcomponente);
                        $(e).find('td:eq(4)').html(v.xcantidad);
                        $(e).find('td:eq(5)').html(v.xalmacen_id + '-' + v.xalmacen);
                        $(e).find('td:eq(6)').html(v.xconcepto);
                        $(e).find('td:eq(7)').html(v.xstock_before);
                        $(e).find('td:eq(8)').html(v.xstock_after);

                        $('#tbl-movs tbody').append(e);
                    });
                    $('#modalMovs').modal('show');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
    });
    $('#f-fpago').change(function () {
        var servicio = $('#f-fpago option:selected').data('servicio');
        if (servicio == 'S') {
            aceptar_servicio();
        }
        if (servicio == 'N') {
            rechazar_servicio();
        }
    });
    $('#btn-calc-servicio').click(function () {
        calc_importe_total();
        $.niftyNoty({
            type: 'success',
            title: 'Impuesto Servicio',
            message: 'Cálculo del impuesto del servicio aplicado.',
            container: 'floating',
            timer: 3000
        });
    });
    $('#msp-art').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%', search_contains: true}).change(function () {
        load_articulo_stock($('#msp-art').val());
    });

    $('#btn-add-cliente').click(function () {
        var cmd = "?module=clientes&action=new-cli";
        //location.href = cmd;
        window.open(cmd);
    });
    $('#btn-new-dir-envio').click(function () {
        var cmd = "?module=clientes&action=new-dir&id=" + $('#f-cliente').data('id');
        window.open(cmd);
        $('#modalDirEnvio').modal('hide');
    });

    $('#btn-add-dir-envio').click(function () {
        var envio = $('#mde-envio').val();
        $.each(dir_envios, function (i, v) {
            if (envio == v.xenvio_id) {
                $('#f-name').val(v.xname);
                $('#f-name2').val(v.xname2);
                $('#f-apellido1').val(v.xapellido1);
                $('#f-apellido2').val(v.xapellido2);
                $('#f-dir1').val(v.xdir1);
                $('#f-numero').val(v.xnumero);
                $('#f-piso').val(v.xpiso);
                $('#f-apartamento').val(v.xapartamento);
                $('#f-entre_calle1').val(v.xentre_calle1);
                $('#f-entre_calle2').val(v.xentre_calle2);
                $('#f-provincia').val(v.xprovincia).change();
                $('#f-city').val(v.xcity);
                $('#f-zipcode').val(v.xzipcode);
                $('#f-phone').val(v.xphone);
                $('#f-reparto').val(v.xreparto);
                $('#f-ci').val(v.xci);
            }
        });

        if ($('#f-city').val() != '') {
            if (almacenes_municipios == 'S')
                setAlmacen();
        }

        $('#modalDirEnvio').modal('hide');
    });
    $('#f-city').change(function () {
        if (almacenes_municipios == 'S')
            setAlmacen();
    });
    $('#btn-add-direnvio').click(function () {
        var status = 1, msg = '';

        if ($('#f-cliente').val() == '') {
            status = 0;
            msg = 'Seleccione un cliente para cargar sus direcciones de envío';
        }

        if (status == 1) {
            load_direcciones();
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Direcciones de Envío',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });

    $('#aceptar-seguro').click(function () {
        var seguro_imp = 0, seguro_coste = $('#f-seguro').data('seguro'), tipo = '', cantidad = 0, seguro_lin = 0;

        //console.debug(seguro_imp);

        mygrid.forEachRow(function (id) {
            if (mygrid.cellById(id, 1).getValue() != '' && mygrid.cellById(id, 2).getValue() != '') {
                cantidad = mygrid.cellById(id, 4).getValue();
                tipo = mygrid.getRowAttribute(id, 'xtipo');
                seguro_lin = mygrid.getRowAttribute(id, 'xseguro');
                //console.debug(tipo);
                if (tipo == 'A') {
                    seguro_imp += (seguro_lin * cantidad);
                }
            }
        });
        if (seguro_imp == 0)
            seguro_imp = seguro_coste;
        $('#f-seguro').val($.number(seguro_imp, 2, ',', ''));
        calc_importe_total();
    });
    $('#rechazar-seguro').click(function () {
        $('#f-seguro').val('0,00');
        calc_importe_total();
    });
    $('#aceptar-transporte').click(function () {
        var valor_cup = parseFloat($('#f-valor-cup').val().replace(/,/gi, '.'));
        var valor_mlc = parseFloat($('#f-valor-mlc').val().replace(/,/gi, '.'));
        var valor_otro = parseFloat($('#f-valor-otro').val().replace(/,/gi, '.'));
        var portes = transporte, portes_cup = transporte.toFixed(0) * valor_cup, portes_mlc = transporte * valor_mlc, portes_otro = transporte * valor_otro;

        $('#f-transporte').val($.number(portes, 2, ',', ''));
        $('#f-importe-transporte').val($.number(portes, 2, ',', ''));
        $('#f-importe-transporte-cup').val($.number(portes_cup, 2, ',', ''));
        $('#f-importe-transporte-mlc').val($.number(portes_mlc, 2, ',', ''));
        $('#f-importe-transporte-otro').val($.number(portes_otro, 2, ',', ''));
        calc_importe_total();
    });
    $('#rechazar-transporte').click(function () {
        $('#f-transporte').val('0,00');
        $('#f-importe-transporte').val($('#f-transporte').val());
        $('#f-importe-transporte-cup').val($('#f-transporte').val());
        $('#f-importe-transporte-mlc').val($('#f-transporte').val());
        $('#f-importe-transporte-otro').val($('#f-transporte').val());
        calc_importe_total();
    });
    $('#btn-copy').click(function () {
        var status = 1, msg = '';

        if ($('#f-pedido').val() == '') {
            status = 0;
            msg = '<div>No puede duplicar un pedido no creado.</div>';
        }

        if (status == 1) {
            if (confirm("¿Deseas duplicar este pedido?? Pedido: " + $('#f-pedido').val())) {
                var cmd = 'module=pedidos&method=copy-order&ord=' + $('#f-pedido').val();
                $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                    success: function (d) {
                        if (d.status == 1) {
                            $.niftyNoty({
                                type: 'success',
                                title: 'Duplicar Pedido',
                                message: d.msg,
                                container: 'floating',
                                timer: 3000
                            });
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Duplicar Pedido',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-cancel-lin-accept').click(function () {
        var status = 1, msg = '';

        if (ped_estado != 12 && ped_estado != 4 && ped_estado != 5) {
            status = 0;
            msg = '<div>No puede cancelar líneas si el pedido no está verificado/enviado.</div>';
        }

        if (status == 1) {
            var cmd = 'module=pedidos&method=cancel-line&lin=' + $('#mcl-lin').val() + '&rev=' + revendedor;
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {

                        mygrid.forEachRow(function (id) {
                            if (mygrid.getRowAttribute(id, 'xlin_id') == d.lin_id) {
                                mygrid.setRowTextStyle(id, style_row_cancel);
                            }
                        });

                        $('#mdlCancelLin').modal('hide');
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Cancelar Pedido',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-cancel-lin').click(function () {
        var status = 1, msg = '';

        if ($('#f-pedido').val() == '') {
            status = 0;
            msg += '<div>No puede cancelar líneas si el pedido está sin guardar.</div>';
        }

        if (ped_estado != 12 && ped_estado != 4 && ped_estado != 5) {
            status = 0;
            msg += '<div>No puede cancelar líneas si el pedido no está verificado/enviado.</div>';
        }

        if (status == 1) {
            var cmd = 'module=pedidos&method=load-lines&ord=' + $('#f-pedido').val() + '&rev=' + revendedor;
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('#mcl-lin').empty();
                        $.each(d.items, function (i, v) {
                            $('#mcl-lin').append('<option value="' + v.xlin_id + '">' + v.xarticulo_id + ' ' + v.xarticulo + ' (lin:' + v.xlin_id + ')</option>');
                        });
                        if (d.rev_id > 0) {
                            $('#mcl-rev').removeClass('hidden');
                            $('#mcl-rev').find('div').html(d.rev);
                        }

                        $('#mdlCancelLin').modal('show');
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Cancelar Pedido',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-cancel-all').click(function () {
        if (confirm("¿Deseas cancelar este pedido con todos sus trackings y envíos?? Pedido: " + $('#f-pedido').val())) {
            $('#btn-cancel-all').attr('disabled', true);
            var cmd = 'module=pedidos&method=cancel-order&ord=' + $('#f-pedido').val();
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('#btn-cancel-all').addClass('hidden');
                        $('#f-estado').val('3 - Cancelado');
                        ped_estado = '3';
                        checkPermisoAlmacen();
                        $.niftyNoty({
                            type: 'success',
                            title: 'Cancelar Pedido',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Cancelar Pedido',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                    $('#btn-cancel-all').attr('disabled', false);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
    });

    $('#view-tracking').click(function () {
        var status = 1, msg = '', extra = 0;
        $('#view-tracking').attr('disabled', true);

        if ($('#f-tracking').val() == '') {
            if (confirm('No tiene asignado tracking, ¿Desea ver si existe alguna imagen asociada en este pedido?')) {
                $('#f-tracking').val('extra');
            } else {
                status = 0;
                msg = '<div>Campo vacío</div>';
            }
        }


        if (status == 1) {
            var cmd = 'module=pedidos&method=view-tracking&tracking=' + $('#f-tracking').val()
                    + '&pedido=' + $('#f-pedido').val();
            /*if ($('#f-tracking').val() == 'extra') {
             cmd += '&pedido=' + $('#f-pedido').val()
             }*/
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#view-tracking').attr('disabled', false);
                    if (d.status == 1) {
                        $('#mvt-title').html('Ver Tracking del Pedido ' + $('#f-pedido').val());
                        $('#modalViewTracking .modal-body').empty();
                        $.each(d.items, function (i, v) {
                            var e = $(tpl_img_tracking);
                            $(e).find('p').html('Tracking <strong>' + v.tracking + '</strong> Referencia <strong>' + v.referencia + '</strong>');
                            $(e).find('img').attr('src', '/img/envios/' + v.image);
                            if ('image2' in v)
                                $(e).find('img').after('<br><img class="img-responsive" src="/img/envios/' + v.image2 + '"/>');
                            $('#modalViewTracking .modal-body').append(e);
                        });
                        $('#modalViewTracking').modal('show');
                        $.niftyNoty({
                            type: 'success',
                            title: 'Ver Tracking',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Ver Tracking',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                }
            });
        } else {
            $('#view-tracking').attr('disabled', false);
            $.niftyNoty({
                type: 'danger',
                title: 'Ver Tracking',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#f-provincia').change(function () {
        $('#f-city').empty().append('<option value="">Selecciona Municipio</option>');
        $.each(municipios, function (i, v) {
            if (v.xprovincia == $('#f-provincia').val())
                $('#f-city').append('<option data-almacen="' + v.xalmacen_id + '" value="' + v.xmunicipio + '">' + v.xmunicipio.replace(/_/gi, ' ') + '</option>');
        });
    });
    $('#btn-chg-estado').click(function () {
        var status = 1, msg = '';
        $('#btn-chg-estado').attr('disabled', true);
        $('#img-loading-chg-estado').removeClass('hidden');
        if (status == 1) {
            var cmd = 'module=pedidos&method=chg-estado&ord=' + $('#f-pedido').val() + '&id=' + $('#mdl-ce-estado-id').val()
                    + '&obs=' + $('#mdl-ce-obs').val();
            cmd_params = cmd;
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#btn-chg-estado').attr('disabled', false);
                    $('#img-loading-chg-estado').addClass('hidden');
                    if (d.status == 1) {
                        $('#mdlChgEstado').modal('hide');
                        $('#f-estado').val($('#mdl-ce-estado-new').val());
                        ped_estado = d.estado;
                        checkPermisoAlmacen();
                        $.niftyNoty({
                            type: 'success',
                            title: 'Cambiar Estado',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    } else {
                        $('#mdlChgEstado').modal('hide');
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Cambiar Estado',
                            message: d.msg,
                            container: 'floating',
                            timer: 6000
                        });
                        if ('emergency' in d && d.emergency == 1) {
                            $('#btn-cancel-all').removeClass('hidden');
                        }
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    //alert(XMLHttpRequest.responseText);
                    $('#mdlChgEstado').modal('hide');
                    $('#btn-chg-estado').attr('disabled', false);
                    $('#img-loading-chg-estado').addClass('hidden');
                    $.niftyNoty({
                        type: 'danger',
                        title: 'Cambiar Estado',
                        message: XMLHttpRequest.responseText,
                        container: 'page'
                    });

                    var cmd = 'module=tools&method=log-error&ref=' + encodeURIComponent('ERROR PANEL CHG-ESTADO PEDIDOS-PV')
                            + '&data=' + encodeURIComponent(XMLHttpRequest.responseText)
                            + '&params=' + encodeURIComponent(cmd_params);
                    $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                        success: function (d) {},
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        }
                    });
                }
            });
        } else {
            $('#btn-chg-estado').attr('disabled', false);
            $.niftyNoty({
                type: 'danger',
                title: 'Cambiar Estado',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('.chg-estado').click(function () {
        var status = 1, msg = '';
        if ($('#f-pedido').val() == '') {
            status = 0;
            msg = 'Debe guardar o crear un pedido antes.';
        }

        if (status == 1) {
            var id = $(this).data('estado');
            var estado_new = $(this).text();
            var estado_old = $('#f-estado').val();
            //alert('modal ' + id + ' ' + estado_new + ' ' + estado_old);
            $('#mdl-ce-estado-old').val(estado_old);
            $('#mdl-ce-estado-new').val(estado_new);
            $('#mdl-ce-estado-id').val(id);
            $('#mdlChgEstado').modal('show');
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Cambiar Estado',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-print').click(function () {
        if (ped_estado == '1' || ped_estado == '2' || ped_estado == '3') {
            imprimir = 1;
            $('#btn-save').click();
        } else {
            imprimir_pedido();
        }
    });
    $('#btn-new').click(function () {
        location.href = '?module=pedidos-pv';
    });
    //$('#f-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    $('#f-fecha .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        language: 'es'
    });
    $('#btn-view-stock-auto').click(function () {
        var status = 1, msg = '';
        var art_id = $('#msp-art-auto').data('id');

        if (art_id == '') {
            status = 0;
            msg += 'No hay producto seleccionado.';
        }

        if (status == 1) {
            load_articulo_stock_auto(art_id);
            $('#modalStocks').modal('show');
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Consulta Stocks',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-add-product-search-auto').click(function () {
        var status = 1, msg = '';
        if ($('#msp-art-auto').data('id') == '') {
            status = 0;
        }
        if (status == 1) {
            var moneda = '', precio_base = 0, precio_base_format = '';
            var valor_cambio_cup = parseFloat($('#f-valor-cup').val().replace(/,/gi, '.'));
            var valor_cambio_mlc = parseFloat($('#f-valor-mlc').val().replace(/,/gi, '.'));
            var valor_cambio_otro = parseFloat($('#f-valor-otro').val().replace(/,/gi, '.'));
            var servicio = parseFloat($('#f-porc-servicio').val().replace(/,/gi, '.'));
            var precio = parseFloat($('#msp-art-auto').data('precio')), precio_format = '';
            //var precio = parseFloat($('#msp-art').data('precio')), precio_format = '';
            var cantidad = 0, precio_cup = 0, precio_cup_format = '';
            var precio_cup = 0, precio_cup_format = '', precio_mlc = 0, precio_mlc_format = '', precio_otro = 0, precio_otro_format = '';
            var importe_lin = 0, importe_lin_format = '', importe_lin_cup = 0, importe_lin_cup_format = '';
            var importe_lin_mlc = 0, importe_lin_mlc_format = '', importe_lin_otro = 0, importe_lin_otro_format = '';
            var importe_lin_moneda_format = '';
            var art_desc = '';
            var art_patron = ' -- ';

            cantidad = $('#msp-cantidad-auto').val();
            precio_base = precio;
            if (cantidad == null || cantidad == '')
                cantidad = 1;
            moneda = $('#f-fpago option:selected').data('moneda');
            //USD
            precio = precio_base * (1 + (servicio / 100));
            importe_lin = precio * cantidad;
            precio_format = $.number(precio, 2, ',', '');
            importe_lin_format = $.number(importe_lin, 2, ',', '');
            //CUP
            precio_cup = ((precio_base * (1 + (servicio / 100))).toFixed(0) * 1) * (valor_cambio_cup);
            importe_lin_cup = precio_cup * cantidad;
            precio_cup_format = $.number(precio_cup, 2, ',', '');
            importe_lin_cup_format = $.number(importe_lin_cup, 2, ',', '');
            //MLC
            precio_mlc = precio_base * (valor_cambio_mlc * (1 + (servicio / 100)));
            importe_lin_mlc = precio_mlc * cantidad;
            precio_mlc_format = $.number(precio_mlc, 2, ',', '');
            importe_lin_mlc_format = $.number(importe_lin_mlc, 2, ',', '');
            //OTRO
            precio_otro = precio_base * (valor_cambio_otro * (1 + (servicio / 100)));
            importe_lin_otro = precio_otro * cantidad;
            precio_otro_format = $.number(precio_otro, 2, ',', '');
            importe_lin_otro_format = $.number(importe_lin_otro, 2, ',', '');
            var fecha = new Date(), fecha_format = '';
            fecha_format = fecha.getFullYear() + '-' + (fecha.getMonth() + 1) + '-' + fecha.getDate() + ' ' + fecha.getHours() + ':' + fecha.getMinutes() + ':' + fecha.getSeconds();
            //console.debug(fecha_format);

            if (moneda == 'usd' || moneda == 'cup')
                importe_lin_moneda_format = importe_lin_cup_format;
            if (moneda == 'mlc')
                importe_lin_moneda_format = importe_lin_mlc_format;
            if (moneda == 'otro')
                importe_lin_moneda_format = importe_lin_otro_format;

            art_desc = $('#msp-art-auto').data('art');
            //art_desc = art_desc.substring(art_desc.indexOf(art_patron) + art_patron.length);
            //art_desc = $('#msp-art').data('art');

            //CALCULAMOS ROWID QUE TENDRÁ LA VARIABLE
            mygrid.forEachRow(function (id) {
                if (mygrid.cellById(id, 1).getValue() === '' && mygrid.cellById(id, 2).getValue() === '') {
                    rowId = id;
                }
            });

            precio_base_format = $.number(precio_base, 2, ',', '');
            mygrid.cellById(rowId, 0).setValue('');
            mygrid.cellById(rowId, 1).setValue($('#msp-art-auto').data('id'));
            //mygrid.cellById(rowId, 1).setValue($('#msp-art').data('id'));
            mygrid.cellById(rowId, 2).setValue(art_desc);
            mygrid.cellById(rowId, 3).setValue(cantidad);
            mygrid.cellById(rowId, 4).setValue(precio_format);
            mygrid.cellById(rowId, 5).setValue(precio_cup_format);
            mygrid.cellById(rowId, 6).setValue(precio_mlc_format);
            mygrid.cellById(rowId, 7).setValue(precio_otro_format);
            mygrid.cellById(rowId, 8).setValue(importe_lin_format);
            mygrid.cellById(rowId, 9).setValue(importe_lin_moneda_format);
            mygrid.setRowAttribute(rowId, 'xhash', $('#msp-art-auto').data('hash'));
            //mygrid.setRowAttribute(rowId, 'xhash', $('#msp-art').data('hash'));
            mygrid.setRowAttribute(rowId, 'xdate', fecha_format);
            mygrid.setRowAttribute(rowId, 'xtipo', $('#msp-art-auto').data('tipo'));
            //mygrid.setRowAttribute(rowId, 'xtipo', $('#msp-art').data('tipo'));
            mygrid.setRowAttribute(rowId, 'xcanjeado', 'N');
            mygrid.setRowAttribute(rowId, 'xseguro', 0);
            mygrid.setRowAttribute(rowId, 'precio_base', precio_base_format);
            mygrid.setRowAttribute(rowId, 'imp_cup', importe_lin_cup_format);
            mygrid.setRowAttribute(rowId, 'imp_mlc', importe_lin_mlc_format);
            mygrid.setRowAttribute(rowId, 'imp_otro', importe_lin_otro_format);
            if (mygrid.getRowIndex(rowId) + 1 == mygrid.getRowsNum()) {
                add_row();
            }
            window.setTimeout(function () {
                //mygrid.selectCell(mygrid.getRowIndex(rowId), 4, false, false, true, true);
                calc_importe_total();
                $('#msp-art-auto').val('').data('id', '').data('art', '').data('hash', '').data('tipo', '').focus();
            }, 1);
            //$('#modalSearchProduct').modal('hide');
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Asignación de Producto',
                message: 'Tiene que seleccionar un producto.',
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-add-product-search').click(function () {
        var status = 1, msg = '';
        if ($('#msp-art').val() == '')
            status = 0;
        if (status == 1) {
            var moneda = '', precio_base = 0, precio_base_format = '';
            var valor_cambio_cup = parseFloat($('#f-valor-cup').val().replace(/,/gi, '.'));
            var valor_cambio_mlc = parseFloat($('#f-valor-mlc').val().replace(/,/gi, '.'));
            var valor_cambio_otro = parseFloat($('#f-valor-otro').val().replace(/,/gi, '.'));
            var servicio = parseFloat($('#f-porc-servicio').val().replace(/,/gi, '.'));
            var precio = parseFloat($('#msp-art option:checked').data('precio')), precio_format = '';
            //var precio = parseFloat($('#msp-art').data('precio')), precio_format = '';
            var cantidad = 0, precio_cup = 0, precio_cup_format = '';
            var precio_cup = 0, precio_cup_format = '', precio_mlc = 0, precio_mlc_format = '', precio_otro = 0, precio_otro_format = '';
            var importe_lin = 0, importe_lin_format = '', importe_lin_cup = 0, importe_lin_cup_format = '';
            var importe_lin_mlc = 0, importe_lin_mlc_format = '', importe_lin_otro = 0, importe_lin_otro_format = '';
            var importe_lin_moneda_format = '';
            var art_desc = '';
            var art_patron = ' -- ';

            cantidad = $('#msp-cantidad').val();
            precio_base = precio;
            if (cantidad == null || cantidad == '')
                cantidad = 1;
            moneda = $('#f-fpago option:selected').data('moneda');
            //USD
            precio = precio_base * (1 + (servicio / 100));
            importe_lin = precio * cantidad;
            precio_format = $.number(precio, 2, ',', '');
            importe_lin_format = $.number(importe_lin, 2, ',', '');
            //CUP
            precio_cup = ((precio_base * (1 + (servicio / 100))).toFixed(0) * 1) * (valor_cambio_cup);
            importe_lin_cup = precio_cup * cantidad;
            precio_cup_format = $.number(precio_cup, 2, ',', '');
            importe_lin_cup_format = $.number(importe_lin_cup, 2, ',', '');
            //MLC
            precio_mlc = precio_base * (valor_cambio_mlc * (1 + (servicio / 100)));
            importe_lin_mlc = precio_mlc * cantidad;
            precio_mlc_format = $.number(precio_mlc, 2, ',', '');
            importe_lin_mlc_format = $.number(importe_lin_mlc, 2, ',', '');
            //OTRO
            precio_otro = precio_base * (valor_cambio_otro * (1 + (servicio / 100)));
            importe_lin_otro = precio_otro * cantidad;
            precio_otro_format = $.number(precio_otro, 2, ',', '');
            importe_lin_otro_format = $.number(importe_lin_otro, 2, ',', '');
            var fecha = new Date(), fecha_format = '';
            fecha_format = fecha.getFullYear() + '-' + (fecha.getMonth() + 1) + '-' + fecha.getDate() + ' ' + fecha.getHours() + ':' + fecha.getMinutes() + ':' + fecha.getSeconds();
            //console.debug(fecha_format);

            if (moneda == 'usd' || moneda == 'cup')
                importe_lin_moneda_format = importe_lin_cup_format;
            if (moneda == 'mlc')
                importe_lin_moneda_format = importe_lin_mlc_format;
            if (moneda == 'otro')
                importe_lin_moneda_format = importe_lin_otro_format;

            art_desc = $('#msp-art option:checked').text();
            art_desc = art_desc.substring(art_desc.indexOf(art_patron) + art_patron.length);
            //art_desc = $('#msp-art').data('art');


            precio_base_format = $.number(precio_base, 2, ',', '');
            mygrid.cellById(rowId, 0).setValue('');
            mygrid.cellById(rowId, 1).setValue($('#msp-art').val());
            //mygrid.cellById(rowId, 1).setValue($('#msp-art').data('id'));
            mygrid.cellById(rowId, 2).setValue(art_desc);
            mygrid.cellById(rowId, 3).setValue(cantidad);
            mygrid.cellById(rowId, 4).setValue(precio_format);
            mygrid.cellById(rowId, 5).setValue(precio_cup_format);
            mygrid.cellById(rowId, 6).setValue(precio_mlc_format);
            mygrid.cellById(rowId, 7).setValue(precio_otro_format);
            mygrid.cellById(rowId, 8).setValue(importe_lin_format);
            mygrid.cellById(rowId, 9).setValue(importe_lin_moneda_format);
            mygrid.setRowAttribute(rowId, 'xhash', $('#msp-art option:checked').data('hash'));
            //mygrid.setRowAttribute(rowId, 'xhash', $('#msp-art').data('hash'));
            mygrid.setRowAttribute(rowId, 'xdate', fecha_format);
            mygrid.setRowAttribute(rowId, 'xtipo', $('#msp-art option:checked').data('tipo'));
            //mygrid.setRowAttribute(rowId, 'xtipo', $('#msp-art').data('tipo'));
            mygrid.setRowAttribute(rowId, 'xcanjeado', 'N');
            mygrid.setRowAttribute(rowId, 'xseguro', 0);
            mygrid.setRowAttribute(rowId, 'precio_base', precio_base_format);
            mygrid.setRowAttribute(rowId, 'imp_cup', importe_lin_cup_format);
            mygrid.setRowAttribute(rowId, 'imp_mlc', importe_lin_mlc_format);
            mygrid.setRowAttribute(rowId, 'imp_otro', importe_lin_otro_format);
            if (mygrid.getRowIndex(rowId) + 1 == mygrid.getRowsNum()) {
                add_row();
            }
            window.setTimeout(function () {
                //mygrid.selectCell(mygrid.getRowIndex(rowId), 4, false, false, true, true);
                calc_importe_total();
            }, 1);
            $('#modalSearchProduct').modal('hide');
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Asignación de Producto',
                message: 'Tiene que seleccionar un producto.',
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-pedidos';
    });
    $('#btn-close').click(function () {
        window.close();
    });
    $('#btn-save').click(function () {
        var status = 1, n = 0, i = 0, c = 0;
        var msg = '', lin = [], tmp = [], iva = [];
        if ($('#f-cliente').val() == '') {
            status = 0;
            msg += 'Debe seleccionar un cliente.<br>';
        }
        if ($('#f-fecha input').val() == '') {
            status = 0;
            msg += 'Debe introducir la fecha del pedido.<br>';
        }
        if ($('#f-provincia').val() == '' || $('#f-provincia').val() == null) {
            status = 0;
            msg += 'Debe introducir la provincia del pedido.<br>';
        }
        if ($('#f-city').val() == '' || $('#f-city').val() == null) {
            status = 0;
            msg += 'Debe introducir la municipio del pedido.<br>';
        }
        if ($('#f-destino').val() == '') {
            $('#f-destino').val('1');
            //status = 0;
            //msg += 'Debe introducir el medio de transporte del pedido.<br>';
        }

        if ($('#f-fpago').val() == '') {
            status = 0;
            msg += 'Debe seleccionar una forma de pago.<br>';
        }

        if ($('#f-transaccion').val() == '') {
            $('#f-transaccion').val('PUNTO DE VENTA');
        }

        i = 0;
        c = 0; //contador de lineas válidas
        //tmp = [];
        mygrid.forEachRow(function (id) {
            i++;
            if (mygrid.cellById(id, 1).getValue() != '' && mygrid.cellById(id, 2).getValue() != '') {
                c++;
                if (mygrid.cellById(id, 2).getValue() == '') {
                    status = 0;
                    msg += 'Error de datos línea nº ' + i + ' código: ' + mygrid.cellById(id, 1).getValue() + ' sin descripción.<br>';
                }
                n = mygrid.cellById(id, 4).getValue().replace(/,/gi, '.');
                if (!$.isNumeric(n)) {
                    status = 0;
                    msg += 'Error de datos línea nº ' + i + ' código: ' + mygrid.cellById(id, 1).getValue() + ' valor incorrecto: <strong>' + n + '</strong>.<br>';
                }
                n = mygrid.cellById(id, 5).getValue().replace(/,/gi, '.');
                if (!$.isNumeric(n)) {
                    status = 0;
                    msg += 'Error de datos línea nº ' + i + ' código: ' + mygrid.cellById(id, 1).getValue() + ' orden incorrecto: <strong>' + n + '</strong>.<br>';
                }
            }
        });
        if (c == 0) {
            status = 0;
            msg += 'No existen líneas válidas. Debe de contener al menos una línea.<br>';
        }

        if (status == 1) {
            $('#img-loading').removeClass('hidden');
            $('#btn-save').attr('disabled', true);
            mygrid.forEachRow(function (id) {
                var precio_base = 0;
                if (mygrid.cellById(id, 1).getValue() != '' && mygrid.cellById(id, 2).getValue() != '') {
                    var d = [];
                    precio_base = mygrid.getRowAttribute(id, 'precio_base');
                    d.push(mygrid.cellById(id, 1).getValue());
                    d.push(encodeURIComponent(mygrid.cellById(id, 2).getValue()));
                    //d.push(mygrid.cellById(id, 3).getValue());
                    d.push(mygrid.cellById(id, 3).getValue().replace(/,/gi, '.')); //CANTIDAD
                    d.push(mygrid.cellById(id, 4).getValue().replace(/,/gi, '.')); //PRECIO USD
                    d.push(mygrid.cellById(id, 5).getValue().replace(/,/gi, '.')); //PRECIO CUP
                    d.push(mygrid.cellById(id, 6).getValue().replace(/,/gi, '.')); //PRECIO MLC
                    d.push(mygrid.cellById(id, 7).getValue().replace(/,/gi, '.')); //PRECIO OTRO
                    d.push(mygrid.cellById(id, 8).getValue().replace(/,/gi, '.')); //IMPORTE USD
                    //d.push(mygrid.cellById(id, 9).getValue().replace(/,/gi, '.'));
                    /*
                     d.push($('#f-importe-base-cup').val().replace(/,/gi, '.'));//IMPORTE CUP
                     d.push($('#f-importe-base-mlc').val().replace(/,/gi, '.'));//IMPORTE MLC
                     d.push($('#f-importe-base-otro').val().replace(/,/gi, '.'));//IMPORTE OTRO
                     */
                    d.push(mygrid.getRowAttribute(id, 'imp_cup').replace(/,/gi, '.')); //IMPORTE CUP
                    d.push(mygrid.getRowAttribute(id, 'imp_mlc').replace(/,/gi, '.')); //IMPORTE MLC
                    d.push(mygrid.getRowAttribute(id, 'imp_otro').replace(/,/gi, '.')); //IMPORTE OTRO
                    d.push(mygrid.getRowAttribute(id, 'xhash'));
                    //d.push(mygrid.getRowAttribute(id, 'xdate'));
                    //d.push(mygrid.getRowAttribute(id, 'xtipo'));
                    //d.push(mygrid.getRowAttribute(id, 'xcanjeado'));
                    if (typeof (precio_base) == 'strig') {
                        precio_base = mygrid.getRowAttribute(id, 'precio_base').replace(/,/gi, '.');
                    }
                    d.push(precio_base);
                    lin.push(d.join('|'));
                }
            });
            if ($('#f-seguro').val() != '0,00')
                $('#aceptar-seguro').click();
            var cmd = 'module=pedidos&method=save-pv&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            cmd += '&action=' + action;
            cmd += '&ximporte=' + $('#f-importe-total').val().replace(/,/gi, '.');
            cmd += '&ximporte_cup=' + $('#f-importe-total-cup').val().replace(/,/gi, '.');
            cmd += '&ximporte_mlc=' + $('#f-importe-total-mlc').val().replace(/,/gi, '.');
            cmd += '&ximporte_otro=' + $('#f-importe-total-otro').val().replace(/,/gi, '.');
            cmd += '&xbase=' + $('#f-importe-base').val().replace(/,/gi, '.');
            cmd += '&xbase_cup=' + $('#f-importe-base-cup').val().replace(/,/gi, '.');
            cmd += '&xbase_mlc=' + $('#f-importe-base-mlc').val().replace(/,/gi, '.');
            cmd += '&xbase_otro=' + $('#f-importe-base-otro').val().replace(/,/gi, '.');
            cmd += '&ximp_servicio=' + $('#f-importe-servicio').val().replace(/,/gi, '.');
            cmd += '&ximp_servicio_cup=' + $('#f-importe-servicio-cup').val().replace(/,/gi, '.');
            cmd += '&ximp_servicio_mlc=' + $('#f-importe-servicio-mlc').val().replace(/,/gi, '.');
            cmd += '&ximp_servicio_otro=' + $('#f-importe-servicio-otro').val().replace(/,/gi, '.');
            cmd += '&xseguro=' + $('#f-seguro').val().replace(/,/gi, '.');
            cmd += '&xvalor_cup=' + $('#f-valor-cup').val().replace(/,/gi, '.');
            cmd += '&xvalor_mlc=' + $('#f-valor-mlc').val().replace(/,/gi, '.');
            cmd += '&xvalor_otro=' + $('#f-valor-otro').val().replace(/,/gi, '.');
            cmd += '&xporc_servicio=' + $('#f-porc-servicio').val().replace(/,/gi, '.');
            cmd += '&xtransporte=' + $('#f-transporte').val().replace(/,/gi, '.');
            cmd += '&xtransporte_cup=' + $('#f-importe-transporte-cup').val().replace(/,/gi, '.');
            cmd += '&xtransporte_mlc=' + $('#f-importe-transporte-mlc').val().replace(/,/gi, '.');
            cmd += '&xtransporte_otro=' + $('#f-importe-transporte-otro').val().replace(/,/gi, '.');
            cmd += '&xcliente_id=' + $('#f-cliente').data('id');
            if (action == 'update') {
                cmd += '&xpedido_id=' + $('#f-pedido').val();
            }
            cmd += '&lin=' + lin.join(';');
            if ($('#f-almacen').prop('disabled')) {
                cmd += '&xalmacen_id=' + $('#f-almacen').val();
            }
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        action = 'update';
                        if (d.action == 'insert') {
                            $('#f-pedido').val(d.id);
                            $('#f-hash').val(d.hash);
                            if (d.estado == '1') {
                                $('#f-estado').val('1 - Pedido Pendiente de Pago');
                            }
                        }
                        $.niftyNoty({
                            type: 'success',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                        if (imprimir == 1) {
                            imprimir = 0;
                            imprimir_pedido();
                        }
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    //alert(textStatus);
                    var e = $(alert_widget_tpl);
                    $(e).find('#alert-title').html('Error');
                    $(e).find('#alert-text').html(XMLHttpRequest.responseText);
                    $('#alert-widget').append(e);
                    //$('body').scrollTo('#alert-widget');
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Guardar datos',
                message: msg,
                container: 'floating',
                timer: 5000
            });
        }
    });
    init_grid();
    autocomplete();
    if (action == 'update' && ped_lin.length > 0) {
        if (municipio_value != '') {
            $('#f-provincia').change();
            $('#f-city').val(municipio_value);
        }

        load_lines(ped_lin);
        add_row();
    } else {
        add_row();
    }

    if ($.inArray(rol, [11]) >= 0) {
        $('#btn-movs').remove();
    }
});
function setAlmacen() {
    var almacen = 0;
    $.each(municipios, function (i, v) {
        if (v.xmunicipio == $('#f-city').val()) {
            almacen = v.xalmacen_id;
        }
    });
    if (almacen > 0) {
        $('#f-almacen').val(almacen);
    }
}
function imprimir_pedido() {
    var cmd = '';
    cmd = 'api-app.php?module=pedidos&method=dl-doc-pv&id=' + $('#f-pedido').val();
    window.open(cmd);
}

function calc_importe_lin(rId) {
    var cant = 0, precio = 0, imp = 0, precio_cup = 0, imp_cup = 0, cambio_cup = 0;
    var cambio_mlc, cambio_otro, servicio = 0, moneda = '', imp_moneda = 0;
    var precio_base = 0, precio_mlc = 0, precio_otro = 0, imp_mlc = 0, imp_otro = 0;
    precio_base = mygrid.getRowAttribute(rId, 'precio_base');
    if (typeof (precio_base) == 'string') {
        precio_base = parseFloat((mygrid.getRowAttribute(rId, 'precio_base')).replace(/,/gi, '.'));
    }
    moneda = $('#f-fpago option:selected').data('moneda');
    servicio = parseFloat($('#f-porc-servicio').val().replace(/,/gi, '.'));
    cambio_cup = parseFloat($('#f-valor-cup').val().replace(/,/gi, '.'));
    cambio_mlc = parseFloat($('#f-valor-mlc').val().replace(/,/gi, '.'));
    cambio_otro = parseFloat($('#f-valor-otro').val().replace(/,/gi, '.'));
    cant = parseFloat(mygrid.cellById(rId, 3).getValue().replace(/,/gi, '.'));
    precio = parseFloat(mygrid.cellById(rId, 4).getValue().replace(/,/gi, '.'));
    precio = precio_base * (1 + (servicio / 100));
    precio_cup = precio_base * cambio_cup * (1 + (servicio / 100));
    precio_mlc = precio_base * cambio_mlc * (1 + (servicio / 100));
    precio_otro = precio_base * cambio_otro * (1 + (servicio / 100));
    imp = cant * precio;
    imp_cup = cant * precio_cup;
    imp_mlc = cant * precio_mlc;
    imp_otro = cant * precio_otro;
    if (moneda == 'usd' || moneda == 'cup')
        imp_moneda = imp_cup;
    if (moneda == 'mlc')
        imp_moneda = imp_mlc;
    if (moneda == 'otro')
        imp_moneda = imp_otro;
    mygrid.cellById(rId, 4).setValue($.number(precio, 2, ',', ''));
    mygrid.cellById(rId, 5).setValue($.number(precio_cup, 2, ',', ''));
    mygrid.cellById(rId, 6).setValue($.number(precio_mlc, 2, ',', ''));
    mygrid.cellById(rId, 7).setValue($.number(precio_otro, 2, ',', ''));
    mygrid.cellById(rId, 8).setValue($.number(imp, 2, ',', ''));
    mygrid.cellById(rId, 9).setValue($.number(imp_moneda, 2, ',', ''));
    mygrid.setRowAttribute(rId, 'imp_cup', $.number(imp_cup, 2, ',', ''));
    mygrid.setRowAttribute(rId, 'imp_mlc', $.number(imp_mlc, 2, ',', ''));
    mygrid.setRowAttribute(rId, 'imp_otro', $.number(imp_otro, 2, ',', ''));
}

function calc_importe_total() {
    var imp = 0, portes = parseFloat($('#f-transporte').val().replace(/,/gi, '.'));
    var servicio = 0, porc_servicio = 0, imp_moneda = 0, servicio_cup = 0, portes_cup = 0;
    var imp_cup = 0, imp_mlc = 0, servicio_mlc = 0, portes_mlc = 0;
    var imp_otro = 0, servicio_otro = 0, portes_otro = 0, cantidad = 0, precio_base = 0;
    var cambio_cup = parseFloat($('#f-valor-cup').val().replace(/,/gi, '.'));
    var cambio_mlc = parseFloat($('#f-valor-mlc').val().replace(/,/gi, '.'));
    var cambio_otro = parseFloat($('#f-valor-otro').val().replace(/,/gi, '.'));
    portes_cup = portes.toFixed(0) * cambio_cup;
    portes_mlc = portes * cambio_mlc;
    portes_otro = portes * cambio_otro;
    mygrid.forEachRow(function (id) {
        if (mygrid.cellById(id, 1).getValue() != '' && mygrid.cellById(id, 2).getValue() != '') {
            cantidad = parseFloat(mygrid.cellById(id, 3).getValue().replace(/,/gi, '.'));
            precio_base = mygrid.getRowAttribute(id, 'precio_base').replace(/,/gi, '.');
            imp += parseFloat(mygrid.cellById(id, 8).getValue().replace(/,/gi, '.'));
            imp_moneda += parseFloat(mygrid.cellById(id, 9).getValue().replace(/,/gi, '.'));
            //imp_cup += cantidad * parseFloat(mygrid.getRowAttribute(id, 'imp_cup').replace(/,/gi, '.'));
            //imp_mlc += cantidad * parseFloat(mygrid.getRowAttribute(id, 'imp_mlc').replace(/,/gi, '.'));
            //imp_otro += cantidad * parseFloat(mygrid.getRowAttribute(id, 'imp_otro').replace(/,/gi, '.'));
            imp_cup += parseFloat(mygrid.getRowAttribute(id, 'imp_cup').replace(/,/gi, '.'));
            imp_mlc += parseFloat(mygrid.getRowAttribute(id, 'imp_mlc').replace(/,/gi, '.'));
            imp_otro += parseFloat(mygrid.getRowAttribute(id, 'imp_otro').replace(/,/gi, '.'));
        }
    });
    if (imp > transporte_free) {
        portes = 0;
        portes_cup = 0;
        portes_mlc = 0;
        portes_otro = 0;
    }

    porc_servicio = parseFloat($('#f-porc-servicio').val().replace(/,/gi, '.'));
    if (porc_servicio > 0) {
//servicio = imp * (porc_servicio / 100);
        servicio = 0; //MARCAMOS SIEMPRE A CERO PUES SE AÑADE A LAS LINEAS
        //servicio_cup = imp_cup * (porc_servicio / 100);
        servicio_cup = 0; //MARCAMOS SIEMPRE A CERO PUES SE AÑADE A LAS LINEAS
        servicio_mlc = 0; //MARCAMOS SIEMPRE A CERO PUES SE AÑADE A LAS LINEAS
        servicio_otro = 0; //MARCAMOS SIEMPRE A CERO PUES SE AÑADE A LAS LINEAS
    }


    $('#f-importe-base').val($.number(imp, 2, ',', ''));
    $('#f-importe-base-cup').val($.number(imp_cup, 2, ',', ''));
    $('#f-importe-base-mlc').val($.number(imp_mlc, 2, ',', ''));
    $('#f-importe-base-otro').val($.number(imp_otro, 2, ',', ''));
    $('#f-importe-servicio').val($.number(servicio, 2, ',', ''));
    $('#f-importe-servicio-cup').val($.number(servicio_cup, 2, ',', ''));
    $('#f-importe-servicio-mlc').val($.number(servicio_mlc, 2, ',', ''));
    $('#f-importe-servicio-otro').val($.number(servicio_otro, 2, ',', ''));
    imp += portes + servicio;
    imp_cup += portes_cup + servicio_cup;
    imp_mlc += portes_mlc + servicio_mlc;
    imp_otro += portes_otro + servicio_otro;
    /*
     if (porc_servicio > 0 && $('#f-fpago').val() === 'USD efectivo') {
     imp = Math.round(imp, 0);
     }
     * 
     */
    if ($('#f-fpago').val() === 'USD efectivo') {
        imp = Math.round(imp, 0);
    }
    $('#f-importe-total').val($.number(imp, 2, ',', ''));
    $('#f-importe-total-cup').val($.number(imp_cup, 2, ',', ''));
    $('#f-importe-total-mlc').val($.number(imp_mlc, 2, ',', ''));
    $('#f-importe-total-otro').val($.number(imp_otro, 2, ',', ''));
}

function add_row() {
    var id = (new Date()).valueOf() + '' + Math.round(Math.random() * 1000, 0);
    mygrid.addRow(id, "");
    mygrid.cellById(id, 0).setValue('img/grid-icons/search.png^Buscar/Añadir Producto^javascript:search_product(' + id + ');^_self');
    mygrid.cellById(id, 3).setValue('1');
    mygrid.cellById(id, 4).setValue('0,00');
    mygrid.cellById(id, 5).setValue('0,00');
    mygrid.cellById(id, 6).setValue('0,00');
    mygrid.cellById(id, 7).setValue('0,00');
    mygrid.cellById(id, 8).setValue('0,00');
    mygrid.cellById(id, 9).setValue('0,00');
    mygrid.cellById(id, mygrid.getColumnsNum() - 1).setValue('img/grid-icons/remove.png^Eliminar Línea^javascript:delete_row(' + id + ');^_self');
    mygrid.selectRowById(id, false, true, false);
    return id;
}

function search_product(id) {
    var status = 1, msg = '';
    mygrid.editStop();
    //mygrid.selectCell(mygrid.getRowIndex(rowId), 5, false, false, false, false);

    if (status == 1) {
        rowId = id;
        //$('#txt-product').val('');
        //$('#msp-lote').empty().append('<option data-lote="-1">Selecciona Lote/Caducidad</option>');
        $('#msp-art,#msp-cantidad,#msp-stock-min,#msp-stock-max').val('');
        $('#tbl-stocks tbody').empty();
        $('#msp-art').data('id', '').data('art', '').data('hash', '').data('tipo', '').data('precio', '');
        $('#msp-art').trigger('chosen:updated');
        $('#modalSearchProduct').modal('show');
        spd = {}; //inicializamos el 
        window.setTimeout(function () {
            $('#txt-product').focus();
        }, 600);
    } else {
        $.niftyNoty({
            type: 'danger',
            title: 'Añadir Producto',
            message: msg,
            container: 'floating',
            timer: 3000
        });
    }
}
function delete_row(id) {
    var status = 1, msg = '';

    if (status == 1) {
        if (mygrid.cellById(id, 2).getValue() != '') {
            if (confirm("¿Deseas eliminar esta línea: " + mygrid.cellById(id, 1).getValue() + '/' + mygrid.cellById(id, 2).getValue() + "?")) {
                mygrid.deleteRow(id);
                calc_importe_total();
                $.niftyNoty({
                    type: 'success',
                    title: 'Eliminar Línea',
                    message: 'Línea eliminada correctamente',
                    container: 'floating',
                    timer: 3000
                });
            }
        }
    } else {
        $.niftyNoty({
            type: 'danger',
            title: 'Eliminar Línea',
            message: msg,
            container: 'floating',
            timer: 3000
        });
    }
}

function init_grid() {
    var field = 'ed';
    if (action == 'update') {
//field = 'ro';
    }
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath(libgrid + "/imgs/");
    mygrid.setHeader(",Código,Descripción,Cantidad,Precio USD,Precio CUP,Precio MLC,Precio Otro,Importe USD,Importe,"); //14
    mygrid.setInitWidths("25,50,400,60,80,80,80,80,80,80,40");
    mygrid.setColAlign("center,right,left,right,right,right,right,right,right,right,center");
    mygrid.setColTypes("img,ro,ro," + field + ",ro,ro,ro,ro,ro,ro,img");
    dhtmlXCalendarObject.prototype.langData["es"] = {
        dateformat: '%d/%m/%Y',
        monthesFNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        monthesSNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        daysFNames: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
        daysSNames: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        weekstart: 1,
        weekname: "w",
        today: "Hoy",
        clear: "Borrar"
    };
    dhtmlXCalendarObject.prototype.lang = 'es';
    mygrid.init();
    mygrid.attachEvent("onEditCell", function (stage, rId, cInd, nValue, oValue) {
        //console.debug('stage: ' + stage);
        var status, msg;
        if (stage == 1 && ($.inArray(cInd, [3]) >= 0) && this.editor && this.editor.obj) {
            $(this.editor.obj).attr('style', 'text-align:right');
            $(this.editor.obj).select();
        }
        if (stage == 2) {
            //colocamos el codigo cuando se modifica una celda
            rowId = rId;
            var mresult = true;
            switch (cInd) {
                case 3://cantidad
                    var n, c;
                    n = nValue.replace(/,/gi, '.');
                    if ($.isNumeric(n)) {
                        nValue = $.number(parseFloat(n), 0, ',', '');
                    }
                    //c = $.number((n * parseFloat(mygrid.cellById(rId, 6).getValue().replace(/,/gi, '.'))) / 100, 4, ',', '');
                    window.setTimeout(function () {
                        mygrid.cellById(rId, cInd).setValue(nValue);
                        //mygrid.cellById(rId, 6).setValue(c);//asginamos coste calculado
                        calc_importe_lin(rId);
                        calc_importe_total();
                        //calc_importe_iva();
                        //mygrid.selectCell(mygrid.getRowIndex(rId), 8, false, false, true, true);
                    }, 100);
                    break;
            }

            return mresult;
        }
    });
}

function load_lines(items) {
    $.each(items, function (i, v) {
        var importe_moneda = v.ximporte_cup;
        var id = add_row();
        mygrid.cellById(id, 0).setValue('');
        mygrid.cellById(id, 1).setValue(v.xarticulo_id);
        mygrid.cellById(id, 2).setValue(v.xarticulo);
        mygrid.cellById(id, 3).setValue($.number(v.xcantidad, 0, ',', ''));
        mygrid.cellById(id, 4).setValue($.number(v.xprecio, 2, ',', ''));
        mygrid.cellById(id, 5).setValue($.number(v.xprecio_cup, 2, ',', ''));
        mygrid.cellById(id, 6).setValue($.number(v.xprecio_mlc, 2, ',', ''));
        mygrid.cellById(id, 7).setValue($.number(v.xprecio_otro, 2, ',', ''));
        mygrid.cellById(id, 8).setValue($.number(v.ximporte, 2, ',', ''));
        mygrid.cellById(id, 9).setValue($.number(importe_moneda, 2, ',', ''));
        mygrid.setRowAttribute(id, 'xhash', v.xhash);
        //mygrid.setRowAttribute(id, 'xdate', v.xdate);
        //mygrid.setRowAttribute(id, 'xtipo', v.xtipo);
        //mygrid.setRowAttribute(id, 'xcanjeado', v.xcanjeado);
        mygrid.setRowAttribute(id, 'xestado', v.xestado);
        mygrid.setRowAttribute(id, 'xlin_id', v.xlin_id);
        mygrid.setRowAttribute(id, 'xseguro', v.xseguro);
        mygrid.setRowAttribute(id, 'imp_cup', $.number(v.ximporte_cup, 2, ',', ''));
        mygrid.setRowAttribute(id, 'imp_mlc', $.number(v.ximporte_mlc, 2, ',', ''));
        mygrid.setRowAttribute(id, 'imp_otro', $.number(v.ximporte_otro, 2, ',', ''));
        mygrid.setRowAttribute(id, 'precio_base', $.number(v.xprecio_base, 2, ',', ''));
        if (v.xestado == 3) {
            mygrid.setRowTextStyle(id, style_row_cancel);
        }
    });
    return true;
}

function autocomplete() {
    $('#f-cliente').autocomplete({
        source: function (request, response) {
            var cmd = 'module=pedidos&method=search-client&t=' + request.term;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    //console.debug(d);
                    if (d.data != null) {
                        response($.map(d.data, function (item) {
                            var label = item.xcliente_id + ' - ' + item.xcliente;
                            var p = {
                                label: label,
                                value: label,
                                cli: item.xcliente,
                                cli_id: item.xcliente_id
                            };
                            return p;
                        }));
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(textStatus);
                }
            });
        },
        minLength: 3,
        select: function (event, ui) {
            spd = ui.item; //asignamos el producto seleccionado a una variable glogal
            //console.debug(ui.item);
            $('#f-cliente').data('id', spd.cli_id);
            $('#f-cliente').data('cli', spd.cli);
            load_direcciones(0);
        },
        open: function () {
            $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
        },
        close: function () {
            $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        }
    });
    $('#cliente-clear-autocomplete').click(function () {
        $('#f-cliente').val('').data('id', '').data('cli', '').focus();
    });
    $('#msp-art-auto').autocomplete({
        source: function (request, response) {
            var cmd = 'module=pedidos&method=search-product&t=' + request.term;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    //console.debug(d);
                    if (d.data != null) {
                        response($.map(d.data, function (item) {
                            var label = item.xarticulo_id + ' - ' + item.xarticulo;
                            var p = {
                                label: label,
                                value: item.xarticulo,
                                art: item.xarticulo,
                                art_id: item.xarticulo_id,
                                hash: item.xarticulo_id,
                                tipo: item.xarticulo_id,
                                precio: item.xprecio
                            };
                            return p;
                        }));
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(textStatus);
                }
            });
        },
        minLength: 3,
        select: function (event, ui) {
            var ctrl = 'msp-art-auto';
            spd = ui.item; //asignamos el producto seleccionado a una variable glogal
            //console.debug(ui.item);
            $('#' + ctrl).data('id', spd.art_id);
            $('#' + ctrl).data('art', spd.art);
            $('#' + ctrl).data('hash', spd.hash);
            $('#' + ctrl).data('tipo', spd.tipo);
            $('#' + ctrl).data('precio', spd.precio);
            //load_articulo_stock($('#' + ctrl).data('id'));
        },
        open: function () {
            $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
        },
        close: function () {
            $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        }
    });
    $('#articulo-clear-autocomplete').click(function () {
        $('#msp-art-auto').val('').data('id', '').data('art', '').focus();
        $('#msp-cantidad-auto').val('');
    });
}

var alert_tpl = '\
    <div class="alert alert-danger fade in">\
        <button class="close" data-dismiss="alert"><span>&times;</span></button>\
        <div class="alert-content">\
            <div><strong>Oh snap!</strong> Change a few things up and try submitting again.</div>\
            <div><strong>Oh snap!</strong> Change a few things up and try submitting again.</div>\
        </div>\
    </div>\
';
var alert_widget_tpl = '\n\
    <div class="alert alert-danger fade in">\n\
        <button class="close" data-dismiss="alert"><span>&times;</span></button>\n\
        <strong><span id="alert-title">Oh snap!</span></strong> <span id="alert-text">Change a few things up and try submitting again.</span>\n\
    </div>\n\
';
var tpl_img_tracking = '\n\
    <div class="row">\n\
        <div class="col-md-12">\n\
            <img class="img-responsive" src="/img/envios/0032-3438-0012-015-01.jpg"/>\n\
            <p>Tracking CU000349888RT Referencia 0032-3438-0012-015-01</p>\n\
        </div>\n\
    </div>\n\
';
function aceptar_servicio() {
    $('#f-porc-servicio').val($.number(servicio, 2, ',', ''));
    mygrid.forEachRow(function (id) {
        if (mygrid.cellById(id, 1).getValue() != '' && mygrid.cellById(id, 2).getValue() != '') {
            calc_importe_lin(id);
        }
    });
    calc_importe_total();
}
function rechazar_servicio() {
    $('#f-porc-servicio').val('0,00');
    mygrid.forEachRow(function (id) {
        if (mygrid.cellById(id, 1).getValue() != '' && mygrid.cellById(id, 2).getValue() != '') {
            calc_importe_lin(id);
        }
    });
    calc_importe_total();
}


var tpl_lin_mov = '\
    <tr>\
        <td>Fecha</td>\
        <td>Doc.</td>\
        <td>Artículo</td>\
        <td>Componente</td>\
        <td>Cantidad</td>\
        <td>Almacén</td>\
        <td>Concepto</td>\
        <td>Stock Antes</td>\
        <td>Stock Después</td>\
    </tr>';
var tpl_lin_his = '\
    <tr>\
        <td>Fecha</td>\
        <td>Estado</td>\
        <td>Usuario</td>\
        <td>Descripción</td>\
    </tr>';
var tpl_lin_sto = '\
    <tr>\
        <td>Componente</td>\
        <td class="text-right">Unidades</td>\
        <td>Almacén</td>\
        <td class="text-right">Stock</td>\
        <td class="text-right">Stock</td>\
    </tr>';

function checkPermisoAlmacen() {
    if ($.inArray(parseInt(ped_estado), [12, 17, 6, 7, 23, 24]) >= 0) {
        $('#f-almacen').attr('disabled', true);
    } else {
        $('#f-almacen').attr('disabled', false);
    }
}

function load_direcciones(modal = 1) {
    //var cmd = 'module=clientes&method=dir-envios&cli=' + $('#f-cliente').val();
    var cmd = 'module=clientes&method=dir-envios&cli=' + $('#f-cliente').data('id');
    $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
        success: function (d) {
            if (d.length > 0) {
                dir_envios = d;
                $('#mde-envio').empty();

                $.each(dir_envios, function (i, v) {
                    $('#mde-envio').append('<option value="' + v.xenvio_id + '">' + v.xname + ' ' + v.xname2 + ' ' + v.xapellido1 + ' ' + v.xapellido2 + '</option>');
                });

                //console.debug(modal);

                if (modal == 1) {
                    $('#btn-add-direnvio').attr('disabled', false);
                    $('#modalDirEnvio').modal('show');
                } else {
                    if ($('#mde-envio option').length == 1) {
                        $('#btn-add-dir-envio').click();
                    }
                }
            } else {
                $.niftyNoty({
                    type: 'danger',
                    title: 'Direcciones de Envío',
                    message: 'No existen direcciones de envío para seleccionar.',
                    container: 'floating',
                    timer: 3000
                });
                if (modal == 1) {
                    $('#mde-envio').empty();
                    $('#modalDirEnvio').modal('show');
                    $('#btn-add-dir-envio').attr('disabled', true);
                }
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}

function load_articulo_stock(art) {
    //var cmd = 'module=articulos&method=get-stock&art=' + $('#msp-art2').data('id');
    var cmd = 'module=articulos&method=get-stock&art=' + art;
    $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
        success: function (d) {
            if (d.status == 1) {
                $('#msp-stock-min').val(d.stock_min);
                $('#msp-stock-max').val(d.stock_max);
                $('#tbl-stocks tbody').empty();
                $.each(d.items, function (i, v) {
                    var e = $(tpl_lin_sto);

                    $(e).find('td:eq(0)').html(v.xcomponente_id + '-' + v.xcomponente);
                    $(e).find('td:eq(1)').html(v.xcantidad);
                    $(e).find('td:eq(2)').html(v.xalmacen_id + '-' + v.xalmacen);
                    $(e).find('td:eq(3)').html(v.xstock);
                    $(e).find('td:eq(4)').html(v.xstock_max);

                    $('#tbl-stocks tbody').append(e);
                });
            } else {
                $.niftyNoty({
                    type: 'danger',
                    title: 'Stocks',
                    message: 'Error no controlado.',
                    container: 'floating',
                    timer: 3000
                });
            }

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}

function load_articulo_stock_auto(art) {
    var cmd = 'module=articulos&method=get-stock&art=' + art;
    $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
        success: function (d) {
            if (d.status == 1) {
                //$('#msp-stock-min').val(d.stock_min);
                //$('#msp-stock-max').val(d.stock_max);
                $('#tbl-stocks-auto tbody').empty();
                $.each(d.items, function (i, v) {
                    var e = $(tpl_lin_sto);

                    $(e).find('td:eq(0)').html(v.xcomponente_id + '-' + v.xcomponente);
                    $(e).find('td:eq(1)').html(v.xcantidad);
                    $(e).find('td:eq(2)').html(v.xalmacen_id + '-' + v.xalmacen);
                    $(e).find('td:eq(3)').html(v.xstock);
                    $(e).find('td:eq(4)').html(v.xstock_max);

                    $('#tbl-stocks-auto tbody').append(e);
                });
            } else {
                $.niftyNoty({
                    type: 'danger',
                    title: 'Stocks',
                    message: 'Error no controlado.',
                    container: 'floating',
                    timer: 3000
                });
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}