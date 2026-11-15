
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

// Validar el token 
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
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$categoriaId = filter_input(
    INPUT_POST,
    'categoria_id',
    FILTER_VALIDATE_INT
);

$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$estado = $_POST['estado'] ?? '';

// Validar los datos obligatorios
if (
    !$id || $id < 1 ||
    !$categoriaId || $categoriaId < 1 ||
    $codigo === '' || strlen($codigo) > 50 ||
    $nombre === '' || strlen($nombre) > 120
) {
    exit('Datos inválidos');
}

// Validar el estado
$estadosPermitidos = [
    'disponible',
    'mantenimiento',
    'fuera_servicio'
];

if (!in_array($estado, $estadosPermitidos, true)) {
    exit('Estado inválido');
}

// Verificar que el equipo exista y no esté eliminado
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM equipos
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute(['id' => $id]);

if ($consulta->fetchColumn() == 0) {
    exit('Equipo no encontrado');
}

// Verificar que la categoría exista 
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM categorias
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute(['id' => $categoriaId]);

if ($consulta->fetchColumn() == 0) {
    exit('Categoría no válida');
}

// Verificar que el codigo no pertenezca a otro equipo
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM equipos
     WHERE codigo = :codigo AND id <> :id"
);

$consulta->execute([
    'codigo' => $codigo,
    'id' => $id
]);

if ($consulta->fetchColumn() > 0) {
    exit('El código ya pertenece a otro equipo');
}

// Actualizar el equipo
$consulta = $conexion->prepare(
    "UPDATE equipos
     SET codigo = :codigo,
         nombre = :nombre,
         categoria_id = :categoria_id,
         descripcion = :descripcion,
         estado = :estado
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'codigo' => $codigo,
    'nombre' => $nombre,
    'categoria_id' => $categoriaId,
    'descripcion' => $descripcion,
    'estado' => $estado,
    'id' => $id
]);

// Regresar al listado
header('Location: ../pages/equipos.php?mensaje=actualizado');
exit;
