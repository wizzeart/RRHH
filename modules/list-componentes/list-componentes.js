var componente = '';
var positivos = 0;
var archivado = 'N';
var activos = '';
var almacen = '';
$(document).ready(function () {
    $('#table-panel').on('click', '.fa.fa-archive', function () {
        var cmd = 'module=articulos&method=archived-componente&id=' + $(this).data('id')
                + '&row=' + $(this).parent().parent().data('index')
                + '&value=' + $(this).data('value');
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                } else {

                }
            }
        });
    });
    $('#table-panel').on('click', '.fa.fa-arrows-h', function () {
        componente = $(this).parent().parent().find('td:eq(1)').text();
        //alert(componente);
        $('#mt-componente-id').val($(this).data('id'));
        var cmd = 'module=articulos&method=list-almacenes&gcc=' + $(this).data('id');
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#mt-almacen-src').empty().append('<option value="">Seleccione almacén origen</option>');
                    $('#mt-almacen-dst').empty().append('<option value="">Seleccione almacén destino</option>');
                    $('#mt-componente').val(componente);
                    $('#mt-cantidad').val('0');
                    if ('gcc' in d)
                        $('#mt-coste').val(d.gcc);
                    $('#mt-concepto').val('');
                    $.each(d.items, function (i, v) {
                        $('#mt-almacen-src').append('<option value="' + v.xalmacen_id + '">' + v.xalmacen + '</option>');
                        $('#mt-almacen-dst').append('<option value="' + v.xalmacen_id + '">' + v.xalmacen + '</option>');
                    });
                    $('#modalTraspaso').modal('show');
                } else {

                }
            }
        });
    });
    $('#btn-do-traspaso').click(function () {
        var status = 1, msg = '', tmp = 0;
        $('#btn-do-traspaso').attr('disabled', true);
        $('#img-loading').removeClass('hidden');

        if ($('#mt-almacen-src').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio seleccionar el almacén origen.</div>';
        }
        if ($('#mt-almacen-dst').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio seleccionar el almacén destino.</div>';
        }
        if (status == 1 && $('#mt-almacen-src').val() == $('#mt-almacen-dst').val()) {
            status = 0;
            msg += '<div>El almacén de origen no puede ser el mismo almacén de destino.</div>';
        }
        if ($('#mt-cantidad').val() == '0' || $('#mt-cantidad').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio introducir la cantidad.</div>';
        } else {
            if (!$.isNumeric($('#mt-cantidad').val())) {
                status = 0;
                msg += '<div>La cantidad debe ser numérico.</div>';
            } else {
                if (parseInt($('#mt-cantidad').val()) < 0) {
                    status = 0;
                    msg += '<div>La cantidad debe ser positiva.</div>';
                }
            }
        }
        tmp = $('#mt-coste').val().replace(/,/gi, '.') * 1;
        if (tmp == 0 || tmp == '') {
            status = 0;
            msg += '<div>Es obligatorio introducir el coste.</div>';
        } else {
            if (!$.isNumeric(tmp)) {
                status = 0;
                msg += '<div>El coste debe ser numérico.</div>';
            }
        }
        if ($('#mt-concepto').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio rellenar el concepto.</div>';
        }

        if (status == 1) {
            var cmd = 'module=articulos&method=add-traspaso&almacen-src=' + $('#mt-almacen-src').val()
                    + '&almacen-dst=' + $('#mt-almacen-dst').val()
                    + '&componente=' + $('#mt-componente-id').val() + '&cantidad=' + $('#mt-cantidad').val()
                    + '&coste=' + $('#mt-coste').val().replace(/,/gi, '.')
                    + '&concepto=' + $('#mt-concepto').val();
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $.niftyNoty({
                            type: 'success',
                            title: 'Traspaso almacén',
                            message: d.msg,
                            container: 'floating',
                            timer: 5000
                        });
                        $('#modalTraspaso').modal('hide');
                        $('button[name=refresh]').click();
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Traspaso almacén',
                            message: d.msg,
                            container: 'floating',
                            timer: 5000
                        });
                    }
                    $('#img-loading').addClass('hidden');
                    $('#btn-do-traspaso').attr('disabled', false);
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Añadir/Restar Stock',
                message: msg,
                container: 'floating',
                timer: 5000
            });
            $('#img-loading').addClass('hidden');
            $('#btn-do-add-stock').attr('disabled', false);
        }
    });
    $('#btn-do-add-stock').click(function () {
        var status = 1, msg = '', tmp = 0;
        $('#btn-do-add-stock').attr('disabled', true);
        $('#img-loading').removeClass('hidden');

        if ($('#mas-almacen').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio seleccionar el almacén.</div>';
        }
        if ($('#mas-cantidad').val() == '0' || $('#mas-cantidad').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio introducir la cantidad.</div>';
        } else {
            if (!$.isNumeric($('#mas-cantidad').val())) {
                status = 0;
                msg += '<div>La cantidad debe ser numérico.</div>';
            }
        }
        tmp = $('#mas-coste').val().replace(/,/gi, '.') * 1;
        if (tmp == 0 || tmp == '') {
            status = 0;
            msg += '<div>Es obligatorio introducir el coste.</div>';
        } else {
            if (!$.isNumeric(tmp)) {
                status = 0;
                msg += '<div>El coste debe ser numérico.</div>';
            }
        }
        if ($('#mas-concepto').val() == '') {
            status = 0;
            msg += '<div>Es obligatorio rellenar el concepto.</div>';
        }

        if (status == 1) {
            var cmd = 'module=articulos&method=add-stock&almacen=' + $('#mas-almacen').val()
                    + '&componente=' + $('#mas-componente-id').val() + '&cantidad=' + $('#mas-cantidad').val()
                    + '&coste=' + $('#mas-coste').val().replace(/,/gi, '.')
                    + '&concepto=' + $('#mas-concepto').val();
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $.niftyNoty({
                            type: 'success',
                            title: 'Añadir/Restar Stock',
                            message: d.msg,
                            container: 'floating',
                            timer: 5000
                        });
                        $('#modalAddStock').modal('hide');
                        $('button[name=refresh]').click();
                    } else {
                        $.niftyNoty({
                            type: 'danger',
                            title: 'Añadir/Restar Stock',
                            message: d.msg,
                            container: 'floating',
                            timer: 5000
                        });
                    }
                    $('#img-loading').addClass('hidden');
                    $('#btn-do-add-stock').attr('disabled', false);
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Añadir/Restar Stock',
                message: msg,
                container: 'floating',
                timer: 5000
            });
            $('#img-loading').addClass('hidden');
            $('#btn-do-add-stock').attr('disabled', false);
        }
    });
    $('#table-panel').on('click', '.fa.fa-plus', function () {
        componente = $(this).parent().parent().find('td:eq(1)').text();
        $('#mas-componente-id').val($(this).data('id'));
        var cmd = 'module=articulos&method=list-almacenes&gcc=' + $(this).data('id');
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#mas-almacen').empty().append('<option value="">Seleccione un almacén</option>');
                    $('#mas-componente').val(componente);
                    $('#mas-cantidad').val('0');
                    if ('gcc' in d)
                        $('#mas-coste').val(d.gcc);
                    $('#mas-concepto').val('');
                    $.each(d.items, function (i, v) {
                        $('#mas-almacen').append('<option value="' + v.xalmacen_id + '">' + v.xalmacen + '</option>');
                    });
                    $('#modalAddStock').modal('show');
                } else {

                }
            }
        });
    });
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=componentes';
    });
    $('#table-panel').on('click', '.toggle-status', function () {
        var value = '';
        if ($(this).hasClass('fa-check') == true) {
            $(this).removeClass('btn-success');
            $(this).addClass('btn-danger');
            $(this).removeClass('fa-check');
            $(this).addClass('fa-remove');
            value = 'N';
        } else {
            $(this).removeClass('btn-danger');
            $(this).addClass('btn-success');
            $(this).removeClass('fa-remove');
            $(this).addClass('fa-check');
            value = 'S';
        }
        var cmd = 'module=articulos&method=checked-componente&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.fa.fa-edit', function () {
        var cmd = 'index.php?module=componentes&id=' + $(this).data('id');
        //location.href = cmd;
        window.open(cmd);
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=articulos&method=del-componente&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                    } else {

                    }
                }
            });
        }
    });

    set_search();

    $('.select-almacen').click(function () {
        almacen = $(this).data('alm');

        $('#btn-filtro-text').html($(this).text());
        $('button[name=refresh]').click();
        //alert(almacen);
    });

    $('#btn-filtro-positivos').click(function () {
        if (!$(this).hasClass('active'))
            positivos = 1;
        else
            positivos = 0;
        $('button[name=refresh]').click();
    });

    $('#btn-filtro-archivados').click(function () {
        if (!$(this).hasClass('active'))
            archivado = 'S';
        else
            archivado = 'N';
        $('button[name=refresh]').click();
    });


    $('#btn-filtro-activos').click(function () {
        if (!$(this).hasClass('active'))
            activos = 'S';
        else
            activos = '';
        $('button[name=refresh]').click();
    });

    $('#btn-print-pdf').click(function () {
        var orderby = '', cmd = '';
        var e = $('th[data-field=xcomponente] div.th-inner');
        if ($(e).hasClass('desc')) {
            orderby = 'desc';
        }
        if ($(e).hasClass('asc')) {
            orderby = 'asc';
        }
        cmd = 'api-app.php?module=articulos&method=print-a4&positivos=' + positivos + '&activo=' + activos
                + '&almacen=' + almacen
                + '&orderby=' + orderby;
        //console.debug(cmd);
        location.href = cmd;
    }
    );
    $('#btn-print-ticket').click(function () {
        location.href = 'api-app.php?module=articulos&method=print-ticket&positivos=' + positivos
                + '&almacen=' + almacen + '&activo=' + activos;
    });
});

// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xcomponente_id + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.xcomponente_id + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var s = '';
    var btn_edit = '<button title="Editar Componente" data-id="' + value + '" class="btn btn-info btn-xs btn-icon icon-sm fa fa-edit"></button>';
    var btn_del = '<button title="Eliminar Componente" data-id="' + value + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-trash"></button>';
    var btn_plus = '<button title="Añadir entrada en Almacén" data-id="' + value + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-plus"></button>';
    var btn_traspaso = '<button title="Añadir traspaso entre Almacén" data-id="' + value + '" class="btn btn-warning btn-xs btn-icon icon-sm fa fa-arrows-h"></button>';
    var btn_archived = '<button data-value="' + row.xarchivado + '" data-id="' + value + '" class="btn btn-default btn-xs btn-icon icon-sm fa fa-archive"></button>';
    if (rol != '1') {
        btn_del = '';
        btn_archived = '';
    }
    return btn_edit + '\n' + btn_plus + '\n' + btn_traspaso + '\n' + btn_archived + '\n' + btn_del;
}

function formatoDescripcion(value, row) {
    var s;
    s = '<a target="_blank" class="text-primary" href="?module=componentes&id=' + row.xcomponente_id + '">' + value + '</a>';
    return s;
}

function idFormatter(data, value) {
    return 'Total';
}

