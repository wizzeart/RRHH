<?php
// Añadir al final de home.php

// Verificar si el usuario es administrador (ID 1) o jefe de área (ID 4)
if ($app->rol == 1 || $app->rol == 4):
    $currentYear = date('Y');

    // Obtener todos los planes de vacaciones que toquen el año actual
    $sql_plans = "SELECT p.*, t.nombre, t.apellidos, t.apellidos_segundos, t.id as trabajador_id, t.foto
                  FROM plan_vacaciones p 
                  JOIN trabajadores t ON p.trabajador_id = t.id 
                  WHERE (YEAR(p.fecha_inicio) = $currentYear 
                     OR YEAR(p.fecha_fin) = $currentYear)
                  AND t.empresa_id = {$app->empresa_id}";
                     
    // Filtrar por departamento y ubicación para rol 4 (jefe de área)
    if ($app->rol == 4) {
        $sql_plans .= " AND t.id IN (
            SELECT DISTINCT t2.id 
            FROM trabajadores t2
            INNER JOIN asignacion_usuarios_departamentos aud ON aud.departamento_id = t2.departamento_id 
            INNER JOIN asignacion_usuarios_ubicaciones auu ON auu.ubicacion_id = t2.ubicacion
            WHERE aud.usuario_id = {$app->user_id} 
            AND auu.usuario_id = {$app->user_id}
        )";
        // Jefe de área ve todos los estados incluyendo los rechazados
    } elseif ($app->rol == 1) {
        // Administrador ve todos los estados incluyendo los rechazados
    }

    $plans = $app->db->fetchAll($sql_plans);

    // Organizar por meses
    // Estructura: $yearData[mes][plan_id] = { datos_plan, dias_en_mes, fechas_en_mes }
    $yearData = array_fill(1, 12, []);

    foreach ($plans as $plan) {
        // OJO: el campo `dias` tiene DOS formatos según el origen del plan:
        //   - Web   (mdl.Vacaciones::_save)      -> lista de fechas "YYYY-MM-DD,YYYY-MM-DD,..."
        //   - Móvil (api/mobile/vacaciones.php)  -> un simple contador numérico ("7")
        // Solo puede tratarse como lista cuando trae fechas reales; en cualquier otro caso se
        // deriva del rango fecha_inicio..fecha_fin (igual que hace el calendario de eventos en
        // mdl.Home::_get_calendario_eventos). Antes se hacía explode() a ciegas, y los planes
        // creados desde la app móvil desaparecían silenciosamente de esta planificación.
        $dates = [];
        $diasRaw = isset($plan['dias']) ? trim((string) $plan['dias']) : '';

        if ($diasRaw !== '' && !is_numeric($diasRaw)) {
            foreach (explode(',', $diasRaw) as $d) {
                $d = trim($d);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                    $dates[] = $d;
                }
            }
        }

        if (empty($dates)) {
            // Sin lista de días utilizable: generarlos a partir del rango
            try {
                if (!empty($plan['fecha_inicio']) && !empty($plan['fecha_fin'])
                    && $plan['fecha_inicio'] !== '0000-00-00' && $plan['fecha_fin'] !== '0000-00-00') {
                    $start = new DateTime($plan['fecha_inicio']);
                    $end = new DateTime($plan['fecha_fin']);
                    $end->modify('+1 day'); // Incluir último día
                    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
                    foreach ($period as $dt) {
                        $dates[] = $dt->format('Y-m-d');
                    }
                }
            } catch (Exception $e) {
                // Fechas corruptas: se omite el plan en lugar de romper la página de inicio
                @error_log('home-admin-vacations: fechas inválidas en plan_vacaciones id='
                    . (isset($plan['id']) ? $plan['id'] : '?') . ': ' . $e->getMessage());
                continue;
            }
        }

        // Distribuir fechas en los meses correspondientes
        $monthsInvolved = [];
        foreach ($dates as $date) {
            $ts = strtotime($date);
            $y = (int) date('Y', $ts);
            $m = (int) date('n', $ts);

            if ($y == $currentYear) {
                if (!isset($monthsInvolved[$m])) {
                    $monthsInvolved[$m] = ['count' => 0, 'dates' => []];
                }
                $monthsInvolved[$m]['count']++;
                $monthsInvolved[$m]['dates'][] = date('d', $ts); // Solo el día
            }
        }

        // Agregar al array principal
        foreach ($monthsInvolved as $m => $info) {
            $yearData[$m][] = [
                'plan' => $plan,
                'days_count' => $info['count'],
                'specific_days' => $info['dates']
            ];
        }
    }

    $monthsNames = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    ?>

    <!-- Sección de Planificación Anual para Administradores -->
    <div class="row" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="col-md-12">
            <div class="panel" style="border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <div class="panel-heading"
                    style="background: linear-gradient(to right, #2c3e50, #4ca1af); color: white; border-radius: 8px 8px 0 0;">
                    <h3 class="panel-title"><i class="fa fa-calendar-check-o"></i> Planificación Anual de Vacaciones -
                        <?php echo $currentYear; ?>
                    </h3>
                </div>
                <div class="panel-body" style="background: #fdfdfd;">
                    <div class="row">
                        <?php foreach ($monthsNames as $mNum => $mName): ?>
                            <div class="col-md-4 col-sm-6" style="margin-bottom: 20px;">
                                <div class="panel panel-bordered panel-primary" style="height: 100%; border-color: #e0e0e0;">
                                    <div class="panel-heading"
                                        style="background-color: #f5f5f5; color: #333; height: 40px; min-height: 40px; padding: 10px 15px;">
                                        <h5 class="panel-title" style="line-height: 1; font-size: 14px; font-weight: bold;">
                                            <?php echo $mName; ?>
                                        </h5>
                                    </div>
                                    <div class="panel-body"
                                        style="padding: 10px; min-height: 100px; max-height: 200px; overflow-y: auto;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                            <?php if (empty($yearData[$mNum])): ?>
                                                <small class="text-muted" style="font-style: italic;">Sin planificaciones</small>
                                            <?php else: ?>
                                                <?php foreach ($yearData[$mNum] as $item):
                                                    $plan = $item['plan'];
                                                    $estado = $plan['estado'] ?? 'Pendiente';

                                                    // El estado 'Procesada' significa que la prenómina YA descontó el plan,
                                                    // no que el trabajador lo haya disfrutado: la prenómina se guarda por el
                                                    // mes en que el plan EMPIEZA, y puede emitirse por adelantado. Por eso
                                                    // solo se rotula "Disfrutada" cuando el periodo ya terminó de verdad.
                                                    $hoy = date('Y-m-d');
                                                    $fIni = !empty($plan['fecha_inicio']) ? substr($plan['fecha_inicio'], 0, 10) : '';
                                                    $fFin = !empty($plan['fecha_fin']) ? substr($plan['fecha_fin'], 0, 10) : '';
                                                    $yaTermino = ($fFin !== '' && $fFin !== '0000-00-00' && $fFin < $hoy);
                                                    $enCurso = (!$yaTermino && $fIni !== '' && $fIni !== '0000-00-00' && $fIni <= $hoy);

                                                    // Determinar color y estado según el nuevo flujo
                                                    switch($estado) {
                                                        case 'Pendiente':
                                                            $statusColor = '#f0ad4e'; // Naranja
                                                            $statusText = 'Pendiente';
                                                            break;
                                                        case 'Aprobado Area':
                                                            $statusColor = '#5bc0de'; // Azul
                                                            $statusText = 'Aprobado por Área';
                                                            break;
                                                        case 'Aprobado':
                                                            $statusColor = '#28a745'; // Verde
                                                            $statusText = 'Aprobado Final';
                                                            break;
                                                        case 'Procesada':
                                                            if ($yaTermino) {
                                                                $statusColor = '#1a3e72'; // Azul oscuro
                                                                $statusText = 'Disfrutada';
                                                            } elseif ($enCurso) {
                                                                $statusColor = '#17a2b8'; // Turquesa
                                                                $statusText = 'En curso';
                                                            } else {
                                                                $statusColor = '#6f42c1'; // Morado
                                                                $statusText = 'Descontada en nómina (pendiente de disfrutar)';
                                                            }
                                                            break;
                                                        case 'Rechazado Area':
                                                            $statusColor = '#d9534f'; // Rojo
                                                            $statusText = 'Rechazado por Área';
                                                            break;
                                                        case 'Rechazado':
                                                                $statusColor = '#d9534f'; // Rojo
                                                                $statusText = 'Rechazado';
                                                                break;
                                                        default:
                                                            $statusColor = '#777'; // Gris
                                                            $statusText = 'Desconocido';
                                                    }
                                                    
                                                    $fullName = htmlspecialchars($plan['nombre'] . ' ' . $plan['apellidos'], ENT_QUOTES);
                                                    $daysList = implode(', ', $item['specific_days']);

                                                    // Si la solicitud original abarcaba más de un mes, _save() la dividió
                                                    // automáticamente en varios planes (uno por mes). Se avisa a quien
                                                    // aprueba para que no confunda las partes con solicitudes distintas.
                                                    $splitNotice = '';
                                                    if (!empty($plan['observaciones'])
                                                        && strpos($plan['observaciones'], 'dividida automáticamente') !== false) {
                                                        $splitNotice = "<p style='font-size:10px; color:#856404; background:#fff3cd; "
                                                            . "padding:3px 5px; border-radius:3px; margin-bottom:5px;'>"
                                                            . "⚠ " . htmlspecialchars($plan['observaciones'], ENT_QUOTES) . "</p>";
                                                    }
                                                    
                                                    // Convertir foto a base64
                                                    $fotoBase64 = '';
                                                    if (!empty($plan['foto'])) {
                                                        // Comprobar si es una ruta o datos binarios
                                                        if (is_string($plan['foto']) && preg_match('/^[\/\\\\a-zA-Z0-9._\/-]+$/', trim($plan['foto']))) {
                                                            // Es una ruta a archivo - intentar desde la raíz de uploads
                                                            $possiblePaths = [
                                                                $_SERVER['DOCUMENT_ROOT'] . '/' . $plan['foto'],
                                                                dirname(__FILE__) . '/../../' . $plan['foto'],
                                                                dirname(__FILE__) . '/../' . $plan['foto']
                                                            ];
                                                            
                                                            foreach ($possiblePaths as $filePath) {
                                                                if (file_exists($filePath)) {
                                                                    @$fotoBase64 = base64_encode(file_get_contents($filePath));
                                                                    break;
                                                                }
                                                            }
                                                        } else {
                                                            // Son datos binarios
                                                            @$fotoBase64 = base64_encode($plan['foto']);
                                                        }
                                                    }
                                                    
                                                    $imgSrc = $fotoBase64 ? "data:image/jpeg;base64," . $fotoBase64 : "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22{$statusColor}%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E" . strtoupper(substr($plan['nombre'], 0, 1) . substr($plan['apellidos'], 0, 1)) . "%3C/text%3E%3C/svg%3E";

                                                    // Determinar botones según rol y estado
                                                    $buttons = '';
                                                    if ($app->rol == 4) { // Jefe de área
                                                        if ($estado == 'Pendiente') {
                                                            $buttons = "<button class='btn btn-xs btn-success btn-block' onclick='approveVacationArea({$plan['id']}, {$plan['trabajador_id']})'>Aprobar</button>
                                                                       <button class='btn btn-xs btn-danger btn-block' onclick='rejectVacationArea({$plan['id']}, {$plan['trabajador_id']})' style='margin-top: 3px;'>Rechazar</button>";
                                                        } elseif ($estado == 'Aprobado Area') {
                                                            $buttons = "<small class='text-info'><i class='fa fa-check'></i> Esperando aprobación final</small>";
                                                        }
                                                    } elseif ($app->rol == 1) { // Administrador
                                                        if ($estado == 'Pendiente') {
                                                            // Administrador puede aprobar directamente desde Pendiente
                                                            $buttons = "<button class='btn btn-xs btn-success btn-block' onclick='approveVacationFinal({$plan['id']}, {$plan['trabajador_id']})'>Aprobar Directo</button>
                                                                       <button class='btn btn-xs btn-danger btn-block' onclick='rejectVacationFinal({$plan['id']}, {$plan['trabajador_id']})' style='margin-top: 3px;'>Rechazar</button>";
                                                        } elseif ($estado == 'Aprobado Area') {
                                                            // Administrador también puede aprobar final desde Aprobado Area
                                                            $buttons = "<button class='btn btn-xs btn-success btn-block' onclick='approveVacationFinal({$plan['id']}, {$plan['trabajador_id']})'>Aprobar Final</button>
                                                                       <button class='btn btn-xs btn-danger btn-block' onclick='rejectVacationFinal({$plan['id']}, {$plan['trabajador_id']})' style='margin-top: 3px;'>Rechazar</button>";
                                                        }
                                                    }

                                                    // Contenido del Tooltip/Popover
                                                    $popoverContent = "
                                                    <div class='text-center'>
                                                        <img src='{$imgSrc}' style='width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 5px; border: 2px solid {$statusColor};' onerror='this.onerror=null;this.src=\"images/default-user.png\";'>
                                                        <p style='font-weight:bold; margin-bottom:2px;'>{$fullName}</p>
                                                        <p style='font-size:12px; margin-bottom:5px;'>Días: {$item['days_count']}<br>Fechas: {$daysList}</p>
                                                        <p style='font-size:11px; color:{$statusColor}; font-weight:bold; margin-bottom:5px;'>{$statusText}</p>
                                                        {$splitNotice}
                                                        {$buttons}
                                                    </div>
                                                ";
                                                    ?>
                                                    <div class="vacation-dot"
                                                        style="width: 16px; height: 16px; background-color: <?php echo $statusColor; ?>; border-radius: 50%; cursor: pointer; transition: transform 0.2s;"
                                                        data-toggle="popover" data-html="true" data-trigger="hover focus"
                                                        data-placement="top"
                                                        data-content="<?php echo htmlspecialchars($popoverContent, ENT_QUOTES); ?>"
                                                        onmouseover="this.style.transform='scale(1.3)'"
                                                        onmouseout="this.style.transform='scale(1)'">
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('[data-toggle="popover"]').popover({
                container: 'body',
                trigger: 'manual'
            }).on('mouseenter', function () {
                var _this = this;
                $(this).popover('show');
                $('.popover').on('mouseleave', function () {
                    $(_this).popover('hide');
                });
            }).on('mouseleave', function () {
                var _this = this;
                setTimeout(function () {
                    if (!$('.popover:hover').length) {
                        $(_this).popover('hide');
                    }
                }, 100);
            });
        });

        // Funciones para el nuevo flujo de aprobación
        function approveVacationArea(id, trabajadorId) {
            if (!confirm('¿Está seguro de aprobar estas vacaciones?')) return;

            $.ajax({
                url: 'api-app.php?module=vacaciones&method=aprobar-area',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    trabajador_id: trabajadorId
                },
                success: function (response) {
                    if (response.status === 1) {
                        $.niftyNoty({
                            type: 'success',
                            container: 'floating',
                            html: response.msg,
                            timer: 5000
                        });
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        alert('Error: ' + response.msg);
                    }
                },
                error: function () {
                    alert('Error de conexión con el servidor.');
                }
            });
        }

        function rejectVacationArea(id, trabajadorId) {
            if (!confirm('¿Está seguro de rechazar estas vacaciones?')) return;

            $.ajax({
                url: 'api-app.php?module=vacaciones&method=rechazar-area',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    trabajador_id: trabajadorId
                },
                success: function (response) {
                    if (response.status === 1) {
                        $.niftyNoty({
                            type: 'warning',
                            container: 'floating',
                            html: response.msg,
                            timer: 5000
                        });
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        alert('Error: ' + response.msg);
                    }
                },
                error: function () {
                    alert('Error de conexión con el servidor.');
                }
            });
        }

        function approveVacationFinal(id, trabajadorId) {
            if (!confirm('¿Está seguro de aprobar finalmente estas vacaciones?')) return;

            $.ajax({
                url: 'api-app.php?module=vacaciones&method=aprobar-final',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    trabajador_id: trabajadorId
                },
                success: function (response) {
                    if (response.status === 1) {
                        $.niftyNoty({
                            type: 'success',
                            container: 'floating',
                            html: response.msg,
                            timer: 5000
                        });
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        alert('Error: ' + response.msg);
                    }
                },
                error: function () {
                    alert('Error de conexión con el servidor.');
                }
            });
        }

        function rejectVacationFinal(id, trabajadorId) {
            if (!confirm('¿Está seguro de rechazar finalmente estas vacaciones?')) return;

            $.ajax({
                url: 'api-app.php?module=vacaciones&method=rechazar-final',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    trabajador_id: trabajadorId
                },
                success: function (response) {
                    if (response.status === 1) {
                        $.niftyNoty({
                            type: 'danger',
                            container: 'floating',
                            html: response.msg,
                            timer: 5000
                        });
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        alert('Error: ' + response.msg);
                    }
                },
                error: function () {
                    alert('Error de conexión con el servidor.');
                }
            });
        }
    </script>
<?php endif; ?>