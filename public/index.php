<?php

$https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || (int) ($_SERVER["SERVER_PORT"] ?? 0) === 443;
session_set_cookie_params([
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => $https,
]);
session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

ini_set("display_errors", "0");
error_reporting(E_ALL);


//==================================================
// CONFIGURACIÓN
//==================================================

require_once __DIR__ . "/../config/app.php";
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

$accionesPermitidas = [
    "auth" => ["login", "logout"],
    "usuario" => ["index", "store", "update", "toggle", "delete"],
    "home" => ["index"],
    "dashboard" => ["index"],
    "dashboardAdvanced" => ["index"],
    "desempeno" => ["index"],
    "event" => ["index", "show", "create", "store", "update", "import", "delete"],
    "relevo" => ["index", "create", "store", "edit", "update", "delete", "import"],
    "operador" => ["index", "store", "update", "toggle"],
    "maquina" => ["index", "store", "update", "toggle"],
    "clasificacion" => ["index"],
    "reporte" => ["index", "export", "history"],
];

if (!isset($accionesPermitidas[$controller]) || !in_array($action, $accionesPermitidas[$controller], true)) {
    http_response_code(404);
    die("Ruta no disponible.");
}

if ($controller !== "auth" && empty($_SESSION["id_usuario"])) {
    header("Location: " . app_route("auth", "login"));
    exit;
}

if ($controller === "auth" && $action === "logout" && $_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Método no permitido.");
}

$rol = strtolower(trim((string) ($_SESSION["rol"] ?? "")));
$aliasesRol = [
    "rrhh" => "rh",
    "recursos humanos" => "rh",
    "recursos_humanos" => "rh",
];
$rol = $aliasesRol[$rol] ?? $rol;
$_SESSION["rol"] = $rol;

$rolesLectura = ["administrador", "supervisor", "monitorista", "gerente", "rh"];
if ($controller !== "auth" && !in_array($rol, $rolesLectura, true)) {
    $_SESSION = [];
    session_destroy();
    header("Location: " . app_route("auth", "login"));
    exit;
}

if ($controller === "usuario" && $rol !== "administrador") {
    http_response_code(403);
    die("Solo el administrador puede administrar usuarios.");
}

$accionesEscritura = [
    "event" => ["create", "store", "update", "import", "delete"],
    "relevo" => ["create", "store", "edit", "update", "delete", "import"],
    "operador" => ["store", "update", "toggle"],
    "maquina" => ["store", "update", "toggle"],
];

$rolesEscritura = ["administrador", "supervisor"];
if (
    isset($accionesEscritura[$controller])
    && in_array($action, $accionesEscritura[$controller], true)
    && !in_array(strtolower((string) ($_SESSION["rol"] ?? "")), $rolesEscritura, true)
) {
    $monitoristaPermitidas = [
        "event" => ["create", "store", "update", "import"],
        "relevo" => ["create", "store", "edit", "update", "import"],
        "reporte" => ["export"],
    ];
    if ($rol !== "monitorista" || !in_array($action, $monitoristaPermitidas[$controller] ?? [], true)) {
        http_response_code(403);
        die("No tienes permisos para realizar esta acción.");
    }
}
if (in_array($rol, ["gerente", "rh"], true)) {
    $rutasPermitidas = [
        "home" => ["index"],
        "dashboardAdvanced" => ["index"],
        "desempeno" => ["index"],
        "reporte" => ["index", "export", "history"],
        "auth" => ["logout"],
    ];
    if (!in_array($action, $rutasPermitidas[$controller] ?? [], true)) {
        http_response_code(403);
        die("Este rol solo puede consultar dashboard, reportes e historial.");
    }
}

$controllerFile =
    __DIR__
    . "/../app/Controllers/"
    . $controllerName
    . ".php";


//==================================================
// VERIFICAR CONTROLADOR
//==================================================

$basePath = __DIR__ . "/../app/Controllers/";
$posiblesRutas = [
    $basePath . $controllerName . ".php",
    $basePath . $controller . "/" . $controllerName . ".php",
    $basePath . strtolower($controller) . "/" . $controllerName . ".php",
    $basePath . ucfirst($controller) . "/" . $controllerName . ".php",
];

$controllerFile = null;
foreach ($posiblesRutas as $ruta) {
    if (file_exists($ruta)) {
        $controllerFile = $ruta;
        break;
    }
}

if ($controllerFile === null) {
    http_response_code(404);
    die("No existe el controlador: " . htmlspecialchars($controllerName) . " - Buscado en Controllers/");
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