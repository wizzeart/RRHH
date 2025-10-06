$(document).ready(function() {
    var calendarEl = document.getElementById('calendar');
    var selectedPeriods = []; // Almacenará los períodos de vacaciones seleccionados
    var vacaciones_planificadas = [];
    var calendar;

    // Inicializar el calendario
    function initCalendar() {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es-CU',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            selectable: false, // Desactivar selección directa en el calendario
            
            eventClick: function(info) {
                // Eliminar período al hacer clic en un evento
                if (confirm('¿Desea eliminar este período de vacaciones?')) {
                    const eventId = info.event.id;
                    selectedPeriods = selectedPeriods.filter(p => p.id !== eventId);
                    updateCalendarEvents();
                }
                return false; // Previene el comportamiento por defecto
            },
            
            // Mejorar la interacción táctil
            eventDidMount: function(info) {
                info.el.style.cursor = 'pointer';
                info.el.style.userSelect = 'none';
                info.el.style.webkitTapHighlightColor = 'transparent';
            }
        });
        
        // Inicializar el calendatorio
        calendar.render();
        
        // Configurar el formulario para agregar vacaciones
        $('#vacation-form').on('submit', function(e) {
            e.preventDefault();
            
            // Crear fechas sin considerar la hora local
            const fechaInicioStr = $('#fecha_inicio').val();
            const fechaFinStr = $('#fecha_fin').val();
            
            if (!fechaInicioStr || !fechaFinStr) {
                notify('warning', 'Error', 'Por favor complete ambas fechas', 3000);
                return;
            }
            
            // Crear fechas en formato YYYY-MM-DD sin ajuste de zona horaria
            const fechaInicio = new Date(fechaInicioStr + 'T00:00:00');
            const fechaFin = new Date(fechaFinStr + 'T00:00:00');
            
            // Fecha actual sin considerar la hora
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            // Validaciones
            if (!fechaInicio || !fechaFin) {
                notify('warning', 'Error', 'Por favor complete ambas fechas', 3000);
                return;
            }
            
            if (fechaInicio < hoy) {
                notify('warning', 'Fecha no válida', 'La fecha de inicio no puede ser anterior a hoy', 3000);
                return;
            }
            
            if (fechaFin < fechaInicio) {
                notify('warning', 'Error', 'La fecha de fin no puede ser anterior a la fecha de inicio', 3000);
                return;
            }
            
            // Crear un ID único para este período
            const periodoId = 'periodo-' + Date.now();
            
            // Agregar el período a la lista
            selectedPeriods.push({
                id: periodoId,
                start: fechaInicio,
                end: fechaFin,
                title: 'Vacaciones Solicitadas',
                color: '#5cb85c'
            });
            
            // Actualizar el calendario
            updateCalendarEvents();
            
            // Limpiar el formulario
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
        });
        
        return calendar;
    }
    
    // Actualizar eventos del calendario
    function updateCalendarEvents() {
        // Eliminar todos los eventos de vacaciones seleccionadas (no los planificados)
        calendar.getEvents().forEach(event => {
            if (event.extendedProps && (event.extendedProps.tipo === 'seleccionado')) {
                event.remove();
            }
        });
        
        // Agregar los períodos de vacaciones seleccionados
        selectedPeriods.forEach(periodo => {
            calendar.addEvent({
                id: periodo.id,
                title: periodo.title,
                start: periodo.start,
                end: new Date(periodo.end.getTime() + 24 * 60 * 60 * 1000), // Añadir un día para incluir el último día
                allDay: true,
                backgroundColor: periodo.color || '#5cb85c',
                borderColor: '#4cae4c',
                extendedProps: {
                    tipo: 'seleccionado'
                }
            });
        });
        
        // Actualizar el resumen de días seleccionados
        updateSelectedDaysSummary();
    }

    // Función para actualizar el resumen de días seleccionados
    function updateSelectedDaysSummary() {
        const allDates = [];
        
        // Obtener todos los días de los períodos seleccionados
        selectedPeriods.forEach(periodo => {
            const currentDate = new Date(periodo.start);
            const endDate = new Date(periodo.end);
            
            while (currentDate <= endDate) {
                // No incluir fines de semana
                if (currentDate.getDay() !== 0 && currentDate.getDay() !== 6) {
                    allDates.push(new Date(currentDate));
                }
                currentDate.setDate(currentDate.getDate()+1);
            }
        });
        
        // Ordenar fechas
        allDates.sort((a, b) => a - b);
        
        // Formatear fechas para mostrar
        const formattedDates = allDates.map(date => {
            return '<li>' + date.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            }) + '</li>';
        }).join('');
        
        // Mostrar resumen
        const summaryElement = $('#dias-seleccionados');
        if (formattedDates) {
            summaryElement.html('<p>Días laborables seleccionados:</p><ul class="list-unstyled">' + formattedDates + '</ul>');
        } else {
            summaryElement.html('<p>No hay días laborables seleccionados en el rango especificado.</p>');
        }
    }

    // Mostrar resumen de fechas seleccionadas
    function showConfirmationModal() {
        if (selectedPeriods.length === 0) {
            notify('warning', 'Atención', 'Por favor agregue al menos un período de vacaciones.', 3000);
            return;
        }
        
        // Actualizar el resumen antes de mostrar el modal
        updateSelectedDaysSummary();
        $('#modalConfirmacion').modal('show');
    }

    // Evento para el botón de guardar
    $('#btn-guardar').click(showConfirmationModal);

    // Evento para confirmar la solicitud
    $('#confirmar-solicitud').click(function() {
        if (selectedPeriods.length === 0) {
            notify('warning', 'Atención', 'No hay períodos de vacaciones para guardar.', 3000);
            return;
        }

        // Mostrar indicador de carga
        const $btn = $(this).button('loading');

        // Preparar las fechas para enviar al servidor
        const allDates = [];
        
        // Obtener todos los días laborables de los períodos seleccionados
        selectedPeriods.forEach(periodo => {
            // Crear fechas sin hora para evitar problemas de zona horaria
            const startDate = new Date(periodo.start);
            const endDate = new Date(periodo.end);
            
            // Asegurarse de que estamos trabajando con la fecha correcta
            startDate.setHours(12, 0, 0, 0); // Establecer al mediodía para evitar cambios de fecha
            endDate.setHours(12, 0, 0, 0);
            
            const currentDate = new Date(startDate);
            
            while (currentDate <= endDate) {
                // No incluir fines de semana
                if (currentDate.getDay() !== 0 && currentDate.getDay() !== 6) {
                    // Formatear como YYYY-MM-DD
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    allDates.push(`${year}-${month}-${day}`);
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
        });
        
        if (allDates.length === 0) {
            notify('warning', 'Atención', 'No hay días laborables en los períodos seleccionados.', 3000);
            $btn.button('reset');
            return;
        }

        const formData = new FormData();
        
        formData.append('module', 'planificacion-vacaciones');
        formData.append('method', 'save');
        formData.append('trabajador_id', trabajadorId);
        formData.append('fechas', allDates);
        // Formatear fechas como YYYY-MM-DD
        const formatDate = (date) => {
            const d = new Date(date);
            d.setHours(12, 0, 0, 0); // Establecer al mediodía para evitar cambios de fecha
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        
        formData.append('fecha_inicio', formatDate(selectedPeriods[0].start));
        formData.append('fecha_fin', formatDate(selectedPeriods[selectedPeriods.length - 1].end));
        // Enviar solicitud al servidor
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 1) {
                    notify('success', 'Éxito', 'Solicitud de vacaciones guardada correctamente.', 5000);
                    $('#modalConfirmacion').modal('hide');
                    selectedPeriods = [];
                    updateCalendarEvents();
                    
                    // Recargar las vacaciones planificadas
                    cargarVacacionesPlanificadas();
                } else {
                    const errorMsg = response.message || 'Error desconocido al guardar la solicitud';
                    notify('danger', 'Error', errorMsg, 5000);
                }
            },
            error: function(xhr, status, error) {
                const response = xhr.responseText || error || 'Error en la conexión';
                notify('danger', 'Error', response, 5000);
            },
            complete: function() {
                $btn.button('reset');
            }
        });
    });

    // Cargar vacaciones planificadas al iniciar
    function cargarVacacionesPlanificadas() {
        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: {
                module: 'planificacion-vacaciones',
                method: 'list-id',
                trabajador_id: trabajadorId
            },
            dataType: 'json',
            success: function(response) {
                if (response) {
                    // Almacenar las vacaciones planificadas
                    vacaciones_planificadas = response || [];
                    
                    // Agregar eventos al calendario
                    vacaciones_planificadas.forEach(function(vacacion) {
                        vacacion.dias = vacacion.dias.split(',');
                        vacacion.dias.forEach(function(item) {
                            calendar.addEvent({
                                title: 'Planificadas',
                                start: item.trim(),
                                allDay: true,
                                className: 'fc-event-planificadas',
                                backgroundColor: '#5bc0de',
                                borderColor: '#46b8da',
                                editable: false,
                                startEditable: false,
                                durationEditable: false,
                                extendedProps: {
                                    tipo: 'planificadas'
                                }
                            });
                        });
                    });
                } else {
                    console.error('Error al cargar vacaciones planificadas:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', status, error);
            }
        });
    }

    // Inicializar el calendario
    initCalendar();
    
    // Cargar las vacaciones planificadas después de inicializar el calendario
    cargarVacacionesPlanificadas();
    // Redimensionar el calendario cuando se abra/cierre el menú lateral
    $(document).on('click' , '.tgl-menu-btn', function() {
        setTimeout(function() {
            calendar.render();
        }, 300); // Pequeño retraso para que termine la animación del menú
    });
});