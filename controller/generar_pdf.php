<?php
// Incluimos todos los DAOs necesarios y la clase PDF
require_once __DIR__ . '/../model/dao/ParticipanteDAO.php';
require_once __DIR__ . '/../model/dao/TipoEntradaDAO.php';
require_once __DIR__ . '/../lib/PDF.php';


date_default_timezone_set('America/Guayaquil');
setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');

// 1. Verificamos que se haya proporcionado un ID de participante
if (!isset($_GET['id_participante'])) {
    die("Error: No se proporcionó un ID de participante.");
}
$id_participante = $_GET['id_participante'];

$participanteDAO = new ParticipanteDAO();
$tipoEntradaDAO = new TipoEntradaDAO();

// 2. Obtenemos los datos del participante y del evento
$datosParticipante = $participanteDAO->getParticipantePorId($id_participante);
if (!$datosParticipante) {
    die("Error: Participante no encontrado.");
}

$datosEntrada = $tipoEntradaDAO->getDetallesEntradaParaPDF($datosParticipante['id_tipo_entrada']);
if (!$datosEntrada) {
    die("Error: No se pudieron obtener los detalles de la entrada.");
}


// --- 3. INICIO DE LA GENERACIÓN DEL PDF ---
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

// --- CÓDIGO ÚNICO DEL BOLETO ---
$codigoUnico = 'EV' . str_pad($datosEntrada['id_evento'], 3, '0', STR_PAD_LEFT) . '-P' . str_pad($datosParticipante['id'], 4, '0', STR_PAD_LEFT);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100);
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', 'Código de Boleto: ' . $codigoUnico), 0, 1, 'C');
$pdf->Ln(5);

// --- SECCIÓN: DETALLES DEL EVENTO ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0); // Restaurar color de texto a negro
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', 'DETALLES DEL EVENTO'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
// Formateamos la fecha para que se lea mejor (ej. "jueves, 10 de julio de 2025")
setlocale(LC_TIME, 'es_ES.UTF-8');
$fechaFormateada = strftime("%A, %d de %B de %Y", strtotime($datosEntrada['fecha']));
$pdf->MultiCell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE',
    "Evento: " . $datosEntrada['nombre_evento'] . "\n" .
    "Fecha: " . $fechaFormateada . "\n" .
    "Hora: " . date("h:i A", strtotime($datosEntrada['hora'])) . "\n" .
    "Lugar: " . $datosEntrada['nombre_lugar']
), 0, 'L');
$pdf->Ln(5);

// --- SECCIÓN: PARTICIPANTE Y QR ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'PARTICIPANTE', 0, 1, 'L');

// ---> ¡CAMBIO AQUÍ! Posición y tamaño del QR ajustados
$qr_x = 120; // Movemos el QR un poco a la izquierda para centrarlo mejor
$qr_y = $pdf->GetY();
// Aumentamos el tamaño del QR a 65x65
$pdf->Image($pdf->generarQR($datosParticipante['cedula']), $qr_x, $qr_y, 65, 65, 'PNG');

$pdf->SetFont('Arial', '', 12);
$textoParticipante = "Nombre: " . $datosParticipante['nombres'] . " " . $datosParticipante['apellidos'] . "\n" .
                     "Cedula: " . $datosParticipante['cedula'] . "\n" .
                     "Email: " . $datosParticipante['email'] . "\n" .
                     "Telefono: " . $datosParticipante['telefono'];

// ---> ¡CAMBIO AQUÍ! Reducimos el ancho del texto para que no se solape con el QR
$pdf->MultiCell(100, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $textoParticipante), 0, 'L');

// ---> ¡CAMBIO AQUÍ! Ajustamos el salto de línea para que quede debajo del QR (que ahora es más grande)
$pdf->Ln(30);


// --- SECCIÓN: DETALLES DEL BOLETO (CORREGIDA) ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'DETALLES DEL BOLETO', 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);

// ---> ¡CAMBIO CLAVE AQUÍ! Se imprime cada detalle en una línea separada.
// 1. Categoría
$pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', "Categoría: " . $datosEntrada['nombre_entrada']), 0, 1, 'L');

// 2. Beneficios
$pdf->MultiCell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', "Beneficios incluidos:\n" . $datosEntrada['detalle_entrada']), 0, 'L');

// 3. Precio
if ((float)$datosEntrada['precio'] === 0.00) {
    $textoPrecio = 'GRATUITO';
} else {
    $textoPrecio = '$' . number_format($datosEntrada['precio'], 2);
}
$pdf->Cell(0, 8, "Precio: " . $textoPrecio, 0, 1, 'L');
$pdf->Ln(10);


// --- SECCIÓN: INSTRUCCIONES ---
$pdf->SetFillColor(220, 220, 220);
$pdf->Rect(10, $pdf->GetY(), 190, 25, 'F');
$pdf->SetY($pdf->GetY() + 2);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'INSTRUCCIONES IMPORTANTES', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$instrucciones = "- Presente este boleto y su documento de identidad en la entrada.\n" .
                 "- Llegue con 30 minutos de anticipacion.\n" .
                 "- El codigo QR sera escaneado para verificar su identidad. Este boleto es intransferible.";
$pdf->MultiCell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $instrucciones), 0, 'C');

// --- 4. Salida del PDF ---
$nombre_archivo_boleto = 'Boleto_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $datosEntrada['nombre_evento']) . '.pdf';
$pdf->Output('D', $nombre_archivo_boleto);
exit();
?>