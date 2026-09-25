$(function () {
    var guardandoMasivo = false; // Variable para controlar ejecuciones múltiples
    var paginaActual = 1;
    var registrosPorPagina = 10;
    var totalRegistros = 0;
    var datosCompletos = null;

    // Registrar helpers de Handlebars
    Handlebars.registerHelper('add', function (value1, value2) {
        return parseInt(value1 || 0) + parseInt(value2 || 0);
    });

    Handlebars.registerHelper('promedio_aspecto', function (trabajador, aspecto_id) {
        var total = 0;
        var count = 0;

        if (trabajador && trabajador.subaspectos) {
            trabajador.subaspectos.forEach(function (subaspecto) {
                // Necesitamos filtrar por aspecto_id, pero como no tenemos esa relación directa,
                // calcularemos el promedio general por ahora
                if (subaspecto.calificacion) {
                    total += parseInt(subaspecto.calificacion);
                    count++;
                }
            });
        }

        return count > 0 ? Math.round(total / count) : '-';
    });

    Handlebars.registerHelper('calcular_porcentaje', function (calificacion, calificacion_max) {
        if (!calificacion || !calificacion_max || calificacion_max == 0) return 0;
        return Math.round((calificacion / calificacion_max) * 100);
    });

    Handlebars.registerHelper('color_porcentaje', function (calificacion, calificacion_max) {
        if (!calificacion || !calificacion_max || calificacion_max == 0) return '';

        var porcentaje = (calificacion / calificacion_max) * 100;

        if (porcentaje < 60) return 'text-danger font-weight-bold';        // Rojo (0-60)
        if (porcentaje >= 60 && porcentaje < 85) return 'text-orange font-weight-bold';  // Naranja (60-85)
        if (porcentaje >= 85 && porcentaje < 95) return 'text-yellow font-weight-bold';  // Amarillo (85-95)
        return 'text-success font-weight-bold';     // Verde (95-100)
    });

    Handlebars.registerHelper('color_total_aspecto', function (total_calificaciones, calificacion_max) {
        if (!total_calificaciones || !calificacion_max || calificacion_max == 0) return '';

        var porcentaje = (total_calificaciones / calificacion_max) * 100;

        if (porcentaje < 60) return 'bg-danger text-white';        // Rojo (0-60)
        if (porcentaje >= 60 && porcentaje < 85) return 'bg-orange text-white';  // Naranja (60-85)
        if (porcentaje >= 85 && porcentaje < 95) return 'bg-yellow';     // Amarillo (85-95)
        return 'bg-success text-white';     // Verde (95-100)
    });

    Handlebars.registerHelper('color_evaluacion_final', function (trabajador) {
        var total = 0;

        if (trabajador && trabajador.aspectos_agrupados) {
            trabajador.aspectos_agrupados.forEach(function (aspecto) {
                if (aspecto.total_calificaciones !== null && aspecto.total_calificaciones !== undefined) {
                    total += parseInt(aspecto.total_calificaciones);
                }
            });
        }

        // La evaluación final se mide sobre 100 (suma total de todos los aspectos)
        var porcentaje = total;

        if (porcentaje < 60) return 'bg-danger text-white';        // Rojo (0-60)
        if (porcentaje >= 60 && porcentaje < 85) return 'bg-orange text-white';  // Naranja (60-85)
        if (porcentaje >= 85 && porcentaje < 95) return 'bg-yellow';     // Amarillo (85-95)
        return 'bg-success text-white';     // Verde (95-100)
    });

    Handlebars.registerHelper('texto_evaluacion_final', function (trabajador) {
        var total = 0;

        if (trabajador && trabajador.aspectos_agrupados) {
            trabajador.aspectos_agrupados.forEach(function (aspecto) {
                if (aspecto.total_calificaciones !== null && aspecto.total_calificaciones !== undefined) {
                    total += parseInt(aspecto.total_calificaciones);
                }
            });
        }

        // La evaluación final se mide sobre 100 (suma total de todos los aspectos)
        if (total < 60) return 'Mal';
        if (total >= 60 && total < 85) return 'Regular';
        if (total >= 85 && total < 95) return 'Bien';
        return 'Muy Bien';
    });

    Handlebars.registerHelper('evaluacion_final', function (trabajador) {
        var total = 0;

        if (trabajador && trabajador.aspectos_agrupados) {
            trabajador.aspectos_agrupados.forEach(function (aspecto) {
                if (aspecto.total_calificaciones !== null && aspecto.total_calificaciones !== undefined) {
                    total += parseInt(aspecto.total_calificaciones);
                }
            });
        }

        return total > 0 ? total : '-';
    });

    Handlebars.registerHelper('lookup', function (obj, field) {
        if (!obj || !obj[field]) return '';
        return obj[field];
    });

    function notify(type, title, message, timer) {
        if ($.niftyNoty) {
            $.niftyNoty({ type: type || 'info', container: 'floating', title: title || '', message: message || '', timer: timer != null ? timer : 3000, closeBtn: true, focus: true });
        } else { alert((title ? title + ': ' : '') + message); }
    }

    function showAlert(type, message) {
        $('#alert-container').html('<div class="alert alert-' + type + ' alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>');
    }

    function cargarEvaluaciones(mes, cargoId, pagina = 1) {
        $('#tabla-container').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando evaluaciones...</div>');

        var requestData = {
            module: 'evaluaciones',
            method: 'list',
            mes: mes,
            pagina: pagina,
            limit: registrosPorPagina
        };

        if (cargoId && cargoId !== 'all') {
            requestData.cargo_id = cargoId;
        }

        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: requestData,
            dataType: 'json',
            success: function (response) {
                if (response && response.data) {
                    datosCompletos = response;
                    totalRegistros = response.total || response.data.length;
                    paginaActual = pagina;
                    renderTabla(response);
                    renderPaginacion();
                } else {
                    showAlert('warning', 'No se encontraron datos para el mes seleccionado');
                    $('#tabla-container').html('<div class="text-center text-muted">No hay datos disponibles</div>');
                    $('#paginacion-container').html('');
                }
            },
            error: function (xhr) {
                showAlert('danger', 'Error al cargar las evaluaciones: ' + (xhr.responseText || 'Error de conexión'));
                $('#tabla-container').html('<div class="text-center text-danger">Error al cargar los datos</div>');
                $('#paginacion-container').html('');
            }
        });
    }

    function getClaseBgPorcentaje(porcentaje) {
        if (porcentaje < 60) return 'bg-danger text-white';
        if (porcentaje >= 60 && porcentaje < 85) return 'bg-orange text-white';
        if (porcentaje >= 85 && porcentaje < 95) return 'bg-yellow';
        return 'bg-success text-white';
    }

    function getTextoEvaluacionFinal(total) {
        if (total < 60) return 'Mal';
        if (total >= 60 && total < 85) return 'Regular';
        if (total >= 85 && total < 95) return 'Bien';
        return 'Muy Bien';
    }

    function actualizarDependientesFila(trabajadorId) {
        var $fila = $('#tabla-evaluaciones').find('tr[data-trabajador-id="' + trabajadorId + '"]');
        if ($fila.length === 0) return;

        var totalFinal = 0;

        $fila.find('td.aspecto-total').each(function () {
            var $cell = $(this);
            var aspectoId = $cell.data('aspecto-id');
            var maxAspecto = parseInt($cell.data('calificacion-max')) || 0;

            var totalAspecto = 0;
            $fila.find('input.input-calificacion[data-aspecto-id="' + aspectoId + '"]').each(function () {
                totalAspecto += (parseInt($(this).val()) || 0);
            });

            totalFinal += totalAspecto;

            var $badge = $cell.find('.total-aspecto-badge');
            if ($badge.length === 0) return;

            if (totalAspecto > 0 && maxAspecto > 0) {
                var porcentaje = (totalAspecto / maxAspecto) * 100;
                $badge.removeClass('bg-danger bg-warning bg-success bg-info bg-orange bg-yellow text-white badge-secondary');
                $badge.addClass(getClaseBgPorcentaje(porcentaje));
                $badge.text(totalAspecto);
            } else {
                $badge.removeClass('bg-danger bg-warning bg-success bg-info bg-orange bg-yellow text-white');
                $badge.addClass('badge-secondary');
                $badge.text('-');
            }
        });

        var $evalCell = $fila.find('td.evaluacion-final');
        if ($evalCell.length) {
            $evalCell.removeClass('bg-danger bg-warning bg-success bg-info bg-orange bg-yellow text-white');
            if (totalFinal > 0) {
                $evalCell.addClass(getClaseBgPorcentaje(totalFinal));
                $evalCell.find('.evaluacion-final-num').text(totalFinal);
                $evalCell.find('.evaluacion-final-text').text(getTextoEvaluacionFinal(totalFinal));
            } else {
                $evalCell.find('.evaluacion-final-num').text('-');
                $evalCell.find('.evaluacion-final-text').text('');
            }
        }
    }

    function renderTabla(data) {
        var template = $('#tabla-evaluaciones-template').html();
        var compiledTemplate = Handlebars.compile(template);
        var html = compiledTemplate(data);
        $('#tabla-container').html(html);

        // Eventos para los inputs de calificación
        $('.input-calificacion').on('change', function () {
            var $input = $(this);
            var trabajadorId = $input.data('trabajador-id');
            var subaspectoId = $input.data('subaspecto-id');
            var calificacion = parseInt($input.val()) || 0;
            var calificacionMax = parseInt($input.data('calificacion-max')) || 100;

            // Validar rango según calificación_max del subaspecto
            if (calificacion < 0 || calificacion > calificacionMax) {
                $input.val('');
                notify('danger', 'Error', 'La calificación debe estar entre 0 y ' + calificacionMax, 3000);
                return;
            }

            // Guardar automáticamente
            actualizarDependientesFila(trabajadorId);
            guardarEvaluacion(trabajadorId, subaspectoId, calificacion, data.mes);
        });
    }

    function renderPaginacion() {
        var totalPaginas = Math.ceil(totalRegistros / registrosPorPagina);

        if (totalPaginas <= 1) {
            $('#paginacion-container').html('');
            return;
        }

        var paginacionHtml = '<nav><ul class="pagination justify-content-center">';

        // Botón anterior
        if (paginaActual > 1) {
            paginacionHtml += '<li class="page-item"><a class="page-link" href="#" data-pagina="' + (paginaActual - 1) + '">Anterior</a></li>';
        } else {
            paginacionHtml += '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
        }

        // Páginas
        var inicio = Math.max(1, paginaActual - 2);
        var fin = Math.min(totalPaginas, paginaActual + 2);

        if (inicio > 1) {
            paginacionHtml += '<li class="page-item"><a class="page-link" href="#" data-pagina="1">1</a></li>';
            if (inicio > 2) {
                paginacionHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for (var i = inicio; i <= fin; i++) {
            if (i === paginaActual) {
                paginacionHtml += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
            } else {
                paginacionHtml += '<li class="page-item"><a class="page-link" href="#" data-pagina="' + i + '">' + i + '</a></li>';
            }
        }

        if (fin < totalPaginas) {
            if (fin < totalPaginas - 1) {
                paginacionHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            paginacionHtml += '<li class="page-item"><a class="page-link" href="#" data-pagina="' + totalPaginas + '">' + totalPaginas + '</a></li>';
        }

        // Botón siguiente
        if (paginaActual < totalPaginas) {
            paginacionHtml += '<li class="page-item"><a class="page-link" href="#" data-pagina="' + (paginaActual + 1) + '">Siguiente</a></li>';
        } else {
            paginacionHtml += '<li class="page-item disabled"><span class="page-link">Siguiente</span></li>';
        }

        paginacionHtml += '</ul></nav>';

        // Información de registros
        paginacionHtml += '<div class="text-center text-muted mb-3">';
        paginacionHtml += 'Mostrando ' + ((paginaActual - 1) * registrosPorPagina + 1) + ' - ' + Math.min(paginaActual * registrosPorPagina, totalRegistros) + ' de ' + totalRegistros + ' registros';
        paginacionHtml += '</div>';

        $('#paginacion-container').html(paginacionHtml);

        // Eventos de paginación
        $('.pagination a[data-pagina]').on('click', function (e) {
            e.preventDefault();
            var pagina = parseInt($(this).data('pagina'));
            var mes = $('#mes-selector').val();
            var cargoId = $('#cargo-selector').val();
            cargarEvaluaciones(mes, cargoId, pagina);
        });
    }

    // Evento para guardar todo (registrado solo una vez)
    $('#btn-guardar-todo').on('click', function () {
        if (guardandoMasivo) {
            notify('warning', 'Advertencia', 'Ya se está guardando las evaluaciones. Por favor espere...', 3000);
            return;
        }
        var mes = $('#mes-selector').val();
        guardarTodasEvaluaciones(mes);
    });

    function guardarEvaluacion(trabajadorId, subaspectoId, calificacion, mes) {
        var datos = {
            module: 'evaluaciones',
            method: 'save',
            trabajador_id: trabajadorId,
            subaspecto_id: subaspectoId,
            calificacion: calificacion,
            mes: mes
        };

        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function (response) {
                if (response.status == 1) {
                    notify('success', 'Éxito', response.msg, 2000);
                } else {
                    notify('danger', 'Error', response.msg, 4000);
                }
            },
            error: function (xhr, status, error) {
                notify('danger', 'Error', 'Error al guardar la evaluación: ' + error, 4000);
            }
        });
    }

    function guardarTodasEvaluaciones(mes) {
        if (guardandoMasivo) {
            return; // Ya se está ejecutando
        }

        guardandoMasivo = true; // Marcar como en ejecución

        var $btn = $('#btn-guardar-todo');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        var evaluaciones = [];
        $('.input-calificacion').each(function () {
            var $input = $(this);
            var calificacion = parseInt($input.val()) || 0;
            var calificacionMax = parseInt($input.data('calificacion-max')) || 100;

            // Validar que esté dentro del rango permitido
            if (calificacion >= 0 && calificacion <= calificacionMax) {
                evaluaciones.push({
                    trabajador_id: $input.data('trabajador-id'),
                    subaspecto_id: $input.data('subaspecto-id'),
                    calificacion: calificacion
                });
            }
        });

        if (evaluaciones.length === 0) {
            guardandoMasivo = false; // Resetear variable de control
            notify('warning', 'Advertencia', 'No hay calificaciones válidas para guardar', 3000);
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Todo');
            return;
        }

        // Enviar todas las evaluaciones en una sola petición
        $.ajax({
            url: 'api-app.php',
            type: 'POST',
            data: {
                module: 'evaluaciones',
                method: 'save_batch',
                mes: mes,
                evaluaciones: JSON.stringify(evaluaciones)
            },
            dataType: 'json',
            success: function (response) {
                guardandoMasivo = false; // Resetear variable de control
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Todo');

                if (response.status == 1) {
                    notify('success', 'Éxito', response.msg, 3000);
                    // Recargar para actualizar los promedios
                    setTimeout(function () {
                        cargarEvaluaciones(mes);
                    }, 500);
                } else {
                    notify('danger', 'Error', response.msg, 4000);
                }
            },
            error: function (xhr, status, error) {
                guardandoMasivo = false; // Resetear variable de control
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Todo');
                notify('danger', 'Error', 'Error al guardar las evaluaciones: ' + error, 4000);
            }
        });
    }

    // Eventos principales
    $('#btn-cargar').on('click', function () {
        var mes = $('#mes-selector').val();
        var cargoId = $('#cargo-selector').val();
        paginaActual = 1; // Resetear a la primera página
        cargarEvaluaciones(mes, cargoId, 1);
    });

    $('#btn-exportar-excel').on('click', function () {
        var mes = $('#mes-selector').val();
        var cargoId = $('#cargo-selector').val();
        var url = 'api-app.php?module=evaluaciones&method=export_excel&mes=' + encodeURIComponent(mes);
        if (cargoId && cargoId !== 'all') {
            url += '&cargo_id=' + encodeURIComponent(cargoId);
        }
        window.location.href = url;
    });

    $('#mes-selector').on('change', function () {
        var mes = $(this).val();
        var cargoId = $('#cargo-selector').val();
        paginaActual = 1; // Resetear a la primera página
        cargarEvaluaciones(mes, cargoId, 1);
    });

    $('#cargo-selector').on('change', function () {
        var mes = $('#mes-selector').val();
        var cargoId = $(this).val();
        paginaActual = 1; // Resetear a la primera página
        cargarEvaluaciones(mes, cargoId, 1);
    });

    // Cargar evaluaciones del mes actual al iniciar
    cargarEvaluaciones(mesActual, 'all', 1);
});

