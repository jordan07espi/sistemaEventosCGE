<?php
// model/dao/EventoDAO.php

require_once __DIR__ . '/../../config/Conexion.php';

class EventoDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    /**
     * Obtiene todos los eventos, uniéndolos con el nombre de su categoría.
     */
    public function getEventos() {
        $query = "
            SELECT 
                e.id, 
                e.nombre, 
                e.descripcion, 
                e.enlace_imagen, 
                e.estado,
                c.nombre AS nombre_categoria
            FROM eventos e
            JOIN categorias_evento c ON e.id_categoria = c.id
            ORDER BY e.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un solo evento por su ID.
     */
    public function getEventoPorId($id) {
        $query = "SELECT id, nombre, descripcion, enlace_imagen, id_categoria, estado 
                  FROM eventos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo evento en la base de datos.
     */
    public function crearEvento($nombre, $descripcion, $id_categoria, $enlace_imagen) {
        $query = "INSERT INTO eventos (nombre, descripcion, id_categoria, enlace_imagen) 
                  VALUES (:nombre, :descripcion, :id_categoria, :enlace_imagen)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':enlace_imagen', $enlace_imagen);
        
        return $stmt->execute();
    }

    /**
     * Actualiza un evento existente en la base de datos.
     */
    public function actualizarEvento($id, $nombre, $descripcion, $id_categoria, $enlace_imagen) {
        $query = "UPDATE eventos SET 
                    nombre = :nombre, 
                    descripcion = :descripcion, 
                    id_categoria = :id_categoria, 
                    enlace_imagen = :enlace_imagen 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':enlace_imagen', $enlace_imagen);

        return $stmt->execute();
    }

    /**
     * ¡NUEVA FUNCIÓN!
     * Cambia el estado de un evento (Activo, Finalizado, Cancelado).
     */
    public function cambiarEstadoEvento($id_evento, $nuevo_estado) {
        $query = "UPDATE eventos SET estado = :nuevo_estado WHERE id = :id_evento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nuevo_estado', $nuevo_estado);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>