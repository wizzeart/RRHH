<?php
/**
 * Exporta el fichero DBF de cuentas bancarias para el banco.
 *
 * IMPORTANTE: este script NO usa la extensión `dbase` de PHP. Esa extensión salió del core
 * en PHP 5.3 y hoy es un PECL que rara vez está instalado; al no existir, `dbase_create()`
 * lanzaba un Error de "función no definida" que el `catch (Exception)` no capturaba (en PHP 7+
 * los Error no son Exception), y la petición moría con un 500 sin mensaje. El fichero DBF se
 * genera ahora byte a byte en PHP puro, sin depender de nada externo.
 */

require_once '../includes/config.php';
require_once '../classes/Sql.class.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escribe un fichero DBF en la variante Visual FoxPro (byte de versión 0x30), que es la que
 * el banco acepta. NO es dBASE III+: el banco lee por posición fija y un fichero dBASE III+
 * con las mismas longitudes "de más" corre todos los campos desde el segundo en adelante.
 *
 * Diferencias con dBASE III+ replicadas aquí a propósito (cotejadas byte a byte contra un
 * fichero aceptado real):
 *   - Byte de versión 0x30 y cabecera de 520 bytes = 32 fijos + 32*campos + terminador 0x0D
 *     + 263 bytes de "bloque de enlace a contenedor" (relleno a cero en una tabla libre).
 *   - Byte 29 de la cabecera declara la página de códigos (0x03 = Windows ANSI 1252).
 *   - Cada descriptor de campo guarda en los bytes 12-15 el desplazamiento del campo dentro
 *     del registro (uint32 LE), en vez de dejarlos reservados en cero.
 *   - El año de la cabecera va módulo 100 (26 para 2026), no year-1900 (daría 126).
 *
 * @param string $filepath destino
 * @param array  $fields   lista de [nombre, tipo, ancho, decimales]
 * @param array  $rows     lista de registros, cada uno array posicional según $fields
 * @return int             número de registros escritos
 */
function dbf_write($filepath, array $fields, array $rows)
{
    $BACKLINK_LEN = 263; // bloque de enlace a contenedor propio de Visual FoxPro
    $headerLen = 32 + (32 * count($fields)) + 1 + $BACKLINK_LEN;

    // Desplazamiento de cada campo dentro del registro (el byte 0 es la marca de borrado,
    // así que el primer campo arranca en el desplazamiento 1, igual que en el fichero aceptado).
    $desplazamientos = [];
    $recordLen = 1; // byte de marca de borrado
    foreach ($fields as $f) {
        $desplazamientos[] = $recordLen;
        $recordLen += (int) $f[2];
    }

    $fp = fopen($filepath, 'wb');
    if (!$fp) {
        throw new Exception('No se pudo crear el fichero DBF temporal.');
    }

    // --- Cabecera (32 bytes) ---
    fwrite($fp, chr(0x30));                                  // versión: Visual FoxPro, sin memo
    fwrite($fp, chr((int) date('y')));                        // año módulo 100 (26, no 126)
    fwrite($fp, chr((int) date('n')));                       // mes
    fwrite($fp, chr((int) date('j')));                       // día
    fwrite($fp, pack('V', count($rows)));                    // nº de registros (uint32 LE)
    fwrite($fp, pack('v', $headerLen));                      // longitud de cabecera (uint16 LE)
    fwrite($fp, pack('v', $recordLen));                      // longitud de registro (uint16 LE)
    fwrite($fp, str_repeat("\0", 17));                       // reservado (bytes 12-28)
    fwrite($fp, chr(0x03));                                  // byte 29: página de códigos (ANSI 1252)
    fwrite($fp, str_repeat("\0", 2));                        // reservado (bytes 30-31)

    // --- Descriptores de campo (32 bytes cada uno) ---
    foreach ($fields as $i => $f) {
        $nombre = strtoupper(substr($f[0], 0, 10));
        fwrite($fp, str_pad($nombre, 11, "\0", STR_PAD_RIGHT)); // nombre (11 bytes, con NUL)
        fwrite($fp, substr($f[1], 0, 1));                       // tipo (C, N, D, L)
        fwrite($fp, pack('V', $desplazamientos[$i]));           // desplazamiento del campo en el registro
        fwrite($fp, chr((int) $f[2]));                          // ancho
        fwrite($fp, chr(isset($f[3]) ? (int) $f[3] : 0));       // decimales
        fwrite($fp, str_repeat("\0", 14));                      // reservado
    }
    fwrite($fp, chr(0x0D)); // terminador de descriptores
    fwrite($fp, str_repeat("\0", $BACKLINK_LEN)); // bloque de enlace a contenedor (tabla libre: vacío)

    // --- Registros ---
    foreach ($rows as $row) {
        fwrite($fp, ' '); // marca de borrado: espacio = registro activo
        foreach ($fields as $i => $f) {
            $ancho = (int) $f[2];
            $valor = isset($row[$i]) ? $row[$i] : '';

            if (strtoupper($f[1]) === 'N') {
                if ($valor === '' || $valor === null) {
                    // Sin valor: campo numérico en blanco (espacios), no "0.00". Es el estado
                    // que trae el fichero aceptado en la inmensa mayoría de sus registros
                    // (por ejemplo IMPORTE_D cuando no hay cuenta en divisa).
                    $txt = str_repeat(' ', $ancho);
                } else {
                    // Numérico: alineado a la derecha, con los decimales declarados
                    $dec = isset($f[3]) ? (int) $f[3] : 0;
                    $txt = number_format((float) $valor, $dec, '.', '');
                    $txt = str_pad($txt, $ancho, ' ', STR_PAD_LEFT);
                    // Si desborda el ancho declarado se recorta por la izquierda
                    $txt = substr($txt, -$ancho);
                }
            } else {
                // Carácter: alineado a la izquierda, relleno con espacios
                $txt = str_pad((string) $valor, $ancho, ' ', STR_PAD_RIGHT);
                $txt = substr($txt, 0, $ancho);
            }
            fwrite($fp, $txt);
        }
    }

    // Sin marca 0x1A de fin de fichero: es opcional en el formato y el fichero que el banco
    // acepta tampoco la lleva.
    fclose($fp);

    return count($rows);
}

