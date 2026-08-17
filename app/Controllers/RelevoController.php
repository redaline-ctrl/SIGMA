<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/RelevoModel.php";
require_once __DIR__ . "/../Services/RelevoImportService.php";

class RelevoController extends BaseController
{
    private RelevoModel $model;
    private RelevoImportService $importService;

    public function __construct()
    {
        global $conexion;

        $this->model = new RelevoModel($conexion);
        $this->importService = new RelevoImportService($conexion);
    }

    public function index(): void
    {
        $relevos = $this->model->listarRelevos();

        $datos = [
            "tituloPagina" => "Relevos",
            "subtituloPagina" => "Control de turnos y horas operativas",
            "usuarioActual" => "Administrador",
            "relevos" => $relevos,
        ];

        $this->render("Relevos/index", $datos);
    }

    public function create(): void
    {
        $this->renderForm(null);
    }

    public function import(): void
    {
        $resultado = null;
        $erroresArchivo = [];
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->validarCsrf();
            if (!isset($_FILES["archivo"]) || ($_FILES["archivo"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $erroresArchivo[] = "Debes seleccionar un archivo .xlsx o .csv.";
            } elseif ($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
                $erroresArchivo[] = "No se pudo subir el archivo.";
            } else {
                try {
                    $filas = $this->importService->leerArchivo($_FILES["archivo"]);
                    $resultado = $this->importService->importar($filas, $this->model);
                } catch (Throwable $e) {
                    $erroresArchivo[] = $e->getMessage();
                }
            }
        }
        $this->render("Relevos/import", [
            "tituloPagina" => "Importar relevos",
            "subtituloPagina" => "Carga masiva desde Excel o CSV",
            "resultado" => $resultado,
            "erroresArchivo" => $erroresArchivo,
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET["id"] ?? 0);
        $relevo = $this->model->obtener($id);
        if ($id <= 0 || $relevo === null) {
            http_response_code(404);
            die("Relevo no encontrado.");
        }

        $this->renderForm($relevo);
    }

    public function store(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->redirect("relevo", "create");
            return;
        }

        $this->validarCsrf();

        $payload = [
            "fecha_operativa" => $_POST["fecha_operativa"] ?? date("Y-m-d"),
            "turno" => $_POST["turno"] ?? "1",
            "id_supervisor" => $_POST["id_supervisor"] ?? null,
            "id_operador" => $_POST["id_operador"] ?? null,
            "id_maquina" => $_POST["id_maquina"] ?? null,
            "hora_inicio" => $_POST["hora_inicio"] ?? "07:00:00",
            "hora_fin" => $_POST["hora_fin"] ?? "15:00:00",
            "observaciones" => $_POST["observaciones"] ?? "",
        ];

        if (!$this->fechaValida((string) $payload["fecha_operativa"])) {
            http_response_code(422);
            die("La fecha operativa no es válida.");
        }

        if (!in_array((string) $payload["turno"], ["1", "2", "3"], true)) {
            http_response_code(422);
            die("El turno no es válido.");
        }

        foreach (["id_supervisor", "id_operador", "id_maquina"] as $campo) {
            if ((int) $payload[$campo] <= 0) {
                http_response_code(422);
                die("La referencia {$campo} no es válida.");
            }
        }

        if (!$this->horaValida((string) $payload["hora_inicio"]) || !$this->horaValida((string) $payload["hora_fin"])) {
            http_response_code(422);
            die("El horario del relevo no es válido.");
        }

        $id = (int) ($_POST["id_relevo"] ?? 0);
        $ok = $id > 0 ? $this->model->actualizar($id, $payload) : $this->model->guardar($payload);

        if ($ok) {
            $this->redirect("relevo");
        }

        http_response_code(500);
        die("No se pudo guardar el relevo.");
    }

    public function delete(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->redirect("relevo");
        }
        $this->validarCsrf();
        $id = (int) ($_POST["id_relevo"] ?? 0);
        if ($id <= 0 || !$this->model->eliminar($id)) {
            http_response_code(500);
            die("No se pudo eliminar el relevo.");
        }
        $this->redirect("relevo");
    }

    private function renderForm(?array $relevo): void
    {
        $this->render("Relevos/create", [
            "tituloPagina" => $relevo ? "Editar relevo" : "Nuevo relevo",
            "subtituloPagina" => "Registrar turno operativo y horas asignadas",
            "relevo" => $relevo ?? [],
            "supervisores" => $this->model->listarSupervisores(),
            "operadores" => $this->model->listarOperadores(),
            "maquinas" => $this->model->listarMaquinas(),
        ]);
    }

    private function fechaValida(string $fecha): bool
    {
        $valor = DateTime::createFromFormat("!Y-m-d", $fecha);

        return $valor !== false && $valor->format("Y-m-d") === $fecha;
    }

    private function horaValida(string $hora): bool
    {
        $valor = DateTime::createFromFormat("!H:i", $hora) ?: DateTime::createFromFormat("!H:i:s", $hora);

        return $valor !== false && $valor->format("H:i") === substr($hora, 0, 5);
    }
}
