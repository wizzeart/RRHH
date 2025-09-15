var e, tmp;
var libgrid = '/plugins/dhtmlxGrid/codebase';

$(document).ready(function () {
    $('#f-categoria').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    $('#f-permiso-agencias').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-pais').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-municipio').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    //$('#f-diametro').chosen({no_results_text: "!Oops, no hay coincidencias!", width: '80%'});
    /*$('#demo-cs-multiselect').chosen({width:'100%'});
     * $('#ev-mascota').trigger('chosen:updated');
     * //$('#mascota').trigger('chosen:updated');
     //$('#s2id_mascota span.select2-chosen').html($('#mascota option:selected').text());
     * 
     * 
     * 
     */

    $('#mac-componente').chosen({width: '100%'});

    $('#tbl-componentes').on('click', '.del-componente', function () {
        $(this).parent().parent().remove();
    });

    $('#btn-do-add-componente').click(function () {
        var status = 1, msg = '';

        if (status == 1) {
            var e = $(tpl_lin_componente);
            $(e).data('coste', $('#mac-componente option:selected').data('coste'));
            $(e).find('td:eq(0)').html($('#mac-componente').val());
            $(e).find('td:eq(1)').html($('#mac-componente option:selected').text());
            $(e).find('td:eq(2)').html($('#mac-cantidad').val());
            $('#tbl-componentes tbody').append(e);
            $('#modalAddComponente').modal('hide');
            calc_total_coste_componente();
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Añadir Componente',
                message: d.msg,
                container: 'floating',
                timer: 3000
            });
        }
    });

    $('#btn-add-componente').click(function () {
        $('#mac-cantidad').val('1');
        $('#mac-componente').val('');
        $('#modalAddComponente').modal('show');
    });

    $('#btn-create-images').click(function () {
        var status = 1, msg = '';

        if ($('#f-articulo-id').val() == '') {
            status = 0;
            msg = 'Artículo no encontrado.';
        }

        if (status == 1) {
            if (confirm('¿Desea regenerar esta imagen para los revendedores?')) {
                var cmd = 'module=articulos&method=create-images&id=' + $('#f-articulo-id').val();
                $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                    success: function (d) {
                        if (d.status == 1) {
                            $.niftyNoty({
                                type: 'success',
                                title: 'Regenerar Imágenes',
                                message: d.msg,
                                container: 'floating',
                                timer: 3000
                            });
                        } else {
                            $.niftyNoty({
                                type: 'danger',
                                title: 'Regenerar Imágenes',
                                message: d.msg,
                                container: 'floating',
                                timer: 3000
                            });
                        }
                    }
                });
            }
        } else {
            $.niftyNoty({
                type: 'danger',
                title: 'Regenerar Imágenes',
                message: msg,
                container: 'floating',
                timer: 3000
            });
        }
    });
    $('#art-obs-an').summernote({
        height: 400
    });
    $('#art-resumen-an').summernote({
        height: 200
    });
    $('#art-obs-sd').summernote({
        height: 400
    });
    $('#art-resumen-sd').summernote({
        height: 200
    });
    $('#art-obs-mr').summernote({
        height: 400
    });
    $('#art-resumen-mr').summernote({
        height: 200
    });
    $('#art-obs').summernote({
        height: 400
    });
    $('#art-resumen').summernote({
        height: 200
    });
    $('#art-infadd').summernote({
        height: 400
    });
    load_recargas();
    init_dropzone();
    init_grid();
    $('#btn-img-an').click(function () {
        $('#dst-imagen').val('allnovu');
        $('#modalUpload .gallery-container').empty();
        $('#modalUpload').modal('show');
    });
    $('#btn-img-mr').click(function () {
        $('#dst-imagen').val('mercarapid');
        $('#modalUpload .gallery-container').empty();
        $('#modalUpload').modal('show');
    });
    $('#btn-img-rev').click(function () {
        $('#dst-imagen').val('revendedores');
        $('#modalUpload .gallery-container').empty();
        $('#modalUpload').modal('show');
    });
    $('#btn-img').click(function () {
        $('#dst-imagen').val('mandasaldo');
        $('#modalUpload .gallery-container').empty();
        $('#modalUpload').modal('show');
    });
    $('#btn-back').click(function () {
        //location.href = 'index.php?module=list-articulos';
        window.close();
    });
    $('#btn-new').click(function () {
        location.href = 'index.php?module=articulos';
    });
    $('#btn-save').click(function () {
        var status = 1, lin = [], linc = [];
        var msg = '', e = '';

        if ($('#f-precio-fijo-cup').val() == '') {
            $('#f-precio-fijo-cup').val('0,00');
        }

        if ($('#f-articulo').val() == '') {
            status = 0;
            msg += 'El campo Descripción del Producto es obligatorio.\n';
        }

        if ($('#f-infadd-activo').val() == 'S' && ($('#f-infadd-title').val() == '' || $('#f-infadd-content').val() == '')) {
            status = 0;
            msg += 'Si la información adicional del producto está activo tiene que rellenar el título y su contenido.\n';
        }

        if ($('#f-activo-mr').val() == 'N' && $('#f-url-amigable-mr').val() == '')
            $('#f-url-amigable-mr').val('sin-valor');

        if (status == 1) {
            $('#img-loading').removeClass('hidden');
            $('#btn-save').attr('disabled', true);

            $('#tbl-componentes tbody tr').each(function (i, v) {
                var d = [];
                d.push($(v).find('td:eq(0)').html());
                d.push($(v).find('td:eq(2)').html());
                linc.push(d.join('|'));
            });
            //console.debug(linc);

            mygrid.forEachRow(function (id) {
                if (mygrid.cellById(id, 1).getValue() != '') {//&& mygrid.cellById(id, 2).getValue() != ''
                    var d = [];
                    d.push(mygrid.cellById(id, 0).getValue());
                    d.push(encodeURIComponent(mygrid.cellById(id, 2).getValue()));
                    d.push(encodeURIComponent(mygrid.cellById(id, 3).getValue().replace(/,/gi, '.')));
                    lin.push(d.join('|'));
                }
            });

            var cmd = 'module=articulos&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            cmd += '&action=' + action;
            if (action == 'update')
                cmd += '&xarticulo_id=' + $('#f-articulo-id').val();
            e = $('#f-categoria').val();
            cmd += '&xcategorias=';
            if (e != null)
                cmd += e.join(',');
            e = $('#f-permiso-agencias').val();
            cmd += '&xpermiso_agencias=';
            if (e != null)
                cmd += e.join(',');
            cmd += '&xobs=' + encodeURIComponent($('#art-obs').code());
            cmd += '&xresumen=' + encodeURIComponent($('#art-resumen').code());
            cmd += '&xobs_mr=' + encodeURIComponent($('#art-obs-mr').code());
            cmd += '&xresumen_mr=' + encodeURIComponent($('#art-resumen-mr').code());
            cmd += '&xobs_an=' + encodeURIComponent($('#art-obs-an').code());
            cmd += '&xresumen_an=' + encodeURIComponent($('#art-resumen-an').code());
            cmd += '&xinfadd_content=' + encodeURIComponent($('#art-infadd').code());
            cmd += '&lin=' + lin.join('#');
            cmd += '&linc=' + linc.join('#');
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    $('#img-loading').addClass('hidden');
                    $('#btn-save').attr('disabled', false);
                    if (d.status == 1) {
                        if (d.action == 'insert') {
                            action = 'update';
                            $('#f-articulo-id').val(d.id);
                        }
                        $('#f-img').val('');
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


function init_dropzone() {
    Dropzone.options.imageGalleryDropzone = false; // Prevent Dropzone from auto discovering this element
    var dropZoneTemplate = $.get('tpl/gallery-dropzone-template.html', function (template) {
        $('#imageGalleryDropzone').dropzone({
            paramName: "file", // The name that will be used to transfer the file
            maxFilesize: 10, // MB
            //acceptedFiles: '.jpg,.jpeg,.png,.gif',
            acceptedFiles: '.jpg',
            uploadMultiple: false,
            previewsContainer: '.gallery-container',
            previewTemplate: template,
            init: function () {
                this.on("addedfile", function (d) {
                    // alert("Added file."); 
                    //console.debug(d);
                    //console.debug(d.status);
                    /*if (d.status == 'added') {
                     console.debug('fichero añadido');
                     try {
                     var res = JSON.parse(d.xhr.response);
                     console.debug(res);
                     switch (res.module) {
                     case 'mascota':
                     alert('ok');
                     break;
                     }
                     } catch (err) {
                     console.debug(err + ' response xhr:' + d.xhr.response);
                     }
                     }*/
                });
                this.on("success", function (d) {
                    // alert("Added file."); 
                    //console.debug(d.status);
                    if (d.status == 'success') {
                        try {
                            var res = JSON.parse(d.xhr.response);
                            console.debug(res);

                            if (res.dst_image == 'mandasaldo') {
                                $('#img-upload').attr('src', res.url + '?' + (new Date()).valueOf());
                                $('#f-img').val(res.filename);
                            }
                            if (res.dst_image == 'revendedores') {
                                $('#img-upload-rev').attr('src', res.url);
                                $('#f-img-rev').val(res.filename);
                            }
                            if (res.dst_image == 'mercarapid') {
                                $('#img-upload-mr').attr('src', res.url);
                                $('#f-img-mr').val(res.filename);
                            }
                            if (res.dst_image == 'allnovu') {
                                $('#img-upload-an').attr('src', res.url);
                                $('#f-img-an').val(res.filename);
                            }
                            $('#modalUpload').modal('hide');
                        } catch (err) {
                            console.debug(err + ' response xhr:' + d.xhr.response);
                        }
                    }
                });
            }
        });
    })
            .fail(function () {
                alert("Image Gallery Error: could not load gallery html template");
            });
}

function init_grid() {
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath(libgrid + "/imgs/");
    mygrid.setHeader("Código,Proveedor,Recarga,Coste"); //14
    mygrid.setInitWidths("80,200,100,100");
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
        if (stage == 1 && ($.inArray(cInd, [3]) >= 0) && this.editor && this.editor.obj) {
            $(this.editor.obj).attr('style', 'text-align:right');
            $(this.editor.obj).select();
        }
        if (stage == 2) {
            //colocamos el codigo cuando se modifica una celda
            rowId = rId;
            var mresult = true;
            switch (cInd) {
                case 2://cantidad
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
    var cmd = 'module=articulos&method=recargas&id=' + $('#f-articulo-id').val();
    $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
        success: function (d) {
            var r;
            //console.debug(d);
            $.each(d, function (i, v) {
                r = add_row();
                mygrid.cellById(r, 0).setValue(v.xproveedor_id);
                mygrid.cellById(r, 1).setValue(v.xproveedor);
                mygrid.cellById(r, 2).setValue(v.xrecargas);
                mygrid.cellById(r, 3).setValue(v.xcoste);
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(textStatus);
        }
    });
}

var tpl_lin_componente = '\
    <tr>\
        <td>ID</td>\
        <td>Componente</td>\
        <td class="text-right">Cantidad</td>\
        <td class="text-center">\
            <a class="btn btn-xs btn-danger del-componente" href="javascript:void(0)" title="Eliminar Componente"><i class="fa fa-trash"></i></a>\
        </td>\
    </tr>\
';

function calc_total_coste_componente() {
    var importe = 0;
    $('#tbl-componentes tbody tr').each(function (i, v) {
        importe += parseFloat($(v).data('coste')) * parseFloat($(v).find('td:eq(2)').html());
    });
    $('#f-coste-cup').val($.number(importe, 2, ',', ''));
    console.debug(importe);
}