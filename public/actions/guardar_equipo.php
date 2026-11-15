
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

// Verificar el token 
if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )
) {
    http_response_code(403);
    exit('Token de seguridad inválido');
}

require_once __DIR__ . '/../../config/database.php';

// Recibir los datos del formulario
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$estado = $_POST['estado'] ?? '';

$categoriaId = filter_input(
    INPUT_POST,
    'categoria_id',
    FILTER_VALIDATE_INT
);

// Validar los datos
if (
    $codigo === '' ||
    strlen($codigo) > 50 ||
    $nombre === '' ||
    strlen($nombre) > 120 ||
    !$categoriaId ||
    $categoriaId < 1
) {
    exit('Datos del equipo inválidos');
}

// Validar el estado del equipo
$estadosPermitidos = [
    'disponible',
    'mantenimiento',
    'fuera_servicio'
];

if (!in_array($estado, $estadosPermitidos, true)) {
    exit('Estado del equipo inválido');
}

// Verificar que la categoria exista y no este eliminada
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM categorias
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $categoriaId
]);

if ($consulta->fetchColumn() == 0) {
    exit('La categoría seleccionada no existe');
}

// Verificar que el codigo del equipo no este registrado
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM equipos
     WHERE codigo = :codigo"
);

$consulta->execute([
    'codigo' => $codigo
]);

if ($consulta->fetchColumn() > 0) {
    exit('Ya existe un equipo con ese código');
}

// Guardar el equipo
$consulta = $conexion->prepare(
    "INSERT INTO equipos
        (codigo, nombre, categoria_id, descripcion, estado)
     VALUES
        (:codigo, :nombre, :categoria_id, :descripcion, :estado)"
);

$consulta->execute([
    'codigo' => $codigo,
    'nombre' => $nombre,
    'categoria_id' => $categoriaId,
    'descripcion' => $descripcion,
    'estado' => $estado
]);

// Regresar al listado
header('Location: ../pages/equipos.php?mensaje=creado');
exit;
