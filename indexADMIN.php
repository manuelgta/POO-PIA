<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';

    if (isset($_POST['requestId'])) {
        $_SESSION['soliAdmin'] = $_POST['requestId'];
        header('location: solicitudesADMIN.php');
        exit();
    }

    $stmt = $enlace->prepare("SELECT r.requestId, r.statusId,
            DATE_FORMAT(r.requestDate, '%d/%m/%Y') AS requestDate,
            u.userName, s.serviceName, sr.statusName, sr.statusClassName FROM requests r
            JOIN users u ON u.userId = r.userId
            JOIN services s ON s.serviceId = r.serviceId
            JOIN statusrequests sr ON sr.statusId = r.statusId
            WHERE r.isDeleted = 0
            ORDER BY r.statusId ASC");
    $stmt->execute();

    $requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $pending = 0;
    $process = 0;
    $completed = 0;
    foreach ($requests as $request) {
        switch ($request['statusId']) {
            case 1:
                $pending++;
                break;
            case 2:
                $process++;
                break;
            case 3:
                $completed++;
                break;
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Panel de Administración</title>
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
                <h2 class="mb-4">Resumen General</h2>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Solicitudes Pendientes</h5>
                                <h2 class="card-text"><?= $pending ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Solicitudes en Proceso</h5>
                                <h2 class="card-text"><?= $process ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Solicitudes Completadas</h5>
                                <h2 class="card-text"><?= $completed ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow">
                    <div class="card-header bg-vino text-white">
                        <h5 class="mb-0">Últimas Solicitudes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($requests as $request) {
                                            echo "
                                            <tr>
                                                <td>#{$request['requestId']}</td>
                                                <td>{$request['userName']}</td>
                                                <td>{$request['serviceName']}</td>
                                                <td>{$request['requestDate']}</td>
                                                <td><span class='badge bg-{$request['statusClassName']}'>{$request['statusName']}</span></td>
                                                <td>
                                                    <form method='post'>
                                                        <button type='submit' class='btn btn-sm btn-vino' name='requestId' value='{$request['requestId']}'>Ver</button>
                                                    </form>
                                                </td>
                                            </tr>";
                                        }
                                    ?>
                                    <!-- <tr>
                                        <td>#1025</td>
                                        <td>Juan Pérez</td>
                                        <td>Instalación</td>
                                        <td>15/05/2025</td>
                                        <td><span class="badge bg-primary">Pendiente</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-vino">Asignar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#1024</td>
                                        <td>María García</td>
                                        <td>Mantenimiento</td>
                                        <td>14/05/2025</td>
                                        <td><span class="badge bg-warning">En Proceso</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-vino">Ver</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#1023</td>
                                        <td>Carlos López</td>
                                        <td>Desinstalación</td>
                                        <td>13/05/2025</td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-vino">Ver</button>
                                        </td>
                                    </tr> -->
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