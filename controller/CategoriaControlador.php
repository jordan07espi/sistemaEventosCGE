<?php
require_once __DIR__ . '/../model/dao/CategoriaDAO.php';

header('Content-Type: application/json');
$categoriaDAO = new CategoriaDAO();

// Si es una petición GET, solo devolvemos la lista de categorías.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $categoriaDAO->getCategorias();
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit();
}

// Si es una petición POST, manejamos las acciones.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Acción no válida.'];
    $accion = $_POST['accion'] ?? null;

    try {
        switch ($accion) {
            case 'crear':
                $categoriaDAO->crearCategoria($_POST['nombre_categoria']);
                $response = ['status' => 'success', 'message' => 'Categoría creada con éxito.'];
                break;
            case 'editar':
                $categoriaDAO->actualizarCategoria($_POST['id_categoria'], $_POST['nombre_categoria']);
                $response = ['status' => 'success', 'message' => 'Categoría actualizada con éxito.'];
                break;
            case 'estado':
                $nuevo_estado = $_POST['estado_actual'] == 1 ? 0 : 1;
                $categoriaDAO->cambiarEstadoCategoria($_POST['id_categoria'], $nuevo_estado);
                $response = ['status' => 'success', 'message' => 'Estado cambiado con éxito.'];
                break;
        }
        
        // Después de una acción POST exitosa, siempre devolvemos la lista actualizada.
        if ($response['status'] === 'success') {
            $response['data'] = $categoriaDAO->getCategorias();
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}
?>