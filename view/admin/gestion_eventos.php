<?php
// Incluimos el DAO necesario
require_once __DIR__ . '/../../model/dao/EventoDAO.php';
$eventoDAO = new EventoDAO();
$eventos = $eventoDAO->getEventos();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Gestión de Eventos</h2>
        <a href="form_evento.php" class="btn btn-primary">Crear Nuevo Evento</a>
    </div>
    <hr>
    <div class="card">
        <div class="card-header">
            Lista de Eventos
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-eventos-body">
                    <tr>
                        <td colspan="4" class="text-center">Cargando eventos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>