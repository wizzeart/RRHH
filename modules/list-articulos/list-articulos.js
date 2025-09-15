var archivado = 'N';
$(document).ready(function () {
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
        var cmd = 'module=articulos&method=checked&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-venta', function () {
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
        var cmd = 'module=articulos&method=checked-venta&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-venta-mr', function () {
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
        var cmd = 'module=articulos&method=checked-venta-mr&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-activo-web', function () {
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
        var cmd = 'module=articulos&method=checked-web&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-activo-web-mr', function () {
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
        var cmd = 'module=articulos&method=checked-web-mr&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-activo-web-an', function () {
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
        var cmd = 'module=articulos&method=checked-web-an&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-activo-rev', function () {
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
        var cmd = 'module=articulos&method=checked-rev&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-status-destacado', function () {
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
        var cmd = 'module=articulos&method=checked-destacado&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-status-destacado-mr', function () {
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
        var cmd = 'module=articulos&method=checked-destacado-mr&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-servicio', function () {
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
        var cmd = 'module=articulos&method=checked-servicio&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.toggle-preventa', function () {
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
        var cmd = 'module=articulos&method=checked-preventa&id=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });
    $('#table-panel').on('click', '.fa.fa-edit', function () {
        var cmd = 'index.php?module=articulos&id=' + $(this).data('id');
        //location.href = cmd;
        window.open(cmd);
    });
    $('#table-panel').on('click', '.fa.fa-copy', function () {
        if (confirm('Estás seguro de querer duplicar el artículo?')) {
            var cmd = 'module=articulos&method=copy&id=' + $(this).data('id');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        alert('refresh');
                    } else {

                    }
                }
            });
        }
    });
    $('#table-panel').on('click', '.fa.fa-trash', function () {
        if (confirm('Estás seguro de eliminar?')) {
            var cmd = 'module=articulos&method=del&id=' + $(this).data('id') + '&row=' + $(this).parent().parent().data('index');
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
    $('#table-panel').on('click', '.fa.fa-archive', function () {
        var cmd = 'module=articulos&method=archived&id=' + $(this).data('id')
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

    set_search();
    $('#btn-dl-pdf').click(function () {
        var status = 1, msg = '';
        var data = $('#table-panel').bootstrapTable('getData'), checked = [];
        //console.debug(data);
        $.each(data, function (i, v) {
            //console.debug(v);
            if (v.xchecked) {
                checked.push(v.xid);
            }
        });

        if (checked.length == 0) {
            status = 0;
            msg += '<div>Selecciona al menos un producto para el catálogo.</div>';
        }
        if ($('#ms-categorias').val() == null) {
            status = 0;
            msg += '<div>Selecciona al menos una categoría para el catálogo.</div>';
        }

        if (status == 1) {
            var cmd = 'api-app.php?module=articulos&method=dl-pdf-revendedor&arts=' + checked.join(',') + '&cats=' + $('#ms-categorias').val();
            //console.debug(cmd);
            location.href = cmd;
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Error Búsqueda',
                message: msg,
                container: 'floating',
                timer: 5000
            });
        }


    });
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=articulos';
    });
    $('#btn-img-an').click(function () {
        if (confirm("¿Desea generar las imágenes para AllNovu.com?")) {
            var cmd = 'module=articulos&method=copy-img-an';
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $.niftyNoty({
                            type: 'success',
                            title: 'Generar Imágenes AllNovu.com',
                            message: d.msg,
                            container: 'floating',
                            timer: 3000
                        });
                    }
                }
            });
        }
    });

    $('#btn-filtro-archivados').click(function () {
        if (!$(this).hasClass('active'))
            archivado = 'S';
        else
            archivado = 'N';
        $('button[name=refresh]').click();
    });
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
    if ($(s).length == 1) {
        $(s).append(e);
    }

    $('#ms-articulo').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    $('#ms-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    $('#ms-categorias').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    $('#btn-show-modal-search').click(function () {
        $('#modalSearch').modal('show');
    });

    $('#btn-do-search').click(function () {
        var status = 1, msg = '';
        $('#img-loading').removeClass('hidden');
        $('#btn-do-search').attr('disabled', true);

        if ($('#ms-categorias').val() == null) {
            status = 0;
            msg += '<div>Es obligatorio seleccionar al menos una categoría</div>';
        }

        if (status == 1) {
            var cmd = 'module=articulos&method=list&categorias=' + $('#ms-categorias').val()
                    + '&activo=' + $('#ms-activo').val() + '&activo-web=' + $('#ms-activo-web').val();
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-do-search').attr('disabled', false);
                    $('#table-panel').bootstrapTable('removeAll');
                    $.each(d.rows, function (i, v) {
                        $('#table-panel').bootstrapTable('insertRow', {index: i, row: v});
                    });
                    $.niftyNoty({
                        type: 'success',
                        title: 'Búsqueda realizada',
                        message: 'Búsqueda realizada con éxito.',
                        container: 'floating',
                        timer: 3000
                    });
                    $('#modalSearch').modal('hide');
                }
            });
        } else {
            $('#img-loading').addClass('hidden');
            $('#btn-do-search').attr('disabled', false);
            $.niftyNoty({
                type: 'danger',
                title: 'Error Búsqueda',
                message: msg,
                container: 'floating',
                timer: 5000
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

var tpl_btn = '\n\
    <button id="btn-show-modal-search" class="btn btn-default" type="button" title="Búsqueda">\n\
        <i class="glyphicon glyphicon-search"></i>\n\
    </button>\n\
    <button id="btn-img-an" class="btn btn-default" type="button" title="Generar imágenes AllNovu">\n\
        <i class="glyphicon glyphicon-picture" style="color: blue;"></i>\n\
    </button>\n\
    <button id="btn-dl-pdf" class="btn btn-default" type="button" title="Descarga Catálogo">\n\
        <i class="glyphicon glyphicon-book"></i>\n\
    </button>\n\
    <button id="btn-filtro-archivados" title="Mostrar archivados" data-toggle="button" class="btn btn-default btn-active-danger" type="button" aria-pressed="false">\n\
        <i class="glyphicon glyphicon-hdd"></i>\n\
    </button>\n\
    <button id="btn-add-new" class="btn btn-default" type="button" title="Añadir Nuevo">\n\
        <i class="glyphicon glyphicon-plus"></i>\n\
    </button>\n\\n\
';

// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

function formatoDestacado(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-status-destacado"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-status-destacado"></button>';
}
function formatoDestacadoMR(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-status-destacado-mr"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-status-destacado-mr"></button>';
}

function formatoServicio(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-servicio"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-servicio"></button>';
}

function formatoVenta(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-venta"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-venta"></button>';
}
function formatoVentaMR(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-venta-mr"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-venta-mr"></button>';
}

function formatoWeb(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-activo-web"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-activo-web"></button>';
}
function formatoWebMR(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-activo-web-mr"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-activo-web-mr"></button>';
}
function formatoWebAN(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-activo-web-an"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-activo-web-an"></button>';
}
function formatoWebSD(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-activo-web-sd"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-activo-web-sd"></button>';
}

function formatoRev(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-activo-rev"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-activo-rev"></button>';
}

function formatoPreventa(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.xid + '" class="btn btn-success btn-xs btn-icon icon-sm fa fa-check toggle-preventa"></button>';
    else
        return '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-remove toggle-preventa"></button>';
}

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var btn_edit = '<button data-id="' + row.xid + '" class="btn btn-info btn-xs btn-icon icon-sm fa fa-edit"></button>';
    var btn_copy = '<button data-id="' + row.xid + '" class="btn btn-warning btn-xs btn-icon icon-sm fa fa-copy"></button>';
    var btn_trash = '<button data-id="' + row.xid + '" class="btn btn-danger btn-xs btn-icon icon-sm fa fa-trash"></button>';
    var btn_archived = '<button data-value="' + row.xarchivado + '" data-id="' + row.xid + '" class="btn btn-default btn-xs btn-icon icon-sm fa fa-archive"></button>';
    if (rol != '1') {
        btn_trash = '';
        btn_archived = '';
    }
    var s = btn_edit + '\n' + btn_copy + '\n' + btn_archived + '\n' + btn_trash;
    if (value == 'toolbar')
        return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}

function formatoDescripcion(value, row) {
    var s;
    s = '<a target="_blank" class="text-primary" href="?module=articulos&id=' + row.xid + '">' + value + '</a>';
    return s;
}

function ajaxRequest(params) {
    var cmd = 'module=articulos&method=list&archivado=' + archivado;
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