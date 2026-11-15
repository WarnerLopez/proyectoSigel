
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



INSERT INTO equipos
(categoria_id, nombre, codigo, descripcion, estado)
VALUES
(
    (SELECT id FROM categorias WHERE nombre = 'Computadoras' AND eliminado = 0 LIMIT 1),
    'Laptop Dell',
    'EQ-001',
    'Computadora portátil para préstamo',
    'disponible'
),
(
    (SELECT id FROM categorias WHERE nombre = 'Proyectores' AND eliminado = 0 LIMIT 1),
    'Proyector Epson',
    'EQ-002',
    'Proyector para presentaciones',
    'disponible'
),
(
    (SELECT id FROM categorias WHERE nombre = 'Cámaras' AND eliminado = 0 LIMIT 1),
    'Cámara Canon',
    'EQ-003',
    'Cámara para actividades académicas',
    'disponible'
);

