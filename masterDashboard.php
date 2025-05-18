<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (!isset($_SESSION['dashboard']['trash'])) $_SESSION['dashboard']['trash'] = 0;
    $trash = $_SESSION['dashboard']['trash'];

    if (!isset($_SESSION['dashboard']['table'])) $_SESSION['dashboard']['table'] = 'logs';
    $table = $_SESSION['dashboard']['table'];
    $tableSLess = substr($table, 0, strlen($table) - 1);
    $ucfirstTable = ucfirst($table);

    if (isset($_SESSION['dashboard']['delete']) || isset($_SESSION['dashboard']['restore'])) {
        $enlace->begin_transaction();

        try {
            if (isset($_SESSION['dashboard']['delete'])) {
                $id = $_SESSION['dashboard']['delete'] ?? NULL;
                unset($_SESSION['dashboard']['delete']);
                $function = "deleteLog";
                $isDeleted = 1;
            } else if (isset($_SESSION['dashboard']['restore'])) {
                $id = $_SESSION['dashboard']['restore'] ?? NULL;
                unset($_SESSION['dashboard']['restore']);
                $function = "restoreLog";
                $isDeleted = 0;
            } else {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $column = "{$tableSLess}Name";
            if ($table == 'urls' || $table == 'requests') {
                $column = "{$tableSLess}Title";
            }
            if ($table == 'icons') {
                $column = "{$tableSLess}Bi";
            }

            $stmt = $enlace->prepare("SELECT $column as currentValue FROM $table
                    WHERE {$tableSLess}Id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $row_count = count($result);

            if ($row_count != 1) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $values = $result[0]["currentValue"];

            if (!$function($enlace, $table, $id, $_SESSION['user']['id'], $values)) {
                throw new Exception("¡Algo salio mal!", -3);
            }

            $stmt = $enlace->prepare("UPDATE $table SET isDeleted = $isDeleted
                    WHERE {$tableSLess}Id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $enlace->commit();
            $_SESSION['success'] = "¡Registro alterado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }

        $url = basename($_SERVER['PHP_SELF']);
        header("location: $url");
        exit();
    }

    if (!isset($_SESSION['dashboard']['page'][$table])) $_SESSION['dashboard']['page'][$table] = 0;
    if (isset($_POST['page'][$table])) $_SESSION['dashboard']['page'][$table] = intval($_POST['page'][$table]) ?? 0;
    $currentPage = $_SESSION['dashboard']['page'][$table];

    $itemsPerPage = 25;

    switch ($table) {
        case 'users':
            $query = "SELECT u.userId AS id, r.roleName, u.userName, u.userMail, u.userPhone, 
                            DATE_FORMAT(u.createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(u.updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM users u
                    LEFT JOIN roles r ON u.roleId = r.roleId
                    WHERE u.isDeleted = $trash
                    ORDER BY u.userId DESC";
            break;

        case 'roles':
            $query = "SELECT roleId AS id, roleName,
                            DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM roles
                    WHERE isDeleted = $trash
                    ORDER BY roleId DESC";
            break;
        case 'services':
            $query = "SELECT s.serviceId AS id, s.serviceName, s.serviceDescription,
                            DATE_FORMAT(s.createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(s.updatedAt, '%d/%m/%y %H:%i') AS updatedAt,
                    i.iconBi
                    FROM services s
                    JOIN icons i ON i.iconId = s.iconId
                    WHERE s.isDeleted = $trash
                    ORDER BY s.serviceId DESC";
            break;

        case 'products':
            $query = "SELECT productId AS id, productName, productDescription, productStock, productImgPath,
                            DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM products
                    WHERE isDeleted = $trash
                    ORDER BY productId DESC";
            break;

        case 'requests':
            $query = "SELECT r.requestId AS id, r.requestComments, r.requestDate, r.requestAddress,
                            DATE_FORMAT(r.createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(r.updatedAt, '%d/%m/%y %H:%i') AS updatedAt,
                    sr.statusName, sr.statusClassName,
                    s.serviceName,
                    u.userName, ut.userName as tecId
                    FROM requests r
                    LEFT JOIN statusrequests sr ON sr.statusId = r.statusId
                    LEFT JOIN users u ON u.userId = r.userId
                    LEFT JOIN users ut ON ut.userId = r.tecId
                    LEFT JOIN services s ON s.serviceId = r.serviceId
                    WHERE r.isDeleted = $trash
                    ORDER BY r.requestId DESC";
            break;

        case 'logs':
            $query = "SELECT l.logId AS id, l.tableName, l.recordId, l.actionType, l.userId, u.userName, l.oldValues, l.newValues, l.timestamp
                    FROM logs l
                    LEFT JOIN users u ON u.userId = l.userId
                    ORDER BY l.logId DESC";
            break;

        case 'urls':
            $query = "SELECT u.urlId AS id, u.urlTitle, u.urlAddress, u.showOrder, u.allowAll, u.isDisabled,
                    DATE_FORMAT(u.createdAt, '%d/%m/%y %H:%i') AS createdAt,
                    DATE_FORMAT(u.updatedAt, '%d/%m/%y %H:%i') AS updatedAt,
                    i.iconBi
                    FROM urls u
                    JOIN icons i ON i.iconId = u.iconId
                    WHERE u.isDeleted = $trash
                    ORDER BY u.urlId DESC";
            break;

        case 'icons':
            $query = "SELECT iconId AS id, iconBi,
                    DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                    DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM icons
                    WHERE isDeleted = $trash
                    ORDER BY iconId DESC";
            break;

        default:
            unset($_SESSION['dashboard']['table']);
            die("Tabla no válida $table.");
    }

    $result = $enlace->query($query);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    $totalData = count($data);
    $totalPages = ceil($totalData / $itemsPerPage);
    $start = $currentPage * $itemsPerPage;
    $end = min($start + $itemsPerPage, $totalData);
    $pageData = array_slice($data, $start, $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - CRUDs</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php include 'includes/admin_navbar.php'; ?>
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
                <h2 class="mb-4">CRUDs - <?= $ucfirstTable ?></h2>
                <div class="row">
                    <div class="col-11 p-0">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'logs' ? 'active' : '' ?>" data-session="table" data-sessionvalue="logs" href="#">Logs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'requests' ? 'active' : '' ?>" data-session="table" data-sessionvalue="requests" href="#">Peticiones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'urls' ? 'active' : '' ?>" data-session="table" data-sessionvalue="urls" href="#">URLs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'icons' ? 'active' : '' ?>" data-session="table" data-sessionvalue="icons" href="#">Iconos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'users' ? 'active' : '' ?>" data-session="table" data-sessionvalue="users" href="#">Usuarios</a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'reports' ? 'active' : '' ?>" data-session="table" data-sessionvalue="reports" href="#">Reportes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'reportspdfs' ? 'active' : '' ?>" data-session="table" data-sessionvalue="reportspdfs" href="#">PDF</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'clients' ? 'active' : '' ?>" data-session="table" data-sessionvalue="clients" href="#">Clientes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'companyroles' ? 'active' : '' ?>" data-session="table" data-sessionvalue="companyroles" href="#">Roles de compañias</a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'roles' ? 'active' : '' ?>" data-session="table" data-sessionvalue="roles" href="#">Roles</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'products' ? 'active' : '' ?>" data-session="table" data-sessionvalue="products" href="#">Productos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-bottom <?= $table == 'services' ? 'active' : '' ?>" data-session="table" data-sessionvalue="services" href="#">Servicios</a>
                            </li>
                            <li class="nav-item">
                                <button type="button" id="btnTrash" class="btn btn-<?= $trash == 0 ? 'outline-' : '' ?>primary" data-session="trash" data-sessionvalue="<?= abs($trash - 1) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button type='button' class='btn btn-outline-primary toggleExpand'>
                                    <i class='bi bi-eye'></i>
                                </button>
                            </li>
                        </ul>
                        <div class="table-fluid overflow-y-scroll overflow-x-scroll" style="max-height: 400px;">
                            <table class="table table-hover table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <?php
                                            switch ($table) {
                                                case 'users':
                                                    echo '
                                                    <th>Accion</th>
                                                    <th>#</th>
                                                    <th>Rol</th>
                                                    <th>Nombre</th>
                                                    <th>Correo</th>
                                                    <th>Numero</th>
                                                    <th>Creado</th>
                                                    <th>Actualizado</th>';
                                                    break;
                                                case 'roles':
                                                    echo '
                                                    <th>Accion</th>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Creado</th>
                                                    <th>Actualizado</th>';
                                                    break;
                                                case 'services':
                                                    echo '
                                                    <th>Accion</th>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Descripcion</th>
                                                    <th>Icono</th>
                                                    <th>Creado</th>
                                                    <th>Actualizado</th>';
                                                    break;
                                                case 'urls':
                                                    echo '
                                                    <th>Accion</th>
                                                        <th>#</th>
                                                        <th>Icono</th>
                                                        <th>Titulo</th>
                                                        <th>Direccion</th>
                                                        <th>Orden</th>
                                                        <th>Permitido a todos</th>
                                                        <th>Desabilitado</th>
                                                        <th>Creado</th>
                                                        <th>Actualizado</th>';
                                                    break;
                                                case 'icons':
                                                    echo '
                                                    <th>Accion</th>
                                                    <th>#</th>
                                                    <th>Icono</th>
                                                    <th>Creado</th>
                                                    <th>Actualizado</th>';
                                                    break;
                                                case 'logs':
                                                    echo '
                                                    <th>#</th>
                                                    <th>Tabla</th>
                                                    <th>Accion</th>
                                                    <th>Responsable</th>
                                                    <th>Anterior</th>
                                                    <th>Nuevo</th>
                                                    <th>Creado</th>';
                                                    break;
                                                case 'requests':
                                                    echo '
                                                    <th>Accion</th>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Fecha</th>
                                                    <th>Productos</th>
                                                    <th>Tecnico</th>
                                                    <th>Estado</th>
                                                    <th>Servicio</th>
                                                    <th>Comentarios</th>
                                                    <th>Creado</th>
                                                    <th>Actualizado</th>';
                                                    break;
                                                case 'products':
                                                    echo '
                                                    <th>Accion</th>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Descripcion</th>
                                                    <th>Stock</th>
                                                    <th>Imagen</th>
                                                    <th>Creado</th>
                                                    <th>Actualizado</th>';
                                                    break;
                                            }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($pageData as $row) {
                                            $id = $row['id'];
                                            $buttons = "
                                            <button class='btn btn-warning set' data-id='$id'><i class='bi bi-pencil-square'></i></button>
                                            <button class='btn btn-danger delete' data-id='$id'><i class='bi bi-trash'></i></button>";
                                            if ($trash == 1) {
                                                $buttons = "
                                                <button class='btn btn-success restore' data-id='$id'><i class='bi bi-recycle'></i></button>";
                                            }

                                            switch ($table) {
                                                case 'logs':
                                                    ?>
                                                    <tr>
                                                        <td><?= $id ?></td>
                                                        <td>
                                                            <?php if ($row['actionType'] != 'DELETE') { ?>
                                                                <a
                                                                    data-jsonconvert='1'
                                                                    data-session='<?= json_encode(["table", "regId"]) ?> '
                                                                    data-sessionvalue='<?= json_encode([$row["tableName"], $row["recordId"]]) ?>' href='#'>
                                                                    <?= $row['tableName'] ?>
                                                                </a>
                                                            <?php } else { ?>
                                                                <a
                                                                    data-jsonconvert='1'
                                                                    data-trash='1'
                                                                    data-session='<?= json_encode(["table", "regId"]) ?> '
                                                                    data-sessionvalue='<?= json_encode([$row["tableName"], $row["recordId"]]) ?>' href='#'>
                                                                    <?= $row['tableName'] ?>
                                                                </a>
                                                            <?php } ?>
                                                        </td>
                                                        <?php
                                                            echo "<td class='bg-";
                                                            switch ($row['actionType']) {
                                                                case 'INSERT':
                                                                    echo "success text-light";
                                                                    break;
                                                                case 'UPDATE':
                                                                    echo "primary text-light";
                                                                    break;
                                                                case 'DELETE':
                                                                    echo "danger text-light";
                                                                    break;
                                                                case 'RESTORE':
                                                                    echo "warning";
                                                                    break;
                                                            }
                                                            echo "'>{$row['actionType']}</td>"
                                                        ?>
                                                        <td>
                                                            <a
                                                                data-jsonconvert='1'
                                                                data-session='<?= json_encode(["table", "regId"]) ?> '
                                                                data-sessionvalue='<?= json_encode(['users', $row["userId"]]) ?>' href='#'>
                                                                <?= $row['userName'] ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <p class='text-truncate'><?= $row['oldValues'] ?></p>
                                                            <?php if (strlen($row['oldValues']) > 23) { ?>
                                                            <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                                <i class='bi bi-eye'></i>
                                                            </button>
                                                            <?php } ?>
                                                        </td>
                                                        <td>
                                                            <p class='text-truncate'><?= $row['newValues'] ?></p>
                                                            <?php if (strlen($row['newValues']) > 23) { ?>
                                                            <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                                <i class='bi bi-eye'></i>
                                                            </button>
                                                            <?php } ?>
                                                            
                                                        </td>
                                                        <td><?= $row['timestamp'] ?></td>
                                                    </tr>
                                                    <?php
                                                    break;
                                                case 'urls':
                                                    $row['allowAll'] = $row['allowAll'] == 1 ? 'True' : 'False';
                                                    $row['isDisabled'] = $row['isDisabled'] == 1 ? 'True' : 'False';
                                                        $class = '';
                                                        if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) 
                                                            $class = "class='bg-primary text-light'";
                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>
                                                        <td $class>{$row['id']}</td>
                                                        <td><i class='bi bi-{$row['iconBi']}'></i></td>
                                                        <td>{$row['urlTitle']}</td>
                                                        <td>{$row['urlAddress']}</td>
                                                        <td>{$row['showOrder']}</td>
                                                        <td>{$row['allowAll']}</td>
                                                        <td>{$row['isDisabled']}</td>
                                                        <td>{$row['createdAt']}</td>
                                                        <td>{$row['updatedAt']}</td>
                                                    </tr>";
                                                    break;
                                                case 'icons':
                                                    $class = '';
                                                    if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) 
                                                        $class = "class='bg-primary text-light'";
                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>
                                                        <td $class>{$row['id']}</td>
                                                        <td><i class='bi bi-{$row['iconBi']}'></i></td>
                                                        <td>{$row['createdAt']}</td>
                                                        <td>{$row['updatedAt']}</td>
                                                    </tr>";
                                                    break;
                                                case 'users':
                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>";
                                                    foreach ($row as $key => $value) {
                                                        $class = '';
                                                        if ($key == 'id' && isset($_SESSION['dashboard']['regId']) && $value == $_SESSION['dashboard']['regId']) 
                                                            $class = "class='bg-primary text-light'";
                                                        echo "
                                                        <td $class>$value</td>";
                                                    }
                                                    echo "
                                                    </tr>";
                                                    break;
                                                case 'services':
                                                    $class = '';
                                                    if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) 
                                                        $class = "class='bg-primary text-light'";
                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>
                                                        <td $class>{$row['id']}</td>
                                                        <td>{$row['serviceName']}</td>
                                                        <td>{$row['serviceDescription']}</td>
                                                        <td><i class='bi bi-{$row['iconBi']}'></i></td>
                                                        <td>{$row['createdAt']}</td>
                                                        <td>{$row['updatedAt']}</td>
                                                    </tr>";
                                                    break;
                                                case 'requests':
                                                    $class = '';
                                                    if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) 
                                                        $class = "class='bg-primary text-light'";

                                                    $stmt = $enlace->prepare("SELECT p.*, rp.productAmount
                                                            FROM products p
                                                            JOIN request_products rp ON rp.productId = p.productId
                                                            WHERE rp.requestId = ?
                                                            ORDER BY p.productId ASC");
                                                    $stmt->bind_param("i", $row['id']);
                                                    $stmt->execute();
                                                    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>
                                                        <td $class>{$row['id']}</td>
                                                        <td>{$row['userName']}</td>
                                                        <td>{$row['requestDate']}</td>
                                                        <td>
                                                            <table class='table table-hover table-striped table-bordered'>
                                                                <thead>
                                                                    <tr>
                                                                        <th>Producto</th>
                                                                        <th>Cantidad</th>
                                                                        <th>Imagen</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>";
                                                                foreach ($products as $product) {
                                                                    if (empty($product['productImgPath']) || is_null($product['productImgPath'])) {
                                                                        $product['productImgPath'] = "img/product_placeholder.png";
                                                                    }
                                                                    echo "
                                                                    <tr>
                                                                        <td>{$product['productName']}</td>
                                                                        <td>{$product['productAmount']}</td>
                                                                        <td><img src='{$product['productImgPath']}' alt='producto' height='50'></td>
                                                                    </tr>";
                                                                }
                                                    echo "
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                        <td>{$row['tecId']}</td>
                                                        <td><span class='badge bg-{$row['statusClassName']}'>{$row['statusName']}</span></td>
                                                        <td>{$row['serviceName']}</td>
                                                        <td>{$row['requestComments']}</td>
                                                        <td>{$row['createdAt']}</td>
                                                        <td>{$row['updatedAt']}</td>
                                                        ";
                                                    break;
                                                case 'products':
                                                    $class = '';
                                                    if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) 
                                                        $class = "class='bg-primary text-light'";

                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>
                                                        <td $class>{$row['id']}</td>
                                                        <td>{$row['productName']}</td>
                                                        <td>{$row['productDescription']}</td>
                                                        <td>{$row['productStock']}</td>
                                                        <td><img src='{$row['productImgPath']}' alt='producto' height='50'></td>
                                                        <td>{$row['createdAt']}</td>
                                                        <td>{$row['updatedAt']}</td>
                                                    </tr>";
                                                    break;
                                                default:
                                                    echo "
                                                    <tr>
                                                        <td>$buttons</td>";
                                                    foreach ($row as $key => $value) {
                                                        $class = '';
                                                        if ($key == 'id' && isset($_SESSION['dashboard']['regId']) && $value == $_SESSION['dashboard']['regId']) 
                                                            $class = "class='bg-primary text-light'";
                                                        echo "
                                                        <td $class>$value</td>";
                                                    }
                                                    echo "
                                                    </tr>";
                                                    break;
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-1 p-0 pt-5">
                        <div class="btn-group-vertical mt-5 d-flex justify-position-center" role="group">
                            <?php
                                if ($totalData > 0) {
                            ?>
                                <button type='button' class='btn border' data-session='<?= $table ?>' data-sessionvalue='0' data-isPage='1'><i class='bi bi-arrow-bar-up'></i></button>
                                <?php
                                    if ($currentPage > 4) {
                                        $pageToGo = $currentPage - 5;
                                        echo "<button type='button' class='btn border' data-session='$table' data-sessionvalue='$pageToGo' data-isPage='1'><i class='bi bi-5-circle'></i></button>";
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
                                                <button type='button' class='btn border $active_text' data-session='$table' data-sessionvalue='$j' data-isPage='1'>$button_text</button>
                                            ";
                                        }
                                    }
                                ?>
                                <?php
                                    if ($currentPage < ceil($totalData / $itemsPerPage) - 5) {
                                        $pageToGo = $currentPage + 5;
                                        echo "<button type='button' class='btn border' data-session='$table' data-sessionvalue='$pageToGo' data-isPage='1'><i class='bi bi-5-circle'></i></button>";
                                    }
                                ?>
                                <button type='button' class='btn border' data-session='<?= $table ?>' data-sessionvalue='<?= ceil($totalData / $itemsPerPage) - 1 ?>' data-isPage='1'><i class='bi bi-arrow-bar-down'></i></button>
                            <?php
                                } else {
                            ?>
                                <button type='button' class='btn border' disabled><i class='bi bi-ban'></i></button>
                            <?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="expandModal" tabindex="-1" aria-labelledby="expandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            
            $('.toggleExpand').on('click', function () {
                $('.text-truncate, .text-truncate-false').toggleClass('text-truncate-false')
                                                         .toggleClass('text-truncate');
                $('.expandBtn').prop('disabled', function(i, val) {
                                 return !val;
                               })
                $('.toggleExpand').toggleClass('btn-outline-primary')
                       .toggleClass('btn-primary');
            });

            $('.expandBtn').on('click', function () {
                let content = $(this).siblings("p").html();
                $("#expandModal .modal-body").html(content);
            });

            $('[data-session]').on('click', function () {
                let self = $(this);
                
                let sessionData = self.attr('data-session');
                let sessionValue = self.attr('data-sessionvalue');

                if (self.is('[data-jsonconvert]')) {
                    try {
                        sessionData = JSON.parse(sessionData);
                        sessionValue = JSON.parse(sessionValue);
                        if (self.is('[data-trash]')) {
                            saveSession(sessionData, sessionValue, false, false);
                            $('#btnTrash').attr('data-sessionvalue', 1).click();
                        } else {
                            saveSession(sessionData, sessionValue, false, false);
                            $('#btnTrash').attr('data-sessionvalue', 0).click();
                        }
                    } catch (error) {
                        console.error("Error al convertir JSON:", error);
                        return;
                    }
                } else if (self.is('[data-isPage]')) {
                    saveSession(sessionData, sessionValue, 'page');
                } else saveSession(sessionData, sessionValue);
            });

            $('.set').on('click', function () {
                let self = $(this);
                saveSession(
                    ['set'],
                    [self.data('id')]
                );
            });

            $('.delete').on('click', function () {
                let self = $(this);
                if (confirm("Estas seguro que deseas eliminar este registro")) {
                    saveSession('delete', self.data('id'));
                }
            });

            $('.restore').on('click', function () {
                let self = $(this);
                if (confirm("Estas seguro que deseas restaurar este registro")) {
                    saveSession('restore', self.data('id'));
                }
            });

            function saveSession(criteria, data, isArray = false, reload = true) {
                $.ajax({
                    url: "php/globalSetSession.php",
                    type: "POST",
                    data: {
                        criteria: criteria,
                        data: data,
                        isArray: isArray,
                        page: "dashboard"
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