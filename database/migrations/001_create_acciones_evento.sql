CREATE TABLE IF NOT EXISTS acciones_evento (
    id_accion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_evento INT UNSIGNED NOT NULL,
    tipo_accion VARCHAR(50) NOT NULL DEFAULT 'comentario',
    descripcion TEXT NOT NULL,
    estado_nuevo VARCHAR(50) NULL,
    usuario_accion INT UNSIGNED NULL,
    fecha_accion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_acciones_evento_evento
        FOREIGN KEY (id_evento) REFERENCES eventos(id_evento) ON DELETE CASCADE,
    CONSTRAINT fk_acciones_evento_supervisor
        FOREIGN KEY (usuario_accion) REFERENCES supervisores(id_supervisor)
);
