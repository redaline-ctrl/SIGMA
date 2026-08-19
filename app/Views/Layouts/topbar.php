<header class="sigma-topbar">

    <button type="button" class="btn btn-light d-md-none me-3" id="btnToggleSidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-title">

        <h1>
            <?= htmlspecialchars($tituloPagina ?? "SIGMA") ?>
        </h1>

        <p>
            <?= htmlspecialchars(
                $subtituloPagina
                ?? "Sistema Integral de Gestión y Monitoreo"
            ) ?>
        </p>

    </div>


    <div class="topbar-actions">

        <button
            type="button"
            class="topbar-button"
            title="Notificaciones"
        >
            <i class="bi bi-bell"></i>
        </button>


        <div class="topbar-user">

            <div class="topbar-avatar">

                <i class="bi bi-person-fill"></i>

            </div>


            <div class="topbar-user-data">

                <strong>
                    <?= htmlspecialchars(
                        $usuarioActual ?? "Usuario"
                    ) ?>
                </strong>

                <small>
                    <?= htmlspecialchars(ucfirst($rolActual ?? ($_SESSION["rol"] ?? "usuario")), ENT_QUOTES, "UTF-8") ?>
                </small>

            </div>

        </div>

        <form method="POST" action="<?= htmlspecialchars(app_route("auth", "logout"), ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
            <button type="submit" class="btn btn-outline-secondary btn-sm">Cerrar sesión</button>
        </form>

    </div>

</header>