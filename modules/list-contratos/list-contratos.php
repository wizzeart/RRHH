<div class="panel">
<div class="panel-heading">
        <h3 class="panel-title">Listado de contratos</h3>
    </div>

    <div class="panel-body">
    <div class="panel">
                    <div class="form-control">
                        <button id="btn-add-new" class="btn btn-mint btn-icon icon-lg fa fa-plus" alt="Añadir Nuevo contrato" title="Añadir Nuevo contrato">Añadir Nuevo Contrato</button>
                    </div>
                </div>
        
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=contratos&method=list"
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
                    <!-- <th data-field="id" data-sortable="true" data-width="80">ID</th> -->
                    <th data-field="trabajador_nombre" data-sortable="true">Trabajador</th>
                    <th data-field="tipo" data-sortable="true" data-width="260">Tipo</th>
                    <th data-field="fecha_inicio" data-sortable="true" data-width="140">Fecha Inicio</th>

                    <th data-field="firma_digital" data-formatter="firmadoFormatter" data-align="center" data-width="140">Firmado</th>
                    <th data-field="archivo_contrato" data-formatter="pdfFormatter" data-align="center" data-width="140">Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
