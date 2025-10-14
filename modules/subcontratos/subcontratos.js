$(document).ready(function () {
  // Helper de notificaciones: usa Nifty Noty si está disponible, si no, fallback a alert
  function notify(type, title, message, timer) {
    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
        $.niftyNoty({
            type: type || 'info',
            container: 'floating',
            title: title || '',
            message: message || '',
            timer: timer != null ? timer : 3000,
            closeBtn: true,
            focus: true
        });
    } else {
        // Fallback simple para garantizar feedback al usuario
        var text = (title ? (title + ': ') : '') + (message || '');
        try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
}


    // UI alert helper (Bootstrap-like)
    function showAlert(type, title, message) {
        // type: 'success' | 'danger' | 'warning' | 'info'
        var $container = $('.panel-body').first();
        if ($container.length === 0) {
            $container = $('.panel').last();
        }
        if ($container.length === 0) {
            $container = $('body');
        }
        
        var html = '<div class="alert alert-' + type + ' alert-dismissible" role="alert" style="margin-bottom:12px; margin-top:12px;">'
                 + '  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                 + '  <strong>' + (title || '') + '</strong> ' + (message || '')
                 + '</div>';
        
        // remove previous alerts of same type to reduce clutter
        $container.find('.alert.alert-' + type).remove();
        
        // Insert after the first panel or at the beginning of container
        if ($container.hasClass('panel-body')) {
            $container.prepend(html);
        } else {
            $container.find('.panel').first().after(html);
            if ($container.find('.alert').length === 0) {
                $container.prepend(html);
            }
        }
        
        // auto dismiss after 6s
        setTimeout(function(){ 
            $container.find('.alert.alert-' + type).fadeOut(400, function(){ 
                $(this).remove(); 
            }); 
        }, 6000);
    }

    // Helper to escape HTML for safe insertion into hidden inputs
    function escapeHtml(str) {
        if (typeof str !== 'string') return str || '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // Función para limpiar el formulario
    function limpiarFormulario() {
        $('#form-subcontrato')[0].reset();
        $('#f-id').val('');
        $('#f-estatus').trigger('change'); // Actualizar validación de entidad
        $('#check-fecha-fin').prop('checked', false).trigger('change'); // Desactivar fecha fin
        // Actualizar la URL para nuevo registro
        window.history.replaceState({}, '', 'index.php?module=subcontratos');
    }

    // Control del checkbox para fecha de fin
    $('#check-fecha-fin').on('change', function() {
        if ($(this).is(':checked')) {
            $('#f-fecha-fin').prop('disabled', false);
            $('#f-fecha-fin').closest('.form-group').find('.help-block').text('Seleccione la fecha de finalización del contrato');
        } else {
            $('#f-fecha-fin').prop('disabled', true).val('');
            $('#f-fecha-fin').removeClass('is-invalid');
            $('#f-fecha-fin').closest('.form-group').find('.help-block').text('Marque la casilla superior para activar este campo');
        }
    });

    // Mejorar funcionalidad de campos de fecha
    $('#f-fecha-inicio').on('click focus', function() {
        $(this)[0].showPicker();
    });
    
    $('#f-fecha-fin').on('click focus', function() {
        if (!$(this).prop('disabled')) {
            $(this)[0].showPicker();
        }
    });

    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-subcontratos';
    });

    $('#btn-new').click(function () {
        location.href = 'index.php?module=subcontratos';
    });

    $('#btn-save').click(function () {
        // Validaciones mejoradas con mensajes específicos
        var errores = [];
        
        if ($('#f-nombre').val().trim() == '') {
            errores.push('El nombre de la persona es obligatorio');
            $('#f-nombre').addClass('is-invalid');
        } else {
            $('#f-nombre').removeClass('is-invalid');
        }

        // Validación de CI (Carnet de Identidad)
        var ci = ($('#f-ci').val() || '').trim();
        var ciRegex = /^\d{11}$/;
        if (ci === '') {
            errores.push('El CI (Carnet de Identidad) es obligatorio');
            $('#f-ci').addClass('is-invalid');
        } else if (!ciRegex.test(ci)) {
            errores.push('El CI debe contener exactamente 11 dígitos numéricos');
            $('#f-ci').addClass('is-invalid');
        } else {
            $('#f-ci').removeClass('is-invalid');
        }
        
        if ($('#f-estatus').val() == '') {
            errores.push('El estatus es obligatorio');
            $('#f-estatus').addClass('is-invalid');
        } else {
            $('#f-estatus').removeClass('is-invalid');
        }
        
        if ($('#f-servicio').val().trim() == '') {
            errores.push('El servicio u objeto de contrato es obligatorio');
            $('#f-servicio').addClass('is-invalid');
        } else {
            $('#f-servicio').removeClass('is-invalid');
        }
        
        if ($('#f-fecha-inicio').val() == '') {
            errores.push('La fecha de inicio es obligatoria');
            $('#f-fecha-inicio').addClass('is-invalid');
        } else {
            $('#f-fecha-inicio').removeClass('is-invalid');
        }

        // Validación adicional: fecha de fin no puede ser anterior a fecha de inicio
        if ($('#check-fecha-fin').is(':checked')) {
            if ($('#f-fecha-fin').val() == '') {
                errores.push('Debe seleccionar una fecha de fin o desmarcar la casilla');
                $('#f-fecha-fin').addClass('is-invalid');
            } else if ($('#f-fecha-inicio').val() && $('#f-fecha-fin').val()) {
                var fechaInicio = new Date($('#f-fecha-inicio').val());
                var fechaFin = new Date($('#f-fecha-fin').val());
                if (fechaFin < fechaInicio) {
                    errores.push('La fecha de fin no puede ser anterior a la fecha de inicio');
                    $('#f-fecha-fin').addClass('is-invalid');
                } else {
                    $('#f-fecha-fin').removeClass('is-invalid');
                }
            } else {
                $('#f-fecha-fin').removeClass('is-invalid');
            }
        }

        if (errores.length > 0) {
            notify('warning', 'Validación', errores.join('<br>'), 5000);
            // Enfocar el primer campo con error
            $('.is-invalid').first().focus();
            return;
        }

        var cmd = $('#form-subcontrato').serialize() + '&module=subcontratos&method=save';
        
        // Mostrar spinner y deshabilitar botón
        $('#img-loading').removeClass('hidden');
        $('#btn-save').attr('disabled', true);
        
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: cmd,
            dataType: 'json',
            success: function (d) {
                $('#img-loading').addClass('hidden');
                $('#btn-save').attr('disabled', false);
                
                console.log('Respuesta del servidor:', d); // Debug temporal
                if (d.status == 1) {
                    // Mensaje de éxito mejorado
                    if ($('#f-id').val() == '') {
                        // Nuevo registro
                        $('#f-id').val(d.id);
                        notify('success', '¡Registro Exitoso!', 'El subcontrato ha sido registrado correctamente en el sistema. ID: ' + d.id, 5000);
                        showAlert('success', '¡Registro Exitoso!', 'El subcontrato ha sido registrado correctamente. ID: ' + d.id);
                        
                        // Actualizar URL para edición
                        window.history.replaceState({}, '', 'index.php?module=subcontratos&id=' + d.id);
                    } else {
                        // Actualización
                        notify('success', '¡Actualización Exitosa!', 'Los datos del subcontrato han sido actualizados correctamente', 5000);
                        showAlert('success', '¡Actualización Exitosa!', 'Los datos del subcontrato han sido actualizados correctamente.');
                    }
                } else {
                    console.log('Error del servidor:', d.msg); // Debug temporal
                    notify('danger', 'Error al Guardar', d.msg || 'Error al guardar el subcontrato', 5000);
                    showAlert('danger', 'Error al Guardar:', d.msg || 'Error al guardar el subcontrato');
                }
            },
            error: function(xhr, status, error) {
                $('#img-loading').addClass('hidden');
                $('#btn-save').attr('disabled', false);

                var responseText = xhr && xhr.responseText ? xhr.responseText.trim() : '';
                var title = 'Error al Guardar';
                var message = 'No se pudo conectar con el servidor';
                // Intentar parsear JSON válido si existe
                try {
                    if (responseText && responseText.charAt(0) === '{') {
                        var jd = JSON.parse(responseText);
                        if (typeof jd === 'object') {
                            if (jd.msg_title) title = jd.msg_title;
                            if (jd.msg) message = jd.msg;
                        }
                    } else if (error) {
                        message = error;
                    }
                } catch (e) {
                    // Si no es JSON válido, mantener mensaje genérico
                    if (error) message = error;
                }

                notify('danger', title, message, 5000);
                showAlert('danger', title, message);
            }
        });
    });


    // Validación en tiempo real del estatus
    $('#f-estatus').change(function() {
        var estatus = $(this).val();
        if (estatus === 'TCP') {
            $('#f-entidad').prop('disabled', true).val('');
            $('#f-entidad').closest('.form-group').find('label').html('Entidad Representada <small class="text-muted">(No aplica para TCP)</small>');
        } else {
            $('#f-entidad').prop('disabled', false);
            $('#f-entidad').closest('.form-group').find('label').html('Entidad Representada');
        }
    });

    // Validación en tiempo real para campos obligatorios
    $('#f-nombre, #f-servicio, #f-ci').on('blur', function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Filtro de entrada para CI: solo dígitos y máx 11
    $('#f-ci').on('input', function() {
        var val = $(this).val().replace(/[^\d]/g, '').slice(0, 11);
        $(this).val(val);
        if (val.length === 11) {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-estatus').on('change', function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#f-fecha-inicio').on('change', function() {
        if ($(this).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de fechas en tiempo real
    $('#f-fecha-fin').on('change', function() {
        if ($('#check-fecha-fin').is(':checked')) {
            if ($(this).val() === '') {
                $(this).addClass('is-invalid');
            } else if ($('#f-fecha-inicio').val() && $(this).val()) {
                var fechaInicio = new Date($('#f-fecha-inicio').val());
                var fechaFin = new Date($(this).val());
                if (fechaFin < fechaInicio) {
                    $(this).addClass('is-invalid');
                    notify('warning', 'Fecha Inválida', 'La fecha de fin no puede ser anterior a la fecha de inicio', 3000);
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    // Ejecutar validación inicial si hay datos cargados
    if ($('#f-estatus').val() !== '') {
        $('#f-estatus').trigger('change');
    }
    
    // Inicializar estado del checkbox de fecha fin
    $('#check-fecha-fin').trigger('change');

    // Agregar estilos CSS para campos inválidos si no existen
    if (!$('#validation-styles').length) {
        $('<style id="validation-styles">')
            .text('.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }')
            .appendTo('head');
    }

    // ===== Vista previa en tiempo real (idéntica en UX al módulo contratos) =====
    // Carga plantilla HTML y llena inputs por índice con los datos disponibles
    function loadTemplateHtml(cb) {
      $.ajax({ url: 'modules/subcontratos/Subcrontratos_Servicios.html', method: 'GET', dataType: 'html' })
        .done(function(html){ cb(null, html); })
        .fail(function(){ cb(new Error('No se pudo cargar la plantilla HTML')); });
    }

    function fillTemplateInIframe(iframe, datos){
      try {
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        var inputs = doc.querySelectorAll('input, textarea');
        if (!inputs || !inputs.length) return;
        // Mapear posiciones conocidas según plantilla actual:
        // 0: No. contrato (título)
        // 1: Nombre persona
        // 2: CI
        // 3: Dirección (domicilio legal)
        // 4: Municipio
        // 5: Provincia
        // ... (otros inputs del primer párrafo)
        // 9: Día firma
        // 10: Mes firma (texto)
        // 11: Año (2 dígitos)
        var numero = ($('#f-id').val()||'').trim();
        var nombre = (datos.persona_nombre||'').trim();
        var ci = (datos.carnet_identidad||'').trim();
        var dir = (datos.direccion||'').trim();
        var muniNombre = (datos.municipio_nombre||'').trim();
        var provNombre = (datos.provincia_nombre||'').trim();
        var d=''; var m=''; var y2='';
        if (datos.fecha_inicio) {
          var dt = new Date(datos.fecha_inicio);
          if (!isNaN(dt.getTime())){
            d = String(dt.getDate());
            var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            m = meses[dt.getMonth()] || '';
            y2 = String(dt.getFullYear()).slice(-2);
          }
        }
        var map = {
          0: numero,
          1: nombre,
          2: ci,
          3: dir,
          4: muniNombre,
          5: provNombre,
          9: d,
          10: m,
          11: y2
        };
        Object.keys(map).forEach(function(k){
          var idx = parseInt(k,10);
          if (inputs[idx]) { inputs[idx].value = map[k]; inputs[idx].setAttribute('value', map[k]); }
        });
        // Servicio/Objeto podría insertarse sustituyendo primer textarea si existiera
        for (var i=0;i<inputs.length;i++){
          if (inputs[i].tagName.toLowerCase()==='textarea'){
            var txt = (datos.servicio_objeto||'');
            inputs[i].value = txt; inputs[i].textContent = txt; break;
          }
        }
      } catch(e){ /* noop */ }
    }

    function actualizarVistaPreviaSubcontrato(){
      var datos = {
        persona_nombre: ($('#f-nombre').val()||'').trim(),
        carnet_identidad: ($('#f-ci').val()||'').trim(),
        estatus: $('#f-estatus').val()||'',
        entidad_representada: ($('#f-entidad').val()||'').trim(),
        servicio_objeto: ($('#f-servicio').val()||''),
        fecha_inicio: $('#f-fecha-inicio').val()||'',
        fecha_fin: $('#check-fecha-fin').is(':checked') ? ($('#f-fecha-fin').val()||'') : '',
        areas_acceso: ($('#f-areas').val()||'').trim(),
        direccion: ($('#f-direccion').val()||'').trim(),
        provincia_id: $('#f-provincia').val()||'',
        municipio_id: $('#f-municipio').val()||'',
        provincia_nombre: ($('#f-provincia option:selected').text()||'').replace('Seleccione provincia','').trim(),
        municipio_nombre: ($('#f-municipio option:selected').text()||'').replace('Seleccione municipio','').trim()
      };

      var $wrap = $('#subcontrato-preview');
      if (!$wrap.length) return;
      loadTemplateHtml(function(err, html){
        if (err) { $wrap.html('<p class="text-center text-muted">No se pudo cargar la plantilla de vista previa</p>'); return; }
        var iframe = document.createElement('iframe');
        iframe.setAttribute('style','width:100%;height:100%;border:0;');
        iframe.setAttribute('title','Vista previa del subcontrato');
        // Insertar y luego llenar una vez cargado el documento
        iframe.onload = function(){ fillTemplateInIframe(iframe, datos); };
        iframe.srcdoc = html;
        $wrap.empty().append(iframe);
      });
    }

    // Eventos para actualizar la vista previa
    $('#f-nombre, #f-ci, #f-estatus, #f-entidad, #f-servicio, #f-fecha-inicio, #f-fecha-fin, #f-areas, #f-direccion, #f-provincia, #f-municipio').on('input change', function(){
      actualizarVistaPreviaSubcontrato();
    });
    $('#check-fecha-fin').on('change', function(){ actualizarVistaPreviaSubcontrato(); });

    // Inicializar vista previa al cargar
    actualizarVistaPreviaSubcontrato();

    // Ver grande: abrir vista previa en nueva pestaña/ventana con opción de imprimir (similar a contratos)
    $(document).on('click', '#btn-preview-fullscreen-subcontrato', function(){
      var datos = {
        persona_nombre: ($('#f-nombre').val()||'').trim(),
        carnet_identidad: ($('#f-ci').val()||'').trim(),
        estatus: $('#f-estatus').val()||'',
        entidad_representada: ($('#f-entidad').val()||'').trim(),
        servicio_objeto: ($('#f-servicio').val()||''),
        fecha_inicio: $('#f-fecha-inicio').val()||'',
        fecha_fin: $('#check-fecha-fin').is(':checked') ? ($('#f-fecha-fin').val()||'') : '',
        areas_acceso: ($('#f-areas').val()||'').trim(),
        direccion: ($('#f-direccion').val()||'').trim(),
        provincia_id: $('#f-provincia').val()||'',
        municipio_id: $('#f-municipio').val()||'',
        provincia_nombre: ($('#f-provincia option:selected').text()||'').replace('Seleccione provincia','').trim(),
        municipio_nombre: ($('#f-municipio option:selected').text()||'').replace('Seleccione municipio','').trim()
      };
      var win = window.open('', '_blank');
      if (!win) { notify('warning','Popup bloqueado','Permite ventanas emergentes para ver a pantalla completa'); return; }
      // Cargar plantilla nuevamente y rellenar en la nueva ventana
      loadTemplateHtml(function(err, templateHtml){
        if (err) { notify('danger','Vista previa','No se pudo cargar la plantilla'); try{ win.close(); }catch(e){} return; }
        var datosJson = JSON.stringify(datos);
        var shell = `<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vista del Subcontrato</title>
  <style>
    html, body { height: 100%; margin: 0; }
    body { display: flex; flex-direction: column; }
    .toolbar { position: sticky; top: 0; z-index: 1000; display: flex; gap: 8px; align-items: center; padding: 10px; background: #f7f7f7; border-bottom: 1px solid #ddd; font-family: Arial, sans-serif; }
    .toolbar button { padding: 8px 12px; border: 1px solid #ccc; background: #fff; cursor: pointer; border-radius: 4px; }
    .toolbar button:hover { background: #f0f0f0; }
    .content { position: relative; flex: 1; min-height: 0; }
    .content iframe { position: relative; z-index: 1; width: 100%; height: 100%; border: 0; }
    @media print { .toolbar { display: none !important; } }
  </style>
  </head>
  <body>
    <div class="toolbar">
      <button id="btn-print">Imprimir</button>
    </div>
    <div class="content"><iframe id="docFrame"></iframe></div>
    <script>
      (function(){
        var iframe = document.getElementById('docFrame');
        var templateHtml = ${JSON.stringify(templateHtml)};
        var datos = ${datosJson};
        function fill(){
          try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            var inputs = doc.querySelectorAll('input, textarea');
            var numero = '';
            try { numero = (window.opener && window.opener.document && window.opener.document.getElementById('f-id')) ? window.opener.document.getElementById('f-id').value : ''; } catch(e){}
            var d='',m='',y2='';
            if (datos.fecha_inicio){ var dt=new Date(datos.fecha_inicio); if(!isNaN(dt.getTime())){ d=String(dt.getDate()); m=['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][dt.getMonth()]||''; y2=String(dt.getFullYear()).slice(-2);} }
            var map = {0: numero, 1: (datos.persona_nombre||''), 2: (datos.carnet_identidad||''), 3: (datos.direccion||''), 4: (datos.municipio_nombre||''), 5: (datos.provincia_nombre||''), 9: d, 10: m, 11: y2};
            Object.keys(map).forEach(function(k){ var idx=parseInt(k,10); if(inputs[idx]){ inputs[idx].value = map[k]; inputs[idx].setAttribute('value', map[k]); }});
            for (var i=0;i<inputs.length;i++){ if (inputs[i].tagName.toLowerCase()==='textarea'){ var txt=(datos.servicio_objeto||''); inputs[i].value = txt; inputs[i].textContent = txt; break; } }
          } catch(e){}
        }
        iframe.onload = fill;
        iframe.srcdoc = templateHtml;
        document.getElementById('btn-print').addEventListener('click', function(){
          try { var w = iframe.contentWindow; if (w && w.focus) w.focus(); if (w && w.print) w.print(); else window.print(); }
          catch(e){ window.print(); }
        });
      })();
    </script>
  </body>
  </html>`;
        win.document.open();
        win.document.write(shell);
        win.document.close();
      });
    });

    // ===== Carga de Provincias/Municipios desde BD (igual que trabajadores) =====
    function cargarProvincias(){
      $.ajax({ url: 'api-app.php', type: 'POST', dataType: 'json', data: { module: 'subcontratos', method: 'list-provincias' } })
        .done(function(list){
          var $prov = $('#f-provincia');
          if (!$prov.length) return;
          var opts = ['<option value="">Seleccione provincia</option>'];
          (list||[]).forEach(function(p){ if (p && p.id) { opts.push('<option value="'+p.id+'">'+ $('<div>').text(p.nombre||'').html() +'</option>'); } });
          $prov.html(opts.join(''));
          var currentProv = $prov.attr('data-current') || $prov.val();
          if (currentProv) { $prov.val(String(currentProv)); cargarMunicipios(currentProv); }
          if (!list || !list.length) { console.warn('Provincias: lista vacía'); notify('warning','Provincias','No hay provincias disponibles'); }
        })
        .fail(function(xhr){
          console.error('Error cargando provincias:', xhr && xhr.responseText);
          // Fallback a GET por compatibilidad
          $.ajax({ url: 'api-app.php', type: 'GET', dataType: 'json', data: { module: 'subcontratos', method: 'list-provincias' } })
            .done(function(list){
              var $prov = $('#f-provincia');
              if (!$prov.length) return;
              var opts = ['<option value="">Seleccione provincia</option>'];
              (list||[]).forEach(function(p){ if (p && p.id) { opts.push('<option value="'+p.id+'">'+ $('<div>').text(p.nombre||'').html() +'</option>'); } });
              $prov.html(opts.join(''));
              var currentProv = $prov.attr('data-current') || $prov.val();
              if (currentProv) { $prov.val(String(currentProv)); cargarMunicipios(currentProv); }
              if (!list || !list.length) { console.warn('Provincias (GET): lista vacía'); notify('warning','Provincias','No hay provincias disponibles'); }
            });
        });
    }
    function cargarMunicipios(provId){
      var $muni = $('#f-municipio');
      if (!$muni.length) return;
      if (!provId){ $muni.prop('disabled', true).html('<option value="">Seleccione municipio</option>'); return; }
      $.ajax({ url: 'api-app.php', type: 'POST', dataType: 'json', data: { module: 'subcontratos', method: 'get-municipios', provincia_id: provId } })
        .done(function(list){
          var opts = ['<option value="">Seleccione municipio</option>'];
          (list||[]).forEach(function(m){ if (m && m.id) { opts.push('<option value="'+m.id+'">'+ $('<div>').text(m.nombre||'').html() +'</option>'); } });
          $muni.prop('disabled', false).html(opts.join(''));
          var currentMuni = $muni.attr('data-current') || $muni.val();
          if (currentMuni) { $muni.val(String(currentMuni)); }
          if (!list || !list.length) { console.warn('Municipios: lista vacía para provincia', provId); notify('warning','Municipios','No hay municipios para la provincia seleccionada'); }
        })
        .fail(function(xhr){
          console.error('Error cargando municipios:', xhr && xhr.responseText);
          // Fallback a GET por compatibilidad
          $.ajax({ url: 'api-app.php', type: 'GET', dataType: 'json', data: { module: 'subcontratos', method: 'get-municipios', provincia_id: provId } })
            .done(function(list){
              var opts = ['<option value=\"\">Seleccione municipio</option>'];
              (list||[]).forEach(function(m){ if (m && m.id) { opts.push('<option value=\"'+m.id+'\">'+ $('<div>').text(m.nombre||'').html() +'</option>'); } });
              $muni.prop('disabled', false).html(opts.join(''));
              var currentMuni = $muni.attr('data-current') || $muni.val();
              if (currentMuni) { $muni.val(String(currentMuni)); }
              if (!list || !list.length) { console.warn('Municipios (GET): lista vacía para provincia', provId); notify('warning','Municipios','No hay municipios para la provincia seleccionada'); }
            });
        });
    }
    // Enlazar cambios
    $(document).on('change', '#f-provincia', function(){ cargarMunicipios($(this).val()); actualizarVistaPreviaSubcontrato(); });
    $(document).on('change', '#f-municipio', function(){ actualizarVistaPreviaSubcontrato(); });
    // Inicial (leer posibles valores precargados desde el backend)
    var $provInit = $('#f-provincia');
    var $muniInit = $('#f-municipio');
    if ($provInit.length && $provInit.val()) { $provInit.attr('data-current', $provInit.val()); }
    if ($muniInit.length && $muniInit.val()) { $muniInit.attr('data-current', $muniInit.val()); }
    cargarProvincias();

    // ===== Módulo de Firmas (Subcontratista y Contratista) =====
    // UI: inyectar botones cerca del área de vista previa si no existen
    (function ensureSignatureButtons(){
      var $wrap = $('#subcontrato-preview');
      if (!$wrap.length) return;
      if ($('#btn-firmar-subcontratista').length && $('#btn-firmar-contratista').length) return;
      var $bar = $('<div class="signature-actions" style="display:flex; gap:8px; justify-content:flex-end; margin:8px 0 12px 0;"></div>');
      var $btnSub = $('<button type="button" id="btn-firmar-subcontratista" class="btn btn-info"><i class="fa fa-pencil"></i> Firmar Subcontratista</button>');
      var $btnCon = $('<button type="button" id="btn-firmar-contratista" class="btn btn-primary"><i class="fa fa-pencil"></i> Firmar Contratista</button>');
      $bar.append($btnSub, $btnCon);
      $wrap.before($bar);
    })();

    // Modal de firma con canvas (inyectado 1 vez)
    (function ensureSignatureModal(){
      if (document.getElementById('firma-modal')) return;
      var html = '\
<div id="firma-modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:none; align-items:center; justify-content:center; z-index:9999;">\
  <div style="background:#fff; width:520px; max-width:95vw; border-radius:8px; box-shadow:0 6px 24px rgba(0,0,0,.2); overflow:hidden;">\
    <div style="padding:10px 14px; border-bottom:1px solid #e5e5e5; display:flex; justify-content:space-between; align-items:center;">\
      <strong id="firma-modal-title">Firmar</strong>\
      <button id="firma-modal-close" style="border:none; background:transparent; font-size:20px; line-height:1;">×</button>\
    </div>\
    <div style="padding:12px 14px;">\
      <div style="border:1px solid #ccc; border-radius:6px; overflow:hidden; background:#fafafa;">\
        <canvas id="firma-canvas" width="480" height="200" style="display:block; width:100%; height:200px; background:#fff; cursor:crosshair;"></canvas>\
      </div>\
      <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:10px;">\
        <button id="firma-clear" class="btn btn-default">Limpiar</button>\
        <button id="firma-save" class="btn btn-success">Guardar firma</button>\
      </div>\
    </div>\
  </div>\
</div>';
      $('body').append(html);
    })();

    var currentSignTarget = null; // 'subcontratista' | 'contratista'
    function openFirmaModal(target){
      currentSignTarget = target;
      $('#firma-modal-title').text(target === 'subcontratista' ? 'Firmar Subcontratista' : 'Firmar Contratista');
      $('#firma-modal').css('display','flex');
      // reset canvas
      var canvas = document.getElementById('firma-canvas');
      var ctx = canvas.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0,0,canvas.width,canvas.height);
      ctx.strokeStyle = '#000'; ctx.lineWidth = 2; ctx.lineCap = 'round';
    }
    function closeFirmaModal(){ $('#firma-modal').hide(); }

    // Dibujar firma en canvas
    (function bindCanvasDrawing(){
      var canvas = document.getElementById('firma-canvas');
      if (!canvas) return;
      var ctx = canvas.getContext('2d');
      var drawing = false, last = null;
      function pos(e){
        var r = canvas.getBoundingClientRect();
        var x = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
        var y = (e.touches ? e.touches[0].clientY : e.clientY) - r.top;
        return {x:x * (canvas.width/r.width), y:y * (canvas.height/r.height)};
      }
      function start(e){ drawing = true; last = pos(e); e.preventDefault(); }
      function move(e){ if (!drawing) return; var p = pos(e); ctx.beginPath(); ctx.moveTo(last.x,last.y); ctx.lineTo(p.x,p.y); ctx.stroke(); last=p; e.preventDefault(); }
      function end(e){ drawing = false; e.preventDefault(); }
      canvas.addEventListener('mousedown', start); canvas.addEventListener('mousemove', move); window.addEventListener('mouseup', end);
      canvas.addEventListener('touchstart', start, {passive:false}); canvas.addEventListener('touchmove', move, {passive:false}); canvas.addEventListener('touchend', end, {passive:false});
    })();

    // Botones modal
    $(document).on('click', '#firma-modal-close', closeFirmaModal);
    $(document).on('click', '#firma-clear', function(){
      var canvas = document.getElementById('firma-canvas');
      var ctx = canvas.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0,0,canvas.width,canvas.height);
    });
    $(document).on('click', '#firma-save', function(){
      try {
        var canvas = document.getElementById('firma-canvas');
        var dataUrl = canvas.toDataURL('image/png');
        var $wrap = $('#subcontrato-preview'); if (!$wrap.length) { closeFirmaModal(); return; }
        var iframe = $wrap.find('iframe')[0]; if (!iframe) { closeFirmaModal(); return; }
        var doc = iframe.contentDocument || iframe.contentWindow.document;

        // Ubicar bloque de firma en la plantilla: son los dos .sig-block dentro de .signature
        var blocks = doc.querySelectorAll('.signature .sig-block');
        var block = null;
        if (currentSignTarget === 'subcontratista') block = blocks && blocks[0];
        else if (currentSignTarget === 'contratista') block = blocks && blocks[1];
        if (!block) { notify('warning','Firmas','No se encontró el bloque de firma en el documento'); closeFirmaModal(); return; }

        // Crear/actualizar imagen de firma
        var img = block.querySelector('img.firma-img');
        if (!img) {
          img = doc.createElement('img');
          img.className = 'firma-img';
          img.setAttribute('style','max-width:160px; height:auto; display:block; margin:6px auto 6px auto;');
          // Insertar imagen antes del texto del bloque (encima de la línea)
          block.insertBefore(img, block.firstChild);
        }
        img.src = dataUrl; img.alt = 'Firma';

        // Persistir cambios del iframe (mantener firma tras nuevas actualizaciones)
        try {
          var html = doc.documentElement.outerHTML;
          iframe.srcdoc = html;
        } catch(err) {}

        closeFirmaModal();
      } catch(e) { closeFirmaModal(); }
    });

    // Handlers de botones
    $(document).on('click', '#btn-firmar-subcontratista', function(){ openFirmaModal('subcontratista'); });
    $(document).on('click', '#btn-firmar-contratista', function(){ openFirmaModal('contratista'); });
});
