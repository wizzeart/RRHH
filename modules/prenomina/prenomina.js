(function(){
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
  $(function(){
    buildTabs();
  });

  // formatters used in the table
  window.currencyFormatter = function(value){
    var num = Number(value||0);
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'CUP', minimumFractionDigits: 2 }).format(num);
  };
  window.number2Formatter = function(value){
    var num = Number(value||0);
    return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
  };

  // Editable hours input
  window.hoursInputFormatter = function(value, row){
    var v = (value == null || value === '') ? 192 : value;
    return '<input type="number" step="0.01" min="0" class="form-control input-sm prenom-horas" data-id="'+ row.id +'" value="'+ v +'" style="width:110px; text-align:right;" />';
  };

  // Recalculate dependent fields when hours change
  function recalcForRow(row){
    var horas = Number(row.horas || 0);
    var tarifa = Number(row.tarifa || 0);
    var a_cobrar = horas * tarifa;
    var sal_dev = a_cobrar;
    var salario_neto = a_cobrar;
    var seg_social = +(a_cobrar * 0.05).toFixed(2);
    var ing_pers = +(a_cobrar * 0.0375).toFixed(2);
    var salario_pagar = +(a_cobrar - (seg_social + ing_pers)).toFixed(2);
    return {
      horas: horas,
      a_cobrar: a_cobrar,
      sal_dev: sal_dev,
      salario_neto: salario_neto,
      seg_social: seg_social,
      ing_pers: ing_pers,
      salario_pagar: salario_pagar
    };
  }

  $(document).on('change input', '#table-prenomina .prenom-horas', function(){
    var $inp = $(this);
    var id = $inp.data('id');
    var horas = Number($inp.val() || 0);
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
    }).then(function(r){ return r.json(); })
      .then(function(resp){
        if (resp && resp.status === 1) {
          alert('Guardado correcto. Filas afectadas: ' + (resp.affected || 0));
          // Exportar a Excel del tab actual
          var $active = $('#tabs-prenomina li.active a');
          var tabName = $active.length ? $active.data('tab') : '';
          var url = 'api-app.php?module=prenomina&method=export-excel&year=' + year + '&month=' + month + (tabName ? ('&tab=' + encodeURIComponent(tabName)) : '');
          window.open(url, '_blank');
        } else {
          alert('Error al guardar: ' + (resp && resp.msg ? resp.msg : 'Desconocido'));
        }
      }).catch(function(err){
        alert('Error de red al guardar: ' + err);
      });
  });
})();
