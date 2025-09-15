$(document).ready(function () {
    $('#table-panel').on('click', '.fa.fa-paper-plane', function () {
        var pedido = $(this).data('id');
        if (confirm('¿Desea enviar un email de confirmación de pago al cliente del pedido ' + pedido + '?')) {
            var cmd = 'module=pedidos&method=send-confirm-efectivo&ord=' + pedido;
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        //$('button[name=refresh]').click();
                        $.niftyNoty({
                            type: 'success',
                            title: 'Enviar Confirmación de Pago',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Enviar Confirmación de Pago',
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
    });
    $('#btn-dl-xls').click(function () {
        var cmd = 'api-app.php?module=pedidos&method=dl-xls';
        //console.debug(cmd);
        location.href = cmd;
        //window.open(cmd); //se abre en otra ventana, si es pdf se carga en el navegador si no se descarga
    });
    $('#btn-dl-doc').click(function () {
        var ped = [], cmd = '';
        $('#table-panel .chk-alb:checked').each(function (i, v) {
            ped.push($(v).data('ped'));
        });
        cmd = 'api-app.php?module=pedidos&method=dl-doc&albs=' + ped.join(';');
        //console.debug(cmd);
        //location.href = cmd;
        window.open(cmd);
    });
    $('#btn-add-new').click(function () {
        var form = 'pedidos';
        if (punto_venta != '')
            form = 'pedidos-pv';
        location.href = 'index.php?module=' + form;
    });
    $('#table-panel').on('click', '.fa.fa-edit', function () {
        var form = 'pedidos';
        var referencia = $(this).data('ref');
        if (referencia.substring(0, 3) == 'PV-')
            form = 'pedidos-pv';
        var url = 'index.php?module=' + form + '&id=' + $(this).data('id');
        window.open(url);
    });
    $('#table-panel').on('click', '.fa.fa-life-ring', function () {
        if (confirm("¿Desea enviar un rescate a este pedido?")) {
            var cmd = 'module=pedidos&method=rescue&id=' + $(this).data('id');
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
    $('#table-panel').on('click', '.fa.fa-print', function () {
        var ped = [], cmd = '';
        ped.push($(this).data('id'));
        var module = 'dl-doc', ref = $(this).data('ref');

        if (ref.substr(0, 2) == 'MS') {
            module = 'dl-doc-ms';
        }
        if (ref.substr(0, 2) == 'PV') {
            module = 'dl-doc-pv';
        }
        if (ref.substr(0, 2) == 'AN') {
            //alert('Plantilla de impresión para pedidos allnovu no creado.');
            module = 'dl-doc-an';
        }

        //cmd = 'api-app.php?module=pedidos&method=dl-doc&id=' + ped.join(';');
        cmd = 'api-app.php?module=pedidos&method=' + module + '&id=' + $(this).data('id');
        window.open(cmd);
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=pedidos&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
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

    set_search();

});

function customSort(sortName, sortOrder, data) {
    var order = sortOrder === 'desc' ? -1 : 1
    switch (sortName) {
        case 'xfecha_format':
            data.sort(function (a, b) {
                var tmp, aa = '', bb = '';

                tmp = a[sortName];
                if (tmp != '') {
                    tmp = tmp.split('/');
                    aa = tmp[2] + '' + tmp[1] + '' + tmp[0];
                }
                tmp = b[sortName];
                if (tmp != '') {
                    tmp = tmp.split('/');
                    bb = tmp[2] + '' + tmp[1] + '' + tmp[0];
                }

                if (aa < bb) {
                    return order * -1
                }
                if (aa > bb) {
                    return order
                }
                return 0
            });
            break;
        case 'xpedido_id':
        case 'xejercicio':
        case 'xserie_id':
        case 'ximporte':
            data.sort(function (a, b) {
                var aa = +((a[sortName] + '').replace(/[^\d]/g, ''))
                var bb = +((b[sortName] + '').replace(/[^\d]/g, ''))
                if (aa < bb) {
                    return order * -1
                }
                if (aa > bb) {
                    return order
                }
                return 0
            });
            break;
        default:
            data.sort(function (a, b) {
                var aa = (a[sortName] + '');
                var bb = (b[sortName] + '');
                if (aa < bb) {
                    return order * -1
                }
                if (aa > bb) {
                    return order
                }
                return 0
            });
            break;
    }
}


function set_search() {
    var e = $(tpl_btn);
    var s = '.fixed-table-toolbar .columns.columns-right.btn-group';
    if ($(s).length == 1)
        $(s).append(e);

    $('#ms-articulo').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    $('#ms-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    $('#btn-show-modal-search').click(function () {
        $('#modalSearch').modal('show');
    });
    $('#btn-show-modal-search-entregados').click(function () {
        $('#modalSearchEntregados').modal('show');
    });
    $('#btn-show-modal-search-finalizados').click(function () {
        $('#modalSearchFinalizados').modal('show');
    });

    $('#btn-do-search-finalizados').click(function () {
        var cmd = 'module=pedidos&method=list-finalizados'
                + '&fi=' + $('#msf-fecha-desde input').val() + '&ff=' + $('#msf-fecha-hasta input').val()
                + '&resultados=' + $('#msf-resultados').val();
        //console.debug(cmd);
        $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
            success: function (d) {
                $('#table-panel').bootstrapTable('removeAll');
                $.each(d, function (i, v) {
                    $('#table-panel').bootstrapTable('insertRow', {index: i, row: v});
                });
                $.niftyNoty({
                    type: 'success',
                    title: 'Búsqueda realizada',
                    message: 'Búsqueda realizada con éxito.',
                    container: 'floating',
                    timer: 5000
                });
                $('#modalSearchFinalizados').modal('hide');
            }
        });
    });
    $('#btn-do-search-entregados').click(function () {
        var cmd = 'module=pedidos&method=list-entregados'
                + '&fi=' + $('#mse-fecha-desde input').val() + '&ff=' + $('#mse-fecha-hasta input').val()
                + '&resultados=' + $('#msf-resultados').val();
        //console.debug(cmd);
        $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
            success: function (d) {
                $('#table-panel').bootstrapTable('removeAll');
                $.each(d, function (i, v) {
                    $('#table-panel').bootstrapTable('insertRow', {index: i, row: v});
                });
                $.niftyNoty({
                    type: 'success',
                    title: 'Búsqueda realizada',
                    message: 'Búsqueda realizada con éxito.',
                    container: 'floating',
                    timer: 5000
                });
                $('#modalSearchEntregados').modal('hide');
            }
        });
    });

    $('#btn-do-search').click(function () {
        $('#img-loading').removeClass('hidden');
        $('#btn-do-search').attr('disabled', true);
        var cmd = 'module=pedidos&method=list&cli=' + $('#ms-cliente').val()
                + '&estado=' + $('#ms-estado').val() + '&pedido=' + $('#ms-pedido').val()
                + '&fi=' + $('#ms-fecha-desde input').val() + '&ff=' + $('#ms-fecha-hasta input').val()
                + '&revendedor=' + $('#ms-revendedor').val()
                + '&resultados=' + $('#ms-resultados').val();
        //console.debug(cmd);
        $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
            success: function (d) {
                $('#img-loading').addClass('hidden');
                $('#btn-do-search').attr('disabled', false);
                $('#table-panel').bootstrapTable('removeAll');
                $.each(d, function (i, v) {
                    $('#table-panel').bootstrapTable('insertRow', {index: i, row: v});
                });
                $.niftyNoty({
                    type: 'success',
                    title: 'Búsqueda realizada',
                    message: 'Búsqueda realizada con éxito.',
                    container: 'floating',
                    timer: 5000
                });
                $('#modalSearch').modal('hide');
            }
        });
    });
    $('#ms-fecha-desde .input-group.date,#ms-fecha-hasta .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        language: 'es'
    });
    $('#msf-fecha-desde .input-group.date,#msf-fecha-hasta .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        language: 'es'
    });
    $('#mse-fecha-desde .input-group.date,#mse-fecha-hasta .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        language: 'es'
    });
    $('.remove-value').click(function () {
        $(this).parent().prev().val('');
    });
}

