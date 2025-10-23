<div class="panel">
                <div class="form-group">
                    <button id="btn-add-new" class="btn btn-mint btn-icon" alt="Añadir Nuevo contrato" title="Añadir Nuevo contrato">
                        <span class="icon-lg fa fa-plus"></span> Añadir Nuevo Contrato
                    </button>
                    <button id="btn-add-anterior" class="btn btn-primary btn-icon" alt="Insertar Contrato Anterior" title="Insertar Contrato Anterior">
                        <span class="icon-lg fa fa-upload"></span> Insertar Contrato Anterior
                    </button>
                </div>
            </div>

<!-- Modal para Contratos Anteriores -->
<div class="modal fade" id="modalContratoAnterior" tabindex="-1" role="dialog" aria-labelledby="modalContratoAnteriorLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalContratoAnteriorLabel">Insertar Contrato Anterior</h4>
            </div>
            <div class="modal-body">
                <form id="form-contrato-anterior" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="trabajador_id">Trabajador <span class="text-danger">*</span></label>
                        <select class="form-control" id="trabajador_id" name="trabajador_id" required>
                            <option value="">Seleccione trabajador</option>
                            <?php 
                            if (isset($data_form['trabajadores']) && is_array($data_form['trabajadores'])) {
                                foreach ($data_form['trabajadores'] as $t) {
                                    $id = $t['id'];
                                    $full = trim(($t['nombre'] ?? '') . ' ' . ($t['apellidos'] ?? '') . ' ' . ($t['apellidos_segundos'] ?? ''));
                                    if ($full === '') continue;
                                    echo '<option value="' . intval($id) . '">' . htmlspecialchars($full, ENT_QUOTES, 'UTF-8') . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tipo_contrato">Tipo de Contrato <span class="text-danger">*</span></label>
                        <select class="form-control" id="tipo_contrato" name="tipo_contrato" required>
                            <option value="">Seleccione tipo de contrato</option>
                            <option value="1">Contrato Determinado</option>
                            <option value="2">Contrato Indeterminado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>

                    <div class="form-group fecha-fin-group" style="display: none;">
                        <label for="fecha_fin">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        <small class="text-muted">Solo para contratos determinados</small>
                    </div>

                    <div class="form-group">
                        <label for="archivo_contrato">Archivo del Contrato (PDF o WORD) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="archivo_contrato" name="archivo_contrato" 
                               accept=".pdf,.doc,.docx" required>
                        <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-undo"></i> Volver
                </button>
                <button type="button" class="btn btn-success" id="btn-guardar-contrato-anterior">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
    
<div class="panel">
<div class="panel-heading">
        <h3 class="panel-title">Listado de contratos</h3>
    </div>

    <div class="panel-body">
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
                    <th data-field="id" data-sortable="true" data-width="80" data-visible="false">ID</th>
                    <th data-field="trabajador_nombre" data-sortable="true">Trabajador</th>
                    <th data-field="tipo" data-sortable="true" data-width="260" data-formatter="tipoFormatter">Tipo</th>
                    <th data-field="fecha_inicio" data-sortable="true" data-width="140">Fecha Inicio</th>

                    <!-- <th data-field="firma_digital" data-formatter="firmadoFormatter" data-align="center" data-width="140">Firmado</th> -->
                    <th data-field="archivo_contrato" data-formatter="pdfFormatter" data-align="center" data-width="180">Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
