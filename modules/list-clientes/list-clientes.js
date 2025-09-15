$(document).ready(function () {
    $('#btn-ver-altas').click(function () {
        var cmd = 'module=clientes&method=list-altas-clientes';
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#tbl-altas-clientes tbody').empty();
                    $.each(d.items, function (i, v) {
                        var e = $(tpl_lin_alta);
                        $(e).find('td:eq(0)').html(v.xrango);
                        $(e).find('td:eq(1)').html(v.xcount);
                        $('#tbl-altas-clientes tbody').append(e);
                    });
                    $('#modalAltas').modal('show');
                } else {
                    alert(d.msg);
                }
            }
        });

    });

    $('#btn-dl-xls').click(function () {
        var cmd = 'api-app.php?module=clientes&method=dl-xls-clientes-mailing';
        location.href = cmd;
    });
    $('#btn-empty-ms-texto').click(function () {
        $('#ms-texto').val('').focus();
    });
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=clientes';
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
        var cmd = 'module=clientes&method=checked&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
        location.href = 'index.php?module=clientes&id=' + $(this).data('id');
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=clientes&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('tr[data-index="' + d.row + '"]').fadeOut('slow');
                    } else {
                        alert(d.msg);
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
    $('button[name=refresh]').remove();

    $('#ms-articulo').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    $('#ms-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    $('#btn-show-modal-search').click(function () {
        show_search();
    });

    $('#btn-refresh-search').click(function () {
        search_data();
    });

    $('#btn-do-search').click(function () {
        search_data();
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

    $('#ms-texto').keypress(function (e) {
        if (e.keyCode == 13) {
            search_data();
        }
    });

    setTimeout(function () {
        show_search();
    }, 600);
}

function show_search() {
    $('#modalSearch').modal('show');
    setTimeout(function () {
        $('#ms-texto').focus();
    }, 600);
}

function search_data() {
    $('#table-panel').bootstrapTable('removeAll');

    var cmd = 'module=clientes&method=list&q=' + $('#ms-texto').val();
    //console.debug(cmd);
    $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
        success: function (d) {

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
}


var tpl_btn = '\
    <button id="btn-show-modal-search" class="btn btn-default" type="button" title="Búsqueda">\n\
        <i class="glyphicon glyphicon-search icon-refresh"></i>\n\
    </button>\n\
    <button id="btn-refresh-search" class="btn btn-default" type="button" title="Refrescar">\n\
        <i class="glyphicon glyphicon-refresh icon-refresh"></i>\n\
    </button>';

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xcliente_id + '" class="btn btn-xs btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.xcliente_id + '" class="btn btn-xs btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

function formatoMovil(value, row) {
    return '(+' + row.xprefijo + ') ' + value;
}

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var btn_edit = '<button data-id="' + row.xcliente_id + '" class="btn btn-xs btn-info btn-icon icon-sm fa fa-edit"></button>';
    var btn_del = '<button data-id="' + row.xcliente_id + '" class="btn btn-xs btn-danger btn-icon icon-sm fa fa-trash"></button>';

    if (rol != '1') {
        btn_del = '';
    }

    if (value == 'toolbar')
        return btn_edit + '\n' + btn_del;
}

function formatoNivel(value, row) {
    var tag = '', n = '', s, l = '';
    l = '<a target="_blank" class="text-primary" href="?module=clientes&id=' + row.xcliente_id + '">' + value + '</a>';
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
    s = l + tag;
    //<span class="label label-table label-success">Enterprise</span>
    //return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
    return s;
}


var tpl_lin_alta = '\n\
    <tr>\n\
        <td>0</td>\n\
        <td class="text-right">0</td>\n\
    </tr>\n\
';