<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once INCLUDES . '/functions.php';
init_app();
require_once BASE . '/classes/App.class.php';

$app = new App();
$db = $app->db;

$trabajadorId = isset($_GET['trabajador_id']) ? (int)$_GET['trabajador_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$direccionPlano = isset($_GET['direccion']) ? trim($_GET['direccion']) : 'Dirección';

$trab = $empresa = $tarjeta = null;
if ($trabajadorId > 0) {
    $trab = $db->fetchRow(
        "SELECT id, apellidos, apellidos_segundos, nombre, carnet_identidad, fecha_contratacion, fecha_baja FROM trabajadores WHERE id = :id",
        ['id' => $trabajadorId]
    );
    $empresa = $db->fetchRow("SELECT id, nombre FROM empresa ORDER BY id LIMIT 1");
    $tarjeta = $db->fetchRow(
        "SELECT id, periodo, fecha_inicio, fecha_cierre FROM tarjetas_snc225 WHERE trabajador_id = :tid ORDER BY id DESC LIMIT 1",
        ['tid' => $trabajadorId]
    );
}

function fmt_date($d){ if(!$d || $d==='0000-00-00') return ''; $t=strtotime($d); return $t?date('Y-m-d',$t):''; }

$primerApellido = $trab['apellidos'] ?? '';
$segundoApellido = $trab['apellidos_segundos'] ?? '';
$nombres        = $trab['nombre'] ?? '';
$expLaboral     = $trab['id'] ?? '';
$ci             = $trab['carnet_identidad'] ?? '';
$empresaNombre  = $empresa['nombre'] ?? '';
$codigo         = $trab['id'] ?? '';
$fechaAlta      = fmt_date($trab['fecha_contratacion'] ?? '');
$fechaBaja      = fmt_date($trab['fecha_baja'] ?? '');
$tarjetaNo      = $tarjeta['id'] ?? '';

// Registros de tarjetas por periodo para poblar SD_mes_anio
$sncRegs = [];
if ($trabajadorId > 0) {
    try {
        $sncRegs = $db->fetchAll(
            "SELECT periodo, salarios_devengados FROM tarjetas_snc225 WHERE trabajador_id = :tid ORDER BY periodo ASC",
            ['tid' => $trabajadorId]
        );
    } catch (Exception $e) {
        $sncRegs = [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tarjetas SNC 225</title>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #f0f0f0;
        padding: 20px;
    }
    .paper {
        background-color: white;
        width: 210mm; /* A4 width approximation */
        min-height: 297mm;
        margin: 0 auto;
        padding: 10mm;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        box-sizing: border-box;
        position: relative;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        font-size: 10px;
    }
    td, th {
        border: 1px solid black;
        padding: 2px 4px;
        vertical-align: top;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }
    
    /* Header Styles */
    .header-title {
        font-size: 14px;
        font-weight: bold;
        vertical-align: middle;
    }
    .header-small { font-size: 9px; }
    .label {
        font-size: 8px;
        color: #333;
        display: block;
        margin-bottom: 2px;
    }
    .input-area {
        height: 15px; /* Min height for writing */
    }
    
    /* Removing double borders between stacked tables */
    .no-top-border td { border-top: none; }
    
    /* Grid Styles */
    .grid-table { margin-top: 5px; }
    .grid-header {
        font-size: 9px;
        text-align: center;
        vertical-align: middle;
        background-color: #fff;
    }
    .col-label { width: 100px; }
    .col-data { width: calc((100% - 100px) / 10); text-align: center; }
    
    /* Nested table for dates */
    .date-table {
        width: 100%;
        height: 100%;
        margin: -2px -4px; /* Offset parent padding */
        border: none;
    }
    .date-table td {
        border: none;
        border-left: 1px solid black;
        border-bottom: 1px solid black;
    }
    .date-table tr:last-child td { border-bottom: none; }
    .date-table tr td:first-child { border-left: none; }

    .footer-code {
        position: absolute;
        bottom: 5mm;
        left: 10mm;
        font-size: 8px;
    }
</style>
</head>
<body>

<div class="paper">

    <!-- Main Header Section -->
    <table>
        <colgroup>
            <col style="width: 30%;">
            <col style="width: 50%;">
            <col style="width: 20%;">
        </colgroup>
        <tr>
            <td class="text-center header-small uppercase">
                Comite Estatal de Finanzas<br>
                Sistema Nacional de Contabilidad<br>
                <span class="bold">Datos del Trabajador</span>
            </td>
            <td class="text-center header-title uppercase">
                Registro de Salarios<br>y Tiempo de Servicios
            </td>
            <td>
                <span class="label">No <?php echo htmlspecialchars($tarjetaNo); ?></span>
                <div class="input-area"></div>
            </td>
        </tr>
    </table>

    <table class="no-top-border">
        <colgroup>
            <col style="width: 20%;">
            <col style="width: 20%;">
            <col style="width: 25%;">
            <col style="width: 15%;">
            <col style="width: 20%;">
        </colgroup>
        <tr>
            <td><span class="label">PRIMER APELLIDO</span><?php echo htmlspecialchars($primerApellido); ?><div class="input-area"></div></td>
            <td><span class="label">SEGUNDO APELLIDO</span><?php echo htmlspecialchars($segundoApellido); ?><div class="input-area"></div></td>
            <td><span class="label">NOMBRES</span><?php echo htmlspecialchars($nombres); ?><div class="input-area"></div></td>
            <td><span class="label">EXP. LABORAL No</span><?php echo htmlspecialchars($expLaboral); ?><div class="input-area"></div></td>
            <td><span class="label">C. IDENTIDAD No</span><?php echo htmlspecialchars($ci); ?><div class="input-area"></div></td>
        </tr>
    </table>

    <table class="no-top-border">
        <colgroup>
            <col style="width: 20%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
            <col style="width: 30%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
        </colgroup>
        <tr>
            <td><span class="label">ORGANISMO</span><div class="input-area"></div></td>
            <td><span class="label">EMPRESA</span><?php echo htmlspecialchars($empresaNombre); ?><div class="input-area"></div></td>
            <td><span class="label">CODIGO</span><?php echo htmlspecialchars($codigo); ?><div class="input-area"></div></td>
            <td><span class="label">DIRECCION</span><?php echo htmlspecialchars($direccionPlano); ?><div class="input-area"></div></td>
            <td colspan="2" style="padding: 0;">
                <table class="date-table">
                    <tr><td colspan="2" class="text-center header-small uppercase" style="border-bottom: 1px solid black;">FECHA DE</td></tr>
                    <tr>
                        <td class="text-center header-small uppercase" style="border-right: 1px solid black;">ALTA<br><span class="nowrap"><?php echo htmlspecialchars($empresaNombre); ?></span></td>
                        <td class="text-center header-small uppercase">BAJA<br><span class="nowrap"><?php echo htmlspecialchars($fechaBaja); ?></span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Data Grid Block 1 -->
    <table class="grid-table">
        <thead>
            <tr>
                <th class="col-label" style="border-top: 1px solid black;">&nbsp;</th>
                <th colspan="10" class="text-center bold uppercase" style="border-top: 1px solid black;">A&nbsp; Ñ&nbsp; O&nbsp; S</th>
            </tr>
                        <tr class="grid-header">
                <th class="col-label"></th>
                <!-- 5 Years columns -->
                <th class="anno" colspan="2">2025</th>
                <th class="anno" colspan="2">2026</th>
                <th class="anno" colspan="2">2027</th>
                <th class="anno" colspan="2">2028</th>
                <th class="anno" colspan="2">2029</th>
            </tr>
            <tr class="grid-header">
                <th class="col-label"></th>
                <!-- 5 Years columns -->
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
            </tr>
            
        </thead>
        <tbody>
            <!-- Months -->
            <tr><td class="mes">Enero</td><td id="DT_enero_2025"></td><td id="SD_enero_2025"></td><td id="DT_enero_2026"></td><td id="SD_enero_2026"></td><td id="DT_enero_2027"></td><td id="SD_enero_2027"></td><td id="DT_enero_2028"></td><td id="SD_enero_2028"></td><td id="DT_enero_2029"></td><td id="SD_enero_2029"></td><td id="DT_enero_2030"></td><td id="SD_enero_2030"></td></tr>
            <tr><td class="mes">Febrero</td><td id="DT_febrero_2025"></td><td id="SD_febrero_2025"></td><td id="DT_febrero_2026"></td><td id="SD_febrero_2026"></td><td id="DT_febrero_2027"></td><td id="SD_febrero_2027"></td><td id="DT_febrero_2028"></td><td id="SD_febrero_2028"></td><td id="DT_febrero_2029"></td><td id="SD_febrero_2029"></td><td id="DT_febrero_2030"></td><td id="SD_febrero_2030"></td></tr>
            <tr><td class="mes">Marzo</td><td id="DT_marzo_2025"></td><td id="SD_marzo_2025"></td><td id="DT_marzo_2026"></td><td id="SD_marzo_2026"></td><td id="DT_marzo_2027"></td><td id="SD_marzo_2027"></td><td id="DT_marzo_2028"></td><td id="SD_marzo_2028"></td><td id="DT_marzo_2029"></td><td id="SD_marzo_2029"></td><td id="DT_marzo_2030"></td><td id="SD_marzo_2030"></td></tr>
            <tr><td class="mes">Abril</td><td id="DT_abril_2025"></td><td id="SD_abril_2025"></td><td id="DT_abril_2026"></td><td id="SD_abril_2026"></td><td id="DT_abril_2027"></td><td id="SD_abril_2027"></td><td id="DT_abril_2028"></td><td id="SD_abril_2028"></td><td id="DT_abril_2029"></td><td id="SD_abril_2029"></td><td id="DT_abril_2030"></td><td id="SD_abril_2030"></td></tr>
            <tr><td class="mes">Mayo</td><td id="DT_mayo_2025"></td><td id="SD_mayo_2025"></td><td id="DT_mayo_2026"></td><td id="SD_mayo_2026"></td><td id="DT_mayo_2027"></td><td id="SD_mayo_2027"></td><td id="DT_mayo_2028"></td><td id="SD_mayo_2028"></td><td id="DT_mayo_2029"></td><td id="SD_mayo_2029"></td><td id="DT_mayo_2030"></td><td id="SD_mayo_2030"></td></tr>
            <tr><td class="mes">Junio</td><td id="DT_junio_2025"></td><td id="SD_junio_2025"></td><td id="DT_junio_2026"></td><td id="SD_junio_2026"></td><td id="DT_junio_2027"></td><td id="SD_junio_2027"></td><td id="DT_junio_2028"></td><td id="SD_junio_2028"></td><td id="DT_junio_2029"></td><td id="SD_junio_2029"></td><td id="DT_junio_2030"></td><td id="SD_junio_2030"></td></tr>
            <tr><td class="mes">Julio</td><td id="DT_julio_2025"></td><td id="SD_julio_2025"></td><td id="DT_julio_2026"></td><td id="SD_julio_2026"></td><td id="DT_julio_2027"></td><td id="SD_julio_2027"></td><td id="DT_julio_2028"></td><td id="SD_julio_2028"></td><td id="DT_julio_2029"></td><td id="SD_julio_2029"></td><td id="DT_julio_2030"></td><td id="SD_julio_2030"></td></tr>
            <tr><td class="mes">Agosto</td><td id="DT_agosto_2025"></td><td id="SD_agosto_2025"></td><td id="DT_agosto_2026"></td><td id="SD_agosto_2026"></td><td id="DT_agosto_2027"></td><td id="SD_agosto_2027"></td><td id="DT_agosto_2028"></td><td id="SD_agosto_2028"></td><td id="DT_agosto_2029"></td><td id="SD_agosto_2029"></td><td id="DT_agosto_2030"></td><td id="SD_agosto_2030"></td></tr>
            <tr><td class="mes">Septiembre</td><td id="DT_septiembre_2025"></td><td id="SD_septiembre_2025"></td><td id="DT_septiembre_2026"></td><td id="SD_septiembre_2026"></td><td id="DT_septiembre_2027"></td><td id="SD_septiembre_2027"></td><td id="DT_septiembre_2028"></td><td id="SD_septiembre_2028"></td><td id="DT_septiembre_2029"></td><td id="SD_septiembre_2029"></td><td id="DT_septiembre_2030"></td><td id="SD_septiembre_2030"></td></tr>
            <tr><td class="mes">Octubre</td><td id="DT_octubre_2025"></td><td id="SD_octubre_2025"></td><td id="DT_octubre_2026"></td><td id="SD_octubre_2026"></td><td id="DT_octubre_2027"></td><td id="SD_octubre_2027"></td><td id="DT_octubre_2028"></td><td id="SD_octubre_2028"></td><td id="DT_octubre_2029"></td><td id="SD_octubre_2029"></td><td id="DT_octubre_2030"></td><td id="SD_octubre_2030"></td></tr>
            <tr><td class="mes">Noviembre</td><td id="DT_noviembre_2025"></td><td id="SD_noviembre_2025"></td><td id="DT_noviembre_2026"></td><td id="SD_noviembre_2026"></td><td id="DT_noviembre_2027"></td><td id="SD_noviembre_2027"></td><td id="DT_noviembre_2028"></td><td id="SD_noviembre_2028"></td><td id="DT_noviembre_2029"></td><td id="SD_noviembre_2029"></td><td id="DT_noviembre_2030"></td><td id="SD_noviembre_2030"></td></tr>
            <!-- Totals and Signatures -->
            <tr><td class="bold">Total</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>Firma<br>Trabajador</td><td colspan="10"></td></tr>
            <tr><td>Firma J'<br>Personal</td><td colspan="10"></td></tr>
        </tbody>
    </table>

    <!-- Data Grid Block 2 (Identical to Block 1) -->
    <table class="grid-table no-top-border">
        <thead>
            <tr>
                <th class="col-label">&nbsp;</th>
                <th colspan="10" class="text-center bold uppercase">A&nbsp; Ñ&nbsp; O&nbsp; S</th>
            </tr>
                        <tr class="grid-header">
                <th class="col-label"></th>
                <!-- 5 Years columns -->
                <th colspan="2">2030</th>
                <th colspan="2">2031</th>
                <th colspan="2">2032</th>
                <th colspan="2">2033</th>
                <th colspan="2">2034</th>
            </tr>
            <tr class="grid-header">
                <th class="col-label"></th>
                <!-- 5 Years columns -->
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
                <th>Días<br>Trab</th><th>Salario<br>Devengado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Months -->
            <tr><td>Enero</td><td id="DT_enero_2030"></td><td id="SD_enero_2030"></td><td id="DT_enero_2031"></td><td id="SD_enero_2031"></td><td id="DT_enero_2032"></td><td id="SD_enero_2032"></td><td id="DT_enero_2033"></td><td id="SD_enero_2033"></td><td id="DT_enero_2034"></td><td id="SD_enero_2034"></td></tr>
            <tr><td>Febrero</td><td id="DT_febrero_2030"></td><td id="SD_febrero_2030"></td><td id="DT_febrero_2031"></td><td id="SD_febrero_2031"></td><td id="DT_febrero_2032"></td><td id="SD_febrero_2032"></td><td id="DT_febrero_2033"></td><td id="SD_febrero_2033"></td><td id="DT_febrero_2034"></td><td id="SD_febrero_2034"></td></tr>
            <tr><td>Marzo</td><td id="DT_marzo_2030"></td><td id="SD_marzo_2030"></td><td id="DT_marzo_2031"></td><td id="SD_marzo_2031"></td><td id="DT_marzo_2032"></td><td id="SD_marzo_2032"></td><td id="DT_marzo_2033"></td><td id="SD_marzo_2033"></td><td id="DT_marzo_2034"></td><td id="SD_marzo_2034"></td></tr>
            <tr><td>Abril</td><td id="DT_abril_2030"></td><td id="SD_abril_2030"></td><td id="DT_abril_2031"></td><td id="SD_abril_2031"></td><td id="DT_abril_2032"></td><td id="SD_abril_2032"></td><td id="DT_abril_2033"></td><td id="SD_abril_2033"></td><td id="DT_abril_2034"></td><td id="SD_abril_2034"></td></tr>
            <tr><td>Mayo</td><td id="DT_mayo_2030"></td><td id="SD_mayo_2030"></td><td id="DT_mayo_2031"></td><td id="SD_mayo_2031"></td><td id="DT_mayo_2032"></td><td id="SD_mayo_2032"></td><td id="DT_mayo_2033"></td><td id="SD_mayo_2033"></td><td id="DT_mayo_2034"></td><td id="SD_mayo_2034"></td></tr>
            <tr><td>Junio</td><td id="DT_junio_2030"></td><td id="SD_junio_2030"></td><td id="DT_junio_2031"></td><td id="SD_junio_2031"></td><td id="DT_junio_2032"></td><td id="SD_junio_2032"></td><td id="DT_junio_2033"></td><td id="SD_junio_2033"></td><td id="DT_junio_2034"></td><td id="SD_junio_2034"></td></tr>
            <tr><td>Julio</td><td id="DT_julio_2030"></td><td id="SD_julio_2030"></td><td id="DT_julio_2031"></td><td id="SD_julio_2031"></td><td id="DT_julio_2032"></td><td id="SD_julio_2032"></td><td id="DT_julio_2033"></td><td id="SD_julio_2033"></td><td id="DT_julio_2034"></td><td id="SD_julio_2034"></td></tr>
            <tr><td>Agosto</td><td id="DT_agosto_2030"></td><td id="SD_agosto_2030"></td><td id="DT_agosto_2031"></td><td id="SD_agosto_2031"></td><td id="DT_agosto_2032"></td><td id="SD_agosto_2032"></td><td id="DT_agosto_2033"></td><td id="SD_agosto_2033"></td><td id="DT_agosto_2034"></td><td id="SD_agosto_2034"></td></tr>
            <tr><td>Septiembre</td><td id="DT_septiembre_2030"></td><td id="SD_septiembre_2030"></td><td id="DT_septiembre_2031"></td><td id="SD_septiembre_2031"></td><td id="DT_septiembre_2032"></td><td id="SD_septiembre_2032"></td><td id="DT_septiembre_2033"></td><td id="SD_septiembre_2033"></td><td id="DT_septiembre_2034"></td><td id="SD_septiembre_2034"></td></tr>
            <tr><td>Octubre</td><td id="DT_octubre_2030"></td><td id="SD_octubre_2030"></td><td id="DT_octubre_2031"></td><td id="SD_octubre_2031"></td><td id="DT_octubre_2032"></td><td id="SD_octubre_2032"></td><td id="DT_octubre_2033"></td><td id="SD_octubre_2033"></td><td id="DT_octubre_2034"></td><td id="SD_octubre_2034"></td></tr>
            <tr><td>Noviembre</td><td id="DT_noviembre_2030"></td><td id="SD_noviembre_2030"></td><td id="DT_noviembre_2031"></td><td id="SD_noviembre_2031"></td><td id="DT_noviembre_2032"></td><td id="SD_noviembre_2032"></td><td id="DT_noviembre_2033"></td><td id="SD_noviembre_2033"></td><td id="DT_noviembre_2034"></td><td id="SD_noviembre_2034"></td></tr>
            <tr><td>Diciembre</td><td id="DT_diciembre_2030"></td><td id="SD_diciembre_2030"></td><td id="DT_diciembre_2031"></td><td id="SD_diciembre_2031"></td><td id="DT_diciembre_2032"></td><td id="SD_diciembre_2032"></td><td id="DT_diciembre_2033"></td><td id="SD_diciembre_2033"></td><td id="DT_diciembre_2034"></td><td id="SD_diciembre_2034"></td></tr>
            <!-- Totals and Signatures -->
            <tr><td class="bold">Total</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>Firma<br>Trabajador</td><td colspan="10"></td></tr>
            <tr><td>Firma J'<br>Personal</td><td colspan="10"></td></tr>
        </tbody>
    </table>

    <!-- Footer -->
    <table class="no-top-border" style="margin-top: 5px;">
        <tr>
            <td style="height: 50px;">
                <span class="label">OBSERVACIONES</span>
            </td>
        </tr>
    </table>

    <div class="footer-code">SC-4-08</div>

</div>
 <script>
      (function(){
        try {
          var data = <?php echo json_encode($sncRegs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
          if (!Array.isArray(data)) data = [];

          var monthNames = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

          var today = new Date();
          var ty = today.getFullYear();
          var tm = today.getMonth() + 1; // 1..12

          function monthsDiff(fromY, fromM, toY, toM) {
            return (toY * 12 + toM) - (fromY * 12 + fromM);
          }

          function numfmt(n){
            if (n === null || n === undefined || isNaN(n)) return '';
            return Number(n).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          }

          // Vaciar todas las celdas SD_ (salarios) antes de poblar
          try {
            var sdCells = document.querySelectorAll('[id^="SD_"]');
            sdCells.forEach(function(c){ c.textContent = ''; });
          } catch(_){}

          // Agregar por mes/año en caso de múltiples registros del mismo período
          var sums = Object.create(null);      // salarios por mes/año
          var daySums = Object.create(null);  // días trabajados por mes/año (meses*26)
          data.forEach(function(r){
            if (!r || !r.periodo) return;
            var d = new Date(r.periodo);
            if (isNaN(d.getTime())) return;
            var y = d.getFullYear();
            var m = d.getMonth() + 1; // 1..12
            var mesName = monthNames[m-1];
            var keySD = 'SD_' + mesName + '_' + y; // salario devengado
            var valSD = Number(r.salarios_devengados);
            if (!isFinite(valSD)) valSD = 0;
            sums[keySD] = (sums[keySD] || 0) + valSD;

            // Cálculo de meses transcurridos (ignorando días) disponible si se requiere mostrar
            var meses = monthsDiff(y, m, ty, tm);
            // Conversión a días trabajados según regla: días = meses * 26
            var dias = meses * 26;
            var keyDT = 'DT_' + mesName + '_' + y; // celda de "Días Trab"
            daySums[keyDT] = (daySums[keyDT] || 0) + dias;
          });

          // Escribir salarios en celdas SD_mes_año
          Object.keys(sums).forEach(function(cellId){
            var el = document.getElementById(cellId);
            if (el) el.textContent = numfmt(sums[cellId]);
          });

          // Escribir días trabajados en celdas DT_mes_año
          Object.keys(daySums).forEach(function(cellId){
            var el = document.getElementById(cellId);
            if (el) el.textContent = String(daySums[cellId]);
          });
        } catch (e) { /* silencioso */ }
      })();
    </script>
</body>
</html>