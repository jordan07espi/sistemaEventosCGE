<?php 
require_once __DIR__ . '/../../controller/seguridad.php'; 
if (!in_array($_SESSION['user_role'], ['Admin', 'Secretaria'])) {
    die("Acceso denegado.");
}
?>
<?php
// Incluimos el encabezado común
include 'partials/header.php';

// Incluimos el contenido principal de esta página
include 'gestion_participantes.php';

// Incluimos el pie de página común
include 'partials/footer.php';
?>