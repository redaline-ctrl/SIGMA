<?php

require_once __DIR__ . "/BaseModel.php";
require_once __DIR__ . "/../../config/app.php";

class EventModel extends BaseModel
{
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

    public function listarTiposEvento(): array
    {
        $sql = "
            SELECT id_tipo_evento, nombre_evento, nivel_riesgo
            FROM tipos_eventos
            WHERE estado = 1
            ORDER BY nombre_evento ASC
        ";

        return $this->consultar($sql);
    }

    public function listarEtiquetas(): array
    {
        $sql = "
            SELECT id_etiqueta, nombre_etiqueta
            FROM etiquetas
            WHERE estado = 1
            ORDER BY nombre_etiqueta ASC
        ";

        return $this->consultar($sql);
    }

    public function listarClasificaciones(): array
    {
        $sql = "
            SELECT id_clasificacion, nombre_clasificacion, descripcion
            FROM clasificaciones
            WHERE estado = 1
            ORDER BY nombre_clasificacion ASC
        ";

        return $this->consultar($sql);
    }

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

    public function listarEventos(array $filtros = []): array
    {
        $sql = "
            SELECT
                e.id_evento,
                e.fecha_evento,
                e.hora_evento,
                e.turno,
                e.fecha_operativa,
                e.estado,
                e.autorizado,
                e.observaciones,
                o.nombre_completo AS operador,
                m.nombre_maquina AS maquina,
                te.nombre_evento AS tipo_evento,
                et.nombre_etiqueta AS etiqueta,
                c.nombre_clasificacion AS clasificacion,
                COALESCE(s.nombre_completo, '-') AS supervisor
            FROM eventos e
            LEFT JOIN operadores o
                ON o.id_operador = e.id_operador
            LEFT JOIN maquinas m
                ON m.id_maquina = e.id_maquina
            LEFT JOIN tipos_eventos te
                ON te.id_tipo_evento = e.id_tipo_evento
            LEFT JOIN etiquetas et
                ON et.id_etiqueta = e.id_etiqueta
            LEFT JOIN clasificaciones c
                ON c.id_clasificacion = e.id_clasificacion
            LEFT JOIN relevos r
                ON r.id_operador = e.id_operador
                AND r.turno = e.turno
                AND DATE(r.fecha_operativa) = DATE(e.fecha_operativa)
                AND r.id_relevo = (
                    SELECT MAX(r2.id_relevo)
                    FROM relevos r2
                    WHERE r2.id_operador = e.id_operador
                    AND r2.turno = e.turno
                    AND DATE(r2.fecha_operativa) = DATE(e.fecha_operativa)
                )
            LEFT JOIN supervisores s
                ON s.id_supervisor = r.id_supervisor
            WHERE 1 = 1
        ";

        $parametros = [];

        if (!empty($filtros["turno"])) {
            $sql .= " AND e.turno = ?";
            $parametros[] = (string) $filtros["turno"];
        }

        if (!empty($filtros["supervisor"])) {
            $sql .= " AND r.id_supervisor = ?";
            $parametros[] = (int) $filtros["supervisor"];
        }

        if (!empty($filtros["fecha_inicio"])) {
            $sql .= " AND DATE(e.fecha_evento) >= ?";
            $parametros[] = $filtros["fecha_inicio"];
        }

        if (!empty($filtros["fecha_fin"])) {
            $sql .= " AND DATE(e.fecha_evento) <= ?";
            $parametros[] = $filtros["fecha_fin"];
        }

        $sql .= " ORDER BY e.fecha_evento DESC, e.hora_evento DESC";

        return $this->consultar($sql, $parametros);
    }

    public function obtenerEvento(int $id): ?array
    {
        $sql = "
            SELECT
                e.id_evento,
                e.fecha_evento,
                e.hora_evento,
                e.turno,
                e.fecha_operativa,
                e.estado,
                e.autorizado,
                e.observaciones,
                o.nombre_completo AS operador,
                o.id_operador,
                m.nombre_maquina AS maquina,
                m.id_maquina,
                te.nombre_evento AS tipo_evento,
                te.id_tipo_evento,
                et.nombre_etiqueta AS etiqueta,
                et.id_etiqueta,
                c.nombre_clasificacion AS clasificacion,
                c.id_clasificacion,
                s.nombre_completo AS supervisor
            FROM eventos e
            LEFT JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN maquinas m ON m.id_maquina = e.id_maquina
            LEFT JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            LEFT JOIN relevos r
                ON r.id_operador = e.id_operador
                AND r.turno = e.turno
                AND DATE(r.fecha_operativa) = DATE(e.fecha_operativa)
                AND r.id_relevo = (
                    SELECT MAX(r2.id_relevo)
                    FROM relevos r2
                    WHERE r2.id_operador = e.id_operador
                    AND r2.turno = e.turno
                    AND DATE(r2.fecha_operativa) = DATE(e.fecha_operativa)
                )
            LEFT JOIN supervisores s ON s.id_supervisor = r.id_supervisor
            WHERE e.id_evento = ?
            LIMIT 1
        ";

        $resultado = $this->consultarUno($sql, [$id]);

        return $resultado !== null ? $resultado : null;
    }

