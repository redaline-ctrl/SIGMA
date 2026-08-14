<?php

require_once __DIR__ . "/BaseModel.php";

class DashboardAdvancedModel extends BaseModel
{
    public function resumenGeneral(): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM eventos) AS total_eventos,
                (SELECT COUNT(*) FROM operadores WHERE estado = 1) AS total_operadores,
                (SELECT COUNT(*) FROM maquinas WHERE estado = 1) AS total_maquinas,
                (SELECT COUNT(*) FROM eventos e
                 INNER JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
                 WHERE et.nombre_etiqueta IN ('Fatiga crítica', 'Uso del teléfono confirmado', 'Fatiga', 'Uso de cigarro')) AS total_criticos,
                (SELECT COUNT(*) FROM relevos) AS total_relevos,
                (SELECT SUM(CASE WHEN turno LIKE '%1%' THEN 1 ELSE 0 END) FROM eventos) AS eventos_turno_1,
                (SELECT SUM(CASE WHEN turno LIKE '%2%' THEN 1 ELSE 0 END) FROM eventos) AS eventos_turno_2,
                (SELECT SUM(CASE WHEN turno LIKE '%3%' THEN 1 ELSE 0 END) FROM eventos) AS eventos_turno_3
        ";

        $resultado = $this->consultarUno($sql);

        return $resultado ?? [
            "total_eventos" => 0,
            "total_operadores" => 0,
            "total_maquinas" => 0,
            "total_criticos" => 0,
            "total_relevos" => 0,
            "eventos_turno_1" => 0,
            "eventos_turno_2" => 0,
            "eventos_turno_3" => 0,
        ];
    }

    public function eventosPorTipo(): array
    {
        $sql = "
            SELECT te.nombre_evento, COUNT(e.id_evento) AS total
            FROM eventos e
            INNER JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            GROUP BY te.id_tipo_evento, te.nombre_evento
            ORDER BY total DESC
        ";

        return $this->consultar($sql);
    }

    public function eventosPorTurno(): array
    {
        $sql = "
            SELECT turno, COUNT(*) AS total
            FROM (
                SELECT
                    CASE
                        WHEN turno IS NULL THEN 'Sin turno'
                        WHEN turno LIKE '%1%' THEN '1'
                        WHEN turno LIKE '%2%' THEN '2'
                        WHEN turno LIKE '%3%' THEN '3'
                        ELSE 'Sin turno'
                    END AS turno
                FROM eventos
            ) t
            GROUP BY turno
            ORDER BY CASE turno
                WHEN '1' THEN 1
                WHEN '2' THEN 2
                WHEN '3' THEN 3
                ELSE 4
            END ASC
        ";

        return $this->consultar($sql);
    }

    public function operadoresConMasEventos(): array
    {
        $sql = "
            SELECT
                o.nombre_completo,
                COUNT(e.id_evento) AS total
            FROM operadores o
            LEFT JOIN eventos e ON e.id_operador = o.id_operador
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC
            LIMIT 5
        ";

        return $this->consultar($sql);
    }

    public function maquinasConMasEventos(): array
    {
        $sql = "
            SELECT
                m.nombre_maquina,
                COUNT(e.id_evento) AS total
            FROM maquinas m
            LEFT JOIN eventos e ON e.id_maquina = m.id_maquina
            GROUP BY m.id_maquina, m.nombre_maquina
            ORDER BY total DESC, m.nombre_maquina ASC
            LIMIT 5
        ";

        return $this->consultar($sql);
    }

    public function horasOperativasPorTurno(): array
    {
        $sql = "
            SELECT turno, ROUND(SUM(COALESCE(total_horas, 0)), 2) AS total_horas
            FROM (
                SELECT
                    CASE
                        WHEN turno IS NULL THEN 'Sin turno'
                        WHEN turno LIKE '%1%' THEN '1'
                        WHEN turno LIKE '%2%' THEN '2'
                        WHEN turno LIKE '%3%' THEN '3'
                        ELSE 'Sin turno'
                    END AS turno,
                    horas_operativas AS total_horas
                FROM relevos
            ) t
            GROUP BY turno
            ORDER BY CASE turno
                WHEN '1' THEN 1
                WHEN '2' THEN 2
                WHEN '3' THEN 3
                ELSE 4
            END ASC
        ";

        return $this->consultar($sql);
    }

    public function eventosPorOperador(): array
    {
        $sql = "
            SELECT
                o.nombre_completo AS operador,
                COUNT(e.id_evento) AS total
            FROM operadores o
            LEFT JOIN eventos e ON e.id_operador = o.id_operador
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC
            LIMIT 8
        ";

        return $this->consultar($sql);
    }

    public function eventosConductualesResumen(): array
    {
        $sql = "
            SELECT
                CASE
                    WHEN et.nombre_etiqueta IN (
                        'Fatiga crítica',
                        'Fatiga moderada',
                        'Uso del teléfono confirmado',
                        'Uso del radio',
                        'Volante suelto',
                        'Lectura de indicadores',
                        'Maniobra',
                        'Estacionado',
                        'Anotaciones durante operación',
                        'Desconexión de la cámara',
                        'Obstrucción de cámara',
                        'Uso de cigarro'
                    ) THEN 'Conductual'
                    ELSE 'No conductual'
                END AS categoria,
                COUNT(*) AS total
            FROM eventos e
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            GROUP BY
                CASE
                    WHEN et.nombre_etiqueta IN (
                        'Fatiga crítica',
                        'Fatiga moderada',
                        'Uso del teléfono confirmado',
                        'Uso del radio',
                        'Volante suelto',
                        'Lectura de indicadores',
                        'Maniobra',
                        'Estacionado',
                        'Anotaciones durante operación',
                        'Desconexión de la cámara',
                        'Obstrucción de cámara',
                        'Uso de cigarro'
                    ) THEN 'Conductual'
                    ELSE 'No conductual'
                END
            ORDER BY total DESC
        ";

        return $this->consultar($sql);
    }

    public function eventosPorOperadorYCategoria(): array
    {
        $sql = "
            SELECT
                o.nombre_completo AS operador,
                SUM(CASE WHEN et.nombre_etiqueta IN (
                    'Fatiga crítica',
                    'Fatiga moderada',
                    'Uso del teléfono confirmado',
                    'Uso del radio',
                    'Volante suelto',
                    'Lectura de indicadores',
                    'Maniobra',
                    'Estacionado',
                    'Anotaciones durante operación',
                    'Desconexión de la cámara',
                    'Obstrucción de cámara',
                    'Uso de cigarro'
                ) THEN 1 ELSE 0 END) AS conductuales,
                SUM(CASE WHEN et.nombre_etiqueta NOT IN (
                    'Fatiga crítica',
                    'Fatiga moderada',
                    'Uso del teléfono confirmado',
                    'Uso del radio',
                    'Volante suelto',
                    'Lectura de indicadores',
                    'Maniobra',
                    'Estacionado',
                    'Anotaciones durante operación',
                    'Desconexión de la cámara',
                    'Obstrucción de cámara',
                    'Uso de cigarro'
                ) OR et.nombre_etiqueta IS NULL THEN 1 ELSE 0 END) AS no_conductuales
            FROM eventos e
            LEFT JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY (conductuales + no_conductuales) DESC, o.nombre_completo ASC
            LIMIT 8
        ";

        return $this->consultar($sql);
    }

    public function eventosConductualesPorOperador(): array
    {
        $sql = "
            SELECT
                o.nombre_completo AS operador,
                et.nombre_etiqueta AS etiqueta,
                COUNT(*) AS total
            FROM eventos e
            LEFT JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            WHERE et.nombre_etiqueta IN (
                'Fatiga crítica',
                'Fatiga moderada',
                'Uso del teléfono confirmado',
                'Volante suelto',
                'Lectura de indicadores',
                'Uso del radio',
                'Maniobra',
                'Estacionado',
                'Anotaciones durante operación',
                'Desconexión de la cámara',
                'Obstrucción de cámara',
                'Uso de cigarro',
                'Inevitable',
                'Falso positivo',
                'Pruebas'
            )
            GROUP BY o.id_operador, o.nombre_completo, et.nombre_etiqueta
            ORDER BY o.nombre_completo ASC, total DESC
        ";

        return $this->consultar($sql);
    }
}
