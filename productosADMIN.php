<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (isset($_POST['productNew'])) {
        $productName = $_POST['productName'] ?? NULL;
        $productStock = $_POST['productStock'] ?? NULL;
        $productDescription = $_POST['productDescription'] ?? NULL;
        $img = $_FILES['productImg'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($productName) || is_null($productStock) || is_null($productDescription)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("INSERT INTO products (productName, productStock, productDescription)
                    VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $productName, $productStock, $productDescription);
            $stmt->execute();
            $regId = $enlace->insert_id;

            // Manejo de la imagen
            if ($img && $img['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($img['name'], PATHINFO_EXTENSION); // Obtener la extensión del archivo
                $productImgPath = "uploads/products/product_$regId.$extension"; // Ruta del archivo

                // Mover la imagen a la carpeta destino
                if (move_uploaded_file($img['tmp_name'], $productImgPath)) {
                    // Actualizar la base de datos con la ruta de la imagen
                    $stmt = $enlace->prepare("UPDATE products SET productImgPath = ? WHERE productId = ?");
                    $stmt->bind_param("si", $productImgPath, $regId);
                    $stmt->execute();
                } else {
                    throw new Exception("Error al subir la imagen.", -3);
                }
            }

            if (!insertLog($enlace, 'products', $regId, $_SESSION['user']['id'], $productName)) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Producto agregada con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "¡Ya existe una producto con ese nombre!";
            }
            $enlace->rollback();
        }
    }

    if (isset($_POST['productEdit'])) {
        $productName = $_POST['productName'] ?? NULL;
        $productStock = $_POST['productStock'] ?? NULL;
        $productDescription = $_POST['productDescription'] ?? NULL;
        $img = $_FILES['productImg'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($productName) || is_null($productStock) || is_null($productDescription)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("SELECT * FROM products WHERE productId = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (count($product) != 1) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $product = $product[0];

            $gotUpdated = false;
            $oldValues = "";
            $newValues = "";
            if ($productName != $product['productName']) {
                $oldValues .= "Name: {$product['productName']} ";
                $newValues .= "Name: $productName ";
                $gotUpdated = true;
            }
            if ($productStock != $product['productStock']) {
                $oldValues .= "Stock: {$product['productStock']} ";
                $newValues .= "Stock: $productStock ";
                $gotUpdated = true;
            }
            if ($productDescription != $product['productDescription']) {
                $oldValues .= "Description: {$product['productDescription']} ";
                $newValues .= "Description: $productDescription ";
                $gotUpdated = true;
            }
            if ($img && $img['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($img['name'], PATHINFO_EXTENSION); // Obtener la extensión del archivo
                $productImgPath = "uploads/products/product_$regId.$extension"; // Ruta del archivo

                // Mover la imagen a la carpeta destino
                if (move_uploaded_file($img['tmp_name'], $productImgPath)) {
                    // Actualizar la base de datos con la ruta de la imagen
                    $stmt = $enlace->prepare("UPDATE products SET productImgPath = ? WHERE productId = ?");
                    $stmt->bind_param("si", $productImgPath, $productId);
                    $stmt->execute();
                    $gotUpdated = true;
                } else {
                    throw new Exception("Error al subir la imagen.", -3);
                }
            }

            if ($gotUpdated) {
                $stmt = $enlace->prepare("UPDATE products SET productName = ?, productStock = ?, productDescription = ? WHERE productId = ?");
                $stmt->bind_param("sisi", $productName, $productStock, $productDescription, $productId);
                $stmt->execute();

                if (!updateLog($enlace, "products", $productId, $_SESSION['user']['id'], $oldValues, $newValues)) {
                    throw new Exception("¡Algo salio mal!", -4);
                }
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Producto editado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "¡Ya existe un producto con ese nombre!";
            }
            $enlace->rollback();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        $url = basename($_SERVER['PHP_SELF']); // Redirigir a la misma pagina
        header("location: $url");
        exit();
    }

    $stmt = $enlace->prepare("SELECT * FROM products
            WHERE isDeleted = 0");
    $stmt->execute();

    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Inventario</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/admin_navbar.php';
        ?>
    </header>
    <div class="admin-wrapper">

        <div class="admin-main">
            <nav class="admin-topbar navbar navbar-expand navbar-light bg-light">
                <div class="container-fluid">
                    <button class="btn btn-sm btn-vino ms-auto">
                        <i class="bi bi-person-circle me-2"></i>Administrador
                    </button>
                </div>
            </nav>

            <div class="admin-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Productos</h2>
                    <button class="btn btn-vino" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus me-2"></i>Agregar Producto
                    </button>
                </div>
                
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagen</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($products as $product) {
                                            $product['productImgPath'] = $product['productImgPath'] ?? "img/product_placeholder.png";
                                            $badgeColor = $product['productStock'] > 0 ? "success" : "danger";
                                            $badgeText = $product['productStock'] > 0 ? "Disponible" : "Agotado";
                                            echo "
                                            <tr>
                                                <td>{$product['productId']}</td>
                                                <td><img src='{$product['productImgPath']}' alt='producto' height='50'></td>
                                                <td>{$product['productName']}</td>
                                                <td>{$product['productDescription']}</td>
                                                <td>{$product['productStock']}</td>
                                                <td><span class='badge bg-$badgeColor'>$badgeText</span></td>
                                                <td>
                                                    <button class='btn btn-sm btn-primary me-1'>Editar</button>
                                                    <button class='btn btn-sm btn-danger me-1'>Eliminar</button>
                                                </td>
                                            </tr>";
                                        }
                                    ?>
                                    <tr>
                                        <td>1</td>
                                        <td><img src="img/product_placeholder.png" alt="Producto" height="50"></td>
                                        <td>Cámara HD 1080p</td>
                                        <td>Cámara de seguridad con resolución Full HD...</td>
                                        <td>15</td>
                                        <td><span class="badge bg-success">Disponible</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary me-1">Editar</button>
                                            <button class="btn btn-sm btn-danger">Eliminar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><img src="../../img/producto2.jpg" alt="Producto" height="50"></td>
                                        <td>Grabador Digital 4 Canales</td>
                                        <td>Sistema de grabación digital para 4 cámaras...</td>
                                        <td>8</td>
                                        <td><span class="badge bg-success">Disponible</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary me-1">Editar</button>
                                            <button class="btn btn-sm btn-danger">Eliminar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td><img src="../../img/producto3.jpg" alt="Producto" height="50"></td>
                                        <td>Kit de Seguridad Básico</td>
                                        <td>Kit completo con 4 cámaras, grabador...</td>
                                        <td>0</td>
                                        <td><span class="badge bg-danger">Agotado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary me-1">Editar</button>
                                            <button class="btn btn-sm btn-danger">Eliminar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal para agregar productos -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-vino text-white">
                    <h5 class="modal-title">Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="productName" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Inicial</label>
                                <input type="number" name="productStock" class="form-control" required min="0" step="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="productDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Imagen del Producto (Opcional)</label>
                                    <input type="file" class="form-control" name="productImg" id="productImg1" accept="image/*">
                                </div>
                                <div class="col-6">
                                    <img id="preview1" src="" alt="Vista previa" class="img-fluid" style="height: 100; width: auto; display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="productNew" class="btn btn-vino">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal para editar productos -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-vino text-white">
                    <h5 class="modal-title">Editar un Producto</h5>
                    <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="productName" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Inicial</label>
                                <input type="number" name="productStock" class="form-control" required min="0" step="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="productDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Imagen del Producto (Opcional)</label>
                                    <input type="file" class="form-control" name="productImg" id="productImg1" accept="image/*">
                                </div>
                                <div class="col-6">
                                    <img id="preview1" src="" alt="Vista previa" class="img-fluid" style="height: 100; width: auto; display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="productNew" class="btn btn-vino">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('#productImg1').on('change', function (event) {
                let input = event.target;
                let preview = $('#preview1');

                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        preview.attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });

            $('[data-bs-dismiss]').on('click', function () {
                let modal = $(this).closest('.modal');
                let form = modal.find('form');
                form.find('input, textarea, select, file').val('');
                form.find('img').attr("src", "").hide();
            });

            function saveSession (criteria, data, reload = false, unset = false) {
                $.ajax({
                    url: "php/globalSetSession.php",
                    type: "POST",
                    data: {
                        criteria: criteria,
                        data: data,
                        unset: unset,
                        page: "vehicles"
                    },
                    success: function(response) {
                        if (reload) window.location.reload();
                    },
                    error: function(response) {
                        alert("Error al eliminar el registro");
                        console.log(response);
                    }
                });
            }

            function globalGet (id = $("#vehicleId").val()) {
                let vehicleId = id; // Obtiene el ID del usuario seleccionado
                
                if (vehicleId) {
                    $.ajax({
                        url: "globalGet.php", // Reemplaza con la ruta correcta de tu archivo PHP
                        type: "POST",
                        data: { id: vehicleId, page: "vehicles" },
                        dataType: "json",
                        success: function (response) {
                            let vehicle = response[0];
                            $("#vehicleModel").val(vehicle.vehicleModel);
                            $("#vehicleYear").val(vehicle.vehicleYear);
                            $("#vehicleDescription").val(vehicle.vehicleDescription);
                            $("#preview2").attr("src", vehicle.vehicleImgPath).show();
                        },
                        error: function () {
                            console.error("Error AJAX:", status, error); // <-- Ver error en consola
                            console.log("Respuesta completa:", xhr.responseText);
                            alert("Error al obtener los vehiculos.");
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>