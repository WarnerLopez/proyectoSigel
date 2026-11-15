
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener el rol y la categoria del usuario
$rolId = (int) ($_SESSION['rol_id'] ?? 0);
$categoriaId = $_SESSION['categoria_id'] ?? null;

// Permitir solamente administrador y operador
if (!in_array($rolId, [1, 2], true)) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Obtener equipos disponibles
$sql = "SELECT e.id, e.codigo, e.nombre, c.nombre AS categoria
        FROM equipos e
        INNER JOIN categorias c ON e.categoria_id = c.id
        WHERE e.eliminado = 0
          AND c.eliminado = 0
          AND e.estado = 'disponible'";

$parametros = [];

// El operador solamente puede seleccionar equipos de su categoría
if ($rolId === 2) {

    if (!$categoriaId) {
        http_response_code(403);
        exit('El operador no tiene una categoría asignada');
    }

    $sql .= " AND e.categoria_id = :categoria_id";
    $parametros['categoria_id'] = $categoriaId;
}

$sql .= " ORDER BY e.nombre ASC";

$consulta = $conexion->prepare($sql);
$consulta->execute($parametros);

$equipos = $consulta->fetchAll(PDO::FETCH_ASSOC);

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

    <title>Registrar Préstamo - SIGEL</title>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Registrar Préstamo</h2>

    <form action="../actions/guardar_prestamo.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">
                Fecha y hora de inicio
            </label>

            <input
                type="datetime-local"
                name="fecha_inicio"
                id="fecha_inicio"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label for="fecha_fin_prevista" class="form-label">
                Fecha y hora de devolución prevista
            </label>

            <input
                type="datetime-local"
                name="fecha_fin_prevista"
                id="fecha_fin_prevista"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label for="equipos" class="form-label">
                Seleccionar equipos
            </label>

            <select
                name="equipos[]"
                id="equipos"
                class="form-select"
                multiple
                size="6"
                required
            >
                <?php foreach ($equipos as $equipo): ?>

                    <option value="<?= (int) $equipo['id'] ?>">
                        <?= htmlspecialchars(
                            $equipo['codigo'] . ' - ' .
                            $equipo['nombre'] . ' (' .
                            $equipo['categoria'] . ')'
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <div class="form-text">
                Mantén presionada la tecla Ctrl para seleccionar
                varios equipos.
            </div>
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">
                Observaciones
            </label>

            <textarea
                name="observaciones"
                id="observaciones"
                class="form-control"
                rows="3"
            ></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Registrar Prestamo
        </button>

        <a href="prestamo.php" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
