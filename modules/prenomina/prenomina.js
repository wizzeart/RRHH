$(document).ready(function(){
  function setActiveTab(tabEl){
    tabEl.closest('ul').querySelectorAll('li').forEach(function(li){ li.classList.remove('active'); });
    tabEl.parentElement.classList.add('active');
  }
  function reloadForTab(tabName){
    var $table = $('#table-prenomina');
    if (!$table.data('bootstrap.table')) { $table.bootstrapTable(); }
    var url = 'api-app.php?module=prenomina&method=list-prenomina&tab=' + encodeURIComponent(tabName);
    $table.bootstrapTable('refreshOptions', { url: url });
  }

  // Build tabs from departamentos (names) and set first as active
  function buildTabs(){
    fetch('api-app.php?module=prenomina&method=list-departamentos')
      .then(function(r){ return r.json(); })
      .then(function(list){
        var $ul = $('#tabs-prenomina');
        $ul.empty();
        if (Array.isArray(list) && list.length){
          list.forEach(function(name, idx){
            var active = idx === 0 ? ' class="active"' : '';
            var li = '<li'+active+'><a href="#" data-tab="'+ (name||'') +'">'+ (name||'') +'</a></li>';
            $ul.append(li);
          });
          var first = $ul.find('li.active a');
          if (first.length){
            setTimeout(function(){ reloadForTab(first.data('tab')); }, 0);
          }
        } else {
          // fallback: no tabs, base URL
          var $table = $('#table-prenomina');
          if (!$table.data('bootstrap.table')) { $table.bootstrapTable(); }
          $table.bootstrapTable('refreshOptions', { url: 'api-app.php?module=prenomina&method=list-prenomina' });
        }
      })
      .catch(function(){
        var $table = $('#table-prenomina');
        if (!$table.data('bootstrap.table')) { $table.bootstrapTable(); }
        $table.bootstrapTable('refreshOptions', { url: 'api-app.php?module=prenomina&method=list-prenomina' });
      });
  }
  
  $(document).on('click', '#tabs-prenomina a', function(e){
    e.preventDefault();
    var tabName = $(this).data('tab');
    setActiveTab(this);
    reloadForTab(tabName);
  });

  // init dynamic tabs
  buildTabs();

  // formatters used in the table
  window.currencyFormatter = function(value){
    var num = Number(value||0);
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'CUP', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num);
  };
  window.number2Formatter = function(value){
    var num = Number(value||0);
    return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num);
  };

  // Editable hours input
  window.hoursInputFormatter = function(value, row){
    var v = (value == null || value === '') ? 192 : parseInt(value);
    return '<input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="3" class="prenom-horas" data-id="'+ row.id +'" value="'+ v +'" style="width:80px; text-align:center; padding:5px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-weight:bold;" />';
  };

  // Recalculate dependent fields when hours change
  function recalcForRow(row){
    var horas = Number(row.horas || 0);
    var tarifa = Number(row.tarifa || 0);
    var ausencias = Number(row.ausencias || 0);
    var a_cobrar = Math.trunc(horas * tarifa);
    var sal_dev = a_cobrar;
    var salario_neto = a_cobrar;
    var seg_social = Math.trunc(a_cobrar * 0.05);
    
    // Calcular ing_pers_3 e ing_pers_5 según rangos salariales
    var ing_pers_3 = 0;
    var ing_pers_5 = 0;
    
    if (salario_neto >= 3260 && salario_neto <= 9510) {
        // Rango 3260-9510: 3% fijo del rango completo
        ing_pers_3 = Math.trunc((9510 - 3260) * 0.03);
    } else if (salario_neto > 9510) {
        // Sobre 9510: 3% fijo + 5% del exceso
        ing_pers_3 = Math.trunc((9510 - 3260) * 0.03);
        ing_pers_5 = Math.trunc((salario_neto - 9510) * 0.05);
    }
    
    var ausenciasCosto = Math.trunc(ausencias * 8 * tarifa);
    var salario_pagar = Math.trunc(a_cobrar - (seg_social + ing_pers_3 + ing_pers_5 + ausenciasCosto));
    
    return {
      horas: horas,
      a_cobrar: a_cobrar,
      sal_dev: sal_dev,
      salario_neto: salario_neto,
      seg_social: seg_social,
      ing_pers_3: ing_pers_3,
      ing_pers_5: ing_pers_5,
      salario_pagar: salario_pagar,
      ausenciasCosto: ausenciasCosto
    };
  }

  $(document).on('input', '#table-prenomina .prenom-horas', function(){
    var $inp = $(this);
    // Only allow integers
    var value = $inp.val().replace(/[^0-9]/g, '');
    $inp.val(value);
  });

  $(document).on('change', '#table-prenomina .prenom-horas', function(){
    var $inp = $(this);
    var id = $inp.data('id');
    var horas = parseInt($inp.val() || 0, 10);
    var $table = $('#table-prenomina');
    var row = $table.bootstrapTable('getRowByUniqueId', id);
    if (!row) return;
    row.horas = horas;
    var updates = recalcForRow(row);
    $table.bootstrapTable('updateByUniqueId', { id: id, row: updates });
  });

  // Guardar: recolectar horas y enviar al backend
  $(document).on('click', '#btn-prenom-save', function(e){
    e.preventDefault();
    var year = parseInt($('#prenom-year').val(), 10) || new Date().getFullYear();
    var month = parseInt($('#prenom-month').val(), 10) || (new Date().getMonth()+1);

    // Recolectar horas por fila desde inputs (más confiable que leer el dataset)
    var rows = [];
    $('#table-prenomina .prenom-horas').each(function(){
      var $inp = $(this);
      var id = parseInt($inp.data('id'), 10);
      var horas = Number($inp.val() || 0);
      if (!isNaN(id)) {
        rows.push({ trabajador_id: id, horas: horas });
      }
    });

    if (rows.length === 0) {
      alert('No hay filas para guardar.');
      return;
    }

    fetch('api-app.php?module=prenomina&method=save-horas', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ year: year, month: month, rows: rows })
    }).then(function(r){ 
      return r.text();
    }).then(function(responseText){
      var resp;
      try {
        resp = JSON.parse(responseText);
      } catch (e) {
        console.error('Respuesta no es JSON válido:', responseText);
        var preview = responseText.length > 500 ? responseText.substring(0, 500) + '...' : responseText;
        alert('Error del servidor: Respuesta inválida\n\nRespuesta recibida:\n' + preview);
        return;
      }
      
      if (resp && resp.status === 1) {
        alert('Guardado correcto. Filas afectadas: ' + (resp.affected || 0));
        // Recargar la tabla
        $('#table-prenomina').bootstrapTable('refresh');
      } else if (resp && resp.error) {
        alert('Error: ' + resp.error);
      } else {
        alert('Error al guardar: ' + (resp && resp.msg ? resp.msg : 'Desconocido'));
      }
    }).catch(function(err){
      alert('Error de red al guardar: ' + err);
    });
  });

  // Exportar Excel: separado del botón guardar
  $(document).on('click', '#btn-prenom-export', function(e){
    e.preventDefault();
    
    var year = parseInt($('#prenom-year').val(), 10) || new Date().getFullYear();
    var month = parseInt($('#prenom-month').val(), 10) || (new Date().getMonth()+1);
    var $active = $('#tabs-prenomina li.active a');
    var tabName = $active.length ? $active.data('tab') : 'Todos';
    
    console.log('Exportando Excel para:', { year: year, month: month, tab: tabName });
    
    // Construir URL de exportación
    var url = 'api-app.php?module=prenomina&method=export-excel&year=' + year + '&month=' + month;
    if (tabName) {
      url += '&tab=' + encodeURIComponent(tabName);
    }
    
    console.log('URL de exportación:', url);
    
    // Abrir en nueva ventana para forzar descarga
    window.location.href = url;
    
    // Alternativa con timeout para mostrar mensaje
    setTimeout(function() {
      console.log('Exportación iniciada. Si no se descarga el archivo, revise la consola del navegador.');
    }, 1000);
  });
});
