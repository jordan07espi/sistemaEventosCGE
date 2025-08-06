<?php
require_once __DIR__ . '/../../config/Conexion.php';

class DashboardDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    /**
     * ✅ CORREGIDO: Obtiene los datos para las tarjetas. Ahora filtra correctamente por evento.
     */
    public function getDatosGenerales($id_evento = null) {
        $response = [
            'total_eventos' => 0,
            'total_participantes' => 0,
            'total_asistentes' => 0,
            'total_ingresos' => 0.00
        ];

        // 1. Contar total de eventos (esto siempre es global)
        $stmt_eventos = $this->conn->prepare("SELECT COUNT(id) FROM eventos");
        $stmt_eventos->execute();
        $response['total_eventos'] = $stmt_eventos->fetchColumn();

        // 2. Construir la consulta base para participantes, asistentes e ingresos
        $query_participantes_base = "
            SELECT 
                COUNT(p.id) as total_participantes,
                COUNT(CASE WHEN p.asistencia = 'Registrado' THEN 1 END) as total_asistentes,
                SUM(CASE WHEN p.asistencia = 'Registrado' THEN te.precio ELSE 0 END) as total_ingresos
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
        ";
        
        // 3. Añadir el filtro si se proporciona un id_evento
        if ($id_evento) {
            // Unimos la tabla calendarios para poder filtrar por el evento
            $query_participantes_base .= " JOIN calendarios c ON te.id_calendario = c.id WHERE c.id_evento = :id_evento";
            $stmt_participantes = $this->conn->prepare($query_participantes_base);
            $stmt_participantes->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        } else {
            $stmt_participantes = $this->conn->prepare($query_participantes_base);
        }

        $stmt_participantes->execute();
        $datos = $stmt_participantes->fetch(PDO::FETCH_ASSOC);

        if ($datos) {
            $response['total_participantes'] = $datos['total_participantes'] ?? 0;
            $response['total_asistentes'] = $datos['total_asistentes'] ?? 0;
            $response['total_ingresos'] = $datos['total_ingresos'] ?? 0.00;
        }

        return $response;
    }

    /**
     * ✅ CORREGIDO: Obtiene los datos para el gráfico principal. Ahora agrupa y filtra correctamente.
     */
    public function getDatosGraficoPrincipal($id_evento = null) {
        $query = "";
        if ($id_evento) {
            // Consulta para un evento específico: agrupa por tipo de entrada (tickets)
            $query = "
                SELECT 
                    te.nombre as grupo,
                    COUNT(p.id) as registrados,
                    COUNT(CASE WHEN p.asistencia = 'Registrado' THEN 1 END) as asistentes
                FROM participantes p
                JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
                JOIN calendarios c ON te.id_calendario = c.id
                WHERE c.id_evento = :id_evento
                GROUP BY te.id, te.nombre
                ORDER BY registrados DESC
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        } else {
            // Consulta general: agrupa por evento
            $query = "
                SELECT 
                    ev.nombre as grupo,
                    COUNT(p.id) as registrados,
                    COUNT(CASE WHEN p.asistencia = 'Registrado' THEN 1 END) as asistentes
                FROM eventos ev
                LEFT JOIN calendarios c ON ev.id = c.id_evento
                LEFT JOIN tipos_entrada te ON c.id = te.id_calendario
                LEFT JOIN participantes p ON te.id = p.id_tipo_entrada
                GROUP BY ev.id, ev.nombre
                ORDER BY registrados DESC
            ";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear datos para Chart.js
        $labels = array_column($resultados, 'grupo');
        $registrados = array_column($resultados, 'registrados');
        $asistentes = array_column($resultados, 'asistentes');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Registrados',
                    'data' => $registrados,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Asistentes',
                    'data' => $asistentes,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.7)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Obtiene una lista de todos los eventos para poblar el dropdown.
     */
    public function getListaEventos() {
        $stmt = $this->conn->prepare("SELECT id, nombre FROM eventos WHERE activo = 1 ORDER BY fecha_inicio DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>