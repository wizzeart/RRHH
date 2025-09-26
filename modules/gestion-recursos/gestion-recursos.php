<script>
    var action = '<?php print($action) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-general" data-toggle="tab" aria-expanded="true">Entrega de Recursos</a></li>
            </ul>
            <a class="fa fa-question-circle fa-lg fa-fw unselectable add-tooltip" href="#" data-original-title="<h4 class='text-thin'>Información</h4><p style='width:150px'>Ficha de Entrega de Recursos</p>" data-html="true" title=""></a>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>

    <!-- BASIC FORM ELEMENTS -->
    <!--===================================================-->
    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-general">
                <div class="panel">
                    <div class="panel-body orm-padding"><!-- form-horizontal -->

                        <!-- Primera fila -->
                        <div class="row">
                            <div class="col-md-3 hidden">
                                <div class="form-group">
                                    <label class="control-label" for="f-id">Código del Recurso</label>
                                    <input type="text" id="f-id" name="id" class="form-control" placeholder="ID" value="<?php if (isset($data['id'])) print($data['id']); ?>" disabled>
                                    <small class="help-block">Identificador único</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-trabajador">Trabajador <span class="text-danger">*</span></label>
                                    <select id="f-trabajador" name="trabajador_id" class="form-control" required <?php if (isset($data['trabajador_id'])) echo 'disabled'; ?>>
                                        <option value="">Seleccione un trabajador</option>
                                        <?php
                                        // Obtener la lista de trabajadores activos
                                        $sql = "SELECT id, CONCAT(nombre, ' ', apellidos) as nombre_completo 
                                                FROM trabajadores 
                                                WHERE trabajador_eliminado = 0 
                                                ORDER BY nombre, apellidos";
                                        $trabajadores = $app->db->fetchAll($sql);

                                        foreach ($trabajadores as $trabajador):
                                            $selected = (isset($data['trabajador_id']) && $data['trabajador_id'] == $trabajador['id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $trabajador['id']; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($trabajador['nombre_completo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="f-recurso">Recurso <span class="text-danger">*</span></label>
                                    <input type="text" id="f-recurso" name="recurso" class="form-control" placeholder="Recurso" value="<?php if (isset($data['nombre'])) print($data['nombre']); ?> " <?php if (isset($data['nombre'])) echo 'disabled'; ?>>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label" for="f-fecha-registro">Fecha Entrega <span class="text-danger">*</span></label>
                                        <input type="date" id="f-fecha-registro" name="fecha_entrega_a_t" class="form-control" value="<?php if (isset($data['fecha_entrega_a_t'])) print($data['fecha_entrega_a_t']);else print(date('Y-m-d')); ?>">
                                    </div>
                                    </div>
                                    <div class="col-md-6" <?php if (!isset($data['id'])) print("hidden"); ?>>
                                    <div class="form-group" >
                                        <label class="control-label" for="f-fecha-devolucion">Fecha Entrega a RH</label>
                                        <input type="date" id="f-fecha-devolucion" name="fecha_entrega_a_rh" class="form-control" value="<?php if (isset($data['fecha_entrega_a_rh'])) print($data['fecha_entrega_a_rh']);else print(date('Y-m-d')); ?>">
                                    </div>  
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" >
                            </div>

                        </div>



                    </div>
                    <div class="panel-footer text-center">
                        <img id="img-loading" class="hidden" src="img/spinners/282.gif" />
                        <button id="btn-save" class="btn btn-info icon-lg" type="button">
                            <i class="fa fa-check"></i>
                            Guardar
                        </button>
                        <button id="btn-back" class="btn btn-default icon-lg" type="button">
                            <i class="fa fa-undo"></i>
                            Volver
                        </button>

                        <hr>

                    </div>
                </div>
            </div><!-- TAB PEDIDOS -->
        </div>
    </div>
    <!-- =================================================== -->
    <!-- END BASIC FORM ELEMENTS -->
</div>