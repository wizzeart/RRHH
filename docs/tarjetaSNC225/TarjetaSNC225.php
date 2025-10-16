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
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Tabla SNC225 - Carta Landscape</title>
<style>
  /* Página / impresión en Carta apaisada */
  @page { size: Letter landscape; margin: 18mm; }
  html, body { height: 100%; margin: 0; padding: 0; }

  /* Contenedor */
  .sheet {
    box-sizing: border-box;
    width: 11in;               /* Carta landscape width */
    max-width: 100%;
    margin: 8mm auto;
    font-family: "Times New Roman", Times, serif;
    font-size: 12px;
    color: #000;
  }

  table.snc {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  table.snc th,
  table.snc td {
    border: 1px solid #000;
    padding: 5px 8px;
    vertical-align: middle;
    word-wrap: break-word;
  }

  /* Encabezados */
  table.snc thead th {
    text-align: center;
    font-weight: bold;
    font-size: 13px;
  }

  .center { text-align: center; }
  .left { text-align: left; }
  .bold { font-weight: 700; }

  .mes { font-weight: 700; width: 9%; white-space: nowrap; }

  .empty-row td { height: 24px; }

  .firma td { height: 36px; }

  /* Ajuste de anchos relativos para Carta */
  td:nth-child(1) { width: 10%; }
  td:nth-child(2), td:nth-child(3), td:nth-child(4) { width: 8.5%; }
  td:nth-child(5), td:nth-child(6) { width: 8.5%; }
  td:nth-child(7), td:nth-child(8) { width: 8.5%; }
  td:nth-child(9), td:nth-child(10), td:nth-child(11), td:nth-child(12) { width: auto; }

  th, td { vertical-align: middle; }

  @media print {
    .sheet { width: 100%; margin: 0; }
    table.snc th, table.snc td { font-size: 11px; padding: 4px 6px; }
  }

  /* Pequeño ajuste para que textos largos no reduzcan demasiado la altura */
  .nowrap { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }




              font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }
        .header-main-title {
            font-size: 12px;
            font-weight: bold;
        }
        .header-sub-title {
            font-size: 10px;
        }
        .no-border {
            border: none;
        }
        .text-left {
            text-align: left;
        }
        .field-label {
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            padding-bottom: 0;
            border-bottom: none;
        }
        .field-value {
            height: 20px;
            border-top: none;
        }
        .year-header td {
            font-weight: bold;
            font-size: 14px;
        }
        .month-col {
            text-align: left;
            font-weight: bold;
        }
        .observations {
            height: 60px;
            text-align: left;
            vertical-align: top;
            font-weight: bold;
        }
        .footer-note {
            text-align: left;
            font-size: 8px;
            padding-top: 10px;
        }
