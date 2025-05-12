<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
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
                        <button class="btn btn-sm btn-outline-vino active">Todas</button>
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
                                    <tr>
                                        <td>#1025</td>
                                        <td>Juan Pérez</td>
                                        <td>Instalación</td>
                                        <td>Kit de Seguridad Básico</td>
                                        <td>15/05/2025</td>
                                        <td>
                                            <select class="form-select form-select-sm">
                                                <option selected>Sin asignar</option>
                                                <option>Carlos Méndez</option>
                                                <option>Ana Rodríguez</option>
                                                <option>Luis González</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm">
                                                <option selected>Pendiente</option>
                                                <option>En Proceso</option>
                                                <option>Completado</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-vino">Guardar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#1024</td>
                                        <td>María García</td>
                                        <td>Mantenimiento</td>
                                        <td>Cámara HD 1080p</td>
                                        <td>14/05/2025</td>
                                        <td>Carlos Méndez</td>
                                        <td>En Proceso</td>
                                        <td>
                                            <button class="btn btn-sm btn-vino">Guardar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#1023</td>
                                        <td>Carlos López</td>
                                        <td>Desinstalación</td>
                                        <td>Grabador Digital</td>
                                        <td>13/05/2025</td>
                                        <td>Ana Rodríguez</td>
                                        <td>Completado</td>
                                        <td>
                                            <button class="btn btn-sm btn-vino">Guardar</button>
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

    <?php include 'includes/body_includes.php'; ?>
</body>
</html>