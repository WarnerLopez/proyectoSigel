
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    dias_max_prestamo INT UNSIGNED NOT NULL DEFAULT 7,
    eliminado TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE equipos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    estado ENUM('disponible', 'mantenimiento', 'fuera_servicio')
        NOT NULL DEFAULT 'disponible',
    eliminado TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipos_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    intentos_fallidos INT UNSIGNED NOT NULL DEFAULT 0,
    eliminado TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    categoria_id INT UNSIGNED NULL,

    FOREIGN KEY (rol_id)
        REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuarios_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE prestamos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED NOT NULL,

    fecha_inicio DATETIME NOT NULL,

    fecha_fin_prevista DATETIME NOT NULL,

    estado ENUM(
        'solicitado',
        'aprobado',
        'entregado',
        'devuelto',
        'cancelado'
    ) NOT NULL DEFAULT 'solicitado',

    observaciones TEXT NULL,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_prestamos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT chk_fechas_prestamo
        CHECK (fecha_fin_prevista > fecha_inicio)
);


CREATE TABLE prestamo_equipos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    prestamo_id INT UNSIGNED NOT NULL,

    equipo_id INT UNSIGNED NOT NULL,

    CONSTRAINT fk_prestamo_equipos_prestamo
        FOREIGN KEY (prestamo_id)
        REFERENCES prestamos(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_prestamo_equipos_equipo
        FOREIGN KEY (equipo_id)
        REFERENCES equipos(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT uq_prestamo_equipo
        UNIQUE (prestamo_id, equipo_id)
);

