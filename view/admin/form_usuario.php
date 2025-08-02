<?php
require_once __DIR__ . '/../../controller/seguridad.php'; 
if ($_SESSION['user_role'] !== 'Admin') { die("Acceso denegado."); }
require_once __DIR__ . '/../../model/dao/UsuarioDAO.php';

$modoEdicion = false;
$usuario = null;
$usuarioDAO = new UsuarioDAO();

if (isset($_GET['id'])) {
    $modoEdicion = true;
    $usuario = $usuarioDAO->getUsuarioPorId($_GET['id']);
}
$roles = $usuarioDAO->getRoles();

include 'partials/header.php';
?>
<div class="container mt-4">
    <h2><?php echo $modoEdicion ? 'Editar Usuario' : 'Crear Nuevo Usuario'; ?></h2>
    <hr>
    <div class="card">
        <div class="card-body">
            <form id="form-usuario" action="../../controller/UsuarioControlador.php" method="POST">
                <input type="hidden" name="accion" value="<?php echo $modoEdicion ? 'editar' : 'crear'; ?>">
                <?php if ($modoEdicion): ?><input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>"><?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Nombres</label><input type="text" name="nombres" class="form-control" required value="<?php echo $modoEdicion ? htmlspecialchars($usuario['nombres']) : ''; ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Apellidos</label><input type="text" name="apellidos" class="form-control" required value="<?php echo $modoEdicion ? htmlspecialchars($usuario['apellidos']) : ''; ?>"></div>
                </div>
                    <div class="mb-3"><label class="form-label">Número de Cédula</label><input type="text" name="cedula" class="form-control" required value="<?php echo $modoEdicion ? htmlspecialchars($usuario['cedula']) : ''; ?>"></div>
                    <div class="mb-3"><label class="form-label">Correo Electrónico</label><input type="email" name="email" class="form-control" required value="<?php echo $modoEdicion ? htmlspecialchars($usuario['email']) : ''; ?>"></div>
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="id_rol" class="form-select" required>
                        <?php foreach($roles as $rol): ?>
                            <option value="<?php echo $rol['id']; ?>" <?php echo ($modoEdicion && $rol['id'] == $usuario['id_rol']) ? 'selected' : ''; ?>><?php echo $rol['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!$modoEdicion): ?>
                <div class="mb-3"><label class="form-label">Contraseña</label><input type="password" name="contrasena" class="form-control" required></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><?php echo $modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>