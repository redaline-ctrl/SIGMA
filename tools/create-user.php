<?php

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/Models/UsuarioModel.php";

$usuario = trim((string) ($argv[1] ?? ""));
$nombre = trim((string) ($argv[2] ?? ""));
$rol = strtolower(trim((string) ($argv[3] ?? "administrador")));

if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $usuario) || $nombre === "" || mb_strlen($nombre) > 150) {
    fwrite(STDERR, "Uso: php tools/create-user.php usuario \"Nombre completo\" [rol]\n");
    exit(1);
}

if (!in_array($rol, ["administrador", "supervisor", "monitorista", "gerente", "rh", "usuario"], true)) {
    fwrite(STDERR, "Rol no válido. Usa administrador, supervisor, monitorista, gerente, rh o usuario.\n");
    exit(1);
}

fwrite(STDOUT, "Contraseña: ");
$password = trim((string) fgets(STDIN));

if (strlen($password) < 8) {
    fwrite(STDERR, "La contraseña debe tener al menos 8 caracteres.\n");
    exit(1);
}

$model = new UsuarioModel($conexion);

try {
    if (!$model->crear($nombre, $usuario, $password, $rol)) {
        throw new RuntimeException("No se pudo crear el usuario.");
    }

    fwrite(STDOUT, "Usuario creado correctamente.\n");
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
