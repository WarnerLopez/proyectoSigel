
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

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Solicitud no válida');
}

require_once __DIR__ . '/../../config/database.php';


$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$rol_id = (int) ($_POST['rol_id'] ?? 0);
$categoria_id = $_POST['categoria_id'] ?? '';

// que no haya campos vacios
if ($nombre === '' || $correo === '' || $password === '') {
    exit('Debe completar todos los campos obligatorios');
}

// se verifica el correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    exit('El correo electrónico no es válido');
}

// se verifica la contraseña
if (strlen($password) < 8) {
    exit('La contraseña debe tener al menos 8 caracteres');
}

if ($password !== $confirmPassword) {
    exit('Las contraseñas no coinciden');
}

// se verifica el rol
if (!in_array($rol_id, [1, 2], true)) {
    exit('El rol seleccionado no es válido');
}

// se valida la categoria que se le va a dar al operador
if ($rol_id === 2) {

    if ($categoria_id === '') {
        exit('Debe seleccionar una categoría para el operador');
    }

    if (
        filter_var($categoria_id, FILTER_VALIDATE_INT) === false ||
        (int) $categoria_id < 1
    ) {
        exit('La categoría seleccionada no es válida');
    }

    $consultaCategoria = $conexion->prepare(
        "SELECT id
         FROM categorias
         WHERE id = :categoria_id
         AND eliminado = 0"
    );

    $consultaCategoria->execute([
        'categoria_id' => $categoria_id
    ]);

    if (!$consultaCategoria->fetch()) {
        exit('La categoría seleccionada no es válida');
    }

    $categoria_id = (int) $categoria_id;

} else {
    $categoria_id = null;
}


$consultaCorreo = $conexion->prepare(
    "SELECT id
     FROM usuarios
     WHERE correo = :correo"
);

$consultaCorreo->execute([
    'correo' => $correo
]);

if ($consultaCorreo->fetch()) {
    exit('Este correo ya está registrado');
}


$passwordHash = password_hash($password, PASSWORD_DEFAULT);


$consultaInsertar = $conexion->prepare(
    "INSERT INTO usuarios
        (rol_id, nombre, correo, password_hash, categoria_id)
     VALUES
        (:rol_id, :nombre, :correo, :password_hash, :categoria_id)"
);

$consultaInsertar->execute([
    'rol_id' => $rol_id,
    'nombre' => $nombre,
    'correo' => $correo,
    'password_hash' => $passwordHash,
    'categoria_id' => $categoria_id
]);


header('Location: ../pages/usuarios.php?mensaje=creado');
exit;


