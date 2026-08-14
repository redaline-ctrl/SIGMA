<?php

$relevos = $relevos ?? [];
?>

<div class="events-page">

    <div class="page-header">
        <div>
            <h2>Relevos operativos</h2>
            <p>Asignación por turno, supervisor y horas operativas.</p>
        </div>

        <a href="/SIGMA/public/index.php?controller=relevo&action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Nuevo relevo
        </a>
    </div>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Supervisor</th>
                    <th>Operador</th>
                    <th>Máquina</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($relevos)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No hay relevos registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($relevos as $relevo): ?>
                        <tr>
                            <td><?= htmlspecialchars($relevo["fecha_operativa"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($relevo["turno"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($relevo["supervisor"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($relevo["operador"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($relevo["maquina"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($relevo["hora_inicio"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($relevo["hora_fin"] ?? "-") ?></td>
                            <td><?= htmlspecialchars((string) ($relevo["horas_operativas"] ?? "0")) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
