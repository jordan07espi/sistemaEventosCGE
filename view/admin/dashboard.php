<?php
// Incluimos el DAO para la carga inicial (aunque ahora la mayoría es por AJAX)
require_once __DIR__ . '/../../model/dao/DashboardDAO.php';
$dashboardDAO = new DashboardDAO();
$datos_iniciales = $dashboardDAO->getDatosGenerales();
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div class="d-flex align-items-center">
             <label for="evento-dashboard-select" class="me-2 mb-0"><strong>Filtrar por Evento:</strong></label>
            <select id="evento-dashboard-select" class="form-select" style="min-width: 250px;">
                <option value="">-- Ver todos los eventos --</option>
                </select>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Eventos</div>
                            <div id="total-eventos" class="h5 mb-0 font-weight-bold text-gray-800"><?= $datos_iniciales['total_eventos'] ?></div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Participantes Registrados</div>
                            <div id="total-participantes" class="h5 mb-0 font-weight-bold text-gray-800"><?= $datos_iniciales['total_participantes'] ?></div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Asistencia Real</div>
                            <div id="total-asistentes" class="h5 mb-0 font-weight-bold text-gray-800"><?= $datos_iniciales['total_asistentes'] ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ingresos Totales</div>
                            <div id="total-ingresos" class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($datos_iniciales['total_ingresos'], 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 id="grafico-titulo" class="m-0 font-weight-bold text-primary">Distribución de Participantes por Evento</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 400px;">
                        <canvas id="participantesPorEventoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>