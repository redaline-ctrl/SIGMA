<?php

require_once __DIR__ . "/BaseController.php";

class HomeController extends BaseController
{
    public function index(): void
    {
        $datos = [

            "tituloPagina" =>
                "Inicio",

            "subtituloPagina" =>
                "Sistema Integral de Gestión y Monitoreo",

            "usuarioActual" =>
                "Administrador"

        ];

        $this->render(
            "Home/index",
            $datos
        );
    }
}