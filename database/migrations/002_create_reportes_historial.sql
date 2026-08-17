CREATE TABLE IF NOT EXISTS reportes_historial (
    id_historial INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NULL,
    usuario VARCHAR(100) NOT NULL,
    rol VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reportes_historial_fecha (fecha_generacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
