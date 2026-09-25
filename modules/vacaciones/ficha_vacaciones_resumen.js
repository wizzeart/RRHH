/**
 * Gestión de Vacaciones del Trabajador
 * Versión simplificada - Solo vacaciones
 */

console.log('=== CALENDARIO DE VACACIONES CARGADO ===');
console.log('Versión con incremento de 2.18 días por mes');

// Variables globales
var trabajadorId = $('#f-id').length ? $('#f-id').val() : null;

// Función de notificaciones
function notify(type, title, message, timer) {
    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
        $.niftyNoty({
            type: type || 'info',
            container: 'floating',
            title: title || '',
            message: message || '',
            timer: timer != null ? timer : 3000,
            closeBtn: true,
            focus: true
        });
    } else {
        var text = (title ? (title + ': ') : '') + (message || '');
        alert(text);
    }
}

// Calcular días laborables (excluyendo fines de semana)
function calcularDiasLaborables(fechaInicio, fechaFin) {
    function parseYMD(ymd) {
        if (!ymd) return NaN;
        var p = ('' + ymd).split('-');
        if (p.length !== 3) return NaN;
        return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
    }

    var inicio = parseYMD(fechaInicio);
    var fin = parseYMD(fechaFin);
    if (isNaN(inicio) || isNaN(fin) || fin < inicio) return 0;

    inicio.setHours(0, 0, 0, 0);
    fin.setHours(0, 0, 0, 0);

    var diasLaborables = 0;
    for (var current = new Date(inicio); current <= fin; current.setDate(current.getDate() + 1)) {
        var diaSemana = current.getDay();
        if (diaSemana !== 0 && diaSemana !== 6) diasLaborables++;
    }
    return diasLaborables;
}

// Formatters para la tabla
function diasFormatter(value, row, index) {
    var total = (value || row.dias_totales || 0);
    if (row && row.fecha_aprobacion && row.fecha_aprobacion !== '0000-00-00' && row.fecha_aprobacion !== null) {
        var diasEncoded = encodeURIComponent(row.dias || '');
        return '<span class="badge badge-info" style="font-size: 14px;">' + total +
            ' <button class="btn btn-default btn-xs ver-dias" data-dias="' + diasEncoded +
            '" data-id="' + (row.id || '') + '" title="Ver días detallados">' +
            '<i class="fa fa-eye"></i></button></span>';
    }
    return '<span class="badge badge-primary" style="font-size: 14px;">' + total + '</span>';
}

function formatoAprobacionVacaciones(value, row, index) {
    var estado = row && row.estado ? row.estado : '';
    // Plan ya descontado en nómina (vacaciones disfrutadas/liquidadas).
    if (estado === 'Procesada') {
        return '<span class="label label-info"><i class="fa fa-plane"></i> Disfrutada</span>';
    }
    // Plan aprobado: días congelados, a la espera de la prenómina del mes de inicio.
    if (estado === 'Aprobado' || (value && value !== '0000-00-00' && value !== null)) {
        return '<span class="label label-success"><i class="fa fa-check-circle"></i> Aprobado</span>';
    }
    if (estado === 'Rechazado' || estado === 'Rechazado Area') {
        return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Rechazado</span>';
    }
    if (estado === 'Aprobado Area') {
        return '<span class="label label-primary"><i class="fa fa-user-check"></i> Aprobado Área</span>';
    }
    return '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pendiente</span>';
}

function accionesVacaciones(value, row, index) {
    if (!row.fecha_aprobacion || row.fecha_aprobacion === '0000-00-00' || row.fecha_aprobacion === null) {
        return '<div class="btn-group btn-group-sm">' +
            '<button class="btn btn-success btn-sm btn-aprobar-vacacion" ' +
            'data-id="' + row.id + '" ' +
            'data-trabajador-id="' + row.trabajador_id + '" ' +
            'data-dias="' + (row.dias_totales || 0) + '" ' +
            'title="Aprobar vacación">' +
            '<i class="fa fa-check"></i> Aprobar' +
            '</button> ' +
            '<button class="btn btn-warning btn-sm btn-rechazar-vacacion" ' +
            'data-id="' + row.id + '" ' +
            'data-trabajador-id="' + row.trabajador_id + '" ' +
            'title="Rechazar solicitud">' +
            '<i class="fa fa-times"></i> Rechazar' +
            '</button>' +
            '</div>';
    }
    return '<div class="btn-group btn-group-sm">' +
        '<button class="btn btn-danger btn-sm btn-eliminar-vacacion" ' +
        'data-id="' + row.id + '" ' +
        'data-trabajador-id="' + row.trabajador_id + '" ' +
        'data-fecha-inicio="' + (row.fecha_inicio || '') + '" ' +
        'title="Eliminar registro">' +
        '<i class="fa fa-trash"></i> Eliminar' +
        '</button>' +
        '</div>';
}

