<div class="modal fade" id="documentoModal" tabindex="-1" role="dialog" aria-labelledby="documentoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="documentoModalLabel">Agregar Documento</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="documentoModalBody">
                <!-- Aquí se insertan los datos -->
                <form id="docForm" enctype="multipart/form-data" method="POST">
                    <div class="form-group">
                        <label for="tipo_doc" class="col-form-label">Tipo de Documento</label>
                        <input type="text" class="form-control" id="tipo_doc" aria-describedby="tipo_doc_help" placeholder="Escriba el tipo de documento" required>
                    </div>
                    <div class="form-group">
                        <label for="file_doc" class="form-label">Seleccione un archivo</label>
                        <input class="form-control" type="file" id="file_doc" name="file_doc" required>
                    </div>
                    <div class="mt-3 text-center">
                        <button type="submit" class="btn btn-primary ml-2">Guardar</button>
                        <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>