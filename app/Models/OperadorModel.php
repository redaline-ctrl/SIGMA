<?php

require_once __DIR__ . "/BaseModel.php";

class OperadorModel extends BaseModel
{
    public function listar(): array
    {
        $sql = "SELECT id_operador, nombre_completo, estado FROM operadores ORDER BY nombre_completo ASC";
        return $this->consultar($sql);
    }
}
