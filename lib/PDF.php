<?php
require_once __DIR__ . '/fpdf.php';

class PDF extends FPDF {
    
    // --- Cabecera de página ---
    function Header() {
        // Fondo de la cabecera
        $this->SetFillColor(11, 65, 109);
        $this->Rect(0, 0, 210, 25, 'F');
        
        // Título principal
        $this->SetY(8); // Posicionamos el texto verticalmente
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'BOLETO DE EVENTO CGE', 0, 1, 'C');
        
        // Salto de línea para empezar el contenido
        $this->Ln(20);
    }

    // --- Pie de página (sin cambios) ---
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Celestium Soft - CGE | Boleto generado el ' . date('d/m/Y \a \l\a\s H:i:s'), 0, 0, 'C');
    }
    
    // --- Función para generar el código QR (sin cambios) ---
    public function generarQR($url) {
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($url);
        return $qrImageUrl;
    }
}
?>