<?php
require_once __DIR__ . '/../../model/dao/DashboardDAO.php';
$dashboardDAO = new DashboardDAO();

// Obtenemos todos los datos que necesitamos para el nuevo dashboard
$totalEventos = $dashboardDAO->getTotalEventosActivos();
$totalParticipantes = $dashboardDAO->getTotalParticipantes();
$totalIngresos = $dashboardDAO->getTotalIngresos();
$proximoEvento = $dashboardDAO->getProximoEvento();

// ¡Nuevos datos!
$nuevosParticipantesMes = $dashboardDAO->getNuevosParticipantesMes();
$ingresosMesActual = $dashboardDAO->getIngresosMesActual();

// Formatear la fecha del próximo evento
$fechaProximoEvento = 'No hay eventos próximos';
if ($proximoEvento) {
    setlocale(LC_TIME, 'es_ES.UTF-8');
    $fechaProximoEvento = strftime("%A, %d de %B", strtotime($proximoEvento['proxima_fecha']));
}
?>

<style>
    .stat-card {
        background-color: #fff;
        border: 1px solid #e3e6f0;
        border-radius: .35rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.75rem 0 rgba(58,59,69,.25);
    }
    .stat-card .card-title {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    .stat-card .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #3a3b45;
    }
    .stat-card .card-icon {
        font-size: 3rem;
        color: #dddfeb;
    }
    .stat-card .sub-text {
        font-size: 0.8rem;
    }
    .text-primary { color: #4e73df !important; }
    .text-success { color: #1cc88a !important; }
    .text-info { color: #36b9cc !important; }
    .text-warning { color: #f6c23e !important; }

    /* Estilo para el mini-gráfico de ingresos */
    .mini-chart {
        height: 50px;
        position: relative;
    }
    .mini-chart-line {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 40px;
        background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none'/%3e%3cpath d='M0 30 C20 10, 40 40, 60 20 C80 0, 100 30, 120 15 C140 0, 160 25, 180 20' stroke='%2336b9cc' stroke-width='2' fill='none'/%3e%3c/svg%3e");
        background-size: cover;
    }
</style>

<div class="container mt-4">
    <h2 class="mb-4">Dashboard General</h2>
    
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="card-title text-primary">Eventos Activos</div>
                        <div class="stat-value"><?php echo $totalEventos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x card-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="card-title text-success">Participantes Totales</div>
                        <div class="stat-value"><?php echo $totalParticipantes; ?></div>
                        <p class="sub-text text-muted mb-0">+<?php echo $nuevosParticipantesMes; ?> este mes</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x card-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="card-title text-info">Ingresos Totales</div>
                        <div class="stat-value">$<?php echo number_format($totalIngresos, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="card-title text-warning">Próximo Evento</div>
                        <div class="stat-value">
                            <?php echo $proximoEvento ? htmlspecialchars($proximoEvento['nombre']) : 'Ninguno'; ?>
                        </div>
                        <?php if ($proximoEvento): ?>
                            <p class="sub-text text-muted mb-0">Fecha: <?php echo date("d/m/Y", strtotime($proximoEvento['proxima_fecha'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-forward fa-2x card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Participantes por Evento</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px; position: relative;">
                        <canvas id="participantesPorEventoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>