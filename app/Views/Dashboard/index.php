<?php

/*
|--------------------------------------------------------------------------
| Valores recibidos desde DashboardController
|--------------------------------------------------------------------------
|
| Estas variables normalmente vienen desde:
|
| DashboardController
|       ↓
| BaseController::render()
|       ↓
| extract($datos)
|
| Las declaramos aquí para que:
| 1. La vista nunca tenga variables indefinidas.
| 2. Intelephense deje de marcarlas como errores.
|
*/

$totalEventos = $totalEventos ?? 0;
$totalOperadores = $totalOperadores ?? 0;
$totalMaquinas = $totalMaquinas ?? 0;
$totalCriticos = $totalCriticos ?? 0;

?>

<div class="dashboard-page">


    <!--=========================================
        ENCABEZADO
    ==========================================-->

    <div class="dashboard-header">

        <h2>
            Dashboard
        </h2>

        <p>
            Resumen general de actividad de SIGMA
        </p>

    </div>


    <!--=========================================
        KPIs
    ==========================================-->

    <div class="kpi-grid">


        <!--=====================================
            TOTAL EVENTOS
        ======================================-->

        <div class="kpi-card">

            <div class="kpi-icon alerta">

                <i class="bi bi-exclamation-triangle-fill"></i>

            </div>


            <div class="kpi-info">

                <span>
                    Total de Eventos
                </span>

                <strong>
                    <?= (int) $totalEventos ?>
                </strong>

                <small>
                    Eventos registrados
                </small>

            </div>

        </div>


        <!--=====================================
            OPERADORES
        ======================================-->

        <div class="kpi-card">

            <div class="kpi-icon operador">

                <i class="bi bi-people-fill"></i>

            </div>


            <div class="kpi-info">

                <span>
                    Operadores
                </span>

                <strong>
                    <?= (int) $totalOperadores ?>
                </strong>

                <small>
                    Operadores activos
                </small>

            </div>

        </div>


        <!--=====================================
            MAQUINARIA
        ======================================-->

        <div class="kpi-card">

            <div class="kpi-icon maquinaria">

                <i class="bi bi-truck-front-fill"></i>

            </div>


            <div class="kpi-info">

                <span>
                    Maquinaria
                </span>

                <strong>
                    <?= (int) $totalMaquinas ?>
                </strong>

                <small>
                    Equipos activos
                </small>

            </div>

        </div>


        <!--=====================================
            EVENTOS CRÍTICOS
        ======================================-->

        <div class="kpi-card">

            <div class="kpi-icon critico">

                <i class="bi bi-shield-fill-exclamation"></i>

            </div>


            <div class="kpi-info">

                <span>
                    Eventos Críticos
                </span>

                <strong>
                    <?= (int) $totalCriticos ?>
                </strong>

                <small>
                    Requieren atención
                </small>

            </div>

        </div>


    </div>

</div>