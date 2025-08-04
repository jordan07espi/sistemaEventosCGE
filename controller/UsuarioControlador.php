<?php
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
header('Content-Type: application/json'); 
$usuarioDAO = new UsuarioDAO();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['status' => 'success', 'data' => $usuarioDAO->getUsuarios()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Acción no válida.'];
    $accion = $_POST['accion'] ?? null;
    try {
        switch ($accion) {
            case 'crear':
                if ($usuarioDAO->cedulaYaExiste($_POST['cedula'])) throw new Exception('La cédula ya está en uso.');
                $usuarioDAO->crearUsuario($_POST['nombres'], $_POST['apellidos'], $_POST['cedula'], $_POST['id_rol'], $_POST['contrasena']);
                $response = ['status' => 'success', 'message' => 'Usuario creado con éxito.'];
                break;
            case 'editar':
                if ($usuarioDAO->cedulaYaExiste($_POST['cedula'], $_POST['id_usuario'])) throw new Exception('La cédula ya está en uso por otro usuario.');
                $usuarioDAO->actualizarUsuario($_POST['id_usuario'], $_POST['nombres'], $_POST['apellidos'], $_POST['cedula'], $_POST['id_rol']);
                $response = ['status' => 'success', 'message' => 'Usuario actualizado con éxito.'];
                break;
            case 'reset_pass_manual':
                $usuarioDAO->actualizarContrasena($_POST['id_usuario'], $_POST['nueva_contrasena']);
                $response = ['status' => 'success', 'message' => 'Contraseña actualizada con éxito.'];
                break;
            case 'eliminar':
                $usuarioDAO->eliminarUsuario($_POST['id_usuario']);
                $response = ['status' => 'success', 'message' => 'Usuario eliminado con éxito.'];
                break;
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE); 
    exit();
}
?>