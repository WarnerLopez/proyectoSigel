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

$consultaCategorias = $conexion->prepare(
    "SELECT id, nombre
     FROM categorias
     WHERE eliminado = 0
     ORDER BY nombre ASC"
);

$consultaCategorias->execute();

$categorias = $consultaCategorias->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    
    
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>

    <main class="vh-100 bg-light d-flex align-items-center">
        <div class="container py-5">
            <div class="row justify-content-center">
              
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-sm border-0" style="border-radius: 16px;">
                        <div class="card-body p-4 p-sm-5">

                            
                            <div class="text-center mb-4">
                                <h1 class="h2 fw-bold text-dark">Registrar Usuario</h1>
                                <p class="text-muted small">Por favor, completa tus datos para registrarte</p>
                            </div>

                            <form action="../actions/guardar_usuario.php" method="POST">
                               <input type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                <!-- Nombre Completo -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary" for="reg_name">Nombre completo</label>
                                    <input type="text" id="reg_name" name="nombre" class="form-control form-control-lg fs-6" placeholder="Ej. Juan Pérez" required />
                                </div>

                                <!--  Correo Electrónico -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary" for="reg_email">Correo electrónico</label>
                                    <input type="email" id="reg_email" name="correo" class="form-control form-control-lg fs-6" placeholder="correo@ejemplo.com" required />
                                </div>
                                <div class="mb-3">
                                    <label for="rol_id" class="form-label">
                                        Rol del usuario
                                    </label>

                                    <select name="rol_id" id="rol_id" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Operador</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="categoria_id" class="form-label">
                                        Categoría del operador
                                    </label>

                                    <select name="categoria_id" id="categoria_id" class="form-select">
                                        <option value="">Sin categoría</option>

                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= (int) $categoria['id'] ?>">
                                                <?= htmlspecialchars($categoria['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Contraseña -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary" for="reg_password">Contraseña</label>
                                    <input type="password" id="reg_password" name="password" class="form-control form-control-lg fs-6" placeholder="Mínimo 8 caracteres" required />
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="mb-4">
                                    <label class="form-label small fw-semibold text-secondary" for="reg_confirm_password">Confirmar contraseña</label>
                                    <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control form-control-lg fs-6" placeholder="Repite tu contraseña" required />
                                </div>
                                

                                <button type="submit" class="btn btn-primary btn-lg w-100 fs-6 fw-bold py-2">vGuardar Usuario</button>


                            </form>
                        </div>
                    </div>
                </div>
               

            </div>
        </div>
    </main>

    <!-- Scripts de Bootstrap -->
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
