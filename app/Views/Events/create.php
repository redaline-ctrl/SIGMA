<?php

$operadores = $operadores ?? [];
$maquinas = $maquinas ?? [];
$tiposEventos = $tiposEventos ?? [];
$etiquetas = $etiquetas ?? [];
$clasificaciones = $clasificaciones ?? [];
?>

<div class="event-form-page">

    <form
        method="POST"
        action="<?= htmlspecialchars(app_route("event", "store"), ENT_QUOTES, "UTF-8") ?>"
        enctype="multipart/form-data"
        class="event-form card shadow-sm border-0"
    >
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
        <div class="card-body p-4">

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha del evento</label>
                    <input type="date" name="fecha_evento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hora</label>
                    <input type="time" name="hora_evento" class="form-control" value="<?= date('H:i') ?>" required>
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
                    <label class="form-label">Fecha operativa</label>
                    <input type="date" name="fecha_operativa" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Operador</label>
                    <select name="id_operador" class="form-select" required>
                        <option value="">Selecciona un operador</option>
                        <?php foreach ($operadores as $operador): ?>
                            <option value="<?= (int) $operador["id_operador"] ?>">
                                <?= htmlspecialchars($operador["nombre_completo"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Máquina</label>
                    <select name="id_maquina" class="form-select" required>
                        <option value="">Selecciona una máquina</option>
                        <?php foreach ($maquinas as $maquina): ?>
                            <option value="<?= (int) $maquina["id_maquina"] ?>">
                                <?= htmlspecialchars($maquina["nombre_maquina"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de evento</label>
                    <select name="id_tipo_evento" class="form-select" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($tiposEventos as $tipo): ?>
                            <option value="<?= (int) $tipo["id_tipo_evento"] ?>">
                                <?= htmlspecialchars($tipo["nombre_evento"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Etiqueta</label>
                    <select name="id_etiqueta" class="form-select">
                        <option value="">Sin etiqueta</option>
                        <?php foreach ($etiquetas as $etiqueta): ?>
                            <option value="<?= (int) $etiqueta["id_etiqueta"] ?>">
                                <?= htmlspecialchars($etiqueta["nombre_etiqueta"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Clasificación</label>
                    <select name="id_clasificacion" class="form-select">
                        <option value="">Sin clasificación</option>
                        <?php foreach ($clasificaciones as $clasificacion): ?>
                            <option value="<?= (int) $clasificacion["id_clasificacion"] ?>">
                                <?= htmlspecialchars($clasificacion["nombre_clasificacion"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="5" placeholder="Ejemplo: Se observa distracción durante operación, luego se identificó un volante suelto; se detuvo la actividad y se notificó al supervisor." required></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Evidencia</label>
                    <input type="file" name="evidencia" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Confirmado">Confirmado</option>
                        <option value="Resuelto">Resuelto</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Autorizado</label>
                    <select name="autorizado" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <a href="<?= htmlspecialchars(app_route("event"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Guardar evento
                </button>
            </div>
        </div>
    </form>
</div>