// Cargar estadísticas de vacaciones
function cargarEstadisticas() {
    if (!trabajadorId) return;

    // Calcular días disponibles según el mes actual
    var hoy = new Date();
    var mesActual = hoy.getMonth();
    var anioActual = hoy.getFullYear();
    var DIAS_POR_MES = 2.18;
    var MAX_DIAS_ANIO = 24;

    // Calcular días acumulados hasta el mes actual
    var diasDisponiblesCalculados = Math.min(DIAS_POR_MES * (mesActual + 1), MAX_DIAS_ANIO);

    console.log('Estadísticas - Mes actual:', mesActual + 1, 'Días calculados:', diasDisponiblesCalculados);

    // Mostrar días disponibles calculados
    $('#dias-disponibles').html(diasDisponiblesCalculados.toFixed(2));

    // Cargar estadísticas del servidor para pendientes y aprobados
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: {
            module: 'vacaciones',
            action: 'api',
            method: 'get-estadisticas',
            trabajador_id: trabajadorId
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                // Días pendientes
                var diasPendientes = response.dias_pendientes || 0;
                $('#dias-pendientes').html(diasPendientes);

                // Días aprobados este año
                var diasAprobados = response.dias_aprobados_anio || 0;
                $('#dias-aprobados').html(diasAprobados);
            }
        },
        error: function () {
            $('#dias-pendientes').html('N/A');
            $('#dias-aprobados').html('N/A');
        }
    });
}

