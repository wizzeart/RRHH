<?php
/**
 * Módulo Control Custodios
 * Visualización de registro_asistencia_horas con gráficos tipo pastel interactivos
 * Visible solo para empresa_id = 3
 */
?>

<div class="panel panel-default" id="control-custodios-panel">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-shield"></i> Control Custodios
            <small class="text-muted" style="margin-left: 8px;">Registro de Huellas por Hora</small>
        </h3>
    </div>
    <div class="panel-body">
        <!-- Filtros -->
        <div class="form-control" style="margin-bottom: 15px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <!-- Fecha -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><i class="fa fa-calendar"></i> Fecha</label>
                                <input type="date" class="form-control" id="custodios-fecha" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <!-- Buscar -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><i class="fa fa-search"></i> Buscar Custodio</label>
                                <input type="text" class="form-control" id="custodios-buscar" 
                                       placeholder="Nombre del custodio..." autocomplete="off">
                            </div>
                        </div>
                        <!-- Tipo de Horario -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><i class="fa fa-clock-o"></i> Tipo de Horario</label>
                                <select class="form-control" id="custodios-tipo-horario">
                                    <option value="">Todos los horarios</option>
                                    <option value="1"><i class="fa fa-sun-o"></i> Diurno</option>
                                    <option value="0">Especial</option>
                                    <option value="2">Nocturno</option>
                                </select>
                            </div>
                        </div>
                        <!-- Botón Filtrar -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button id="btn-filtrar-custodios" class="btn btn-primary btn-block">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                            </div>
                        </div>
                        <!-- Resumen -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div id="custodios-resumen" class="well well-sm text-center" style="margin-bottom: 0; padding: 8px;">
                                    <i class="fa fa-users"></i> <span id="custodios-total" style="font-weight: 700;">0</span> custodios
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de tarjetas con gráficos -->
        <div id="custodios-charts-container" class="row">
            <div class="col-md-12 text-center" style="padding: 60px 20px;">
                <i class="fa fa-spinner fa-spin fa-3x text-muted"></i>
                <p class="text-muted" style="margin-top: 15px;">Cargando registros de custodios...</p>
            </div>
        </div>
    </div>
</div>

<!-- Estilos del módulo -->
<style>
    /* ============ Control Custodios - Consistent with system panels ============ */
    .custodio-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border: 1px solid #eaeaea;
    }
    .custodio-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .custodio-card-header {
        background: #f5f5f5;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #eaeaea;
    }
    .custodio-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
    }
    .custodio-avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 20px;
    }
    .custodio-info {
        flex: 1;
        overflow: hidden;
    }
    .custodio-name {
        color: #333;
        font-size: 14px;
        font-weight: 600;
        margin: 0;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .custodio-name a {
        color: inherit;
        text-decoration: none;
    }
    .custodio-name a:hover {
        text-decoration: underline;
        color: #337ab7;
    }
    .custodio-registros-count {
        color: #999;
        font-size: 12px;
        margin: 2px 0 0;
    }
    .custodio-horario-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 999px;
        font-weight: 600;
    }
    .custodio-card-body {
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 200px;
    }
    .custodio-chart-wrapper {
        flex: 0 0 160px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .custodio-chart-canvas {
        max-width: 160px;
        max-height: 160px;
    }
    .custodio-horas-list {
        flex: 1;
        max-height: 180px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .custodio-horas-list::-webkit-scrollbar {
        width: 4px;
    }
    .custodio-horas-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }
    .custodio-hora-item {
        display: flex;
        align-items: center;
        padding: 5px 8px;
        margin-bottom: 3px;
        border-radius: 6px;
        background: #f9f9f9;
        border-left: 3px solid;
        transition: background 0.2s ease;
        font-size: 13px;
    }
    .custodio-hora-item:hover {
        background: #eef2f7;
    }
    .custodio-hora-item .hora-bullet {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
        flex-shrink: 0;
    }
    .custodio-hora-item .hora-text {
        font-weight: 700;
        font-size: 13px;
        color: #333;
        font-family: 'Courier New', Courier, monospace;
    }
    .custodio-hora-item .hora-label {
        margin-left: auto;
        font-size: 11px;
        color: #aaa;
    }

    /* Empty state */
    .custodios-empty {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .custodios-empty i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 12px;
    }
    .custodios-empty h4 {
        color: #666;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .custodio-card-body {
            flex-direction: column;
        }
        .custodio-chart-wrapper {
            flex: none;
        }
    }
</style>

<!-- Chart.js CDN for pie charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Script del módulo -->
<script src="/modules/control-custodios/control-custodios.js"></script>
<script>
    (function checkJQueryCustodios() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(checkJQueryCustodios, 100);
            return;
        }
        if (typeof Chart === 'undefined') {
            setTimeout(checkJQueryCustodios, 200);
            return;
        }
        if (typeof window.controlCustodios === 'undefined') {
            window.controlCustodios = new ControlCustodios();
        } else {
            window.controlCustodios.init();
        }
    })();
</script>
