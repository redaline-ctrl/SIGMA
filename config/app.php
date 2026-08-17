<?php

if (!defined("APP_BASE_URL")) {
    $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "/public/index.php");
    $publicPosition = strpos($scriptName, "/public/");
    $basePath = $publicPosition === false
        ? rtrim(dirname($scriptName), "/")
        : substr($scriptName, 0, $publicPosition);

    define("APP_BASE_URL", rtrim($basePath, "/"));
}

function app_url(string $path = ""): string
{
    $path = "/" . ltrim($path, "/");

    $publicUrl = rtrim((string) getenv("SIGMA_PUBLIC_URL"), "/");
    if ($publicUrl !== "") {
        return $publicUrl . $path;
    }

    return APP_BASE_URL . $path;
}

function app_route(string $controller, string $action = "index", array $query = []): string
{
    $query = array_merge([
        "controller" => $controller,
        "action" => $action,
    ], $query);

    return app_url("/public/index.php?" . http_build_query($query));
}
