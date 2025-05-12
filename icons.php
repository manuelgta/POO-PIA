<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (isset($_POST['newIcon'])) {
        $iconBi = $_POST['iconBi'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if(is_null($iconBi)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("INSERT INTO icons (iconBi)
            VALUES (?)");
            $stmt->bind_param("s", $iconBi);
            $stmt->execute();
            $regId = $enlace->insert_id;

            if (!insertLog($enlace, "icons", $regId, $_SESSION['user']['id'], $iconBi)) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Icono registrado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "¡Ese icono ya esta registrado!";
            }
            $enlace->rollback();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $url = basename($_SERVER['PHP_SELF']);
        header("location: $url");
        exit();
    }

    $stmt = $enlace->prepare("SELECT * FROM icons");
    $stmt->execute();

    $icons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Iconos</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php include 'includes/admin_navbar.php'; ?>
    </header>
    <div class="admin-wrapper">
        <div class="admin-main">
            <nav class="admin-topbar navbar navbar-expand navbar-light bg-light">
                <div class="container-fluid">
                    <button class="btn btn-sm btn-vino ms-auto">
                        <i class="bi bi-person-circle me-2"></i>Administrador
                    </button>
                </div>
            </nav>
            <div class="admin-content p-4">
                <h1 class="text-center mb-4">Iconos</h1>
                <div class="row">
                    <div class="col-6">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Agregar</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="input-label">Icono</label>
                                            <input type="text" name="iconBi" id="iconBi" class="form-control" placeholder="0-circle" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Preview</label>
                                            <span id="preview" class="input-group-text"><i class="bi bi- me-2"></i>Pagina</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="newIcon" class="btn btn-vino">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-6">
                        <iframe src="https://icons.getbootstrap.com/" frameborder="0" style='width: 100%; height: 100vh;'></iframe>
                    </div>
                </div>
            </div>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('#iconBi').on('input', function () {
                $('#preview i').attr('class', 'bi bi-' + $('#iconBi').val() + ' me-2');
            });
        });
    </script>
</body>
</html>