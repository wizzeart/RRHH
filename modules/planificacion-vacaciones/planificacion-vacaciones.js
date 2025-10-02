$(document).ready(function() {
    var calendarEl = document.getElementById('calendar');
    var selectedDates = []; // Almacenará las fechas seleccionadas
    var vacaciones_planificadas = [];
    var calendar;

    // Inicializar el calendario
    function initCalendar() {
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            selectable: true,
            
            eventClick: function(info) {
                // Eliminar fecha al hacer clic o tocar un evento
                var dateStr = info.event.start.toISOString().split('T')[0];
                selectedDates = selectedDates.filter(d => d !== dateStr);
                updateCalendarEvents();
                return false; // Previene el comportamiento por defecto
            },
            // Manejar clic en días del calendario
            dateClick: function(info) {
                // Verificar si la fecha es anterior a hoy
                const clickedDate = new Date(info.dateStr);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (clickedDate < today) {
                    notify('warning', 'Fecha no válida', 'No se pueden seleccionar fechas pasadas', 3000);
                    return;
                }
                
                // Verificar si ya hay un evento en esta fecha
                const eventosEnFecha = calendar.getEvents().filter(event => {
                    const eventDate = event.start ? event.start.toISOString().split('T')[0] : null;
                    return eventDate === info.dateStr;
                });

                // Si hay eventos y alguno es de vacaciones planificadas, no hacer nada
                if (eventosEnFecha.some(event => event.title === 'Planificadas')) {
                    return;
                }

                var dateStr = info.dateStr;
                var index = selectedDates.indexOf(dateStr);
                
                if (index === -1) {
                    // Si la fecha no está seleccionada, la agregamos
                    selectedDates.push(dateStr);
                } else {
                    // Si la fecha ya está seleccionada, la quitamos
                    selectedDates.splice(index, 1);
                }
                
                updateCalendarEvents();
            },
            // Mejorar la interacción táctil
            eventDidMount: function(info) {
                info.el.style.cursor = 'pointer';
                info.el.style.userSelect = 'none';
                info.el.style.webkitTapHighlightColor = 'transparent';
            }
        });
        
        // Inicializar el calendario
        calendar.render();
        return calendar;
    }

    // Actualizar eventos del calendario
    function updateCalendarEvents() {
        // Eliminar solo los eventos de vacaciones seleccionadas (no los planificados)
        calendar.getEvents().forEach(event => {
            // Solo eliminar si es un evento de vacaciones seleccionadas (no planificadas)
            if (event.title === 'Vacaciones') {
                event.remove();
            }
        });
        
        // Agregar nuevos eventos de vacaciones seleccionadas
        selectedDates.forEach(dateStr => {
            calendar.addEvent({
                title: 'Vacaciones',
                start: dateStr,
                allDay: true,
                backgroundColor: '#5cb85c',
                borderColor: '#4cae4c'
            });
        });
    }

    // Mostrar resumen de fechas seleccionadas
    function showConfirmationModal() {
        if (selectedDates.length === 0) {
            alert('Por favor seleccione al menos un día de vacaciones.');
            return;
        }

        var datesList = selectedDates
            .sort()
            .map(date => {
                var d = new Date(date);
                return '<li>' + d.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                }) + '</li>';
            })
            .join('');

        $('#dias-seleccionados').html('<ul class="list-unstyled">' + datesList + '</ul>');
        $('#modalConfirmacion').modal('show');
    }

    // Evento para el botón de guardar
    $('#btn-guardar').click(showConfirmationModal);

    // Evento para confirmar la solicitud
    $('#confirmar-solicitud').click(function() {
        if (selectedDates.length === 0) {
            alert('No hay días seleccionados para guardar.');
            return;
        }

        // Mostrar indicador de carga
        var $btn = $(this).button('loading');

        var formData = new FormData();
        formData.append('module', 'planificacion-vacaciones');
        formData.append('method', 'save');
        formData.append('trabajador_id', trabajadorId);
        formData.append('fechas', selectedDates);

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
                    alert('Solicitud de vacaciones guardada correctamente.');
                    $('#modalConfirmacion').modal('hide');
                    selectedDates = [];
                    updateCalendarEvents();
                } else {
                    alert('Error al guardar la solicitud: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                //$('#img-loading').addClass('hidden');
               // $('#btn-save').attr('disabled', false);
                var response = XMLHttpRequest && XMLHttpRequest.responseText ? XMLHttpRequest.responseText : (errorThrown || textStatus || 'Error desconocido');
                notify('danger', 'Error al guardar', response, 5000);
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
                                durationEditable: false
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