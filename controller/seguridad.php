<?php
session_start();
// Si no hay una sesión de usuario iniciada, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>