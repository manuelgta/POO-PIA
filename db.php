<?php
$servidor = '162.241.60.169';
$usuario = 'rifasmgc_poo_user';
$clave = 'q9R~_HR0{WDi';
$baseDeDatos = 'rifasmgc_poo_project';

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);

if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}