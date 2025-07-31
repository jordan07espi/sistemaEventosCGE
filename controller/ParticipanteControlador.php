<?php
require_once __DIR__ . '/../model/dao/ParticipanteDAO.php';
require_once __DIR__ . '/../model/dao/TipoEntradaDAO.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'errors' => ['Petición no válida.']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $id_tipo_entrada = $_POST['id_tipo_entrada'] ?? null;
    $numero_transaccion = trim($_POST['numero_transaccion'] ?? '');
    $banco = $_POST['banco'] ?? 'Otro'; // Recogemos el nuevo campo del banco

    // --- LÓGICA PARA EVENTOS GRATUITOS ---
    $esGratuito = false;
    if ($id_tipo_entrada) {
        $tipoEntrada = $tipoEntradaDAO->getTipoEntradaPorId($id_tipo_entrada);
        if ($tipoEntrada && (float)$tipoEntrada['precio'] === 0.00) {
            $esGratuito = true;
        }
    }

    // --- Función de Validación de Cédula Ecuatoriana ---
    function validarCedula($cedula) {
        if (!is_string($cedula) || strlen($cedula) !== 10 || !ctype_digit($cedula)) {
            return false;
        }
        $provincia = substr($cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return false;
        }
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
    if (empty($nombres)) $errors[] = 'El campo Nombres es obligatorio.';
    if (!preg_match('/^[A-Z\s]+$/i', $nombres)) $errors[] = 'El campo Nombres solo puede contener letras y espacios.';
    
    if (empty($apellidos)) $errors[] = 'El campo Apellidos es obligatorio.';
    if (!preg_match('/^[A-Z\s]+$/i', $apellidos)) $errors[] = 'El campo Apellidos solo puede contener letras y espacios.';

    if (empty($cedula)) $errors[] = 'El campo Cédula es obligatorio.';
    if (!validarCedula($cedula)) $errors[] = 'La cédula ingresada no es válida.';
    
    if (empty($email)) $errors[] = 'El campo Correo Electrónico es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El formato del correo electrónico es inválido.';

    if (empty($telefono)) $errors[] = 'El campo Teléfono es obligatorio.';
    if (!preg_match('/^09\d{8}$/', $telefono)) $errors[] = 'El teléfono debe tener 10 dígitos y empezar con 09.';

    if (empty($id_tipo_entrada)) $errors[] = 'Debe seleccionar un tipo de entrada.';

    // SOLO validamos los campos de pago SI NO es gratuito
    if (!$esGratuito) {
        if (empty($numero_transaccion)) $errors[] = 'El número de transacción es obligatorio.';
        if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] != 0) {
            $errors[] = 'Es obligatorio subir el archivo del comprobante.';
        }
    }

    // Si no hay errores de formato, procedemos a validar contra la BD
    if (empty($errors)) {
        if ($participanteDAO->cedulaYaRegistradaEnEvento($cedula, $id_evento)) {
            $errors[] = "La cédula '$cedula' ya ha sido registrada en este evento.";
        }
        // Solo validamos transacción si no es gratuito y se proveyó un número
        if (!$esGratuito && !empty($numero_transaccion) && $participanteDAO->transaccionYaRegistrada($numero_transaccion)) {
            $errors[] = "El número de transacción '$numero_transaccion' ya ha sido utilizado.";
        }
    }

    // --- Procesamiento ---
    if (empty($errors)) {
        try {
            $ruta_para_bd = 'N/A'; // Valor por defecto
            
            if (!$esGratuito) {
                // Lógica de subida de archivo para eventos de pago
                $nombre_carpeta_banco = preg_replace("/[^a-zA-Z0-9]+/", "", $banco);
                $directorio_subida = __DIR__ . '/../uploads/comprobantes/' . $nombre_carpeta_banco . '/';
                if (!is_dir($directorio_subida)) mkdir($directorio_subida, 0777, true);
                
                $nombre_archivo = uniqid() . '-' . basename($_FILES['comprobante']['name']);
                $ruta_completa = $directorio_subida . $nombre_archivo;
                
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_completa)) {
                    $ruta_para_bd = 'uploads/comprobantes/' . $nombre_carpeta_banco . '/' . $nombre_archivo;
                } else {
                    throw new Exception("Hubo un error al guardar el archivo del comprobante.");
                }
            } else {
                // Si es gratuito, asignamos valores por defecto
                $numero_transaccion = 'GRATUITO-' . uniqid();
                $banco = 'N/A';
            }

            // Llamamos al DAO con el nuevo parámetro de banco
            $participanteDAO->crearParticipante($nombres, $apellidos, $cedula, $email, $telefono, $id_tipo_entrada, $numero_transaccion, $banco, $ruta_para_bd);
            $response = ['status' => 'success', 'message' => '¡Registro guardado exitosamente! Gracias por inscribirte.'];

        } catch (Exception $e) {
            $response = ['status' => 'error', 'errors' => [$e->getMessage()]];
        }
    } else {
        $response = ['status' => 'error', 'errors' => $errors];
    }
}

echo json_encode($response);
exit();
?>