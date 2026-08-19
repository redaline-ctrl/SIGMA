<?php

require_once __DIR__ . "/BaseModel.php";

class DashboardFilteredModel extends BaseModel
{
    private function eventFilter(array $filters, string $alias = "e"): array
    {
        $where = ["1 = 1"];
        $params = [];

        if (!empty($filters["fecha"])) { $where[] = "DATE({$alias}.fecha_evento) = ?"; $params[] = $filters["fecha"]; }
        if (!empty($filters["desde"])) { $where[] = "DATE({$alias}.fecha_evento) >= ?"; $params[] = $filters["desde"]; }
        if (!empty($filters["hasta"])) { $where[] = "DATE({$alias}.fecha_evento) <= ?"; $params[] = $filters["hasta"]; }
        if (!empty($filters["mes"])) { $where[] = "MONTH({$alias}.fecha_evento) = ?"; $params[] = (int) $filters["mes"]; }
        if (!empty($filters["anio"])) { $where[] = "YEAR({$alias}.fecha_evento) = ?"; $params[] = (int) $filters["anio"]; }
        if (!empty($filters["turno"])) { $where[] = "{$alias}.turno = ?"; $params[] = (string) $filters["turno"]; }
        if (!empty($filters["operador"])) { $where[] = "{$alias}.id_operador = ?"; $params[] = (int) $filters["operador"]; }
        if (!empty($filters["supervisor"])) { $where[] = "{$alias}.supervisor_id = ?"; $params[] = (int) $filters["supervisor"]; }

        return [implode(" AND ", $where), $params];
    }

    private function relevoFilter(array $filters, string $alias = "r"): array
    {
        $where = ["1 = 1"];
        $params = [];

        if (!empty($filters["fecha"])) { $where[] = "DATE({$alias}.fecha_operativa) = ?"; $params[] = $filters["fecha"]; }
        if (!empty($filters["desde"])) { $where[] = "DATE({$alias}.fecha_operativa) >= ?"; $params[] = $filters["desde"]; }
        if (!empty($filters["hasta"])) { $where[] = "DATE({$alias}.fecha_operativa) <= ?"; $params[] = $filters["hasta"]; }
        if (!empty($filters["mes"])) { $where[] = "MONTH({$alias}.fecha_operativa) = ?"; $params[] = (int) $filters["mes"]; }
        if (!empty($filters["anio"])) { $where[] = "YEAR({$alias}.fecha_operativa) = ?"; $params[] = (int) $filters["anio"]; }
        if (!empty($filters["turno"])) { $where[] = "{$alias}.turno = ?"; $params[] = (string) $filters["turno"]; }
        if (!empty($filters["operador"])) { $where[] = "{$alias}.id_operador = ?"; $params[] = (int) $filters["operador"]; }
        if (!empty($filters["supervisor"])) { $where[] = "{$alias}.id_supervisor = ?"; $params[] = (int) $filters["supervisor"]; }

        return [implode(" AND ", $where), $params];
    }

    private function condConductual(string $aliasClasificacion = "c"): string
    {
        return "LOWER(TRIM({$aliasClasificacion}.nombre_clasificacion)) = 'conductual'";
    }

    private function condRegistrado(string $aliasClasificacion = "c"): string
    {
        return "LOWER(TRIM({$aliasClasificacion}.nombre_clasificacion)) = 'registrado'";
    }

    public function listarOperadores(): array
    {
        return $this->consultar("SELECT id_operador, nombre_completo FROM operadores WHERE estado = 1 ORDER BY nombre_completo", []);
    }

    public function listarSupervisores(): array
    {
        return $this->consultar("SELECT id_supervisor, nombre_completo FROM supervisores WHERE estado = 1 ORDER BY nombre_completo", []);
    }

