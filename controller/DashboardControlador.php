<?php
require_once __DIR__ . '/../model/dao/DashboardDAO.php';

header('Content-Type: application/json');

$dashboardDAO = new DashboardDAO();

// Preparamos los datos para el gráfico
$datosGrafico = $dashboardDAO->getParticipantesPorEvento();

$labels = [];
$data = [];

foreach ($datosGrafico as $fila) {
    $labels[] = $fila['nombre_evento'];
    $data[] = $fila['total_participantes'];
}

// Devolvemos los datos en un formato que Chart.js entiende
echo json_encode([
    'labels' => $labels,
    'data' => $data
]);

exit();
?>