<?php
//  configuracion para cualquier tipo de base de datos
$tipo_bd = "mysql"; 
$host_bd = "localhost";
$user = "root";
$pass = "";
$db_name = ""; 

try {
    // agrego todo en la cadena dsn
    $dsn = $tipo_bd . ":host=" . $host_bd . ";dbname=" . $db_name . ";charset=utf8";
    
    $conexion = new PDO($dsn, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $err) {
    echo "Fallo la conexion: " . $err->getMessage();
}
?>