<?php

class RelevoImportService
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function leerArchivo(array $archivo): array
    {
        $extension = strtolower(pathinfo((string) ($archivo["name"] ?? ""), PATHINFO_EXTENSION));
        if ($extension === "csv") return $this->leerCsv((string) ($archivo["tmp_name"] ?? ""));
        if ($extension === "xlsx") return $this->leerXlsx((string) ($archivo["tmp_name"] ?? ""));
        throw new RuntimeException("Solo se permiten archivos .xlsx o .csv.");
    }

    public function importar(array $filas, RelevoModel $model): array
    {
        if (empty($filas)) throw new RuntimeException("El archivo no contiene filas de datos.");
        $resultado = ["importados" => 0, "errores" => [], "advertencias" => []];
        $this->conexion->beginTransaction();
        try {
            foreach ($filas as $index => $fila) {
                $filaNumero = $index + 2;
                $datos = $this->validar($this->normalizarFila($fila));
                if (!$datos["ok"]) {
                    $resultado["errores"][] = ["fila" => $filaNumero, "errores" => $datos["errores"]];
                    continue;
                }
                if ($model->existeDuplicado($datos["datos"])) {
                    $resultado["advertencias"][] = ["fila" => $filaNumero, "mensaje" => "Relevo duplicado omitido."];
                    continue;
                }
                if (!$model->guardar($datos["datos"])) {
                    $resultado["errores"][] = ["fila" => $filaNumero, "errores" => ["No se pudo insertar el relevo."]];
                    continue;
                }
                $resultado["importados"]++;
            }
            $this->conexion->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            throw $e;
        }
    }

    private function validar(array $fila): array
    {
        $errores = [];
        $fecha = $this->fecha($this->valor($fila, ["fecha_operativa", "fecha"]));
        $inicio = $this->hora($this->valor($fila, ["hora_inicio", "inicio"]));
        $fin = $this->hora($this->valor($fila, ["hora_fin", "fin"]));
        $turno = $this->turno($this->valor($fila, ["turno"]));
        if (!$fecha) $errores[] = "fecha_operativa inválida.";
        if (!$inicio || !$fin) $errores[] = "hora_inicio u hora_fin inválida.";
        if (!in_array($turno, ["1", "2", "3"], true)) $errores[] = "turno inválido.";

        $supervisor = $this->entidad($fila, ["id_supervisor", "supervisor"], "supervisores", "id_supervisor", "nombre_completo");
        $operador = $this->entidad($fila, ["id_operador", "operador"], "operadores", "id_operador", "nombre_completo");
        $maquina = $this->entidad($fila, ["id_maquina", "maquina"], "maquinas", "id_maquina", "nombre_maquina");
        if ($supervisor === null) $errores[] = "supervisor no encontrado.";
        if ($operador === null) $errores[] = "operador no encontrado.";
        if ($maquina === null) $errores[] = "máquina no encontrada.";
        if ($errores) return ["ok" => false, "errores" => $errores];

        return ["ok" => true, "datos" => [
            "fecha_operativa" => $fecha,
            "turno" => $turno,
            "id_supervisor" => $supervisor,
            "id_operador" => $operador,
            "id_maquina" => $maquina,
            "hora_inicio" => $inicio,
            "hora_fin" => $fin,
            "observaciones" => (string) ($this->valor($fila, ["observaciones", "comentarios", "comentario"]) ?? ""),
        ]];
    }

    private function entidad(array $fila, array $claves, string $tabla, string $id, string $nombre): ?int
    {
        $valor = $this->valor($fila, $claves);
        if ($valor === null || trim((string) $valor) === "") return null;
        $stmt = $this->conexion->prepare(ctype_digit((string) $valor) ? "SELECT {$id} FROM {$tabla} WHERE {$id} = ? LIMIT 1" : "SELECT {$id} FROM {$tabla} WHERE LOWER(TRIM({$nombre})) = LOWER(TRIM(?)) LIMIT 1");
        $stmt->execute([trim((string) $valor)]);
        $found = $stmt->fetchColumn();
        return $found === false ? null : (int) $found;
    }

    private function leerCsv(string $path): array
    {
        $handle = fopen($path, "rb"); if (!$handle) throw new RuntimeException("No se pudo abrir el CSV.");
        $headers = null; $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if ($headers === null) { $headers = array_map([$this, "encabezado"], $values); continue; }
            $row = []; foreach ($headers as $i => $header) $row[$header] = $values[$i] ?? null; $rows[] = $row;
        }
        fclose($handle); return $rows;
    }

    private function leerXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class) || !class_exists(DOMDocument::class)) throw new RuntimeException("PHP requiere ZipArchive y DOM para leer XLSX.");
        $zip = new ZipArchive(); if ($zip->open($path) !== true) throw new RuntimeException("No se pudo abrir el XLSX.");
        $ns = "http://schemas.openxmlformats.org/spreadsheetml/2006/main"; $shared = [];
        $sharedXml = $zip->getFromName("xl/sharedStrings.xml");
        if ($sharedXml !== false) { $doc = new DOMDocument(); $doc->loadXML($sharedXml); foreach ($doc->getElementsByTagNameNS($ns, "si") as $si) { $text = ""; foreach ($si->getElementsByTagNameNS($ns, "t") as $t) $text .= $t->textContent; $shared[] = trim($text); } }
        $workbook = new DOMDocument(); $workbook->loadXML($zip->getFromName("xl/workbook.xml")); $rels = new DOMDocument(); $rels->loadXML($zip->getFromName("xl/_rels/workbook.xml.rels"));
        $sheet = $workbook->getElementsByTagNameNS($ns, "sheet")->item(0); $rid = $sheet->getAttributeNS("http://schemas.openxmlformats.org/officeDocument/2006/relationships", "id"); $target = "xl/worksheets/sheet1.xml";
        foreach ($rels->getElementsByTagNameNS("http://schemas.openxmlformats.org/package/2006/relationships", "Relationship") as $rel) if ($rel->getAttribute("Id") === $rid) { $relationshipTarget = ltrim($rel->getAttribute("Target"), "/"); $target = str_starts_with($relationshipTarget, "xl/") ? $relationshipTarget : "xl/" . $relationshipTarget; }
        $sheetDoc = new DOMDocument(); $sheetDoc->loadXML($zip->getFromName($target)); $zip->close(); $rows = []; $headers = null;
        foreach ($sheetDoc->getElementsByTagNameNS($ns, "row") as $rowNode) { $row = []; foreach ($rowNode->getElementsByTagNameNS($ns, "c") as $cell) { preg_match('/([A-Z]+)/', $cell->getAttribute("r"), $match); $column = $match[1] ?? ""; $v = $cell->getElementsByTagNameNS($ns, "v")->item(0); $value = $v ? $v->textContent : ""; if ($cell->getAttribute("t") === "s") $value = $shared[(int)$value] ?? ""; elseif ($cell->getAttribute("t") === "inlineStr") { $t = $cell->getElementsByTagNameNS($ns, "t")->item(0); $value = $t ? $t->textContent : ""; } $row[$column] = trim($value); } if ($headers === null) { $headers=[]; foreach($row as $column=>$value) $headers[$column]=$this->encabezado($value); continue; } $assoc=[]; foreach($headers as $column=>$header) $assoc[$header]=$row[$column]??null; $rows[]=$assoc; }
        return $rows;
    }

    private function normalizarFila(array $fila): array { $out=[]; foreach($fila as $key=>$value) $out[$this->encabezado((string)$key)]=is_string($value)?trim($value):$value; return $out; }
    private function encabezado(string $value): string { $value = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", strtolower(trim($value))) ?: strtolower(trim($value)); return trim((string)preg_replace('/[^a-z0-9]+/', '_', $value), "_"); }
    private function valor(array $fila, array $keys): mixed { foreach($keys as $key){$key=$this->encabezado($key);if(array_key_exists($key,$fila))return $fila[$key];}return null; }
    private function fecha(mixed $value): ?string { $value=trim((string)$value);if($value==="")return null;if(is_numeric($value)){ $date=new DateTime("1899-12-30");$date->modify("+".(int)floor((float)$value)." days");return $date->format("Y-m-d");} foreach(["Y-m-d","d/m/Y","m/d/Y","d-m-Y"] as $format){$date=DateTime::createFromFormat("!".$format,$value);if($date&&$date->format($format)===$value)return $date->format("Y-m-d");}return null; }
    private function hora(mixed $value): ?string { $value=trim((string)$value);if($value==="")return null;if(is_numeric($value)&&(float)$value>=0&&(float)$value<1)return gmdate("H:i:s",min(86399,(int)round((float)$value*86400)));foreach(["H:i:s","H:i"] as $format){$date=DateTime::createFromFormat("!".$format,$value);if($date&&$date->format($format)===$value)return strlen($value)===5?$value.":00":$value;}return null; }
    private function turno(mixed $value): string { $text=strtolower(trim((string)$value));if(str_starts_with($text,"1")||str_contains($text,"primer"))return "1";if(str_starts_with($text,"2")||str_contains($text,"segund"))return "2";if(str_starts_with($text,"3")||str_contains($text,"tercer"))return "3";return (string)$value; }
}
