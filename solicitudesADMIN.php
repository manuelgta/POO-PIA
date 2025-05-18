<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (isset($_POST['requestId'])) {
        $requestId = $_POST['requestId'] ?? NULL;
        $statusId = $_POST['statusId'] ?? NULL;
        $tecId = $_POST['tecId'] ?? NULL;

        $enlace->begin_transaction(); // Para tratar multiples ejecuciones a la base de datos

        try {

            if(is_null($requestId) ||is_null($statusId) ||is_null($tecId)) {
                throw new Exception("¡Algo salio mal!", -1); // Mandar a catch
            }

            $stmt = $enlace->prepare("SELECT *
                    FROM requests 
                    WHERE requestId = ?");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (count($request) != 1) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $request = $request[0];

            $gotUpdated = false;
            $oldValues = "";
            $newValues = "";
            if ($statusId != $request['statusId']) {
                $oldValues .= "Status: {$request['statusId']} ";
                $newValues .= "Status: $statusId ";
                $gotUpdated = true;
            }
            if ($tecId != $request['tecId']) {
                $oldValues .= "TecId: {$request['tecId']} ";
                $newValues .= "TecId: $tecId ";
                $gotUpdated = true;
            }

            if ($gotUpdated) {

                $stmt = $enlace->prepare("UPDATE requests SET statusId = ?, tecId = ?
                        WHERE requestId = ?");
                $stmt->bind_param("iii", $statusId, $tecId, $requestId);
                $stmt->execute();

                if (!updateLog($enlace, "requests", $requestId, $_SESSION['user']['id'], $oldValues, $newValues)) {
                    throw new Exception("¡Algo salio mal!", -3);
                }
            }

            $enlace->commit(); // Guardar todos los cambios hechos
            $_SESSION['success'] = "¡Mensaje de exito!"; // Mensaje de exito que aparece en navbar.php
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}"; // Mensaje de error junto con el codigo
            $enlace->rollback(); // Deshacer cambios hechos en la base de datos en caso de error
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        $url = basename($_SERVER['PHP_SELF']); // Redirigir a la misma pagina
        header("location: $url");
        exit();
    }

    $stmt = $enlace->prepare("SELECT r.requestId, r.statusId, r.tecId,
            DATE_FORMAT(r.requestDate, '%d/%m/%Y') AS requestDate,
            u.userName, t.userName as tecName, s.serviceName, sr.statusName FROM requests r
            JOIN users u ON u.userId = r.userId
            LEFT JOIN users t ON t.userId = r.tecId
            JOIN services s ON s.serviceId = r.serviceId
            JOIN statusrequests sr ON sr.statusId = r.statusId
            WHERE r.isDeleted = 0");
    $stmt->execute();

    $requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



    $stmt = $enlace->prepare("SELECT * FROM users
            WHERE roleId = 3 AND isDeleted = 0");
    $stmt->execute();

    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $enlace->prepare("SELECT * FROM statusrequests
            WHERE isDeleted = 0");
    $stmt->execute();

    $statuses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Admin Solicitudes</title>
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
                    <div class="btn-group ms-auto">
                        <button class="btn btn-sm btn-vino">Todas</button>
                        <button class="btn btn-sm btn-outline-vino">Pendientes</button>
                        <button class="btn btn-sm btn-outline-vino">En Proceso</button>
                        <button class="btn btn-sm btn-outline-vino">Completadas</button>
                    </div>
                    <button class="btn btn-sm btn-vino ms-2">
                        <i class="bi bi-person-circle me-2"></i>Administrador
                    </button>
                </div>
            </nav>

            <div class="admin-content p-4">
                <h2 class="mb-4">Gestión de Solicitudes</h2>
                
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Producto</th>
                                        <th>Fecha</th>
                                        <th>Técnico</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($requests as $request) {
                                            $class = '';
                                            if (isset($_SESSION['soliAdmin']) && $request['requestId'] == $_SESSION['soliAdmin']) 
                                                $class = "class='bg-primary text-light'";
                                            unset($_SESSION['soliAdmin']);

                                            $stmt = $enlace->prepare("SELECT p.*, rp.productAmount
                                                    FROM products p
                                                    JOIN request_products rp ON rp.productId = p.productId
                                                    WHERE rp.requestId = ?
                                                    ORDER BY p.productId ASC");
                                            $stmt->bind_param("i", $request['requestId']);
                                            $stmt->execute();
                                            $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                                            $totalProducts = count($products);
                                            $wentWrong = 0;

                                            if ($totalProducts == 0) {
                                                $wentWrong = 1;
                                            } 

                                            echo "
                                            <form method='post'>
                                                <tr>
                                                    <td $class>#{$request['requestId']}</td>
                                                    <td>{$request['userName']}</td>
                                                    <td>{$request['statusName']}</td>
                                                    <td>";
                                            if (!$wentWrong) {
                                                if ($totalProducts == 1) {
                                                    $products = $products[0];
                                                    if ($products['productAmount'] == 1) {
                                                        echo "
                                                        {$products['productName']}";
                                                    } else {
                                                        echo "
                                                        {$products['productName']} ({$products['productAmount']})";
                                                    }
                                                } else {
                                                    echo "
                                                    <table class='table table-hover table-striped table-bordered'>
                                                        <thead>
                                                            <tr>
                                                                <th>Producto</th>
                                                                <th>Cantidad</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>";
                                                        foreach ($products as $product) {
                                                            echo "
                                                            <tr>
                                                                <td>{$product['productName']}</td>
                                                                <td>{$product['productAmount']}</td>
                                                            </tr>";
                                                        }
                                                    echo "
                                                        </tbody>
                                                    </table>";
                                                }
                                            }
                                            echo "
                                                    </td>
                                                    <td>{$request['requestDate']}</td>
                                                    <td>
                                                        <select class='form-select form-select-sm' required name='tecId'>
                                                            <option value=''>-- Elije --";
                                            if (empty($users)) {
                                                echo "
                                                <option value='' disabled>Sin técnicos</options>";
                                            } else foreach ($users as $user) {
                                                $selected = "";
                                                if ($request['tecId'] == $user['userId']) $selected = "selected";
                                                echo "
                                                <option value='{$user['userId']}' $selected>{$user['userName']}</option>";
                                            }
                                            echo "
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class='form-select form-select-sm' required name='statusId'>";
                                            foreach ($statuses as $status) {
                                                $selected = "";
                                                if ($request['statusId'] == $status['statusId']) $selected = "selected";
                                                echo "
                                                <option value='{$status['statusId']}' $selected>{$status['statusName']}</option>";
                                            }
                                            echo "
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button type='submit' name='requestId' value='{$request['requestId']}' class='btn btn-sm btn-vino'>Guardar</button>
                                                    </td>
                                                </tr>
                                            </form>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
</body>
</html>