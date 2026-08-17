<?php
$clasificaciones = $clasificaciones ?? [];
?>

<div class="page">
    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Clasificación</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clasificaciones)): ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            No hay clasificaciones registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clasificaciones as $clasificacion): ?>
                        <tr>
                            <td><?= htmlspecialchars($clasificacion["nombre_clasificacion"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($clasificacion["descripcion"] ?? "-") ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
