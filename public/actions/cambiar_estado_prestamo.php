
<?php
session_start();

// Verificar que el usuario inició sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// Verificar que sea administrador u operador
$rolId = (int) ($_SESSION['rol_id'] ?? 0);

if (!in_array($rolId, [1, 2], true)) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Verificar el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Validar el token CSRF
if (
    empty($_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token'] ?? null) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token']
    )
) {
    http_response_code(403);
    exit('Token de seguridad inválido');
}

require_once __DIR__ . '/../../config/database.php';

// Obtener los datos enviados por el formulario
$prestamoId = filter_input(
    INPUT_POST,
    'prestamo_id',
    FILTER_VALIDATE_INT
);

$nuevoEstado = $_POST['estado'] ?? '';

if (
    !$prestamoId ||
    $prestamoId < 1 ||
    !is_string($nuevoEstado)
) {
    exit('Datos inválidos');
}

// Definir los cambios de estado permitidos
$transiciones = [
    'solicitado' => ['aprobado', 'cancelado'],
    'aprobado' => ['entregado', 'cancelado'],
    'entregado' => ['devuelto'],
    'devuelto' => [],
    'cancelado' => []
];

try {
    $conexion->beginTransaction();

    // Buscar el préstamo y bloquearlo durante el cambio
    $consulta = $conexion->prepare(
        "SELECT id, estado
         FROM prestamos
         WHERE id = :id
         FOR UPDATE"
    );

    $consulta->execute(['id' => $prestamoId]);

    $prestamo = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$prestamo) {
        throw new Exception('Préstamo no encontrado');
    }

    // Verificar los permisos del operador
    if ($rolId === 2) {

        $categoriaId = filter_var(
            $_SESSION['categoria_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$categoriaId) {
            throw new Exception('Operador sin categoría asignada');
        }

        $consulta = $conexion->prepare(
            "SELECT COUNT(*)
             FROM prestamo_equipos pe
             INNER JOIN equipos e ON pe.equipo_id = e.id
             WHERE pe.prestamo_id = :prestamo_id
               AND e.categoria_id <> :categoria_id"
        );

        $consulta->execute([
            'prestamo_id' => $prestamoId,
            'categoria_id' => $categoriaId
        ]);

        if ((int) $consulta->fetchColumn() > 0) {
            throw new Exception(
                'No tiene permiso para modificar este préstamo'
            );
        }
    }

    // Validar la transición
    $estadoActual = $prestamo['estado'];

    if (!in_array(
        $nuevoEstado,
        $transiciones[$estadoActual] ?? [],
        true
    )) {
        throw new Exception('Cambio de estado no permitido');
    }
    
    // no se pueden entregar componentes wue esten en matenimiento o fuera de servicio
if (in_array($nuevoEstado, ['aprobado', 'entregado'], true)) {

    $consultaDisponibilidad = $conexion->prepare(
        "SELECT COUNT(*)
         FROM prestamo_equipos pe
         INNER JOIN equipos e ON e.id = pe.equipo_id
         INNER JOIN categorias c ON c.id = e.categoria_id
         WHERE pe.prestamo_id = :prestamo_id
           AND (
               e.estado <> 'disponible'
               OR e.eliminado <> 0
               OR c.eliminado <> 0
           )"
    );

    $consultaDisponibilidad->execute([
        'prestamo_id' => $prestamoId
    ]);

    $equiposNoDisponibles = (int) $consultaDisponibilidad->fetchColumn();

    if ($equiposNoDisponibles > 0) {
        throw new Exception(
            'No se puede aprobar o entregar: uno de los equipos no está disponible'
        );
    }
}



    // Actualizar el estado del préstamo
    $consulta = $conexion->prepare(
        "UPDATE prestamos
         SET estado = :estado
         WHERE id = :id"
    );

    $consulta->execute([
        'estado' => $nuevoEstado,
        'id' => $prestamoId
    ]);

    $conexion->commit();

    header('Location: ../pages/prestamo.php?mensaje=estado_actualizado');
    exit;

} catch (Exception $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(400);
    exit('No se pudo cambiar el estado: ' . $e->getMessage());
}
