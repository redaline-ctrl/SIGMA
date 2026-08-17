<div class="container-fluid py-5">
    <div class="row g-4">
        <!-- Header Principal -->
        <div class="col-12">
            <div class="card border-0 shadow-sm sigma-home-hero">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center gap-4">
                        <div>
                            <i class="bi bi-graph-up fs-1 sigma-home-icon"></i>
                        </div>
                        <div>
                            <h1 class="mb-2 sigma-home-title">Bienvenido a SIGMA</h1>
                            <p class="mb-0 sigma-home-subtitle">Sistema Integral de Gestión y Monitoreo de la Salinera</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="col-12">
            <h5 class="mb-3">Accesos rápidos</h5>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="<?= htmlspecialchars(app_route("dashboardAdvanced"), ENT_QUOTES, "UTF-8") ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark transition" style="transition: transform 0.2s;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bar-chart-line-fill fs-1 text-primary mb-3"></i>
                    <h6 class="mb-2">Dashboard Ejecutivo</h6>
                    <p class="small text-muted mb-0">Resumen y KPIs en tiempo real</p>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="<?= htmlspecialchars(app_route("event"), ENT_QUOTES, "UTF-8") ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark transition" style="transition: transform 0.2s;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-warning mb-3"></i>
                    <h6 class="mb-2">Eventos</h6>
                    <p class="small text-muted mb-0">Registro y seguimiento de incidentes</p>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="<?= htmlspecialchars(app_route("relevo"), ENT_QUOTES, "UTF-8") ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark transition" style="transition: transform 0.2s;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-repeat fs-1 text-info mb-3"></i>
                    <h6 class="mb-2">Relevos</h6>
                    <p class="small text-muted mb-0">Control de turnos y supervisión</p>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="<?= htmlspecialchars(app_route("reporte"), ENT_QUOTES, "UTF-8") ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark transition" style="transition: transform 0.2s;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-1 text-success mb-3"></i>
                    <h6 class="mb-2">Reportes</h6>
                    <p class="small text-muted mb-0">Análisis y exportación de datos</p>
                </div>
            </a>
        </div>

        <!-- Información del sistema -->
        <div class="col-12">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle text-primary"></i>
                                Acerca del sistema
                            </h6>
                            <p class="small mb-2">
                                <strong>SIGMA</strong> es un sistema especializado para el monitoreo y gestión operativa de salineras, 
                                permitiendo el registro de eventos, seguimiento de relevos, control supervisado y generación de reportes ejecutivos.
                            </p>
                            <p class="small text-muted mb-0">
                                Desarrollado para optimizar la seguridad y eficiencia operativa.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-lightning-fill text-warning"></i>
                                Características principales
                            </h6>
                            <ul class="small mb-0 ps-4">
                                <li>Monitoreo de incidentes operacionales</li>
                                <li>Gestión de turnos y supervisión</li>
                                <li>Reportes analíticos por período</li>
                                <li>Seguimiento de evidencias</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
