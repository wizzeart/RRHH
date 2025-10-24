$(function(){
    function notify(type, title, message, timer){
        if ($.niftyNoty) {
            $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
        } else { alert((title?title+': ':'')+message); }
    }
    $('#btn-back').on('click', function(){ location.href='?module=list-cargos'; });
    $('#btn-new').on('click', function(){ location.href='?module=cargos'; });
    // Actualizar salario/hora en tiempo real cuando se introduce Salario Mensual
    $('#f-salario-mensual').on('input change', function(){
        try {
            var v = parseFloat($(this).val());
            if (!isNaN(v) && v !== 0) {
                var sh = Math.round(v / 192);
                $('#f-salario').val(sh);
            } else {
                // si el campo mensual está vacío o cero, limpiar el salario/hora
                $('#f-salario').val('');
            }
        } catch (e) {
            // no bloquear por errores
        }
    });
    $('#btn-save').on('click', function(){
        var status=1, msg='';
        if($('#f-nombre').val()==''){ status=0; msg+='<div>El campo Nombre es obligatorio.</div>'; }
        if($('#f-departamento').val()==''){ status=0; msg+='<div>El campo Departamento es obligatorio.</div>'; }
        if(status==0){ notify('danger','Validación',msg,4000); return; }
        $('#btn-save').prop('disabled', true);
        // Asegurar conversión: si el usuario introdujo Salario Mensual, dividir por 192 y redondear al entero más cercano
        try {
            var salarioMensualVal = parseFloat($('#f-salario-mensual').val());
            if (!isNaN(salarioMensualVal) && salarioMensualVal !== 0) {
                var salarioHora = Math.round(salarioMensualVal / 192);
                // Garantizar número entero y asignarlo al campo de salario (CUP/HORA)
                $('#f-salario').val(salarioHora);
            }
        } catch (e) {
            // no bloquear envío por error en conversión
        }
        var payload={
            module:'cargos', method:'save', action: action,
            nombre: $('#f-nombre').val(),
            descripcion: $('#f-descripcion').val(),
            salario: $('#f-salario').val(),
            
        };
        if(action==='update'){ payload.id=$('#f-id').val(); }
        $.ajax({ url:'api-app.php', type:'POST', data:payload, dataType:'json'
        }).done(function(d){
            $('#btn-save').prop('disabled', false);
            if(d.status==1){
                if(d.action==='insert'){ action='update'; $('#f-id').val(d.id); }
                notify('success','Éxito', d.msg||'Guardado correctamente', 3000);
                //espera 1 segundo
                setTimeout(function(){ location.href='?module=list-cargos'; }, 1000);
            } else { notify('danger','Error', d.msg||'Error al guardar', 4000); }
        }).fail(function(xhr){
            $('#btn-save').prop('disabled', false);
            notify('danger','Error', xhr.responseText||'Error al guardar', 4000);
        });
    });
});
