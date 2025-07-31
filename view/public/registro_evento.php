<?php
// Iniciar sesión para poder leer los mensajes de estado
session_start();

require_once __DIR__ . '/../../model/dao/EventoDAO.php';
require_once __DIR__ . '/../../model/dao/CalendarioDAO.php';
require_once __DIR__ . '/../../model/dao/TipoEntradaDAO.php';

if (!isset($_GET['id_evento'])) {
    header('Location: ../../index.php');
    exit();
}
$id_evento = $_GET['id_evento'];

$eventoDAO = new EventoDAO();
$evento = $eventoDAO->getEventoPorId($id_evento);

if (!$evento) {
    // Si no se encuentra el evento, redirigir a la página principal
    header('Location: ../../index.php');
    exit();
}

$calendarioDAO = new CalendarioDAO();
$calendarios = $calendarioDAO->getCalendariosPorEventoId($id_evento);

$tipoEntradaDAO = new TipoEntradaDAO();

// --- CÓDIGO PARA CALCULAR CUPOS TOTALES DISPONIBLES ---
$cuposTotalesDisponibles = 0;
foreach ($calendarios as $cal) {
    $tipos = $tipoEntradaDAO->getTiposEntradaPorCalendarioId($cal['id']);
    foreach ($tipos as $tipo) {
        $cuposTotalesDisponibles += $tipo['cantidad_disponible'];
    }
}

include 'partials/header.php';
?>

<div class="row">
    <div class="col-lg-5 mb-4 mb-lg-0">
        <div class="card shadow-sm h-100">
            <img src="/sistemaEventos/<?php echo htmlspecialchars($evento['enlace_imagen']); ?>" class="card-img-top" alt="Banner Evento">
            <div class="card-body">
                <h3><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                <p class="lead text-muted"><?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?></p>
                <hr>
                
                <h4>Cupos Disponibles: <span class="badge bg-primary"><?php echo $cuposTotalesDisponibles; ?></span></h4>
                <hr>

                <h4>Funciones Disponibles</h4>
                <?php if(empty($calendarios)): ?>
                    <p>No hay fechas programadas para este evento todavía.</p>
                <?php else: ?>
                    <?php foreach($calendarios as $cal): ?>
                        <div class="mb-2">
                            <strong>🗓️ Fecha:</strong> <?php echo date("d/m/Y", strtotime($cal['fecha'])); ?> <br>
                            <strong>🕒 Hora:</strong> <?php echo date("h:i A", strtotime($cal['hora'])); ?> <br>
                            <strong>📍 Lugar:</strong> <?php echo htmlspecialchars($cal['nombre_establecimiento']); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4">Formulario de Registro</h4>
                
                <div id="alert-container"></div>

                <form id="form-registro-participante" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="id_evento" value="<?php echo $id_evento; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" id="nombres" name="nombres" class="form-control" required>
                            <div class="form-text text-danger" id="nombres-error"></div>
                            <div class="form-text text-warning" id="nombres-sugerencia"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" class="form-control" required>
                            <div class="form-text text-danger" id="apellidos-error"></div>
                            <div class="form-text text-warning" id="apellidos-sugerencia"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cédula</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" required maxlength="10">
                        <div class="form-text text-danger" id="cedula-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" required maxlength="10">
                        <div class="form-text text-danger" id="telefono-error"></div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Seleccione el Tipo de Entrada</label>
                        <select name="id_tipo_entrada" id="tipo_entrada_select" class="form-select" required>
                            <option value="" disabled selected>-- Elige una opción --</option>
                            <?php foreach($calendarios as $cal): ?>
                                <optgroup label="Fecha: <?php echo date("d/m/Y", strtotime($cal['fecha'])); ?>">
                                    <?php $tipos = $tipoEntradaDAO->getTiposEntradaPorCalendarioId($cal['id']); ?>
                                    <?php foreach($tipos as $tipo): ?>
                                        <?php 
                                            $cupos_disponibles = $tipo['cantidad_disponible'];
                                            $texto_cupos = ($cupos_disponibles > 0) ? "- Quedan $cupos_disponibles cupos" : "- AGOTADO";
                                            $esta_deshabilitado = ($cupos_disponibles <= 0) ? 'disabled' : '';
                                        ?>
                                        <option value="<?php echo $tipo['id']; ?>" data-precio="<?php echo $tipo['precio']; ?>" <?php echo $esta_deshabilitado; ?>>
                                            <?php echo htmlspecialchars($tipo['nombre']); ?> ($<?php echo number_format($tipo['precio'], 2); ?>) <?php echo $texto_cupos; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="campo-banco" class="mb-3" style="display:none;">
                        <label class="form-label">Banco de la Transacción</label>
                        <select name="banco" class="form-select">
                            <option value="Banco Pichincha">Banco Pichincha</option>
                            <option value="Banco del Pacifico">Banco del Pacífico</option>
                            <option value="Otro">Otro Banco</option>
                        </select>
                    </div>

                    <div id="campo-transaccion" class="mb-3" style="display:none;">
                        <label class="form-label">Número de Transacción/Depósito</label>
                        <input type="text" name="numero_transaccion" class="form-control">
                    </div>

                    <div id="campo-comprobante" class="mb-3" style="display:none;">
                        <label class="form-label">Subir Comprobante (Imagen o PDF)</label>
                        <input type="file" name="comprobante" class="form-control" accept="image/*,.pdf">
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="../../index.php" class="btn btn-secondary">Volver a la Lista</a>
                        <button type="submit" class="btn btn-primary">Registrarme</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'partials/footer.php';
?>