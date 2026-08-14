<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/ClasificacionModel.php";

class ClasificacionController extends BaseController
{
    private ClasificacionModel $model;

    public function __construct()
    {
        global $conexion;
        $this->model = new ClasificacionModel($conexion);
    }

    public function index(): void
    {
        $clasificaciones = $this->model->listar();

        $datos = [
            "tituloPagina" => "Clasificaciones",
            "subtituloPagina" => "Categorización de eventos",
            "usuarioActual" => "Administrador",
            "clasificaciones" => $clasificaciones,
        ];

        $this->render("Clasificaciones/index", $datos);
    }
}
