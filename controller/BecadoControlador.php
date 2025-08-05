<?php
// Asegúrate de que la ruta al autoload de Composer sea correcta
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../model/dao/BecadoDAO.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Establecer la cabecera para respuestas JSON
header('Content-Type: application/json');

$becadoDAO = new BecadoDAO();
$response = ['status' => 'error', 'message' => 'Acción no válida o no especificada.'];

// Determinar el método de la solicitud (GET o POST)
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    if ($requestMethod === 'GET') {
        // Si es una solicitud GET, se asume que se están pidiendo todos los becados.
        $becados = $becadoDAO->getBecados();
        $response = ['status' => 'success', 'data' => $becados];

    } elseif ($requestMethod === 'POST') {
        // Si es una solicitud POST, se verifica la acción a realizar.
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'cambiar_estado') {
            // Acción para activar o desactivar un becado.
            $id_becado = $_POST['id_becado'] ?? 0;
            $estado_actual = $_POST['estado_actual'] ?? '';
            
            if (empty($id_becado) || empty($estado_actual)) {
                throw new Exception("Faltan datos para cambiar el estado.");
            }

            $nuevo_estado = ($estado_actual === 'Activo') ? 'Inactivo' : 'Activo';
            $becadoDAO->cambiarEstado($id_becado, $nuevo_estado);
            
            // Se devuelve la lista actualizada de becados.
            $becados = $becadoDAO->getBecados();
            $response = ['status' => 'success', 'message' => 'Estado del becado actualizado correctamente.', 'data' => $becados];

        } elseif ($accion === 'importar_excel') {
            // Acción para importar becados desde un archivo Excel.
            if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error en la subida del archivo o archivo no proporcionado.");
            }

            // Cargar el archivo Excel utilizando PhpSpreadsheet
            $spreadsheet = IOFactory::load($_FILES['archivo_excel']['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();

            // Obtener las cédulas que ya existen para evitar duplicados.
            $cedulasExistentes = $becadoDAO->getCedulasExistentes();
            $nuevos = 0;
            $omitidos = 0;

            // Iterar sobre las filas del Excel, comenzando desde la fila 2 para omitir los encabezados.
            for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
                // --- LÓGICA CORREGIDA PARA LEER COLUMNAS ---
                $nombres_apellidos = trim($sheet->getCell('A' . $row)->getValue());
                $cedula = trim($sheet->getCell('B' . $row)->getValue());
                $programa = trim($sheet->getCell('C' . $row)->getValue());

                // --- VALIDACIONES ---
                // 1. Omitir filas si algún campo esencial está vacío.
                if (empty($cedula) || empty($nombres_apellidos) || empty($programa)) {
                    $omitidos++;
                    continue;
                }
                // 2. Omitir si la cédula ya existe en la base de datos.
                if (in_array($cedula, $cedulasExistentes)) {
                    $omitidos++;
                    continue;
                }

                // Si todas las validaciones pasan, se llama al método DAO correcto.
                $becadoDAO->crearBecado($cedula, $nombres_apellidos, $programa);
                
                // Añadir la cédula recién insertada al array para evitar duplicados dentro del mismo archivo.
                $cedulasExistentes[] = $cedula;
                $nuevos++;
            }
            
            // Se devuelve la lista actualizada y un mensaje resumen.
            $becados = $becadoDAO->getBecados();
            $response = [
                'status' => 'success', 
                'message' => "$nuevos becados importados. $omitidos registros omitidos (por estar vacíos o duplicados).", 
                'data' => $becados
            ];
        } else {
            // Si la acción no es reconocida.
             throw new Exception('La acción solicitada no es válida.');
        }
    }
} catch (Exception $e) {
    // Capturar cualquier error inesperado durante el proceso.
    $response['message'] = $e->getMessage();
}

// Imprimir la respuesta final en formato JSON.
echo json_encode($response);
exit();
?>