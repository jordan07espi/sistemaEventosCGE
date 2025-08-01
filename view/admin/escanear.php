<?php
// Incluimos los DAOs para obtener la lista de eventos activos
require_once __DIR__ . '/../../model/dao/EventoDAO.php';
$eventoDAO = new EventoDAO();
$eventos = $eventoDAO->getEventos();

include 'partials/header.php';
?>

<div class="container mt-4">
    <h2>Control de Asistencia por QR</h2>
    <p class="text-muted">Selecciona un evento para empezar a registrar la asistencia de los participantes.</p>
    <hr>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label for="evento-checkin-select" class="form-label fw-bold">1. Selecciona el Evento:</label>
                    <select id="evento-checkin-select" class="form-select">
                        <option value="" selected disabled>-- Elige un evento --</option>
                        <?php foreach($eventos as $evento): ?>
                            <?php if ($evento['estado'] === 'Activo'): ?>
                                <option value="<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['nombre']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="search-asistencia" class="form-label fw-bold">2. Buscar Participante:</label>
                    <input type="text" id="search-asistencia" class="form-control" placeholder="Buscar por nombre, apellido o cédula..." disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">3. Escanear:</label>
                    <button id="start-scan-btn" class="btn btn-primary w-100" disabled>
                        <i class="fas fa-qrcode"></i> Iniciar Cámara
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="scanner-container" class="mb-4" style="display: none;">
        <div class="row">
            <div class="col-md-8">
                <div id="qr-reader" style="width: 100%;"></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Resultado del Escaneo</strong></div>
                    <div class="card-body d-flex align-items-center justify-content-center" id="scan-result-container">
                        <p class="text-muted">Apunte la cámara al código QR...</p>
                    </div>
                </div>
                <button id="stop-scan-btn" class="btn btn-danger w-100 mt-2">Detener Cámara</button>
            </div>
        </div>
    </div>

    <div id="participantes-checkin-container" style="display: none;">
        <h5>Lista de Asistencia</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Apellidos y Nombres</th>
                        <th>Cédula</th>
                        <th>Estado de Asistencia</th>
                        <th>Acción Manual</th>
                    </tr>
                </thead>
                <tbody id="tabla-asistencia-body">
                    </tbody>
            </table>
        </div>
        <div id="pagination-asistencia-container" class="d-flex justify-content-center mt-3"></div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<?php
include 'partials/footer.php';
?>