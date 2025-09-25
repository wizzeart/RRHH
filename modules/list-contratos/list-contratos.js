$(document).ready(function(){
  $('#btn-add-new').click(function(){ location.href = 'index.php?module=contratos'; });
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

// Formatter para la columna Opciones -> Ver PDF
function pdfFormatter(value, row, index) {
  var url = value || row.archivo_contrato;
  if (url && String(url).trim() !== '' && url !== 'null') {
    var filename = (function(u){ try { var p = u.split('?')[0]; var parts = p.split('/'); return parts[parts.length-1] || 'contrato.pdf'; } catch(e){ return 'contrato.pdf'; } })(url);
    return '<button type="button" class="btn btn-danger btn-sm btn-view-pdf" data-url="' + url + '" data-toggle="tooltip" title="' + filename + '"><i class="fa fa-file-pdf-o"></i></button>';
  }
  return '<button type="button" class="btn btn-default btn-sm" disabled data-toggle="tooltip" title="Sin archivo"><i class="fa fa-file-o"></i></button>';
}

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
