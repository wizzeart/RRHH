<?php
require_once(__DIR__ . '/plugins/tcpdf/tcpdf.php'); // Ajusta la ruta según tu estructura


// Función para obtener datos POST de forma segura
function post($k) {
    return isset($_POST[$k]) ? $_POST[$k] : '';
}

// Recoger datos del formulario
$nombre = trim(post('nombre'));
$apellidos = trim(post('apellidos'));
$carnet_identidad = trim(post('carnet_identidad'));
$cargo_nombre = trim(post('cargo_nombre'));
$cargos_id = trim(post('cargos_id'));
$areas_acceso = trim(post('areas_acceso'));
$fecha_generacion = trim(post('fecha_generacion'));
$vigente = trim(post('vigente'));
$foto = trim(post('foto'));

// Crear PDF con orientación horizontal y tamaño A5
$pdf = new TCPDF('L', 'mm',array(200, 100), true, 'UTF-8', false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Fondo blanco
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');




// Foto a la izquierda
$photoX = 15; $photoY = 15; $photoW = 60; $photoH = 70;
if ($foto) {
    $imgSrc = $foto;
    if (!preg_match('#^https?://#i', $foto) && file_exists(__DIR__ . '/' . ltrim($foto, '/\\'))) {
        $imgSrc = __DIR__ . '/' . ltrim($foto, '/\\');
    }
    $pdf->Image($imgSrc, $photoX, $photoY, $photoW, $photoH, '', '', '', false, 300);
} else {
    $pdf->SetXY($photoX, $photoY + 30);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($photoW, 10, 'No hay foto', 0, 0, 'C');
}

// Datos a la derecha

$rightX = $photoX + $photoW + 20;
$pdf->SetXY($rightX, 20);

$pdf->SetFont('helvetica', 'B', 18);

$pdf->Cell(0, 0, 'Pase de Acceso', 1, 0, 'C', 0, '', 0, false, 'T', 'M');
$pdf->SetXY($rightX, 28);
$pdf->Cell(0, 10, $nombre . ' ' . $apellidos, 0, 1);

$pdf->SetFont('helvetica', '', 14);
$pdf->SetX($rightX);
$pdf->Cell(0, 8, 'Cargo: ' . $cargo_nombre, 0, 1);
$pdf->SetX($rightX);
$pdf->Cell(0, 8, 'CI: ' . $carnet_identidad, 0, 1);
$pdf->SetX($rightX);
$pdf->Cell(0, 8, 'Áreas de acceso: ' . $areas_acceso, 0, 1);
$pdf->SetX($rightX);
$pdf->Cell(0, 8, 'Fecha de generación: ' . $fecha_generacion, 0, 1);
$pdf->SetX($rightX);
$pdf->Cell(0, 8, 'Estado: ' . ($vigente === '1' ? 'Vigente' : 'No Vigente'), 0, 1);
$pdf->SetX($rightX+60);
$pdf->Image(__DIR__ . '/img/logo-iml-servicios.jpg', 30, 12, 30); 


// Salida del PDF
$filename = 'ID_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre . '_' . $apellidos) . '.pdf';
$pdf->Output($filename, 'I');
exit;