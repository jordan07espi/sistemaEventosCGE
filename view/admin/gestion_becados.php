<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Gestión de Estudiantes Becados</h2>
    </div>
    <hr>

    <div class="card mb-4">
        <div class="card-header">
            Importar Becados desde Excel
        </div>
        <div class="card-body">
            <form id="form-importar-becados" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="importar_excel">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="archivo_excel" class="form-label">Seleccionar archivo (.xlsx, .xls)</label>
                        <input type="file" class="form-control" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
                        <div class="form-text">
                            El archivo debe tener 3 columnas: <strong>NOMBRE Y APELLIDOS, CÉDULA, PROGRAMA</strong> (en ese orden).
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-file-excel"></i> Importar Estudiantes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

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