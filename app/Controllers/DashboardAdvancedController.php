<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/DashboardAdvancedModel.php";

class DashboardAdvancedController extends BaseController
{
    private DashboardAdvancedModel $dashboard;

    public function __construct()
    {
        global $conexion;

        $this->dashboard = new DashboardAdvancedModel($conexion);
    }

    public function index(): void
    {
        $resumen = $this->dashboard->resumenGeneral();
        $eventosPorTipo = $this->dashboard->eventosPorTipo();
        $eventosPorTurno = $this->dashboard->eventosPorTurno();
        $operadoresTop = $this->dashboard->operadoresConMasEventos();
        $maquinasTop = $this->dashboard->maquinasConMasEventos();
        $horasTurno = $this->dashboard->horasOperativasPorTurno();
        $eventosPorOperador = $this->dashboard->eventosPorOperador();
        $eventosConductuales = $this->dashboard->eventosConductualesResumen();
        $operadorCategoria = $this->dashboard->eventosPorOperadorYCategoria();
        $eventosConductualesPorOperador = $this->dashboard->eventosConductualesPorOperador();

        $datos = [
            "tituloPagina" => "Dashboard ejecutivo",
            "subtituloPagina" => "Resumen operativo por turno, operador y maquinaria",
            "usuarioActual" => "Administrador",
            "resumen" => $resumen,
            "eventosPorTipo" => $eventosPorTipo,
            "eventosPorTurno" => $eventosPorTurno,
            "operadoresTop" => $operadoresTop,
            "maquinasTop" => $maquinasTop,
            "horasTurno" => $horasTurno,
            "eventosPorOperador" => $eventosPorOperador,
            "eventosConductuales" => $eventosConductuales,
            "operadorCategoria" => $operadorCategoria,
            "eventosConductualesPorOperador" => $eventosConductualesPorOperador,
        ];

        $this->render("Dashboard/advanced", $datos);
    }
}
