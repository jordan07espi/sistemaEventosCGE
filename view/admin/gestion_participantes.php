<?php
// Necesitamos la lista de eventos para llenar el menú desplegable
require_once __DIR__ . '/../../model/dao/EventoDAO.php';
$eventoDAO = new EventoDAO();
$eventos = $eventoDAO->getEventos();
?>
<div class="container mt-4">
    <h2>Gestión de Participantes</h2>
    <hr>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <label for="evento-select" class="form-label">Selecciona un evento para ver sus participantes:</label>
                    <select id="evento-select" class="form-select">
                        <option value="" selected disabled>-- Elige un evento --</option>
                        <?php foreach($eventos as $evento): ?>
                            <?php if ($evento['estado'] !== 'Cancelado'): ?>
                                <option value="<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['nombre']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <h5 id="titulo-tabla-participantes" style="display:none;">Participantes Registrados</h5>
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
    </div>
</div>