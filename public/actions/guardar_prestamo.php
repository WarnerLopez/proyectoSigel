
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$rolId = (int) ($_SESSION['rol_id'] ?? 0);

if (!in_array($rolId, [1, 2], true)) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Validar token 
if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )
) {
    http_response_code(403);
    exit('Token de seguridad inválido');
}

require_once __DIR__ . '/../../config/database.php';

// Obtener datos del formulario
$usuarioId = (int) $_SESSION['usuario_id'];
$categoriaId = $_SESSION['categoria_id'] ?? null;

$fechaInicio = trim($_POST['fecha_inicio'] ?? '');
$fechaFin = trim($_POST['fecha_fin_prevista'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$equipos = $_POST['equipos'] ?? [];

// Validar que se haya seleccionado al menos un equipo
if (!is_array($equipos) || count($equipos) === 0) {
    exit('Debe seleccionar al menos un equipo');
}

// Validar los equipos indetificados
$equiposValidados = [];

foreach ($equipos as $equipoId) {
    $id = filter_var($equipoId, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        exit('Equipo inválido');
    }

    $equiposValidados[] = $id;
}

// Evitar equipos repetidos
$equiposValidados = array_values(array_unique($equiposValidados));

// Validar el formato de las fechas
$inicio = DateTimeImmutable::createFromFormat(
    '!Y-m-d\TH:i',
    $fechaInicio
);

$fin = DateTimeImmutable::createFromFormat(
    '!Y-m-d\TH:i',
    $fechaFin
);

if (
    !$inicio ||
    !$fin ||
    $inicio->format('Y-m-d\TH:i') !== $fechaInicio ||
    $fin->format('Y-m-d\TH:i') !== $fechaFin
) {
    exit('Formato de fecha inválido');
}

// La fecha final debe ser posterior a la inicial
if ($fin <= $inicio) {
    exit('La fecha de devolución debe ser posterior a la de inicio');
}

// Validar que el operador tenga categoraa asignada
if ($rolId === 2 && !$categoriaId) {
    http_response_code(403);
    exit('El operador no tiene una categoría asignada');
}


try {
    // Iniciar una transaccion
    $conexion->beginTransaction();

    // Bloquear los equipos seleccionados mientras se registra el prestamo
    sort($equiposValidados, SORT_NUMERIC);

    $consultaEquipo = $conexion->prepare(
        "SELECT e.id, e.categoria_id, e.estado
         FROM equipos e
         INNER JOIN categorias c ON c.id = e.categoria_id
         WHERE e.id = :id
           AND e.eliminado = 0
           AND c.eliminado = 0
         FOR UPDATE"
    );

    $consultaTraslape = $conexion->prepare(
        "SELECT COUNT(*)
         FROM prestamo_equipos pe
         INNER JOIN prestamos p ON p.id = pe.prestamo_id
         WHERE pe.equipo_id = :equipo_id
           AND p.estado IN ('solicitado', 'aprobado', 'entregado')
           AND p.fecha_inicio < :fecha_fin
           AND p.fecha_fin_prevista > :fecha_inicio"
    );

    foreach ($equiposValidados as $equipoId) {

        // Comprobar que el equipo exista
        $consultaEquipo->execute(['id' => $equipoId]);
        $equipo = $consultaEquipo->fetch(PDO::FETCH_ASSOC);
        $consultaEquipo->closeCursor();

        if (!$equipo) {
            throw new Exception('Uno de los equipos no existe o fue eliminado');
        }

        // Comprobar que esté disponible
        if ($equipo['estado'] !== 'disponible') {
            throw new Exception('Uno de los equipos no está disponible');
        }

        // Validar la categoría del operador
        if (
            $rolId === 2 &&
            (int) $equipo['categoria_id'] !== (int) $categoriaId
        ) {
            throw new Exception('No tiene permiso para prestar ese equipo');
        }

        // Comprobar traslapes de fechas
        $consultaTraslape->execute([
            'equipo_id' => $equipoId,
            'fecha_inicio' => $inicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $fin->format('Y-m-d H:i:s')
        ]);

        $totalTraslapes = (int) $consultaTraslape->fetchColumn();
        $consultaTraslape->closeCursor();

        if ($totalTraslapes > 0) {
            throw new Exception(
                'El equipo con ID ' . $equipoId .
                ' ya tiene un préstamo activo en ese horario'
            );
        }
    }

    // Registrar el prestamo
    $consulta = $conexion->prepare(
        "INSERT INTO prestamos
            (usuario_id, fecha_inicio, fecha_fin_prevista,
             estado, observaciones)
         VALUES
            (:usuario_id, :fecha_inicio, :fecha_fin,
             'solicitado', :observaciones)"
    );

    $consulta->execute([
        'usuario_id' => $usuarioId,
        'fecha_inicio' => $inicio->format('Y-m-d H:i:s'),
        'fecha_fin' => $fin->format('Y-m-d H:i:s'),
        'observaciones' => $observaciones
    ]);

    // Obtener el ID del prestamo recien creado
    $prestamoId = $conexion->lastInsertId();

    // Relacionar los equipos con el prestamo
    $consultaDetalle = $conexion->prepare(
        "INSERT INTO prestamo_equipos
            (prestamo_id, equipo_id)
         VALUES
            (:prestamo_id, :equipo_id)"
    );

    foreach ($equiposValidados as $equipoId) {
        $consultaDetalle->execute([
            'prestamo_id' => $prestamoId,
            'equipo_id' => $equipoId
        ]);
    }

    // Confirmar todos los cambios
    $conexion->commit();

    header('Location: ../pages/crear_prestamo.php?mensaje=creado');
    exit;

} catch (Exception $e) {

    // quita los cambios si ocurre un error
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(400);
    exit('No se pudo registrar el préstamo: ' . $e->getMessage());
}
