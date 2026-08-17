<?php
$operadores = $operadores ?? [];
$error = $_GET["error"] ?? "";
?>

<div class="page">
    <?php if ($error !== ""): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars(app_route("operador", "store"), ENT_QUOTES, "UTF-8") ?>" class="row g-2 align-items-end mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
        <div class="col-md-8">
            <label for="nombre_completo" class="form-label">Nuevo operador</label>
            <input id="nombre_completo" name="nombre_completo" type="text" maxlength="150" class="form-control" required>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Agregar operador</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($operadores)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No hay operadores registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($operadores as $operador): ?>
                        <tr>
                            <td>
                                <form method="POST" action="<?= htmlspecialchars(app_route("operador", "update"), ENT_QUOTES, "UTF-8") ?>" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                                    <input type="hidden" name="id_operador" value="<?= (int) $operador["id_operador"] ?>">
                                    <input name="nombre_completo" value="<?= htmlspecialchars($operador["nombre_completo"] ?? "", ENT_QUOTES, "UTF-8") ?>" maxlength="150" class="form-control form-control-sm" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar cambios"><i class="bi bi-check-lg"></i></button>
                                </form>
                            </td>
                            <td>
                                <span class="badge <?= ($operador["estado"] ?? 0) == 1 ? "bg-success" : "bg-secondary" ?>">
                                    <?= ($operador["estado"] ?? 0) == 1 ? "Activo" : "Inactivo" ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?= htmlspecialchars(app_route("operador", "toggle"), ENT_QUOTES, "UTF-8") ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                                    <input type="hidden" name="id_operador" value="<?= (int) $operador["id_operador"] ?>">
                                    <input type="hidden" name="estado" value="<?= (int) ($operador["estado"] ?? 0) ?>">
                                    <button type="submit" class="btn btn-sm <?= ($operador["estado"] ?? 0) == 1 ? "btn-outline-warning" : "btn-outline-success" ?>">
                                        <i class="bi <?= ($operador["estado"] ?? 0) == 1 ? "bi-pause-circle" : "bi-play-circle" ?>"></i>
                                        <?= ($operador["estado"] ?? 0) == 1 ? "Desactivar" : "Activar" ?>
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
