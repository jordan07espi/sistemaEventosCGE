<?php
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    $usuarioDAO = new UsuarioDAO();
    $usuario = $usuarioDAO->getUsuarioPorCedula($cedula);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        // Credenciales correctas. Guardamos los datos en la sesión.
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombres'];
        $_SESSION['user_role'] = $usuario['nombre_rol'];

        // --- ¡CAMBIO CLAVE AQUÍ! ---
        // Redirigimos al usuario a la página correcta según su rol.
        switch ($_SESSION['user_role']) {
            case 'Admin':
                header('Location: ../view/admin/index.php');
                break;
            case 'Secretaria':
                header('Location: ../view/admin/participantes.php');
                break;
            case 'Supervisor':
                header('Location: ../view/admin/escanear.php');
                break;
            default:
                // Si el rol no es reconocido, lo enviamos al login con un error.
                $_SESSION['login_error'] = "Rol de usuario no configurado para el acceso.";
                header('Location: ../login.php');
                break;
        }
        exit(); // Detenemos el script después de la redirección.

    } else {
        // Si los datos son incorrectos, lo devolvemos al login con un error.
        $_SESSION['login_error'] = "Cédula o contraseña incorrectos.";
        header('Location: ../login.php');
        exit();
    }
}
?>