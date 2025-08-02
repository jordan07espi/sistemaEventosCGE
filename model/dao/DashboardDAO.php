<?php
require_once __DIR__ . '/../../config/Conexion.php';

class DashboardDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    // Devuelve el número total de eventos activos
    public function getTotalEventosActivos() {
        $stmt = $this->conn->prepare("SELECT COUNT(id) FROM eventos WHERE estado = 'Activo'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Devuelve el número total de participantes registrados
    public function getTotalParticipantes() {
        $stmt = $this->conn->prepare("SELECT COUNT(id) FROM participantes");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Devuelve la suma total de los precios de las entradas de los participantes registrados
    public function getTotalIngresos() {
        $query = "
            SELECT SUM(te.precio) 
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $total = $stmt->fetchColumn();
        return $total ?? 0; // Devuelve 0 si no hay ingresos
    }

    // Devuelve los datos del próximo evento
    public function getProximoEvento() {
        $query = "
            SELECT e.nombre, MIN(c.fecha) as proxima_fecha
            FROM eventos e
            JOIN calendarios c ON e.id = c.id_evento
            WHERE e.estado = 'Activo' AND c.fecha >= CURDATE()
            GROUP BY e.id, e.nombre
            ORDER BY proxima_fecha ASC
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Obtiene el total de participantes para cada evento activo.
     */
    public function getParticipantesPorEvento() {
        $query = "
            SELECT e.nombre AS nombre_evento, COUNT(p.id) AS total_participantes
            FROM eventos e
            LEFT JOIN calendarios c ON e.id = c.id_evento
            LEFT JOIN tipos_entrada te ON c.id = te.id_calendario
            LEFT JOIN participantes p ON te.id = p.id_tipo_entrada
            WHERE e.estado = 'Activo'
            GROUP BY e.id, e.nombre
            ORDER BY total_participantes DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>