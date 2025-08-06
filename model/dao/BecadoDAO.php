<?php
require_once __DIR__ . '/../../config/Conexion.php';
// Incluimos el autoloader de Composer para poder usar PhpSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class BecadoDAO {
    private $conn;
    private $registros_por_pagina = 25;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    public function getBecados($busqueda = '', $pagina = 1) {
        $offset = ($pagina - 1) * $this->registros_por_pagina;
        $busqueda_like = '%' . $busqueda . '%';

        $query = "SELECT * FROM becados 
                  WHERE nombres_apellidos LIKE :busqueda OR cedula LIKE :busqueda
                  ORDER BY nombres_apellidos ASC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':busqueda', $busqueda_like);
        $stmt->bindParam(':limit', $this->registros_por_pagina, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function contarBecados($busqueda = '') {
        $busqueda_like = '%' . $busqueda . '%';
        $query = "SELECT COUNT(id) FROM becados 
                  WHERE nombres_apellidos LIKE :busqueda OR cedula LIKE :busqueda";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':busqueda', $busqueda_like);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    public function cambiarEstado($id, $estado_actual) {
        $nuevo_estado = ($estado_actual === 'Activo') ? 'Inactivo' : 'Activo';
        $stmt = $this->conn->prepare("UPDATE becados SET estado = ? WHERE id = ?");
        return $stmt->execute([$nuevo_estado, $id]);
    }

    public function getRegistrosPorPagina() {
        return $this->registros_por_pagina;
    }

    /**
     * ¡NUEVA FUNCIÓN RESTAURADA!
     * Procesa un archivo Excel e inserta los becados en la base de datos.
     */
    public function importarBecados($filePath) {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $registros_insertados = 0;
        $filas_procesadas = 0;

        // Preparamos una única sentencia de inserción que se reutilizará
        $stmt_insert = $this->conn->prepare(
            "INSERT IGNORE INTO becados (nombres_apellidos, cedula, programa, ateneas_cursadas, estado) VALUES (?, ?, ?, 0, 'Activo')"
        );

        $this->conn->beginTransaction();
        try {
            for ($row = 2; $row <= $highestRow; $row++) { // Asumimos que la fila 1 son encabezados
                $nombres_apellidos = trim($sheet->getCell('A' . $row)->getValue());
                $cedula = trim($sheet->getCell('B' . $row)->getValue());
                $programa = trim($sheet->getCell('C' . $row)->getValue());

                if (empty($cedula) || empty($nombres_apellidos)) {
                    continue; // Omitir filas con datos esenciales faltantes
                }
                
                $filas_procesadas++;
                $stmt_insert->execute([$nombres_apellidos, $cedula, $programa]);
                
                // rowCount() nos dirá si la inserción fue exitosa (1) o ignorada (0)
                if ($stmt_insert->rowCount() > 0) {
                    $registros_insertados++;
                }
            }
            $this->conn->commit();

            return [
                'insertados' => $registros_insertados, 
                'omitidos' => $filas_procesadas - $registros_insertados
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error al procesar el archivo Excel: " . $e->getMessage());
        }
    }
}
?>