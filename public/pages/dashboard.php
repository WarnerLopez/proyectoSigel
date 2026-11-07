
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Bootstrap</title>
  
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>

  <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Navbar</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
  
      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ms-auto">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
          <?php if ($_SESSION['rol_id'] == 1): ?>

            <a class="nav-link" href="usuarios.php">Usuarios</a>

          <?php endif; ?>

          <?php if ($_SESSION['rol_id'] == 1): ?>

            <a class="nav-link" href="categorias.php">Categorías</a>

          <?php endif; ?>
          <a class="nav-link" href="#">Equipos</a>
          
          <form action="../actions/logout.php" method="POST">
              <button type="submit" class="btn btn-outline-danger">
                  Cerrar sesión
              </button>
          </form>
          
        </div>
      </div>
    </div>
  </nav>

  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
