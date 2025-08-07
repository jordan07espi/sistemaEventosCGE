<?php 
require_once __DIR__ . '/../../controller/seguridad.php'; 
if ($_SESSION['user_role'] !== 'Admin') {
    die("Acceso denegado.");
}
?>

<?php
require_once __DIR__ . '/../../model/dao/CategoriaDAO.php';

$modoEdicion = false;
$categoria = null;

if (isset($_GET['id'])) {
    $modoEdicion = true;
    $categoriaDAO = new CategoriaDAO();
    // Necesitaremos una nueva función en el DAO para esto
    $categoria = $categoriaDAO->getCategoriaPorId($_GET['id']);
}

include 'partials/header.php';
?>


<h2><?php echo $modoEdicion ? 'Editar Categoría' : 'Crear Nueva Categoría'; ?></h2>
<hr>
<div class="card">
    <div class="card-body">
        <form id="form-categoria" action="../../controller/CategoriaControlador.php" method="POST">
            
            <input type="hidden" name="accion" value="<?php echo $modoEdicion ? 'editar' : 'crear'; ?>">
            <?php if ($modoEdicion): ?>
                <input type="hidden" name="id_categoria" value="<?php echo $categoria['id']; ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required value="<?php echo $modoEdicion ? htmlspecialchars($categoria['nombre']) : ''; ?>">
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
            <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php
include 'partials/footer.php';
?>