var tpl_btn = '\
    <button id="btn-show-modal-search" class="btn btn-default" type="button" title="Búsqueda">\n\
        <i class="glyphicon glyphicon-search"></i>\n\
    </button>\n\
    <button id="btn-show-modal-search-entregados" class="btn btn-default" type="button" title="Búsqueda Pedidos Entregados">\n\
        <i class="glyphicon glyphicon-send"></i>\n\
    </button>\n\
    <button id="btn-show-modal-search-finalizados" class="btn btn-default" type="button" title="Búsqueda Pedidos Finalizados">\n\
        <i class="glyphicon glyphicon-grain"></i>\n\
    </button>\n\
';

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xpedido_id + '" class="btn btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.xpedido_id + '" class="btn btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

// Sample Format for Tracking Number Column.
// =================================================================
function formatoPedido(value, row) {
    //var s = '<div><input data-ped="' + value + '" type="checkbox" class="chk-ped"/>&nbsp;' + value + '</div>';
    var s = '<div>' + value + '</div>';
    s += '<small>' + row.xhash + '</small>';
    return s;
}
function formatoToolbar(value, row) {
    var btn_edit = '<button title="Editar" data-id="' + row.xpedido_id + '" data-ref="' + row.xhash + '" class="btn btn-info btn-xs btn-icon icon-sm fa fa-edit"></button>';
    var btn_print = '<button title="Imprimir" data-id="' + row.xpedido_id + '"  data-ref="' + row.xhash + '" class="btn btn-warning btn-xs btn-icon icon-sm fa fa-print"></button>';
    var btn_del = '<button title="Eliminar" data-id="' + row.xpedido_id + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-trash"></button>';
    var btn_rescue = '<button title="Rescatar" data-id="' + row.xpedido_id + '" class="btn btn-default btn-xs btn-icon icon-sm fa fa-life-ring"></button>';
    var btn_email = '<button title="Enviar confirmación de pago" data-id="' + row.xpedido_id + '" class="btn btn-purple btn-xs btn-icon icon-sm fa fa-paper-plane"></button>';
    if (rol != '1') {
        btn_del = '';
    }
    if (row.xestado == 'F')
        btn_del = '';
    if (punto_venta != '') {
        btn_rescue = '';
        btn_email = '';
    }
    return btn_edit + '\n' + btn_rescue + '\n' + btn_print + '\n' + btn_email + '\n' + btn_del;
}
function formatoProducto(value, row) {
    var art = [], art_join = '';
    if ('items' in row)
        $.each(row.items, function (i, v) {
            var art_ele = {
                art: v.xarticulo,
                cant: $.number(v.xcantidad, 0, ',', '')
            };

            art.push(art_ele);
        });

    $.each(art, function (i, v) {
        art_join += '<div style="font-size:10px;">' + v.cant + ' ' + v.art + '</div>';
    });

    var s = '<div>' + art_join + '</div>';
    if (row.xobs != '' && row.xobs != null) {
        s += '<div><strong>Notas: </strong>' + row.xobs + '</div>';
    }
    if (row.xchofer_id > 0) {
        s += '<div><strong>Chofer: </strong>' + row.xchofer + '</div>';
    }

    return s;
}
function formatoFechas(value, row) {
    var s = '<div>' + value + '</div>';
    if (row.xfecha_entregado_format != '' && row.xfecha_entregado_format != null) {
        s += '<div><small>Fecha Entregado: ' + row.xfecha_entregado_format + '</small></div>';
    }
    if (row.xfecha_finalizado_format != '' && row.xfecha_finalizado_format != null) {
        s += '<div><small>Fecha Finalizado: ' + row.xfecha_finalizado_format + '</small></div>';
    }
    return s;
}
function formatoCantidad(value, row) {
    var s = '';
    if (row.xestado == 'P')
        value = '';
    return s;
}
function formatoEstado(value, row) {
    var s = '', style = '';

    if (value == 'Finalizado') {
        style = 'font-weight: bold;text-transform: uppercase;color:black;';
    }


    s = '<span style="' + style + '" class="label label-' + row.xcolor + '">' + value + '</span>';
    //console.debug(s);
    return s;
}
function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
function formatoTransaccion(value, row) {
    var web = row.xweb;
    if (web == null)
        web = '';
    if (value == null)
        value = '';
    //var s = '<div>' + web + '</div><div>' + value + '</div>';
    var s = '<div>' + value + '</div>';
    if (value == 'REVENDEDOR' || value == 'PUNTO DE VENTA') {
        if (row.xrevendedor == null)
            row.xrevendedor = '';
        s += '<small>' + row.xrevendedor + '</small>';
    } else {
        s = '<div>' + web + '</div><div>' + value + '</div>';
    }
    return s;
}
function formatoNivel(value, row) {
    var tag = '', n = '', s;
    if (row.xnivel == 1)
        n = 'danger';
    if (row.xnivel == 2)
        n = 'warning';
    if (row.xnivel == 3)
        n = 'success';
    if (row.xnivel == 5)
        n = 'pink';
    if (row.xnivel == 6)
        n = 'info';
    if (row.xnivel == 7)
        n = 'black';
    if (n != '')
        tag = '<span class="pull-right badge badge-' + n + '">' + row.xnivel + '</span>';
    //tag = ' <span class="label label-table label-' + n + '">' + row.xnivel + '</span>';
    //s = value + tag;
    s = '<a target="_blank" class="text-primary" href="?module=clientes&id=' + row.xcliente_id + '">' + value + '</a>' + tag;
    //<span class="label label-table label-success">Enterprise</span>
    //return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
    return s;
}
