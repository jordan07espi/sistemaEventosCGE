<?php
// Incluimos todos los DAOs que vamos a necesitar en esta página
require_once __DIR__ . '/../../model/dao/EventoDAO.php';
require_once __DIR__ . '/../../model/dao/CalendarioDAO.php';
require_once __DIR__ . '/../../model/dao/PonenteDAO.php';
require_once __DIR__ . '/../../model/dao/TipoEntradaDAO.php';
require_once __DIR__ . '/../../model/dao/LugarDAO.php';

// Validación del ID del evento
if (!isset($_GET['id'])) {
    header('Location: eventos.php');
    exit();
}
$id_evento = $_GET['id'];

// Instanciamos todos los DAOs
$eventoDAO = new EventoDAO();
$calendarioDAO = new CalendarioDAO();
$ponenteDAO = new PonenteDAO();
$tipoEntradaDAO = new TipoEntradaDAO();
$lugarDAO = new LugarDAO();

// Obtenemos los datos necesarios para la página
$evento = $eventoDAO->getEventoPorId($id_evento);
if (!$evento) {
    header('Location: eventos.php');
    exit();
}
$calendarios = $calendarioDAO->getCalendariosPorEventoId($id_evento);
$lugares = $lugarDAO->getLugares();

// Incluimos el header
include 'partials/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Detalle del Evento</h2>
        <a href="eventos.php" class="btn btn-secondary">Volver a la Lista</a>
    </div>

    <div class="card mb-4">
        <img src="<?php echo '../../' . htmlspecialchars($evento['enlace_imagen']); ?>" class="card-img-top" alt="Banner del Evento" style="max-height: 400px; object-fit: cover;">
        <div class="card-body">
            <h3 class="card-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Funciones del Evento</h4>
        </div>
        <div class="card-body">
            <div class="card bg-light p-3 mb-4">
                <h5>Agregar Nueva Función</h5>
                <form id="form-agregar-funcion" action="../../controller/CalendarioControlador.php" method="POST">
                    <input type="hidden" name="accion" value="crear">
                    <input type="hidden" name="id_evento" value="<?php echo $evento['id']; ?>">
                    <div class="row align-items-end">
                        <div class="col-md-4"><label for="fecha" class="form-label">Fecha</label><input type="date" class="form-control" name="fecha" required></div>
                        <div class="col-md-3"><label for="hora" class="form-label">Hora</label><input type="time" class="form-control" name="hora" required></div>
                        <div class="col-md-3">
                            <label for="id_lugar" class="form-label">Lugar</label>
                            <select name="id_lugar" class="form-select" required>
                                <option value="" disabled selected>Seleccionar...</option>
                                <?php foreach($lugares as $lugar): ?>
                                    <option value="<?php echo $lugar['id']; ?>"><?php echo htmlspecialchars($lugar['nombre_establecimiento']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Agregar</button></div>
                    </div>
                </form>
            </div>
            
            <hr>
            <h5>Funciones Programadas</h5>

            <?php if (empty($calendarios)): ?>
                <p>Este evento aún no tiene fechas programadas.</p>
            <?php else: ?>
                <?php foreach ($calendarios as $calendario): ?>
                    <div class="mb-3 p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>🗓️ Fecha: <?php echo date("d/m/Y", strtotime($calendario['fecha'])); ?> - 📍 Lugar: <?php echo htmlspecialchars($calendario['nombre_establecimiento'] ?? 'No asignado'); ?></h5>
                            <form class="form-eliminar-funcion" action="../../controller/CalendarioControlador.php" method="POST">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_evento" value="<?php echo $evento['id']; ?>">
                                <input type="hidden" name="id_calendario" value="<?php echo $calendario['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar Función</button>
                            </form>
                        </div>
                        <hr>

                        <div>
                            <h6>Ponentes Asignados:</h6>
                            <div id="ponentes-list-<?php echo $calendario['id']; ?>">
                                <?php $ponentes = $ponenteDAO->getPonentesPorCalendarioId($calendario['id']); ?>
                                <?php if (empty($ponentes)): ?>
                                    <p class="ms-3 fst-italic">- No hay ponentes asignados.</p>
                                <?php else: ?>
                                    <ul><?php foreach($ponentes as $ponente) { echo '<li>' . htmlspecialchars($ponente['nombre_completo']) . ' (' . htmlspecialchars($ponente['especialidad']) . ')</li>'; } ?></ul>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2">
                                <form class="form-agregar-ponente row gx-2" action="../../controller/PonenteControlador.php" method="POST">
                                    <input type="hidden" name="accion" value="crear"><input type="hidden" name="id_evento" value="<?php echo $evento['id']; ?>"><input type="hidden" name="id_calendario" value="<?php echo $calendario['id']; ?>">
                                    <div class="col-5"><input type="text" name="nombre_ponente" class="form-control form-control-sm" placeholder="Nombre completo del ponente" required></div>
                                    <div class="col-5"><input type="text" name="especialidad_ponente" class="form-control form-control-sm" placeholder="Especialidad (Ej: PhD en IA)" required></div>
                                    <div class="col-2"><button type="submit" class="btn btn-success btn-sm w-100">Añadir</button></div>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4">
                             <h6>Tipos de Entrada:</h6>
                             <?php $tiposEntrada = $tipoEntradaDAO->getTiposEntradaPorCalendarioId($calendario['id']); ?>
                             <div class="entradas-container" id="entradas-container-<?php echo $calendario['id']; ?>" <?php if(empty($tiposEntrada)) echo 'style="display:none;"'; ?>>
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark"><tr><th>Nombre</th><th>Detalle</th><th>Precio</th><th>Cupos</th></tr></thead>
                                    <tbody id="entradas-table-body-<?php echo $calendario['id']; ?>">
                                    <?php foreach($tiposEntrada as $tipo): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tipo['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($tipo['detalle']); ?></td>
                                            <td>$<?php echo number_format($tipo['precio'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($tipo['cantidad_disponible']); ?> / <?php echo htmlspecialchars($tipo['cantidad_total']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                             </div>
                             <p class="ms-3 fst-italic empty-entradas-msg" id="entradas-empty-msg-<?php echo $calendario['id']; ?>" <?php if(!empty($tiposEntrada)) echo 'style="display:none;"'; ?>>- No hay tipos de entrada definidos.</p>
                             <div class="mt-3">
                                <form class="form-agregar-entrada row gx-2" action="../../controller/TipoEntradaControlador.php" method="POST">
                                    <input type="hidden" name="accion" value="crear"><input type="hidden" name="id_evento" value="<?php echo $evento['id']; ?>"><input type="hidden" name="id_calendario" value="<?php echo $calendario['id']; ?>">
                                    <div class="col-md-3"><input type="text" name="nombre_entrada" class="form-control form-control-sm" placeholder="Nombre (Ej: General)" required></div>
                                    <div class="col-md-4"><input type="text" name="detalle_entrada" class="form-control form-control-sm" placeholder="Detalle (Ej: Incluye almuerzo)"></div>
                                    <div class="col-md-2"><input type="number" step="0.01" min="0" name="precio_entrada" class="form-control form-control-sm" placeholder="Precio" required></div>
                                    <div class="col-md-2"><input type="number" min="1" name="cantidad_entrada" class="form-control form-control-sm" placeholder="Cantidad" required></div>
                                    <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100">Añadir</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include 'partials/footer.php';
?>