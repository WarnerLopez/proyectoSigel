
<?php
session_start();

// Verificar que el usuario inició sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// Solo el administrador puede registrar equipos
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    http_response_code(403);
    exit('Acceso denegado');
}

require_once __DIR__ . '/../../config/database.php';

// Obtener las categorías disponibles
$consulta = $conexion->prepare(
    "SELECT id, nombre
     FROM categorias
     WHERE eliminado = 0
     ORDER BY nombre ASC"
);

$consulta->execute();

$categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Crear el token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Equipo</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Registrar Equipo</h2>


    <form action="../actions/guardar_equipo.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <div class="mb-3">
            <label for="codigo" class="form-label">
                Código del equipo
            </label>

            <input
                type="text"
                name="codigo"
                id="codigo"
                class="form-control"
                maxlength="50"
                required
            >
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label">
                Nombre del equipo
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
            <label for="categoria_id" class="form-label">
                Categoría
            </label>

            <select
                name="categoria_id"
                id="categoria_id"
                class="form-select"
                required
            >
                <option value="">Seleccione una categoría</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int) $categoria['id'] ?>">
                        <?= htmlspecialchars($categoria['nombre']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">
                Descripción
            </label>

            <textarea
                name="descripcion"
                id="descripcion"
                class="form-control"
                rows="3"
            ></textarea>
        </div>

        <div class="mb-3">
            <label for="estado" class="form-label">
                Estado
            </label>

            <select
                name="estado"
                id="estado"
                class="form-select"
                required
            >
                <option value="disponible">Disponible</option>
                <option value="mantenimiento">Mantenimiento</option>
                <option value="fuera_servicio">Fuera de servicio</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar Equipo
        </button>

        <a href="equipos.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
