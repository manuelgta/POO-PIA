<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (empty($_SESSION['cart']['items'])) {
        header ('location: productos.php');
        $_SESSION['error'] = "Debe tener productos en su carrito para agendar";
        exit();
    }

    if (isset($_POST['requestNew'])) {
        $serviceId = $_POST['serviceId'] ?? NULL;
        $bookingDate = $_POST['bookingDate'] ?? NULL;
        $bookingTime = $_POST['bookingTime'] ?? NULL;
        $requestAddress = $_POST['requestAddress'] ?? NULL;
        $requestComments = $_POST['requestComments'] ?? NULL;
        $userId = $_SESSION['user']['id'] ?? NULL;

        $enlace->begin_transaction();

        try {
            if (in_array(NULL, [$serviceId, $bookingDate, $bookingTime, $requestAddress, $userId], true)) {
                throw new Exception("Algo salio mal!", -1);
            }

            $requestDate = $bookingDate . ' ' . $bookingTime . ':00';

            $stmt = $enlace->prepare("INSERT INTO requests (serviceId, statusId, userId, requestDate, requestAddress, requestComments)
                    VALUES (?, 1, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $serviceId, $userId, $requestDate, $requestAddress, $requestComments);
            $stmt->execute();
            $regId = $enlace->insert_id;

            foreach ($_SESSION['cart']['items'] as $productId => $amount) {
                $stmt = $enlace->prepare("INSERT INTO request_products (requestId, productId, productAmount)
                        VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $regId, $productId, $amount);
                $stmt->execute();
            }

            if (!insertLog($enlace, "requests", $regId, $userId, $requestDate)) {
                throw new Exception("Algo salio mal!", -2);
            }
            unset($_SESSION['appointment']);
            unset($_SESSION['cart']);
            $enlace->commit();
            $_SESSION['success'] = "¡Ha registrado su pedido con éxito, nos pondremos en contacto con usted!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        header("location: index.php");
        exit();
    }

    $stmt = $enlace->prepare("SELECT * FROM services
            WHERE isDeleted = 0");
    $stmt->execute();

    $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Agendar Servicio</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/navbar.php';
        ?>
    </header>

    <section class="booking-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-vino text-white">
                            <h3 class="mb-0">Agendar Servicio</h3>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-2 mb-3 p-2">
                                        <a href="index.php" class="btn btn-primary">Ver Servicios <i class="bi bi-wrench"></i></a>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label for="serviceId" class="form-label">Tipo de Servicio</label>
                                        <select id="serviceId" name="serviceId" class="form-select" data-session="serviceId">
                                            <option value="">-- Selecciona uno --</option>
                                            <?php
                                                foreach ($services as $service) {
                                                    $selected = "";
                                                    if (isset($_SESSION['appointment']['serviceId']) && $_SESSION['appointment']['serviceId'] == $service['serviceId'])
                                                        $selected = "selected";
                                                    echo "
                                                    <option value='{$service['serviceId']}' $selected>{$service['serviceName']}</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <div class="row">
                                            <div class="col">
                                                <label for="bookingDate" class="form-label">Fecha</label>
                                                <input type="date" class="form-control" name="bookingDate" id="bookingDate"
                                                data-session="bookingDate" required value="<?= $_SESSION['appointment']['bookingDate'] ?? "" ?>">
                                            </div>
                                            <div class="col">
                                                <label for="bookingTime" class="form-label">Hora del Servicio</label>
                                                <input type="time" class="form-control" name="bookingTime" id="bookingTime"
                                                data-session="bookingTime" required value="<?= $_SESSION['appointment']['bookingTime'] ?? "" ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Productos</label>
                                        <?php
                                            $productIDs = array_keys($_SESSION['cart']['items']);

                                            // Crear los placeholders (?, ?, ?, ...)
                                            $placeholders = implode(',', array_fill(0, count($productIDs), '?'));
                                            // Crear el string de tipos (todos 'i' porque son enteros)
                                            $types = str_repeat('i', count($productIDs));

                                            $stmt = $enlace->prepare("SELECT * FROM products WHERE productId IN ($placeholders)");
                                            $stmt->bind_param($types, ...$productIDs);
                                            $stmt->execute();
                                            $productsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                            $productKeys = array_column($_SESSION['cart']['items'], "productId");
                                            
                                            foreach ($productsData as $item) {
                                                if (empty($item['productImgPath']) || !file_exists($item['productImgPath'])) {
                                                    $item['productImgPath'] = "img/product_placeholder.png";
                                                }
                                                $amount = $_SESSION['cart']['items'][$item['productId']];
                                                $item["productPrice"] = 0;
                                                $price = number_format($item['productPrice'], 2);

                                                echo "
                                                <div class='row border-bottom py-3 align-items-center'>
                                                    <div class='col-3 col-md-2'>
                                                        <img src='{$item['productImgPath']}' alt='producto' class='img-fluid img-thumbnail zoomable-img'>
                                                    </div>
                                                    <div class='col-9 col-md-10'>
                                                        <div class='d-flex justify-content-between'>
                                                            <div class='me-3'>
                                                                <h5 class='mb-1'>{$item['productName']}</h5>
                                                                <p class='mb-1 text-muted text-truncate product-description'>{$item['productDescription']}</p>
                                                            </div>
                                                            <button type='button' data-cartRemove='{$item['productId']}' class='btn btn-sm btn-outline-danger'>Eliminar</button>
                                                        </div>
                                                        <div class='d-flex justify-content-end mt-2'>
                                                            <select data-cartChange='{$item['productId']}' class='form-select form-select-sm w-auto'>
                                                ";

                                                // Selector de cantidad
                                                for ($i = 1; $i <= $item['productStock']; $i++) {
                                                    $selected = ($i == $amount) ? "selected" : "";
                                                    echo "<option value='$i' $selected>$i</option>";
                                                }

                                                echo "
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                ";
                                            }
                                        ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bookingAddress" class="form-label">Dirección</label>
                                        <textarea class="form-control" name="requestAddress" id="bookingAddress" rows="3" required data-session="bookingAddress"><?= $_SESSION['appointment']['bookingAddress'] ?? "" ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bookingNotes" class="form-label">Notas Adicionales</label>
                                        <textarea class="form-control" name="requestComments" id="bookingNotes" rows="3" data-session="bookingNotes"><?= $_SESSION['appointment']['bookingAddress'] ?? "" ?></textarea>
                                    </div>
                                </div>
                                <button type="submit" name="requestNew" class="btn btn-vino w-100">Confirmar Servicio</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 CIYSE.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Contacto: contacto@ciyse.com | Tel: +52 81 8989 4539</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- modal confirmacion -->
    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Servicio Agendado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tu servicio ha sido agendado exitosamente. Nos pondremos en contacto contigo para confirmar los detalles.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-vino" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('[data-session]').on('change', function () {
                let self = $(this);
                let criteria = self.attr('data-session');
                let data = self.val();
                saveSession(criteria, data);
            });

            function saveSession (criteria, data, reload = false, unset = false) {
                $.ajax({
                    url: "php/globalSetSession.php",
                    type: "POST",
                    data: {
                        criteria: criteria,
                        data: data,
                        unset: unset,
                        page: "appointment"
                    },
                    success: function(response) {
                        if (reload) window.location.reload();
                    },
                    error: function(response) {
                        alert("Error al eliminar el registro");
                        console.log(response);
                    }
                });
            }
        });
    </script>
</body>
</html>