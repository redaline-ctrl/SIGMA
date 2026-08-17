<?php

require_once __DIR__ . "/BaseModel.php";

class ReporteModel extends BaseModel
{
    public function __construct(PDO $conexion)
    {
        parent::__construct($conexion);
        $this->conexion->exec("CREATE TABLE IF NOT EXISTS reportes_historial (
            id_historial INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT UNSIGNED NULL,
            usuario VARCHAR(100) NOT NULL,
            rol VARCHAR(50) NOT NULL,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE NOT NULL,
            periodo VARCHAR(20) NOT NULL,
            fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_reportes_historial_fecha (fecha_generacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function registrarHistorial(array $datos): bool
    {
        return $this->ejecutar(
            "INSERT INTO reportes_historial (id_usuario, usuario, rol, fecha_inicio, fecha_fin, periodo) VALUES (?, ?, ?, ?, ?, ?)",
            [$datos["id_usuario"] ?? null, $datos["usuario"] ?? "-", $datos["rol"] ?? "-", $datos["fecha_inicio"], $datos["fecha_fin"], $datos["periodo"]]
        );
    }

    public function historial(): array
    {
        return $this->consultar("SELECT id_historial, usuario, rol, fecha_inicio, fecha_fin, periodo, fecha_generacion FROM reportes_historial ORDER BY fecha_generacion DESC LIMIT 100");
    }

    public function resumenDiario(string $fecha): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) = ?) AS total_eventos,
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) = ? AND turno = '1') AS turno_1,
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) = ? AND turno = '2') AS turno_2,
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) = ? AND turno = '3') AS turno_3,
                (SELECT COUNT(*) FROM eventos e
                 INNER JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
                 WHERE DATE(e.fecha_evento) = ?
                 AND et.nombre_etiqueta IN ('Fatiga crítica', 'Uso del teléfono confirmado')) AS criticos,
                (SELECT COUNT(*) FROM relevos WHERE DATE(fecha_operativa) = ?) AS relevos,
                (SELECT ROUND(COALESCE(SUM(horas_operativas), 0), 2) FROM relevos WHERE DATE(fecha_operativa) = ?) AS horas_operativas
        ";

        $params = [$fecha, $fecha, $fecha, $fecha, $fecha, $fecha, $fecha];

        return $this->consultarUno($sql, $params) ?? [
            "total_eventos" => 0,
            "turno_1" => 0,
            "turno_2" => 0,
            "turno_3" => 0,
            "criticos" => 0,
            "relevos" => 0,
            "horas_operativas" => 0,
        ];
    }

    public function resumenPeriodo(string $inicio, string $fin): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) BETWEEN ? AND ?) AS total_eventos,
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) BETWEEN ? AND ? AND turno = '1') AS turno_1,
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) BETWEEN ? AND ? AND turno = '2') AS turno_2,
                (SELECT COUNT(*) FROM eventos WHERE DATE(fecha_evento) BETWEEN ? AND ? AND turno = '3') AS turno_3,
                (SELECT COUNT(*) FROM eventos e
                 INNER JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
                 WHERE DATE(e.fecha_evento) BETWEEN ? AND ?
                 AND et.nombre_etiqueta IN ('Fatiga crítica', 'Uso del teléfono confirmado')) AS criticos,
                (SELECT COUNT(*) FROM relevos WHERE DATE(fecha_operativa) BETWEEN ? AND ?) AS relevos,
                (SELECT ROUND(COALESCE(SUM(horas_operativas), 0), 2) FROM relevos WHERE DATE(fecha_operativa) BETWEEN ? AND ?) AS horas_operativas
        ";

        $parametros = [];
        for ($i = 0; $i < 7; $i++) {
            $parametros[] = $inicio;
            $parametros[] = $fin;
        }

        $resultado = $this->consultarUno($sql, $parametros);

        return $resultado ?? [
            "total_eventos" => 0,
            "turno_1" => 0,
            "turno_2" => 0,
            "turno_3" => 0,
            "criticos" => 0,
            "relevos" => 0,
            "horas_operativas" => 0,
        ];
    }

    public function resumenSemanal(string $fecha): array
    {
        $start = new DateTime($fecha);
        $start->modify("monday this week");
        $end = clone $start;
        $end->modify("+6 days");

        return $this->resumenPeriodo($start->format("Y-m-d"), $end->format("Y-m-d"));
    }

    public function resumenMensual(string $fecha): array
    {
        $start = new DateTime($fecha);
        $inicio = $start->format("Y-m-01");
        $fin = $start->format("Y-m-t");

        return $this->resumenPeriodo($inicio, $fin);
    }

    public function topOperadores(string $fecha): array
    {
        $sql = "
            SELECT
                o.nombre_completo,
                COUNT(e.id_evento) AS total
            FROM operadores o
            LEFT JOIN eventos e ON e.id_operador = o.id_operador AND DATE(e.fecha_evento) = ?
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC
            LIMIT 5
        ";

        return $this->consultar($sql, [$fecha]);
    }

    public function topOperadoresPeriodo(string $inicio, string $fin): array
    {
        $sql = "
            SELECT
                o.nombre_completo,
                COUNT(e.id_evento) AS total
            FROM operadores o
            LEFT JOIN eventos e ON e.id_operador = o.id_operador AND DATE(e.fecha_evento) BETWEEN ? AND ?
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC
            LIMIT 5
        ";

        return $this->consultar($sql, [$inicio, $fin]);
    }

    public function topMaquinas(string $fecha): array
    {
        $sql = "
            SELECT
                m.nombre_maquina,
                COUNT(e.id_evento) AS total
            FROM maquinas m
            LEFT JOIN eventos e ON e.id_maquina = m.id_maquina AND DATE(e.fecha_evento) = ?
            GROUP BY m.id_maquina, m.nombre_maquina
            ORDER BY total DESC, m.nombre_maquina ASC
            LIMIT 5
        ";

        return $this->consultar($sql, [$fecha]);
    }

    public function topMaquinasPeriodo(string $inicio, string $fin): array
    {
        $sql = "
            SELECT
                m.nombre_maquina,
                COUNT(e.id_evento) AS total
            FROM maquinas m
            LEFT JOIN eventos e ON e.id_maquina = m.id_maquina AND DATE(e.fecha_evento) BETWEEN ? AND ?
            GROUP BY m.id_maquina, m.nombre_maquina
            ORDER BY total DESC, m.nombre_maquina ASC
            LIMIT 5
        ";

        return $this->consultar($sql, [$inicio, $fin]);
    }

    public function eventosRecientesPeriodo(string $inicio, string $fin, int $limit = 12): array
    {
        $sql = "
            SELECT
                e.id_evento,
                e.fecha_evento,
                e.hora_evento,
                e.turno,
                o.nombre_completo AS operador,
                m.nombre_maquina AS maquina,
                te.nombre_evento AS tipo_evento,
                e.estado
            FROM eventos e
            LEFT JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN maquinas m ON m.id_maquina = e.id_maquina
            LEFT JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            WHERE DATE(e.fecha_evento) BETWEEN ? AND ?
            ORDER BY e.fecha_evento DESC, e.hora_evento DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$inicio, $fin]);
        $result = $stmt->fetchAll();

        return array_slice($result, 0, $limit);
    }

    public function relevosPeriodo(string $inicio, string $fin): array
    {
        $sql = "
            SELECT
                r.fecha_operativa,
                r.turno,
                r.hora_inicio,
                r.hora_fin,
                r.horas_operativas,
                o.nombre_completo AS operador,
                m.nombre_maquina AS maquina,
                s.nombre_completo AS supervisor
            FROM relevos r
            LEFT JOIN operadores o ON o.id_operador = r.id_operador
            LEFT JOIN maquinas m ON m.id_maquina = r.id_maquina
            LEFT JOIN supervisores s ON s.id_supervisor = r.id_supervisor
            WHERE DATE(r.fecha_operativa) BETWEEN ? AND ?
            ORDER BY r.fecha_operativa ASC, r.hora_inicio ASC
        ";

        return $this->consultar($sql, [$inicio, $fin]);
    }

    public function eventosRecientes(int $limit = 10): array
    {
        $limit = max(1, min($limit, 100));

        $sql = "
            SELECT
                e.id_evento,
                e.fecha_evento,
                e.hora_evento,
                e.turno,
                o.nombre_completo AS operador,
                m.nombre_maquina AS maquina,
                te.nombre_evento AS tipo_evento,
                et.nombre_etiqueta AS etiqueta,
                e.estado
            FROM eventos e
            LEFT JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN maquinas m ON m.id_maquina = e.id_maquina
            LEFT JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            ORDER BY e.fecha_evento DESC, e.hora_evento DESC
            LIMIT {$limit}
        ";

        return $this->consultar($sql);
    }
}
