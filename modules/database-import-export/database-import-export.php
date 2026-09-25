<?php
$page['title'] = 'Importar/Exportar Base de Datos';
$page['subtitle'] = 'Herramientas para importar y exportar la base de datos';

// Obtener información de la base de datos
$db_info = array();
if (method_exists($mdl, 'getDatabaseInfo')) {
    $db_info = $mdl->getDatabaseInfo();
} else {
    // Información por defecto si el método no está disponible
    $db_info['database_name'] = 'N/A';
    $db_info['total_tables'] = 0;
    $db_info['total_size'] = 'N/A';
}
?>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $page['subtitle']; ?></h3>
    </div>

    <div class="panel-body">
        <!-- Información de la Base de Datos -->
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h4><i class="fa fa-info-circle"></i> Información de la Base de Datos</h4>
                    <p><strong>Base de Datos:</strong> <?php echo htmlspecialchars($db_info['database_name']); ?></p>
                    <p><strong>Total de Tablas:</strong> <?php echo $db_info['total_tables']; ?></p>
                    <p><strong>Tamaño Total:</strong> <?php echo $db_info['total_size']; ?></p>
                </div>
            </div>
        </div>

        <!-- Sección de Exportación -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h4><i class="fa fa-upload"></i> Exportar Base de Datos</h4>
                    </div>
                    <div class="panel-body">
                        <p>Exporte toda la base de datos a un archivo SQL. Esto incluye:</p>
                        <ul>
                            <li>Estructura de todas las tablas</li>
                            <li>Todos los datos existentes</li>
                            <li>Relaciones y restricciones</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> 
                            <strong>Nota:</strong> La exportación puede tardar varios minutos dependiendo del tamaño de la base de datos.
                        </div>
                        
                        <button id="btn-export" class="btn btn-success btn-lg btn-block">
                            <i class="fa fa-upload"></i> Exportar Base de Datos Completa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sección de Importación -->
            <div class="col-md-6">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h4><i class="fa fa-download"></i> Importar Base de Datos</h4>
                    </div>
                    <div class="panel-body">
                        <p>Importe una base de datos desde un archivo SQL. <strong>ADVERTENCIA:</strong></p>
                        <ul>
                            <li>Esta acción <strong>reemplazará</strong> todos los datos actuales</li>
                            <li>No se puede deshacer esta operación</li>
                            <li>Se recomienda hacer un backup antes de importar</li>
                        </ul>
                        
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-circle"></i> 
                            <strong>Precaución:</strong> Asegúrese de que el archivo SQL sea compatible con su sistema.
                        </div>
                        
                        <form id="import-form" method="post" enctype="multipart/form-data" action="index.php?module=database-import-export&action=import">
                            <div class="form-group">
                                <label for="sql-file" class="control-label">Seleccionar archivo SQL:</label>
                                <input type="file" id="sql-file" name="sql_file" class="form-control" accept=".sql" required>
                                <small class="help-block">Solo se permiten archivos .sql</small>
                                <p class="help-block"><strong>Archivo seleccionado:</strong> <span id="file-name">Ninguno</span></p>
                            </div>
                            
                            <button type="button" id="btn-import" class="btn btn-warning btn-lg btn-block">
                                <i class="fa fa-download"></i> Importar Base de Datos
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instrucciones Adicionales -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><i class="fa fa-question-circle"></i> Instrucciones y Recomendaciones</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fa fa-shield"></i> Seguridad</h5>
                                <ul>
                                    <li>Mantenga sus archivos SQL en un lugar seguro</li>
                                    <li>No comparta sus backups con personas no autorizadas</li>
                                    <li>Considere encriptar archivos sensibles</li>
                                    <li>Haga backups regularmente</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fa fa-cogs"></i> Mejores Prácticas</h5>
                                <ul>
                                    <li>Exporte antes de realizar cambios importantes</li>
                                    <li>Verifique el archivo SQL antes de importar</li>
                                    <li>Importe en un entorno de prueba primero</li>
                                    <li>Documente cuándo y por qué realizó cada backup</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h5><i class="fa fa-info"></i> Formato del Archivo SQL</h5>
                                <p>El sistema genera archivos SQL compatibles con MySQL que incluyen:</p>
                                <ul>
                                    <li>Comandos CREATE TABLE para la estructura</li>
                                    <li>Comandos INSERT para los datos</li>
                                    <li>Comentarios con fecha y hora del backup</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Mostrar mensajes de éxito o error
if (isset($_SESSION['success'])) {
    echo '<script>alert("' . $_SESSION['success'] . '");</script>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<script>alert("' . $_SESSION['error'] . '");</script>';
    unset($_SESSION['error']);
}
?>
