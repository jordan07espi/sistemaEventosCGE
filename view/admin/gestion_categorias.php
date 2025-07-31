<?php
require_once __DIR__ . '/../../model/dao/CategoriaDAO.php';
$categoriaDAO = new CategoriaDAO();
$categorias = $categoriaDAO->getCategorias();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Gestión de Categorías</h2>
        <a href="form_categoria.php" class="btn btn-primary">Crear Nueva Categoría</a>
    </div>
    <hr>
    <div class="card">
        <div class="card-header">
            Lista de Categorías
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-categorias-body">
                    <tr>
                        <td colspan="3" class="text-center">Cargando categorías...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
