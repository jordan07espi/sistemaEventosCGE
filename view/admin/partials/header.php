<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
      body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: #f8f9fa;
      }
      main {
        flex: 1;
      }
      .card .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
      .card .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
      .card .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
      .card .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
      .text-gray-300 { color: #dddfeb !important; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">EvaSoft</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="adminNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="categorias.php">Categorías</a></li>
                            <li class="nav-item"><a class="nav-link" href="eventos.php">Eventos</a></li>
                            <li class="nav-item"><a class="nav-link" href="lugares.php">Lugares</a></li>
                            <li class="nav-item"><a class="nav-link" href="becados.php">Becados</a></li> <li class="nav-item"><a class="nav-link" href="usuarios.php">Usuarios</a></li>
                            <li class="nav-item"><a class="nav-link" href="participantes.php">Participantes</a></li>
                            <li class="nav-item"><a class="nav-link" href="escanear.php">Escanear QR</a></li>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] === 'Secretaria'): ?>
                            <li class="nav-item"><a class="nav-link" href="participantes.php">Participantes</a></li>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] === 'Supervisor'): ?>
                            <li class="nav-item"><a class="nav-link" href="escanear.php">Escanear QR</a></li>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex">
                        <span class="navbar-text me-3">
                            Hola, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?> 
                            (<strong><?php echo htmlspecialchars($_SESSION['user_role']); ?></strong>)
                        </span>
                        <a href="../../controller/logout.php" class="btn btn-outline-light">Salir</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4">