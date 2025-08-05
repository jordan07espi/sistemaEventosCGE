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


// --- LÓGICA PARA OBTENER LA FECHA DEL EVENTO PARA EL CONTADOR ---
$fechaTimestamp = 0;
if (!empty($calendarios)) {
    // Tomamos la fecha de la primera función del evento
    $primeraFuncion = $calendarios[0];
    // Combinamos fecha y hora y la convertimos a un timestamp de Unix
    $fechaTimestamp = strtotime($primeraFuncion['fecha'] . ' ' . $primeraFuncion['hora']);
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
                
                <h4>El evento comienza en:</h4>
                <div id="flipdown" class="flipdown mb-3 "></div>
                <hr>

                <h4>Cupos Disponibles</h4>
                <ul class="list-unstyled">
                    <?php 
                    $hayCuposDefinidos = false;
                    foreach($calendarios as $cal): 
                        $tipos = $tipoEntradaDAO->getTiposEntradaPorCalendarioId($cal['id']);
                        if (!empty($tipos)) $hayCuposDefinidos = true;
                        foreach($tipos as $tipo):
                    ?>
                        <li>
                            <strong><?php echo htmlspecialchars($tipo['nombre']); ?>:</strong>
                            <span class="badge bg-primary ms-2"><?php echo $tipo['cantidad_disponible']; ?> restantes</span>
                        </li>
                    <?php 
                        endforeach; 
                    endforeach; 
                    if (!$hayCuposDefinidos) {
                        echo "<p class='text-muted'>No hay tipos de entrada definidos para este evento.</p>";
                    }
                    ?>
                </ul>
                <hr>

                <h4>Funciones</h4>
                <?php if(empty($calendarios)): ?>
                    <p class="text-muted">No hay fechas programadas para este evento todavía.</p>
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
                <?php
                if (isset($_SESSION['mensaje_error'])) {
                    echo '<div class="alert alert-danger mt-3" role="alert">' . $_SESSION['mensaje_error'] . '</div>';
                    unset($_SESSION['mensaje_error']); // Limpiamos el mensaje para no mostrarlo de nuevo
                }
                ?>

                <form id="form-registro-participante" action="../../controller/ParticipanteControlador.php" method="POST" enctype="multipart/form-data" novalidate>
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

                    <div class="mb-3">
                        <label class="form-label">¿Perteneces a?</label>
                        <select name="tipo_asistente" id="tipo_asistente_select" class="form-select" required>
                            <option value="" disabled selected>-- Elige una opción --</option>
                            <option value="Instituto">Instituto CGE</option>
                            <option value="Capacitadora">Capacitadora CGE</option>
                            <option value="Externo">Público General / Externo</option>
                        </select>
                    </div>

                    <div class="mb-3" id="campo-sede" style="display:none;">
                        <label class="form-label">Sede</label>
                        <select name="sede" id="sede_select" class="form-select">
                            </select>
                    </div>

                    <div id="campos-instituto" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Carrera del Instituto</label>
                            <select name="carrera_curso_instituto" id="carrera_select" class="form-select">
                                <option value="" disabled selected>-- Elige una carrera --</option>
                                <option value="Enfermeria">ENFERMERIA</option>
                                <option value="Emergencias Medicas">EMERGENCIAS MEDICAS</option>
                                <option value="REHABILITACION FISICA">REHABILITACION FISICA</option>
                                <option value="ADMINISTRACION DE SISTEMAS DE LA SALUD">ADMINISTRACION DE SISTEMAS DE LA SALUD</option>
                                <option value="EDUCACION INICIAL">EDUCACION INICIAL</option>
                                <option value="ADMINISTRACION DE EMPRESAS - ONLINE">ADMINISTRACION DE EMPRESAS - ONLINE</option>
                                <option value="ADMINISTRACIÓN DE FARMACIAS">ADMINISTRACIÓN DE FARMACIAS</option>
                                <option value="MARKETING DIGITAL Y COMERCIO ELECTRONICO">MARKETING DIGITAL Y COMERCIO ELECTRONICO</option>
                                <option value="NATUROPATIA">NATUROPATIA</option>
                                <option value="GASTRONOMIA">GASTRONOMIA</option>
                                <option value="MECANICA AUTOMOTRIZ">MECANICA AUTOMOTRIZ</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nivel</label>
                            <select name="nivel_instituto" id="nivel_instituto_select" class="form-select">
                                <option value="" disabled selected>-- Elige un Nivel --</option>
                                <option value="PRIMERO">PRIMERO</option>
                                <option value="SEGUNDO">SEGUNDO</option>
                                <option value="TERCERO">TERCERO</option>
                                <option value="CUARTO">CUARTO</option>
                            </select>
                        </div>
                    </div>

                    <div id="campos-capacitadora" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Curso de Capacitación</label>
                            <select name="carrera_curso_capacitadora" id="curso_select" class="form-select">
                                <option value="" disabled selected>-- ELIGE UN CURSO --</option>
                                <option value="AUXILIAR DE ENFERMERIA">AUXILIAR DE ENFERMERIA</option>
                                <option value="AUXILIAR DE FARMACIA">AUXILIAR DE FARMACIA</option>
                                <option value="AUXILIAR DE PARVULOS">AUXILIAR DE PARVULOS</option>
                                <option value="AUXILIAR DE LABORATORIO">AUXILIAR DE LABORATORIO</option>
                                <option value="AUXILIAR EN EMERGENCIAS MEDICAS">AUXILIAR EN EMERGENCIAS MEDICAS</option>
                                <option value="AUXILIAR DE ODONTOLOGIA">AUXILIAR DE ODONTOLOGIA</option>
                                <option value="AUXILIAR DE RIESGOS Y DESASTRES">AUXILIAR DE RIESGOS Y DESASTRES</option>
                                <option value="AUXILIAR EN IMAGENOLOGIA MEDICA">AUXILIAR EN IMAGENOLOGIA MEDICA</option>
                                <option value="AUXILIAR DE NUTRICION">AUXILIAR DE NUTRICION</option>
                                <option value="AUXILIAR DE NATUROPATIA">AUXILIAR DE NATUROPATIA</option>
                                <option value="AUXILIAR DE VETERINARIA">AUXILIAR DE VETERINARIA</option>
                                <option value="AUXILIAR EN GERIATRIA">AUXILIAR EN GERIATRIA</option>
                                <option value="AUXILIAR EN CUIDADOS INTENSIVOS">AUXILIAR EN CUIDADOS INTENSIVOS</option>
                                <option value="REHABILITACION FISICA">REHABILITACION FISICA</option>
                                <option value="ULISES">ULISES</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nivel</label>
                            <select name="nivel_capacitadora" id="nivel_capacitadora_select" class="form-select">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="Nivel <?php echo $i; ?>">Nivel <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
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
                        <label class="form-label">Banco desde donde realizó el pago</label>
                        <select name="banco" class="form-select">
                            <option value="Banco Pichincha">Banco Pichincha</option>
                            <option value="Banco del Pacifico">Banco del Pacífico</option>
                            <option value="Banco de Guayaquil">Banco de Guayaquil</option>
                            <option value="Produbanco">Produbanco</option>
                            <option value="Banco del Austro">Banco del Austro</option>
                            <option value="Banco Bolivariano">Banco Bolivariano</option>
                            <option value="Banco Internacional">Banco Internacional</option>
                            <option value="Banco General Rumiñahui">Banco General Rumiñahui</option>
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

<script>
    var unixTimestamp = <?php echo $fechaTimestamp; ?>;
</script>
<?php
include 'partials/footer.php';
?>