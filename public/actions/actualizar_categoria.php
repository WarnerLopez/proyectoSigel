
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

$nombre = trim($_POST['nombre'] ?? '');

$diasMax = filter_input(
    INPUT_POST,
    'dias_max_prestamo',
    FILTER_VALIDATE_INT
);

// Validar los datos
if (
    !$id || $id < 1 ||
    $nombre === '' ||
    $diasMax === false ||
    $diasMax === null ||
    $diasMax < 1
) {
    exit('Datos de categoría inválidos');
}

// Verificar que la categoría exista y no esté eliminada
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM categorias
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);

if ($consulta->fetchColumn() == 0) {
    exit('Categoría no encontrada');
}

// Verificar que no se repita la categoria
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM categorias
     WHERE nombre = :nombre
     AND id != :id
     AND eliminado = 0"
);

$consulta->execute([
    'nombre' => $nombre,
    'id' => $id
]);

if ($consulta->fetchColumn() > 0) {
    exit('Ya existe otra categoría con ese nombre');
}

// Actualizar los datos
$consulta = $conexion->prepare(
    "UPDATE categorias
     SET nombre = :nombre,
         dias_max_prestamo = :dias_max_prestamo
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'nombre' => $nombre,
    'dias_max_prestamo' => $diasMax,
    'id' => $id
]);


header('Location: ../pages/categorias.php?mensaje=actualizado');
exit;
