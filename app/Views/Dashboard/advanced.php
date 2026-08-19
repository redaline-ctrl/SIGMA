<?php

$resumen = $resumen ?? [];
$totalesClasificacion = $totalesClasificacion ?? [];
$comparativaClasificacion = $comparativaClasificacion ?? [];
$conductualesPorOperador = $conductualesPorOperador ?? [];
$registradosPorOperador = $registradosPorOperador ?? [];
$detalleOperadorCompleto = $detalleOperadorCompleto ?? [];
$detalleConductualPorEtiqueta = $detalleConductualPorEtiqueta ?? [];
$eventosPorTipo = $eventosPorTipo ?? [];
$eventosPorEtiqueta = $eventosPorEtiqueta ?? [];
$eventosPorTipoPorOperador = $eventosPorTipoPorOperador ?? [];
$eventosPorTurno = $eventosPorTurno ?? [];
$operadoresTop = $operadoresTop ?? [];
$maquinasTop = $maquinasTop ?? [];
$horasTurno = $horasTurno ?? [];
$eventosPorOperador = $eventosPorOperador ?? [];
$eventosConductuales = $eventosConductuales ?? [];
$operadorCategoria = $operadorCategoria ?? [];
$eventosConductualesPorOperador = $eventosConductualesPorOperador ?? [];
$horariosRiesgo = $horariosRiesgo ?? [];
$tendenciaSemanal = $tendenciaSemanal ?? [];
$tendenciaMensual = $tendenciaMensual ?? [];
$horaFrecuente = $horaFrecuente ?? null;
$horaCritica = $horaCritica ?? null;
$operadorCritico = $operadorCritico ?? null;
$maquinaCritica = $maquinaCritica ?? null;
$filtros = $filtros ?? [];
$operadoresFiltro = $operadoresFiltro ?? [];
$supervisoresFiltro = $supervisoresFiltro ?? [];

if (!function_exists('sigmaTurnoLabel')) {
    function sigmaTurnoLabel(string $numero): string
    {
        return match ($numero) {
            "1" => "Turno 1",
            "2" => "Turno 2",
            "3" => "Turno 3",
            default => "Sin turno",
        };
    }
}

if (!function_exists('sigmaNormalizeChartLabel')) {
    function sigmaNormalizeChartLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        $map = [
            'Bostez??' => 'Bostezo',
            'Distracci??n' => 'Distracción',
            'Obstrucci??n de c??mara' => 'Obstrucción de cámara',
            'Desconexi??n de la c??mara' => 'Desconexión de la cámara',
            'Uso del tel??fono confirmado' => 'Uso del teléfono confirmado',
            'Fatiga cr??tica' => 'Fatiga crítica',
            'Bostez�' => 'Bostezo',
            'Distracci�n' => 'Distracción',
            'Obstrucci�n de c�mara' => 'Obstrucción de cámara',
            'Desconexi�n de la c�mara' => 'Desconexión de la cámara',
            'Uso del tel�fono confirmado' => 'Uso del teléfono confirmado',
            'Fatiga cr�tica' => 'Fatiga crítica',
            'DistracciÃ³n' => 'Distracción',
            'ObstrucciÃ³n de cÃ¡mara' => 'Obstrucción de cámara',
            'DesconexiÃ³n de la cÃ¡mara' => 'Desconexión de la cámara',
            'Uso del telÃ©fono confirmado' => 'Uso del teléfono confirmado',
            'Fatiga crÃ­tica' => 'Fatiga crítica',
        ];

        return $map[$value] ?? $value;
    }
}

$tipoLabels = array_map(fn($item) => htmlspecialchars(sigmaNormalizeChartLabel((string) ($item["nombre_evento"] ?? "-")), ENT_QUOTES, 'UTF-8'), $eventosPorTipo);
$tipoValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $eventosPorTipo);

$etiquetaLabels = array_map(fn($item) => htmlspecialchars(sigmaNormalizeChartLabel((string) ($item["etiqueta"] ?? "Sin etiqueta")), ENT_QUOTES, 'UTF-8'), $eventosPorEtiqueta);
$etiquetaValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $eventosPorEtiqueta);

