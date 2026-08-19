<?php

require_once __DIR__ . "/BaseModel.php";

class DashboardFilteredModel extends BaseModel
{
    private const CONDUCTUALES = [
        "Fatiga crítica", "Fatiga moderada", "Uso del teléfono confirmado", "Uso del radio",
        "Volante suelto", "Lectura de indicadores", "Maniobra", "Estacionado",
        "Anotaciones durante operación", "Desconexión de la cámara", "Obstrucción de cámara", "Uso de cigarro",
    ];

    private const CRITICAS = [
        "Fatiga crítica", "Fatiga moderada", "Uso del teléfono confirmado", "Uso de cigarro",
        "Obstrucción de cámara", "Desconexión de la cámara",
    ];

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
        if (!empty($filters["supervisor"])) {
            $where[] = "EXISTS (SELECT 1 FROM relevos rf WHERE rf.fecha_operativa = {$alias}.fecha_operativa AND rf.turno = {$alias}.turno AND rf.id_operador = {$alias}.id_operador AND rf.id_supervisor = ?)";
            $params[] = (int) $filters["supervisor"];
        }
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

    private function inList(string $column, array $values, array &$params): string
    {
        $params = array_merge($params, $values);
        return $column . " IN (" . implode(",", array_fill(0, count($values), "?")) . ")";
    }

    public function listarOperadores(): array { return $this->consultar("SELECT id_operador, nombre_completo FROM operadores WHERE estado = 1 ORDER BY nombre_completo", []); }
    public function listarSupervisores(): array { return $this->consultar("SELECT id_supervisor, nombre_completo FROM supervisores WHERE estado = 1 ORDER BY nombre_completo", []); }

    public function resumenGeneral(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        $crit = [];
        $critWhere = $this->inList("et.nombre_etiqueta", self::CRITICAS, $crit);
        $sql = "SELECT COUNT(*) total_eventos, COUNT(DISTINCT e.id_operador) total_operadores, COUNT(DISTINCT e.id_maquina) total_maquinas,
            SUM(CASE WHEN {$critWhere} THEN 1 ELSE 0 END) total_criticos FROM eventos e LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta WHERE {$where}";
        return $this->consultarUno($sql, array_merge($crit, $params)) ?? ["total_eventos"=>0,"total_operadores"=>0,"total_maquinas"=>0,"total_criticos"=>0];
    }

    public function eventosPorTipo(array $f): array { return $this->groupEvent($f, "te.nombre_evento", "nombre_evento", "tipos_eventos te ON te.id_tipo_evento=e.id_tipo_evento"); }
    public function eventosPorTurno(array $f): array { return $this->groupEvent($f, "e.turno", "turno", ""); }
    public function eventosPorOperador(array $f): array { return $this->groupEvent($f, "o.nombre_completo", "operador", "operadores o ON o.id_operador=e.id_operador", "o.nombre_completo", 8); }
    public function operadoresConMasEventos(array $f): array { return $this->groupEvent($f, "o.nombre_completo", "nombre_completo", "operadores o ON o.id_operador=e.id_operador", "o.nombre_completo", 5); }
    public function maquinasConMasEventos(array $f): array { return $this->groupEvent($f, "m.nombre_maquina", "nombre_maquina", "maquinas m ON m.id_maquina=e.id_maquina", "m.nombre_maquina", 5); }
    public function eventosPorEtiqueta(array $f): array
    {
        [$where, $params] = $this->eventFilter($f);
        return $this->consultar(
            "SELECT COALESCE(NULLIF(TRIM(et.nombre_etiqueta), ''), 'Sin etiqueta') etiqueta, COUNT(e.id_evento) total
             FROM eventos e
             LEFT JOIN etiquetas et ON et.id_etiqueta = e.id_etiqueta
             WHERE {$where}
             GROUP BY etiqueta
             ORDER BY total DESC, etiqueta ASC
             LIMIT 12",
            $params
        );
    }

    private function groupEvent(array $f, string $group, string $label, string $join = "", string $order = "total DESC", int $limit = 0): array
    {
        [$where, $params] = $this->eventFilter($f);
        $sql = "SELECT {$group} AS {$label}, COUNT(e.id_evento) total FROM eventos e" . ($join ? " INNER JOIN {$join}" : "") . " WHERE {$where} GROUP BY {$group} ORDER BY {$order}" . ($limit ? " LIMIT {$limit}" : "");
        return $this->consultar($sql, $params);
    }

    public function horasOperativasPorTurno(array $f): array
    {
        [$where, $params] = $this->relevoFilter($f);
        return $this->consultar("SELECT r.turno, ROUND(SUM(COALESCE(r.horas_operativas,0)),2) total_horas FROM relevos r WHERE {$where} GROUP BY r.turno ORDER BY r.turno", $params);
    }

