<?php 
require_once __DIR__ . '/../../controller/seguridad.php'; 
if ($_SESSION['user_role'] !== 'Admin') { die("Acceso denegado."); }
include 'partials/header.php';
include 'gestion_becados.php'; // Incluimos el contenido
include 'partials/footer.php';
?>