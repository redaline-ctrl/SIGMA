<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/DashboardModel.php";


class DashboardController extends BaseController
{
    private DashboardModel $dashboard;


    public function __construct()
    {
        global $conexion;

        $this->dashboard = new DashboardModel($conexion);
    }


    /**
     * Dashboard principal.
     */
    public function index(): void
    {
        /*
         * Consultamos los KPIs.
         */
        $totalEventos =
            $this->dashboard->totalEventos();

        $totalOperadores =
            $this->dashboard->totalOperadores();

        $totalMaquinas =
            $this->dashboard->totalMaquinas();

        $totalCriticos =
            $this->dashboard->totalCriticos();


        /*
         * Datos que recibe la vista.
         */
        $datos = [

            "tituloPagina" =>
                "Dashboard",

            "subtituloPagina" =>
                "Resumen general del sistema",

            "usuarioActual" =>
                "Administrador",

            "totalEventos" =>
                $totalEventos,

            "totalOperadores" =>
                $totalOperadores,

            "totalMaquinas" =>
                $totalMaquinas,

            "totalCriticos" =>
                $totalCriticos

        ];


        /*
         * Renderizamos Dashboard/index.php
         * dentro del layout principal.
         */
        $this->render(
            "Dashboard/index",
            $datos
        );
    }
}