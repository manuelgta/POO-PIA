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
                    } else foreach ($products as $product) {
                        $product['productImgPath'] = $product['productImgPath'] ?? "img/product_placeholder.png";
                        echo "
                        <div class='col-md-4 mb-4'>
                            <div class='card h-100' data-session='producto'>
                                <img src='{$product['productImgPath']}' class='card-img-top' alt='Producto'>
                                <div class='card-body'>
                                    <h5 class='card-title'>{$product['productName']}</h5>
                                    <p class='card-text'>{$product['productDescription']}</p>
                                </div>
                            </div>
                        </div>";
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