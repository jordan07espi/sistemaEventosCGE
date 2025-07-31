<?php
require_once __DIR__ . '/../model/dao/PonenteDAO.php';

header('Content-Type: application/json');
$ponenteDAO = new PonenteDAO();
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    try {
        $id_calendario = $_POST['id_calendario'];
        $ponenteDAO->crearPonente($id_calendario, $_POST['nombre_ponente'], $_POST['especialidad_ponente']);
        
        // Devolvemos la lista actualizada de ponentes para ese calendario
        $response = [
            'status' => 'success',
            'message' => 'Ponente añadido con éxito.',
            'ponentes' => $ponenteDAO->getPonentesPorCalendarioId($id_calendario)
        ];
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>