<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "poo_project";

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $password = $_POST['password'];
    $confirmar = $_POST['confirmar'];

    if ($password !== $confirmar) {
        echo "Las contraseñas no coinciden";
        exit();
    }

    $sql = "INSERT INTO cliente (nombre, correo, telefono, password) 
            VALUES ('$nombre', '$correo', '$telefono', '$password')";

    if (mysqli_query($enlace, $sql)) {
        echo "ok"; 
    } else {
        echo "error";
    }
}
?>