<!--Basic Toolbar-->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=programas-capacitacion&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="id"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" data-show-pagination-switch="true">
            <thead>
                <tr>
                    <th data-field="tema" data-sortable="true">Tema</th>
                    <th data-field="dirigido_a" data-sortable="true">Dirigido a</th>
                    <th data-field="responsable" data-sortable="true">Responsable</th>
                    <th data-field="fecha_estimada" data-sortable="true">Fecha Estimada</th>
                    <th data-field="modalidad" data-sortable="true">Modalidad</th>
                    <th data-field="horas" data-sortable="true">Horas</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->
