<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/OperadorModel.php";

class OperadorController extends BaseController
{
    private OperadorModel $model;

    public function __construct()
    {
        global $conexion;
        $this->model = new OperadorModel($conexion);
    }

    public function index(): void
    {
        $operadores = $this->model->listar();

        $datos = [
            "tituloPagina" => "Operadores",
            "subtituloPagina" => "Gestión de operadores del sistema",
            "usuarioActual" => "Administrador",
            "operadores" => $operadores,
        ];

        $this->render("Operadores/index", $datos);
    }

    public function store(): void
    {
        $this->validarCsrf();
        $this->guardarNombre("nombre_completo");
    }

    public function update(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_operador"] ?? 0);
        $nombre = trim((string) ($_POST["nombre_completo"] ?? ""));

        if ($id <= 0 || $nombre === "" || mb_strlen($nombre) > 150) {
            $this->redirigir("El nombre del operador es inválido.");
        }

        if (!$this->model->actualizar($id, $nombre)) {
            $this->redirigir("No se pudo actualizar el operador.");
        }
        $this->redirigir();
    }

    public function toggle(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_operador"] ?? 0);
        $estado = (int) ($_POST["estado"] ?? 0) === 1 ? 0 : 1;

        if ($id <= 0) {
            $this->redirigir("Operador inválido.");
        }

        if (!$this->model->cambiarEstado($id, $estado)) {
            $this->redirigir("No se pudo cambiar el estado del operador.");
        }
        $this->redirigir();
    }

    private function guardarNombre(string $campo): void
    {
        $nombre = trim((string) ($_POST[$campo] ?? ""));

        if ($_SERVER["REQUEST_METHOD"] !== "POST" || $nombre === "" || mb_strlen($nombre) > 150) {
            $this->redirigir("El nombre del operador es inválido.");
        }

        if (!$this->model->crear($nombre)) {
            $this->redirigir("No se pudo crear el operador.");
        }
        $this->redirigir();
    }

    private function redirigir(?string $error = null): never
    {
        $url = $this->route("operador");
        if ($error !== null) {
            $url .= "&error=" . urlencode($error);
        }
        header("Location: " . $url);
        exit;
    }
}
