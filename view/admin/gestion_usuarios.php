<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead><tr><th>Nombre Completo</th><th>Cédula</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead>
                <tbody id="tabla-usuarios-body"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Restablecer Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="form-reset-pass">
          <div class="modal-body">
            <input type="hidden" name="accion" value="reset_pass_manual">
            <input type="hidden" id="reset-id-usuario" name="id_usuario">
            <div class="mb-3">
                <label for="nueva-contrasena" class="form-label">Escribe la nueva contraseña para <strong id="nombre-usuario-reset"></strong>:</label>
                <input type="password" class="form-control" id="nueva-contrasena" name="nueva_contrasena" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Nueva Contraseña</button>
          </div>
      </form>
    </div>
  </div>
</div>