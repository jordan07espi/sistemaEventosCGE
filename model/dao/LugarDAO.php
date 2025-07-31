<?php
require_once __DIR__ . '/../../config/Conexion.php';

class LugarDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    public function getLugares() {
        $query = "SELECT * FROM lugares ORDER BY nombre_establecimiento";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLugarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM lugares WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearLugar($nombre, $direccion, $ciudad, $capacidad) {
        $stmt = $this->conn->prepare("INSERT INTO lugares (nombre_establecimiento, direccion, ciudad, capacidad) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $direccion, $ciudad, $capacidad]);
    }

    public function actualizarLugar($id, $nombre, $direccion, $ciudad, $capacidad) {
        $stmt = $this->conn->prepare("UPDATE lugares SET nombre_establecimiento = ?, direccion = ?, ciudad = ?, capacidad = ? WHERE id = ?");
        $stmt->execute([$nombre, $direccion, $ciudad, $capacidad, $id]);
    }

    public function eliminarLugar($id) {
        $stmt = $this->conn->prepare("DELETE FROM lugares WHERE id = ?");
        $stmt->execute([$id]);
    }
}
?>