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

  // Abrir modal de plantilla según tipo seleccionado
  $('#btn-open-template').on('click', function(){
    var tipo = $('#f-tipo').val() || '';
    if (!tipo) { notify('warning','Validación','Seleccione un tipo de contrato antes de generar'); return; }
  $('#contrato-template-container').html('Cargando plantilla...');
    // Map types to php template paths per user's request
    var map = {
      'Contrato por Tiempo Indeterminado': 'docs/contrato-de-trabajo-indeterminado/contrato-de-trabajo-indeterminado.php',
      'Contrato por Tiempo Determinado': 'docs/cotrato_pedriodo_prueba/contrato-de-trabajo-periodo-de-prueba-1.php',
      'Suplemento': 'docs/suplemento/suplemento-al-contrato-de-trabajo.php'
    };
    var tpl = map[tipo] || null;
  if (!tpl) { $('#contrato-template-container').html('<div class="text-danger">Plantilla no definida para este tipo</div>'); return; }
    // Load the raw PHP file content via AJAX (served statically by the server)
  $.ajax({ url: tpl, method: 'GET', dataType: 'html', success: function(html){
    try {
      // Create a container to parse the returned HTML
      var $tmp = $('<div>').html(html);
            // Determine base path for resources (css/images) from the template path
            var tplBase = tpl.substring(0, tpl.lastIndexOf('/') + 1);
            // basePath: the directory of the current page (so we preserve any project subfolder)
            var basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

      // Import any <link rel="stylesheet"> from the template into the document head (avoid duplicates)
      $tmp.find('link[rel="stylesheet"]').each(function(){
        var href = $(this).attr('href') || '';
        if (!href) return;
                var absHref = href.match(/^https?:|^\//) ? href : (basePath + tplBase + href);
        // Add with a data attribute to avoid duplicates
        if ($('head link[data-template-src="' + absHref + '"]').length === 0) {
          var $ln = $('<link rel="stylesheet" type="text/css" />').attr('href', absHref).attr('data-template-src', absHref);
          $('head').append($ln);
        }
      });

      // Rewrite relative image/src and link hrefs inside the template to absolute paths so they resolve
        $tmp.find('[src]').each(function(){
        var $el = $(this);
        var src = $el.attr('src') || '';
        if (!src) return;
        if (!src.match(/^https?:|^\//)) {
          $el.attr('src', basePath + tplBase + src);
        }
      });
      $tmp.find('[href]').each(function(){
        var $el = $(this);
        var href = $el.attr('href') || '';
        // only rewrite local css/font links we parsed earlier; skip anchors
        if (!href) return;
        if (!href.match(/^https?:|^\//) && (href.indexOf('.css') !== -1 || href.indexOf('.woff') !== -1 || href.indexOf('.ttf') !== -1)) {
          $el.attr('href', basePath + tplBase + href);
        }
      });

      // Extract body content if present, otherwise use whole HTML
      var $body = $tmp.find('body');
      var contentHtml = $body.length ? $body.html() : $tmp.html();

      // Convert blanks to inputs (keeps placeholders intact)
      var processed = convertBlanksToInputs(contentHtml);
      $('#contrato-template-container').html(processed);
      // Immediately inject current extras so the on-page template displays filled values
      try {
        var currentExtras = collectExtraValues();
        var curHtml = $('#contrato-template-container').html();
        // Replace CARGO word if template uses it
        if (currentExtras.cargo_nombre) curHtml = curHtml.replace(/\bCARGO\b/i, currentExtras.cargo_nombre);
        curHtml = fillDeOtraParteInHtml(curHtml, currentExtras);
        $('#contrato-template-container').html(curHtml);

        // DOM-level fallbacks: if template still shows literal words like 'salario' or the checkboxes text,
        // try to replace the first occurrences so values appear in the visible boxes.
        var $cont = $('#contrato-template-container');
        // salario fallback
        if (currentExtras.salario) {
          var $sal = $cont.find('span').filter(function(){ return $(this).text().trim().toLowerCase() === 'salario' || $(this).text().trim().match(/^_+$/); }).first();
          if ($sal.length) $sal.text(currentExtras.salario);
        }
        // formas_pago fallback: map numeric values to labels
        if (currentExtras.formas_pago && currentExtras.formas_pago.length) {
          var mapFormas = { '1':'A sueldo', '2':'Por tarifa horaria', '3':'Por resultados', '4':'A Destajo' };
          var formasText = currentExtras.formas_pago.map(function(v){ return mapFormas[v] || v; }).join('; ');
          // Find a span that contains 'Por resultados' or 'A Destajo' or similar, else fallback to first small span
          var $fp = $cont.find('span').filter(function(){ var t=$(this).text().trim(); return /Por resultados|A Destajo|tarifa horaria|A sueldo/i.test(t); }).first();
          if ($fp.length) { $fp.text(formasText); } else {
            var $small = $cont.find('span').filter(function(){ return $(this).text().trim().length < 40; }).eq(0);
            if ($small.length) $small.text(formasText);
          }
        }
        // frecuencia fallback
        if (currentExtras.frecuencia_pago) {
          var $freq = $cont.find('span').filter(function(){ return $(this).text().trim().toLowerCase().indexOf('frecuencia') !== -1; }).first();
          if ($freq.length) {
            // replace the whole node text to include selected value
            $freq.text('La frecuencia de pago será: ' + currentExtras.frecuencia_pago);
          } else {
            // fallback: replace first occurrence of 'Mensual'/'Quincenal'/'Semanal' with selected
            var $f2 = $cont.find('span').filter(function(){ return /Mensual|Quincenal|Semanal/i.test($(this).text().trim()); }).first();
            if ($f2.length) $f2.text(currentExtras.frecuencia_pago);
          }
        }

      } catch(e) { console.warn('No se pudo inyectar extras en vista previa:', e); }
      // scroll container to top
      $('#contrato-template-container').scrollTop(0);
    } catch(e) {
      console.error('Error procesando plantilla:', e);
      $('#contrato-template-container').html('<div class="text-danger">Error al procesar plantilla</div>');
    }
    }, error: function(){ $('#contrato-template-container').html('<div class="text-danger">Error al cargar plantilla</div>'); }
  });
  });

  // Generate PDF using server-side FPDF (new button)
  $('#btn-generate-fpdf').on('click', function(){
    var tipo = $('#f-tipo').val() || '';
    if (!tipo) { notify('warning','Validación','Seleccione un tipo de contrato'); return; }
    var extras = collectExtraValues();
    // send tipo + extras + trabajador to backend to render PHP and create PDF via FPDF
    $('#btn-generate-fpdf').attr('disabled', true).text('Generando...');
    $.ajax({ url: 'api-app.php', method: 'POST', data: { module: 'contratos', method: 'generatePdfWithFpdf', tipo: tipo, extras: JSON.stringify(extras), trabajador_id: $('#f-trabajador').val(), contrato_id: $('#f-id').val() }, dataType: 'json',
      success: function(d){ $('#btn-generate-fpdf').attr('disabled', false).text('Generar PDF (FPDF)'); if (d && d.status==1) { notify('success','Generado','PDF creado: ' + (d.file_url||'')); if (d.file_url) window.open(d.file_url,'_blank'); } else { notify('danger','Error', d.msg || 'No se pudo generar PDF'); } },
      error: function(){ $('#btn-generate-fpdf').attr('disabled', false).text('Generar PDF (FPDF)'); notify('danger','Error','No se pudo generar PDF'); }
    });
  });

  // Render extras when tipo changes
  function renderExtrasForTipo(tipo) {
    var $cont = $('#extra-contrato-fields');
    $cont.empty();
    if (!tipo) return;
    if (tipo === 'Contrato por Tiempo Indeterminado') {
      // cargo select from API, salario input, formas de pago (4), frecuencia (3)
      var html = '';
      html += '<div class="col-md-4">\n';
      html += '<div class="form-group">\n<label>Cargo <span class="text-danger">*</span></label>\n<select id="f-cargo" name="cargos_id" class="form-control"><option value="">Cargando...</option></select>\n</div>\n</div>\n';
      html += '<div class="col-md-2">\n<div class="form-group">\n<label>Salario</label>\n<input type="number" step="0.01" id="f-salario" name="salario" class="form-control" />\n</div>\n</div>\n';
      html += '<div class="col-md-6">\n<div class="form-group">\n<label>Formas de Pago</label><br/>';
      html += '<label class="mr-2"><input type="checkbox" name="formas_pago[]" value="1"> 1 A sueldo</label>';
      html += '<label class="mr-2"><input type="checkbox" name="formas_pago[]" value="2"> 2 Por tarifa horaria</label>';
      html += '<label class="mr-2"><input type="checkbox" name="formas_pago[]" value="3"> 3 Por resultados</label>';
      html += '<label class="mr-2"><input type="checkbox" name="formas_pago[]" value="4"> 4 A Destajo</label>';
  html += '<div class="mt-2">\n<label>Frecuencia de Pago</label><br/>';
  html += '<label class="mr-2"><input type="radio" name="frecuencia_pago" value="Mensual"> Mensual</label>';
  html += '<label class="mr-2"><input type="radio" name="frecuencia_pago" value="Quincenal"> Quincenal</label>';
  html += '<label class="mr-2"><input type="radio" name="frecuencia_pago" value="Semanal"> Semanal</label>';
  html += '</div></div></div>';

  // DE OTRA PARTE fields
  html += '<div class="col-md-12 mt-3">\n<div class="card">\n<div class="card-body">\n<h5>DE OTRA PARTE</h5>\n<div class="row">\n<div class="col-md-4">\n<div class="form-group">\n<label>Nombre</label>\n<input type="text" id="f-de-nombre" class="form-control" />\n</div>\n</div>\n<div class="col-md-2">\n<div class="form-group">\n<label>Carné No.</label>\n<input type="text" id="f-de-ci" class="form-control" />\n</div>\n</div>\n<div class="col-md-6">\n<div class="form-group">\n<label>Domicilio</label>\n<input type="text" id="f-de-domicilio" class="form-control" />\n</div>\n</div>\n<div class="col-md-4">\n<div class="form-group">\n<label>Municipio</label>\n<input type="text" id="f-de-municipio" class="form-control" />\n</div>\n</div>\n<div class="col-md-4">\n<div class="form-group">\n<label>Provincia</label>\n<input type="text" id="f-de-provincia" class="form-control" />\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>';

      $cont.html(html);

      // Load cargos into select
      $.ajax({ url:'api-app.php', type:'GET', data:{ module:'cargos', method:'list' }, dataType:'json', success: function(list){
        var $sel = $('#f-cargo'); if (!$sel.length) return;
        var opts = ['<option value="">Seleccione</option>'];
        if (Array.isArray(list)) {
          list.forEach(function(c){ opts.push('<option value="'+(c.id||'')+'">'+(c.nombre||('ID '+c.id))+'</option>'); });
        }
        $sel.html(opts.join(''));
      }, error:function(){ $('#f-cargo').html('<option value="">Error</option>'); }});
    }
  }

  // bind change
  $('#f-tipo').on('change', function(){ renderExtrasForTipo($(this).val()); });
  // initial render if value present
  renderExtrasForTipo($('#f-tipo').val());

  // Collect extras to inject into template replacements
  function collectExtraValues() {
    var extras = {};
    // cargo
    extras.cargos_id = $('#f-cargo').val() || '';
    extras.cargo_nombre = $('#f-cargo option:selected').text() || '';
    extras.salario = $('#f-salario').val() || '';
  extras.formas_pago = [];
  $('input[name="formas_pago[]"]:checked').each(function(){ extras.formas_pago.push($(this).val()); });
  extras.frecuencia_pago = $('input[name="frecuencia_pago"]:checked').val() || '';
    // DE OTRA PARTE fields (create inputs if not present)
    extras.de_nombre = $('#f-de-nombre').val() || $('#f-trabajador option:selected').text() || '';
    extras.de_ci = $('#f-de-ci').val() || '';
    extras.de_domicilio = $('#f-de-domicilio').val() || '';
    extras.de_municipio = $('#f-de-municipio').val() || '';
    extras.de_provincia = $('#f-de-provincia').val() || '';
    return extras;
  }

  // Fill the 'DE OTRA PARTE' block in HTML by simple replace of labels/underscores
  function fillDeOtraParteInHtml(html, extras) {
    // Replace named placeholders inserted into the template
    html = html.split('{{DE_NOMBRE}}').join(extras.de_nombre || '_____________');
    html = html.split('{{DE_CI}}').join(extras.de_ci || '________');
    html = html.split('{{DE_DOMICILIO}}').join(extras.de_domicilio || '________________');
    html = html.split('{{DE_MUNICIPIO}}').join(extras.de_municipio || '_____');
    html = html.split('{{DE_PROVINCIA}}').join(extras.de_provincia || '_____');
    // Cargo, salario, formas and frecuencia placeholders
    html = html.split('{{CARGO}}').join(extras.cargo_nombre || '');
    html = html.split('{{SALARIO}}').join(extras.salario || '');
    html = html.split('{{FORMAS_PAGO}}').join((extras.formas_pago && extras.formas_pago.length) ? extras.formas_pago.join(', ') : '');
    html = html.split('{{FRECUENCIA_PAGO}}').join(extras.frecuencia_pago || '');
    return html;
  }

  // When generating from modal, include extras
  $('#btn-save-and-generate').off('click').on('click', function(){
    var tipo = $('#f-tipo').val() || '';
    if (!tipo) { notify('warning','Validación','Seleccione un tipo de contrato'); return; }
    var $cont = $('#contrato-template-container'); if ($cont.length === 0) { notify('danger','Error','Plantilla no cargada'); return; }
    var clone = $cont.clone();
    clone.find('input.contrato-field').each(function(){ var v = $(this).val() || '_____'; $(this).replaceWith($('<span>').text(v)); });
  var finalHtml = clone.html();
  // inject extras substitutions for DE OTRA PARTE and cargo name
  var extras = collectExtraValues();
  if (extras.cargo_nombre) finalHtml = finalHtml.replace(/\bCARGO\b/i, extras.cargo_nombre);
  finalHtml = fillDeOtraParteInHtml(finalHtml, extras);
  // Prepend any template-specific CSS links we injected earlier so mPDF can pick them up
  var cssLinks = '';
  $('head link[data-template-src]').each(function(){ cssLinks += this.outerHTML; });
  finalHtml = '<!doctype html><html><head>' + cssLinks + '</head><body>' + finalHtml + '</body></html>';
    $('#btn-save-and-generate').attr('disabled', true).text('Generando...');
    $.ajax({ url: 'api-app.php', method: 'POST', data: { module: 'contratos', method: 'generatePdfFromHtml', tipo: tipo, html: finalHtml, contrato_id: $('#f-id').val() }, dataType: 'json',
      success: function(d){ $('#btn-save-and-generate').attr('disabled', false).text('Guardar y Generar'); if (d && d.status==1) { notify('success','Generado','Contrato generado'); if (d.file_url) { $('#btn-download-pdf').off('click').on('click', function(){ window.open(d.file_url,'_blank'); }); } } else { notify('danger','Error', d.msg || 'No se generó'); } },
      error: function(){ $('#btn-save-and-generate').attr('disabled', false).text('Guardar y Generar'); notify('danger','Error','No se pudo generar el contrato'); }
    });
  });

  // Convierte ocurrencias de guiones bajos (4 o más) en inputs editables con data-key incremental
  function convertBlanksToInputs(html) {
    var counter = 0;
    // regex para secuencias de guiones bajos o líneas cortas: ____+
    return html.replace(/_{3,}|____+/g, function(match){
      counter++;
      return '<input class="contrato-field" data-key="field_' + counter + '" style="border-bottom:1px solid #000; min-width:80px; margin:0 4px;" />';
    });
  }

  // Guardar y generar: recoger valores de inputs y enviar HTML final al backend para generar PDF
  $('#btn-save-and-generate').on('click', function(){
    var tipo = $('#f-tipo').val() || '';
    if (!tipo) { notify('warning','Validación','Seleccione un tipo de contrato'); return; }
    // Extraer HTML de la plantilla
    var $cont = $('#contrato-template-container');
    if ($cont.length === 0) { notify('danger','Error','Plantilla no cargada'); return; }
    // Reemplazar inputs por su valor en el HTML
    var clone = $cont.clone();
    clone.find('input.contrato-field').each(function(){
      var v = $(this).val() || '_____';
      $(this).replaceWith($('<span>').text(v));
    });
  var finalHtml = clone.html();
  // inject extras placeholders
  var extras = collectExtraValues();
  if (extras.cargo_nombre) finalHtml = finalHtml.replace(/\bCARGO\b/i, extras.cargo_nombre);
  finalHtml = fillDeOtraParteInHtml(finalHtml, extras);
  // Prepend any template-specific CSS links for mPDF
  var cssLinks2 = '';
  $('head link[data-template-src]').each(function(){ cssLinks2 += this.outerHTML; });
  finalHtml = '<!doctype html><html><head>' + cssLinks2 + '</head><body>' + finalHtml + '</body></html>';
  // POST al backend para generar y guardar PDF
    $('#btn-save-and-generate').attr('disabled', true).text('Generando...');
    $.ajax({ url: 'api-app.php', method: 'POST', data: { module: 'contratos', method: 'generatePdfFromHtml', tipo: tipo, html: finalHtml, contrato_id: $('#f-id').val() }, dataType: 'json',
      success: function(d){
        $('#btn-save-and-generate').attr('disabled', false).text('Guardar y Generar');
        if (d && d.status == 1) {
          notify('success','Generado','Contrato generado correctamente');
          if (d.file_url) {
            $('#btn-download-pdf').off('click').on('click', function(){ window.open(d.file_url, '_blank'); });
            // también actualizar enlace en el formulario
            if (d.file_url && $('#f-id').val()) {
              // actualizar vista previa del archivo
              // reemplaza el texto si existe
            }
          }
          // cerrar modal opcional
          // $('#modal-contrato').modal('hide');
        } else {
          notify('danger','Error', d && d.msg ? d.msg : 'Error al generar PDF');
        }
      },
      error: function(xhr){ $('#btn-save-and-generate').attr('disabled', false).text('Guardar y Generar'); notify('danger','Error','No se pudo generar el contrato'); }
    });
  });

  // Descargar: si ya se generó, abrir; si no, obtener HTML actual y generar (fallback)
  $('#btn-download-pdf').on('click', function(){
    var tipo = $('#f-tipo').val() || '';
    var $cont = $('#contrato-template-container');
    if ($cont.find('span').length === 0 && $cont.find('input.contrato-field').length > 0) {
      // generar primero
      $('#btn-save-and-generate').trigger('click');
      return;
    }
    // si ya tiene enlace configurado, el handler anterior lo abrirá
  });
});
