<?php
/**
 * VALIDADOR DE INTEGRIDAD DEL CALENDARIO PROFESIONAL
 * Verifica que todos los componentes están en su lugar
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validador - Calendario Profesional</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin: 0 0 10px;
        }
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #5cb85c;
            background: #f1f8f5;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .check-item.error {
            border-left-color: #d9534f;
            background: #fef5f5;
        }
        .check-item.warning {
            border-left-color: #f0ad4e;
            background: #fffcf0;
        }
        .check-icon {
            font-size: 20px;
            font-weight: bold;
        }
        .check-item.error .check-icon {
            color: #d9534f;
        }
        .check-item.warning .check-icon {
            color: #f0ad4e;
        }
        .check-item:not(.error):not(.warning) .check-icon {
            color: #5cb85c;
        }
        .check-content {
            flex: 1;
        }
        .check-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .check-desc {
            font-size: 13px;
            color: #666;
            margin: 4px 0 0;
        }
        .section {
            margin: 30px 0 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #8ab4f8;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .summary {
            background: linear-gradient(135deg, #e8f4ff 0%, #f0f8ff 100%);
            border: 2px solid #8ab4f8;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
        }
        .summary-status {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .summary-text {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .summary-subtext {
            color: #666;
            font-size: 14px;
            margin: 8px 0 0;
        }
        code {
            background: #f5f7fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Validador - Calendario Profesional</h1>
        <div class="subtitle">Verificación de integridad del sistema</div>

        <?php
        $checks = [];
        $base_path = __DIR__;

        // ========== ARCHIVOS ==========
        $files = [
            'modules/home/home.js' => 'JavaScript del calendario',
            'modules/home/home.php' => 'HTML y CSS del calendario',
            'classes/mdl.SubmayorVacaciones.php' => 'Backend API',
        ];

        foreach ($files as $path => $desc) {
            $full_path = $base_path . '/' . $path;
            if (file_exists($full_path)) {
                $size = filesize($full_path);
                $checks[] = [
                    'status' => 'ok',
                    'title' => "✓ Archivo: $path",
                    'desc' => "$desc - " . number_format($size) . " bytes"
                ];
            } else {
                $checks[] = [
                    'status' => 'error',
                    'title' => "✗ Archivo faltante: $path",
                    'desc' => "Este archivo es crítico para el funcionamiento"
                ];
            }
        }

        // ========== FUNCIONES JS ==========
        $home_js_path = $base_path . '/modules/home/home.js';
        if (file_exists($home_js_path)) {
            $js_content = file_get_contents($home_js_path);
            $functions_to_check = [
                'cargarCalendarioEventos' => 'Carga eventos del servidor',
                'renderizarCalendarioCompleto' => 'Renderiza el calendario',
                'generarTablaCalendario' => 'Genera la grilla',
                'formatearFechaISO' => 'Formatea fechas',
                'Dashboard.navCalendario' => 'Navegación entre meses'
            ];

            foreach ($functions_to_check as $func => $desc) {
                if (strpos($js_content, "function $func") !== false || strpos($js_content, "$func =") !== false) {
                    $checks[] = [
                        'status' => 'ok',
                        'title' => "✓ Función JavaScript: $func",
                        'desc' => $desc
                    ];
                } else {
                    $checks[] = [
                        'status' => 'warning',
                        'title' => "⚠ Función no encontrada: $func",
                        'desc' => "Puede causar errores en tiempo de ejecución"
                    ];
                }
            }
        }

        // ========== CSS CLASES ==========
        $home_php_path = $base_path . '/modules/home/home.php';
        if (file_exists($home_php_path)) {
            $php_content = file_get_contents($home_php_path);
            $css_classes = [
                '.calendario-grid' => 'Grilla del calendario',
                '.calendario-dia' => 'Estilo de cada día',
                '.calendario-header' => 'Encabezado con navegación',
                '.btn-nav-calendar' => 'Botones de navegación',
                '.evento-badge' => 'Indicadores de evento'
            ];

            foreach ($css_classes as $class => $desc) {
                if (strpos($php_content, $class) !== false) {
                    $checks[] = [
                        'status' => 'ok',
                        'title' => "✓ Clase CSS: $class",
                        'desc' => $desc
                    ];
                } else {
                    $checks[] = [
                        'status' => 'error',
                        'title' => "✗ Clase CSS faltante: $class",
                        'desc' => "Estilos rotos, el calendario no se verá correctamente"
                    ];
                }
            }
        }

        // ========== MÉTODO PHP ==========
        $mdl_path = $base_path . '/classes/mdl.SubmayorVacaciones.php';
        if (file_exists($mdl_path)) {
            $mdl_content = file_get_contents($mdl_path);
            if (strpos($mdl_content, '_get_calendario_eventos') !== false) {
                $checks[] = [
                    'status' => 'ok',
                    'title' => "✓ Método PHP: _get_calendario_eventos",
                    'desc' => "Backend API para obtener eventos"
                ];
            } else {
                $checks[] = [
                    'status' => 'error',
                    'title' => "✗ Método PHP no encontrado",
                    'desc' => "El API no funcionará sin este método"
                ];
            }
        }

        // Mostrar resultados
        $ok_count = 0;
        $error_count = 0;
        $warning_count = 0;

        echo '<div class="section">';
        echo '<div class="section-title">🔍 Verificación de Componentes</div>';

        foreach ($checks as $check) {
            if ($check['status'] === 'ok') $ok_count++;
            elseif ($check['status'] === 'error') $error_count++;
            else $warning_count++;

            echo '<div class="check-item ' . $check['status'] . '">';
            echo '<div class="check-icon">' . ($check['status'] === 'ok' ? '✓' : ($check['status'] === 'error' ? '✗' : '⚠')) . '</div>';
            echo '<div class="check-content">';
            echo '<p class="check-title">' . htmlspecialchars($check['title']) . '</p>';
            echo '<p class="check-desc">' . htmlspecialchars($check['desc']) . '</p>';
            echo '</div></div>';
        }

        echo '</div>';

        // Resumen
        echo '<div class="summary">';
        if ($error_count === 0 && $warning_count === 0) {
            echo '<div class="summary-status">🟢</div>';
            echo '<p class="summary-text">SISTEMA FUNCIONAL</p>';
        } elseif ($error_count === 0) {
            echo '<div class="summary-status">🟡</div>';
            echo '<p class="summary-text">CON ADVERTENCIAS</p>';
        } else {
            echo '<div class="summary-status">🔴</div>';
            echo '<p class="summary-text">ERRORES DETECTADOS</p>';
        }
        echo '<p class="summary-subtext">✓ OK: ' . $ok_count . ' | ⚠ Warnings: ' . $warning_count . ' | ✗ Errores: ' . $error_count . '</p>';
        echo '</div>';

        // Instrucciones
        echo '<div class="section">';
        echo '<div class="section-title">📋 Próximos Pasos</div>';
        echo '<div class="check-item">';
        echo '<div class="check-icon">1</div>';
        echo '<div class="check-content">';
        echo '<p class="check-title">Abre el Dashboard</p>';
        echo '<p class="check-desc">Accede a <code>http://localhost/</code></p>';
        echo '</div></div>';

        echo '<div class="check-item">';
        echo '<div class="check-icon">2</div>';
        echo '<div class="check-content">';
        echo '<p class="check-title">Verifica el calendario</p>';
        echo '<p class="check-desc">Debería aparecer en la segunda posición del dashboard</p>';
        echo '</div></div>';

        echo '<div class="check-item">';
        echo '<div class="check-icon">3</div>';
        echo '<div class="check-content">';
        echo '<p class="check-title">Abre la consola</p>';
        echo '<p class="check-desc">Presiona F12 → Console para ver logs de <code>[CALENDARIO]</code></p>';
        echo '</div></div>';

        echo '</div>';
        ?>
    </div>
</body>
</html>
