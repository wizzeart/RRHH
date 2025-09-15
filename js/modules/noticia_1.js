var img_news = 'http://labici.net/conocea/upload/news/news.jpg';

$(document).ready(function () {
    // DROPZONE.JS
    // =================================================================
    // Require Dropzone
    // http://www.dropzonejs.com/
    // =================================================================
    //$(".dropzone").dropzone(
    Dropzone.options.demoDropzone = {// The camelized version of the ID of the form element
        // The configuration we've talked about above
        autoProcessQueue: true,
        maxFilesize: 3, // MB
        acceptedFiles: 'image/jpeg',
        maxFiles: 1,
        addRemoveLinks: true,
        uploadMultiple: false,
        //parallelUploads: 25,

        // The setting up of the dropzone
        /*init: function () {
         var myDropzone = this;
         //  Here's the change from enyo's tutorial...
         //  $("#submit-all").click(function (e) {
         //  e.preventDefault();
         //  e.stopPropagation();
         //  myDropzone.processQueue();
         //
         //}
         //    );
         
         }*/
        /*, //comentado pues nos basamos en el nombre del fichero que está en tmp....
         success: function (file, response) {
         console.debug(file);
         console.debug(response);
         mf = file;
         mr = response;
         // to remove
         
         
         if (mf.accepted) {
         var data = JSON.parse(response);
         $(file.previewElement).addClass('dz-success');
         } else {
         $(file.previewElement).addClass('dz-error');
         }
         
         //mf=e;
         //return true;
         }*/

    }
    $('#btn-remove-img').click(function () {
        if ($('#img-main').attr('src') != img_news) {
            var cmd = 'module=noticias&method=del-image&id=' + $(this).data('id');
            $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        $('#img-main').attr('src', img_news);
                    } else {
                        alert(d.msg);
                    }
                }
            });
        }
    });
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-noticias';
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
            //var cmd = 'module=noticias&method=save&' + $.param($('input[name^=x],select[name^=x],textarea[name^=x]').serializeArray());
            //cmd += '&xcontent=' + $('#f-content').code();
            var cmd = {module: "noticias", method: "save"
                , xnoticia_id: $('input[name=xnoticia_id]').val()
                , xnoticia: $('input[name=xnoticia]').val()
                , xintro: $('#f-intro').val()
                , xaudio: $('input[name=xaudio]').val()
                , xvideo: $('input[name=xvideo]').val()
                , xfecha: $('#f-date input').val()
                , xcontent: $('#f-content').code()
                , xcity: cnc_city
            };
            if ($('input[name=xactivo]').prop('checked'))
                cmd['xactivo'] = "on";
            if ($('.dz-preview.dz-success').length == 1) {
                cmd['ximage'] = $('.dz-preview.dz-success .dz-details .dz-filename span').html();
            }
            //console.debug(cmd);
            $.ajax({url: 'api-app.php', type: 'POST', data: cmd, dataType: 'json',
                success: function (d) {
                    if (d.status == 1) {
                        //alert(d.msg);
                        location.href = 'index.php?module=list-noticias';
                    } else {
                        alert(d.msg);
                    }
                }
            });
        } else {
            alert(msg);
        }
    });

    // BOOTSTRAP DATEPICKER WITH AUTO CLOSE
    // =================================================================
    // Require Bootstrap Datepicker
    // http://eternicode.github.io/bootstrap-datepicker/
    // =================================================================
    $('#f-date .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true
    });

    // SUMMERNOTE
    // =================================================================
    // Require Summernote
    // http://hackerwins.github.io/summernote/
    // =================================================================
    $('#f-content').summernote({
        height: 250,
        onImageUpload: function (files, editor, welEditable) {
            sendFile(files[0], editor, welEditable);
            //editor.insertImage(welEditable, '/labici/upload/pic.png');
        }
    });

    function sendFile(file, editor, welEditable) {
        var data = new FormData();
        data.append("file", file);
        $.ajax({
            data: data,
            type: "POST",
            url: "upload.php",
            cache: false,
            contentType: false,
            processData: false,
            success: function (url) {
                editor.insertImage(welEditable, url);
            }
        });
    }

});