// --- Modal de vista previa de foto al pasar el mouse (list-evaluaciones) ---
(function () {
    var css = '\n'
        + '.lee-photo-modal-overlay { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 2000; pointer-events: none; }\n'
        + '.lee-photo-modal { pointer-events: auto; background: rgba(255,255,255,1); padding: 8px; border-radius: 6px; box-shadow: 0 8px 30px rgba(0,0,0,0.45); transition: opacity 220ms ease, transform 220ms ease; opacity: 0; transform: scale(0.96); }\n'
        + '.lee-photo-modal.show { opacity: 1; transform: scale(1); }\n'
        + '.lee-photo-modal img { display:block; max-width: 640px; max-height: 640px; width: auto; height: auto; border-radius:4px; }\n';

    var style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    var overlay = document.createElement('div');
    overlay.className = 'lee-photo-modal-overlay';
    overlay.style.display = 'none';

    var modal = document.createElement('div');
    modal.className = 'lee-photo-modal';
    var img = document.createElement('img');
    img.alt = 'Foto ampliada';
    modal.appendChild(img);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    var hideTimer = null;

    function showModal(src) {
        if (!src) return;
        img.src = src;
        overlay.style.display = 'flex';
        void modal.offsetWidth;
        modal.classList.add('show');
    }

    function hideModal() {
        modal.classList.remove('show');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () { overlay.style.display = 'none'; img.src = ''; }, 240);
    }

    // Delegated listeners: images generated in the evaluaciones table have class img-thumbnail
    document.addEventListener('mouseover', function (e) {
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && (t.closest('#tabla-evaluaciones') || t.classList.contains('img-thumbnail'))) {
            var src = t.getAttribute('src');
            if (src) {
                clearTimeout(hideTimer);
                showModal(src);
            }
        }
    });

    document.addEventListener('mouseout', function (e) {
        var from = e.relatedTarget || e.toElement;
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && (t.closest('#tabla-evaluaciones') || t.classList.contains('img-thumbnail'))) {
            if (!from || !(from === modal || modal.contains(from))) {
                hideModal();
            }
        }
    });

    overlay.addEventListener('mouseleave', function () { hideModal(); });
    overlay.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
})();
