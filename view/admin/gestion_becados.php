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
        <div class="card-body">
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
                    <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>