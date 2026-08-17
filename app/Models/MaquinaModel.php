<?php

require_once __DIR__ . "/BaseModel.php";

class MaquinaModel extends BaseModel
{
    public function listar(): array
    {
        $sql = "SELECT id_maquina, nombre_maquina, estado FROM maquinas ORDER BY nombre_maquina ASC";
        return $this->consultar($sql);
    }

    public function crear(string $nombre): bool
    {
        return $this->ejecutar(
            "INSERT INTO maquinas (nombre_maquina, estado) VALUES (?, 1)",
            [$nombre]
        );
    }

    public function actualizar(int $id, string $nombre): bool
    {
        return $this->ejecutar(
            "UPDATE maquinas SET nombre_maquina = ? WHERE id_maquina = ?",
            [$nombre, $id]
        );
    }

    public function cambiarEstado(int $id, int $estado): bool
    {
        return $this->ejecutar(
            "UPDATE maquinas SET estado = ? WHERE id_maquina = ?",
            [$estado, $id]
        );
    }
}
