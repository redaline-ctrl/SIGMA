<?php

$evento = $evento ?? [];
$acciones = $acciones ?? [];
$estados = $estados ?? [];
?>

<div class="events-page">
    <div class="page-header mb-4">
        <a href="<?= htmlspecialchars(app_route("event"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Volver
        </a>
    </div>

    <div class="row g-4">
        <!-- Panel Izquierdo: Datos del evento -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1">Evento #<?= (int) ($evento["id_evento"] ?? 0) ?></h4>
                            <small class="text-muted">Registro operacional</small>
                        </div>
                        <span class="badge bg-warning text-dark">
                            <?= htmlspecialchars($evento["estado"] ?? "Pendiente") ?>
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Fecha</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["fecha_evento"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Hora</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["hora_evento"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Turno</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["turno"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Fecha operativa</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["fecha_operativa"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Operador</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["operador"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Supervisor</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["supervisor"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Máquina</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["maquina"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tipo de evento</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["tipo_evento"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Etiqueta</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["etiqueta"] ?? "-") ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Clasificación</label>
                            <div class="fw-semibold"><?= htmlspecialchars($evento["clasificacion"] ?? "-") ?></div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div>
                        <label class="text-muted small">Observaciones</label>
                        <div class="mt-2 p-3 bg-light rounded border">
                            <?= nl2br(htmlspecialchars($evento["observaciones"] ?? "Sin observaciones registradas.")) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de acciones -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Historial de acciones</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($acciones)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                            Sin acciones registradas aún
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($acciones as $accion): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex gap-3">
                                        <div class="timeline-marker bg-primary text-white rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="bi bi-chat-left-text"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong><?= htmlspecialchars($accion["supervisor_nombre"] ?? "Sistema") ?></strong>
                                                    <div class="small text-muted"><?= htmlspecialchars($accion["fecha_accion"] ?? "-") ?></div>
                                                </div>
                                                <?php if ($accion["estado_nuevo"]): ?>
                                                    <span class="badge bg-info">→ <?= htmlspecialchars($accion["estado_nuevo"]) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-2 p-2 bg-light rounded small">
                                                <?= nl2br(htmlspecialchars($accion["descripcion"] ?? "-")) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Evidencia y acciones -->
        <div class="col-lg-4">
            <!-- Evidencia -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Evidencia</h5>

                    <?php
                    $observaciones = (string) ($evento["observaciones"] ?? "");
                    $rutaEvidencia = null;
                    if (preg_match('/Evidencia:\s*(\S+)/i', $observaciones, $coincidencias)) {
                        $rutaEvidencia = $coincidencias[1];
                    }
                    ?>

                    <?php if ($rutaEvidencia): ?>
                        <?php if (preg_match('/\.pdf(?:$|\?)/i', $rutaEvidencia)): ?>
                            <a href="<?= htmlspecialchars($rutaEvidencia, ENT_QUOTES, "UTF-8") ?>" target="_blank" rel="noopener" class="btn btn-outline-danger">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Abrir evidencia PDF
                            </a>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($rutaEvidencia, ENT_QUOTES, "UTF-8") ?>" class="img-fluid rounded border mb-3" alt="Evidencia del evento">
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-muted border rounded p-4 bg-light">
                            <i class="bi bi-image fs-1 d-block mb-2"></i>
                            Sin evidencia asociada
                        </div>
                    <?php endif; ?>

                    <div class="small text-muted mt-3">
                        El sistema conserva la referencia de evidencia dentro del registro del evento.
                    </div>
                </div>
            </div>

            <!-- Formulario de acciones -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Registrar acción</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= htmlspecialchars(app_route("event", "update"), ENT_QUOTES, "UTF-8") ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                        <input type="hidden" name="id_evento" value="<?= (int) ($evento["id_evento"] ?? 0) ?>">

                        <div class="mb-3">
                            <label class="form-label">Nuevo estado</label>
                            <select name="estado" class="form-select">
                                <option value="<?= htmlspecialchars($evento["estado"] ?? "Pendiente") ?>">
                                    <?= htmlspecialchars($evento["estado"] ?? "Pendiente") ?> (Actual)
                                </option>
                                <?php foreach ($estados as $clave => $valor): ?>
                                    <?php if ($clave !== $evento["estado"]): ?>
                                        <option value="<?= htmlspecialchars($clave) ?>">
                                            <?= htmlspecialchars($valor) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Comentario o acción</label>
                            <textarea name="comentario" class="form-control" rows="4" placeholder="Describe la acción tomada, resultado de la investigación, recomendaciones..."></textarea>
                            <small class="text-muted d-block mt-2">Aquí puedes dejar un comentario sobre la investigación, acciones tomadas, o cambios de estado.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i>
                            Guardar acción
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