function stockFormatterFooter(data) {
    var s = 0;

    $.each(data, function (i, v) {
        s += v.xstock * 1;
    });

    return s;
}

function stockValoradoFormatterFooter(data) {
    var s = 0;

    $.each(data, function (i, v) {
        s += (v.xstock * 1) * (v.xcoste * 1);
    });

    s = $.number(s, 2, ',', '.');

    return s;
}

function customSort(sortName, sortOrder, data) {
    var order = sortOrder === 'desc' ? -1 : 1;
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
                    return order * -1;
                }
                if (aa > bb) {
                    return order;
                }
                return 0;
            });
            break;
        case 'xdoc_id':
        case 'xcantidad':
        case 'xserie_id':
        case 'ximporte':
            data.sort(function (a, b) {
                var aa = +((a[sortName] + '').replace(/[^\d]/g, ''));
                var bb = +((b[sortName] + '').replace(/[^\d]/g, ''));
                if (aa < bb) {
                    return order * -1;
                }
                if (aa > bb) {
                    return order;
                }
                return 0;
            });
            break;
        default:
            //console.debug(sortName);
            data.sort(function (a, b) {
                var aa = (a[sortName] + '');
                var bb = (b[sortName] + '');
                if (aa < bb) {
                    return order * -1;
                }
                if (aa > bb) {
                    return order;
                }
                return 0;
            });
            break;
    }
}

