$(document).ready(function () {
    //$('#f-usuario-chosen').hide();
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-notificaciones';
    });
    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';

        if (action == 'insert') {
            if ($('#f-title').val() == '') {
                status = 0;
                msg += 'El campo Título es obligatorio.\n';
            }
        }


        if (status == 1) {
            var cmd = 'module=notificaciones&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            /*var cmd = {module: "noticias", method: "save"
             , xnoticia_id: $('input[name=xnoticia_id]').val()
             , xnoticia: $('input[name=xnoticia]').val()
             , xfecha: $('#f-date input').val()
             , xcontent: $('#f-content').code()
             };*/
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        //alert(d.msg);
                        location.href = 'index.php?module=list-notificaciones';
                    } else {
                        alert(d.msg);
                    }
                }
            });
        } else {
            alert(msg);
        }
    });
    $('#f-type').change(function () {
        if ($(this).val() == '1') {
            //$('#f-usuario').attr('disabled', false).focus();
            $('#f_usuario_chosen_chosen').show();
        } else {
            //$('#f-usuario').attr('disabled', true);
            $('#f_usuario_chosen_chosen').hide();
            $('#f-usuario').val('');
        }
    });


    $('#f-usuario-chosen').empty();
    $('#f-usuario-chosen').chosen({width: '100%'}).change(function () {
        $('#f-usuario').val($('#f-usuario-chosen').val());
    });
    $('#f_usuario_chosen_chosen').hide();
    load_users();
    //chosen_ajax();
});

function chosen_ajax() {
    console.debug($('#f_usuario_chosen_chosen .chosen-search input').length);
    $('#f_usuario_chosen_chosen .chosen-search input').autocomplete({
        source: function (request, response) {
            console.debug('autocmplete');
            var cmd = 'module=notificaciones&method=load_users&city=' + cnc_city + '&term=' + request.term;
            $.ajax({
                url: 'api-app.php',
                data: cmd,
                dataType: "json",
                beforeSend: function () {
                    //$('ul.chzn-results').empty();
                    $('#f-usuario-chosen').empty();
                    //console.debug('antes de enviar');
                },
                afterSend: function () {
                    console.debug('after');
                },
                success: function (data) {
                    console.debug(data);
                    /*response($.map(data, function (item) {
                     $('ul.chzn-results').append('<li class="active-result">' + item.name + '</li>');
                     }));*/
                    /*$.each(data.items, function (i, v) {
                        $('#f-usuario-chosen').append('<option value="' + v.id_usuario + '">' + v.nombre + ' ' + v.apellido1 + ' ' + v.apellido2 + '</option>');
                    });
                    $('#f-usuario-chosen').trigger('chosen:updated');*/
                }
            });
        }
    });
}
function load_users() {
    var cmd = 'module=notificaciones&method=load_users&city=' + cnc_city;
    $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
        success: function (d) {
            if (d.status == 1) {
                //alert(d.msg);
                //location.href = 'index.php?module=list-notificaciones';
                $.each(d.items, function (i, v) {
                    $('#f-usuario-chosen').append('<option value="' + v.id_usuario + '">' + v.nombre + ' ' + v.apellido1 + ' ' + v.apellido2 + '</option>');
                });
                $('#f-usuario-chosen').trigger('chosen:updated');
            } else {
                alert(d.msg);
            }
        }
    });
}