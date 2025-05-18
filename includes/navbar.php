<nav class="navbar navbar-expand-lg navbar-dark bg-vino">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="img/logo sin fondo.png" alt="CIYSE Logo" height="40">
        </a>
        <div class="d-flex justify-content-end">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php
                if (isset($_SESSION['cart']['items']) && count($_SESSION['cart']['items']) > 0 && $currentURL != 'agendar.php') {
            ?>
            <button type="button" class="btn text-white ms-5 position-relative"
                    data-bs-toggle="offcanvas" data-bs-target="#cart" aria-controls="cart">
                Mi carrito <i class="bi bi-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                    <?php
                        $amount = count($_SESSION['cart']['items']);
                        echo $amount;
                    ?>
                </span>
            </button>
            <?php
                }
            ?>
        </div>
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
<?php
    if (isset($_SESSION['cart']['items']) && count($_SESSION['cart']['items']) > 0 && $currentURL != 'agendar.php') {
?>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cart" aria-labelledby="cartLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="cartLabel">Mis productos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            
            <?php
                $productIDs = array_keys($_SESSION['cart']['items']);

                // Crear los placeholders (?, ?, ?, ...)
                $placeholders = implode(',', array_fill(0, count($productIDs), '?'));
                // Crear el string de tipos (todos 'i' porque son enteros)
                $types = str_repeat('i', count($productIDs));

                $stmt = $enlace->prepare("SELECT * FROM products WHERE productId IN ($placeholders)");
                $stmt->bind_param($types, ...$productIDs);
                $stmt->execute();
                $productsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $productKeys = array_column($_SESSION['cart']['items'], "productId");
                
                foreach ($productsData as $item) {
                    if (empty($item['productImgPath']) || !file_exists($item['productImgPath'])) {
                        $item['productImgPath'] = "img/product_placeholder.png";
                    }
                    $amount = $_SESSION['cart']['items'][$item['productId']];
                    $item["productPrice"] = 0;
                    $price = number_format($item['productPrice'], 2);

                    echo "
                    <div class='row border-bottom py-3 align-items-center'>
                        <div class='col-3 col-md-2'>
                            <img src='{$item['productImgPath']}' alt='producto' class='img-fluid img-thumbnail zoomable-img'>
                        </div>
                        <div class='col-9 col-md-10'>
                            <div class='d-flex justify-content-between'>
                                <div class='me-3'>
                                    <h5 class='mb-1'>{$item['productName']}</h5>
                                    <p class='mb-1 text-muted text-truncate product-description'>{$item['productDescription']}</p>
                                </div>
                                <button type='button' data-cartRemove='{$item['productId']}' class='btn btn-sm btn-outline-danger'>Eliminar</button>
                            </div>
                            <div class='d-flex justify-content-end mt-2'>
                                <select data-cartChange='{$item['productId']}' class='form-select form-select-sm w-auto'>
                    ";

                    // Selector de cantidad
                    for ($i = 1; $i <= $item['productStock']; $i++) {
                        $selected = ($i == $amount) ? "selected" : "";
                        echo "<option value='$i' $selected>$i</option>";
                    }

                    echo "
                                </select>
                            </div>
                        </div>
                    </div>
                    ";
                }
                ?>
            <a href='agendar.php' class='btn btn-vino mt-5'><i class='bi bi-clipboard'></i>Agendar</a>
        </div>
    </div>
    <?php
    }
    ?>
    <!-- Modal de imagen -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <img id="modalImage" src="" class="img-fluid w-100" alt="Imagen ampliada">
                </div>
            </div>
        </div>
    </div>