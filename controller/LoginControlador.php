<?php
// Se requiere el UsuarioDAO para interactuar con la base de datos.
// La ruta se ajusta para que coincida con la estructura de tu proyecto.
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
session_start();

// --- CONFIGURACIÓN DE SEGURIDAD ---
const MAX_INTENTOS_LOGIN = 5; // Límite de intentos fallidos antes de bloquear.
const TIEMPO_BLOQUEO_MINUTOS = 15; // Tiempo que el usuario/IP estará bloqueado.

// Verificamos que la solicitud sea por el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR']; // Obtenemos la IP del usuario.

    $usuarioDAO = new UsuarioDAO();

    // --- PASO 1: VERIFICAR SI EL USUARIO ESTÁ BLOQUEADO ---
    $intentos_fallidos = $usuarioDAO->contarIntentosFallidos($cedula, $ip_address);

    if ($intentos_fallidos >= MAX_INTENTOS_LOGIN) {
        // Si se supera el límite, se muestra un error y se detiene el proceso.
        $_SESSION['login_error'] = 'Demasiados intentos fallidos. Por favor, inténtelo de nuevo en ' . TIEMPO_BLOQUEO_MINUTOS . ' minutos.';
        header('Location: ../login.php');
        exit();
    }

    // --- PASO 2: INTENTAR AUTENTICAR AL USUARIO ---
    $usuario = $usuarioDAO->getUsuarioPorCedula($cedula);

    // Se verifica que el usuario exista y que la contraseña sea correcta.
    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        // --- INICIO DE SESIÓN EXITOSO ---

        // 1. Limpiamos cualquier registro de intento fallido para este usuario.
        $usuarioDAO->limpiarIntentosFallidos($cedula);

        // 2. Regeneramos el ID de la sesión por seguridad.
        session_regenerate_id(true);

        // 3. Guardamos los datos del usuario en la sesión.
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombres'];
        $_SESSION['user_role'] = $usuario['nombre_rol']; // Usamos el nombre del rol directamente.

        // 4. Redirigimos al panel correspondiente según el rol del usuario.
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
                // Si el rol no tiene una redirección configurada.
                $_SESSION['login_error'] = "Rol de usuario no configurado para el acceso.";
                header('Location: ../login.php');
                break;
        }
        exit(); // Detenemos la ejecución del script.

    } else {
        // --- INICIO DE SESIÓN FALLIDO ---

        // 1. Registramos el intento fallido en la base de datos.
        $usuarioDAO->registrarIntentoFallido($cedula, $ip_address);

        // 2. Enviamos un mensaje de error genérico.
        $_SESSION['login_error'] = "Cédula o contraseña incorrecta.";
        header('Location: ../login.php');
        exit();
    }

} else {
    // Si alguien intenta acceder al script directamente sin enviar datos, lo redirigimos al login.
    header('Location: ../login.php');
    exit();
}
?>