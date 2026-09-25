<!-- <div class="panel">
    <div class="form-control"> -->
<!-- <button id="btn-back" class="btn btn-mint btn-icon icon-lg fa fa-arrow-left" alt="Volver" title="Volver"></button> -->
<!-- </div>
</div> -->
<div class="panel">
    <div class="form-control">
        <button id="btn-add-new" class="btn btn-mint btn-icon" alt="Añadir Nuevo Trabajador" title="Añadir Nuevo Trabajador"><span class="icon-lg fa fa-plus"></span> Añadir Nuevo Trabajador</button>
    </div>
</div>
<!--Basic Toolbar-->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading">
        <div class="panel-control">
            <ul class="nav nav-tabs">
                <li><a href="#" onclick="location.href='?module=list-trabajadores'"><i class="fa fa-list"></i> Listado de Trabajadores</a></li>
                <!-- <li><a href="#" onclick="location.href='?module=delete-trabajadores'">Dar de baja</a></li> -->
                <li class="active"><a href="#tab-listado-bajas" data-toggle="tab" aria-expanded="true"><i class="fa fa-ban"></i> Listado de Bajas</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">


            <div class="tab-pane fade active in" id="tab-listado-bajas">
                <table
                    id="table-panel"
                    data-toggle="table"
                    data-url="api-app.php?module=trabajadores&method=list-bajas"
                    data-search="true"
                    data-show-refresh="true"
                    data-show-toggle="false"
                    data-show-columns="false"
                    data-sort-name="fecha_baja"
                    data-sort-order="desc"
                    data-page-list="[20, 50, 100]"
                    data-page-size="10"
                    data-pagination="true"
                    data-show-pagination-switch="true"
                    data-response-handler="responseHandler">
                    <thead>
                        <tr>
                            <th data-field="carnet_identidad" data-sortable="true">CI</th>
                            <th data-field="nombre" data-sortable="true" data-formatter="formatoNombreCompleto">Nombre Completo</th>

                            <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                            <th data-field="fecha_contratacion" data-sortable="true">Fecha Contratación</th>
                            <th data-field="fecha_baja" data-sortable="true">Fecha Baja</th>
                            <th data-field="acciones" data-formatter="formatoAcciones" data-sortable="false" data-align="center">Acciones</th>

                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!--===================================================-->

<script>
    // Función para formatear fechas
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }

    // Función para formatear acciones en la tabla
    function formatoAcciones(value, row, index) {
        if (!row || !row.id) {
            return '<span class="text-muted">N/A</span>';
        }

        var nombre = (row.nombre || '') + ' ' + (row.apellidos || '');
        return '<button type="button" class="btn btn-xs btn-info btn-recontratar" data-id="' + row.id + '" data-nombre="' + nombre.trim() + '" onclick="abrirModalRecontratar(event)"><i class="fa fa-refresh"></i> Recontratar</button>';
    }

    // Función para formatear la respuesta de la API
    function responseHandler(res) {
        if (Array.isArray(res)) {
            return {
                total: res.length,
                rows: res
            };
        }
        return {
            total: 0,
            rows: []
        };
    }

    // Función para cargar empresas
    function cargarEmpresas() {
        $.ajax({
            url: 'api-app.php?module=empresa&method=list',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Empresas cargadas:', data);
                var empresaSelect = $('#empresaSelect');
                empresaSelect.html('<option value="">-- Seleccionar empresa --</option>');

                if (data && Array.isArray(data) && data.length > 0) {
                    $.each(data, function(index, empresa) {
                        empresaSelect.append('<option value="' + empresa.id + '">' + empresa.nombre + '</option>');
                    });
                } else if (data && data.rows && Array.isArray(data.rows)) {
                    $.each(data.rows, function(index, empresa) {
                        empresaSelect.append('<option value="' + empresa.id + '">' + empresa.nombre + '</option>');
                    });
                } else {
                    console.warn('Formato de datos no esperado:', data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar empresas:', error);
                alert('Error al cargar las empresas. Verifica la consola del navegador.');
            }
        });
    }

    // Función para abrir modal desde el formatter
    function abrirModalRecontratar(event) {
        event.preventDefault();
        event.stopPropagation();

        var btn = event.target.closest('.btn-recontratar');
        if (!btn) return;

        var trabajadorId = btn.getAttribute('data-id');
        var trabajadorNombre = btn.getAttribute('data-nombre');

        console.log('Abriendo modal para: ID=' + trabajadorId + ', Nombre=' + trabajadorNombre);

        $('#trabajadorId').val(trabajadorId);
        $('#trabajadorNombre').val(trabajadorNombre);

        // Limpiar el select
        $('#empresaSelect').html('<option value="">-- Seleccionar empresa --</option>');

        // Cargar empresas en el select
        cargarEmpresas();

        // Mostrar modal
        $('#recontratarModal').modal('show');
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        console.log('Inicializando página de bajas...');

        // Configurar la tabla
        $('#table-panel').bootstrapTable({
            url: 'api-app.php?module=trabajadores&method=list-bajas',
            search: true,
            showRefresh: true,
            showToggle: false,
            showColumns: false,
            sortName: 'fecha_baja',
            sortOrder: 'desc',
            pagination: true,
            pageSize: 50,
            pageList: [20, 50, 100],
            responseHandler: responseHandler,
            onLoadSuccess: function() {
                console.log('Tabla cargada exitosamente');
            },
            onLoadError: function(status) {
                console.error('Error al cargar la tabla:', status);
            }
        });
    });

    // Función para confirmar recontratar
    function confirmarRecontrata() {
        console.log('Confirmar recontratar clickeado');
        var trabajadorId = $('#trabajadorId').val();
        console.log('ID del trabajador a recontratar: ' + trabajadorId);
        var empresaId = $('#empresaSelect').val();

        console.log('Confirmando recontrata - Trabajador ID: ' + trabajadorId + ', Empresa ID: ' + empresaId);

        if (!trabajadorId || !empresaId) {
            alert('Por favor completa todos los campos');
            return;
        }

        $.ajax({
            url: 'api-app.php?module=trabajadores&method=recontratar',
            type: 'POST',
            dataType: 'json',
            data: {
                trabajador_id: trabajadorId,
                empresa_id: empresaId
            },
            success: function(response) {
                console.log('Respuesta exitosa:', response);
                if (response && response.status == 1) {
                    alert('Trabajador recontratado exitosamente');
                    $('#recontratarModal').modal('hide');
                    $('#table-panel').bootstrapTable('refresh');
                } else {
                    alert('Error: ' + (response.msg || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', status, error, xhr.responseText);
                alert('Error al recontratar el trabajador: ' + error);
            }
        });
    }
</script>

<!--Modal para ver detalles del trabajador dado de baja-->
<div class="modal fade" id="trabajadorBajaModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Detalles del Trabajador Dado de Baja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Aquí se insertan los datos -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para recontratar trabajador -->
<div class="modal fade" id="recontratarModal" tabindex="-1" role="dialog" aria-labelledby="recontratarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recontratarLabel">Recontratar Trabajador</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="trabajadorNombre" class="form-label">Trabajador:</label>
                    <input type="text" id="trabajadorNombre" class="form-control" readonly>
                    <input type="hidden" id="trabajadorId" value="">
                </div>
                <div class="form-group">
                    <label for="empresaSelect" class="form-label">Seleccionar Empresa:</label>
                    <select id="empresaSelect" class="form-control" required>
                        <option value="">-- Seleccionar empresa --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarRecontrata()">Recontratar</button>
            </div>
        </div>
    </div>
</div>