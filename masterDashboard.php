<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (!isset($_SESSION['dashboard']['trash'])) $_SESSION['dashboard']['trash'] = 0;
    $trash = $_SESSION['dashboard']['trash'];

    if (!isset($_SESSION['dashboard']['table'])) $_SESSION['dashboard']['table'] = 'services';
    $table = $_SESSION['dashboard']['table'];
    $tableSLess = substr($table, 0, strlen($table) - 1);
    $ucfirstTable = ucfirst($table);

    if (isset($_SESSION['dashboard']['delete'])) {
        $delete = $_SESSION['dashboard']['delete'] ?? NULL;
        if ($table != 'logs') {
            $enlace->begin_transaction();

            try {
                if (is_null($delete)) {
                    throw new Exception("¡Algo salio mal!");
                }

                if ($table == 'companies') {
                    $tableSLess = "company";
                }
                $oldColumn = "{$tableSLess}Name";
                if ($table == 'reports') {
                    $oldColumn = "{$tableSLess}Folio";
                }
                if ($table == 'reportspdfs') {
                    $oldColumn = "{$tableSLess}Path";
                }
                if ($table == 'urls') {
                    $oldColumn = "{$tableSLess}Title";
                }
                if ($table == 'icons') {
                    $oldColumn = "{$tableSLess}Bi";
                }

                $stmt = $enlace->prepare("SELECT $oldColumn as OldValues FROM $table WHERE {$tableSLess}Id = ?");
                $stmt->bind_param("i", $delete);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $row_count = count($result);

                if ($row_count != 1) {
                    throw new Exception("¡Algo salio mal 2!");
                }

                $oldValues = $result[0]["OldValues"];

                if(!deleteLog($enlace, $table, $delete, $_SESSION['user']['id'], $oldValues)) {
                    throw new Exception("¡Algo salio mal 3!");
                }

                $stmt = $enlace->prepare("UPDATE $table SET isDeleted = 1 WHERE {$tableSLess}Id = ?");
                $stmt->bind_param("i", $delete);
                $stmt->execute();

                $enlace->commit();
                unset($_SESSION['dashboard']['delete']);
                $_SESSION['success'] = '¡Registro eliminado con éxito!';
                header('location: dashboard.php');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $enlace->rollback();
            }
        }
        unset($_SESSION['dashboard']['delete']);
    }

    if (isset($_SESSION['dashboard']['restore'])) {
        $restore = $_SESSION['dashboard']['restore'] ?? NULL;
        if ($table != 'logs') {
            $enlace->begin_transaction();

            try {
                if (is_null($restore)) {
                    throw new Exception("¡Algo salio mal!");
                }

                if ($table == 'companies') {
                    $tableSLess = "company";
                }
                $oldColumn = "{$tableSLess}Name";
                if ($table == 'reports') {
                    $oldColumn = "{$tableSLess}Folio";
                }
                if ($table == 'reportspdfs') {
                    $oldColumn = "{$tableSLess}Path";
                }
                if ($table == 'urls') {
                    $oldColumn = "{$tableSLess}Title";
                }
                if ($table == 'icons') {
                    $oldColumn = "{$tableSLess}Bi";
                }

                $stmt = $enlace->prepare("SELECT $oldColumn as restoredValues FROM $table WHERE {$tableSLess}Id = ?");
                $stmt->bind_param("i", $restore);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $row_count = count($result);

                if ($row_count != 1) {
                    throw new Exception("¡Algo salio mal 2!");
                }

                $restoredValues = $result[0]["restoredValues"];

                if(!restoreLog($enlace, $table, $restore, $_SESSION['user']['id'], $restoredValues)) {
                    throw new Exception("¡Algo salio mal 3!");
                }

                $stmt = $enlace->prepare("UPDATE $table SET isDeleted = 0 WHERE {$tableSLess}Id = ?");
                $stmt->bind_param("i", $restore);
                $stmt->execute();

                $enlace->commit();
                unset($_SESSION['dashboard']['restore']);
                $_SESSION['success'] = '¡Registro restaurado con éxito!';
                header('location: dashboard.php');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $enlace->rollback();
            }
        }
        unset($_SESSION['dashboard']['restore']);
    }

    if (!isset($_SESSION['dashboard']['page'][$table])) $_SESSION['dashboard']['page'][$table] = 0;
    if (isset($_POST['page'][$table])) $_SESSION['dashboard']['page'][$table] = intval($_POST['page'][$table]) ?? 0;
    $currentPage = $_SESSION['dashboard']['page'][$table];

    $itemsPerPage = 25;

    switch ($table) {
        case 'users':
            $query = "SELECT u.userId AS id, r.roleName, u.userName, u.userRealName, 
                            DATE_FORMAT(u.createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(u.updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM users u
                    LEFT JOIN roles r ON u.roleId = r.roleId
                    WHERE u.isDeleted = $trash
                    ORDER BY u.userId DESC";
            break;

        case 'roles':
        case 'groups':
        case 'measures':
        case 'identifiers':
        case 'machines':
        case 'companyroles':
            $query = "SELECT {$tableSLess}Id AS id,  {$tableSLess}Name,
                            DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM $table
                    WHERE isDeleted = $trash
                    ORDER BY {$tableSLess}Id DESC";
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

        case 'companies':
            $query = "SELECT companyId AS id, companyName, companyImgPath,
                            DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM companies
                    WHERE isDeleted = $trash
                    ORDER BY companyId DESC";
            break;

        case 'clients':
            $query = "SELECT clientId AS id, clientName, clientAddress, clientPhone, clientEmail,
                            DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM clients
                    WHERE isDeleted = $trash
                    ORDER BY clientId DESC";
            break;

        case 'reportspdfs':
            $query = "SELECT reportspdfId AS id, reportspdfPath, reportspdfType, isDaily, reportspdfDate,
                            DATE_FORMAT(createdAt, '%d/%m/%y %H:%i') AS createdAt,
                            DATE_FORMAT(updatedAt, '%d/%m/%y %H:%i') AS updatedAt
                    FROM reportspdfs
                    WHERE isDeleted = $trash
                    ORDER BY reportspdfId DESC";
            break;

        case 'reports':
            $query = "SELECT 
                r.reportId AS id, 
                r.reportFolio, 
                DATE_FORMAT(r.reportDate, '%d/%m/%y') AS reportDate,
                r.reportResume, 
                r.reportObservations, 
                DATE_FORMAT(r.createdAt, '%d/%m/%y %H:%i') AS createdAt,
                DATE_FORMAT(r.updatedAt, '%d/%m/%y %H:%i') AS updatedAt,

                -- Datos de clients
                c.clientName,

                -- Nombre de groups concatenados
                g.groupName,

                -- Identifiers concatenados
                GROUP_CONCAT(DISTINCT CONCAT(i.identifierName, ' (', ri.data, ')') SEPARATOR '<br>') AS identifierData,

                -- Machines concatenadas
                GROUP_CONCAT(DISTINCT CONCAT(m.machineName, ' (', rm.data, ')') SEPARATOR '<br>') AS machineData,

                -- Services concatenados
                GROUP_CONCAT(DISTINCT CONCAT(s.serviceName) SEPARATOR '<br>') AS serviceData,

                -- Measures concatenadas
                GROUP_CONCAT(DISTINCT CONCAT(me.measureName, ' (', rme.data, ')') SEPARATOR '<br>') AS measureData

            FROM reports r
            -- Relación con clients
            LEFT JOIN clients c ON r.clientId = c.clientId

            -- Relación con groups
            LEFT JOIN group_reports gr ON r.reportId = gr.reportId
            LEFT JOIN groups g ON gr.groupId = g.groupId

            -- Relación con identifiers
            LEFT JOIN report_identifiers ri ON r.reportId = ri.reportId
            LEFT JOIN identifiers i ON ri.identifierId = i.identifierId

            -- Relación con machines
            LEFT JOIN report_machines rm ON r.reportId = rm.reportId
            LEFT JOIN machines m ON rm.machineId = m.machineId

            -- Relación con services
            LEFT JOIN report_services rs ON r.reportId = rs.reportId
            LEFT JOIN services s ON rs.serviceId = s.serviceId

            -- Relación con measures
            LEFT JOIN report_measures rme ON r.reportId = rme.reportId
            LEFT JOIN measures me ON rme.measureId = me.measureId

            WHERE r.isDeleted = $trash
            GROUP BY r.reportId
            ORDER BY r.reportId DESC";
            break;

        case 'logs':
            $query = "SELECT l.logId AS id, l.tableName, l.recordId, l.actionType, l.userId, u.userName, l.oldValues, l.newValues, l.timestamp
                    FROM logs l
                    LEFT JOIN users u ON u.userId = l.userId
                    ORDER BY l.logId DESC";
            break;

        case 'loginlogs':
            $query = "SELECT ll.loginLogId AS id, ll.userId, ll.companyId, u.userName, c.companyName, ll.timestamp
                    FROM loginLogs ll
                    LEFT JOIN users u ON u.userId = ll.userId
                    LEFT JOIN companies c ON c.companyId = ll.companyId
                    ORDER BY ll.loginLogId DESC";
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
    <title>CIYSE - Dashboard</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php include 'includes/navbar.php'; ?>
    </header>
    <div class="container py-5">
        <h1 class="text-center mb-4">Dashboard</h1>
        <div class="row">
            <div class="col-11 p-0">
                <h2 class="text-center mb-4"><?= $ucfirstTable ?></h2>
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'logs' ? 'active' : '' ?>" data-session="table" data-sessionvalue="logs" href="#">Logs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'loginlogs' ? 'active' : '' ?>" data-session="table" data-sessionvalue="loginlogs" href="#">Accesos</a>
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
                    <li class="nav-item">
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
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'roles' ? 'active' : '' ?>" data-session="table" data-sessionvalue="roles" href="#">Roles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'groups' ? 'active' : '' ?>" data-session="table" data-sessionvalue="groups" href="#">Grupos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'companies' ? 'active' : '' ?>" data-session="table" data-sessionvalue="companies" href="#">Compañias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'measures' ? 'active' : '' ?>" data-session="table" data-sessionvalue="measures" href="#">Medidas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'identifiers' ? 'active' : '' ?>" data-session="table" data-sessionvalue="identifiers" href="#">Identificadores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-bottom <?= $table == 'machines' ? 'active' : '' ?>" data-session="table" data-sessionvalue="machines" href="#">Equipos</a>
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
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Rol</th>
                                            <th>Usuario</th>
                                            <th>Nombre</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'roles':
                                        case 'groups':
                                        case 'measures':
                                        case 'identifiers':
                                        case 'machines':
                                        case 'companyroles':
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'services':
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Descripcion</th>
                                            <th>Icono</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'companies':
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Imagen</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'clients':
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'reportspdfs':
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Archivo</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'reports':
                                            echo '<th>Accion</th>
                                                <th>#</th>
                                                <th>Folio</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Grupos</th>
                                                <th>Identificadores</th>
                                                <th>Máquinas</th>
                                                <th>Servicios</th>
                                                <th>Mediciones</th>
                                                <th>Resumen</th>
                                                <th>Observaciones</th>
                                                <th>Creado</th>
                                                <th>Actualizado</th>
                                            ';
                                            break;
                                        case 'urls':
                                            echo '<th>Accion</th>
                                                <th>#</th>
                                                <th>Icono</th>
                                                <th>Titulo</th>
                                                <th>Direccion</th>
                                                <th>Orden</th>
                                                <th>Permitido a todos</th>
                                                <th>Desabilitado</th>
                                                <th>Creado</th>
                                                <th>Actualizado</th>
                                            ';
                                            break;
                                        case 'icons':
                                            echo '<th>Accion</th>
                                            <th>#</th>
                                            <th>Icono</th>
                                            <th>Creado</th>
                                            <th>Última actualización</th>
                                            ';
                                            break;
                                        case 'logs':
                                            echo '<th>#</th>
                                            <th>Tabla</th>
                                            <th>Accion</th>
                                            <th>Responsable</th>
                                            <th>Anterior</th>
                                            <th>Nuevo</th>
                                            <th>Creado</th>
                                            ';
                                            break;
                                        case 'loginlogs':
                                            echo '<th>#</th>
                                            <th>Usuario</th>
                                            <th>Compañia</th>
                                            <th>Creado</th>
                                            ';
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
                                        case 'loginlogs':
                                            ?>
                                            <tr>
                                                <td><?= $id ?></td>
                                                <td>
                                                    <a
                                                        data-jsonconvert='1'
                                                        data-session='<?= json_encode(["table", "regId"]) ?> '
                                                        data-sessionvalue='<?= json_encode(['users', $row["userId"]]) ?>' href='#'>
                                                        <?= $row['userName'] ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a
                                                        data-jsonconvert='1'
                                                        data-session='<?= json_encode(["table", "regId"]) ?> '
                                                        data-sessionvalue='<?= json_encode(['companies', $row["companyId"]]) ?>' href='#'>
                                                        <?= $row['companyName'] ?>
                                                    </a>
                                                </td>
                                                <td><?= $row['timestamp'] ?></td>
                                            </tr>
                                            <?php
                                            break;
                                        case 'reports':
                                            $active = '';
                                            if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) {
                                                $active = "class='bg-primary text-light'";
                                            }
                                            echo "<tr>
                                                <td>
                                                    $buttons
                                                </td>
                                                <td $active>{$row['id']}</td>
                                                <td class='text-danger fw-bold'>{$row['reportFolio']}</td>
                                                <td>{$row['reportDate']}</td>
                                                <td>{$row['clientName']}</td>
                                                <td>{$row['groupName']}</td>
                                                <td>
                                                    <p class='text-truncate'>{$row['identifierData']}</p>
                                                    <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <p class='text-truncate'>{$row['machineData']}</p>
                                                    <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <p class='text-truncate'>{$row['serviceData']}</p>
                                                    <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <p class='text-truncate'>{$row['measureData']}</p>
                                                    <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <p class='text-truncate'>{$row['reportResume']}</p>
                                                    <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <p class='text-truncate'>{$row['reportObservations']}</p>
                                                    <button type='button' class='btn btn-primary expandBtn' data-bs-toggle='modal' data-bs-target='#expandModal'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                </td>
                                                <td>{$row['createdAt']}</td>
                                                <td>{$row['updatedAt']}</td>
                                            </tr>";
                                            break;
                                        case 'companies':
                                            $active = '';
                                            if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) {
                                                $active = "class='bg-primary text-light'";
                                            }
                                            echo "
                                            <tr>
                                                <td>$buttons</td>
                                                <td $active>{$row['id']}</td>
                                                <td>{$row['companyName']}</td>
                                                <td><img class='logo' src='{$row['companyImgPath']}' alt='Logo{$row['id']}' style='height: 52px; width: auto;'></td>
                                                <td>{$row['createdAt']}</td>
                                                <td>{$row['updatedAt']}</td>
                                            </tr>";
                                            break;
                                        case 'reportspdfs':
                                            $active = '';
                                            if (isset($_SESSION['dashboard']['regId']) && $row['id'] == $_SESSION['dashboard']['regId']) {
                                                $active = "class='bg-primary text-light'";
                                            }
                                            $type = '';
                                            switch ($row['reportspdfType']) {
                                                case 't':
                                                    $type = 'Tecnico';
                                                    break;
                                                case 'f':
                                                    $type = 'Fotografico';
                                                    break;
                                            }
                                            if ($row['isDaily'] == 0) {
                                                $date = new DateTime($row['reportspdfDate']);
                                                $row['reportspdfDate'] = ucfirst(strftime('%B / %Y', $date->getTimestamp()));
                                            } else {
                                                $date = new DateTime($row['reportspdfDate']);
                                                $row['reportspdfDate'] = strftime('%d / %B / %Y', $date->getTimestamp());
                                                $type = "Diario";
                                            }
                                            echo "
                                            <tr>
                                                <td>$buttons</td>
                                                <td $active>{$row['id']}</td>
                                                <td><a href='{$row['reportspdfPath']}' target='blank'>Ver pdf</a></td>
                                                <td>$type</td>
                                                <td>{$row['reportspdfDate']}</td>
                                                <td>{$row['createdAt']}</td>
                                                <td>{$row['updatedAt']}</td>
                                            </tr>";
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
            <div class="col-1 p-0">
                <h2 class="text-center mb-5">Pagina</h2>
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