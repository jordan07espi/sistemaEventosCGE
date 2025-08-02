<?php 
require_once __DIR__ . '/../../controller/seguridad.php'; 
if ($_SESSION['user_role'] !== 'Admin') {
    die("Acceso denegado."); // O redirigir
}
?>
<?php
include 'partials/header.php';
include 'gestion_lugares.php';
include 'partials/footer.php';
?>