function set_search() {
    var e = '';
    var s = '.fixed-table-toolbar .columns.columns-right.btn-group.pull-right';
    if ($(s).length == 1) {
        e = $(tpl_btn_print);
        $(s).append(e);
        e = $(tbl_btnToggle);
        $(s).append(e);
        e = $(tbl_btnToggleActivos);
        $(s).append(e);
        e = $(tbl_btnToggleArchivados);
        $(s).append(e);


        var alm = '';
        $.each(almacenes, function (i, v) {
            alm += '<li><a href="javascript:void(0)" class="select-almacen" data-alm="' + v.xalmacen_id + '">' + v.xalmacen + '</a></li>';
        });

        e = $(tpl_btn_filtro.replace(/##almacenes##/gi, alm));
        $(s).append(e);
    }

    //$(s).find('button[name=refresh]').remove();

    //$('#ms-articulo').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    //$('#ms-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    $('#btn-show-modal-search').click(function () {
        $('#modalSearch').modal('show');
    });

    $('#btn-do-search').click(function () {
        var status = 1, msg = '';
        var fi = $('#ms-fecha-desde input').val(), ff = $('#ms-fecha-hasta input').val();
        var tmp = [], yi, yf, fi_format = '', ff_format = '';

        tmp = fi.split('/');
        yi = tmp[2];
        fi = new Date(tmp[1] + '/' + tmp[0] + '/' + tmp[2]);
        fi_format = tmp[2] + '-' + tmp[1] + '-' + tmp[0] + ' 00:00:00';
        tmp = ff.split('/');
        yf = tmp[2];
        ff = new Date(tmp[1] + '/' + tmp[0] + '/' + tmp[2]);
        ff_format = tmp[2] + '-' + tmp[1] + '-' + tmp[0] + ' 23:59:59';


        //a = fi;
        //b = ff;

        if (fi.getTime() > ff.getTime()) {
            status = 0;
            msg += '<div>La <strong>Fecha Desde</strong> no puede ser superior a la <strong>Fecha Hasta</strong></div>';
        } else {
            if (yf - yi > 1) {
                status = 0;
                msg += '<div>La diferencias de fechas no puede ser mayor de 1 año</div>';
            }
        }

        if (status == 1) {
            $('#img-loading').removeClass('hidden');
            $('#btn-do-search').attr('disabled', true);
            $('#icon-refresh').addClass('hidden');
            $('#icon-loading').removeClass('hidden');

            var cmd = 'module=almacenes&method=list-movimientos'
                    //+ "&pedido=" + $('#ms-pedido').val()
                    + "&fi=" + fi_format + "&ff=" + ff_format;

            if ($('#ms-tipo').val() != '') {
                cmd += '&type=' + $('#ms-tipo').val();
            }
            if ($('#ms-componente').val() != '') {
                cmd += '&componente=' + $('#ms-componente').val();
            }

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
                    $('#modalSearch').modal('hide');
                    $('#img-loading').addClass('hidden');
                    $('#btn-do-search').attr('disabled', false);
                    $('#icon-refresh').removeClass('hidden');
                    $('#icon-loading').addClass('hidden');
                }
            });
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Búsqueda',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#ms-fecha-desde .input-group.date,#ms-fecha-hasta .input-group.date').datepicker({
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

var tbl_btnToggle = '\n\
    <button id="btn-filtro-positivos" title="Mostrar positivos" data-toggle="button" class="btn btn-default btn-active-warning" type="button" aria-pressed="false">\n\
        <i class="glyphicon glyphicon-signal"></i>\n\
    </button>\n\
';

var tbl_btnToggleActivos = '\n\
    <button id="btn-filtro-activos" title="Mostrar activos" data-toggle="button" class="btn btn-default btn-active-warning" type="button" aria-pressed="false">\n\
        <i class="glyphicon glyphicon-screenshot"></i>\n\
    </button>\n\
';

var tbl_btnToggleArchivados = '\n\
    <button id="btn-filtro-archivados" title="Mostrar archivados" data-toggle="button" class="btn btn-default btn-active-danger" type="button" aria-pressed="false">\n\
        <i class="glyphicon glyphicon-hdd"></i>\n\
    </button>\n\
';

var tpl_btn = '\n\
    <button id="btn-refresh" class="btn btn-default" type="button" title="Refrescar">\n\
        <i id="icon-refresh" class="glyphicon glyphicon-refresh"></i>\n\
        <img id="icon-loading" style="width:15px;" class="hidden" src="img/spinners/282.gif"/>\n\
    </button>\n\
    <button id="btn-show-modal-search" class="btn btn-default" type="button" title="Búsqueda">\n\
        <i class="glyphicon glyphicon-search"></i>\n\
    </button>\n\
';

var tpl_btn_print = '\n\
    <button id="btn-print-pdf" class="btn btn-default" type="button" title="Imprimir Stock en A4">\n\
        <i class="glyphicon glyphicon-print"></i>\n\
    </button>\n\
    <button id="btn-print-ticket" class="btn btn-default" type="button" title="Imprimir Stock en Ticket">\n\
        <i class="glyphicon glyphicon-barcode"></i>\n\
    </button>\n\
';

var tpl_btn_filtro = '\n\
    <div class="btn-group">\n\
        <button class="btn btn-default btn-active-pink dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button" aria-expanded="false">\n\
                <span id="btn-filtro-text">Todos los almacenes</span> <i class="dropdown-caret fa fa-caret-down"></i>\n\
        </button>\n\
        <ul class="dropdown-menu">\n\
                <li><a href="javascript:void(0)" class="select-almacen" data-alm="0">Todos los almacenes</a></li>\n\
                ##almacenes##\n\
        </ul>\n\
    </div>\n\
';

var icon_refresh = '<i class="glyphicon glyphicon-refresh"></i>';
var icon_loading = '<img style="width:15px;" id="img-loading" class="hidden" src="img/spinners/282.gif"/>';

function load_data_search() {
    var cmd = 'module=informes&method=load-search-resume-ventas';
    //console.debug(cmd);
    $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
        success: function (d) {
            $.each(d.secciones, function (i, v) {
                $('#f-seccion').append('<option value="' + v.CODSEC + '">' + v.CODSEC + '-' + v.DESSEC + '</option>');
            });
            $.each(d.familias, function (i, v) {
                $('#f-familia').append('<option value="' + v.CODFAM + '">' + v.CODFAM + '-' + v.DESFAM + '</option>');
            });
            $.each(d.proveedores, function (i, v) {
                $('#f-proveedor').append('<option value="' + v.CODPRO + '">' + v.CODPRO + '-' + v.NOFPRO + '</option>');
            });
        }
    });
}

function ajaxRequest(params) {
    if (almacen == '0')
        almacen = '';
    var cmd = 'module=articulos&method=list-componentes&positivos=' + positivos + '&almacen=' + almacen
            + '&activo=' + activos + '&archivado=' + archivado;

    $.ajax({
        type: "GET",
        url: "api-app.php",
        data: cmd,
        dataType: "json",
        success: function (data) {
            params.success({
                "rows": data.rows,
                "total": data.rows.length
            }, null, {});
        },
        error: function (er) {
            params.error(er);
        }
    });
}