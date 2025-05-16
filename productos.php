<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';

    $stmt = $enlace->prepare("SELECT * FROM products
            WHERE isDeleted = 0");
    $stmt->execute();
    $allProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $products = [];
    $unavailableProducts = [];
    foreach ($allProducts as $product) {
        if ($product['productStock'] > 0) {
            $products[] = $product;
        } else {
            $unavailableProducts[] = $product;
        }
    }

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

        if (in_array($productId, array_keys($_SESSION['cart']['items']), true)) {
            $_SESSION['cart']['items'][$productId]++;
        } else {
            $_SESSION['cart']['items'][$productId] = 1;
        }

        if (isset($_SESSION['cart']['service'])) {
            header("location: agendar.php");
            exit();
        } else {
            $_SESSION['servicios']['appointmentRedirect'] = 1;
            $_SESSION['success'] = "Por favor, elija un servicio.";
            header("location: servicios.php");
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
                    if (empty($products)) {
                        echo "<h2 class='text-center'>¡No hay productos disponibles!</h2>";
                    } else {
                        foreach ($products as $product) {
                            if (empty($product['productImgPath']) || is_null($product['productImgPath'])) {
                                $product['productImgPath'] = "img/product_placeholder.png";
                            }
                            echo "
                            <div class='col-lg-3 col-md-4 col-sm-6 mb-4'>
                                <div class='card h-100 product-card' data-session='producto'>
                                    <img src='{$product['productImgPath']}' class='card-img-top' alt='Producto'>
                                    <div class='card-header'>
                                        Stock: {$product['productStock']}
                                    </div>
                                    <div class='card-body'>
                                        <h5 class='card-title'>{$product['productName']}</h5>
                                        <p class='card-text'>{$product['productDescription']}</p>
                                    </div>
                                    <form method='post'>
                                        <div class='card-footer d-flex justify-content-center input-group'>
                                            <button type='submit' class='btn btn-vino btn-sm' name='productBuy' value='{$product['productId']}'>
                                                <i class='bi bi-cash-stack me-2'></i>Comprar
                                            </button>
                                            <button type='submit' class='btn btn-primary btn-sm' name='productAdd' value='{$product['productId']}'>
                                                <i class='bi bi-cart me-2'></i>Añadir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>";
                        }
                        foreach ($unavailableProducts as $product) {
                            if (empty($product['productImgPath']) || is_null($product['productImgPath'])) {
                                $product['productImgPath'] = "img/product_placeholder.png";
                            }
                            echo "
                            <div class='col-lg-3 col-md-4 col-sm-6 mb-4'>
                                <div class='card h-100 product-card' data-session='producto'>
                                    <img src='{$product['productImgPath']}' class='card-img-top' alt='Producto'>
                                    <div class='card-header text-danger'>
                                        Sin stock
                                    </div>
                                    <div class='card-body'>
                                        <h5 class='card-title'>{$product['productName']}</h5>
                                        <p class='card-text'>{$product['productDescription']}</p>
                                    </div>
                                    <div class='card-footer d-flex justify-content-center input-group'>
                                        <button type='button' class='btn btn-vino btn-sm' disabled>
                                            <i class='bi bi-cash-stack me-2'></i>Comprar
                                        </button>
                                        <button type='button' class='btn btn-primary btn-sm' disabled>
                                            <i class='bi bi-cart me-2'></i>Añadir
                                        </button>
                                    </div>
                                </div>
                            </div>";
                        }
                    }
                ?>
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