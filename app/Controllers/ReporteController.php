<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/ReporteModel.php";

class ReporteController extends BaseController
{
    private ReporteModel $model;

    public function __construct()
    {
        global $conexion;

        $this->model = new ReporteModel($conexion);
    }

    private function calcularRango(string $fecha, string $periodo): array
    {
        $fechaObj = new DateTime($fecha);

        if ($periodo === "semana") {
            $inicio = clone $fechaObj;
            $inicio->modify("monday this week");
            $fin = clone $inicio;
            $fin->modify("+6 days");

            return [
                "inicio" => $inicio->format("Y-m-d"),
                "fin" => $fin->format("Y-m-d"),
                "titulo" => "Semana",
            ];
        }

        if ($periodo === "mes") {
            $inicio = new DateTime($fechaObj->format("Y-m-01"));
            $fin = new DateTime($fechaObj->format("Y-m-t"));

            return [
                "inicio" => $inicio->format("Y-m-d"),
                "fin" => $fin->format("Y-m-d"),
                "titulo" => "Mes",
            ];
        }

        return [
            "inicio" => $fechaObj->format("Y-m-d"),
            "fin" => $fechaObj->format("Y-m-d"),
            "titulo" => "Día",
        ];
    }

    public function index(): void
    {
        $fecha = $_GET["fecha"] ?? date("Y-m-d");
        $periodo = $_GET["periodo"] ?? "dia";

        $rango = $this->calcularRango($fecha, $periodo);

        $resumen = $this->model->resumenDiario($fecha);

        if ($periodo === "semana") {
            $resumen = $this->model->resumenSemanal($fecha);
        } elseif ($periodo === "mes") {
            $resumen = $this->model->resumenMensual($fecha);
        }

        $topOperadores = $this->model->topOperadores($fecha);
        $topMaquinas = $this->model->topMaquinas($fecha);

        if ($periodo !== "dia") {
            $topOperadores = $this->model->topOperadoresPeriodo($rango["inicio"], $rango["fin"]);
            $topMaquinas = $this->model->topMaquinasPeriodo($rango["inicio"], $rango["fin"]);
        }

        $ultimosEventos = $this->model->eventosRecientesPeriodo($rango["inicio"], $rango["fin"], 12);

        $datos = [
            "tituloPagina" => "Reportes",
            "subtituloPagina" => "Resumen operativo y exportación de información",
            "usuarioActual" => "Administrador",
            "fecha" => $fecha,
            "periodo" => $periodo,
            "rango" => $rango,
            "resumen" => $resumen,
            "topOperadores" => $topOperadores,
            "topMaquinas" => $topMaquinas,
            "ultimosEventos" => $ultimosEventos,
        ];

        $this->render("Reportes/index", $datos);
    }

    public function export(): void
    {
        $fecha = $_GET["fecha"] ?? date("Y-m-d");
        $periodo = $_GET["periodo"] ?? "dia";

        $rango = $this->calcularRango($fecha, $periodo);

        $resumen = $this->model->resumenDiario($fecha);
        if ($periodo === "semana") {
            $resumen = $this->model->resumenSemanal($fecha);
        } elseif ($periodo === "mes") {
            $resumen = $this->model->resumenMensual($fecha);
        }

        $topOperadores = $this->model->topOperadores($fecha);
        $topMaquinas = $this->model->topMaquinas($fecha);

        if ($periodo !== "dia") {
            $topOperadores = $this->model->topOperadoresPeriodo($rango["inicio"], $rango["fin"]);
            $topMaquinas = $this->model->topMaquinasPeriodo($rango["inicio"], $rango["fin"]);
        }

        $ultimosEventos = $this->model->eventosRecientesPeriodo($rango["inicio"], $rango["fin"], 10);

        $titulo = "Reporte SIGMA - " . ucfirst($rango["titulo"]) . " " . $fecha;

        header("Content-Type: text/html; charset=UTF-8");
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>" . htmlspecialchars($titulo) . "</title><style>body{font-family:Arial,sans-serif;padding:30px;color:#1e293b;}h1{margin:0 0 10px;} .box{border:1px solid #dfe7f1;border-radius:12px;padding:16px;margin-bottom:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #dfe7f1;padding:8px;text-align:left;} .kpis{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;} .kpi{border:1px solid #dfe7f1;border-radius:12px;padding:12px 16px;min-width:150px;} @media print{body{padding:0;} .no-print{display:none;}}</style></head><body>";
        echo "<div class='no-print' style='margin-bottom:20px;'><button onclick='window.print()'>Imprimir / Guardar como PDF</button></div>";
        echo "<h1>" . htmlspecialchars($titulo) . "</h1>";
        echo "<p>Periodo: " . htmlspecialchars($rango["inicio"]) . " al " . htmlspecialchars($rango["fin"]) . "</p>";
        echo "<div class='kpis'>";
        echo "<div class='kpi'><strong>Eventos</strong><br>" . (int) ($resumen["total_eventos"] ?? 0) . "</div>";
        echo "<div class='kpi'><strong>Turno 1</strong><br>" . (int) ($resumen["turno_1"] ?? 0) . "</div>";
        echo "<div class='kpi'><strong>Turno 2</strong><br>" . (int) ($resumen["turno_2"] ?? 0) . "</div>";
        echo "<div class='kpi'><strong>Críticos</strong><br>" . (int) ($resumen["criticos"] ?? 0) . "</div>";
        echo "</div>";

        echo "<div class='box'><h3>Top operadores</h3><table><tr><th>Operador</th><th>Total</th></tr>";
        foreach ($topOperadores as $item) {
            echo "<tr><td>" . htmlspecialchars($item["nombre_completo"] ?? "-") . "</td><td>" . (int) ($item["total"] ?? 0) . "</td></tr>";
        }
        echo "</table></div>";

        echo "<div class='box'><h3>Top máquinas</h3><table><tr><th>Máquina</th><th>Total</th></tr>";
        foreach ($topMaquinas as $item) {
            echo "<tr><td>" . htmlspecialchars($item["nombre_maquina"] ?? "-") . "</td><td>" . (int) ($item["total"] ?? 0) . "</td></tr>";
        }
        echo "</table></div>";

        echo "<div class='box'><h3>Eventos recientes</h3><table><tr><th>Fecha</th><th>Hora</th><th>Turno</th><th>Operador</th><th>Máquina</th><th>Tipo</th></tr>";
        foreach ($ultimosEventos as $evento) {
            echo "<tr><td>" . htmlspecialchars($evento["fecha_evento"] ?? "-") . "</td><td>" . htmlspecialchars($evento["hora_evento"] ?? "-") . "</td><td>" . htmlspecialchars($evento["turno"] ?? "-") . "</td><td>" . htmlspecialchars($evento["operador"] ?? "-") . "</td><td>" . htmlspecialchars($evento["maquina"] ?? "-") . "</td><td>" . htmlspecialchars($evento["tipo_evento"] ?? "-") . "</td></tr>";
        }
        echo "</table></div>";
        echo "</body></html>";
        exit;
    }
}
