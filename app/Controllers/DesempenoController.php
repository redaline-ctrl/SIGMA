<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/DesempenoModel.php";

class DesempenoController extends BaseController
{
    private DesempenoModel $model;

    public function __construct()
    {
        global $conexion;
        $this->model = new DesempenoModel($conexion);
    }

    public function index(): void
    {
        $filters = [
            "fecha" => $this->date($_GET["fecha"] ?? ""),
            "desde" => $this->date($_GET["desde"] ?? ""),
            "hasta" => $this->date($_GET["hasta"] ?? ""),
            "mes" => $this->integer($_GET["mes"] ?? "", 1, 12),
            "anio" => $this->integer($_GET["anio"] ?? "", 2000, 2100),
            "turno" => in_array((string) ($_GET["turno"] ?? ""), ["1", "2", "3"], true) ? (string) $_GET["turno"] : "",
            "operador" => $this->integer($_GET["operador"] ?? "", 1),
            "supervisor" => $this->integer($_GET["supervisor"] ?? "", 1),
        ];

        $operadores = $this->model->operadores($filters);
        $supervisores = $this->model->supervisores($filters);

        $this->render("Desempeno/index", [
            "tituloPagina" => "Desempeño",
            "subtituloPagina" => "Análisis histórico normalizado por horas operativas",
            "filters" => $filters,
            "filterOperators" => $this->model->listarOperadores(),
            "filterSupervisors" => $this->model->listarSupervisores(),
            "operadores" => $operadores,
            "supervisores" => $supervisores,
            "mejoresOperadores" => array_slice(array_values(array_filter($operadores, static fn(array $row): bool => $row["score"] !== null)), 0, 3),
            "atencionOperadores" => array_slice(array_reverse(array_values(array_filter($operadores, static fn(array $row): bool => $row["score"] !== null))), 0, 3),
            "mejoresSupervisores" => array_slice(array_values(array_filter($supervisores, static fn(array $row): bool => $row["score"] !== null)), 0, 3),
            "atencionSupervisores" => array_slice(array_reverse(array_values(array_filter($supervisores, static fn(array $row): bool => $row["score"] !== null))), 0, 3),
        ]);
    }

    private function date(string $value): string
    {
        if ($value === "") return "";
        $date = DateTime::createFromFormat("!Y-m-d", $value);
        return $date !== false && $date->format("Y-m-d") === $value ? $value : "";
    }

    private function integer(mixed $value, int $min, int $max = PHP_INT_MAX): string
    {
        return filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => $min, "max_range" => $max]]) !== false ? (string) $value : "";
    }
}
