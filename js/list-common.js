(function(window, $){
  function notify(type, title, message, timer){
    if ($ && $.niftyNoty){
      $.niftyNoty({ type:type||'info', container:'floating', title:title||'', message:message||'', timer: timer!=null?timer:3000, closeBtn:true, focus:true });
    } else if (window.alert) {
      alert((title?title+': ':'') + (message||''));
    }
  }

  function buildOperateFormatter(){
    return [
      '<button class="edit btn btn-info btn-xs btn-icon icon-sm fa fa-edit" title="Editar" style="margin-right:6px"></button>',
      '<button class="remove btn btn-danger btn-xs btn-icon icon-sm fa fa-trash" title="Eliminar"></button>'
    ].join('');
  }

  function buildOperateEvents(config){
    var editModule = config && config.editModule ? config.editModule : '';
    var apiModule  = config && config.apiModule  ? config.apiModule  : '';
    var tableId    = config && config.tableId    ? config.tableId    : '#table-panel';

    return {
      'click .edit': function (e, value, row){
        if (!row || !row.id) return;
        location.href = '?module=' + editModule + '&id=' + row.id;
      },
      'click .remove': function (e, value, row){
        if (!row || !row.id) return;
        if (confirm('¿Está seguro de eliminar el registro: ' + (row.nombre||row.id) + '?')){
          $.ajax({
            url: 'api-app.php', type:'POST', dataType:'json',
            data: { module: apiModule, method: 'del', id: row.id }
          }).done(function(resp){
            if (resp && resp.status==1){
              $(tableId).bootstrapTable('refresh');
              notify('success','Éxito', (resp.msg||'Eliminado correctamente'), 3000);
            } else {
              notify('danger','Error', (resp && resp.msg) || 'Error al eliminar', 4000);
            }
          }).fail(function(){
            notify('danger','Error', 'Error de conexión', 4000);
          });
        }
      }
    };
  }

  window.ListCommon = {
    notify: notify,
    buildOperateFormatter: buildOperateFormatter,
    buildOperateEvents: buildOperateEvents
  };
})(window, window.jQuery);
