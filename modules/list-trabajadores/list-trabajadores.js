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
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: 'api-app.php',
                type: 'GET',
                data: {
                    module: 'trabajadores',
                    method: 'list',
                    termino: termino
                },
                dataType: 'json',
                success: function(response) {
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
                error: function() {
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
            resultados.forEach(function(trabajador) {
                $resultados.append(
                    '<div class="list-group-item list-group-item-action" data-id="' + trabajador.id + '" style="cursor: pointer;">' +
                    '   <strong>' + (trabajador.nombre || '') + ' ' + (trabajador.apellidos || '') + '</strong><br>' +
                    '   <small class="text-muted">CI: ' + (trabajador.carnet_identidad || '') + '</small>' +
                    '</div>'
                );
            });
            
            // Add click handler for search results
            $resultados.off('click', '.list-group-item').on('click', '.list-group-item', function() {
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
    $('#buscar-trabajador').on('input', function() {
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
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#buscar-trabajador, #resultados-busqueda').length) {
            $('#resultados-busqueda').hide();
        }
    });
    
    // Function to apply filters
    function applyFilters() {
        var cargoId = $('#filterCargo').val();
        var deptoId = $('#filterDepartamento').val();
        
        // Build the URL with filters
        var url = 'api-app.php?module=trabajadores&method=list-filter';
        if (cargoId) url += '&cargo_id=' + cargoId;
        if (deptoId) url += '&departamento_id=' + deptoId;
        
        // Reload table with new URL
        $table.bootstrapTable('refresh', {
            url: url,
            silent: true
        });
    }
    
    // Filter button click handler
    $('#btn-filter').on('click', function() {
        applyFilters();
    });
    
    // Reset button click handler
    $('#btn-reset').on('click', function() {
        // Clear all filters
        $('#filterCargo, #filterDepartamento').val('');
        $('#buscar-trabajador').val('');
        $('#resultados-busqueda').hide().empty();
        
        // Reset the table to show all records
        $table.bootstrapTable('filterBy', {});
        $table.bootstrapTable('refresh', {
            url: 'api-app.php?module=trabajadores&method=list',
            silent: true
        });
    });
    
    // Handle Enter key in filter inputs
    $('#filterCargo, #filterDepartamento').keypress(function(e) {
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
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    $('#table-panel').on('click', '.fa.fa-edit', function () {
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
    $('#table-panel').on('click', '.fa.fa-eye', function () {
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
    var s = '<button data-id="' + row.id + '" class="btn btn-info btn-icon icon-sm fa fa-edit" title="Editar trabajador"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-eye" title="Ver detalles"></button>\n\
                <button data-id="' + row.id + '" class="btn btn-warning btn-icon icon-sm fa fa-tablet" title="Pase de acceso"></button>';

    return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}
