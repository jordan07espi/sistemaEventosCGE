<?php
require_once __DIR__ . '/../../config/Conexion.php';

class PonenteDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getPonentesPorCalendarioId($calendario_id) {
        $query = "SELECT id, nombre_completo, especialidad FROM ponentes WHERE id_calendario = :calendario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':calendario_id', $calendario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearPonente($id_calendario, $nombre, $especialidad) {
        $query = "INSERT INTO ponentes (id_calendario, nombre_completo, especialidad) VALUES (:id_calendario, :nombre, :especialidad)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_calendario', $id_calendario, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':especialidad', $especialidad);
        return $stmt->execute();
    }
}
?>