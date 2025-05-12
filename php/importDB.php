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

$enlace->select_db("rifasmgc_poo_project");

// Importar el archivo SQL
$sqlFile = 'ciysedbv0-5.sql'; // Asegúrate de que esté en el mismo directorio

$comandosSQL = file_get_contents($sqlFile);

if ($enlace->multi_query($comandosSQL)) {
    echo "Archivo SQL importado correctamente.";
    // Limpiar múltiples resultados
    do {
        $enlace->store_result();
    } while ($enlace->next_result());
} else {
    echo "Error al importar archivo SQL: " . $enlace->error;
}

$enlace->close();
?>