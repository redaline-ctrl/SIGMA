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
}
