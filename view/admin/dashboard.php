<?php
// Incluimos el DAO para la carga inicial de datos en las tarjetas
require_once __DIR__ . '/../../model/dao/DashboardDAO.php';
$dashboardDAO = new DashboardDAO();
$datos_iniciales = $dashboardDAO->getDatosGenerales();
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Dashboard</h1>
        
        <div class="input-group" style="width: auto; max-width: 450px;">
            <label class="input-group-text" for="evento-dashboard-select"><strong>Filtrar por Evento:</strong></label>
            <select id="evento-dashboard-select" class="form-select">
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
                            <div id="total-eventos" class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($datos_iniciales['total_eventos']) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Registrados</div>
                            <div id="total-participantes" class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($datos_iniciales['total_participantes']) ?></div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Asistentes</div>
                            <div id="total-asistentes" class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($datos_iniciales['total_asistentes']) ?></div>
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
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 id="grafico-titulo" class="m-0 font-weight-bold text-primary">Distribución General por Evento</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 400px; position: relative;">
                        <canvas id="participantesPorEventoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>