<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-base">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-listado">Listado</a></li>
                <li><a data-toggle="tab" href="#tab-registrar">Registrar Deuda</a></li>
            </ul>
            <div class="tab-content">
                <div id="tab-listado" class="tab-pane fade active in">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Deudas registradas</h4>
                        </div>
                        <div class="panel-body">
                            <table 
                                id="table-deudas"
                                data-toggle="table"
                                data-url="api-app.php?module=deudas&method=list&_t=" + new Date().getTime()
                                data-search="true"
                                data-show-refresh="true"
                                data-page-list="[20, 50, 100]"
                                data-page-size="20"
                                data-pagination="true"
                                data-show-pagination-switch="true">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-align="center" data-width="70">ID</th>
                                        <th data-field="trabajador" data-sortable="true">Trabajador</th>
                                        <th data-field="monto" data-align="right" data-formatter="moneyFormatter" data-width="120">Monto</th>
                                        <th data-field="fecha_registro" data-sortable="true" data-width="140">Fecha Registro</th>
                                        <th data-field="saldada" data-align="center" data-width="90">Saldada</th>
                                        <th data-field="fecha_saldo" data-sortable="true" data-width="140">Fecha Saldo</th>
                                        <th data-field="descripcion">Descripción</th>
                                        <th data-field="_actions" data-formatter="deudasActions" data-events="deudasActionsEvents" data-align="center" data-width="120">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="tab-registrar" class="tab-pane fade">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Registrar nueva deuda</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="f-trabajador">Trabajador <span class="text-danger">*</span></label>
                                        <select id="f-trabajador" class="form-control">
                                            <option value="">Seleccione trabajador</option>
                                            <?php if (isset($data_form['trabajadores']) && is_array($data_form['trabajadores'])) { ?>
                                                <?php foreach ($data_form['trabajadores'] as $t) { ?>
                                                    <option value="<?php print($t['id']); ?>"><?php print($t['nombre']); ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="f-monto">Monto <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" id="f-monto" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="f-fecha">Fecha de Registro <span class="text-danger">*</span></label>
                                        <input type="date" id="f-fecha" class="form-control" value="<?php print(date('Y-m-d')); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="control-label" for="f-descripcion">Descripción</label>
                                        <textarea id="f-descripcion" class="form-control" rows="3" placeholder="Detalles adicionales"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="f-saldada">Saldada</label>
                                        <select id="f-saldada" class="form-control">
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="f-fecha-saldo">Fecha de Saldo</label>
                                        <input type="date" id="f-fecha-saldo" class="form-control" value="">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="f-id-deuda" value="">
                            <div class="text-center">
                                <button id="btn-guardar-deuda" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function waitForJQ(cb){
        if (window.jQuery) { cb(window.jQuery); return; }
        if (document.readyState === 'complete') { console.error('jQuery no está cargado.'); return; }
        setTimeout(function(){ waitForJQ(cb); }, 50);
    }
    function notifyRaw(type, title, message, timer, $) {
        if ($ && $.niftyNoty && typeof $.niftyNoty === 'function') {
            $.niftyNoty({ type: type||'info', container: 'floating', title: title||'', message: message||'', timer: timer!=null?timer:3000, closeBtn: true, focus: true });
        } else {
            try { alert((title?title+': ':'') + (message||'')); } catch(e) { console.log(title, message); }
        }
    }

    window.moneyFormatter = function(value){
        if (value === null || value === undefined || value === '') return '-';
        return '$' + parseFloat(value).toFixed(2);
    };

    function initHandlers($){
    $('#btn-guardar-deuda').on('click', function(){
        var errors = [];
        var payload = {
            module: 'deudas',
            method: 'save',
            trabajador_id: $('#f-trabajador').val(),
            monto: $('#f-monto').val(),
            fecha_registro: $('#f-fecha').val(),
            descripcion: $('#f-descripcion').val(),
            saldada: $('#f-saldada').val(),
            fecha_saldo: $('#f-fecha-saldo').val()
        };
        var editingId = $('#f-id-deuda').val();
        if (editingId) { payload.id = editingId; }
        if(!payload.trabajador_id) errors.push('Seleccione un trabajador.');
        if(!payload.monto || isNaN(payload.monto) || Number(payload.monto) <= 0) errors.push('Ingrese un monto válido.');
        if(!payload.fecha_registro) errors.push('La fecha de registro es obligatoria.');
        if(errors.length){ notifyRaw('danger','Validación', errors.join('\n'), 4000, $); return; }

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function(res){
                if(res.status==1){
                    notifyRaw('success','Deudas','Deuda guardada correctamente',3000, $);
                    $('a[href=#tab-listado]').tab('show');
                    setTimeout(function() { 
                        var $table = $('#table-deudas');
                        $table.bootstrapTable('refresh', {
                            url: 'api-app.php?module=deudas&method=list&_t=' + new Date().getTime()
                        });
                    }, 100);
                } 
            },
            error: function(xhr){
                notifyRaw('danger','Error', xhr.responseText || 'Error al guardar', 5000, $);
            }
        });
    });

    $('#btn-limpiar-deuda').on('click', function(){
        $('#f-trabajador').val('');
        $('#f-monto').val('');
        $('#f-fecha').val('<?php print(date('Y-m-d')); ?>');
        $('#f-descripcion').val('');
        $('#f-saldada').val('0');
        $('#f-fecha-saldo').val('');
        $('#f-id-deuda').val('');
    });

    window.deudasActions = function(value, row){
        return [
            '<button class="btn btn-xs btn-info" title="Editar" data-action="edit"><i class="fa fa-edit"></i></button> ',
            '<button class="btn btn-xs btn-danger" title="Eliminar" data-action="del"><i class="fa fa-trash"></i></button>'
        ].join('');
    };
    window.deudasActionsEvents = {
        'click [data-action=edit]': function(e, value, row){
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                dataType: 'json',
                data: {module:'deudas', method:'get', id: row.id},
                success: function(r){
                    if (!r || !r.id) { notifyRaw('danger','Error','No se encontró la deuda',4000, $); return; }
                    $('#f-id-deuda').val(r.id);
                    $('#f-trabajador').val(r.trabajador_id);
                    $('#f-monto').val(r.monto);
                    $('#f-fecha').val(r.fecha_registro);
                    $('#f-descripcion').val(r.descripcion||'');
                    $('#f-saldada').val(r.saldada?1:0);
                    $('#f-fecha-saldo').val(r.fecha_saldo||'');
                    $('a[href=#tab-registrar]').tab('show');
                },
                error: function(xhr){ notifyRaw('danger','Error', xhr.responseText||'Error al cargar',4000, $); }
            });
        },
        'click [data-action=del]': function(e, value, row){
            if (!confirm('¿Eliminar la deuda #' + row.id + '?')) return;
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                dataType: 'json',
                data: {module:'deudas', method:'del', id: row.id},
                success: function(r){
                    if (r && r.status==1) {
                        notifyRaw('success','Deudas','Deuda eliminada',2500, $);
                        setTimeout(function() { $('#table-deudas').bootstrapTable('refresh'); }, 300);
                    } else {
                        notifyRaw('danger','Error', (r&&r.msg)||'No se pudo eliminar',4000, $);
                    }
                },
                error: function(xhr){ notifyRaw('danger','Error', xhr.responseText||'Error al eliminar',4000, $); }
            });
        }
    };
    }

    function refreshTable() {
        $('#table-deudas').bootstrapTable('refresh');
    }

    waitForJQ(function($) { 
        initHandlers($);
        var $table = $('#table-deudas');
        $table.bootstrapTable();
        $table.on('load-success.bs.table', function (e, data) { console.log('Tabla de deudas cargada', data); });
        $table.on('load-error.bs.table', function (e, status, res) { console.error('Error al cargar la tabla de deudas:', status, res); });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { if ($(e.target).attr('href') === '#tab-listado') { $table.bootstrapTable('refresh'); } });
        setTimeout(function() { $table.bootstrapTable('refresh'); }, 100);
    });
    window.refreshDeudasTable = refreshTable;
})();
</script>
