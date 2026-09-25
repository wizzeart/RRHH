$(document).ready(function () {
    // Initialize table reference
    var $table = $('#table-panel');
    var searchTimeout;

    // Function to search workers
    function buscarTrabajadores(termino) {
        if (termino.length < 2) {
            $('#resultados-busqueda').hide().empty();
            return;
        }

        // Cancel previous search if exists
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Set new timeout for search
        searchTimeout = setTimeout(function () {
            $.ajax({
                url: 'api-app.php',
                type: 'GET',
                data: {
                    module: 'trabajadores',
                    method: 'list',
                    termino: termino
                },
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        const resultados = response.map(trabajador => {
                            // Create a search string with all relevant fields
                            const searchStr = (
                                (trabajador.nombre || '') + ' ' +
                                (trabajador.apellidos || '') + ' ' +
                                (trabajador.carnet_identidad || '')
                            ).toLowerCase();

                            // Calculate match score
                            const index = searchStr.indexOf(termino.toLowerCase());
                            if (index === -1) return { ...trabajador, _score: 0 };

                            const positionScore = 1 / (index + 1);
                            const lengthScore = termino.length / searchStr.length;
                            const score = positionScore * 0.7 + lengthScore * 0.3;

                            return { ...trabajador, _score: score };
                        })
                            .filter(t => t._score > 0)
                            .sort((a, b) => b._score - a._score)
                            .map(({ _score, ...trabajador }) => trabajador);

                        mostrarResultadosBusqueda(resultados);
                    } else {
                        mostrarResultadosBusqueda([]);
                    }
                },
                error: function () {
                    console.error('Error al buscar trabajadores');
                    $('#resultados-busqueda').hide().empty();
                }
            });
        }, 300); // Wait 300ms after last keystroke
    }

    // Show search results
    function mostrarResultadosBusqueda(resultados) {
        var $resultados = $('#resultados-busqueda');
        $resultados.empty();

        if (resultados.length === 0) {
            $resultados.append('<div class="list-group-item">No se encontraron resultados</div>');
        } else {
            resultados.forEach(function (trabajador) {
                $resultados.append(
                    '<div class="list-group-item list-group-item-action" data-id="' + trabajador.id + '" style="cursor: pointer;">' +
                    '   <strong>' + (trabajador.nombre || '') + ' ' + (trabajador.apellidos || '') + '</strong><br>' +
                    '   <small class="text-muted">CI: ' + (trabajador.carnet_identidad || '') + '</small>' +
                    '</div>'
                );
            });

            // Add click handler for search results
            $resultados.off('click', '.list-group-item').on('click', '.list-group-item', function () {
                var id = $(this).data('id');
                var nombreCompleto = $(this).find('strong').text().trim();

                // Set the search box value to the selected worker's name
                $('#buscar-trabajador').val(nombreCompleto);

                // Filter the table to show only the selected worker
                $table.bootstrapTable('filterBy', {
                    id: id
                });
                $resultados.hide();
            });
        }

        $resultados.show();
    }

    // Handle search input
    $('#buscar-trabajador').on('input', function () {
        var termino = $(this).val().trim();
        if (termino === '') {
            // If search box is cleared, reset the table filter
            $table.bootstrapTable('filterBy', {});
            $('#resultados-busqueda').hide().empty();
        } else {
            buscarTrabajadores(termino);
        }
    });

    // Hide search results when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#buscar-trabajador, #resultados-busqueda').length) {
            $('#resultados-busqueda').hide();
        }
    });

    // Function to apply filters
    function applyFilters() {
        var cargoId = $('#filterCargo').val();
        var deptoId = $('#filterDepartamento').val();
        var ubicId = $('#filterUbicacion').val();

        // Build the URL with filters
        var url = 'api-app.php?module=trabajadores&method=list-filter';
        if (cargoId) url += '&cargo_id=' + cargoId;
        if (deptoId) url += '&departamento_id=' + deptoId;
        if (ubicId) url += '&ubicacion_id=' + ubicId;

        // Reload table with new URL
        $table.bootstrapTable('refresh', {
            url: url,
            silent: true
        });
    }

    // Filter button click handler
    $('#btn-filter').on('click', function () {
        applyFilters();
    });

    // Reset button click handler
    $('#btn-reset').on('click', function () {
        // Clear all filters
        $('#filterCargo, #filterDepartamento, #filterUbicacion').val('');
        $('#buscar-trabajador').val('');
        $('#resultados-busqueda').hide().empty();

        // Reset the table to show all records
        $table.bootstrapTable('filterBy', {});
        $table.bootstrapTable('refresh', {
            url: 'api-app.php?module=trabajadores&method=list',
            silent: true
        });
    });

    // Export to Excel button handler
    $('#btn-export-excel').on('click', function () {
        var cargoId = $('#filterCargo').val();
        var deptoId = $('#filterDepartamento').val();
        var ubicId = $('#filterUbicacion').val();

        var search = '';
        var $searchInput = $('.fixed-table-toolbar .search input');
        if ($searchInput.length) {
            search = $searchInput.val();
        }

        var params = [];
        params.push('module=trabajadores');
        params.push('method=export-excel');
        if (cargoId) params.push('cargo_id=' + encodeURIComponent(cargoId));
        if (deptoId) params.push('departamento_id=' + encodeURIComponent(deptoId));
        if (ubicId) params.push('ubicacion_id=' + encodeURIComponent(ubicId));
        if (search) params.push('search=' + encodeURIComponent(search));

        var url = 'api-app.php?' + params.join('&');
        window.location = url;
    });

    // Handle Enter key in filter inputs
    $('#filterCargo, #filterDepartamento').keypress(function (e) {
        if (e.which == 13) {
            applyFilters();
            return false;
        }
    });
    // Helper to escape HTML for safe insertion into hidden inputs
    function escapeHtml(str) {
        if (typeof str !== 'string') return str || '';
        return str.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    // Move the add new button handler after the filter code
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=trabajadores';
    });
    $('#table-panel').on('click', '.toggle-status', function () {
        var value = '';
        if ($(this).hasClass('fa-check') == true) {
            $(this).removeClass('btn-success');
            $(this).addClass('btn-danger');
            $(this).removeClass('fa-check');
            $(this).addClass('fa-remove');
            value = 'N';
        } else {
            $(this).removeClass('btn-danger');
            $(this).addClass('btn-success');
            $(this).removeClass('fa-remove');
            $(this).addClass('fa-check');
            value = 'S';
        }
        var cmd = 'module=trabajadorescheckedid=' + $(this).data('id') + '&value=' + value;
        $.ajax({
            url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '#btn-editar', function () {
        location.href = 'index.php?module=trabajadores&id=' + $(this).data('id');
    });

    // Handler for 'view' (eye) button - show modal with full worker info
    // Handler for access control pass (tablet icon)
    $('#table-panel').on('click', '.fa.fa-tablet', function () {
        var $tr = $(this).closest('tr');
        var idx = $tr.data('index');

        var allData = $('#table-panel').bootstrapTable('getData');
        var rowData;
        if (typeof idx !== 'undefined' && allData && allData[idx]) {
            rowData = allData[idx];
        } else {
            var id = $(this).data('id');
            if (allData && allData.length) {
                rowData = allData.find(function (r) { return r.id == id || r.xusuario_id == id; });
            }
        }

        if (!rowData) {
            alert('No se encontró la información del trabajador.');
            return;
        }

        // Debug: ver los datos que recibimos
        console.log('Datos del trabajador:', rowData);
        console.log('Cargo nombre:', rowData.cargo_nombre);
        console.log('Cargo ID:', rowData.cargos_id);

        var t = rowData;
        var fotoHtml = '';
        if (t.foto) {
            fotoHtml = '<img src="' + t.foto + '" alt="Foto del trabajador" style="max-width:200px; margin-bottom:10px;" class="img-thumbnail">';
        }

        // Construir el HTML del modal como tarjeta de identificación
        // Usamos un formulario oculto para enviar los datos al generador de PDF.
        var formId = 'pdfForm_' + (t.id || Math.floor(Math.random() * 100000));
        var html = '<div class="id-card-container" style="max-width:700px; margin:0 auto;">'
            + '<form id="' + formId + '" method="POST" action="generate_id_pdf.php" target="_blank">'
            // Hidden inputs to send to the PDF generator
            + '<input type="hidden" name="id" value="' + (t.id || '') + '">'
            + '<input type="hidden" name="nombre" value="' + (escapeHtml(t.nombre || '')) + '">'
            + '<input type="hidden" name="apellidos" value="' + (escapeHtml(t.apellidos || '')) + '">'
            + '<input type="hidden" name="carnet_identidad" value="' + (escapeHtml(t.carnet_identidad || '')) + '">'
            + '<input type="hidden" name="cargo_nombre" value="' + (escapeHtml(t.cargo_nombre || '')) + '">'
            + '<input type="hidden" name="cargos_id" value="' + (t.cargos_id || '') + '">'
            + '<input type="hidden" name="areas_acceso" value="' + (escapeHtml(t.areas_acceso || '')) + '">'
            + '<input type="hidden" name="fecha_generacion" value="' + (escapeHtml(t.fecha_generacion || '')) + '">'
            + '<input type="hidden" name="vigente" value="' + (t.vigente || '') + '">'
            + '<input type="hidden" name="foto" value="' + (escapeHtml(t.foto || '')) + '">'
            + '<div class="row">'
            + '<div class="col-md-12 text-center mb-3">'
            + '<h4 style="margin:0;">Tarjeta de Identificación</h4>'
            + '<small class="text-muted">Pase de Control de Acceso</small>'
            + '</div>'
            + '</div>'
            + '<div class="row align-items-center">'
            + '<div class="col-md-4 text-center">'
            + '<div class="photo-box" style="width:200px; height:260px; margin:0 auto; border:2px solid #e9ecef; border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fff;">'
            + (fotoHtml || '<div style="padding:10px;">No hay foto</div>')
            + '</div>'
            + '<div style="margin-top:10px;">'
            + '<span class="badge badge-info">ID: ' + (t.cargos_id || 'N/A') + '</span>'
            + '</div>'
            + '</div>'
            + '<div class="col-md-8">'
            + '<div class="card" style="border:1px solid #e9ecef; box-shadow: none;">'
            + '<div class="card-body p-3">'
            + '<h5 style="font-weight:700; margin-bottom:6px;">' + (t.nombre || '') + ' ' + (t.apellidos || '') + '</h5>'
            + '<p style="margin:0 0 8px 0; color:#6c757d;">' + (t.cargo_nombre || 'Cargo no especificado') + '</p>'
            + '<table class="table table-sm" style="margin-bottom:0; font-size:0.95em;">'
            + '<tr><td><strong>CI</strong></td><td>' + (t.carnet_identidad || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Áreas</strong></td><td>' + (t.areas_acceso || 'No definidas') + '</td></tr>'
            + '<tr><td><strong>Fecha Gen.</strong></td><td>' + (t.fecha_generacion || 'N/A') + '</td></tr>'
            + '<tr><td><strong>Estado Pase</strong></td><td>' + (t.vigente === '1' ? 'Vigente' : 'No Vigente') + '</td></tr>'
            + '</table>'
            + '</div>'
            + '</div>'
            + '<div class="mt-2">'
            + '<button type="submit" class="btn btn-primary btn-sm" style="margin-right:8px;" onclick="document.getElementById(\'' + formId + '\').submit();">Generar PDF</button>'
            + '<button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Cerrar</button>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</form>'
            + '</div>';

        // Actualizar el contenido del modal
        $('#modalBody').html(html);
        $('#trabajadorModal').modal('show');
    });

    // Handler for worker details (eye icon)
    $('#table-panel').on('click', '#btn-ver-ficha', function () {
        // Try to get the row index from the DOM (bootstrap-table sets data-index on <tr>)
        //         var $tr = $(this).closest('tr');
        //         var idx = $tr.data('index');

        //         // Get all table data via bootstrapTable API
        //         var allData = $('#table-panel').bootstrapTable('getData');
        //         var rowData;
        //         if (typeof idx !== 'undefined' && allData && allData[idx]) {
        //             rowData = allData[idx];
        //         } else {
        //             // Fallback: try to find by data-id attribute
        //             var id = $(this).data('id');
        //             if (allData && allData.length) {
        //                 rowData = allData.find(function (r) { return r.id == id || r.xusuario_id == id; });
        //             }
        //         }

        //         if (!rowData) {
        //             alert('No se encontró la información del trabajador.');
        //             return;
        //         }

        //         var t = rowData;
        //         var fotoHtml = '';
        //         if (t.foto) {
        //             // If the foto value is a relative path, you might need to adjust the prefix
        //             fotoHtml = '<img src="' + t.foto + '" alt="Foto del trabajador" style="max-width:100%; margin-bottom:10px;">';
        //         }
        // //---------------------------------------------------------------------------------------
        //         // Modal Visualizar Trabajador
        //         var html = '<div class="row">'
        //             + '<div class="col-md-4 text-center">'
        //             + (fotoHtml || '<div class="alert alert-info">No hay foto disponible</div>')
        //             + '</div>'
        //             + '<div class="col-md-8">'
        //             + '<h3>Información de trabajador</h3>'
        //             + '<div class="card">'
        //             + '<div class="card-body">'
        //             + '<h4 class="card-title mb-4">' + (t.nombre || '') + ' ' + (t.apellidos || '') + '</h4>'
        //             + '<div class="row mb-3">'
        //             + '<div class="col-md-6">'
        //             + '<ul class="list-group">'
        //             + '<li class="list-group-item"><i class="fa fa-id-card mr-2"></i> <strong>CI:</strong> ' + (t.carnet_identidad || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-user mr-2"></i> <strong>Sexo:</strong> ' + (t.sexo || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-birthday-cake mr-2"></i> <strong>Edad:</strong> ' + (t.edad || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-phone mr-2"></i> <strong>Teléfono:</strong> ' + (t.telefono || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-envelope mr-2"></i> <strong>Email:</strong> ' + (t.email || 'N/A') + '</li>'
        //             + '</ul>'
        //             + '</div>'
        //             + '<div class="col-md-6">'
        //             + '<ul class="list-group">'
        //             + '<li class="list-group-item"><i class="fa fa-map-marker mr-2"></i> <strong>Dirección:</strong> ' + (t.direccion || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-graduation-cap mr-2"></i> <strong>Nivel Educacional:</strong> ' + (t.nivel_educacional || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Contratación:</strong> ' + (t.fecha_contratacion || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-calendar mr-2"></i> <strong>Fecha Baja:</strong> ' + (t.fecha_baja || 'N/A') + '</li>'
        //             + '<li class="list-group-item"><i class="fa fa-info-circle mr-2"></i> <strong>Estatus:</strong> ' + (t.estatus || 'N/A') + '</li>'
        //             + '</ul>'
        //             + '</div>'
        //             + '</div>'
        //             + '<div class="row">'
        //             + '<div class="col-12">'
        //             + '<div class="alert ' + (t.trabajador_eliminado == 1 ? 'alert-danger' : 'alert-success') + '">'
        //             + '<i class="fa ' + (t.trabajador_eliminado == 1 ? 'fa-user-times' : 'fa-user-check') + ' mr-2"></i> '
        //             + '<strong>Estado:</strong> ' + (t.trabajador_eliminado == 1 ? 'Trabajador Eliminado' : 'Trabajador Activo')
        //             + '</div>'
        //             + '</div>'
        //             + '</div>'
        //             + '<div class="row mt-3">'
        //             + '<div class="col-md-12">'
        //             + '</div>'
        //             + '<div class="col-md-12 mt-3">'
        //             + '<div class="badge badge-secondary"><i class="fa fa-user mr-1"></i> ID Trabajador: ' + (t.id || 'N/A') + '</div>'
        //             + '<div class="badge badge-secondary"><i class="fa fa-database mr-1"></i> Bolsa Empleo ID: ' + (t.bolsa_empleo_id || 'N/A') + '</div>'
        //             + ' <span class="badge badge-info">ID Cargo: ' + (t.cargos_id || 'N/A') + '</span>'
        //             + ' <span class="badge badge-success">Cargo: ' + (t.cargo_nombre ? t.cargo_nombre : 'No especificado')  + '</span>'
        //             + '</div>'
        //             + '</div>'
        //             + '</div>'
        //             + '</div>'
        //             + '</div>'
        //             + '</div>';

        //         $('#modalBody').html(html);
        //         $('#trabajadorModal').modal('show');
        location.href = 'index.php?module=ficha-trabajador&id=' + $(this).data('id');
    });


});

// --- Modal de vista previa de foto al pasar el mouse (list-trabajadores) ---
(function () {
    var css = '\n'
        + '.ltr-photo-modal-overlay { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 2000; pointer-events: none; }\n'
        + '.ltr-photo-modal { pointer-events: auto; background: rgba(255,255,255,1); padding: 8px; border-radius: 6px; box-shadow: 0 8px 30px rgba(0,0,0,0.45); transition: opacity 220ms ease, transform 220ms ease; opacity: 0; transform: scale(0.96); }\n'
        + '.ltr-photo-modal.show { opacity: 1; transform: scale(1); }\n'
        + '.ltr-photo-modal img { display:block; max-width: 640px; max-height: 640px; width: auto; height: auto; border-radius:4px; }\n';

    var style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    var overlay = document.createElement('div');
    overlay.className = 'ltr-photo-modal-overlay';
    overlay.style.display = 'none';

    var modal = document.createElement('div');
    modal.className = 'ltr-photo-modal';
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

    // Delegated listeners: images generated by formatoFoto have class img-thumbnail
    document.addEventListener('mouseover', function (e) {
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && (t.closest('#table-panel') || t.closest('#table-trabajadores') || t.classList.contains('img-thumbnail'))) {
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
        if (t.tagName === 'IMG' && (t.closest('#table-panel') || t.closest('#table-trabajadores') || t.classList.contains('img-thumbnail'))) {
            if (!from || !(from === modal || modal.contains(from))) {
                hideModal();
            }
        }
    });

    overlay.addEventListener('mouseleave', function () { hideModal(); });
    overlay.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
})();





// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

// Sample Format for Tracking Number Column.
// =================================================================
function formatoToolbar(value, row) {
    var s = '';
    
    // INVENTARIO role (id=3) and JEFE DE AREA role (id=4) can only see "Ver Ficha"
    if (userRole == '3' || userRole == '4') {
        s += '<button id="btn-ver-ficha" data-id="' + row.id + '" class="btn btn-success btn-icon" title="Ficha Trabajador"><span class="fa fa-eye"></span> Ver Ficha</button>';
    } else {
        // Other roles see all buttons
        s = '<button id="btn-editar" data-id="' + row.id + '" class="btn btn-info btn-icon" title="Editar trabajador"><span class="fa fa-edit"></span> Editar</button>\n\
                <button id="btn-ver-ficha" data-id="' + row.id + '" class="btn btn-success btn-icon" title="Ficha Trabajador"><span class="fa fa-eye"></span> Ver Ficha</button>\n';
        
        // Si es_liquidacion es 1, mostrar botón "Liquidar", sino "Dar Baja"
        if (row.es_liquidacion && row.es_liquidacion == 1) {
            s += '<button data-id="' + row.id + '" class="btn btn-warning btn-icon btn-liquidar" title="Liquidar trabajador"><span class="fa fa-money"></span> Liquidar</button>';
        } else {
            s += '<button data-id="' + row.id + '" class="btn btn-danger btn-icon btn-dar-baja" title="Dar de baja trabajador"><span class="fa fa-ban"></span> Dar Baja</button>';
        }
    }

    return s;
}

function imageFormatter(value, row) {
    return '<img src="' + value + '" style="height: 50px;" />';
}

// Formateador para mostrar la foto del trabajador
function formatoFoto(value, row) {
    // Si hay una foto guardada, mostrarla
    if (value) {
        // Si es una data URL (base64), usarla directamente
        if (value.startsWith('data:')) {
            return '<img src="' + value + '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" class="img-thumbnail" alt="Foto del trabajador">';
        }
        // Si la ruta ya es una URL completa, usarla directamente
        if (value.startsWith('http') || value.startsWith('/')) {
            return '<img src="' + value + '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" class="img-thumbnail" alt="Foto del trabajador">';
        }
        // Si no, asumir que es solo el nombre del archivo
        var basePath = 'uploads/fotos_trabajadores/';
        return '<img src="' + basePath + value + '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" class="img-thumbnail" alt="Foto del trabajador">';
    }

    // Si no hay foto, mostrar un avatar por defecto
    return '<div style="width: 50px; height: 50px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;" class="img-thumbnail">' +
        '<i class="fa fa-user" style="font-size: 24px; color: #999;"></i>' +
        '</div>';
}

// ===============================================
// FUNCIONALIDAD PARA DAR DE BAJA TRABAJADORES
// ===============================================

// Event handler para el botón "Dar de Baja"
$(document).on('click', '.btn-dar-baja', function (e) {
    e.preventDefault();
    var trabajadorId = $(this).data('id');
    var rowIndex = $(this).closest('tr').data('index');

    // Chequeo previo: si tiene recursos asignados sin devolver, NO abrir el modal
    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: { module: 'gestion-recursos', method: 'list-id', trabajador_id: trabajadorId },
        dataType: 'json'
    }).done(function (rows) {
        try {
            var activos = 0;
            if ($.isArray(rows)) {
                for (var i = 0; i < rows.length; i++) {
                    var r = rows[i] || {};
                    var estado = parseInt(r.estado, 10);
                    var frh = (r.fecha_entrega_a_rh || '').trim();
                    var sinDevolver = (estado === 1) || frh === '' || frh === null || frh === '0000-00-00';
                    if (sinDevolver) { activos++; }
                }
            }
            if (activos > 0) {
                if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: 'warning',
                        container: 'floating',
                        title: 'Acción no permitida',
                        message: 'No se puede dar de baja: el trabajador tiene recursos asignados sin devolver.',
                        timer: 3500,
                        closeBtn: true,
                        focus: true
                    });
                } else {
                    alert('No se puede dar de baja: el trabajador tiene recursos asignados sin devolver.');
                }
                return; // No abrir modal
            }
        } catch (e) { /* continuar y abrir modal */ }

        // Almacenar datos temporales para el modal y abrirlo
        $('#confirm-delete-id').val(trabajadorId);
        $('#confirm-delete-row').val(rowIndex);
        $('#confirm-delete-text').val('');
        $('#confirm-delete-help').hide();
        $('#confirmDeleteModal').modal('show');
    }).fail(function () {
        // Si el chequeo falla, como fallback abrir modal (backend también valida)
        $('#confirm-delete-id').val(trabajadorId);
        $('#confirm-delete-row').val(rowIndex);
        $('#confirm-delete-text').val('');
        $('#confirm-delete-help').hide();
        $('#confirmDeleteModal').modal('show');
    });
});

