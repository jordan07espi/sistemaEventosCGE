<?php
require_once __DIR__ . '/../../config/Conexion.php';

class UsuarioDAO {
    private $conn;
    public function __construct() { $this->conn = (new Conexion())->getConnection(); }

    public function getUsuarioPorCedula($cedula) {
        // --- CAMBIO AQUÍ: Eliminamos 'email' de la consulta ---
        $query = "SELECT u.id, u.contrasena, u.id_rol, u.nombres, u.apellidos, u.cedula, r.nombre as nombre_rol 
                  FROM usuarios u 
                  JOIN roles_usuario r ON u.id_rol = r.id 
                  WHERE u.cedula = :cedula";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsuarios() {
        // --- CAMBIO AQUÍ: Eliminamos 'email' de la consulta ---
        $query = "SELECT u.id, u.nombres, u.apellidos, u.cedula, r.nombre as nombre_rol 
                  FROM usuarios u 
                  JOIN roles_usuario r ON u.id_rol = r.id 
                  ORDER BY u.apellidos, u.nombres";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsuarioPorId($id) {
        $query = "SELECT id, nombres, apellidos, cedula, id_rol FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cedulaYaExiste($cedula, $id_actual = 0) {
        $query = "SELECT COUNT(*) FROM usuarios WHERE cedula = :cedula AND id != :id_actual";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':id_actual', $id_actual, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function crearUsuario($nombres, $apellidos, $cedula, $id_rol, $contrasena) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $query = "INSERT INTO usuarios (nombres, apellidos, cedula, id_rol, contrasena) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombres, $apellidos, $cedula, $id_rol, $hash]);
    }

    public function actualizarUsuario($id, $nombres, $apellidos, $cedula, $id_rol) {
        $query = "UPDATE usuarios SET nombres = ?, apellidos = ?, cedula = ?, id_rol = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombres, $apellidos, $cedula, $id_rol, $id]);
    }

    public function actualizarContrasena($id, $nueva_contrasena) {
        $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $query = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$hash, $id]);
    }

    public function eliminarUsuario($id) {
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
    
    public function getRoles() {
        $stmt = $this->conn->prepare("SELECT * FROM roles_usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>