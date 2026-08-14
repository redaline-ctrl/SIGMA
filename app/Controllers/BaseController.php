<?php

class BaseController
{
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


        extract($datos);


        ob_start();

        require $archivoVista;

        $contenido = ob_get_clean();


        require $archivoLayout;
    }
}