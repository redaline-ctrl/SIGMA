<?php
$filters = $filters ?? [];
$operadores = $operadores ?? [];
$supervisores = $supervisores ?? [];
$filterOperators = $filterOperators ?? [];
$filterSupervisors = $filterSupervisors ?? [];
$mejoresOperadores = $mejoresOperadores ?? [];
$atencionOperadores = $atencionOperadores ?? [];
$mejoresSupervisores = $mejoresSupervisores ?? [];
$atencionSupervisores = $atencionSupervisores ?? [];

function desempenoColor(string $nivel): string
{
    return match ($nivel) {
        "Excelente" => "bg-success",
        "Adecuado" => "bg-warning text-dark",
        "Requiere seguimiento" => "bg-orange",
        "Crítico" => "bg-danger",
        default => "bg-secondary",
    };
}

function tarjetaTop(array $rows, string $nombre): void
{
    echo '<div class="list-stack simple">';
    if (empty($rows)) {
        echo '<p class="text-muted mb-0">Sin muestra suficiente.</p>';
    } else {
        foreach ($rows as $index => $row) {
            echo '<div class="row-item"><span>' . ($index + 1) . '. ' . htmlspecialchars($row[$nombre] ?? "-") . '</span><strong>' . htmlspecialchars($row["nivel"] ?? "-") . '</strong></div>';
        }
    }
    echo '</div>';
}
?>

<div class="dashboard-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div><h1 class="h4 mb-1">Desempeño</h1><p class="text-muted mb-0">Ranking normalizado por horas operativas. No modifica información histórica.</p></div>
        <a href="<?= htmlspecialchars(app_route("desempeno"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">Limpiar filtros</a>
    </div>

    <div class="card shadow-sm border-0 mb-4"><div class="card-body">
        <form method="GET" action="<?= htmlspecialchars(app_route("desempeno"), ENT_QUOTES, "UTF-8") ?>" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Fecha</label><input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filters["fecha"] ?? "") ?>"></div>
            <div class="col-md-2"><label class="form-label">Desde</label><input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($filters["desde"] ?? "") ?>"></div>
            <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($filters["hasta"] ?? "") ?>"></div>
            <div class="col-md-1"><label class="form-label">Mes</label><select name="mes" class="form-select"><option value="">Todos</option><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= (string)($filters["mes"]??"")===(string)$m?"selected":"" ?>><?= $m ?></option><?php endfor; ?></select></div>
            <div class="col-md-2"><label class="form-label">Año</label><input type="number" name="anio" min="2000" max="2100" class="form-control" value="<?= htmlspecialchars($filters["anio"] ?? "") ?>"></div>
            <div class="col-md-2"><label class="form-label">Turno</label><select name="turno" class="form-select"><option value="">Todos</option><?php foreach(["1","2","3"] as $turno): ?><option value="<?= $turno ?>" <?= ($filters["turno"]??"")===$turno?"selected":"" ?>>Turno <?= $turno ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Operador</label><select name="operador" class="form-select"><option value="">Todos</option><?php foreach($filterOperators as $item): ?><option value="<?= (int)$item["id_operador"] ?>" <?= (string)($filters["operador"]??"")===(string)$item["id_operador"]?"selected":"" ?>><?= htmlspecialchars($item["nombre_completo"]) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Supervisor</label><select name="supervisor" class="form-select"><option value="">Todos</option><?php foreach($filterSupervisors as $item): ?><option value="<?= (int)$item["id_supervisor"] ?>" <?= (string)($filters["supervisor"]??"")===(string)$item["id_supervisor"]?"selected":"" ?>><?= htmlspecialchars($item["nombre_completo"]) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><button class="btn btn-primary w-100">Aplicar filtros</button></div>
        </form>
    </div></div>

    <div class="row g-4 mb-4">
        <?php foreach ([["Top 3 operadores", $mejoresOperadores, "nombre_completo"], ["Operadores que requieren atención", $atencionOperadores, "nombre_completo"], ["Top 3 supervisores", $mejoresSupervisores, "nombre_completo"], ["Supervisores que requieren atención", $atencionSupervisores, "nombre_completo"]] as [$title, $rows, $name]): ?>
            <div class="col-lg-3"><div class="panel-card"><div class="panel-header"><h3><?= htmlspecialchars($title) ?></h3></div><?php tarjetaTop($rows, $name); ?></div></div>
        <?php endforeach; ?>
    </div>

    <?php foreach ([["Desempeño de Operadores", $operadores, "horas", "relevos"], ["Desempeño de Supervisores", $supervisores, "horas", "operadores"]] as [$title, $rows, $hours, $secondary]): ?>
        <div class="panel-card mb-4"><div class="panel-header"><h2><?= htmlspecialchars($title) ?></h2></div><div class="table-wrap"><table class="table table-striped table-hover align-middle"><thead><tr><th>Ranking</th><th>Nombre</th><th>Alertas</th><th>Horas operativas</th><th>Índice de riesgo</th><th>Nivel</th><th>Estado</th><th>Observación</th></tr></thead><tbody>
        <?php if (empty($rows)): ?><tr><td colspan="8" class="text-center text-muted py-4">Sin datos para los filtros seleccionados.</td></tr><?php else: foreach($rows as $row): ?><tr><td><strong><?= (int)$row["ranking"] ?></strong></td><td><?= htmlspecialchars($row["nombre_completo"] ?? "-") ?></td><td><?= (int)($row["alertas"]??0) ?></td><td><?= number_format((float)($row[$hours]??0),2) ?></td><td><?= $row["indice_riesgo"] === null ? "-" : number_format((float)$row["indice_riesgo"],2) ?></td><td><span class="badge <?= desempenoColor($row["nivel"]??"") ?>"><?= htmlspecialchars($row["nivel"]??"-") ?></span></td><td><?= htmlspecialchars($row["estado"]??"-") ?></td><td><?= htmlspecialchars($row["observacion"]??"-") ?></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
    <?php endforeach; ?>
</div>
