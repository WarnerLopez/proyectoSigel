
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

// Recibir el ID del equipo
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID de equipo inválido');
}

// Verificar que el equipo exista 
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM equipos
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);

if ($consulta->fetchColumn() == 0) {
    exit('Equipo no encontrado');
}

// borra logicamente
$consulta = $conexion->prepare(
    "UPDATE equipos
     SET eliminado = 1
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);


header('Location: ../pages/equipos.php?mensaje=eliminado');
exit;
