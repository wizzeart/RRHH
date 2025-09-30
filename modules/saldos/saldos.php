<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <p>Módulo de gestión de saldos. Utilice el <a href="?module=list-saldos">listado de saldos</a> para ver la información detallada.</p>
        
        <div class="form-control">
            <button id="btn-view-list" class="btn btn-primary btn-icon icon-lg fa fa-list" title="Ver Listado de Saldos">Ver Listado de Saldos</button>
        </div>
    </div>
</div>

<script>
$(function(){
    $('#btn-view-list').on('click', function(){
        location.href='?module=list-saldos';
    });
});
</script>
