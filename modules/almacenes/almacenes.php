<script>
    action = '<?php print($action) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <form class="panel-body form-padding"><!-- form-horizontal -->
        <!--Text Input-->
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="f-title">Código Contenedor</label>
                    <input type="text" id="f-contenedor-id" name="xcontenedor_id" class="form-control" placeholder="ID Contenedor" value="<?php if (isset($data['xcontenedor_id'])) print($data['xcontenedor_id']); ?>" disabled="true">
                    <small class="help-block">Código de la contenedor</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label" for="f-referencia">Referencia</label>
                    <input type="text" id="f-referencia" name="xreferencia" class="form-control" placeholder="Referencia Contenedor" value="<?php if (isset($data['xreferencia'])) print($data['xreferencia']); ?>">
                    <small class="help-block">Referencia de la contenedor</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div>
                        <label class="control-label" for="f-fecha">Fecha</label>
                    </div>
                    <div id="f-fecha">
                        <div class="input-group date">
                            <input name="xfecha" type="text" class="form-control" value="<?php if (isset($data['xfecha_format'])) print($data['xfecha_format']); ?>" autocomplete="off">
                            <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                        </div>
                        <small class="help-block">Seleccione fecha del pedido</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea id="f-obs" name="xobs" class="form-control" rows="5" ><?php if (isset($data['xobs'])) print($data['xobs']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="panel-footer text-center">
            <img id="img-loading" class="hidden" src="img/spinners/282.gif"/>
            <button id="btn-save" class="btn btn-info icon-lg" type="button">
                <i class="fa fa-check"></i>
                Guardar
            </button>
            <button id="btn-back" class="btn btn-default icon-lg" type="button">
                <i class="fa fa-undo"></i>
                Volver
            </button>
            <button id="btn-new" class="btn btn-warning icon-lg" type="button">
                <i class="fa fa-plus"></i>
                Nuevo
            </button>
        </div>

    </form>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->

</div>

<div id="modalUpload" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-hdd"></i> Asociar Imagen</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Seleccione un documento que quiere asociar a la ficha del cliente.</p>
                <div class="list-group dropzone-img">
                    <div class="list-group-item dropzone-container">
                        <div class="form-group">
                            <form action="file-upload.php" class="dropzone dz-clickable" id="imageGalleryDropzone">
                                <input type="hidden" name="module" value="contenedor"/>
                                <input type="hidden" name="contenedor_id" value="<?php print($data['xcontenedor_id']) ?>"/>
                                <div class="dz-message clearfix">
                                    <div>
                                        <i class="fa fa-image fa-3x"></i>
                                    </div>
                                    <span>Haz click para seleccionar<br>máx: 10MB</span>
                                    <div class="hover">
                                        <i class="icon-download"></i>
                                        <span>DROP FILES HERE</span>
                                    </div>
                                </div>
                            </form>                           
                        </div>
                    </div>
                    <div class="list-group-item preview-container">
                        <div class="form-group">
                            <div class="gallery-container">
                                <!-- list image gallery -->
                            </div>                       
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Cancelar</button>
                <!-- <button id="btn-save-temperatura" type="button" class="btn btn-lg btn-primary">Guardar Imagen</button> -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->