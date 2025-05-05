<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo1.css">
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
                        <i class="fas fa-user-circle me-2"></i>Administrador
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
                                <h2 class="card-text">12</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Solicitudes en Proceso</h5>
                                <h2 class="card-text">5</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Solicitudes Completadas</h5>
                                <h2 class="card-text">23</h2>
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
                                    <tr>
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
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="javascript/script1.js"></script>
</body>
</html>