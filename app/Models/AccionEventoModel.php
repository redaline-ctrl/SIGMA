<?php

require_once __DIR__ . "/BaseModel.php";

class AccionEventoModel extends BaseModel
{
    public function listarAcciones(int $idEvento): array
    {
        $sql = "
            SELECT
                ae.id_accion,
                ae.tipo_accion,
                ae.descripcion,
                ae.estado_nuevo,
                ae.usuario_accion,
                ae.fecha_accion,
                s.nombre_completo AS supervisor_nombre
            FROM acciones_evento ae
            LEFT JOIN supervisores s ON s.id_supervisor = ae.usuario_accion
            WHERE ae.id_evento = ?
            ORDER BY ae.fecha_accion DESC
        ";

        return $this->consultar($sql, [$idEvento]);
    }

    public function guardarAccion(int $idEvento, array $datos): bool
    {
        $sql = "
            INSERT INTO acciones_evento (
                id_evento,
                tipo_accion,
                descripcion,
                estado_nuevo,
                usuario_accion,
                fecha_accion
            ) VALUES (
                ?, ?, ?, ?, ?, NOW()
            )
        ";

        $parametros = [
            $idEvento,
            $datos["tipo_accion"] ?? "comentario",
            $datos["descripcion"] ?? "",
            $datos["estado_nuevo"] ?? null,
            $datos["usuario_accion"] ?? null,
        ];

        return $this->ejecutar($sql, $parametros);
    }

    public function verificarTabla(): bool
    {
        try {
            $sql = "SHOW TABLES LIKE 'acciones_evento'";
            $resultado = $this->consultar($sql);
            return !empty($resultado);
        } catch (Exception $e) {
            return false;
        }
    }

    public function crearTabla(): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS acciones_evento (
                id_accion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_evento INT UNSIGNED NOT NULL,
                tipo_accion VARCHAR(50) DEFAULT 'comentario',
                descripcion TEXT,
                estado_nuevo VARCHAR(50),
                usuario_accion INT UNSIGNED,
                fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_evento) REFERENCES eventos(id_evento) ON DELETE CASCADE,
                FOREIGN KEY (usuario_accion) REFERENCES supervisores(id_supervisor)
            )
        ";

        try {
            $this->conexion->exec($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
