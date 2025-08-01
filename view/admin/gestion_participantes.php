<?php
require_once __DIR__ . '/../../model/dao/EventoDAO.php';
$eventoDAO = new EventoDAO();
$eventos = $eventoDAO->getEventos();
?>
<div class="container mt-4">
    <h2>Gestión de Participantes</h2>
    <hr>
    <div class="card">
        <div class="card-header">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="evento-select" class="form-label">Selecciona un evento:</label>
                    <select id="evento-select" class="form-select">
                        <option value="" selected disabled>-- Elige un evento --</option>
                        <?php foreach($eventos as $evento): ?>
                            <?php if ($evento['estado'] !== 'Cancelado'): ?>
                                <option value="<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['nombre']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-7">
                    <input type="text" id="search-participante" class="form-control" placeholder="Buscar por nombre, apellido o cédula..." style="display: none;">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Cédula</th>
                            <th>Email</th>
                            <th>Entrada</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-participantes-body">
                        <tr>
                            <td colspan="5" class="text-center text-muted">Por favor, selecciona un evento para comenzar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="d-flex justify-content-center mt-3"></div>
        </div>
    </div>
</div>