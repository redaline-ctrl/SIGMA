<?php

require_once __DIR__ . "/BaseModel.php";

class DashboardModel extends BaseModel
{
    public function totalEventos(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM eventos";

        $resultado = $this->consultarUno($sql);

        return (int) ($resultado["total"] ?? 0);
    }

    public function totalOperadores(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM operadores";

        $resultado = $this->consultarUno($sql);

        return (int) ($resultado["total"] ?? 0);
    }

    public function totalMaquinas(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM maquinas";

        $resultado = $this->consultarUno($sql);

        return (int) ($resultado["total"] ?? 0);
    }

    public function totalCriticos(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM eventos e
            INNER JOIN etiquetas et
                ON e.id_etiqueta = et.id_etiqueta
            WHERE et.nombre_etiqueta IN (
                'Fatiga crítica',
                'Uso del teléfono confirmado'
            )
        ";

        $resultado = $this->consultarUno($sql);

        return (int) ($resultado["total"] ?? 0);
    }
}