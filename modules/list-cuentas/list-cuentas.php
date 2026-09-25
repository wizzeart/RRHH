<script>
    var rol = '<?php print($app->rol) ?>';
</script>
<div class="panel">
    <div class="form-control">


        <button id="export-dbf" class="btn btn-primary" style="margin-left: 15px; text-align: center;"><span class="icon-lg fa fa-plus"></span> Exportar a DBF</button>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">

        <h3 class="panel-title">Listado de Cuentas</h3>

    </div>

    <div class="panel-body">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-listado">Listado de Cuentas</a></li>
            <!-- <li><a data-toggle="tab" href="#tab-bancos">Listado de Bancos</a></li> -->
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab-listado">
                <!-- Tabla de cuentas bancarias -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="table-cuentas" class="table table-striped table-bordered table-hover"
                                data-toggle="table"
                                data-url="api-app.php?module=cuentas&method=list&empresa_id=<?php echo $app->empresa_id; ?>"
                                data-side-pagination="client"
                                data-pagination="true"
                                data-page-size="25"
                                data-search="true"
                                data-show-refresh="true"
                                data-show-columns="true"
                                data-sort-name="nombre_completo"
                                data-sort-order="asc"
                                data-toolbar="#toolbar"
                                data-show-export="true"
                                data-export-types="['csv', 'excel']"
                                data-export-options='{
                                       "fileName": "cuentas_bancarias_" + new Date().toISOString().slice(0,10)
                                   }'>
                                <thead>
                                    <tr>
                                        <th data-field="carnet_identidad" data-sortable="true">Carnet de Identidad</th>
                                        <th data-field="nombre_completo" data-sortable="true" data-formatter="formatoNombreLink">Nombre Completo</th>
                                        <th data-field="tarjeta_display" data-sortable="true">Tarjeta de Salario</th>
                                        <th data-field="cuenta_display" data-sortable="true">Cuenta Estándar</th>
                                        <!-- <th data-field="estatus_display" data-sortable="true">Estado</th> -->
                                        <!-- <th data-field="acciones" data-formatter="accionesFormatter" data-events="accionesEvents">Acciones</th> -->
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-bancos">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="table-bancos" class="table table-striped table-bordered table-hover"
                                data-toggle="table"
                                data-url="api/list-cuentas-data.php"
                                data-side-pagination="client"
                                data-pagination="true"
                                data-page-size="25"
                                data-search="true"
                                data-show-refresh="true"
                                data-show-columns="true"
                                data-sort-name="nombre_completo"
                                data-sort-order="asc"
                                data-toolbar="#toolbar"
                                data-show-export="true"
                                data-export-types="['csv', 'excel']"
                                data-export-options='{
                               "fileName": "bancos_trabajadores_prenomina_" + new Date().toISOString().slice(0,10)
                           }'>
                                <thead>
                                    <tr>
                                        <th data-field="carnet_identidad" data-sortable="true">Carnet de Identidad</th>



                                        <th data-field="numero_cuenta_estandar" data-sortable="true">Número de Cuenta</th>
                                        <th data-field="salario_pagar" data-sortable="true">Salario a Pagar</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir JavaScript del módulo -->
<script src="modules/list-cuentas/list-cuentas.js?<?php echo time(); ?>"></script>

<script>
    document.getElementById('export-dbf').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;

        // Antes de descargar se comprueba si algún trabajador con prenómina del mes se queda
        // fuera del fichero por no tener cuenta estándar registrada, y se avisa.
        fetch('api/export-bancos-dbf.php?check=1', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (info) {
                if (info.status !== 1) { throw new Error(info.msg || 'Respuesta inválida'); }

                if (info.sin_cuenta && info.sin_cuenta.length > 0) {
                    var lista = info.sin_cuenta.slice(0, 15).map(function (t) {
                        return '  · ' + t.nombre + ' (CI ' + (t.carnet_identidad || 'sin CI') + ')';
                    }).join('\n');
                    if (info.sin_cuenta.length > 15) {
                        lista += '\n  … y ' + (info.sin_cuenta.length - 15) + ' más';
                    }

                    var msg = '⚠️ ' + info.sin_cuenta.length + ' trabajador(es) con prenómina de '
                        + info.periodo + ' NO tienen cuenta estándar registrada y quedarán FUERA '
                        + 'del fichero (no se les pagará por transferencia):\n\n' + lista
                        + '\n\nSe exportarán ' + info.a_exportar + ' trabajador(es).'
                        + '\n\n¿Desea continuar de todos modos?';

                    if (!confirm(msg)) { return; }
                } else if (info.a_exportar === 0) {
                    alert('No hay trabajadores con prenómina de ' + info.periodo + ' para exportar.');
                    return;
                }

                window.location.href = 'api/export-bancos-dbf.php';
            })
            .catch(function (e) {
                alert('No se pudo comprobar el fichero antes de exportar: ' + e.message);
            })
            .then(function () { btn.disabled = false; });
    });
</script>