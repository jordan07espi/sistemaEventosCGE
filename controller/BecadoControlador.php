<?php
require_once __DIR__ . '/../model/dao/BecadoDAO.php';

header('Content-Type: application/json');
$becadoDAO = new BecadoDAO();
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

$accion = $_REQUEST['accion'] ?? 'listar';

try {
    switch ($accion) {
        case 'listar':
            $busqueda = $_GET['search'] ?? '';
            $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            $becados = $becadoDAO->getBecados($busqueda, $pagina);
            $total_records = $becadoDAO->contarBecados($busqueda);
            $limit = $becadoDAO->getRegistrosPorPagina();

            $response = [
                'status' => 'success',
                'data' => $becados,
                'pagination' => [
                    'total_records' => (int)$total_records,
                    'current_page' => $pagina,
                    'total_pages' => ceil($total_records / $limit),
                    'limit' => $limit
                ]
            ];
            break;

        case 'cambiar_estado':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'];
                $estado_actual = $_POST['estado'];
                $becadoDAO->cambiarEstado($id, $estado_actual);
                $response = [
                    'status' => 'success',
                    'message' => 'Estado cambiado con éxito.'
                ];
            }
            break;

        case 'importar_excel':
            if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] === UPLOAD_ERR_OK) {
                $tmpFilePath = $_FILES['archivo_excel']['tmp_name'];
                $resultados = $becadoDAO->importarBecados($tmpFilePath);

                // ¡Mejora! Obtenemos los datos actualizados para refrescar la tabla
                $becados_actualizados = $becadoDAO->getBecados('', 1);
                $total_records = $becadoDAO->contarBecados('');
                $limit = $becadoDAO->getRegistrosPorPagina();

                $response = [
                    'status' => 'success',
                    'message' => "Importación completada.\nNuevos: {$resultados['insertados']}.\nOmitidos (duplicados): {$resultados['omitidos']}.",
                    'data' => $becados_actualizados,
                    'pagination' => [
                        'total_records' => (int)$total_records,
                        'current_page' => 1,
                        'total_pages' => ceil($total_records / $limit),
                        'limit' => $limit
                    ]
                ];
            } else {
                throw new Exception('No se recibió el archivo o hubo un error en la subida.');
            }
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
?>