// Confirmar eliminación desde el modal
$(document).on('click', '#btn-confirm-delete', function () {
    var typed = ($('#confirm-delete-text').val() || '').trim();
    if (typed !== 'ELIMINAR') {
        $('#confirm-delete-help').show();
        return;
    }

    var id = $('#confirm-delete-id').val();
    var rowIndex = $('#confirm-delete-row').val();

    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: {
            module: 'trabajadores',
            method: 'del',
            id: id,
            row: rowIndex
        },
        dataType: 'json',
        success: function (d) {
            $('#confirmDeleteModal').modal('hide');
            if (d.status == 1) {
                // Remover la fila de la tabla
                $('tr[data-index="' + d.row + '"]').fadeOut('slow');

                // Mostrar notificación de éxito
                if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: 'success',
                        container: 'floating',
                        title: 'Dado de Baja',
                        message: 'El trabajador ha sido dado de baja correctamente con fecha de hoy.',
                        timer: 3000,
                        closeBtn: true,
                        focus: true
                    });
                } else {
                    alert('Trabajador dado de baja correctamente.');
                }

                // Refrescar la tabla después de un momento
                setTimeout(function () {
                    $('#table-trabajadores').bootstrapTable('refresh');
                }, 1000);

            } else {
                // Mostrar error
                if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: 'danger',
                        container: 'floating',
                        title: 'Error',
                        message: d.msg || 'No se pudo dar de baja al trabajador.',
                        timer: 3000,
                        closeBtn: true,
                        focus: true
                    });
                } else {
                    alert(d.msg || 'No se pudo dar de baja al trabajador.');
                }
            }
        },
        error: function () {
            $('#confirmDeleteModal').modal('hide');
            if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                $.niftyNoty({
                    type: 'danger',
                    container: 'floating',
                    title: 'Error',
                    message: 'Error de conexión al dar de baja al trabajador.',
                    timer: 3000,
                    closeBtn: true,
                    focus: true
                });
            } else {
                alert('Error de conexión.');
            }
        }
    });
});

