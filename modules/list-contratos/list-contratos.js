$(document).ready(function(){
  // Helper de notificaciones
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
      var text = (title ? (title + ': ') : '') + (message || '');
      try { alert(text); } catch(e) { console.warn('Notify:', text); }
    }
  }

  $('#btn-add-new').click(function(){ location.href = 'index.php?module=contratos'; });

  // Abrir modal de contrato anterior
  $('#btn-add-anterior').click(function(){
    $('#form-contrato-anterior')[0].reset();
    $('.fecha-fin-group').hide();
    $('#modalContratoAnterior').modal('show');
  });

  // Mostrar/ocultar fecha fin según tipo de contrato
  $('#tipo_contrato').change(function(){
    if($(this).val() === '1') { // Contrato Determinado
      $('.fecha-fin-group').slideDown();
      $('#fecha_fin').prop('required', true);
    } else {
      $('.fecha-fin-group').slideUp();
      $('#fecha_fin').prop('required', false).val('');
    }
  });

  // Validar archivo al seleccionarlo
  $('#archivo_contrato').change(function(){
    var file = this.files[0];
    var fileType = file.type;
    var validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    if (!file) {
      return;
    }

    if (validTypes.indexOf(fileType) === -1) {
      notify('danger', 'Error', 'El archivo debe ser PDF o Word (doc/docx)', 4000);
      $(this).val('');
      return;
    }

    // Validar tamaño (máximo 10MB)
    if (file.size > 10 * 1024 * 1024) {
      notify('danger', 'Error', 'El archivo no debe superar los 10MB', 4000);
      $(this).val('');
      return;
    }
  });

  // Guardar contrato anterior
  $('#btn-guardar-contrato-anterior').click(function(){
    var $form = $('#form-contrato-anterior');
    var $submitBtn = $(this);

    // Validar formulario
    if (!$form[0].checkValidity()) {
      notify('warning', 'Formulario incompleto', 'Por favor complete todos los campos requeridos', 3000);
      return;
    }

    // Validaciones adicionales
    var fechaInicio = $('#fecha_inicio').val();
    var fechaFin = $('#fecha_fin').val();
    var tipoContrato = $('#tipo_contrato').val();

    if (tipoContrato === '1' && !fechaFin) { // Contrato determinado requiere fecha fin
      notify('warning', 'Formulario incompleto', 'Para contratos determinados debe especificar la fecha de fin', 3000);
      return;
    }

    if (fechaFin && new Date(fechaFin) <= new Date(fechaInicio)) {
      notify('warning', 'Error en fechas', 'La fecha de fin debe ser posterior a la fecha de inicio', 3000);
      return;
    }

    var formData = new FormData($form[0]);
    formData.append('module', 'contratos');
    formData.append('method', 'save-anterior');
    formData.append('firma_digital', '0'); // Por defecto, sin firma digital

    // Deshabilitar botón mientras se procesa
    $submitBtn.prop('disabled', true);

    $.ajax({
      url: 'api-app.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response){
        if (response.status === 1) {
          notify('success', '¡Éxito!', 'Contrato anterior guardado correctamente', 3000);
          $('#modalContratoAnterior').modal('hide');
          // Recargar tabla
          $('#table-panel').bootstrapTable('refresh');
        } else {
          notify('danger', 'Error', response.msg || 'Error al guardar el contrato', 4000);
        }
      },
      error: function(xhr, status, error){
        notify('danger', 'Error', 'Error al comunicarse con el servidor', 4000);
        console.error('Error:', error);
      },
      complete: function(){
        $submitBtn.prop('disabled', false);
      }
    });
  });

  // Inicializar tooltips si Bootstrap está disponible
  try { if ($.fn.tooltip) { $('[data-toggle="tooltip"]').tooltip(); } } catch(e) {}
  // Re-inicializar tooltips después de que la tabla renderiza
  $(document).on('post-body.bs.table', '#table-panel', function(){
    try { if ($.fn.tooltip) { $('[data-toggle="tooltip"]').tooltip(); } } catch(e) {}
  });
});

// Formatter para la columna Firmado
function firmadoFormatter(value, row, index) {
  var firmado = (value && String(value).trim() !== '' && value !== 'null' && value !== null);
  if (firmado) {
    return '<span class="label label-success">Firmado</span>';
  }
  return '<span class="label label-warning">No firmado</span>';
}

function tipoFormatter(value, row, index) {
  //si tipo es 1, mostrar "Contrato de Trabajo Por Tiempo Determinado";
  //si tipo es 2, mostrar "Contrato de Trabajo Por Tiempo Indeterminado";
  if (value === '4') {
    return 'Contrato de Servicios';
  } else if (value === '1') {
    return 'Contrato de Trabajo Por Tiempo Indeterminado';
  } else if (value === '2') {
    return 'Contrato de Trabajo Por Tiempo Determinado';
  } else if (value === '3') {
    return 'Suplemento de Contrato';
  }
}

// Formatter para la columna Opciones -> Ver PDF
function pdfFormatter(value, row, index) {
  var buttons = [];
  
  // Botón Editar
  // buttons.push('<button type="button" class="btn btn-primary btn-sm btn-edit-contract" data-id="' + row.id + '" data-toggle="tooltip" title="Editar Contrato" style="margin-right: 5px;"><i class="fa fa-edit"></i></button>');
  
  // Botón PDF
  var url = value || row.archivo_contrato;
  if (url && String(url).trim() !== '' && url !== 'null') {
    var filename = (function(u){ try { var p = u.split('?')[0]; var parts = p.split('/'); return parts[parts.length-1] || 'contrato.pdf'; } catch(e){ return 'contrato.pdf'; } })(url);
    buttons.push('<button type="button" class="btn btn-danger btn-sm btn-view-pdf" data-url="' + url + '" data-toggle="tooltip" title="' + filename + '"><i class="fa fa-file-pdf-o"></i></button>');
  } else {
    buttons.push('<button type="button" class="btn btn-default btn-sm" disabled data-toggle="tooltip" title="Sin archivo"><i class="fa fa-file-o"></i></button>');
  }
  
  return buttons.join('');
}

// Click en botón editar
$(document).on('click', '.btn-edit-contract', function(e){
  e.preventDefault();
  var id = $(this).data('id');
  window.location.href = 'index.php?module=contratos&id=' + id;
});

// Delegado: click en Ver PDF -> verificar existencia del archivo antes de abrir
$(document).on('click', '.btn-view-pdf', function(e){
  e.preventDefault();
  var url = $(this).data('url');
  var $btn = $(this);
  $btn.prop('disabled', true);
  $.ajax({
    url: url,
    type: 'HEAD',
    cache: false,
    success: function(){
      $btn.prop('disabled', false);
      window.open(url, '_blank');
    },
    error: function(){
      $btn.prop('disabled', false);
      try {
        if ($.niftyNoty) {
          $.niftyNoty({ type:'warning', container:'floating', title:'Archivo no encontrado', message:'El PDF no existe en la ruta especificada.', timer:4000, closeBtn:true });
        } else {
          alert('Archivo no encontrado: El PDF no existe en la ruta especificada.');
        }
      } catch(ex) { alert('Archivo no encontrado'); }
    }
  });
});
