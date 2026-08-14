<?php

require_once __DIR__ . "/BaseModel.php";

class MaquinaModel extends BaseModel
{
    public function listar(): array
    {
        $sql = "SELECT id_maquina, nombre_maquina, estado FROM maquinas ORDER BY nombre_maquina ASC";
        return $this->consultar($sql);
    }
}