$tiposPorOperador = [];
$operadoresTipoLabels = [];
foreach ($eventosPorTipoPorOperador as $item) {
    $operador = (string) ($item["operador"] ?? "Sin operador");
    $tipo = sigmaNormalizeChartLabel((string) ($item["tipo_evento"] ?? "Sin tipo"));
    $tiposPorOperador[$operador][$tipo] = (int) ($item["total"] ?? 0);
    if (!in_array($operador, $operadoresTipoLabels, true)) {
        $operadoresTipoLabels[] = $operador;
    }
}

$tiposEventoLabels = [];
foreach ($tiposPorOperador as $tipos) {
    foreach (array_keys($tipos) as $tipo) {
        if (!in_array($tipo, $tiposEventoLabels, true)) {
            $tiposEventoLabels[] = $tipo;
        }
    }
}
$tiposPorOperadorDatasets = [];
$coloresTiposEvento = ['#1D70B8', '#00A7A3', '#F59E0B', '#EF4444', '#8B5CF6', '#64748B', '#14B8A6'];
foreach ($tiposEventoLabels as $indice => $tipo) {
    $tiposPorOperadorDatasets[] = [
        'label' => $tipo,
        'data' => array_map(fn($operador) => $tiposPorOperador[$operador][$tipo] ?? 0, $operadoresTipoLabels),
        'backgroundColor' => $coloresTiposEvento[$indice % count($coloresTiposEvento)],
        'borderWidth' => 0,
    ];
}

$turnoPorNombre = [];
foreach (["1", "2", "3"] as $turno) {
    $turnoPorNombre[$turno] = 0;
}
foreach ($eventosPorTurno as $item) {
    $turno = (string) ($item["turno"] ?? "");
    if (isset($turnoPorNombre[$turno])) {
        $turnoPorNombre[$turno] = (int) ($item["total"] ?? 0);
    }
}

$turnoLabels = ["Turno 1", "Turno 2", "Turno 3"];
$turnoValores = [
    (int) ($turnoPorNombre["1"] ?? 0),
    (int) ($turnoPorNombre["2"] ?? 0),
    (int) ($turnoPorNombre["3"] ?? 0),
];

$operadorLabels = array_map(fn($item) => htmlspecialchars($item["nombre_completo"] ?? "-", ENT_QUOTES, 'UTF-8'), $operadoresTop);
$operadorValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $operadoresTop);

$maquinaLabels = array_map(fn($item) => htmlspecialchars($item["nombre_maquina"] ?? "-", ENT_QUOTES, 'UTF-8'), $maquinasTop);
$maquinaValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $maquinasTop);

$horasPorTurno = [];
foreach (["1", "2", "3"] as $turno) {
    $horasPorTurno[$turno] = 0;
}
foreach ($horasTurno as $item) {
    $turno = (string) ($item["turno"] ?? "");
    if (isset($horasPorTurno[$turno])) {
        $horasPorTurno[$turno] = (float) ($item["total_horas"] ?? 0);
    }
}
$horasLabels = ["Turno 1", "Turno 2", "Turno 3"];
$horasValores = [
    (float) ($horasPorTurno["1"] ?? 0),
    (float) ($horasPorTurno["2"] ?? 0),
    (float) ($horasPorTurno["3"] ?? 0),
];

$conducLabels = array_map(function ($item) {
    return htmlspecialchars((string) ($item["clasificacion"] ?? "-"), ENT_QUOTES, 'UTF-8');
}, $comparativaClasificacion);
$conducValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $comparativaClasificacion);

$operadorCategoriaLabels = array_map(fn($item) => htmlspecialchars($item["operador"] ?? "-", ENT_QUOTES, 'UTF-8'), $operadorCategoria);
$operadorConductuales = array_map(fn($item) => (int) ($item["conductuales"] ?? 0), $operadorCategoria);
$operadorNoConductuales = array_map(fn($item) => (int) ($item["no_conductuales"] ?? 0), $operadorCategoria);

