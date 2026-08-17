<?php

$eventos = $eventos ?? [];
$filtros = $filtros ?? [];
$supervisores = $supervisores ?? [];
?>

<div class="events-page">

    <div class="page-header">
        <a href="<?= htmlspecialchars(app_route("event", "import"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-primary me-2">
            <i class="bi bi-upload"></i>
            Importar Excel
        </a>
        <a href="<?= htmlspecialchars(app_route("event", "create"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Nuevo evento
        </a>
    </div>

        <?php if (!empty($_GET["eliminados"])): ?>
            <div class="alert alert-success mt-3">
                <?= (int) $_GET["eliminados"] ?> evento(s) eliminado(s) correctamente.
            </div>
        <?php endif; ?>

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
                    <a href="<?= htmlspecialchars(app_route("event"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-wrap">
        <form method="POST" action="<?= htmlspecialchars(app_route("event", "delete"), ENT_QUOTES, "UTF-8") ?>" onsubmit="return confirmarEliminacionEventos();">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="seleccionarTodosEventos">
                    <span class="form-check-label">Seleccionar todos</span>
                </label>
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Eliminar seleccionados</button>
            </div>

        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Sel.</th>
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
                        <td colspan="12" class="text-center text-muted py-4">
                            No hay eventos registrados con esos filtros.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><input class="form-check-input evento-checkbox" type="checkbox" name="ids_evento[]" value="<?= (int) ($evento["id_evento"] ?? 0) ?>"></td>
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
                                <a href="<?= htmlspecialchars(app_route("event", "show", ["id" => (int) ($evento["id_evento"] ?? 0)]), ENT_QUOTES, "UTF-8") ?>" class="btn btn-sm btn-outline-primary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
        </form>
</div>

    <script>
        document.getElementById('seleccionarTodosEventos')?.addEventListener('change', function () {
            document.querySelectorAll('.evento-checkbox').forEach((checkbox) => checkbox.checked = this.checked);
        });

        function confirmarEliminacionEventos() {
            const cantidad = document.querySelectorAll('.evento-checkbox:checked').length;
            if (cantidad === 0) {
                alert('Selecciona al menos un evento.');
                return false;
            }
            return confirm(`¿Eliminar ${cantidad} evento(s) seleccionado(s)? Esta acción no se puede deshacer.`);
        }
    </script>
