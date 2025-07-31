<?php
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/TipoEntradaDAO.php';

class ParticipanteDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    /**
     * Verifica si un número de cédula ya está registrado para cualquier función de un evento específico.
     * @param string $cedula La cédula a verificar.
     * @param int $id_evento El ID del evento en el que se está registrando.
     * @return bool True si la cédula ya existe en ese evento, false en caso contrario.
     */
    public function cedulaYaRegistradaEnEvento($cedula, $id_evento) {
        $query = "
            SELECT COUNT(*) 
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE p.cedula = :cedula AND c.id_evento = :id_evento
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica si un número de transacción ya ha sido registrado en el sistema.
     * @param string $numero_transaccion El número de la transacción.
     * @return bool True si ya existe, false si no.
     */
    public function transaccionYaRegistrada($numero_transaccion) {
        $query = "SELECT COUNT(*) FROM participantes WHERE numero_transaccion = :numero_transaccion";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_transaccion', $numero_transaccion);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }


    /**
     * Crea un nuevo registro de participante y descuenta un cupo, todo dentro de una transacción.
     */
    public function crearParticipante($nombres, $apellidos, $cedula, $email, $telefono, $id_tipo_entrada, $numero_transaccion, $ruta_comprobante) {
        
        $tipoEntradaDAO = new TipoEntradaDAO(); // Necesitamos una instancia para llamar a la otra clase

        try {
            // Iniciamos la transacción
            $this->conn->beginTransaction();

            // 1. Intentamos decrementar el cupo
            $filas_afectadas = $tipoEntradaDAO->decrementarCupo($id_tipo_entrada);

            // Si rowCount() es 0, significa que no había cupos disponibles. Lanzamos una excepción.
            if ($filas_afectadas === 0) {
                throw new Exception("Lo sentimos, ya no quedan cupos disponibles para este tipo de entrada.");
            }

            // 2. Si el decremento fue exitoso, insertamos el nuevo participante
            $query = "INSERT INTO participantes (nombres, apellidos, cedula, email, telefono, id_tipo_entrada, numero_transaccion, ruta_comprobante) 
                      VALUES (:nombres, :apellidos, :cedula, :email, :telefono, :id_tipo_entrada, :numero_transaccion, :ruta_comprobante)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':id_tipo_entrada', $id_tipo_entrada, PDO::PARAM_INT);
            $stmt->bindParam(':numero_transaccion', $numero_transaccion);
            $stmt->bindParam(':ruta_comprobante', $ruta_comprobante);
            $stmt->execute();

            // 3. Si todo fue bien, confirmamos los cambios
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falla, revertimos todos los cambios
            $this->conn->rollBack();
            // Propagamos la excepción para que el controlador la maneje
            throw $e;
        }
    }
}
?>