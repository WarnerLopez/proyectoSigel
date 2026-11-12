
<?php
session_start();


if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}


if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    http_response_code(403);
    exit('Acceso denegado');
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// verifica que el token no venga vaci que sea una cedena de string y que se igual al token del servidor
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Solicitud no válida');
}

require_once __DIR__ . '/../../config/database.php';

// Recibir los datos del formulario
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT);
$categoria_id = $_POST['categoria_id'] ?? '';
$activo = filter_input(INPUT_POST, 'activo', FILTER_VALIDATE_INT);


if (!$id || $id < 1) {
    exit('ID de usuario no válido');
}


if ($nombre === '' || $correo === '') {

    exit('Debe completar los campos obligatorios');
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    exit('Correo electrónico no válido');
}

// Validar rol
if (!in_array($rol_id, [1, 2], true)) {
    exit('Rol no válido');
}

// Validar estado
if (!in_array($activo, [0, 1], true)) {
    exit('Estado no válido');
}

// Comprobar que el usuario exista
$consultaUsuario = $conexion->prepare(
    "SELECT id
     FROM usuarios
     WHERE id = :id AND eliminado = 0"
);

$consultaUsuario->execute(['id' => $id]);

if (!$consultaUsuario->fetch()) {
    http_response_code(404);
    exit('Usuario no encontrado');
}

// Validar categoría del operador
if ($rol_id === 2) {

    $categoriaValidada = filter_var(
        $categoria_id,
        FILTER_VALIDATE_INT
    );

    if ($categoriaValidada === false || $categoriaValidada < 1) {
        exit('Debe seleccionar una categoría válida');
    }

    $consultaCategoria = $conexion->prepare(
        "SELECT id FROM categorias
         WHERE id = :id AND eliminado = 0"
    );

    $consultaCategoria->execute([
        'id' => $categoriaValidada
    ]);

    if (!$consultaCategoria->fetch()) {
        exit('La categoría seleccionada no existe');
    }

    $categoria_id = $categoriaValidada;

} else {
    // los administradores siempre quedara en null no pueden tener categoria
    $categoria_id = null;
}

// no permite correos repetidos
$consultaCorreo = $conexion->prepare(
    "SELECT id FROM usuarios
     WHERE correo = :correo AND id <> :id"
);

$consultaCorreo->execute([
    'correo' => $correo,
    'id' => $id
]);

if ($consultaCorreo->fetch()) {
    exit('Este correo ya pertenece a otro usuario');
}

// Actualizar usuario
$actualizar = $conexion->prepare(
    "UPDATE usuarios
     SET nombre = :nombre,
         correo = :correo,
         rol_id = :rol_id,
         categoria_id = :categoria_id,
         activo = :activo
     WHERE id = :id AND eliminado = 0"
);

$actualizar->execute([
    'nombre' => $nombre,
    'correo' => $correo,
    'rol_id' => $rol_id,
    'categoria_id' => $categoria_id,
    'activo' => $activo,
    'id' => $id
]);


header('Location: ../pages/usuarios.php?mensaje=actualizado');
exit;
