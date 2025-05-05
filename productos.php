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
    <title>CIYSE - Productos</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/navbar.php';
        ?>
    </header>

    <section class="product-section py-5">
        <div class="container">
            <h1 class="text-center mb-5">Nuestros Productos</h1>
            
            <div class="row">
                <!-- producto1 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <img src="img/producto1.jpg" class="card-img-top" alt="Cámara de seguridad">
                        <div class="card-body">
                            <h5 class="card-title">Cámara HD 1080p</h5>
                            <p class="card-text">Cámara de seguridad con resolución Full HD 1080p, visión nocturna y resistencia a la intemperie.</p>
                        </div>
                    </div>
                </div>
                
                <!-- producto2 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <img src="img/producto2.jpg" class="card-img-top" alt="DVR">
                        <div class="card-body">
                            <h5 class="card-title">Grabador Digital 4 Canales</h5>
                            <p class="card-text">Sistema de grabación digital para 4 cámaras con almacenamiento de hasta 2TB.</p>
                        </div>
                    </div>
                </div>
                
                <!-- producto3 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <img src="img/producto3.jpg" class="card-img-top" alt="Kit de seguridad">
                        <div class="card-body">
                            <h5 class="card-title">Kit de Seguridad Básico</h5>
                            <p class="card-text">Kit completo con 4 cámaras, grabador digital y todos los accesorios necesarios para instalación.</p>
                        </div>
                    </div>
                </div>
                
                <!-- rrellenar mas -->
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

    <?php include 'includes/body_includes.php'; ?>
</body>
</html>