<?php
require_once __DIR__ . '/../model/dao/CalendarioDAO.php';

header('Content-Type: application/json');
$calendarioDAO = new CalendarioDAO();
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_evento = $_POST['id_evento'];

    try {
        if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
            $calendarioDAO->crearCalendario($id_evento, $_POST['fecha'], $_POST['hora'], $_POST['id_lugar']);
            $response = ['status' => 'success', 'message' => 'Función creada con éxito.'];
        } elseif (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
            $calendarioDAO->eliminarCalendario($_POST['id_calendario']);
            $response = ['status' => 'success', 'message' => 'Función eliminada con éxito.'];
        }

        // Para cualquier acción exitosa, devolvemos la lista actualizada de calendarios para este evento
        if ($response['status'] === 'success') {
            $response['calendarios'] = $calendarioDAO->getCalendariosPorEventoId($id_evento);
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>