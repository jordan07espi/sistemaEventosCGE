<?php
require_once 'model/dao/EventoDAO.php';

$eventoDAO = new EventoDAO();
// Obtenemos solo los eventos que estén 'Activo'
$eventos = $eventoDAO->getEventos(); // Asumimos que getEventos() ya filtra por estado o lo adaptamos

include 'view/public/partials/header.php';
?>

<div class="text-center mb-5">
    <h1>Próximos Eventos</h1>
    <p class="lead">Explora nuestros eventos y regístrate para participar.</p>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php foreach ($eventos as $evento): ?>
        <?php if ($evento['estado'] == 'Activo'): ?>
        <div class="col">
            <div class="card h-100 shadow-sm event-card">
                <img src="/sistemaEventos/<?php echo htmlspecialchars($evento['enlace_imagen']); ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($evento['nombre']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($evento['nombre_categoria']); ?></p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a href="view/public/registro_evento.php?id_evento=<?php echo $evento['id']; ?>" class="btn btn-primary w-100">Ver Detalles y Registrarse</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php
include 'view/public/partials/footer.php';
?>