$(document).ready(function () {
  // Variables globales para datos del trabajador
  var trabajadorData = {};
  var contratoId = $('#f-id').val();
  
  // Si es edición (existe contratoId), cargar los datos guardados
  if (contratoId) {
    // Campos del formulario a actualizar
    ['tipo_contrato', 'regimen_trabajo_desde', 'regimen_trabajo_hasta', 
     'hora_desde_h', 'hora_hasta_h', 'modalidad_trabajo', 'departamento_id',
     'regimen_descanso', 'salario_base', 'frecuencia_trabajo'].forEach(function(campo) {
      var valor = $('[name="' + campo + '"]').data('valor-guardado');
      if (valor) {
        $('#f-' + campo.replace(/_/g, '-')).val(valor);
      }
    });
  }
  
  // Cargar datos iniciales si hay un trabajador seleccionado
  var trabajadorIdInicial = $('#f-trabajador').val();
  if (trabajadorIdInicial) {
    cargarDatosTrabajador(trabajadorIdInicial);
  }
  
  function notify(type, title, message, timer) {
    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
        $.niftyNoty({ type: type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
    } else {
        var text = (title ? (title + ': ') : '') + (message || '');
        try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
  }

  $('#btn-back').click(function(){ location.href = 'index.php?module=list-contratos'; });

  // Función para cargar datos del trabajador seleccionado
  function cargarDatosTrabajador(trabajadorId) {
    if (!trabajadorId) {
      limpiarDatosTrabajador();
      return;
    }
    
    $.ajax({
      url: 'api-app.php',
      type: 'GET',
      data: { module: 'contratos', method: 'get-trabajador-data', trabajador_id: trabajadorId },
      dataType: 'json',
      success: function(response) {
        if (response.status == 1 && response.data) {
          trabajadorData = response.data;
          mostrarDatosTrabajador(response.data);
          actualizarVistaPrevia();
        } else {
          notify('warning', 'Advertencia', response.msg || 'No se pudieron cargar los datos del trabajador');
          limpiarDatosTrabajador();
        }
      },
      error: function() {
        notify('danger', 'Error', 'Error al cargar datos del trabajador');
        limpiarDatosTrabajador();
      }
    });
  }

  // Función para mostrar datos del trabajador en el panel
  function mostrarDatosTrabajador(data) {
    // Actualizar los campos de solo lectura
    $('#trabajador-nombre').text(data.nombre || '-');
    $('#trabajador-apellidos').text(data.apellidos || '-');
    $('#trabajador-apellidos-segundos').text(data.apellidos_segundos || '-');
    $('#trabajador-cargo').text(data.cargo_nombre || '-');
    $('#trabajador-provincia').text(data.provincia_nombre || '-');
    $('#trabajador-municipio').text(data.municipio_nombre || '-');
    $('#trabajador-direccion').text(data.direccion || '-');

    // Actualizar campos del formulario si existen valores en data
    if (data.regimen_trabajo_desde) {
      $('#f-regimen-trabajo-desde').val(data.regimen_trabajo_desde);
    }
    if (data.regimen_trabajo_hasta) {
      $('#f-regimen-trabajo-hasta').val(data.regimen_trabajo_hasta);
    }
    if (data.hora_desde_h) {
      $('#f-desde-hora').val(data.hora_desde_h);
    }
    if (data.hora_hasta_h) {
      $('#f-hasta-hora').val(data.hora_hasta_h);
    }
    if (data.modalidad_trabajo) {
      $('#f-modalidad-trabajo').val(data.modalidad_trabajo);
    }
    if (data.tipo_contrato) {
      $('#f-tipo-contrato').val(data.tipo_contrato);
    }
    if (data.departamento_id) {
      $('#f-ubicacion-laboral').val(data.departamento_id);
    }
    if (data.regimen_descanso) {
      $('#f-regimen-descanso').val(data.regimen_descanso);
    }
    if (data.salario_base) {
      $('#f-salario-base').val(data.salario_base);
    }
    if (data.frecuencia_trabajo) {
      $('#f-frecuencia-trabajo').val(data.frecuencia_trabajo);
    }

    // Disparar el evento change para actualizar la vista previa
    actualizarVistaPrevia();
  }

  // Función para limpiar datos del trabajador
  function limpiarDatosTrabajador() {
    trabajadorData = {};
    $('#trabajador-nombre, #trabajador-apellidos, #trabajador-apellidos-segundos, #trabajador-cargo, #trabajador-provincia, #trabajador-municipio, #trabajador-direccion').text('-');
    actualizarVistaPrevia();
  }

  // Event listener para cambio de trabajador
  $('#f-trabajador').on('change', function() {
    var trabajadorId = $(this).val();
    cargarDatosTrabajador(trabajadorId);
  });

  // Estado inicial del botón "Ver grande" (antes Generar PDF): sin lógica especial
  // (Se mantiene habilitado; no depende de existencia de archivo PDF)

  // Event listeners para actualización en tiempo real
  $('#f-tipo-contrato, #f-ubicacion-laboral, #f-regimen-descanso, #f-salario-base, #f-modalidad-trabajo, #f-regimen-trabajo-desde, #f-regimen-trabajo-hasta, #f-desde-hora, #f-hasta-hora, #f-frecuencia-trabajo').on('change input', function() {
    actualizarVistaPrevia();
  });

  // UX: abrir selector de hora al clickear cualquier parte del input
  function bindTimePicker(selector) {
    var $el = $(selector);
    if (!$el.length) return;
    function openPicker(input) {
      try {
        if (input && typeof input.showPicker === 'function') {
          input.showPicker();
          return true;
        }
      } catch (e) { /* ignore */ }
      try { input.focus(); input.click(); } catch(e) { /* ignore */ }
      return false;
    }
    // Abrir en click y en focus vía teclado
    $el.on('click', function(e){ openPicker(this); });
    $el.on('focus', function(e){
      // abrir automáticamente al enfocar con teclado/tab
      setTimeout(() => openPicker(this), 0);
    });
    // También permitir abrir con Enter o Space
    $el.on('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openPicker(this);
      }
    });
  }
  bindTimePicker('#f-desde-hora');
  bindTimePicker('#f-hasta-hora');

  // Función para actualizar la vista previa del contrato
  function actualizarVistaPrevia() {
    var tipoContrato = $('#f-tipo-contrato').val();
    var ubicacionLaboral = $('#f-ubicacion-laboral option:selected').text();
    var regimenDescanso = $('#f-regimen-descanso').val();
    var salarioBase = $('#f-salario-base').val();
    var modalidadTrabajo = $('#f-modalidad-trabajo option:selected').text();
    var regimenDesde = $('#f-regimen-trabajo-desde').val();
    var regimenHasta = $('#f-regimen-trabajo-hasta').val();
    var desdeHora = $('#f-desde-hora').val();
    var hastaHora = $('#f-hasta-hora').val();
    var frecuencia = $('#f-frecuencia-trabajo').val();

    if (!trabajadorData.nombre) {
      $('#contrato-preview').html('<p class="text-muted text-center">Seleccione un trabajador para ver la vista previa del contrato</p>');
      return;
    }

    var tipoTexto = tipoContrato == '1' ? 'Tiempo Determinado' : (tipoContrato == '2' ? 'Tiempo Indeterminado' : '');

    // Mostrar estado de carga
    $('#contrato-preview').html('<p class="text-center text-muted">Generando vista previa...</p>');

    // Construir payload para backend render-contrato-unico
    var payload = {
      module: 'contratos',
      method: 'render-contrato-unico',
      trabajador_nombre: trabajadorData.nombre || '',
      trabajador_apellidos: trabajadorData.apellidos || '',
      trabajador_apellidos_segundos: trabajadorData.apellidos_segundos || '',
      trabajador_direccion: trabajadorData.direccion || '',
      trabajador_municipio: trabajadorData.municipio_nombre || '',
      trabajador_provincia: trabajadorData.provincia_nombre || '',
      trabajador_ci: trabajadorData.carnet_identidad || '',
      cargo_nombre: trabajadorData.cargo_nombre || '',
      tipo_contrato_texto: tipoTexto || '',
      modalidad_trabajo_texto: modalidadTrabajo || '',
      ubicacion_laboral_texto: (ubicacionLaboral && ubicacionLaboral !== 'Seleccione departamento') ? ubicacionLaboral : '',
      regimen_descanso: regimenDescanso || '',
      salario_base: salarioBase || '',
      regimen_trabajo_desde: regimenDesde || '',
      regimen_trabajo_hasta: regimenHasta || '',
      hora_desde_h: desdeHora || '',
      hora_hasta_h: hastaHora || '',
      frecuencia_trabajo: frecuencia || ''
      // Horarios/otros opcionales pueden añadirse aquí si luego se agregan campos al formulario
    };

    $.ajax({
      url: 'api-app.php',
      method: 'POST',
      data: payload,
      dataType: 'json'
    }).done(function(d){
      if (d && d.status == 1 && d.html) {
        // Renderizar documento completo EXACTO dentro de un iframe usando srcdoc
        var iframe = document.createElement('iframe');
        iframe.setAttribute('style', 'width:100%;height:100%;border:0;');
        iframe.setAttribute('title', 'Vista previa del contrato');
        // Mantener el documento tal cual
        iframe.srcdoc = d.html;
        var $wrap = $('#contrato-preview');
        $wrap.empty().append(iframe);
      } else {
        // Fallback a la plantilla JS simple si el backend falla, dentro de un documento mínimo
        var nombreCompleto = (trabajadorData.nombre + ' ' + trabajadorData.apellidos + ' ' + (trabajadorData.apellidos_segundos || '')).trim();
        var contratoHtml = generarContratoHtml(nombreCompleto, tipoTexto, ubicacionLaboral, regimenDescanso, salarioBase, modalidadTrabajo);
        var fallbackDoc = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">\
<style>html,body{margin:0;padding:0;height:100%} body{padding:16px; box-sizing:border-box; font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif}</style></head><body>' + contratoHtml + '</body></html>';
        var iframe = document.createElement('iframe');
        iframe.setAttribute('style', 'width:100%;height:100%;border:0;');
        iframe.setAttribute('title', 'Vista previa del contrato');
        iframe.srcdoc = fallbackDoc;
        var $wrap = $('#contrato-preview');
        $wrap.empty().append(iframe);
      }
    }).fail(function(){
      var nombreCompleto = (trabajadorData.nombre + ' ' + trabajadorData.apellidos + ' ' + (trabajadorData.apellidos_segundos || '')).trim();
      var contratoHtml = generarContratoHtml(nombreCompleto, tipoTexto, ubicacionLaboral, regimenDescanso, salarioBase, modalidadTrabajo);
      var fallbackDoc = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">\
<style>html,body{margin:0;padding:0;height:100%} body{padding:16px; box-sizing:border-box; font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif}</style></head><body>' + contratoHtml + '</body></html>';
      var iframe = document.createElement('iframe');
      iframe.setAttribute('style', 'width:100%;height:100%;border:0;');
      iframe.setAttribute('title', 'Vista previa del contrato');
      iframe.srcdoc = fallbackDoc;
      var $wrap = $('#contrato-preview');
      $wrap.empty().append(iframe);
    });
  }

  // Función para generar el HTML del contrato
  function generarContratoHtml(nombreCompleto, tipoContrato, ubicacionLaboral, regimenDescanso, salarioBase, modalidadTrabajo) {
    var fechaActual = new Date().toLocaleDateString('es-ES');
    var desdeHora = $('#f-desde-hora').val() || '';
    var hastaHora = $('#f-hasta-hora').val() || '';
    var regimenDesde = $('#f-regimen-trabajo-desde').val() || '';
    var regimenHasta = $('#f-regimen-trabajo-hasta').val() || '';
    
    var frecuencia = $('#f-frecuencia-trabajo').val() || '';
    var freqTexto = frecuencia ? frecuencia.charAt(0).toUpperCase() + frecuencia.slice(1) : '';

    return `
      <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="margin-bottom: 10px;">SUPLEMENTO AL CONTRATO DE TRABAJO</h2>
        <h3 style="margin-bottom: 20px;">CONTRATO POR ${tipoContrato.toUpperCase()}</h3>
      </div>
      
      <div style="text-align: justify; line-height: 1.8;">
        <p><strong>PRIMERA:</strong> Que entre <strong>LA EMPRESA</strong> y <strong>${nombreCompleto}</strong>, 
        se establece el presente suplemento al contrato de trabajo bajo las siguientes condiciones:</p>
        
        <p><strong>SEGUNDA:</strong> El trabajador prestará sus servicios en la ubicación laboral de 
        <strong>${ubicacionLaboral || '[UBICACIÓN LABORAL]'}</strong>, bajo la modalidad de trabajo 
        <strong>${modalidadTrabajo || '[MODALIDAD DE TRABAJO]'}</strong>.</p>
        
        <p><strong>TERCERA:</strong> El régimen de descanso será: <strong>${regimenDescanso || '[RÉGIMEN DE DESCANSO]'}</strong>.</p>
        <p><strong>JORNADA:</strong> Desde <strong>${(regimenDesde||'[DÍA DESDE]')}</strong> a <strong>${(regimenHasta||'[DÍA HASTA]')}</strong>, en el horario de <strong>${(desdeHora||'[HORA DESDE]')}</strong> a <strong>${(hastaHora||'[HORA HASTA]')}</strong>.</p>
        
        <p><strong>CUARTA:</strong> El salario base acordado es de <strong>$${salarioBase || '0.00'}</strong> mensuales. Frecuencia: <strong>${freqTexto || '[FRECUENCIA]'}</strong>.</p>
        
        <p><strong>QUINTA:</strong> El presente contrato entra en vigor a partir del <strong>${fechaActual}</strong>.</p>
        
        <div style="margin-top: 50px;">
          <p><strong>Datos del Trabajador:</strong></p>
          <ul style="list-style: none; padding-left: 20px;">
            <li><strong>Nombre:</strong> ${trabajadorData.nombre || ''}</li>
            <li><strong>Apellidos:</strong> ${trabajadorData.apellidos || ''} ${trabajadorData.apellidos_segundos || ''}</li>
            <li><strong>Cargo:</strong> ${trabajadorData.cargo_nombre || 'Sin cargo'}</li>
            <li><strong>Dirección:</strong> ${trabajadorData.direccion || 'No especificada'}</li>
            <li><strong>Provincia:</strong> ${trabajadorData.provincia_nombre || 'No especificada'}</li>
            <li><strong>Municipio:</strong> ${trabajadorData.municipio_nombre || 'No especificado'}</li>
          </ul>
        </div>
        
        <div style="margin-top: 60px; display: flex; justify-content: space-between;">
          <div style="text-align: center;">
            <div style="border-top: 1px solid #000; width: 200px; margin-top: 50px;">
              <p style="margin-top: 5px;"><strong>Firma del Trabajador</strong></p>
            </div>
          </div>
          <div style="text-align: center;">
            <div style="border-top: 1px solid #000; width: 200px; margin-top: 50px;">
              <p style="margin-top: 5px;"><strong>Firma de la Empresa</strong></p>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Función para guardar contrato
  $('#btn-save').click(function(){
    var errores = [];
    var trabajadorId = $('#f-trabajador').val();
    var tipoContrato = $('#f-tipo-contrato').val();

    // Validaciones
    if (!trabajadorId) { errores.push('El trabajador es obligatorio'); $('#f-trabajador').addClass('is-invalid'); } else { $('#f-trabajador').removeClass('is-invalid'); }
    if (!tipoContrato) { errores.push('El tipo de contrato es obligatorio'); $('#f-tipo-contrato').addClass('is-invalid'); } else { $('#f-tipo-contrato').removeClass('is-invalid'); }
    // Los demás campos son opcionales para guardar. Se mantienen visibles para la vista previa y futura expansión.
    $('#f-ubicacion-laboral').removeClass('is-invalid');
    $('#f-regimen-descanso').removeClass('is-invalid');
    $('#f-salario-base').removeClass('is-invalid');
    $('#f-modalidad-trabajo').removeClass('is-invalid');

    if (errores.length) { 
      notify('warning','Validación',errores.join('<br>'),5000); 
      $('.is-invalid').first().focus(); 
      return; 
    }

    var fd = new FormData(document.getElementById('form-contrato'));
    fd.append('module', 'contratos');
    fd.append('method', 'save');

    $('#img-loading').removeClass('hidden');
    $('#btn-save').attr('disabled', true);

    $.ajax({ 
      url:'api-app.php', 
      type:'POST', 
      data: fd, 
      dataType:'json', 
      processData:false, 
      contentType:false,
      success: function(d){
        $('#img-loading').addClass('hidden');
        $('#btn-save').attr('disabled', false);
        if (d.status == 1) {
          if (!$('#f-id').val()) {
            $('#f-id').val(d.id);
            notify('success','¡Registro Exitoso!','El contrato ha sido registrado correctamente. ID: ' + (d.id||''),5000);
            window.history.replaceState({}, '', 'index.php?module=contratos&id=' + d.id);
          } else {
            notify('success','¡Actualización Exitosa!','El contrato ha sido actualizado correctamente',5000);
          }
        } else {
          notify('danger', d.msg_title || 'Error', d.msg || 'Error al guardar el contrato', 5000);
        }
      },
      error: function(xhr, status, error){
        $('#img-loading').addClass('hidden');
        $('#btn-save').attr('disabled', false);
        notify('danger', 'Error al Guardar', 'No se pudo conectar con el servidor', 5000);
      }
    });
  });

  // Botón "Ver grande" (antes Generar PDF) -> reutiliza la lógica de pantalla completa
  $('#btn-generate-pdf').off('click').on('click', function(){
    $('#btn-preview-fullscreen').trigger('click');
  });

  // Fallback: si el backend no cargó trabajadores en el HTML, poblar vía API
  (function cargarTrabajadoresFallback(){
    var $sel = $('#f-trabajador');
    if (!$sel.length) return;
    // si ya hay opciones además del placeholder, no hacer nada
    if ($sel.find('option').length > 1) return;
    function renderTrabajadores(list){
      if (!Array.isArray(list)) return false;
      var opts = ['<option value="">Seleccione trabajador</option>'];
      list.forEach(function(t){
        if (!t || !t.id) return;
        var full = ((t.nombre||'') + ' ' + (t.apellidos||'') + ' ' + (t.apellidos_segundos||'')).trim();
        if (!full) full = 'ID ' + t.id;
        opts.push('<option value="'+ t.id +'">'+ $('<div>').text(full).html() +'</option>');
      });
      $sel.html(opts.join(''));
      return true;
    }
    // 1) Intentar con nuestro propio módulo de contratos
    $.ajax({
      url: 'api-app.php',
      method: 'GET',
      data: { module: 'contratos', method: 'list-trabajadores' },
      dataType: 'json'
    }).done(function(list){
      if (renderTrabajadores(list)) return;
      // 2) Fallback a módulo trabajadores si existe
      $.ajax({
        url: 'api-app.php',
        method: 'GET',
        data: { module: 'trabajadores', method: 'list' },
        dataType: 'json'
      }).done(renderTrabajadores);
    }).fail(function(){
      // Si falla, intentar directamente módulo trabajadores
      $.ajax({
        url: 'api-app.php',
        method: 'GET',
        data: { module: 'trabajadores', method: 'list' },
        dataType: 'json'
      }).done(renderTrabajadores);
    });
  })();

  // Fallback: si el backend no cargó departamentos en el HTML, poblar vía API
  (function cargarDepartamentosFallback(){
    var $sel = $('#f-ubicacion-laboral');
    if (!$sel.length) return;
    if ($sel.find('option').length > 1) return; // ya trae opciones
    function renderDeps(list){
      if (!Array.isArray(list)) return false;
      var opts = ['<option value="">Seleccione departamento</option>'];
      list.forEach(function(d){
        if (!d || !d.id) return;
        var nombre = (d.nombre||'').trim();
        opts.push('<option value="'+ d.id +'">'+ $('<div>').text(nombre).html() +'</option>');
      });
      $sel.html(opts.join(''));
      return true;
    }
    // 1) Intentar con nuestro propio módulo de contratos
    $.ajax({
      url: 'api-app.php',
      method: 'GET',
      data: { module: 'contratos', method: 'list-departamentos' },
      dataType: 'json'
    }).done(function(list){
      if (renderDeps(list)) return;
    }).fail(function(){ /* silencioso */ });
  })();

  // Ver grande: abrir vista previa en nueva pestaña/ventana con barra de acciones
  $(document).on('click', '#btn-preview-fullscreen', function(){
    var $iframe = $('#contrato-preview iframe');
    if (!$iframe.length) { notify('warning','Vista previa','La vista previa no está lista aún'); return; }
    var innerHtml = $iframe[0].srcdoc || ($iframe[0].contentDocument ? '<!doctype html>' + $iframe[0].contentDocument.documentElement.outerHTML : '');
    if (!innerHtml) { notify('warning','Vista previa','La vista previa no está lista aún'); return; }

    var contratoId = $('#f-id').val() || '';
    var win = window.open('', '_blank');
    if (!win) { notify('warning','Popup bloqueado','Permite ventanas emergentes para ver a pantalla completa'); return; }

    var shell = `<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vista del Contrato</title>
  <style>
    html, body { height: 100%; margin: 0; }
    body { display: flex; flex-direction: column; }
    .toolbar { position: sticky; top: 0; z-index: 1000; display: flex; gap: 8px; align-items: center; padding: 10px; background: #f7f7f7; border-bottom: 1px solid #ddd; font-family: Arial, sans-serif; }
    .toolbar button { padding: 8px 12px; border: 1px solid #ccc; background: #fff; cursor: pointer; border-radius: 4px; }
    .toolbar button:hover { background: #f0f0f0; }
    .status { margin-left: auto; font-size: 12px; color: #555; }
    .content { position: relative; flex: 1; min-height: 0; }
    .content iframe { position: relative; z-index: 1; width: 100%; height: 100%; border: 0; }
    @media print { .toolbar { display: none !important; } }
    /* Modal firma */
    .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.5); }
    .modal .modal-dialog { background: #fff; max-width: 1000px; margin: 40px auto; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .modal-header, .modal-footer { padding: 10px 16px; border-bottom: 1px solid #eee; }
    .modal-header { display:flex; align-items:center; justify-content: space-between; }
    .modal-title { margin: 0; font-size: 16px; }
    .modal-body { padding: 16px; }
    .form-row { display:flex; gap: 12px; align-items:center; flex-wrap: wrap; margin-bottom: 10px; }
    .form-row label { font-size: 12px; color: #333; }
    .form-row input[type="number"] { width: 90px; }
    .btn { padding: 8px 12px; border: 1px solid #ccc; background: #fff; cursor: pointer; border-radius: 4px; }
    .btn.primary { background: #1976d2; color:#fff; border-color:#1976d2; }
    .btn.danger { background: #d32f2f; color:#fff; border-color:#d32f2f; }
    .btn:hover { opacity: .95; }
  </style>
</head>
<body>
  <div class="toolbar">
     <button id="btn-full-print">Imprimir</button>
    <button id="btn-full-sign">Firmar Trabajador</button>
    <button id="btn-full-sign-admin">Firmar Administrador</button>
    <span class="status" id="status-msg"></span>
  </div>
  <div class="content"><iframe id="docFrame"></iframe></div>
  <!-- Modal Firma -->
  <div id="signModal" class="modal" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-header">
        <h4 class="modal-title">Firmar documento</h4>
        <button id="closeModal" class="btn" title="Cerrar">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <label>Color: <input type="color" id="color" value="#000000" /></label>
          <label>Tamaño: <input type="range" id="size" min="1" max="15" value="3" /> <span id="sizeVal">3</span> px</label>
          <label>Padding: <input type="number" id="pad" min="0" max="60" value="6" /> <span id="padVal">6</span> px</label>
          <button id="clearBtn" class="btn danger" type="button">Limpiar</button>
        </div>
        <div style="border:1px dashed #bbb; background:#fafafa; border-radius:4px;">
          <canvas id="drawCanvas" style="display:block; width:100%; height:360px; touch-action:none;"></canvas>
        </div>
      </div>
      <div class="modal-footer" style="display:flex; gap:8px; justify-content:flex-end;">
        <button id="saveBtn" class="btn primary" type="button">Guardar firma en el documento</button>
      </div>
    </div>
  </div>
  <script>
    (function(){
      var contratoId = ${JSON.stringify(contratoId)};
      var currentSignTarget = 'trabajador';
      var iframe = document.getElementById('docFrame');
      var statusEl = document.getElementById('status-msg');
      // Cargar contenido del contrato en el iframe
      iframe.srcdoc = ${JSON.stringify(innerHtml)};

      function setStatus(msg){ if(statusEl){ statusEl.textContent = msg || ''; } }

      document.getElementById('btn-full-print').addEventListener('click', function(){
        try {
          var w = document.getElementById('docFrame').contentWindow;
          if (w && w.focus) w.focus();
          if (w && w.print) w.print();
          else window.print();
        } catch(e){ window.print(); }
      });

      var genBtn = document.getElementById('btn-full-generate');
      if (genBtn) genBtn.addEventListener('click', function(){
        if (!contratoId) { alert('Primero guarde el contrato para poder generar el PDF.'); return; }
        setStatus('Generando PDF...');
        var htmlActual = '';
        try {
          // Preferir DOM actual (incluye firmas añadidas)
          var w = document.getElementById('docFrame').contentWindow;
          if (w && w.document && w.document.documentElement) {
            htmlActual = '<!doctype html>' + w.document.documentElement.outerHTML;
          }
          if (!htmlActual) {
            htmlActual = document.getElementById('docFrame').srcdoc || '';
          }
        } catch(e) { htmlActual = document.getElementById('docFrame').srcdoc || ''; }

        if (!htmlActual) {
          setStatus('');
          alert('No se pudo obtener el HTML de la vista para generar el PDF.');
          return;
        }

        var params = new URLSearchParams();
        params.set('module', 'contratos');
        params.set('method', 'generatePdfFromHtml');
        params.set('tipo', 'contrato');
        params.set('contrato_id', contratoId);
        params.set('html', htmlActual);

        fetch('api-app.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: params
        }).then(function(r){ return r.json(); }).then(function(d){
          if (d && d.status == 1) {
            setStatus('PDF generado');
            if (d.file_url) { try { window.open(d.file_url, '_blank'); } catch(e){} }
          } else {
            setStatus('');
            alert((d && (d.msg_title||d.msg)) ? (d.msg_title || d.msg) : 'No se pudo generar el PDF');
          }
        }).catch(function(err){ setStatus(''); alert('Error al generar PDF'); });
      });

      // Abrir modal de firma - Trabajador
      document.getElementById('btn-full-sign').addEventListener('click', function(){
        currentSignTarget = 'trabajador';
        var modal = document.getElementById('signModal');
        if (!modal) { alert('No se encontró el modal de firma. Recargue la vista grande.'); return; }
        modal.style.display = 'block';
        // Inicializar canvas una vez visible (display:block) para que getBoundingClientRect no devuelva 0
        setTimeout(function(){
          try {
            var canvas = document.getElementById('drawCanvas');
            if (!canvas) return;
            // Asegurar tamaño visual
            if (!canvas.style.width) { canvas.style.width = '100%'; }
            if (!canvas.style.height) { canvas.style.height = '360px'; }
            var dpr = window.devicePixelRatio || 1;
            var rect = canvas.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
              // forzar un tamaño por defecto si el layout aún no calculó
              canvas.style.width = '800px';
              canvas.style.height = '360px';
              rect = canvas.getBoundingClientRect();
            }
            canvas.width = Math.round(rect.width * dpr);
            canvas.height = Math.round(rect.height * dpr);
            var ctx = canvas.getContext('2d', { alpha: true });
            if (ctx) { ctx.setTransform(1,0,0,1,0,0); ctx.scale(dpr, dpr); }
          } catch(e) { /* ignore */ }
        }, 0);
      });

      // Abrir modal de firma - Administrador
      document.getElementById('btn-full-sign-admin').addEventListener('click', function(){
        currentSignTarget = 'administrador';
        var modal = document.getElementById('signModal');
        if (!modal) { alert('No se encontró el modal de firma. Recargue la vista grande.'); return; }
        modal.style.display = 'block';
        setTimeout(function(){
          try {
            var canvas = document.getElementById('drawCanvas');
            if (!canvas) return;
            if (!canvas.style.width) { canvas.style.width = '100%'; }
            if (!canvas.style.height) { canvas.style.height = '360px'; }
            var dpr = window.devicePixelRatio || 1;
            var rect = canvas.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
              canvas.style.width = '800px';
              canvas.style.height = '360px';
              rect = canvas.getBoundingClientRect();
            }
            canvas.width = Math.round(rect.width * dpr);
            canvas.height = Math.round(rect.height * dpr);
            var ctx = canvas.getContext('2d', { alpha: true });
            if (ctx) { ctx.setTransform(1,0,0,1,0,0); ctx.scale(dpr, dpr); }
          } catch(e) { /* ignore */ }
        }, 0);
      });

      // Lógica de dibujo y guardado de firma
      (function(){
        const canvas = document.getElementById('drawCanvas');
        const ctx = canvas.getContext('2d', { alpha: true });
        const colorInput = document.getElementById('color');
        const sizeInput = document.getElementById('size');
        const sizeVal = document.getElementById('sizeVal');
        const saveBtn = document.getElementById('saveBtn');
        const clearBtn = document.getElementById('clearBtn');
        const padInput = document.getElementById('pad');
        const padVal = document.getElementById('padVal');
        const closeBtn = document.getElementById('closeModal');

        function fixDPI() {
          const dpr = window.devicePixelRatio || 1;
          const rect = canvas.getBoundingClientRect();
          canvas.width = Math.round(rect.width * dpr);
          canvas.height = Math.round(rect.height * dpr);
          canvas.style.width = rect.width + 'px';
          canvas.style.height = rect.height + 'px';
          ctx.setTransform(1,0,0,1,0,0);
          ctx.scale(dpr, dpr);
        }
        function setCanvasVisualSize() {
          // Nada: usamos width:100% en CSS; solo ajustar DPI
          fixDPI();
        }
        setCanvasVisualSize();
        window.addEventListener('resize', fixDPI);

        let drawing = false; let lastX=0, lastY=0;
        function applyStrokeSettings(){
          ctx.lineJoin = 'round';
          ctx.lineCap = 'round';
          ctx.lineWidth = parseInt(sizeInput.value,10);
          ctx.strokeStyle = colorInput.value;
        }
        applyStrokeSettings();
        sizeInput.addEventListener('input', ()=>{ sizeVal.textContent = sizeInput.value; applyStrokeSettings(); });
        colorInput.addEventListener('input', applyStrokeSettings);
        padInput.addEventListener('input', ()=>{ padVal.textContent = padInput.value; });

        function pointerPos(e){
          const rect = canvas.getBoundingClientRect();
          const clientX = e.clientX ?? (e.touches && e.touches[0] && e.touches[0].clientX);
          const clientY = e.clientY ?? (e.touches && e.touches[0] && e.touches[0].clientY);
          return { x: (clientX-rect.left), y: (clientY-rect.top) };
        }
        function startDraw(e){ e.preventDefault(); drawing=true; const p=pointerPos(e); lastX=p.x; lastY=p.y; ctx.beginPath(); ctx.moveTo(lastX,lastY); }
        function draw(e){ if(!drawing) return; e.preventDefault(); const p=pointerPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); lastX=p.x; lastY=p.y; }
        function endDraw(){ if(!drawing) return; drawing=false; ctx.closePath(); }

        canvas.addEventListener('pointerdown', (e)=>{ canvas.setPointerCapture(e.pointerId); applyStrokeSettings(); startDraw(e); });
        canvas.addEventListener('pointermove', draw);
        canvas.addEventListener('pointerup', (e)=>{ canvas.releasePointerCapture(e.pointerId); endDraw(); });
        canvas.addEventListener('pointercancel', endDraw);
        canvas.addEventListener('pointerout', endDraw);
        canvas.addEventListener('pointerleave', endDraw);
        canvas.addEventListener('touchstart', (e)=>{ e.preventDefault(); }, { passive:false });

        clearBtn.addEventListener('click', ()=>{
          ctx.setTransform(1,0,0,1,0,0);
          ctx.clearRect(0,0,canvas.width,canvas.height);
          fixDPI();
        });

        closeBtn.addEventListener('click', ()=>{
          document.getElementById('signModal').style.display = 'none';
        });

        // Guardar firma e insertar al final del documento del iframe
        saveBtn.addEventListener('click', ()=>{
          const wBuf = canvas.width, hBuf = canvas.height;
          if (!wBuf || !hBuf) { alert('Canvas vacío.'); return; }
          const imgData = ctx.getImageData(0,0,wBuf,hBuf).data;
          let minX=wBuf, minY=hBuf, maxX=0, maxY=0, found=false;
          for (let y=0; y<hBuf; y++){
            for (let x=0; x<wBuf; x++){
              const a = imgData[(y*wBuf+x)*4 + 3];
              if (a>0){ found=true; if(x<minX)minX=x; if(x>maxX)maxX=x; if(y<minY)minY=y; if(y>maxY)maxY=y; }
            }
          }
          if (!found){ alert('No hay trazo para guardar.'); return; }
          const dpr = window.devicePixelRatio || 1;
          const padCss = parseInt(padInput.value,10)||0;
          const pad = Math.round(padCss * dpr);
          const sx=Math.max(0,minX-pad), sy=Math.max(0,minY-pad);
          const sw=Math.min(wBuf - sx, (maxX-minX+1)+2*pad);
          const sh=Math.min(hBuf - sy, (maxY-minY+1)+2*pad);
          const out = document.createElement('canvas'); out.width=sw; out.height=sh; const octx = out.getContext('2d',{alpha:true});
          const tmp = new Image();
          tmp.onload = function(){
            octx.drawImage(tmp, sx, sy, sw, sh, 0, 0, sw, sh);
            // Usar data URL para que sea embebible y compatible con servidor (no blob:)
            const dataUrl = out.toDataURL('image/png');
            try {
              var ifw = document.getElementById('docFrame').contentWindow;
              var doc = ifw && ifw.document;
              if (!doc) throw new Error('No se pudo acceder al documento.');

              // Asegurar wrapper de firmas (lado a lado con espacio)
              var wrapper = doc.getElementById('firmas-wrapper');
              if (!wrapper) {
                wrapper = doc.createElement('div');
                wrapper.id = 'firmas-wrapper';
                wrapper.setAttribute('style','page-break-inside:avoid; display:flex; justify-content:center; gap:90px; margin-top:60px; padding:0 40px; align-items:flex-start;');
                (doc.body || doc.documentElement).appendChild(wrapper);
              } else {
                // asegurar que la posición se actualice si ya existía
                wrapper.setAttribute('style','page-break-inside:avoid; display:flex; justify-content:center; gap:90px; margin-top:60px; padding:0 40px; align-items:flex-start;');
              }

              var targetId = (currentSignTarget === 'administrador') ? 'firma-admin' : 'firma-trabajador';
              var targetTitle = (currentSignTarget === 'administrador') ? 'Firma Administrador' : 'Firma Trabajador';

              var container = doc.getElementById(targetId);
              if (!container) {
                container = doc.createElement('div');
                container.id = targetId;
                container.setAttribute('style','text-align:center;');
                var title = doc.createElement('div');
                title.className = 'firma-title';
                title.textContent = targetTitle;
                title.setAttribute('style','margin:0 0 4px 0; font-style:italic; color:#555;');
                var placeholder = doc.createElement('div');
                placeholder.setAttribute('style','border-top:1px solid #000; width:200px; height:0; margin: 46px auto 0 auto;');
                var img = doc.createElement('img');
                img.style.maxWidth = '120px'; img.style.height = 'auto'; img.style.display = 'block'; img.style.margin = '6px auto 0 auto';
                container.appendChild(title);
                container.appendChild(img);
                // container.appendChild(placeholder);
                wrapper.appendChild(container);
              }

              // Reemplazar/colocar la imagen en el contenedor destino
              var imgEl = container.querySelector('img');
              if (!imgEl) { imgEl = doc.createElement('img'); container.appendChild(imgEl); }
              imgEl.src = dataUrl; imgEl.alt = 'Firma'; imgEl.style.maxWidth = '120px'; imgEl.style.height = 'auto'; imgEl.style.display = 'block'; imgEl.style.margin = '6px auto 0 auto';
              // Asegurar título correcto
              var titleEl = container.querySelector('.firma-title');
              if (titleEl) { titleEl.textContent = targetTitle; titleEl.setAttribute('style','margin:0 0 4px 0; font-style:italic; color:#555;'); }
            
              // Actualizar srcdoc con DOM actual para conservar la firma en futuras acciones
              try { var htmlNow = '<!doctype html>'+ doc.documentElement.outerHTML; document.getElementById('docFrame').srcdoc = htmlNow; } catch(e){}
              document.getElementById('signModal').style.display = 'none';
              setStatus('Firma insertada');
            } catch(e){ alert('No se pudo insertar la firma en el documento.'); }
          };
          tmp.src = canvas.toDataURL('image/png');
        });
      })();
    })();
  </script>
</body>
</html>`;

    win.document.open();
    win.document.write(shell);
    win.document.close();
  });

});
