<?php
$maquinas = $maquinas ?? [];
?>

<div class="page">
    <div class="page-header mb-4">
        <div>
            <h2>Maquinaria</h2>
            <p>Listado de equipos y máquinas del sistema.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre de Máquina</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($maquinas)): ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            No hay máquinas registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($maquinas as $maquina): ?>
                        <tr>
                            <td><?= htmlspecialchars($maquina["nombre_maquina"] ?? "-") ?></td>
                            <td>
                                <span class="badge <?= ($maquina["estado"] ?? 0) == 1 ? "bg-success" : "bg-secondary" ?>">
                                    <?= ($maquina["estado"] ?? 0) == 1 ? "Activa" : "Inactiva" ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
