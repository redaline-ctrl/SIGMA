<?php $historial = $historial ?? []; ?>

<div class="dashboard-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Historial de reportes</h1>
            <p class="text-muted mb-0">Reportes consultados y exportados por los usuarios.</p>
        </div>
        <a href="<?= htmlspecialchars(app_route("reporte"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">Volver a reportes</a>
    </div>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr><th>Fecha</th><th>Usuario</th><th>Rol</th><th>Periodo</th><th>Rango</th></tr>
            </thead>
            <tbody>
                <?php if (empty($historial)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay reportes en el historial.</td></tr>
                <?php else: ?>
                    <?php foreach ($historial as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item["fecha_generacion"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($item["usuario"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($item["rol"] ?? "-") ?></td>
                            <td><?= htmlspecialchars(ucfirst($item["periodo"] ?? "-")) ?></td>
                            <td><?= htmlspecialchars(($item["fecha_inicio"] ?? "-") . " al " . ($item["fecha_fin"] ?? "-")) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
