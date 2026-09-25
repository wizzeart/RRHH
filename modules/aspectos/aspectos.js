$(function(){
    function notify(type, title, message, timer){
        if ($.niftyNoty) {
            $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
        } else { alert((title?title+': ':'')+message); }
    }
    $('#btn-back').on('click', function(){ location.href='?module=list-aspectos'; });
    $('#btn-new').on('click', function(){ location.href='?module=aspectos'; });
    $('#btn-save').on('click', function(){
        var status=1, msg='';
        if($('#f-nombre').val()==''){ status=0; msg+='<div>El campo Nombre es obligatorio.</div>'; }
        if($('#f-calificacion-max').val()=='' || $('#f-calificacion-max').val()<=0){ status=0; msg+='<div>El campo Calificación Máxima es obligatorio y debe ser mayor a 0.</div>'; }
        if(status==0){ notify('danger','Validación',msg,4000); return; }
        $('#btn-save').prop('disabled', true);
        var payload={
            module:'aspectos', method:'save', action: action,
            nombre: $('#f-nombre').val(),
            calificacion_max: $('#f-calificacion-max').val()
        };
        if(action==='update'){ payload.id=$('#f-id').val(); }
        $.ajax({ url:'api-app.php', type:'POST', data:payload, dataType:'json'
        }).done(function(d){
            $('#btn-save').prop('disabled', false);
            if(d.status==1){
                if(d.action==='insert'){ action='update'; $('#f-id').val(d.id); }
                notify('success','Éxito', d.msg||'Guardado correctamente', 3000);
                setTimeout(function(){ location.href='?module=list-aspectos'; }, 1000);
            } else { notify('danger','Error', d.msg||'Error al guardar', 4000); }
        }).fail(function(xhr){
            $('#btn-save').prop('disabled', false);
            notify('danger','Error', xhr.responseText||'Error al guardar', 4000);
        });
    });
});
