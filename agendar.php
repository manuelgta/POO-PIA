<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';

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
                            <form id="bookingForm">
                                <div class="row">
                                    <div class="col-md-2 mb-3 p-2">
                                        <a href="servicios.php" class="btn btn-primary">Servicios <i class="bi bi-wrench"></i></a>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Tipo de Servicio</label>
                                        <select name="serviceId" class="form-select" data-session="serviceId">
                                            <option value=""></option>
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
                                    <div class="mb-3">
                                        <label class="form-label">Producto Seleccionado</label>
                                        <input type="text" class="form-control" id="selectedProduct" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bookingDate" class="form-label">Fecha</label>
                                        <input type="date" class="form-control" id="bookingDate" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bookingTime" class="form-label">Hora del Servicio</label>
                                        <input type="time" class="form-control" id="bookingTime" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bookingAddress" class="form-label">Dirección</label>
                                        <textarea class="form-control" id="bookingAddress" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bookingNotes" class="form-label">Notas Adicionales</label>
                                        <textarea class="form-control" id="bookingNotes" rows="3"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-vino w-100">Confirmar Servicio</button>
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