$(document).ready(function () {
  function notify(type, title, message, timer) {
    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
        $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
    } else {
        var text = (title ? (title + ': ') : '') + (message || '');
        try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
  }
  function showAlert(type, title, message) {
    var $container = $('.panel-body').first();
    if ($container.length === 0) $container = $('body');
    var html = '<div class="alert alert-' + type + ' alert-dismissible" role="alert" style="margin:12px 0;">'
             + '  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
             + '  <strong>' + (title||'') + '</strong> ' + (message||'')
             + '</div>';
    $container.find('.alert.alert-' + type).remove();
    $container.prepend(html);
    setTimeout(function(){ $container.find('.alert.alert-' + type).fadeOut(300, function(){ $(this).remove(); }); }, 6000);
  }

  $('#btn-back').click(function(){ location.href = 'index.php?module=list-contratos'; });
  $('#btn-new').click(function(){ location.href = 'index.php?module=contratos'; });

  // Mejor UX para fechas
  $('#f-fecha-inicio, #f-fecha-fin').on('click focus', function(){ if (this.showPicker) this.showPicker(); });

  // Cargar trabajadores dinámicamente si el backend no los insertó en el HTML
  function cargarTrabajadoresSiFaltan() {
    var $sel = $('#f-trabajador');
    if ($sel.length === 0) return;
    var hasOptions = $sel.find('option').length > 1; // más del placeholder
    if (hasOptions) return; // ya poblado desde PHP

    var selected = $sel.data('selected') || ($sel.val() || '');
    $.ajax({
      url: 'api-app.php',
      type: 'GET',
      data: { module: 'trabajadores', method: 'list' },
      dataType: 'json',
      success: function(list) {
        if (Array.isArray(list)) {
          list.sort(function(a,b){ return (a.nombre||'').localeCompare(b.nombre||''); });
          var opts = ['<option value="">Seleccione trabajador</option>'];
          list.forEach(function(t){
            if (t && t.id) {
              var full = ((t.nombre||'') + ' ' + (t.apellidos||'')).trim();
              var sel = selected && (String(selected) === String(t.id)) ? ' selected' : '';
              opts.push('<option value="' + t.id + '"' + sel + '>' + (full||('ID '+t.id)) + '</option>');
            }
          });
          $sel.html(opts.join(''));
        }
      },
      error: function(){ /* silencioso */ }
    });
  }
  cargarTrabajadoresSiFaltan();

  // Checkbox: Sin fecha fin -> deshabilita y limpia fecha_fin
  function aplicarSinFechaFin() {
    var $chk = $('#chk-sin-fecha-fin');
    var $ff = $('#f-fecha-fin');
    if ($chk.is(':checked')) {
      $ff.val('').prop('disabled', true).removeClass('is-invalid');
    } else {
      $ff.prop('disabled', false);
    }
  }
  $('#chk-sin-fecha-fin').on('change', aplicarSinFechaFin);
  aplicarSinFechaFin();

  $('#btn-save').click(function(){
    var errores = [];
    var trabajadorId = ($('#f-trabajador').val()||'').trim();
    var tipo = ($('#f-tipo').val()||'').trim();
    var fi = ($('#f-fecha-inicio').val()||'').trim();
    var ff = ($('#chk-sin-fecha-fin').is(':checked') ? '' : ($('#f-fecha-fin').val()||'').trim());

    if (trabajadorId === '') { errores.push('El trabajador es obligatorio'); $('#f-trabajador').addClass('is-invalid'); } else { $('#f-trabajador').removeClass('is-invalid'); }
    if (tipo === '') { errores.push('El tipo de contrato es obligatorio'); $('#f-tipo').addClass('is-invalid'); } else { $('#f-tipo').removeClass('is-invalid'); }
    if (fi === '') { errores.push('La fecha de inicio es obligatoria'); $('#f-fecha-inicio').addClass('is-invalid'); } else { $('#f-fecha-inicio').removeClass('is-invalid'); }
    if (fi && ff && (new Date(ff) < new Date(fi))) { errores.push('La fecha fin no puede ser anterior a la fecha inicio'); $('#f-fecha-fin').addClass('is-invalid'); }

    if (errores.length) { notify('warning','Validación',errores.join('<br>'),5000); $('.is-invalid').first().focus(); return; }

    var fd = new FormData(document.getElementById('form-contrato'));
    fd.append('module', 'contratos');
    fd.append('method', 'save');

    $('#img-loading').removeClass('hidden');
    $('#btn-save').attr('disabled', true);

    $.ajax({ url:'api-app.php', type:'POST', data: fd, dataType:'json', processData:false, contentType:false,
      success: function(d){
        $('#img-loading').addClass('hidden');
        $('#btn-save').attr('disabled', false);
        if (d.status == 1) {
          if (!$('#f-id').val()) {
            $('#f-id').val(d.id);
            notify('success','¡Registro Exitoso!','El contrato ha sido registrado correctamente. ID: ' + (d.id||''),5000);
            showAlert('success','¡Registro Exitoso!','El contrato ha sido registrado correctamente. ID: ' + (d.id||''));
            window.history.replaceState({}, '', 'index.php?module=contratos&id=' + d.id);
          } else {
            notify('success','¡Actualización Exitosa!','El contrato ha sido actualizado correctamente',5000);
            showAlert('success','¡Actualización Exitosa!','El contrato ha sido actualizado correctamente');
          }
        } else {
          notify('danger', d.msg_title || 'Error', d.msg || 'Error al guardar el contrato', 5000);
          showAlert('danger', d.msg_title || 'Error', d.msg || 'Error al guardar el contrato');
        }
      },
      error: function(xhr, status, error){
        $('#img-loading').addClass('hidden');
        $('#btn-save').attr('disabled', false);
        var rt = xhr && xhr.responseText ? xhr.responseText.trim() : '';
        var title = 'Error al Guardar'; var message = 'No se pudo conectar con el servidor';
        try { if (rt && rt.charAt(0)==='{'){ var jd = JSON.parse(rt); if (jd.msg_title) title = jd.msg_title; if (jd.msg) message = jd.msg; } else if (error) { message = error; } } catch(e){ if (error) message = error; }
        notify('danger', title, message, 5000); showAlert('danger', title, message);
      }
    });
  });
});
