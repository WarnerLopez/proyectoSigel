
<?php
session_start();

// Verificar que el usuario inició sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';




$buscar = trim($_GET['buscar'] ?? '');

// Cantidad de equipos por página
$porPagina = 5;

// Obtener el número de página
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);

if (!$pagina || $pagina < 1) {
    $pagina = 1;
}

// Contar los equipos que coinciden con la búsqueda
$consultaTotal = $conexion->prepare(
    "SELECT COUNT(*)
     FROM equipos
     WHERE eliminado = 0
     AND (
         nombre LIKE :buscarNombre
         OR codigo LIKE :buscarCodigo
     )"
);

$consultaTotal->execute([
    'buscarNombre' => '%' . $buscar . '%',
    'buscarCodigo' => '%' . $buscar . '%'
]);

$totalEquipos = (int) $consultaTotal->fetchColumn();

// Calcular el número total de páginas
$totalPaginas = (int) ceil($totalEquipos / $porPagina);

// Evitar páginas mayores al total
if ($totalPaginas > 0 && $pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}

// Calcular desde qué registro comenzar
$offset = ($pagina - 1) * $porPagina;

// Obtener los equipos de la página actual
$consulta = $conexion->prepare(
    "SELECT
        e.id,
        e.codigo,
        e.nombre,
        c.nombre AS categoria,
        e.estado
     FROM equipos e
     INNER JOIN categorias c
         ON e.categoria_id = c.id
     WHERE e.eliminado = 0
     AND (
         e.nombre LIKE :buscarNombre
         OR e.codigo LIKE :buscarCodigo
     )
     ORDER BY e.id ASC
     LIMIT :limite OFFSET :offset"
);

$consulta->bindValue(
    ':buscarNombre',
    '%' . $buscar . '%',
    PDO::PARAM_STR
);

$consulta->bindValue(
    ':buscarCodigo',
    '%' . $buscar . '%',
    PDO::PARAM_STR
);

$consulta->bindValue(
    ':limite',
    $porPagina,
    PDO::PARAM_INT
);

$consulta->bindValue(
    ':offset',
    $offset,
    PDO::PARAM_INT
);

$consulta->execute();

$equipos = $consulta->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Gestión de Equipos</h2>

<form method="GET" action="equipos.php" class="mb-4">

    <div class="input-group">

        <input
            type="text"
            name="buscar"
            class="form-control"
            placeholder="Buscar equipo por nombre o código"
            value="<?= htmlspecialchars($buscar) ?>"
        >

        <button type="submit" class="btn btn-primary">
            Buscar
        </button>

        <a href="equipos.php" class="btn btn-secondary">
            Limpiar
        </a>

    </div>

</form>


    <table class="table table-bordered table-striped">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($equipos as $equipo): ?>
                <tr>
                    <td><?= (int) $equipo['id'] ?></td>

                    <td><?= htmlspecialchars($equipo['codigo']) ?></td>

                    <td><?= htmlspecialchars($equipo['nombre']) ?></td>

                    <td><?= htmlspecialchars($equipo['categoria']) ?></td>

                    <td><?= htmlspecialchars($equipo['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

<?php if ($totalPaginas > 1): ?>

    <nav aria-label="Paginación de equipos">
        <ul class="pagination">

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>

                <li class="page-item <?= $pagina == $i ? 'active' : '' ?>">

                    <a
                        class="page-link"
                        href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>"
                    >
                        <?= $i ?>
                    </a>

                </li>

            <?php endfor; ?>

        </ul>
    </nav>

<?php endif; ?>


    <a href="dashboard.php" class="btn btn-secondary">
        Volver
    </a>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