    public function getTotalesClasificacion(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        $registrado = $this->condRegistrado("c");

        $row = $this->consultarUno(
            "SELECT
                SUM(CASE WHEN {$conductual} THEN 1 ELSE 0 END) AS conductuales,
                SUM(CASE WHEN {$registrado} THEN 1 ELSE 0 END) AS registrados
            FROM eventos e
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where}",
            $params
        ) ?? ["conductuales" => 0, "registrados" => 0];

        $conductuales = (int) ($row["conductuales"] ?? 0);
        $registrados = (int) ($row["registrados"] ?? 0);

        return [
            "conductuales" => $conductuales,
            "registrados" => $registrados,
            "total" => $conductuales + $registrados,
        ];
    }

    public function getComparativaConductualRegistrado(array $f): array
    {
        $totales = $this->getTotalesClasificacion($f);
        return [
            ["clasificacion" => "Conductual", "total" => (int) $totales["conductuales"]],
            ["clasificacion" => "Registrado", "total" => (int) $totales["registrados"]],
        ];
    }

    public function getConductualesPorOperador(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");

        return $this->consultar(
            "SELECT o.nombre_completo AS operador, COUNT(*) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC",
            $params
        );
    }

    public function getRegistradosPorOperador(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $registrado = $this->condRegistrado("c");

        return $this->consultar(
            "SELECT o.nombre_completo AS operador, COUNT(*) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$registrado}
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC",
            $params
        );
    }

    public function getDetalleOperadorCompleto(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        $registrado = $this->condRegistrado("c");

        return $this->consultar(
            "SELECT
                o.nombre_completo AS operador,
                SUM(CASE WHEN {$conductual} THEN 1 ELSE 0 END) AS conductuales,
                SUM(CASE WHEN {$registrado} THEN 1 ELSE 0 END) AS registrados,
                SUM(CASE WHEN {$conductual} OR {$registrado} THEN 1 ELSE 0 END) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where}
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, o.nombre_completo ASC",
            $params
        );
    }

    public function getDetalleConductualPorOperadorPorEtiqueta(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");

        return $this->consultar(
            "SELECT
                o.nombre_completo AS operador,
                et.nombre_etiqueta AS etiqueta,
                COUNT(*) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY o.id_operador, o.nombre_completo, et.nombre_etiqueta
            ORDER BY o.nombre_completo ASC, total DESC",
            $params
        );
    }

    public function getHorariosMayorRiesgo(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");

        return $this->consultar(
            "SELECT HOUR(e.hora_evento) AS hora, COUNT(*) AS total_eventos, COUNT(*) AS eventos_criticos
            FROM eventos e
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY HOUR(e.hora_evento)
            ORDER BY total_eventos DESC, hora ASC",
            $params
        );
    }

    public function getTendenciaSemanal(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");

        return $this->consultar(
            "SELECT
                YEARWEEK(e.fecha_evento, 1) AS semana_orden,
                DATE_FORMAT(MIN(e.fecha_evento), '%d/%m/%Y') AS semana,
                COUNT(*) AS total
            FROM eventos e
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY YEARWEEK(e.fecha_evento, 1)
            ORDER BY semana_orden ASC",
            $params
        );
    }

    public function getEventosPorTurno(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");

        return $this->consultar(
            "SELECT e.turno, COUNT(*) AS total
            FROM eventos e
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY e.turno
            ORDER BY e.turno ASC",
            $params
        );
    }

    public function getTendenciaPorPeriodo(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");

        return $this->consultar(
            "SELECT
                DATE_FORMAT(e.fecha_evento, '%Y-%m') AS periodo,
                DATE_FORMAT(MIN(e.fecha_evento), '%m/%Y') AS periodo_label,
                COUNT(*) AS total
            FROM eventos e
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY DATE_FORMAT(e.fecha_evento, '%Y-%m')
            ORDER BY periodo ASC",
            $params
        );
    }

    public function resumenGeneral(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $totales = $this->getTotalesClasificacion($f);

        $base = $this->consultarUno(
            "SELECT
                COUNT(DISTINCT e.id_operador) AS total_operadores,
                COUNT(DISTINCT e.id_maquina) AS total_maquinas
            FROM eventos e
            WHERE {$where}",
            $params
        ) ?? ["total_operadores" => 0, "total_maquinas" => 0];

        return [
            "total_eventos" => (int) $totales["total"],
            "total_operadores" => (int) ($base["total_operadores"] ?? 0),
            "total_maquinas" => (int) ($base["total_maquinas"] ?? 0),
            "total_criticos" => (int) $totales["conductuales"],
        ];
    }

    public function eventosPorTipo(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        return $this->consultar(
            "SELECT te.nombre_evento, COUNT(e.id_evento) AS total
            FROM eventos e
            INNER JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            WHERE {$where}
            GROUP BY te.id_tipo_evento, te.nombre_evento
            ORDER BY total DESC",
            $params
        );
    }

    public function eventosPorEtiqueta(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $etiquetaExpr = "COALESCE(NULLIF(TRIM(et.nombre_etiqueta), ''), 'Sin etiqueta')";

        return $this->consultar(
            "SELECT {$etiquetaExpr} AS etiqueta, COUNT(e.id_evento) AS total
            FROM eventos e
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            WHERE {$where}
            GROUP BY {$etiquetaExpr}
            ORDER BY total DESC, {$etiquetaExpr} ASC
            LIMIT 12",
            $params
        );
    }

    public function eventosPorTipoPorOperador(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $etiqueta = "COALESCE(NULLIF(TRIM(et.nombre_etiqueta), ''), 'Sin etiqueta')";
        return $this->consultar(
            "SELECT o.nombre_completo AS operador, te.nombre_evento AS tipo_evento,
                {$etiqueta} AS etiqueta, COUNT(e.id_evento) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            INNER JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            WHERE {$where}
            GROUP BY o.id_operador, o.nombre_completo, te.id_tipo_evento,
                 te.nombre_evento, {$etiqueta}
            ORDER BY o.nombre_completo ASC, total DESC, te.nombre_evento ASC",
            $params
        );
    }

    public function eventosPorTipoEtiquetaPorOperador(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $etiqueta = "COALESCE(NULLIF(TRIM(et.nombre_etiqueta), ''), 'Sin etiqueta')";
        return $this->consultar(
            "SELECT o.nombre_completo AS operador, te.nombre_evento AS tipo_evento,
                    {$etiqueta} AS etiqueta, COUNT(*) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            INNER JOIN tipos_eventos te ON te.id_tipo_evento = e.id_tipo_evento
            LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
            WHERE {$where}
            GROUP BY o.id_operador, o.nombre_completo, te.id_tipo_evento,
                     te.nombre_evento, {$etiqueta}
            ORDER BY o.nombre_completo ASC, total DESC, te.nombre_evento ASC",
            $params
        );
    }

    public function eventosPorTurno(array $f): array
    {
        return $this->getEventosPorTurno($f);
    }

    public function operadoresConMasEventos(array $f): array
    {
        $lista = $this->getConductualesPorOperador($f);
        return array_slice($lista, 0, 5);
    }

    public function operadoresMayorRiesgo(array $f): array
    {
        return array_slice($this->getConductualesPorOperador($f), 0, 5);
    }

    public function operadoresMejorDesempeno(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        return $this->consultar(
            "SELECT o.nombre_completo AS operador,
                    SUM(CASE WHEN {$conductual} THEN 1 ELSE 0 END) AS conductuales,
                    COUNT(e.id_evento) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where}
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY conductuales ASC, total DESC, o.nombre_completo ASC
            LIMIT 5",
            $params
        );
    }

    public function maquinasConMasEventos(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        return $this->consultar(
            "SELECT m.nombre_maquina, COUNT(e.id_evento) AS total
            FROM eventos e
            INNER JOIN maquinas m ON m.id_maquina = e.id_maquina
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY m.id_maquina, m.nombre_maquina
            ORDER BY total DESC, m.nombre_maquina ASC
            LIMIT 5",
            $params
        );
    }

    public function horasOperativasPorTurno(array $f): array
    {
        [$where, $params] = $this->relevoFilter($f);
        return $this->consultar("SELECT r.turno, ROUND(SUM(COALESCE(r.horas_operativas,0)),2) AS total_horas FROM relevos r WHERE {$where} GROUP BY r.turno ORDER BY r.turno", $params);
    }

    public function eventosPorOperador(array $f): array
    {
        $lista = $this->getDetalleOperadorCompleto($f);
        $salida = [];
        foreach ($lista as $item) {
            $salida[] = [
                "operador" => $item["operador"],
                "total" => (int) ($item["total"] ?? 0),
            ];
        }
        return array_slice($salida, 0, 8);
    }

    public function eventosConductualesResumen(array $f): array
    {
        return $this->getComparativaConductualRegistrado($f);
    }

    public function eventosPorOperadorYCategoria(array $f): array
    {
        $lista = $this->getDetalleOperadorCompleto($f);
        $salida = [];

        foreach ($lista as $item) {
            $salida[] = [
                "operador" => $item["operador"],
                "conductuales" => (int) ($item["conductuales"] ?? 0),
                "no_conductuales" => (int) ($item["registrados"] ?? 0),
            ];
        }

        return array_slice($salida, 0, 8);
    }

    public function eventosConductualesPorOperador(array $f): array
    {
        return $this->getDetalleConductualPorOperadorPorEtiqueta($f);
    }

    public function horariosRiesgoOperacional(array $f): array
    {
        return $this->getHorariosMayorRiesgo($f);
    }

    public function tendenciaConductualSemanal(array $f): array
    {
        return $this->getTendenciaSemanal($f);
    }

    public function tendenciaEventosMensual(array $f): array
    {
        return $this->getTendenciaPorPeriodo($f);
    }

    public function horaMasFrecuente(array $f): ?array
    {
        [$where, $params] = $this->eventFilter($f);
        return $this->consultarUno(
            "SELECT HOUR(e.hora_evento) AS hora, COUNT(*) AS total
            FROM eventos e
            WHERE {$where}
            GROUP BY HOUR(e.hora_evento)
            ORDER BY total DESC, hora ASC
            LIMIT 1",
            $params
        );
    }

    public function horaMasCritica(array $f): ?array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        return $this->consultarUno(
            "SELECT HOUR(e.hora_evento) AS hora, COUNT(*) AS total
            FROM eventos e
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY HOUR(e.hora_evento)
            ORDER BY total DESC, hora ASC
            LIMIT 1",
            $params
        );
    }

    public function operadorMasCritico(array $f): ?array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        return $this->consultarUno(
            "SELECT o.nombre_completo AS nombre, COUNT(*) AS total
            FROM eventos e
            INNER JOIN operadores o ON o.id_operador = e.id_operador
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY o.id_operador, o.nombre_completo
            ORDER BY total DESC, nombre ASC
            LIMIT 1",
            $params
        );
    }

    public function maquinaMasCritica(array $f): ?array
    {
        [$where, $params] = $this->eventFilter($f);
        $conductual = $this->condConductual("c");
        return $this->consultarUno(
            "SELECT m.nombre_maquina AS nombre, COUNT(*) AS total
            FROM eventos e
            INNER JOIN maquinas m ON m.id_maquina = e.id_maquina
            LEFT JOIN clasificaciones c ON c.id_clasificacion = e.id_clasificacion
            WHERE {$where} AND {$conductual}
            GROUP BY m.id_maquina, m.nombre_maquina
            ORDER BY total DESC, nombre ASC
            LIMIT 1",
            $params
        );
    }
}