// Event handler para el botón "Liquidar"
$(document).on('click', '.btn-liquidar', function (e) {
    e.preventDefault();
    var trabajadorId = $(this).data('id');
    var rowIndex = $(this).closest('tr').data('index');

    if (!confirm('¿Está seguro de que desea liquidar este trabajador? Esta acción es definitiva.')) {
        return;
    }

    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: {
            module: 'trabajadores',
            method: 'liquidar',
            id: trabajadorId,
            row: rowIndex
        },
        dataType: 'json',
        success: function (d) {
            if (d.status == 1) {
                // Remover la fila de la tabla
                $('tr[data-index="' + d.row + '"]').fadeOut('slow');

                // Mostrar notificación de éxito
                if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: 'success',
                        container: 'floating',
                        title: 'Liquidado',
                        message: 'El trabajador ha sido liquidado correctamente.',
                        timer: 3000,
                        closeBtn: true,
                        focus: true
                    });
                } else {
                    alert('Trabajador liquidado correctamente.');
                }

                // Refrescar la tabla después de un momento
                setTimeout(function () {
                    $('#table-panel').bootstrapTable('refresh');
                }, 1000);

            } else {
                // Verificar si el error es por falta de prenomina
                var msg = d.msg || 'No se pudo liquidar al trabajador.';
                if (msg.indexOf('prenomina') !== -1 || msg.indexOf('prenómina') !== -1) {
                    // Mostrar modal de error con opción de forzar baja
                    $('#liquidar-error-msg').text(msg);
                    $('#liquidar-error-id').val(trabajadorId);
                    $('#liquidar-error-row').val(rowIndex);
                    $('#liquidarErrorModal').modal('show');
                } else {
                    // Otro tipo de error: mostrar notificación
                    if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                        $.niftyNoty({
                            type: 'danger',
                            container: 'floating',
                            title: 'Error',
                            message: msg,
                            timer: 3000,
                            closeBtn: true,
                            focus: true
                        });
                    } else {
                        alert(msg);
                    }
                }
            }
        },
        error: function () {
            if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                $.niftyNoty({
                    type: 'danger',
                    container: 'floating',
                    title: 'Error',
                    message: 'Error de conexión al liquidar al trabajador.',
                    timer: 3000,
                    closeBtn: true,
                    focus: true
                });
            } else {
                alert('Error de conexión.');
            }
        }
    });
});

