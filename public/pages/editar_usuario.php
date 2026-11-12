
<?php
session_start();

// Verificar que exista una sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// Permitir solamente administradores
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    http_response_code(403);
    exit('Acceso denegado');
}

require_once __DIR__ . '/../../config/database.php';

// Recibir y validar el ID del usuario
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID de usuario no válido');
}

// Buscar los datos del usuario
$consulta = $conexion->prepare(
    "SELECT id, nombre, correo, rol_id, categoria_id, activo
     FROM usuarios
     WHERE id = :id AND eliminado = 0"
);

$consulta->execute([
    'id' => $id
]);

$usuario = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    http_response_code(404);
    exit('Usuario no encontrado');
}

// Consultar categorías disponibles
$consultaCategorias = $conexion->prepare(
    "SELECT id, nombre
     FROM categorias
     WHERE eliminado = 0
     ORDER BY nombre ASC"
);

$consultaCategorias->execute();

$categorias = $consultaCategorias->fetchAll(PDO::FETCH_ASSOC);

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h2 class="mb-4">Editar Usuario</h2>

                    <form action="../actions/actualizar_usuario.php" method="POST">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <input type="hidden"
                               name="id"
                               value="<?= (int) $usuario['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text"
                                   name="nombre"
                                   class="form-control"
                                   value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email"
                                   name="correo"
                                   class="form-control"
                                   value="<?= htmlspecialchars($usuario['correo']) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="rol_id" class="form-select" required>
                                <option value="1"
                                    <?= $usuario['rol_id'] == 1 ? 'selected' : '' ?>>
                                    Administrador
                                </option>

                                <option value="2"
                                    <?= $usuario['rol_id'] == 2 ? 'selected' : '' ?>>
                                    Operador
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Categoría del operador
                            </label>

                            <select name="categoria_id" class="form-select">
                                <option value="">Sin categoría</option>

                                <?php foreach ($categorias as $categoria): ?>
                                    <option
                                        value="<?= (int) $categoria['id'] ?>"
                                        <?= $usuario['categoria_id'] == $categoria['id']
                                            ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>

                            <select name="activo" class="form-select">
                                <option value="1"
                                    <?= $usuario['activo'] == 1 ? 'selected' : '' ?>>
                                    Activo
                                </option>

                                <option value="0"
                                    <?= $usuario['activo'] == 0 ? 'selected' : '' ?>>
                                    Inactivo
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Guardar Cambios
                        </button>

                        <a href="usuarios.php" class="btn btn-secondary">
                            Cancelar
                        </a>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
