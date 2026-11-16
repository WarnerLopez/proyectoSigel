
<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ((int) ($_SESSION['rol_id'] ?? 0) !== 1) {
    http_response_code(403);
    exit('Acceso denegado. Solo el administrador puede ver reportes.');
}


// Equipos mas prestados
$sqlEquipos = "
    SELECT
        e.nombre,
        e.codigo,
        COUNT(pe.id) AS total_prestamos
    FROM equipos e
    INNER JOIN prestamo_equipos pe
        ON e.id = pe.equipo_id
    INNER JOIN prestamos p
        ON pe.prestamo_id = p.id
    WHERE p.estado IN ('aprobado', 'entregado', 'devuelto')
    GROUP BY e.id, e.nombre, e.codigo
    ORDER BY total_prestamos DESC, e.nombre ASC
";

$stmtEquipos = $conexion->prepare($sqlEquipos);
$stmtEquipos->execute();

$equiposMasPrestados = $stmtEquipos->fetchAll(PDO::FETCH_ASSOC);



//  Préstamos por categoría
$sqlCategorias = "
    SELECT
        c.nombre AS categoria,
        COUNT(DISTINCT p.id) AS total_prestamos
    FROM categorias c
    INNER JOIN equipos e
        ON c.id = e.categoria_id
    INNER JOIN prestamo_equipos pe
        ON e.id = pe.equipo_id
    INNER JOIN prestamos p
        ON pe.prestamo_id = p.id
    WHERE p.estado IN ('aprobado', 'entregado', 'devuelto')
    GROUP BY c.id, c.nombre
    ORDER BY total_prestamos DESC, c.nombre ASC
";

$stmtCategorias = $conexion->prepare($sqlCategorias);
$stmtCategorias->execute();

$prestamosPorCategoria = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);


// REPORTE 3: Préstamos por mes
$sqlMeses = "
    SELECT
        YEAR(p.creado_en) AS anio,
        MONTH(p.creado_en) AS mes,
        COUNT(p.id) AS total_prestamos
    FROM prestamos p
    WHERE p.estado IN ('aprobado', 'entregado', 'devuelto')
    GROUP BY YEAR(p.creado_en), MONTH(p.creado_en)
    ORDER BY anio DESC, mes DESC
";

$stmtMeses = $conexion->prepare($sqlMeses);
$stmtMeses->execute();

$prestamosPorMes = $stmtMeses->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - SIGEL</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>

<div class="container mt-5">

    <h2>Reportes de SIGEL</h2>

    <p class="text-muted">
        Estadísticas de los préstamos de equipos.
    </p>

    <a href="dashboard.php" class="btn btn-secondary mb-4">
        Volver al Dashboard
    </a>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Equipos más prestados</h5>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Código</th>
                        <th>Total de préstamos</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($equiposMasPrestados as $equipo): ?>
                        <tr>
                            <td><?= htmlspecialchars($equipo['nombre']) ?></td>
                            <td><?= htmlspecialchars($equipo['codigo']) ?></td>
                            <td><?= (int) $equipo['total_prestamos'] ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($equiposMasPrestados)): ?>
                        <tr>
                            <td colspan="3" class="text-center">
                                No hay préstamos para mostrar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

        <!-- prestamos por categoria --> 
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Préstamos por categoria</h5>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Total de prestamos</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($prestamosPorCategoria as $categoria): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($categoria['categoria']) ?>
                                </td>
                                <td>
                                    <?= (int) $categoria['total_prestamos'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($prestamosPorCategoria)): ?>
                            <tr>
                                <td colspan="2" class="text-center">
                                    No hay préstamos para mostrar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


<!-- PRESTAMOS POR MES -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Préstamos por mes</h5>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Mes</th>
                    <th>Total de préstamos</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $nombresMeses = [
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre'
                ];
                ?>

                <?php foreach ($prestamosPorMes as $registro): ?>
                    <tr>
                        <td><?= (int) $registro['anio'] ?></td>
                        <td>
                            <?= $nombresMeses[(int) $registro['mes']] ?>
                        </td>
                        <td>
                            <?= (int) $registro['total_prestamos'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($prestamosPorMes)): ?>
                    <tr>
                        <td colspan="3" class="text-center">
                            No hay préstamos para mostrar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



</div>

</body>
</html>
