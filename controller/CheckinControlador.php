<?php
require_once __DIR__ . '/../model/dao/ParticipanteDAO.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Petición no válida.'];
$participanteDAO = new ParticipanteDAO();

// --- LÓGICA PARA PETICIONES GET (LISTAR ASISTENCIA) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id_evento'])) {
        $participantes = $participanteDAO->getParticipantesParaCheckin($_GET['id_evento'], $_GET['busqueda'] ?? '');
        $response = ['status' => 'success', 'data' => $participantes];
    }
    echo json_encode($response);
    exit();
}

// --- LÓGICA PARA PETICIONES POST (REGISTRAR ASISTENCIA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_evento = $_POST['id_evento'] ?? null;
    $cedula = $_POST['cedula'] ?? null;
    $id_participante = $_POST['id_participante'] ?? null;

    try {
        $participante = null;
        if ($cedula && $id_evento) { // Búsqueda por Cédula (QR)
            $participante = $participanteDAO->getParticipantePorCedulaYEvento($cedula, $id_evento);
            if (!$participante) throw new Exception("Participante con cédula $cedula no encontrado en este evento.");
        } elseif ($id_participante) { // Búsqueda por ID (Manual)
            $participante = $participanteDAO->getParticipantePorId($id_participante);
             if (!$participante) throw new Exception("Participante no encontrado.");
        } else {
            throw new Exception("Datos insuficientes para verificar la asistencia.");
        }
        
        if ($participante['asistencia'] === 'Registrado') {
            throw new Exception("ACCESO DENEGADO: Este boleto ya fue utilizado por " . $participante['nombres'] . " " . $participante['apellidos'] . ".");
        }

        $participanteDAO->registrarAsistencia($participante['id']);
        
        $response = [
            'status' => 'success', 
            'message' => 'ACCESO CONCEDIDO',
            'participante' => $participante['nombres'] . ' ' . $participante['apellidos']
        ];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}
?>