$(document).ready(function () {
    // Obtener ID del trabajador de la URL o del formulario
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('id')) {
        trabajadorId = urlParams.get('id');
    }

    // Cargar estadísticas al inicio
    cargarEstadisticas();

    // Botón volver
    $('#btn-back').click(function () {
        location.href = 'index.php?module=list-trabajadores';
    });

    // Variables globales para el calendario
    var currentCalendarMonth = new Date().getMonth();
    var currentCalendarYear = new Date().getFullYear();
    var selectedDates = [];
    var diasBaseActuales = 0;
    var DIAS_POR_MES = 2.18;
    var MAX_DIAS_ANIO = 24;

    // Función para calcular días disponibles según el mes visualizado
    function calcularDiasDisponibles(mesVisualizando, anioVisualizando) {
        // El cálculo es simple: cada mes acumula 2.18 días
        // Enero (mes 0) = 2.18 * 1 = 2.18
        // Febrero (mes 1) = 2.18 * 2 = 4.36
        // Marzo (mes 2) = 2.18 * 3 = 6.54
        // ...
        // Diciembre (mes 11) = 2.18 * 12 = 26.16

        // Calcular días acumulados hasta este mes (mes + 1 porque enero es mes 0)
        var diasAcumulados = DIAS_POR_MES * (mesVisualizando + 1);

        console.log('Calculando días disponibles:', {
            mes: mesVisualizando + 1, // +1 para mostrar 1-12
            nombreMes: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][mesVisualizando],
            anio: anioVisualizando,
            diasPorMes: DIAS_POR_MES,
            diasAcumulados: diasAcumulados,
            maxDiasAnio: MAX_DIAS_ANIO
        });

        // Limitar al máximo anual (24 días)
        var resultado = Math.min(diasAcumulados, MAX_DIAS_ANIO);

        console.log('Días disponibles final:', resultado);

        return resultado;
    }

    // Función para renderizar el calendario
    function renderCalendar(month, year) {
        var firstDay = new Date(year, month, 1);
        var lastDay = new Date(year, month + 1, 0);
        var daysInMonth = lastDay.getDate();
        var startingDayOfWeek = firstDay.getDay();

        var today = new Date();
        today.setHours(0, 0, 0, 0);

        var monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $('#calendar-month-year').text(monthNames[month] + ' ' + year);

        // Calcular días disponibles para este mes
        var diasDisponibles = calcularDiasDisponibles(month, year);
        console.log('Renderizando calendario - Días disponibles:', diasDisponibles);

        // Actualizar visualización con formato claro
        $('#dias-disponibles-calendar').text(diasDisponibles.toFixed(2));

        // Cambiar color según cantidad
        var $badgeDias = $('#dias-disponibles-calendar');
        $badgeDias.removeClass('badge-info badge-success badge-warning badge-danger');
        if (diasDisponibles >= 10) {
            $badgeDias.addClass('badge-success');
        } else if (diasDisponibles >= 5) {
            $badgeDias.addClass('badge-info');
        } else if (diasDisponibles > 0) {
            $badgeDias.addClass('badge-warning');
        } else {
            $badgeDias.addClass('badge-danger');
        }

        var html = '<table class="vacation-calendar">';
        html += '<thead><tr>';
        html += '<th>Dom</th><th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th>';
        html += '</tr></thead><tbody><tr>';

        // Días vacíos al inicio
        for (var i = 0; i < startingDayOfWeek; i++) {
            html += '<td class="empty"></td>';
        }

        // Días del mes
        for (var day = 1; day <= daysInMonth; day++) {
            var currentDate = new Date(year, month, day);
            currentDate.setHours(0, 0, 0, 0);
            var dayOfWeek = currentDate.getDay();
            var dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');

            var classes = [];
            var clickable = true;

            // Verificar si es fin de semana
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                classes.push('weekend');
                clickable = false;
            }
            // Verificar si es día pasado
            else if (currentDate < today) {
                classes.push('past');
                clickable = false;
            }
            // Día seleccionable
            else {
                classes.push('selectable');
            }

            // Verificar si está seleccionado
            if (selectedDates.indexOf(dateStr) !== -1) {
                classes.push('selected');
            }

            // Verificar si es hoy
            if (currentDate.getTime() === today.getTime()) {
                classes.push('current-day');
            }

            var classStr = classes.join(' ');
            var dataAttr = clickable ? ' data-date="' + dateStr + '"' : '';

            html += '<td class="' + classStr + '"' + dataAttr + '>' + day + '</td>';

            // Nueva fila cada domingo
            if ((startingDayOfWeek + day) % 7 === 0 && day < daysInMonth) {
                html += '</tr><tr>';
            }
        }

        // Completar última fila
        var totalCells = startingDayOfWeek + daysInMonth;
        var remainingCells = 7 - (totalCells % 7);
        if (remainingCells < 7) {
            for (var i = 0; i < remainingCells; i++) {
                html += '<td class="empty"></td>';
            }
        }

        html += '</tr></tbody></table>';

        $('#vacation-calendar').html(html);

        // Actualizar contador de días seleccionados
        updateSelectedDatesDisplay();
    }

    // Función para actualizar la visualización de días seleccionados
    function updateSelectedDatesDisplay() {
        $('#dias-seleccionados-display').text(selectedDates.length);

        if (selectedDates.length === 0) {
            $('#selected-dates-list').html('<em class="text-muted">Haga clic en los días del calendario para seleccionarlos</em>');
        } else {
            var html = '<div style="display: flex; flex-wrap: wrap; gap: 5px;">';
            selectedDates.sort().forEach(function (date) {
                html += '<span class="label label-success">' + date + ' <i class="fa fa-times" style="cursor:pointer;" data-remove-date="' + date + '"></i></span>';
            });
            html += '</div>';
            $('#selected-dates-list').html(html);
        }
    }

    // Exponer función para obtener fechas seleccionadas
    window.__vacaciones_get_selected_dates = function () {
        return selectedDates.slice(); // Retornar copia
    };

    // Botón solicitar vacaciones
    $('#btn-add-new-vacaciones').click(function () {
        if (!trabajadorId) {
            notify('warning', 'Error', 'No se ha identificado el trabajador', 3000);
            return;
        }

        $('#trabajador_id_vac').val(trabajadorId);
        selectedDates = [];

        // Inicializar calendario en mes actual
        var hoy = new Date();
        currentCalendarMonth = hoy.getMonth();
        currentCalendarYear = hoy.getFullYear();

        // Mostrar días base como referencia (opcional, ya que el cálculo es por mes)
        $('#dias-base-display').text('N/A');

        renderCalendar(currentCalendarMonth, currentCalendarYear);

        $('#modalPlanificacionVacaciones').modal('show');
    });

    // Navegación del calendario
    $(document).on('click', '#btn-prev-month', function () {
        currentCalendarMonth--;
        if (currentCalendarMonth < 0) {
            currentCalendarMonth = 11;
            currentCalendarYear--;
        }
        renderCalendar(currentCalendarMonth, currentCalendarYear);
    });

    $(document).on('click', '#btn-next-month', function () {
        currentCalendarMonth++;
        if (currentCalendarMonth > 11) {
            currentCalendarMonth = 0;
            currentCalendarYear++;
        }
        renderCalendar(currentCalendarMonth, currentCalendarYear);
    });

    // Selección de días en el calendario
    $(document).on('click', '.vacation-calendar td.selectable', function () {
        var dateStr = $(this).attr('data-date');
        if (!dateStr) return;

        var index = selectedDates.indexOf(dateStr);

        // Calcular días disponibles para el mes más lejano seleccionado
        var maxDisponibles = diasBaseActuales;
        if (selectedDates.length > 0 || index === -1) {
            var allDates = selectedDates.slice();
            if (index === -1) allDates.push(dateStr);

            // Encontrar el mes más lejano
            var maxMonth = -1;
            var maxYear = -1;
            allDates.forEach(function (d) {
                var parts = d.split('-');
                var y = parseInt(parts[0]);
                var m = parseInt(parts[1]) - 1;
                if (y > maxYear || (y === maxYear && m > maxMonth)) {
                    maxMonth = m;
                    maxYear = y;
                }
            });

            if (maxMonth !== -1) {
                maxDisponibles = calcularDiasDisponibles(maxMonth, maxYear);
            }
        }

        if (index === -1) {
            // Agregar día
            if (selectedDates.length >= maxDisponibles) {
                notify('warning', 'Límite alcanzado',
                    'No puede seleccionar más días. Días disponibles: ' + maxDisponibles.toFixed(2), 3000);
                return;
            }
            if (selectedDates.length >= MAX_DIAS_ANIO) {
                notify('warning', 'Límite anual',
                    'No puede seleccionar más de ' + MAX_DIAS_ANIO + ' días al año', 3000);
                return;
            }
            selectedDates.push(dateStr);
            $(this).addClass('selected');
        } else {
            // Quitar día
            selectedDates.splice(index, 1);
            $(this).removeClass('selected');
        }

        updateSelectedDatesDisplay();
    });

    // Remover día desde la lista
    $(document).on('click', '[data-remove-date]', function () {
        var dateStr = $(this).attr('data-remove-date');
        var index = selectedDates.indexOf(dateStr);
        if (index !== -1) {
            selectedDates.splice(index, 1);
            renderCalendar(currentCalendarMonth, currentCalendarYear);
        }
    });

    // Botón historial
    $('#btn-historial-vacaciones').click(function () {
        if (!trabajadorId) {
            notify('warning', 'Error', 'No se ha identificado el trabajador', 3000);
            return;
        }

        // Cargar historial completo
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: {
                module: 'vacaciones',
                action: 'api',
                method: 'list-historial',
                trabajador_id: trabajadorId
            },
            dataType: 'json',
            success: function (response) {
                if (response && Array.isArray(response)) {
                    $('#table-historial-vacaciones').bootstrapTable('load', response);
                    $('#modalHistorialVacaciones').modal('show');
                }
            }
        });
    });

    // Guardar vacaciones
    $('#btn-guardar-vacaciones').click(function () {
        var $btn = $(this);
        var fechaInicio = $('#fecha_inicio_vac').val();
        var fechaFin = $('#fecha_fin_vac').val();
        var selected_dates = [];

        if (typeof window.__vacaciones_get_selected_dates === 'function') {
            selected_dates = window.__vacaciones_get_selected_dates() || [];
        }

        // Validaciones
        if (!trabajadorId) {
            notify('warning', 'Error', 'No se ha identificado el trabajador', 3000);
            return;
        }

        if (!fechaInicio || !fechaFin) {
            notify('warning', 'Error', 'Debe seleccionar las fechas de inicio y fin', 3000);
            return;
        }

        // Se admite un solo día (fin == inicio); solo se rechaza si el fin es anterior.
        if (new Date(fechaFin) < new Date(fechaInicio)) {
            notify('warning', 'Error', 'La fecha de fin no puede ser anterior a la fecha de inicio', 3000);
            return;
        }

        var diasLaborables = 0;
        var diasString = '';

        if (selected_dates && selected_dates.length > 0) {
            diasString = selected_dates.join(',');
            diasLaborables = selected_dates.length;
            fechaInicio = selected_dates[0];
            fechaFin = selected_dates[selected_dates.length - 1];
        } else {
            diasLaborables = calcularDiasLaborables(fechaInicio, fechaFin);
            var arrDias = [];
            var cur = new Date(fechaInicio);
            var endd = new Date(fechaFin);
            cur.setHours(0, 0, 0, 0);
            endd.setHours(0, 0, 0, 0);

            for (var d = new Date(cur); d <= endd; d.setDate(d.getDate() + 1)) {
                var day = d.getDay();
                if (day === 0 || day === 6) continue;
                var y = d.getFullYear();
                var m = (d.getMonth() + 1).toString().padStart(2, '0');
                var da = d.getDate().toString().padStart(2, '0');
                arrDias.push(y + '-' + m + '-' + da);
            }
            diasString = arrDias.join(',');
        }

        // Un rango que solo cubre fin de semana no deja ningún día que solicitar.
        if (diasLaborables === 0) {
            notify('warning', 'Error', 'El rango seleccionado no contiene días laborables', 3000);
            return;
        }

        var textoDias = diasLaborables === 1 ? '1 día' : diasLaborables + ' días';
        var textoRango = (fechaInicio === fechaFin) ? 'el ' + fechaInicio : 'del ' + fechaInicio + ' al ' + fechaFin;
        if (!confirm('¿Desea solicitar ' + textoDias + ' de vacaciones (' + textoRango + ')?')) {
            return;
        }

        $btn.prop('disabled', true);
        $('#img-loading').removeClass('hidden');

        var formData = new FormData();
        formData.append('module', 'vacaciones');
        formData.append('action', 'api');
        formData.append('method', 'save');
        formData.append('trabajador_id', trabajadorId);
        formData.append('fecha_inicio', fechaInicio);
        formData.append('fecha_fin', fechaFin);
        if (diasString) formData.append('dias', diasString);

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 1) {
                    var mensaje = response.msg || 'Vacaciones guardadas correctamente';
                    if (response.dias_solicitados && response.dias_disponibles) {
                        var diasRestantes = response.dias_disponibles - response.dias_solicitados;
                        mensaje += '<br>Días solicitados: ' + response.dias_solicitados +
                            '<br>Días disponibles restantes: ' + diasRestantes;
                    }
                    notify('success', '¡Éxito!', mensaje, 5000);
                    $('#modalPlanificacionVacaciones').modal('hide');
                    $('#table-vacaciones').bootstrapTable('refresh');
                    cargarEstadisticas();
                } else {
                    notify('danger', 'Error', response.msg || 'Error al guardar las vacaciones', 5000);
                }
            },
            error: function (xhr) {
                var errorMsg = 'Error al comunicarse con el servidor';
                if (xhr.responseText) {
                    try {
                        var errorData = JSON.parse(xhr.responseText);
                        errorMsg = errorData.msg || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText;
                    }
                }
                notify('danger', 'Error', errorMsg, 5000);
            },
            complete: function () {
                $btn.prop('disabled', false);
                $('#img-loading').addClass('hidden');
            }
        });
    });

    // Ver días detallados
    $(document).on('click', '.ver-dias', function (e) {
        e.preventDefault();
        var diasEncoded = $(this).attr('data-dias') || '';
        var diasStr = '';
        try {
            diasStr = decodeURIComponent(diasEncoded);
        } catch (ex) {
            diasStr = diasEncoded;
        }
        var arr = diasStr ? diasStr.split(',') : [];
        var html = '<ul class="list-unstyled" style="column-count: 2;">';
        if (arr.length === 0) {
            html += '<li>No hay días registrados</li>';
        } else {
            arr.forEach(function (d) {
                html += '<li><i class="fa fa-calendar-o"></i> ' + d + '</li>';
            });
        }
        html += '</ul>';

        var messageHtml = '<div><strong>Días del Plan de Vacaciones:</strong></div>' + html;
        notify('info', 'Días de vacaciones', messageHtml, 10000);
    });

    // Aprobar vacación
    $(document).on('click', '.btn-aprobar-vacacion', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var vacacionId = $btn.data('id');
        var diasSolicitados = $btn.data('dias');

        if (!confirm('¿Está seguro de aprobar esta solicitud de ' + diasSolicitados + ' días de vacaciones?\n\n' +
            'Esta acción descontará:\n' +
            '- Los días del saldo disponible\n' +
            '- El equivalente en salario acumulado')) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: {
                module: 'vacaciones',
                action: 'api',
                method: 'aprobar',
                id: vacacionId,
                trabajador_id: trabajadorId
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 1) {
                    var mensaje = response.msg || 'Vacación aprobada correctamente';
                    if (response.dias_descontados && response.dias_restantes !== undefined) {
                        mensaje += '<br><strong>Días descontados:</strong> ' + response.dias_descontados +
                            '<br><strong>Días restantes:</strong> ' + response.dias_restantes;
                    }
                    notify('success', '¡Aprobado!', mensaje, 6000);
                    $('#table-vacaciones').bootstrapTable('refresh');
                    cargarEstadisticas();
                } else {
                    notify('danger', 'Error', response.msg || 'No se pudo aprobar la vacación', 5000);
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Aprobar');
                }
            },
            error: function (xhr) {
                var errorMsg = 'Error al comunicarse con el servidor';
                if (xhr.responseText) {
                    try {
                        var errorData = JSON.parse(xhr.responseText);
                        errorMsg = errorData.msg || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText;
                    }
                }
                notify('danger', 'Error', errorMsg, 5000);
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Aprobar');
            }
        });
    });

    // Rechazar vacación
    $(document).on('click', '.btn-rechazar-vacacion', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('¿Desea rechazar esta solicitud de vacaciones?')) return;

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: {
                module: 'vacaciones',
                action: 'api',
                method: 'rechazar',
                id: id,
                trabajador_id: trabajadorId
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 1) {
                    notify('success', 'Éxito', 'Solicitud rechazada correctamente');
                    $('#table-vacaciones').bootstrapTable('refresh');
                    cargarEstadisticas();
                } else {
                    notify('danger', 'Error', 'Error al rechazar solicitud');
                }
            }
        });
    });

    // Eliminar vacación
    $(document).on('click', '.btn-eliminar-vacacion', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var fechaInicioStr = $(this).data('fecha-inicio');

        // Validación: no permitir eliminar si ya inició
        if (fechaInicioStr) {
            try {
                var hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                var partes = ('' + fechaInicioStr).split('-');
                if (partes.length === 3) {
                    var fechaInicio = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
                    if (fechaInicio < hoy) {
                        notify('warning', 'No permitido', 'No se puede eliminar un plan de vacaciones que ya inició.');
                        return;
                    }
                }
            } catch (ex) { }
        }

        if (!confirm('¿Desea eliminar este registro de vacaciones?')) return;

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: {
                module: 'vacaciones',
                action: 'api',
                method: 'del',
                id: id
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 1) {
                    notify('success', 'Éxito', 'Registro eliminado correctamente');
                    $('#table-vacaciones').bootstrapTable('refresh');
                    cargarEstadisticas();
                } else {
                    notify('danger', 'Error', 'Error al eliminar registro');
                }
            }
        });
    });

    // Actualizar tabla cada 30 segundos
    setInterval(function () {
        if ($('#table-vacaciones').length) {
            $('#table-vacaciones').bootstrapTable('refresh', { silent: true });
        }
    }, 30000);
});
