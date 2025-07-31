<?php
require_once __DIR__ . '/../model/dao/EventoDAO.php';

header('Content-Type: application/json');
$eventoDAO = new EventoDAO();

// Si la petición es GET, simplemente listamos los eventos.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['status' => 'success', 'data' => $eventoDAO->getEventos()]);
    exit();
}

// Si la petición es POST, manejamos las diferentes acciones.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Acción no válida.'];
    $accion = $_POST['accion'] ?? null;

    try {
        // --- Acción para Crear o Editar un Evento ---
        if ($accion === 'crear' || $accion === 'editar') {
            
            $nombre = $_POST['nombre_evento'];
            $descripcion = $_POST['descripcion_evento'];
            $id_categoria = $_POST['id_categoria'];
            $enlace_imagen_final = '';

            if ($accion === 'editar') {
                $id_evento = $_POST['id_evento'];
                $evento_actual = $eventoDAO->getEventoPorId($id_evento);
                $enlace_imagen_final = $evento_actual['enlace_imagen']; // Mantenemos la imagen actual por defecto
            }
            
            // Si se subió un archivo nuevo, este siempre tiene prioridad.
            if (isset($_FILES['banner_archivo']) && $_FILES['banner_archivo']['error'] === UPLOAD_ERR_OK) {
                $directorio_subida = __DIR__ . '/../uploads/banners/';
                if (!is_dir($directorio_subida)) {
                    mkdir($directorio_subida, 0777, true);
                }
                $nombre_archivo = uniqid() . '-' . basename($_FILES['banner_archivo']['name']);
                $ruta_completa = $directorio_subida . $nombre_archivo;

                if (move_uploaded_file($_FILES['banner_archivo']['tmp_name'], $ruta_completa)) {
                    $enlace_imagen_final = 'uploads/banners/' . $nombre_archivo;
                } else {
                    throw new Exception('Error al mover el archivo. Revisa permisos de /uploads.');
                }
            } elseif ($accion === 'crear' && empty($enlace_imagen_final)) {
                // Si es un evento nuevo y no se subió archivo, lanzamos un error.
                throw new Exception('Es obligatorio subir una imagen para crear un evento.');
            }

            if ($accion === 'crear') {
                $eventoDAO->crearEvento($nombre, $descripcion, $id_categoria, $enlace_imagen_final);
                $response = ['status' => 'success', 'message' => 'Evento creado con éxito.'];
            } else {
                $id_evento = $_POST['id_evento'];
                $eventoDAO->actualizarEvento($id_evento, $nombre, $descripcion, $id_categoria, $enlace_imagen_final);
                $response = ['status' => 'success', 'message' => 'Evento actualizado con éxito.'];
            }
        } 
        
        // --- NUEVO CASO: Acción para Cambiar el Estado de un Evento ---
        elseif ($accion === 'cambiar_estado') {
            $id_evento = $_POST['id_evento'];
            $estado_actual = $_POST['estado_actual'];
            
            // Determinamos el nuevo estado
            $nuevo_estado = ($estado_actual === 'Activo') ? 'Finalizado' : 'Activo';
            
            $eventoDAO->cambiarEstadoEvento($id_evento, $nuevo_estado);
            
            $response = ['status' => 'success', 'message' => 'Estado del evento actualizado.'];
        }

        // --- Respuesta Final ---
        // Si cualquier operación fue exitosa, adjuntamos la lista actualizada de eventos.
        if (isset($response['status']) && $response['status'] === 'success') {
            $response['data'] = $eventoDAO->getEventos();
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}
?>