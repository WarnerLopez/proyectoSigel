
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$rolId = (int) ($_SESSION['rol_id'] ?? 0);
$categoriaId = $_SESSION['categoria_id'] ?? null;

// Permitir administrador y operador
if (!in_array($rolId, [1, 2], true)) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Consultar los prestamos y sus equipos
$sql = "SELECT
            p.id,
            p.fecha_inicio,
            p.fecha_fin_prevista,
            p.estado,
            u.nombre AS usuario,
            GROUP_CONCAT(
                CONCAT(e.codigo, ' - ', e.nombre)
                ORDER BY e.nombre
                SEPARATOR ', '
            ) AS equipos
        FROM prestamos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN prestamo_equipos pe ON p.id = pe.prestamo_id
        INNER JOIN equipos e ON pe.equipo_id = e.id";

$parametros = [];

// El operador solo puede consultar préstamos de su categoría
if ($rolId === 2) {

    if (!$categoriaId) {
        http_response_code(403);
        exit('El operador no tiene categoría asignada');
    }

    $sql .= " WHERE NOT EXISTS (
                SELECT 1
                FROM prestamo_equipos pe2
                INNER JOIN equipos e2 ON pe2.equipo_id = e2.id
                WHERE pe2.prestamo_id = p.id
                  AND e2.categoria_id <> :categoria_id
              )";

    $parametros['categoria_id'] = $categoriaId;
}

$sql .= " GROUP BY
            p.id,
            p.fecha_inicio,
            p.fecha_fin_prevista,
            p.estado,
            u.nombre
          ORDER BY p.id DESC";

$consulta = $conexion->prepare($sql);
$consulta->execute($parametros);

$prestamos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de Préstamos - SIGEL</title>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h2 class="mb-4">Gestión de Préstamos</h2>

    <a href="crear_prestamo.php" class="btn btn-success mb-3">
        Crear Préstamo
    </a>

    <div class="table-responsive">

        <table class="table table-bordered table-striped">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Equipos</th>
                    <th>Fecha de inicio</th>
                    <th>Devolución prevista</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($prestamos as $prestamo): ?>

                    <tr>
                        <td>
                            <?= (int) $prestamo['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($prestamo['usuario']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($prestamo['equipos']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($prestamo['fecha_inicio']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($prestamo['fecha_fin_prevista']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($prestamo['estado']) ?>
                        </td>

                        <td>
                            <?php if ($prestamo['estado'] === 'solicitado'): ?>
                                <span class="text-secondary">
                                    Pendiente de aprobación
                                </span>

                            <?php elseif ($prestamo['estado'] === 'aprobado'): ?>
                                <span class="text-primary">
                                    Pendiente de entrega
                                </span>

                            <?php elseif ($prestamo['estado'] === 'entregado'): ?>
                                <span class="text-warning">
                                    Pendiente de devolución
                                </span>

                            <?php else: ?>
                                <span class="text-muted">
                                    Préstamo finalizado
                                </span>
                            <?php endif; ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($prestamos)): ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            No hay préstamos registrados.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <a href="dashboard.php" class="btn btn-secondary">
        Volver al Dashboard
    </a>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
