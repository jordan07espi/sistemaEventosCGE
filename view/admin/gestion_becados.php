<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Gestión de Estudiantes Becados</h2>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importarBecadosModal">
            <i class="fas fa-file-excel"></i> Importar desde Excel
        </button>
    </div>
    <hr>

    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="search-becados-input" class="form-control" placeholder="Buscar por nombre o cédula...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <small id="pagination-info" class="text-muted"></small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre y Apellidos</th>
                            <th>Programa</th>
                            <th>Ateneas Cursadas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-becados-body">
                        </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-center">
            <nav>
                <ul class="pagination" id="pagination-controls">
                    </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="importarBecadosModal" tabindex="-1" aria-labelledby="importarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importarModalLabel">Importar Becados desde Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-importar-becados" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="importar_excel">
                    
                    <label for="archivo_excel" class="form-label">Seleccionar archivo (.xlsx, .xls)</label>
                    <input type="file" class="form-control" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
                    
                    <div class="form-text mt-2">
                        El archivo debe tener 3 columnas en este orden: <strong>NOMBRE Y APELLIDOS, CÉDULA, PROGRAMA</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Importar Estudiantes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="notification-area" class="position-fixed top-0 end-0 p-3" style="z-index: 1055"></div>