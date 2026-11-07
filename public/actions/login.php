<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $correo = $_POST['mail'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($correo) || empty($pass)) {
        echo "Debe completar ambos campos";
        exit;
}
$consulta = $conexion->prepare(
    "SELECT id, nombre, correo, password_hash, rol_id, categoria_id
     FROM usuarios
     WHERE correo = :correo
     AND activo = 1
     AND eliminado = 0"
);
$consulta->execute(['correo' => $correo]);

$usuario = $consulta->fetch(PDO::FETCH_ASSOC);

if($usuario){
    if (password_verify($pass, $usuario['password_hash'])) {
    echo "Contraseña correcta";
    // regenera la secion para evitar que se roben la secion
     session_regenerate_id(true);

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['rol_id'] = $usuario['rol_id'];
    $_SESSION['categoria_id'] = $usuario['categoria_id'];
    header('Location: ../pages/menu.php');
    exit;

} else {
    echo "correo o contraseña no son validas";
}

}else{
    echo"correo o contraseña no son validas";

}



}












?>