<?php

class BaseModel
{
    protected PDO $conexion;


    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }


    /**
     * Ejecuta una consulta y devuelve todas las filas.
     */
    protected function consultar(
        string $sql,
        array $parametros = []
    ): array {

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }


    /**
     * Ejecuta una consulta y devuelve una sola fila.
     */
    protected function consultarUno(
        string $sql,
        array $parametros = []
    ): ?array {

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute($parametros);

        $resultado = $stmt->fetch();

        return $resultado !== false
            ? $resultado
            : null;
    }


    /**
     * Ejecuta INSERT, UPDATE o DELETE.
     */
    protected function ejecutar(
        string $sql,
        array $parametros = []
    ): bool {

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute($parametros);
    }
}