$detalleConductualPorOperador = [];
foreach ($eventosConductualesPorOperador as $item) {
    $operador = (string) ($item["operador"] ?? "-");
    $etiqueta = sigmaNormalizeChartLabel((string) ($item["etiqueta"] ?? "-"));
    $cantidad = (int) ($item["total"] ?? 0);

    if (!isset($detalleConductualPorOperador[$operador])) {
        $detalleConductualPorOperador[$operador] = [];
    }

    $detalleConductualPorOperador[$operador][] = [
        "etiqueta" => $etiqueta,
        "total" => $cantidad,
    ];
}

// Procesamiento para gráfico de conductuales por operador
$todasLasEtiquetas = [];
$operadoresConducitosData = [];

foreach ($eventosConductualesPorOperador as $item) {
    $etiqueta = sigmaNormalizeChartLabel((string) ($item["etiqueta"] ?? "-"));
    if (!in_array($etiqueta, $todasLasEtiquetas)) {
        $todasLasEtiquetas[] = $etiqueta;
    }
}

$operadoresUnicos = [];
foreach ($detalleConductualPorOperador as $operador => $items) {
    $operadoresUnicos[] = $operador;
}

// Crear matriz de datos: cada etiqueta es un dataset
$datasetsGrafico = [];
$coloresGrafico = ['#1D70B8', '#5DA7FF', '#00A7A3', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#6366F1', '#14B8A6', '#F97316'];

foreach ($todasLasEtiquetas as $idx => $etiqueta) {
    $data = [];
    foreach ($operadoresUnicos as $operador) {
        $cantidad = 0;
        if (isset($detalleConductualPorOperador[$operador])) {
            foreach ($detalleConductualPorOperador[$operador] as $item) {
                if ($item["etiqueta"] === $etiqueta) {
                    $cantidad = $item["total"];
                    break;
                }
            }
        }
        $data[] = $cantidad;
    }
    
    $datasetsGrafico[] = [
        "label" => $etiqueta,
        "data" => $data,
        "backgroundColor" => $coloresGrafico[$idx % count($coloresGrafico)],
    ];
}

$operadoresLabelsGrafico = json_encode($operadoresUnicos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$datasetsGraficoJson = json_encode($datasetsGrafico, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$horariosRiesgoLabels = array_map(fn($item) => sprintf("%02d:00", (int) ($item["hora"] ?? 0)), $horariosRiesgo);
$horariosRiesgoValores = array_map(fn($item) => (int) ($item["eventos_criticos"] ?? 0), $horariosRiesgo);
$tendenciaSemanalLabels = array_map(fn($item) => "Sem. " . ($item["semana"] ?? "-"), $tendenciaSemanal);
$tendenciaSemanalValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $tendenciaSemanal);
$tendenciaMensualLabels = array_map(fn($item) => $item["periodo_label"] ?? "-", $tendenciaMensual);
$tendenciaMensualValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $tendenciaMensual);

$horaFrecuenteTexto = $horaFrecuente ? sprintf("%02d:00", (int) $horaFrecuente["hora"]) : "Sin datos";
$horaCriticaTexto = $horaCritica ? sprintf("%02d:00", (int) $horaCritica["hora"]) : "Sin datos";
$operadorCriticoTexto = $operadorCritico["nombre"] ?? "Sin datos";
$maquinaCriticaTexto = $maquinaCritica["nombre"] ?? "Sin datos";

$totalesGlobales = (int) ($totalesClasificacion["total"] ?? 0);
$totalesConductuales = (int) ($totalesClasificacion["conductuales"] ?? 0);
$totalesRegistrados = (int) ($totalesClasificacion["registrados"] ?? 0);
$porcentajeConductual = $totalesGlobales > 0 ? round(($totalesConductuales / $totalesGlobales) * 100, 1) : 0;
$porcentajeRegistrado = $totalesGlobales > 0 ? round(($totalesRegistrados / $totalesGlobales) * 100, 1) : 0;
?>

<div class="dashboard-page">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filtros del dashboard</h2>
                    <p class="text-muted mb-0">Todos los indicadores y gráficas usan estos criterios.</p>
                </div>
                <a href="<?= htmlspecialchars(app_route("dashboardAdvanced"), ENT_QUOTES, "UTF-8") ?>" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
            </div>
            <form method="GET" action="<?= htmlspecialchars(app_route("dashboardAdvanced"), ENT_QUOTES, "UTF-8") ?>" class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Fecha específica</label><input type="date" name="fecha" value="<?= htmlspecialchars($filtros["fecha"] ?? "") ?>" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Desde</label><input type="date" name="desde" value="<?= htmlspecialchars($filtros["desde"] ?? "") ?>" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" name="hasta" value="<?= htmlspecialchars($filtros["hasta"] ?? "") ?>" class="form-control"></div>
                <div class="col-md-1"><label class="form-label">Mes</label><select name="mes" class="form-select"><option value="">Todos</option><?php for ($mes = 1; $mes <= 12; $mes++): ?><option value="<?= $mes ?>" <?= (string) ($filtros["mes"] ?? "") === (string) $mes ? "selected" : "" ?>><?= $mes ?></option><?php endfor; ?></select></div>
                <div class="col-md-2"><label class="form-label">Año</label><input type="number" name="anio" min="2000" max="2100" value="<?= htmlspecialchars($filtros["anio"] ?? "") ?>" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Turno</label><select name="turno" class="form-select"><option value="">Todos</option><?php foreach (["1" => "Turno 1", "2" => "Turno 2", "3" => "Turno 3"] as $valor => $texto): ?><option value="<?= $valor ?>" <?= (string) ($filtros["turno"] ?? "") === $valor ? "selected" : "" ?>><?= $texto ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Operador</label><select name="operador" class="form-select"><option value="">Todos</option><?php foreach ($operadoresFiltro as $operador): ?><option value="<?= (int) $operador["id_operador"] ?>" <?= (string) ($filtros["operador"] ?? "") === (string) $operador["id_operador"] ? "selected" : "" ?>><?= htmlspecialchars($operador["nombre_completo"] ?? "-") ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Supervisor</label><select name="supervisor" class="form-select"><option value="">Todos</option><?php foreach ($supervisoresFiltro as $supervisor): ?><option value="<?= (int) $supervisor["id_supervisor"] ?>" <?= (string) ($filtros["supervisor"] ?? "") === (string) $supervisor["id_supervisor"] ? "selected" : "" ?>><?= htmlspecialchars($supervisor["nombre_completo"] ?? "-") ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><button class="btn btn-primary w-100">Aplicar filtros</button></div>
            </form>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon alerta">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="kpi-info">
                <span>Total eventos por clasificación</span>
                <strong><?= $totalesGlobales ?></strong>
                <small><?= $totalesConductuales ?> Conductuales (<?= number_format($porcentajeConductual, 1) ?>%) | <?= $totalesRegistrados ?> Registrados (<?= number_format($porcentajeRegistrado, 1) ?>%)</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon operador">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="kpi-info">
                <span>Operadores</span>
                <strong><?= (int) ($resumen["total_operadores"] ?? 0) ?></strong>
                <small>Activos en el sistema</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon maquinaria">
                <i class="bi bi-truck-front-fill"></i>
            </div>
            <div class="kpi-info">
                <span>Máquinas</span>
                <strong><?= (int) ($resumen["total_maquinas"] ?? 0) ?></strong>
                <small>Equipos monitoreados</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon critico">
                <i class="bi bi-shield-fill-exclamation"></i>
            </div>
            <div class="kpi-info">
                <span>Críticos</span>
                <strong><?= (int) ($resumen["total_criticos"] ?? 0) ?></strong>
                <small>Requieren atención</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon operador"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-info"><span>Horario más frecuente</span><strong><?= htmlspecialchars($horaFrecuenteTexto) ?></strong><small>Mayor volumen de eventos</small></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon critico"><i class="bi bi-alarm"></i></div>
            <div class="kpi-info"><span>Horario más crítico</span><strong><?= htmlspecialchars($horaCriticaTexto) ?></strong><small>Mayor concentración de riesgo</small></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon operador"><i class="bi bi-person-exclamation"></i></div>
            <div class="kpi-info"><span>Operador más crítico</span><strong><?= htmlspecialchars($operadorCriticoTexto) ?></strong><small>Eventos de riesgo</small></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon maquinaria"><i class="bi bi-truck-front"></i></div>
            <div class="kpi-info"><span>Máquina más crítica</span><strong><?= htmlspecialchars($maquinaCriticaTexto) ?></strong><small>Eventos de riesgo</small></div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Row Principal: Análisis de Conductuales -->
        <div class="col-lg-8">
            <div class="chart-card">
                <h3>Conductuales por operador</h3>
                <div class="chart-wrap"><canvas id="operatorCategoryChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="chart-card">
                <h3>Conductual vs Registrado</h3>
                <div class="chart-wrap"><canvas id="conductualChart"></canvas></div>
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card">
                <h3>Resumen por operador (conductuales y registrados)</h3>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Operador</th><th class="text-end">Conductuales</th><th class="text-end">Registrados</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($detalleOperadorCompleto)): ?>
                            <tr><td colspan="4" class="text-muted">Sin datos</td></tr>
                        <?php else: ?>
                            <?php foreach ($detalleOperadorCompleto as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($item["operador"] ?? "-"), ENT_QUOTES, "UTF-8") ?></td>
                                    <td class="text-end fw-semibold"><?= (int) ($item["conductuales"] ?? 0) ?></td>
                                    <td class="text-end fw-semibold"><?= (int) ($item["registrados"] ?? 0) ?></td>
                                    <td class="text-end fw-semibold"><?= (int) ($item["total"] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card">
                <h3>Etiquetas más registradas por tipo de evento</h3>
                <div class="chart-wrap"><canvas id="tagTypeChart"></canvas></div>
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card">
                <h3>Eventos por tipo y operador</h3>
                <div class="chart-wrap large"><canvas id="eventTypeOperatorChart"></canvas></div>
            </div>
        </div>

        <!-- Detalle Conductual: Gráfico visual -->
        <div class="col-12">
            <div class="chart-card">
                <h3>Detalle conductual por operador</h3>
                <div class="chart-wrap large"><canvas id="operadorEtiquetasChart"></canvas></div>
            </div>
        </div>

        <!-- Row Eventos y Máquinas -->
        <div class="col-12">
            <div class="chart-card">
                <h3>Máquinas</h3>
                <div class="chart-wrap"><canvas id="machineChart"></canvas></div>
            </div>
        </div>

        <!-- Row Análisis adicionales -->
        <div class="col-lg-7">
            <div class="chart-card">
                <h3>Eventos por tipo</h3>
                <div class="chart-wrap"><canvas id="eventTypeChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="chart-card">
                <h3>Eventos por turno</h3>
                <div class="chart-wrap"><canvas id="turnoChart"></canvas></div>
            </div>
        </div>

        <!-- Row Final: Top Operadores y Productividad -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3>Top operadores</h3>
                <div class="chart-wrap"><canvas id="operatorChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card">
                <h3>Horas operativas por turno</h3>
                <div class="chart-wrap"><canvas id="hoursChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card">
                <h3>Horarios de mayor riesgo operacional</h3>
                <div class="chart-wrap"><canvas id="riskHoursChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card">
                <h3>Tendencia semanal de eventos conductuales</h3>
                <div class="chart-wrap"><canvas id="weeklyConductualChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card">
                <h3>Tendencia mensual de eventos</h3>
                <div class="chart-wrap"><canvas id="monthlyEventsChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const baseColors = ['#1D70B8', '#5DA7FF', '#00A7A3', '#F59E0B', '#EF4444', '#10B981'];

        const eventTypeChart = new Chart(document.getElementById('eventTypeChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($tipoLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Eventos',
                    data: <?= json_encode($tipoValores) ?>,
                    backgroundColor: baseColors,
                    borderRadius: 8,
                    maxBarThickness: 36,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y} eventos` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        const turnoChart = new Chart(document.getElementById('turnoChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($turnoLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    data: <?= json_encode($turnoValores) ?>,
                    backgroundColor: ['#1D70B8', '#5DA7FF', '#10B981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: { legend: { position: 'bottom' } }
            }
        });

        const conductualChart = new Chart(document.getElementById('conductualChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($conducLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    data: <?= json_encode($conducValores) ?>,
                    backgroundColor: ['#1D70B8', '#10B981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        const tagTypeChart = new Chart(document.getElementById('tagTypeChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($etiquetaLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Eventos por etiqueta',
                    data: <?= json_encode($etiquetaValores) ?>,
                    backgroundColor: '#0EA5E9',
                    borderRadius: 8,
                    maxBarThickness: 30,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { ticks: { autoSkip: false } }
                }
            }
        });

        const eventTypeOperatorChart = new Chart(document.getElementById('eventTypeOperatorChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($operadoresTipoLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: <?= json_encode($tiposPorOperadorDatasets, JSON_UNESCAPED_UNICODE) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                    y: { stacked: true, ticks: { autoSkip: false } }
                },
                plugins: {
                    legend: { position: 'bottom', maxHeight: 50 },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.x} eventos` } }
                }
            }
        });

        const operadorEtiquetasChart = new Chart(document.getElementById('operadorEtiquetasChart'), {
            type: 'bar',
            data: {
                labels: <?= $operadoresLabelsGrafico ?>,
                datasets: <?= $datasetsGraficoJson ?>
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', maxHeight: 50 },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.x}` } }
                },
                scales: {
                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                    y: { stacked: true, ticks: { autoSkip: false } }
                }
            }
        });

        const operatorChart = new Chart(document.getElementById('operatorChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($operadorLabels) ?>,
                datasets: [{
                    label: 'Eventos',
                    data: <?= json_encode($operadorValores) ?>,
                    backgroundColor: '#1D70B8',
                    borderRadius: 8,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { ticks: { autoSkip: false } }
                }
            }
        });

        const operatorCategoryChart = new Chart(document.getElementById('operatorCategoryChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($operadorCategoriaLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [
                    {
                        label: 'Conductuales',
                        data: <?= json_encode($operadorConductuales) ?>,
                        backgroundColor: '#1D70B8',
                        borderRadius: 6
                    },
                    {
                        label: 'Registrados',
                        data: <?= json_encode($operadorNoConductuales) ?>,
                        backgroundColor: '#10B981',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        const machineChart = new Chart(document.getElementById('machineChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($maquinaLabels) ?>,
                datasets: [{
                    label: 'Incidencias',
                    data: <?= json_encode($maquinaValores) ?>,
                    backgroundColor: ['#10B981', '#14B8A6', '#60A5FA', '#F59E0B', '#EF4444'],
                    borderRadius: 8,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        const hoursChart = new Chart(document.getElementById('hoursChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($horasLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Horas',
                    data: <?= json_encode($horasValores) ?>,
                    borderColor: '#1D70B8',
                    backgroundColor: 'rgba(29, 112, 184, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        new Chart(document.getElementById('riskHoursChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($horariosRiesgoLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{ label: 'Eventos críticos', data: <?= json_encode($horariosRiesgoValores) ?>, backgroundColor: '#EF4444', borderRadius: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart(document.getElementById('weeklyConductualChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($tendenciaSemanalLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{ label: 'Eventos conductuales', data: <?= json_encode($tendenciaSemanalValores) ?>, borderColor: '#F59E0B', backgroundColor: 'rgba(245, 158, 11, .14)', fill: true, tension: .3 }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart(document.getElementById('monthlyEventsChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($tendenciaMensualLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{ label: 'Eventos conductuales', data: <?= json_encode($tendenciaMensualValores) ?>, borderColor: '#00A7A3', backgroundColor: 'rgba(0, 167, 163, .14)', fill: true, tension: .3 }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    });
</script>
