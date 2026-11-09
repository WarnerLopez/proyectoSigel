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

$consulta = $conexion->prepare(
    "SELECT
        u.id,
        u.nombre,
        u.correo,
        r.nombre AS rol,
        u.activo
     FROM usuarios AS u
     INNER JOIN roles AS r
        ON u.rol_id = r.id
     WHERE u.eliminado = 0"
);

$consulta->execute();

$usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);


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
    <table class="table table-striped">
       <thead>
        <tr>
            <th>ID</th>
            <th>NOMBRE</th>
            <th>CORREO</th>
            <th>ROL</th>
            <th>ESTADO</th>

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
        </tr>
    <?php endforeach; ?>

         </tbody>
    </table>

</div>


     <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>