<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/EventModel.php";
require_once __DIR__ . "/../Models/AccionEventoModel.php";
require_once __DIR__ . "/../Services/EventImportService.php";

class EventController extends BaseController
{
    private EventModel $model;
    private AccionEventoModel $accionModel;
    private EventImportService $importService;

    public function __construct()
    {
        global $conexion;

        $this->model = new EventModel($conexion);
        $this->accionModel = new AccionEventoModel($conexion);
        $this->importService = new EventImportService($conexion);

        // Crear tabla de acciones si no existe
        $this->accionModel->crearTabla();
    }

    public function index(): void
    {
        $filtros = [
            "turno" => $_GET["turno"] ?? "",
            "supervisor" => $_GET["supervisor"] ?? "",
            "fecha_inicio" => $_GET["fecha_inicio"] ?? "",
            "fecha_fin" => $_GET["fecha_fin"] ?? "",
        ];

        $eventos = $this->model->listarEventos($filtros);

        $datos = [
            "tituloPagina" => "Eventos",
            "subtituloPagina" => "Registro y clasificación de incidencias operativas",
            "usuarioActual" => "Administrador",
            "eventos" => $eventos,
            "filtros" => $filtros,
            "supervisores" => $this->model->listarSupervisores(),
        ];

        $this->render("Events/index", $datos);
    }

