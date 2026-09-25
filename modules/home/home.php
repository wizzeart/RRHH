<?php
// Asegurarse de que tenemos acceso a la base de datos
global $data, $page;

// Determinar si es jefe de área para mostrar contenido limitado
$esJefeArea = (isset($app->rol) && $app->rol == 4);
?>

<!-- Dashboard Header -->

<?php if (!$esJefeArea): ?>
<div class="row">

    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Resumen Estadísticas</h3>
            </div>
        </div>
    </div>

    <style>
        /* Hover sutil para paneles clicables */
        a .panel.panel-colorful {
            transition: transform .12s ease-in-out, box-shadow .12s ease-in-out;
        }

        a .panel.panel-colorful:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(226, 224, 224, 0.83);
        }

        .mar-btm {
            margin-bottom: 8px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Fotos circulares */
        img[style*="border-radius: 50%"] {
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
    </style>
    <link rel="stylesheet" href="modules/home/matriuska.css">

    <!-- Contadores fila 1: existentes (4 elementos) -->
    <div class="panel-body" style="background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 50%, #dcdcdc 100%); ">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <!-- Total Trabajadores -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="?module=list-trabajadores" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver lista de trabajadores">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-users fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-trabajadores">0</span>
                                    <p>Trabajadores</p>
                                </div>
                            </div>
                        </a>
                    </div>


                    <!-- Promedio de Edad -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="?module=list-departamentos" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver detalle de departamentos">
                            <div class="panel panel-success panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-bar-chart fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="departamentos">0</span>
                                    <p>Departamentos</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Cargos Diferentes -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="?module=list-cargos" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver cargos en trabajadores">
                            <div class="panel panel-warning panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-briefcase fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-cargos">0</span>
                                    <p>Cargos</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Usuarios -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="?module=list-usuarios" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver listado de usuarios">
                            <div class="panel panel-info panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-user fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-usuarios">0</span>
                                    <p>Usuarios</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contadores fila 2: Subcontratos, Contratos, Capacitaciones, Bolsas (clickables) -->
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <!-- Subcontratos -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="#" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver listado de subcontratos">
                            <div class="panel panel-purple panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-sort-desc fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-subcontratos">0</span>
                                    <p>Subcontratos</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Contratos -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="#" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver listado de contratos">
                            <div class="panel panel-danger panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-file-text-o fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-contratos">0</span>
                                    <p>Contratos</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Capacitaciones -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="#" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver programas de capacitación">
                            <div class="panel panel-dark panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-graduation-cap fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-capacitaciones">0</span>
                                    <p>Capacitaciones</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Bolsas de Empleo -->
                    <div class="col-sm-6 col-lg-3 panel-card">
                        <a href="?module=list-bolsas_empleos" style="text-decoration:none;" data-toggle="tooltip"
                            title="Ver postulaciones de bolsa de empleo">
                            <div class="panel panel-mint panel-colorful">
                                <div class="pad-all text-center">
                                    <div class="mar-btm"><i class="fa fa-database fa-2x"></i></div>
                                    <span class="text-3x text-thin" id="total-bolsas">0</span>
                                    <p>Bolsa de Empleo</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contadores fila 3: centrados (Usuarios, Bajas Trabajadores) -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">


                <!-- Trabajadores dados de baja -->
                <!-- <div class="col-sm-6 col-lg-3">
                    <a href="?module=bajas-trabajadores" style="text-decoration:none;" data-toggle="tooltip" title="Ver trabajadores dados de baja">
                        <div class="panel panel-warning panel-colorful">
                            <div class="pad-all text-center">
                                <div class="mar-btm"><i class="fa fa-user-times fa-2x"></i></div>
                                <span class="text-3x text-thin" id="total-bajas-trabajadores">0</span>
                                <p>Trabajadores dados de baja</p>
                            </div>
                        </div>
                    </a>
                </div> -->
                <div class="col-sm-6 col-lg-3"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Scripts: load jQuery from CDN with local fallback, then the dashboard script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.jQuery || document.write('<script src="js/jquery-3.2.1.min.js"><\/script>');
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.33/jspdf.plugin.autotable.min.js"></script>
<script>
    // Asegurar que autoTable está registrado
    if (typeof window.jspdf !== 'undefined' && window.jspdf.jsPDF) {
        console.log("jsPDF cargado correctamente");
    }
</script>
<script type="text/javascript" src="modules/home/home.js"></script>

<?php if (!$esJefeArea): ?>
<!-- Estadísticas Hombres vs Mujeres -->
<div class="row" style="margin-top: 30px;">
    <div class="col-md-12">
        <div class="panel"
            style="background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 50%, #dcdcdc 100%); border: 1px solid #bdbdbd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div class="panel-heading"
                style="background: linear-gradient(135deg, #3a3a3a, #555555); border-bottom: 2px solid #888888;">
                <h3 class="panel-title"
                    style="color: #ffffff; text-shadow: 0 1px 3px rgba(249, 234, 234, 0.49); letter-spacing: 1px;">
                    <i class="fa fa-venus-mars" style="animation: pulse 2s infinite; margin-right: 8px; color: #4a90e2;"></i>
                    <span>PROPORCIÓN HOMBRES - MUJERES</span>
                </h3>
            </div>
            <div class="panel-body" style="padding: 20px; background: linear-gradient(to bottom, #ffffff, #f9f9f9);">
                <div id="sexo-stats-container" style="padding: 20px;">
                    <div class="text-center" style="color: #666666; padding: 40px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: #888888;"></i>
                        <p style="margin-top: 10px; letter-spacing: 1px; color: #777777;">Cargando estadísticas...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Estadísticas de Asistencias - Efecto Matriuska Futurista -->
<div class="row" style="margin-top: 30px;">
    <div class="col-md-12">
        <div class="panel"
            style="background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 50%, #dcdcdc 100%); border: 1px solid #bdbdbd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div class="panel-heading"
                style="background: linear-gradient(135deg, #3a3a3a, #555555); border-bottom: 2px solid #888888;">
                <h3 class="panel-title"
                    style="color: #ffffff; text-shadow: 0 1px 3px rgba(249, 234, 234, 0.49); letter-spacing: 1px;">
                    <i class="fa fa-users" style="animation: pulse 2s infinite; margin-right: 8px; color: #4a90e2;"></i>
                    <span>ASISTENCIAS DEL DÍA</span>
                </h3>
            </div>
            <div class="panel-body" style="padding: 20px; background: linear-gradient(to bottom, #ffffff, #f9f9f9);">
                <div id="asistencias-matriuska-container" style="padding: 20px;">
                    <!-- Matriuska layers se cargarán aquí -->
                    <div class="text-center" style="color: #666666; padding: 40px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: #888888;"></i>
                        <p style="margin-top: 10px; letter-spacing: 1px; color: #777777;">Cargando estadísticas...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Profesional Ausentes -->
<div id="modal-ausentes" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
" onclick="if(event.target === this) document.getElementById('modal-ausentes').style.display = 'none';">
    <div style="
        background: white;
        border-radius: 25px;
        padding: 45px;
        max-width: 1100px;
        width: 96%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        border: 1px solid #e0e0e0;
    ">
        <!-- Header -->
        <div
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 2px solid #f5f5f5;">
            <div style="flex: 1;">
                <h2 style="margin: 0 0 8px 0; color: #333333; font-size: 26px; font-weight: 700;">
                    <i class="fa fa-user-times"
                        style="color: #d9534f; margin-right: 12px; font-size: 24px;"></i>TRABAJADORES AUSENTES
                </h2>
                <p style="margin: 0; color: #888; font-size: 14px;"><i class="fa fa-info-circle"></i> Haz click en una
                    foto para ver más detalles</p>
            </div>
            <button onclick="document.getElementById('modal-ausentes').style.display = 'none';" style="
                background: #f8f8f8;
                border: 2px solid #e8e8e8;
                border-radius: 50%;
                width: 45px;
                height: 45px;
                cursor: pointer;
                font-size: 28px;
                color: #666;
                transition: all 0.25s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-weight: bold;
            " onmouseover="this.style.background='#d9534f'; this.style.color='white'; this.style.borderColor='#c1423a'; this.style.boxShadow='0 4px 12px rgba(217,83,79,0.3)';"
                onmouseout="this.style.background='#f8f8f8'; this.style.color='#666'; this.style.borderColor='#e8e8e8'; this.style.boxShadow='none';">×</button>
        </div>

        <!-- Grid de Fotos -->
        <div id="modal-ausentes-content" style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
            gap: 30px;
            padding: 15px 0;
            margin-bottom: 25px;
            min-height: 100px;
        ">
            <div style="grid-column: 1 / -1; text-align: center; color: #999; padding: 40px;"><i
                    class="fa fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Preview Ampliada -->
        <div id="preview-ausente" style="
            margin-top: 30px;
            padding: 35px;
            background: linear-gradient(135deg, #fef9f7 0%, #faf8f8 100%);
            border-radius: 18px;
            text-align: center;
            display: none;
            border: 2px solid #f0e8e6;
        ">
            <img id="preview-ausente-img" src="" style="
                max-width: 160px;
                max-height: 160px;
                border-radius: 50%;
                box-shadow: 0 10px 30px rgba(217,83,79,0.25);
                border: 5px solid #d9534f;
                cursor: pointer;
                transition: all 0.3s ease;
            " />
            <p id="preview-ausente-nombre" style="
                margin: 20px 0 0 0;
                color: #333333;
                font-weight: 700;
                font-size: 18px;
            "></p>
            <div id="preview-ausente-info" style="
                margin-top: 15px;
                color: #555;
                font-size: 13px;
                line-height: 2;
            "></div>
        </div>
    </div>
</div>

<!-- Modal Profesional Presentes -->
<div id="modal-presentes" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
" onclick="if(event.target === this) document.getElementById('modal-presentes').style.display = 'none';">
    <div style="
        background: white;
        border-radius: 25px;
        padding: 45px;
        max-width: 1100px;
        width: 96%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        border: 1px solid #e0e0e0;
    ">
        <!-- Header -->
        <div
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 2px solid #f5f5f5;">
            <div style="flex: 1;">
                <h2 style="margin: 0 0 8px 0; color: #333333; font-size: 26px; font-weight: 700;">
                    <i class="fa fa-user-check"
                        style="color: #4a90e2; margin-right: 12px; font-size: 24px;"></i>TRABAJADORES PRESENTES
                </h2>
                <p style="margin: 0; color: #888; font-size: 14px;"><i class="fa fa-info-circle"></i> Haz click en una
                    foto para ver más detalles</p>
            </div>
            <button onclick="document.getElementById('modal-presentes').style.display = 'none';" style="
                background: #f8f8f8;
                border: 2px solid #e8e8e8;
                border-radius: 50%;
                width: 45px;
                height: 45px;
                cursor: pointer;
                font-size: 28px;
                color: #666;
                transition: all 0.25s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-weight: bold;
            " onmouseover="this.style.background='#4a90e2'; this.style.color='white'; this.style.borderColor='#2e5fa3'; this.style.boxShadow='0 4px 12px rgba(74,144,226,0.3)';"
                onmouseout="this.style.background='#f8f8f8'; this.style.color='#666'; this.style.borderColor='#e8e8e8'; this.style.boxShadow='none';">×</button>
        </div>

        <!-- Grid de Fotos -->
        <div id="modal-presentes-content" style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
            gap: 30px;
            padding: 15px 0;
            margin-bottom: 25px;
            min-height: 100px;
        ">
            <div style="grid-column: 1 / -1; text-align: center; color: #999; padding: 40px;"><i
                    class="fa fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Preview Ampliada -->
        <div id="preview-presente" style="
            margin-top: 30px;
            padding: 35px;
            background: linear-gradient(135deg, #f0f7ff 0%, #f8fafb 100%);
            border-radius: 18px;
            text-align: center;
            display: none;
            border: 2px solid #d9e8f5;
        ">
            <img id="preview-presente-img" src="" style="
                max-width: 160px;
                max-height: 160px;
                border-radius: 50%;
                box-shadow: 0 10px 30px rgba(74,144,226,0.25);
                border: 5px solid #4a90e2;
                cursor: pointer;
                transition: all 0.3s ease;
            " />
            <p id="preview-presente-nombre" style="
                margin: 20px 0 0 0;
                color: #333333;
                font-weight: 700;
                font-size: 18px;
            "></p>
            <div id="preview-presente-info" style="
                margin-top: 15px;
                color: #555;
                font-size: 13px;
                line-height: 2;
            "></div>
        </div>
    </div>
</div>

<!-- Modal Profesional Vacaciones -->
<div id="modal-vacaciones" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
" onclick="if(event.target === this) document.getElementById('modal-vacaciones').style.display = 'none';">
    <div style="
        background: white;
        border-radius: 25px;
        padding: 45px;
        max-width: 1100px;
        width: 96%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        border: 1px solid #e0e0e0;
    ">
        <!-- Header -->
        <div
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 2px solid #f5f5f5;">
            <div style="flex: 1;">
                <h2 style="margin: 0 0 8px 0; color: #333333; font-size: 26px; font-weight: 700;">
                    <i class="fa fa-umbrella"
                        style="color: #f0ad4e; margin-right: 12px; font-size: 24px;"></i>TRABAJADORES DE VACACIONES
                </h2>
                <p style="margin: 0; color: #888; font-size: 14px;"><i class="fa fa-info-circle"></i> Trabajadores que están de vacaciones hoy</p>
            </div>
            <button onclick="document.getElementById('modal-vacaciones').style.display = 'none';" style="
                background: #f8f8f8;
                border: 2px solid #e8e8e8;
                border-radius: 50%;
                width: 45px;
                height: 45px;
                cursor: pointer;
                font-size: 28px;
                color: #666;
                transition: all 0.25s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-weight: bold;
            " onmouseover="this.style.background='#f0ad4e'; this.style.color='white'; this.style.borderColor='#e6b426'; this.style.boxShadow='0 4px 12px rgba(240,173,78,0.3)';"
                onmouseout="this.style.background='#f8f8f8'; this.style.color='#666'; this.style.borderColor='#e8e8e8'; this.style.boxShadow='none';">×</button>
        </div>

        <!-- Grid de Fotos -->
        <div id="modal-vacaciones-content" style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
            gap: 30px;
            padding: 15px 0;
            margin-bottom: 25px;
            min-height: 100px;
        ">
            <div style="grid-column: 1 / -1; text-align: center; color: #999; padding: 40px;"><i
                    class="fa fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Preview Ampliada Vacaciones -->
        <div id="preview-vacacion" style="
            margin-top: 30px;
            padding: 35px;
            background: linear-gradient(135deg, #f0f8f0 0%, #f8fafb 100%);
            border-radius: 18px;
            text-align: center;
            display: none;
            border: 2px solid #d9e8f5;
        ">
            <img id="preview-vacacion-img" src="" style="
                max-width: 200px;
                max-height: 200px;
                border-radius: 50%;
                box-shadow: 0 10px 30px rgba(240,173,78,0.25);
                border: 5px solid #f0ad4e;
                cursor: pointer;
                transition: all 0.3s ease;
            " />
            <p id="preview-vacacion-nombre" style="
                margin: 20px 0 0 0;
                color: #333333;
                font-weight: 700;
                font-size: 18px;
            "></p>
            <div id="preview-vacacion-info" style="
                margin-top: 15px;
                color: #555;
                font-size: 13px;
                line-height: 2;
            "></div>
        </div>
    </div>
</div>

<!-- Modal Profesional Especiales -->
<div id="modal-especiales" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
" onclick="if(event.target === this) document.getElementById('modal-especiales').style.display = 'none';">
    <div style="
        background: white;
        border-radius: 25px;
        padding: 45px;
        max-width: 1100px;
        width: 96%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        border: 1px solid #e0e0e0;
    ">
        <!-- Header -->
        <div
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 2px solid #f5f5f5;">
            <div style="flex: 1;">
                <h2 style="margin: 0 0 8px 0; color: #333333; font-size: 26px; font-weight: 700;">
                    <i class="fa fa-star"
                        style="color: #9b59b6; margin-right: 12px; font-size: 24px;"></i>TRABAJADORES ESPECIALES
                </h2>
                <p style="margin: 0; color: #888; font-size: 14px;"><i class="fa fa-info-circle"></i> Trabajadores con horario especial (tipo 3)</p>
            </div>
            <button onclick="document.getElementById('modal-especiales').style.display = 'none';" style="
                background: #f8f8f8;
                border: 2px solid #e8e8e8;
                border-radius: 50%;
                width: 45px;
                height: 45px;
                cursor: pointer;
                font-size: 28px;
                color: #666;
                transition: all 0.25s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-weight: bold;
            " onmouseover="this.style.background='#9b59b6'; this.style.color='white'; this.style.borderColor='#8e44ad'; this.style.boxShadow='0 4px 12px rgba(155,89,182,0.3)';"
                onmouseout="this.style.background='#f8f8f8'; this.style.color='#666'; this.style.borderColor='#e8e8e8'; this.style.boxShadow='none';">×</button>
        </div>

        <!-- Grid de Fotos -->
        <div id="modal-especiales-content" style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
            gap: 30px;
            padding: 15px 0;
            margin-bottom: 25px;
            min-height: 100px;
        ">
            <div style="grid-column: 1 / -1; text-align: center; color: #999; padding: 40px;"><i
                    class="fa fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Preview Ampliada Especiales -->
        <div id="preview-especial" style="
            margin-top: 30px;
            padding: 35px;
            background: linear-gradient(135deg, #f8f0ff 0%, #faf8fb 100%);
            border-radius: 18px;
            text-align: center;
            display: none;
            border: 2px solid #e8d5f2;
        ">
            <img id="preview-especial-img" src="" style="
                max-width: 200px;
                max-height: 200px;
                border-radius: 50%;
                box-shadow: 0 10px 30px rgba(155,89,182,0.25);
                border: 5px solid #9b59b6;
                cursor: pointer;
                transition: all 0.3s ease;
            " />
            <p id="preview-especial-nombre" style="
                margin: 20px 0 0 0;
                color: #333333;
                font-weight: 700;
                font-size: 18px;
            "></p>
            <div id="preview-especial-info" style="
                margin-top: 15px;
                color: #555;
                font-size: 13px;
                line-height: 2;
            "></div>
        </div>
    </div>
</div>

<!-- Lista de Trabajadores -->
<div class="row" style="margin-top:20px;" hidden>
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Últimos Trabajadores</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="trabajadores-list">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cargo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- filas generadas por JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listas adicionales: Cargos, Departamentos, Pases de Acceso -->
<div class="row" style="margin-top:20px;" hidden>
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Cargos</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="cargos-list">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <th>Nombre</th>
                                <th class="text-center">Salario (CUP/Hora)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6" hidden>
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Departamentos</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="departamentos-list">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Cantidad de Trabajadores</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$esJefeArea): ?>
<!-- CALENDARIO FUTURISTA: Vacaciones y Cumpleaños -->
<div class="row" style="margin-top: 40px; margin-bottom: 40px;">
    <div class="col-md-12">
        <div class="panel"
            style="background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 50%, #f0f3f7 100%); border: 2px solid #d0d8e8; box-shadow: 0 10px 40px rgba(200, 210, 230, 0.3);">
            <div class="panel-heading"
                style="background: linear-gradient(135deg, #e8f0f8, #f0f5fc); border-bottom: 3px solid #8ab4f8;">
                <h3 class="panel-title"
                    style="color: #2c3e50; text-shadow: 0 1px 3px rgba(255,255,255,0.5); letter-spacing: 2px; font-size: 18px;">
                    <i class="fa fa-calendar"
                        style="animation: pulse 2s infinite; margin-right: 12px; color: #f0ad4e; font-size: 20px;"></i>
                    <span>CALENDARIO DE EVENTOS - VACACIONES, CUMPLEAÑOS & INCIDENCIAS</span>
                </h3>
            </div>
            <div class="panel-body" style="padding: 30px; background: linear-gradient(to bottom, #ffffff, #f8fafb);">
                <div id="calendario-container" style="color: #2c3e50;">
                    <div class="text-center" style="padding: 60px 20px;">
                        <i class="fa fa-spinner fa-spin"
                            style="font-size: 48px; color: #8ab4f8; animation: pulse 2s infinite;"></i>
                        <p style="margin-top: 20px; letter-spacing: 1px; color: #666666; font-size: 16px;">Construyendo
                            calendario interactivo...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS para Calendario Futurista -->
<style>
    .calendario-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
        border-radius: 12px;
        border: 1px solid #d0d8e8;
        box-shadow: 0 4px 15px rgba(200, 210, 230, 0.3);
        overflow: hidden;
    }

    .calendario-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
        border-bottom: 2px solid #e0e8f0;
    }

    .calendario-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 24px;
        font-weight: 600;
        flex: 1;
        text-align: center;
    }

    .btn-nav-calendar {
        background: linear-gradient(135deg, #8ab4f8 0%, #5a96f0 100%);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(138, 180, 248, 0.3);
    }

    .btn-nav-calendar:hover {
        background: linear-gradient(135deg, #5a96f0 0%, #3a76d0 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(138, 180, 248, 0.5);
    }

    .calendario-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #d0d8e8;
        padding: 1px;
        min-height: 400px;
    }

    .calendario-dias-header {
        display: contents;
    }

    .calendario-dias-header div {
        background: linear-gradient(180deg, #f5f7fa 0%, #e8f0f8 100%);
        padding: 12px 8px;
        font-weight: 600;
        color: #2c3e50;
        text-align: center;
        border-bottom: 2px solid #8ab4f8;
        font-size: 12px;
    }

    .calendario-semana {
        display: contents;
    }

    .calendario-dia {
        background: white;
        padding: 8px;
        min-height: 80px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid #e8f0f8;
        display: flex;
        flex-direction: column;
    }

    .calendario-dia:hover {
        background: linear-gradient(135deg, #f8fbff 0%, #f0f5fa 100%);
        box-shadow: 0 4px 12px rgba(138, 180, 248, 0.2);
        transform: translateY(-2px);
    }

    .calendario-dia.otro-mes {
        background: #f9f9f9;
        opacity: 0.5;
    }

    .calendario-dia.otro-mes .numero-dia {
        color: #ccc;
    }

    .calendario-dia.hoy {
        background: linear-gradient(135deg, #e8f4ff 0%, #f0f8ff 100%);
        border: 2px solid #8ab4f8;
        box-shadow: 0 0 12px rgba(138, 180, 248, 0.3);
    }

    .numero-dia {
        font-weight: 700;
        font-size: 16px;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .evento-badge {
        font-size: 18px;
        margin: 4px 0;
    }

    .evento-badge.vacaciones {
        color: #5cb85c;
    }

    .evento-badge.cumpleanos {
        color: #d9534f;
    }

    .evento-badge.ambos {
        color: #f0ad4e;
    }

    .evento-count {
        font-size: 11px;
        background: #8ab4f8;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-top: auto;
    }

    .calendario-leyenda {
        display: flex;
        gap: 20px;
        padding: 15px 20px;
        background: #f9fafb;
        border-top: 1px solid #e0e8f0;
        justify-content: center;
        flex-wrap: wrap;
    }

    .leyenda-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #2c3e50;
    }

    .leyenda-badge {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
    }

    .leyenda-badge.vacaciones {
        background: linear-gradient(135deg, #5cb85c, #4aa84a);
    }

    .leyenda-badge.cumpleanos {
        background: linear-gradient(135deg, #d9534f, #c9453f);
    }

    .leyenda-badge.ambos {
        background: linear-gradient(135deg, #f0ad4e, #e09a3d);
    }

    .leyenda-badge.incidencias {
        background: linear-gradient(135deg, #333333, #1a1a1a);
    }

    .calendario-error {
        padding: 40px 20px;
        text-align: center;
        background: linear-gradient(135deg, #fff5f5 0%, #fffbfb 100%);
        border-radius: 8px;
        color: #2c3e50;
    }

    .calendario-error i {
        font-size: 48px;
        color: #d9534f;
        margin-bottom: 15px;
        display: block;
    }

    .calendario-error h4 {
        margin: 15px 0 10px;
        color: #d9534f;
    }

    .calendario-error p {
        font-size: 12px;
        color: #888;
        margin: 10px 0;
    }

    .btn-reintentar {
        background: #d9534f;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 15px;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .btn-reintentar:hover {
        background: #c9453f;
    }

    @media (max-width: 768px) {
        .calendario-grid {
            min-height: 300px;
        }

        .calendario-dia {
            min-height: 60px;
            padding: 6px;
            font-size: 12px;
        }

        .numero-dia {
            font-size: 13px;
        }

        .evento-badge {
            font-size: 14px;
        }

        .calendario-header h2 {
            font-size: 18px;
        }

        .btn-nav-calendar {
            padding: 8px 12px;
            font-size: 12px;
        }
    }

    /* Estilos para Eventos Personalizados */
    .evento-badge.personalizado {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid;
        font-size: 12px;
        line-height: 16px;
        text-align: center;
        margin: 2px 0;
        font-weight: bold;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .evento-badge.personalizado:hover {
        transform: scale(1.15);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
    }

    .leyenda-badge.personalizado {
        background: linear-gradient(135deg, #f0ad4e, #e09a3d);
        border: 2px solid #f0ad4e;
    }
</style>
<?php endif; ?>

<!-- Estadísticas de Vacaciones -->
<!-- <div class="row" style="margin-top: 30px;">
    <div class="col-md-12">
        <div class="panel" style="background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 50%, #dcdcdc 100%); border: 1px solid #bdbdbd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div class="panel-heading" style="background: linear-gradient(135deg, #3a3a3a, #555555); border-bottom: 2px solid #888888;">
                <h3 class="panel-title" style="color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.3); letter-spacing: 1px;">
                    <i class="fa fa-calendar-o" style="animation: pulse 2s infinite; margin-right: 8px; color: #f0ad4e;"></i>
                    <span>VACACIONES</span>
                </h3>
            </div>
            <div class="panel-body" style="padding: 20px; background: linear-gradient(to bottom, #ffffff, #f9f9f9);">
                <div id="vacaciones-estadisticas-container" style="padding: 20px;">
                   
                    <div class="text-center" style="color: #666666; padding: 40px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: #888888;"></i>
                        <p style="margin-top: 10px; letter-spacing: 1px; color: #777777;">Cargando estadísticas...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal para Crear/Editar Evento Personalizado -->
<div class="modal fade" id="modalEventoPersonalizado" tabindex="-1" role="dialog" aria-labelledby="modalEventoLabel"
    style="z-index: 1050;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" id="modalEventoHeader"
                style="background: linear-gradient(135deg, #f0ad4e 0%, #f9a825 100%); border: none;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalEventoLabel" style="color: white; font-weight: 600;">
                    <i class="fa fa-star" style="margin-right: 10px;"></i>Nuevo Evento Personalizado
                </h4>
            </div>
            <div class="modal-body">
                <form id="formEventoPersonalizado">
                    <input type="hidden" id="eventoId" value="">
                    <input type="hidden" id="eventoIsIncidencia" value="0">
                    <input type="hidden" id="eventoTrabajadorId" value="">

                    <!-- Toggle Incidencia -->
                    <div class="form-group"
                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding: 10px; background: #f9f9f9; border-radius: 5px;">
                        <label for="toggleIncidencia" style="font-weight: 600; color: #2c3e50; margin: 0;">
                            <i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>Incidencia
                        </label>
                        <div class="toggle-switch"
                            style="position: relative; display: inline-block; width: 50px; height: 24px;">
                            <input type="checkbox" id="toggleIncidencia" class="toggle-checkbox"
                                style="opacity: 0; width: 0; height: 0;">
                            <label for="toggleIncidencia" class="toggle-label"
                                style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; display: block;">
                                <span class="toggle-inner"
                                    style="position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                            </label>
                            <style>
                                .toggle-checkbox:checked+.toggle-label {
                                    background-color: #000 !important;
                                }

                                .toggle-checkbox:checked+.toggle-label .toggle-inner {
                                    left: 29px !important;
                                }
                            </style>
                        </div>
                    </div>

                    <!-- Select Trabajadores (solo para Incidencias) -->
                    <div class="form-group" id="groupTrabajador" style="display: none;">
                        <label for="eventoTrabajador" style="font-weight: 600; color: #2c3e50;">Trabajador *</label>
                        <select class="form-control" id="eventoTrabajador" style="border: 1px solid #d0d8e8;">
                            <option value="">-- Seleccionar trabajador --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="eventoFecha" style="font-weight: 600; color: #2c3e50;">Fecha del Evento *</label>
                        <input type="date" class="form-control" id="eventoFecha" required
                            style="border: 1px solid #d0d8e8;">
                    </div>

                    <div class="form-group" id="groupNombre">
                        <label for="eventoNombre" style="font-weight: 600; color: #2c3e50;">Nombre del Evento *</label>
                        <input type="text" class="form-control" id="eventoNombre" placeholder="Ej: Reunión, Viaje, etc."
                            required style="border: 1px solid #d0d8e8;">
                    </div>

                    <div class="form-group">
                        <label for="eventoDescripcion" style="font-weight: 600; color: #2c3e50;">Descripción</label>
                        <textarea class="form-control" id="eventoDescripcion" rows="3"
                            placeholder="Detalle del evento..."
                            style="border: 1px solid #d0d8e8; resize: vertical;"></textarea>
                    </div>

                    <div class="form-group" id="groupColor">
                        <label for="eventoColor" style="font-weight: 600; color: #2c3e50;">Color del Evento</label>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <input type="color" class="form-control" id="eventoColor" value="#f0ad4e"
                                style="width: 60px; height: 40px; padding: 2px; border: 1px solid #d0d8e8; cursor: pointer;">
                            <select class="form-control" style="flex: 1; border: 1px solid #d0d8e8;"
                                onchange="document.getElementById('eventoColor').value = this.value;">
                                <option value="#f0ad4e" style="background: #f0ad4e;">🟡 Amarillo (Predeterminado)
                                </option>
                                <option value="#5cb85c" style="background: #5cb85c;">🟢 Verde</option>
                                <option value="#5bc0de" style="background: #5bc0de;">🔵 Azul</option>
                                <option value="#d9534f" style="background: #d9534f;">🔴 Rojo</option>
                                <option value="#9b59b6" style="background: #9b59b6;">🟣 Morado</option>
                                <option value="#e74c3c" style="background: #e74c3c;">🟠 Naranja</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e8f0f8;">
                <button type="button" class="btn btn-default" data-dismiss="modal"
                    style="border: 1px solid #d0d8e8;">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnGuardarEvento"
                    style="background: linear-gradient(135deg, #f0ad4e 0%, #f9a825 100%); border: none; color: white;">
                    <i class="fa fa-save" style="margin-right: 5px;"></i>Guardar Evento
                </button>
                <button type="button" class="btn btn-danger" id="btnEliminarEvento" style="display: none;">
                    <i class="fa fa-trash" style="margin-right: 5px;"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Lista de Eventos del Día -->
<div class="modal fade" id="modalListaEventos" tabindex="-1" role="dialog" aria-labelledby="modalListaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #5bc0de 0%, #46b8da 100%); border: none;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalListaLabel" style="color: white; font-weight: 600;">
                    <i class="fa fa-list-ul" style="margin-right: 10px;"></i>Eventos del Día
                </h4>
            </div>
            <div class="modal-body" style="background-color: #f9f9f9; padding: 0;">
                <div id="listaEventosContainer" style="max-height: 400px; overflow-y: auto;">
                    <!-- Lista de eventos se carga aquí -->
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e8f0f8;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnNuevoEventoDia">
                    <i class="fa fa-plus" style="margin-right: 5px;"></i>Nuevo Evento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lista de Incidencias -->
<div class="modal fade" id="modalListaIncidencias" tabindex="-1" role="dialog"
    aria-labelledby="modalListaIncidenciasLabel" style="z-index: 1040;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #333333 0%, #1a1a1a 100%); color: white; border-bottom: 3px solid #555;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalListaIncidenciasLabel">
                    <i class="fa fa-exclamation-circle" style="margin-right: 10px;"></i>Lista de Incidencias
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <!-- Barra de búsqueda -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-8">
                        <input type="text" id="buscarIncidencia" class="form-control"
                            placeholder="Buscar por trabajador, fecha o descripción..."
                            style="border: 1px solid #ddd; padding: 10px;">
                    </div>
                    <div class="col-md-4" style="text-align: right;">
                        <button class="btn btn-danger" id="btnExportarIncidenciasPDF"
                            onclick="Dashboard.exportarIncidenciasPDF()">
                            <i class="fa fa-file-pdf-o" style="margin-right: 5px;"></i>Exportar PDF
                        </button>
                    </div>
                </div>

                <!-- Tabla de incidencias -->
                <div id="tablaIncidenciasContainer" style="overflow-x: auto;">
                    <table class="table table-striped table-hover" id="tablaIncidencias" style="margin-bottom: 0;">
                        <thead style="background: #f5f5f5; border-top: 2px solid #333;">
                            <tr>
                                <th style="color: #333; font-weight: bold;">Fecha</th>
                                <th style="color: #333; font-weight: bold;">Trabajador</th>
                                <th style="color: #333; font-weight: bold;">Nombre</th>
                                <th style="color: #333; font-weight: bold;">Descripción</th>
                                <th style="color: #333; font-weight: bold;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoIncidencias">
                            <!-- Se llena dinámicamente -->
                        </tbody>
                    </table>
                </div>
                <div id="incidenciasVacias" style="text-align: center; padding: 40px; color: #999;">
                    <i class="fa fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                    <p>No hay incidencias registradas</p>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="Dashboard.abrirModalEvento()">
                    <i class="fa fa-plus" style="margin-right: 5px;"></i>Nueva Incidencia
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de justificación de ausencia -->
<div class="modal fade" id="modalAsistencia" tabindex="-1" role="dialog" aria-labelledby="modalAsistenciaLabel" style="z-index: 10000;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalAsistenciaLabel">Justificar Ausencia</h4>
      </div>
      <div class="modal-body">
        <div style="display: flex; align-items: center; margin-bottom: 20px;">
          <div style="margin-right: 15px;">
            <img id="trabajador-foto" src="" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;" />
          </div>
          <div>
            <h5 id="trabajador-nombre" style="margin: 0; font-weight: 600; color: #333;"></h5>
            <p id="trabajador-info" style="margin: 5px 0 0 0; color: #666; font-size: 14px;"></p>
          </div>
        </div>
        
        <form id="formAsistencia">
          <input type="hidden" name="id" id="asistencia_id">
          <input type="hidden" name="trabajador_id" id="trabajador_id">

          <div class="form-group">
            <label for="fecha">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" disabled>
          </div>

          <div class="row" hidden>
            <div class="col-md-6">
              <div class="form-group">
                <label for="hora_entrada">Hora de Entrada</label>
                <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="hora_salida">Hora de Salida</label>
                <input type="time" class="form-control" id="hora_salida" name="hora_salida" disabled>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Tipo de Ausencia</label>
            <select class="form-control" id="tipo_ausencia" name="tipo_ausencia" required>
              <option value="">Seleccione un tipo</option>
              <option value="Injustificada">Injustificada</option>
              <option value="Justificada">Justificada</option>
              <option value="Enfermedad">Enfermedad</option>
              <option value="Licencia de Maternidad">Licencia de Maternidad</option>
            </select>
          </div>

          <div class="form-group">
            <label>Descripción</label>
            <textarea class="form-control" id="justificacion" name="justificacion" rows="3" placeholder="Ingrese la descripción de la ausencia" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar-asistencia">Guardar</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/home-admin-vacations.php'; ?>