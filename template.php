<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/ulrRestrictions.php';
    include 'php/createLog.php';

    if (isset($_POST['customAction'])) {

        $enlace->begin_transaction(); // Para tratar multiples ejecuciones a la base de datos

        try {

            if($si) {
                throw new Exception("Error custom", -1); // Mandar a catch
            }

            $enlace->commit(); // Guardar todos los cambios hechos
            $_SESSION['success'] = "¡Mensaje de exito!"; // Mensaje de exito que aparece en navbar.php
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}"; // Mensaje de error junto con el codigo
            if ($e->getCode() == -1) $_SESSION['error'] = "Mensaje custom por error custom"; // Para leer codigos de error custom
            $enlace->rollback(); // Deshacer cambios hechos en la base de datos en caso de error
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        $url = basename($_SERVER['PHP_SELF']); // Redirigir a la misma pagina
        header("location: $url");
        exit();
    }

    $stmt = $enlace->prepare("SELECT tn.* FROM tableName tn
            JOIN otherTable ot ON ot.otherTableId = tn.tableNameId
            WHERE tn.tableNameId = ?
            AND ot.otherField = ?
            AND isDeleted = 0");
    $stmt->bind_param("is", $id, $string); // Bind param para evitar inyecciones de SQL
    // ("is") son los tipos de datos que se va a utilizar, i es integer, s string, d decimal
    $stmt->execute();

    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Conseguir todo en un array asociativo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Titulo</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/navbar.php';
        ?>
    </header>

    <div class="container py-5">
        <h1 class="text-center mb-4">Titulo</h1>
        
    </div>

    <?php include 'includes/body_includes.php'; ?>
</body>
</html>