    public function import(): void
    {
        $resultado = null;
        $erroresArchivo = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->validarCsrf();

            if (!isset($_FILES["archivo"]) || ($_FILES["archivo"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $erroresArchivo[] = "Debes seleccionar un archivo .xlsx o .csv.";
            } elseif (($_FILES["archivo"]["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $erroresArchivo[] = "No se pudo subir el archivo.";
            } else {
                try {
                    $filas = $this->importService->leerArchivo($_FILES["archivo"]);
                    $resultado = $this->importService->importarFilas($filas, $this->model);
                } catch (Throwable $e) {
                    $erroresArchivo[] = $e->getMessage();
                }
            }
        }

        $this->render("Events/import", [
            "tituloPagina" => "Importar eventos",
            "subtituloPagina" => "Carga masiva desde Excel o CSV",
            "resultado" => $resultado,
            "erroresArchivo" => $erroresArchivo,
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->redirect("event");
        }

        $this->validarCsrf();
        $ids = $_POST["ids_evento"] ?? [];
        $ids = array_values(array_unique(array_filter(array_map("intval", (array) $ids), static fn(int $id): bool => $id > 0)));

        if (empty($ids)) {
            http_response_code(422);
            die("Selecciona al menos un evento para eliminar.");
        }

        if (!$this->model->eliminarEventos($ids)) {
            http_response_code(500);
            die("No se pudieron eliminar los eventos seleccionados.");
        }

        $this->redirect("event", "index", ["eliminados" => count($ids)]);
    }

    public function show(): void
    {
        $id = (int) ($_GET["id"] ?? 0);

        if ($id <= 0) {
            $this->redirect("event");
            return;
        }

        $evento = $this->model->obtenerEvento($id);

        if (!$evento) {
            $this->redirect("event");
            return;
        }

        $acciones = $this->accionModel->listarAcciones($id);

        $datos = [
            "tituloPagina" => "Detalle del evento",
            "subtituloPagina" => "Seguimiento y evidencia del registro operativo",
            "usuarioActual" => "Administrador",
            "evento" => $evento,
            "acciones" => $acciones,
            "estados" => $this->model->obtenerEstados(),
        ];

        $this->render("Events/show", $datos);
    }

    public function update(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->redirect("event");
            return;
        }

        $this->validarCsrf();

        $id = (int) ($_POST["id_evento"] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            die("ID de evento inválido.");
        }

        $evento = $this->model->obtenerEvento($id);

        if (!$evento) {
            http_response_code(404);
            die("Evento no encontrado.");
        }

        $nuevoEstado = $_POST["estado"] ?? $evento["estado"];
        $comentario = trim((string) ($_POST["comentario"] ?? ""));

        if (!array_key_exists($nuevoEstado, $this->model->obtenerEstados())) {
            http_response_code(422);
            die("El estado del evento no es válido.");
        }

        // Actualizar estado si cambió
        if ($nuevoEstado !== $evento["estado"]) {
            if (!$this->model->actualizarEstado($id, $nuevoEstado)) {
                http_response_code(500);
                die("No se pudo actualizar el estado del evento.");
            }
        }

        // Guardar acción/comentario si hay
        if (!empty($comentario)) {
            if (!$this->accionModel->guardarAccion($id, [
                "tipo_accion" => "comentario",
                "descripcion" => $comentario,
                "estado_nuevo" => $nuevoEstado,
                "usuario_accion" => $_SESSION["id_supervisor"] ?? null,
            ])) {
                http_response_code(500);
                die("No se pudo guardar la acción del evento.");
            }
        }

        $this->redirect("event", "show", ["id" => $id]);
    }

    public function create(): void
    {
        $datos = [
            "tituloPagina" => "Nuevo evento",
            "subtituloPagina" => "Registrar observación detectada en cámara",
            "usuarioActual" => "Administrador",
            "operadores" => $this->model->listarOperadores(),
            "maquinas" => $this->model->listarMaquinas(),
            "tiposEventos" => $this->model->listarTiposEvento(),
            "etiquetas" => $this->model->listarEtiquetas(),
            "clasificaciones" => $this->model->listarClasificaciones(),
        ];

        $this->render("Events/create", $datos);
    }

    public function store(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->redirect("event", "create");
            return;
        }

        $this->validarCsrf();

        $rutaEvidencia = null;

        if (isset($_FILES["evidencia"]) && $_FILES["evidencia"]["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES["evidencia"]["error"] !== UPLOAD_ERR_OK) {
                http_response_code(422);
                die("No se pudo recibir la evidencia adjunta.");
            }

            $rutaEvidencia = $this->model->guardarEvidencia($_FILES["evidencia"]);
            if ($rutaEvidencia === null) {
                http_response_code(422);
                die("La evidencia debe ser una imagen o PDF de máximo 5 MB.");
            }
        }

        $fechaEvento = trim((string) ($_POST["fecha_evento"] ?? ""));
        $fechaOperativa = trim((string) ($_POST["fecha_operativa"] ?? ""));
        $horaEvento = trim((string) ($_POST["hora_evento"] ?? ""));
        $turno = (string) ($_POST["turno"] ?? "");
        $estado = (string) ($_POST["estado"] ?? "Pendiente");

        if (!$this->fechaValida($fechaEvento) || !$this->fechaValida($fechaOperativa) || !$this->horaValida($horaEvento)) {
            http_response_code(422);
            die("La fecha o la hora del evento no son válidas.");
        }

        if (!in_array($turno, ["1", "2", "3"], true) || !array_key_exists($estado, $this->model->obtenerEstados())) {
            http_response_code(422);
            die("El turno o estado del evento no es válido.");
        }

        $idsRequeridos = ["id_operador", "id_maquina", "id_tipo_evento"];
        foreach ($idsRequeridos as $campo) {
            if ((int) ($_POST[$campo] ?? 0) <= 0) {
                http_response_code(422);
                die("Falta seleccionar un valor válido para {$campo}.");
            }
        }

        $payload = [
            "fecha_evento" => $fechaEvento,
            "hora_evento" => $horaEvento,
            "turno" => $turno,
            "fecha_operativa" => $fechaOperativa,
            "id_operador" => $_POST["id_operador"] ?? null,
            "id_maquina" => $_POST["id_maquina"] ?? null,
            "id_tipo_evento" => $_POST["id_tipo_evento"] ?? null,
            "id_etiqueta" => $_POST["id_etiqueta"] ?? null,
            "id_clasificacion" => $_POST["id_clasificacion"] ?? null,
            "estado" => $estado,
            "autorizado" => $_POST["autorizado"] ?? 0,
            "observaciones" => $_POST["observaciones"] ?? "",
            "evidencia_ruta" => $rutaEvidencia,
        ];

        $ok = $this->model->guardar($payload);

        if ($ok) {
            $this->redirect("event");
        }

        http_response_code(500);
        die("No se pudo guardar el evento.");
    }

    private function fechaValida(string $fecha): bool
    {
        $valor = DateTime::createFromFormat("!Y-m-d", $fecha);

        return $valor !== false && $valor->format("Y-m-d") === $fecha;
    }

    private function horaValida(string $hora): bool
    {
        $valor = DateTime::createFromFormat("!H:i", $hora) ?: DateTime::createFromFormat("!H:i:s", $hora);

        return $valor !== false && in_array($valor->format("H:i"), [substr($hora, 0, 5)], true);
    }
}
