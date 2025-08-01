<?php
require_once __DIR__ . '/../../config/Conexion.php';

class TipoEntradaDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getTiposEntradaPorCalendarioId($calendario_id) {
        $query = "SELECT id, nombre, precio, cantidad_total, cantidad_disponible, detalle 
                  FROM tipos_entrada WHERE id_calendario = :calendario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':calendario_id', $calendario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTipoEntradaPorId($id) {
        $query = "SELECT * FROM tipos_entrada WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function crearTipoEntrada($id_calendario, $nombre, $precio, $cantidad, $detalle) {
        $query = "INSERT INTO tipos_entrada (id_calendario, nombre, precio, cantidad_total, cantidad_disponible, detalle) 
                  VALUES (:id_calendario, :nombre, :precio, :cantidad, :cantidad, :detalle)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_calendario', $id_calendario);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':detalle', $detalle); 
        return $stmt->execute();
    }

    public function decrementarCupo($id_tipo_entrada) {
        $query = "UPDATE tipos_entrada 
                  SET cantidad_disponible = cantidad_disponible - 1 
                  WHERE id = :id_tipo_entrada AND cantidad_disponible > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_tipo_entrada', $id_tipo_entrada, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * ¡FUNCIÓN CORREGIDA!
     * Obtiene todos los detalles necesarios para generar el boleto en PDF.
     */
    public function getDetallesEntradaParaPDF($id_tipo_entrada) {
        $query = "
            SELECT 
                te.nombre AS nombre_entrada,
                te.precio,
                te.detalle AS detalle_entrada,
                c.fecha,
                c.hora,
                l.nombre_establecimiento AS nombre_lugar,
                e.nombre AS nombre_evento,
                e.id AS id_evento /* ¡CAMBIO CLAVE AQUÍ! */
            FROM tipos_entrada te
            JOIN calendarios c ON te.id_calendario = c.id
            JOIN lugares l ON c.id_lugar = l.id
            JOIN eventos e ON c.id_evento = e.id
            WHERE te.id = :id_tipo_entrada
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_tipo_entrada', $id_tipo_entrada, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>