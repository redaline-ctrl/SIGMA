<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/MaquinaModel.php";

class MaquinaController extends BaseController
{
    private MaquinaModel $model;

    public function __construct()
    {
        global $conexion;
        $this->model = new MaquinaModel($conexion);
    }

    public function index(): void
    {
        $maquinas = $this->model->listar();

        $datos = [
            "tituloPagina" => "Maquinaria",
            "subtituloPagina" => "Gestión de equipos y máquinas",
            "usuarioActual" => "Administrador",
            "maquinas" => $maquinas,
        ];

        $this->render("Maquinas/index", $datos);
    }

    public function store(): void
    {
        $this->validarCsrf();
        $nombre = trim((string) ($_POST["nombre_maquina"] ?? ""));

        if ($_SERVER["REQUEST_METHOD"] !== "POST" || $nombre === "" || mb_strlen($nombre) > 150) {
            $this->redirigir("El nombre de la máquina es inválido.");
        }

        if (!$this->model->crear($nombre)) {
            $this->redirigir("No se pudo crear la máquina.");
        }
        $this->redirigir();
    }

    public function update(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_maquina"] ?? 0);
        $nombre = trim((string) ($_POST["nombre_maquina"] ?? ""));

        if ($id <= 0 || $nombre === "" || mb_strlen($nombre) > 150) {
            $this->redirigir("El nombre de la máquina es inválido.");
        }

        if (!$this->model->actualizar($id, $nombre)) {
            $this->redirigir("No se pudo actualizar la máquina.");
        }
        $this->redirigir();
    }

    public function toggle(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_maquina"] ?? 0);
        $estado = (int) ($_POST["estado"] ?? 0) === 1 ? 0 : 1;

        if ($id <= 0) {
            $this->redirigir("Máquina inválida.");
        }

        if (!$this->model->cambiarEstado($id, $estado)) {
            $this->redirigir("No se pudo cambiar el estado de la máquina.");
        }
        $this->redirigir();
    }

    private function redirigir(?string $error = null): never
    {
        $url = $this->route("maquina");
        if ($error !== null) {
            $url .= "&error=" . urlencode($error);
        }
        header("Location: " . $url);
        exit;
    }
}
