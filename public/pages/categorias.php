
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
$buscar = trim($_GET['buscar'] ?? '');

// muestra cuantas categorias se van a ver por pagina
$porPagina = 5;

// número de página
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);

if (!$pagina || $pagina < 1) {
    $pagina = 1;
}

// trae la categoria que se busca
$consultaTotal = $conexion->prepare(
    "SELECT COUNT(*)
     FROM categorias
     WHERE eliminado = 0
     AND nombre LIKE :buscar"
);

$consultaTotal->execute([
    'buscar' => '%' . $buscar . '%'
]);

$totalCategorias = (int) $consultaTotal->fetchColumn();


$totalPaginas = (int) ceil($totalCategorias / $porPagina);

// no se crean paginas mayores al total
if ($totalPaginas > 0 && $pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}


$offset = ($pagina - 1) * $porPagina;

// Consultar las categorías de la página actual
$consulta = $conexion->prepare(
    "SELECT id, nombre, dias_max_prestamo
     FROM categorias
     WHERE eliminado = 0
     AND nombre LIKE :buscar
     ORDER BY id ASC
     LIMIT :limite OFFSET :offset"
);

$consulta->bindValue(
    ':buscar',
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

$categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - SIGEL</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">Gestión de Categorías</h2>

    <a href="crear_categoria.php" class="btn btn-success mb-3">
        Crear Categoría
    </a>
    
    <form method="GET" action="categorias.php" class="mb-4">

    <div class="input-group">

        <input
            type="text"
            name="buscar"
            class="form-control"
            placeholder="Buscar categoría por nombre"
            value="<?= htmlspecialchars($buscar) ?>"
        >

        <button type="submit" class="btn btn-primary">
            Buscar
        </button>

        <a href="categorias.php" class="btn btn-secondary">
            Limpiar
        </a>

    </div>

</form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Días máximos de préstamo</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?= (int) $categoria['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($categoria['nombre']) ?>
                    </td>

                    <td>
                        <?= (int) $categoria['dias_max_prestamo'] ?>
                    </td>
                     <td>
                        <a
                            href="editar_categoria.php?id=<?= (int) $categoria['id'] ?>"
                            class="btn btn-warning btn-sm">Editar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalPaginas > 1): ?>

        <nav aria-label="Paginación de categorías">
            <ul class="pagination">

                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>

                    <li class="page-item <?= $pagina == $i ? 'active' : '' ?>">

                        <a class="page-link"
                        href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>">
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
