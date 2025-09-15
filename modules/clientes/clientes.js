var e, tmp;

$(document).ready(function () {

    $('#btn-add-dir').click(function () {
        var status = 1, msg = '';
        if ($('#f-cliente-id').val() == '') {
            status = 0;
            msg += '<div>Tiene que seleccionar un cliente o dar de alta un nuevo cliente.</div>';
        }

        if (status == 1) {
            $('#mde-id').val('NUEVO');
            $('#modalDirEnvio').modal('show');
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Nueva dirección',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#btn-close').click(function () {
        window.close();
    });
    //$('#f-provincia').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-provincia').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-municipio').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-pais').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-municipio').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-diametro').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    /*
     * $('#ev-mascota').trigger('chosen:updated');
     * //$('#mascota').trigger('chosen:updated');
     //$('#s2id_mascota span.select2-chosen').html($('#mascota option:selected').text());
     * 
     * 
     * 
     */

    /*$('#f-pais').change(function () {
     $('#f-provincia').empty().append('<option value="">Selecciona una provincia</option>').trigger('chosen:updated');
     $('#f-municipio').empty().append('<option value="">Selecciona un municipio</option>').trigger('chosen:updated');
     load_provincias();
     });
     $('#f-provincia').change(function () {
     $('#f-municipio').empty().append('<option value="">Selecciona un municipio</option>').trigger('chosen:updated');
     load_municipios();
     });
     $('#f-municipio').change(function () {
     $('#f-municipio-desc').val($('#f-municipio option:selected').text());
     });*/

    $('#mde-btn-save').click(function () {
        var status = 1, msg = '';

        if ($('#mde-ship-nombre').val() == '') {
            status = 0;
            msg += '<div>Campo Nombre de la dirección de envío es obligatorio.</div>';
        }
        if ($('#mde-ship-apellido1').val() == '') {
            status = 0;
            msg += '<div>Campo Apellido 1 de la dirección de envío es obligatorio.</div>';
        }
        if ($('#mde-ship-apellido2').val() == '') {
            status = 0;
            msg += '<div>Campo Apellido 2 de la dirección de envío es obligatorio.</div>';
        }
        if ($('#mde-ship-dir1').val() == '') {
            status = 0;
            msg += '<div>Campo Dirección de la dirección de envío es obligatorio.</div>';
        }
        if ($('#mde-ship-city').val() == '' || $('#mde-ship-city').val() == null) {
            status = 0;
            msg += '<div>Campo Municipio de la dirección de envío es obligatorio. Que el pedido llegue correctamente depende de este dato.</div>';
        }
        var zip = ['00000', '11111', '22222', '33333', '44444', '55555', '66666', '77777', '88888', '99999'];
        if ($('#mde-ship-zipcode').val() == '' || $.inArray($('#mde-ship-zipcode').val(), zip) >= 0) {
            status = 0;
            msg += '<div>Campo Código Postal / ZIP de la dirección de envío es obligatorio.</div>';
        } else {
            if (!$.isNumeric($('#mde-ship-zipcode').val())) {
                status = 0;
                msg += '<div>Campo Código Postal / ZIP tiene que ser numérico.</div>';
            }
            if ($('#mde-ship-zipcode').val().length != 5) {
                status = 0;
                msg += '<div>Campo Código Postal / ZIP tiene que tener 5 dígitos.</div>';
            }
        }
        if ($('#mde-ship-provincia').val() == '' || $('#mde-ship-provincia').val() == null) {
            status = 0;
            msg += '<div>Campo Provincia de la dirección de envío es obligatorio.</div>';
        }
        if ($('#mde-ship-phone').val() == '') {
            status = 0;
            msg += '<div>Campo Teléfono de la dirección de envío es obligatorio.</div>';
        } else {
            if (!$.isNumeric($('#mde-ship-phone').val())) {
                status = 0;
                msg += '<div>Campo Teléfono tiene que ser numérico.</div>';
            }
        }
        if ($('#mde-ship-numero').val() == '') {
            status = 0;
            msg += '<div>Campo Número de la dirección de envío es obligatorio.</div>';
        }
        if ($('#mde-ship-ci').val() == '' || $('#mde-ship-ci').val() == '00000000000') {
            status = 0;
            msg += '<div>Campo Carnet de Identidad en la dirección de envío es obligatorio.  Que el pedido llegue correctamente depende de este dato.</div>';
        } else {
            if (!$.isNumeric($('#mde-ship-ci').val())) {
                status = 0;
                msg += '<div>Campo Carnet de Identidad tiene que ser numérico.</div>';
            }
            if ($('#mde-ship-ci').val().length != 11) {
                status = 0;
                msg += '<div>Campo Carnet de Identidad tiene que tener 11 dígitos.</div>';
            }
        }

        if (status == 1) {
            var cmd = 'module=clientes&method=save-envio&id=' + $('#mde-id').val()
                    + '&n1=' + $('#mde-ship-nombre').val() + '&n2=' + $('#mde-ship-nombre2').val()
                    + '&a1=' + $('#mde-ship-apellido1').val() + '&a2=' + $('#mde-ship-apellido2').val()
                    + '&d1=' + $('#mde-ship-dir1').val() + '&n=' + $('#mde-ship-numero').val()
                    + '&a=' + $('#mde-ship-apartamento').val() + '&p=' + $('#mde-ship-piso').val()
                    + '&prov=' + $('#mde-ship-provincia').val() + '&cit=' + $('#mde-ship-city').val()
                    + '&cp=' + $('#mde-ship-zipcode').val() + '&rpt=' + $('#mde-ship-reparto').val()
                    + '&c1=' + $('#mde-ship-entre-calle1').val() + '&c2=' + $('#mde-ship-entre-calle2').val()
                    + '&tlf=' + $('#mde-ship-phone').val() + '&ci=' + $('#mde-ship-ci').val()
                    + '&cli=' + $('#f-cliente-id').val();
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('#modalDirEnvio').modal('hide');
                        load_direcciones_envios();
                        $.niftyNoty({
                            type: 'success',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                }
            });
        } else {
            $('#mdlValidate .modal-body').html(msg);
            $('#mdlValidate').modal('show');
        }
    });
    $('#mde-ship-provincia').change(function () {
        $('#mde-ship-city').empty().append('<option value="">Selecciona Municipio</option>');
        $.each(municipios, function (i, v) {
            if (v.xprovincia == $('#mde-ship-provincia').val())
                $('#mde-ship-city').append('<option value="' + v.xmunicipio + '">' + v.xmunicipio.replace(/_/gi, ' ') + '</option>');
        });
    });

    $('#tbl-pedidos').on('click', '.rescue', function () {
        var e = $(this).parent().parent();
        var pedido = $(e).find(':eq(0) a').text();

        if (confirm("¿Desea enviar un rescate al pedido " + pedido + "?")) {
            var cmd = 'module=pedidos&method=rescue&id=' + pedido;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $.niftyNoty({
                            type: 'success',
                            title: 'Rescate Pedido',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                }
            });
        }
    });

    $('#tbl-pedidos').on('click', '.view-details', function () {
        var e = $(this).parent().parent();
        var pedido = $(e).find(':eq(0) a').text();

        var cmd = 'module=pedidos&method=view-details&id=' + pedido;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#tbl-pedidos-lin tbody').empty();
                    $.each(d.items, function (i, v) {

                        var e = $(tpl_pedidos_lin);
                        $(e).find('td:eq(0)').html(v.xpedido_id);
                        $(e).find('td:eq(1)').html(v.xfecha_pedido_format);
                        $(e).find('td:eq(2)').html(v.xlin_id);
                        $(e).find('td:eq(3)').html(v.xarticulo_id + ' - ' + v.xarticulo);
                        $(e).find('td:eq(4)').html(v.xcorte_id);
                        $(e).find('td:eq(5)').html(v.xtracking);
                        $(e).find('td:eq(6)').html(v.xfecha_salida);
                        $(e).find('td:eq(7)').html(v['xestado-actual']);
                        $('#tbl-pedidos-lin tbody').append(e);
                    });
                    $('#mdlViewDetails').modal('show');
                } else {
                    $.niftyNoty({
                        type: 'danger',
                        title: 'Eliminar Pedido',
                        message: d.msg,
                        container: 'floating',
                        timer: 3000
                    });
                }
            }
        });
    });

    $('#tbl-pedidos').on('click', '.remove', function () {
        var e = $(this).parent().parent();
        var pedido = $(e).find(':eq(0) a').text();

        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=pedidos&method=del&id=' + pedido + '&row=' + $(e).index();
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('#tbl-pedidos tbody tr:eq(' + d.row + ')').remove();
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Eliminar Pedido',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                }
            });
        }
    });

    $('#tbl-envios').on('click', '.remove-dir', function () {
        var e = $(this).parent().parent();
        var dir = $(e).data('id');

        if (confirm('Estás seguro de eliminar la dirección de envío número ' + dir + '?')) {
            var cmd = 'module=clientes&method=del-dir&id=' + dir + '&row=' + $(e).index();
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('#tbl-envios tbody tr:eq(' + d.row + ')').remove();
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Eliminar Pedido',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                }
            });
        }
    });

    $('#tbl-envios').on('click', '.edit-dir', function () {
        var e = $(this).parent().parent();

        $('#mde-id').val($(e).data('id'));
        $('#mde-ship-nombre').val($(e).find('td:eq(0)').html());
        $('#mde-ship-nombre2').val($(e).find('td:eq(1)').html());
        $('#mde-ship-apellido1').val($(e).find('td:eq(2)').html());
        $('#mde-ship-apellido2').val($(e).find('td:eq(3)').html());
        $('#mde-ship-dir1').val($(e).find('td:eq(4)').html());
        $('#mde-ship-numero').val($(e).find('td:eq(5)').html());
        $('#mde-ship-apartamento').val($(e).find('td:eq(6)').html());
        $('#mde-ship-piso').val($(e).find('td:eq(7)').html());

        $('#mde-ship-zipcode').val($(e).find('td:eq(8)').html());
        $('#mde-ship-reparto').val($(e).find('td:eq(10)').html());
        $('#mde-ship-entre-calle1').val($(e).find('td:eq(11)').html());
        $('#mde-ship-entre-calle2').val($(e).find('td:eq(12)').html());
        $('#mde-ship-provincia').val($(e).find('td:eq(13)').html()).change();
        $('#mde-ship-city').val($(e).find('td:eq(9)').html());
        $('#mde-ship-phone').val($(e).find('td:eq(14)').html().replace(/\(\+53\) /gi, ''));
        $('#mde-ship-ci').val($(e).find('td:eq(15)').html());

        $('#modalDirEnvio').modal('show');
    });
    $('#tbl-pedidos').on('click', '.view-tracking', function () {
        $('#cuba-tracking').val($(this).data('track'));
        $('#btn-cuba-tracking').click();
    });

    $('#btn-view-movil').click(function () {
        var cmd = 'https://premium.whitepages.com/phone/' + $('#f-movil').val();
        window.open(cmd);
    });
    $('#btn-view-ip').click(function () {
        var cmd = 'https://www.abuseipdb.com/check/' + $('#f-ip').val();
        window.open(cmd);
    });

    $('#cli-notas').summernote({
        height: 400
    });

    $('#btn-new').click(function () {
        location.href = '?module=clientes';
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-clientes';
    });
    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';

        if ($('#f-cliente').val() == '') {
            status = 0;
            msg += '<div>El campo Nombre del Cliente es obligatorio.</div>';
        }
        if ($('#f-email').val() == '') {
            status = 0;
            msg += '<div>El campo Email del Cliente es obligatorio.</div>';
        }
        if (!$.isNumeric($('#f-riesgo').val().replace(/,/gi, '.'))) {
            status = 0;
            msg += '<div>El campo Riesgo del Cliente es obligatorio.</div>';
        }
        if (action == 'insert') {
            if ($('#f-pwd').val() == '') {
                status = 0;
                msg += '<div>El campo Contraseña del Cliente es obligatorio cuando se registra el cliente por primera vez.</div>';
            }
        }

        if (status == 1) {
            $('#img-loading').removeClass('hidden');
            $('#btn-save').attr('disabled', true);

            var cmd = 'module=clientes&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            cmd += '&action=' + action;
            cmd += '&xcountry=' + $('#f-prefijo option:selected').data('country');
            if (action == 'update')
                cmd += '&xcliente_id=' + $('#f-cliente-id').val();
            cmd += '&xnotas=' + encodeURIComponent($('#cli-notas').code());
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        if (d.action == 'insert') {
                            action = 'update';
                            $('#f-cliente-id').val(d.id);
                        }
                        $.niftyNoty({
                            type: 'success',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Guardar datos',
                            message: d.msg,
                            container: 'page'
                        });
                    }
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Guardar datos',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });

    if (action_web == 'new-dir') {
        setTimeout(function () {
            $('ul.nav.nav-tabs li:eq(1) a').click();
        }, 600);
    }
});

