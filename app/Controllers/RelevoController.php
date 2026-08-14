<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/RelevoModel.php";

class RelevoController extends BaseController
{
    private RelevoModel $model;

    public function __construct()
    {
        global $conexion;

        $this->model = new RelevoModel($conexion);
    }

    public function index(): void
    {
        $relevos = $this->model->listarRelevos();

        $datos = [
            "tituloPagina" => "Relevos",
            "subtituloPagina" => "Control de turnos y horas operativas",
            "usuarioActual" => "Administrador",
            "relevos" => $relevos,
        ];

        $this->render("Relevos/index", $datos);
    }

    public function create(): void
    {
        $datos = [
            "tituloPagina" => "Nuevo relevo",
            "subtituloPagina" => "Registrar turno operativo y horas asignadas",
            "usuarioActual" => "Administrador",
            "supervisores" => $this->model->listarSupervisores(),
            "operadores" => $this->model->listarOperadores(),
            "maquinas" => $this->model->listarMaquinas(),
        ];

        $this->render("Relevos/create", $datos);
    }

    public function store(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /SIGMA/public/index.php?controller=relevo&action=create");
            return;
        }

        $payload = [
            "fecha_operativa" => $_POST["fecha_operativa"] ?? date("Y-m-d"),
            "turno" => $_POST["turno"] ?? "1",
            "id_supervisor" => $_POST["id_supervisor"] ?? null,
            "id_operador" => $_POST["id_operador"] ?? null,
            "id_maquina" => $_POST["id_maquina"] ?? null,
            "hora_inicio" => $_POST["hora_inicio"] ?? "07:00:00",
            "hora_fin" => $_POST["hora_fin"] ?? "15:00:00",
            "observaciones" => $_POST["observaciones"] ?? "",
        ];

        $ok = $this->model->guardar($payload);

        if ($ok) {
            header("Location: /SIGMA/public/index.php?controller=relevo&action=index");
            exit;
        }

        http_response_code(500);
        die("No se pudo guardar el relevo.");
    }
}
