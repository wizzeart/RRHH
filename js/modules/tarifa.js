$(document).ready(function () {
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-tarifas';
    });
    $('#btn-save').click(function () {
        var status = 1;
        var msg = '';

        if (action == 'insert') {
            if ($('#f-title').val() == '') {
                status = 0;
                msg += 'El campo Nombre es obligatorio.\n';
            }
        } 


        if (status == 1) {
            var cmd = 'module=tarifas&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        //alert(d.msg);
                        location.href = 'index.php?module=list-tarifas';
                    } else {
                        alert(d.msg);
                    }
                }
            });
        } else {
            alert(msg);
        }
    });

});