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
     * Verifica si una combinación de número de transacción Y banco ya ha sido registrada.
     * @param string $numero_transaccion El número de la transacción.
     * @param string $banco El nombre del banco.
     * @return bool True si la combinación ya existe, false si no.
     */
    public function transaccionYaRegistrada($numero_transaccion, $banco) {
        // La consulta ahora busca una fila donde AMBOS, el número y el banco, coincidan.
        $query = "SELECT COUNT(*) FROM participantes 
                  WHERE numero_transaccion = :numero_transaccion AND banco = :banco";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_transaccion', $numero_transaccion);
        $stmt->bindParam(':banco', $banco);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }


    /**
     * Crea un nuevo registro de participante y descuenta un cupo, todo dentro de una transacción.
     */
    public function crearParticipante($nombres, $apellidos, $cedula, $email, $telefono, $id_tipo_entrada, $numero_transaccion, $banco, $ruta_comprobante) {
        
        // La conexión ya está disponible en $this->conn, no necesitamos una nueva instancia de DAO aquí.
        // El decremento de cupo se manejará directamente.
        $tipoEntradaDAO = new TipoEntradaDAO();

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
            $query = "INSERT INTO participantes (nombres, apellidos, cedula, email, telefono, id_tipo_entrada, numero_transaccion, banco, ruta_comprobante) 
                      VALUES (:nombres, :apellidos, :cedula, :email, :telefono, :id_tipo_entrada, :numero_transaccion, :banco, :ruta_comprobante)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':id_tipo_entrada', $id_tipo_entrada, PDO::PARAM_INT);
            $stmt->bindParam(':numero_transaccion', $numero_transaccion);
            $stmt->bindParam(':banco', $banco); // <-- ¡PARÁMETRO AÑADIDO!
            $stmt->bindParam(':ruta_comprobante', $ruta_comprobante);
            $stmt->execute();
            $ultimoId = $this->conn->lastInsertId(); // Obtenemos el ID del registro recién insertado
            $this->conn->commit();
            return $ultimoId; // Devolvemos el ID
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }


    public function getUltimoParticipanteRegistradoPorCedula($cedula) {
        $query = "SELECT * FROM participantes WHERE cedula = :cedula ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getParticipantePorId($id) {
        $query = "SELECT * FROM participantes WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ¡FUNCIÓN ACTUALIZADA!
     * Obtiene una lista paginada y filtrada de participantes de un evento.
     */
    public function getParticipantesPorEventoId($id_evento, $busqueda = '', $pagina = 1, $registros_por_pagina = 25) {
        $offset = ($pagina - 1) * $registros_por_pagina;
        $busqueda_like = '%' . $busqueda . '%';
        
        $query = "
            SELECT p.nombres, p.apellidos, p.cedula, p.email, p.ruta_comprobante, te.nombre AS nombre_entrada
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE c.id_evento = :id_evento AND 
                  (p.nombres LIKE :busqueda OR p.apellidos LIKE :busqueda OR p.cedula LIKE :busqueda)
            ORDER BY p.apellidos, p.nombres
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->bindParam(':busqueda', $busqueda_like);
        $stmt->bindParam(':limit', $registros_por_pagina, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ¡NUEVA FUNCIÓN!
     * Cuenta el total de participantes de un evento para la paginación.
     */
    public function contarParticipantesPorEventoId($id_evento, $busqueda = '') {
        $busqueda_like = '%' . $busqueda . '%';
        $query = "
            SELECT COUNT(p.id)
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE c.id_evento = :id_evento AND
                  (p.nombres LIKE :busqueda OR p.apellidos LIKE :busqueda OR p.cedula LIKE :busqueda)
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->bindParam(':busqueda', $busqueda_like);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * ¡FUNCIÓN ACTUALIZADA!
     * Obtiene una lista paginada de participantes para la tabla de check-in.
     */
    public function getParticipantesParaCheckin($id_evento, $busqueda = '', $pagina = 1, $limite = 25) {
        $offset = ($pagina - 1) * $limite;
        $busqueda_like = '%' . $busqueda . '%';
        $query = "
            SELECT p.id, p.nombres, p.apellidos, p.cedula, p.asistencia
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE c.id_evento = :id_evento AND 
                  (p.nombres LIKE :busqueda OR p.apellidos LIKE :busqueda OR p.cedula LIKE :busqueda)
            ORDER BY p.apellidos, p.nombres
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->bindParam(':busqueda', $busqueda_like);
        $stmt->bindParam(':limit', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un participante por su cédula y el ID del evento al que se registró.
     */
    public function getParticipantePorCedulaYEvento($cedula, $id_evento) {
        $query = "
            SELECT p.id, p.nombres, p.apellidos, p.asistencia
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE p.cedula = :cedula AND c.id_evento = :id_evento
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de asistencia de un participante a 'Registrado'.
     */
    public function registrarAsistencia($id_participante) {
        $query = "UPDATE participantes SET asistencia = 'Registrado' WHERE id = :id_participante";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_participante', $id_participante, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Cuenta el total de participantes para la paginación del check-in.
     */
    public function contarParticipantesParaCheckin($id_evento, $busqueda = '') {
        $busqueda_like = '%' . $busqueda . '%';
        $query = "
            SELECT COUNT(p.id)
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE c.id_evento = :id_evento AND
                  (p.nombres LIKE :busqueda OR p.apellidos LIKE :busqueda OR p.cedula LIKE :busqueda)
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->bindParam(':busqueda', $busqueda_like);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

     /**
     * Obtiene todos los datos de los participantes de un evento para un reporte.
     */
    public function getParticipantesParaReporte($id_evento) {
        $query = "
            SELECT 
                p.nombres, p.apellidos, p.cedula, p.email, p.telefono, 
                p.numero_transaccion, p.banco, p.asistencia, p.fecha_registro,
                te.nombre AS nombre_entrada
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
            JOIN calendarios c ON te.id_calendario = c.id
            WHERE c.id_evento = :id_evento
            ORDER BY p.apellidos, p.nombres
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>