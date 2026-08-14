<?php
$activeController = $_GET['controller'] ?? 'home';
$activeController = preg_replace('/[^a-zA-Z0-9_-]/', '', $activeController);
?>

<aside class="sigma-sidebar">

    <!-- LOGO -->

    <div class="sidebar-logo">

        <div class="sidebar-logo-text">
            SIGMA
        </div>

        <div class="sidebar-logo-subtitle">
            Sistema Integral de Gestión
        </div>

    </div>


    <!-- MENÚ -->

    <nav class="sidebar-menu">

        <a
            href="/SIGMA/public/index.php?controller=home&action=index"
            class="sidebar-item <?= $activeController === 'home' ? 'active' : '' ?>"
        >

            <i class="bi bi-speedometer2"></i>

            <span>Inicio</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=dashboardAdvanced&action=index" class="sidebar-item <?= $activeController === 'dashboardAdvanced' ? 'active' : '' ?>">

            <i class="bi bi-bar-chart-line-fill"></i>

            <span>Dashboard</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=event&action=index" class="sidebar-item <?= $activeController === 'event' ? 'active' : '' ?>">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <span>Eventos</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=relevo&action=index" class="sidebar-item <?= $activeController === 'relevo' ? 'active' : '' ?>">

            <i class="bi bi-arrow-repeat"></i>

            <span>Relevos</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=operador&action=index" class="sidebar-item <?= $activeController === 'operador' ? 'active' : '' ?>">

            <i class="bi bi-people-fill"></i>

            <span>Operadores</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=maquina&action=index" class="sidebar-item <?= $activeController === 'maquina' ? 'active' : '' ?>">

            <i class="bi bi-truck-front-fill"></i>

            <span>Maquinaria</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=clasificacion&action=index" class="sidebar-item <?= $activeController === 'clasificacion' ? 'active' : '' ?>">

            <i class="bi bi-tags-fill"></i>

            <span>Clasificaciones</span>

        </a>


        <a href="/SIGMA/public/index.php?controller=reporte&action=index" class="sidebar-item <?= $activeController === 'reporte' ? 'active' : '' ?>">

            <i class="bi bi-file-earmark-bar-graph-fill"></i>

            <span>Reportes</span>

        </a>

    </nav>


    <!-- PIE -->

    <div class="sidebar-footer">

        <div class="sidebar-status">

            <span class="status-dot"></span>

            Sistema operativo

        </div>

        <small>
            SIGMA
        </small>

    </div>

</aside>