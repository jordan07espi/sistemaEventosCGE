<?php
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    $usuarioDAO = new UsuarioDAO();
    $usuario = $usuarioDAO->getUsuarioPorCedula($cedula);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        // --- INICIO DE LA LÓGICA DE SESIÓN (ESTO ES LO QUE FALTABA) ---

        // 1. Guardamos los datos del usuario en la sesión para usarlos en el panel
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombres'];
        $_SESSION['user_role'] = $usuario['nombre_rol'];

        // 2. Redirigimos al usuario al dashboard del panel de administración
        header('Location: ../view/admin/index.php');
        exit(); // Es crucial detener el script después de una redirección

        // --- FIN DE LA LÓGICA DE SESIÓN ---

    } else {
        // Si los datos son incorrectos, lo devolvemos al login con un error
        $_SESSION['login_error'] = "Cédula o contraseña incorrectos.";
        header('Location: ../login.php');
        exit();
    }
}
?>