<?php

$eventos = $eventos ?? [];
$filtros = $filtros ?? [];
$supervisores = $supervisores ?? [];
?>

<div class="events-page">

    <div class="page-header">
        <div>
            <h2>Eventos operativos</h2>
            <p>Monitoreo, clasificación y evidencias por turno.</p>
        </div>

        <a href="/SIGMA/public/index.php?controller=event&action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Nuevo evento
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="controller" value="event">
                <input type="hidden" name="action" value="index">

                <div class="col-md-3">
                    <label class="form-label">Turno</label>
                    <select name="turno" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?= (($filtros["turno"] ?? "") === "1") ? "selected" : "" ?>>Turno 1</option>
                        <option value="2" <?= (($filtros["turno"] ?? "") === "2") ? "selected" : "" ?>>Turno 2</option>
                        <option value="3" <?= (($filtros["turno"] ?? "") === "3") ? "selected" : "" ?>>Turno 3</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Supervisor</label>
                    <select name="supervisor" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($supervisores as $supervisor): ?>
                            <option value="<?= (int) ($supervisor["id_supervisor"] ?? 0) ?>"
                                <?= ((string) ($filtros["supervisor"] ?? "") === (string) ($supervisor["id_supervisor"] ?? "")) ? "selected" : "" ?>
                            >
                                <?= htmlspecialchars($supervisor["nombre_completo"] ?? "-") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($filtros["fecha_inicio"] ?? "") ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($filtros["fecha_fin"] ?? "") ?>">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    <a href="/SIGMA/public/index.php?controller=event&action=index" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Turno</th>
                    <th>Operador</th>
                    <th>Supervisor</th>
                    <th>Máquina</th>
                    <th>Tipo</th>
                    <th>Etiqueta</th>
                    <th>Clasificación</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($eventos)): ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            No hay eventos registrados con esos filtros.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><?= htmlspecialchars($evento["fecha_evento"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["hora_evento"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["turno"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["operador"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["supervisor"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["maquina"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["tipo_evento"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["etiqueta"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($evento["clasificacion"] ?? "-") ?></td>
                            <td>
                                <span class="badge <?= ($evento["estado"] ?? "Pendiente") === "Confirmado" ? "bg-success" : "bg-warning text-dark" ?>">
                                    <?= htmlspecialchars($evento["estado"] ?? "Pendiente") ?>
                                </span>
                            </td>
                            <td>
                                <a href="/SIGMA/public/index.php?controller=event&action=show&id=<?= (int) ($evento["id_evento"] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
