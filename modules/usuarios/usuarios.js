var e, tmp, cmd_params;

$(document).ready(function () {
    $('#f-almacen').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '90%'});
    $('#f-punto-venta').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

    // Inicializar multiselects para jefe de área
    $('#f-departamentos').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '100%'});
    $('#f-ubicaciones').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '100%'});

    // Cargar datos de departamentos y ubicaciones
    cargarDepartamentos();
    cargarUbicaciones();

    // Mostrar/ocultar campos de jefe de área según el rol seleccionado
    $('#f-rol').on('change', function() {
        var rolId = $(this).val();
        if (rolId == '4') { // JEFE_DE_AREA
            $('#row-jefe-area').show();
        } else {
            $('#row-jefe-area').hide();
            $('#f-departamentos').val('').trigger('chosen:updated');
            $('#f-ubicaciones').val('').trigger('chosen:updated');
        }
    });

    // Verificar rol inicial
    var rolInicial = $('#f-rol').val();
    if (rolInicial == '4') {
        $('#row-jefe-area').show();
        cargarAsignacionesJefe();
    }

    $('#btn-new').click(function () {
        location.href = '?module=usuarios';
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-usuarios';
    });
    $('#btn-test').click(function () {
        location.href = '?module=usuarios';
    });
    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';

        if ($('#f-usuario').val() == '') {
            status = 0;
            msg += '<div>El campo Nombre del Usuario es obligatorio.</div>';
        }
        if ($('#f-pass').val() == '') {
            status = 0;
            msg += '<div>El campo Contraseña es obligatorio.</div>';
        }

        if ($('#f-rol').val() == '') {
            status = 0;
            msg += '<div>El campo Rol del Usuario es obligatorio.</div>';
        } else {
            if ($('#f-rol').val() == '10' && $('#f-punto-venta').val() == null) {
                status = 0;
                msg += '<div>Rol Punto de Venta Facturación debe tener al menos un punto de venta asociado.</div>';
            }
            
            // Validación para Jefe de Área (rol_id = 4)
            if ($('#f-rol').val() == '4') {
                var departamentos = $('#f-departamentos').val();
                var ubicaciones = $('#f-ubicaciones').val();
                
                if (!departamentos || departamentos.length === 0) {
                    status = 0;
                    msg += '<div>El Jefe de Área debe tener al menos un departamento asignado.</div>';
                }
                
                if (!ubicaciones || ubicaciones.length === 0) {
                    status = 0;
                    msg += '<div>El Jefe de Área debe tener al menos una ubicación asignada.</div>';
                }
            }
        }

        if (status == 1) {
            var param_almacen = '';
            var param_punto_venta = '';
            $('#img-loading').removeClass('hidden');

            setTimeout(function() {
                $('#img-loading').addClass('hidden');
            }, 2000); // 2000 milisegundos = 2 segundos
            
            $('#btn-save').attr('disabled', true);

            var cmd = 'module=usuarios&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            cmd += '&action=' + action;
            cmd += '&xusuario_id=' + $('#f-usuario-id').val();
            
            // Agregar departamentos y ubicaciones para jefe de área
            var departamentos = $('#f-departamentos').val();
            var ubicaciones = $('#f-ubicaciones').val();
            
            if (departamentos && departamentos.length > 0) {
                cmd += '&departamentos=' + encodeURIComponent(JSON.stringify(departamentos));
            }
            
            if (ubicaciones && ubicaciones.length > 0) {
                cmd += '&ubicaciones=' + encodeURIComponent(JSON.stringify(ubicaciones));
            }
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        if (d.action == 'insert') {
                            action = 'update';
                            $('#f-usuario-id').val(d.id);
                        }
                        $('#f-pass').val('');
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
            $.niftyNoty({
                type: 'danger',
                title: 'Guardar datos',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
});


function formatoPedido(value, row) {
    //var s = '<div><input data-ped="' + value + '" type="checkbox" class="chk-ped"/>&nbsp;' + value + '</div>';
    if (row.xrevendedor == null)
        row.xrevendedor = 'sin agencia';
    var s = '<div>' + value + '</div>';
    s += '<div><small>' + row.xhash + '</small></div>';
    s += '<div>' + row.xrevendedor + '</div>';
    return s;
}
function formatoToolbar(value, row) {
    var btn_edit = '<button title="Editar" data-id="' + row.xpedido_id + '" data-ref="' + row.xhash + '" class="btn btn-info btn-xs btn-icon icon-sm fa fa-edit"></button>';
    var btn_print = '<button title="Imprimir" data-id="' + row.xpedido_id + '" class="btn btn-warning btn-xs btn-icon icon-sm fa fa-print"></button>';
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

    return  s;
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
    var s = '';
    s = '<span class="label label-' + row.xcolor + '">' + value + '</span>';
    //console.debug(s);
    return s;
}
function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
function formatoTransaccion(value, row) {
    if (value == null)
        value = '';
    var s = '<div>' + value + '</div>';
    if (value = 'REVENDEDOR') {
        if (row.xrevendedor == null)
            row.xrevendedor = '';
        s += '<small>' + row.xrevendedor + '</small>';
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

// Funciones para manejo de jefe de área
function cargarDepartamentos() {
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: { module: 'departamentos', method: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response) {
                var $select = $('#f-departamentos');
                $select.empty();
                $select.append('<option value="">Seleccione departamentos</option>');
                
                $.each(response, function(i, depto) {
                    $select.append('<option value="' + depto.id + '">' + depto.nombre + '</option>');
                });
                
                $select.trigger('chosen:updated');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando departamentos:', error);
        }
    });
}

function cargarUbicaciones() {
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: { module: 'ubicaciones', method: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response) {
                var $select = $('#f-ubicaciones');
                $select.empty();
                $select.append('<option value="">Seleccione ubicaciones</option>');
                
                $.each(response, function(i, ubicacion) {
                    $select.append('<option value="' + ubicacion.id + '">' + ubicacion.nombre + '</option>');
                });
                
                $select.trigger('chosen:updated');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando ubicaciones:', error);
        }
    });
}

function cargarAsignacionesJefe() {
    var usuarioId = $('#f-usuario-id').val();
    if (!usuarioId) return;
    
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: { 
            module: 'usuarios', 
            method: 'get-asignaciones-jefe',
            usuario_id: usuarioId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                // Seleccionar departamentos asignados
                if (response.departamentos && response.departamentos.length > 0) {
                    $('#f-departamentos').val(response.departamentos).trigger('chosen:updated');
                }
                
                // Seleccionar ubicaciones asignadas
                if (response.ubicaciones && response.ubicaciones.length > 0) {
                    $('#f-ubicaciones').val(response.ubicaciones).trigger('chosen:updated');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando asignaciones de jefe:', error);
        }
    });
}