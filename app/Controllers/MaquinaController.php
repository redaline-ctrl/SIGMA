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
}
