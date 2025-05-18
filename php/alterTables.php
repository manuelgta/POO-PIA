<?php
// ADVERTENCIA, este archivo puede eliminar toda la base de datos
// favor de no usar salvo que sepas lo que haces
/*
$servidor = '162.241.60.169';
$usuario = 'rifasmgc_poo_user';
$clave = 'q9R~_HR0{WDi';
$baseDeDatos = 'rifasmgc_poo_project';

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);

if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}

$sql = "
ALTER TABLE `requests`
MODIFY `requestComments` TEXT NULL;
";

if ($enlace->query($sql) === TRUE) {
    echo "Columna 'navbarShow' agregada correctamente y clave foránea establecida.";
} else {
    echo "Error al modificar la tabla: " . $enlace->error;
}

$enlace->close();
?>
