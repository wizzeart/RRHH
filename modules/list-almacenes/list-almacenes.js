$(document).ready(function () {
    $('#ma-municipios').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '100%'});

    $('#btn-do-save-almacen').click(function () {
        var status = 1, msg = '';

        //$(this).parent().parent().data('index')

        if (status == 1) {
            var cmd = 'module=almacenes&method=save&xalmacen_id=' + $('#ma-almacen-id').val()
                    + '&xalmacen=' + $('#ma-almacen').val() + '&xalmacen_web=' + $('#ma-almacen-web').val()
                    + '&municipios=' + (($('#ma-municipios').val() != null) ? $('#ma-municipios').val().join(',') : '')
                    + '&row=' + $('#ma-row').val();
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        //$('tr[data-index="' + d.row + '"]').fadeOut('slow');
                        $('button[name=refresh]').click();
                        $('#modalAlmacen').modal('hide');
                    } else {

                    }
                }
            });
        } else {

        }
    });
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=almacenes';
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
        var cmd = 'module=almacenes&method=checked&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '.toggle-status-web', function () {
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
        var cmd = 'module=almacenes&method=checked-web&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-status-web-an', function () {
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
        var cmd = 'module=almacenes&method=checked-web-an&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '.fa.fa-eye', function () {
        location.href = 'index.php?module=list-stock&id=' + $(this).data('id');
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        //location.href = 'index.php?module=categorias&id=' + $(this).data('id');
        //alert('edit');
        var e = $(this).parent().parent();
        $('#ma-almacen-id').val($(this).data('id'));
        $('#ma-almacen').val($(e).find('td:eq(1)').text());
        $('#ma-almacen-web').val($(e).find('td:eq(2)').text());
        $('#ma-municipios').val(null);
        $('#ma-municipios').trigger('chosen:updated');
        $('#modalAlmacen h4.modal-title').html('Editar Almacén');

        var cmd = 'module=almacenes&method=get-municipios&id=' + $(this).data('id');
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                var e = [];
                $.each(d, function (i, v) {
                    e.push(v.xmunicipio);
                });

                console.debug(e);
                $('#ma-municipios').val(e);
                $('#ma-municipios').trigger('chosen:updated');
            }
        });

        $('#modalAlmacen').modal('show');

    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=almacenes&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
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
});

function set_search() {
    var e = $(tpl_btn);
    var s = '.fixed-table-toolbar .columns.columns-right.btn-group';
    if ($(s).length == 1)
        $(s).append(e);

    //$('#ms-articulo').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    //$('#ms-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    $('#btn-show-modal-search').click(function () {
        //$('#modalSearch').modal('show');
        alert('No disponible...');
    });
    $('#btn-show-modal-search-entregados').click(function () {
        $('#modalSearchEntregados').modal('show');
    });
    $('#btn-show-modal-search-finalizados').click(function () {
        $('#modalSearchFinalizados').modal('show');
    });
    $('#btn-add-almacen').click(function () {
        $('#modalAlmacen h4.modal-title').html('Añadir Almacén');
        $('#modalAlmacen').modal('show');
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
        var cmd = 'module=pedidos&method=list&cli=' + $('#ms-cliente').val()
                + '&estado=' + $('#ms-estado').val() + '&pedido=' + $('#ms-pedido').val()
                + '&fi=' + $('#ms-fecha-desde input').val() + '&ff=' + $('#ms-fecha-hasta input').val()
                + '&resultados=' + $('#ms-resultados').val();
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
    <button id="btn-add-almacen" class="btn btn-default" type="button" title="Añade/Edita un almacén">\n\
        <i class="glyphicon glyphicon-plus"></i>\n\
    </button>\
';




// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xalmacen_id + '" class="btn btn-xs btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.xalmacen_id + '" class="btn btn-xs btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
}
function formatoActivoWeb(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xalmacen_id + '" class="btn btn-xs btn-success btn-icon icon-sm fa fa-check toggle-status-web"></button>';
    else
        return '<button data-id="' + row.xalmacen_id + '" class="btn btn-xs btn-danger btn-icon icon-sm fa fa-remove toggle-status-web"></button>';
}
function formatoActivoWebAN(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xalmacen_id + '" class="btn btn-xs btn-success btn-icon icon-sm fa fa-check toggle-status-web-an"></button>';
    else
        return '<button data-id="' + row.xalmacen_id + '" class="btn btn-xs btn-danger btn-icon icon-sm fa fa-remove toggle-status-web-an"></button>';
}

function formatoMunicipios(value, row) {
    var s = '';
    if (row.items.length > 0) {
        $.each(row.items, function (i, v) {
            s += '<div><small>' + v.xmunicipio.replace(/_/gi, ' ') + '</small></div>';
        });
    }

    return s;
}

function formatoDestacado(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-xs btn-success btn-icon icon-sm fa fa-check toggle-status-destacado"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-xs btn-danger btn-icon icon-sm fa fa-remove toggle-status-destacado"></button>';
}


// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var s = '';
    var btn_stock = '<button title="Listado de Stock" data-id="' + row.xalmacen_id + '" data-municipios="" class="btn btn-xs btn-success btn-icon icon-sm fa fa-eye"></button>';
    var btn_edit = '<button title="Editar" data-id="' + row.xalmacen_id + '" class="btn btn-info btn-xs btn-icon icon-sm fa fa-edit"></button>';
    s = btn_stock + '\n' + btn_edit;
    return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}

function formatoDescripcion(value, row) {
    var s;
    s = '<a target="_blank" class="text-primary" href="?module=categorias&id=' + row.xid + '">' + value + '</a>';
    return s;
}