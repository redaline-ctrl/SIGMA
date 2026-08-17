<?php

require_once __DIR__ . "/BaseModel.php";

class OperadorModel extends BaseModel
{
    public function listar(): array
    {
        $sql = "SELECT id_operador, nombre_completo, estado FROM operadores ORDER BY nombre_completo ASC";
        return $this->consultar($sql);
    }

    public function crear(string $nombre): bool
    {
        return $this->ejecutar(
            "INSERT INTO operadores (nombre_completo, estado) VALUES (?, 1)",
            [$nombre]
        );
    }

    public function actualizar(int $id, string $nombre): bool
    {
        return $this->ejecutar(
            "UPDATE operadores SET nombre_completo = ? WHERE id_operador = ?",
            [$nombre, $id]
        );
    }

    public function cambiarEstado(int $id, int $estado): bool
    {
        return $this->ejecutar(
            "UPDATE operadores SET estado = ? WHERE id_operador = ?",
            [$estado, $id]
        );
    }
}
