<?php

require_once __DIR__ . "/app.php";

$host = getenv("SIGMA_DB_HOST") ?: "127.0.0.1";
$usuario = getenv("SIGMA_DB_USER") ?: "root";
$password = getenv("SIGMA_DB_PASSWORD");
$password = $password === false ? "" : $password;
$baseDatos = getenv("SIGMA_DB_NAME") ?: "sigma_db";
$puerto = (int) (getenv("SIGMA_DB_PORT") ?: 3306);

try {

    $conexion = new PDO(
        "mysql:host={$host};port={$puerto};dbname={$baseDatos};charset=utf8mb4",
        $usuario,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    http_response_code(500);
    die("No fue posible conectar con la base de datos.");

}