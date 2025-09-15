var e, tmp;
var libgrid = '/plugins/dhtmlxGrid/codebase';

$(document).ready(function () {
    //$('#f-provincia').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
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
    //load_recargas();
    //init_grid();
    $('#f-fecha .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        language: 'es'
    });
    $('#btn-new').click(function () {
        location.href = '?module=contenedores';
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-contenedores';
    });
    $('#btn-save').click(function () {
        var status = 1, lin = [];
        var msg = '';

        if ($('#f-contenedor').val() == '') {
            status = 0;
            msg += 'El campo Nombre es obligatorio.\n';
        }
        if ($('#f-orden').val() == '')
            $('#f-orden').val('0');

        if (status == 1) {
            $('#img-loading').removeClass('hidden');
            $('#btn-save').attr('disabled', true);


            var cmd = 'module=contenedores&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            cmd += '&action=' + action;
            if (action == 'update')
                cmd += '&xcontenedor_id=' + $('#f-contenedor-id').val();
            cmd += '&lin=' + lin.join('#');
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        if (d.action == 'insert') {
                            action = 'update';
                            $('#f-contenedor-id').val(d.id);
                        }
                        $('#f-pwd').val('');
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

function init_grid() {
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath(libgrid + "/imgs/");
    mygrid.setHeader("Código,Descripción,Recarga,Coste"); //14
    mygrid.setInitWidths("80,300,100,100");
    mygrid.setColAlign("left,left,left,right");
    mygrid.setColTypes("ro,ro,ed,ed");

    dhtmlXCalendarObject.prototype.langData["es"] = {
        dateformat: '%d/%m/%Y',
        monthesFNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        monthesSNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        daysFNames: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
        daysSNames: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        weekstart: 1,
        weekname: "w",
        today: "Hoy",
        clear: "Borrar"
    };
    dhtmlXCalendarObject.prototype.lang = 'es';
    mygrid.init();
    mygrid.attachEvent("onEditCell", function (stage, rId, cInd, nValue, oValue) {
        //console.debug('stage: ' + stage);
        var status, msg;
        //para alinear el contenido del input a la izquierda en caso de números
        if (stage == 1 && ($.inArray(cInd, [3]) >= 0) && this.editor && this.editor.obj) {
            $(this.editor.obj).attr('style', 'text-align:right');
            $(this.editor.obj).select();
        }
        if (stage == 2) {
            //colocamos el codigo cuando se modifica una celda
            rowId = rId;
            var mresult = true;
            switch (cInd) {
                case 2://recarga
                    /*window.setTimeout(function () {
                     var n = mygrid.getRowsNum();
                     var i = mygrid.getRowIndex(rId);
                     //mygrid.cellById(rId, 6).setValue(c);//asginamos coste calculado
                     if ((n - 1) > i)
                     mygrid.selectCell(i + 1, cInd, false, false, true, true);
                     }, 100);*/
                    break;
                case 3:
                    var n;
                    n = nValue.replace(/,/gi, '.');
                    if ($.isNumeric(n)) {
                        nValue = $.number(parseFloat(n), 2, ',', '');
                    }
                    window.setTimeout(function () {
                        mygrid.cellById(rId, cInd).setValue(nValue);
                        //calc_importe_lin(rId);
                        //calc_importe_total();
                        //calc_importe_iva();
                        //mygrid.selectCell(mygrid.getRowIndex(rId), 4, false, false, true, true);
                    }, 100);
                    break;
            }

            return mresult;
        }
    });
}

function add_row() {
    var id = (new Date()).valueOf() + '' + Math.round(Math.random() * 1000, 0);
    mygrid.addRow(id, "");
    //mygrid.cellById(id, 0).setValue('');
    //mygrid.cellById(id, 1).setValue('');
    //mygrid.cellById(id, 2).setValue('');
    //mygrid.cellById(id, mygrid.getColumnsNum() - 1).setValue('img/grid-icons/remove.png^Eliminar Línea^javascript:delete_row(' + id + ');^_self');
    mygrid.selectRowById(id, false, true, false);
    return id;
}

function load_recargas() {
    var cmd = 'module=contenedores&method=recargas&id=' + $('#f-contenedor-id').val();
    $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
        success: function (d) {
            var r;
            //console.debug(d);
            $.each(d, function (i, v) {
                r = add_row();

                mygrid.cellById(r, 0).setValue(v.xarticulo_id);
                mygrid.cellById(r, 1).setValue(v.xarticulo);
                mygrid.cellById(r, 2).setValue(v.xrecargas);
                mygrid.cellById(r, 3).setValue(v.xcoste);
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(textStatus);
        }
    });
}