function load_direcciones_envios() {
    var cmd = 'module=clientes&method=dir-envios&cli=' + $('#f-cliente-id').val();
    $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
        success: function (d) {
            $('#tbl-envios tbody').empty();
            $.each(d, function (i, v) {
                var e = $(tpl_dir_envio);
                $(e).data('id', v.xenvio_id);
                $(e).find('td:eq(0)').html(v.xname);
                $(e).find('td:eq(1)').html(v.xname2);
                $(e).find('td:eq(2)').html(v.xapellido1);
                $(e).find('td:eq(3)').html(v.xapellido2);
                $(e).find('td:eq(4)').html(v.xdir1);
                $(e).find('td:eq(5)').html(v.xnumero);
                $(e).find('td:eq(6)').html(v.xapartamento);
                $(e).find('td:eq(7)').html(v.xpiso);
                $(e).find('td:eq(8)').html(v.xzipcode);
                $(e).find('td:eq(9)').html(v.xcity);
                $(e).find('td:eq(10)').html(v.xreparto);
                $(e).find('td:eq(11)').html(v.xentre_calle1);
                $(e).find('td:eq(12)').html(v.xentre_calle2);
                $(e).find('td:eq(13)').html(v.xprovincia);
                $(e).find('td:eq(14)').html(v.xphone);
                $(e).find('td:eq(15)').html(v.xci);
                $('#tbl-envios tbody').append(e);
            });
        }
    });
}

