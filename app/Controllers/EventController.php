<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Models/EventModel.php";
require_once __DIR__ . "/../Models/AccionEventoModel.php";

class EventController extends BaseController
{
    private EventModel $model;
    private AccionEventoModel $accionModel;

    public function __construct()
    {
        global $conexion;

        $this->model = new EventModel($conexion);
        $this->accionModel = new AccionEventoModel($conexion);

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

    public function show(): void
    {
        $id = (int) ($_GET["id"] ?? 0);

        if ($id <= 0) {
            header("Location: /SIGMA/public/index.php?controller=event&action=index");
            return;
        }

        $evento = $this->model->obtenerEvento($id);

        if (!$evento) {
            header("Location: /SIGMA/public/index.php?controller=event&action=index");
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
            header("Location: /SIGMA/public/index.php?controller=event&action=index");
            return;
        }

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
        $comentario = $_POST["comentario"] ?? "";

        // Actualizar estado si cambió
        if ($nuevoEstado !== $evento["estado"]) {
            $this->model->actualizarEstado($id, $nuevoEstado);
        }

        // Guardar acción/comentario si hay
        if (!empty($comentario)) {
            $this->accionModel->guardarAccion($id, [
                "tipo_accion" => "comentario",
                "descripcion" => $comentario,
                "estado_nuevo" => $nuevoEstado,
                "usuario_accion" => 1, // Aquí iría el ID del usuario autenticado
            ]);
        }

        header("Location: /SIGMA/public/index.php?controller=event&action=show&id={$id}");
        exit;
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
            header("Location: /SIGMA/public/index.php?controller=event&action=create");
            return;
        }

        $rutaEvidencia = null;

        if (isset($_FILES["evidencia"]) && $_FILES["evidencia"]["error"] === UPLOAD_ERR_OK) {
            $rutaEvidencia = $this->model->guardarEvidencia($_FILES["evidencia"]);
        }

        $payload = [
            "fecha_evento" => $_POST["fecha_evento"] ?? date("Y-m-d"),
            "hora_evento" => $_POST["hora_evento"] ?? date("H:i:s"),
            "turno" => $_POST["turno"] ?? "1",
            "fecha_operativa" => $_POST["fecha_operativa"] ?? date("Y-m-d"),
            "id_operador" => $_POST["id_operador"] ?? null,
            "id_maquina" => $_POST["id_maquina"] ?? null,
            "id_tipo_evento" => $_POST["id_tipo_evento"] ?? null,
            "id_etiqueta" => $_POST["id_etiqueta"] ?? null,
            "id_clasificacion" => $_POST["id_clasificacion"] ?? null,
            "estado" => $_POST["estado"] ?? "Pendiente",
            "autorizado" => $_POST["autorizado"] ?? 0,
            "observaciones" => $_POST["observaciones"] ?? "",
            "evidencia_ruta" => $rutaEvidencia,
        ];

        $ok = $this->model->guardar($payload);

        if ($ok) {
            header("Location: /SIGMA/public/index.php?controller=event&action=index");
            exit;
        }

        http_response_code(500);
        die("No se pudo guardar el evento.");
    }
}