$filepath = null;

try {
    $db = new Sql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, '3306');

    $empresaId = isset($_GET['empresa_id'])
        ? intval($_GET['empresa_id'])
        : (isset($_SESSION['empresa_id']) ? intval($_SESSION['empresa_id']) : 1);

    // Periodo exportado: el que llegue por parámetro (el calendario de prenómina-2 pide meses
    // pasados) y, si no viene ninguno, el mes en curso. Se acepta `mes=YYYY-MM` o `year`+`month`.
    $year  = (int) date('Y');
    $month = (int) date('n');

    if (!empty($_GET['mes']) && preg_match('/^(\d{4})-(\d{1,2})$/', $_GET['mes'], $m)) {
        $year  = (int) $m[1];
        $month = (int) $m[2];
    } else {
        if (isset($_GET['year']))  { $year  = (int) $_GET['year']; }
        if (isset($_GET['month'])) { $month = (int) $_GET['month']; }
    }

    if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
        throw new Exception('Periodo inválido: ' . $year . '-' . $month);
    }

    // INNER JOIN a prenomina acotado al periodo: sin ese filtro la consulta devolvía una fila
    // por CADA prenómina histórica del trabajador, y el fichero salía con el pago duplicado
    // tantas veces como meses tuviera registrados.
    // Se excluyen los trabajadores dados de baja y los que no tienen cuenta bancaria: no deben
    // aparecer en un fichero de transferencias.
    $query = "
        SELECT
            t.carnet_identidad,
            b.numero_cuenta_estandar,
            p.salario_pagar
        FROM trabajadores t
        INNER JOIN bancos b ON b.trabajador_id = t.id
        INNER JOIN prenomina p ON p.trabajador_id = t.id
                              AND p.year = :year
                              AND p.month = :month
        WHERE t.empresa_id = :empresa_id
          AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
          AND b.numero_cuenta_estandar IS NOT NULL
          AND TRIM(b.numero_cuenta_estandar) <> ''
        ORDER BY t.id ASC
    ";
    $data = $db->fetchAll($query, [
        'year'       => $year,
        'month'      => $month,
        'empresa_id' => $empresaId,
    ]);

    // Trabajadores que SÍ tienen prenómina de este mes pero quedan FUERA del fichero por no
    // tener cuenta estándar registrada. No se pueden incluir en la transferencia, pero tampoco
    // deben desaparecer en silencio: quien exporta tiene que saber a quién no se le va a pagar.
    $sinCuenta = $db->fetchAll("
        SELECT
            t.carnet_identidad,
            CONCAT(t.nombre, ' ', t.apellidos) AS nombre
        FROM trabajadores t
        INNER JOIN prenomina p ON p.trabajador_id = t.id
                              AND p.year = :year
                              AND p.month = :month
        LEFT JOIN bancos b ON b.trabajador_id = t.id
        WHERE t.empresa_id = :empresa_id
          AND (t.trabajador_eliminado = 0 OR t.trabajador_eliminado = '0' OR t.trabajador_eliminado IS NULL)
          AND (b.numero_cuenta_estandar IS NULL OR TRIM(b.numero_cuenta_estandar) = '')
        ORDER BY t.nombre ASC, t.apellidos ASC
    ", [
        'year'       => $year,
        'month'      => $month,
        'empresa_id' => $empresaId,
    ]);

    // Modo comprobación: la vista lo llama ANTES de descargar para poder avisar.
    if (!empty($_GET['check'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'     => 1,
            'periodo'    => sprintf('%04d-%02d', $year, $month),
            'a_exportar' => count($data),
            'sin_cuenta' => $sinCuenta,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!empty($sinCuenta)) {
        @error_log('export-bancos-dbf: ' . count($sinCuenta) . ' trabajador(es) sin cuenta estándar '
            . 'quedaron fuera del fichero de ' . sprintf('%04d-%02d', $year, $month));
    }

    // Estructura del fichero que espera el banco (longitudes cotejadas contra un fichero
    // aceptado real: el registro completo debe medir 85 bytes, no 93).
    $fields = [
        ['COD_PAEXID', 'C', 3],
        ['COD_TIPID',  'C', 2],
        ['NUM_IDEPER', 'C', 15],
        ['CTA_MNAC',   'C', 16],
        ['IMPORTE_N',  'N', 16, 2],
        ['CTA_MLC',    'C', 16],
        ['IMPORTE_D',  'N', 16, 2],
    ];

    $rows = [];
    foreach ($data as $row) {
        $carnet  = isset($row['carnet_identidad']) ? trim($row['carnet_identidad']) : '';
        $cuenta  = isset($row['numero_cuenta_estandar']) ? trim($row['numero_cuenta_estandar']) : '';
        $salario = isset($row['salario_pagar']) ? floatval($row['salario_pagar']) : 0;

        // CTA_MLC (cuenta en divisa) no se maneja hoy, siempre va vacía. IMPORTE_D debe ir
        // en blanco en ese caso (como en la mayoría de registros del fichero aceptado): repetir
        // aquí el salario (como hacía antes) pide un segundo pago en divisa sin decir a qué
        // cuenta, duplicando la nómina completa.
        $ctaDivisa = '';
        $importeDivisa = ($ctaDivisa === '') ? '' : $salario;

        $rows[] = ['247', 'CI', $carnet, $cuenta, $salario, $ctaDivisa, $importeDivisa];
    }

    $filename = 'bancos_export_' . date('YmdHis') . '.dbf';
    $filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

    dbf_write($filepath, $fields, $rows);

    if (!file_exists($filepath) || filesize($filepath) == 0) {
        throw new Exception('El fichero DBF no se generó correctamente.');
    }

    // El nombre lleva el periodo: desde el calendario se pueden bajar varios meses seguidos y
    // si todos se llamaran igual el navegador los apila como "(1)", "(2)"... sin decir cuál es cuál.
    header('Content-Type: application/x-dbf');
    header('Content-Disposition: attachment; filename="bancos_export_'
        . sprintf('%04d-%02d', $year, $month) . '.dbf"');
    header('Content-Length: ' . filesize($filepath));

    readfile($filepath);
    unlink($filepath);

// Throwable, no Exception: en PHP 7+ los errores fatales (función no definida, argumentos
// inválidos...) son Error, y con `catch (Exception)` se escapaban como un 500 sin mensaje.
} catch (Throwable $e) {
    if ($filepath !== null && file_exists($filepath)) {
        @unlink($filepath);
    }

    @error_log('export-bancos-dbf: ' . $e->getMessage());

    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
    }
    echo 'Error al generar el DBF: ' . $e->getMessage();
}
