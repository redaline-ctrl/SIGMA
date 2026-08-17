<?php

require_once __DIR__ . "/BaseModel.php";

class RelevoModel extends BaseModel
{
    public function listarSupervisores(): array
    {
        $sql = "
            SELECT id_supervisor, nombre_completo
            FROM supervisores
            WHERE estado = 1
            ORDER BY nombre_completo ASC
        ";

        return $this->consultar($sql);
    }

    public function listarOperadores(): array
    {
        $sql = "
            SELECT id_operador, nombre_completo
            FROM operadores
            WHERE estado = 1
            ORDER BY nombre_completo ASC
        ";

        return $this->consultar($sql);
    }

    public function listarMaquinas(): array
    {
        $sql = "
            SELECT id_maquina, nombre_maquina
            FROM maquinas
            WHERE estado = 1
            ORDER BY nombre_maquina ASC
        ";

        return $this->consultar($sql);
    }

    public function listarRelevos(): array
    {
        $sql = "
            SELECT
                r.id_relevo,
                r.fecha_operativa,
                r.turno,
                r.hora_inicio,
                r.hora_fin,
                r.horas_operativas,
                r.observaciones,
                s.nombre_completo AS supervisor,
                o.nombre_completo AS operador,
                m.nombre_maquina AS maquina
            FROM relevos r
            LEFT JOIN supervisores s
                ON s.id_supervisor = r.id_supervisor
            LEFT JOIN operadores o
                ON o.id_operador = r.id_operador
            LEFT JOIN maquinas m
                ON m.id_maquina = r.id_maquina
            ORDER BY r.fecha_operativa DESC, r.turno DESC
        ";

        return $this->consultar($sql);
    }

    public function obtener(int $id): ?array
    {
        return $this->consultarUno(
            "SELECT id_relevo, fecha_operativa, turno, id_supervisor, id_operador, id_maquina, hora_inicio, hora_fin, observaciones FROM relevos WHERE id_relevo = ?",
            [$id]
        );
    }

    public function guardar(array $datos): bool
    {
        $sql = "
            INSERT INTO relevos (
                fecha_operativa,
                turno,
                id_supervisor,
                id_operador,
                id_maquina,
                hora_inicio,
                hora_fin,
                horas_operativas,
                observaciones,
                fecha_registro
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ";

        $turno = (string) ($datos["turno"] ?? "1");
        $horaInicio = $datos["hora_inicio"] ?? "07:00:00";
        $horaFin = $datos["hora_fin"] ?? "15:00:00";
        $horasOperativas = $this->calcularHoras($horaInicio, $horaFin);

        $parametros = [
            $datos["fecha_operativa"],
            $turno,
            (int) $datos["id_supervisor"],
            (int) $datos["id_operador"],
            (int) $datos["id_maquina"],
            $horaInicio,
            $horaFin,
            $horasOperativas,
            trim((string) ($datos["observaciones"] ?? "")),
        ];

        return $this->ejecutar($sql, $parametros);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $horaInicio = $datos["hora_inicio"] ?? "07:00:00";
        $horaFin = $datos["hora_fin"] ?? "15:00:00";
        $sql = "
            UPDATE relevos
            SET fecha_operativa = ?, turno = ?, id_supervisor = ?, id_operador = ?,
                id_maquina = ?, hora_inicio = ?, hora_fin = ?, horas_operativas = ?, observaciones = ?
            WHERE id_relevo = ?
        ";

        return $this->ejecutar($sql, [
            $datos["fecha_operativa"], $datos["turno"], (int) $datos["id_supervisor"],
            (int) $datos["id_operador"], (int) $datos["id_maquina"], $horaInicio,
            $horaFin, $this->calcularHoras($horaInicio, $horaFin), trim((string) ($datos["observaciones"] ?? "")), $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        return $this->ejecutar("DELETE FROM relevos WHERE id_relevo = ?", [$id]);
    }

    public function existeDuplicado(array $datos): bool
    {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM relevos WHERE fecha_operativa = ? AND turno = ? AND id_supervisor = ? AND id_operador = ? AND id_maquina = ? AND hora_inicio = ? AND hora_fin = ?");
        $stmt->execute([
            $datos["fecha_operativa"], $datos["turno"], $datos["id_supervisor"],
            $datos["id_operador"], $datos["id_maquina"], $datos["hora_inicio"], $datos["hora_fin"],
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function calcularHoras(string $horaInicio, string $horaFin): float
    {
        try {
            $inicio = new DateTime($horaInicio);
            $fin = new DateTime($horaFin);

            if ($fin < $inicio) {
                $fin->modify("+1 day");
            }

            $diff = $inicio->diff($fin);

            $totalHoras = ($diff->h + ($diff->i / 60) + ($diff->s / 3600));

            return round((float) $totalHoras, 2);
        } catch (Exception $e) {
            return 0.0;
        }
    }
}
