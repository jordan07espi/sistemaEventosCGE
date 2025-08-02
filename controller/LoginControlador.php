<?php
require_once __DIR__ . '/../model/dao/UsuarioDAO.php'; // Necesitaremos un UsuarioDAO

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    $usuarioDAO = new UsuarioDAO();
    $usuario = $usuarioDAO->getUsuarioPorEmail($email);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        // Login exitoso
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
        $_SESSION['user_role'] = $usuario['nombre_rol'];
        header('Location: ../view/admin/index.php'); // Redirigir al dashboard
        exit();
    } else {
        // Login fallido
        $_SESSION['login_error'] = "Correo o contraseña incorrectos.";
        header('Location: ../login.php');
        exit();
    }
}
?>