    public function guardar(array $datos): bool
    {
        $sql = "
            INSERT INTO eventos (
                fecha_evento,
                hora_evento,
                turno,
                fecha_operativa,
                id_operador,
                id_maquina,
                id_tipo_evento,
                id_etiqueta,
                id_clasificacion,
                etiqueta,
                estado,
                autorizado,
                observaciones,
                fecha_registro
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ";

        $etiquetaNombre = "";

        if (!empty($datos["id_etiqueta"])) {
            $etiqueta = $this->consultarUno(
                "SELECT nombre_etiqueta FROM etiquetas WHERE id_etiqueta = ?",
                [(int) $datos["id_etiqueta"]]
            );

            $etiquetaNombre = $etiqueta["nombre_etiqueta"] ?? "";
        }

        $observaciones = trim((string) ($datos["observaciones"] ?? ""));

        if (!empty($datos["evidencia_ruta"])) {
            $observaciones =
                ($observaciones !== ""
                    ? $observaciones . "\n"
                    : "")
                . "Evidencia: " . $datos["evidencia_ruta"];
        }

        $parametros = [
            $datos["fecha_evento"],
            $datos["hora_evento"],
            $datos["turno"],
            $datos["fecha_operativa"],
            (int) $datos["id_operador"],
            (int) $datos["id_maquina"],
            (int) $datos["id_tipo_evento"],
            !empty($datos["id_etiqueta"]) ? (int) $datos["id_etiqueta"] : null,
            !empty($datos["id_clasificacion"]) ? (int) $datos["id_clasificacion"] : null,
            $etiquetaNombre,
            $datos["estado"] ?? "Pendiente",
            isset($datos["autorizado"]) ? (int) $datos["autorizado"] : 0,
            $observaciones,
        ];

        return $this->ejecutar($sql, $parametros);
    }

    public function guardarEvidencia(array $archivo): ?string
    {
        $tiposPermitidos = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp",
            "application/pdf" => "pdf",
        ];

        if (
            !isset($archivo["tmp_name"])
            || !is_uploaded_file($archivo["tmp_name"])
            || ($archivo["size"] ?? 0) > 5 * 1024 * 1024
        ) {
            return null;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo["tmp_name"]);
        if (!isset($tiposPermitidos[$mime])) {
            return null;
        }

        $directorio = __DIR__ . "/../../storage/evidencias";

        if (!is_dir($directorio)) {
            mkdir($directorio, 0750, true);
        }

        $nombreArchivo = "evidencia_" . bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$mime];
        $rutaDestino = $directorio . "/" . $nombreArchivo;

        if (!move_uploaded_file($archivo["tmp_name"], $rutaDestino)) {
            return null;
        }

        return app_url("/storage/evidencias/" . $nombreArchivo);
    }

    public function actualizarEstado(int $idEvento, string $nuevoEstado): bool
    {
        $sql = "UPDATE eventos SET estado = ? WHERE id_evento = ?";

        return $this->ejecutar($sql, [$nuevoEstado, $idEvento]);
    }

    public function eliminarEventos(array $ids): bool
    {
        $ids = array_values(array_unique(array_filter(array_map("intval", $ids), static fn(int $id): bool => $id > 0)));
        if (empty($ids)) {
            return false;
        }

        $marcadores = implode(",", array_fill(0, count($ids), "?"));
        $this->conexion->beginTransaction();

        try {
            $stmt = $this->conexion->prepare("DELETE FROM eventos WHERE id_evento IN ({$marcadores})");
            $stmt->execute($ids);
            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    public function obtenerEstados(): array
    {
        return [
            "Pendiente" => "Pendiente",
            "Confirmado" => "Confirmado",
            "Criticado" => "Criticado",
            "Resuelto" => "Resuelto",
        ];
    }
}