// Event handler para confirmar baja sin prenómina
$(document).on('click', '#btn-confirm-baja-sin-prenomina', function (e) {
    e.preventDefault();
    var trabajadorId = $('#liquidar-error-id').val();
    var rowIndex = $('#liquidar-error-row').val();

    $('#liquidarErrorModal').modal('hide');

    $.ajax({
        url: 'api-app.php',
        type: 'GET',
        data: {
            module: 'trabajadores',
            method: 'liquidar-forzado',
            id: trabajadorId,
            row: rowIndex
        },
        dataType: 'json',
        success: function (d) {
            if (d.status == 1) {
                $('tr[data-index="' + d.row + '"]').fadeOut('slow');

                if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: 'success',
                        container: 'floating',
                        title: 'Baja realizada',
                        message: 'Trabajador dado de baja correctamente (sin liquidación en prenómina).',
                        timer: 3000,
                        closeBtn: true,
                        focus: true
                    });
                } else {
                    alert('Trabajador dado de baja correctamente.');
                }

                setTimeout(function () {
                    $('#table-panel').bootstrapTable('refresh');
                }, 1000);
            } else {
                if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: 'danger',
                        container: 'floating',
                        title: 'Error',
                        message: d.msg || 'No se pudo dar de baja al trabajador.',
                        timer: 3000,
                        closeBtn: true,
                        focus: true
                    });
                } else {
                    alert(d.msg || 'No se pudo dar de baja al trabajador.');
                }
            }
        },
        error: function () {
            if ($.niftyNoty && typeof $.niftyNoty === 'function') {
                $.niftyNoty({
                    type: 'danger',
                    container: 'floating',
                    title: 'Error',
                    message: 'Error de conexión al dar de baja al trabajador.',
                    timer: 3000,
                    closeBtn: true,
                    focus: true
                });
            } else {
                alert('Error de conexión.');
            }
        }
    });
});

