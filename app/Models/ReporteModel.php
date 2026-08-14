<?php

require_once __DIR__ . "/BaseModel.php";

class ReporteModel extends BaseModel
{
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
                COUNT(*) AS total_eventos,
                SUM(CASE WHEN turno = '1' THEN 1 ELSE 0 END) AS turno_1,
                SUM(CASE WHEN turno = '2' THEN 1 ELSE 0 END) AS turno_2,
                SUM(CASE WHEN turno = '3' THEN 1 ELSE 0 END) AS turno_3,
                SUM(CASE WHEN e.id_etiqueta IS NOT NULL AND et.nombre_etiqueta IN ('Fatiga crítica', 'Uso del teléfono confirmado') THEN 1 ELSE 0 END) AS criticos,
                COUNT(DISTINCT r.id_relevo) AS relevos,
                ROUND(COALESCE(SUM(r.horas_operativas), 0), 2) AS horas_operativas
            FROM eventos e
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            LEFT JOIN relevos r ON DATE(r.fecha_operativa) BETWEEN ? AND ?
            WHERE DATE(e.fecha_evento) BETWEEN ? AND ?
        ";

        $resultado = $this->consultarUno($sql, [$inicio, $fin, $inicio, $fin]);

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

    public function eventosRecientes(int $limit = 10): array
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
                et.nombre_etiqueta AS etiqueta,
                e.estado
            FROM eventos e
            LEFT JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN maquinas m ON m.id_maquina = e.id_maquina
            LEFT JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            ORDER BY e.fecha_evento DESC, e.hora_evento DESC
            LIMIT ?
        ";

        return $this->consultar($sql, [$limit]);
    }
}
