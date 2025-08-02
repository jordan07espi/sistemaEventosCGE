<?php 
require_once __DIR__ . '/../../controller/seguridad.php'; 
if ($_SESSION['user_role'] !== 'Admin') {
    die("Acceso denegado."); // O redirigir
}
?>
<?php
// Incluimos el encabezado común
include 'partials/header.php';

// Incluimos el contenido específico de la gestión de categorías
include 'gestion_categorias.php';

// Incluimos el pie de página común
include 'partials/footer.php';
?>