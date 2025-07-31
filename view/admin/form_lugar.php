<?php
require_once __DIR__ . '/../../model/dao/LugarDAO.php';

$modoEdicion = false;
$lugar = null;

if (isset($_GET['id'])) {
    $modoEdicion = true;
    $lugarDAO = new LugarDAO();
    $lugar = $lugarDAO->getLugarPorId($_GET['id']);
}

include 'partials/header.php';
?>

<div class="container mt-4">
    <h2><?php echo $modoEdicion ? 'Editar Lugar' : 'Crear Nuevo Lugar'; ?></h2>
    <hr>
    <div class="card">
        <div class="card-body">
            <form  id="form-lugar" action="../../controller/LugarControlador.php" method="POST">
                
                <input type="hidden" name="accion" value="<?php echo $modoEdicion ? 'editar' : 'crear'; ?>">
                <?php if ($modoEdicion): ?>
                    <input type="hidden" name="id_lugar" value="<?php echo $lugar['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nombre_establecimiento" class="form-label">Nombre del Establecimiento</label>
                    <input type="text" class="form-control" id="nombre_establecimiento" name="nombre_establecimiento" required value="<?php echo $modoEdicion ? htmlspecialchars($lugar['nombre_establecimiento']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo $modoEdicion ? htmlspecialchars($lugar['direccion']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" required value="<?php echo $modoEdicion ? htmlspecialchars($lugar['ciudad']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="capacidad" class="form-label">Capacidad</label>
                    <input type="number" class="form-control" id="capacidad" name="capacidad" min="0" value="<?php echo $modoEdicion ? htmlspecialchars($lugar['capacidad']) : ''; ?>">
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
                <a href="lugares.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
include 'partials/footer.php';
?>