var tpl_pedidos_lin = '\
    <tr>\
        <td>1</td>\
        <td>2</td>\
        <td>3</td>\
        <td>4</td>\
        <td>5</td>\
        <td>6</td>\
        <td>7</td>\
        <td>8</td>\
    </tr>\
';

var tpl_dir_envio = '\
    <tr data-id="XXX">\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td>XXX</td>\
        <td class="text-center">\
            <a class="btn btn-xs btn-default add-tooltip edit-dir" data-toggle="tooltip" href="javascript:void(0)" data-original-title="Editar" data-container="body"><i class="fa fa-pencil"></i></a>\
        </td>\
    </tr>\
';

function load_provincias() {
    var cmd = 'module=ubicaciones&method=provincias&pais=' + $('#f-pais').val();
    $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
        success: function (d) {
            $.each(d, function (i, v) {
                $('#f-provincia').append('<option value="' + v.xprovincia_id + '">' + v.xprovincia + '</option>')
            });
            $('#f-provincia').trigger('chosen:updated');
        }
    });
}
function load_municipios() {
    var cmd = 'module=ubicaciones&method=municipios&pais=' + $('#f-pais').val() + '&provincia=' + $('#f-provincia').val();
    $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
        success: function (d) {
            $.each(d, function (i, v) {
                $('#f-municipio').append('<option value="' + v.xmunicipio_id + '">' + v.xmunicipio + '</option>')
            });
            $('#f-municipio').trigger('chosen:updated');
        }
    });
}