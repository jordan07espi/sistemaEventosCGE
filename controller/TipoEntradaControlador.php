<?php
// ¡CORREGIDO! Ahora usa el DAO correcto.
require_once __DIR__ . '/../model/dao/TipoEntradaDAO.php'; 

header('Content-Type: application/json');
$tipoEntradaDAO = new TipoEntradaDAO(); // ¡CORREGIDO!
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    try {
        $id_calendario = $_POST['id_calendario'];
        $nombre = $_POST['nombre_entrada'];
        $precio = $_POST['precio_entrada'];
        $cantidad = $_POST['cantidad_entrada'];
        $detalle = $_POST['detalle_entrada']; // <-- NUEVO CAMPO

        // ¡CORREGIDO! Llamamos a la función correcta
        $tipoEntradaDAO->crearTipoEntrada($id_calendario, $nombre, $precio, $cantidad, $detalle);

        $response = [
            'status' => 'success',
            'message' => 'Tipo de entrada añadido con éxito.',
            // Devolvemos la lista actualizada para que el AJAX la dibuje
            'tiposEntrada' => $tipoEntradaDAO->getTiposEntradaPorCalendarioId($id_calendario) 
        ];
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>