<?php
require_once __DIR__ . '/../model/dao/DashboardDAO.php';

header('Content-Type: application/json');

$dashboardDAO = new DashboardDAO();
$response = ['status' => 'error', 'message' => 'Acción no válida'];

// Determinar la acción a realizar
$accion = $_GET['accion'] ?? 'get_datos_generales';
$id_evento = isset($_GET['id_evento']) && !empty($_GET['id_evento']) ? (int)$_GET['id_evento'] : null;

try {
    switch ($accion) {
        case 'get_datos_generales':
            // Esta acción ahora devuelve el paquete completo de datos para la vista inicial
            $response = [
                'status' => 'success',
                'datos_tarjetas' => $dashboardDAO->getDatosGenerales(),
                'datos_grafico' => $dashboardDAO->getDatosGraficoPrincipal()
            ];
            break;

        case 'get_datos_evento':
            // Esta acción devuelve el paquete de datos para un evento específico
            if (!$id_evento) {
                // Si el ID del evento es nulo o vacío, devuelve los datos generales.
                 $response = [
                    'status' => 'success',
                    'datos_tarjetas' => $dashboardDAO->getDatosGenerales(),
                    'datos_grafico' => $dashboardDAO->getDatosGraficoPrincipal()
                ];
            } else {
                 $response = [
                    'status' => 'success',
                    'datos_tarjetas' => $dashboardDAO->getDatosGenerales($id_evento),
                    'datos_grafico' => $dashboardDAO->getDatosGraficoPrincipal($id_evento)
                ];
            }
            break;

        case 'get_lista_eventos':
            // Nueva acción para poblar el dropdown
            $response = [
                'status' => 'success',
                'data' => $dashboardDAO->getListaEventos()
            ];
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
?>