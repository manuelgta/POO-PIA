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

    

    if (isset($_POST['editIcon'])) {
        $iconId = $_POST['iconId'] ?? NULL;
        $iconBi = $_POST['iconBi'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if(is_null($iconBi) || is_null($iconId)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("SELECT iconBi FROM icons WHERE iconId = ?");
            $stmt->bind_param("i", $iconId);
            $stmt->execute();
            $icon = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if (empty($icon)) {
                throw new Exception("¡Icono no encontrado!", -1);
            }
            $iconBiOld = $icon[0]['iconBi'];

            $stmt = $enlace->prepare("UPDATE icons SET iconBi = ?
            WHERE iconId = ?");
            $stmt->bind_param("si", $iconBi, $iconId);
            $stmt->execute();

            if (!updateLog($enlace, "icons", $iconId, $_SESSION['user']['id'], $iconBiOld, $iconBi)) {
                throw new Exception("¡Algo salio mal!", -3);
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Icono guardado con exito!";
            unset($_SESSION['urls']);
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
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Editar</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="input-label">Seleccionar icono</label>
                                            <select name="iconId" id="iconId" class="form-select">
                                                <option value="">Seleccionar</option>
                                                <?php foreach ($icons as $icon): ?>
                                                    <option value="<?= $icon['iconId'] ?>"><?= $icon['iconBi'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Icono</label>
                                            <input type="text" name="iconBi" id="iconName" class="form-control" placeholder="0-circle" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Preview</label>
                                            <span id="preview" class="input-group-text"><i class="bi bi- me-2"></i>Pagina</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="editIcon" class="btn btn-vino">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('#iconBi, #iconName').on('input', function () {
                let self = $(this);
                let iconBi = self.val();
                self.closest('.row').find('span i').attr('class', 'bi bi-' + self.val() + ' me-2');
            });

            $('#iconId').on('change', function () {
                globalGet($(this).val());
            });

            function globalGet (id = null) {
                
                if (id) {
                    $.ajax({
                        url: "globalGet.php", // Reemplaza con la ruta correcta de tu archivo PHP
                        type: "POST",
                        data: { id: id, page: "icons" },
                        dataType: "json",
                        success: function (response) {
                            let icon = response[0];
                            $("#iconName").val(icon.iconBi);
                            $("#iconName").closest('.row').find('span i').attr('class', 'bi bi-' + $('#iconName').val() + ' me-2');
                        },
                        error: function (response) {
                            console.error("Error AJAX:", status, response); // <-- Ver error en consola
                            alert("Error al obtener los vehiculos.");
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>