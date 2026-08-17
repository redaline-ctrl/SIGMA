<?php

$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | SIGMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ink: #17212b; --blue: #1769aa; --paper: #f4f7fa; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; color: var(--ink); background: linear-gradient(135deg, #e8f1f8, var(--paper)); font-family: system-ui, sans-serif; }
        .login-shell { width: min(100%, 420px); background: #fff; border: 1px solid #dce5ec; border-radius: 12px; box-shadow: 0 18px 50px rgba(23, 33, 43, .12); }
        .login-mark { color: var(--blue); font-size: 2rem; font-weight: 800; letter-spacing: .08em; }
        .login-copy { color: #607080; }
    </style>
</head>
<body>
    <main class="login-shell p-4 p-md-5">
        <div class="login-mark mb-2">SIGMA</div>
        <h1 class="h4 mb-2">Acceso al sistema</h1>
        <p class="login-copy mb-4">Ingresa tus credenciales para continuar.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars(app_route("auth", "login"), ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"] ?? "", ENT_QUOTES, "UTF-8") ?>">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input id="usuario" name="usuario" class="form-control" maxlength="50" autocomplete="username" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <input id="password" type="password" name="password" class="form-control" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
        </form>
    </main>
</body>
</html>
