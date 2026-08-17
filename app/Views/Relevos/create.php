<?php

$supervisores = $supervisores ?? [];
$operadores = $operadores ?? [];
$maquinas = $maquinas ?? [];
$relevo = $relevo ?? [];
$editando = !empty($relevo["id_relevo"]);
?>

<div class="event-form-page">

    <form method="POST" action="<?= htmlspecialchars(app_route("relevo", "store"), ENT_QUOTES, "UTF-8") ?>" class="event-form card shadow-sm border-0">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
        <?php if ($editando): ?><input type="hidden" name="id_relevo" value="<?= (int) $relevo["id_relevo"] ?>"><?php endif; ?>
        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Fecha operativa</label>
                    <input type="date" name="fecha_operativa" class="form-control" value="<?= htmlspecialchars($relevo["fecha_operativa"] ?? date('Y-m-d')) ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Turno</label>
                    <select name="turno" class="form-select" required>
                        <?php foreach (["1" => "1° 07:00 - 15:00", "2" => "2° 15:00 - 23:00", "3" => "3° 23:00 - 07:00"] as $turno => $label): ?>
                            <option value="<?= $turno ?>" <?= (string) ($relevo["turno"] ?? "1") === $turno ? "selected" : "" ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hora inicio</label>
                    <input type="time" name="hora_inicio" class="form-control" value="<?= htmlspecialchars(substr((string) ($relevo["hora_inicio"] ?? "07:00"), 0, 5)) ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hora fin</label>
                    <input type="time" name="hora_fin" class="form-control" value="<?= htmlspecialchars(substr((string) ($relevo["hora_fin"] ?? "15:00"), 0, 5)) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Supervisor</label>
                    <select name="id_supervisor" class="form-select" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($supervisores as $supervisor): ?>
                            <option value="<?= (int) $supervisor["id_supervisor"] ?>" <?= (int) ($relevo["id_supervisor"] ?? 0) === (int) $supervisor["id_supervisor"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($supervisor["nombre_completo"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Operador</label>
                    <select name="id_operador" class="form-select" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($operadores as $operador): ?>
                            <option value="<?= (int) $operador["id_operador"] ?>" <?= (int) ($relevo["id_operador"] ?? 0) === (int) $operador["id_operador"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($operador["nombre_completo"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Máquina</label>
                    <select name="id_maquina" class="form-select" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($maquinas as $maquina): ?>
                            <option value="<?= (int) $maquina["id_maquina"] ?>" <?= (int) ($relevo["id_maquina"] ?? 0) === (int) $maquina["id_maquina"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($maquina["nombre_maquina"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones del relevo, condiciones de operación, comentarios del supervisor..."><?= htmlspecialchars($relevo["observaciones"] ?? "") ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <a href="<?= htmlspecialchars(app_route("relevo"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    <?= $editando ? "Actualizar relevo" : "Guardar relevo" ?>
                </button>
            </div>
        </div>
    </form>
</div>
