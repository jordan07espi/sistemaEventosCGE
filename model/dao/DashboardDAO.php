<?php
require_once __DIR__ . '/../../config/Conexion.php';

class DashboardDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    /**
     * Obtiene los datos globales para las tarjetas principales.
     * Si se pasa un id_evento, calcula los datos solo para ese evento.
     */
    public function getDatosGenerales($id_evento = null) {
        $response = [
            'total_eventos' => 0,
            'total_participantes' => 0,
            'total_asistentes' => 0,
            'total_ingresos' => 0.00
        ];

        // Contar total de eventos (siempre es global)
        $stmt_eventos = $this->conn->prepare("SELECT COUNT(id) FROM eventos");
        $stmt_eventos->execute();
        $response['total_eventos'] = $stmt_eventos->fetchColumn();

        // Construir queries con filtro opcional
        $filtro_sql = ($id_evento) ? "WHERE p.id_evento = :id_evento" : "";
        
        $query_participantes = "SELECT 
                                    COUNT(p.id) as total_participantes,
                                    COUNT(CASE WHEN p.asistencia = 'Registrado' THEN 1 ELSE NULL END) as total_asistentes,
                                    SUM(te.precio) as total_ingresos
                                FROM participantes p
                                JOIN tipos_entrada te ON p.id_tipo_entrada = te.id
                                $filtro_sql";

        $stmt_participantes = $this->conn->prepare($query_participantes);
        if ($id_evento) {
            $stmt_participantes->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        }
        $stmt_participantes->execute();
        $datos = $stmt_participantes->fetch(PDO::FETCH_ASSOC);

        if ($datos) {
            $response['total_participantes'] = (int) $datos['total_participantes'];
            $response['total_asistentes'] = (int) $datos['total_asistentes'];
            $response['total_ingresos'] = number_format((float) $datos['total_ingresos'], 2, '.', '');
        }
        
        return $response;
    }

    /**
     * Obtiene los datos para el gráfico de barras.
     * Si no se pasa id_evento, obtiene los datos de todos los eventos.
     * Si se pasa id_evento, obtiene la distribución por carrera/curso de ese evento.
     */
    public function getDatosGraficoPrincipal($id_evento = null) {
        if ($id_evento) {
            // Vista detallada: distribución por carrera/curso para un evento específico
            $query = "SELECT 
                        COALESCE(NULLIF(carrera, ''), NULLIF(curso, ''), 'No especificado') as grupo,
                        COUNT(id) as registrados,
                        COUNT(CASE WHEN asistencia = 'Registrado' THEN 1 ELSE NULL END) as asistentes
                      FROM participantes
                      WHERE id_evento = :id_evento
                      GROUP BY grupo
                      ORDER BY registrados DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
        } else {
            // Vista general: comparación entre eventos
            $query = "SELECT 
                        ev.nombre as grupo,
                        COUNT(p.id) as registrados,
                        COUNT(CASE WHEN p.asistencia = 'Registrado' THEN 1 ELSE NULL END) as asistentes
                      FROM eventos ev
                      LEFT JOIN participantes p ON ev.id = p.id_evento
                      GROUP BY ev.id, ev.nombre
                      ORDER BY registrados DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
     * Obtiene una lista de todos los eventos para poblar el dropdown del dashboard.
     */
    public function getListaEventos() {
        $stmt = $this->conn->prepare("SELECT id, nombre FROM eventos ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>