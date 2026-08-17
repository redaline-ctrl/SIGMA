<?php

$resultado = $resultado ?? null;
$erroresArchivo = $erroresArchivo ?? [];
$ejemplo = [
    "fecha_evento" => "2026-08-15",
    "hora_evento" => "14:30:00",
    "turno" => "1",
    "fecha_operativa" => "2026-08-15",
    "operador" => "Juan Pérez",
    "maquina" => "Maquina 01",
    "tipo_evento" => "Distracción",
    "etiqueta" => "Fatiga crítica",
    "clasificacion" => "Conductual",
    "estado" => "Pendiente",
    "autorizado" => "0",
    "observaciones" => "Texto libre",
];
?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h4 mb-1">Importar eventos desde Excel</h1>
                <p class="text-muted mb-0">Carga un archivo .xlsx o .csv con encabezados similares a los de ejemplo.</p>
            </div>
            <a href="<?= htmlspecialchars(app_route("event"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">
                Volver a eventos
            </a>
        </div>

        <?php if (!empty($erroresArchivo)): ?>
            <div class="alert alert-danger">
                <?php foreach ($erroresArchivo as $error): ?>
                    <div><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($resultado): ?>
            <div class="alert alert-info">
                <strong>Importación finalizada.</strong>
                Importados: <?= (int) ($resultado["importados"] ?? 0) ?>.
                Errores: <?= count($resultado["errores"] ?? []) ?>.
                Advertencias: <?= count($resultado["advertencias"] ?? []) ?>.
            </div>

            <?php if (!empty($resultado["errores"])): ?>
                <div class="mb-4">
                    <h2 class="h6">Filas rechazadas</h2>
                    <ul class="list-group">
                        <?php foreach ($resultado["errores"] as $errorFila): ?>
                            <li class="list-group-item">
                                Fila <?= (int) ($errorFila["fila"] ?? 0) ?>:
                                <?= htmlspecialchars(implode(" ", $errorFila["errores"] ?? []), ENT_QUOTES, "UTF-8") ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars(app_route("event", "import"), ENT_QUOTES, "UTF-8") ?>" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
            <div class="col-md-8">
                <label class="form-label">Archivo Excel o CSV</label>
                <input type="file" name="archivo" class="form-control" accept=".xlsx,.csv" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Importar ahora</button>
            </div>
        </form>

        <hr class="my-4">

        <h2 class="h6">Encabezados recomendados</h2>
        <p class="text-muted">Puedes usar nombres equivalentes como fecha, hora, operador, máquina, tipo o descripcion.</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <?php foreach (array_keys($ejemplo) as $encabezado): ?>
                            <th><?= htmlspecialchars($encabezado, ENT_QUOTES, "UTF-8") ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php foreach ($ejemplo as $valor): ?>
                            <td><?= htmlspecialchars($valor, ENT_QUOTES, "UTF-8") ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
