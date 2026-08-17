<?php

require_once __DIR__ . "/BaseModel.php";

class DesempenoModel extends BaseModel
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

    public function listarOperadores(): array { return $this->consultar("SELECT id_operador,nombre_completo FROM operadores WHERE estado=1 ORDER BY nombre_completo", []); }
    public function listarSupervisores(): array { return $this->consultar("SELECT id_supervisor,nombre_completo FROM supervisores WHERE estado=1 ORDER BY nombre_completo", []); }

    public function operadores(array $filters): array
    {
        [$eventWhere, $eventParams] = $this->eventWhere($filters, "e");
        [$relevoWhere, $relevoParams] = $this->relevoWhere($filters, "r");
        $conductParams = [];
        $conduct = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $conductParams);
        $criticalParams = [];
        $critical = $this->inList("et.nombre_etiqueta", self::CRITICAS, $criticalParams);

        $sql = "
            SELECT o.id_operador, o.nombre_completo,
                COALESCE(ev.alertas,0) alertas,
                COALESCE(ev.conductuales,0) conductuales,
                COALESCE(ev.criticas,0) criticas,
                COALESCE(rh.horas,0) horas,
                COALESCE(rh.relevos,0) relevos
            FROM operadores o
            LEFT JOIN (
                SELECT e.id_operador, COUNT(*) alertas,
                    SUM(CASE WHEN {$conduct} THEN 1 ELSE 0 END) conductuales,
                    SUM(CASE WHEN {$critical} THEN 1 ELSE 0 END) criticas
                FROM eventos e LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta
                WHERE {$eventWhere}
                GROUP BY e.id_operador
            ) ev ON ev.id_operador=o.id_operador
            LEFT JOIN (
                SELECT r.id_operador, SUM(COALESCE(r.horas_operativas,0)) horas, COUNT(*) relevos
                FROM relevos r WHERE {$relevoWhere}
                GROUP BY r.id_operador
            ) rh ON rh.id_operador=o.id_operador
            WHERE o.estado=1
            ORDER BY o.nombre_completo
        ";

        $rows = $this->consultar($sql, array_merge($conductParams, $criticalParams, $eventParams, $relevoParams));
        return $this->clasificar($rows, "operador");
    }

    public function supervisores(array $filters): array
    {
        [$relevoWhere, $relevoParams] = $this->relevoWhere($filters, "r");
        $eventParams = [];
        $conductParams = [];
        $conduct = $this->inList("et.nombre_etiqueta", self::CONDUCTUALES, $conductParams);
        $criticalParams = [];
        $critical = $this->inList("et.nombre_etiqueta", self::CRITICAS, $criticalParams);
        $eventWhere = $this->eventSupervisorWhere($filters, "e", "rmap");
        $eventWhereParams = $this->eventSupervisorParams($filters);

        $sql = "
            SELECT s.id_supervisor, s.nombre_completo,
                COALESCE(ev.alertas,0) alertas,
                COALESCE(ev.conductuales,0) conductuales,
                COALESCE(ev.criticas,0) criticas,
                COALESCE(rh.horas,0) horas,
                COALESCE(rh.operadores,0) operadores,
                COALESCE(rh.relevos,0) relevos
            FROM supervisores s
            LEFT JOIN (
                SELECT rmap.id_supervisor, COUNT(DISTINCT e.id_evento) alertas,
                    COUNT(DISTINCT CASE WHEN {$conduct} THEN e.id_evento END) conductuales,
                    COUNT(DISTINCT CASE WHEN {$critical} THEN e.id_evento END) criticas
                FROM relevos rmap
                LEFT JOIN eventos e ON e.fecha_operativa=rmap.fecha_operativa AND e.turno=rmap.turno AND e.id_operador=rmap.id_operador AND e.id_maquina=rmap.id_maquina
                LEFT JOIN etiquetas et ON et.id_etiqueta=e.id_etiqueta
                WHERE {$eventWhere}
                GROUP BY rmap.id_supervisor
            ) ev ON ev.id_supervisor=s.id_supervisor
            LEFT JOIN (
                SELECT r.id_supervisor, SUM(COALESCE(r.horas_operativas,0)) horas, COUNT(DISTINCT r.id_operador) operadores, COUNT(*) relevos
                FROM relevos r WHERE {$relevoWhere}
                GROUP BY r.id_supervisor
            ) rh ON rh.id_supervisor=s.id_supervisor
            WHERE s.estado=1
            ORDER BY s.nombre_completo
        ";

        $rows = $this->consultar($sql, array_merge($conductParams, $criticalParams, $eventWhereParams, $relevoParams));
        return $this->clasificar($rows, "supervisor");
    }

    private function clasificar(array $rows, string $tipo): array
    {
        foreach ($rows as &$row) {
            $horas = (float) ($row["horas"] ?? 0);
            $criticas = (int) ($row["criticas"] ?? 0);
            $conductuales = (int) ($row["conductuales"] ?? 0);
            $row["indice_riesgo"] = $horas > 0 ? round(($criticas * 0.7 + $conductuales * 0.3) / $horas * 100, 2) : null;
            if ($horas < 8) {
                $row["nivel"] = "Muestra insuficiente";
                $row["observacion"] = "Se requieren al menos 8 horas operativas para clasificar.";
                $row["estado"] = "Pendiente de muestra";
                $row["score"] = null;
            } else {
                $indice = (float) $row["indice_riesgo"];
                $row["nivel"] = $indice <= 2 ? "Excelente" : ($indice <= 5 ? "Adecuado" : ($indice <= 10 ? "Requiere seguimiento" : "Crítico"));
                $row["estado"] = $row["nivel"] === "Crítico" ? "Atención" : "Normal";
                $row["observacion"] = $row["nivel"] === "Crítico" ? "Revisar alertas críticas y planificar seguimiento." : ($row["nivel"] === "Requiere seguimiento" ? "Dar seguimiento a la tendencia de alertas." : "Mantener las prácticas operativas actuales.");
                $row["score"] = max(0, 100 - $indice);
            }
        }
        unset($row);
        usort($rows, static function (array $a, array $b): int {
            if ($a["score"] === null && $b["score"] !== null) return 1;
            if ($a["score"] !== null && $b["score"] === null) return -1;
            return ($b["score"] ?? 0) <=> ($a["score"] ?? 0);
        });
        foreach ($rows as $index => &$row) { $row["ranking"] = $index + 1; }
        unset($row);
        return $rows;
    }

    private function eventWhere(array $f, string $alias): array
    {
        $where = ["1=1"]; $params=[];
        if (!empty($f["fecha"])) {$where[]="DATE({$alias}.fecha_evento)=?";$params[]=$f["fecha"];}
        if (!empty($f["desde"])) {$where[]="DATE({$alias}.fecha_evento)>=?";$params[]=$f["desde"];}
        if (!empty($f["hasta"])) {$where[]="DATE({$alias}.fecha_evento)<=?";$params[]=$f["hasta"];}
        if (!empty($f["mes"])) {$where[]="MONTH({$alias}.fecha_evento)=?";$params[]=(int)$f["mes"];}
        if (!empty($f["anio"])) {$where[]="YEAR({$alias}.fecha_evento)=?";$params[]=(int)$f["anio"];}
        if (!empty($f["turno"])) {$where[]="{$alias}.turno=?";$params[]=$f["turno"];}
        if (!empty($f["operador"])) {$where[]="{$alias}.id_operador=?";$params[]=(int)$f["operador"];}
        if (!empty($f["supervisor"])) {$where[]="EXISTS (SELECT 1 FROM relevos rf WHERE rf.fecha_operativa={$alias}.fecha_operativa AND rf.turno={$alias}.turno AND rf.id_operador={$alias}.id_operador AND rf.id_supervisor=?)";$params[]=(int)$f["supervisor"];}
        return [implode(" AND ",$where),$params];
    }

    private function relevoWhere(array $f, string $alias): array
    {
        $where=["1=1"]; $params=[];
        if (!empty($f["fecha"])) {$where[]="DATE({$alias}.fecha_operativa)=?";$params[]=$f["fecha"];}
        if (!empty($f["desde"])) {$where[]="DATE({$alias}.fecha_operativa)>=?";$params[]=$f["desde"];}
        if (!empty($f["hasta"])) {$where[]="DATE({$alias}.fecha_operativa)<=?";$params[]=$f["hasta"];}
        if (!empty($f["mes"])) {$where[]="MONTH({$alias}.fecha_operativa)=?";$params[]=(int)$f["mes"];}
        if (!empty($f["anio"])) {$where[]="YEAR({$alias}.fecha_operativa)=?";$params[]=(int)$f["anio"];}
        if (!empty($f["turno"])) {$where[]="{$alias}.turno=?";$params[]=$f["turno"];}
        if (!empty($f["operador"])) {$where[]="{$alias}.id_operador=?";$params[]=(int)$f["operador"];}
        if (!empty($f["supervisor"])) {$where[]="{$alias}.id_supervisor=?";$params[]=(int)$f["supervisor"];}
        return [implode(" AND ",$where),$params];
    }

    private function eventSupervisorWhere(array $f, string $eventAlias, string $relevoAlias): string
    {
        [$where] = $this->eventWhere($f, $eventAlias);
        $where = preg_replace('/AND EXISTS \(SELECT 1 FROM relevos rf .*?\)/', "", $where) ?? $where;
        return $where . " AND 1=1";
    }

    private function eventSupervisorParams(array $f): array
    {
        [$where, $params] = $this->eventWhere($f, "e");
        if (!empty($f["supervisor"])) { array_pop($params); }
        return $params;
    }

    private function inList(string $column, array $values, array &$params): string
    {
        $params = array_merge($params, $values);
        return $column . " IN (" . implode(",", array_fill(0,count($values),"?")) . ")";
    }
}
