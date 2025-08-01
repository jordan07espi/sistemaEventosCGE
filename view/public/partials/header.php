<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      /* Estilos para el layout y sticky footer */
      body { 
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: #f8f9fa; 
      }
      main {
        flex: 1; /* Hace que el contenido principal ocupe el espacio sobrante */
      }

      /* Estilos para las tarjetas de evento */
      .event-card { 
        transition: transform 0.2s; 
      }
      .event-card:hover { 
        transform: scale(1.03); 
      }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/sistemaEventos/index.php">Eventos del Instituto CGE</a>
        </div>
    </nav>
    <main class="container py-5">