function formatoNombreCompleto(value, row) {
    var id = row.trabajador_id || row.id;
    var nombreCompleto = (row.nombre || '') + ' ' + (row.apellidos || '');
    if (value && !nombreCompleto.trim()) { nombreCompleto = value; }

    // Si value ya es el nombre completo
    if (!row.nombre && !row.apellidos && value) {
        nombreCompleto = value;
    }

    // Los dados de baja pendientes de liquidar se listan arriba del todo; sin un distintivo
    // el orden resultaria inexplicable para quien mira la tabla.
    var badge = '';
    if (Number(row.es_liquidacion) === 1) {
        badge = ' <span class="label" style="background-color: #f0ad4e; color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 3px; white-space: nowrap;" title="Dado de baja, pendiente de liquidar en prenómina">Baja sin liquidar</span>';
    }

    if (id) {
        return '<a href="index.php?module=ficha-trabajador&id=' + id + '" style="cursor: pointer; color: inherit; text-decoration: none;" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#337ab7\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'inherit\';">' + nombreCompleto + '</a>' + badge;
    }
    return nombreCompleto + badge;
}
// Function to apply highlighting to rows with es_liquidacion = 1
function resaltarFilasLiquidacion() {
    var datos = $('#table-panel').bootstrapTable('getData');
    datos.forEach(function(row, index) {
        if (row.es_liquidacion && row.es_liquidacion == 1) {
            var $fila = $('tr[data-index="' + index + '"]');
            if ($fila.length > 0) {
                $fila.css('background-color', '#fff3cd');
            }
        }
    });
}

// Event when table loads data
$('#table-panel').on('load-success.bs.table', function() {
    resaltarFilasLiquidacion();
});

// Also apply highlighting after refresh
$('#table-panel').on('refresh.bs.table', function() {
    setTimeout(function() {
        resaltarFilasLiquidacion();
    }, 100);
});