<?php
require_once __DIR__ . '/../model/dao/ParticipanteDAO.php';
require_once __DIR__ . '/../model/dao/TipoEntradaDAO.php';
require_once __DIR__ . '/../model/dao/BecadoDAO.php';

// --- MANEJADOR PARA SOLICITUDES GET (PANEL DE ADMIN) ---
// Esta sección se mantiene sin cambios.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'No se especificó un evento.'];
    
    if (isset($_GET['id_evento'])) {
        $participanteDAO = new ParticipanteDAO();
        $id_evento = $_GET['id_evento'];
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 25;

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
    
    // ✅ CAMBIO #1: INSTANCIACIÓN ÚNICA DE OBJETOS DAO
    // Se crean una sola vez al inicio del script para estar disponibles en todo momento.
    $participanteDAO = new ParticipanteDAO();
    $tipoEntradaDAO = new TipoEntradaDAO();
    $becadoDAO = new BecadoDAO(); 
    
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

    // --- Lógica de Tipo de Entrada (Gratuito / Beca) ---
    $esGratuito = false;
    $esEntradaBeca = false; 
    if ($id_tipo_entrada) {
        $tipoEntrada = $tipoEntradaDAO->getTipoEntradaPorId($id_tipo_entrada);
        if ($tipoEntrada && (float)$tipoEntrada['precio'] === 0.00) {
            $esGratuito = true;
            $esEntradaBeca = true; 
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

    // --- BLOQUE CENTRAL DE VALIDACIONES ---
    // Todas las comprobaciones se hacen aquí, antes de cualquier operación en la base de datos.
    if (empty($nombres) || !preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/iu', $nombres)) $errors[] = 'El campo Nombres es inválido (solo letras mayúsculas y espacios).';
    if (empty($apellidos) || !preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/iu', $apellidos)) $errors[] = 'El campo Apellidos es inválido (solo letras mayúsculas y espacios).';
    if (empty($cedula) || !validarCedula($cedula)) $errors[] = 'La cédula ingresada no es válida.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El formato del correo es inválido.';
    if (empty($telefono) || !preg_match('/^09\d{8}$/', $telefono)) $errors[] = 'El teléfono es inválido. Debe empezar con 09 y tener 10 dígitos.';
    if (in_array($tipo_asistente, ['Instituto', 'Capacitadora']) && empty($sede)) $errors[] = 'Debe seleccionar una sede.';
    if (empty($id_tipo_entrada)) $errors[] = 'Debe seleccionar un tipo de entrada.';
    
    if (!$esGratuito) {
        if (empty($numero_transaccion)) $errors[] = 'El número de transacción es obligatorio.';
        if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] != 0) $errors[] = 'Es obligatorio subir el comprobante de pago.';
    }

    // ✅ CAMBIO #2: VALIDACIÓN DE BECA EN EL LUGAR CORRECTO
    // Se valida la beca junto al resto de las reglas de negocio.
    if ($esEntradaBeca) {
        if (!$becadoDAO->isBecadoValido($cedula)) {
            $errors[] = "La cédula ingresada no corresponde a un estudiante con beca activa. Contactese con secretaria para más información.";
        }
    }

    // Si no hay errores de formato, validamos contra la Base de Datos
    if (empty($errors)) {
        if ($participanteDAO->cedulaYaRegistradaEnEvento($cedula, $id_evento)) {
            $errors[] = "La cédula '$cedula' ya está registrada en este evento.";
        }
        if (!$esGratuito && !empty($numero_transaccion) && $participanteDAO->transaccionYaRegistrada($numero_transaccion, $banco)) {
            $errors[] = "El número de transacción '$numero_transaccion' del banco '$banco' ya ha sido utilizado.";
        }
    }

    // --- Procesamiento (Solo si no hay errores) ---
    if (empty($errors)) {
        try {
            $ruta_para_bd = 'N/A';
            if (!$esGratuito) {
                $nombre_carpeta_banco = preg_replace("/[^a-zA-Z0-9]+/", "", $banco);
                $directorio_subida = __DIR__ . '/../uploads/comprobantes/' . $nombre_carpeta_banco . '/';
                if (!is_dir($directorio_subida)) {
                    mkdir($directorio_subida, 0777, true);
                }
                $nombre_archivo = uniqid() . '-' . basename($_FILES['comprobante']['name']);
                $ruta_completa = $directorio_subida . $nombre_archivo;
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_completa)) {
                    $ruta_para_bd = 'uploads/comprobantes/' . $nombre_carpeta_banco . '/' . $nombre_archivo;
                } else {
                    throw new Exception("Hubo un error al guardar el comprobante de pago.");
                }
            } else {
                $numero_transaccion = $esEntradaBeca ? 'BECA-' . uniqid() : 'GRATUITO-' . uniqid();
                $banco = 'N/A';
            }

            // Llamada al DAO para crear el participante
            $nuevoId = $participanteDAO->crearParticipante(
                $nombres, $apellidos, $cedula, $email, $telefono, $sede, 
                $tipo_asistente, $carrera_curso, $nivel,
                $id_tipo_entrada, $numero_transaccion, $banco, $ruta_para_bd
            );
            
            if ($nuevoId) {
                // ✅ CAMBIO #3: USO CORRECTO DEL OBJETO DAO EXISTENTE
                // Si el registro fue de beca, se usa el objeto $becadoDAO ya creado.
                if ($esEntradaBeca) {
                    $becadoDAO->incrementarAtenea($cedula);
                }

                $response = [
                    'status' => 'success', 
                    'message' => '¡Registro guardado exitosamente!',
                    'id_participante' => $nuevoId
                ];
            } else {
                throw new Exception("No se pudo obtener el ID del nuevo registro después de la inserción.");
            }

        } catch (Exception $e) {
            $response = ['status' => 'error', 'errors' => [$e->getMessage()]];
        }
    } else {
        // Si hubo errores de validación, se devuelven todos juntos.
        $response = ['status' => 'error', 'errors' => $errors];
    }

    echo json_encode($response);
    exit();
}
?>