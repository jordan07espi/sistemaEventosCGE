<?php
require_once __DIR__ . '/../model/dao/ParticipanteDAO.php';
require_once __DIR__ . '/../model/dao/TipoEntradaDAO.php';

// --- MANEJADOR PARA SOLICITUDES GET (PANEL DE ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'No se especificó un evento.'];
    
    if (isset($_GET['id_evento'])) {
        $participanteDAO = new ParticipanteDAO();
        $id_evento = $_GET['id_evento'];
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25; // Registros por página

        $participantes = $participanteDAO->getParticipantesPorEventoId($id_evento, $busqueda, $pagina, $limite);
        $total = $participanteDAO->contarParticipantesPorEventoId($id_evento, $busqueda);
        
        $response = [
            'status' => 'success', 
            'data' => $participantes,
            'paginacion' => [
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'total_paginas' => ceil($total / $limite)
            ]
        ];
    }
    
    echo json_encode($response);
    exit();
}

// --- MANEJADOR PARA SOLICITUDES POST (FORMULARIO PÚBLICO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'errors' => ['Petición no válida.']];
    $errors = [];
    $participanteDAO = new ParticipanteDAO();
    $tipoEntradaDAO = new TipoEntradaDAO();
    
    // --- Recolección de Datos ---
    $id_evento = $_POST['id_evento'] ?? null;
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $sede = trim($_POST['sede'] ?? '');
    $id_tipo_entrada = $_POST['id_tipo_entrada'] ?? null;
    $numero_transaccion = trim($_POST['numero_transaccion'] ?? '');
    $banco = $_POST['banco'] ?? 'Otro';

    // --- NUEVOS CAMPOS ---
    $tipo_asistente = $_POST['tipo_asistente'] ?? '';
    $carrera_curso = '';
    $nivel = '';

    if ($tipo_asistente === 'Instituto') {
        $carrera_curso = $_POST['carrera_curso_instituto'] ?? '';
        $nivel = $_POST['nivel_instituto'] ?? '';
    } elseif ($tipo_asistente === 'Capacitadora') {
        $carrera_curso = $_POST['carrera_curso_capacitadora'] ?? '';
        $nivel = $_POST['nivel_capacitadora'] ?? '';
    }
    // Si es 'Externo', las variables quedan vacías, lo cual es correcto.

    // --- Lógica para Eventos Gratuitos ---
    $esGratuito = false;
    if ($id_tipo_entrada) {
        $tipoEntrada = $tipoEntradaDAO->getTipoEntradaPorId($id_tipo_entrada);
        if ($tipoEntrada && (float)$tipoEntrada['precio'] === 0.00) {
            $esGratuito = true;
        }
    }

    // --- Función de Validación de Cédula Ecuatoriana ---
    function validarCedula($cedula) {
        if (!is_string($cedula) || strlen($cedula) !== 10 || !ctype_digit($cedula)) return false;
        $provincia = substr($cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24) return false;
        $digitoVerificador = (int)substr($cedula, 9, 1);
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $producto = (int)$cedula[$i] * $coeficientes[$i];
            $suma += ($producto >= 10) ? $producto - 9 : $producto;
        }
        $resultado = ($suma % 10 === 0) ? 0 : 10 - ($suma % 10);
        return $resultado === $digitoVerificador;
    }

    // --- Reglas de Validación del Servidor ---
    if (empty($nombres) || !preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/iu', $nombres)) $errors[] = 'El campo Nombres es inválido (solo letras y espacios).';
    if (empty($apellidos) || !preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/iu', $apellidos)) $errors[] = 'El campo Apellidos es inválido (solo letras y espacios).';
    if (empty($cedula) || !validarCedula($cedula)) $errors[] = 'La cédula ingresada no es válida.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El formato del correo es inválido.';
    if (empty($telefono) || !preg_match('/^09\d{8}$/', $telefono)) $errors[] = 'El teléfono es inválido.';
    if (in_array($tipo_asistente, ['Instituto', 'Capacitadora']) && empty($sede)) {
        $errors[] = 'Debe seleccionar una sede.';
    }
    if (empty($tipo_asistente)) $errors[] = 'Debe especificar si pertenece al instituto, capacitadora o es externo.';
    if (empty($id_tipo_entrada)) $errors[] = 'Debe seleccionar un tipo de entrada.';
    
    // Validaciones condicionales para campos de pago
    if (!$esGratuito) {
        if (empty($numero_transaccion)) $errors[] = 'El número de transacción es obligatorio.';
        if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] != 0) $errors[] = 'Es obligatorio subir el comprobante.';
    }

    // Si no hay errores de formato, procedemos a validar contra la BD
    if (empty($errors)) {
        if ($participanteDAO->cedulaYaRegistradaEnEvento($cedula, $id_evento)) {
            $errors[] = "La cédula '$cedula' ya está registrada en este evento.";
        }
        if (!$esGratuito && !empty($numero_transaccion) && $participanteDAO->transaccionYaRegistrada($numero_transaccion, $banco)) {
            $errors[] = "El número de transacción '$numero_transaccion' del banco '$banco' ya ha sido utilizado.";
        }
    }

    // --- Procesamiento ---
    if (empty($errors)) {
        try {
            $ruta_para_bd = 'N/A';
            if (!$esGratuito) {
                $nombre_carpeta_banco = preg_replace("/[^a-zA-Z0-9]+/", "", $banco);
                $directorio_subida = __DIR__ . '/../uploads/comprobantes/' . $nombre_carpeta_banco . '/';
                if (!is_dir($directorio_subida)) mkdir($directorio_subida, 0777, true);
                $nombre_archivo = uniqid() . '-' . basename($_FILES['comprobante']['name']);
                $ruta_completa = $directorio_subida . $nombre_archivo;
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_completa)) {
                    $ruta_para_bd = 'uploads/comprobantes/' . $nombre_carpeta_banco . '/' . $nombre_archivo;
                } else {
                    throw new Exception("Hubo un error al guardar el comprobante.");
                }
            } else {
                $numero_transaccion = 'GRATUITO-' . uniqid();
                $banco = 'N/A';
            }

            // Llamada al método ACTUALIZADO del DAO con los nuevos campos
            $nuevoId = $participanteDAO->crearParticipante(
                $nombres, $apellidos, $cedula, $email, $telefono, $sede, 
                $tipo_asistente, $carrera_curso, $nivel, // Nuevos parámetros
                $id_tipo_entrada, $numero_transaccion, $banco, $ruta_para_bd
            );
            
            if ($nuevoId) {
                $response = [
                    'status' => 'success', 
                    'message' => '¡Registro guardado exitosamente!',
                    'id_participante' => $nuevoId
                ];
            } else {
                throw new Exception("No se pudo obtener el ID del nuevo registro.");
            }

        } catch (Exception $e) {
            $response = ['status' => 'error', 'errors' => [$e->getMessage()]];
        }
    } else {
        $response = ['status' => 'error', 'errors' => $errors];
    }
    
    echo json_encode($response);
    exit();
}
?>