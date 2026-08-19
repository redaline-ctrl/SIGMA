<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/DashboardFilteredModel.php";

class DashboardAdvancedController extends BaseController
{
    private DashboardFilteredModel $dashboard;

    public function __construct()
    {
        global $conexion;

        $this->dashboard = new DashboardFilteredModel($conexion);
    }

    public function index(): void
    {
        $filtros = [
            "fecha" => $this->fecha($_GET["fecha"] ?? ""),
            "desde" => $this->fecha($_GET["desde"] ?? ""),
            "hasta" => $this->fecha($_GET["hasta"] ?? ""),
            "mes" => $this->entero($_GET["mes"] ?? "", 1, 12),
            "anio" => $this->entero($_GET["anio"] ?? "", 2000, 2100),
            "turno" => in_array((string) ($_GET["turno"] ?? ""), ["1", "2", "3"], true) ? (string) $_GET["turno"] : "",
            "operador" => $this->entero($_GET["operador"] ?? "", 1),
            "supervisor" => $this->entero($_GET["supervisor"] ?? "", 1),
        ];

        $operadoresMayorRiesgo = array_slice(
            $this->dashboard->getConductualesPorOperador($filtros),
            0,
            5
        );
        $operadoresMejorDesempeno = $this->dashboard->getDetalleOperadorCompleto($filtros);
        usort($operadoresMejorDesempeno, static function (array $a, array $b): int {
            $conductualesA = (int) ($a["conductuales"] ?? 0);
            $conductualesB = (int) ($b["conductuales"] ?? 0);
            if ($conductualesA !== $conductualesB) {
                return $conductualesA <=> $conductualesB;
            }

            return (int) ($b["total"] ?? 0) <=> (int) ($a["total"] ?? 0);
        });
        $operadoresMejorDesempeno = array_slice($operadoresMejorDesempeno, 0, 5);

        $datosFiltrados = [
            "resumen" => $this->dashboard->resumenGeneral($filtros),
            "totalesClasificacion" => $this->dashboard->getTotalesClasificacion($filtros),
            "comparativaClasificacion" => $this->dashboard->getComparativaConductualRegistrado($filtros),
            "conductualesPorOperador" => $this->dashboard->getConductualesPorOperador($filtros),
            "registradosPorOperador" => $this->dashboard->getRegistradosPorOperador($filtros),
            "detalleOperadorCompleto" => $this->dashboard->getDetalleOperadorCompleto($filtros),
            "detalleConductualPorEtiqueta" => $this->dashboard->getDetalleConductualPorOperadorPorEtiqueta($filtros),
            "eventosPorTipo" => $this->dashboard->eventosPorTipo($filtros),
            "eventosPorEtiqueta" => $this->dashboard->eventosPorEtiqueta($filtros),
            "eventosPorTipoPorOperador" => $this->dashboard->eventosPorTipoPorOperador($filtros),
            "eventosPorTipoEtiquetaPorOperador" => $this->dashboard->eventosPorTipoPorOperador($filtros),
            "eventosPorTurno" => $this->dashboard->getEventosPorTurno($filtros),
            "operadoresMayorRiesgo" => $operadoresMayorRiesgo,
            "operadoresMejorDesempeno" => $operadoresMejorDesempeno,
            "maquinasTop" => $this->dashboard->maquinasConMasEventos($filtros),
            "horasTurno" => $this->dashboard->horasOperativasPorTurno($filtros),
            "eventosPorOperador" => $this->dashboard->eventosPorOperador($filtros),
            "eventosConductuales" => $this->dashboard->getComparativaConductualRegistrado($filtros),
            "operadorCategoria" => $this->dashboard->eventosPorOperadorYCategoria($filtros),
            "eventosConductualesPorOperador" => $this->dashboard->getDetalleConductualPorOperadorPorEtiqueta($filtros),
            "horariosRiesgo" => $this->dashboard->getHorariosMayorRiesgo($filtros),
            "tendenciaSemanal" => $this->dashboard->getTendenciaSemanal($filtros),
            "tendenciaMensual" => $this->dashboard->getTendenciaPorPeriodo($filtros),
            "horaFrecuente" => $this->dashboard->horaMasFrecuente($filtros),
            "horaCritica" => $this->dashboard->horaMasCritica($filtros),
            "operadorCritico" => $this->dashboard->operadorMasCritico($filtros),
            "maquinaCritica" => $this->dashboard->maquinaMasCritica($filtros),
        ];

        $datos = [
            "tituloPagina" => "Dashboard ejecutivo",
            "subtituloPagina" => "Resumen operativo por turno, operador y maquinaria",
            "usuarioActual" => "Administrador",
            "filtros" => $filtros,
            "operadoresFiltro" => $this->dashboard->listarOperadores(),
            "supervisoresFiltro" => $this->dashboard->listarSupervisores(),
        ];

        $datos = array_merge($datos, $datosFiltrados);

        $this->render("Dashboard/advanced", $datos);
    }

    private function fecha(string $valor): string
    {
        if ($valor === "") { return ""; }
        $fecha = DateTime::createFromFormat("!Y-m-d", $valor);
        return $fecha !== false && $fecha->format("Y-m-d") === $valor ? $valor : "";
    }

    private function entero(mixed $valor, int $min, int $max = PHP_INT_MAX): string
    {
        return filter_var($valor, FILTER_VALIDATE_INT, ["options" => ["min_range" => $min, "max_range" => $max]]) !== false ? (string) $valor : "";
    }
}
