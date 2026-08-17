<?php

require_once __DIR__ . "/BaseModel.php";

class UsuarioModel extends BaseModel
{
    public const ROLES = ["administrador", "monitorista", "gerente", "rh"];

    public function buscarActivo(string $usuario): ?array
    {
        $sql = "
            SELECT id_usuario, nombre_usuario, usuario, password, rol, estado
            FROM usuarios
            WHERE usuario = ? AND estado = 1
            LIMIT 1
        ";

        return $this->consultarUno($sql, [$usuario]);
    }

    public function crear(string $nombre, string $usuario, string $password, string $rol = "usuario"): bool
    {
        $sql = "
            INSERT INTO usuarios (nombre_usuario, usuario, password, rol, estado)
            VALUES (?, ?, ?, ?, 1)
        ";

        return $this->ejecutar($sql, [
            $nombre,
            $usuario,
            password_hash($password, PASSWORD_DEFAULT),
            $rol,
        ]);
    }

    public function listar(): array
    {
        return $this->consultar("SELECT id_usuario, nombre_usuario, usuario, rol, estado, fecha_creacion FROM usuarios ORDER BY nombre_usuario ASC");
    }

    public function actualizar(int $id, string $nombre, string $usuario, string $rol, ?string $password = null): bool
    {
        if ($password !== null && $password !== "") {
            return $this->ejecutar(
                "UPDATE usuarios SET nombre_usuario = ?, usuario = ?, rol = ?, password = ? WHERE id_usuario = ?",
                [$nombre, $usuario, $rol, password_hash($password, PASSWORD_DEFAULT), $id]
            );
        }

        return $this->ejecutar(
            "UPDATE usuarios SET nombre_usuario = ?, usuario = ?, rol = ? WHERE id_usuario = ?",
            [$nombre, $usuario, $rol, $id]
        );
    }

    public function cambiarEstado(int $id, int $estado): bool
    {
        return $this->ejecutar("UPDATE usuarios SET estado = ? WHERE id_usuario = ?", [$estado, $id]);
    }

    public function contarAdministradoresActivos(): int
    {
        return (int) $this->conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador' AND estado = 1")->fetchColumn();
    }
}
