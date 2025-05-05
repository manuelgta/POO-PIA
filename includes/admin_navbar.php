<?php
    // Funcion para ponerle la clase Active a la pagina de la navbar actual
    $currentURL = basename($_SERVER['PHP_SELF']);
    $urlData = [ // Se pueden agregar mas páginas a la navbar usando este array asociativo
        [
            "url" => "indexADMIN.php",
            "active" => "",
            "title" => "<i class='bi bi-speedometer me-2'></i>Dashboard"
        ],
        [
            "url" => "productosADMIN.php",
            "active" => "",
            "title" => "<i class='bi bi-dropbox me-2'></i>Productos"
        ],
        [
            "url" => "solicitudesADMIN.php",
            "active" => "",
            "title" => "<i class='bi bi-clipboard-check me-2'></i>Solicitudes"
        ],
        [
            "url" => "index.php",
            "active" => "",
            "title" => "<i class='bi bi-door-open me-2'></i>Salir"
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
<nav class="admin-sidebar bg-dark">
    <div class="sidebar-header text-center py-4">
        <img src="img/logo sin fondo.png" alt="CIYSE Logo" height="40">
        <h4 class="text-white mt-3">Panel de Administración</h4>
    </div>
    <ul class="nav flex-column">
        <?php
            foreach ($urlData as $url) {
                echo "
                <li class='nav-item'>
                    <a class='nav-link {$url['active']}' href='{$url['url']}'>{$url['title']}</a>
                </li>";
            }
        ?>
    </ul>
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