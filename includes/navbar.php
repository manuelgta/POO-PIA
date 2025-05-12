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
                    foreach ($urlData as $row) {
                        $currentURL = basename($_SERVER['PHP_SELF']);
                        $active = '';
                        $link = $row['urlAddress'];
                        if ($currentURL == $link) $active = 'active';

                        echo "
                        <li class='nav-item'>
                            <a class='nav-link $active' href='$link'>
                                {$row['urlTitle']}
                            </a>
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
<!-- <?php
    function printStuff($parentKey, $array) {
        if (is_array($array)) {
            ksort($array); // Ordenar por clave A-Z
            $i = 0;
            $length = count($array);
            echo "$parentKey: [";
            foreach ($array as $key => $col) {
                $i++;
                printStuff($key, $col);
                if ($i != $length) echo ", ";
            }
            echo "] /";
        } else {
            echo "$parentKey: $array";
        }
    }
    
        printStuff('SESSION', $_SESSION);
        echo ". ";
?> -->