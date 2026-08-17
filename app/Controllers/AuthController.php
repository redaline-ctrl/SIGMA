<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/UsuarioModel.php";

class AuthController extends BaseController
{
    private UsuarioModel $model;

    public function __construct()
    {
        global $conexion;

        $this->model = new UsuarioModel($conexion);
    }

    public function login(): void
    {
        $rolSesion = strtolower(trim((string) ($_SESSION["rol"] ?? "")));
        $rolesValidos = ["administrador", "supervisor", "monitorista", "gerente", "rh", "rrhh"];
        if (!empty($_SESSION["id_usuario"]) && in_array($rolSesion, $rolesValidos, true)) {
            $this->redirect("home");
        }

        if (!empty($_SESSION["id_usuario"]) && !in_array($rolSesion, $rolesValidos, true)) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }

        $error = null;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->validarCsrf();

            $usuario = trim((string) ($_POST["usuario"] ?? ""));
            $password = (string) ($_POST["password"] ?? "");
            $registro = $this->model->buscarActivo($usuario);

            if ($registro && password_verify($password, $registro["password"])) {
                session_regenerate_id(true);
                $_SESSION["id_usuario"] = (int) $registro["id_usuario"];
                $_SESSION["usuario"] = $registro["usuario"];
                $_SESSION["nombre_usuario"] = $registro["nombre_usuario"];
                $_SESSION["rol"] = strtolower(trim((string) $registro["rol"]));
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                $this->redirect("home");
            }

            $error = "Usuario o contraseña incorrectos.";
        }

        $this->view("Auth/login", [
            "error" => $error,
        ]);
    }

    public function logout(): void
    {
        $this->validarCsrf();

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        session_destroy();
        header("Location: " . $this->route("auth", "login"));
        exit;
    }
}
