<?php

$supervisores = $supervisores ?? [];
$operadores = $operadores ?? [];
$maquinas = $maquinas ?? [];
?>

<div class="event-form-page">

    <div class="page-header mb-4">
        <div>
            <h2>Registrar relevo</h2>
            <p>Registro de turno, supervisión y horas operativas.</p>
        </div>
    </div>

    <form method="POST" action="/SIGMA/public/index.php?controller=relevo&action=store" class="event-form card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Fecha operativa</label>
                    <input type="date" name="fecha_operativa" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Turno</label>
                    <select name="turno" class="form-select" required>
                        <option value="1">1° 07:00 - 15:00</option>
                        <option value="2">2° 15:00 - 23:00</option>
                        <option value="3">3° 23:00 - 07:00</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hora inicio</label>
                    <input type="time" name="hora_inicio" class="form-control" value="07:00" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hora fin</label>
                    <input type="time" name="hora_fin" class="form-control" value="15:00" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Supervisor</label>
                    <select name="id_supervisor" class="form-select" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($supervisores as $supervisor): ?>
                            <option value="<?= (int) $supervisor["id_supervisor"] ?>">
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
                            <option value="<?= (int) $operador["id_operador"] ?>">
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
                            <option value="<?= (int) $maquina["id_maquina"] ?>">
                                <?= htmlspecialchars($maquina["nombre_maquina"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones del relevo, condiciones de operación, comentarios del supervisor..."></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <a href="/SIGMA/public/index.php?controller=relevo&action=index" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Guardar relevo
                </button>
            </div>
        </div>
    </form>
</div>
