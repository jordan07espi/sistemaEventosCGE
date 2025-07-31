<?php
require_once __DIR__ . '/../model/dao/LugarDAO.php';

header('Content-Type: application/json');
$lugarDAO = new LugarDAO();

// Si la petición es GET, simplemente listamos los lugares.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['status' => 'success', 'data' => $lugarDAO->getLugares()]);
    exit();
}

// Si la petición es POST, manejamos las acciones.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Acción no válida.'];
    $accion = $_POST['accion'] ?? '';
    
    try {
        if ($accion === 'crear' || $accion === 'editar') {
            $nombre = $_POST['nombre_establecimiento'];
            $direccion = $_POST['direccion'];
            $ciudad = $_POST['ciudad'];
            $capacidad = $_POST['capacidad'] ?: null;

            if ($accion === 'crear') {
                $lugarDAO->crearLugar($nombre, $direccion, $ciudad, $capacidad);
                $response = ['status' => 'success', 'message' => 'Lugar creado con éxito.'];
            } else {
                $id = $_POST['id_lugar'];
                $lugarDAO->actualizarLugar($id, $nombre, $direccion, $ciudad, $capacidad);
                $response = ['status' => 'success', 'message' => 'Lugar actualizado con éxito.'];
            }
        } elseif ($accion === 'eliminar') {
            $id = $_POST['id_lugar'];
            $lugarDAO->eliminarLugar($id);
            $response = ['status' => 'success', 'message' => 'Lugar eliminado con éxito.'];
        }

        if ($response['status'] === 'success') {
            $response['data'] = $lugarDAO->getLugares();
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}
?>