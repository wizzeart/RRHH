<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <!-- Contenido directo sin tabs -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Tarifa por hora de los Trabajadores</h4>
            </div>
            <div class="panel-body">
                <table 
                    id="table-trabajadores"
                    data-toggle="table"
                    data-url="api-app.php?module=saldos&method=list-trabajadores"
                    data-search="true"
                    data-show-refresh="true"
                    data-show-toggle="false"
                    data-show-columns="false"
                    data-sort-name="nombre"
                    data-page-list="[20, 50, 100]"
                    data-page-size="50"
                    data-pagination="true" 
                    data-show-pagination-switch="true">
                    <thead>
                        <tr>
                            
                            <th data-field="nombre" data-sortable="true">Nombre y Apellidos</th>
                            
                           
                            <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                            <th data-field="cargo_salario" data-align="right" data-sortable="true" data-formatter="salarioFormatter" data-width="120">Tarifa x Hora</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    // Inicialización básica del módulo
    console.log('Módulo Gestión de Saldos cargado');
});

// Formatter para mostrar salarios con formato de moneda
function salarioFormatter(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }
    return '$' + parseFloat(value).toFixed(2);
}
</script>
