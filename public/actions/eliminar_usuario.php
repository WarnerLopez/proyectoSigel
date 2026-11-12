
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// svalida que solo administradores puedan borrar
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

// recibe y se asegura que la id sea valida y en entera
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID de usuario no válido');
}

// el administrador no se puede borrar el mismo
if ($id === (int) $_SESSION['usuario_id']) {
    exit('No puede eliminar su propia cuenta');
}

// Verificar que el usuario exista 
$consulta = $conexion->prepare(
    "SELECT id FROM usuarios
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute(['id' => $id]);

if (!$consulta->fetch()) {
    http_response_code(404);
    exit('Usuario no encontrado');
}

// elimina el usuario
$eliminar = $conexion->prepare(
    "UPDATE usuarios
     SET eliminado = 1, activo = 0
     WHERE id = :id AND eliminado = 0"
);

$eliminar->execute(['id' => $id]);

// Regresar al listado
header('Location: ../pages/usuarios.php?mensaje=eliminado');
exit;
