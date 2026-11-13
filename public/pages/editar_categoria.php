
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

require_once __DIR__ . '/../../config/database.php';

// el id viene del boton editar
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Verificar que el ID sea válido
if (!$id || $id < 1) {
    exit('ID de categoría inválido');
}

$consulta = $conexion->prepare(
    "SELECT id, nombre, dias_max_prestamo
     FROM categorias
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);

$categoria = $consulta->fetch(PDO::FETCH_ASSOC);


if (!$categoria) {
    exit('Categoría no encontrada');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría - SIGEL</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Editar Categoría</h2>

    <form action="../actions/actualizar_categoria.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <input
            type="hidden"
            name="id"
            value="<?= (int) $categoria['id'] ?>"
        >

        <div class="mb-3">
            <label for="nombre" class="form-label">
                Nombre de la categoría
            </label>

            <input
                type="text"
                name="nombre"
                id="nombre"
                class="form-control"
                maxlength="120"
                value="<?= htmlspecialchars($categoria['nombre']) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="dias_max_prestamo" class="form-label">
                Días máximos de préstamo
            </label>

            <input
                type="number"
                name="dias_max_prestamo"
                id="dias_max_prestamo"
                class="form-control"
                min="1"
                value="<?= (int) $categoria['dias_max_prestamo'] ?>"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar Cambios
        </button>

        <a href="categorias.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
