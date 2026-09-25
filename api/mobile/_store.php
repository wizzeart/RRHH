<?php
/**
 * Almacén JSON en disco para la API móvil (avisos, leídos, tokens push).
 *
 * NO usa la base de datos: todo vive en `api/mobile/data/*.json`. Lecturas y
 * escrituras usan flock para evitar carreras entre peticiones concurrentes.
 */

function store_dir()
{
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

function store_path($name)
{
    // Solo permitimos nombres simples (sin separadores de ruta).
    $name = preg_replace('/[^a-z0-9_\-]/i', '', $name);
    return store_dir() . '/' . $name . '.json';
}

/** Lee un archivo JSON y devuelve un array (vacío si no existe / corrupto). */
function store_read($name)
{
    $path = store_path($name);
    if (!is_file($path)) return [];
    $fp = @fopen($path, 'r');
    if (!$fp) return [];
    $data = [];
    if (flock($fp, LOCK_SH)) {
        $raw = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        if ($raw !== false && $raw !== '') {
            $j = json_decode($raw, true);
            if (is_array($j)) $data = $j;
        }
    }
    fclose($fp);
    return $data;
}

/** Escribe (sobrescribe) un archivo JSON de forma atómica con bloqueo exclusivo. */
function store_write($name, array $data)
{
    $path = store_path($name);
    $fp = @fopen($path, 'c+');
    if (!$fp) return false;
    $ok = false;
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        $ok = fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $ok;
}

/** Siguiente id autoincremental para una colección (array de filas con 'id'). */
function store_next_id(array $rows)
{
    $max = 0;
    foreach ($rows as $r) {
        if (isset($r['id']) && (int)$r['id'] > $max) $max = (int)$r['id'];
    }
    return $max + 1;
}
