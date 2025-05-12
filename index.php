<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Inicio</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/navbar.php';
        ?>
    </header>

    <header class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h1>CIYSE - Seguridad Integral</h1>
                    <p class="lead">Especialistas en instalación, mantenimiento y desinstalación de cámaras de seguridad.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="about-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="section-title">Quiénes Somos</h2>
                    <p>CIYSE es una empresa especializada en soluciones de seguridad para hogares y negocios. Con más de 10 años de experiencia en el mercado, ofrecemos servicios profesionales de instalación, mantenimiento y desinstalación de sistemas de seguridad.</p>
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title">Qué Ofrecemos</h2>
                    <ul>
                        <li>Sistemas de cámaras de seguridad de última generación</li>
                        <li>Instalación profesional</li>
                        <li>Mantenimiento preventivo y correctivo</li>
                        <li>Desinstalación segura de equipos</li>
                        <li>Soporte técnico especializado</li>
                    </ul>
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

    <?php include 'includes/body_includes.php'; ?>
</body>
</html>