<?php
require_once __DIR__ . '/../../config/Conexion.php';

class DashboardDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    /**
     * Obtiene los datos de las tarjetas de resumen (totales).
     * Funciona tanto para la vista general como para un evento específico.
     */
    public function getDatosGenerales($id_evento = null) {
        $response = [
            'total_eventos' => 0,
            'total_participantes' => 0,
            'total_asistentes' => 0,
            'total_ingresos' => 0.00
        ];

        // 1. Contar total de eventos (siempre es el total global)
        $stmt_eventos = $this->conn->prepare("SELECT COUNT(id) FROM eventos");
        $stmt_eventos->execute();
        $response['total_eventos'] = $stmt_eventos->fetchColumn();

        // 2. Consulta base para participantes, asistentes e ingresos
        $query_participantes_base = "
            SELECT 
                COUNT(p.id) as total_participantes,
                COUNT(CASE WHEN p.asistencia = 'Registrado' THEN 1 END) as total_asistentes,
                SUM(CASE WHEN p.asistencia = 'Registrado' THEN te.precio ELSE 0 END) as total_ingresos
            FROM participantes p
            JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
        ";
        
        // 3. Si se filtra por un evento, se añade un JOIN y un WHERE
        if ($id_evento) {
            // ✅ CORRECCIÓN: Se une con 'calendarios' para poder filtrar por 'id_evento'
            $query_participantes_base .= " JOIN calendarios c ON te.id_calendario = c.id WHERE c.id_evento = :id_evento";
            $stmt_participantes = $this->conn->prepare($query_participantes_base);
            $stmt_participantes->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        } else {
            // Si no hay filtro, se usa la consulta base
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
     * Obtiene los datos para el gráfico principal.
     * La consulta cambia dependiendo si se filtra por un evento o no.
     */
    public function getDatosGraficoPrincipal($id_evento = null) {
        $query = "";
        if ($id_evento) {
            // Consulta para un evento específico: Agrupa por tipo de entrada
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
            // Consulta general: Agrupa por evento
            // ✅ CORRECCIÓN: Se usan los JOINs correctos para vincular eventos con participantes
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

        // Formatear datos para que Chart.js los entienda
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
                ],
                [
                    'label' => 'Asistentes',
                    'data' => $asistentes,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.7)',
                ]
            ]
        ];
    }

    /**
     * Obtiene la lista de todos los eventos para el menú desplegable.
     */
    public function getListaEventos() {
        // ✅ CORRECCIÓN: Se ordena por 'id' descendente porque la columna 'fecha_registro' no existe en la tabla 'eventos'.
        // Esto mostrará los eventos creados más recientemente primero.
        $stmt = $this->conn->prepare("SELECT id, nombre FROM eventos ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>