<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

// Accept JSON POST
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;
$signer = isset($input['signer']) ? $input['signer'] : '';
$reason = isset($input['reason']) ? $input['reason'] : '';

if ($id <= 0) {
    echo json_encode(['status' => 0, 'error' => 'Invalid id']);
    exit;
}

$mysqli = new mysqli(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
if ($mysqli->connect_errno) {
    echo json_encode(['status' => 0, 'error' => $mysqli->connect_error]);
    exit;
}

// Get document
$stmt = $mysqli->prepare("SELECT id, nombre, path FROM documentos WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['status' => 0, 'error' => 'Documento no encontrado']);
    exit;
}
$doc = $res->fetch_assoc();
$stmt->close();

$inputPath = $doc['path'];
// Ensure path is absolute or relative to project
$python = escapeshellcmd('python');
$wrapper = __DIR__ . '/../DIGITAL-DESIGN/sign_wrapper.py';
$cmd = escapeshellcmd($python) . ' ' . escapeshellarg($wrapper) . ' --input ' . escapeshellarg($inputPath) . ' --signer ' . escapeshellarg($signer) . ' --reason ' . escapeshellarg($reason);

// Execute python wrapper
exec($cmd . ' 2>&1', $output, $exitCode);
$outputText = implode("\n", $output);
if ($exitCode !== 0) {
    echo json_encode(['status' => 0, 'error' => 'Signing failed', 'output' => $outputText]);
    exit;
}

// Expect JSON output from python wrapper with {status:1, signed_path: '...'}
$json = json_decode($outputText, true);
if (!$json || !isset($json['status']) || $json['status'] != 1) {
    echo json_encode(['status' => 0, 'error' => 'Invalid signer response', 'output' => $outputText]);
    exit;
}

$signedPath = $json['signed_path'];

// Update DB: path, firmado, fecha_modificado
$now = date('Y-m-d H:i:s');
$upd = $mysqli->prepare("UPDATE documentos SET path = ?, firmado = 1, fecha_modificado = ? WHERE id = ?");
$upd->bind_param('ssi', $signedPath, $now, $id);
$ok = $upd->execute();
$upd->close();

if ($ok) {
    echo json_encode(['status' => 1, 'signed_path' => $signedPath]);
} else {
    echo json_encode(['status' => 0, 'error' => $mysqli->error]);
}
