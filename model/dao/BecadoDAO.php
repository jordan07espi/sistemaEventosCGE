<?php
require_once __DIR__ . '/../../config/Conexion.php';

class BecadoDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    // Obtener todos los becados para mostrarlos en la tabla
    public function getBecados() {
        $stmt = $this->conn->prepare("SELECT * FROM becados ORDER BY nombres_apellidos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un solo becado (usado por la importación de Excel)
        public function crearBecado($cedula, $nombres_apellidos, $programa) {
            $stmt = $this->conn->prepare("INSERT INTO becados (cedula, nombres_apellidos, programa) VALUES (?, ?, ?)");
            return $stmt->execute([$cedula, $nombres_apellidos, $programa]);
        }

    // Cambiar el estado de un becado (Activo/Inactivo)
    public function cambiarEstado($id, $estado) {
        $stmt = $this->conn->prepare("UPDATE becados SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
    
    // Obtener todas las cédulas que ya existen en la tabla
    public function getCedulasExistentes() {
        $stmt = $this->conn->prepare("SELECT cedula FROM becados");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    // Verifica si un estudiante es becado activo y si aún tiene cupos para ATENEA
    public function isBecadoValido($cedula) {
        $stmt = $this->conn->prepare("SELECT * FROM becados WHERE cedula = ? AND estado = 'Activo' AND ateneas_cursadas < 3");
        $stmt->execute([$cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Incrementa el contador de ATENEA para un becado
    public function incrementarAtenea($cedula) {
        $stmt = $this->conn->prepare("UPDATE becados SET ateneas_cursadas = ateneas_cursadas + 1 WHERE cedula = ?");
        return $stmt->execute([$cedula]);
    }
}
?>