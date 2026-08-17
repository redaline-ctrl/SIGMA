<?php
$resultado = $resultado ?? null;
$erroresArchivo = $erroresArchivo ?? [];
$ejemplo = [
    "fecha_operativa" => "2026-07-01",
    "turno" => "1",
    "supervisor" => "Erick Pacheco",
    "operador" => "Juan Pérez",
    "maquina" => "CAT8",
    "hora_inicio" => "07:00:00",
    "hora_fin" => "15:00:00",
    "observaciones" => "Turno normal",
];
?>
<div class="card shadow-sm border-0"><div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h1 class="h4 mb-1">Importar relevos desde Excel</h1><p class="text-muted mb-0">Carga un archivo .xlsx o .csv con una fila por relevo.</p></div><a href="<?= htmlspecialchars(app_route("relevo"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary">Volver a relevos</a></div>
    <?php if (!empty($erroresArchivo)): ?><div class="alert alert-danger"><?php foreach($erroresArchivo as $error): ?><div><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div><?php endforeach; ?></div><?php endif; ?>
    <?php if ($resultado): ?><div class="alert alert-info"><strong>Importación finalizada.</strong> Importados: <?= (int)$resultado["importados"] ?>. Errores: <?= count($resultado["errores"]) ?>. Advertencias: <?= count($resultado["advertencias"]) ?>.</div><?php if (!empty($resultado["errores"])): ?><div class="mb-4"><h2 class="h6">Filas rechazadas</h2><ul class="list-group"><?php foreach($resultado["errores"] as $error): ?><li class="list-group-item">Fila <?= (int)$error["fila"] ?>: <?= htmlspecialchars(implode(" ",$error["errores"]), ENT_QUOTES, "UTF-8") ?></li><?php endforeach; ?></ul></div><?php endif; ?><?php endif; ?>
    <form method="POST" action="<?= htmlspecialchars(app_route("relevo", "import"), ENT_QUOTES, "UTF-8") ?>" enctype="multipart/form-data" class="row g-3"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>"><div class="col-md-8"><label class="form-label">Archivo Excel o CSV</label><input type="file" name="archivo" class="form-control" accept=".xlsx,.csv" required></div><div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Importar ahora</button></div></form>
    <hr class="my-4"><h2 class="h6">Encabezados recomendados</h2><p class="text-muted">Los nombres de supervisor, operador y máquina deben coincidir con los catálogos actuales.</p><div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead><tr><?php foreach(array_keys($ejemplo) as $header): ?><th><?= htmlspecialchars($header) ?></th><?php endforeach; ?></tr></thead><tbody><tr><?php foreach($ejemplo as $value): ?><td><?= htmlspecialchars($value) ?></td><?php endforeach; ?></tr></tbody></table></div>
</div></div>
