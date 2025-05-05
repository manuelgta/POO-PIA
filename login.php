<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "poo_project";

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM cliente WHERE correo = '$correo'";
    $resultado = mysqli_query($enlace, $sql);
    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario) {
        if (password_verify($password, $usuario['password'])) {
            echo "ok";  
        } else {
            echo "Contraseña incorrecta";
        }
    } else {
        echo "Correo no registrado";
    }
}
?>
