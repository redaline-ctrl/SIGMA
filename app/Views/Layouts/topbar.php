<header class="sigma-topbar">

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
                        $usuarioActual ?? "Administrador"
                    ) ?>
                </strong>

                <small>
                    Administrador
                </small>

            </div>

        </div>

    </div>

</header>