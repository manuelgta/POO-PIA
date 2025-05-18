<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';

    if (isset($_SESSION['servicios']['id'])) {
        if (isset($_SESSION['user'])) {
            $_SESSION['cart']['service'] = $_SESSION['servicios']['id'];
            if (isset($_SESSION['servicios']['appointmentRedirect'])) {
                header('location: agendar.php');
            } else {
                header('location: productos.php');
            }
            unset($_SESSION['servicios']);
            exit();
        } else {
            unset($_SESSION['servicios']);
            $_SESSION['login']['serviceRedirect'] = 1;
            $_SESSION['error'] = "Primero debes iniciar sesión.";
            header('location: login.php');
            exit();
        }
    }

    unset($_SESSION['login']['serviceRedirect']);
    unset($_SESSION['cart']['service']);

    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Servicios</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/navbar.php';
        ?>
    </header>

    <section class="services-section py-5">
        <div class="container">
            
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

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('[data-session]').on('click', function () {
                var self = $(this);
                var criteria = self.attr('data-session');
                console.log(criteria);
                var data = self.val();
                saveSession(criteria, data);
            });

            function saveSession(criteria, data, reload = true) {
                $.ajax({
                    url: "php/globalSetSession.php",
                    type: "POST",
                    data: {
                        criteria: criteria,
                        data: data,
                        page: "servicios"
                    },
                    success: function(response) {
                        console.log(response);
                        if (reload)
                            window.location.reload();
                    },
                    error: function(response) {
                        alert("Error al procesar el evento");
                        console.log(response);
                    }
                });
            }
        });
    </script>
</body>
</html>