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
                <li><a href="#" onclick="location.href='?module=list-trabajadores'">Listado de Trabajadores</a></li>
                <li><a href="#" onclick="location.href='?module=delete-trabajadores'">Dar de baja</a></li>
                <li class="active"><a href="#tab-listado-bajas" data-toggle="tab" aria-expanded="true">Listado de Bajas</a></li>
            </ul>
        </div>
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tab-content">
            <div class="form-inline" style="margin-bottom: 15px;">
                        <!-- Search Box -->
                        <!-- <div class="form-group" style="margin-right: 10px; width: 300px; position: relative;">
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscar-trabajador" placeholder="Buscar por nombre, apellido o CI...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                            <div id="resultados-busqueda" class="list-group" style="position: absolute; z-index: 1000; width: 100%; display: none; max-height: 300px; overflow-y: auto;"></div>
                        </div> -->
                        
                        <!-- Cargo Filter -->
                        <div class="form-group" style="margin-right: 10px;">
                            <select id="filterCargo" class="form-control">
                                <option value="">Todos los cargos</option>
                                <?php
                                $cargos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM cargos ORDER BY nombre");
                                foreach ($cargos as $cargo) {
                                    echo "<option value='" . $cargo['id'] . "'>" . htmlspecialchars($cargo['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Departamento Filter -->
                        <div class="form-group" style="margin-right: 10px;">
                            <select id="filterDepartamento" class="form-control">
                                <option value="">Todos los departamentos</option>
                                <?php
                                $deptos = $app->db->fetchAll("SELECT DISTINCT id, nombre FROM departamentos ORDER BY nombre");
                                foreach ($deptos as $depto) {
                                    echo "<option value='" . $depto['id'] . "'>" . htmlspecialchars($depto['nombre']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button id="btn-filter" class="btn btn-primary">Filtrar</button>
                        <button id="btn-reset" class="btn btn-default" style="margin-left: 5px;">Limpiar</button>
                    </div>
            
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
            data-page-size="50"
            data-pagination="true" 
            data-show-pagination-switch="true"
            data-response-handler="responseHandler">
            <thead>
                <tr>
                    <th data-field="carnet_identidad" data-sortable="true">CI</th>
                    <th data-field="nombre" data-sortable="true">Nombre</th>
                    <th data-field="apellidos" data-sortable="true">Apellidos</th>
                    
                    <th data-field="cargo_nombre" data-sortable="true">Cargo</th>
                    <th data-field="fecha_contratacion" data-sortable="true">Fecha Contratación</th>
                    <th data-field="fecha_baja" data-sortable="true">Fecha Baja</th>
                    
                </tr>
            </thead>
        </table>
            </div>
        </div>
    </div>
</div>
<!--===================================================-->

<script>
// Función para formatear la respuesta de la API
function responseHandler(res) {
    // Asegurarse de que los datos sean un array
    if (Array.isArray(res)) {
        return {total: res.length, rows: res};
    }
    return {total: 0, rows: []};
}

// Inicializar la tabla
$(document).ready(function() {
    // Actualizar la tabla al hacer clic en el botón de actualizar
    $('body').on('click', '.refresh', function() {
        $('#table-panel').bootstrapTable('refresh');
    });

    // Formatear fechas
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }

    // Configurar las columnas de la tabla
    $('#table-panel').bootstrapTable({
        columns: [{
            field: 'nombre',
            title: 'Nombre',
            sortable: true
        }, {
            field: 'apellidos',
            title: 'Apellidos',
            sortable: true
        }, {
            field: 'carnet_identidad',
            title: 'CI',
            sortable: true
        }, {
            field: 'cargo_nombre',
            title: 'Cargo',
            sortable: true,
            formatter: function(value) {
                return value || 'Sin cargo';
            }
        }, {
            field: 'fecha_contratacion',
            title: 'Fecha Contratación',
            sortable: true,
            formatter: function(value) {
                return formatDate(value);
            }
        }, {
            field: 'fecha_baja',
            title: 'Fecha Baja',
            sortable: true,
            formatter: function(value) {
                return formatDate(value);
            }
        }, {
            field: 'estatus',
            title: 'Estado',
            sortable: true,
            formatter: function(value) {
                return value || 'Inactivo';
            }
        }]
    });
});
</script>

<!-- Asegurarse de que jQuery esté cargado -->
<script>
// Función para formatear fechas
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES');
}

// Función para formatear la respuesta de la API
function responseHandler(res) {
    // Asegurarse de que los datos sean un array
    if (Array.isArray(res)) {
        return {total: res.length, rows: res};
    }
    return {total: 0, rows: []};
}

// Inicializar la tabla cuando el documento esté listo
$(document).ready(function() {
    // Verificar si Bootstrap Table está disponible
    if (typeof $.fn.bootstrapTable === 'undefined') {
        console.error('Bootstrap Table no está cargado correctamente');
        return;
    }

    // Configurar la tabla
    $('#table-panel').bootstrapTable({
        columns: [{
            field: 'nombre',
            title: 'Nombre',
            sortable: true
        }, {
            field: 'apellidos',
            title: 'Apellidos',
            sortable: true
        }, {
            field: 'carnet_identidad',
            title: 'CI',
            sortable: true
        }, {
            field: 'cargo_nombre',
            title: 'Cargo',
            sortable: true,
            formatter: function(value) {
                return value || 'Sin cargo';
            }
        }, {
            field: 'fecha_contratacion',
            title: 'Fecha Contratación',
            sortable: true,
            formatter: function(value) {
                return formatDate(value);
            }
        }, {
            field: 'fecha_baja',
            title: 'Fecha Baja',
            sortable: true,
            formatter: function(value) {
                return formatDate(value);
            }
        }, {
            field: 'estatus',
            title: 'Estado',
            sortable: true,
            formatter: function(value) {
                return value || 'Inactivo';
            }
        }],
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
        responseHandler: responseHandler
    });

    // Actualizar la tabla al hacer clic en el botón de actualizar
    $('body').on('click', '.refresh', function() {
        $('#table-panel').bootstrapTable('refresh');
    });
});

// Iniciar la verificación de jQuery
//checkJQuery();
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
