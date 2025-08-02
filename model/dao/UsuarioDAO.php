<?php
require_once __DIR__ . '/../../config/Conexion.php';

class UsuarioDAO {
    private $conn;
    public function __construct() { $this->conn = (new Conexion())->getConnection(); }

    public function getUsuarioPorEmail($email) {
        $query = "SELECT u.*, r.nombre as nombre_rol FROM usuarios u JOIN roles_usuario r ON u.id_rol = r.id WHERE u.email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Aquí añadiremos más funciones para el CRUD de usuarios después
}
?>