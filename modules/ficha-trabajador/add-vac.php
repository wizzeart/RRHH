<!-- ============================
     MODAL GRANDE DE VACACIONES
============================ -->
<div class="modal fade" id="modalPlanificacionVacaciones" tabindex="-1" role="dialog" aria-labelledby="vacacionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- modal grande -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="vacacionesModalLabel">
                    <i class="fa fa-calendar"></i> Planificación de Vacaciones
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <head>
                    <link href="js/mainfc.min.css" rel="stylesheet">
                    <style>
                        .fc-event {
                            cursor: pointer;
                        }

                        #calendar {
                            margin: 20px 0;
                        }

                        /* Mejoras para móviles */
                        .fc-day-touch {
                            background-color: rgba(0, 0, 0, 0.05) !important;
                        }

                        .fc .fc-button {
                            padding: 0.4em 0.65em;
                        }

                        .fc .fc-toolbar-title {
                            font-size: 1.25em;
                        }

                        /* Estilos para el desglose de vacaciones */
                        #desglose-vacaciones {
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            gap: 15px;
                            margin-top: 15px;
                            padding-top: 15px;
                            border-top: 2px solid rgba(51, 122, 183, 0.2);
                        }

                        .desglose-item {
                            background: linear-gradient(135deg, rgba(51, 122, 183, 0.08) 0%, rgba(51, 122, 183, 0.02) 100%);
                            border-left: 4px solid #337ab7;
                            padding: 12px 15px;
                            border-radius: 4px;
                            text-align: center;
                            transition: all 0.3s ease;
                        }

                        .desglose-item:hover {
                            background: linear-gradient(135deg, rgba(51, 122, 183, 0.15) 0%, rgba(51, 122, 183, 0.08) 100%);
                            transform: translateY(-2px);
                            box-shadow: 0 2px 8px rgba(51, 122, 183, 0.15);
                        }

                        .desglose-item-label {
                            font-size: 11px;
                            text-transform: uppercase;
                            color: #666;
                            font-weight: 600;
                            letter-spacing: 0.5px;
                            margin-bottom: 8px;
                            display: block;
                        }

                        .desglose-item-valor {
                            font-size: 20px;
                            font-weight: bold;
                            color: #337ab7;
                            margin-bottom: 3px;
                        }

                        .desglose-item-unidad {
                            font-size: 11px;
                            color: #999;
                        }

                        .desglose-separador {
                            display: none;
                        }

                        .desglose-total .desglose-item-label {
                            color: #27ae60;
                        }

                        .desglose-total .desglose-item-valor {
                            color: #27ae60;
                            font-size: 24px;
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

                            #desglose-vacaciones {
                                grid-template-columns: 1fr;
                                gap: 10px;
                            }
                        }
                    </style>

                    <script src="js/mainfc.min.js"></script>
                    <script src="js/locales-all.min.js"></script>
                    <!-- <script src="modules/planificacion-vacaciones/planificacion-vacaciones.js"></script> -->
                </head>

                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Calendario de Vacaciones</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-success">
                                        <i class="fa fa-calendar-check-o"></i> <strong>Días de vacaciones disponibles:</strong> 
                                        <span id="dias-disponibles-display" class="badge badge-success" style="font-size: 16px; padding: 5px 10px;">0</span>
                                        <br/>
                                        <small style="color: #555; margin-top: 10px; display: block;">
                                            <span id="desglose-vacaciones"></span>
                                        </small>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> Seleccione el rango de fechas para sus vacaciones.
                                    </div>
                                    <form id="vacation-form" class="form-inline mb-3">
                                        <div class="row">
                                            <div>
                                                <input type="hidden" name="trabajador_id" id="trabajador_id_vac">
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group ml-2">
                                                    <label for="fecha_inicio_vac" class="mr-2">Fecha Inicio:</label>
                                                    <input type="date" class="form-control" id="fecha_inicio_vac" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mr-2 ml-2">
                                                    <label for="fecha_fin_vac" class="mr-2">Fecha Fin:</label>
                                                    <input type="date" class="form-control" id="fecha_fin_vac" required>
                                                </div>
                                            </div>
                                        </div>
                                    
                                    </form>
                                </div>
                            </div>
                            <div id="calendar"></div>



                        </div>
                    </div>
                </div>

                <!-- Modal interno de confirmación -->
                <div class="modal fade" id="modalConfirmacionVacaciones" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="modalConfirmacionLabel">Confirmar Solicitud de Vacaciones</h4>
                            </div>
                            <div class="modal-body">
                                <p>¿Está seguro que desea solicitar los siguientes días de vacaciones?</p>
                                <div id="dias-seleccionados"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="confirmar-solicitud-vacaciones">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- modal-body -->

            <div class="modal-footer">
                <div class="text-right" style="margin-top: 20px;">
                    <button id="btn-guardar-vacaciones" class="btn btn-success">
                        <i class="fa fa-save"></i> Guardar Vacaciones
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>