<?php
require_once __DIR__ . '/../../model/dao/DashboardDAO.php';
$dashboardDAO = new DashboardDAO();

$totalEventos = $dashboardDAO->getTotalEventosActivos();
$totalParticipantes = $dashboardDAO->getTotalParticipantes();
$totalIngresos = $dashboardDAO->getTotalIngresos();
$proximoEvento = $dashboardDAO->getProximoEvento();
?>
<div class="container mt-4">
    <h2 class="mb-4">Dashboard General</h2>
    
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Eventos Activos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalEventos; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Participantes Totales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalParticipantes; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ingresos Totales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($totalIngresos, 2); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Próximo Evento</div>
                            <?php if ($proximoEvento): ?>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($proximoEvento['nombre']); ?></div>
                            <?php else: ?>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Ninguno</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto"><i class="fas fa-forward fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Participantes por Evento</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="participantesPorEventoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>