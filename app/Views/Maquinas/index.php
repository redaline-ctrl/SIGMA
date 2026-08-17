<?php
$maquinas = $maquinas ?? [];
$error = $_GET["error"] ?? "";
?>

<div class="page">
    <?php if ($error !== ""): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars(app_route("maquina", "store"), ENT_QUOTES, "UTF-8") ?>" class="row g-2 align-items-end mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
        <div class="col-md-8">
            <label for="nombre_maquina" class="form-label">Nueva máquina</label>
            <input id="nombre_maquina" name="nombre_maquina" type="text" maxlength="150" class="form-control" required>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Agregar máquina</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre de Máquina</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($maquinas)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No hay máquinas registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($maquinas as $maquina): ?>
                        <tr>
                            <td>
                                <form method="POST" action="<?= htmlspecialchars(app_route("maquina", "update"), ENT_QUOTES, "UTF-8") ?>" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                                    <input type="hidden" name="id_maquina" value="<?= (int) $maquina["id_maquina"] ?>">
                                    <input name="nombre_maquina" value="<?= htmlspecialchars($maquina["nombre_maquina"] ?? "", ENT_QUOTES, "UTF-8") ?>" maxlength="150" class="form-control form-control-sm" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar cambios"><i class="bi bi-check-lg"></i></button>
                                </form>
                            </td>
                            <td>
                                <span class="badge <?= ($maquina["estado"] ?? 0) == 1 ? "bg-success" : "bg-secondary" ?>">
                                    <?= ($maquina["estado"] ?? 0) == 1 ? "Activa" : "Inactiva" ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?= htmlspecialchars(app_route("maquina", "toggle"), ENT_QUOTES, "UTF-8") ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                                    <input type="hidden" name="id_maquina" value="<?= (int) $maquina["id_maquina"] ?>">
                                    <input type="hidden" name="estado" value="<?= (int) ($maquina["estado"] ?? 0) ?>">
                                    <button type="submit" class="btn btn-sm <?= ($maquina["estado"] ?? 0) == 1 ? "btn-outline-warning" : "btn-outline-success" ?>">
                                        <i class="bi <?= ($maquina["estado"] ?? 0) == 1 ? "bi-pause-circle" : "bi-play-circle" ?>"></i>
                                        <?= ($maquina["estado"] ?? 0) == 1 ? "Desactivar" : "Activar" ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
