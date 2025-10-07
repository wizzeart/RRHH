<?php
global $data, $page, $app;

// Verificar si el usuario está autenticado
if (empty($app->user_id)) {
    header("Location: login.html");
    exit();
}

// Obtener el ID del trabajador
$trabajador_id = null;
if (isset($_GET['id'])) {
    $trabajador_id = $_GET['id'];
} elseif (isset($_GET['usuario_id'])) {
    $sql = "SELECT id FROM trabajadores WHERE usuario_id = :usuario_id";
    $trabajador = $app->db->fetchRow($sql, ['usuario_id' => $_GET['usuario_id']]);
    if ($trabajador) {
        $trabajador_id = $trabajador['id'];
    }
}

if (!$trabajador_id) {
    die("No se pudo identificar al trabajador");
}

$page['title'] = 'Planificación de Vacaciones';
$page['subtitle'] = 'Seleccione los días de vacaciones';

// Agregar estilos y scripts necesarios en el header y footer
?>
<head>
<link href="js/mainfc.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    #calendar {
        margin: 20px 0;
    }
</style>

<script src="js/mainfc.min.js"></script>
<script src="js/locales-all.min.js"></script>
<!-- <script src="modules/planificacion-vacaciones/planificacion-vacaciones.js"></script> -->

<style>
    
    Mejoras para móviles
    .fc-day-touch {
        background-color: rgba(0,0,0,0.05) !important;
    }
    
    .fc .fc-button {
        padding: 0.4em 0.65em;
    }
    
    .fc .fc-toolbar-title {
        font-size: 1.25em;
    }
    
    @media (max-width: 767px) {
        .fc-toolbar.fc-header-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .fc-toolbar.fc-header-toolbar .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        
        .fc .fc-toolbar-title {
            font-size: 1.1em;
            margin: 5px 0;
        }
        
        .fc .fc-button {
            padding: 0.3em 0.5em;
            font-size: 0.9em;
        }
    }
</style>

</head>

    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Calendario de Vacaciones</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Seleccione el rango de fechas para sus vacaciones.
                        </div>
                        <form id="vacation-form" class="form-inline mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group ml-2">
                                        <label for="fecha_inicio" class="mr-2">Fecha Inicio:</label>
                                        <input type="date" class="form-control" id="fecha_inicio" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mr-2 ml-2">
                                        <label for="fecha_fin" class="mr-2">Fecha Fin:</label>
                                        <input type="date" class="form-control" id="fecha_fin" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary ml-2">
                                        <i class="fa fa-plus"></i> Agregar Vacaciones
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="calendar" style="margin-top: 20px;"></div>
                
                <div class="text-right" style="margin-top: 20px;">
                    <button id="btn-guardar" class="btn btn-success">
                        <i class="fa fa-save"></i> Guardar Vacaciones
                    </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar la solicitud -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalConfirmacionLabel">Confirmar Solicitud de Vacaciones</h4>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea solicitar los siguientes días de vacaciones?</p>
                <div id="dias-seleccionados"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-solicitud">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    var trabajadorId = <?php echo json_encode($trabajador_id); ?>;
</script>