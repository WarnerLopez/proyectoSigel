
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

// recibe el id de equipo
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID de equipo inválido');
}

// Buscar el equipo seleccionado
$consulta = $conexion->prepare(
    "SELECT id, codigo, nombre, categoria_id, descripcion, estado
     FROM equipos
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);

$equipo = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$equipo) {
    exit('Equipo no encontrado');
}

//se obtienen las categorías 
$consulta = $conexion->prepare(
    "SELECT id, nombre
     FROM categorias
     WHERE eliminado = 0
     ORDER BY nombre ASC"
);

$consulta->execute();

$categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Crear token 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipo</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Editar Equipo</h2>

    <form action="../actions/actualizar_equipo.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <input
            type="hidden"
            name="id"
            value="<?= (int) $equipo['id'] ?>"
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
                value="<?= htmlspecialchars($equipo['codigo']) ?>"
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
                value="<?= htmlspecialchars($equipo['nombre']) ?>"
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
                <?php foreach ($categorias as $categoria): ?>

                    <option
                        value="<?= (int) $categoria['id'] ?>"
                        <?= $equipo['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>
                    >
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
            ><?= htmlspecialchars($equipo['descripcion'] ?? '') ?></textarea>
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
                <?php
                $estados = [
                    'disponible' => 'Disponible',
                    'mantenimiento' => 'Mantenimiento',
                    'fuera_servicio' => 'Fuera de servicio'
                ];
                ?>

                <?php foreach ($estados as $valor => $texto): ?>
                    <option
                        value="<?= $valor ?>"
                        <?= $equipo['estado'] === $valor ? 'selected' : '' ?>
                    >
                        <?= $texto ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar Cambios
        </button>

        <a href="equipos.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
