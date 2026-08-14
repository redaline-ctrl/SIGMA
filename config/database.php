<?php

$host = "127.0.0.1";
$usuario = "root";
$password = "";
$baseDatos = "sigma_db";
$puerto = 3306;

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

    die(
        "Error de conexión a la base de datos: "
        . $e->getMessage()
    );

}