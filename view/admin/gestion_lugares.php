<?php
require_once __DIR__ . '/../../model/dao/LugarDAO.php';
$lugarDAO = new LugarDAO();
$lugares = $lugarDAO->getLugares();
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Gestión de Lugares</h2>
        <a href="form_lugar.php" class="btn btn-primary">Crear Nuevo Lugar</a>
    </div>
    <hr>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Establecimiento</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Capacidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-lugares-body">
                    <tr>
                        <td colspan="5" class="text-center">Cargando lugares...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>