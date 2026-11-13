
<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// Solo el administrador puede crear categorías
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Crear token de seguridad para el formulario
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Categoría - SIGEL</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Registrar Categoría</h2>

    <form action="../actions/guardar_categoria.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
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
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar Categoría
        </button>

        <a href="categorias.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
