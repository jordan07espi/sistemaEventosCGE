<?php
// Paso 1: Activar el reporte de errores de PHP al máximo.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establecemos la cabecera para la respuesta JSON.
header('Content-Type: application/json');

// Paso 2: Intentar incluir el archivo DAO.
try {
    require_once __DIR__ . '/../model/dao/DashboardDAO.php';
} catch (Throwable $e) {
    // Si falla la inclusión (error de sintaxis en el DAO), morimos aquí.
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fatal al incluir DashboardDAO.php.',
        'error_detail' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

// Paso 3: Intentar instanciar el DAO y conectar a la BD.
try {
    $dashboardDAO = new DashboardDAO();
} catch (Throwable $e) {
    // Si falla el constructor (error de conexión en new Conexion()), morimos aquí.
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al instanciar DashboardDAO (probablemente en la conexión a la BD).',
        'error_detail' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

// Si llegamos hasta aquí, la conexión y la inclusión fueron exitosas.
// Ahora procesamos la acción solicitada.
$response = ['status' => 'error', 'message' => 'Acción no reconocida.'];
$accion = $_GET['accion'] ?? '';
$id_evento = !empty($_GET['id_evento']) ? (int)$_GET['id_evento'] : null;

try {
    switch ($accion) {
        case 'get_datos_dashboard':
            $response = [
                'status' => 'success',
                'datos_tarjetas' => $dashboardDAO->getDatosGenerales($id_evento),
                'datos_grafico' => $dashboardDAO->getDatosGraficoPrincipal($id_evento)
            ];
            break;

        case 'get_lista_eventos':
            $response = [
                'status' => 'success',
                'data' => $dashboardDAO->getListaEventos()
            ];
            break;
        
        default:
             $response['message'] = 'La acción especificada no existe.';
             break;
    }
} catch (Exception $e) {
    // Este catch ahora solo se activará para errores en las consultas, no en la conexión.
    $response['message'] = 'Ocurrió un error al ejecutar la acción del dashboard.';
    $response['error_detail'] = $e->getMessage();
}

echo json_encode($response);

?>