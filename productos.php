<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';

    if (!isset($_SESSION['productos']['page'])) $_SESSION['productos']['page'] = 0;
    $currentPage = $_SESSION['productos']['page'];

    $itemsPerPage = 8;

    $stmt = $enlace->prepare("SELECT * FROM products
            WHERE isDeleted = 0
            ORDER BY productStock DESC, productName ASC");
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalData = count($products);
    $totalPages = ceil($totalData / $itemsPerPage);
    $start = $currentPage * $itemsPerPage;
    $end = min($start + $itemsPerPage, $totalData);
    $pageData = array_slice($products, $start, $itemsPerPage);

    function failedProduct ($code = 0, $message = "¡Algo salio mal!", $showError = true) {
        if ($showError) $_SESSION['error'] = "Error $code: $message";
        $url = basename($_SERVER['PHP_SELF']); // Redirigir a la misma pagina
        header("location: $url");
        exit();
    }

    if (isset($_POST['productAdd'])) {
        $productId = $_POST['productAdd'] ?? NULL;
        $productIDs = array_column($products, "productId");

        if (!isset($productId)) {
            failedProduct(-1, "Algo salio mal");
        }

        $productId = intval($productId);
        
        if (!in_array($productId, $productIDs, true)) {
            failedProduct(-2, "Algo salio mal");
        }

        if (isset($_SESSION['cart']['items']) && in_array($productId, array_keys($_SESSION['cart']['items']), true)) {
            $_SESSION['cart']['items'][$productId]++;
        } else {
            $_SESSION['cart']['items'][$productId] = 1;
        }

        $_SESSION['success'] = "¡Producto agregado con éxito!";
    }
    
    if (isset($_POST['productBuy'])) {
        $productId = $_POST['productBuy'] ?? NULL;
        $productIDs = array_column($products, "productId");

        if (!isset($productId)) {
            failedProduct(-1, "Algo salio mal");
        }

        $productId = intval($productId);
        
        if (!in_array($productId, $productIDs, true)) {
            failedProduct(-2, "Algo salio mal");
        }

        if (isset($_SESSION['cart']) && in_array($productId, array_keys($_SESSION['cart']['items']), true)) {
            $_SESSION['cart']['items'][$productId]++;
        } else {
            $_SESSION['cart']['items'][$productId] = 1;
        }

        if (isset($_SESSION['user'])) {
            header("location: agendar.php");
            exit();
        } else {
            $_SESSION['login']['appointmentRedirect'] = 1;
            $_SESSION['success'] = "Por favor, inicia sesión.";
            header("location: login.php");
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        failedProduct(0, "si", false);
    }
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
            
            <div class="row d-flex justify-content-center">
                <?php
                    if (empty($pageData)) {
                        echo "<h2 class='text-center'>¡No hay productos disponibles!</h2>";
                    } else {
                        foreach ($pageData as $product) {
                            if (empty($product['productImgPath']) || is_null($product['productImgPath']) || !file_exists($product['productImgPath'])) {
                                $product['productImgPath'] = "img/product_placeholder.png";
                            }
                            if ($product['productStock'] > 0) {
                                $header = "
                                <div class='card-header'>
                                    Stock: {$product['productStock']}
                                </div>";
                                $buttons = "
                                <form method='post'>
                                    <div class='card-footer d-flex justify-content-center input-group'>
                                        <button type='submit' class='btn btn-vino btn-sm' name='productBuy' value='{$product['productId']}'>
                                            <i class='bi bi-clipboard me-2'></i>Agendar
                                        </button>
                                        <button type='submit' class='btn btn-primary btn-sm' name='productAdd' value='{$product['productId']}'>
                                            <i class='bi bi-cart me-2'></i>Añadir
                                        </button>
                                    </div>
                                </form>";
                            } else {
                                $header = "
                                <div class='card-header text-danger'>
                                    Sin stock
                                </div>";
                                $buttons = "
                                <div class='card-footer d-flex justify-content-center input-group'>
                                    <button type='button' class='btn btn-vino btn-sm' disabled>
                                        <i class='bi bi-clipboard me-2'></i>Agendar
                                    </button>
                                    <button type='button' class='btn btn-primary btn-sm' disabled>
                                        <i class='bi bi-cart me-2'></i>Añadir
                                    </button>
                                </div>";
                            }
                            echo "
                            <div class='col-lg-3 col-md-4 col-sm-6 mb-4'>
                                <div class='card h-100 product-card'>
                                    <img src='{$product['productImgPath']}' class='card-img-top' alt='Producto'>
                                    $header
                                    <div class='card-body'>
                                        <h5 class='card-title'>{$product['productName']}</h5>
                                        <p class='card-text'>{$product['productDescription']}</p>
                                    </div>
                                    $buttons
                                </div>
                            </div>";
                        }
                    }
                ?>
            </div>
        </div>
    </section>
    <div class="btn-group mb-5 d-flex justify-position-center" role="group">
        <?php
            if ($totalData > 0) {
        ?>
            <button type='button' class='btn border' data-session='page' data-sessionvalue='0'><i class='bi bi-arrow-bar-left'></i></button>
            <?php
                if ($currentPage > 4) {
                    $pageToGo = $currentPage - 5;
                    echo "<button type='button' class='btn border' data-session='page' data-sessionvalue='$pageToGo'><i class='bi bi-5-circle'></i></button>";
                }
            ?>
            <?php
                if ($currentPage < 3) {
                    $i = 0;
                } else {
                    $i = $currentPage - 2;
                }

                for ($j = $i; $j < $i + 5; $j++) {
                    if ($j < ceil($totalData / $itemsPerPage)) {
                        $button_text = $j + 1;
                        $active_text = ($j == $currentPage ? 'bg-primary text-light' : '');
                        echo "
                            <button type='button' class='btn border $active_text' data-session='page' data-sessionvalue='$j'>$button_text</button>
                        ";
                    }
                }
            ?>
            <?php
                if ($currentPage < ceil($totalData / $itemsPerPage) - 5) {
                    $pageToGo = $currentPage + 5;
                    echo "<button type='button' class='btn border' data-session='page' data-sessionvalue='$pageToGo'><i class='bi bi-5-circle'></i></button>";
                }
            ?>
            <button type='button' class='btn border' data-session='page' data-sessionvalue='<?= ceil($totalData / $itemsPerPage) - 1 ?>'><i class='bi bi-arrow-bar-right'></i></button>
        <?php
            } else {
        ?>
            <button type='button' class='btn border' disabled><i class='bi bi-ban'></i></button>
        <?php
            }
        ?>
    </div>
    

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
                let self = $(this);
                let criteria = self.attr('data-session');
                let data = self.attr('data-sessionvalue');
                saveSession(criteria, data);
            });

            function saveSession(criteria, data, unset = false, reload = true) {
                $.ajax({
                    url: "php/globalSetSession.php",
                    type: "POST",
                    data: {
                        criteria: criteria,
                        data: data,
                        unset: unset,
                        page: "productos"
                    },
                    success: function(response) {
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