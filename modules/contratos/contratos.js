$(document).ready(function () {
  // Variables globales para datos del trabajador
  var trabajadorData = {};
  
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
    $('#trabajador-nombre').text(data.nombre || '-');
    $('#trabajador-apellidos').text(data.apellidos || '-');
    $('#trabajador-apellidos-segundos').text(data.apellidos_segundos || '-');
    $('#trabajador-cargo').text(data.cargo_nombre || '-');
    $('#trabajador-provincia').text(data.provincia_nombre || '-');
    $('#trabajador-municipio').text(data.municipio_nombre || '-');
    $('#trabajador-direccion').text(data.direccion || '-');
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

  // Event listeners para actualización en tiempo real
  $('#f-tipo-contrato, #f-ubicacion-laboral, #f-regimen-descanso, #f-salario-base, #f-modalidad-trabajo').on('change input', function() {
    actualizarVistaPrevia();
  });

  // Función para actualizar la vista previa del contrato
  function actualizarVistaPrevia() {
    var tipoContrato = $('#f-tipo-contrato').val();
    var ubicacionLaboral = $('#f-ubicacion-laboral option:selected').text();
    var regimenDescanso = $('#f-regimen-descanso').val();
    var salarioBase = $('#f-salario-base').val();
    var modalidadTrabajo = $('#f-modalidad-trabajo option:selected').text();
    
    if (!trabajadorData.nombre) {
      $('#contrato-preview').html('<p class="text-muted text-center">Seleccione un trabajador para ver la vista previa del contrato</p>');
      return;
    }

    var tipoTexto = tipoContrato == '1' ? 'Tiempo Determinado' : (tipoContrato == '2' ? 'Tiempo Indeterminado' : '');
    var nombreCompleto = (trabajadorData.nombre + ' ' + trabajadorData.apellidos + ' ' + (trabajadorData.apellidos_segundos || '')).trim();
    
    var contratoHtml = generarContratoHtml(nombreCompleto, tipoTexto, ubicacionLaboral, regimenDescanso, salarioBase, modalidadTrabajo);
    $('#contrato-preview').html(contratoHtml);
  }

  // Función para generar el HTML del contrato
  function generarContratoHtml(nombreCompleto, tipoContrato, ubicacionLaboral, regimenDescanso, salarioBase, modalidadTrabajo) {
    var fechaActual = new Date().toLocaleDateString('es-ES');
    
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
        
        <p><strong>CUARTA:</strong> El salario base acordado es de <strong>$${salarioBase || '0.00'}</strong> mensuales.</p>
        
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
    var departamentoId = $('#f-ubicacion-laboral').val();
    var regimenDescanso = $('#f-regimen-descanso').val();
    var salarioBase = $('#f-salario-base').val();
    var modalidadTrabajo = $('#f-modalidad-trabajo').val();

    // Validaciones
    if (!trabajadorId) { errores.push('El trabajador es obligatorio'); $('#f-trabajador').addClass('is-invalid'); } else { $('#f-trabajador').removeClass('is-invalid'); }
    if (!tipoContrato) { errores.push('El tipo de contrato es obligatorio'); $('#f-tipo-contrato').addClass('is-invalid'); } else { $('#f-tipo-contrato').removeClass('is-invalid'); }
    if (!departamentoId) { errores.push('La ubicación laboral es obligatoria'); $('#f-ubicacion-laboral').addClass('is-invalid'); } else { $('#f-ubicacion-laboral').removeClass('is-invalid'); }
    if (!regimenDescanso) { errores.push('El régimen de descanso es obligatorio'); $('#f-regimen-descanso').addClass('is-invalid'); } else { $('#f-regimen-descanso').removeClass('is-invalid'); }
    if (!salarioBase || parseFloat(salarioBase) <= 0) { errores.push('El salario base debe ser mayor a 0'); $('#f-salario-base').addClass('is-invalid'); } else { $('#f-salario-base').removeClass('is-invalid'); }
    if (!modalidadTrabajo) { errores.push('La modalidad de trabajo es obligatoria'); $('#f-modalidad-trabajo').addClass('is-invalid'); } else { $('#f-modalidad-trabajo').removeClass('is-invalid'); }

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

  // Función para generar PDF
  $('#btn-generate-pdf').click(function(){
    var contratoId = $('#f-id').val();
    if (!contratoId) {
      notify('warning','Validación','Debe guardar el contrato antes de generar el PDF');
      return;
    }

    var contratoHtml = $('#contrato-preview').html();
    if (!contratoHtml || contratoHtml.indexOf('Seleccione un trabajador') !== -1) {
      notify('warning','Validación','Complete todos los campos para generar el PDF');
      return;
    }

    $('#btn-generate-pdf').attr('disabled', true).text('Generando...');
    
    $.ajax({ 
      url: 'api-app.php', 
      method: 'POST', 
      data: { 
        module: 'contratos', 
        method: 'generatePdfFromHtml', 
        html: '<!doctype html><html><head><style>body{font-family:Arial,sans-serif;}</style></head><body>' + contratoHtml + '</body></html>',
        contrato_id: contratoId 
      }, 
      dataType: 'json',
      success: function(d){ 
        $('#btn-generate-pdf').attr('disabled', false).text('Generar PDF'); 
        if (d && d.status==1) { 
          notify('success','Generado','PDF creado correctamente'); 
          if (d.file_url) window.open(d.file_url,'_blank'); 
        } else { 
          notify('danger','Error', d.msg || 'No se pudo generar PDF'); 
        } 
      },
      error: function(){ 
        $('#btn-generate-pdf').attr('disabled', false).text('Generar PDF'); 
        notify('danger','Error','No se pudo generar PDF'); 
      }
    });
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

});
