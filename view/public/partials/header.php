<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/flipdown@0.3.2/dist/flipdown.css">
    <style>
          /* Estilos para el layout y sticky footer */
          body { 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa; 
          }
          main {
            flex: 1;
          }

          /* Estilos para las tarjetas de evento */
          .event-card { 
            transition: transform 0.2s; 
          }
          .event-card:hover { 
            transform: scale(1.03); 
          }
          
          /* --- ESTILOS CORREGIDOS PARA EL CONTADOR --- */
          /* Cambia el color de fondo de TODAS las partes de los números */
            /* 
            .flipdown.flipdown__theme-dark .rotor,
            .flipdown.flipdown__theme-dark .rotor-leaf-front,
            .flipdown.flipdown__theme-dark .rotor-leaf-rear,
            .flipdown.flipdown__theme-dark .rotor-bottom { 
            background-color: var(--bs-primary, #0d6efd); 
            }
            */

            /* 
            .flipdown.flipdown__theme-dark .rotor,
            .flipdown.flipdown__theme-dark .rotor-top,
            .flipdown.flipdown__theme-dark .rotor-leaf-front {
              color: #fff;
            }
            */

            /* Ajuste para centrar y escalar el contador */
            .flipdown {
                transform: scale(0.85); /* Reduce el tamaño al 85%. ¡Puedes ajustar este valor! */
                transform-origin: center;
            }

          /* --- FIN DE ESTILOS CORREGIDOS --- */

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/sistemaEventos/index.php">Eventos del Instituto CGE</a>
        </div>
    </nav>
    <main class="container py-5">