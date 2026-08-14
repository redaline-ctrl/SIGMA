<?php

$resumen = $resumen ?? [];
$eventosPorTipo = $eventosPorTipo ?? [];
$eventosPorTurno = $eventosPorTurno ?? [];
$operadoresTop = $operadoresTop ?? [];
$maquinasTop = $maquinasTop ?? [];
$horasTurno = $horasTurno ?? [];

function turnoLabel(string $numero): string
{
    return match ($numero) {
        "1" => "Turno 1",
        "2" => "Turno 2",
        "3" => "Turno 3",
        default => "Sin turno",
    };
}

$tipoLabels = array_map(fn($item) => htmlspecialchars($item["nombre_evento"] ?? "-", ENT_QUOTES, 'UTF-8'), $eventosPorTipo);
$tipoValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $eventosPorTipo);

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

$operadorChartLabels = array_map(fn($item) => htmlspecialchars($item["operador"] ?? "-", ENT_QUOTES, 'UTF-8'), $eventosPorOperador);
$operadorChartValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $eventosPorOperador);

$conducLabels = array_map(fn($item) => htmlspecialchars($item["categoria"] ?? "-", ENT_QUOTES, 'UTF-8'), $eventosConductuales);
$conducValores = array_map(fn($item) => (int) ($item["total"] ?? 0), $eventosConductuales);

$operadorCategoriaLabels = array_map(fn($item) => htmlspecialchars($item["operador"] ?? "-", ENT_QUOTES, 'UTF-8'), $operadorCategoria);
$operadorConductuales = array_map(fn($item) => (int) ($item["conductuales"] ?? 0), $operadorCategoria);
$operadorNoConductuales = array_map(fn($item) => (int) ($item["no_conductuales"] ?? 0), $operadorCategoria);

$detalleConductualPorOperador = [];
foreach ($eventosConductualesPorOperador as $item) {
    $operador = (string) ($item["operador"] ?? "-");
    $etiqueta = (string) ($item["etiqueta"] ?? "-");
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
    $etiqueta = (string) ($item["etiqueta"] ?? "-");
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

$operadoresLabelsGrafico = json_encode($operadoresUnicos, JSON_UNESCAPED_UNICODE);
$datasetsGraficoJson = json_encode($datasetsGrafico, JSON_UNESCAPED_UNICODE);
?>

<div class="dashboard-page">

    <div class="dashboard-header">
        <h2>Dashboard ejecutivo</h2>
        <p>Resumen operativo, turnos, incidentes y productividad.</p>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon alerta">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="kpi-info">
                <span>Total eventos</span>
                <strong><?= (int) ($resumen["total_eventos"] ?? 0) ?></strong>
                <small>Registros en operación</small>
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
                <h3>Eventos conductuales</h3>
                <div class="chart-wrap"><canvas id="conductualChart"></canvas></div>
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
        <div class="col-lg-7">
            <div class="chart-card">
                <h3>Eventos por operador</h3>
                <div class="chart-wrap"><canvas id="operatorEventsChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-5">
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

        const operatorEventsChart = new Chart(document.getElementById('operatorEventsChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($operadorChartLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Eventos',
                    data: <?= json_encode($operadorChartValores) ?>,
                    backgroundColor: '#1D70B8',
                    borderRadius: 8,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
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
                        label: 'No conductuales',
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
    });
</script>
