<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-cumpleanos" data-toggle="tab" aria-expanded="true"><i class="fa fa-birthday-cake"></i> Listado de Cumpleaños</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
            <!-- Tab Cumpleaños -->
            <div class="tab-pane fade active in" id="tab-cumpleanos">
                <h3 style="margin-bottom: 20px;">Listado de Cumpleaños</h3>
                <!-- Filtro por mes -->
                <div class="form-inline" style="margin-bottom: 15px;">
                    <div class="form-group" style="margin-right: 10px;">
                        <label for="filterMesCumpleanos" style="margin-right: 10px;">Filtrar por mes:</label>
                        <select id="filterMesCumpleanos" class="form-control">
                            <option value="">Todos los meses</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                    <button id="btn-filter-cumpleanos" class="btn btn-primary">Filtrar</button>
                    <button id="btn-reset-cumpleanos" class="btn btn-default" style="margin-left: 5px;">Limpiar</button>
                </div>
                <script>
                    // Set current month as default
                    document.addEventListener('DOMContentLoaded', function() {
                        var currentMonth = new Date().getMonth() + 1;
                        document.getElementById('filterMesCumpleanos').value = currentMonth;
                    });
                </script>
                <script src="js/confetti.min.js"></script>
                <table
                    id="table-cumpleanos"
                    data-toggle="table"
                    data-url="api-app.php?module=trabajadores&method=list-cumpleanos"
                    data-search="true"
                    data-show-refresh="true"
                    data-sort-name="mes"
                    data-sort-order="asc"
                    data-page-size="25"
                    data-pagination="true"
                    data-side-pagination="client">
                    <thead>
                        <tr>
                            <th data-field="nombre" data-sortable="true" data-formatter="formatoNombreCompleto">Nombre Completo</th>
                            <th data-field="fecha_cumpleanos" data-sortable="true">Cumpleaños (Día/Mes)</th>
                            <th data-field="dias_para_cumpleanos" data-formatter="diasCumpleanosFormatter" data-sortable="true">Días Restantes</th>
                            <th data-field="edad" data-sortable="true">Edad</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>