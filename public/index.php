<?php

session_start();

ini_set("display_errors", "1");
error_reporting(E_ALL);


//==================================================
// CONFIGURACIÓN
//==================================================

require_once __DIR__ . "/../config/database.php";


//==================================================
// OBTENER RUTA
//==================================================

$controller = $_GET["controller"] ?? "home";
$action = $_GET["action"] ?? "index";


//==================================================
// NORMALIZAR NOMBRE DEL CONTROLADOR
//==================================================

$controller = preg_replace(
    "/[^a-zA-Z0-9_-]/",
    "",
    $controller
);

$action = preg_replace(
    "/[^a-zA-Z0-9_-]/",
    "",
    $action
);


//==================================================
// CONSTRUIR NOMBRE
//==================================================

$controllerName = ucfirst($controller) . "Controller";

$controllerFile =
    __DIR__
    . "/../app/Controllers/"
    . $controllerName
    . ".php";


//==================================================
// VERIFICAR CONTROLADOR
//==================================================

if (!file_exists($controllerFile)) {

    http_response_code(404);

    die(
        "No existe el controlador: "
        . htmlspecialchars($controllerName)
    );
}


//==================================================
// CARGAR CONTROLADOR
//==================================================

require_once $controllerFile;


//==================================================
// CREAR CONTROLADOR
//==================================================

if (!class_exists($controllerName)) {

    die(
        "No existe la clase: "
        . htmlspecialchars($controllerName)
    );
}

$controllerObject = new $controllerName();


//==================================================
// VERIFICAR ACCIÓN
//==================================================

if (!method_exists($controllerObject, $action)) {

    http_response_code(404);

    die(
        "No existe la acción: "
        . htmlspecialchars($action)
    );
}


//==================================================
// EJECUTAR
//==================================================

$controllerObject->$action();