</style>
</head>
<body>
  <div class="sheet">
    <table class="snc" role="table" aria-label="Tabla SNC225">
      <thead>
      <tr>
        <th colspan="4">COMTE ESTATAL DE FINANZAS SISTEMA NACIONAL DE CONTABILIDAD DATOS DEL TRABAJADOR</th>
        <th colspan="5">REGISTRO DE SALARIOS Y TIEMPO DE SERVICIOS</th>
        <th colspan="4">No <?php echo htmlspecialchars($tarjetaNo); ?></th>
      </tr>
      </thead>

      <tbody>
        <tr>
          <td colspan="3" class="center bold">PRIMER APELLIDO<br><span class="nowrap"><?php echo htmlspecialchars($primerApellido); ?></span></td>
          <td colspan="3" class="center bold">SEGUNDO APELLIDO<br><span class="nowrap"><?php echo htmlspecialchars($segundoApellido); ?></span></td>
          <td colspan="3" class="center bold">NOMBRES<br><span class="nowrap"><?php echo htmlspecialchars($nombres); ?></span></td>
          <td colspan="2" class="center bold">EXP LABORAL No<br><span class="nowrap"><?php echo htmlspecialchars($expLaboral); ?></span></td>
          <td colspan="2" class="center bold">C IDENTIDAD No<br><span class="nowrap"><?php echo htmlspecialchars($ci); ?></span></td>
        </tr>

        <tr>
          <td rowspan="2" class="center bold">ORGANISMO</td>
          <td colspan="3" rowspan="2" class="center bold">EMPRESA<br><span class="nowrap"><?php echo htmlspecialchars($empresaNombre); ?></span></td>
          <td colspan="2" rowspan="2" class="center bold">CÓDIGO<br><span class="nowrap"><?php echo htmlspecialchars($codigo); ?></span></td>
          <td colspan="3" rowspan="2" class="center bold">DIRECCIÓN<br><span class="nowrap"><?php echo htmlspecialchars($direccionPlano); ?></span></td>
          <td colspan="4" class="center bold">FECHA DE</td>
        </tr>

        <tr>
          <td colspan="2" class="center bold">ALTA<br><span class="nowrap"><?php echo htmlspecialchars($fechaAlta); ?></span></td>
          <td colspan="2" class="center bold">BAJA<br><span class="nowrap"><?php echo htmlspecialchars($fechaBaja); ?></span></td>
        </tr>

        <tr>
          <td rowspan="3"></td>
          <td colspan="12" class="center bold">A Ñ O S</td>
        </tr>

        <tr>
          <td colspan="2" align="center">2025</td>
          <td colspan="2" align="center">2026</td>
          <td colspan="2" align="center">2027</td>
          <td colspan="2" align="center">2028</td>
          <td colspan="2" align="center">2029</td>
          <td colspan="2" align="center">2030</td>
        </tr>

        <tr>
          <td class="center">Dias Trab</td>
          <td class="center">Salario Devengado</td>
          <td class="center">Dias Trab</td>
          <td class="center">Salario Devengado</td>
          <td class="center">Dias Trab</td>
          <td class="center">Salario Devengado</td>
          <td class="center">Dias Trab</td>
          <td class="center">Salario Devengado</td>
          <td class="center">Dias Trab</td>
          <td class="center">Salario Devengado</td>
          <td class="center">Dias Trab</td>
          <td class="center">Salario Devengado</td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Enero</td>
          <td id="DT_enero_2025"></td><td id="SD_enero_2025"></td><td id="DT_enero_2026"></td><td id="SD_enero_2026"></td><td id="DT_enero_2027"></td><td id="SD_enero_2027"></td><td id="DT_enero_2028"></td><td id="SD_enero_2028"></td><td id="DT_enero_2029"></td><td id="SD_enero_2029"></td><td id="DT_enero_2030"></td><td id="SD_enero_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Febrero</td>
          <td id="DT_febrero_2025"></td><td id="SD_febrero_2025"></td><td id="DT_febrero_2026"></td><td id="SD_febrero_2026"></td><td id="DT_febrero_2027"></td><td id="SD_febrero_2027"></td><td id="DT_febrero_2028"></td><td id="SD_febrero_2028"></td><td id="DT_febrero_2029"></td><td id="SD_febrero_2029"></td><td id="DT_febrero_2030"></td><td id="SD_febrero_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Marzo</td>
          <td id="DT_marzo_2025"></td><td id="SD_marzo_2025"></td><td id="DT_marzo_2026"></td><td id="SD_marzo_2026"></td><td id="DT_marzo_2027"></td><td id="SD_marzo_2027"></td><td id="DT_marzo_2028"></td><td id="SD_marzo_2028"></td><td id="DT_marzo_2029"></td><td id="SD_marzo_2029"></td><td id="DT_marzo_2030"></td><td id="SD_marzo_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Abril</td>
          <td id="DT_abril_2025"></td><td id="SD_abril_2025"></td><td id="DT_abril_2026"></td><td id="SD_abril_2026"></td><td id="DT_abril_2027"></td><td id="SD_abril_2027"></td><td id="DT_abril_2028"></td><td id="SD_abril_2028"></td><td id="DT_abril_2029"></td><td id="SD_abril_2029"></td><td id="DT_abril_2030"></td><td id="SD_abril_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Mayo</td>
          <td id="DT_mayo_2025"></td><td id="SD_mayo_2025"></td><td id="DT_mayo_2026"></td><td id="SD_mayo_2026"></td><td id="DT_mayo_2027"></td><td id="SD_mayo_2027"></td><td id="DT_mayo_2028"></td><td id="SD_mayo_2028"></td><td id="DT_mayo_2029"></td><td id="SD_mayo_2029"></td><td id="DT_mayo_2030"></td><td id="SD_mayo_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Junio</td>
          <td id="DT_junio_2025"></td><td id="SD_junio_2025"></td><td id="DT_junio_2026"></td><td id="SD_junio_2026"></td><td id="DT_junio_2027"></td><td id="SD_junio_2027"></td><td id="DT_junio_2028"></td><td id="SD_junio_2028"></td><td id="DT_junio_2029"></td><td id="SD_junio_2029"></td><td id="DT_junio_2030"></td><td id="SD_junio_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Julio</td>
          <td id="DT_julio_2025"></td><td id="SD_julio_2025"></td><td id="DT_julio_2026"></td><td id="SD_julio_2026"></td><td id="DT_julio_2027"></td><td id="SD_julio_2027"></td><td id="DT_julio_2028"></td><td id="SD_julio_2028"></td><td id="DT_julio_2029"></td><td id="SD_julio_2029"></td><td id="DT_julio_2030"></td><td id="SD_julio_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Agosto</td>
          <td id="DT_agosto_2025"></td><td id="SD_agosto_2025"></td><td id="DT_agosto_2026"></td><td id="SD_agosto_2026"></td><td id="DT_agosto_2027"></td><td id="SD_agosto_2027"></td><td id="DT_agosto_2028"></td><td id="SD_agosto_2028"></td><td id="DT_agosto_2029"></td><td id="SD_agosto_2029"></td><td id="DT_agosto_2030"></td><td id="SD_agosto_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Septiembre</td>
          <td id="DT_septiembre_2025"></td><td id="SD_septiembre_2025"></td><td id="DT_septiembre_2026"></td><td id="SD_septiembre_2026"></td><td id="DT_septiembre_2027"></td><td id="SD_septiembre_2027"></td><td id="DT_septiembre_2028"></td><td id="SD_septiembre_2028"></td><td id="DT_septiembre_2029"></td><td id="SD_septiembre_2029"></td><td id="DT_septiembre_2030"></td><td id="SD_septiembre_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Octubre</td>
          <td id="DT_octubre_2025"></td><td id="SD_octubre_2025"></td><td id="DT_octubre_2026"></td><td id="SD_octubre_2026"></td><td id="DT_octubre_2027"></td><td id="SD_octubre_2027"></td><td id="DT_octubre_2028"></td><td id="SD_octubre_2028"></td><td id="DT_octubre_2029"></td><td id="SD_octubre_2029"></td><td id="DT_octubre_2030"></td><td id="SD_octubre_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Noviembre</td>
          <td id="DT_noviembre_2025"></td><td id="SD_noviembre_2025"></td><td id="DT_noviembre_2026"></td><td id="SD_noviembre_2026"></td><td id="DT_noviembre_2027"></td><td id="SD_noviembre_2027"></td><td id="DT_noviembre_2028"></td><td id="SD_noviembre_2028"></td><td id="DT_noviembre_2029"></td><td id="SD_noviembre_2029"></td><td id="DT_noviembre_2030"></td><td id="SD_noviembre_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Diciembre</td>
          <td id="DT_diciembre_2025"></td><td id="SD_diciembre_2025"></td><td id="DT_diciembre_2026"></td><td id="SD_diciembre_2026"></td><td id="DT_diciembre_2027"></td><td id="SD_diciembre_2027"></td><td id="DT_diciembre_2028"></td><td id="SD_diciembre_2028"></td><td id="DT_diciembre_2029"></td><td id="SD_diciembre_2029"></td><td id="DT_diciembre_2030"></td><td id="SD_diciembre_2030"></td>
        </tr>

        <tr class="empty-row">
          <td class="mes">Total</td>
          <td id="DT_total_2025"></td><td id="SD_total_2025"></td><td id="DT_total_2026"></td><td id="SD_total_2026"></td><td id="DT_total_2027"></td><td id="SD_total_2027"></td><td id="DT_total_2028"></td><td id="SD_total_2028"></td><td id="DT_total_2029"></td><td id="SD_total_2029"></td><td id="DT_total_2030"></td><td id="SD_total_2030"></td>
        </tr>

        <tr class="firma">
          <td class="bold">Firma Trabajador</td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
        </tr>

        <tr class="firma">
          <td class="bold">Firma J´ Personal</td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
    <hr>
    <br>
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
