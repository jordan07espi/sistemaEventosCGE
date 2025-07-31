<?php
require_once __DIR__ . '/../../config/Conexion.php';

class CalendarioDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getCalendariosPorEventoId($evento_id) {
        // Unimos con la tabla lugares para obtener el nombre
        $query = "SELECT c.id, c.fecha, c.hora, c.estado, l.nombre_establecimiento 
                FROM calendarios c
                LEFT JOIN lugares l ON c.id_lugar = l.id
                WHERE c.id_evento = :evento_id 
                ORDER BY c.fecha, c.hora";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCalendario($id_evento, $fecha, $hora, $id_lugar) {
        $query = "INSERT INTO calendarios (id_evento, fecha, hora, id_lugar) VALUES (:id_evento, :fecha, :hora, :id_lugar)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':id_lugar', $id_lugar, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarCalendario($id_calendario) {
    // Al eliminar un calendario, también se eliminan los ponentes y tipos de entrada asociados
    // gracias a la configuración "ON DELETE CASCADE" en la base de datos.
    $query = "DELETE FROM calendarios WHERE id = :id_calendario";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_calendario', $id_calendario, PDO::PARAM_INT);
    return $stmt->execute();
    }
}
?>