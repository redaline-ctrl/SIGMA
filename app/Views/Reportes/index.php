<?php

$fecha = $fecha ?? date('Y-m-d');
$periodo = $periodo ?? 'dia';
$rango = $rango ?? [
    'inicio' => $fecha,
    'fin' => $fecha,
    'titulo' => 'Día',
];
$resumen = $resumen ?? [];
$topOperadores = $topOperadores ?? [];
$topMaquinas = $topMaquinas ?? [];
$ultimosEventos = $ultimosEventos ?? [];
?>

<div class="dashboard-page">

    <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>Reportes ejecutivos</h2>
            <p>Resumen por día, semana o mes para supervisión y toma de decisiones.</p>
        </div>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="controller" value="reporte">
                <input type="hidden" name="action" value="index">
                <select name="periodo" class="form-select" style="width: 150px;">
                    <option value="dia" <?= $periodo === 'dia' ? 'selected' : '' ?>>Día</option>
                    <option value="semana" <?= $periodo === 'semana' ? 'selected' : '' ?>>Semana</option>
                    <option value="mes" <?= $periodo === 'mes' ? 'selected' : '' ?>>Mes</option>
                </select>
                <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-control">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>

            <a href="/SIGMA/public/index.php?controller=reporte&action=export&fecha=<?= urlencode($fecha) ?>&periodo=<?= urlencode($periodo) ?>" target="_blank" class="btn btn-outline-dark">
                <i class="bi bi-file-earmark-pdf"></i>
                Exportar PDF
            </a>
        </div>
    </div>

    <div class="mb-3 text-muted">
        <strong>Periodo:</strong> <?= htmlspecialchars($rango["inicio"] ?? $fecha) ?> al <?= htmlspecialchars($rango["fin"] ?? $fecha) ?>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon alerta">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="kpi-info">
                <span>Eventos</span>
                <strong><?= (int) ($resumen["total_eventos"] ?? 0) ?></strong>
                <small><?= htmlspecialchars($rango["titulo"] ?? 'Día') ?></small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon operador">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="kpi-info">
                <span>Turno 1</span>
                <strong><?= (int) ($resumen["turno_1"] ?? 0) ?></strong>
                <small>07:00 - 15:00</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon maquinaria">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="kpi-info">
                <span>Turno 2</span>
                <strong><?= (int) ($resumen["turno_2"] ?? 0) ?></strong>
                <small>15:00 - 23:00</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon critico">
                <i class="bi bi-shield-fill-exclamation"></i>
            </div>
            <div class="kpi-info">
                <span>Críticos</span>
                <strong><?= (int) ($resumen["criticos"] ?? 0) ?></strong>
                <small>Atención inmediata</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-header">
                    <h3>Operadores con más eventos</h3>
                </div>
                <div class="list-stack simple">
                    <?php if (empty($topOperadores)): ?>
                        <p class="text-muted mb-0">Sin datos.</p>
                    <?php else: ?>
                        <?php foreach ($topOperadores as $item): ?>
                            <div class="row-item">
                                <span><?= htmlspecialchars($item["nombre_completo"] ?? "-") ?></span>
                                <strong><?= (int) ($item["total"] ?? 0) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-header">
                    <h3>Máquinas con más incidencias</h3>
                </div>
                <div class="list-stack simple">
                    <?php if (empty($topMaquinas)): ?>
                        <p class="text-muted mb-0">Sin datos.</p>
                    <?php else: ?>
                        <?php foreach ($topMaquinas as $item): ?>
                            <div class="row-item">
                                <span><?= htmlspecialchars($item["nombre_maquina"] ?? "-") ?></span>
                                <strong><?= (int) ($item["total"] ?? 0) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="panel-card">
                <div class="panel-header">
                    <h3>Detalle de eventos recientes</h3>
                </div>
                <div class="table-wrap">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Turno</th>
                                <th>Operador</th>
                                <th>Máquina</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimosEventos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Sin eventos.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ultimosEventos as $evento): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($evento["fecha_evento"] ?? "-") ?></td>
                                        <td><?= htmlspecialchars($evento["hora_evento"] ?? "-") ?></td>
                                        <td><?= htmlspecialchars($evento["turno"] ?? "-") ?></td>
                                        <td><?= htmlspecialchars($evento["operador"] ?? "-") ?></td>
                                        <td><?= htmlspecialchars($evento["maquina"] ?? "-") ?></td>
                                        <td><?= htmlspecialchars($evento["tipo_evento"] ?? "-") ?></td>
                                        <td>
                                            <?php $estado = $evento["estado"] ?? "Pendiente"; ?>
                                            <span class="badge <?= $estado === "Confirmado" ? "bg-success" : "bg-warning text-dark" ?>">
                                                <?= htmlspecialchars($estado) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
