var e, tmp;
var libgrid = '/plugins/dhtmlxGrid/codebase';

$(document).ready(function () {
    //$('#f-categoria').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-provincia').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-pais').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});

    $('#f-condiciones-email').summernote({
        height: 500
    });
    $('#f-condiciones-email-mr').summernote({
        height: 500
    });
    $('#f-condiciones-email-an').summernote({
        height: 500
    });
    $('#f-condiciones-email-rev').summernote({
        height: 500
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=home';
    });
    $('#btn-save').click(function () {
        var status = 1, lin = [];
        var msg = '';

        if (status == 1) {
            $('#img-loading').removeClass('hidden');
            $('#btn-save').attr('disabled', true);

            var cmd = 'module=config&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            cmd += '&action=' + action;
            //if (action == 'update')
            //    cmd += '&xconfig_id=' + $('#f-config-id').val();
            cmd += '&xcondiciones=' + encodeURIComponent($('#f-condiciones-email').code());
            cmd += '&xcondiciones_mr=' + encodeURIComponent($('#f-condiciones-email-mr').code());
            cmd += '&xcondiciones_rev=' + encodeURIComponent($('#f-condiciones-email-rev').code());
            cmd += '&xcondiciones_an=' + encodeURIComponent($('#f-condiciones-email-an').code());
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        if (d.action == 'insert') {
                            action = 'update';
                            $('#f-pagina-id').val(d.id);
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