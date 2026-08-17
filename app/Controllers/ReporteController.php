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
        $fechaObj = DateTime::createFromFormat("!Y-m-d", $fecha);

        if (!$fechaObj || $fechaObj->format("Y-m-d") !== $fecha) {
            http_response_code(400);
            die("La fecha debe tener el formato YYYY-MM-DD.");
        }

        if (!in_array($periodo, ["dia", "semana", "mes"], true)) {
            $periodo = "dia";
        }

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

    public function history(): void
    {
        $this->render("Reportes/history", [
            "tituloPagina" => "Historial de reportes",
            "subtituloPagina" => "Consultas y exportaciones realizadas",
            "historial" => $this->model->historial(),
        ]);
    }

    public function export(): void
    {
        $fecha = $_GET["fecha"] ?? date("Y-m-d");
        $periodo = $_GET["periodo"] ?? "dia";

        $rango = $this->calcularRango($fecha, $periodo);

        $this->model->registrarHistorial([
            "id_usuario" => $_SESSION["id_usuario"] ?? null,
            "usuario" => $_SESSION["nombre_usuario"] ?? $_SESSION["usuario"] ?? "-",
            "rol" => $_SESSION["rol"] ?? "-",
            "fecha_inicio" => $rango["inicio"],
            "fecha_fin" => $rango["fin"],
            "periodo" => $periodo,
        ]);

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
        $relevos = $this->model->relevosPeriodo($rango["inicio"], $rango["fin"]);

        $titulo = "Reporte de Monitoreo de Seguridad y Operación";
        $esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? "-"), ENT_QUOTES, "UTF-8");
        $criticos = (int) ($resumen["criticos"] ?? 0);
        $eventos = (int) ($resumen["total_eventos"] ?? 0);
        $maquinasOperando = count(array_unique(array_filter(array_column($relevos, "maquina"))));

        header("Content-Type: text/html; charset=UTF-8");
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>" . $esc($titulo) . "</title><style>
        *{box-sizing:border-box}body{font-family:Georgia,'Times New Roman',serif;color:#202020;margin:0;padding:28px 46px;font-size:14px}header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #222;padding-bottom:12px}header strong{font-size:18px}header small{font-size:16px;font-weight:bold}.report-title{display:flex;justify-content:space-between;align-items:end;margin:24px 0 20px}.report-title h1{font-size:20px;margin:0}.report-title strong{font-size:16px}.info{border:1px solid #999;padding:12px 16px;margin-bottom:24px}.info-title{font-weight:bold;margin-bottom:8px}.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.section-title{font-size:15px;font-weight:bold;border-bottom:1px solid #222;padding:8px 0 3px;margin-top:20px}.section-title span{margin-right:12px}table{width:100%;border-collapse:collapse;margin-top:8px}th,td{padding:5px 10px;border-bottom:1px solid #9db9e6}th{text-align:center}td:nth-child(2),td:nth-child(3){text-align:center}.stripe{background:#d8e3f7}.note{margin:12px 0 4px}.empty{padding-left:20px}.footer{display:flex;justify-content:space-between;margin-top:54px;font-size:12px}@media print{body{padding:20px 38px}.no-print{display:none!important}}@media(max-width:700px){body{padding:18px}.info-grid{grid-template-columns:1fr}.report-title{display:block}}
        </style></head><body><div class='no-print' style='text-align:right;margin-bottom:12px'><button onclick='window.print()'>Imprimir / Guardar como PDF</button></div>";
        echo "<header><strong>INDUSTRIA SALINERA DE YUCATÁN, S.A. DE C.V.</strong><small>ISYSA</small></header>";
        echo "<div class='report-title'><h1>" . $esc($titulo) . "</h1><strong>" . date("d/m/Y") . "</strong></div>";
        echo "<div class='info'><div class='info-title'>Datos de monitoreo</div><div class='info-grid'><div><b>Fecha:</b> " . $esc($rango["inicio"]) . " al " . $esc($rango["fin"]) . "</div><div><b>Supervisor en Turno:</b> No especificado</div><div><b>Turno:</b> Todos los turnos (reporte " . $esc($rango["titulo"]) . ")</div><div><b>Monitorista:</b> Administrador SIGMA</div></div></div>";
        echo "<div class='section-title'><span>1.</span>RESUMEN DE RENDIMIENTO Y PRODUCTIVIDAD DE LA FLOTA</div><table><thead><tr><th>Indicador Operativo</th><th>Cantidad</th><th>Estado / Observaciones</th></tr></thead><tbody>";
        echo "<tr class='stripe'><td><b>Alertas Reales Confirmadas</b></td><td>" . $criticos . "</td><td>" . ($criticos ? "Requieren atención y seguimiento." : "Ninguna. El periodo concluyó con cero alertas de fatiga o riesgo crítico.") . "</td></tr>";
        echo "<tr><td><b>Alertas Descartadas</b></td><td>0</td><td>No se registran alertas descartadas en este periodo.</td></tr>";
        echo "<tr class='stripe'><td><b>Unidades operando</b></td><td>" . $maquinasOperando . "</td><td>Equipos con relevo registrado.</td></tr>";
        echo "<tr><td><b>Unidades con Fallas Operativas</b></td><td>" . $eventos . "</td><td>Eventos registrados en el periodo.</td></tr></tbody></table>";
        echo "<div class='section-title'><span>2.</span>Reporte detallado de alertas conductuales</div><div class='note'>□ <b>Alertas Reales Confirmadas (Riesgo Crítico):</b></div><div class='empty'>• " . ($criticos ? "Se registraron {$criticos} alertas críticas." : "Ninguna. El periodo concluyó con cero (0) alertas de fatiga o riesgo crítico.") . "</div><div class='note'>□ <b>Alertas Descartadas (Falsos Positivos / Maniobras):</b></div><div class='empty'>• Ninguna alerta descartada registrada.</div>";
        echo "<div class='section-title'><span>3.</span>Control de operadores y tiempos operativos</div><table><thead><tr><th>Maquinaria</th><th>Operador</th><th>Hora Inicio</th><th>Hora Término</th><th>Horas Operativas</th></tr></thead><tbody>";
        if (!$relevos) { echo "<tr><td colspan='5'>Sin relevos registrados para el periodo.</td></tr>"; }
        foreach ($relevos as $indice => $relevo) {
            $clase = $indice % 2 === 0 ? " class='stripe'" : "";
            echo "<tr{$clase}><td><b>" . $esc($relevo["maquina"] ?? "-") . "</b></td><td>" . $esc($relevo["operador"] ?? "-") . "</td><td>" . $esc($relevo["hora_inicio"] ?? "-") . "</td><td>" . $esc($relevo["hora_fin"] ?? "-") . "</td><td>" . number_format((float) ($relevo["horas_operativas"] ?? 0), 2) . "</td></tr>";
        }
        echo "</tbody></table><div class='footer'><span>Generado: " . date("d/m/Y h:i:s a") . "</span><span>Página 1 de 1</span></div></body></html>";
        exit;
    }
}
