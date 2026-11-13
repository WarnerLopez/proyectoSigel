
<?php
session_start();

// Verificar que el usuario inició sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// Solo el administrador puede guardar categorías
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Solo permitir solicitudes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Verificar el token CSRF
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
$nombre = trim($_POST['nombre'] ?? '');

$diasMax = filter_input(
    INPUT_POST,
    'dias_max_prestamo',
    FILTER_VALIDATE_INT
);

// Validar los datos
if ($nombre === '' || $diasMax === false || $diasMax === null || $diasMax < 1) {
    exit('Debe ingresar un nombre y una cantidad válida de días');
}

// Verificar que no exista otra categoría activa con ese nombre
$consulta = $conexion->prepare(
    "SELECT COUNT(*)
     FROM categorias
     WHERE nombre = :nombre
     AND eliminado = 0"
);

$consulta->execute([
    'nombre' => $nombre
]);

if ($consulta->fetchColumn() > 0) {
    exit('Ya existe una categoría con ese nombre');
}

// Guardar la categoría
$consulta = $conexion->prepare(
    "INSERT INTO categorias (nombre, dias_max_prestamo)
     VALUES (:nombre, :dias_max_prestamo)"
);

$consulta->execute([
    'nombre' => $nombre,
    'dias_max_prestamo' => $diasMax
]);

// Regresar al listado
header('Location: ../pages/categorias.php?mensaje=creado');
exit;
