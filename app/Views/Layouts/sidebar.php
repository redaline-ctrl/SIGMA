<?php
$activeController = $_GET['controller'] ?? 'home';
$activeController = preg_replace('/[^a-zA-Z0-9_-]/', '', $activeController);
$rolUsuario = strtolower((string) ($_SESSION["rol"] ?? ""));
$soloConsulta = in_array($rolUsuario, ["gerente", "rh"], true);
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
            href="<?= htmlspecialchars(app_route("home"), ENT_QUOTES, "UTF-8") ?>"
            class="sidebar-item <?= $activeController === 'home' ? 'active' : '' ?>"
        >

            <i class="bi bi-speedometer2"></i>

            <span>Inicio</span>

        </a>


        <a href="<?= htmlspecialchars(app_route("dashboardAdvanced"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'dashboardAdvanced' ? 'active' : '' ?>">

            <i class="bi bi-bar-chart-line-fill"></i>

            <span>Dashboard</span>

        </a>

        <a href="<?= htmlspecialchars(app_route("desempeno"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'desempeno' ? 'active' : '' ?>">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Desempeño</span>
        </a>


        <?php if (!$soloConsulta): ?><a href="<?= htmlspecialchars(app_route("event"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'event' ? 'active' : '' ?>">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <span>Eventos</span>

        </a><?php endif; ?>


        <?php if (!$soloConsulta): ?><a href="<?= htmlspecialchars(app_route("relevo"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'relevo' ? 'active' : '' ?>">

            <i class="bi bi-arrow-repeat"></i>

            <span>Relevos</span>

        </a><?php endif; ?>


        <?php if (!$soloConsulta): ?><a href="<?= htmlspecialchars(app_route("operador"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'operador' ? 'active' : '' ?>">

            <i class="bi bi-people-fill"></i>

            <span>Operadores</span>

        </a><?php endif; ?>


        <?php if (!$soloConsulta): ?><a href="<?= htmlspecialchars(app_route("maquina"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'maquina' ? 'active' : '' ?>">

            <i class="bi bi-truck-front-fill"></i>

            <span>Maquinaria</span>

        </a><?php endif; ?>


        <?php if (!$soloConsulta): ?><a href="<?= htmlspecialchars(app_route("clasificacion"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'clasificacion' ? 'active' : '' ?>">

            <i class="bi bi-tags-fill"></i>

            <span>Clasificaciones</span>

        </a><?php endif; ?>


        <a href="<?= htmlspecialchars(app_route("reporte"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'reporte' ? 'active' : '' ?>">

            <i class="bi bi-file-earmark-bar-graph-fill"></i>

            <span>Reportes</span>

        </a>

        <?php if ($rolUsuario === "administrador"): ?>
        <a href="<?= htmlspecialchars(app_route("usuario"), ENT_QUOTES, "UTF-8") ?>" class="sidebar-item <?= $activeController === 'usuario' ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i>
            <span>Usuarios</span>
        </a>
        <?php endif; ?>

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