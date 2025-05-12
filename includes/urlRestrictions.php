<?php

    $urlData = [];
    $urls = [];
    $adminId = 1; // Id de administrador
    $invitedId = 4; // Id del rol de invitado
    if (!isset($_SESSION['urls'])) {
        if (isset($_SESSION['user']['role'])) {
            if ($_SESSION['user']['role'] == $adminId) {
                $stmt = $enlace->prepare("SELECT DISTINCT u.urlTitle, u.urlAddress, i.iconBi FROM urls u
                        LEFT JOIN icons i ON i.iconId = u.iconId
                        WHERE u.isDisabled = 0 AND u.isDeleted = 0
                        ORDER BY showOrder ASC");
                $stmt->execute();
                $urlData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $urls = array_column($urlData, 'urlAddress');
            } else {
                $stmt = $enlace->prepare("SELECT DISTINCT u.urlTitle, u.urlAddress, i.iconBi FROM urls u
                        JOIN icons i ON i.iconId = u.iconId
                        LEFT JOIN role_urls ru ON ru.urlId = u.urlId
                        WHERE (ru.roleId = ? OR u.allowAll = 1)
                        AND (u.isDisabled = 0 AND u.isDeleted = 0)
                        ORDER BY showOrder ASC");
                $stmt->bind_param("i", $_SESSION['user']['role']);
                $stmt->execute();
                $urlData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $urls = array_column($urlData, 'urlAddress');
            }
        } else {
            $stmt = $enlace->prepare("SELECT DISTINCT u.urlTitle, u.urlAddress, i.iconBi FROM urls u
                    JOIN icons i ON i.iconId = u.iconId
                    LEFT JOIN role_urls ru ON ru.urlId = u.urlId
                    WHERE (ru.roleId = $invitedId OR u.allowAll = 1)
                    AND (u.isDisabled = 0 AND u.isDeleted = 0)
                    ORDER BY showOrder ASC");
            $stmt->execute();
            $urlData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $urls = array_column($urlData, 'urlAddress');
        }
        $_SESSION['urls'] = $urlData;
    } else {
        $urlData = $_SESSION['urls'];
        $urls = array_column($urlData, 'urlAddress');
    }

    if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == $adminId) {
        $role = $role ?? '';
        echo "<!-- $role";
        foreach ($urls as $key => $url) echo " $url";
        echo "-->";
    }

    $currentURL = basename($_SERVER['PHP_SELF']);
    if (!in_array($currentURL, $urls, true) && $currentURL != '404.php') {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>CIYSE - Prohibido</title>";
        include 'includes/head_includes.php';
        echo "
        </head>
        <body>
            <header>";
        include 'includes/navbar.php';
        echo "
            </header>

            <div class='container py-5'>
                <h1 class='text-center mb-4'>¡Uy, no tienes acceso a este sitio!</h1>
                <a href='index.php'>Da clic aqui para volver.</a>
            </div>";
        include 'includes/body_includes.php';
        die();
    }

    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_ES');
?>
