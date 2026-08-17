<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/UsuarioModel.php";

class UsuarioController extends BaseController
{
    private UsuarioModel $model;

    public function __construct()
    {
        global $conexion;
        $this->model = new UsuarioModel($conexion);
    }

    public function index(): void
    {
        $this->render("Usuarios/index", [
            "tituloPagina" => "Usuarios",
            "subtituloPagina" => "Administración de cuentas y permisos",
            "usuarios" => $this->model->listar(),
            "roles" => UsuarioModel::ROLES,
        ]);
    }

    public function store(): void
    {
        $this->validarCsrf();
        $datos = $this->validarDatos();
        if ($datos["password"] === null) {
            http_response_code(422);
            die("La contraseña es obligatoria al crear un usuario.");
        }
        if (!$this->model->crear($datos["nombre"], $datos["usuario"], $datos["password"], $datos["rol"])) {
            http_response_code(422);
            die("No se pudo crear el usuario. Verifica que el usuario no esté repetido.");
        }
        $this->redirect("usuario");
    }

    public function update(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_usuario"] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            die("Usuario inválido.");
        }
        $datos = $this->validarDatos(false);
        if (!$this->model->actualizar($id, $datos["nombre"], $datos["usuario"], $datos["rol"], $datos["password"])) {
            http_response_code(422);
            die("No se pudo actualizar el usuario.");
        }
        $this->redirect("usuario");
    }

    public function toggle(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_usuario"] ?? 0);
        $estado = (int) ($_POST["estado"] ?? 0) === 1 ? 0 : 1;
        if ($id === (int) ($_SESSION["id_usuario"] ?? 0) && $estado === 0) {
            http_response_code(422);
            die("No puedes desactivar tu propia cuenta.");
        }
        if ($estado === 0 && $this->model->contarAdministradoresActivos() <= 1) {
            $usuario = $this->model->listar();
            foreach ($usuario as $fila) {
                if ((int) $fila["id_usuario"] === $id && $fila["rol"] === "administrador") {
                    http_response_code(422);
                    die("No puedes desactivar el último administrador activo.");
                }
            }
        }
        if (!$this->model->cambiarEstado($id, $estado)) {
            http_response_code(500);
            die("No se pudo cambiar el estado del usuario.");
        }
        $this->redirect("usuario");
    }

    public function delete(): void
    {
        $this->validarCsrf();
        $id = (int) ($_POST["id_usuario"] ?? 0);
        if ($id <= 0 || $id === (int) ($_SESSION["id_usuario"] ?? 0)) {
            http_response_code(422);
            die("No puedes eliminar tu propia cuenta.");
        }
        if (!$this->model->cambiarEstado($id, 0)) {
            http_response_code(500);
            die("No se pudo desactivar el usuario.");
        }
        $this->redirect("usuario");
    }

    private function validarDatos(bool $requierePassword = true): array
    {
        $nombre = trim((string) ($_POST["nombre_usuario"] ?? ""));
        $usuario = trim((string) ($_POST["usuario"] ?? ""));
        $rol = strtolower(trim((string) ($_POST["rol"] ?? "")));
        $password = trim((string) ($_POST["password"] ?? ""));
        if ($nombre === "" || mb_strlen($nombre) > 150 || !preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $usuario) || !in_array($rol, UsuarioModel::ROLES, true) || ($requierePassword && strlen($password) < 8) || (!$requierePassword && $password !== "" && strlen($password) < 8)) {
            http_response_code(422);
            die("Los datos del usuario no son válidos.");
        }
        return ["nombre" => $nombre, "usuario" => $usuario, "rol" => $rol, "password" => $password === "" ? null : $password];
    }
}
