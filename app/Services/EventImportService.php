<?php

class EventImportService
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function leerArchivo(array $archivo): array
    {
        $ext = strtolower(pathinfo((string) ($archivo["name"] ?? ""), PATHINFO_EXTENSION));

        if ($ext === "csv") {
            return $this->leerCsv((string) ($archivo["tmp_name"] ?? ""));
        }

        if ($ext === "xlsx") {
            return $this->leerXlsx((string) ($archivo["tmp_name"] ?? ""));
        }

        throw new RuntimeException("Solo se permiten archivos .xlsx o .csv.");
    }

    public function importarFilas(array $filas, EventModel $eventModel): array
    {
        if (empty($filas)) {
            throw new RuntimeException("El archivo no contiene filas de datos debajo de los encabezados.");
        }

        $resultado = [
            "importados" => 0,
            "errores" => [],
            "advertencias" => [],
        ];

        $this->conexion->beginTransaction();

        try {
            foreach ($filas as $indice => $fila) {
                $numeroFila = $indice + 2;
                $normalizada = $this->normalizarFila($fila);

                if ($this->filaVacia($normalizada)) {
                    continue;
                }

                $validacion = $this->validarFila($normalizada, $eventModel);

                if (!$validacion["ok"]) {
                    $resultado["errores"][] = [
                        "fila" => $numeroFila,
                        "errores" => $validacion["errores"],
                    ];
                    continue;
                }

                if ($this->eventoDuplicado($validacion["datos"])) {
                    $resultado["advertencias"][] = [
                        "fila" => $numeroFila,
                        "mensaje" => "Evento duplicado omitido.",
                    ];
                    continue;
                }

                if (!$eventModel->guardar($validacion["datos"])) {
                    $resultado["errores"][] = [
                        "fila" => $numeroFila,
                        "errores" => ["No se pudo insertar el evento."],
                    ];
                    continue;
                }

                $resultado["importados"]++;
            }

            $this->conexion->commit();
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }

        return $resultado;
    }

    private function leerCsv(string $ruta): array
    {
        if (!is_file($ruta)) {
            throw new RuntimeException("No se pudo leer el archivo CSV.");
        }

        $manejo = fopen($ruta, "rb");
        if ($manejo === false) {
            throw new RuntimeException("No se pudo abrir el archivo CSV.");
        }

        $filas = [];
        $encabezados = null;

        while (($datos = fgetcsv($manejo)) !== false) {
            if ($encabezados === null) {
                $encabezados = array_map([$this, "normalizarEncabezado"], $datos);
                continue;
            }

            $fila = [];
            foreach ($encabezados as $indice => $encabezado) {
                $fila[$encabezado] = $datos[$indice] ?? null;
            }
            $filas[] = $fila;
        }

        fclose($manejo);

        return $filas;
    }

    private function leerXlsx(string $ruta): array
    {
        return $this->leerXlsxConDom($ruta);
        /*
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException("La extensión ZipArchive no está disponible.");
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new RuntimeException("No se pudo abrir el archivo XLSX.");
        }

        $sharedStrings = [];
        $sharedIndex = $zip->locateName("xl/sharedStrings.xml");
        if ($sharedIndex !== false) {
            $sharedStrings = $this->leerSharedStrings($zip->getFromIndex($sharedIndex));
        }

        $sheetPath = $this->resolverHojaPrincipal($zip);
        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException("No se encontró la hoja principal del XLSX.");
        }

        $xml = simplexml_load_string($sheetXml);
        if ($xml === false) {
            throw new RuntimeException("El contenido del XLSX está dañado.");
        }

        $xml->registerXPathNamespace("a", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");

        $filas = [];
        $encabezados = null;

        foreach ($xml->xpath("//a:sheetData/a:row") as $row) {
            $fila = [];
            foreach ($row->children("http://schemas.openxmlformats.org/spreadsheetml/2006/main") as $cell) {
                $referencia = (string) $cell["r"];
                $columna = $this->columnaDesdeCelda($referencia);
                $valor = $this->leerValorCelda($cell, $sharedStrings);
                $fila[$columna] = $valor;
            }

            if ($encabezados === null) {
                $encabezados = [];
                foreach ($fila as $columna => $valor) {
                    $encabezados[$columna] = $this->normalizarEncabezado((string) $valor);
                }
                continue;
            }

            $asociativa = [];
            foreach ($encabezados as $columna => $encabezado) {
                $asociativa[$encabezado] = $fila[$columna] ?? null;
            }
            $filas[] = $asociativa;
        }

        if ($encabezados === null || empty($encabezados)) {
            throw new RuntimeException("El archivo XLSX no contiene encabezados legibles en la primera fila.");
        }

        return $filas;
        */
    }

    private function leerXlsxConDom(string $ruta): array
    {
        if (!class_exists(ZipArchive::class) || !class_exists(DOMDocument::class)) {
            throw new RuntimeException("Las extensiones ZipArchive y DOM son necesarias para leer XLSX.");
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new RuntimeException("No se pudo abrir el archivo XLSX.");
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName("xl/sharedStrings.xml");
        if ($sharedXml !== false) {
            $sharedDocument = new DOMDocument();
            $sharedDocument->loadXML($sharedXml);
            foreach ($sharedDocument->getElementsByTagNameNS("http://schemas.openxmlformats.org/spreadsheetml/2006/main", "si") as $item) {
                $texto = "";
                foreach ($item->getElementsByTagNameNS("http://schemas.openxmlformats.org/spreadsheetml/2006/main", "t") as $fragmento) {
                    $texto .= $fragmento->textContent;
                }
                $sharedStrings[] = trim($texto);
            }
        }

        $sheetPath = $this->resolverHojaPrincipal($zip);
        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException("No se encontró la hoja principal del XLSX.");
        }

        $document = new DOMDocument();
        if (!$document->loadXML($sheetXml)) {
            throw new RuntimeException("El contenido del XLSX está dañado.");
        }

        $namespace = "http://schemas.openxmlformats.org/spreadsheetml/2006/main";
        $rows = $document->getElementsByTagNameNS($namespace, "row");
        $encabezados = null;
        $filas = [];

        foreach ($rows as $row) {
            $fila = [];
            foreach ($row->getElementsByTagNameNS($namespace, "c") as $cell) {
                $referencia = $cell->getAttribute("r");
                $columna = $this->columnaDesdeCelda($referencia);
                $tipo = $cell->getAttribute("t");
                $valorNodo = $cell->getElementsByTagNameNS($namespace, "v")->item(0);
                $valor = $valorNodo ? $valorNodo->textContent : "";

                if ($tipo === "s") {
                    $valor = $sharedStrings[(int) $valor] ?? "";
                } elseif ($tipo === "inlineStr") {
                    $textoNodo = $cell->getElementsByTagNameNS($namespace, "t")->item(0);
                    $valor = $textoNodo ? $textoNodo->textContent : "";
                }

                $fila[$columna] = trim((string) $valor);
            }

            if ($encabezados === null) {
                $encabezados = [];
                foreach ($fila as $columna => $valor) {
                    $encabezados[$columna] = $this->normalizarEncabezado($valor);
                }
                continue;
            }

            $asociativa = [];
            foreach ($encabezados as $columna => $encabezado) {
                $asociativa[$encabezado] = $fila[$columna] ?? null;
            }
            $filas[] = $asociativa;
        }

        if (empty($encabezados)) {
            throw new RuntimeException("El archivo XLSX no contiene encabezados legibles en la primera fila.");
        }

        return $filas;
    }

    private function leerSharedStrings(string $xml): array
    {
        $documento = simplexml_load_string($xml);
        if ($documento === false) {
            return [];
        }

        $documento->registerXPathNamespace("a", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");

        $valores = [];
        foreach ($documento->xpath("//a:si") as $item) {
            $texto = [];
            foreach ($item->children("http://schemas.openxmlformats.org/spreadsheetml/2006/main") as $fragmento) {
                if ($fragmento->getName() === "t") {
                    $texto[] = (string) $fragmento;
                }
            }
            $valores[] = trim(implode("", $texto));
        }

        return $valores;
    }

    private function resolverHojaPrincipal(ZipArchive $zip): string
    {
        $workbook = simplexml_load_string($zip->getFromName("xl/workbook.xml"));
        $rels = simplexml_load_string($zip->getFromName("xl/_rels/workbook.xml.rels"));

        if ($workbook === false || $rels === false) {
            throw new RuntimeException("No se pudo leer la estructura del libro Excel.");
        }

        $workbook->registerXPathNamespace("a", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        $workbook->registerXPathNamespace("r", "http://schemas.openxmlformats.org/officeDocument/2006/relationships");
        $rels->registerXPathNamespace("rel", "http://schemas.openxmlformats.org/package/2006/relationships");

        $sheets = $workbook->xpath("//a:sheets/a:sheet");
        if (empty($sheets)) {
            throw new RuntimeException("El archivo Excel no contiene hojas.");
        }

        $relId = (string) $sheets[0]["r:id"];
        foreach ($rels->xpath("//rel:Relationship") as $relationship) {
            if ((string) $relationship["Id"] === $relId) {
                $target = (string) $relationship["Target"];
                return str_starts_with($target, "xl/") ? $target : "xl/" . ltrim($target, "/");
            }
        }

        return "xl/worksheets/sheet1.xml";
    }

    private function leerValorCelda(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $namespace = "http://schemas.openxmlformats.org/spreadsheetml/2006/main";
        $tipo = (string) $cell["t"];
        $hijos = $cell->children($namespace);
        $valor = isset($hijos->v) ? (string) $hijos->v : "";

        if ($tipo === "s") {
            return $sharedStrings[(int) $valor] ?? "";
        }

        if ($tipo === "inlineStr") {
            return trim((string) ($hijos->is->t ?? ""));
        }

        return trim($valor);
    }

    private function columnaDesdeCelda(string $referencia): string
    {
        return preg_replace('/\d+/', '', $referencia) ?: "";
    }

    private function normalizarEncabezado(string $encabezado): string
    {
        $encabezado = strtolower(trim($encabezado));
        $encabezado = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $encabezado) ?: $encabezado;
        $encabezado = preg_replace('/[^a-z0-9]+/', '_', $encabezado);
        return trim($encabezado, "_");
    }

    private function normalizarFila(array $fila): array
    {
        $normalizada = [];
        foreach ($fila as $clave => $valor) {
            $normalizada[$this->normalizarEncabezado((string) $clave)] = is_string($valor) ? trim($valor) : $valor;
        }
        return $normalizada;
    }

    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (trim((string) $valor) !== "") {
                return false;
            }
        }
        return true;
    }

    private function validarFila(array $fila, EventModel $eventModel): array
    {
        $errores = [];
        $datos = [];

        $fechaEvento = $this->normalizarFechaExcel($this->tomarPrimerValor($fila, ["fecha_evento", "fecha_del_evento", "fecha"]));
        $horaEvento = $this->normalizarHoraExcel($this->tomarPrimerValor($fila, ["hora_evento", "hora"]));
        $turno = $this->normalizarTurno($this->tomarPrimerValor($fila, ["turno"]));
        $fechaOperativa = $this->normalizarFechaExcel($this->tomarPrimerValor($fila, ["fecha_operativa"]));
        $estado = $this->tomarPrimerValor($fila, ["estado"]);
        $autorizado = $this->tomarPrimerValor($fila, ["autorizado"]);
        $observaciones = (string) ($this->tomarPrimerValor($fila, ["observaciones", "descripcion", "comentario"]) ?? "");

        if (!$this->fechaValida($fechaEvento)) {
            $errores[] = "fecha_evento inválida.";
        }
        if (!$this->horaValida($horaEvento)) {
            $errores[] = "hora_evento inválida.";
        }
        if (!in_array((string) $turno, ["1", "2", "3"], true)) {
            $errores[] = "turno inválido.";
        }
        if ($fechaOperativa !== null && $fechaOperativa !== "" && !$this->fechaValida($fechaOperativa)) {
            $errores[] = "fecha_operativa inválida.";
        }

        $estado = $estado !== null && $estado !== "" ? (string) $estado : "Pendiente";
        $estadosPermitidos = array_keys($eventModel->obtenerEstados());
        if (!in_array($estado, $estadosPermitidos, true)) {
            $errores[] = "estado inválido.";
        }

        $autorizado = $this->booleano($autorizado);

        $idOperador = $this->resolverIdEntidad($fila, ["id_operador", "operador"], "operadores", "id_operador", "nombre_completo");
        $idMaquina = $this->resolverIdEntidad($fila, ["id_maquina", "maquina"], "maquinas", "id_maquina", "nombre_maquina");
        $idTipoEvento = $this->resolverIdEntidad($fila, ["id_tipo_evento", "tipo_evento", "tipo"] , "tipos_eventos", "id_tipo_evento", "nombre_evento");
        $idEtiqueta = $this->resolverIdEntidad($fila, ["id_etiqueta", "etiqueta"], "etiquetas", "id_etiqueta", "nombre_etiqueta", true);
        $idClasificacion = $this->resolverIdEntidad($fila, ["id_clasificacion", "clasificacion"], "clasificaciones", "id_clasificacion", "nombre_clasificacion", true);

        if ($idOperador === null) {
            $errores[] = "operador no encontrado.";
        }
        if ($idMaquina === null) {
            $errores[] = "máquina no encontrada.";
        }
        if ($idTipoEvento === null) {
            $errores[] = "tipo_evento no encontrado.";
        }

        if (!empty($errores)) {
            return ["ok" => false, "errores" => $errores];
        }

        $datos = [
            "fecha_evento" => $fechaEvento,
            "hora_evento" => $this->normalizarHora($horaEvento),
            "turno" => (string) $turno,
            "fecha_operativa" => $fechaOperativa !== null && $fechaOperativa !== "" ? $fechaOperativa : $fechaEvento,
            "id_operador" => $idOperador,
            "id_maquina" => $idMaquina,
            "id_tipo_evento" => $idTipoEvento,
            "id_etiqueta" => $idEtiqueta,
            "id_clasificacion" => $idClasificacion,
            "estado" => $estado,
            "autorizado" => $autorizado,
            "observaciones" => $observaciones,
            "evidencia_ruta" => null,
        ];

        return ["ok" => true, "datos" => $datos];
    }

    private function resolverIdEntidad(array $fila, array $claves, string $tabla, string $columnaId, string $columnaNombre, bool $permitirNulo = false): ?int
    {
        $valor = $this->tomarPrimerValor($fila, $claves);
        if ($valor === null || trim((string) $valor) === "") {
            return $permitirNulo ? null : null;
        }

        $valorTexto = trim((string) $valor);

        if (ctype_digit((string) $valor)) {
            $stmt = $this->conexion->prepare("SELECT {$columnaId} FROM {$tabla} WHERE {$columnaId} = ? LIMIT 1");
            $stmt->execute([(int) $valor]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        $stmt = $this->conexion->prepare("SELECT {$columnaId} FROM {$tabla} WHERE LOWER({$columnaNombre}) = LOWER(?) LIMIT 1");
        $stmt->execute([$valorTexto]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }

        // Fallback tolerante: permite diferencias de acentos, espacios y texto con codificación dañada.
        $stmt = $this->conexion->prepare("SELECT {$columnaId} AS id, {$columnaNombre} AS nombre FROM {$tabla}");
        $stmt->execute();
        $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $buscado = $this->normalizarTextoComparacion($valorTexto);
        foreach ($candidatos as $filaCandidata) {
            $nombre = (string) ($filaCandidata["nombre"] ?? "");
            if ($this->normalizarTextoComparacion($nombre) === $buscado) {
                return (int) ($filaCandidata["id"] ?? 0);
            }
        }

        return null;
    }

    private function normalizarTextoComparacion(string $texto): string
    {
        $texto = trim($texto);
        if ($texto === "") {
            return "";
        }

        $texto = strtr($texto, [
            "Ã¡" => "á", "Ã©" => "é", "Ã­" => "í", "Ã³" => "ó", "Ãº" => "ú", "Ã±" => "ñ",
            "Ã" => "Á", "Ã‰" => "É", "Ã" => "Í", "Ã“" => "Ó", "Ãš" => "Ú", "Ã‘" => "Ñ",
            "�" => "",
        ]);

        $texto = preg_replace('/operaci\?+n/iu', 'operación', $texto) ?? $texto;
        $texto = preg_replace('/distracci\?+n/iu', 'distracción', $texto) ?? $texto;
        $texto = preg_replace('/obstrucci\?+n/iu', 'obstrucción', $texto) ?? $texto;
        $texto = preg_replace('/desconexi\?+n/iu', 'desconexión', $texto) ?? $texto;
        $texto = preg_replace('/tel\?+fono/iu', 'teléfono', $texto) ?? $texto;
        $texto = preg_replace('/c\?+mara/iu', 'cámara', $texto) ?? $texto;
        $texto = preg_replace('/cr\?+tica/iu', 'crítica', $texto) ?? $texto;

        $ascii = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $texto);
        if ($ascii !== false) {
            $texto = $ascii;
        }

        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '', $texto) ?? $texto;

        return $texto;
    }

    private function eventoDuplicado(array $datos): bool
    {
        $sql = "
            SELECT COUNT(*)
            FROM eventos
            WHERE fecha_evento = ?
              AND hora_evento = ?
              AND id_operador = ?
              AND id_maquina = ?
              AND id_tipo_evento = ?
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            $datos["fecha_evento"],
            $datos["hora_evento"],
            $datos["id_operador"],
            $datos["id_maquina"],
            $datos["id_tipo_evento"],
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function tomarPrimerValor(array $fila, array $claves): mixed
    {
        foreach ($claves as $clave) {
            $claveNormalizada = $this->normalizarEncabezado($clave);
            if (array_key_exists($claveNormalizada, $fila)) {
                return $fila[$claveNormalizada];
            }
        }

        return null;
    }

    private function fechaValida(string $fecha): bool
    {
        $fecha = trim($fecha);
        $valor = DateTime::createFromFormat("!Y-m-d", $fecha);
        return $valor !== false && $valor->format("Y-m-d") === $fecha;
    }

    private function normalizarFechaExcel(mixed $valor): ?string
    {
        if ($valor === null || trim((string) $valor) === "") {
            return null;
        }

        $texto = trim((string) $valor);
        if (is_numeric($texto)) {
            $fecha = new DateTime("1899-12-30");
            $fecha->modify("+" . (int) floor((float) $texto) . " days");
            return $fecha->format("Y-m-d");
        }

        foreach (["Y-m-d", "d/m/Y", "m/d/Y", "d-m-Y"] as $formato) {
            $fecha = DateTime::createFromFormat("!" . $formato, $texto);
            if ($fecha !== false && $fecha->format($formato) === $texto) {
                return $fecha->format("Y-m-d");
            }
        }

        return $texto;
    }

    private function normalizarHoraExcel(mixed $valor): string
    {
        if ($valor === null || trim((string) $valor) === "") {
            return "";
        }

        $texto = trim((string) $valor);
        if (is_numeric($texto) && (float) $texto >= 0 && (float) $texto < 1) {
            $segundos = (int) round((float) $texto * 86400);
            $segundos = min($segundos, 86399);
            return gmdate("H:i:s", $segundos);
        }

        return $texto;
    }

    private function normalizarTurno(mixed $valor): string
    {
        $texto = strtolower(trim((string) $valor));

        if (str_starts_with($texto, "1") || str_contains($texto, "primer")) {
            return "1";
        }
        if (str_starts_with($texto, "2") || str_contains($texto, "segund")) {
            return "2";
        }
        if (str_starts_with($texto, "3") || str_contains($texto, "tercer")) {
            return "3";
        }

        return (string) $valor;
    }

    private function horaValida(string $hora): bool
    {
        $hora = trim($hora);
        $valor = DateTime::createFromFormat("!H:i", $hora) ?: DateTime::createFromFormat("!H:i:s", $hora);
        return $valor !== false;
    }

    private function normalizarHora(string $hora): string
    {
        $hora = trim($hora);
        if (strlen($hora) === 5) {
            return $hora . ":00";
        }

        return $hora;
    }

    private function booleano(mixed $valor): int
    {
        $valor = strtolower(trim((string) $valor));
        return in_array($valor, ["1", "si", "sí", "true", "yes", "y"], true) ? 1 : 0;
    }
}
