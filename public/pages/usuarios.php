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
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../config/database.php';

$buscar = trim($_GET['buscar'] ?? '');

$porPagina = 5;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT
            u.id,
            u.nombre,
            u.correo,
            r.nombre AS rol,
            u.activo
        FROM usuarios AS u
        INNER JOIN roles AS r
            ON u.rol_id = r.id
        WHERE u.eliminado = 0
          AND (u.nombre LIKE :buscar_nombre
               OR u.correo LIKE :buscar_correo)
               ORDER BY u.id ASC
        LIMIT :limite OFFSET :offset";

$consulta = $conexion->prepare($sql);

$consulta->bindValue(':buscar_nombre', '%' . $buscar . '%', PDO::PARAM_STR);
$consulta->bindValue(':buscar_correo', '%' . $buscar . '%', PDO::PARAM_STR);

$consulta->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$consulta->bindValue(':offset', $offset, PDO::PARAM_INT);

$consulta->execute();

$usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

$sqlTotal = "SELECT COUNT(*)
             FROM usuarios
             WHERE eliminado = 0
             AND (nombre LIKE :buscar_nombre
                  OR correo LIKE :buscar_correo)";

$consultaTotal = $conexion->prepare($sqlTotal);

$consultaTotal->execute([
    'buscar_nombre' => '%' . $buscar . '%',
    'buscar_correo' => '%' . $buscar . '%'
]);

$totalUsuarios = (int) $consultaTotal->fetchColumn();

$totalPaginas = (int) ceil($totalUsuarios / $porPagina);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <div class="container text-center">
        <form method="GET" action="usuarios.php">
    <input type="text" name="buscar" placeholder="Buscar usuario">

    <button type="submit">
        Buscar
    </button>
</form>
    <table class="table table-striped">
       <thead>
        <tr>
            <th>ID</th>
            <th>NOMBRE</th>
            <th>CORREO</th>
            <th>ROL</th>
            <th>ESTADO</th>
            <th>Acciones</th>
        </tr>
    </thead>
     <tbody>
    <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= $usuario['id'] ?></td>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['correo']) ?></td>
            <td><?= htmlspecialchars($usuario['rol']) ?></td>
            <td><?php  
                if($usuario['activo'] == 1){
                    echo "Activo";

                }else {
                    echo "Inactivo";
                }
            ?>
            </td>
            <td>
            <a href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>" class="btn btn-warning btn-sm"> Editar </a>
            <form action="../actions/eliminar_usuario.php"
                method="POST"
                class="d-inline"
                onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">

                <input type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <input type="hidden"
                    name="id"
                    value="<?= (int) $usuario['id'] ?>">

                <button type="submit" class="btn btn-danger btn-sm">
                    Eliminar
                </button>

            </form>
</td>
        </tr>
    <?php endforeach; ?>

         </tbody>
    </table>

    <nav aria-label="Paginación de usuarios">
    <ul class="pagination justify-content-center">

        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>

            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                <a class="page-link"
                   href="?buscar=<?= urlencode($buscar) ?>&pagina=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>

        <?php endfor; ?>

    </ul>
    <a href="dashboard.php" class="btn btn-secondary">
        Volver
    </a>
</nav>

</div>


     <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>