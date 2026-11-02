-- se registra el rol de los usuarios

INSERT INTO roles (id, nombre) VALUES
(1, 'administrador'),
(2, 'operador');



-- se registra el admi
--contra Admin123

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
    '$2y$10$MED1/YcYMOdM9zfZdPah1eU.Yixj0HzzsWSuXeFw/NM7y4T7DQrtC',
    1
);

-- se registra el ope
-- contra Operador123

INSERT INTO usuarios (
    rol_id,
    nombre,
    correo,
    password_hash,
    activo
) VALUES (
    2,
    'Operador SIGEL',
    'operador@sigel.com',
    '$2y$10$J5IR9Xo/SfV4hJHQL.diaeEYECXpxx/1ZIAjV8ZpZW8xjTSUkPmKG',
    1
);