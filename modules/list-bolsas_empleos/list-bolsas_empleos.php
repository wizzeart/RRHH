<!--Basic Toolbar-->
<!--===================================================-->
<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon " alt="Agregar a la bolsa" title="Agregar a la bolsa"><span class="icon-lg fa fa-plus"></span> Agregar a la bolsa</button>
    </div>
</div>
<!-- atributos quitados del tag table:   -->
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=bolsas_empleos&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="id"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" data-show-pagination-switch="true">
            <thead>
                <tr>
                    <!-- <th data-field="id" data-sortable="true">ID</th> -->
                    <th data-field="nombre" data-sortable="true" data-formatter="formatoNombreCompleto">Nombre Completo</th>
                    <th data-field="telefono" data-sortable="true">Teléfono</th>
                    <th data-field="cargo_postulado" data-sortable="true">Cargo Postulado</th>
                    <th data-field="fecha_registro" data-sortable="true">Fecha Registro</th>
                    <th data-field="observaciones" data-sortable="false" data-align="center" data-formatter="formatoObservaciones">Observaciones</th>
                    <th data-field="toolbar" data-align="center" data-sortable="false" data-formatter="formatoToolbar">Acciones</th>

                </tr>
            </thead>
        </table>
    </div>
</div>
<!--===================================================-->

<!-- Modal para observaciones -->
<div class="modal fade" id="modalObservaciones" tabindex="-1" role="dialog" aria-labelledby="modalObservacionesLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalObservacionesLabel">Observaciones</h4>
            </div>
            <div class="modal-body">
                <form id="formObservaciones">
                    <input type="hidden" id="observacion_id" name="id" value="">
                    <div class="form-group">
                        <label for="observacion_texto">Ingrese sus observaciones:</label>
                        <textarea class="form-control" id="observacion_texto" name="observaciones" rows="5" placeholder="Escriba aquí sus observaciones..."></textarea>
                        <input type="hidden" id="observacion_nombre" name="nombre" value="">
                        <input type="hidden" id="observacion_apellidos" name="apellidos" value="">
                        <input type="hidden" id="observacion_segundos_apellidos" name="segundos_apellidos" value="">
                        <input type="hidden" id="observacion_curriculum" name="curriculum" value="">
                        <input type="hidden" id="observacion_cargo_postulado_id" name="cargo_postulado_id" value="">
                        <input type="hidden" id="observacion_telefono" name="telefono" value="">
                        <input type="hidden" id="observacion_fecha_registro" name="fecha_registro" value="">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarObservacion">Guardar</button>
            </div>
        </div>
    </div>
</div>