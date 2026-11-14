
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

// validar el id
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID de categoría inválido');
}

// Comprobar que la categoría exista
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

// verificar que no tenga usuarios activos   asociados a la categoria
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM usuarios
     WHERE categoria_id = :id
     AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);

if ($consulta->fetchColumn() > 0) {
    exit('No se puede eliminar la categoría porque tiene usuarios asociados');
}

//  eliminación lógica
$consulta = $conexion->prepare(
    "UPDATE categorias
     SET eliminado = 1
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);


header('Location: ../pages/categorias.php?mensaje=eliminado');
exit;
