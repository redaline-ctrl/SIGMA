<?php
$operadores = $operadores ?? [];
?>

<div class="page">
    <div class="page-header mb-4">
        <div>
            <h2>Operadores</h2>
            <p>Listado de operadores del sistema.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($operadores)): ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            No hay operadores registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($operadores as $operador): ?>
                        <tr>
                            <td><?= htmlspecialchars($operador["nombre_completo"] ?? "-") ?></td>
                            <td>
                                <span class="badge <?= ($operador["estado"] ?? 0) == 1 ? "bg-success" : "bg-secondary" ?>">
                                    <?= ($operador["estado"] ?? 0) == 1 ? "Activo" : "Inactivo" ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
