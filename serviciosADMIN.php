<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (isset($_POST['serviceNew'])) {
        $iconId = $_POST['iconId'] ?? NULL;
        $serviceName = $_POST['serviceName'] ?? NULL;
        $serviceDescription = $_POST['serviceDescription'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($iconId) || is_null($serviceName) || is_null($serviceDescription)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("INSERT INTO services (iconId, serviceName, serviceDescription)
                    VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $iconId, $serviceName, $serviceDescription);
            $stmt->execute();
            $regId = $enlace->insert_id;

            if (!insertLog($enlace, 'services', $regId, $_SESSION['user']['id'], $serviceName)) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Servicio agregado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "¡Ya existe una servicio con ese nombre!";
            }
            $enlace->rollback();
        }
    }

    if (isset($_POST['serviceEdit'])) {
        $serviceId = $_POST['serviceId'] ?? NULL;
        $iconId = $_POST['iconId'] ?? NULL;
        $serviceName = $_POST['serviceName'] ?? NULL;
        $serviceDescription = $_POST['serviceDescription'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($serviceId) || is_null($iconId) || is_null($serviceName) || is_null($serviceDescription)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("SELECT * FROM services WHERE serviceId = ?");
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $service = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (count($service) != 1) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $service = $service[0];

            $gotUpdated = false;
            $oldValues = "";
            $newValues = "";
            if ($iconId != $service['iconId']) {
                $oldValues .= "Icon: {$service['iconId']} ";
                $newValues .= "Icon: $iconId ";
                $gotUpdated = true;
            }
            if ($serviceName != $service['serviceName']) {
                $oldValues .= "Name: {$service['serviceName']} ";
                $newValues .= "Name: $serviceName ";
                $gotUpdated = true;
            }
            if ($serviceDescription != $service['serviceDescription']) {
                $oldValues .= "Description: {$service['serviceDescription']} ";
                $newValues .= "Description: $serviceDescription ";
                $gotUpdated = true;
            }

            if ($gotUpdated) {
                $stmt = $enlace->prepare("UPDATE services SET iconId = ?, serviceName = ?, serviceDescription = ? WHERE serviceId = ?");
                $stmt->bind_param("issi", $iconId, $serviceName, $serviceDescription, $serviceId);
                $stmt->execute();

                if (!updateLog($enlace, "services", $serviceId, $_SESSION['user']['id'], $oldValues, $newValues)) {
                    throw new Exception("¡Algo salio mal!", -4);
                }
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Servicio editado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "¡Ya existe un servicio con ese nombre!";
            }
            $enlace->rollback();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        $url = basename($_SERVER['PHP_SELF']); // Redirigir a la misma pagina
        header("location: $url");
        exit();
    }

    $stmt = $enlace->prepare("SELECT s.*, i.iconBi
            FROM services s
            JOIN icons i ON i.iconId = s.iconId
            WHERE s.isDeleted = 0");
    $stmt->execute();

    $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $enlace->prepare("SELECT iconBi as bi, iconId as id FROM icons WHERE isDeleted = 0");
    $stmt->execute();

    $icons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Lista de servicios</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/admin_navbar.php';
        ?>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Servicios</h2>
                    <button class="btn btn-vino" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-2"></i>Agregar Servicios
                    </button>
                </div>
                
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Icono</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($services as $service) {
                                            echo "
                                            <tr>
                                                <td>{$service['serviceId']}</td>
                                                <td><i class='bi bi-{$service['iconBi']}'></i></td>
                                                <td>{$service['serviceName']}</td>
                                                <td>{$service['serviceDescription']}</td>
                                                <td>
                                                    <button type='button' value='{$service['serviceId']}' class='btn btn-sm btn-primary me-1 serviceEditButton'>Editar</button>
                                                </td>
                                            </tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal para agregar servicios -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-vino text-white">
                    <h5 class="modal-title">Agregar Nuevo servicio</h5>
                    <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="input-label">Icono</label>
                                <div class="input-group">
                                    <select class="form-select col-8" id="iconId1" name="iconId" required>
                                        <option value="" selected>-- Elije --</option>
                                        <?php
                                            foreach ($icons as $icon) {
                                                echo "<option value='{$icon['id']}'>{$icon['bi']}</option>";
                                            }
                                        ?>
                                    </select>
                                    <span class="input-group-text col-4"><i class="bi bi- me-2"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del servicio</label>
                                <input type="text" name="serviceName" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="serviceDescription" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="serviceNew" class="btn btn-vino">Guardar serviceo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal para editar servicios -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-vino text-white">
                    <h5 class="modal-title">Editar un servicio</h5>
                    <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="serviceId" id="serviceId">
                            <div class="col-md-6 mb-3">
                                <label class="input-label">Icono</label>
                                <div class="input-group">
                                    <select class="form-select col-8" id="iconId2" name="iconId" required>
                                        <option value="" selected>-- Elije --</option>
                                        <?php
                                            foreach ($icons as $icon) {
                                                echo "<option value='{$icon['id']}'>{$icon['bi']}</option>";
                                            }
                                        ?>
                                    </select>
                                    <span class="input-group-text col-4"><i class="bi bi- me-2"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del servicio</label>
                                <input type="text" name="serviceName" id="serviceName" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="serviceDescription" id="serviceDescription" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="serviceEdit" class="btn btn-vino">Guardar servicios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('#iconId1, #iconId2').on('change', function () {
                $(this).siblings('span').children('i').attr('class', 'bi bi-' + $('#' + $(this).attr('id') + ' option:selected').text() + ' me-2');
            });

            $('[data-bs-dismiss]').on('click', function () {
                let modal = $(this).closest('.modal');
                let form = modal.find('form');
                form.find('input, textarea, select, file').val('');
                form.find('span').children('i').attr('class', 'bi bi- me-2');
                form.find('img').attr("src", "").hide();
            });

            $('.serviceEditButton').on('click', function () {
                let serviceId = $(this).val();
                globalGet(serviceId);
            });

            function globalGet (id = null) {
                
                if (id) {
                    $.ajax({
                        url: "globalGet.php", // Reemplaza con la ruta correcta de tu archivo PHP
                        type: "POST",
                        data: { id: id, page: "services" },
                        dataType: "json",
                        success: function (response) {
                            let service = response[0];
                            $("#serviceId").val(service.serviceId);
                            $("#iconId2").val(service.iconId).siblings('span').children('i').attr('class', 'bi bi-' + $('#iconId2 option:selected').text() + ' me-2');;
                            $("#serviceName").val(service.serviceName);
                            $("#serviceDescription").val(service.serviceDescription);
                            $('#editServiceModal').modal('show');
                        },
                        error: function () {
                            console.error("Error AJAX:", status, error); // <-- Ver error en consola
                            console.log("Respuesta completa:", xhr.responseText);
                            alert("Error al obtener los vehiculos.");
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>