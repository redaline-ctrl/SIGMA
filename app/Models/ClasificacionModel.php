<?php

require_once __DIR__ . "/BaseModel.php";

class ClasificacionModel extends BaseModel
{
    public function listar(): array
    {
        $sql = "SELECT id_clasificacion, nombre_clasificacion, descripcion FROM clasificaciones ORDER BY nombre_clasificacion ASC";
        return $this->consultar($sql);
    }
}
