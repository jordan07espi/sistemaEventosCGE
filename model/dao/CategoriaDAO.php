<?php
// model/dao/CategoriaDAO.php

// Incluimos el archivo de conexión una sola vez
require_once __DIR__ . '/../../config/Conexion.php';

class CategoriaDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getCategoriaPorId($id) {
        $query = "SELECT id, nombre, activa FROM categorias_evento WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todas las categorías
    public function getCategorias() {
        $query = "SELECT id, nombre, activa FROM categorias_evento ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear una nueva categoría
    public function crearCategoria($nombre) {
        $query = "INSERT INTO categorias_evento (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        return $stmt->execute();
    }

    // Actualizar una categoría existente
    public function actualizarCategoria($id, $nombre) {
        $query = "UPDATE categorias_evento SET nombre = :nombre WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Cambiar el estado de una categoría (Borrado Lógico)
    public function cambiarEstadoCategoria($id, $estado) {
        $query = "UPDATE categorias_evento SET activa = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>