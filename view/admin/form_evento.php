<?php
require_once __DIR__ . '/../../model/dao/EventoDAO.php';
require_once __DIR__ . '/../../model/dao/CategoriaDAO.php';

$modoEdicion = false;
$evento = null;

if (isset($_GET['id'])) {
    $modoEdicion = true;
    $eventoDAO = new EventoDAO();
    $evento = $eventoDAO->getEventoPorId($_GET['id']);
}

$categoriaDAO = new CategoriaDAO();
$categorias = $categoriaDAO->getCategorias();

include 'partials/header.php';
?>

<div class="container mt-4">
    <h2><?php echo $modoEdicion ? 'Editar Evento' : 'Crear Nuevo Evento'; ?></h2>
    <hr>
    <div class="card">
        <div class="card-body">
            <form id="form-evento" action="../../controller/EventoControlador.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="accion" value="<?php echo $modoEdicion ? 'editar' : 'crear'; ?>">
                <?php if ($modoEdicion): ?>
                    <input type="hidden" name="id_evento" value="<?php echo $evento['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nombre_evento" class="form-label">Nombre del Evento</label>
                    <input type="text" class="form-control" id="nombre_evento" name="nombre_evento" required value="<?php echo $modoEdicion ? htmlspecialchars($evento['nombre']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="descripcion_evento" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion_evento" name="descripcion_evento" rows="3"><?php echo $modoEdicion ? htmlspecialchars($evento['descripcion']) : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="id_categoria" class="form-label">Categoría</label>
                    <select class="form-select" id="id_categoria" name="id_categoria" required>
                        <option value="" disabled <?php echo !$modoEdicion ? 'selected' : ''; ?>>-- Seleccione una categoría --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <?php if ($cat['activa'] == 1): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($modoEdicion && $cat['id'] == $evento['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr>
                <p class="fw-bold">Banner del Evento</p>

                <?php if ($modoEdicion && !empty($evento['enlace_imagen'])): ?>
                    <div class="mb-3">
                        <p><strong>Banner Actual:</strong></p>
                        <img src="/sistemaEventos/<?php echo htmlspecialchars($evento['enlace_imagen']); ?>" alt="Banner Actual" class="img-thumbnail" style="max-width: 300px;">
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="banner_archivo" class="form-label">
                        <?php echo $modoEdicion ? 'Subir una nueva imagen (reemplazará la actual)' : 'Subir imagen del banner'; ?>
                    </label>
                    <input class="form-control" type="file" id="banner_archivo" name="banner_archivo" accept="image/*" <?php echo !$modoEdicion ? 'required' : ''; ?>>
                </div>
                <hr>

                <button type="submit" class="btn btn-primary"><?php echo $modoEdicion ? 'Actualizar Evento' : 'Guardar Evento'; ?></button>
                <a href="eventos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
include 'partials/footer.php';
?>