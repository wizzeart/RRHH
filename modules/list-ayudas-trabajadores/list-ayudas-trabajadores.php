<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-base">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-listado">Listado</a></li>
                <li><a data-toggle="tab" href="#tab-registrar">Registrar Ayuda</a></li>
            </ul>
            <div class="tab-content">
                <div id="tab-listado" class="tab-pane fade active in">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Ayudas registradas</h4>
                        </div>
                        <div class="panel-body">
                            <table 
                                id="table-ayudas"
                                data-toggle="table"
                                data-url="api-app.php?module=ayudas&method=list&_t=" + new Date().getTime()
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
                                        <th data-field="tipo_ayuda" data-sortable="true">Tipo</th>
                                        <th data-field="valor" data-align="right" data-formatter="moneyFormatter" data-width="120">Valor</th>
                                        <th data-field="moneda" data-align="center" data-width="90">Moneda</th>
                                        <th data-field="fecha_entrega" data-sortable="true" data-width="140">Fecha Entrega</th>
                                        <th data-field="descripcion">Descripción</th>
                                        <th data-field="_actions" data-formatter="ayudasActions" data-events="ayudasActionsEvents" data-align="center" data-width="120">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="tab-registrar" class="tab-pane fade">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Registrar nueva ayuda</h4>
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
                                        <label class="control-label" for="f-tipo">Tipo de Ayuda <span class="text-danger">*</span></label>
                                        <input type="text" id="f-tipo" class="form-control" placeholder="Ej: Préstamo, Bonificación, etc.">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="f-valor">Valor <span class="text-danger">*</span></label>
                                        <input type="number" step="10" min="0" id="f-valor" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="f-moneda">Moneda <span class="text-danger">*</span></label>
                                        <select id="f-moneda" class="form-control">
                                            <option value="">Seleccione</option>
                                            <option value="USD">USD</option>
                                            <option value="CUP">CUP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="f-fecha">Fecha de Entrega <span class="text-danger">*</span></label>
                                        <input type="date" id="f-fecha" class="form-control" value="<?php print(date('Y-m-d')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="control-label" for="f-descripcion">Descripción</label>
                                        <textarea id="f-descripcion" class="form-control" rows="3" placeholder="Detalles adicionales"></textarea>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="f-id-ayuda" value="">
                            <div class="text-center">
                                <button id="btn-guardar-ayuda" class="btn btn-primary">
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
        if (document.readyState === 'complete') { // si ya cargó todo y no hay jQuery, abortar
            console.error('jQuery no está cargado.');
            return;
        }
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
    $('#btn-guardar-ayuda').on('click', function(){
        var errors = [];
        var payload = {
            module: 'ayudas',
            method: 'save',
            trabajador_id: $('#f-trabajador').val(),
            tipo_ayuda: $('#f-tipo').val(),
            valor: $('#f-valor').val(),
            moneda: $('#f-moneda').val(),
            fecha_entrega: $('#f-fecha').val(),
            descripcion: $('#f-descripcion').val()
        };
        var editingId = $('#f-id-ayuda').val();
        if (editingId) { payload.id = editingId; }
        if(!payload.trabajador_id) errors.push('Seleccione un trabajador.');
        if(!payload.tipo_ayuda) errors.push('El tipo de ayuda es obligatorio.');
        if(!payload.valor || isNaN(payload.valor) || Number(payload.valor) <= 0) errors.push('Ingrese un valor válido.');
        if(!payload.moneda || (payload.moneda!=='USD' && payload.moneda!=='CUP')) errors.push('Seleccione una moneda válida.');
        if(!payload.fecha_entrega) errors.push('La fecha de entrega es obligatoria.');
        if(errors.length){ notifyRaw('danger','Validación', errors.join('\n'), 4000, $); return; }

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function(res){
                if(res.status==1){
                    notifyRaw('success','Ayudas','Ayuda guardada correctamente',3000, $);
                    $('a[href=#tab-listado]').tab('show');
                    setTimeout(function() { 
                        var $table = $('#table-ayudas');
                        $table.bootstrapTable('refresh', {
                            url: 'api-app.php?module=ayudas&method=list&_t=' + new Date().getTime()
                        });
                    }, 100);
                } 
            },
            error: function(xhr){
                notifyRaw('danger','Error', xhr.responseText || 'Error al guardar', 5000, $);
            }
        });
    });

    $('#btn-limpiar-ayuda').on('click', function(){
        $('#f-trabajador').val('');
        $('#f-tipo').val('');
        $('#f-valor').val('');
        $('#f-moneda').val('');
        $('#f-fecha').val('<?php print(date('Y-m-d')); ?>');
        $('#f-descripcion').val('');
        $('#f-id-ayuda').val('');
    });

    // Formateador y eventos de acciones
    window.ayudasActions = function(value, row){
        return [
            '<button class="btn btn-xs btn-info" title="Editar" data-action="edit"><i class="fa fa-edit"></i></button> ',
            '<button class="btn btn-xs btn-danger" title="Eliminar" data-action="del"><i class="fa fa-trash"></i></button>'
        ].join('');
    };
    window.ayudasActionsEvents = {
        'click [data-action=edit]': function(e, value, row){
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                dataType: 'json',
                data: {module:'ayudas', method:'get', id: row.id},
                success: function(r){
                    if (!r || !r.id) { notifyRaw('danger','Error','No se encontró la ayuda',4000, $); return; }
                    $('#f-id-ayuda').val(r.id);
                    $('#f-trabajador').val(r.trabajador_id);
                    $('#f-tipo').val(r.tipo_ayuda);
                    $('#f-valor').val(r.valor);
                    $('#f-moneda').val(r.moneda);
                    $('#f-fecha').val(r.fecha_entrega);
                    $('#f-descripcion').val(r.descripcion||'');
                    $('a[href=#tab-registrar]').tab('show');
                },
                error: function(xhr){ notifyRaw('danger','Error', xhr.responseText||'Error al cargar',4000, $); }
            });
        },
        'click [data-action=del]': function(e, value, row){
            if (!confirm('¿Eliminar la ayuda #' + row.id + '?')) return;
            $.ajax({
                url: 'api-app.php',
                type: 'POST',
                dataType: 'json',
                data: {module:'ayudas', method:'del', id: row.id},
                success: function(r){
                    if (r && r.status==1) {
                        notifyRaw('success','Ayudas','Ayuda eliminada',2500, $);
                        setTimeout(function() { $('#table-ayudas').bootstrapTable('refresh'); }, 300);
                    } else {
                        notifyRaw('danger','Error', (r&&r.msg)||'No se pudo eliminar',4000, $);
                    }
                },
                error: function(xhr){ notifyRaw('danger','Error', xhr.responseText||'Error al eliminar',4000, $); }
            });
        }
    };
    }

    waitForJQ(function($) { 
        initHandlers($);
        var $table = $('#table-ayudas');
        // No llamar a bootstrapTable() porque ya se inicializa con data-toggle="table"
        $table.on('load-success.bs.table', function (e, data) { console.log('Tabla de ayudas cargada', data); });
        $table.on('load-error.bs.table', function (e, status, res) { console.error('Error al cargar la tabla de ayudas:', status, res); });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { if ($(e.target).attr('href') === '#tab-listado') { $table.bootstrapTable('refresh'); } });
    });
})();
</script>
