<?php
/**
 * Módulo de Análisis de Asistencia con K-Means (Client-Side)
 */

// Obtener meses disponibles (Helper para el dropdown)
$mesesDisponibles = [];
$fechaActual = new DateTime();
$mesesEsp = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

for ($i = 0; $i < 12; $i++) {
    $fecha = clone $fechaActual;
    $fecha->modify('-' . $i . ' month');
    $mNum = (int)$fecha->format('n');
    $anio = $fecha->format('Y');
    
    $mesesDisponibles[] = [
        'val' => $fecha->format('Y-m'),
        'label' => $mesesEsp[$mNum] . ' ' . $anio
    ];
}
$selectedMes = isset($_REQUEST['mes']) ? $_REQUEST['mes'] : date('Y-m');
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-filter"></i> Configuración del Análisis</h3>
            </div>
            <div class="panel-body">
                <form class="form-inline" onsubmit="return false;">
                    <div class="form-group">
                        <label for="select-mes">Mes de Análisis:</label>
                        <select id="select-mes" class="form-control">
                            <?php foreach ($mesesDisponibles as $m): ?>
                                <option value="<?php echo $m['val']; ?>" <?php echo ($m['val'] == $selectedMes) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($m['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-left: 15px;">
                        <!-- Fixed to K=3 as requested -->
                        <input type="hidden" id="select-clusters" value="3">
                    </div>
                    <button id="btn-ejecutar" class="btn btn-primary" style="margin-left: 15px;">
                        <i class="fa fa-play"></i> Analizar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="status-container"></div>

<div id="results-container" style="display:none;">
    
    <!-- Resumen de Grupos -->
    <div id="clusters-summary" class="row"></div>

    <div class="row">
        <!-- Gráfico Principal -->
        <div class="col-md-8">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-line-chart"></i> Distribución: Hora vs Variabilidad</h3>
                </div>
                <!-- Relative position for tooltip -->
                <div class="panel-body" style="position:relative;">
                    <div id="kmeans-scatter" style="height: 450px; width: 100%;"></div>
                    <div id="kmeans-scatter-tooltip" class="panel panel-default" style="display:none; position:absolute; z-index:1000; opacity:0.9; box-shadow: 2px 2px 10px rgba(0,0,0,0.2);"></div>
                    <div class="text-center text-muted" style="margin-top:10px;">
                        <i class="fa fa-arrow-right"></i> Eje X: <b>Hora Promedio de Entrada</b> &nbsp;|&nbsp; 
                        <i class="fa fa-arrow-up"></i> Eje Y: <b>Consistencia</b> (Minutos de variación)
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico Circular -->
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Proporción de Trabajadores</h3>
                </div>
                <div class="panel-body">
                    <div id="kmeans-pie" style="height: 300px; width: 100%;"></div>
                    
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                        <div class="text-left" style="font-size: 13px;">
                            <div style="margin-bottom: 4px;">
                                <i class="fa fa-square" style="color: #28a745;"></i> <b>Casos Excelentes</b>
                            </div>
                            <div style="margin-bottom: 4px;">
                                <i class="fa fa-square" style="color: #007bff;"></i> <b>Casos Cumplidores</b>
                            </div>
                            <div>
                                <i class="fa fa-square" style="color: #dc3545;"></i> <b>Casos No Cumplidores</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="alert alert-info" style="font-size: 13px;">
                <strong><i class="fa fa-info-circle"></i> Interpretación de Variabilidad (Eje Y):</strong>
                <ul style="padding-left: 20px; margin-top: 5px; margin-bottom: 0;">
                    <li style="margin-bottom: 3px;">
                        <span class="label label-success">Consistentes</span> <small>Baja variabilidad (+/- 2 min)</small><br/>
                        <i>Llega casi siempre a la misma hora.</i>
                    </li>
                    <li>
                        <span class="label label-warning">Irregulares</span> <small>Alta variabilidad (+/- 30 min)</small><br/>
                        <i>Sus horas de llegada cambian mucho día a día.</i>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="row">
        <div class="col-md-12">
            <div id="details-container"></div>
        </div>
    </div>
</div>

<!-- Estilos Específicos -->
<style>
    .flot-tick-label { font-size: 11px; color: #555; }
    .nav-tabs > li > a { font-weight: 600; }
    #kmeans-scatter-tooltip { pointer-events: none; }
</style>

<!-- Script del Módulo -->
<script src="/modules/analisis-asistencia/analisis-asistencia.js"></script>
<script>
    // Esperar a que jQuery esté disponible
    (function checkJQuery() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.log('Esperando jQuery...');
            setTimeout(checkJQuery, 100);
            return;
        }
        
        console.log('jQuery disponible, inicializando AttendanceAnalyzer');
        
        // Solo inicializar si no existe ya
        if (typeof window.attendanceAnalyzer === 'undefined') {
            console.log('Creando nueva instancia de AttendanceAnalyzer');
            window.attendanceAnalyzer = new AttendanceAnalyzer();
        } else {
            console.log('Ya existe instancia, reinicializando...');
            window.attendanceAnalyzer.init();
        }
    })();
</script>
