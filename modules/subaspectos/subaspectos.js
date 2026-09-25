$(function(){
    function notify(type, title, message, timer){
        if ($.niftyNoty) {
            $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
        } else { alert((title?title+': ':'')+message); }
    }
    
    function cargarInfoAspecto(aspecto_id) {
        if (!aspecto_id) {
            $('#aspecto-info').hide();
            return;
        }
        
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: { module: 'subaspectos', method: 'get_aspecto_info', aspecto_id: aspecto_id },
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    $('#aspecto-nombre').text(response.data.nombre);
                    $('#aspecto-calificacion-max').text(response.data.calificacion_max);
                    $('#subaspectos-suma').text(response.data.subaspectos_suma);
                    $('#aspecto-info').show();
                }
            }
        });
    }
    
    $('#btn-back').on('click', function(){ location.href='?module=list-subaspectos'; });
    $('#btn-new').on('click', function(){ location.href='?module=subaspectos'; });
    
    // Cargar información del aspecto al cambiar la selección
    $('#f-aspecto_id').on('change', function(){
        cargarInfoAspecto($(this).val());
    });
    
    // Cargar información al iniciar si hay un aspecto seleccionado
    if ($('#f-aspecto_id').val()) {
        cargarInfoAspecto($('#f-aspecto_id').val());
    }
    
    $('#btn-save').on('click', function(){
        var status=1, msg='';
        if($('#f-aspecto_id').val()==''){ status=0; msg+='<div>El campo Aspecto es obligatorio.</div>'; }
        if($('#f-descripcion').val()==''){ status=0; msg+='<div>El campo Descripción es obligatorio.</div>'; }
        if($('#f-calificacion-max').val()=='' || $('#f-calificacion-max').val()<=0){ status=0; msg+='<div>El campo Calificación Máxima es obligatorio y debe ser mayor a 0.</div>'; }
        if(status==0){ notify('danger','Validación',msg,4000); return; }
        $('#btn-save').prop('disabled', true);
        var payload={
            module:'subaspectos', method:'save', action: action,
            aspecto_id: $('#f-aspecto_id').val(),
            descripcion: $('#f-descripcion').val(),
            calificacion_max: $('#f-calificacion-max').val()
        };
        if(action==='update'){ payload.id=$('#f-id').val(); }
        $.ajax({ url:'api-app.php', type:'POST', data:payload, dataType:'json'
        }).done(function(d){
            $('#btn-save').prop('disabled', false);
            if(d.status==1){
                if(d.action==='insert'){ action='update'; $('#f-id').val(d.id); }
                notify('success','Éxito', d.msg||'Guardado correctamente', 3000);
                setTimeout(function(){ location.href='?module=list-subaspectos'; }, 1000);
            } else { notify('danger','Error', d.msg||'Error al guardar', 4000); }
        }).fail(function(xhr){
            $('#btn-save').prop('disabled', false);
            notify('danger','Error', xhr.responseText||'Error al guardar', 4000);
        });
    });
});