    public function eventosConductualesResumen(array $f): array
    {
        [$where, $whereParams] = $this->eventFilter($f); $inParams = []; $in = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $inParams);
        return $this->consultar("SELECT CASE WHEN {$in} THEN 'Conductual' ELSE 'No conductual' END categoria, COUNT(*) total FROM eventos e LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta WHERE {$where} GROUP BY categoria ORDER BY total DESC", array_merge($inParams, $whereParams));
    }

    public function eventosPorOperadorYCategoria(array $f): array
    {
        [$where, $whereParams] = $this->eventFilter($f); $inParams1 = []; $in1 = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $inParams1); $inParams2 = []; $in2 = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $inParams2);
        return $this->consultar("SELECT o.nombre_completo operador, SUM(CASE WHEN {$in1} THEN 1 ELSE 0 END) conductuales, SUM(CASE WHEN NOT ({$in2}) OR et.nombre_etiqueta IS NULL THEN 1 ELSE 0 END) no_conductuales FROM eventos e LEFT JOIN operadores o ON o.id_operador=e.id_operador LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta WHERE {$where} GROUP BY o.id_operador,o.nombre_completo ORDER BY (conductuales+no_conductuales) DESC LIMIT 8", array_merge($inParams1, $inParams2, $whereParams));
    }

    public function eventosConductualesPorOperador(array $f): array
    {
        [$where, $whereParams] = $this->eventFilter($f); $inParams = []; $in = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $inParams);
        return $this->consultar("SELECT o.nombre_completo operador, et.nombre_etiqueta etiqueta, COUNT(*) total FROM eventos e LEFT JOIN operadores o ON o.id_operador=e.id_operador LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta WHERE {$where} AND {$in} GROUP BY o.id_operador,o.nombre_completo,et.nombre_etiqueta ORDER BY o.nombre_completo,total DESC", array_merge($whereParams, $inParams));
    }

    public function horariosRiesgoOperacional(array $f): array
    {
        [$where, $whereParams] = $this->eventFilter($f); $inParams = []; $in = $this->inList("et.nombre_etiqueta", self::CRITICAS, $inParams);
        return $this->consultar("SELECT HOUR(e.hora_evento) hora, COUNT(*) total_eventos, SUM(CASE WHEN {$in} THEN 1 ELSE 0 END) eventos_criticos FROM eventos e LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta WHERE {$where} GROUP BY HOUR(e.hora_evento) ORDER BY eventos_criticos DESC,total_eventos DESC", array_merge($inParams, $whereParams));
    }

    public function tendenciaConductualSemanal(array $f): array { return $this->trend($f, "YEARWEEK(e.fecha_evento,1)", "DATE_FORMAT(MIN(e.fecha_evento),'%d/%m/%Y')", "semana"); }
    public function tendenciaEventosMensual(array $f): array { return $this->trend($f, "DATE_FORMAT(e.fecha_evento,'%Y-%m')", "DATE_FORMAT(MIN(e.fecha_evento),'%m/%Y')", "periodo", false); }

    private function trend(array $f, string $group, string $labelSql, string $label, bool $conductual = true): array
    {
        [$where, $params] = $this->eventFilter($f); $extra = "";
        if ($conductual) { $in = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $params); $extra = " INNER JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta WHERE {$where} AND {$in}"; } else { $extra = " WHERE {$where}"; }
        return $this->consultar("SELECT {$group} periodo_orden, {$labelSql} {$label}, COUNT(*) total FROM eventos e{$extra} GROUP BY {$group} ORDER BY periodo_orden", $params);
    }

    public function horaMasFrecuente(array $f): ?array { return $this->singleHour($f, false); }
    public function horaMasCritica(array $f): ?array { return $this->singleHour($f, true); }
    private function singleHour(array $f, bool $critical): ?array { [$where,$whereParams]=$this->eventFilter($f); $inParams=[]; $extra=" LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta"; $condition=""; if($critical){$in=$this->inList("et.nombre_etiqueta",self::CRITICAS,$inParams);$condition=" AND {$in}";} return $this->consultarUno("SELECT HOUR(e.hora_evento) hora,COUNT(*) total FROM eventos e{$extra} WHERE {$where}{$condition} GROUP BY HOUR(e.hora_evento) ORDER BY total DESC,hora ASC LIMIT 1", array_merge($whereParams,$inParams)); }
    public function operadorMasCritico(array $f): ?array { return $this->singleEntity($f,"operadores o ON o.id_operador=e.id_operador","o.nombre_completo","nombre"); }
    public function maquinaMasCritica(array $f): ?array { return $this->singleEntity($f,"maquinas m ON m.id_maquina=e.id_maquina","m.nombre_maquina","nombre"); }
    private function singleEntity(array $f,string $join,string $field,string $label):?array { [$where,$whereParams]=$this->eventFilter($f);$inParams=[];$in=$this->inList("et.nombre_etiqueta",self::CRITICAS,$inParams);return $this->consultarUno("SELECT {$field} {$label},COUNT(*) total FROM eventos e INNER JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta INNER JOIN {$join} WHERE {$where} AND {$in} GROUP BY {$field} ORDER BY total DESC LIMIT 1",array_merge($whereParams,$inParams)); }
}
