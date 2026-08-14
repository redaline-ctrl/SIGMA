<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($tituloPagina ?? "SIGMA") ?>
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Google Fonts -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- CSS SIGMA -->

    <link
        rel="stylesheet"
        href="/SIGMA/public/assets/css/sidebar.css"
    >
    <link
        rel="stylesheet"
        href="/SIGMA/public/assets/css/topbar.css"
    >
    <link
        rel="stylesheet"
        href="/SIGMA/public/assets/css/dashboard.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }


        html,
        body {

            margin: 0;

            padding: 0;

            min-height: 100%;

            font-family: 'Inter', sans-serif;

            background: #F4F7FA;

        }


        body {

            overflow-x: hidden;

        }


        .sigma-main {

            margin-left: 260px;

            min-height: 100vh;

            background: #F4F7FA;

        }


        .sigma-content {

            padding: 30px;

        }

        .sigma-home-hero {
            background: linear-gradient(135deg, #1D70B8 0%, #0F4A8E 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.20);
            box-shadow: 0 12px 30px rgba(13, 59, 107, 0.28);
        }

        .sigma-home-icon {
            color: #ffffff !important;
            opacity: 1 !important;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
        }

        .sigma-home-title {
            color: #ffffff !important;
            opacity: 1 !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
        }

        .sigma-home-subtitle {
            color: rgba(255, 255, 255, 0.98) !important;
            opacity: 1 !important;
            font-weight: 500;
        }

    </style>

</head>


<body>


    <!-- SIDEBAR -->

    <?php require __DIR__ . "/sidebar.php"; ?>


    <!-- ÁREA PRINCIPAL -->

<div class="sigma-main">

    <?php require __DIR__ . "/topbar.php"; ?>


    <main class="sigma-content">

        <?= $contenido ?? "" ?>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

</body>

</html>