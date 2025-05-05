<?php
    session_start();
    include 'includes/require_db.php';
    // include 'php/createLog.php';
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
            <h1 class="text-center mb-5">Nuestros Servicios</h1>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-camera fa-3x mb-3"></i>
                            <h3 class="card-title">Instalación</h3>
                            <p class="card-text">Instalación profesional de sistemas de seguridad en hogares y negocios.</p>
                            <a href="#" class="btn btn-vino select-service" data-service="instalacion">Seleccionar</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tools fa-3x mb-3"></i>
                            <h3 class="card-title">Mantenimiento</h3>
                            <p class="card-text">Mantenimiento preventivo y correctivo para sistemas de seguridad.</p>
                            <a href="#" class="btn btn-vino select-service" data-service="mantenimiento">Seleccionar</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-trash-alt fa-3x mb-3"></i>
                            <h3 class="card-title">Desinstalación</h3>
                            <p class="card-text">Desinstalación segura y profesional de equipos de seguridad.</p>
                            <a href="#" class="btn btn-vino select-service" data-service="desinstalacion">Seleccionar</a>
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

    <!-- se necesita modal para login -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login Requerido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Debes iniciar sesión para solicitar un servicio. ¿Deseas iniciar sesión ahora?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="login.html" class="btn btn-vino">Iniciar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
</body>
</html>