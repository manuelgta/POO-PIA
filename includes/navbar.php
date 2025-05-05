<?php
    // Funcion para ponerle la clase Active a la pagina de la navbar actual
    $currentURL = basename($_SERVER['PHP_SELF']);
    $urlData = [ // Se pueden agregar mas páginas a la navbar usando este array asociativo
        [
            "url" => "index.php",
            "active" => "",
            "title" => "Inicio"
        ],
        [
            "url" => "productos.php",
            "active" => "",
            "title" => "Productos"
        ],
        [
            "url" => "servicios.php",
            "active" => "",
            "title" => "Servicios"
        ],
        [
            "url" => "login.php",
            "active" => "",
            "title" => "Iniciar Sesión"
        ] /* ,
        [
            "url" => "ejemplo.php",
            "active" => "",
            "title" => "Titulo de ejemplo"
        ] */
    ];

    $urls = array_column($urlData, "url");
    foreach ($urls as $key => $url) {
        if ($currentURL == $url) $urlData[$key]["active"] = "active";
    }
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-vino">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="img/logo sin fondo.png" alt="CIYSE Logo" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php
                    foreach ($urlData as $url) {
                        echo "
                        <li class='nav-item'>
                            <a class='nav-link {$url['active']}' href='{$url['url']}'>{$url['title']}</a>
                        </li>";
                    }
                ?>
            </ul>
        </div>
    </div>
</nav>
<?php
    if (isset($_SESSION['error'])) { // Mensajes de error
        echo "
        <div class='alert alert-danger alert-dismissible fade show m-5 auto-dismiss' role='alert' style='position: fixed; z-index: 10000;'>
            <strong>{$_SESSION['error']}</strong>
        </div>";
        unset($_SESSION['error']);
    } if (isset($_SESSION['success'])) { // Mensajes de exito
        echo "
        <div class='alert alert-success alert-dismissible fade show m-5 auto-dismiss' role='alert' style='position: fixed; z-index: 10000;'>
            <strong>{$_SESSION['success']}</strong>
        </div>";
        unset($_SESSION['success']);
    }
?>