<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
      body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
      }
      main {
        flex: 1;
      }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">Admin Eventos</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="categorias.php">Categorías</a></li>
                            <li class="nav-item"><a class="nav-link" href="eventos.php">Eventos</a></li>
                            <li class="nav-item"><a class="nav-link" href="lugares.php">Lugares</a></li>
                            <li class="nav-item"><a class="nav-link" href="participantes.php">Participantes</a></li>
                            <?php endif; ?>

                        <?php if ($_SESSION['user_role'] === 'Admin' || $_SESSION['user_role'] === 'Secretaria'): ?>
                            <li class="nav-item"><a class="nav-link" href="participantes.php">Participantes</a></li>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] === 'Admin' || $_SESSION['user_role'] === 'Supervisor'): ?>
                            <li class="nav-item"><a class="nav-link" href="escanear.php">Escanear QR</a></li>
                        <?php endif; ?>
                    </ul>
                    <div class="d-flex text-white">
                        <span class="navbar-text me-3">Hola, <?php echo $_SESSION['user_nombre']; ?> (<?php echo $_SESSION['user_role']; ?>)</span>
                        <a href="../../controller/logout.php" class="btn btn-outline-light">Salir</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-4">