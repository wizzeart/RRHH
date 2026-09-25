<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon" title="Enviar Nueva Notificación SMS">
            <span class="icon-lg fa fa-plus"></span> Enviar Nueva Notificación SMS
        </button>
        <button id="btn-config-auto" class="btn btn-primary btn-icon" title="Configuraciones Automáticas">
            <span class="icon-lg fa fa-cog"></span> Configuraciones Automáticas
        </button>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <table 
            id="table-panel"
            data-toggle="table"
            data-url="api-app.php?module=notificaciones-sms&method=list"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="false"
            data-show-columns="false"
            data-sort-name="fecha"
            data-sort-order="desc"
            data-page-list="[20, 50, 100]"
            data-page-size="50"
            data-pagination="true" 
            data-show-pagination-switch="true">
            <thead>
                <tr>
                    <th data-field="fecha" data-sortable="true" data-formatter="dateTimeFormatter">Fecha y Hora</th>
                    <th data-field="trabajador_nombre" data-sortable="false" data-formatter="formatoTrabajadorLink">Trabajador</th>
                    <th data-field="telefono" data-sortable="false">Teléfono</th>
                    <th data-field="mensaje" data-sortable="false" data-formatter="messageFormatter">Mensaje</th>
                    <th data-field="usuario_nombre" data-sortable="false">Enviado por</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modalVerMensajeSms" tabindex="-1" role="dialog" aria-labelledby="modalVerMensajeSmsLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalVerMensajeSmsLabel">Mensaje SMS</h4>
            </div>
            <div class="modal-body">
                <div id="verMensajeSmsTexto" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Configuración Automática -->
<div class="modal fade" id="modalConfiguracionAutomatica" tabindex="-1" role="dialog" aria-labelledby="modalConfiguracionAutomaticaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalConfiguracionAutomaticaLabel">Configuraciones Automáticas de Notificaciones SMS</h4>
            </div>
            <div class="modal-body">
                <form id="form-configuracion-automatica">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="chk-notificar-vacaciones" name="notificar_vacaciones" value="1">
                                    <strong>Notificar aprobación de vacaciones</strong><br>
                                    <small class="text-muted">Envía un SMS automático al trabajador cuando se aprueban sus vacaciones</small>
                                </label>
                            </div>
                            
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="chk-notificar-ausencia" name="notificar_ausencia" value="1">
                                    <strong>Notificar en caso de ausencia (9:00 AM)</strong><br>
                                    <small class="text-muted">Envía un SMS automático a las 9:00 AM a los trabajadores que no hayan registrado su entrada, informándoles que deben contactar con Recursos Humanos</small>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="chk-notificar-cumpleanos" name="notificar_cumpleanos" value="1">
                                    <strong>Notificar cumpleaños</strong><br>
                                    <small class="text-muted">Envía un SMS automático de felicitación a los trabajadores que cumplen años, de parte de Recursos Humanos</small>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="chk-notificar-parte-nocturno" name="notificar_parte_nocturno" value="1">
                                    <strong>Parte Nocturno Custodios (7:00 AM)</strong><br>
                                    <small class="text-muted">Envía un SMS automático a las 7:00 AM con el reporte de entradas y salidas de los custodios de horario nocturno (6:00 PM - 6:00 AM) del día anterior</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-configuracion">
                    <i class="fa fa-save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</div>
