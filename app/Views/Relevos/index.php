<?php

$relevos = $relevos ?? [];
$rolUsuario = strtolower((string) ($_SESSION["rol"] ?? ""));
$puedeEscribir = in_array($rolUsuario, ["administrador", "supervisor"], true);
?>

<div class="events-page">

    <?php if ($puedeEscribir || $rolUsuario === "monitorista"): ?>
    <div class="page-header">
        <a href="<?= htmlspecialchars(app_route("relevo", "import"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-primary me-2">
            <i class="bi bi-upload"></i> Importar Excel
        </a>
        <a href="<?= htmlspecialchars(app_route("relevo", "create"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Nuevo relevo
        </a>
    </div>
    <?php endif; ?>

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
                    <?php if ($puedeEscribir): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($relevos)): ?>
                    <tr>
                        <td colspan="<?= $puedeEscribir ? 9 : 8 ?>" class="text-center text-muted py-4">
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
                            <?php if ($puedeEscribir): ?><td class="d-flex gap-2">
                                <a href="<?= htmlspecialchars(app_route("relevo", "edit", ["id" => (int) $relevo["id_relevo"]]), ENT_QUOTES, "UTF-8") ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form method="POST" action="<?= htmlspecialchars(app_route("relevo", "delete"), ENT_QUOTES, "UTF-8") ?>" onsubmit="return confirm('¿Eliminar este relevo?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                                    <input type="hidden" name="id_relevo" value="<?= (int) $relevo["id_relevo"] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
