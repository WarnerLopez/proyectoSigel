
-- Se registran los roles
INSERT INTO roles (id, nombre) VALUES
(1, 'administrador'),
(2, 'operador');


-- Se registran las categorias iniciales
INSERT INTO categorias (nombre, dias_max_prestamo) VALUES
('Computadoras', 7),
('Proyectores', 5),
('Camaras', 3);


-- Se registra el admini
-- Contrasena  Admin123
INSERT INTO usuarios (
    rol_id,
    nombre,
    correo,
    password_hash,
    activo
) VALUES (
    1,
    'Administrador SIGEL',
    'admin@sigel.com',
    '$2y$10$.D3Jbuy7e0rL.oH2v98Z1uRC3HMxqmP3/typchyv28AUR8ak0Nz72',
    1
);


-- Se registra el operador
-- Contrasena  Operador123
INSERT INTO usuarios (
    rol_id,
    nombre,
    correo,
    password_hash,
    activo,
    categoria_id
) VALUES (
    2,
    'Operador SIGEL',
    'operador@sigel.com',
    '$2y$10$BokTwxAEL1kjseOYnYGQTuHDCYhWWAkxGIvBD/QTBdKHAGg91FPWa',
    1,
    (SELECT id FROM categorias WHERE nombre = 'Computadoras')
);
