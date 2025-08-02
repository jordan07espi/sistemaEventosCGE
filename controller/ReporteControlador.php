<?php
// Incluimos los DAOs necesarios
require_once __DIR__ . '/../model/dao/ParticipanteDAO.php';
require_once __DIR__ . '/../model/dao/EventoDAO.php';

// Verificamos que se haya proporcionado un ID de evento
if (!isset($_GET['id_evento'])) {
    die("Error: No se ha especificado un evento para el reporte.");
}
$id_evento = $_GET['id_evento'];

$participanteDAO = new ParticipanteDAO();
$eventoDAO = new EventoDAO();

// Obtenemos los datos del evento y de los participantes
$evento = $eventoDAO->getEventoPorId($id_evento);
$participantes = $participanteDAO->getParticipantesParaReporte($id_evento); // Necesitaremos crear esta nueva función

// --- INICIO DE LA GENERACIÓN DEL CSV ---

// 1. Definimos el nombre del archivo
$nombre_archivo = "Reporte_" . preg_replace('/[^a-zA-Z0-9_ -]/s', '', $evento['nombre']) . ".csv";

// 2. Establecemos las cabeceras para forzar la descarga del archivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $nombre_archivo);

// 3. Abrimos el flujo de salida de PHP
$output = fopen('php://output', 'w');

// 4. Escribimos la fila de encabezados del CSV
fputcsv($output, [
    'Nombres', 
    'Apellidos', 
    'Cedula', 
    'Email', 
    'Telefono', 
    'Tipo de Entrada', 
    'Banco', 
    'Numero de Transaccion', 
    'Asistencia',
    'Fecha de Registro'
]);

// 5. Escribimos los datos de cada participante en una nueva fila
if (!empty($participantes)) {
    foreach ($participantes as $p) {
        fputcsv($output, [
            $p['nombres'],
            $p['apellidos'],
            $p['cedula'],
            $p['email'],
            $p['telefono'],
            $p['nombre_entrada'],
            $p['banco'],
            $p['numero_transaccion'],
            $p['asistencia'],
            $p['fecha_registro']
        ]);
    }
}

// Cerramos el flujo
fclose($output);
exit();
?>