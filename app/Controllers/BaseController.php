<?php

require_once __DIR__ . "/../../config/app.php";

class BaseController
{
    protected function usuarioActual(): string
    {
        return (string) ($_SESSION["nombre_usuario"] ?? "Usuario");
    }

    protected function rolActual(): string
    {
        return (string) ($_SESSION["rol"] ?? "usuario");
    }

    protected function route(string $controller, string $action = "index", array $query = []): string
    {
        return app_route($controller, $action, $query);
    }

    protected function redirect(string $controller, string $action = "index", array $query = []): void
    {
        header("Location: " . $this->route($controller, $action, $query));
        exit;
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["csrf_token"];
    }

    protected function validarCsrf(): void
    {
        $token = $_POST["csrf_token"] ?? "";

        if (!hash_equals((string) ($_SESSION["csrf_token"] ?? ""), (string) $token)) {
            http_response_code(419);
            die("La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.");
        }
    }

    /**
     * Carga una vista directamente.
     */
    protected function view(
        string $vista,
        array $datos = []
    ): void {

        $archivoVista =
            __DIR__
            . "/../Views/"
            . $vista
            . ".php";

        if (!file_exists($archivoVista)) {

            http_response_code(500);

            die(
                "No existe la vista: "
                . htmlspecialchars($vista)
            );
        }

        extract($datos);

        require $archivoVista;
    }


    /**
     * Carga una vista dentro del layout principal.
     */
    protected function render(
        string $vista,
        array $datos = []
    ): void {

        $archivoVista =
            __DIR__
            . "/../Views/"
            . $vista
            . ".php";

        $archivoLayout =
            __DIR__
            . "/../Views/Layouts/main.php";


        if (!file_exists($archivoVista)) {

            http_response_code(500);

            die(
                "No existe la vista: "
                . htmlspecialchars($vista)
            );
        }


        if (!file_exists($archivoLayout)) {

            http_response_code(500);

            die("No existe el layout principal.");
        }


        $datos["usuarioActual"] = $this->usuarioActual();
        $datos["rolActual"] = $this->rolActual();

        extract($datos);


        ob_start();

        require $archivoVista;

        $contenido = ob_get_clean();


        require $archivoLayout;
    }
}